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
  'TERMS_OF_USE_CONTENT'  => 'Welkom op het forum van vereniging De Wereldfietser. Hier ontmoet je mensen die met jou de passie delen voor het reizen per fiets, maar verder op heel veel punten van elkaar verschillen. Om discussies tussen al deze uiteenlopende karakters in goede banen te leiden hanteert de vereniging regels. <br /> 
  <br />Die regels beschrijven hoe en wat je aan het forum kunt bijdragen, wat er wel en niet mag, en hoe we ingrijpen als je regels overtreedt. Lees die regels door voor je begint. <br /><br />Wanneer je alleen maar wilt rondkijken op het forum, dan kun je dat vrijelijk doen; deze regels zijn in dat geval niet van toepassing.<br /><br />
<b>Wanneer je actief wilt deelnemen aan het forum, dan moet je jezelf registreren. </b> <br /><br />Leden van de vereniging de Wereldfietser zijn niet automatisch lid van het forum. Daarvoor is een aparte registratie vereist.<br /><br />Door forumlid te worden geef je aan akkoord te gaan met de geldende regels. Als je niet akkoord gaat met deze regels, wees dan niet actief op dit forum. Al de berichten worden met hun IP-adressen opgeslagen in een database. De vereniging kan er niet verantwoordelijk voor worden gehouden als door een hackpoging gegevens vrijkomen.<br /><br />Wij wensen je een goede tijd op het forum.

  ',
  'AGREE'  => 'Ik ga akkoord met de regels en wil me registreren',
  'NOT_AGREE'  => 'Ik ga niet akkoord met de regels en zie af van registratie',
));