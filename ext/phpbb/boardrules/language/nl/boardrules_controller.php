<?php
/**
*
* Board Rules extension for the phpBB Forum Software package.
* Dutch translation by Dutch Translators (https://github.com/dutch-translators)
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
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
	'BOARDRULES_HEADER'			=> 'Forumregels',
	'BOARDRULES_EXPLAIN'		=> 'Op het forum van vereniging De Wereldfietser ontmoet je mensen die met jou de passie delen voor het reizen per fiets, maar verder op heel veel punten van elkaar verschillen. Om de discussies tussen al deze uiteenlopende karakters in goede banen te leiden, hanteert de vereniging regels. Dit document beschrijft wat je aan het forum kunt bijdragen, wat er wel en niet mag, en hoe we ingrijpen als je regels overtreedt.<br><br> <b>Begrippen:</b> <br><ul><li><b>Forumlid:</b> Een forumlid heeft zich geregistreerd op het forum; de forumregels zijn gericht op forumleden</li><li><b>Forumgast:</b> Een gast heeft zich niet geregistreerd op het forum</li></ul><br><br><b>Registratie en profiel</b><br>Om actief te zijn als forumlid op het forum moet je jezelf registreren. Vermeld in je profiel een e-mailadres waarop we je kunnen benaderen. Eenmalige e-mailadressen zijn niet toegestaan. Neem als gebruikersnaam liever je echte naam en geen pseudoniem of je e-mailadres. Gebruik geen animatie als profielafbeelding. Beperk je onderschrift bij berichten tot één regel. Overweeg als je je registreert, om jezelf te introduceren bijvoorbeeld in het topic "post eens een foto van je vakantiefiets".<br><br>Door forumlid te worden en berichten te plaatsen geef je automatisch aan akkoord te gaan met de geldende regels. Als je niet akkoord gaat met deze regels, wees dan niet actief op dit forum. Berichten en gedragingen die ingaan tegen de geldende regels, kunnen ertoe leiden dat je met onmiddellijke ingang tijdelijk of zelfs permanent wordt verbannen van het forum. De vereniging heeft het recht om tussentijds regels aan te passen. Hiervan doet zij dan direct melding op het forum.<br><br>Een forumgast bezoekt en raadpleegt het forum. Registratie is niet nodig en deze regels zijn dan ook niet van toepassing op een forumgast.<br><br>Leden van de vereniging de Wereldfietser zijn niet automatisch ook lid van het forum. Een aparte registratie is vereist.<br><br>',
	'BOARDRULES_CATEGORIES'		=> 'Regelsecties',
	'BOARDRULES_CATEGORY_ANCHOR'=> 'sectie-%s',
	'BOARDRULES_RULE_ANCHOR'	=> 'regel-%s',
));
