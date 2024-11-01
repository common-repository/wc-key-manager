<?php
/**
 * The template for editing a generator.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.0
 * @var \KeyManager\Models\Generator $generator Generator object.
 */

defined( 'ABSPATH' ) || exit;

?>
<h1 class="wp-heading-inline">
	<?php esc_html_e( 'Edit Generator', 'wc-key-manager' ); ?>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys&tab=generators' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Go Back', 'wc-key-manager' ); ?>
	</a>
</h1>


<?php
require __DIR__ . '/form.php';
