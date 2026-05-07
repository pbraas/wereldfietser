<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\service;
class search_client
{
protected $config;
protected $log;
public function __construct(\phpbb\config\config $config, \phpbb\log\log $log)
{
$this->config = $config;
$this->log = $log;
}
public function search($query, $user_id, array $allowed_forum_ids)
{
$base_url = rtrim((string) $this->config['acme_aisearch_base_url'], '/');
$client_id = (string) $this->config['acme_aisearch_client_id'];
$secret = (string) $this->config['acme_aisearch_shared_secret'];
if ($base_url === '')
{
throw new \RuntimeException('AI Search service URL is not configured.');
}
if ($client_id === '' || $secret === '')
{
throw new \RuntimeException('AI Search authentication settings are incomplete.');
}
$path = '/v1/search';
$url = $base_url . $path;
$payload = [
'traceId' => $this->generate_trace_id(),
'query' => $query,
'topK' => (int) $this->config['acme_aisearch_top_k'],
'filters' => [
'forumIds' => array_values($allowed_forum_ids),
'visibility' => ['approved'],
],
'userContext' => [
'userId' => (int) $user_id,
'lang' => 'en',
],
];
$body = json_encode($payload);
if ($body === false)
{
throw new \RuntimeException('Unable to encode request payload.');
}
$timestamp = (string) time();
$nonce = bin2hex(random_bytes(16));
$body_hash = hash('sha256', $body);
$canonical = implode("\n", ['POST', $path, $timestamp, $nonce, $body_hash]);
$signature = hash_hmac('sha256', $canonical, $secret);
$timeout_seconds = max(1, (int) ceil(((int) $this->config['acme_aisearch_timeout_ms']) / 1000));
$context = stream_context_create([
'http' => [
'method' => 'POST',
'timeout' => $timeout_seconds,
'ignore_errors' => true,
'header' => [
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
$response = @file_get_contents($url, false, $context);
$status_code = $this->get_status_code(isset($http_response_header) ? $http_response_header : []);
if ($response === false || $status_code < 200 || $status_code >= 300)
{
$this->log->add('critical', 0, '', 'LOG_AISEARCH_REMOTE_ERROR', false, [$status_code, $url]);
throw new \RuntimeException('AI Search service is unavailable right now.');
}
$decoded = json_decode($response, true);
if (!is_array($decoded) || !isset($decoded['results']) || !is_array($decoded['results']))
{
throw new \RuntimeException('AI Search response format is invalid.');
}
$rows = [];
foreach ($decoded['results'] as $item)
{
$rows[] = [
'TITLE' => isset($item['title']) ? (string) $item['title'] : '',
'SNIPPET' => isset($item['snippet']) ? (string) $item['snippet'] : '',
'URL' => isset($item['url']) ? (string) $item['url'] : '',
'SCORE' => isset($item['score']) ? (float) $item['score'] : 0.0,
];
}
return $rows;
}

public function health_check()
{
$base_url = rtrim((string) $this->config['acme_aisearch_base_url'], '/');
if ($base_url === '')
{
throw new \RuntimeException('AI Search service URL is not configured.');
}

$url = $base_url . '/v1/health';
$timeout_seconds = max(1, (int) ceil(((int) $this->config['acme_aisearch_timeout_ms']) / 1000));

$context = stream_context_create([
'http' => [
'method' => 'GET',
'timeout' => $timeout_seconds,
'ignore_errors' => true,
'header' => [
'Accept: application/json',
],
],
]);

$response = @file_get_contents($url, false, $context);
$status_code = $this->get_status_code(isset($http_response_header) ? $http_response_header : []);

if ($response === false || $status_code < 200 || $status_code >= 300)
{
throw new \RuntimeException('AI Search service did not respond with a successful health status.');
}

$decoded = json_decode($response, true);
$status_text = is_array($decoded) && isset($decoded['status']) ? (string) $decoded['status'] : 'ok';
$version = is_array($decoded) && isset($decoded['version']) ? (string) $decoded['version'] : '';

return [
'ok' => true,
'status_code' => $status_code,
'message' => $version !== '' ? $status_text . ' (v' . $version . ')' : $status_text,
];
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
