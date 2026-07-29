<?php
namespace wereldfietser\wereldfietser\auth\provider;

use phpbb\auth\provider\provider_interface;
use phpbb\db\driver\driver_interface;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\user;

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

    // TODO: Make these configurable via ACP
    private $api_url = 'https://wereldfietser.genkgo.app/_/integration/api/v1/login';
    private $api_token = '51234349-96d2-11f0-a78c-0242fa146f00';

    private const WERELDFIETSER_GROUP_NAME = 'Wereldfietser';
    private const ACTIEVE_LEDEN_GROUP = 'actieve leden';

    public function __construct(driver_interface $db, user $user, config $config, language $language, $phpbb_root_path, $php_ext, \phpbb\auth\provider\db $db_provider)
    {
        $this->db = $db;
        $this->user = $user;
        $this->config = $config;
        $this->language = $language;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->db_provider = $db_provider;
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Api-Token: ' . $this->api_token,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);
            // API unreachable — fall back to DB login and clean up group
            return $this->handle_forum_login_and_cleanup_group($username, $password);
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $api_response = json_decode($response, true);

            if (!empty($api_response['resource']['success']) && !empty($api_response['resource']['user']['uid']) && !empty($api_response['resource']['user']['id'])) {
                $api_user = $api_response['resource']['user'];
                $wereldfietser_uid = $api_user['uid'];
                $wereldfietser_id = $api_user['id'];

                // Check whether this API user is still an "actieve leden"
                $is_active_member = $this->check_is_active_member($api_user);

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
                        // Sync group membership based on "actieve leden" status.
                        $debug_log = [];
                        $debug_log[] = "User {$user_id} linked with ID {$wereldfietser_id}. Active member: " . ($is_active_member ? 'yes' : 'no');

                        if ($is_active_member) {
                            $debug_log[] = "Attempting to add user to Wereldfietser group...";
                            $this->add_user_to_wereldfietser_group((int) $user_id);
                            $debug_log[] = "Add group function completed.";
                        } else {
                            $debug_log[] = "User is not active member, removing from Wereldfietser group...";
                            $this->remove_user_from_wereldfietser_group((int) $user_id);
                            $debug_log[] = "Remove group function completed.";
                        }

                        // Log debug info to file for troubleshooting
                        if (defined('DEBUG') && DEBUG) {
                            error_log(implode("\n", $debug_log), 0);
                        }

                        return [
                            'status'    => LOGIN_SUCCESS,
                            'error_msg' => false,
                            'user_row'  => $user_row,
                        ];
                    }

                    // User exists but is NOT linked. Trigger linking flow.
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

                    $cp_data = ['pf_wereldfietser_id' => $wereldfietser_id];
                    $user_id = user_add($user_row_new, $cp_data);

                    if (is_string($user_id)) {
                        return [
                            'status'    => LOGIN_ERROR_EXTERNAL_AUTH,
                            'error_msg' => $user_id,
                            'user_row'  => ['user_id' => ANONYMOUS],
                        ];
                    }

                    return [
                        'status'    => LOGIN_SUCCESS,
                        'error_msg' => false,
                        'user_row'  => array_merge($user_row_new, ['user_id' => $user_id]),
                    ];
                }
            }
        }

        // API returned non-200 or unsuccessful response — fall back to DB login and clean up group
        return $this->handle_forum_login_and_cleanup_group($username, $password);
    }

    /**
     * Log in via the DB provider and, on success, remove the user from the
     * Wereldfietser group (they are authenticating with a forum password, not
     * through the Wereldfietser API).
     */
    private function handle_forum_login_and_cleanup_group(string $username, string $password): array
    {
        $result = $this->db_provider->login($username, $password);

        if (
            isset($result['status'], $result['user_row']['user_id'])
            && (int) $result['status'] === LOGIN_SUCCESS
            && (int) $result['user_row']['user_id'] > 0
        ) {
            $user_id = (int) $result['user_row']['user_id'];
            $this->remove_user_from_wereldfietser_group($user_id);

            // Also unlink the wereldfietser account so the user is fully decoupled
            $sql = 'UPDATE ' . PROFILE_FIELDS_DATA_TABLE . "
                    SET pf_wereldfietser_id = 0
                    WHERE user_id = " . $user_id;
            $this->db->sql_query($sql);
        }

        return $result;
    }

    /**
     * Determine whether the API user is an "actieve leden".
     * The API login response may include a "groups" array; we check for it.
     * If the field is absent we assume the user is still active (safe default).
     */
    private function check_is_active_member(array $api_user): bool
    {
        // If the API response includes a groups/memberships array, check it.
        if (isset($api_user['groups']) && is_array($api_user['groups'])) {
            foreach ($api_user['groups'] as $group) {
                $name = is_array($group) ? ($group['name'] ?? '') : (string) $group;
                if (stripos($name, self::ACTIEVE_LEDEN_GROUP) !== false) {
                    return true;
                }
            }
            return false;
        }

        if (isset($api_user['memberships']) && is_array($api_user['memberships'])) {
            foreach ($api_user['memberships'] as $membership) {
                $name = is_array($membership) ? ($membership['name'] ?? '') : (string) $membership;
                if (stripos($name, self::ACTIEVE_LEDEN_GROUP) !== false) {
                    return true;
                }
            }
            return false;
        }

        // No group info in response — cannot determine, assume active.
        return true;
    }

    /**
     * Add $user_id to the Wereldfietser group if they are not yet a member.
     * Uses direct SQL to avoid any dependency on group_user_add() loading correctly.
     */
    private function add_user_to_wereldfietser_group(int $user_id): void
    {
        $group_id = $this->get_group_id_by_name(self::WERELDFIETSER_GROUP_NAME);
        if (!$group_id) {
            error_log("ERROR: Wereldfietser group not found for user {$user_id}");
            return;
        }

        // Check if already a member
        $sql = 'SELECT user_id FROM ' . USER_GROUP_TABLE . '
                WHERE group_id = ' . (int) $group_id . '
                AND user_id = ' . $user_id;
        $result = $this->db->sql_query($sql);
        $member = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$member) {
            error_log("User {$user_id} not yet in Wereldfietser group {$group_id}. Attempting to add...");

            // Insert directly with raw SQL (more stable than sql_build_array)
            $insert_sql = 'INSERT INTO ' . USER_GROUP_TABLE . ' (group_id, user_id, group_leader, user_pending) '
                . 'VALUES (' . (int) $group_id . ', ' . $user_id . ', 0, 0)';
            $this->db->sql_query($insert_sql);
            error_log("Executed INSERT: {$insert_sql}");

            // Also update the user's default group to Wereldfietser
            $update_sql = 'UPDATE ' . USERS_TABLE . ' SET group_id = ' . (int) $group_id . ' WHERE user_id = ' . $user_id;
            $this->db->sql_query($update_sql);
            error_log("Executed UPDATE: {$update_sql}");

            // Verify the insert worked
            $verify_sql = 'SELECT COUNT(*) as cnt FROM ' . USER_GROUP_TABLE . '
                           WHERE group_id = ' . (int) $group_id . '
                           AND user_id = ' . $user_id;
            $verify_result = $this->db->sql_query($verify_sql);
            $verify_row = $this->db->sql_fetchrow($verify_result);
            $this->db->sql_freeresult($verify_result);
            error_log("Verify: User {$user_id} count in group {$group_id}: " . ($verify_row['cnt'] ?? 0));
        } else {
            error_log("User {$user_id} already in Wereldfietser group {$group_id}");
        }

        // Clear cached permissions AND sessions so phpBB recomputes them fresh on next login.
        $this->db->sql_query('UPDATE ' . USERS_TABLE . " SET user_permissions = '' WHERE user_id = " . $user_id);
        $this->db->sql_query('DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . $user_id);
    }

    /**
     * Remove $user_id from the Wereldfietser group if they are a member.
     * Uses direct SQL to avoid any dependency on group_user_del() loading correctly.
     */
    private function remove_user_from_wereldfietser_group(int $user_id): void
    {
        $group_id = $this->get_group_id_by_name(self::WERELDFIETSER_GROUP_NAME);
        if (!$group_id) {
            return;
        }

        // Check membership
        $sql = 'SELECT user_id FROM ' . USER_GROUP_TABLE . '
                WHERE group_id = ' . (int) $group_id . '
                AND user_id = ' . $user_id;
        $result = $this->db->sql_query($sql);
        $member = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($member) {
            $sql = 'DELETE FROM ' . USER_GROUP_TABLE . '
                    WHERE group_id = ' . (int) $group_id . '
                    AND user_id = ' . $user_id;
            $this->db->sql_query($sql);

            // Clear cached permissions AND sessions
            $this->db->sql_query('UPDATE ' . USERS_TABLE . " SET user_permissions = '' WHERE user_id = " . $user_id);
            $this->db->sql_query('DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . $user_id);

            // If the user's default group was Wereldfietser, switch it to REGISTERED
            $sql = 'SELECT group_id FROM ' . USERS_TABLE . ' WHERE user_id = ' . $user_id;
            $result = $this->db->sql_query($sql);
            $user_data = $this->db->sql_fetchrow($result);
            $this->db->sql_freeresult($result);

            if ($user_data && (int) $user_data['group_id'] === $group_id) {
                $registered_gid = $this->get_group_id_by_name('REGISTERED');
                if ($registered_gid) {
                    $sql = 'UPDATE ' . USERS_TABLE . ' SET group_id = ' . (int) $registered_gid . ' WHERE user_id = ' . $user_id;
                    $this->db->sql_query($sql);
                }
            }
        }
    }

    /**
     * Return the group_id for a given group name, or null if not found.
     */
    private function get_group_id_by_name(string $group_name): ?int
    {
        $sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = '" . $this->db->sql_escape($group_name) . "'";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return $row ? (int) $row['group_id'] : null;
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
