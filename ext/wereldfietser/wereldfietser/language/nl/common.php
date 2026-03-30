<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'WERELDFIETSER_ID'                              => 'Wereldfietser ID',
    'PHP_CURL_NOT_INSTALLED'                        => 'De cURL-extensie voor PHP is vereist voor de API-authenticatieprovider. Schakel deze in uw php.ini-bestand in en herstart uw webserver.',
    'EXTERNAL_AUTH_INVALID_RESPONSE'                => 'De authenticatieserver gaf een ongeldig antwoord. Neem contact op met de beheerder.',
    'LOGIN_ERROR_EMAIL_NOT_ALLOWED'                 => 'Inloggen met een e-mailadres is niet toegestaan. Gebruik uw gebruikersnaam. Als uw gebruikersnaam een e-mailadres is, wijzig deze dan eerst op <a href="https://wereldfietser.nl">wereldfietser.nl</a>.',
    'LOGIN_ERROR_USERNAME_TAKEN_BY_OTHER_ACCOUNT'   => 'U probeert in te loggen met een account waarvan de gebruikersnaam al in gebruik is door een ander account op dit forum. Neem contact op met de beheerder om dit op te lossen.',
    'WERELDFIETSER_LINK_ACCOUNT'                    => 'Je hebt dezelfde gebruikersnaam als iemand op het forum. Wil je dit aan elkaar linken? Zo ja volg dan de instructies op de volgende pagina. Zo nee, wijzig je gebruikersnaam op wereldfietser.nl naar een gebruikersnaam die nog niet bestaat op het forum.',
    'WERELDFIETSER_ACCOUNT_CONFLICT'                => 'Er is een conflict met uw Wereldfietser-account. De Wereldfietser ID die aan uw login is gekoppeld, komt niet overeen met die op het forum. Neem contact op met de beheerder.',
    'WERELDFIETSER_NOT_ACTIVE_MEMBER'               => 'U bent geen actief lid van De Wereldfietser. Toegang tot dit forum is voorbehouden aan actieve leden.',
    'MERGE_ACCOUNTS_TITLE'                          => 'Wereldfietser Account Koppelen',
    'MERGE_ACCOUNTS_EXPLAIN'                        => 'Om uw Wereldfietser-account aan uw forumaccount te koppelen, voert u hieronder uw Wereldfietser Lidnummer en uw forumwachtwoord in om te bevestigen dat u eigenaar bent van beide accounts.',
    'YOUR_PASSWORD'                                 => 'Uw Forum Wachtwoord',
    'WERELDFIETSER_MERGE_ID_MISMATCH'               => 'De ingevoerde Wereldfietser ID komt niet overeen met die van uw inlogpoging. Ga terug en probeer het opnieuw.',
    'WERELDFIETSER_MERGE_INVALID_PASSWORD'          => 'Het ingevoerde wachtwoord voor uw forumaccount is onjuist.',
));
