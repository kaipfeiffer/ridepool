<?php

namespace Loworx\Ridepool;

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class Entrypoints_Cpt extends Custom_Post_Type_Abstract
{
	/** 
	 * NONCE
	 * 
	 * Der String, mit dem das Nonce für die Bearbeitung der 
	 * Ticker-Texte generiert wird
	 * 
	 * @const string
	 */
	const NONCE = 'lwrx_rdpl_entrypoint';

	const CLASSNAME	= __CLASS__;

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected static $object_type = 'lwrx_rdpl_entrypoint';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected static $post_type = 'lwrx_rdpl_entrypoint';


	/**
	 * Stores cart data.
	 *
	 * @var array
	 */
	protected static $data = array(
		'lwrx_rdpl_entrypoint_id'			=> '',
		'lwrx_rdpl_entrypoint_customer_id'	=> '',
	);


	/**
	 * create custom post types
	 *
	 * @param string the value to set
	 *
	 * @since    1.0.0
	 */
	public static function create_custom_post_types()
	{
        $text_domain    = Settings::get_plugin_text_domain();
		$sbu_cart_list_args = array(
			'capability' => self::$post_type,
			'exclude_from_search' => true,
			'hierarchical' => true,
			'menu_icon' => 'dashicons-businessman',
			'public' => true,
			'show_in_menu'        => false,
			'query_var' => self::$post_type,
			'supports' => array(
				'thumbnail',
				'author',
				'title',
				'page-attributes',
				'revisions'
			),
			'taxonomies'  => array('category'),
			'labels' => array(
				'name'                  => _x('Entrypoints', 'post type general name', $text_domain),
				'singular_name'         => _x('Entrypoint', 'post type singular name', $text_domain),
				'add_new'               => __('New Entrypoint', $text_domain),
				'add_new_item'          => __('Create new Entrypoint', $text_domain),
				'edit_item'             => __('Edit Entrypoint', $text_domain),
				'new_item'              => __('New Entrypoint', $text_domain),
				'view_item'             => _x('View Entrypoint', 'view item singular name', $text_domain),
				'view_items'            => _x('View Entrypoints', 'view item general name', $text_domain),
				'search_items'          => __('Search for Entrypoints', $text_domain),
				'not_found'             => __('Nothing found', $text_domain),
				'not_found_in_trash'    => __('Nothing found un trash', $text_domain),
				'all_items'             => __('All Entrypoints', $text_domain),
				'archives'              => __('Entrypoint archives', $text_domain),
				'attributes'            => __('Entrypoint attributes', $text_domain),
				'insert_into_item'      => __('Insert', $text_domain),
				'uploaded_to_this_item' => __('Media for Entrypoint', $text_domain),
				'menu_name'             => __('Entrypoint', $text_domain),
				'items_list_navigation' => __('Entrypoint', $text_domain),
				'items_list'            => __('More Entrypoints', $text_domain),
				'name_admin_bar'        => __('Entrypoint', $text_domain),
			)
		);

		register_post_type(self::$post_type, $sbu_cart_list_args);
	}


	/**
	 * create meta boxes
	 *
	 * @since    1.0.0
	 */
	public static function create_meta_boxes()
	{
		add_meta_box(
			'_sbu_cartlist',
			__('Eigenschaften', 'ridepool'),
			array(__CLASS__, 'create_view'),
			self::$post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'_sbu_cartlist_data',
			__('Bestellungen-Daten', 'ridepool'),
			array(__CLASS__, 'create_data_view'),
			self::$post_type,
			'normal',
			'high'
		);
	}


	/**
	 * create_data_view
	 *
	 * @since    1.0.0
	 */
	public static function create_data_view()
	{
	}


	/**
	 * get_ajax_params
	 *
	 * @return array
	 * @since    1.0.0
	 */
	public static function get_ajax_params()
	{
		return [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce(static::NONCE),
		];
	}


	/**
	 * get_data_details
	 *
	 * @return array
	 */
	protected static function get_data_details()
	{
		$labels	= self::get_labels();
		$result = array(
			'sbu_wc_cart_customer_id'	=> array('type' => 'number', 'label' => $labels['sbu_wc_cart_customer_id']),
			'sbu_wc_cart_id'			=> array('type' => 'number', 'label' => $labels['sbu_wc_cart_id']),
		);

		return $result;
	}


	/**
	 * Stores labels for the data.
	 *
	 * @return array
	 */
	public static function get_labels()
	{
		return array(
			'sbu_wc_cart_customer_id'	=> __('Kundennummer', 'ridepool'),
			'sbu_wc_cart_id'			=> __('Entrypointnnummer', 'ridepool'),
		);
	}


	/**
	 * save_cart
	 *
	 * @param	array
	 * @since   1.0.0
	 */
	protected static function save_cart($data)
	{
		extract($data);

		$cart		= array(
			'id'			=> $post_id,
			'customer_id'	=> $address['customer_id'],
			'cart_id'		=> $sbu_wc_cart_cartlist_id,
		);
		// echo '<h3>' . __CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '</h3><pre>' . print_r($cart, 1) . '</pre>';

		return $data;
	}


	/**
	 * init post-type hooks.
	 *
	 * @since    1.0.0
	 */
	static function init()
	{
		// Der Post-Type wird in der Methode init der abstrakten Klasse initialisiert
		parent::init();

		// Filter registrieren
		add_filter('single_template', array(__CLASS__, 'post_type_template'));
	}

	/**
	 * filter: remove_protected_info
	 *
	 * entfernt den "Deschützt:"-Vermek bei der Ansicht einer Bestellung
	 * 
	 * @param	string	Titel
	 * @return	string	Titel ohne Geschützt-Vermerk
	 * @since    1.0.0
	 */
	static function remove_protected_info($title)
	{
		$title = str_replace('Gesch&uuml;tzt: ', '', $title);
		$title = str_replace('Geschützt: ', '', $title);
		return $title;
	}

	/**
	 * wp_insert_post_data
	 *
	 * @param	array
	 * @param	array
	 * @return	array
	 * @since	1.0.0
	 */
	public static function wp_insert_post_data($data, $postarr):array
	{
		if (static::$post_type === $data['post_type']) {
			$data = parent::wp_insert_post_data($data, $postarr);
		}
		return $data; // Returns the modified data.
	}
}
