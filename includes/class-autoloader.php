<?php

namespace Loworx\Ridepool;

if (!defined('WPINC')) {
	die;
}

/**
 * Ridepool autoloader.
 *
 * Ridepool autoloader handler class is responsible for loading the different
 * classes needed to run the plugin.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since 1.0.0
 */
class Autoloader
{
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
	 * Default namespace regex for autoloader.
	 *
	 * escaped regular expression avoid signal characters through backslashes in the namespace in regexes
	 * 
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var string
	 */
	private static $default_namespace_regex;

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
			$default_namespace = __NAMESPACE__;
		}

		self::$default_namespace_regex	= str_replace('\\', '\\\\', $default_namespace);
		self::$default_namespace		= $default_namespace;
		self::$default_path				= $default_path;

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
				'Activator'		=> 'includes' . DIRECTORY_SEPARATOR . 'class-activator.php',
				'Deactivator'	=> 'includes' . DIRECTORY_SEPARATOR . 'class-deactivator.php',
			);
		}

		return self::$classes_map;
	}

	/**
	 * get_file_name
	 *
	 * For a given class name, retrieve the filename for require
	 *
	 * @since 1.0.65
	 * @access private
	 * @static
	 *
	 * @param string $class_name Class name.
	 */
	private static function get_file_name($class_name)
	{
		$file_name 			= str_replace('_', '-', strtolower($class_name));

		// Resources
		if (str_ends_with($file_name, 'resource')) {
			return self::$default_path . 'includes' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'class-' . $file_name . '.php';
		}
		// Singletons
		if (str_ends_with($file_name, 'singleton')) {
			// error_log(__CLASS__ . '->' . __LINE__ . '->' . $file_name.'->'.self::$default_path . '/includes/singletons/class-' . $file_name . '.php');
			return self::$default_path . 'includes' . DIRECTORY_SEPARATOR . 'singletons' . DIRECTORY_SEPARATOR . 'class-' . $file_name . '.php';
		}
		// Tables
		if (str_ends_with($file_name, 'table')) {
			return self::$default_path . 'includes' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR . 'class-' . $file_name . '.php';
		}
		// Traits
		if (str_ends_with($file_name, 'trait')) {
			return self::$default_path . 'includes' . DIRECTORY_SEPARATOR . 'traits' . DIRECTORY_SEPARATOR . 'class-' . $file_name . '.php';
		}
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
		} else {
			$filename = self::get_file_name($relative_class_name);
		}


		if (is_readable($filename)) {
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
		// terminate method, if namespace doesn't match
		if ( 0 !== strpos( $class, self::$default_namespace . '\\' ) ) {
			return;
		}
		// error_log(__CLASS__.'->'.__LINE__.'->'.$regex);
		$relative_class_name = preg_replace('/^' . self::$default_namespace_regex . '\\\/', '', $class);

		$class_name = self::$default_namespace . '\\' . $relative_class_name;

		// error_log(__CLASS__ . '->' . __LINE__ . '->' . $class . '->' . $relative_class_name . '->' . self::$default_namespace);
		if (!class_exists($class_name)) {
			self::load_class($relative_class_name);
		}
	}
}
