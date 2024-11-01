<?php

namespace KeyManager\Handlers;

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle miscellaneous actions and filters.
 *
 * @since 1.0.0
 * @package KeyManager
 * @subpackage Handlers
 */
class Misc {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'handle_product_query_var' ), 10, 2 );
	}

	/**
	 * Register post types.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_post_types() {
		// API post type. Private post type.
		register_post_type(
			'wckm_api',
			array(
				'labels'              => array( 'name' => __( 'API', 'wc-key-manager' ) ),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'query_var'           => false,
				'can_export'          => false,
				'rewrite'             => false,
				'capability_type'     => 'post',
				'has_archive'         => false,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array(),
			)
		);
	}

	/**
	 * Handle product query var.
	 *
	 * @param array $query The query.
	 * @param array $vars The query vars.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function handle_product_query_var( $query, $vars ) {
		// keyed.
		// if keyed is set to yes, then add the meta query.
		if ( isset( $vars['keyed'] ) && 'yes' === $vars['keyed'] ) {
			$query['meta_query'][] = array(
				'key'     => '_wckm_keyed',
				'value'   => 'yes',
				'compare' => '=',
			);
		}

		// source.
		if ( isset( $vars['source'] ) && array_key_exists( $vars['source'], Key::get_sources() ) ) {
			$query['meta_query'][] = array(
				'key'     => '_wckm_key_source',
				'value'   => $vars['source'],
				'compare' => '=',
			);
		}

		return $query;
	}
}
