<?php

namespace KeyManager\Handlers;

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Shortcodes
 *
 * @package KeyManager\Handlers
 */
class Shortcodes {

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_shortcode( 'wckm_validate_key', array( $this, 'validate_key' ) );
	}

	/**
	 * Add query vars
	 *
	 * @param array $vars Query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'key';

		return $vars;
	}

	/**
	 * Validate key
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function validate_key( $atts ) {
		$atts                   = shortcode_atts(
			array(
				'key_label'       => __( 'Key', 'wc-key-manager' ),
				'key_placeholder' => __( 'Enter your key', 'wc-key-manager' ),
				'email_field'     => 'no',
				'email_label'     => __( 'Email', 'wc-key-manager' ),
				'email_required'  => 'no',
				'submit_label'    => __( 'Validate', 'wc-key-manager' ),
			),
			$atts,
			'wckm_validate_key'
		);
		$atts['email_field']    = filter_var( $atts['email_field'], FILTER_VALIDATE_BOOLEAN );
		$atts['email_required'] = filter_var( $atts['email_required'], FILTER_VALIDATE_BOOLEAN );

		// get key.
		$uuid = get_query_var( 'key' );
		$key  = ! empty( $uuid ) ? Key::find( array( 'uuid' => $uuid ) ) : null;

		// If key found but expired show the notice.
		if ( $key && $key->is_expired() ) {
			wc_add_notice( __( 'The key has expired.', 'wc-key-manager' ), 'error' );
		} elseif ( $key && $key->is_valid() ) {
			wc_add_notice( __( 'The key is valid.', 'wc-key-manager' ), 'success' );
		}

		// enqueue woo styles.
		wp_enqueue_style( 'woocommerce-general' );

		ob_start();

		/**
		 * Hook: wc_key_manager_before_validate_key.
		 *
		 * @param Key $key Key object.
		 */
		do_action( 'wc_key_manager_before_validate_key', $key );

		wc_get_template(
			'key/validate.php',
			array(
				'atts' => $atts,
				'key'  => $key,
			),
			'',
			WCKM()->get_template_path()
		);

		return ob_get_clean();
	}
}
