<?php
/**
 * View key template.
 *
 * Shows list of keys customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/key/view.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 1.0.0
 *
 * @package KeyManager/Templates
 *
 * @var Key    $key The key object.
 * @var string $context The context of the view.
 */

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! $key ) {
	return;
}

$order   = $key->get_order();
$product = $key->get_product();

/**
 * Hook: wc_key_manager_before_key_details.
 *
 * @param Key      $key The key object.
 * @param WC_Order $order The order object.
 *
 * @since 1.0.0
 */
do_action( 'wc_key_manager_before_key_details', $key, $order );

?>
	<section class="woocommerce-order-details woocommerce-order-details__key">
		<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Key Details', 'wc-key-manager' ); ?></h2>
		<p class="order-info">
			<?php
			echo wp_kses_post(
				sprintf(
				/* translators: 1: order number 2: order date 3: order status */
					__( 'The key was purchased on %1$s and the order ID is: %3$s.', 'wc-key-manager' ),
					'<mark class="order-date">' . wp_date( get_option( 'date_format' ), strtotime( $key->ordered_at ) ) . '</mark>',
					'<mark class="order-status">' . wc_get_order_status_name( $key->get_status_label() ) . '</mark>',
					'<a class="order-number" href="' . esc_url( $key->get_order()->get_view_order_url() ) . '">#' . esc_html( $key->get_order()->get_order_number() ) . '</a>'
				)
			);
			?>
		</p>
		<table class="shop_table shop_table_responsive my_account_orders woocommerce-orders-table woocommerce-MyAccount-orders woocommerce-orders-table--key">
			<tbody>
			<?php
			/**
			 * Hook: wc_key_manager_before_key_details.
			 *
			 * @param Key      $key The key object.
			 * @param WC_Order $order The order object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_key_manager_before_formatted_key_attributes', $key, $order );
			?>
			<?php foreach ( $key->get_formatted_attributes( $context ) as $attribute ) : ?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row-<?php echo esc_attr( $attribute['key'] ); ?>">
					<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $attribute['key'] ); ?>-label">
						<?php echo esc_html( $attribute['label'] ); ?>
					</th>
					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $attribute['key'] ); ?>-value">
						<?php
						/**
						 * Action before rendering the key.
						 *
						 * @param Key $key The key object.
						 */
						do_action( 'wc_key_manager_before_formatted_key_attribute_' . $attribute['key'], $key );
						?>
						<?php if ( ! empty( $attribute['content'] ) && is_scalar( $attribute['content'] ) ) : ?>
							<span><?php echo wp_kses_post( $attribute['content'] ); ?></span>
						<?php endif; ?>

						<?php
						/**
						 * Action after rendering the key.
						 *
						 * @param Key $key The key object.
						 */
						do_action( 'wc_key_manager_after_formatted_key_attribute_' . $attribute['key'], $key );
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php
			/**
			 * Hook: wc_key_manager_before_key_details.
			 *
			 * @param Key      $key The key object.
			 * @param WC_Order $order The order object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_key_manager_after_formatted_key_attributes', $key, $order );
			?>
			</tbody>
		</table>
	</section>
<?php

/**
 * Hook: wc_key_manager_after_key_details.
 *
 * @param Key      $key The key object.
 * @param WC_Order $order The order object.
 *
 * @since 1.0.0
 */
do_action( 'wc_key_manager_after_key_details', $key, $order );
