<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'WERELDFIETSER_ID'                              => 'Wereldfietser ID',
    'PHP_CURL_NOT_INSTALLED'                        => 'The cURL extension for PHP is required for the API authentication provider to work. Please enable it in your php.ini file and restart your web server.',
    'EXTERNAL_AUTH_INVALID_RESPONSE'                => 'The authentication server returned an invalid response. Please contact the board administrator.',
    'LOGIN_ERROR_EMAIL_NOT_ALLOWED'                 => 'Logging in with an email address is not allowed. Please use your username. If your username is an email address, please change it on <a href="https://wereldfietser.nl">wereldfietser.nl</a> first.',
    'LOGIN_ERROR_USERNAME_TAKEN_BY_OTHER_ACCOUNT'   => 'You are trying to log in with an account whose username is already in use by another account on this forum. Please contact the administrator to resolve this issue.',
    'WERELDFIETSER_LINK_ACCOUNT'                    => 'You have the same username as someone on the forum. Do you want to link these accounts? If yes, follow the instructions on the next page. If no, change your username on wereldfietser.nl to a username that does not yet exist on the forum.',
    'WERELDFIETSER_ACCOUNT_CONFLICT'                => 'There is a conflict with your Wereldfietser account. The Wereldfietser ID associated with your login does not match the one stored on the forum. Please contact the administrator.',
    'WERELDFIETSER_NOT_ACTIVE_MEMBER'               => 'You are not an active member of De Wereldfietser. Access to this forum is restricted to active members.',
    'MERGE_ACCOUNTS_TITLE'                          => 'Link Wereldfietser Account',
    'MERGE_ACCOUNTS_EXPLAIN'                        => 'To link your Wereldfietser account with your forum account, please enter your Wereldfietser Member ID and your forum password below to confirm ownership of both accounts.',
    'YOUR_PASSWORD'                                 => 'Your Forum Password',
    'WERELDFIETSER_MERGE_ID_MISMATCH'               => 'The Wereldfietser ID you entered does not match the one from your login attempt. Please go back and try again.',
    'WERELDFIETSER_MERGE_INVALID_PASSWORD'          => 'The password you entered for your forum account is incorrect.',
));
