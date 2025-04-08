<?php

namespace Loworx\Ridepool;


/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Ridepool
 * @subpackage Ridepool/includes
 * @author     Kai Pfeiffer <kp@loworx.com>
 */
class Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	static protected $actions = array();

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	static protected $filters = array();

	/**
	 * The array of methods registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $methods 
	 */
	static protected $methods = array();


	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	static public function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		static::$actions = static::add( static::$actions, $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	static public function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		static::$filters = static::add( static::$filters, $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	static private function add( $hooks, $hook, $callback, $priority, $accepted_args ) {
        $hook_hash  = md5(json_encode(array('hook' => $hook, 'callback' => $callback)));
        static::$methods[$hook_hash]    = $callback;
		$hooks[] = array(
			'hook'          => $hook,
			// 'callback'      => array(static::class, $hook_hash),
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( static::$filters as $hook ) {
			add_filter( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( static::$actions as $hook ) {
			add_action( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
		}

	}

}