<?php
/**
 *
 * Wereldfietser. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, Phillip Braas, https://www.wereldfietser.nl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace wereldfietser\wereldfietser\acp;

/**
 * Wereldfietser ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\wereldfietser\wereldfietser\acp\main_module',
			'title'		=> 'ACP_WERELDFIETSER_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_WERELDFIETSER',
					'auth'	=> 'ext_wereldfietser/wereldfietser && acl_a_board',
					'cat'	=> ['ACP_WERELDFIETSER_TITLE'],
				],
			],
		];
	}
}
