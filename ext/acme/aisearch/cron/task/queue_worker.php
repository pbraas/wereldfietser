<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\cron\task;

/**
 * Cron task that drains the aisearch_queue table.
 *
 * For every NEW (or retryable FAILED) row it either
 *   - fetches post+topic data from the DB and calls /v1/index/upsert, or
 *   - calls /v1/index/delete with the stored post IDs.
 *
 * Rows are marked DONE on success and FAILED (with incremented retry_count)
 * on error. Rows that have reached MAX_RETRIES remain FAILED permanently and
 * are cleaned up after CLEANUP_DAYS days.
 */
class queue_worker extends \phpbb\cron\task\base
{
	/** Run at most once per minute. */
	const CRON_FREQUENCY = 60;

	/** Abandon a row after this many consecutive failures. */
	const MAX_RETRIES = 3;

	/** Remove DONE / permanently-failed rows older than this many days. */
	const CLEANUP_DAYS = 7;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \acme\aisearch\service\index_client */
	protected $index_client;

	/** @var string */
	protected $queue_table;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $topics_table;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\acme\aisearch\service\index_client $index_client,
		$queue_table,
		$posts_table,
		$topics_table
	)
	{
		$this->config       = $config;
		$this->db           = $db;
		$this->index_client = $index_client;
		$this->queue_table  = $queue_table;
		$this->posts_table  = $posts_table;
		$this->topics_table = $topics_table;
	}

	// -------------------------------------------------------------------------
	// phpBB cron interface
	// -------------------------------------------------------------------------

	/**
	 * Only run when the extension is enabled and the service URL is set.
	 */
	public function is_runnable()
	{
		return (bool) $this->config['acme_aisearch_enabled']
			&& (string) $this->config['acme_aisearch_base_url'] !== '';
	}

	/**
	 * Run at most once every CRON_FREQUENCY seconds.
	 */
	public function should_run()
	{
		return (int) $this->config['acme_aisearch_cron_last_run'] < (time() - self::CRON_FREQUENCY);
	}

	/**
	 * Main entry point called by phpBB's cron dispatcher.
	 */
	public function run()
	{
		$batch_size = max(1, (int) $this->config['acme_aisearch_batch_size']);
		$this->do_process($batch_size);
		$this->config->set('acme_aisearch_cron_last_run', time(), false);
	}

	/**
	 * Process queue items immediately, bypassing the cron timer.
	 * Loops internally until the queue is empty or the time budget runs out.
	 * Called from the ACP "Process queue now" button.
	 *
	 * @param  int $batch_size      Rows per inner iteration (sent as one API call).
	 * @param  int $time_limit_sec  Stop after this many seconds (default 600).
	 * @return int                  Total rows processed in this call.
	 */
	public function flush($batch_size = 500, $time_limit_sec = 600)
	{
		@set_time_limit(max(90, (int) $time_limit_sec + 30));
		$started         = time();
		$total_processed = 0;

		do
		{
			$processed = $this->do_process($batch_size);
			$total_processed += $processed;

			if ($processed < $batch_size)
			{
				break; // Queue is now empty (last batch was smaller than requested)
			}
		}
		while ((time() - $started) < $time_limit_sec);

		return $total_processed;
	}

	// -------------------------------------------------------------------------
	// Internal helpers
	// -------------------------------------------------------------------------

	/**
	 * Core batch-processing logic shared by run() and flush().
	 * Returns the number of rows that were picked up for processing.
	 */
	protected function do_process($batch_size)
	{
		// ---- 1. Fetch a batch of processable rows ---------------------------
		$sql = 'SELECT id, event_type, payload_json, retry_count
				FROM ' . $this->queue_table . "
				WHERE event_status IN ('NEW', 'FAILED')
				AND retry_count < " . self::MAX_RETRIES . '
				ORDER BY id ASC';
		$result = $this->db->sql_query_limit($sql, $batch_size);

		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		if (empty($rows))
		{
			return 0;
		}

		// ---- 2. Lock the batch by marking it PROCESSING ---------------------
		$ids = array_map('intval', array_column($rows, 'id'));
		$sql = 'UPDATE ' . $this->queue_table . "
				SET event_status = 'PROCESSING'
				WHERE " . $this->db->sql_in_set('id', $ids);
		$this->db->sql_query($sql);

		// ---- 3. Split by event type -----------------------------------------
		$upsert_rows = [];
		$delete_rows = [];

		foreach ($rows as $row)
		{
			if ($row['event_type'] === 'UPSERT')
			{
				$upsert_rows[] = $row;
			}
			else if ($row['event_type'] === 'DELETE')
			{
				$delete_rows[] = $row;
			}
			else
			{
				$this->mark_done([(int) $row['id']]);
			}
		}

		// ---- 4 & 5. Process each group --------------------------------------
		if (!empty($upsert_rows))
		{
			$this->process_upserts($upsert_rows);
		}

		if (!empty($delete_rows))
		{
			$this->process_deletes($delete_rows);
		}

		// ---- 6. House-keeping -----------------------------------------------
		$this->cleanup_old_rows();

		return count($rows);
	}

	/**
	 * Fetch post + topic data for all UPSERT rows, build documents,
	 * and send them to the index service in one batch API call.
	 */
	protected function process_upserts(array $rows)
	{
		// Map post_id => queue row id
		$post_id_to_queue_id = [];

		foreach ($rows as $row)
		{
			$payload = json_decode($row['payload_json'], true);

			if (!isset($payload['post_id']))
			{
				// Malformed payload – discard
				$this->mark_done([(int) $row['id']]);
				continue;
			}

			$post_id_to_queue_id[(int) $payload['post_id']] = (int) $row['id'];
		}

		if (empty($post_id_to_queue_id))
		{
			return;
		}

		$post_ids = array_keys($post_id_to_queue_id);

		// Fetch post + topic in a single JOIN query
		$sql = 'SELECT p.post_id, p.topic_id, p.forum_id, p.poster_id, p.post_text, p.post_time,
					   t.topic_title
				FROM ' . $this->posts_table . ' p
				JOIN ' . $this->topics_table . ' t ON p.topic_id = t.topic_id
				WHERE ' . $this->db->sql_in_set('p.post_id', $post_ids);
		$result = $this->db->sql_query($sql);

		$documents      = [];
		$found_post_ids = [];

		while ($post = $this->db->sql_fetchrow($result))
		{
			$post_id        = (int) $post['post_id'];
			$found_post_ids[] = $post_id;
			$content        = $this->strip_bbcode((string) $post['post_text']);
			$url            = $this->build_post_url($post_id, (int) $post['topic_id']);

			$documents[] = [
				'documentId'      => 'post:' . $post_id,
				'sourceType'      => 'phpbb_post',
				'sourceId'        => $post_id,
				'forumId'         => (int) $post['forum_id'],
				'topicId'         => (int) $post['topic_id'],
				'postId'          => $post_id,
				'authorId'        => (int) $post['poster_id'],
				'title'           => (string) $post['topic_title'],
				'content'         => $content,
				'url'             => $url,
				'visibility'      => 'approved',
				'permissionScope' => ['forumIds' => [(int) $post['forum_id']]],
				'updatedAt'       => gmdate('Y-m-d\TH:i:s\Z', (int) $post['post_time']),
				'contentHash'     => md5($content),
			];
		}
		$this->db->sql_freeresult($result);

		// Posts not found in DB were deleted before the cron ran – treat as DONE
		$missing = array_diff($post_ids, $found_post_ids);
		foreach ($missing as $missing_id)
		{
			$this->mark_done([$post_id_to_queue_id[$missing_id]]);
		}

		if (empty($documents))
		{
			return;
		}

		$queue_ids = [];
		foreach ($found_post_ids as $pid)
		{
			$queue_ids[] = $post_id_to_queue_id[$pid];
		}

		try
		{
			$this->index_client->upsert($documents);
			$this->mark_done($queue_ids);
		}
		catch (\RuntimeException $e)
		{
			$this->mark_failed($queue_ids);
		}
	}

	/**
	 * Collect all post IDs from DELETE rows and remove them from the index
	 * in one batch API call.
	 */
	protected function process_deletes(array $rows)
	{
		$document_ids = [];
		$queue_ids    = [];

		foreach ($rows as $row)
		{
			$payload = json_decode($row['payload_json'], true);

			if (!isset($payload['post_ids']) || !is_array($payload['post_ids']))
			{
				// Malformed payload – discard
				$this->mark_done([(int) $row['id']]);
				continue;
			}

			$queue_ids[] = (int) $row['id'];

			foreach ($payload['post_ids'] as $post_id)
			{
				$document_ids[] = 'post:' . (int) $post_id;
			}
		}

		if (empty($document_ids) || empty($queue_ids))
		{
			return;
		}

		try
		{
			$this->index_client->delete_by_ids($document_ids);
			$this->mark_done($queue_ids);
		}
		catch (\RuntimeException $e)
		{
			$this->mark_failed($queue_ids);
		}
	}

	/**
	 * Mark queue rows as successfully processed.
	 */
	protected function mark_done(array $ids)
	{
		if (empty($ids))
		{
			return;
		}
		$sql = 'UPDATE ' . $this->queue_table . "
				SET event_status = 'DONE'
				WHERE " . $this->db->sql_in_set('id', $ids);
		$this->db->sql_query($sql);
	}

	/**
	 * Mark queue rows as failed and increment their retry counter.
	 */
	protected function mark_failed(array $ids)
	{
		if (empty($ids))
		{
			return;
		}
		$sql = 'UPDATE ' . $this->queue_table . "
				SET event_status = 'FAILED', retry_count = retry_count + 1
				WHERE " . $this->db->sql_in_set('id', $ids);
		$this->db->sql_query($sql);
	}

	/**
	 * Delete DONE rows and permanently-failed rows older than CLEANUP_DAYS.
	 */
	protected function cleanup_old_rows()
	{
		$cutoff = time() - (self::CLEANUP_DAYS * 86400);
		$sql    = 'DELETE FROM ' . $this->queue_table . "
				WHERE (event_status = 'DONE' OR retry_count >= " . self::MAX_RETRIES . ')
				AND created_at < ' . (int) $cutoff;
		$this->db->sql_query($sql);
	}

	/**
	 * Build an absolute URL to a specific post.
	 */
	protected function build_post_url($post_id, $topic_id)
	{
		$scheme      = rtrim((string) $this->config['server_protocol'], ':/') . '://';
		$server_name = (string) $this->config['server_name'];
		$server_port = (int) $this->config['server_port'];
		$script_path = rtrim((string) $this->config['script_path'], '/');

		$port_suffix = '';
		if ($server_port && $server_port !== 80 && $server_port !== 443)
		{
			$port_suffix = ':' . $server_port;
		}

		return $scheme . $server_name . $port_suffix . $script_path
			. '/viewtopic.php?p=' . (int) $post_id
			. '&t=' . (int) $topic_id
			. '#p' . (int) $post_id;
	}

	/**
	 * Strip BBCode markup and collapse whitespace from post text.
	 */
	protected function strip_bbcode($text)
	{
		// Remove [tag], [tag=value], [/tag]
		$text = preg_replace('#\[/?[a-z][a-z0-9]*(?:=[^\]]+)?\]#i', '', (string) $text);
		// Decode HTML entities
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		// Collapse whitespace
		return trim(preg_replace('/\s+/', ' ', $text));
	}
}

