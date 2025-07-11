<?php

namespace Loworx\Ridepool;

if (!defined('WPINC')) {
    die;
}

/**
 * Abstract Static Class for Database-Access via wpdb
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

abstract class Base_Logger_Abstract
{

	/**
	 * logger
	 * 
	 * @access private
	 * @since   1.0.0 
	 * @static
	 * @var     Logger_Singleton
	 */
	static private $logger_instance;


    /**
     * Use the logger instance.
     *
     * @return Logger_Singleton
     */
    static public function use_logger()
    {
        if (null === self::$logger_instance) {
            self::$logger_instance = Logger_Singleton::get_instance(true);
        }
        // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . 'LOGGER INSTANCE: ' );
        return self::$logger_instance;
    }
}
