<?php
/**
 * Template for displaying the recent customers dashboard widget.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\Views
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
// get the last 5 unique order id and sum the total amount from wckm_keys table.
$customers = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT customer_id, SUM( price ) as total
	FROM {$wpdb->prefix}wckm_keys
	WHERE customer_id > 0
	GROUP BY order_id, customer_id
	ORDER BY ordered_at DESC
	LIMIT %d",
		5
	)
)
?>
<div class="bk-list bk-card has--hover has--split has--striped">
	<h2 class="bk-list__header"><?php esc_html_e( 'Recent Customers', 'wc-key-manager' ); ?></h2>
	<div class="bk-list__item">
		<span><?php esc_html_e( 'Customer', 'wc-key-manager' ); ?></span>
		<span><?php esc_html_e( 'Amount', 'wc-key-manager' ); ?></span>
	</div>
	<?php if ( $customers ) : ?>
		<?php foreach ( $customers as $customer ) : ?>
			<div class="bk-list__item">
				<span>
				<?php
				// get the customer name.
				$user = new \WC_Customer( $customer->customer_id );
				// if the customer exists.
				if ( $user ) {
					$user = $user->get_display_name();
				} else {
					$user = esc_html__( 'Guest', 'wc-key-manager' );
				}
				printf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=shop_order&customer_id=' . $customer->customer_id ) ), esc_html( $user ) );
				?>
				</span>
				<span><?php echo wp_kses_post( wc_price( $customer->total ) ); ?></span>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="bk-list__item">
			<span><?php esc_html_e( 'No customers found', 'wc-key-manager' ); ?></span>
		</div>
	<?php endif; ?>
</div>
