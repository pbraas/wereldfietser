<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\service;

class index_client
{
	protected $config;
	protected $log;

	public function __construct(\phpbb\config\config $config, \phpbb\log\log $log)
	{
		$this->config = $config;
		$this->log    = $log;
	}

	/**
	 * Send an array of documents to the Java service for indexing.
	 *
	 * Each element of $documents must match IndexUpsertRequest.Document:
	 *   documentId, sourceType, sourceId, forumId, topicId, postId,
	 *   authorId, title, content, url, visibility, permissionScope,
	 *   updatedAt, contentHash
	 *
	 * @param array $documents
	 * @throws \RuntimeException
	 */
	public function upsert(array $documents)
	{
		$payload = [
			'traceId'   => $this->generate_trace_id(),
			'documents' => $documents,
		];
		$this->signed_post('/v1/index/upsert', $payload);
	}

	/**
	 * Delete indexed documents by their document ID strings.
	 *
	 * @param string[] $document_ids  e.g. ['post:42', 'post:43']
	 * @throws \RuntimeException
	 */
	public function delete_by_ids(array $document_ids)
	{
		$payload = [
			'traceId'  => $this->generate_trace_id(),
			'deleteBy' => 'document_id',
			'ids'      => array_values($document_ids),
			'reason'   => 'post_deleted',
		];
		$this->signed_post('/v1/index/delete', $payload);
	}

	/**
	 * Wipe the entire index on the Java service (all documents gone).
	 *
	 * @throws \RuntimeException
	 */
	public function purge_all()
	{
		$this->signed_post('/v1/index/purge', ['traceId' => $this->generate_trace_id()]);
	}

	/**
	 * Sign the request body with HMAC-SHA256 and POST it.
	 *
	 * @param string $path     e.g. '/v1/index/upsert'
	 * @param array  $payload
	 * @throws \RuntimeException
	 */
	protected function signed_post($path, array $payload)
	{
		$base_url  = rtrim((string) $this->config['acme_aisearch_base_url'], '/');
		$client_id = (string) $this->config['acme_aisearch_client_id'];
		$secret    = (string) $this->config['acme_aisearch_shared_secret'];

		if ($base_url === '' || $client_id === '' || $secret === '')
		{
			throw new \RuntimeException('AI Search indexer is not fully configured (missing URL, client ID or secret).');
		}

		$url  = $base_url . $path;
		$body = json_encode($payload);

		if ($body === false)
		{
			throw new \RuntimeException('Unable to JSON-encode index request payload.');
		}

		$timestamp = (string) time();
		$nonce     = bin2hex(random_bytes(16));
		$body_hash = hash('sha256', $body);
		$canonical = implode("\n", ['POST', $path, $timestamp, $nonce, $body_hash]);
		$signature = hash_hmac('sha256', $canonical, $secret);

		$timeout_seconds = max(1, (int) ceil(((int) $this->config['acme_aisearch_timeout_ms']) / 1000));

		$context = stream_context_create([
			'http' => [
				'method'        => 'POST',
				'timeout'       => $timeout_seconds,
				'ignore_errors' => true,
				'header'        => [
					'Content-Type: application/json',
					'X-Client-Id: ' . $client_id,
					'X-Timestamp: ' . $timestamp,
					'X-Nonce: ' . $nonce,
					'X-Body-Sha256: ' . $body_hash,
					'X-Signature: ' . $signature,
				],
				'content' => $body,
			],
		]);

		$response    = @file_get_contents($url, false, $context);
		$status_code = $this->get_status_code(isset($http_response_header) ? $http_response_header : []);

		if ($response === false || $status_code < 200 || $status_code >= 300)
		{
			$this->log->add('critical', 0, '', 'LOG_AISEARCH_REMOTE_ERROR', false, [$status_code, $url]);
			throw new \RuntimeException('AI Search index call failed (HTTP ' . $status_code . ') for ' . $path . '.');
		}
	}

	protected function generate_trace_id()
	{
		return bin2hex(random_bytes(16));
	}

	protected function get_status_code(array $headers)
	{
		if (empty($headers) || !isset($headers[0]))
		{
			return 0;
		}
		if (preg_match('#\\s(\\d{3})\\s#', $headers[0], $matches))
		{
			return (int) $matches[1];
		}
		return 0;
	}
}

