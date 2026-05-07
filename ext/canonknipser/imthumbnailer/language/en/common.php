<?php
/**
 *
 * ImageMagick Thumbnailer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, canonknipser, http://canonknipser.com
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

	'ACP_CK_IT'					=> 'Settings',
	'ACP_CK_IT_QUALITY'			=> 'Compression quality',
	'ACP_CK_IT_QUALITY_EXPLAIN'	=> 'With this setting you can control the image compression quality of the generated thumbnail. Higher values give higher quality, but also bigger file sizes. Before phpBB 3.2.4, the value was set to 80 in the pphBB core',
	'ACP_CK_IT_SETTING_SAVED'	=> 'Settings have been saved successfully!',

	'CK_ERR_NEW_INSTANCE'		=> 'Error 0001: creating a new instance of Imagick failed',
	'CK_ERR_SET_FORMAT'			=> 'Error 0002: SetImageFormat failed',
	'CK_ERR_SET_COMPRESSION_Q'	=> 'Error 0003: SetCompressionQuality failed',
	'CK_ERR_RESIZE'				=> 'Error 0004: Resize command failed: ',
	'CK_ERR_WRITE_IMAGE'		=> 'Error 0005: WriteIimage failed ',
	'CK_ERR_CALLING_IMAGICK'	=> 'Error 0006: Calling a Imagick service failed, original message:',

	'CK_WARN_MIMETYPE'			=> 'Warning 0001: unknown Mime-Type: ',

));
