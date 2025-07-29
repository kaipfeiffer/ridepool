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
