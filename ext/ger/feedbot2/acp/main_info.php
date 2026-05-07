<?php
/**
 *
 * Feed Bot 2. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedbot2\acp;

/**
 * Feed post bot ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\ger\feedbot2\acp\main_module',
			'title'		=> 'FB2_ACP_FEEDBOT2_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'FB2_ACP_FEEDBOT2_TITLE',
					'auth'	=> 'ext_ger/feedbot2 && acl_a_board',
					'cat'	=> array('FB2_ACP_FEEDBOT2_TITLE')
				),
			),
		);
	}
}
