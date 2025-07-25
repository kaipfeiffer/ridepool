<?php

namespace Loworx\Ridepool;
if (!defined('WPINC')) {
    die;
}

use \Kaipfeiffer\Tramp\Controllers\RidingController;

/**
 * controller for ridings
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

class Riding_Controller extends Controller_Abstract
{
    /**
     * AJAX_METHODS 
     * 
     * list of permitted functions, that can be called via Ajax
     * all requests to functions that ar not listed here are blocked
     */
    const AJAX_METHODS  = array('get');
    

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
        $tramp_class = static::get_tramp_class();
        if ($tramp_class === null) {
            return;
        }
        
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__));
    }
}
