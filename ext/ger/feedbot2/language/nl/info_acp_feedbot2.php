<?php
/**
 *
 * Feed bot 2. An extension for the phpBB Forum Software package.
 * [Dutch]
 *
 * @copyright (c) 2018, Ger, https://github.com/GerB
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
	'FB2_ACP_FORUM_ID'					=> 'Feed forum',
	'FB2_ACP_FORUM_ID_EXPLAIN'			=> 'Forum waar nieuwe feed berichten in geplaatst worden.',
	'FB2_ACP_SETTINGS_EXPLAIN'			=> 'Deze module werkt onafhankelijk van Feed Post Bot! <br>Je kunt RSS, ATOM en RDF feeds toevoegen met onderstaand formulier. Begin met het toevoegen van een feed URL. Als je feeds toegevoegd hebt, wordt een tabel met deze parameters getoond:',
	'FB2_ACP_FEEDBOT2_SETTING_SAVED'	=> 'Feed bot 2 instellingen opgeslagen',
	'FB2_ACP_FEEDBOT2_TITLE'			=> 'Feed bot 2',
	'FB2_ACP_FETCHED_ITEMS'             => array(
		1	=> 'Alle feeds verwerkt; %d bericht.',
		2	=> 'Alle feeds verwerkt; %d berichten.',
	),
    'FB2_ACP_NO_FETCHED_ITEMS'          => 'Geen (nieuwe) items om te verwerken',
	'FB2_ADD_FEED'						=> 'Feed toevoegen',
	'FB2_APPEND_LINK'					=> 'Link toevoegen',
	'FB2_APPEND_LINK_EXPLAIN'			=> 'Voeg een link naar de bron van het feedbericht toe.',    
	'FB2_CRON_FREQUENCY'				=> 'Interval voor het automatisch ophalen van feeds (in seconden). 0 om dit uit te schakelen.',
	'FB2_CURDATE'						=> 'Lokale datum/tijd',
	'FB2_CURDATE_EXPLAIN'				=> 'Vink aan om moment van verwerken als berichttijd op te slaan. Laat uit om de publicatiedatum van de feed als berichttijd op te slaan.',
	'FB2_FETCH_ALL_FEEDS'				=> 'Verwerk alle feeds handmatig',
	'FB2_FEED_TYPE'						=> 'Feed type',
	'FB2_FEED_TYPE_EXPLAIN'				=> 'Feeds kunnen van het type ATOM, RDF of RSS zijn. Bij het toevoegen van een feed wordt dit automatisch herkend. Indien er geen berichten gevonden worden kan het helpen om dit aan te passen.',
	'FB2_FEED_URL'						=> 'Feed URL',
	'FB2_FEED_URL_EXPLAIN'				=> 'De URL van de feed, bijv. <code>https://www.phpbb.com/feeds/rss/</code>. Iedere feed URL moet uniek zijn.',
	'FB2_FEED_URL_INVALID'				=> 'Ongeldige feed URL. Dit kan komen doordat deze reeds in de lijst staat of omdat de URL een onjuist format heeft.',
	'FB2_FEEDS'                         => 'Feeds',
	'FB2_LOCKED_EXPLAIN'                => 'Het verwerken van feeds is gestart maar niet voltooid en kan daarom niet nogmaals gestart worden. Indien deze melding blijft bestaan, kun je het proces middels deze knop vrijgeven',
	'FB2_LOG_FEED_ERROR'				=> 'XML fout in feed bron<br />» %s',
	'FB2_LOG_FEED_FETCHED'				=> 'Feed verwerkt<br />» %s',
	'FB2_LOG_UPDATE_NO_GUID'            => 'Feed item heeft geen guid en kan daarom niet betrouwbaar bijgewerkt worden in bericht<br />» %s',
	'FB2_LOG_FEED_TIMEOUT'				=> 'Feed timeout bereikt<br />» %s',
	'FB2_NO_FEEDS'						=> 'Er zijn nog geed feeds ingevoerd.',
	'FB2_READ_MORE'						=> 'Lees meer',
	'FB2_REQUIRE_SIMPLEXML'				=> 'De PHP <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML extensie</a> is niet beschikbaar op de server. De extensie heeft dit nodig om feeds te lezen en kan daarom niet geïnstalleerd worden.',
	'FB2_SOURCE'						=> 'Bron:',
	'FB2_TIMEOUT'						=> 'Time-out',
	'FB2_TIMEOUT_EXPLAIN'				=> 'De tijd die gewacht wordt op antwoord van de feed URL. Indien deze tijd verstreken is zonder antwoord wordt het verzoek afgebroken.',
	'FB2_TYPE_ATOM'						=> 'ATOM',
	'FB2_TYPE_RDF'						=> 'RDF',
	'FB2_TYPE_RSS'						=> 'RSS',
	'FB2_UPDATE'						=> 'Update bestaande',
	'FB2_UPDATE_EXPLAIN'				=> 'Werk reeds geplaatste items in deze feed bij. Let op: dit kan alleen als de feed een unieke identifier (guid) voor iedere bericht heeft.',
	'FB2_USER_ID'						=> 'Feed gebruiker id',
	'FB2_USER_ID_EXPLAIN'				=> 'De id van de gebruiker op wiens naam de nieuwe berichten geplaatst worden.',
));
