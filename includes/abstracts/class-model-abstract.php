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

abstract class Model_Abstract
{
    /**
     * VARIABLES
     */

    /**
     * @var string class_name
     * 
     * diese Variable muss in der abgeleiteten Klasse mit dem Inhalte der Konstanten __CLASS__
     * belegt werden, damit die Instanzen richtig funktionieren
     */
    protected static $class_name = __CLASS__;

    /**
     * $chunk_size
     * 
     * @var integer
     * Die Anzahl der Zeilen, die in einem Rutsch in die Datenbank eingefügt werden
     */
    protected static $chunk_size = 30;

    /**
     * $columns
     * 
     * @var array
     * Assoziatives Array mit den Spaltennamen und den zugehörigen printf-Platzhaltern
     */
    protected static $columns;

    /**
     * $error
     * error id
     * 
     * @var integer
     */
    protected static $error;


    /**
     * $error404
     * error id
     * 
     * @var integer
     */
    protected static $error404 = 'Ressource not found';


    /**
     * $error503
     * error id
     * 
     * @var integer
     */
    protected static $error503 = 'Forbidden';

    
    /**
     * $errorOnSave
     * error id
     * 
     * @var integer
     */
    protected static $errorOnSave = 'Saving aborted';


    /**
     * $error_message
     * message to error
     * 
     * @var integer
     */
    protected static $error_message;

    /**
     * $import_file
     * the name of the file to import
     * 
     * @var string
     */
    protected static $import_file;

    /**
     * $page_size
     * the number of entries to deliver
     * 
     * @var string
     */
    protected static $page_size = 30;

    /**
     * $prefix
     * the prefix of this plugin
     * 
     * @var string
     */
    protected static $prefix = 'kpm_counter_';

    /**
     * $primary
     * the name of the primary index
     * 
     * @var string
     */
    protected static $primary;

    /**
     * $user
     * 
     * die User-ID
     * 
     * @var integer
     */
    protected static $user;

    /**
     * $table_name
     * the name of the table without wp-prefix
     * 
     * @var string
     */
    protected static $table_name;

    /**
     * $chunk_list
     * 
     * @var array
     * Assoziatives Array mit den Spaltennamen und den zugehörigen Werten
     */
    protected static $chunk_list;


    /**
     * PRIVATE METHODS
     */


    /**
     * @function  create_indices
     * 
     * creates the indices for the table
     */
    protected static function create_indices()
    {
    }


    /**
     * @function  create_table
     * 
     * creates the table
     */
    protected static function create_table()
    {
    }


    /**
     * @function escape_placeholder
     * 
     * escape the placholder for prepare-statements with double "%"
     * 
     * @param   string  the placeholder
     * @return  string  escaped placeholder
     */
    protected static function escape_placeholder($placeholder)
    {
        $placeholder = str_replace('%', '%%', $placeholder);
        return $placeholder;
    }


    /**
     * @function get_defaults
     * 
     * get default values to the table columns
     * 
     * @return array    default values
     */
    protected static function get_defaults()
    {
        return array();
    }


    /**
     * @function get_tablename
     * 
     * @param   string  => optional der Tabellenname, der mit Prefixes versehenwerden soll
     *                              ansonsten wird der Name aus static::$table_name genutzt
     * @return  string => Tabellenname mit dem Prefix der Wordpress-Installation 
     * 
     * liefert den Tabellennamen mit dem Prefix der Wordpress-Installation zurück
     */
    public static function get_tablename($table_name = null)
    {
        global $wpdb;

        $table_name = $table_name ? $table_name : static::$table_name;
        return $wpdb->prefix . static::$prefix . $table_name;
    }


    /**
     * @function get_update_defaults
     * 
     * get default update values to the table columns
     * 
     * @return array    default update values
     */
    protected static function get_update_defaults()
    {
        return array();
    }


    /**
     * @function set_values
     * 
     * set the values of the table columns
     * 
     * @param   array   columns with the values to set
     * @return  array   list aof placeholders
     */
    protected static function set_values($columns)
    {
        $placeholders = $chunk_list = array();

        // error_log(__CLASS__.'-'.print_r($columns,1));
        // error_log(__CLASS__.'-'.print_r(static::$columns,1));

        foreach ($columns as $key => $value) {
            if (array_key_exists($key, static::$columns)) {
                // error_log(__CLASS__.'-'.$key);
                array_push($placeholders, static::$columns[$key]);
                // array_push($placeholders, static::escape_placeholder(static::$columns[$key]));
                $chunk_list[$key] = sprintf(static::$columns[$key], $value);
            }
        }

        return array($chunk_list, $placeholders);
    }


    /**
     * @function  table_exists
     * 
     * check if table exists
     * 
     * @return  bool    true id table exists
     */
    protected static function table_exists()
    {
        global $wpdb;

        $sql = sprintf(
            'SELECT COUNT(TABLE_NAME)
        FROM 
           information_schema.TABLES 
        WHERE 
           TABLE_SCHEMA LIKE "%1$s" AND 
            TABLE_TYPE LIKE "BASE TABLE" AND
            TABLE_NAME = "%2$s";',
            $wpdb->dbname,
            static::get_tablename()
        );

        return (bool)$wpdb->get_var($sql);
    }


    /**
     * PUBLIC METHODS
     */

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
        global $wpdb;

        $inserts    = array();
        $values     = array();

        // Die Felder in einem Rutsch in die Datenbank eintragen
        foreach ($rows as $row) {
            list($chunklist, $placeholders) = static::set_values($row);
            array_push($inserts, '(' . implode(',', $placeholders) . ')');
            array_push($values, ...array_values($chunklist));
        }

        $sql    = 'INSERT INTO `' . static::get_tablename() . '` (`' . implode('`,`', array_keys($chunklist)) . '`) VALUES ' . implode(',', $inserts) . ';';
        error_log(__CLASS__ . '->' . __LINE__ . $sql);
        $result = $wpdb->query($wpdb->prepare($sql, $values));

        return $result;
    }


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
        global $wpdb;

        $placeholders   = $chunk_list   = array();
        $row            = null;

        // merge default columns with updated values
        $columns = array_merge(static::get_defaults(), $columns);

        // set only supported keys and retrieve prepare-placeholders
        list($chunk_list, $placeholders)   = static::set_values($columns);

        error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '->' . print_r($chunk_list, 1) . '->' . print_r($placeholders, 1));
        $result = $wpdb->insert(static::get_tablename(), $chunk_list, $placeholders);
        if ($result) {
            error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '->' . $result . '->' . $wpdb->insert_id);
            $row = static::read($wpdb->insert_id,);
        }
        return $row;
    }


    /**
     * get_primary
     * 
     * get the primary-key of the model
     * 
     * @return string     Primary-Key
     */
    public static function get_primary()
    {
        return static::$primary;
    }


    /**
     * @function delete
     * 
     * get the row to committed ID
     * 
     * @param integer|array primary id or where columns for selection
     * @return int|bool     the number of affected rows or false
     */
    public static function delete($where = null)
    {
        global $wpdb;

        // if where is an integer or array
        if ($where) {
            $placeholders = $chunk_list = array();

            // if an integer is submitted
            if (is_int($where)) {
                $id     = $where;
                $where  = array();
                array_push($placeholders, static::$columns[static::$primary]);
                $where[static::$primary] = sprintf(
                    static::$columns[static::$primary],
                    $id
                );
            }
            // an array was submitted
            else {
                // set only supported keys and retrieve prepare-placeholders
                list($chunk_list, $placeholders)   = static::set_values($where);
                $where          = $chunk_list;
            }
            $result = $wpdb->delete(static::get_tablename(), $where, $placeholders);
        }
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

        $sql = sprintf(
            'SELECT
                    `%1$s`
                FROM
                    `%2$s`
                WHERE
                    1
                LIMIT
                    %3$d,%4$d;',
            implode('`,`', array_keys(static::$columns)),
            static::get_tablename(),
            $page * $page_size,
            $page_size
        );

        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }


    /**
     * @function import
     * 
     */
    public static function user($user)
    {
        static::$user = $user;
        return static::$class_name;
    }


    /**
     * @function import
     * 
     */
    protected static function import()
    {
        global $wpdb;

        $csv_file_path = Settings::get_plugin_name() . 'data/imports/' . static::$import_file;

        error_log($csv_file_path . '|' . file_exists($csv_file_path) . '|');
        if (file_exists($csv_file_path)) {
            $file       = fopen($csv_file_path, "r");
            $table      = static::get_tablename();
            $columns    = null;
            $chunk_list = [];
            $chunk      = [];

            // Anzahl der Zeilen pro Insert
            $chunk_size = $left = static::$chunk_size;

            while (($row = fgetcsv($file)) !== FALSE) {
                if (!$columns) {
                    $columns = '(`' . implode('`,`', $row) . '`)';
                } else {
                    // Wenn Chunk vollständig ist
                    if (!$left) {
                        $left = $chunk_size;
                        array_push($chunk_list, $chunk);
                        $chunk = [];
                    }
                    // Zeile hinzufügen
                    array_push($chunk, '("' . implode('","', $row) . '")');
                    $left--;
                }
            }

            // Wenn sich noch Zeilen im chunk befinden
            if ($left) {
                array_push($chunk_list, $chunk);
            }

            // Alle chunks durchgehen
            for ($i = 0; $i < count($chunk_list); $i++) {
                $sql = sprintf(
                    'INSERT INTO `%1$s` %2$s VALUES %3$s;',
                    $table,
                    $columns,
                    implode(',', $chunk_list[$i])
                );
                $wpdb->query($sql);
            }
        }
    }


    /**
     * @function raw_sql
     * 
     * get result of a raw sql-statement
     * 
     * @param string                    statement
     * @param array|null                values
     * @return array|object|null|void   the fetched data row
     */
    public static function raw_sql($stmt, $params = null)
    {
        global $wpdb;
        if ($params) {
            $stmt   = $wpdb->prepare($stmt, array_values($params));
        }
        error_log(__CLASS__ . '->' . __LINE__ . '->' . $stmt);
        $result = $wpdb->get_results($stmt, ARRAY_A);

        return $result;
    }


    /**
     * @function read
     * 
     * get the row to committed ID
     * 
     * @param integer                   ID of the required row
     * @return array|object|null|void   the fetched data row
     */
    public static function read($where, $or = false, $page = 0, $page_count = null)
    {
        global $wpdb;

        $where_stmts    = array();
        $operator       = $or ? ' OR ' : ' AND ';
        $pagination     = null;
        $placeholders   = $chunk_list   = array();
        $id             = null;

        // if an integer is submitted
        if (!is_array($where) && intval($where)) {
            $id     = $where;
            $where  = array();

            // set placeholder
            array_push($placeholders, static::$columns[static::$primary]);

            $where[static::$primary] = sprintf(
                static::$columns[static::$primary],
                $id
            );
        }
        // an array was submitted
        else {
            // set only supported keys and retrieve prepare-placeholders
            list($chunk_list, $placeholders)   = static::set_values($where);
            $where          = $chunk_list;

            error_log(__CLASS__ . '-' . print_r($placeholders, 1));
            error_log(__CLASS__ . '-' . print_r($where, 1));
            if ($page) {
                $pagination     = sprintf(
                    'LIMIT 
                        %1$d,%2$d',
                    $page * $page_count,
                    $page_count
                );
            }
        }

        foreach (array_keys($where) as $index => $key) {
            array_push(
                $where_stmts,
                sprintf(
                    '`%1$s` = %2$s ' . "\n",
                    $key,
                    $placeholders[$index]
                )
            );
        }

        $sql = sprintf(
            'SELECT
                    *
                FROM
                    `%1$s`
                WHERE
                    %2$s
                    %3$s;',
            static::get_tablename(),
            implode($operator, $where_stmts),
            $pagination
        );

        error_log(__CLASS__ . $sql);
        error_log(__CLASS__ . $wpdb->prepare($sql, array_values($where)));
        // if a single row ist queried
        if ($id) {
            $result = $wpdb->get_row($wpdb->prepare($sql, array_values($where)), ARRAY_A);
        } else {
            $result = $wpdb->get_results($wpdb->prepare($sql, array_values($where)), ARRAY_A);
        }
        return $result;
    }

    /**
     * @function  setup_table
     * 
     * setup the table
     */
    public static function setup_table()
    {
        $table_exists   = static::table_exists();

        // create the table
        static::create_table();
        // if the table doesn't exist
        if (!$table_exists) {
            // insert data
            if (static::$import_file) {
                // static::import();
            }
            // create indices
            // static::create_indices();
        }
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
        global $wpdb;

        $row            = null;

        if ($where || array_key_exists(static::$primary, $columns)) {
            $placeholders   = array();

            // if no where values were submitted
            if (!$where) {
                // create where with primary key
                $where[static::$primary] = sprintf(
                    static::$columns[static::$primary],
                    $columns[static::$primary]
                );
            }

            // merge default columns with updated values
            $columns = array_merge(static::get_update_defaults(), $columns);

            list($chunk_list, $placeholders)   = static::set_values($columns);

            $result = $wpdb->update(static::get_tablename(), $chunk_list, $where, $placeholders);

            error_log(__CLASS__ . '->' . __LINE__ . '->' . static::get_tablename() . '->' . print_r($chunk_list, 1) . '->' . print_r($where, 1) . '->' . print_r($placeholders, 1));
            // if update was successful
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($wpdb->last_query, 1));
            if ($result) {
                // $row = static::read($chunk_list);
                $row = static::read(array(static::$primary => $columns[static::$primary]));
            }
        } else {
            // throw error
        }
        error_log(__CLASS__ . '->' . __LINE__ . '->' . static::get_tablename() . '->' . print_r($row, 1));
        return $row;
    }
}
