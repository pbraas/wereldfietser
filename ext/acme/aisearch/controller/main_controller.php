<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\controller;
class main_controller
{
protected $auth;
protected $helper;
protected $language;
protected $request;
protected $template;
protected $user;
protected $search_client;
protected $config;
public function __construct(
\phpbb\auth\auth $auth,
\phpbb\controller\helper $helper,
\phpbb\language\language $language,
\phpbb\request\request_interface $request,
\phpbb\template\template $template,
\phpbb\user $user,
\acme\aisearch\service\search_client $search_client,
\phpbb\config\config $config
)
{
$this->auth = $auth;
$this->helper = $helper;
$this->language = $language;
$this->request = $request;
$this->template = $template;
$this->user = $user;
$this->search_client = $search_client;
$this->config = $config;
}
public function handle()
{
$this->language->add_lang('common', 'acme/aisearch');
$query = trim($this->request->variable('q', '', true));
$results = [];
$error = '';
$enabled = (bool) $this->config['acme_aisearch_enabled'];
if ($query !== '' && $enabled)
{
$allowed_forum_ids = [];
$forum_acl = $this->auth->acl_getf('f_read', true);
foreach ($forum_acl as $forum_id => $can_read)
{
if ($can_read)
{
$allowed_forum_ids[] = (int) $forum_id;
}
}
try
{
$results = $this->search_client->search($query, (int) $this->user->data['user_id'], $allowed_forum_ids);
}
catch (\RuntimeException $e)
{
$error = $e->getMessage();
}
}
else if ($query !== '' && !$enabled)
{
$error = $this->language->lang('AISEARCH_DISABLED');
}
$this->template->assign_vars([
'AISEARCH_QUERY' => $query,
'AISEARCH_ERROR' => $error,
'S_AISEARCH_ENABLED' => $enabled,
'U_AISEARCH' => $this->helper->route('acme_aisearch_main'),
]);
$this->template->assign_block_vars_array('result', $results);
return $this->helper->render('aisearch_results.html', $this->language->lang('AISEARCH_TITLE'));
}
}
