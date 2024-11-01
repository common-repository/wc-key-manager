<?php
/**
 * View: Admin dashboard
 *
 * @since       1.0.0
 * @subpackage  Admin/Views/Overview
 * @package     StarerPlugin
 */

defined( 'ABSPATH' ) || exit();
?>
<div class="wrap bk-wrap">
	<h1 class="wp-heading-inline" style="margin-bottom: 20px;">
		<?php esc_html_e( 'Dashboard', 'wc-key-manager' ); ?>
	</h1>
	<hr class="wp-header-end">

	<?php require __DIR__ . '/dashboard/growth-cart.php'; ?>

	<div class="bk-grids bk-grid-2 row--gap-0">
		<?php require __DIR__ . '/dashboard/recent-orders.php'; ?>
		<?php require __DIR__ . '/dashboard/recent-customers.php'; ?>
		<?php require __DIR__ . '/dashboard/top-products.php'; ?>
		<?php require __DIR__ . '/dashboard/product-stocks.php'; ?>
	</div>
</div>
