<?php
/**
 * Product key options.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\Views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit;
?>
	<div id="wckm_product_options_data" class="panel woocommerce_options_panel wckm_product_options" style="display: none;">
		<?php
		/**
		 * Action after key settings.
		 *
		 * @param WC_Product $product Product object.
		 *
		 * @since 1.0.0
		 */
		do_action( "wc_key_manager_{$product->get_type()}_product_options", $product );

		/**
		 * Action after key settings.
		 *
		 * @param WC_Product $product Product object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_product_options', $product );

		?>
	</div>
<?php
