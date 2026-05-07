<?php
/**
 *
 * AI Search.
 *
 */
namespace acme\aisearch\acp;
class main_module
{
public $page_title;
public $tpl_name;
public $u_action;
public function main($id, $mode)
{
global $phpbb_container;
/** @var \acme\aisearch\controller\acp_controller $acp_controller */
$acp_controller = $phpbb_container->get('acme.aisearch.controller.acp');
$this->tpl_name = 'acp_aisearch_body';
$this->page_title = 'ACP_AISEARCH_TITLE';
$acp_controller->set_page_url($this->u_action);
$acp_controller->display_options();
}
}
