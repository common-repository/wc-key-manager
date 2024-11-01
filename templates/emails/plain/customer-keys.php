<?php
/**
 * Customer keys (plain text).
 *
 *  This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/customer-keys.php.
 *
 *  HOWEVER, on occasion WooCommerce will need to update template files and you
 *  (the theme developer) will need to copy the new files to your theme to
 *  maintain compatibility. We try to do this as little as possible, but it does
 *  happen. When this occurs the version of the template file will be bumped and
 *  the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package KeyManager\Templates\Emails\Plain
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Customer first name */
printf( esc_html__( 'Hi %s,', 'wc-key-manager' ), esc_html( $order->get_billing_first_name() ) ) . "\n\n";
echo "\n\n";
if ( ! empty( $keys ) ) :
	echo esc_html(
		wp_strip_all_tags(
			sprintf(
			// translators: %1$d: Order number, %2$s: Order date.
				esc_html__(
					'Here are the key(s) of your order #%1$d placed on %2$s:',
					'wc-key-manager'
				),
				esc_html( $order->get_order_number() ),
				esc_html( wc_format_datetime( $order->get_date_created() ) )
			)
		)
	);
	echo "\n\n";
	esc_html_e( 'Key(s)', 'wc-key-manager' );
	echo "\n\n";
	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
	echo esc_html( implode( ' | ', $columns ) );
	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
	foreach ( $keys as $key ) :
		echo esc_html( $key->get_product_name() ) . ' | ' . esc_html( $key->key ) . ' | ' . esc_html( $key->get_expires_html() );
		echo "\n-------------------------------------------------------\n";
	endforeach;
endif;
echo "\n";
/**
 * Hook for woocommerce_email_customer_details.
 *
 * @param WC_Order $order The order object.
 * @param bool $sent_to_admin Whether the email is being sent to the admin.
 * @param bool $plain_text Whether the email is plain text.
 * @param WC_Email $email The email object.
 *
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
echo "\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
