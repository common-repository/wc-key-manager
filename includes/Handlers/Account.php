<?php

namespace KeyManager\Handlers;

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Account class
 *
 * @since 1.0.0
 * @package KeyManager\Frontend
 * @category Class
 */
class Account {

	/**
	 * Account constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// if not enabled return.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_scripts' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
		add_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'add_query_vars' ), 0 );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu_items' ) );
		add_action( 'woocommerce_account_keys_endpoint', array( __CLASS__, 'my_keys' ) );
		add_action( 'woocommerce_account_view-key_endpoint', array( __CLASS__, 'view_key' ) );
		add_action( 'wc_key_manager_after_key_details', array( __CLASS__, 'view_activations' ), 10, 2 );
		add_action( 'wp_loaded', array( __CLASS__, 'handle_key_validation' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'handle_key_activation' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'handle_key_deactivation' ) );
	}


	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.0.0
	 */
	public static function frontend_scripts() {
		WCKM()->scripts->register_style( 'wc-key-manager', 'css/frontend.css' );
		WCKM()->scripts->register_script( 'wc-key-manager', 'js/frontend.js' );

		wp_enqueue_style( 'wc-key-manager' );
		wp_enqueue_script( 'wc-key-manager' );
	}

	/**
	 * Add body classes.
	 *
	 * @param array $classes Body classes.
	 *
	 * @since 1.0.0
	 * @return array Altered body classes.
	 */
	public static function body_classes( $classes ) {
		// if wckm_validate_key shortcode is present, add woocomerce class.
		if ( has_shortcode( get_the_content(), 'wckm_validate_key' ) ) {
			$classes[] = 'woocommerce woocommerce-page';
		}

		return $classes;
	}

	/**
	 * Change title for keys endpoint.
	 *
	 * @param string $title Current page title.
	 *
	 * @since 1.0.0
	 * @return string Altered page title.
	 */
	public static function endpoint_title( $title ) {
		if ( is_wc_endpoint_url( 'keys' ) && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$title = __( 'Keys', 'wc-key-manager' );
			remove_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		} elseif ( is_wc_endpoint_url( 'view-key' ) && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$editing = filter_input( INPUT_GET, 'edit', FILTER_VALIDATE_BOOLEAN );
			$title   = $editing ? __( 'Edit Keys', 'wc-key-manager' ) : __( 'View Key', 'wc-key-manager' );
			remove_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Add keys query var.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 * @return array altered query vars
	 */
	public static function add_query_vars( $vars ) {
		$vars['keys']     = 'keys';
		$vars['view-key'] = 'view-key';

		return $vars;
	}

	/**
	 * Add keys endpoint to My Account menu.
	 *
	 * @param array $items Menu items.
	 *
	 * @since 1.0.0
	 * @return array Altered menu items.
	 */
	public static function menu_items( $items ) {
		if ( get_option( 'wckm_enable_my_account_keys_page', 'yes' ) !== 'yes' ) {
			return $items;
		}
		$new_items         = array();
		$new_items['keys'] = __( 'Keys', 'wc-key-manager' );

		return self::insert_new_items_after( $items, $new_items, 'dashboard' );
	}

	/**
	 * My Keys content.
	 *
	 * @since 1.0.0
	 */
	public static function my_keys() {
		// when disabled, return.
		if ( get_option( 'wckm_enable_my_account_keys_page', 'yes' ) !== 'yes' ) {
			return;
		}

		$customer_id = get_current_user_id();
		$order_ids   = wc_get_orders(
			array(
				'customer' => $customer_id,
				'status'   => wckm_get_order_paid_statuses(),
				'return'   => 'ids',
				'limit'    => - 1,
			)
		);
		$keys        = array();
		if ( ! empty( $order_ids ) ) {
			$keys = wckm_get_keys(
				array(
					'customer_id__in'     => $customer_id,
					'customer_id__exists' => true,
					'order_id__in'        => $order_ids,
					'order_id__exists'    => true,
					'limit'               => - 1,
				)
			);
		}

		$enabled_columns = get_option( 'wckm_my_account_keys_columns', array( 'product', 'key', 'expires', 'actions' ) );

		$columns = apply_filters(
			'wc_key_manager_my_account_keys_table_columns',
			array(
				'product' => __( 'Product', 'wc-key-manager' ),
				'key'     => __( 'Key', 'wc-key-manager' ),
				'expires' => __( 'Expires', 'wc-key-manager' ),
				'actions' => __( 'Actions', 'wc-key-manager' ),
			)
		);

		if ( is_array( $enabled_columns ) && ! empty( $enabled_columns ) ) {
			$columns = array_intersect_key( $columns, array_flip( $enabled_columns ) );
		}

		wc_get_template(
			'myaccount/keys.php',
			array(
				'keys'    => $keys,
				'columns' => $columns,
			),
			'',
			WCKM()->get_template_path()
		);
	}

	/**
	 * View Key content.
	 *
	 * @since 1.0.0
	 */
	public static function view_key() {
		$uuid        = get_query_var( 'view-key' );
		$redirect_to = wc_get_account_endpoint_url( 'keys' );
		// If uuid is not set, redirect to keys page.
		if ( empty( $uuid ) ) {
			wp_safe_redirect( $redirect_to );
			exit;
		}
		$key = wckm_get_key( array( 'uuid' => $uuid ) );
		// if key is not found, or user is not the owner, redirect to keys page.
		if ( ! $key || get_current_user_id() !== $key->customer_id ) {
			wp_safe_redirect( $redirect_to );
			exit;
		}

		wc_get_template(
			'key/view.php',
			array(
				'key'     => $key,
				'context' => 'my-account',
			),
			'',
			WCKM()->get_template_path()
		);
	}

	/**
	 * View Activations content.
	 *
	 * @param Key       $key The key object.
	 * @param \WC_Order $order The order object.
	 *
	 * @since 1.0.0
	 */
	public static function view_activations( $key, $order ) {
		if ( 'yes' !== get_option( 'wckm_my_enable_account_activations', 'yes' ) || ! is_account_page() ) {
			return;
		}
		$columns = apply_filters(
			'wc_key_manager_my_account_activations_table_columns',
			array(
				'instance'   => __( 'Instance', 'wc-key-manager' ),
				'ip_address' => __( 'IP Address', 'wc-key-manager' ),
				'date'       => __( 'Activated At', 'wc-key-manager' ),
				'actions'    => '',
			)
		);

		if ( 'yes' !== get_option( 'wckm_my_account_allow_deactivation', 'yes' ) ) {
			unset( $columns['actions'] );
		}

		wc_get_template(
			'key/activations.php',
			array(
				'key'         => $key,
				'order'       => $order,
				'columns'     => $columns,
				'activations' => $key->activations,
			),
			'',
			WCKM()->get_template_path()
		);
	}

	/**
	 * Handle key validation.
	 *
	 * @since 1.0.0
	 */
	public static function handle_key_validation() {
		if ( ! isset( $_POST['action'] ) || 'wckm_validate_key' !== $_POST['action'] ) {
			return;
		}
		$referrer = wp_get_referer();
		$nonce    = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wckm_validate_key' ) ) {
			wc_add_notice( __( 'Something went wrong, please try again.', 'wc-key-manager' ), 'error' );
			wp_safe_redirect( $referrer );
			exit();
		}

		$code  = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : false;
		if ( empty( $code ) ) {
			wc_add_notice( __( 'Please enter a valid key.', 'wc-key-manager' ), 'error' );
			wp_safe_redirect( $referrer );
			exit();
		}

		$key = wckm_get_key( array( 'code' => $code ) );
		if ( $email && $key && $key->customer_email !== $email ) {
			wc_add_notice( __( 'The key is not valid.', 'wc-key-manager' ), 'error' );
			wp_safe_redirect( $referrer );
			exit();
		}

		// remove all the query args from the referrer.
		$redirect_to = add_query_arg( array( 'key' => $key->uuid ), $referrer );
		wp_safe_redirect( $redirect_to );
		exit();
	}

	/**
	 * Handle key activation.
	 *
	 * @since 1.0.0
	 */
	public static function handle_key_activation() {
		if ( ! isset( $_GET['action'] ) || 'wckm_activate_key' !== $_GET['action'] ) {
			return;
		}
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wckm_activate_key' ) ) {
			wc_add_notice( __( 'Something went wrong, please try again.', 'wc-key-manager' ), 'error' );

			return;
		}

		$uuid = isset( $_GET['uuid'] ) ? sanitize_text_field( wp_unslash( $_GET['uuid'] ) ) : '';
		if ( empty( $uuid ) ) {
			wc_add_notice( __( 'Something went wrong, please try again.', 'wc-key-manager' ), 'error' );

			return;
		}

		$key = wckm_get_key( array( 'uuid' => $uuid ) );
		if ( ! $key || get_current_user_id() !== $key->customer_id ) {
			wc_add_notice( __( 'Invalid key. The key does not exist or you do not have permission to activate it.', 'wc-key-manager' ), 'error' );

			return;
		}

		$data = array(
			'key'        => $key,
			'instance'   => ! empty( $_GET['instance'] ) ? sanitize_text_field( wp_unslash( $_GET['instance'] ) ) : wckm_get_default_instance(),
			'ip_address' => wckm_get_ip_address(),
			'user_agent' => wckm_get_user_agent(),
		);

		// Finally, if the key is not activated, create a new activation.
		$activation = $key->activate( $data );
		if ( is_wp_error( $activation ) ) {
			wc_add_notice( $activation->get_error_message(), 'error' );

			return;
		}

		wc_add_notice( __( 'The key has been activated successfully.', 'wc-key-manager' ), 'success' );
		wp_safe_redirect( wp_get_referer() );
		exit();
	}

	/**
	 * Handle key deactivation.
	 *
	 * @since 1.0.0
	 */
	public static function handle_key_deactivation() {
		if ( ! isset( $_GET['action'] ) || 'wckm_deactivate_key' !== $_GET['action'] ) {
			return;
		}
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wckm_deactivate_key' ) ) {
			wc_add_notice( __( 'Something went wrong, please try again.', 'wc-key-manager' ), 'error' );

			return;
		}

		$uuid     = isset( $_GET['uuid'] ) ? sanitize_text_field( wp_unslash( $_GET['uuid'] ) ) : '';
		$instance = isset( $_GET['instance'] ) ? sanitize_text_field( wp_unslash( $_GET['instance'] ) ) : '';

		if ( empty( $uuid ) ) {
			wc_add_notice( __( 'Something went wrong, please try again.', 'wc-key-manager' ), 'error' );

			return;
		}

		$key = wckm_get_key( array( 'uuid' => $uuid ) );
		if ( ! $key || get_current_user_id() !== $key->customer_id ) {
			wc_add_notice( __( 'Invalid key. The key does not exist or you do not have permission to deactivate it.', 'wc-key-manager' ), 'error' );

			return;
		}

		$deactivation = $key->deactivate( array( 'instance' => $instance ) );
		if ( is_wp_error( $deactivation ) ) {
			wc_add_notice( $deactivation->get_error_message(), 'error' );

			return;
		}

		wc_add_notice( __( 'The key has been deactivated successfully.', 'wc-key-manager' ), 'success' );

		wp_safe_redirect( wp_get_referer() );
		exit();
	}

	/**
	 * Helper to add new items into an array after a selected item.
	 *
	 * @param array  $items Menu items.
	 * @param array  $new_items New menu items.
	 * @param string $after Key in items.
	 *
	 * @since 1.0.0
	 * @return array Menu items
	 */
	protected static function insert_new_items_after( $items, $new_items, $after ) {
		// Search for the item position and +1 since is after the selected item key.
		$position = array_search( $after, array_keys( $items ), true ) + 1;

		// Insert the new item.
		$array  = array_slice( $items, 0, $position, true );
		$array += $new_items;
		$array += array_slice( $items, $position, count( $items ) - $position, true );

		return $array;
	}
}
