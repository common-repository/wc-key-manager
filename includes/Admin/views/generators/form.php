<?php
/**
 * The template for adding a generator.
 *
 * @package KeyManager/Admin/Views
 * @since 1.0.0
 * @var \KeyManager\Models\Generator $generator Generator object.
 */

defined( 'ABSPATH' ) || exit;
?>

<form id="wckm-edit-generator-form" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
	<div class="bk-poststuff">
		<div class="column-1">
			<div class="bk-card">
				<div class="bk-card__header">
					<h2 class="bk-card__title">
						<?php esc_html_e( 'Generator Attributes', 'wc-key-manager' ); ?>
					</h2>
				</div>

				<div class="bk-card__body inline--fields">

					<div class="bk-form-field">
						<label for="name">
							<?php esc_html_e( 'Name', 'wc-key-manager' ); ?>
							<abbr title="required"></abbr>
						</label>
						<input type="text" name="name" id="name" required="required" value="<?php echo esc_attr( $generator->name ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Enter a friendly name for the generator.', 'wc-key-manager' ); ?>
						</p>
					</div>

					<div class="bk-form-field">
						<label for="pattern">
							<?php esc_html_e( 'Pattern', 'wc-key-manager' ); ?>
							<abbr title="required"></abbr>
						</label>
						<input type="text" name="pattern" id="pattern" required="required" value="<?php echo esc_attr( $generator->pattern ); ?>"/>
						<ul class="description">
							<li><?php esc_html_e( 'Hash (#) will be replaced by random alphanumeric character.', 'wc-key-manager' ); ?></li>
							<li><?php esc_html_e( '{product_id} will be replaced with the product id.', 'wc-key-manager' ); ?></li>
							<li><?php esc_html_e( '{product_sku} will be replaced with the product SKU.', 'wc-key-manager' ); ?></li>
							<li><?php esc_html_e( '{d}, {m}, {y}, {h}, {i}, {s} will be replaced with the current date and time.', 'wc-key-manager' ); ?></li>
						</ul>
					</div>

					<div class="bk-form-field">
						<label for="charset">
							<?php esc_html_e( 'Charset', 'wc-key-manager' ); ?>
							<abbr title="required"></abbr>
						</label>
						<input type="text" name="charset" id="charset" value="<?php echo esc_attr( $generator->charset ); ?>" required="required"/>
						<p class="description">
							<?php esc_html_e( 'Enter the charset for the generator. Leave empty for default charset.', 'wc-key-manager' ); ?>
						</p>
					</div>

					<div class="bk-form-field">
						<label for="valid_for">
							<?php esc_html_e( 'Valid For (days)', 'wc-key-manager' ); ?>
						</label>
						<input type="number" name="valid_for" id="valid_for" placeholder="0" step="any" min="0" value="<?php echo esc_attr( $generator->valid_for ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Relative expiration date in number from the time of purchase. Leave it blank for lifetime validity.', 'wc-key-manager' ); ?>
						</p>
					</div>

					<div class="bk-form-field">
						<label for="activation_limit">
							<?php esc_html_e( 'Activation Limit', 'wc-key-manager' ); ?>
						</label>
						<input type="number" name="activation_limit" id="activation_limit" placeholder="0" step="any" min="0" value="<?php echo esc_attr( $generator->activation_limit ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Number of times the key can be activated. Leave it blank for unlimited activations.', 'wc-key-manager' ); ?>
						</p>
					</div>

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
								<?php foreach ( $generator->get_statuses() as $status => $label ) : ?>
									<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $generator->status, $status ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
				<div class="bk-card__footer">
					<?php if ( $generator->exists() ) : ?>
						<a class="del" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', admin_url( 'admin.php?page=wckm-keys&tab=generators&id=' . $generator->id ) ), 'bulk-generators' ) ); ?>"><?php esc_html_e( 'Delete', 'wc-key-manager' ); ?></a>
					<?php endif; ?>
					<button class="button button-primary"><?php esc_html_e( 'Submit', 'wc-key-manager' ); ?></button>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="action" value="wckm_edit_generator"/>
	<input type="hidden" name="id" value="<?php echo esc_attr( $generator->id ); ?>"/>
	<?php wp_nonce_field( 'wckm_edit_generator' ); ?>
</form>


