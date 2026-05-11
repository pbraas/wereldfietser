<?php
/**
 *
 * Thanks For Posts.
 * Adds the ability to thank the author and to use per posts/topics/forum rating system based on the count of thanks.
 * An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, rxu, https://www.phpbbguru.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ACL_F_THANKS' 						=> 'Mag berichten bedanken',
	'ACL_M_THANKS' 						=> 'Mag de bedankjeslijst wissen',
	'ACL_U_VIEWTHANKS' 					=> 'Mag de volledige bedankjeslijst bekijken',
	'ACL_U_VIEWTOPLIST'					=> 'Mag de toplijst bekijken',
]);

