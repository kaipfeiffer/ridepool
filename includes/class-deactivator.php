<?php

namespace Ridepool;

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
class Deactivator
{
    use Settings;

	/**
	 * activate
	 *
	 * wordpress activation hook
	 *
     * @access  public
     * @since   1.0.0 
     * @static
	 */
	public static function deactivate()
	{
        error_log(__CLASS__.'->'.__LINE__.'->'. Settings::get_plugin_dir_path());
    }
}