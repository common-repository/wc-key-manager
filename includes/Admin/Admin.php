<?php

namespace KeyManager\Admin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin class.
 *
 * @since 1.0.0
 * @package KeyManager\Admin
 */
class Admin {

	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
		add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook The current admin page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		$is_order_page   = ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && in_array( get_post_type(), array( 'shop_order' ), true ) ) || 'woocommerce_page_wc-orders' === $hook;
		$is_product_page = in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && in_array( get_post_type(), array( 'product' ), true );

		// TODO: Register halloween styles only on halloween day otherwise remove it.
		WCKM()->scripts->register_style( 'wckm-halloween', 'css/halloween.css' );

		WCKM()->scripts->enqueue_style( 'wckm-admin', 'css/admin.css', array( 'bytekit-layout', 'bytekit-components', 'woocommerce_admin_styles', 'wckm-halloween' ) );
		WCKM()->scripts->register_script( 'wckm-chart', 'js/chart.js' );
		WCKM()->scripts->register_script( 'wckm-admin', 'js/admin.js' );

		if ( ! in_array( $hook, Utilities::get_screen_ids(), true ) && ! $is_product_page && ! $is_order_page ) {
			return;
		}

		// If dashboard page, enqueue the chart script.
		if ( 'toplevel_page_key-manager' === $hook ) {
			wp_enqueue_script( 'wckm-chart' );
		}

		$localize = array(
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'security'     => wp_create_nonce( 'wc_key_manager' ),
			'i18n'         => array(
				'search_products'  => esc_html__( 'Select products', 'wc-key-manager' ),
				'search_orders'    => esc_html__( 'Select orders', 'wc-key-manager' ),
				'search_customers' => esc_html__( 'Select customers', 'wc-key-manager' ),
			),
			'key_settings' => array(
				'pattern' => get_option( 'wckm_pattern', '####-####-####-####' ),
				'charset' => get_option( 'wckm_charset', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' ),
			),
		);

		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_localize_script( 'wckm-admin', 'wckm_admin_vars', $localize );
		wp_enqueue_script( 'wckm-admin' );
		wp_enqueue_style( 'wckm-admin' );
	}

	/**
	 * Add the plugin screens to the WooCommerce screens.
	 * This will load the WooCommerce admin styles and scripts.
	 *
	 * @param array $ids Screen ids.
	 *
	 * @return array
	 */
	public function add_screen_ids( $ids ) {
		return array_merge( $ids, Utilities::get_screen_ids() );
	}

	/**
	 * Request review.
	 *
	 * @param string $text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function admin_footer_text( $text ) {
		if ( in_array( get_current_screen()->id, Utilities::get_screen_ids(), true ) ) {
			$text = sprintf(
			/* translators: %s: Plugin name */
				__( 'Thank you for using %s!', 'wc-key-manager' ),
				'<strong>' . esc_html( WCKM()->get_name() ) . '</strong>',
			);
			if ( WCKM()->get_review_url() ) {
				$text .= sprintf(
				/* translators: %s: Plugin name */
					__( ' Share your appreciation with a five-star review %s.', 'wc-key-manager' ),
					'<a href="' . esc_url( WCKM()->get_review_url() ) . '" target="_blank">here</a>'
				);
			}
		}

		return $text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function update_footer( $text ) {
		if ( in_array( get_current_screen()->id, Utilities::get_screen_ids(), true ) ) {
			/* translators: 1: Plugin version */
			$text = sprintf( esc_html__( 'Version %s', 'wc-key-manager' ), WCKM()->get_version() );
		}

		return $text;
	}

	/**
	 * Add a post display state for special pages in the page list table.
	 *
	 * @param array    $post_states An array of post display states.
	 * @param \WP_Post $post        The current post object.
	 */
	public function add_display_post_states( $post_states, $post ) {
		if ( absint( get_option( 'wckm_validate_key_page_id', 0 ) ) === $post->ID ) {
			$post_states['wckm_validate_key_page'] = __( 'Key Validation Page', 'wc-key-manager' );
		}

		return $post_states;
	}
}
