<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\controller;

class acp_controller
{
	protected $config;
	protected $db;
	protected $language;
	protected $log;
	protected $request;
	protected $template;
	protected $user;
	protected $search_client;
	protected $index_client;
	protected $queue_worker;
	protected $queue_table;
	protected $posts_table;
	protected $u_action;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\acme\aisearch\service\search_client $search_client,
		\acme\aisearch\service\index_client $index_client,
		\acme\aisearch\cron\task\queue_worker $queue_worker,
		$queue_table,
		$posts_table
	)
	{
		$this->config       = $config;
		$this->db           = $db;
		$this->language     = $language;
		$this->log          = $log;
		$this->request      = $request;
		$this->template     = $template;
		$this->user         = $user;
		$this->search_client = $search_client;
		$this->index_client  = $index_client;
		$this->queue_worker  = $queue_worker;
		$this->queue_table   = $queue_table;
		$this->posts_table   = $posts_table;
	}

	public function display_options()
	{
		$this->language->add_lang('info_acp_aisearch', 'acme/aisearch');
		add_form_key('acme_aisearch_acp');

		$errors       = [];
		$health_check = null;

		// ---- Save settings --------------------------------------------------
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('acme_aisearch_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			if (empty($errors))
			{
				$search_mode = $this->request->variable('acme_aisearch_search_mode', 'lexical');
				if (!in_array($search_mode, ['lexical', 'hybrid'], true))
				{
					$search_mode = 'lexical';
				}

				$semantic_strategy = $this->request->variable('acme_aisearch_semantic_strategy', 'proxy');
				if (!in_array($semantic_strategy, ['proxy', 'embedding'], true))
				{
					$semantic_strategy = 'proxy';
				}

				$hybrid_alpha = (float) $this->request->variable('acme_aisearch_hybrid_alpha', 0.35);
				$hybrid_alpha = max(0.0, min(1.0, $hybrid_alpha));

				$semantic_top_k = (int) $this->request->variable('acme_aisearch_semantic_top_k', 50);
				$semantic_top_k = max(1, min(200, $semantic_top_k));

				$this->config->set('acme_aisearch_enabled',       $this->request->variable('acme_aisearch_enabled', 0));
				$this->config->set('acme_aisearch_base_url',      $this->request->variable('acme_aisearch_base_url', '', true));
				$this->config->set('acme_aisearch_client_id',     $this->request->variable('acme_aisearch_client_id', '', true));
				$this->config->set('acme_aisearch_shared_secret', $this->request->variable('acme_aisearch_shared_secret', '', true));
				$this->config->set('acme_aisearch_timeout_ms',    $this->request->variable('acme_aisearch_timeout_ms', 3000));
				$this->config->set('acme_aisearch_top_k',         $this->request->variable('acme_aisearch_top_k', 10));
				$this->config->set('acme_aisearch_search_mode',   $search_mode);
				$this->config->set('acme_aisearch_semantic_enabled', $this->request->variable('acme_aisearch_semantic_enabled', 0));
				$this->config->set('acme_aisearch_hybrid_alpha',  number_format($hybrid_alpha, 2, '.', ''));
				$this->config->set('acme_aisearch_semantic_top_k', $semantic_top_k);
				$this->config->set('acme_aisearch_semantic_strategy', $semantic_strategy);
				$this->config->set('acme_aisearch_embedding_query_enabled', $this->request->variable('acme_aisearch_embedding_query_enabled', 0));
				$this->config->set('acme_aisearch_embedding_index_enabled', $this->request->variable('acme_aisearch_embedding_index_enabled', 0));
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_AISEARCH_SETTINGS');
				trigger_error($this->language->lang('ACP_AISEARCH_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		// ---- Health check ---------------------------------------------------
		if ($this->request->is_set_post('health_check'))
		{
			if (!check_form_key('acme_aisearch_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			if (empty($errors))
			{
				try
				{
					$result       = $this->search_client->health_check();
					$health_check = [
						'ok'          => !empty($result['ok']),
						'message'     => (string) $result['message'],
						'status_code' => (int)    $result['status_code'],
					];
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_AISEARCH_HEALTH_CHECK');
				}
				catch (\RuntimeException $e)
				{
					$health_check = ['ok' => false, 'message' => $e->getMessage(), 'status_code' => 0];
				}
			}
		}

		// ---- Test search ----------------------------------------------------
		$test_query = '';
		$test_error = '';
		$test_ran   = false;

		if ($this->request->is_set_post('test_search'))
		{
			if (!check_form_key('acme_aisearch_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			if (empty($errors))
			{
				$test_query = trim($this->request->variable('acme_aisearch_test_query', '', true));
				$test_ran   = true;
				if ($test_query !== '')
				{
					try
					{
						$rows = $this->search_client->search($test_query, $this->user->data['user_id'], []);
						foreach ($rows as $row)
						{
							$this->template->assign_block_vars('test_results', [
								'TITLE'   => $row['TITLE'],
								'SNIPPET' => $row['SNIPPET'],
								'URL'     => $row['URL'],
								'SCORE'   => number_format($row['SCORE'], 4),
							]);
						}
						$this->template->assign_var('AISEARCH_TEST_COUNT', count($rows));
					}
					catch (\RuntimeException $e)
					{
						$test_error = $e->getMessage();
					}
				}
			}
		}

		// ---- Purge Java index -----------------------------------------------
		if ($this->request->is_set_post('purge_index'))
		{
			if (!check_form_key('acme_aisearch_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			if (empty($errors))
			{
				try
				{
					$this->index_client->purge_all();
					// Mark DONE rows as NEW again so they get re-sent next flush
					$sql = 'UPDATE ' . $this->queue_table . " SET event_status = 'NEW', retry_count = 0 WHERE event_status = 'DONE'";
					$this->db->sql_query($sql);
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_AISEARCH_PURGE');
					trigger_error($this->language->lang('ACP_AISEARCH_PURGE_DONE') . adm_back_link($this->u_action));
				}
				catch (\RuntimeException $e)
				{
					$errors[] = $e->getMessage();
				}
			}
		}

		// ---- Queue all existing posts for re-indexing -----------------------
		if ($this->request->is_set_post('queue_reindex'))
		{
			if (!check_form_key('acme_aisearch_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			if (empty($errors))
			{
				// Clear stale unprocessed rows to avoid duplicates
				$sql = 'DELETE FROM ' . $this->queue_table . " WHERE event_status IN ('NEW', 'FAILED')";
				$this->db->sql_query($sql);

				$queued = $this->bulk_queue_all_posts();

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_AISEARCH_REINDEX', false, [$queued]);
				trigger_error($this->language->lang('ACP_AISEARCH_REINDEX_QUEUED', $queued) . adm_back_link($this->u_action));
			}
		}

		// ---- Process queue now ----------------------------------------------
		if ($this->request->is_set_post('flush_queue'))
		{
			if (!check_form_key('acme_aisearch_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			if (empty($errors))
			{
				$processed = $this->queue_worker->flush(500, 600);
				$remaining = $this->count_pending();
				trigger_error($this->language->lang('ACP_AISEARCH_FLUSH_DONE', $processed, $remaining) . adm_back_link($this->u_action));
			}
		}

		// ---- Stop queue (release PROCESSING rows) ----------------------------
		if ($this->request->is_set_post('stop_queue'))
		{
			if (!check_form_key('acme_aisearch_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			if (empty($errors))
			{
				$sql = 'UPDATE ' . $this->queue_table . "\n\t\t\t\t\tSET event_status = 'NEW'\n\t\t\t\t\tWHERE event_status = 'PROCESSING'";
				$this->db->sql_query($sql);
				$released = (int) $this->db->sql_affectedrows();
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_AISEARCH_STOP_QUEUE', false, [$released]);
				trigger_error($this->language->lang('ACP_AISEARCH_STOP_QUEUE_DONE', $released) . adm_back_link($this->u_action));
			}
		}

		// ---- Queue stats (shown on every page load) -------------------------
		$sql    = 'SELECT COUNT(*) as cnt FROM ' . $this->posts_table . ' WHERE post_visibility = 1';
		$result = $this->db->sql_query($sql);
		$total_posts = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		$status_counts = ['NEW' => 0, 'PROCESSING' => 0, 'DONE' => 0, 'FAILED' => 0];
		$sql    = 'SELECT event_status, COUNT(*) as cnt FROM ' . $this->queue_table . ' GROUP BY event_status';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$status_counts[$row['event_status']] = (int) $row['cnt'];
		}
		$this->db->sql_freeresult($result);

		$queue_pending = $status_counts['NEW'] + $status_counts['FAILED'];
		$derived_done  = max(0, $total_posts - $queue_pending - $status_counts['PROCESSING']);
		$progress_done = min(max($status_counts['DONE'], $derived_done), $total_posts);
		$progress_pct  = ($total_posts > 0) ? round(($progress_done * 100) / $total_posts, 2) : 0;

		// ---- Build health message -------------------------------------------
		$health_message = '';
		if (is_array($health_check))
		{
			$health_message = $health_check['ok']
				? $this->language->lang('ACP_AISEARCH_HEALTH_OK',   $health_check['status_code'], $health_check['message'])
				: $this->language->lang('ACP_AISEARCH_HEALTH_FAIL', $health_check['status_code'], $health_check['message']);
		}

		$s_errors = !empty($errors);
		$this->template->assign_vars([
			'S_ERROR'                     => $s_errors,
			'ERROR_MSG'                   => $s_errors ? implode('<br>', $errors) : '',
			'S_AISEARCH_HEALTH_CHECK'     => is_array($health_check),
			'S_AISEARCH_HEALTH_OK'        => is_array($health_check) && !empty($health_check['ok']),
			'AISEARCH_HEALTH_MESSAGE'     => $health_message,
			'S_AISEARCH_TEST_RAN'         => $test_ran,
			'AISEARCH_TEST_QUERY'         => $test_query,
			'AISEARCH_TEST_ERROR'         => $test_error,
			'U_ACTION'                    => $this->u_action,
			'ACME_AISEARCH_ENABLED'       => (bool)   $this->config['acme_aisearch_enabled'],
			'ACME_AISEARCH_BASE_URL'      => (string) $this->config['acme_aisearch_base_url'],
			'ACME_AISEARCH_CLIENT_ID'     => (string) $this->config['acme_aisearch_client_id'],
			'ACME_AISEARCH_SHARED_SECRET' => (string) $this->config['acme_aisearch_shared_secret'],
			'ACME_AISEARCH_TIMEOUT_MS'    => (int)    $this->config['acme_aisearch_timeout_ms'],
			'ACME_AISEARCH_TOP_K'         => (int)    $this->config['acme_aisearch_top_k'],
			'ACME_AISEARCH_SEARCH_MODE'   => (string) (isset($this->config['acme_aisearch_search_mode']) ? $this->config['acme_aisearch_search_mode'] : 'lexical'),
			'ACME_AISEARCH_SEMANTIC_ENABLED' => (bool) (isset($this->config['acme_aisearch_semantic_enabled']) ? $this->config['acme_aisearch_semantic_enabled'] : 0),
			'ACME_AISEARCH_HYBRID_ALPHA'  => (float)  (isset($this->config['acme_aisearch_hybrid_alpha']) ? $this->config['acme_aisearch_hybrid_alpha'] : 0.35),
			'ACME_AISEARCH_SEMANTIC_TOP_K' => (int)   (isset($this->config['acme_aisearch_semantic_top_k']) ? $this->config['acme_aisearch_semantic_top_k'] : 50),
			'ACME_AISEARCH_SEMANTIC_STRATEGY' => (string) (isset($this->config['acme_aisearch_semantic_strategy']) ? $this->config['acme_aisearch_semantic_strategy'] : 'proxy'),
			'ACME_AISEARCH_EMBEDDING_QUERY_ENABLED' => (bool) (isset($this->config['acme_aisearch_embedding_query_enabled']) ? $this->config['acme_aisearch_embedding_query_enabled'] : 0),
			'ACME_AISEARCH_EMBEDDING_INDEX_ENABLED' => (bool) (isset($this->config['acme_aisearch_embedding_index_enabled']) ? $this->config['acme_aisearch_embedding_index_enabled'] : 0),
			// Index stats
			'AISEARCH_TOTAL_POSTS'        => $total_posts,
			'AISEARCH_QUEUE_PENDING'      => $queue_pending,
			'AISEARCH_QUEUE_DONE'         => $status_counts['DONE'],
			'AISEARCH_QUEUE_FAILED'       => $status_counts['FAILED'],
			'AISEARCH_QUEUE_PROCESSING'   => $status_counts['PROCESSING'],
			'AISEARCH_PROGRESS_DONE'      => $progress_done,
			'AISEARCH_PROGRESS_PERCENT'   => $progress_pct,
		]);
	}

	/**
	 * Count queue rows that still need processing.
	 */
	protected function count_pending()
	{
		$sql    = 'SELECT COUNT(*) as cnt FROM ' . $this->queue_table
				. " WHERE event_status IN ('NEW', 'FAILED') AND retry_count < " . \acme\aisearch\cron\task\queue_worker::MAX_RETRIES;
		$result = $this->db->sql_query($sql);
		$count  = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);
		return $count;
	}

	/**
	 * Bulk-insert all visible forum posts into the aisearch_queue as UPSERT events.
	 * Returns the number of posts queued.
	 */
	protected function bulk_queue_all_posts()
	{
		$sql    = 'SELECT post_id, topic_id, forum_id, poster_id
				   FROM ' . $this->posts_table . '
				   WHERE post_visibility = 1
				   ORDER BY post_id ASC';
		$result = $this->db->sql_query($sql);

		$batch = [];
		$count = 0;
		$now   = time();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$batch[] = [
				'event_type'   => 'UPSERT',
				'payload_json' => json_encode([
					'post_id'  => (int) $row['post_id'],
					'topic_id' => (int) $row['topic_id'],
					'forum_id' => (int) $row['forum_id'],
					'user_id'  => (int) $row['poster_id'],
				]),
				'event_status' => 'NEW',
				'retry_count'  => 0,
				'created_at'   => $now,
			];
			$count++;

			if (count($batch) >= 500)
			{
				$this->db->sql_multi_insert($this->queue_table, $batch);
				$batch = [];
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($batch))
		{
			$this->db->sql_multi_insert($this->queue_table, $batch);
		}

		return $count;
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
