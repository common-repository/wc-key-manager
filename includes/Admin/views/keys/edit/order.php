<?php
/**
 * The template for order related to a key.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Key $key
 */

defined( 'ABSPATH' ) || exit;

?>

<?php if ( $key->get_order() ) : ?>

	<div class="bk-card">
		<div class="bk-card__header">
			<h2 class="bk-card__title"><?php esc_html_e( 'Order details', 'wc-key-manager' ); ?></h2>
			<a href="<?php echo esc_url( get_edit_post_link( $key->order_id ) ); ?>" target="_blank">
				<?php esc_html_e( 'View Order', 'wc-key-manager' ); ?>
			</a>
		</div>

		<div class="bk-card__body" style="--bcard-padding-x: 0;--bcard-padding-y: 0;">
			<div class="bk-table">
				<table>
					<tbody>
					<tr>
						<th><?php esc_html_e( 'Order', 'wc-key-manager' ); ?></th>
						<td>#<?php echo esc_html( $key->order_id ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Order Date', 'wc-key-manager' ); ?></th>
						<td><?php echo esc_html( $key->get_order()->get_date_created()->format( 'Y-m-d H:i:s' ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Order Status', 'wc-key-manager' ); ?></th>
						<td><?php echo esc_html( wc_get_order_status_name( $key->get_order()->get_status() ) ); ?></td>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<?php
endif;
