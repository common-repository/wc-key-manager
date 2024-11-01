<?php
/**
 * Top products dashboard widget.
 *
 * @since   1.0.0
 * @package KeyManager
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;
// find most top 5 products.
$results = $wpdb->get_results(
	"SELECT product_id, SUM( price ) as total
	FROM {$wpdb->prefix}wckm_keys
	GROUP BY product_id
	ORDER BY total DESC
	LIMIT 5"
);

?>
<div class="bk-list bk-card has--hover has--split has--striped">
	<h2 class="bk-list__header"><?php esc_html_e( 'Top Products', 'wc-key-manager' ); ?></h2>
	<div class="bk-list__item">
		<span><?php esc_html_e( 'Product', 'wc-key-manager' ); ?></span>
		<span><?php esc_html_e( 'Total', 'wc-key-manager' ); ?></span>
	</div>
	<?php if ( ! empty( $results ) ) : ?>
		<?php foreach ( $results as $result ) : ?>
			<?php $product = wc_get_product( $result->product_id ); ?>
			<?php if ( $product ) : ?>
				<div class="bk-list__item">
					<span><?php printf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $product->get_id() ) ), esc_html( $product->get_name() ) ); ?></span>
					<span><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="bk-list__item">
			<span><?php esc_html_e( 'No products found.', 'wc-key-manager' ); ?></span>
		</div>
	<?php endif; ?>
</div>
