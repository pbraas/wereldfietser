<?php
/**
 *
 * Wereldfietser. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, Phillip Braas, https://www.wereldfietser.nl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace wereldfietser\wereldfietser\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Wereldfietser Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \wereldfietser\wereldfietser\auth\provider\api */
	protected $auth_provider;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/**
	 * Map phpBB core events to the listener methods that should handle those events
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header'							=> 'add_page_header_link',
			'core.viewonline_overwrite_location'		=> 'viewonline_page',
			'core.user_change_name'						=> 'on_user_change_name',
			'core.ucp_profile_reg_details_validate'	=> 'prevent_password_and_username_change_for_linked_accounts',
		];
	}

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\db\driver\driver_interface	$db		Database connection
	 * @param \phpbb\user	$user		User object
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param string	$php_ext	phpEx
	 * @param \wereldfietser\wereldfietser\auth\provider\api	$auth_provider	Auth provider
	 * @param string	$phpbb_root_path	phpBB root path
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\template\template $template, $php_ext, \wereldfietser\wereldfietser\auth\provider\api $auth_provider, $phpbb_root_path)
	{
		$this->language = $language;
		$this->db       = $db;
		$this->user     = $user;
		$this->helper   = $helper;
		$this->template = $template;
		$this->php_ext  = $php_ext;
		$this->auth_provider = $auth_provider;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'wereldfietser/wereldfietser',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add a link to the controller in the forum navbar
	 */
	public function add_page_header_link()
	{
		$is_logged_in = ($this->user->data['user_id'] != ANONYMOUS);
		$is_wf = $is_logged_in ? $this->is_wf_member((int) $this->user->data['user_id']) : false;

		$this->template->assign_vars([
			'U_WERELDFIETSER_PAGE'		=> $this->helper->route('wereldfietser_wereldfietser_merge_controller'),
			'U_WF_INSTRUCTIONS'			=> generate_board_url() . '/koppeling-instructie.html',
			'S_WF_MEMBER_LOGIN'			=> ($is_logged_in && !$is_wf) ? true : false,
		]);
	}

	/**
	 * Check if the current user belongs to the Wereldfietser member group.
	 *
	 * @param int $user_id
	 * @return bool
	 */
	private function is_wf_member($user_id)
	{
		$sql = 'SELECT 1
			FROM ' . USER_GROUP_TABLE . ' ug
			INNER JOIN ' . GROUPS_TABLE . " g ON g.group_id = ug.group_id
			WHERE ug.user_id = " . (int) $user_id . "
				AND g.group_name = '" . $this->db->sql_escape('Wereldfietser') . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$is_member = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $is_member;
	}

	/**
	 * Show users viewing Wereldfietser page on the Who Is Online page
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/account-merge') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_WERELDFIETSER_WERELDFIETSER');
			$event['location_url'] = $this->helper->route('wereldfietser_wereldfietser_merge_controller');
		}
	}

	/**
	 * Check user's API membership status when username changes
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function on_user_change_name($event)
	{
		$user_id = (int) $event['user_id'];

		// Get the user's Wereldfietser ID from profile fields
		$sql = 'SELECT pf_wereldfietser_id FROM ' . PROFILE_FIELDS_DATA_TABLE . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$pf_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$wereldfietser_id = ($pf_row && !empty($pf_row['pf_wereldfietser_id'])) ? $pf_row['pf_wereldfietser_id'] : null;

		// Only check if user is linked to a Wereldfietser account
		if (!$wereldfietser_id) {
			return;
		}

		// Check if user is still an active member
		if (!$this->auth_provider->check_membership_status($wereldfietser_id)) {
			// User is no longer active, remove from group
			$this->auth_provider->remove_member_group($user_id);
		}
	}

	/**
	 * Prevent password and username changes for users linked to Wereldfietser account
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function prevent_password_and_username_change_for_linked_accounts($event)
	{
		$user_id = (int) $this->user->data['user_id'];

		// Get the user's Wereldfietser ID from profile fields
		$sql = 'SELECT pf_wereldfietser_id FROM ' . PROFILE_FIELDS_DATA_TABLE . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$pf_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$wereldfietser_id = ($pf_row && !empty($pf_row['pf_wereldfietser_id'])) ? $pf_row['pf_wereldfietser_id'] : null;

		// Only restrict if user is linked to a Wereldfietser account
		if (!$wereldfietser_id) {
			return;
		}

		$data = $event['data'];
		$error = $event['error'];

		// Check if password was changed (new_password field is not empty)
		if (!empty($data['new_password']) || (isset($data['cur_password']) && !empty($data['cur_password']))) {
			$error[] = $this->language->lang('PASSWORD_CHANGE_NOT_ALLOWED_LINKED_ACCOUNT');
		}

		// Check if username was changed (username field differs from current username)
		if (isset($data['username']) && $data['username'] !== $this->user->data['username']) {
			$error[] = $this->language->lang('USERNAME_CHANGE_NOT_ALLOWED_LINKED_ACCOUNT');
		}

		$event['error'] = $error;
	}
}
