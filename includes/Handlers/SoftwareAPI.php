<?php

namespace KeyManager\Handlers;

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;


/**
 * API class
 *
 * @since 1.0.0
 * @package KeyManager\Handlers
 */
class SoftwareAPI {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ), 0 );
		add_action( 'parse_request', array( __CLASS__, 'parse_request' ), 0 );
		add_action( 'wc_key_manager_api_validate_key', array( __CLASS__, 'validate_key' ) );
		add_action( 'wc_key_manager_api_activate_key', array( __CLASS__, 'activate_key' ) );
		add_action( 'wc_key_manager_api_deactivate_key', array( __CLASS__, 'deactivate_key' ) );
		add_action( 'wc_key_manager_api_get_version', array( __CLASS__, 'get_version' ) );
		add_action( 'wc_key_manager_api_get_changelog', array( __CLASS__, 'get_changelog' ) );
		add_action( 'wc_key_manager_api_download', array( __CLASS__, 'download_package' ) );
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'wckm-api';

		return $vars;
	}

	/**
	 * Process request.
	 *
	 * @since 1.0.0
	 */
	public static function parse_request() {
		global $wp;
		if ( ! empty( $wp->query_vars['wckm-api'] ) && 'yes' === get_option( 'wckm_enable_software_api', 'yes' ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			nocache_headers();

			// Clean the API request.
			$action = sanitize_key( wp_unslash( $wp->query_vars['wckm-api'] ) );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( "wc_key_manager_api_{$action}" ) ? 200 : 400 );

			wp_verify_nonce( '_wp_nonce' );

			/**
			 * Action before API request.
			 *
			 * @param Key   $key Key object.
			 * @param array $request Request data.
			 *
			 * @since 1.0.0
			 */
			do_action( "wc_key_manager_api_{$action}", $_REQUEST );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}

	/**
	 * Validate key.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 */
	public static function validate_key( $request ) {
		$defaults = array(
			'key'        => '',
			'instance'   => '',
			'email'      => '',
			'product_id' => '',
		);

		$request = wp_parse_args( $request, $defaults );

		/**
		 * Filter the request data.
		 *
		 * @param array $request Request data.
		 *
		 * @since 1.0.0
		 */
		$args     = apply_filters( 'wc_key_manager_api_validate_key_args', $request );
		$code     = isset( $args['key'] ) ? sanitize_text_field( wp_unslash( $args['key'] ) ) : '';
		$instance = ! empty( $args['instance'] ) ? wckm_sanitize_instance( wp_unslash( $args['instance'] ) ) : '';

		$key = Key::find( array( 'key' => $code ) );

		if ( ! $key instanceof Key ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid_key',
					'message' => __( 'Invalid key, please provide a valid key.', 'wc-key-manager' ),
				)
			);
		}

		// If key is not valid, return error.
		if ( ! $key->is_valid() ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid_key',
					'message' => __( 'Invalid key, please provide a valid key.', 'wc-key-manager' ),
				)
			);
		}

		// If expired, return error.
		if ( $key->is_expired() ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'expired',
					'message' => __( 'Key is expired.', 'wc-key-manager' ),
				)
			);
		}

		$result = array(
			'success'              => true,
			'message'              => __( 'Key is valid.', 'wc-key-manager' ),
			'activation_limit'     => $key->activation_limit,
			'activation_count'     => $key->activation_count,
			'activation_remaining' => $key->activation_limit > 0 ? $key->activation_limit - $key->activation_count : 'unlimited',
			'expires'              => $key->get_expires() ? $key->get_expires() : 'never',
			'order_id'             => $key->order_id,
			'customer_name'        => $key->get_order() ? $key->get_order()->get_formatted_billing_full_name() : '',
			'customer_email'       => $key->get_order() ? $key->get_order()->get_billing_email() : '',
			'checksum'             => self::get_request_checksum( $args ),
		);

		// If instance is passed, check if the key is activated for the instance.
		if ( ! empty( $instance ) ) {
			$activation = $key->get_activation( $instance );
			if ( $activation && 'active' === $activation->status ) {
				$result['instance']        = $activation->instance;
				$result['instance_status'] = $activation->status;
			} else {
				$result['instance']        = $args['instance'];
				$result['instance_status'] = 'inactive';
			}
		}

		wp_send_json(
			apply_filters(
			/**
			 * Filter the response data.
			 *
			 * @param array $result Response data.
			 * @param Key   $key Key object.
			 * @param array $args Request data.
			 *
			 * @since 1.0.0
			 */
				'wc_key_manager_key_validation_response',
				$result,
				$key,
				$args
			)
		);
	}

	/**
	 * Activate key.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 */
	public static function activate_key( $request ) {
		$defaults = array(
			'key'        => '',
			'email'      => '',
			'product_id' => '',
			'instance'   => wckm_get_default_instance(),
			'ip_address' => wckm_get_ip_address(),
			'user_agent' => wckm_get_user_agent(),
		);

		$request = wp_parse_args( $request, $defaults );

		/**
		 * Filter the request data.
		 *
		 * @param array $request Request data.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'wc_key_manager_api_activate_key_args', $request );
		$code = isset( $args['key'] ) ? sanitize_text_field( wp_unslash( $args['key'] ) ) : '';
		$key  = Key::find( array( 'key' => $code ) );
		if ( ! $key instanceof Key ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid_key',
					'message' => __( 'Invalid key, please provide a valid key.', 'wc-key-manager' ),
				)
			);
		}

		$activation = $key->activate( $args );
		if ( is_wp_error( $activation ) ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => $activation->get_error_code(),
					'message' => $activation->get_error_message(),
				)
			);
		}

		$result = array(
			'success'              => true,
			'message'              => __( 'Key activated successfully.', 'wc-key-manager' ),
			'instance'             => $activation->instance,
			'activation_limit'     => $key->activation_limit,
			'activation_count'     => $key->activation_count,
			'activation_remaining' => $key->activation_limit > 0 ? $key->activation_limit - $key->activation_count : 'unlimited',
			'expires'              => $key->get_expires() ? $key->get_expires() : 'never',
			'order_id'             => $key->order_id,
			'customer_name'        => $key->get_order() ? $key->get_order()->get_formatted_billing_full_name() : '',
			'customer_email'       => $key->get_order() ? $key->get_order()->get_billing_email() : '',
			'checksum'             => self::get_request_checksum( $args ),
		);

		wp_send_json(
			apply_filters(
				'wc_key_manager_key_activation_response',
				$result,
				$activation,
				$key,
				$args
			)
		);
	}

	/**
	 * Deactivate key.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate_key( $request ) {
		$defaults = array(
			'key'        => '',
			'instance'   => wckm_get_default_instance(),
			'email'      => '',
			'product_id' => '',
		);

		$request = wp_parse_args( $request, $defaults );

		/**
		 * Filter the request data.
		 *
		 * @param array $request Request data.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'wc_key_manager_api_deactivate_key_args', $request );
		$code = isset( $args['key'] ) ? sanitize_text_field( wp_unslash( $args['key'] ) ) : '';

		$key = Key::find( array( 'key' => $code ) );
		if ( ! $key instanceof Key ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid_key',
					'message' => __( 'Invalid key, please provide a valid key.', 'wc-key-manager' ),
				)
			);
		}

		$deactivation = $key->deactivate( $args );
		if ( is_wp_error( $deactivation ) ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => $deactivation->get_error_code(),
					'message' => $deactivation->get_error_message(),
				)
			);
		}

		$result = array(
			'success'              => true,
			'message'              => __( 'Key deactivated successfully.', 'wc-key-manager' ),
			'instance'             => $deactivation->instance,
			'activation_limit'     => $key->activation_limit,
			'activation_count'     => $key->activation_count,
			'activation_remaining' => $key->activation_limit > 0 ? $key->activation_limit - $key->activation_count : 'unlimited',
			'expires'              => $key->get_expires() ? $key->get_expires() : 'never',
			'order_id'             => $key->order_id,
			'customer_name'        => $key->get_order() ? $key->get_order()->get_formatted_billing_full_name() : '',
			'customer_email'       => $key->get_order() ? $key->get_order()->get_billing_email() : '',
			'checksum'             => self::get_request_checksum( $args ),
		);

		wp_send_json(
			apply_filters(
				'wc_key_manager_key_deactivation_response',
				$result,
				$deactivation,
				$key,
				$args
			)
		);
	}

	/**
	 * Get version.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 */
	public static function get_version( $request ) {
		$defaults = array(
			'key'        => '',
			'instance'   => '',
			'email'      => '',
			'product_id' => '',
		);

		$request = wp_parse_args( $request, $defaults );

		/**
		 * Filter the request data.
		 *
		 * @param array $request Request data.
		 *
		 * @since 1.0.0
		 */
		$args       = apply_filters( 'wc_key_manager_api_get_version_args', $request );
		$code       = isset( $args['key'] ) ? sanitize_text_field( wp_unslash( $args['key'] ) ) : '';
		$instance   = isset( $args['instance'] ) ? wckm_sanitize_instance( wp_unslash( $args['instance'] ) ) : wckm_get_default_instance();
		$product_id = isset( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;

		$key = Key::find( array( 'key' => $code ) );
		// If key is not found and product id is not provided, return error.
		if ( ! $key && empty( $product_id ) ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid',
					'message' => __( 'Invalid key or product id, please provide a valid key or product id.', 'wc-key-manager' ),
				)
			);
		}
		$product_id = $key ? $key->product_id : $product_id;
		$product    = wc_get_product( $product_id );

		// If product is not found, return error.
		if ( ! $product ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid_product',
					'message' => __( 'Invalid product, product not found.', 'wc-key-manager' ),
				)
			);
		}

		$file          = $product->get_meta( '_wckm_software_file', true );
		$version       = $product->get_meta( '_wckm_software_version', true );
		$slug          = ! empty( $request['slug'] ) ? sanitize_text_field( $request['slug'] ) : $product->get_slug();
		$description   = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
		$description   = strip_shortcodes( $description );
		$changelog     = $product->get_meta( '_wckm_software_changelog', true );
		$min_php       = $product->get_meta( '_wckm_software_min_php_version', true );
		$min_wp        = $product->get_meta( '_wckm_software_min_wp_version', true );
		$changelog_url = site_url( '?wckm-api=get_changelog&product_id=' . $product->get_id() );

		$response = array(
			'success'        => true,
			'new_version'    => $version,
			'stable_version' => $version,
			'name'           => $product->get_name(),
			'slug'           => $slug,
			'min_php'        => $min_php,
			'min_wp'         => $min_wp,
			'url'            => esc_url( $changelog_url ),
			'package'        => '',
			'download_link'  => '',
			'last_updated'   => $product->get_date_modified() ? $product->get_date_modified()->date( 'Y-m-d' ) : $product->get_date_created()->date( 'Y-m-d' ),
			'homepage'       => get_permalink( $product->get_id() ),
			'sections'       => maybe_serialize(
				array(
					'description' => wpautop( strip_tags( $description, '<p><li><ul><ol><strong><a><em><span><br>' ) ),
					'changelog'   => wpautop( strip_tags( stripslashes( $changelog ), '<p><li><ul><ol><strong><a><em><span><br>' ) ),
				)
			),
			'banners'        => array(),
			'icons'          => array(),
		);

		if ( ! empty( $file ) && $key && ! $key->is_expired() ) {
			$hours                     = '+' . apply_filters( 'wc_key_manager_download_link_expires', '24hours' );
			$expires                   = strtotime( $hours, wp_date( 'U' ) );
			$hash                      = md5( $product->get_id() . $key->code . $instance . (int) $expires );
			$token                     = base64_encode( sprintf( '%s:%s:%d:%s:%s', $expires, $key->code, $product->get_id(), $hash, $instance ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Base64 encoding is used to encode the token.
			$package_url               = site_url( '?wckm-api=download&token=' . $token );
			$response['package']       = $package_url;
			$response['download_link'] = $package_url;
		}

		if ( has_post_thumbnail( $product->get_id() ) ) {
			$thumbnail_id          = get_post_thumbnail_id( $product->get_id() );
			$thumbnail             = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			$response['banners'][] = array(
				'low'  => $thumbnail[0],
				'high' => $thumbnail[0],
			);
			$response['icons'][]   = array(
				'low'  => $thumbnail[0],
				'high' => $thumbnail[0],
			);
		}

		$response['banners'] = maybe_serialize( $response['banners'] );
		$response['icons']   = maybe_serialize( $response['icons'] );

		/**
		 * Filter the response data.
		 *
		 * @param array $response Response data.
		 * @param array $args Request data.
		 * @param Key   $key Key object.
		 *
		 * @since 1.0.0
		 */
		$response = apply_filters( 'wc_key_manager_api_get_version_response', $response, $args, $key );

		// Encode emojis.
		if ( function_exists( 'wp_encode_emoji' ) ) {
			$response['name'] = wp_encode_emoji( $response['name'] );

			$sections             = maybe_unserialize( $response['sections'] );
			$response['sections'] = maybe_serialize( array_map( 'wp_encode_emoji', $sections ) );
		}

		wp_send_json( $response );
	}

	/**
	 * Get changelog.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 */
	public static function get_changelog( $request ) {
		$defaults = array(
			'product_id' => '',
		);

		$request    = wp_parse_args( $request, $defaults );
		$product_id = isset( $request['product_id'] ) ? absint( $request['product_id'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid_product',
					'message' => __( 'Invalid product, please provide a valid product id.', 'wc-key-manager' ),
				)
			);
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'invalid_product',
					'message' => __( 'Invalid product, product not found.', 'wc-key-manager' ),
				)
			);
		}

		$changelog = $product->get_meta( '_wckm_software_changelog', true );
		$changelog = strip_shortcodes( $changelog );
		if ( empty( $changelog ) ) {
			esc_html_e( 'No changelog found.', 'wc-key-manager' );

			return;
		}

		echo wp_kses_post( wpautop( wptexturize( $changelog ) ) );
		exit;
	}

	/**
	 * Download package.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 */
	public static function download_package( $request ) {
		$token  = isset( $request['token'] ) ? sanitize_text_field( wp_unslash( $request['token'] ) ) : '';
		$values = explode( ':', base64_decode( $token ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Base64 decoding is used to decode the token.
		if ( empty( $values ) || count( $values ) !== 5 ) {
			wp_die( esc_html__( 'Invalid token.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}
		$expires    = $values[0];
		$code       = $values[1];
		$product_id = (int) $values[2];
		$instance   = str_replace( '@', ':', $values[4] );

		$key = Key::find( array( 'key' => $code ) );

		// If key is not found, return error.
		if ( ! $key ) {
			wp_die( esc_html__( 'Invalid key.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		// If key is expired, return error.
		if ( $key->is_expired() ) {
			wp_die( esc_html__( 'Key is expired.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		// If download link is expired, return error.
		if ( wp_date( 'U' ) > $expires ) {
			wp_die( esc_html__( 'Your download link has expired.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		$computed_hash = md5( $product_id . $key->code . $instance . (int) $expires );
		if ( ! hash_equals( $computed_hash, $values[3] ) ) {
			wp_die( esc_html__( 'Provided hash does not validate.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		// If we have the instance still active, we can proceed with the download.
		$activation = $key->get_activation( $instance );
		if ( ! $activation || 'active' !== $activation->status ) {
			wp_die( esc_html__( 'Invalid instance.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_die( esc_html__( 'Invalid product.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		$file = $product->get_meta( '_wckm_software_file', true );
		if ( empty( $file ) ) {
			wp_die( esc_html__( 'No file found.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		if ( ! class_exists( 'WC_Download_Handler' ) || ! method_exists( 'WC_Download_Handler', 'download' ) ) {
			wp_die( esc_html__( 'Download handler not found.', 'wc-key-manager' ), array( 'response' => 401 ) );
		}

		// Download the file.
		$download = new \WC_Download_Handler();
		$download->download( $file, $product_id );
		exit;
	}

	/**
	 * Given an array of arguments, sort them by length, and then md5 them to generate a checksum.
	 *
	 * @param array $args Array of arguments to sort.
	 *
	 * @since 1.0
	 * @return string
	 */
	private static function get_request_checksum( $args = array() ) {
		// remove null values.
		$args = array_filter( $args );
		usort(
			$args,
			function ( $a, $b ) {
				return strlen( $a ) - strlen( $b );
			}
		);
		$string_args = wp_json_encode( $args );

		return md5( $string_args );
	}
}
