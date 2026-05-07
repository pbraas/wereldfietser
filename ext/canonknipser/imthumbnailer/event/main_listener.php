<?php
/**
 *
 * ImageMagick Thumbnailer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, canonknipser, http://canonknipser.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace canonknipser\imthumbnailer\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ImageMagick Thumbnailer Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\log\log */
	protected $log;

	/* @var \phpbb\user */
	protected $user;

	/**
	* Constructor for listener
	*
	* @param \phpbb\config\config	$config		phpBB config
	* @param \phpbb\request\request	$request	phpBB request
	* @param \phpbb\log\log			$log		phpBB log
	* @param \phpbb\user			$user		phpBB user
	*
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\request\request $request, \phpbb\log\log $log, \phpbb\user $user)
	{
		$this->config	= $config;
		$this->request	= $request;
		$this->log		= $log;
		$this->user		= $user;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.thumbnail_create_before'	=> 'ck_it_create_tumbnail',
		);
	}

	/**
	 * Create a thumbnail using IMagick
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function ck_it_create_tumbnail($event)
	{
		/**
		store all variables from $event for future use,
		return value is only thumbnail_created, defaults to false
		*/

		$ck_it_source				= $event['source'];
		$ck_it_destination			= $event['destination'];
		$ck_it_mimetype				= $event['mimetype'];
		$ck_it_new_width			= $event['new_width'];
		$ck_it_new_height			= $event['new_height'];
		$ck_it_thumbnail_created	= false;

		// load language file
		$this->user->add_lang('common', false, false,'canonknipser/imthumbnailer');

		try
		{

			// create a new instance of ImageMagick and load current image
			if (!($ck_it_thumb = new \Imagick(realpath($ck_it_source))))
			{
				$this->ck_im_loggen($this->user->lang['CK_ERR_NEW_INSTANCE']);
			}


			// Check the mimetype and set the appropriate type for the thumbnail
			switch($ck_it_mimetype)
			{
				case 'image/jpeg':
					$ck_it_imageformat = 'JPEG';
					break;
				case 'image/png':
					$ck_it_imageformat = 'PNG';
					break;
				case 'image/gif':
					$ck_it_imageformat = 'GIF';
					break;
				default:
					// unknown type, set a default
					$ck_it_imageformat = 'JPEG';
					$this->ck_im_loggen($this->user->lang['CK_WARN_MIMETYPE'].$ck_it_mimetype);
					break;
			}

			if (!($ck_it_thumb->setImageFormat($ck_it_imageformat)))
			{
				$this->ck_im_loggen($this->user->lang['CK_ERR_SET_FORMAT']);
			}

			// Compression quality is read from config, set in ACP
			if (!($ck_it_thumb->setImageCompressionQuality($this->config['ck_it_quality'])))
			{
				$this->ck_im_loggen($this->user->lang['CK_ERR_SET_FORMAT']);
			}

			// rotate the image according it's orientation flag

			// read the Orientation from the Image
			if (!($ck_it_orientation = $ck_it_thumb->getImageOrientation()))
			{
				// Orientation flag not available
				$ck_it_orientation = \Imagick::ORIENTATION_UNDEFINED;
			}

			// set flags needed for rotation
			// $ck_it_flop = rotate image around central y-axis
			// $ct_it_rotate = angle in degrees to rotate
			// $ck_it_exc_dimension = exchange the dimensions for the generated thumbnail
			switch($ck_it_orientation)
			{
				case \Imagick::ORIENTATION_TOPLEFT:
					$ck_it_flop = false;
					$ck_it_rotate = 0;
					$ck_it_exc_dimension = false;
					break;
				case \Imagick::ORIENTATION_TOPRIGHT:
					$ck_it_flop = true;
					$ck_it_rotate = 0;
					$ck_it_exc_dimension = false;
					break;
				case \Imagick::ORIENTATION_BOTTOMRIGHT:
					$ck_it_flop = false;
					$ck_it_rotate = 180;
					$ck_it_exc_dimension = false;
					break;
				case \Imagick::ORIENTATION_BOTTOMLEFT:
					$ck_it_flop = true;
					$ck_it_rotate = 180;
					$ck_it_exc_dimension = false;
					break;
				case \Imagick::ORIENTATION_LEFTTOP:
					$ck_it_flop = true;
					$ck_it_rotate = -90;
					$ck_it_exc_dimension = true;
					break;
				case \Imagick::ORIENTATION_RIGHTTOP:
					$ck_it_flop = false;
					$ck_it_rotate = 90;
					$ck_it_exc_dimension = true;
					break;
				case \Imagick::ORIENTATION_RIGHTBOTTOM:
					$ck_it_flop = true;
					$ck_it_rotate = 90;
					$ck_it_exc_dimension = true;
					break;
				case \Imagick::ORIENTATION_LEFTBOTTOM:
					$ck_it_flop = false;
					$ck_it_rotate = -90;
					$ck_it_exc_dimension = true;
					break;
				default:
					$ck_it_flop = false;
					$ck_it_rotate = 0;
					$ck_it_exc_dimension = false;
					break;
			}
			if ($ck_it_flop)
			{
				$ck_it_thumb->flopImage();
			}
			if ($ck_it_rotate != 0)
			{
				$ck_it_thumb->rotateImage("#000", $ck_it_rotate);
			}
			if ($ck_it_exc_dimension)
			{
				$ck_it_tmp = $ck_it_new_width;
				$ck_it_new_width = $ck_it_new_height;
				$ck_it_new_height = $ck_it_tmp;
			}
			$ck_it_thumb->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);

			// Do the magic: resize the image using a proper filter
			// todo: add choise of filters to ACP
			if (!($ck_it_thumb->resizeImage($ck_it_new_width, $ck_it_new_height, \Imagick::FILTER_LANCZOS, 1, false)))
			{
				$this->ck_im_loggen($this->user->lang['CK_ERR_RESIZE']);
			}

			// Store the image
			if (($ck_it_thumb->writeImage($ck_it_destination)))
			{
				$ck_it_thumbnail_created = true;
			}
			else
			{
				$this->ck_im_loggen($this->user->lang['CK_ERR_WRITE_IMAGE']);
			}

		}
		catch ( \ImagickException $ex)
		{
			// write error message for Imagick exceptions to admin log
			$this->ck_im_loggen($this->user->lang['CK_ERR_CALLING_IMAGICK'].'<br/>'.$ex->getMessage());
		}

		// set return value
		$event['thumbnail_created'] = $ck_it_thumbnail_created;
	}

	/**
	 * Log a message to the admin log
	 *
	 * @param string	$ck_log_message	Message written to the log
	 */
	function ck_im_loggen($ck_log_message)
	{
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $ck_log_message);
	}


}
