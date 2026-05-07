<?php
/**
 *
 * Add config item for locking fetch process
 *
 * @copyright (c) 2018, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedbot2\migrations;

class config_lock extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['feedbot2_locked']);
	}

	static public function depends_on()
	{
		return array('\ger\feedbot2\migrations\install_feedbot2');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('feedbot2_locked', 0, true)),
		);
	}
}