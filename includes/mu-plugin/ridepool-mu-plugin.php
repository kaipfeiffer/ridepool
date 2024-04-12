<?php
/**
 * Plugin Name: Ridepool Custom AJAX
 * Description: Custom Request Handler
 * Author: Kai Pfeiffer
 * Version: 1.0
 * Author URI: https://loworx.com
 */

if ( ! isset( $_GET['ridepool-router'] ) ) {
    return;
}

// Define the WordPress "DOING_AJAX" constant.
if ( ! defined( 'DOING_AJAX' ) ) {
    define( 'DOING_AJAX', true );
}

wp_send_json(
    array(
     'time'  => time()
    )
);