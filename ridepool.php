<?php

namespace Loworx;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://loworx.com
 * @since             1.0.0
 * @package           ridepool
 *
 * @wordpress-plugin
 * Plugin Name:       Ride-Pool
 * Plugin URI:        https://loworx.com
 * Description:       Ridepool is a wordpress plugin, that connects local rides with local co-riders to reduce traffic and carbon dioxide emissions.
 * Version:           1.0.0
 * Author:            Kai Pfeiffer
 * Author URI:        https://loworx.com
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       ridepool
 * Domain Path:       /languages
 * Requires PHP:      7.3
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

require plugin_dir_path( __FILE__ ) . 'includes/class-starter.php';

/**
 * Main class to bootstrap the plugin
 *
 * @author     Kai Pfeiffer <kp@loworx.com>
 * @package    ridepool
 * @since      1.0.0
 */
class Ridepool
{
    /**
     * activate
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * define the hook to activate the plugin
     */
    static function activate()
    {
        Ridepool\Activator::activate();
    }

    /**
     * deactivate
     * 
     * @access public
     * @since   1.0.0 
     * @static
     * define the hook to deactivate the plugin
     */
    static function deactivate()
    {
        Ridepool\Deactivator::deactivate();
    }

    // register_deactivation_hook(__FILE__, 'deactivate_sbu_wc_handout');

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @access public
     * @since   1.0.0 
     * @static
     */
    static function start()
    {
        register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
        
        register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));
        
		Ridepool\Starter::run(array(
            'plugin_dir_path'       => plugin_dir_path( __FILE__ ),
            'plugin_name'           => 'pcm-flipbook',
            'plugin_text_domain'    => 'pcm-flipbook',
            'plugin_url'            => plugin_dir_url(__FILE__),
            'plugin_version'        => '1.0.0',
        ));
    }
}
Ridepool::start();
