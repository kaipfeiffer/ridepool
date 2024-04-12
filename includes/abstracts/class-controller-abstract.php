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
     * get_class
     * 
     * erstellt beim ersten Aufruf eine Instanz der benötigten Klasse und
     * gibt diese Instanz zurück
     * 
     * @param   
     * @return  
     * @since    1.0.0
     */
    function __construct()
    {
        $this->nonce    = strtolower(preg_replace('/\W+/','_',static::class).'_nonce');   
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
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'nonce' => $this->nonce));
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
}
