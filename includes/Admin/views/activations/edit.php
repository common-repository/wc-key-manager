<?php
/**
 * The template for editing an activation.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Activation $activation Activation object.
 */

defined( 'ABSPATH' ) || exit;

?>
<h1 class="wp-heading-inline">
	<?php esc_html_e( 'Edit Activation', 'wc-key-manager' ); ?>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys&tab=activations&add=yes' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add Another', 'wc-key-manager' ); ?>
	</a>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys&tab=activations' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Go Back', 'wc-key-manager' ); ?>
	</a>
</h1>

<form id="wckm-edit-activation-form" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
	<div class="bk-poststuff">
		<div class="column-1">
			<div class="bk-card">
				<div class="bk-card__header">
					<h2 class="bk-card__title"><?php esc_html_e( 'Activation Attributes', 'wc-key-manager' ); ?></h2>
				</div>
				<div class="bk-card__body inline--fields">
					<div class="bk-form-field">
						<label for="instance">
							<?php esc_html_e( 'Instance', 'wc-key-manager' ); ?><abbr title="required"></abbr>
						</label>
						<input type="text" name="instance" id="instance" required="required" placeholder="####-####-####-####" value="<?php echo esc_attr( $activation->instance ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Anything you want to use to reference this activation. It could be a number, domain, or anything else but it must be unique per key.', 'wc-key-manager' ); ?>
						</p>
					</div>

					<!--IP Address-->
					<div class="bk-form-field">
						<label for="ip_address">
							<?php esc_html_e( 'IP Address', 'wc-key-manager' ); ?>
						</label>
						<input type="text" name="ip_address" id="ip_address" placeholder="192.0.0.0" value="<?php echo esc_attr( $activation->ip_address ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'The IP address from where the activation was made.', 'wc-key-manager' ); ?>
						</p>
					</div>

					<!--User Agent-->
					<div class="bk-form-field">
						<label for="user_agent">
							<?php esc_html_e( 'User Agent', 'wc-key-manager' ); ?>
						</label>
						<input type="text" name="user_agent" id="user_agent" placeholder="Mozilla/5.0" value="<?php echo esc_attr( $activation->user_agent ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'The user agent from where the activation was made.', 'wc-key-manager' ); ?>
						</p>
					</div>
				</div>
				<div class="bk-card__footer">
					<?php // translators: %s: activation creation date. ?>
					<?php printf( esc_html__( 'The activation was created on %s', 'wc-key-manager' ), esc_html( $activation->created_at ) ); ?>
					<?php if ( $activation->updated_at ) : ?>
						<?php // translators: %s: activation update date. ?>
						<?php printf( esc_html__( ' and last updated on %s', 'wc-key-manager' ), esc_html( $activation->updated_at ) ); ?>
					<?php endif; ?>
				</div>

			</div>
		</div>
		<div class="column-2">
			<div class="bk-card">
				<div class="bk-card__header">
					<h2 class="bk-card__title"><?php esc_html_e( 'Actions', 'wc-key-manager' ); ?></h2>
				</div>
				<div class="bk-card__body">
					<div class="bk-form-field">
						<div class="bk-form-field">
							<label for="status">
								<?php esc_html_e( 'Status', 'wc-key-manager' ); ?>
							</label>
							<select name="status" id="status" required="required">
								<?php foreach ( $activation->get_statuses() as $status => $label ) : ?>
									<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $activation->status, $status ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
				<div class="bk-card__footer">
					<?php if ( $activation->exists() ) : ?>
						<a class="del" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', admin_url( 'admin.php?page=wckm-keys&tab=activations&id=' . $activation->id ) ), 'bulk-activations' ) ); ?>"><?php esc_html_e( 'Delete', 'wc-key-manager' ); ?></a>
					<?php endif; ?>
					<button class="button button-primary"><?php esc_html_e( 'Submit', 'wc-key-manager' ); ?></button>
				</div>
			</div>
			<?php require __DIR__ . '/edit/key.php'; ?>
		</div>
	</div>

	<input type="hidden" name="action" value="wckm_edit_activation">
	<input type="hidden" name="id" value="<?php echo esc_attr( $activation->id ); ?>">
	<?php wp_nonce_field( 'wckm_edit_activation' ); ?>
</form>
