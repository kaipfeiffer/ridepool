<?php

namespace Loworx\Ridepool;

if (!defined('WPINC')) {
    die;
}

require_once Settings::get_plugin_dir_path() . 'includes/abstracts/abstract-kpm-counter-model.php';
/**
 * Abstract Static Class for Database-Access via wpdb
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

abstract class Model_Filtered_Abstract extends Model_Abstract
{
    /**
     * VARIABLES
     */

    /**
     * @var string class
     * 
     * diese Variable muss in der abgeleiteten Klasse mit dem Inhalte der Konstanten __CLASS__
     * belegt werden, damit die Instanzen richtig funktionieren
     */
    protected static $class_name = __CLASS__;


    /**
     * $operators
     * 
     * die Operatoren für die Vergleiche in Where Statements
     * 
     * @var integer
     */
    protected static $operators;

    /**
     * $user
     * 
     * die User-ID
     * 
     * @var integer
     */
    protected static $user;

    /**
     * $user_column
     * 
     * die Spalte, die die User-Informationen beinhaltet
     * 
     * @var integer
     */
    protected static $user_column;


    /**
     * $user_primary
     * the name of the primary index of the user-table
     * 
     * @var string
     */
    protected static $user_primary = 'id';


    /**
     * $user_related_ids
     * 
     * Zulässige IDs, die in der Spalte $user_column vorkommen dürfen
     * 
     * @var string
     */
    protected static $user_related_ids;


    /**
     * $user_table_key
     * 
     * the key field which is identifies the user in the user-table
     * 
     * @var string
     */
    protected static $user_table_key;


    /**
     * $user_table_name
     * the name of the table with the user-id without wp-prefix
     * 
     * @var string
     */
    protected static $user_table_name;


    /**
     * PRIVATE METHODS
     */


    /**
     * @function get_user
     * 
     * gets the user id
     * 
     * @return string|int  the user ID
     */
    protected static function get_user()
    {
        return static::$user;
    }


    /**
     * @function get_user_related_ids
     * 
     * gets the ids of the rows belongeing to the user
     * 
     * @return array the matched ids
     */
    protected static function get_user_related_ids($prefix = '')
    {
        global $wpdb;

        // if there are no $user_related_ids present
        if (!static::$user_related_ids) {
            // isf ther is a key to a different table
            if (isset(static::$user_table_key)) {
                $ids    = array();

                $sql    = sprintf(
                    'SELECT
                    `%1$s`
                FROM
                    `%2$s`
                WHERE
                    `%3$s` = "%4$s";',
                    static::$user_primary,
                    static::get_tablename(static::$user_table_name),
                    static::$user_table_key,
                    static::$user
                );

                $result = $wpdb->get_results($sql,ARRAY_A);
                foreach ($result as $row) {
                    array_push($ids, $row[static::$user_primary]);
                }
                static::$user_related_ids = $ids;
            }
            else{
                static::$user_related_ids = array(static::$user);
            }
        }
        return static::$user_related_ids;
    }


    /**
     * @function get_user_query
     * 
     * gets the query string, wchich identify columns that belong to the user
     * 
     * @return string  the query string
     */
    protected static function get_user_query($prefix = '')
    {
        $prefix         = '' < $prefix ? '`' . $prefix . '`.' : '';
        if (
            isset(static::$user_table_name) && '' < static::$user_table_name &&
            isset(static::$user_table_key) && '' < static::$user_table_key &&
            isset(static::$user_primary) && '' < static::$user_primary
        ) {
            $ids        = static::get_user_related_ids();
            $user_query = sprintf($prefix . '`%1$s` IN ("%2$s") ', static::$user_column, implode('","', $ids));
        } else {
            $user_query = '%d' === static::$columns[static::$user_column] ?
                $prefix . '`%1$s`= %2$d ' :
                sprintf($prefix . '`%%1$s`= "%s" ', static::$columns[static::$user_column]);
            $user_query = sprintf($user_query, static::$user_column, static::$user);
        }

        return $user_query;
    }




    /**
     * PUBLIC METHODS
     */

    /**
     * @function create
     * 
     * add a new row to the table
     * 
     * @param   array       associative array with key => value pairs for insertion
     * @return  array|null  if successful, the stored data row
     */
    public static function create($columns)
    {
        $allowed_ids    = static::get_user_related_ids();
        $result         = null;


        if (in_array($columns[static::$user_column], $allowed_ids)) {
            $result  = parent::create($columns);
        }
        elseif(in_array(static::$user, $allowed_ids) && !(isset(static::$user_table_name))){
            $columns[static::$user_column] = static::$user;
            $result  = parent::create($columns);
        }

        return $result;
    }


    /**
     * @function create_multi
     * 
     * add multiple new rows to the table
     * 
     * @param   array       array with rows to insert
     * @return  int|null    if successful, the number of inserted records
     */
    public static function create_multi($rows)
    {
        $allowed_rows   = array();
        $allowed_ids    = static::get_user_related_ids();
        $result         = null;

        error_log(__CLASS__ . '->' . __FUNCTION__ . '->$allowed_ids ' . print_r($allowed_ids, 1) . ' ' . static::$user_column);
        // Alle Zeilen durchgehen und nur die passenden Keys auslesen
        foreach ($rows as $row) {

            error_log(__CLASS__ . '->' . __FUNCTION__ . '->id ' . $row[static::$user_column]);
            if (in_array($row[static::$user_column], $allowed_ids)) {
                array_push($allowed_rows, array_merge(static::get_defaults(), $row));
            }
        }

        error_log(__CLASS__ . '->' . __FUNCTION__ . '->$allowed_rows ' . print_r($allowed_rows, 1) . ' ');
        if (count($allowed_rows)) {
            $result = parent::create_multi($allowed_rows);
        }

        return $result;
    }


    /**
     * @function get
     * 
     * gets rows
     * 
     * @param integer                   ID of the required row
     * @return array|object|null|void   the fetched data row
     */
    public static function get($page = 0, $page_size = null)
    {
        global $wpdb;

        $page_size = $page_size ? $page_size : static::$page_size;

        error_log(__CLASS__ . '->' . __FUNCTION__ . '-> Class_name: ' . print_r(static::$class_name, 1));

        $sql = sprintf(
            'SELECT
                    `%1$s`
                FROM
                    `%2$s`
                WHERE
                    -- query for user relateted identifiers
                    %3$s
                LIMIT
                    %4$d,%5$d;',
            implode('`,`', array_keys(static::$columns)),
            static::get_tablename(),
            static::get_user_query(),
            $page * $page_size,
            $page_size
        );

        error_log(__CLASS__ . '->' . __FUNCTION__ . '-> SQL:' . $sql);
        $result = $wpdb->get_results($sql,ARRAY_A);

        return $result;
    }


    /**
     * @function user
     * 
     */
    public static function user($user)
    {
        static::$user = $user;
        return static::$class_name;
    }


    /**
     * @function read
     * 
     * get the row to committed ID
     * 
     * @param integer                   ID of the required row
     * @return array|object|null|void   the fetched data row
     */
    public static function read($where, $or = false, $page = 0, $page_size = null)
    {
        global $wpdb;

        $operator       = $or ? ' OR ' : ' AND ';
        $pagination     = null;
        $id             = null;

        if ($page_size) {
            $pagination     = sprintf(
                'LIMIT 
                    %1$d,%2$d',
                $page * $page_size,
                $page_size
            );
        }

        $sql = sprintf(
            'SELECT
                    `%1$s`
                FROM
                    `%2$s`
                WHERE
                    -- query for user relateted identifiers
                    %3$s
                AND
                   ( %4$s )
                    %5$s;',
            implode('`,`', array_keys(static::$columns)),
            static::get_tablename(),
            static::get_user_query(),
            implode($operator, static::get_where($where)),
            $pagination
        );

        error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '->' . $sql . '->' . print_r($where, 1));

        // if a single row ist queried
        if ($id) {
            $result = $wpdb->get_row($wpdb->prepare($sql, $where),ARRAY_A);
        } else {
            $result = $wpdb->get_results($wpdb->prepare($sql, $where),ARRAY_A);
        }

        return $result;
    }


    /**
     * @function set_where
     * 
     * set the values of the where statements
     * 
     * @param   array   columns with the values to set
     * @param   string  prefix to identify table (optional)
     * @return  array   list with statements
     */
    protected static function get_where($where, $prefix = '')
    {
        global $wpdb;

        $where_stmts    = array();
        $prefix         = '' < $prefix ? '`' . $prefix . '`.' : '';

        error_log(__CLASS__ . '->' . __FUNCTION__);
        // if an integer is submitted
        if (!is_array($where) && intval($where)) {
            $id     = $where;
            $sql    = sprintf(
                $prefix . '`%1$s` = %2$s',
                static::$primary,
                static::$columns[static::$primary]
            );
            // Statement mit wpdb->prepare escapen
            $where_stmts[]  = $wpdb->prepare($sql, $id);
        }
        // an array was submitted
        elseif (is_array($where)) {
            foreach ($where as $key => $value) {
                $operator    = '=';
                // Falls $value ein Array ist,
                // Operator und Wert auslesen

                error_log(__CLASS__ . '->' . __FUNCTION__ . '-> REQUEST: ' . print_r($value, 1));
                if (is_array($value) && isset($value['operator'])) {
                    $operator   = $value['operator'];
                    $value      = $value['value'];
                }
                $sql    = sprintf(
                    $prefix . '`%1$s` %2$s %3$s',
                    $key,
                    $operator,
                    static::$columns[$key]
                );
                // Statement mit wpdb->prepare escapen
                $where_stmts[]  = $wpdb->prepare($sql, $value);
            }
        }
        return $where_stmts;
    }


    /**
     * @function update
     * 
     * updates a new row of the table
     * 
     * @param   array       associative array with key => value pairs for update
     * @return  array|null  if successful, the stored data row
     */
    public static function update($columns, $where = null)
    {
        if (static::read(array(static::$primary => $columns[static::$primary]))) {
            return parent::update($columns, $where);
        } else {
            $error = new \WP_Error(
                'rest_post_invalid_id',
                __(static::$error503, ''),
                array('status' => 503)
            );
            return $error;
        }
    }
}
