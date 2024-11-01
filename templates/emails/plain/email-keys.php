<?php
/**
 * Plain Email Keys.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/email-keys.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
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
