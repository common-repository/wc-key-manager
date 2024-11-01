<?php
/**
 * Product key options.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit;

echo '<div class="options_group">';
// Delivery quantity.
$delivery_qty = (int) get_post_meta( $product->get_id(), '_wckm_delivery_qty', true );
woocommerce_wp_text_input(
	array(
		'id'          => '_wckm_delivery_qty',
		'label'       => __( 'Delivery Quantity', 'wc-key-manager' ),
		'description' => __( 'Enter the number of keys to be delivered per quantity of the product.', 'wc-key-manager' ),
		'placeholder' => '1',
		'value'       => empty( $delivery_qty ) ? 1 : $delivery_qty,
		'type'        => 'number',
		'desc_tip'    => false,
	)
);

/**
 * Action hook to add more product key options.
 *
 * @since 1.0.0
 */
do_action( 'wc_key_manager_product_key_options', $product );

echo '</div>';
