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

abstract class Model_Ctagged_Abstract extends Model_Filtered_Abstract
{
    /**
     * VARIABLES
     */
    /**
     * $ctag_column
     * the name of the ctag column
     * 
     * @var string
     */
    protected static $ctag_column = 'ctag';


    /**
     * $primary
     * the name of the primary index
     * 
     * @var string
     */
    private static $ctag_primary = 'id';


    /**
     * $table_name
     * the name of the table without wp-prefix
     * 
     * @var string
     */
    private static $ctag_table_name = 'customers';

    /**
     * $user_column
     * the name of the user column
     * 
     * @var string
     */
    private static $ctag_user_column = 'owner';


    protected static function get_ctag()
    {
        global $wpdb;
        $ctag   = null;

        $sql    = sprintf(
            'SELECT
                    `%1$s`,
                    `%2$s`
                FROM
                    `%3$s`
                WHERE
                    `%1$s` = "%5$s";',
            static::$ctag_primary,
            static::$ctag_column,
            static::get_tablename(static::$ctag_table_name),
            static::$ctag_user_column,
            static::get_user()
        );

        error_log(__CLASS__ . '->' . __LINE__ . '->' . $sql);

        $row    = $wpdb->get_row($sql);
        if ($row) {
            $row->{static::$ctag_column}++;
            $where  =  array(static::$ctag_primary => $row->{static::$ctag_primary});

            // Dieses UPDATE-Statement kann gelöscht werden
            $sql    = sprintf(
                'UPDATE
                        `%1$s``
                    SET
                        `%2$s` = "%3$s"
                    WHERE
                        `%4$s` = "%5$s";',
                static::get_tablename(static::$ctag_table_name),
                static::$ctag_column,
                $row->{static::$ctag_column},
                static::$ctag_primary,
                $row->{static::$ctag_primary}
            );

            $update_params  = array(static::$ctag_column => $row->{static::$ctag_column});
            $query_params   = array(static::$ctag_primary => $row->{self::$ctag_primary});
            // error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($update_params,1).'-'.print_r($query_params,1));
            
            $result = $wpdb->update(
                static::get_tablename(static::$ctag_table_name),
                $update_params,
                $query_params
            );

            if ($result) {
                $ctag   = $row->{static::$ctag_column};
            }
        }
        return $ctag;
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
        $ctag   = static::get_ctag();
        $columns[static::$ctag_column]  = $ctag;

        $result  = parent::create($columns);
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
        $ctag   = static::get_ctag();
        foreach ($rows as $index => $row) {
            $row[static::$ctag_column]  = $ctag;
            $rows[$index]               = $row;
        }
        $result = parent::create_multi($rows);

        return $result;
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
        if (!static::read(array(static::$primary => $columns[static::$primary]))) {
            $error = new \WP_Error(
                'rest_post_invalid_id',
                __(static::$error503, ''),
                array('status' => 503)
            );
            return $error;
        }


        $row            = null;

        if (!$where && !array_key_exists(static::$primary, $columns)) {
            $error = new \WP_Error(
                'rest_post_invalid_id',
                __(static::$error404, ''),
                array('status' => 404)
            );
            return $error;
        }

        $placeholders   = $chunk_list   = array();

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

        $wpdb->show_errors     = true;
        $wpdb->suppress_errors = false;
        $result = $wpdb->update(static::get_tablename(), $chunk_list, $where, $placeholders);

        error_log(__CLASS__ . '->' . __LINE__ . '->' . $result . '|' . print_r($wpdb->last_query, 1));
        error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($chunk_list, 1) . '->' . print_r($where, 1) . '->' . print_r($placeholders, 1));
        // if update was successful
        if (!$result) {
            $error = new \WP_Error(
                __(static::$errorOnSave, ''),
                __(static::$error404, ''),
                array('status' => 404)
            );
            return $error;
        }

        $ctag   = static::get_ctag();
        if ($ctag) {

            $columns[static::$ctag_column] = $ctag;

            list($chunk_list, $placeholders)   = static::set_values($columns);

            $result = $wpdb->update(static::get_tablename(), $chunk_list, $where, $placeholders);
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($wpdb->last_query, 1));

            $user_table_columns  = $user_table_where = array(
                static::$user_primary   => $columns[static::$user_column]
            );
            $user_table_columns[static::$ctag_column]   = $ctag;

            error_log(__CLASS__ . '->' . __LINE__ . '->' . static::get_tablename(static::$user_table_name));
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($user_table_columns, 1));
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($user_table_where, 1));
            $wpdb->update(static::get_tablename(static::$user_table_name), $user_table_columns, $user_table_where);
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($wpdb->last_query, 1));
        }

        $row = static::read(array(static::$primary => $columns[static::$primary]));
        return $row;
    }


    /**
     * @function update
     * 
     * updates a new row of the table
     * 
     * @param   array       associative array with key => value pairs for update
     * @return  array|null  if successful, the stored data row
     */
    public static function update2($columns, $where = null)
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

            // error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($chunk_list, 1));
            // if update was successful
            if ($result) {
                // $row = static::read($chunk_list);
                $row = static::read(array(static::$primary => $columns[static::$primary]));
            }
        } else {
            // throw error
        }
        return $row;
    }
}
