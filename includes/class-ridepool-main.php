<?php

namespace Loworx\Ridepool;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://loworx.com
 * @since      1.0.0
 *
 * @package    Ridepool
 * @subpackage Ridepool/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ridepool
 * @subpackage Ridepool/includes
 * @author     Kai Pfeiffer <kp@loworx.com>
 */
class Ridepool_Main
{

	/*
    *   VARIABLES
    */

	/**
	 * is_loaded
	 * 
	 * @access private
	 * @since   1.0.0 
	 * @static
	 * @var     string
	 */
	static private $is_loaded = false;

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - class-autoloader.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	static private function load_dependencies()
	{

		if (!self::$is_loaded) {
			/**
			 * The Settings-Class
			 */
			require plugin_dir_path(dirname(__FILE__)) . 'includes' . DIRECTORY_SEPARATOR . 'class-settings.php';

			/**
			 * The Autoloader
			 */
			require plugin_dir_path(dirname(__FILE__)) . 'includes' . DIRECTORY_SEPARATOR . 'class-autoloader.php';

			self::$is_loaded	= true;
		}
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	static private function set_locale()
	{
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	static private function define_admin_hooks()
	{
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	static private function define_public_hooks()
	{
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	static public function run($params)
	{
		// load dependencies
		self::load_dependencies();

		// set settings
		Settings::set_plugin_dir_path($params['plugin_dir_path']);
		Settings::set_plugin_name($params['plugin_name']);
		Settings::set_plugin_text_domain($params['plugin_text_domain']);
		Settings::set_plugin_url($params['plugin_url']);
		Settings::set_plugin_version($params['plugin_version']);


		// start autoloader
		Autoloader::run();

		self::set_locale();
		self::define_admin_hooks();
		self::define_public_hooks();
	}
}
