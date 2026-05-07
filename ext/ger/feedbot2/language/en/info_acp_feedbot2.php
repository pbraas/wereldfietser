<?php
/**
 *
 * Feed bot 2. An extension for the phpBB Forum Software package.
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
	'FB2_ACP_FORUM_ID_EXPLAIN'			=> 'The forum to post the new feed items in.',
	'FB2_ACP_SETTINGS_EXPLAIN'			=> 'This module is independent of Feed Post Bot! <br>You can add RSS, ATOM or RDF feeds using the form below. Start with posting a feed URL. When you have entered feeds, you find a table with these parameters:',
	'FB2_ACP_FEEDBOT2_SETTING_SAVED'	=> 'Feed bot 2 settings saved',
	'FB2_ACP_FEEDBOT2_TITLE'			=> 'Feed bot 2',
    'FB2_ACP_FETCHED_ITEMS'             => array(
		1	=> 'All feeds fetched; %d post processed',
		2	=> 'All feeds fetched: %d posts processed',
	),
    'FB2_ACP_NO_FETCHED_ITEMS'          => 'No (new) items to fetch',
	'FB2_ADD_FEED'						=> 'Add feed',
	'FB2_APPEND_LINK'					=> 'Append link',
	'FB2_APPEND_LINK_EXPLAIN'			=> 'Append a link to the source of the feed item',
    'FB2_CRON_FREQUENCY'				=> 'Interval for automatic processing feeds (seconds). 0 to disable automated fetching.',
	'FB2_CURDATE'						=> 'Local date/time',
	'FB2_CURDATE_EXPLAIN'				=> 'Check to use the feed fetch time as post time. Uncheck to use the feed PubDate as post time.',
	'FB2_FETCH_ALL_FEEDS'				=> 'Fetch all feeds manually',
	'FB2_FEED_TYPE'						=> 'Feed type',
	'FB2_FEED_TYPE_EXPLAIN'				=> 'Feeds can be ATOM, RDF or RSS. Upon entering a feed for the first time, the type is autodetected. If the feed doesn’t return any items to post, try to change this.',
	'FB2_FEED_URL'						=> 'Feed URL',
	'FB2_FEED_URL_EXPLAIN'				=> 'The URL to the actual feed, e.g. <code>https://www.phpbb.com/feeds/rss/</code>. Each feed URL should be unique',
	'FB2_FEED_URL_INVALID'				=> 'Invalid feed URL. This may be the result of a duplicate in your feed list or simply an URL that does not meet the specifications',
    'FB2_FEEDS'                         => 'Feeds',
    'FB2_LOCKED_EXPLAIN'                => 'Feed processing has started but not completed and therefore cannot start again. If this persists you can release the process by clicking this button',
	'FB2_LOG_FEED_ERROR'				=> 'XML error in feed source<br />» %s',
	'FB2_LOG_FEED_FETCHED'				=> 'Feed fetched<br />» %s',
	'FB2_LOG_UPDATE_NO_GUID'            => 'Feed item has no guid and cannot update post reliably<br />» %s',    
	'FB2_LOG_FEED_TIMEOUT'				=> 'Feed timeout reached<br />» %s',
	'FB2_NO_FEEDS'						=> 'There are no feeds yet.',
	'FB2_READ_MORE'						=> 'Read more',
	'FB2_REQUIRE_SIMPLEXML'				=> 'The PHP <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML extension</a> is not available on the server. The extension needs this to read the feeds and therefore cannot be installed.',
	'FB2_SOURCE'						=> 'Source:',
	'FB2_TIMEOUT'						=> 'Timeout',
	'FB2_TIMEOUT_EXPLAIN'				=> 'Timeout for requesting the Feed URL. If this time has passed without retrieving the feed content, the request is cancelled.',
    'FB2_TYPE_ATOM'						=> 'ATOM',
	'FB2_TYPE_RDF'						=> 'RDF',
	'FB2_TYPE_RSS'						=> 'RSS',
	'FB2_UPDATE'						=> 'Update existing',
	'FB2_UPDATE_EXPLAIN'				=> 'Tracks posted items from this feed and updates the previous posted item. This only works if the feed has a unique identiefer (guid) for each item.',
    'FB2_USER_ID'						=> 'Feed user id',
	'FB2_USER_ID_EXPLAIN'				=> 'The id of the user that will be used to post new items.',
));
