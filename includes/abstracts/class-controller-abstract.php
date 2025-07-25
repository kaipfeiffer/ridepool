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

abstract class Controller_Abstract implements Ajax_Interface
{
    /**
     * AJAX_METHODS 
     * 
     * list of permitted functions, that can be called via Ajax
     * all requests to functions that ar not listed here are blocked
     */
    const AJAX_METHODS  = array();

    /** 
     * $nonce 
     * 
     * @var string
     */
    private $nonce;

    /**
     * class that provides database access
     * 
     * @var class
     */
    private $model;

    /**
     * $tramp_class
     * 
     * class for tramp locations
     * 
     * @var string
     */
    static protected $tramp_class = null;


    /*
    *   PROTECTED METHODS
    */

    /**
     * get_tramp_class
     * 
     * Get-Request
     * 
     * @return  string  class name of the tramp controller
     * @since    1.0.0
     */
    protected static function get_tramp_class()
    {
        if (static::$tramp_class === null) {
            $prefix = '\\Kaipfeiffer\\Tramp\\Controllers\\';
            $classname = str_replace('_', '', preg_match('/[^\\\\]+$/', static::class, $matches) ? $matches[0] : '');
            if (!$classname) {
                throw new \Exception('No classname found for ' . static::class);
            }
            static::$tramp_class = $prefix . $classname;
            call_user_func(array(static::$tramp_class, 'set_dao'), new WPDB_DAO(''));
        }
        return static::$tramp_class;
    }


    /*
    *   PUBLIC METHODS
    */

    /**
     * check
     * 
     * Check data for missings
     * 
     * @param   array   $data
     * @return  array   list of missing columns
     * @since    1.0.0
     */
    public static function check($data)
    {
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r('No tramp class found', 1));
            return;
        }

        $missings = call_user_func(array($tramp_class, 'check'), $data);
        return ($missings);
    }


    /**
     * get_columns
     *
     * @return array
     * @since 1.0.0
     */
    public static function get_columns()
    {
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r('No tramp class found', 1));
            return;
        }

        error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($tramp_class, 1));
        $location_columns = call_user_func(array($tramp_class, 'get_editable_columns'));
        return $location_columns;
    }


    /**
     * create
     * 
     * Create row
     * 
     * @param   array   $data
     * @return  integer id of the created row
     * @since    1.0.0
     */
    public static function create($data)
    {
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r('No tramp class found', 1));
            return;
        }

        $id = call_user_func(array($tramp_class, 'create'), $data);
        return ($id);
    }


    /**
     * delete
     * 
     * Delete-Request
     * 
     * @param   array	request
     * @return  array   result für json
     * @since    1.0.0
     */
    public function delete($request)
    {
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'nonce' => $this->nonce));
    }


    /**
     * read
     * 
     * Read-Request
     * 
     * @param   integer  $id
     * @param   integer  $page
     * @return  array    result für json
     * @since    1.0.0
     */
    public static function read($id = null, $page = null)
    {
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r('No tramp class found', 1));
            return;
        }

        error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($tramp_class, 1));
        $location_columns = call_user_func(array($tramp_class, 'read'), $id, $page);
        return ($location_columns);
    }


    /**
     * get
     * 
     * Get-Request
     * 
     * @param   array|object	request
     * @return  array   result für json
     * @since    1.0.0
     */
    public function get($request)
    {
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            return;
        }

        $location_columns = call_user_func(array($tramp_class, 'read'), $request['id']);
        return (array(
            'request' => $request,
            'method' => __FUNCTION__,
            'class' => __CLASS__,
            'nonce' => $this->nonce,
            'result' => $location_columns,
        ));
    }



    /**
     * is_allowed
     * 
     * checks, if the requested method could be called via ajax
     * 
     * @param string
     * @since   1.0.0
     */
    static function is_allowed(string $name)
    {
        return in_array($name, static::AJAX_METHODS);
    }


    /**
     * patch
     * 
     * Patch-Request
     * 
     * @param   array	request
     * @return  array   result für json
     * @since    1.0.0
     */
    public function patch($request)
    {
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'nonce' => $this->nonce));
    }


    /**
     * post
     * 
     * Post-Request
     * 
     * @param   array	request
     * @return  array   result für json
     * @since    1.0.0
     */
    public function post($request)
    {
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'nonce' => $this->nonce));
    }


    /**
     * put
     * 
     * Put-Request
     * 
     * @param   array	request
     * @return  array   result für json
     * @since    1.0.0
     */
    public function put($request)
    {
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'nonce' => $this->nonce));
    }

    /**
     * update
     * 
     * Update row
     * 
     * @param   integer $id
     * @param   array   $data
     * @return  boolean true on success, false on failure
     * @since    1.0.0
     */
    public static function update($data)
    {
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r('No tramp class found', 1));
            return;
        }

        $result = call_user_func(array($tramp_class, 'update'), $data);
        return ($result);
    }
}
