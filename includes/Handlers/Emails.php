<?php

namespace KeyManager\Handlers;

use KeyManager\Emails\CustomerKeys;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class Emails.
 *
 * Handles emails.
 *
 * @since 1.0.0
 * @package KeyManager\Emails
 */
class Emails {

	/**
	 * Emails constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_email_classes' ) );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'send_customer_keys' ), 20 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'send_customer_keys' ), 20 );
		add_action( 'woocommerce_email_before_order_table', array( __CLASS__, 'email_after_order_table' ), 10, 4 );
		add_action( 'wc_key_manager_email_keys_details', array( __CLASS__, 'email_keys_details' ), 20, 4 );
	}

	/**
	 * Get email_keys_columns.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function get_email_keys_columns() {
		return apply_filters(
			'wc_key_manager_email_keys_columns',
			array(
				'product' => __( 'Product', 'wc-key-manager' ),
				'key'     => __( 'Key', 'wc-key-manager' ),
				'expires' => __( 'Expires', 'wc-key-manager' ),
			)
		);
	}

	/**
	 *  Add a key email to the list of emails WooCommerce should load.
	 *
	 * @param array $email_classes available email classes.
	 *
	 * @since 1.0.0
	 * @return array filtered available email.
	 */
	public static function add_email_classes( $email_classes ) {
		$email_classes['WCKM_Email_Customer_Keys'] = new CustomerKeys();

		return $email_classes;
	}

	/**
	 * Send customer email.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @since 1.0.0
	 */
	public static function send_customer_keys( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( ! wckm_order_has_products( $order->get_id() ) ) {
			return;
		}

		// if already sent, don't send again.
		if ( $order->get_meta( '_wckm_customer_keys_sent' ) ) {
			return;
		}

		if ( 'yes' !== get_option( 'wckm_automatic_delivery', 'yes' ) ) {
			return;
		}

		// Send the email.
		WC()->mailer()->emails['WCKM_Email_Customer_Keys']->trigger( $order_id );

		// Mark the email as sent.
		$order->update_meta_data( '_wckm_customer_keys_sent', 'yes' );
	}

	/**
	 * Add content to the email after the order table.
	 *
	 * @param \WC_Order $order Order object.
	 * @param bool      $sent_to_admin Sent to admin.
	 * @param bool      $plain_text Plain text.
	 * @param \WC_Email $email Email object.
	 *
	 * @since 1.0.0
	 */
	public static function email_after_order_table( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! wckm_order_has_products( $order->get_id() ) ) {
			return;
		}

		if ( 'yes' === get_option( 'wckm_hide_keys_from_processing_email', 'no' ) && 'processing' === $order->get_status() ) {
			return;
		}

		if ( 'yes' === get_option( 'wckm_hide_keys_from_completed_email', 'no' ) && 'completed' === $order->get_status() ) {
			return;
		}

		$keys = wckm_get_order_keys( $order->get_id() );

		// Determine the template path based on whether the email is plain text.
		$template = $plain_text ? 'emails/plain/email-keys.php' : 'emails/email-keys.php';
		wc_get_template(
			$template,
			array(
				'keys'          => $keys,
				'order'         => $order,
				'columns'       => self::get_email_keys_columns(),
				'sent_to_admin' => $sent_to_admin,
				'plain_text'    => $plain_text,
				'email'         => $email,
			),
			'',
			WCKM()->get_template_path()
		);
	}


	/**
	 * Add content to the email after the order table.
	 *
	 * @param \WC_Order $order The order object.
	 * @param bool      $sent_to_admin Whether the email is being sent to the admin.
	 * @param bool      $plain_text Whether the email is plain text.
	 * @param \WC_Email $email The email object.
	 *
	 * @since 1.0.0
	 */
	public static function email_keys_details( $order, $sent_to_admin, $plain_text, $email ) {
		$keys = wckm_get_order_keys( $order->get_id() );
		wc_get_template(
			'emails/email-keys.php',
			array(
				'keys'          => $keys,
				'order'         => $order,
				'sent_to_admin' => $sent_to_admin,
				'plain_text'    => $plain_text,
				'email'         => $email,
				'columns'       => self::get_email_keys_columns(),
			),
			'',
			WCKM()->get_template_path()
		);
	}
}
