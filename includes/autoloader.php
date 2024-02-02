<?php

namespace Ridepool;

if (!defined('WPINC')) {
	die;
}

/**
 * Ridepool autoloader.
 *
 * Ridepool autoloader handler class is responsible for loading the different
 * classes needed to run the plugin.
 *
 * @since 1.0.0
 */
class Autoloader
{

	use Settings;

	/**
	 * Classes map.
	 *
	 * Maps Ridepool classes to file names.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var array Classes used by elementor.
	 */
	private static $classes_map;

	/**
	 * Default namespace for autoloader.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var string
	 */
	private static $default_namespace;

	/**
	 * Default path for autoloader.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var string
	 */
	private static $default_path;


	/**
	 * Run autoloader.
	 *
	 * Register a function as `__autoload()` implementation.
	 *
	 * @access public
	 * 
	 * @param string
	 * 
	 * @since 1.0.0
	 * @static
	 */
	public static function run($default_path = '', $default_namespace = '')
	{
		if ('' === $default_path) {
			$default_path = Settings::get_plugin_dir_path();
		}
		if ('' === $default_namespace) {
			$default_namespace = Settings::get_plugin_name_space();
		}

		self::$default_namespace = $default_namespace;
		self::$default_path = $default_path;

		spl_autoload_register([__CLASS__, 'autoload']);
	}

	/**
	 * Get classes aliases.
	 *
	 * retrieve the classes aliases names.
	 *
	 * @access public
	 * 
	 * @return array
	 * 
	 * @since 1.0.0
	 * @static
	 *
	 */
	public static function get_classes_map()
	{
		if (!self::$classes_map) {
			self::$classes_map = array(
				'Activator' => 'includes/class-activator.php',
				'Deactivator' => 'includes/class--deactivator.php',
				'Settings' => 'includes/traits/trait-settings.php',
			);
		}

		return self::$classes_map;
	}

	/**
	 * Load class.
	 *
	 * For a given class name, require the class file.
	 *
	 * @access private
	 * @since 1.0.0
	 * @static
	 *
	 * @param string
	 */
	private static function load_class($relative_class_name)
	{
		$filename		= '';
		$classes_map = self::get_classes_map();

		if (isset($classes_map[$relative_class_name])) {
			$filename = self::$default_path . $classes_map[$relative_class_name];
		}
		error_log($relative_class_name.'->'. $filename);

		if (is_readable($filename)) {
			error_log($relative_class_name.'->'. $filename);
			require $filename;
		}
	}

	/**
	 * Autoload.
	 *
	 * For a given class, check if it exist and load it.
	 *
	 * @since 1.6.0
	 * @access private
	 * @static
	 *
	 * @param string
	 */
	private static function autoload($class)
	{

		$relative_class_name = preg_replace('/^' . self::$default_namespace . '\\\/', '', $class);

		$class_name = self::$default_namespace . '\\' . $relative_class_name;

		if (!class_exists($class_name)) {
			self::load_class($relative_class_name);
		}
	}
}
