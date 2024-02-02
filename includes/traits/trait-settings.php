<?php

namespace Ridepool;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


/**
 * Settings
 * 
 * define "constants" to use in the classes
 * 
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */
trait Settings
{
    /*
    *   PRIVATE VARIABLES
    */

    /**
     * plugin dir path
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @var     string
     */
    static private $plugin_dir_path;

    /**
     * plugin name
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @var     string 
     */
    static private $plugin_name     = 'ridepool';

    /**
     * plugin name_space
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @var     string 
     */
    static private $plugin_name_space     = 'Ridepool';

    /**
     * plugin url
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @var     string 
     */
    static private $plugin_url;

    /**
     * current plugin version.
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @var     string
     */
    static private $plugin_version  = '1.0.0';


    /*
    *   PUBLIC FUNCTIONS
    */

    /**
     * get_plugin_dir_path
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @return  string
     */
    static function get_plugin_dir_path()
    {
        if (!self::$plugin_dir_path) {
            $plugin_dir_path        = plugin_dir_path(__FILE__);
            error_log(__CLASS__.'->'.__LINE__.'->'.$plugin_dir_path);
            $plugin_dir_path        = str_replace('includes'.DIRECTORY_SEPARATOR.'traits'.DIRECTORY_SEPARATOR,'',$plugin_dir_path);
            error_log(__CLASS__.'->'.__LINE__.'->'.$plugin_dir_path);
            self::$plugin_dir_path  = $plugin_dir_path;
        }
        return self::$plugin_dir_path;
    }

    /**
     * get_plugin_name
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @return  string
     */
    static function get_plugin_name()
    {
        return self::$plugin_name;
    }

    /**
     * get_plugin_name_space
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @return  string
     */
    static function get_plugin_name_space()
    {
        return self::$plugin_name_space;
    }

    /**
     * get_plugin_url
     * 
     * @access private
     * @since   1.0.0 
     * @static 
     * @return  string
     */
    static function get_plugin_url()
    {
        if (!self::$plugin_dir_path) {
            $plugin_url        = plugin_dir_url(__FILE__);
            error_log(__CLASS__.'->'.__LINE__.'->'.$plugin_url);
            $plugin_url        = str_replace('include'.DIRECTORY_SEPARATOR.'traits'.DIRECTORY_SEPARATOR,'',$plugin_url);
            error_log(__CLASS__.'->'.__LINE__.'->'.$plugin_url);
            self::$plugin_url  = $plugin_url;
        }
        return self::$plugin_url;
    }

    /**
     * get_plugin_version
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @return  string
     */
    static function get_plugin_version()
    {
        return self::$plugin_version;
    }
}
