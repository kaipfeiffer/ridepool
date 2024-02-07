<?php

namespace Loworx\Ridepool;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


/**
 * Settings
 * 
 * define settings to use in the classes
 * 
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */
class Settings
{

    /*
    *   VARIABLES
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
    static private $plugin_name;

    /**
     * plugin text domain
     * 
     * @access private
     * @since   1.0.0 
     * @static
     * @var     string 
     */
    static private $plugin_text_domain;

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
    static private $plugin_version;


    /*
    *   PUBLIC FUNCTIONS
    */

    /**
     * set_plugin_dir_path
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * @param string
     */
    static function set_plugin_dir_path($plugin_dir_path)
    {
        if (!self::$plugin_dir_path) {
            self::$plugin_dir_path  = $plugin_dir_path;
        }
    }

    /**
     * get_plugin_dir_path
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * @return string
     */
    static function get_plugin_dir_path()
    {
        return self::$plugin_dir_path;
    }

    /**
     * set_plugin_name
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * @param  string
     */
    static function set_plugin_name($plugin_name)
    {
        if (!self::$plugin_name) {
            self::$plugin_name  = $plugin_name;
        }
    }

    /**
     * get_plugin_name
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * @return  string
     */
    static function get_plugin_name()
    {
        return self::$plugin_name;
    }

    /**
     * set_plugin_text_domain
     * 
     * @access public
     * @since   1.0.0 
     * @static 
     * @param  string
     */
    static function set_plugin_text_domain($plugin_text_domain)
    {
        if (!self::$plugin_text_domain) {
            self::$plugin_text_domain  = $plugin_text_domain;
        }
    }

    /**
     * get_plugin_text_domain
     * 
     * @access public
     * @since   1.0.0 
     * @static 
     * @return  string
     */
    static function get_plugin_text_domain()
    {
        return self::$plugin_text_domain;
    }

    /**
     * set_plugin_url
     * 
     * @access public
     * @since   1.0.0 
     * @static 
     * @param  string
     */
    static function set_plugin_url($plugin_url)
    {
        if (!self::$plugin_url) {
            self::$plugin_url  = $plugin_url;
        }
    }

    /**
     * get_plugin_url
     * 
     * @access public
     * @since   1.0.0 
     * @static 
     * @return  string
     */
    static function get_plugin_url()
    {
        return self::$plugin_url;
    }

    /**
     * set_plugin_version
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * @param  string
     */
    static function set_plugin_version($plugin_version)
    {
        if (!self::$plugin_version) {
            self::$plugin_version  = $plugin_version;
        }
    }

    /**
     * get_plugin_version
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * @return  string
     */
    static function get_plugin_version()
    {
        return self::$plugin_version;
    }
}
