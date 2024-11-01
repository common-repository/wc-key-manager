<?php
/**
 * Template for displaying the recent orders dashboard widget.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\Views
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
// get the last 5 unique order id and sum the total amount from wckm_keys table.
$orders = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT order_id, SUM( price ) as total
	FROM {$wpdb->prefix}wckm_keys
	WHERE order_id > 0
	GROUP BY order_id
	ORDER BY ordered_at DESC
	LIMIT %d",
		5
	)
)
?>
<div class="bk-list bk-card has--hover has--split has--striped">
	<h2 class="bk-list__header"><?php esc_html_e( 'Recent Orders', 'wc-key-manager' ); ?></h2>
	<div class="bk-list__item">
		<span><?php esc_html_e( 'Order', 'wc-key-manager' ); ?></span>
		<span><?php esc_html_e( 'Amount', 'wc-key-manager' ); ?></span>
	</div>
	<?php if ( $orders ) : ?>
		<?php foreach ( $orders as $order ) : ?>
			<div class="bk-list__item">
				<span>
				<a href="<?php echo esc_url( get_edit_post_link( $order->order_id ) ); ?>">
					<?php
					printf(
						// translators: %d: order id.
						esc_html__( 'Order #%d', 'wc-key-manager' ),
						esc_html( $order->order_id )
					);
					?>
				</a>
				</span>
				<span><?php echo wp_kses_post( wc_price( $order->total ) ); ?></span>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="bk-list__item">
			<span><?php esc_html_e( 'No orders found', 'wc-key-manager' ); ?></span>
		</div>
	<?php endif; ?>
</div>
