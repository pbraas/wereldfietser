<?php
namespace wereldfietser\wereldfietser\auth\provider;

use phpbb\auth\provider\provider_interface;
use phpbb\db\driver\driver_interface;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\user;
use phpbb\log\log; // Added log service

class api implements provider_interface
{
    /** @var driver_interface */
    private $db;

    /** @var user */
    private $user;

    /** @var config */
    private $config;

    /** @var language */
    private $language;

    /** @var string */
    private $phpbb_root_path;

    /** @var string */
    private $php_ext;

    /** @var \phpbb\auth\provider\db */
    private $db_provider;
    
    /** @var log */
    private $log; // Added log property

    // TODO: Make these configurable via ACP
    private $api_url = 'https://wereldfietser.genkgo.app/_/integration/api/v1/login';
    private $api_check_url = 'https://wereldfietser.genkgo.app/_/integration/api/v1/organization/entry/';
    private $api_token = '51234349-96d2-11f0-a78c-0242fa146f00';
    private $member_group_name = 'Wereldfietser'; // The name of the group to assign

    // Updated constructor to accept log service
    public function __construct(driver_interface $db, user $user, config $config, language $language, $phpbb_root_path, $php_ext, \phpbb\auth\provider\db $db_provider, log $log)
    {
        $this->db = $db;
        $this->user = $user;
        $this->config = $config;
        $this->language = $language;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->db_provider = $db_provider;
        $this->log = $log;
    }

    public function init()
    {
        if (!function_exists('curl_init')) {
            $this->language->add_lang('common', 'wereldfietser/wereldfietser');
            return 'PHP_CURL_NOT_INSTALLED';
        }

        return false;
    }

    public function login($username, $password)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $this->language->add_lang('common', 'wereldfietser/wereldfietser');
            return [
                'status'    => LOGIN_ERROR_USERNAME,
                'error_msg' => 'LOGIN_ERROR_EMAIL_NOT_ALLOWED',
                'user_row'  => ['user_id' => ANONYMOUS],
            ];
        }

        if (!$username || !$password) {
            return [
                'status'    => LOGIN_ERROR_USERNAME,
                'error_msg' => 'NO_USERNAME_OR_PASSWORD',
                'user_row'  => ['user_id' => ANONYMOUS],
            ];
        }

        $payload = json_encode(['uid' => $username, 'password' => $password]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Connection timeout after 5 seconds
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Api-Token: ' . $this->api_token,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            // Log cURL error to ACP
            $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'API_CURL_ERROR', false, [curl_error($ch)]);
            curl_close($ch);
            
            $this->sync_membership_status_by_username($username);
            return $this->db_provider->login($username, $password);
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log API response code if not 200
        if ($http_code !== 200) {
             $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'API_LOGIN_FAILED', false, ["HTTP Code: $http_code"]);
        }

        if ($http_code === 200) {
            $api_response = json_decode($response, true);

            if (!empty($api_response['resource']['success']) && !empty($api_response['resource']['user']['uid']) && !empty($api_response['resource']['user']['id'])) {
                $api_user = $api_response['resource']['user'];
                $wereldfietser_uid = $api_user['uid'];
                $wereldfietser_id = $api_user['id'];

                // --- NEW CHECK: Verify Active Membership ---
                if (!$this->check_membership_status($wereldfietser_id)) {
                    // Log membership failure
                    $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'API_MEMBERSHIP_CHECK_FAILED', false, ["User: $username"]);

                    // User is NOT an active member.
                    // Try to find the local user and remove them from the group.
                    $sql = 'SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($wereldfietser_uid)) . "'";
                    $result = $this->db->sql_query($sql);
                    $user_row = $this->db->sql_fetchrow($result);
                    $this->db->sql_freeresult($result);

                    if ($user_row) {
                        $this->remove_member_group($user_row['user_id']);
                    }

                    return $this->db_provider->login($username, $password);
                }
                // -------------------------------------------

                // Check if a phpBB user with this username already exists
                $sql = 'SELECT * FROM ' . USERS_TABLE . " WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($wereldfietser_uid)) . "'";
                $result = $this->db->sql_query($sql);
                $user_row = $this->db->sql_fetchrow($result);
                $this->db->sql_freeresult($result);

                if ($user_row) {
                    $user_id = $user_row['user_id'];

                    // Check if the account is already linked correctly
                    $sql = 'SELECT pf_wereldfietser_id FROM ' . PROFILE_FIELDS_DATA_TABLE . ' WHERE user_id = ' . (int) $user_id;
                    $result = $this->db->sql_query($sql);
                    $pf_row = $this->db->sql_fetchrow($result);
                    $this->db->sql_freeresult($result);

                    $stored_wereldfietser_id = ($pf_row && !empty($pf_row['pf_wereldfietser_id'])) ? $pf_row['pf_wereldfietser_id'] : null;

                    if ($stored_wereldfietser_id == $wereldfietser_id) {
                        // SUCCESS: User is already linked.
                        
                        // --- ASSIGN GROUP ---
                        $this->assign_member_group($user_id);
                        // --------------------

                        return [
                            'status'    => LOGIN_SUCCESS,
                            'error_msg' => false,
                            'user_row'  => $user_row,
                        ];
                    }

                    // User exists but is NOT linked (or linked to wrong ID). Trigger linking flow.
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['wereldfietser_id_to_link'] = $wereldfietser_id;
                    $_SESSION['wereldfietser_user_id_to_link'] = $user_id;

                    return [
                        'status'    => LOGIN_ERROR_EXTERNAL_AUTH,
                        'error_msg' => 'LOGIN_ERROR_EXTERNAL_AUTH',
                        'user_row'  => ['user_id' => ANONYMOUS],
                        'custom_data' => [
                            'action' => 'WERELDFIETSER_LINK_ACCOUNT',
                        ],
                    ];
                } else {
                    // No phpBB user with this username exists. Create a new one.
                    if (!function_exists('user_add')) {
                        include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
                    }

                    $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = '" . $this->db->sql_escape('REGISTERED') . "' AND group_type = " . GROUP_SPECIAL;
                    $result = $this->db->sql_query($sql);
                    $group = $this->db->sql_fetchrow($result);
                    $this->db->sql_freeresult($result);

                    if (!$group) {
                        return [
                            'status'    => LOGIN_ERROR_EXTERNAL_AUTH,
                            'error_msg' => 'REGISTERED_GROUP_NOT_FOUND',
                            'user_row'  => ['user_id' => ANONYMOUS],
                        ];
                    }

                    $user_email = !empty($api_user['email']) ? $api_user['email'] : $wereldfietser_uid . '@users.noreply.' . $this->config['server_name'];

                    $user_row_new = [
                        'username'      => $wereldfietser_uid,
                        'user_password' => phpbb_hash(gen_rand_string(32)),
                        'user_email'    => $user_email,
                        'group_id'      => (int) $group['group_id'],
                        'user_type'     => USER_NORMAL,
                        'user_lang'     => $this->config['default_lang'],
                        'user_timezone' => $this->config['board_timezone'],
                    ];

                    // Pass custom profile field data directly to user_add
                    $cp_data = ['pf_wereldfietser_id' => $wereldfietser_id];

                    $user_id = user_add($user_row_new, $cp_data);

                    if (is_string($user_id)) {
                        return [
                            'status'    => LOGIN_ERROR_EXTERNAL_AUTH,
                            'error_msg' => $user_id,
                            'user_row'  => ['user_id' => ANONYMOUS],
                        ];
                    }

                    // --- ASSIGN GROUP ---
                    $this->assign_member_group($user_id);
                    // --------------------

                    return [
                        'status'    => LOGIN_SUCCESS,
                        'error_msg' => false,
                        'user_row'  => array_merge($user_row_new, ['user_id' => $user_id]),
                    ];
                }
            }
        }

        // Fallback: Check status before DB login
        $this->sync_membership_status_by_username($username);
        return $this->db_provider->login($username, $password);
    }

    private function sync_membership_status_by_username($username)
    {
        // 1. Find user
        $sql = 'SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
        $result = $this->db->sql_query($sql);
        $user_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$user_row) {
            return; // User not found locally
        }

        $user_id = $user_row['user_id'];

        // 2. Get Wereldfietser ID
        $sql = 'SELECT pf_wereldfietser_id FROM ' . PROFILE_FIELDS_DATA_TABLE . ' WHERE user_id = ' . (int) $user_id;
        $result = $this->db->sql_query($sql);
        $pf_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        $wereldfietser_id = ($pf_row && !empty($pf_row['pf_wereldfietser_id'])) ? $pf_row['pf_wereldfietser_id'] : null;

        if (!$wereldfietser_id) {
            return; // Not linked
        }

        // 3. Check Status
        if ($this->check_membership_status($wereldfietser_id)) {
            // Active: Ensure in group
            $this->assign_member_group($user_id);
        } else {
            // Inactive: Remove from group
            $this->remove_member_group($user_id);
        }
    }

    private function check_membership_status($wereldfietser_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_check_url . $wereldfietser_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Connection timeout after 5 seconds
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Api-Token: ' . $this->api_token,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $data = json_decode($response, true);
            // Check for folder ID 27933 (Actieve leden)
            if (isset($data['resource']['parentFolderId']) && $data['resource']['parentFolderId'] == 27933) {
                return true;
            }
        }

        return false;
    }

    private function assign_member_group($user_id)
    {
        if (!function_exists('group_user_add')) {
            include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
        }

        // Find the group ID
        $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = '" . $this->db->sql_escape($this->member_group_name) . "'";
        $result = $this->db->sql_query($sql);
        $group_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($group_row) {
            $group_id = (int) $group_row['group_id'];
            
            // Add user to group (false = not default group, false = not leader, false = not pending)
            // Check if user is already in group to avoid unnecessary queries/logs
            $sql = 'SELECT 1 FROM ' . USER_GROUP_TABLE . ' WHERE group_id = ' . (int) $group_id . ' AND user_id = ' . (int) $user_id;
            $result = $this->db->sql_query($sql);
            $is_in_group = $this->db->sql_fetchrow($result);
            $this->db->sql_freeresult($result);

            if (!$is_in_group) {
                group_user_add($group_id, $user_id, false, false, false);
            }
        }
    }

    private function remove_member_group($user_id)
    {
        if (!function_exists('group_user_del')) {
            include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
        }

        // Find the group ID
        $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = '" . $this->db->sql_escape($this->member_group_name) . "'";
        $result = $this->db->sql_query($sql);
        $group_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($group_row) {
            $group_id = (int) $group_row['group_id'];
            
            // Remove user from group
            group_user_del($group_id, $user_id);
        }
    }

    public function autologin() { return null; }
    public function logout($data, $new_session) {}
    public function acp() { return null; }
    public function get_acp_template($new_config) { return null; }
    public function get_login_data() { return null; }
    public function validate_session($user) { return null; }
    public function login_link_has_necessary_data(array $login_link_data) { return null; }
    public function link_account(array $link_data) {}
    public function get_auth_link_data($user_id = 0) { return null; }
    public function unlink_account(array $link_data) {}
}
