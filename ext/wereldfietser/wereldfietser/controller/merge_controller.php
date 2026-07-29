<?php
namespace wereldfietser\wereldfietser\controller;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\log\log;
use phpbb\passwords\manager;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\profilefields\manager as cp_manager;

class merge_controller
{
    /** @var config */
    protected $config;

    /** @var driver_interface */
    protected $db;

    /** @var request */
    protected $request;

    /** @var template */
    protected $template;

    /** @var user */
    protected $user;

    /** @var log */
    protected $log;

    /** @var manager */
    protected $passwords_manager;

    /** @var helper */
    protected $helper;

    /** @var language */
    protected $language;

    /** @var string */
    protected $php_ext;

    /** @var cp_manager */
    protected $cp_manager;

    public function __construct(config $config, driver_interface $db, request $request, template $template, user $user, log $log, manager $passwords_manager, helper $helper, language $language, $php_ext, cp_manager $cp_manager)
    {
        $this->config = $config;
        $this->db = $db;
        $this->request = $request;
        $this->template = $template;
        $this->user = $user;
        $this->log = $log;
        $this->passwords_manager = $passwords_manager;
        $this->helper = $helper;
        $this->language = $language;
        $this->php_ext = $php_ext;
        $this->cp_manager = $cp_manager;
    }

    public function handle()
    {
        // Load the language file
        $this->language->add_lang('common', 'wereldfietser/wereldfietser');

        $wereldfietser_id = $this->request->variable('wereldfietser_id', 0);
        $user_id = $this->request->variable('user_id', 0);
        $submit = $this->request->is_set_post('submit');

        if ($submit) {
            return $this->process_form($wereldfietser_id, $user_id);
        } else {
            return $this->display_form($wereldfietser_id, $user_id);
        }
    }

    private function display_form($wereldfietser_id, $user_id)
    {
        $action_url = $this->helper->route('wereldfietser_wereldfietser_merge_controller', ['wereldfietser_id' => $wereldfietser_id, 'user_id' => $user_id]);

        // FORCE FIX: Ensure app.php is present for local environments without mod_rewrite
        if (strpos($action_url, 'app.php') === false && strpos($action_url, '?') !== false) {
             $action_url = str_replace('/account-merge', '/app.php/account-merge', $action_url);
        }

        $this->template->assign_vars([
            'WERELDFIETSER_ID' => $wereldfietser_id,
            'USER_ID' => $user_id,
            'S_MERGE_ACTION' => $action_url,
        ]);

        return $this->helper->render('@wereldfietser_wereldfietser/merge_form.html');
    }

    private function process_form($wereldfietser_id, $user_id)
    {
        $entered_wereldfietser_id = $this->request->variable('wereldfietser_id_confirm', 0);
        $password = $this->request->variable('password', '', true);

        if ($entered_wereldfietser_id != $wereldfietser_id) {
            $this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'WERELDFIETSER_MERGE_ID_MISMATCH');
            trigger_error('WERELDFIETSER_MERGE_ID_MISMATCH');
        }

        $sql = 'SELECT user_password FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $user_id;
        $result = $this->db->sql_query($sql);
        $user_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$user_row || !$this->passwords_manager->check($password, $user_row['user_password'])) {
            $this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'WERELDFIETSER_MERGE_INVALID_PASSWORD');
            trigger_error('WERELDFIETSER_MERGE_INVALID_PASSWORD');
        }

        // Update the profile field using the manager
        $this->cp_manager->update_profile_field_data($user_id, ['pf_wereldfietser_id' => $wereldfietser_id]);

        // Randomise the phpBB password so the user can no longer log in with their
        // old forum password. They can still use "Forgot password" to regain access
        // if they ever cancel their wereldfietser.nl membership.
        $random_password = bin2hex(random_bytes(16));
        $hashed_password = $this->passwords_manager->hash($random_password);
        $this->db->sql_query('UPDATE ' . USERS_TABLE . " SET user_password = '" . $this->db->sql_escape($hashed_password) . "' WHERE user_id = " . (int) $user_id);

        // Add user to the Wereldfietser group
        $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = 'Wereldfietser' LIMIT 1";
        $result = $this->db->sql_query($sql);
        $group_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($group_row) {
            $wereldfietser_group_id = (int) $group_row['group_id'];

            // Check if already a member
            $sql = 'SELECT user_id FROM ' . USER_GROUP_TABLE . ' WHERE group_id = ' . $wereldfietser_group_id . ' AND user_id = ' . (int) $user_id;
            $result = $this->db->sql_query($sql);
            $already_member = $this->db->sql_fetchrow($result);
            $this->db->sql_freeresult($result);

            if (!$already_member) {
                $this->db->sql_query('INSERT INTO ' . USER_GROUP_TABLE . ' (group_id, user_id, group_leader, user_pending) VALUES (' . $wereldfietser_group_id . ', ' . (int) $user_id . ', 0, 0)');
            }

            // Set Wereldfietser as the user's default group
            $this->db->sql_query('UPDATE ' . USERS_TABLE . ' SET group_id = ' . $wereldfietser_group_id . ' WHERE user_id = ' . (int) $user_id);
        }

        // Clear phpBB's cached permissions so the new group is picked up immediately
        $this->db->sql_query('UPDATE ' . USERS_TABLE . " SET user_permissions = '' WHERE user_id = " . (int) $user_id);

        // Delete old sessions so the new session gets fresh group permissions
        $this->db->sql_query('DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . (int) $user_id);

        $this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'WERELDFIETSER_MERGE_SUCCESS');

        // Log the user in with a fresh session
        $this->user->session_create($user_id, false, true, true);
        
        // Ensure script path ends with a slash
        $script_path = $this->config['script_path'];
        if (substr($script_path, -1) !== '/') {
            $script_path .= '/';
        }
        
        redirect(append_sid($script_path . 'index.' . $this->php_ext));
    }
}
