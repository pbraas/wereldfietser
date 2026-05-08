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
'ACP_AISEARCH_TITLE' => 'AI Search',
'ACP_AISEARCH' => 'Settings',
'ACP_AISEARCH_SETTING_SAVED' => 'AI Search settings updated.',
'ACP_AISEARCH_HEALTH_CHECK' => 'Check AI service health',
'ACP_AISEARCH_HEALTH_OK' => 'AI service is reachable (HTTP %1$d): %2$s',
'ACP_AISEARCH_HEALTH_FAIL' => 'AI service health check failed (HTTP %1$d): %2$s',
'ACP_AISEARCH_ENABLED' => 'Enable AI Search',
'ACP_AISEARCH_BASE_URL' => 'Service base URL',
'ACP_AISEARCH_CLIENT_ID' => 'Client ID',
'ACP_AISEARCH_SHARED_SECRET' => 'Shared secret',
'ACP_AISEARCH_TIMEOUT_MS' => 'Timeout (ms)',
'ACP_AISEARCH_TOP_K' => 'Results per query',
'ACP_AISEARCH_SEARCH_MODE' => 'Search mode',
'ACP_AISEARCH_SEARCH_MODE_LEXICAL' => 'Lexical (BM25 only)',
'ACP_AISEARCH_SEARCH_MODE_HYBRID' => 'Hybrid (BM25 + semantic)',
'ACP_AISEARCH_SEARCH_MODE_EXPLAIN' => 'Keep lexical as default during rollout. Switch to hybrid after semantic indexing is validated.',
'ACP_AISEARCH_SEMANTIC_ENABLED' => 'Enable semantic indexing',
'ACP_AISEARCH_SEMANTIC_ENABLED_EXPLAIN' => 'When enabled, posts are prepared for semantic retrieval in the Java service.',
'ACP_AISEARCH_HYBRID_ALPHA' => 'Hybrid weight (alpha)',
'ACP_AISEARCH_HYBRID_ALPHA_EXPLAIN' => 'Blend weight for lexical score contribution between 0.00 and 1.00.',
'ACP_AISEARCH_SEMANTIC_TOP_K' => 'Semantic candidate pool (top K)',
'ACP_AISEARCH_SEMANTIC_TOP_K_EXPLAIN' => 'How many semantic candidates are gathered before final ranking.',
'ACP_AISEARCH_SEMANTIC_STRATEGY' => 'Semantic strategy',
'ACP_AISEARCH_SEMANTIC_STRATEGY_PROXY' => 'Proxy (fuzzy-based)',
'ACP_AISEARCH_SEMANTIC_STRATEGY_EMBEDDING' => 'Embedding (true semantic)',
'ACP_AISEARCH_SEMANTIC_STRATEGY_EXPLAIN' => 'Use Proxy for stable rollout. Switch to Embedding after index and query embedding toggles are enabled and validated.',
'ACP_AISEARCH_EMBEDDING_INDEX_ENABLED' => 'Enable embedding generation for indexing',
'ACP_AISEARCH_EMBEDDING_INDEX_ENABLED_EXPLAIN' => 'When enabled, newly indexed posts are prepared for embedding-based retrieval in the Java service.',
'ACP_AISEARCH_EMBEDDING_QUERY_ENABLED' => 'Enable embedding-based query retrieval',
'ACP_AISEARCH_EMBEDDING_QUERY_ENABLED_EXPLAIN' => 'When enabled, hybrid mode may use embedding candidates in addition to lexical ranking.',
'LOG_ACP_AISEARCH_SETTINGS' => '<strong>Changed AI Search settings</strong>',
'LOG_ACP_AISEARCH_HEALTH_CHECK' => '<strong>Checked AI Search service health</strong>',
'ACP_AISEARCH_TEST_SEARCH' => 'Test Search',
'ACP_AISEARCH_TEST_QUERY' => 'Search query',
'ACP_AISEARCH_TEST_QUERY_EXPLAIN' => 'Enter a query to send directly to the AI service and inspect the raw results.',
'ACP_AISEARCH_RUN_TEST' => 'Run test search',
'ACP_AISEARCH_TEST_RESULTS' => 'Test results (%d hit(s))',
'ACP_AISEARCH_TEST_NO_RESULTS' => 'The AI service returned no results for this query.',
'ACP_AISEARCH_TEST_ERROR' => 'Test search failed: %s',
	'ACP_AISEARCH_TEST_RESULT_SCORE' => 'Score',
	// Index management
	'ACP_AISEARCH_INDEX_MGMT'          => 'Index Management',
	'ACP_AISEARCH_STATS_TOTAL_POSTS'   => 'Total indexable posts in forum',
	'ACP_AISEARCH_STATS_QUEUE_PENDING' => 'Pending in queue (not yet indexed)',
	'ACP_AISEARCH_STATS_QUEUE_DONE'    => 'Posts successfully indexed',
	'ACP_AISEARCH_STATS_QUEUE_FAILED'  => 'Failed (will retry up to 3×)',
	'ACP_AISEARCH_STATS_PROGRESS'      => 'Index progress',
	'ACP_AISEARCH_PURGE_INDEX'         => 'Purge Java index',
	'ACP_AISEARCH_PURGE_INDEX_EXPLAIN' => 'Wipes all documents from the Java service. Old posts will not be found until re-indexed.',
	'ACP_AISEARCH_QUEUE_REINDEX'       => 'Queue all posts for re-indexing',
	'ACP_AISEARCH_QUEUE_REINDEX_EXPLAIN' => 'Adds every visible post to the indexing queue. The cron worker (or "Process queue now") will send them to the Java service.',
	'ACP_AISEARCH_FLUSH_QUEUE'         => 'Process queue now (~10 min)',
	'ACP_AISEARCH_FLUSH_QUEUE_EXPLAIN' => 'Processes as many queued posts as possible in ~10 minutes (500 posts per round-trip to Java). For large forums this can clear far more queue items per click.',
	'ACP_AISEARCH_PURGE_DONE'          => 'Java index purged. All DONE queue rows have been reset to NEW so they will be re-indexed.',
	'ACP_AISEARCH_REINDEX_QUEUED'      => '%d post(s) have been added to the indexing queue.',
	'ACP_AISEARCH_FLUSH_DONE'          => '%1$d post(s) indexed in this pass. %2$d still pending — click again to continue.',
	'ACP_AISEARCH_STOP_QUEUE'          => 'Stop queue now',
	'ACP_AISEARCH_STOP_QUEUE_EXPLAIN'  => 'Cancels the current queue pass by releasing rows stuck in PROCESSING back to NEW.',
	'ACP_AISEARCH_STOP_QUEUE_DONE'     => 'Queue stopped. %d row(s) were moved from PROCESSING back to NEW.',
	'LOG_ACP_AISEARCH_PURGE'           => '<strong>Purged AI Search Java index</strong>',
	'LOG_ACP_AISEARCH_REINDEX'         => '<strong>Queued all posts for AI re-indexing</strong> (%d posts)',
	'LOG_ACP_AISEARCH_STOP_QUEUE'      => '<strong>Stopped AI Search queue pass</strong> (%d rows released)',
]);
