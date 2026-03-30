<?php
/**
 *
 * Wereldfietser. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, Phillip Braas, https://www.wereldfietser.nl
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace wereldfietser\wereldfietser\controller;

/**
 * Wereldfietser main controller.
 */
class main_controller
{
    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\language\language */
    protected $language;

    /** @var \phpbb\user */
    protected $user;

    /**
     * Constructor
     *
     * @param \phpbb\config\config $config Config object
     * @param \phpbb\controller\helper $helper Controller helper object
     * @param \phpbb\template\template $template Template object
     * @param \phpbb\language\language $language Language object
     * @param \phpbb\user $user User object
     */
    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\user $user)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->language = $language;
        $this->user = $user;
    }

    /**
     * Controller handler for route /demo/{name}
     *
     * @param string $name
     *
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function handle($name)
    {
        $l_message = !$this->config['wereldfietser_wereldfietser_goodbye'] ? 'WERELDFIETSER_HELLO' : 'WERELDFIETSER_GOODBYE';
        $this->template->assign_var('WERELDFIETSER_MESSAGE', $this->language->lang($l_message, $name));

        return $this->helper->render('@wereldfietser_wereldfietser/wereldfietser_body.html', $name);
    }
}
