<?php

namespace Loworx\Ridepool;

if (!defined('WPINC')) {
    die;
}

/**
 * abstract static class for admin tabs
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

abstract class Admin_Subpage_Abstract implements Ajax_Interface
{
    const ADMIN_SUBPAGE_SLUG = '';


    /** 
     * AJAX_METHODS
     * 
     * Methods that could by called by the ajax router
     * 
     * @since 1.0.63
     */
    const AJAX_METHODS  = array('ajax_response');

    const CLASS_NAME    = '';

    const NONCE = '';

    const NONCE_FIELD = 'search_nonce';

    const SEARCH_INPUT_ID   = 'search_id';

    /** 
     *  $admin_hook_suffix
     * 
     *  @var string
     *  @since 1.0.39
     */
    protected static $admin_hook_suffix = null;


    /** 
     *  $admin_hook_suffix
     * 
     *  @var    \WP_LIST_TABLE
     *  @since  1.0.0
     */
    protected static $wp_list_table_instance = null;

    /**
     * $form_table_instance
     */
    protected static $form_table_instance;

    abstract static function get_plural();

    abstract static function get_singlular();

    /**
     * admin_menu
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    static public function admin_menu($page_slug)
    {
        $subpage_slug      = Settings::get_plugin_name() . '_' . static::ADMIN_SUBPAGE_SLUG;

        static::$admin_hook_suffix = add_submenu_page(
            $page_slug,
            static::get_page_title(),
            static::get_menu_title(),
            'manage_options',
            $subpage_slug,
            [static::CLASS_NAME, 'display_admin_page']
        );

        if (!$_REQUEST['action'] ?? null) {
            add_action('load-' . static::$admin_hook_suffix, array(static::CLASS_NAME, 'add_options'));
        }
        return null;
    }

    function add_options()
    {
        $list_table_instance = static::get_wp_list_table();
        $option = 'per_page';
        $args = array(
            'label' => static::get_menu_title(),
            'default' => 10,
            'option' => static::get_slug() . 's_per_page'
        );
        add_screen_option($option, $args);
    }

    static function ajax_response()
    {

        check_ajax_referer(static::NONCE . '_ajax', static::NONCE_FIELD . '_ajax');

        error_log(__CLASS__ . '->' . __LINE__ . '->AJAXRESPONSE');
        $list_table_instance = static::get_wp_list_table();
        $list_table_instance->ajax_response();
    }

    static function display_admin_page()
    {
        switch ($_REQUEST['action'] ?? '') {
            case 'edit':
                static::display_edit_form();
                return;
            default:
                static::display_wp_list_table();
        }
    }

    static function display_edit_form()
    {
        if (current_user_can('manage_options') && isset($_REQUEST['user']) && is_numeric($_REQUEST['user'])) {
            $form = static::get_form_table();
            if ($form instanceof User_Form_Table) {
                $form->display();
            } else {
                echo '<p>' . __('User not found.', 'ridepool') . '</p>';
            }
        } else {
            echo '<p>' . __('You do not have permission to edit users.', 'ridepool') . '</p>';
        }
    }

    static function display_wp_list_table()
    {
        // vars used in template
        $page_title         = static::get_page_title();
        $page_content       =
            $page_search    = '';

        error_log(__CLASS__ . '->' . __LINE__ . '->' . static::NONCE);
        $list_table_instance = static::get_wp_list_table();
        if ($list_table_instance instanceof \WP_List_Table) {
            $list_table_instance->prepare_items();
            ob_start();
            $list_table_instance->display();
            $page_content      = ob_get_clean();
            ob_start();
            $list_table_instance->search_box(__('search', 'ridepool'), static::SEARCH_INPUT_ID);
            wp_nonce_field(static::NONCE, static::NONCE_FIELD);
            echo sprintf('<input type="hidden" name="page" value="%s" />', esc_attr($_GET['page']));
            $page_search      = ob_get_clean();
        }
        $template   = static::get_template();
        if (file_exists($template)) {
            include_once $template;
        }
        static::enqueue_scripts();
    }

    static function enqueue_scripts()
    {
        $js_handle  = Settings::get_plugin_name() . '_admin_subpage';
        $js_url  = Settings::get_plugin_url() . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'js', 'wp-list-class-ajax.js'));
        wp_enqueue_script($js_handle, $js_url, array('jquery'), Settings::get_plugin_version(), false);
        wp_localize_script($js_handle, $js_handle . '_data', array(
            'action'        => 'ridepool-admin-router',
            'ajax_target'   => 'ajax_response',
            'ajaxurl'       => admin_url('admin-ajax.php'),
            'class'         => str_replace(__NAMESPACE__ . '\\', '', static::CLASS_NAME),
            'nonce_field'   => static::NONCE_FIELD . '_ajax',
            'pagination_aria_labels' => array(
                __('First page', 'ridepool'),
                __('Previous page', 'ridepool'),
                __('Next page', 'ridepool'),
                __('Last page', 'ridepool'),
            ),
            'search_input_id'   => static::SEARCH_INPUT_ID,
        ));
    }

    static function get_constructor_param()
    {
        return array(
            'ajax'              => true,
            'nonce'             => static::NONCE,
            'nonce_field'       => static::NONCE_FIELD,
            'plural'            => static::get_plural(),
            'singular'          => static::get_singlular(),
        );
    }

    static function get_form_table()
    {
        if (!(static::$form_table_instance ?? null)) {
            $class_name = str_replace(__NAMESPACE__ . '\\', '', static::CLASS_NAME);

            $data_source = reset(explode('_', $class_name));

            $list_table = __NAMESPACE__ . '\\' . $data_source . '_Form_Table';

            static::$form_table_instance = new $list_table(static::get_constructor_param());
        }
        return static::$form_table_instance;
    }

    static function get_wp_list_table(): \WP_LIST_TABLE
    {
        if (!(static::$wp_list_table_instance ?? null)) {
            $class_name = str_replace(__NAMESPACE__ . '\\', '', static::CLASS_NAME);

            $data_source = reset(explode('_', $class_name));

            $list_table = __NAMESPACE__ . '\\Ridings_' . $data_source . '_WP_List_Table';

            static::$wp_list_table_instance = new $list_table(static::get_constructor_param());
        }
        return static::$wp_list_table_instance;
    }

    static function get_menu_title()
    {
        return static::get_title();
    }

    static function get_page_title()
    {
        return static::get_title();
    }

    static function get_slug()
    {
        if (!static::ADMIN_SUBPAGE_SLUG) {
            return null;
        }
        return static::ADMIN_SUBPAGE_SLUG;
    }

    static function get_title()
    {
        return __('Titel', 'ridepool');
    }

    static function get_template()
    {
        return wp_unslash(Settings::get_plugin_dir_path()) . '/admin/templates/default_subpage_template.php';
    }

    static function get_view()
    {
        $class  = static::class;

        $relative_class_name = strtolower(str_replace('_', '-', preg_replace('/\W/', '', str_replace(__NAMESPACE__, '', $class))));
        return '<div>' . $relative_class_name . '</div>';

        return static::ADMIN_SUBPAGE_SLUG;
    }


    /**
     * is_allowed
     * 
     * checks, if the requested method could be called via ajax
     * 
     * @param string
     * @since   1.0.0
     */
    static function is_allowed(string $name)
    {
        return in_array($name, static::AJAX_METHODS);
    }
}
