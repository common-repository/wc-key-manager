<?php
/**
 * Product key type specific settings.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\Views
 * @var $product \WC_Product Product object.
 */

use KeyManager\Models\Key;
use KeyManager\Models\Generator;

defined( 'ABSPATH' ) || exit;
?>
<div class="options_group">
	<?php
	$generator_id = get_post_meta( $product->get_id(), '_wckm_generator_id', true );
	$generator    = Generator::find( $generator_id );
	$key_source   = get_post_meta( $product->get_id(), '_wckm_key_source', true );
	$sources      = Key::get_sources();


	if ( ! array_key_exists( $key_source, $sources ) ) {
		$key_source = current( array_keys( $sources ) );
	}

	$stock = wckm_get_key_stock( $product->get_id() );

	woocommerce_wp_select(
		array(
			'id'          => '_wckm_key_source',
			'label'       => __( 'Key Source', 'wc-key-manager' ),
			'value'       => $key_source,
			'options'     => $sources,
			'desc_tip'    => true,
			'description' => __( 'Automatically generate keys or use preset keys.', 'wc-key-manager' ),
		)
	);

	?>
	<p class="form-field _wckm_preset_generator_field wckm_show_if_key_source__automatic">
		<label for="_wckm_generator_id"><?php esc_html_e( 'Key Generator', 'wc-key-manager' ); ?></label>
		<select id="_wckm_generator_id" name="_wckm_generator_id" class="wckm_select2" data-action="wckm_json_search" data-type="generator" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" style="width: 50%;" data-placeholder="<?php esc_attr_e( 'Select a  Generator...', 'wc-key-manager' ); ?>">
			<?php if ( $generator ) : ?>
				<option value="<?php echo esc_attr( $generator->id ); ?>" selected><?php echo esc_html( $generator->name ); ?></option>
			<?php endif; ?>
		</select>
		<span class="description" style="clear: both;display: block;margin-left: 0;">
			<?php esc_html_e( 'Select a specific key generator or leave empty to use default settings.', 'wc-key-manager' ); ?>
		</span>
	</p>

	<p class="form-field _wckm_preset_stock_field wckm_show_if_key_source__preset">
		<label><?php esc_html_e( 'Preset Stock', 'wc-key-manager' ); ?></label>
		<span class="description">
			<?php
			echo wp_kses_post(
				sprintf(
					// translators: %s: Number of keys.
					__( 'You have %s preset keys for this product.', 'wc-key-manager' ),
					'<strong>' . number_format( $stock ) . '</strong>'
				)
			);
			?>
		</span>
	</p>
	<?php
	// Generate sequential keys.
	woocommerce_wp_checkbox(
		array(
			'id'            => '_wckm_is_sequential',
			'label'         => __( 'Sequential Keys', 'wc-key-manager' ),
			'description'   => __( 'Generate keys in sequential order.', 'wc-key-manager' ),
			'value'         => get_post_meta( $product->get_id(), '_wckm_is_sequential', true ),
			'wrapper_class' => 'wckm_show_if_key_source__automatic',
		)
	);

	/**
	 * Action after source of key settings.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wc_key_manager_product_source_options', $product );
	?>
</div>
