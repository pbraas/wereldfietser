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

    /** @var string */
    protected $phpbb_root_path;

    public function __construct(config $config, driver_interface $db, request $request, template $template, user $user, log $log, manager $passwords_manager, helper $helper, language $language, $php_ext, cp_manager $cp_manager, $phpbb_root_path)
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
        $this->phpbb_root_path = $phpbb_root_path;
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

        // --- ASSIGN GROUP ---
        $this->assign_member_group($user_id);
        // --------------------

        $this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'WERELDFIETSER_MERGE_SUCCESS');

        // Log the user in
        $this->user->session_create($user_id, false, true, true);
        
        // Ensure script path ends with a slash
        $script_path = $this->config['script_path'];
        if (substr($script_path, -1) !== '/') {
            $script_path .= '/';
        }
        
        redirect(append_sid($script_path . 'index.' . $this->php_ext));
    }

    private function assign_member_group($user_id)
    {
        if (!function_exists('group_user_add')) {
            include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
        }

        // Find the group ID
        $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = '" . $this->db->sql_escape('Wereldfietser') . "'";
        $result = $this->db->sql_query($sql);
        $group_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($group_row) {
            $group_id = (int) $group_row['group_id'];
            
            // Add user to group (false = not default group, false = not leader, false = not pending)
            group_user_add($group_id, $user_id, false, false, false);
        }
    }
}
