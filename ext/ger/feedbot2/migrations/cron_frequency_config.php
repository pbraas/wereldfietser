<?php
/**
 *
 * Move cron frequency to config item instead of hardcoded
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedbot2\migrations;

class cron_frequency_config extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['feedbot2_cron_frequency']);
	}

	static public function depends_on()
	{
		return array('\ger\feedbot2\migrations\install_feedbot2');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('feedbot2_cron_frequency', 1800)),
		);
	}
}


