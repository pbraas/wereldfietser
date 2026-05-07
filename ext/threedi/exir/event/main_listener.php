<?php
/**
 *
 * Exif Image Rotator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, 3Di, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\exir\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Exif Image Rotator Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var string phpBB root path */
	protected $root_path;

	/**
	* Constructor
	*/
	public function __construct(\phpbb\config\config $config, $root_path)
	{
		$this->config		= $config;
		$this->root_path	= $root_path;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.modify_uploaded_file' => 'exir_image_rotation',
		);
	}

	/**
	 * Event to modify uploaded file before submit to the post
	 *
	 * @event core.modify_uploaded_file
	 * @var	array	filedata	Array containing uploaded file data
	 * @var	bool	is_image	Flag indicating if the file is an image
	 * @since 3.1.0-RC3
	 */
	public function exir_image_rotation($event)
	{
		$is_image = $event['is_image'];
		$filedata = $event['filedata'];

		/* UTF8-safe basename() function FTW */
		$dest_file = $this->root_path . $this->config['upload_path'] . '/' . utf8_basename($filedata['physical_filename']);

		if ($is_image)
		{
			/* Only JPEG supported so far */
			if ( function_exists('exif_read_data') && ($filedata['mimetype'] == 'image/jpeg') )
			{
				$this->image_orient($dest_file);
			}
			else
			{
				return;
			}
		}
	}

	/**
	 * Originally written by AmigoJack
	 * https://www.phpbb.com/community/viewtopic.php?p=14664586#p14664586
	 *
	 * @param string	$dest_file		the current attachment's complete physical_filename (abs URL)
	 * @return void
	 */
	protected function image_orient($dest_file)
	{
		/* For some this may be of help */
		@ini_set('memory_limit', '256M');

		/* Only JPEG supported so far */
		$orient_dest = imagecreatefromjpeg($dest_file);

		$a_exif = @exif_read_data($dest_file);

		if (isset($a_exif['Orientation']))
		{
			switch($a_exif['Orientation'])
			{
				/* Horizontal flip */
				case 2:
				/* Vertical flip */
				case 4:
				/* Vertical flip & 90° rotate clockwise */
				case 5:
				/* Horizontal flip & 90° rotate counter clockwise */
				case 7:
					$b_flip = true;
				break;

				default:
					$b_flip = false;
				break;
			}

			switch( $a_exif['Orientation'] )
			{
				/* 180° rotate */
				case 3:
				/* Vertical flip */
				case 4:
					$i_rotate = 180;
				break;

				/* Vertical flip & 90° rotate clockwise */
				case 5:
				/* 90° rotate clockwise */
				case 8:
					$i_rotate = 90;
				break;

				/* 90° rotate counter clockwise */
				case 6:
				/* Horizontal flip & 90° rotate counter clockwise */
				case 7:
					$i_rotate = -90;
				break;

				default:
					$i_rotate = 0;
				break;
			}

			if ($b_flip)
			{
				imageflip( $orient_dest, IMG_FLIP_HORIZONTAL );
			}

			if ($i_rotate)
			{
				$orient_dest = imagerotate($orient_dest, $i_rotate, 0);
			}

			/**
			 * Using 100 means double/triple the original filesize.
			 * Default it is 95 by installation.
			 */
			imagejpeg($orient_dest, $dest_file, (int) $this->config['threedi_exir_percent']);

			/* Free memory */
			imagedestroy($orient_dest);
		}
	}
}
