<?php

namespace Loworx\Ridepool;

use Kaipfeiffer\Tramp\Interfaces\DaoConnectorInterface;

class WPDB_DAO implements DaoConnectorInterface
{



    /**
     * db
     *
     * @var     wpdb
     * @since   1.0.0
     */
    protected $db;

    /**
     * columns
     *
     * @var     array
     * @since   1.0.0
     */
    protected $columns;

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
     * get place_holder
     * 
     * create the model classname with prefixed namespace
     * 
     * @param   string
     * @return  string|null
     * @since   1.0.0
     */
    public function get_place_holder($type): ?string
    {
        preg_match('/^\w+/', $type, $matches);

        switch ($matches[0]) {
            case 'bigint':
            case 'int':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':
                return '%d';
            case 'varchar':
            case 'text':
            case 'char':
            case 'date':
            case 'datetime':
                return '%s';
            case 'decimal':
            case 'numeric':
            case 'float':
            case 'double':
                return '%f';
            default:
                return null;
        }
    }


    /**
     * get_columns
     * 
     * get the columns for the current table
     * 
     * @return  array|null
     * @since   1.0.0
     */
    public function get_columns(): ?array
    {
        if (!$this->columns) {
            $sql = sprintf('DESCRIBE `%s`;', $this->get_tablename());

            $this->columns = array();
            $result = $this->db->get_results($sql, ARRAY_A);
            foreach ($result as $field) {
                $this->columns[$field['Field']]  = $this->get_place_holder($field['Type']);
            }
            error_log(__CLASS__ . '->' . __LINE__ . '->' . $sql . '->' . print_r($this->columns, 1));
        }
        return $this->columns;
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


        $tablename  = $this->db->prefix . $this->prefix . ($tablename ?? $this->table);
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
     * 
     * 
     * @param   string
     * @return  array|null
     * @since   1.0.0
     */
    public function get_primary_key(string $table_name): ?array
    {

        $schema = $this->db->get_results(sprintf('DESCRIBE %s', $table_name), ARRAY_A);

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
        global $wpdb;
        $this->db   = $wpdb;
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


        $table_name = $this->get_tablename();

        $res        = $this->db->insert($table_name, $row);
        if ($res) {
            return $this->db->insert_id;
        }
        return 0;
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


        $model  = $this->get_model($data['tablename']);
        $method = array($model, 'create_table');

        if (is_callable($method)) {
            call_user_func($method, $data);
        }

        $table_name         = $this->get_tablename($data['tablename']);
        $wpdb_collate       = $this->db->collate;
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
     * get row count
     * 
     * get the number of rows in the table
     * 
     * @return  int|null
     * @since   1.0.0
     */
    public function get_row_cnt(?array $query = null): ?int
    {
        $table_name     = $this->get_tablename();
        $primary_key    = $this->get_primary_key($table_name);

        if ($query) {
            $queries    = array();
            $values     = array();

            if (array_is_list($query)) {
                foreach ($query as $details) {
                    $detail = $this->parse_query($details);
                    if ($detail) {
                        array_push($queries, $detail);
                        $value = $this->parse_value($details);
                        array_push($values, $value);
                    }
                }
            } elseif (is_array($query)) {
                $detail = $this->parse_query($query);
                if ($detail) {
                    array_push($queries, $detail);
                    $value = $this->parse_value($query);
                    array_push($values, $value);
                }
            }

            $sql = sprintf(
                'SELECT COUNT(`%1$s`) FROM `%2$s` WHERE %3$s;',
                $primary_key['column'],
                $table_name,
                implode(' OR ', $queries)
            );
            $sql    = $this->db->prepare($sql, ...$values);
        } else {
            $sql = sprintf('SELECT COUNT(`%1$s`) FROM `%2$s`;', $primary_key['column'], $table_name);
        }

        $row_cnt    = $this->db->get_var($sql);
        return $row_cnt;
    }


    /**
     * read row 
     * 
     * @param   int|null
     * @param   int|null
     * @param   int|null
     * @return  array|null
     * @since   1.0.0
     */
    public function read($id = null, ?int  $page = null, ?int  $per_page = null): ?array
    {
        $row            = null;
        $table_name     = $this->get_tablename();
        $primary_key    = $this->get_primary_key($table_name);

        if ($id) {
            $sql = sprintf('SELECT * FROM `%s` WHERE `%s` = %s', $table_name, $primary_key['column'], $primary_key['placeholder']);
            $sql = $this->db->prepare($sql, $id);
            $row    = $this->db->get_row($sql, ARRAY_A);
        } else {
            $row_cnt    = $per_page ?? 10;
            $sql = sprintf('SELECT * FROM `%s` LIMIT %d, %d;', $table_name, $page * $row_cnt, $row_cnt);
            $row    = $this->db->get_results($sql, ARRAY_A);
        }

        // echo (__CLASS__ . '->' . __LINE__ . '->' . $sql . '->'.print_r($row,1));
        // $res        = $this->db->insert($table_name, $row);
        return $row;
    }

    protected function parse_value(array $query)
    {
        error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($query, 1));
        switch (strtolower($query['comparator'] ?? '')) {
            case '%like':
                $value = '%' . $this->db->esc_like($query['value']);
                break;
            case '%like%':
                $value = '%' . $this->db->esc_like($query['value']) . '%';
                break;
            case 'like%':
                $value = $this->db->esc_like($query['value']) . '%';
                break;
            default:
                $value = $query['value'];
        }
        return $value;
    }

    protected function parse_query(array $query): ?string
    {
        $columns = $this->get_columns();

        $column     = $query['column'];
        $comparator = $query['comparator'] ?? '=';

        error_log(__CLASS__ . '->' . __LINE__ . '->' . $columns[$column] . '->' . print_r($query, 1));
        if ($columns[$column] ?? null) {
            $placeholder = $columns[$column];
            switch (strtolower($comparator)) {
                case 'like%':
                case '%like':
                case '%like%':
                    return sprintf(
                        '`%s` LIKE %s',
                        $column,
                        $placeholder
                    );
                default:
                    return sprintf(
                        '`%s` %s %s',
                        $column,
                        $comparator,
                        $placeholder
                    );
            }
        }
        return null;
    }


    /**
     * read row by query
     * 
     * @param   array
     * @param   integer
     * @return  array|null
     * @since   1.0.0
     */
    public function read_by(array $query, ?int  $page = null, ?int  $per_page = null): ?array
    {
        $queries    = array();
        $values     = array();
        if (array_is_list($query)) {
            foreach ($query as $details) {
                $detail = $this->parse_query($details);
                if ($detail) {
                    array_push($queries, $detail);
                    $value = $this->parse_value($details);
                    array_push($values, $value);
                }
            }
        } elseif (is_array($query)) {
            $detail = $this->parse_query($query);
            if ($detail) {
                array_push($queries, $detail);
                $value = $this->parse_value($query);
                array_push($values, $value);
            }
        }

        $table_name     = $this->get_tablename();
        $row_cnt    = $per_page ?? 10;
        $sql = sprintf(
            'SELECT * FROM %1$s WHERE %2$s LIMIT %3$d, %4$d;',
            $table_name,
            implode(' OR ', $queries),
            $page * $row_cnt,
            $row_cnt
        );

        $sql    = $this->db->prepare($sql, ...$values);
        error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($values, 1));
        error_log(__CLASS__ . '->' . __LINE__ . '->' . $sql);
        $result = $this->db->get_results($sql, ARRAY_A);
        error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($result, 1));
        return $result;
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


        $row            = null;
        $table_name     = $this->get_tablename();
        $primary_key    = $this->get_primary_key($table_name);

        return $this->db->update($table_name, $row, array($primary_key['column'] => $row[$primary_key['column']]));
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
