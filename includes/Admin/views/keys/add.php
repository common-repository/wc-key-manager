<?php
/**
 * The template for adding a key.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var Key $key
 */

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;
?>

<h1>
	<?php esc_html_e( 'Add Key', 'wc-key-manager' ); ?>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Go Back', 'wc-key-manager' ); ?>
	</a>
</h1>

<p><?php esc_html_e( 'You can create a new key here. This form will create a key for the user, and optionally an associated order. Created orders will be marked as pending payment.', 'wc-key-manager' ); ?></p>
<br>
<form id="wckm-add-key-form" class="inline--fields" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php
	/**
	 * Action hook to add custom fields.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wc_key_manager_before_add_key_form_fields' );
	?>
	<div class="bk-form-field">
		<label for="key"><?php esc_html_e( 'Key', 'wc-key-manager' ); ?><abbr title="required"></abbr></label>
		<div class="bk-input-group" style="max-width: 350px">
			<input type="text" name="key" id="key" class="wckm_key_code regular-text" required="required" placeholder="SERIAL-ABC-DEF-GHI"/>
			<!--Generate button as addon-->
			<button type="button" class="button button-secondary wckm_generate_key">
				<span class="dashicons dashicons-update"></span>
			</button>
		</div>
		<p class="description">
			<?php esc_html_e( 'Enter the key, It\'s will be sent to the customer when they purchase the associated product.', 'wc-key-manager' ); ?>
		</p>
	</div>

	<div class="bk-form-field">
		<label for="product_id"><?php esc_html_e( 'Product', 'wc-key-manager' ); ?><abbr title="required"></abbr></label>
		<select id="product_id" name="product_id" class="wckm_select2 regular-text" data-action="wckm_json_search" data-type="product" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Select a  product...', 'wc-key-manager' ); ?>" required>
			<option value=""><?php esc_html_e( 'Select a product...', 'wc-key-manager' ); ?></option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the product associated with the key. When customer purchase this product, the key will be sent to them.', 'wc-key-manager' ); ?>
		</p>
	</div>

	<div class="bk-form-field">
		<label for="wckm_valid_for"><?php esc_html_e( 'Valid For (days)', 'wc-key-manager' ); ?></label>
		<input type="number" name="valid_for" id="wckm_valid_for" class="regular-text wckm-valid-for" min="0" placeholder="0"/>
		<p class="description">
			<?php esc_html_e( 'Relative expiration dates from the date of purchase. Leave empty for no expiration.', 'wc-key-manager' ); ?>
		</p>
	</div>

	<div class="bk-form-field">
		<label for="wckm_date_expires"><?php esc_html_e( 'Date Expires', 'wc-key-manager' ); ?></label>
		<input type="text" name="expires_at" id="wckm_date_expires" class="regular-text wckm_datepicker" placeholder="yyyy-mm-dd" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"/>
		<p class="description">
			<?php esc_html_e( 'Specific expiration date for the key. Leave empty for no expiration.', 'wc-key-manager' ); ?>
		</p>
	</div>

	<div class="bk-form-field">
		<label for="activation_limit"><?php esc_html_e( 'Activation Limit', 'wc-key-manager' ); ?></label>
		<input type="number" name="activation_limit" id="activation_limit" class="regular-text" min="0" placeholder="0"/>
		<p class="description">
			<?php esc_html_e( 'Enter the number of times the key can be activated. Leave empty for unlimited activations.', 'wc-key-manager' ); ?>
		</p>
	</div>

	<?php
	/**
	 * Action hook to add custom fields.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wc_key_manager_after_add_key_form_fields' );
	?>

	<div class="bk-form-field">
		<label for="">&nbsp;</label>
		<div>
			<p>
				<label>
					<input type="radio" name="action_type" value="new_key" class="checkbox" checked/>
					<?php esc_html_e( 'Create as a new key & set the status to available.', 'wc-key-manager' ); ?>
					<span class="tips" data-tip="<?php esc_attr_e( 'This will create a new key and set the status to available.', 'wc-key-manager' ); ?>">[?]</span>
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="action_type" value="create_order" class="checkbox"/>
					<?php esc_html_e( 'Create a new corresponding order for this key.', 'wc-key-manager' ); ?>
					<span class="tips" data-tip="<?php esc_attr_e( 'This will first create a new key and then create a corresponding order with the selected product and assign the key to that order.', 'wc-key-manager' ); ?>">[?]</span>
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="action_type" value="existing_order" class="checkbox"/>
					<?php esc_html_e( 'Assign this key to an existing order.', 'wc-key-manager' ); ?>
					<span class="tips" data-tip="<?php esc_attr_e( 'This will check the existing order and if the selected product is found without a key, it will assign the key to that item otherwise it will add a new product to the order and assign the key.', 'wc-key-manager' ); ?>">[?]</span>
				</label>
			</p>
		</div>
	</div>

	<div class="bk-form-field wckm_show_if_action_type__create_order" style="display: none">
		<label for="customer_id"><?php esc_html_e( 'Customer', 'wc-key-manager' ); ?></label>
		<select name="customer_id" id="customer_id" class="wckm_select2 regular-text" data-action="wckm_json_search" data-type="customer" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Select a customer...', 'wc-key-manager' ); ?>">
			<option value=""><?php esc_html_e( 'Select a customer...', 'wc-key-manager' ); ?></option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the customer associated with the key.', 'wc-key-manager' ); ?>
			<!--Add customer link-->
			<a href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>" target="_blank">
				<?php esc_html_e( 'Add Customer', 'wc-key-manager' ); ?>
			</a>
		</p>
	</div>

	<div class="bk-form-field wckm_show_if_action_type__existing_order" style="display: none">
		<label for="order_id"><?php esc_html_e( 'Order', 'wc-key-manager' ); ?></label>
		<select name="order_id" id="order_id" class="wckm_select2 regular-text" data-action="wckm_json_search" data-type="order" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Select an order...', 'wc-key-manager' ); ?>">
			<option value=""><?php esc_html_e( 'Select an order...', 'wc-key-manager' ); ?></option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the order to which the key should be assigned.', 'wc-key-manager' ); ?>
		</p>
	</div>

	<div class="bk-form-field">
		<label for="">&nbsp</label>
		<div>
			<input type="hidden" name="action" value="wckm_add_key"/>
			<?php wp_nonce_field( 'wckm_add_key' ); ?>
			<?php submit_button( __( 'Add Key', 'wc-key-manager' ), 'primary', 'add_key' ); ?>
		</div>
	</div>
</form>
