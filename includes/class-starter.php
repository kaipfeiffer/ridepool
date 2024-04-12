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
class Starter
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
	 * logger
	 * 
	 * @access private
	 * @since   1.0.0 
	 * @static
	 * @var     Logger_Singleton
	 */
	static private $logger;

	/**
	 * admin_classes
	 * 
	 * classes that provide hooks for the admin
	 * 
	 * @access private
	 * @since   1.0.0 
	 * @static
	 * @var     array
	 */
	static private $admin_classes = array();

	/**
	 * frontend_classes
	 * 
	 * classes that provide hooks for the frontend
	 * 
	 * @access private
	 * @since   1.0.0 
	 * @static
	 * @var     array
	 */
	static private $frontend_classes = array();

	/**
	 * json_classes
	 * 
	 * classes that provide json hooks
	 * 
	 * @access private
	 * @since   1.0.0 
	 * @static
	 * @var     array
	 */
	static private $json_classes = array();

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
			 * The singleton-trait
			 */
			require plugin_dir_path(dirname(__FILE__)) . 'includes' . DIRECTORY_SEPARATOR . 'traits' . DIRECTORY_SEPARATOR . 'class-singleton-trait.php';

			/**
			 * The Logger-Class
			 */
			require plugin_dir_path(dirname(__FILE__)) . 'includes' . DIRECTORY_SEPARATOR . 'singletons' . DIRECTORY_SEPARATOR . 'class-logger-singleton.php';

			/**
			 * The Settings-Class
			 */
			require plugin_dir_path(dirname(__FILE__)) . 'includes' . DIRECTORY_SEPARATOR . 'class-settings.php';

			/**
			 * The Autoloader
			 */
			require plugin_dir_path(dirname(__FILE__)) . 'includes' . DIRECTORY_SEPARATOR . 'class-autoloader.php';

			/**
			 * Provide new php-methods
			 */
			if (phpversion() < '8') {
				require plugin_dir_path(dirname(__FILE__)) . 'includes' . DIRECTORY_SEPARATOR . 'compatibility' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'php8-functions.php';
			}

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
		static::$logger->log('Define');
		if (wp_is_json_request()) {
			foreach (static::$json_classes  as $class) {
				static::$logger->log('Exists:' . $class . '->' . class_exists($class) . '<-');
				static::$logger->log($class . '->' . is_callable(array($class, 'init_json')) . '<-');
				if (is_callable(array($class, 'init_json'))) {
					call_user_func(array($class, 'init_json'),static::$logger);
				}
			}
		}
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

		self::$logger	= Logger_Singleton::get_instance(true);
		static::$logger->log('Define');

		// set settings
		Settings::set_plugin_dir_path($params['plugin_dir_path']);
		Settings::set_plugin_name($params['plugin_name']);
		Settings::set_plugin_text_domain($params['plugin_text_domain']);
		Settings::set_plugin_url($params['plugin_url']);
		Settings::set_plugin_version($params['plugin_version']);

		// start autoloader
		Autoloader::run();

		// classes that provide admin hooks
		static::$admin_classes = array(
			// Webapp_Controller::class => true,	// Öffentliche JSON Schnittstelle
		);

		// classes that provide frontend hooks
		static::$frontend_classes = array(
			// Webapp_Controller::class => true,	// Öffentliche JSON Schnittstelle
		);

		//  classes that provide json hooks
		static::$json_classes = array(
			Routing_Handler::class,
		);

		self::set_locale();
		self::define_admin_hooks();
		self::define_public_hooks();
	}
}
