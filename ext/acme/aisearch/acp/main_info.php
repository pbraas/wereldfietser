<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\acp;
class main_info
{
public function module()
{
return [
'filename' => '\\acme\\aisearch\\acp\\main_module',
'title' => 'ACP_AISEARCH_TITLE',
'modes' => [
'settings' => [
'title' => 'ACP_AISEARCH',
'auth' => 'ext_acme/aisearch && acl_a_board',
'cat' => ['ACP_AISEARCH_TITLE'],
],
],
];
}
}
