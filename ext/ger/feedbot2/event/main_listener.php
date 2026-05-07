<?php

/**
 *
 * Feed Bot 2
 *
 * @copyright (c) 2018 Ger Bruinsma
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedbot2\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{

	static public function getSubscribedEvents()
	{
		return array(
			'core.submit_post_modify_sql_data'  => 'drop_stat',
		);
	}

	/**
	 * Prevent useless edited by ... at ... line
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function drop_stat($event)
	{
		if (isset($event['data']['drop_post_stat']))
        {
            $sql_data = $event['sql_data'];
            unset($sql_data[POSTS_TABLE]['stat']);
            $event['sql_data'] = $sql_data;
        }
	}

}
