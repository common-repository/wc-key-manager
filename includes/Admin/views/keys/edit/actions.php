<?php
/**
 * The template for edit key actions.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Key $key
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="bk-card">
	<div class="bk-card__header">
		<h2 class="bk-card__title"><?php esc_html_e( 'Actions', 'wc-key-manager' ); ?></h2>
	</div>
	<div class="bk-card__body">
		<?php
		/**
		 * Fires before the key edit form actions.
		 *
		 * @param \KeyManager\Models\Key $key The key being edited.
		 */
		do_action( 'wc_key_manager_before_edit_key_actions_fields', $key );
		?>
		<div class="bk-form-field">
			<label for="status">
				<?php esc_html_e( 'Status', 'wc-key-manager' ); ?>
			</label>
			<select name="status" id="status" required="required">
				<?php foreach ( $key->get_statuses() as $status => $label ) : ?>
					<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $key->status, $status ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php
		/**
		 * Fires after the key edit form actions.
		 *
		 * @param \KeyManager\Models\Key $key The key being edited.
		 */
		do_action( 'wc_key_manager_after_edit_key_actions_fields', $key );
		?>
	</div>
	<div class="bk-card__footer">
		<?php if ( $key->exists() ) : ?>
			<a class="del" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', admin_url( 'admin.php?page=wckm-keys&id=' . $key->id ) ), 'bulk-keys' ) ); ?>"><?php esc_html_e( 'Delete', 'wc-key-manager' ); ?></a>
		<?php endif; ?>
		<button class="button button-primary"><?php esc_html_e( 'Submit', 'wc-key-manager' ); ?></button>
	</div>
</div>
