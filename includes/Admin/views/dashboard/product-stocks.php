<?php
/**
 * Template for displaying the product stocks dashboard widget.
 *
 * @since   1.0.0
 * @package KeyManager
 * @Admin\Views
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

$ids     = wckm_get_products(
	array(
		'keyed'  => 'yes',
		'source' => 'preset',
		'return' => 'ids',
	)
);
$results = array();
if ( ! empty( $ids ) ) {
	// now get available keys count from keys table for each product.
	$results = $wpdb->get_results(
		"SELECT product_id, COUNT( id ) as total
	FROM {$wpdb->prefix}wckm_keys
	WHERE product_id IN (" . implode( ',', $ids ) . ')
	GROUP BY product_id
	ORDER BY product_id ASC
	LIMIT 5'
	);
	$results = wp_list_pluck( $results, 'total', 'product_id' );
	// if any of the product has no keys, then set the total to 0.
	foreach ( $results as $product_id => $total ) {
		if ( ! isset( $results[ $product_id ] ) ) {
			$results[ $product_id ] = 0;
		}
	}
}

// Now sort the results based on the values. from small to large.
asort( $results );
?>

<div class="bk-list bk-card has--hover has--split has--striped">
	<h2 class="bk-list__header"><?php esc_html_e( 'Product Stocks', 'wc-key-manager' ); ?></h2>
	<div class="bk-list__item">
		<span><?php esc_html_e( 'Product', 'wc-key-manager' ); ?></span>
		<span><?php esc_html_e( 'Stocks', 'wc-key-manager' ); ?></span>
	</div>
	<?php if ( ! empty( $results ) ) : ?>
		<?php foreach ( $results as $product_id => $total ) : ?>
			<?php
			$product = wc_get_product( $product_id );
			?>
			<div class="bk-list__item">
				<span><?php printf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $product_id ) ), esc_html( $product->get_name() ) ); ?></span>
				<span><?php echo esc_html( $total ); ?></span>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="bk-list__item">
			<span><?php esc_html_e( 'No products found.', 'wc-key-manager' ); ?></span>
		</div>
	<?php endif; ?>
</div>
