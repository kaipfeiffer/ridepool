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
        // $hi     = \Kaipfeiffer\Tramp\Tramp::hello('');
        $hi     = \Kaipfeiffer\Tramp\Tramp::hello(new WPDB_DAO('test'));
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'hi'=> $hi));
    }
}
