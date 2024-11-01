<?php
/**
 * The template for general tools.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<hr class="wp-header-end">

<div class="bk-card">
	<div class="bk-card__header">
		<h2 class="bk-card__title"><?php esc_html_e( 'Bulk Keys Generator', 'wc-key-manager' ); ?></h2>
	</div>
	<div class="bk-card__body">
		<p>
			<?php esc_html_e( 'Generate keys in bulk for a product. You can generate keys in bulk for a product using this tool.', 'wc-key-manager' ); ?>
			<br>
			<?php // Translators: %1$s and %2$s are HTML tags. ?>
			<?php printf( esc_html__( '%1$sNote%2$s: Product key source will be automatically change to "Preset" if it is not already set & Generated keys will be treated as preset keys.', 'wc-key-manager' ), '<strong>', '</strong>' ); ?>
		</p>

		<form method="POST" action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" name="wckm-bulk-key-generator" id="wckm-bulk-key-generator" class="wckm-bulk-key-generator inline--fields">
			<div class="bk-form-field">
				<label for="product_id"><?php esc_html_e( 'Product *', 'wc-key-manager' ); ?></label>
				<select id="product_id" name="product_id" class="wckm_select2 regular-text" data-action="wckm_json_search" data-type="product" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Select a  product...', 'wc-key-manager' ); ?>" required>
					<option value=""><?php esc_html_e( 'Select a key enabled product...', 'wc-key-manager' ); ?></option>
					<?php foreach ( wc_get_products( array() ) as $product ) : ?>
						<option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( sprintf( '%s (#%s)', $product->get_name(), $product->get_id() ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?php esc_html_e( 'Select the product associated with the key.', 'wc-key-manager' ); ?>
				</p>
			</div>

			<div class="bk-form-field">
				<label for="generator_id"><?php esc_html_e( 'Generator', 'wc-key-manager' ); ?></label>
				<select id="generator_id" name="generator_id" class="wckm_select2 regular-text" data-action="wckm_json_search" data-type="generator" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Select a  Generator...', 'wc-key-manager' ); ?>">
					<option value=""><?php esc_html_e( 'Select a key generator...', 'wc-key-manager' ); ?></option>
				</select>

				<p class="description">
					<?php esc_html_e( 'Select a specific key generator or leave empty to use default settings.', 'wc-key-manager' ); ?>
				</p>

			</div>

			<!-- Quantity -->
			<div class="bk-form-field">
				<label for="quantity"><?php esc_html_e( 'Quantity *', 'wc-key-manager' ); ?></label>
				<input type="number" id="quantity" name="quantity"  class="regular-text" value="10" max="500" required>
				<p class="description">
					<?php esc_html_e( 'Enter the number of keys to generate.', 'wc-key-manager' ); ?>
				</p>
			</div>
			<?php wp_nonce_field( 'wckm_generate_bulk_keys' ); ?>
			<input type="hidden" name="action" value="wckm_generate_bulk_keys">
		</form>
	</div>
	<div class="bk-card__footer">
		<button type="submit" form="wckm-bulk-key-generator" id="wckm-bulk-key-generator-btn" class="button button-primary"><?php esc_html_e( 'Generate Keys', 'wc-key-manager' ); ?></button>
	</div>
</div>
