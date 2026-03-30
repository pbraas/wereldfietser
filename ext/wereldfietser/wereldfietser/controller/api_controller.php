<?php
namespace wereldfietser\wereldfietser\controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class api_controller
{
    /** @var \phpbb\auth\auth */
    protected $auth;

    /** @var Request */
    protected $request;

    /** @var \phpbb\user */
    protected $user;

    public function __construct(\phpbb\auth\auth $auth, Request $request, \phpbb\user $user)
    {
        $this->auth = $auth;
        $this->request = $request;
        $this->user = $user;
    }

    public function login()
    {
        $username = $this->request->get('username');
        $password = $this->request->get('password');

        $result = $this->auth->login($username, $password);

        if ($result['status'] == LOGIN_SUCCESS) {
            return new JsonResponse(['status' => 'success', 'user_id' => $this->user->data['user_id']]);
        }

        return new JsonResponse(['status' => 'error', 'message' => $result['error_msg']], 401);
    }
}
