<?php
/**
 *
 * Wereldfietser. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, Phillip Braas, https://www.wereldfietser.nl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace wereldfietser\wereldfietser\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['wereldfietser_wereldfietser_goodbye']);
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_data()
	{
		return [
			['config.add', ['wereldfietser_wereldfietser_goodbye', 0]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_WERELDFIETSER_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_WERELDFIETSER_TITLE',
				[
					'module_basename'	=> '\wereldfietser\wereldfietser\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
