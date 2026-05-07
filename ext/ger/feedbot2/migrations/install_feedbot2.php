<?php
/**
 *
 * Feed Bot 2. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedbot2\migrations;

class install_feedbot2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['feedbot2_cron_last_run']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}
    
	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'feedbot2_posts'	 => array(
					'COLUMNS'    => array(
						'post_id'	 => array('UINT:10', 0),
                        'feed64'     => array('TEXT_UNI', ''),
						'guid'       => array('TEXT_UNI', ''),
					),
                    'PRIMARY_KEY'	=> 'post_id',
				),
			),
		);
	}
	public function update_data()
	{
		return array(
			array('config.add', array('feedbot2_cron_last_run', 0)),
			array('config_text.add', array('ger_feedbot2_current_state', '')),
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'FB2_ACP_FEEDBOT2_TITLE'
			)),
			array('module.add', array(
				'acp',
				'FB2_ACP_FEEDBOT2_TITLE',
				array(
					'module_basename'	=> '\ger\feedbot2\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}


