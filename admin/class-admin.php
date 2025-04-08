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
class Admin implements Ajax_Interface
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
     *  $instance
     * 
     *  Die Instanz des Singletons
     * 
     *  @var class
     *  @since 1.0.39
     */
    private static $instance = null;


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


    /**
     * darf nur privat aufgerufen werden
     */
    private function __construct() {}

    /**darf nicht geklont werden
     */
    private function __clone() {}

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }


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

?>

        <div>
        </div>
<?php
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
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function get_instance()
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }

        return static::$instance;
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
        error_log(__CLASS__ . '->' . __LINE__ . '->' . $class_name);
        return $class_name;
    }

    static public function save_tramp_user_data($user_id)
    {
        if (!current_user_can('edit_user', $user_id))
            return false;

        $is_tramp_user = get_user_meta($user_id, 'is_tramp_user', 1);
        $sanitized  = 'on' === $_POST['is_tramp_user'];
        if ($is_tramp_user) {
            $location_columns = $_POST['tramp_location'];
            $user_columns = $_POST['tramp_user'];

            $wpdb_dao   = new WPDB_DAO('');
            \Kaipfeiffer\Tramp\Controllers\LocationController::set_dao($wpdb_dao);
            \Kaipfeiffer\Tramp\Controllers\UserController::set_dao($wpdb_dao);

            $tramp_location_id = \Kaipfeiffer\Tramp\Controllers\LocationController::create($location_columns);
            $user_columns['location_id']    = $tramp_location_id;
            $tramp_user_id  = \Kaipfeiffer\Tramp\Controllers\UserController::create($user_columns);

            error_log(__CLASS__ . '->' . __LINE__ . '->' . $tramp_location_id .'->USER:'.$tramp_user_id);
            update_user_meta($user_id, 'tramp_location_id', $tramp_location_id);
            update_user_meta($user_id, 'tramp_user_id', $tramp_user_id);
        }
        error_log(__CLASS__ . '->' . __LINE__ . '->' . $sanitized);
        update_user_meta($user_id, 'is_tramp_user', $sanitized);
    }

    static public function show_tramp_user_data($user)
    {
        $wpdb_dao   = new WPDB_DAO('');
        $is_tramp_user = get_user_meta($user->ID, 'is_tramp_user', 1);
        if ($is_tramp_user) {
            $tramp_location_id  = get_user_meta($user->ID, 'tramp_location_id', 1);
            $tramp_user_id      = get_user_meta($user->ID, 'tramp_user_id', 1);

            \Kaipfeiffer\Tramp\Controllers\LocationController::set_dao($wpdb_dao);
            \Kaipfeiffer\Tramp\Controllers\UserController::set_dao($wpdb_dao);

            if ($tramp_location_id ?? null) {
                echo $tramp_location_id.'<hr />';
                $location_columns = \Kaipfeiffer\Tramp\Controllers\LocationController::read($tramp_location_id);
                echo $tramp_location_id.'<hr />';
            } else {
                $location_columns = \Kaipfeiffer\Tramp\Controllers\LocationController::get_editable_columns();
            }
            if ($tramp_user_id ?? null) {
                $user_columns = \Kaipfeiffer\Tramp\Controllers\UserController::read($tramp_user_id);
            } else {
                $user_columns = \Kaipfeiffer\Tramp\Controllers\UserController::get_editable_columns();
            }
        }
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
