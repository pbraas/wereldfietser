<?php

namespace ger\feedbot2;

class ext extends \phpbb\extension\base
{
    public function is_enableable()
    {
		if (!function_exists('simplexml_load_string'))
		{
			$user = $this->container->get('user');
			$user->add_lang_ext('ger/feedbot2', 'info_acp_feedbot2');
			trigger_error($user->lang('FB2_REQUIRE_SIMPLEXML'), E_USER_WARNING);
		}
		return true;
    }
}
