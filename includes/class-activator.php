<?php

namespace Loworx\Ridepool;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0
 */
class Activator
{
	/**
	 * activate
	 *
	 * wordpress activation hook
	 *
     * @access  public
     * @since   1.0.0 
     * @static
	 */
	public static function activate()
	{
        error_log(__CLASS__.'->'.__LINE__.'-> Activator'. Settings::get_plugin_dir_path());
		$data	= array(
			'Hallo' => 'Kai'
		);
		$jwt	= JWT_Singleton::get_instance();
		$token	= $jwt->generate_jwt($data);
        error_log(__CLASS__.'->'.__LINE__.'-> Activator'. $token);
    }
}