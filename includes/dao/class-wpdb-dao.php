<?php

namespace Loworx\Ridepool;

use Kaipfeiffer\Tramp\Interfaces\DaoConnectorInterface;

class WPDB_DAO implements DaoConnectorInterface
{


    /**
     * table_name
     *
     * @var     string
     * @since   1.0.0
     */
    protected $table;


    /**
     * prefix  for table name
     *
     * @var     string
     * @since   1.0.0
     */
    protected $prefix = 'tramp_';


    /**
     * PROTECTED METHODS
     */

    /**
     * create keys
     * 
     * creates the keys for the WP-dbDelta query
     * 
     * @param   array
     * @param   string
     * @since   1.0.0
     */
    protected function create_keys(array $keys, string $type)
    {
        $key_list   = array();
        foreach ($keys as $key) {
            $key_list[] = sprintf('%1$s %2$s (%3$s)', $type, implode('_', $key), implode(',', $key));
        };
        return $key_list;
    }


    /**
     * create string definition
     * 
     * creates the definition for string columns for the WP-dbDelta query
     * 
     * @param   array
     * @since   1.0.0
     */
    protected function create_string_definition(array $column_data)
    {
        switch (true) {
            case 10000   <   (int)($column_data['length'] ?? 0): {
                    $column_data['type']    = ' text';
                    break;
                }
            case 0   <   (int)($column_data['length'] ?? 0): {
                    $column_data['type']    = sprintf(' varchar(%d)', $column_data['length']);
                    break;
                }
            default:
                $column_data['type']    = ' varchar(100)';
        }
        return $column_data['type'];
    }


    /**
     * create integer definition
     * 
     * creates the definition for integer columns for the WP-dbDelta query
     * 
     * @param   array
     * @since   1.0.0
     */
    protected function create_integer_definition(array $column_data)
    {
        switch (true) {
            case 11 < (int)($column_data['length'] ?? 0): {
                    $column_data['type']    = ' bigint(20)%s';
                    break;
                }
            case 9 < (int)($column_data['length'] ?? 0): {
                    $column_data['type']    = ' int(11)%s';
                    break;
                }
            case 6 < (int)($column_data['length'] ?? 0): {
                    $column_data['type']    = ' mediumint(9)%s';
                    break;
                }
            case 4 < (int)($column_data['length'] ?? 0): {
                    $column_data['type']    = ' smallint(6)%s';
                    break;
                }
            case 0 < (int)($column_data['length'] ?? 0): {
                    $column_data['type']    = ' tinyint(4)%s';
                    break;
                }
            default:
                $column_data['type']    = ' int(11)%s';
        }
        $column_data['type'] = sprintf($column_data['type'], ($column_data['signed'] ?? true) ? '' : ' unsigned');
        return $column_data['type'];
    }


    /**
     * create columns
     * 
     * creates the keys for the WP-dbDelta query
     * 
     * @param   array
     * @param   string
     * @since   1.0.0
     */
    protected function create_columns(array $columns)
    {
        $column_list        = array();
        $has_auto_increment = false;

        foreach ($columns as $column_name => $column_data) {
            switch ($column_data['type']) {
                case 'int':
                case 'integer': {
                        $column_data['type'] = $this->create_integer_definition($column_data);
                        break;
                    }
                case 'string': {
                        $column_data['type'] = $this->create_string_definition($column_data);
                        break;
                    }
                default:
                    $column_data['type'] = ' ' . $column_data['type'];
            }

            $autoincrement      = '';

            if (!$has_auto_increment) {
                $has_auto_increment =
                    $autoincrement  = ($column_data['autoincrement'] ?? false) ? ' AUTO_INCREMENT' : '';
            }

            $column = sprintf(
                '%1$s%2$s%3$s%4$s',
                $column_name, // column_name
                $column_data['type'], // column_type
                ($column_data['null'] ?? true) ? ' NULL' : ' NOT NULL', // null
                $autoincrement
            );
            array_push($column_list, $column);
        }
        return $column_list;
    }


    /**
     * get model
     * 
     * create the model classname with prefixed namespace
     * 
     * @param   string
     * @return  string|null
     * @since   1.0.0
     */
    public function get_model(?string $tablename = null): ?string
    {
        $model  = __NAMESPACE__ . '\\' . ucfirst(strtolower($tablename ?? $this->table)) . '_Model';
        return $model;
    }


    /**
     * get tablename
     * 
     * create the tablename with the required prefixes
     * 
     * @param   string
     * @return  string|null
     * @since   1.0.0
     */
    public function get_tablename(?string $tablename = null): ?string
    {
        global $wpdb;

        $tablename  = $wpdb->prefix . $this->prefix . ($tablename ?? $this->table);
        return $tablename;
    }


    /**
     * get placeholder
     * 
     * get the placeholder for the selected Fieldtype
     * 
     * @param   string
     * @return  string|null
     * @since   1.0.0
     */
    protected function get_placeholder(string $type): ?string
    {
        switch (strtolower($type)) {
            case 'bigint(20)':
            case 'bigint(20) unsigned':
            case 'int(11)':
            case 'int(11) unsigned':
            case 'mediumint(9)':
            case 'mediumint(9) unsigned':
            case 'smallint(6)':
            case 'smallint(6) unsigned':
            case 'tinyint(4)':
            case 'tinyint(4) unsigned':
            case 'int(11)':
            case 'int(11) unsigned': {
                    return '%d';
                    break;
                }
            case 'decimal':
            case 'float':
                return '%f';
            default:
                return '%s';
        }
    }


    /**
     * get primary_key
     * 
     * create the tablename with the required prefixes
     * 
     * @param   string
     * @return  array|null
     * @since   1.0.0
     */
    protected function get_primary_key(string $table_name): ?array
    {
        global $wpdb;
        $schema = $wpdb->get_results(sprintf('DESCRIBE %s', $table_name), ARRAY_A);

        foreach ($schema as $key => $field) {
            if ('PRI' === strtoupper($field['Key'])) {
                return array('column' => $field['Field'], 'placeholder' => $this->get_placeholder($field['Type']));
            }
        }
        return null;
    }


    /**
     * PUBLIC METHODS
     */

    /**
     * constructor
     * 
     * @param   string
     * @since   1.0.0
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * create row
     * 
     * @param   array $row
     * @return  int|null
     * @since   1.0.0
     */
    public function create(array $row): int
    {
        global $wpdb;

        $table_name = $this->get_tablename();

        $res        = $wpdb->insert($table_name, $row);
        if ($res) {
            return $wpdb->insert_id;
        }
        return 0;
    }


    /**
     * delete row
     * 
     * @param   array
     * @return  bool
     * @since   1.0.0
     */
    public function delete(array $row): bool
    {
        return false;
    }


    /**
     * read row 
     * 
     * @param   int|null
     * @param   int|null
     * @return  array|null
     * @since   1.0.0
     */
    public function read($id = null, $page = null): ?array
    {
        global $wpdb;

        $row            = null;
        $table_name     = $this->get_tablename();
        $primary_key    = $this->get_primary_key($table_name);

        if ($id) {
            $sql = sprintf('SELECT * FROM `%s` WHERE `%s` = %s', $table_name, $primary_key['column'], $primary_key['placeholder']);
            $sql = $wpdb->prepare($sql, $id);
            $row    = $wpdb->get_row($sql, ARRAY_A);
        }

        // echo (__CLASS__ . '->' . __LINE__ . '->' . $sql . '->' . print_r($primary_key, 1));
        // $res        = $wpdb->insert($table_name, $row);
        return $row;
    }


    /**
     * read row by query
     * 
     * @param   array
     * @param   integer
     * @return  array|null
     * @since   1.0.0
     */
    public function read_by(array $query, $page = null): ?array
    {
        return null;
    }


    /**
     * create table
     * 
     * create the table with the requirements from the dao
     * 
     * @param   array
     * @return  int|null
     * @since   1.0.0
     */
    public function create_table(array $data): ?int
    {
        global $wpdb;

        $model  = $this->get_model($data['tablename']);
        $method = array($model, 'create_table');

        if (is_callable($method)) {
            call_user_func($method, $data);
        }

        $table_name         = $this->get_tablename($data['tablename']);
        $wpdb_collate       = $wpdb->collate;
        $columns            =
            $keys           = array();


        $columns    = $this->create_columns($data['column_types']);

        $keys       = array_merge($keys, $this->create_keys($data['keys'], 'KEY'));
        $keys       = array_merge($keys, $this->create_keys($data['unique_keys'], 'UNIQUE KEY'));

        $sql = sprintf(
            'CREATE TABLE %s (
            %s,
            PRIMARY KEY  (%s)%s
            )
            COLLATE %s',
            $table_name,
            implode(",\n", $columns), // columns
            $data['primary_key'],
            count($keys) ? ",\n" . implode(",\n", $keys) : '', // keys
            $wpdb_collate
        );

        // dbDelta is located in upgrade.php
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // error_log(__CLASS__ . '->' . __LINE__ . '->SQL:' . $sql);
        $res = dbDelta($sql);
        return count($res);
    }


    /**
     * updaterow
     * 
     * @param   array
     * @return  int|null
     * @since   1.0.0
     */
    public function update(array $row): ?int
    {
        global $wpdb;

        $row            = null;
        $table_name     = $this->get_tablename();
        $primary_key    = $this->get_primary_key($table_name);

        return $wpdb->update($table_name,$row, array($primary_key['column'] => $row[$primary_key['column']]));
    }



    /**
     * table
     * 
     * declare target table of the query
     * 
     * @param   array
     * @return  DAOConnector
     * @since   1.0.0
     */
    public function table(string $table): DaoConnectorInterface
    {
        error_log(__CLASS__ . '->' . __LINE__ . '->Table:' . $table);
        $this->table = $table;
        return $this;
    }
}
