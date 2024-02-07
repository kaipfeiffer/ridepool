<?php

namespace Loworx\Ridepool;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


/**
 * Settings
 * 
 * define "constants" to use in the classes
 * 
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */
trait Singleton_Trait
{
    /**
     * Die Instanz der Klasse.
     * 
     * @since    1.0.0
     */
    protected static $instance = null;


	/**
	 * gets the instance via lazy initialization (created on first usage)
	 */
	public static function get_instance()
	{
		if (static::$instance === null) {
			static::$instance = new static();
		} else {
			// static::$instance::$class_name
			// error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . ' STATIC: ' . static::$instance::$class_name);
		}

		return static::$instance;
	}

	/**
	 * darf nur privat aufgerufen werden
	 */
	protected function __construct()
	{
	}

	/**darf nicht geklont werden
	 */
	private function __clone()
	{
	}

	/**
	 * prevent from being unserialized (which would create a second instance of it)
	 */
	public function __wakeup()
	{
		throw new \Exception("Cannot unserialize singleton");
	}
}
