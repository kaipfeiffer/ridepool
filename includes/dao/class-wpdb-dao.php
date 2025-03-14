<?php

namespace Loworx\Ridepool;

use Kaipfeiffer\Tramp\Interfaces\DAOConnector;

class WPDB_DAO implements DAOConnector
{

    protected $table;


    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @param int|null
     * @param int|null
     */
    public function read($id = null, $page = null)
    {
        return $this->table;
    }

    public function read_by(array $query, $page = null) {}

    public function create(array $row) {}

    public function create_keys(array $keys, string $type)
    {
        $key_list   = array();
        foreach ($keys as $key) {
            $key_list[] = sprintf('%1$s %2$s (%3$s)', $type, implode('_', $key), implode(',', $key));
        };
        return $key_list;
    }


    /**
     * 
     */
    public function create_table(array $data)
    {
        global $wpdb;
        $has_auto_increment = false;
        $table_name         = $wpdb->prefix . $data['tablename'];
        $wpdb_collate       = $wpdb->collate;
        $columns            =
            $keys           = array();

        foreach ($data['column_types'] as $column_name => $column_data) {
            switch ($column_data['type']) {
                case 'int':
                case 'integer': {
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
                        break;
                    }
                case 'string': {
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
                        break;
                    }
                default:
                    $column_data['type'] = ' ' . $column_data['type'];
            }
            switch ($column_data['default']) {
            }
            $autoincrement  = '';
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
            array_push($columns, $column);
        }

        /**
         * database keys
         */
        $keys = array_merge($keys, $this->create_keys($data['keys'], 'KEY'));
        $keys = array_merge($keys, $this->create_keys($data['unique_keys'], 'UNIQUE KEY'));

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

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $res = dbDelta($sql);
        return count($res);
    }

    public function update(array $row) {}

    public function delete(array $row) {}

    public function table(string $table): DAOConnector
    {
        $this->table = $table;
        return $this;
    }
}
