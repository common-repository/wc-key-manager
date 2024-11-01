<?php
/**
 * Validate key template.
 *
 * Shows list of keys customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/key/validate.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 1.0.0
 *
 * @package KeyManager/Templates
 *
 * @var \KeyManager\Models\Key $key The key object.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
?>
<?php wc_print_notices(); ?>
<?php if ( ! $key ) : ?>
	<form id="wckm-validation-form" method="post" enctype="multipart/form-data">
		<p class="form-row">
			<label for="key"><?php echo esc_html( $atts['key_label'] ); ?></label>
			<input type="text" name="key" id="key" class="input-text" placeholder="<?php echo esc_attr( $atts['key_placeholder'] ); ?>" required>
		</p>
		<?php if ( $atts['email_field'] ) : ?>
			<p class="form-row">
				<label for="email"><?php echo esc_html( $atts['email_label'] ); ?></label>
				<input type="email" name="email" id="email" class="input-text" placeholder="<?php echo esc_attr( $atts['email_label'] ); ?>" <?php echo $atts['email_required'] ? 'required' : ''; ?>>
			</p>
		<?php endif; ?>

		<p class="form-row">
			<button type="submit" class="button"><?php echo esc_html( $atts['submit_label'] ); ?></button>
			<input type="hidden" name="action" value="wckm_validate_key">
			<?php wp_nonce_field( 'wckm_validate_key' ); ?>
		</p>
	</form>
<?php else : ?>
	<?php
	wc_get_template(
		'key/view.php',
		array(
			'key'     => $key,
			'context' => 'validate',
		),
		'',
		WCKM()->get_template_path()
	);
	?>
<?php endif; ?>
