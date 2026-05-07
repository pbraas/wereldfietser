<?php
/**
 *
 * AI Search.
 *
 */
if (!defined('IN_PHPBB'))
{
exit;
}
if (empty($lang) || !is_array($lang))
{
$lang = [];
}
$lang = array_merge($lang, [
'AISEARCH_TITLE' => 'AI Search',
'AISEARCH_QUERY_LABEL' => 'Ask the forum',
'AISEARCH_SEARCH' => 'Search',
'AISEARCH_NO_RESULTS' => 'No AI results found for this query.',
'AISEARCH_DISABLED' => 'AI Search is currently disabled by the administrator.',
'AISEARCH_MENU' => 'AI Search',
'LOG_AISEARCH_REMOTE_ERROR'      => '<strong>AI Search remote error</strong><br>HTTP code: %1$s<br>URL: %2$s',
'LOG_AISEARCH_QUEUE_UPSERT_FAIL' => '<strong>AI Search queue worker – upsert batch failed</strong>',
'LOG_AISEARCH_QUEUE_DELETE_FAIL' => '<strong>AI Search queue worker – delete batch failed</strong>',
]);
