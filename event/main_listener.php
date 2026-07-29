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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use phpbb\user;

class main_listener implements EventSubscriberInterface
{
	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var user */
	protected $user;

	/** @var driver_interface */
	protected $db;

	/** @var request_interface */
	protected $request;

	/** @var string phpEx */
	protected $php_ext;

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup' => 'load_language_on_setup',
			'core.page_header' => 'lock_reg_details_for_linked_users',
			'core.ucp_profile_reg_details_validate' => 'prevent_reg_details_changes_for_linked_users',
			'core.ucp_profile_reg_details_sql_ary' => 'prevent_reg_details_changes_for_linked_users',
			'core.viewonline_overwrite_location' => 'viewonline_page',
		];
	}

	public function __construct(
		\phpbb\language\language $language,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		driver_interface $db,
		request_interface $request,
		user $user,
		$php_ext
	)
	{
		$this->language = $language;
		$this->helper = $helper;
		$this->template = $template;
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->php_ext = $php_ext;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'wereldfietser/wereldfietser',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function viewonline_page($event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/demo') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_WERELDFIETSER_WERELDFIETSER');
			$event['location_url'] = $this->helper->route('wereldfietser_wereldfietser_controller', ['name' => 'wereld']);
		}
	}

	public function lock_reg_details_for_linked_users()
	{
		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			return;
		}

		if ($this->request->variable('mode', '') !== 'reg_details')
		{
			return;
		}

		$module = $this->request->variable('i', '');
		if ($module !== 'ucp_profile' && !is_numeric($module))
		{
			return;
		}

		if ($this->is_linked_user())
		{
			$this->template->assign_var('S_WERELDFIETSER_READONLY', true);
		}
	}

	public function prevent_reg_details_changes_for_linked_users($event)
	{
		if ((int) $this->user->data['user_id'] === ANONYMOUS || !$this->is_linked_user())
		{
			return;
		}

		if (isset($event['error']) && is_array($event['error']))
		{
			$event['error'][] = 'WERELDFIETSER_REG_DETAILS_READONLY';
		}
		else
		{
			$event['error'] = ['WERELDFIETSER_REG_DETAILS_READONLY'];
		}
	}

	private function is_linked_user()
	{
		$sql = 'SELECT pf_wereldfietser_id
			FROM ' . PROFILE_FIELDS_DATA_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return !empty($row['pf_wereldfietser_id']);
	}
}
