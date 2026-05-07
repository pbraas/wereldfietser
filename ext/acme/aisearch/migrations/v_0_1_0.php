<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\migrations;
class v_0_1_0 extends \phpbb\db\migration\migration
{
public static function depends_on()
{
return ['\\phpbb\\db\\migration\\data\\v320\\v320'];
}
public function effectively_installed()
{
return isset($this->config['acme_aisearch_enabled']);
}
public function update_schema()
{
return [
'add_tables' => [
$this->table_prefix . 'aisearch_queue' => [
'COLUMNS' => [
'id' => ['UINT', null, 'auto_increment'],
'event_type' => ['VCHAR:32', ''],
'payload_json' => ['TEXT_UNI', ''],
'event_status' => ['VCHAR:16', 'NEW'],
'retry_count' => ['UINT', 0],
'created_at' => ['TIMESTAMP', 0],
],
'PRIMARY_KEY' => 'id',
'KEYS' => [
'status_idx' => ['INDEX', 'event_status'],
],
],
],
];
}
public function revert_schema()
{
return [
'drop_tables' => [
$this->table_prefix . 'aisearch_queue',
],
];
}
public function update_data()
{
return [
['config.add', ['acme_aisearch_enabled', 0]],
['config.add', ['acme_aisearch_base_url', '']],
['config.add', ['acme_aisearch_client_id', 'phpbb-prod']],
['config.add', ['acme_aisearch_shared_secret', '']],
['config.add', ['acme_aisearch_timeout_ms', 3000]],
['config.add', ['acme_aisearch_top_k', 10]],
['module.add', [
'acp',
'ACP_CAT_DOT_MODS',
'ACP_AISEARCH_TITLE',
]],
['module.add', [
'acp',
'ACP_AISEARCH_TITLE',
[
'module_basename' => '\\acme\\aisearch\\acp\\main_module',
'modes' => ['settings'],
],
]],
];
}
}
