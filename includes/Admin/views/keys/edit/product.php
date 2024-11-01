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

<?php if ( $key->get_product() ) : ?>

	<div class="bk-card">
		<div class="bk-card__header">
			<h2 class="bk-card__title"><?php esc_html_e( 'Product details', 'wc-key-manager' ); ?></h2>
			<a href="<?php echo esc_url( get_edit_post_link( $key->get_parent_product_id() ) ); ?>" target="_blank">
				<?php esc_html_e( 'View Product', 'wc-key-manager' ); ?>
			</a>
		</div>

		<div class="bk-card__body" style="--bcard-padding-x: 0;--bcard-padding-y: 0;">
			<div class="bk-table">
				<table>
					<tbody>
					<tr>
						<th><?php esc_html_e( 'Name', 'wc-key-manager' ); ?></th>
						<td><?php echo esc_html( $key->get_product_name() ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'SKU', 'wc-key-manager' ); ?></th>
						<td><?php echo esc_html( $key->get_product()->get_sku() ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Type', 'wc-key-manager' ); ?></th>
						<td><?php echo esc_html( $key->get_product()->get_type() ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Price', 'wc-key-manager' ); ?></th>
						<td><?php echo wp_kses_post( wc_price( $key->get_product()->get_price() ) ); ?></td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<?php
endif;
