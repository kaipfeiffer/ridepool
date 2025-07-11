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
class Autoloader extends Base_Logger_Abstract
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
				'Activator'		=> implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'class-activator.php')
				),
				'Admin'		=> implode(
					DIRECTORY_SEPARATOR,
					array('admin', 'class-admin.php')
				),
				'Deactivator'	=> implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'class-deactivator.php')
				),
				'Sanitize'		=> implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'helpers', 'class-sanitize-helper.php')
				),
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

		// abstracts
		if (str_ends_with($file_name, 'abstract')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'abstracts', 'class-' . $file_name . '.php')
			);
		}
		// Controllers
		if (str_ends_with($file_name, 'controller')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'controllers', 'class-' . $file_name . '.php')
			);
		}
		// Controllers
		if (str_ends_with($file_name, 'cpt')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'custom-post-types', 'class-' . $file_name . '.php')
			);
		}
		// Dao
		if (str_ends_with($file_name, 'dao')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'dao', 'class-' . $file_name . '.php')
			);
		}
		// Handlers
		if (str_ends_with($file_name, 'handler')) {
			// self::use_logger()->log('->' . $file_name.'->'.self::$default_path . '/includes/singletons/class-' . $file_name . '.php');
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'handlers', 'class-' . $file_name . '.php')
			);
		}
		// Interfaces
		if (str_ends_with($file_name, 'interface')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'interfaces', 'class-' . $file_name . '.php')
			);
		}
		// Models
		if (str_ends_with($file_name, 'model')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'models', 'class-' . $file_name . '.php')
			);
		}
		// Resources
		if (str_ends_with($file_name, 'resource')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'resources', 'class-' . $file_name . '.php')
			);
		}
		// Singletons
		if (str_ends_with($file_name, 'singleton')) {
			// self::use_logger()->log('->' . $file_name.'->'.self::$default_path . '/includes/singletons/class-' . $file_name . '.php');
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'singletons', 'class-' . $file_name . '.php')
			);
		}
		// Tables
		if (str_ends_with($file_name, 'table')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'tables', 'class-' . $file_name . '.php')
			);
		}
		// Traits
		if (str_ends_with($file_name, 'trait')) {
			return self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'traits', 'class-' . $file_name . '.php')
			);
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
		if (0 !== strpos($class, self::$default_namespace . '\\')) {
			return;
		}

		$relative_class_name = preg_replace('/^' . self::$default_namespace_regex . '\\\/', '', $class);

		$class_name = self::$default_namespace . '\\' . $relative_class_name;

		static::use_logger()->log('->' . $class . '->' . $relative_class_name . '->' . self::$default_namespace);

		if (!class_exists($class_name)) {
			self::load_class($relative_class_name);
		}
	}
}
