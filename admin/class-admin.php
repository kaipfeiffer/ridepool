<?php

namespace Loworx\Ridepool;

if (!defined('WPINC')) {
    exit;
} // Exit if accessed directly


/**
 * Adds Sbu-Handouts Shortcodes
 *
 * @class        WC_Sbu_Handouts_Shortcodes
 * @version        1.0.0
 * @author        Kai Pfeiffer
 */
class Admin extends Base_Logger_Abstract implements Ajax_Interface
{


    /** 
     * AJAX_METHODS
     * 
     * Methoden, die über den Ajax-Router aufgerufen werden.
     * 
     * @since 1.0.63
     */
    const AJAX_METHODS  = array();


    /**
     * ADMIN_PAGE_SLUG 
     * 
     * Eine Konstante für den Slug, weil dieser an mehrern Positionen abgefragt wird
     */
    const ADMIN_PAGE_SLUG  = 'ridepool_admin';

    /**
     * DRAFT_PAGE_COUNT
     * 
     * Eine Konstante um festzulegen, wieviele veraltete Handzettel-Seiten und 
     * Handzettel-Beiträge per Skript-Aufruf auf Entwurf umgestellt werden sollen
     * 
     * ACHTUNG!
     * Durch den Aufruf von update_post_meta kann es zu einem Memory-Leak kommen
     * DRAFT_PAGE_COUNT = 30 ist definitiv zu hoch
     */
    const DRAFT_PAGE_COUNT = 10;


    /** 
     * NONCE 
     * 
     * string, der zur eindeutigen Identifizierung des Nonces benötigt wird
     * 
     * @since   1.0.80
     */
    const NONCE = 'Ridepool_Admin';



    /** 
     *  $admin_hook_suffix
     * 
     *  @var string
     *  @since 1.0.39
     */
    private $admin_hook_suffix = null;

    /** 
     *  $admin_page_tabs
     * 
     *  @var array
     *  @since 1.0.39
     */
    private $admin_page_tabs = [];

    /** 
     *  $blog_id
     * 
     *  Die ID des Blogs, in dem die Einstellungen gespeichert werden sollen
     *  @var int
     *  @since 1.0.39
     */
    protected static $blog_id = null;

    /** 
     *  $plugin_option_slug
     * 
     *  @var string
     *  @since 1.0.105
     */
    protected static $plugin_option_slug;


    /** 
     *  $sections
     * 
     * Die Sections, die im Adminbereich eingebunden werden sollen
     *  @var array
     *  @since 1.0.39
     */
    protected $sections = array();


    /** 
     *  $sub_pages
     * 
     * Die Sub_pages, die im Adminbereich eingebunden werden sollen
     *  @var array
     *  @since 1.0.39
     */
    protected $sub_pages = array();

    /*
    *   PROTECTED METHODS
    */

    /**
     * get_blog_id
     * 
     * Gibt die Blog-ID zurück, zu welscher die Einstellungen gespeichert werden sollen
     * 
     * @return int
     */
    static protected function get_blog_id()
    {
        global $wpdb;
        if (null === static::$blog_id) {
            static::$blog_id = get_current_blog_id();
        }
        return $wpdb->prefix . static::$blog_id;
    }


    /**
     * get_class_name
     * 
     * Ermittelt den Klassennamen aus einem Page-Slug
     *
     * @since    1.0.0
     */
    protected static function get_class_name($slug)
    {
        $slug_parts = explode('_', $slug);

        foreach ($slug_parts as $index => $slug) {
            $slug_parts[$index] = ucfirst($slug);
        }

        $class_name = implode('_', $slug_parts);
        static::use_logger('Class-Name' . $class_name);
        return $class_name;
    }

    /**
     * get_is_tramp_user_meta
     * 
     * Gibt zurück, ob der Benutzer ein Tramp-User ist
     * 
     * @param WP_User $user
     * @return bool
     */
    static protected function get_is_tramp_user_meta($user_id)
    {
        $blog_id = static::get_blog_id();
        return get_user_meta($user_id, $blog_id . '_is_tramp_user', true);
    }

    static protected function get_labels()
    {
        $labels = array(
            'title' => __('Title', 'ridepool'),
            'givenname' => __('Given Name', 'ridepool'),
            'familyname'    => __('Family Name', 'ridepool'),
            'birthday'  => __('Birthday', 'ridepool'),
            'email' => __('Email', 'ridepool'),
            'phone' => __('Phone', 'ridepool'),
            'cell'  => __('Cell', 'ridepool'),
            'identity_card_number'  => __('Identity Card Number', 'ridepool'),
            'identity_card_validity'    => __('Identity Card Validity', 'ridepool'),
            'street'    => __('Street', 'ridepool'),
            'zipcode'   => __('Zip Code', 'ridepool'),
            'city'  => __('City', 'ridepool'),
            'region'    => __('Region', 'ridepool'),
            'country'   => __('Country', 'ridepool'),
            'latitude'  => __('Latitude', 'ridepool'),
            'longitude' => __('Longitude', 'ridepool'),
        );

        return apply_filters('ridepool_tramp_user_labels', $labels);
    }

    /**
     * get_tramp_location_id_meta
     * 
     * Gibt die Tramp-Location-ID des Benutzers zurück
     * 
     * @param WP_User $user
     * @return mixed
     */
    static protected function get_tramp_location_id_meta($user_id)
    {
        $blog_id = static::get_blog_id();
        return get_user_meta($user_id, $blog_id . '_tramp_location_id', true);
    }

    /**
     * get_tramp_user_id_meta
     * 
     * Gibt die Tramp-User-ID des Benutzers zurück
     * 
     * @param WP_User $user
     * @return mixed
     */
    static protected function get_tramp_user_id_meta($user_id)
    {
        $blog_id = static::get_blog_id();
        return get_user_meta($user_id, $blog_id . '_tramp_user_id', true);
    }


    /**
     * set_is_tramp_user_meta
     * 
     * Speichert die Information, ob der Benutzer ein Tramp-User ist
     * 
     * @param WP_User $user
     * @param bool $data
     * @return bool
     */
    static protected function set_is_tramp_user_meta($user_id, $data)
    {
        $blog_id = static::get_blog_id();
        static::use_logger()->log('set_is_tramp_user_meta: ' . $blog_id . '_is_tramp_user' . '->' . $data);
        return update_user_meta($user_id, $blog_id . '_is_tramp_user', $data);
    }

    /**
     * set_tramp_location_id_meta
     * 
     * Speichert die Tramp-Location-ID des Benutzers
     * 
     * @param WP_User $user
     * @param mixed $data
     * @return bool
     */
    static protected function set_tramp_location_id_meta($user_id, $data)
    {
        $blog_id = static::get_blog_id();
        return update_user_meta($user_id, $blog_id . '_tramp_location_id', $data);
    }

    /**
     * set_tramp_user_id_meta
     * 
     * Speichert die Tramp-User-ID des Benutzers
     * 
     * @param WP_User $user
     * @param mixed $data
     * @return bool
     */
    static protected function set_tramp_user_id_meta($user_id, $data)
    {
        $blog_id = static::get_blog_id();
        return update_user_meta($user_id, $blog_id . '_tramp_user_id', $data);
    }

    /*
    *   PUBLIC METHODS
    */
    /**
     * admin_menu
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    public function add_admin_tab($tab_name, $tab_content, $tab_slug = null)
    {
        $tab_slug = $tab_slug ? strtolower(Settings::get_plugin_name()) . '_' . $tab_slug : strtolower(Settings::get_plugin_name() . '_' . preg_replace('/\W/', '_', $tab_name));
        $this->admin_page_tabs[$tab_slug]   = ['tab_name' => $tab_name, 'tab_content' => $tab_content];
    }


    /**
     * add_page
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    public function add_page($tab_name, $tab)
    {
        $method         = array($tab, 'get_slug');
        $tab_slug       = call_user_func($method);
        $tab_content    = call_user_func(array($tab, 'get_view'));

        $this->add_admin_tab($tab_name, $tab_content, $tab_slug);
    }


    /**
     * admin_menu
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    public function admin_menu()
    {
        $text_domain    = Settings::get_plugin_text_domain();
        $page_slug      = Settings::get_plugin_name() . '_' . self::ADMIN_PAGE_SLUG;

        $this->admin_hook_suffix = add_menu_page(
            __('Ridings Admin', $text_domain),
            __('Ridings Admin', $text_domain),
            'manage_options',
            $page_slug,
            [$this, 'display_admin_page'],
            'dashicons-schedule',
            30
        );

        // Bezeichnung des ersten Eintrages ändern (erster Eintrag verweist ebenfalls auf Hauptseite)
        add_submenu_page($page_slug, __('Einstellungen', $text_domain), __('Einstellungen', $text_domain), 'manage_options', $page_slug);

        foreach ($this->sub_pages as $sub_page) {
            // error_log(__CLASS__ . '->' . __LINE__ . '->' . $sub_page);
            $method = array(__NAMESPACE__ . '\\' . $sub_page, 'admin_menu');
            if (is_callable($method)) {
                call_user_func($method, $page_slug);
            }
        }

        // Styles und Scripte nur bei Anzeige der Backend-Seite laden
        add_action('admin_print_styles-' . $this->admin_hook_suffix, array($this, 'enqueue_scripts'));

        // $loader->add_action('woocommerce_process_product_meta', $this, 'save_custom_meta_fields', 1);
        return null;
    }


    /**
     * display
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    public function display()
    {
        $tabs = [];
        $containers = [];
        $active = 'active';

        foreach ($this->admin_page_tabs as $slug => $content) {
            $tabs[] = '<li class="' . $active . '"><a id="' . $slug . '_menu_item">' . $content['tab_name'] . '</a></li>';
            $containers[] = '<div id="' . $slug . '_content" class="' . $active . '">' . $content['tab_content'] . '</div>';
            $active = '';
        }
    }


    /**
     * display_admin_page
     * 
     * Seite zum Admin-Menu-Punkt anzeigen
     * 
     * @since 1.0.39
     */
    public function display_admin_page()
    {

        foreach ($this->sections as $entry) {

            // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> DISPLAY_ADMIN_PAGE'.__NAMESPACE__ . '\\' . $entry);
            $method = array(__NAMESPACE__ . '\\' . $entry, 'get_title');

            if (is_callable($method)) {
                $this->add_page(call_user_func($method), __NAMESPACE__ . '\\' . $entry);
            }
        }

        $this->display();
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


    /**
     * init
     * 
     * Filter und Actions der Klasse zufügen
     * @since 1.0
     */
    public function init()
    {
        $page_slug      = Settings::get_plugin_name() . '_' . self::ADMIN_PAGE_SLUG;
        // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> INIT Backend');

        add_action('admin_menu', array($this, 'admin_menu'));
        // $loader->add_action('woocommerce_process_product_meta', $this, 'save_custom_meta_fields', 1);


        // Module müssen nur geladen werden, wenn die Handout-Backend-Seite aufgerufen wird
        if (isset($_GET['page']) && $page_slug  === $_GET['page']) {
            // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> INIT SECTIONS');
            foreach ($this->sections as $entry) {
                $method = array(__NAMESPACE__ . '\\' . $entry, 'init');
                if (is_callable($method)) {
                    call_user_func($method);
                }

                $method = array(__NAMESPACE__ . '\\' . $entry, 'enqueue_scripts');
                if (is_callable($method)) {
                    // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '->IS_SCRIPT:' . $entry . '->' . 'admin_print_styles-' . $this->admin_hook_suffix);
                    // $loader->add_action('admin_print_styles-'.$this->admin_hook_suffix, $entry, 'enqueue_scripts');
                    add_action('admin_enqueue_scripts', $method);
                    // $entry::enqueue_scripts();
                }

                $method = array(__NAMESPACE__ . '\\' . $entry, 'enqueue_styles');
                if (is_callable($method)) {
                    // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '->IS_SCRIPT:' . $entry . '->' . 'admin_print_styles-' . $this->admin_hook_suffix);
                    // $loader->add_action('admin_print_styles-'.$this->admin_hook_suffix, $entry, 'enqueue_scripts');
                    add_action('admin_enqueue_scripts', $method);
                    // $entry::enqueue_scripts();
                }
            }
        } elseif (!wp_doing_ajax()) {
            foreach ($this->sub_pages as $sub_page) {
                // error_log(__CLASS__ . '->' . __LINE__ . '->' . $sub_page);
                $method = array(__NAMESPACE__ . '\\' . $sub_page, 'admin_init');
                if (is_callable($method)) {
                    add_action('admin_init', $method);
                    // add_action('rest_api_init', $method);
                }
            }
        }

        // Handzettel nur bei Aufruf im Backend deaktivieren
        if (!wp_doing_ajax()) {
            static::$plugin_option_slug = strtolower(Settings::get_plugin_name()) . '_options_frontend';
            $plugin_options     = get_option(static::$plugin_option_slug);

            list($year, $month, $kw)  = explode('-', date('Y-m-W')); {
                if (52 <= (int)$kw  && 1 === (int)$month) {
                    $year--;
                } elseif (1 === (int)$kw  && 12 === (int)$month) {
                    $year++;
                }
            }
        }
        return null;
    }


    /**
     * save_tramp_user_data
     * 
     * Speichert die Tramp-User-Daten des Benutzers
     * 
     * @param int $user_id
     * @since 1.0.39
     */
    static public function save_tramp_user_data($user_id)
    {
        if (!current_user_can('edit_user', $user_id))
            return false;

        $is_tramp_user =  $sanitized  = 'on' === $_POST['is_tramp_user'];
        if ($is_tramp_user) {
            $location_columns = $_POST['tramp_location'];
            $user_columns = $_POST['tramp_user'];

            $missing_location_columns = Location_Controller::check($location_columns);
            $missing_user_columns = User_Controller::check($user_columns);

            $missings = array_merge($missing_location_columns, $missing_user_columns);

            if (!count($missings)) {
                if (!isset($location_columns['id']) || empty($location_columns['id'])) {
                    $tramp_location_id = Location_Controller::create($location_columns);
                    static::set_tramp_location_id_meta($user_id, $tramp_location_id);
                } else {
                    $location_columns = Location_Controller::update($location_columns);
                    $tramp_location_id = $location_columns['id'];
                }

                $user_columns['location_id']    = $tramp_location_id;
                if (!isset($user_columns['id']) || empty($user_columns['id'])) {
                    $tramp_user_id = User_Controller::create($user_columns);
                    static::set_tramp_user_id_meta($user_id, $tramp_user_id);
                } else {
                    $user_columns = User_Controller::update($user_columns);
                    $tramp_user_id = $user_columns['id'];
                }
            }
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($missings, 1));
        }

        $result = static::set_is_tramp_user_meta($user_id, $sanitized);
        static::use_logger()->log('set_is_tramp_user_meta: ' . $user_id . '_is_tramp_user' . '->' . $result);
    }



    /**
     * show_tramp_user_data
     * 
     * Zeigt die Tramp-User-Daten des Benutzers an
     * 
     * @param WP_User $user
     * @return void
     */
    static public function show_tramp_user_data($user)
    {
        $is_tramp_user = static::get_is_tramp_user_meta($user->ID);
        static::use_logger()->log('Log-ID' . get_current_blog_id());
        if ($is_tramp_user) {
            $tramp_location_id  = static::get_tramp_location_id_meta($user->ID);
            $tramp_user_id      = static::get_tramp_user_id_meta($user->ID);

            if ($tramp_location_id ?? null) {
                $location_columns = Location_Controller::read($tramp_location_id);
            } else {
                $location_columns = Location_Controller::get_columns();
                static::use_logger()->log('Location-Columns: ' . print_r($location_columns, 1));
            }
            if ($tramp_user_id ?? null) {
                $user_columns = User_Controller::read($tramp_user_id);
            } else {
                $user_columns = User_Controller::get_columns();
            }
            
            $user_columns['email']  = $user_columns['email'] ? $user_columns['email'] : $user->user_email;
        }
        $labels = static::get_labels();
        $input_types    = array(
            'givenname' => 'text',
            'familyname' => 'text',
            'birthday' => 'date',
            'email' => 'email',
            'phone' => 'tel',
            'cell' => 'tel',
            'identity_card_number' => 'text',
            'identity_card_validity' => 'date',
            'street' => 'text',
            'zipcode' => 'text',
            'city' => 'text',
            'region' => 'text',
            'country' => 'text',
            // Geodata
            'latitude' => 'number',
            'longitude' => 'number');
         include_once Settings::get_plugin_dir_path() . implode(DIRECTORY_SEPARATOR, array('admin', 'templates', 'tramp-user-settings.php'));
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    static public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Sbu_wc_handout_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Sbu_wc_handout_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> enqueue_scripts' );
        wp_enqueue_style(Settings::get_plugin_name(), Settings::get_plugin_url() . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'css', 'admin.css')), array(), Settings::get_plugin_version(), 'all');
        wp_enqueue_script(Settings::get_plugin_name(), Settings::get_plugin_url() . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'js', 'admin.js')), array('jquery'), Settings::get_plugin_version(), false);
    }
}
