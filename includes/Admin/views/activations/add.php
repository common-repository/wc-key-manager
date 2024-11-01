<?php
/**
 * The template for adding an activation.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Activation $activation Activation object.
 */

defined( 'ABSPATH' ) || exit;
$key_id = filter_input( INPUT_GET, 'key_id', FILTER_VALIDATE_INT );
$key    = \KeyManager\Models\Key::find( $key_id );
?>
<h1>
	<?php esc_html_e( 'Activate Key', 'wc-key-manager' ); ?>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys&tab=activations' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Go Back', 'wc-key-manager' ); ?>
	</a>
</h1>

<form id="wckm-add-activation-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row">
				<label for="key">
					<?php esc_html_e( 'Key', 'wc-key-manager' ); ?>
					<abbr title="required">*</abbr>
				</label>
			</th>
			<td>
				<input type="text" name="key" id="key" required="required" placeholder="SERIAL-ABC-DEF-GHI" value="<?php echo esc_attr( $key ? $key->key : '' ); ?>">
				<p class="description">
					<?php esc_html_e( 'Enter the key to activate.', 'wc-key-manager' ); ?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="instance">
					<?php esc_html_e( 'Instance', 'wc-key-manager' ); ?>
				</label>
			</th>
			<td>
				<input type="text" id="instance" name="instance" class="regular-text" placeholder="123456">
				<p class="description">
					<?php esc_html_e( 'Anything you want to use to reference this activation. It could be a number, domain, or anything else but it must be unique per key.', 'wc-key-manager' ); ?>
				</p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">&nbsp;</th>
			<td>
				<input type="hidden" name="action" value="wckm_add_activation"/>
				<?php wp_nonce_field( 'wckm_add_activation' ); ?>
				<?php submit_button( __( 'Add Key', 'wc-key-manager' ), 'primary', 'add_activation' ); ?>
			</td>
		</tr>

		</tbody>
	</table>
</form>
