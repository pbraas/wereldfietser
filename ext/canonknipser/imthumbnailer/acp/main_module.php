<?php
/**
 *
 * ImageMagick Thumbnailer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, canonknipser, http://canonknipser.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace canonknipser\imthumbnailer\acp;

/**
 * ImageMagick Thumbnailer ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang('common', false, false, 'canonknipser/imthumbnailer');
		$this->tpl_name = 'acp_ck_it_body';
		$this->page_title = $user->lang('ACP_CK_IT_TITLE');
		add_form_key('ck/it');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('ck/it'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$config->set('ck_it_quality', $request->variable('ck_it_quality', 0));

			trigger_error($user->lang('ACP_CK_IT_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'CK_IT_QUALITY'		=> $config['ck_it_quality'],
		));
	}
}
