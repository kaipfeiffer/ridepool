<?php

namespace Loworx\Ridepool;
if (!defined('WPINC')) {
    die;
}

use \Kaipfeiffer\Tramp\Controllers\UserController;

/**
 * controller for locations
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

class User_Controller extends Controller_Abstract
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
}
