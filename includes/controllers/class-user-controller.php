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
     * NONCE 
     * 
     * string to create an unique nonce
     */
    const NONCE = 'loworx_user_controller_nonce';

    /**
     * $tramp_class
     * 
     * class for tramp locations
     * 
     * @var string
     */
    static protected $tramp_class = null;

    static function get_column_labels()
    {
        $columns = array(
            'title' => array(
                'type' => 'text',
                'label' => __('Title', 'ridepool')
            ),
            'familyname' => array(
                'type' => 'text',
                'label'    => __('Family name', 'ridepool')
            ),
            'givenname' => array(
                'type' => 'text',
                'label' => __('Given name', 'ridepool')
            ),
            'birthday' => array(
                'type' => 'date',
                'label'  => __('Birthday', 'ridepool')
            ),
            'email' => array(
                'type' => 'email',
                'label' => __('E-Mail', 'ridepool')
            ),
            'phone' => array(
                'type' => 'tel',
                'label' => __('Phone', 'ridepool')
            ),
            'cell' => array(
                'type' => 'tel',
                'label'  => __('Cell', 'ridepool')
            ),
            // 'id' => 'ID',
            'identity_card_number' => array(
                'type' => 'text',
                'label'  => __('Identity Card Number', 'ridepool')
            ),
            'identity_card_validity' => array(
                'type' => 'date',
                'label'    => __('Identity Card Validity', 'ridepool')
            )
        );
        return $columns;
    }

    static function get_title($row)
    {
        return sprintf('%s %s',
            $row['givenname'] ?? '',
            $row['familyname'] ?? ''
        );
    }
}
