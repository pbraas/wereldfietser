<?php
/**
 *
 * Exif Image Rotator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, 3Di, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\exir\migrations;

class m1_config extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		/* If does NOT exist go ahead */
		return isset($this->config['threedi_exir_percent']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_data()
	{
		return array(
			/* (INT) export img percent */
			array('config.add', array('threedi_exir_percent', 95)),
		);
	}
}
