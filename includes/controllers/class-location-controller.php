<?php

namespace Loworx\Ridepool;
if (!defined('WPINC')) {
    die;
}

use \Kaipfeiffer\Tramp\Controllers\LocationController;

/**
 * controller for locations
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

class Location_Controller extends Controller_Abstract
{
    /**
     * AJAX_METHODS 
     * 
     * list of permitted functions, that can be called via Ajax
     * all requests to functions that ar not listed here are blocked
     */
    const AJAX_METHODS  = array('get','post');

    
    /** 
     * NONCE 
     * 
     * string to create an unique nonce
     */
    const NONCE = 'loworx_riding_controller_nonce';

    /**
     * $tramp_class
     * 
     * class for tramp locations
     * 
     * @var string
     */
    static protected $tramp_class = null;


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
    public function post($request)
    {
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            return;
        }
        $hi     = call_user_func(array($tramp_class, 'create'), array(
            'city'      => 'Gudensberg',
            'zipcode'   => '34281',
            'street'    => 'Mühlenweg 2',
            'format'    => 'json',
        ));
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'hi'=> $hi));
    }
}
