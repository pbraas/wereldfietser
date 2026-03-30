<?php
namespace wereldfietser\wereldfietser\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\user;

class listener implements EventSubscriberInterface
{
    /** @var helper */
    protected $helper;

    /** @var template */
    protected $template;

    /** @var user */
    protected $user;

    public function __construct(helper $helper, template $template, user $user)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
    }

    public static function getSubscribedEvents()
    {
        return [
            'core.login_box_failed' => 'handle_login_failure',
        ];
    }

    public function handle_login_failure($event)
    {
        // Get the full result array from the event
        $result = isset($event['result']) ? $event['result'] : [];

        // Check for our custom action signal
        if (isset($result['custom_data']['action']) && $result['custom_data']['action'] === 'WERELDFIETSER_LINK_ACCOUNT') {
            
            // Retrieve the data we stored in the session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $wereldfietser_id = isset($_SESSION['wereldfietser_id_to_link']) ? $_SESSION['wereldfietser_id_to_link'] : null;
            $user_id = isset($_SESSION['wereldfietser_user_id_to_link']) ? $_SESSION['wereldfietser_user_id_to_link'] : null;

            if ($wereldfietser_id && $user_id) {
                $redirect_url = $this->helper->route('wereldfietser_wereldfietser_merge_controller', [
                    'wereldfietser_id' => $wereldfietser_id,
                    'user_id' => $user_id,
                ]);

                // FORCE FIX: Ensure app.php is present for local environments without mod_rewrite
                if (strpos($redirect_url, 'app.php') === false && strpos($redirect_url, '?') !== false) {
                     $redirect_url = str_replace('/account-merge', '/app.php/account-merge', $redirect_url);
                }
                
                // Perform the redirect
                redirect($redirect_url);
            }
        }
    }
}
