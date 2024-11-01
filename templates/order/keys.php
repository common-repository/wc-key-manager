<?php
/**
 * Order keys template.
 *
 * Shows list of keys customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/keys.php.
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
 */

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>

<section class="wckm-order-keys">

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Keys', 'wc-key-manager' ); ?></h2>

	<?php if ( in_array( $order->get_status(), wckm_get_order_paid_statuses(), true ) && ! empty( $keys ) ) : ?>
		<table class="shop_table shop_table_responsive woocommerce-orders-table woocommerce-MyAccount-orders woocommerce-orders-table--keys">
			<thead>
			<tr>
				<?php foreach ( $columns as $column_id => $column_name ) : ?>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>">
						<span class="nobr"><?php echo esc_html( $column_name ); ?></span>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $keys as $key ) : ?>
				<tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $key->status ); ?>">
					<?php foreach ( $columns as $column_id => $column_name ) : ?>
						<td class="order-<?php echo esc_attr( $column_id ); ?> woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php
							/**
							 * Action before rendering the key.
							 *
							 * @param Key $key The key object.
							 */
							do_action( 'wc_key_manager_order_details_keys_before_column_' . $column_id, $key );

							switch ( $column_id ) {
								case 'product':
									echo '<a href="' . esc_url( $key->get_product_url() ) . '">' . esc_html( $key->get_product_name() ) . '</a>';
									break;
								case 'key':
									echo wp_kses_post( $key->get_key_html() );
									break;
								case 'expires':
									echo wp_kses_post( $key->get_expires_html() );
									break;
								case 'status':
									echo wp_kses_post( $key->get_status_html() );
									break;
								case 'actions':
									?>
									<a href="<?php echo esc_url( $key->get_view_key_url() ); ?>" class="button view">
										<?php esc_html_e( 'View', 'wc-key-manager' ); ?>
									</a>
									<?php
									break;
							}

							/**
							 * Action after rendering the key.
							 *
							 * @param Key $key The key object.
							 */
							do_action( 'wc_key_manager_order_details_keys_column_' . $column_id, $key );
							?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<?php
		/**
		 * Filter the message shown when there are no keys.
		 *
		 * @param string $message The message shown when there are no keys.
		 * @param \WC_Order $order The order object.
		 */
		echo wp_kses_post(
			wpautop(
				wptexturize(
					apply_filters(
						'wc_key_manager_order_keys_no_keys_message',
						esc_html__( 'Your keys will be dispatched shortly. The delivery timeframe ranges from a few minutes to a maximum of 24 hours, depending on payment processing and our internal procedures. Your understanding and patience are highly valued.', 'wc-key-manager' ),
						$order
					)
				),
			)
		);
		?>
	<?php endif; ?>
</section>
