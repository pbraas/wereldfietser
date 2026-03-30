<?php
namespace wereldfietser\wereldfietser\ucp;

class main_info
{
    public function module()
    {
        return [
            'filename'  => '\wereldfietser\wereldfietser\ucp\main_module',
            'title'     => 'UCP_WERELDFIETSER_TITLE',
            'version'   => '1.0.0',
            'modes'     => [
                'settings'  => [
                    'title' => 'UCP_WERELDFIETSER_SETTINGS',
                    'auth'  => '',
                    'cat'   => ['UCP_WERELDFIETSER_TITLE'],
                ],
            ],
        ];
    }
}
