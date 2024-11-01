<?php
/**
 * Customer keys.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-keys.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package KeyManager\Templates\Emails
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'wc-key-manager' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<?php if ( ! empty( $keys ) ) : ?>
	<p>
		<?php
		printf(
		// translators: %1$d: Order number, %2$s: Order date.
			esc_html__(
				'Here are the key(s) of your order #%1$d placed on %2$s:',
				'wc-key-manager'
			),
			esc_html( $order->get_order_number() ),
			esc_html( wc_format_datetime( $order->get_date_created() ) )
		);
		?>
	</p>
<?php endif; ?>
<?php
/**
 * Hook for the email keys.
 *
 * @param WC_Order $order The order object.
 * @param bool $sent_to_admin Whether the email is being sent to the admin.
 * @param bool $plain_text Whether the email is plain text.
 * @param WC_Email $email The email object.
 *
 * @hooked KeyManager\Handlers\Emails::email_keys_details())
 */
do_action( 'wc_key_manager_email_keys_details', $order, $sent_to_admin, $plain_text, $email );

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

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/**
 * Executes the email footer.
 *
 * @param WC_Email $email The email object.
 *
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
