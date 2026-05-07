<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class main_listener implements EventSubscriberInterface
{
protected $controller_helper;
protected $db;
protected $template;
protected $queue_table;
public function __construct(
\phpbb\controller\helper $controller_helper,
\phpbb\db\driver\driver_interface $db,
\phpbb\language\language $language,
\phpbb\template\template $template,
$queue_table
)
{
$this->controller_helper = $controller_helper;
$this->db = $db;
$this->template = $template;
$this->queue_table = $queue_table;
}
public static function getSubscribedEvents()
{
return [
'core.user_setup' => 'load_language_on_setup',
'core.page_header_after' => 'add_header_link',
'core.submit_post_end' => 'queue_post_upsert',
'core.delete_posts_in_transaction' => 'queue_post_delete',
];
}
public function load_language_on_setup($event)
{
$lang_set_ext = $event['lang_set_ext'];
$lang_set_ext[] = [
'ext_name' => 'acme/aisearch',
'lang_set' => 'common',
];
$event['lang_set_ext'] = $lang_set_ext;
}
public function add_header_link($event)
{
$this->template->assign_vars([
'U_AISEARCH' => $this->controller_helper->route('acme_aisearch_main'),
]);
}
public function queue_post_upsert($event)
{
$data = $event['data'];
$payload = [
'post_id' => (int) $data['post_id'],
'topic_id' => (int) $data['topic_id'],
'forum_id' => (int) $data['forum_id'],
'user_id' => (int) $data['poster_id'],
];
$this->enqueue_event('UPSERT', $payload);
}
public function queue_post_delete($event)
{
$post_ids = array_map('intval', (array) $event['post_ids']);
$this->enqueue_event('DELETE', ['post_ids' => $post_ids]);
}
protected function enqueue_event($event_type, array $payload)
{
$sql_ary = [
'event_type' => (string) $event_type,
'payload_json' => json_encode($payload),
'event_status' => 'NEW',
'retry_count' => 0,
'created_at' => time(),
];
$sql = 'INSERT INTO ' . $this->queue_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
$this->db->sql_query($sql);
}
}
