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
	'ACP_DELTHANKS'						=> 'Verwijderde bedankjes',
	'ACP_POSTS'							=> 'Totaal berichten',
	'ACP_POSTSEND'						=> 'Resterende berichten met bedankjes',
	'ACP_POSTSTHANKS'					=> 'Totaal berichten met bedankjes',
	'ACP_THANKS'						=> 'Bedankt voor berichten',
	'ACP_THANKS_MOD_VER'				=> 'Extensieversie: ',
	'ACP_THANKS_TRUNCATE'				=> 'De lijst met bedankjes wissen',
	'ACP_ALLTHANKS'						=> 'Bedankjes verwerkt',
	'ACP_THANKSEND'						=> 'Resterende te verwerken bedankjes',
	'ACP_THANKS_REPUT'					=> 'Beoordelingsopties',
	'ACP_THANKS_REPUT_SETTINGS'			=> 'Beoordelingsopties',
	'ACP_THANKS_REPUT_SETTINGS_EXPLAIN'	=> 'Stel hier de standaardinstellingen in voor de beoordeling van berichten, onderwerpen en forums, op basis van het bedankjessysteem. <br /> Het onderwerp (bericht, topic of forum) met het grootste totale aantal bedankjes krijgt een beoordeling van 100%.',
	'ACP_THANKS_SETTINGS'				=> 'Bedankjes-instellingen',
	'ACP_THANKS_SETTINGS_EXPLAIN'		=> 'De standaardinstellingen voor Bedankt voor berichten kunnen hier worden gewijzigd.',
	'ACP_THANKS_REFRESH'				=> 'Tellers bijwerken',
	'ACP_UPDATETHANKS'					=> 'Bijgewerkte bedankjes',
	'ACP_USERSEND'						=> 'Resterende gebruikers die bedankten',
	'ACP_USERSTHANKS'					=> 'Totaal gebruikers die bedankten',

	'GRAPHIC_BLOCK_BACK'				=> 'ext/gfksx/thanksforposts/images/rating/reput_block_back.gif',
	'GRAPHIC_BLOCK_RED'					=> 'ext/gfksx/thanksforposts/images/rating/reput_block_red.gif',
	'GRAPHIC_DEFAULT'					=> 'Afbeeldingen',
	'GRAPHIC_OPTIONS'					=> 'Grafische opties',
	'GRAPHIC_STAR_BACK'					=> 'ext/gfksx/thanksforposts/images/rating/reput_star_back.gif',
	'GRAPHIC_STAR_BLUE'					=> 'ext/gfksx/thanksforposts/images/rating/reput_star_blue.gif',
	'GRAPHIC_STAR_GOLD'					=> 'ext/gfksx/thanksforposts/images/rating/reput_star_gold.gif',

	'IMG_THANKPOSTS'					=> 'Bedanken voor het bericht',
	'IMG_REMOVETHANKS'					=> 'Bedankje annuleren',

	'LOG_CONFIG_THANKS'					=> 'Configuratie van de extensie Bedankt voor berichten bijgewerkt',

	'REFRESH'							=> 'Vernieuwen',
	'REMOVE_THANKS'						=> 'Bedankje verwijderen',
	'REMOVE_THANKS_EXPLAIN'				=> 'Gebruikers kunnen bedankjes verwijderen als deze optie is ingeschakeld.',

	'STEPR'								=> ' - uitgevoerd, stap %s',

	'THANKS_AJAX_ENABLE'				=> 'Ajax inschakelen',
	'THANKS_AJAX_ENABLE_EXPLAIN'		=> 'Indien ingeschakeld, worden bedankjes geven en verwijderen uitgevoerd zonder pagina opnieuw te laden.',
	'THANKS_COUNTERS_VIEW'				=> 'Bedankjestellers',
	'THANKS_COUNTERS_VIEW_EXPLAIN'		=> 'Indien ingeschakeld, toont het informatieblok van de auteur het aantal gegeven/ontvangen bedankjes.',
	'THANKS_FORUM_REPUT_VIEW'			=> 'Forumbeoordelingen tonen',
	'THANKS_GLOBAL_POST'				=> 'Bedankjes in globale aankondigingen',
	'THANKS_GLOBAL_POST_EXPLAIN'		=> 'Indien ingeschakeld, zijn bedankjes in globale aankondigingen toegestaan.',
	'THANKS_FORUM_REPUT_VIEW_EXPLAIN'	=> 'Indien ingeschakeld, wordt de forumbeoordeling weergegeven in de forumlijst.',
	'THANKS_INFO_PAGE'					=> 'Informatieve berichten',
	'THANKS_INFO_PAGE_EXPLAIN'			=> 'Indien ingeschakeld, worden informatieve berichten weergegeven na het bedanken/verwijderen van een bedankje voor een bericht.',
	'THANKS_NOTICE_ON'					=> 'Meldingen beschikbaar',
	'THANKS_NOTICE_ON_EXPLAIN'			=> 'Indien ingeschakeld, zijn meldingen beschikbaar en kan de gebruiker de melding instellen via zijn profiel.',
	'THANKS_NUMBER'						=> 'Aantal bedankjes uit de lijst weergegeven in profiel',
	'THANKS_NUMBER_EXPLAIN'				=> 'Maximaal aantal bedankjes dat wordt weergegeven bij het bekijken van een profiel. <br /> <strong> Houd er rekening mee dat vertraging merkbaar wordt als deze waarde hoger dan 250 wordt ingesteld. </strong>',
	'THANKS_NUMBER_DIGITS'				=> 'Het aantal decimalen voor de beoordeling',
	'THANKS_NUMBER_DIGITS_EXPLAIN'		=> 'Geef het aantal decimalen op voor de beoordelingswaarde.',
	'THANKS_NUMBER_ROW_REPUT'			=> 'Het aantal rijen in de toplijst voor beoordeling',
	'THANKS_NUMBER_ROW_REPUT_EXPLAIN'	=> 'Geef het aantal rijen op dat wordt weergegeven in de toplijst voor berichten-, topic- en forumbeoordelingen.',
	'THANKS_NUMBER_POST'				=> 'Aantal bedankjes weergegeven in een bericht',
	'THANKS_NUMBER_POST_EXPLAIN'		=> 'Maximaal aantal bedankjes dat wordt weergegeven bij het bekijken van een bericht. <br /> <strong> Houd er rekening mee dat vertraging merkbaar wordt als deze waarde hoger dan 250 wordt ingesteld. </strong>',
	'THANKS_ONLY_FIRST_POST'			=> 'Alleen voor het eerste bericht in het topic',
	'THANKS_ONLY_FIRST_POST_EXPLAIN'	=> 'Indien ingeschakeld, kunnen gebruikers alleen bedanken voor het eerste bericht in het topic.',
	'THANKS_POST_REPUT_VIEW'			=> 'Berichtbeoordeling tonen',
	'THANKS_POST_REPUT_VIEW_EXPLAIN'	=> 'Indien ingeschakeld, wordt de berichtbeoordeling weergegeven bij het bekijken van een topic.',
	'THANKS_POSTLIST_VIEW'				=> 'Bedankjeslijst in bericht weergeven',
	'THANKS_POSTLIST_VIEW_EXPLAIN'		=> 'Indien ingeschakeld, wordt een lijst weergegeven van gebruikers die de auteur voor het bericht hebben bedankt. <br/> Let op: deze optie is alleen effectief als de beheerder de toestemming heeft ingeschakeld om bedankjes te geven in dat forum.',
	'THANKS_PROFILELIST_VIEW'			=> 'Bedankjeslijst in profiel weergeven',
	'THANKS_PROFILELIST_VIEW_EXPLAIN'	=> 'Indien ingeschakeld, wordt een volledige lijst van bedankjes weergegeven, inclusief het aantal bedankjes en voor welke berichten een gebruiker is bedankt.',
	'THANKS_REFRESH'					=> 'Bedankjestellers bijwerken',
	'THANKS_REFRESH_EXPLAIN'			=> 'Hier kunt u de bedankjestellers bijwerken na een massale verwijdering van berichten/topics/gebruikers, splitsen/samenvoegen van topics, instellen/verwijderen van globale aankondiging, in-/uitschakelen van de optie \'Alleen voor het eerste bericht in het topic\', wijzigen van berichteigenaren enzovoort. Dit kan enige tijd duren.<br /><strong>Belangrijk: voor correct functioneren heeft de functie tellers vernieuwen MySQL versie 4.1 of hoger nodig!<br />Let op!<br /> - Vernieuwen verwijdert alle bedankjes voor gastberichten!<br /> - Vernieuwen verwijdert alle bedankjes voor globale aankondigingen als de optie \'Bedankjes in globale aankondigingen\' UIT staat!<br /> - Vernieuwen verwijdert alle bedankjes voor alle berichten behalve het eerste bericht in het topic als de optie \'Alleen voor het eerste bericht in het topic\' AAN staat!</strong>',
	'THANKS_REFRESH_MSG'				=> 'Dit kan een paar minuten duren. Alle onjuiste bedankjesvermeldingen worden verwijderd! <br /> De actie is niet omkeerbaar!',
	'THANKS_REFRESHED_MSG'				=> 'Tellers bijgewerkt',
	'THANKS_REPUT_GRAPHIC'				=> 'Grafische weergave van de beoordeling',
	'THANKS_REPUT_GRAPHIC_EXPLAIN'		=> 'Indien ingeschakeld, wordt de beoordelingswaarde grafisch weergegeven met behulp van onderstaande afbeeldingen.',
	'THANKS_REPUT_HEIGHT'				=> 'Grafische hoogte',
	'THANKS_REPUT_HEIGHT_EXPLAIN'		=> 'Geef de hoogte van de schuifregelaar voor de beoordeling op in pixels. <br /> <strong> Let op! Voor een correcte weergave moet de hoogte gelijk zijn aan de hoogte van de volgende afbeelding! </strong>',
	'THANKS_REPUT_IMAGE'				=> 'De hoofdafbeelding voor de schuifregelaar',
	'THANKS_REPUT_IMAGE_DEFAULT'		=> '<strong>Grafische voorvertoning</strong>',
	'THANKS_REPUT_IMAGE_DEFAULT_EXPLAIN' => 'De afbeelding zelf en het pad naar de afbeelding zijn hier te zien. Afbeeldingsgrootte is 15x15 pixels. <br /> U kunt uw eigen afbeeldingen tekenen voor de voor- en achtergrond. <strong>De hoogte en breedte van de afbeelding moeten gelijk zijn om de grafische schaal correct op te bouwen.</strong>',
	'THANKS_REPUT_IMAGE_EXPLAIN'		=> 'Het pad - relatief aan de rootmap van phpBB - naar de afbeelding voor de grafische schaal.',
	'THANKS_REPUT_IMAGE_NOEXIST'		=> 'De hoofdafbeelding voor de grafische schaal niet gevonden.',
	'THANKS_REPUT_IMAGE_BACK'			=> 'De achtergrondafbeelding voor de schuifregelaar',
	'THANKS_REPUT_IMAGE_BACK_EXPLAIN'	=> 'Het pad - relatief aan de rootmap van phpBB - naar de achtergrondafbeelding voor de grafische schaal.',
	'THANKS_REPUT_IMAGE_BACK_NOEXIST'	=> 'De achtergrondafbeelding voor de grafische schaal niet gevonden.',
	'THANKS_REPUT_LEVEL'				=> 'Aantal afbeeldingen in een grafische schaal',
	'THANKS_REPUT_LEVEL_EXPLAIN'		=> 'Het maximale aantal afbeeldingen overeenkomend met 100% van de beoordelingsschaalwaarde in de grafiek.',
	'THANKS_TIME_VIEW'					=> 'Bedankjestijd',
	'THANKS_TIME_VIEW_EXPLAIN'			=> 'Indien ingeschakeld, wordt de bedankjestijd weergegeven in het bericht.',
	'THANKS_TOP_NUMBER'					=> 'Aantal gebruikers in toplijst',
	'THANKS_TOP_NUMBER_EXPLAIN'			=> 'Geef het aantal gebruikers op dat wordt weergegeven in de toplijst op de indexpagina. 0 = toplijst uitschakelen.',
	'THANKS_TOPIC_REPUT_VIEW'			=> 'Topicbeoordeling tonen',
	'THANKS_TOPIC_REPUT_VIEW_EXPLAIN'	=> 'Indien ingeschakeld, wordt de topicbeoordeling weergegeven bij het bekijken van een forum.',
	'TRUNCATE'							=> 'Wissen',
	'TRUNCATE_THANKS'					=> 'De lijst met bedankjes wissen',
	'TRUNCATE_THANKS_EXPLAIN'			=> 'Deze procedure wist volledig alle bedankjestellers (verwijdert alle gegeven bedankjes). <br /> Deze actie is niet omkeerbaar!',
	'TRUNCATE_THANKS_MSG'				=> 'Bedankjestellers gewist.',
	'REFRESH_THANKS_CONFIRM'			=> 'Weet je zeker dat je de bedankjestellers wilt vernieuwen?',
	'TRUNCATE_THANKS_CONFIRM'			=> 'Weet je zeker dat je de bedankjestellers wilt wissen?',
	'TRUNCATE_NO_THANKS'				=> 'Bewerking geannuleerd',
	'ALLOW_THANKS_PM_ON'				=> 'Stuur mij een PB als iemand mijn bericht bedankt',
	'ALLOW_THANKS_EMAIL_ON'				=> 'Stuur mij een e-mail als iemand mijn bericht bedankt',
]);

