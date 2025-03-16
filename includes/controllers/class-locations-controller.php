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

class Locations_Controller extends Controller_Abstract
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
        \Kaipfeiffer\Tramp\Controllers\LocationController::set_dao(new WPDB_DAO('locations'));
        $hi     = \Kaipfeiffer\Tramp\Controllers\LocationController::create(array(
            'city'      => 'Gudensberg',
            'zipcode'   => '34281',
            'street'    => 'Mühlenweg 2',
            'format'    => 'json',
        ));
        return (array('request' => $request, 'method' => __FUNCTION__, 'class' => __CLASS__, 'hi'=> $hi));
    }
}
