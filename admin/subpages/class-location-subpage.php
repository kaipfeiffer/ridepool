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
class Location_Subpage extends Admin_Subpage_Abstract{

    const ADMIN_SUBPAGE_SLUG = 'location';

    const CLASS_NAME    = __CLASS__;

    const NONCE = 'Location_Subpage_Nonce';


    static function get_plural()
    {
        return __('Location','ridepool');
    }

    static function get_singlular()
    {
        return __('Locations','ridepool');
    }

        static function get_page_title()
        {
            return __('Edit Locations','ridepool');
        }

    static function get_title()
    {
        return __('Locations','ridepool');
    }
}