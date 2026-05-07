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

	'ACP_CK_IT'					=> 'Einstellungen',
	'ACP_CK_IT_QUALITY'			=> 'Komprimierungsqualität',
	'ACP_CK_IT_QUALITY_EXPLAIN'	=> 'Mit dieser Einstellung können Sie die Komprimierungsqualität für die generierten Vorschaubilder einstellen. Höhere Werte ergeben eine höhere Qualität, aber auch eine größere Datei. Vor phpBB 3.2.4 wurde dieser Wert in den Kerndateien auf 80 gesetzt.',
	'ACP_CK_IT_SETTING_SAVED'	=> 'Einstellungen wurden erfolgreich gespeichert',

	'CK_ERR_NEW_INSTANCE'		=> 'Fehler 0001: Das Erzeugen einer neuen Instanz von Imagick ist fehlgeschlagen',
	'CK_ERR_SET_FORMAT'			=> 'Fehler 0002: Der Befehl SetImageFormat ist fehlgeschlagen',
	'CK_ERR_SET_COMPRESSION_Q'	=> 'Fehler 0003: Der Befehl SetCompressionQuality ist fehlgeschlagen',
	'CK_ERR_RESIZE'				=> 'Fehler 0004: Der Befehl Resize ist fehlgeschlagen: ',
	'CK_ERR_WRITE_IMAGE'		=> 'Fehler 0005: Der Befehl WriteImage ist fehlgeschlagen ',
	'CK_ERR_CALLING_IMAGICK'	=> 'Fehler 0006: Der Aufruf eines Imagick Services ist fehlgeschlagen, Original-Nachricht:',

	'CK_WARN_MIMETYPE'			=> 'Warnung 0001: unbekannter Mime-Type: ',

));
