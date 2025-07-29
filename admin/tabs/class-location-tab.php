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
class Location_Tab extends Admin_Tab_Abstract implements Ajax_Interface{

    const ADMIN_TAB_SLUG = 'location';

    const ADMIN_TAB_LIB = 'location-tab';

    static function get_title()
    {
        return __('Locations','ridepool');
    }
}