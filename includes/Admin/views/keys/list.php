<?php
/**
 * List of Items
 *
 * @package KeyManager
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Keys', 'wc-key-manager' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-keys&add=yes' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Add New', 'wc-key-manager' ); ?>
		</a>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wckm-tools&tab=import' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Import', 'wc-key-manager' ); ?>
		</a>
		<?php if ( $this->list_table->get_request_search() ) : ?>
			<?php // translators: %s: search query. ?>
			<span class="subtitle"><?php echo esc_html( sprintf( __( 'Search results for "%s"', 'wc-key-manager' ), esc_html( $this->list_table->get_request_search() ) ) ); ?></span>
		<?php endif; ?>
	</h1>
	<hr class="wp-header-end">

	<form id="wckm-keys-table" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<?php $this->list_table->views(); ?>
		<?php $this->list_table->search_box( __( 'Search', 'wc-key-manager' ), 'search' ); ?>
		<?php $this->list_table->display(); ?>
		<input type="hidden" name="page" value="wckm-keys"/>
		<input type="hidden" name="tab" value="keys"/>
	</form>
<?php
