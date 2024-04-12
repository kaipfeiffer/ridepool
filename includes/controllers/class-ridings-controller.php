<?php

namespace Loworx\Ridepool;
if (!defined('WPINC')) {
    die;
}

/**
 * controller for ridings
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

class Ridings_Controller extends Controller_Abstract
{
    /** 
     * NONCE 
     * 
     * string to create an unique nonce
     */
    const NONCE = 'loworx_riding_controller_nonce';

}
