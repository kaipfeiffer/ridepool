<?php

namespace Loworx\Ridepool;

if (!defined('WPINC')) {
    die;
}

/**
 * Abstract Static Class for Database-Access via wpdb
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

abstract class Controller_Abstract
{
    /**
     * VARIABLES
     */


    /**
     * $auth
     * 
     * @var Kpm_Counter_Auth
     * Die Auth-Klasse der Route
     */
    protected static $auth;


    /**
     * $auth_class
     * 
     * @var string
     * Die Datenbank-Klasse der Route
     */
    protected static $auth_class = 'Auth';


    /**
     * $class_name
     * 
     * @var string
     * Der Klassenname
     */
    protected static $class_name = __CLASS__;


    /**
     * $data
     * 
     * @var array|object
     * Die Daten für die Route
     */
    protected static $data;


    /**
     * $databse_class
     * 
     * @var string
     * Die Route, die Zieldatei
     */
    protected static $database_class;


    /**
     * $route
     * 
     * @var string
     * Die Route, an die Rest-Requests entgegengenommen werden
     */
    protected static $route = 'kpm-counter/v1';


    /**
     * $target
     * 
     * @var string
     * Die Route, die Zieldatei
     */
    protected static $target;


    /**
     * PRIVATE METHODS
     */


    protected static function include_database_class()
    {
        $class =  Settings::get_plugin_dir_path() . 'includes/classes/models/class-' . str_replace('_', '-', strtolower(static::$database_class)) . '.php';

        // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> CLASS: ' . $class);
        require_once $class;
    }


    /**
     * PUBLIC METHODS
     */


    /**
     * @function authenticate
     * 
     * delete a row of the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function authenticate($request)
    {
        $class =  Settings::get_plugin_dir_path() . 'includes/singletons/class-' . str_replace('_', '-', strtolower(static::$auth_class)) . '.php';

        require_once $class;

        static::$auth   = static::$auth_class::get_instance('');

        $result   = static::$auth->authenticate($request);

        // error_log(__CLASS__ . '->' . __FUNCTION__ . '-> CLASS: ' . static::$class_name . '->' . print_r($result, 1));
        // error_log(__CLASS__.'->'.__FUNCTION__.'-> Class_name: '.print_r(static::$class_name,1));
        if (isset($result['message']) && 'Access granted:' === $result['message']) {
            static::$data = $result['data'];
            if (class_exists(static::$database_class)) {
                static::$database_class::user($result['data']->counter_user);
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * @function register_rest_route
     * 
     * register the restroute for this model
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function register_rest_route()
    {
        static::include_database_class();

        register_rest_route(static::$route, '/' . static::$target . '/', array(
            'methods' => 'POST',
            'callback' => static::$class_name . '::post',
            'permission_callback' =>  __CLASS__ . '::authenticate',
        ));
    }


    /**
     * @function delete
     * 
     * delete a row of the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function delete(WP_REST_Request $request)
    {
    }


    /**
     * @function edit
     * 
     * delete a row of the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function edit(WP_REST_Request $request)
    {
        // error_log(__CLASS__ . '->' .__LINE__.'->'.print_r($request,1));
        $method = $request->get_method();
        switch ($method) {
                // Neuen Eintrag speichern
            case 'POST': {
                    return static::post($request);
                    break;
                }
            case 'PUT': {
                    return static::put($request);
                    break;
                }
            case 'PATCH': {
                    return static::patch($request);
                    break;
                }
        }
    }


    /**
     * @function filter_multiple
     * 
     * prepare date before insertion
     * 
     * @param   array       array with entries to insert
     * @return  array|null  if successful, the edited array
     */
    public static function filter_multiple($params)
    {    
        return $params;
    }


    /**
     * @function get
     * 
     * get a row from the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function get(WP_REST_Request $request)
    {
        // error_log(__CLASS__.'->'.__FUNCTION__.'-> Class_name: '.print_r(static::$class_name,1));
        // error_log(__CLASS__.'->'.__FUNCTION__.'-> STAT-DATA: '.print_r(static::$data,1));
        // error_log(__CLASS__.'->'.__FUNCTION__.'-> SELF-DATA: '.print_r($request['page'],1));
        // error_log(__CLASS__.'->'.__FUNCTION__.'-> REQUEST: '.print_r($request,1));

        $page = $request['page'] ? $request['page'] : 0;
        $page_size = $request['page_size'] ? $request['page_size'] : null;


        if ($request['id']) {
            $result = static::$database_class::read($request['id']);

            if (!$result) {
                $error = new \WP_Error(
                    'rest_post_invalid_id',
                    sprintf(__(static::$error404, ''), $request['id']),
                    array('status' => 404)
                );
                return $error;
            }
        } elseif ($request['ctag']) {
            $result = static::$database_class::user(static::$data->counter_user)::read(['ctag' => ['value' => $request['ctag'], 'operator' => '>']], false, $page, $page_size);
        } elseif ($request['filters']) {
            $result = static::$database_class::read($request['filters'], null, $page, $page_size);

            if (!$result) {
                $error = new \WP_Error(
                    'rest_post_invalid_id',
                    __(static::$errorMulti404, ''),
                    array('status' => 404)
                );
                return $error;
            }
        } else {
            $result = static::$database_class::user(static::$data->counter_user)::get($page, $page_size);
        }

        return $result;
    }


    /**
     * @function patch
     * 
     * modify a row of the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function patch(WP_REST_Request $request)
    {
        return ['message' => 'PATCH Method'];
    }


    /**
     * @function create
     * 
     * add a new row to the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function post(WP_REST_Request $request)
    {
        $params = $request->get_params();
        // error_log(__CLASS__ . '->' . __LINE__ . '-> PARAMS:' . print_r($params, 1));
        if ($params) {
            if (isset($params[static::$database_class::get_primary()]) && 0 < $params[static::$database_class::get_primary()]) {

                // error_log(__CLASS__ . '->' . __LINE__ . '->UPDATE');
                $result = static::$database_class::update($params);
            } elseif (array_is_list($params)) {
                // error_log(__CLASS__ . '->' . __LINE__ . '->MULTIPLE');
                if (is_callable(array(static::$class_name, 'filter_multiple'))) {
                    $params = static::filter_multiple($params);
                }
                $result = static::$database_class::user(static::$data->counter_user)::create_multi($params);
            } else {
                // error_log(__CLASS__ . '->' . __LINE__ . '->CREATE');
                $result = static::$database_class::create($params);
            }
            error_log(__CLASS__ . '->' . __LINE__ . '->CREATE:'.print_r($result,1));
            if ($result) {

                return $result;
            } else {
                $error = new \WP_Error(
                    'rest_post_invalid_id',
                    __(static::$errorOnSave, ''),
                    array('status' => 404)
                );
                return $error;
            }
        } else {
            $error = new \WP_Error(
                'rest_post_invalid_id',
                __(static::$errorMissingData, ''),
                array('status' => 404)
            );
            return $error;
        }
    }


    /**
     * @function put
     * 
     * save a row to the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function put(WP_REST_Request $request)
    {
        $params = $request->get_params();
        if (isset($params[static::$database_class::get_primary()]) && 0 < $params[static::$database_class::get_primary()]) {

            error_log(__CLASS__ . '->' . __LINE__ . '->UPDATE');
            $result = static::$database_class::user(static::$data->counter_user)::update($params);
        }
        if ($result) {
            return $result;
        } else {
            $error = new \WP_Error(
                'rest_post_invalid_id',
                __(static::$errorOnSave, ''),
                array('status' => 404)
            );
            return $error;
        }
    }
}
