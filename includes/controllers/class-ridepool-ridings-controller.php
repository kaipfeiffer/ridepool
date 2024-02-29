<?php
if (!defined('WPINC')) {
    die;
}

/**
 * controller for ridings
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

class Ridepool_Ridings_Controller extends Controller_Abstract
{
    /**
     * VARIABLES
     */


    /**
     * $class_name
     * 
     * @var string
     * Der Klassenname
     */
    protected static $class_name = __CLASS__;


    /**
     * $databse_class
     * 
     * @var string
     * Die Datenbank-Klasse der Route
     */
    protected static $database_class = 'Ridepool_Ridings_Model';


    /**
     * $error404
     * 
     * @var string
     * Die Route, an die Rest-Requests entgegengenommen werden
     */
    protected static $error404 = 'Es gibt keinen Zählerstand mit der ID `%d`.';


    /**
     * $target
     * 
     * @var string
     * Die Route, die Zieldatei
     */
    protected static $target = 'readings';


    /**
     * PUBLIC METHODS
     */


    /**
     * @function filter_multiple
     * 
     * prepare date before insertion
     * 
     * @param   array       array with entries to insert
     * @return  array|null  if successful, the edited array
     */
    public static function filter_multiple($params)
    {
        $unions = array();
        foreach ($params as $index => $entry) {
            $sql = sprintf(
                'SELECT
                *
                FROM
                (SELECT 
                    *
                FROM 
                    `%1$s`
                WHERE
                    `counter_id` = %2$d
                ORDER BY
                    `reading_date` DESC
                LIMIT 1) `r`',
                static::$database_class::get_tablename(),
                $entry['counter_id']
            );
            array_push($unions, $sql);
        }
        $results    = static::$database_class::raw_sql(implode("\nUNION\n", $unions));
        $res_hash   = array();
        foreach ($results as $index => $entry) {
            $res_hash[$entry['counter_id']] = $entry['reading'];
        }
        foreach ($params as $index => $entry) {
            if (!$params[$index]['consumption']) {
                $params[$index]['consumption'] = $params[$index]['reading'] > $res_hash[$entry['counter_id']] ? $params[$index]['reading'] - $res_hash[$entry['counter_id']] : 0;
            }
        }

        // error_log(__CLASS__.'->'.__LINE__.'->'.print_r($params,1));
        return $params;
    }


    /**
     * @function register_rest_route
     * 
     * register the restroute for this model
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function register_rest_route()
    {
        // Datenbank-Klasse einbinden
        // parent::register_rest_route();

        // error_log(__CLASS__.'->'.__LINE__.'->'.WP_REST_Server::EDITABLE.'->'.static::$route. '/' . static::$target . '/');
        // error_log(__CLASS__.'->'.__FUNCTION__.'-> CALLABLE: '.is_callable(__CLASS__ . '::get'));
        register_rest_route(static::$route, '/' . static::$target . '/', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => __CLASS__ . '::get',
            'permission_callback' =>  __CLASS__ . '::authenticate',
        ));
        register_rest_route(static::$route, '/' . static::$target . '/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => __CLASS__ . '::get',
            'permission_callback' =>  __CLASS__ . '::authenticate',
        ));
        register_rest_route(static::$route, '/' . static::$target . '/ctag/(?P<ctag>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => __CLASS__ . '::get',
            'permission_callback' =>  __CLASS__ . '::authenticate',
        ));
        register_rest_route(static::$route, '/' . static::$target . '/', array(
            'methods' =>  WP_REST_Server::EDITABLE,
            'callback' => __CLASS__ . '::edit',
            'permission_callback' =>  __CLASS__ . '::authenticate',
        ));
    }
}
