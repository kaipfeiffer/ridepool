<?php

namespace Loworx\Ridepool;

if (!defined('ABSPATH')) {
    exit;
}

class Ridings_User_WP_List_Table extends Ridings_WP_List_Table_Abstract
{

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'birthday':
            case 'identity_card_validity':
                return preg_replace('/(\d{4}).(\d{2}).(\d{2})/', __('$3.$2.$1', 'ridepool'), $item[$column_name]);
            default:
                return parent::column_default($item, $column_name);
        }
    }

    function get_columns()
    {
        $columns = parent::get_columns();
        unset($columns['title'], $columns['familyname'], $columns['givenname']);

        $columns    = array('fullname' => __('Name', 'ridepool')) + $columns;
        return $columns;
    }


    function column_fullname($item)
    {
        $name = sprintf(
            '%1$s %2$s %3$s',
            $item['title'],
            $item['givenname'],
            $item['familyname']
        );
        $actions = array(
            'edit'      => sprintf('<a href="?page=%1$s&amp;action=%2$s&amp;user=%3$d" aria-label="%5$s %4$s">%5$s</a>', $_REQUEST['page'], 'edit', $item['id'], $name, __('Edit')),
            'delete'    => sprintf('<a href="?page=%1$s&amp;action=%2$s&amp;user=%3$d" aria-label="%5$s %4$s">%5$s</a>', $_REQUEST['page'], 'delete', $item['id'], $name, __('Delete')),
        );

        return sprintf(
            '%1$s %2$s',
            $name,
            $this->row_actions($actions)
        );
    }
}
