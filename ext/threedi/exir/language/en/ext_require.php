<?php
/**
 *
 * Exif Image Rotator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, 3Di, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'EXIR_ERROR_322_VERSION'	=> 'Minimum phpBB version required is 3.2.2 but less than 3.3.0@dev.',
	'EXIR_ERROR_PHP_VERSION'	=> 'PHP version must be equal or greater than 5.5.',
	'EXIR_ERROR_EXIF'			=> 'You need the EXIF PHP Extension to use this extension.',
));
