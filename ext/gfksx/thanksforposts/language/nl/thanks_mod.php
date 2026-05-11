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
	'CLEAR_LIST_THANKS'			=> 'Bedanklijst wissen',
	'CLEAR_LIST_THANKS_CONFIRM'	=> 'Weet je zeker dat je de bedanklijst van de gebruiker wilt wissen?',
	'CLEAR_LIST_THANKS_GIVE'	=> 'De lijst van bedankjes gegeven door de gebruiker is gewist.',
	'CLEAR_LIST_THANKS_POST'	=> 'De lijst van bedankjes in het bericht is gewist.',
	'CLEAR_LIST_THANKS_RECEIVE'	=> 'De lijst van ontvangen bedankjes van de gebruiker is gewist.',

	'DISABLE_REMOVE_THANKS'		=> 'Het verwijderen van bedankjes is uitgeschakeld door de beheerder',

	'GIVEN'						=> 'Duimpjes&nbsp;gegeven',
	'GIVEN_ONE'				=> 'Duimpje&nbsp;gegeven',
	'GIVEN_MANY'			=> 'Duimpjes&nbsp;gegeven',
	'THANKS_GIVEN_ONE'		=> 'Duimpje&nbsp;gegeven',
	'THANKS_GIVEN_MANY'		=> 'Duimpjes&nbsp;gegeven',
	'GLOBAL_INCORRECT_THANKS'	=> 'Je kunt geen bedankje geven voor een globale aankondiging die niet aan een specifiek forum is gekoppeld.',
	'GRATITUDES'				=> 'Bedanklijst',

	'INCORRECT_THANKS'			=> 'Ongeldig bedankje',

	'JUMP_TO_FORUM'				=> 'Ga naar forum',
	'JUMP_TO_TOPIC'				=> 'Ga naar onderwerp',

	'FOR_MESSAGE'				=> ' voor bericht',
	'FURTHER_THANKS'		    => [
		1 => ' en nog één gebruiker',
		2 => ' en nog %d gebruikers',
	],

	'NO_VIEW_USERS_THANKS'		=> 'Je hebt geen toestemming om de bedanklijst te bekijken.',

	'NOTIFICATION_THANKS_GIVE'	=> [
		1 => '%1$s <strong>heeft je bedankt</strong> voor dit bericht:',
		2 => '%1$s <strong>heeft je bedankt</strong> voor dit bericht:',
	],
	'NOTIFICATION_THANKS_REMOVE'=> [
		1 => '<strong>Bedankje verwijderd</strong> van %1$s voor het bericht:',
		2 => '<strong>Bedankjes verwijderd</strong> van %1$s voor het bericht:',
	],
	'NOTIFICATION_TYPE_THANKS_GIVE'		=> 'Iemand heeft je bedankt voor een bericht',
	'NOTIFICATION_TYPE_THANKS_REMOVE'	=> 'Iemand heeft een bedankje verwijderd voor jouw bericht',

	'RECEIVED'					=> 'Duimpje',
	'REMOVE_THANKS'				=> 'Bedankje verwijderen: ',
	'REMOVE_THANKS_CONFIRM'		=> 'Weet je zeker dat je je bedankje wilt verwijderen?',
	'REMOVE_THANKS_SHORT'		=> 'Bedankje verwijderen',
	'REPUT'						=> 'Beoordeling',
	'REPUT_TOPLIST'				=> 'Bedankjes toplijst - %d',
	'RATING_LOGIN_EXPLAIN'		=> 'Je hebt geen toestemming om de toplijst te bekijken.',
	'RATING_NO_VIEW_TOPLIST'	=> 'Je hebt geen toestemming om de toplijst te bekijken.',
	'RATING_VIEW_TOPLIST_NO'	=> 'Toplijst is leeg of uitgeschakeld door de beheerder',
	'RATING_FORUM'				=> 'Forum',
	'RATING_POST'				=> 'Bericht',
	'RATING_TOP_FORUM'			=> 'Top forums',
	'RATING_TOP_POST'			=> 'Top berichten',
	'RATING_TOP_TOPIC'			=> 'Top onderwerpen',
	'RATING_TOPIC'				=> 'Onderwerp',

	'THANK'						=> 'keer',
	'THANK_FROM'				=> 'van',
	'THANK_TEXT_1'				=> 'Deze gebruikers bedankten de auteur ',
	'THANK_TEXT_2'				=> ' voor het bericht: ',
	'THANK_TEXT_2PL'			=> ' voor het bericht (totaal %d):',
	'THANK_POST'				=> 'Bedank de auteur van het bericht: ',
	'THANK_POST_SHORT'			=> 'Bedanken',
	'THANKS'					=> [
		1	=> '%d keer',
		2	=> '%d keer',
	],
	'THANKS_BACK'				=> 'Terug',
	'THANKS_INFO_GIVE'			=> 'Je hebt zojuist het bericht bedankt.',
	'THANKS_INFO_REMOVE'		=> 'Je hebt zojuist je bedankje verwijderd.',
	'THANKS_LIST'				=> 'Lijst bekijken/sluiten',
	'THANKS_PM_MES_GIVE'		=> 'heeft je bedankt voor het bericht',
	'THANKS_PM_MES_REMOVE'		=> 'heeft het bedankje verwijderd voor het bericht',
	'THANKS_PM_SUBJECT_GIVE'	=> 'Bedankje voor het bericht',
	'THANKS_PM_SUBJECT_REMOVE'	=> 'Bedankje verwijderd voor het bericht',
	'THANKS_USER'				=> 'Bedankjeslijst',
	'TOPLIST'					=> 'Berichten toplijst',
]);

