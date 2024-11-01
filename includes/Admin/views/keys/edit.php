<?php
/**
 * The template for editing a key.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Key $key
 */

defined( 'ABSPATH' ) || exit;
?>
<h1 class="wp-heading-inline">
	<?php esc_html_e( 'Edit Key', 'wc-key-manager' ); ?>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys&add=yes' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add Another', 'wc-key-manager' ); ?>
	</a>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Go Back', 'wc-key-manager' ); ?>
	</a>
</h1>

<form id="wckm-edit-key-form" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
	<div class="bk-poststuff">
		<div class="column-1">
			<?php
			/**
			 * Fires before the key edit form.
			 *
			 * @param \KeyManager\Models\Key $key The key being edited.
			 */
			do_action( 'wc_key_manager_start_edit_key_form', $key );
			?>
			<div class="bk-card">
				<div class="bk-card__header">
					<h2 class="bk-card__title"><?php esc_html_e( 'Key Attributes', 'wc-key-manager' ); ?></h2>
				</div>
				<div class="bk-card__body inline--fields">
					<?php
					/**
					 * Fires before the key edit form fields.
					 *
					 * @param \KeyManager\Models\Key $key The key being edited.
					 */
					do_action( 'wc_key_manager_before_edit_key_form_fields', $key );
					?>
					<div class="bk-form-field">
						<label for="key">
							<?php esc_html_e( 'key', 'wc-key-manager' ); ?>
							<abbr title="required"></abbr>
						</label>
						<input type="text" name="key" id="key" required="required" placeholder="####-####-####-####" value="<?php echo esc_attr( $key->code ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'The key that will be sent to the customer.', 'wc-key-manager' ); ?>
						</p>
					</div>

					<div class="bk-form-field">
						<label for="valid_for">
							<?php esc_html_e( 'Valid For (days)', 'wc-key-manager' ); ?>
						</label>
						<input type="number" name="valid_for" id="valid_for" placeholder="0" step="any" min="0" value="<?php echo esc_attr( $key->valid_for ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Relative expiration date in number from the time of purchase. Leave it blank for lifetime validity. Use either "Valid For" or "Date Expires".', 'wc-key-manager' ); ?>
						</p>
					</div>

					<div class="bk-form-field">
						<label for="expires_at">
							<?php esc_html_e( 'Date Expires', 'wc-key-manager' ); ?>
						</label>
						<input type="text" name="expires_at" id="expires_at" class="wckm_datepicker" placeholder="yyyy-mm-dd" value="<?php echo esc_attr( $key->expires_at ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Specific expiration date for the key. Leave empty for no expiration. Use either "Valid For" or "Date Expires".', 'wc-key-manager' ); ?>
						</p>
					</div>
					<div class="bk-form-field">
						<label for="activation_limit">
							<?php esc_html_e( 'Activation Limit', 'wc-key-manager' ); ?>
						</label>
						<input type="number" name="activation_limit" id="activation_limit" placeholder="0" step="any" min="0" value="<?php echo esc_attr( $key->activation_limit ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Number of times the key can be activated. Leave it blank for unlimited activations.', 'wc-key-manager' ); ?>
						</p>
					</div>

					<?php if ( $key->order_id ) : ?>
						<div class="bk-form-field">
							<label for="activation_count">
								<?php esc_html_e( 'Activation Count', 'wc-key-manager' ); ?>
							</label>
							<input type="number" id="activation_count" placeholder="0" step="any" min="0" value="<?php echo esc_attr( $key->activation_count ); ?>" readonly/>
							<p class="description">
								<?php esc_html_e( 'Number of times the key has been activated.', 'wc-key-manager' ); ?>
							</p>
						</div>

					<?php endif; ?>

					<?php
					/**
					 * Fires after the key edit form fields.
					 *
					 * @param \KeyManager\Models\Key $key The key being edited.
					 */
					do_action( 'wc_key_manager_after_edit_key_form_fields', $key );
					?>
				</div>


				<div class="bk-card__footer">
					<?php // translators: %s: key creation date. ?>
					<?php printf( esc_html__( 'The key was created on %s', 'wc-key-manager' ), esc_html( $key->created_at ) ); ?>
					<?php if ( $key->updated_at ) : ?>
						<?php // translators: %s: key update date. ?>
						<?php printf( esc_html__( ' and last updated on %s', 'wc-key-manager' ), esc_html( $key->updated_at ) ); ?>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $key->order_id ) : ?>
				<?php require __DIR__ . '/edit/activations.php'; ?>
			<?php endif; ?>

			<?php
			/**
			 * Fires after the key edit form.
			 *
			 * @param \KeyManager\Models\Key $key The key being edited.
			 */
			do_action( 'wc_key_manager_end_edit_key_form', $key );
			?>
		</div>

		<div class="column-2">
			<?php
			/**
			 * Fires before the key edit form sidebar.
			 *
			 * @param \KeyManager\Models\Key $key The key being edited.
			 */
			do_action( 'wc_key_manager_start_edit_key_form_sidebar', $key );
			?>
			<?php require __DIR__ . '/edit/actions.php'; ?>
			<?php require __DIR__ . '/edit/product.php'; ?>
			<?php require __DIR__ . '/edit/order.php'; ?>
			<?php require __DIR__ . '/edit/customer.php'; ?>
			<?php
			/**
			 * Fires after the key edit form.
			 *
			 * @param \KeyManager\Models\Key $key The key being edited.
			 */
			do_action( 'wc_key_manager_end_edit_key_form_sidebar', $key );
			?>
		</div>
	</div>

	<input type="hidden" name="action" value="wckm_edit_key">
	<input type="hidden" name="id" value="<?php echo esc_attr( $key->id ); ?>">
	<?php wp_nonce_field( 'wckm_edit_key' ); ?>
</form>
