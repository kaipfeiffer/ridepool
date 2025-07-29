<?php

namespace Loworx\Ridepool;

if (!defined('WPINC')) {
    exit;
} // Exit if accessed directly


/**
 * 
 *
 * @class        
 * @version        1.0.0
 * @author        Kai Pfeiffer
 */
class User_Subpage extends Admin_Subpage_Abstract{

    const ADMIN_SUBPAGE_SLUG = 'user';

    const CLASS_NAME    = __CLASS__;

    const NONCE = 'User_Subpage_Nonce';

    static function get_title()
    {
        return __('Users','ridepool');
    }

    static function get_plural()
    {
        return __('Users','ridepool');
    }

    static function get_singlular()
    {
        return __('User','ridepool');
    }

    static function get_page_title()
    {
        return __('Edit Users','ridepool');
    }
}