<?php
/**
*
* WF Text Adjustments extension for the phpBB Forum Software package.
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'FAQ'					=> 'Tips',
	'FAQ_EXPLAIN'			=> 'Veelgestelde vragen',
  'BOARDRULES_AGREEMENT' => 'De voorwaarden vind je in',
  'BOARDRULES_TITLE' => 'Regels voor gebruik van dit forum',
  'POST_REPLY' => 'Plaats reactie',
  'QUICK_LINKS' => 'Menu',
  'SEARCH_TOPIC' => 'Doorzoek onderwerp',
));