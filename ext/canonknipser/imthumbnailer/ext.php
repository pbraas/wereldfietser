<?php
/**
 *
 * ImageMagick Thumbnailer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, canonknipser, http://canonknipser.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace canonknipser\imthumbnailer;

/**
 * ImageMagick Thumbnailer Extension base
 *
 * It is recommended to remove this file from
 * an extension if it is not going to be used.
 */
class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		$return_value = true;
		// several tests, each test is only executed if the previous tests did not fail
		// first test: Imagick library installed?
		if ($return_value)
		{
			$return_value = class_exists('Imagick');
			if (!$return_value)
			{
				$user = $this->container->get('user');
				$user->add_lang('imthumbnailer_acp', false, false, 'canonknipser/imthumbnailer');
				trigger_error($user->lang('CK_IM_REQUIRE_IMAGICK'), E_USER_WARNING);
			}
		}
		// second test: phpBB version greater equal 3.2.4?
		if ($return_value)
		{
			$config =$this->container->get('config');
			$return_value = phpbb_version_compare($config['version'], '3.2.4', '>=');
			if (!$return_value)
			{
				$user = $this->container->get('user');
				$user->add_lang('imthumbnailer_acp', false, false, 'canonknipser/imthumbnailer');
				trigger_error($user->lang('CK_IM_REQUIRE_324'), E_USER_WARNING);
			}
		}

		return $return_value;
	}

}


