<?php
/**
 * The template for upgrade to pro.
 *
 * @package KeyManager/Admin/Views
 * @version 1.0.5
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wckm-feature-cards">
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-01.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Custom Fields', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Add custom fields to license keys', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Personalize and add metadata to keys', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Collect additional product-related information', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-02.png' ); ?>" alt="card-image">
		<h3>
			<?php esc_html_e( 'Variable Products', 'wc-key-manager' ); ?>
		</h3>
		<ul>
			<li><?php esc_html_e( 'Manage license keys for each product variation', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Control key generation, expiration, activations', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Set keys delivered based on variation attributes', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-03.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Subscription Products', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Sell license keys with subscription products', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Sync key expiration with subscription expiry', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Automatic key status updates', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-04.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Bulk Import & Export', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Import/export keys in bulk via CSV or text files', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Include custom fields during import/export', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Bulk-generate pre-generated keys for sale', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-05.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'SMS Notifications', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Notify customers with key details via SMS', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Customize SMS templates with key information', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'SMS gateway for seamless communication', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-06.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Encrypted Keys', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Encrypt license keys for added security', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Automatic encryption on saving and decryption on viewing', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-07.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'QR Codes', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Generate QR codes for license keys', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Simplify key verifications and activations for customers', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-08.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Barcode', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Create barcodes for key scanning and tracking', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Ideal for inventory management and quick verification', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-09.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Customize Key Labels', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Tailor key labels to align with your brand', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Customize key name, value, and status displays', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-10.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Multi vendor Support', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Support for multiple vendors to sell keys', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Compatible with Dokan and future support for other multivendor plugins', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-11.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Advanced Reporting', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Monitor activations and manage stock levels', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Track key generation, assignment, and usage in real-time', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
	<div class="wckm-feature-cards__item">
		<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/card-image-12.png' ); ?>" alt="card-image">
		<h3><?php esc_html_e( 'Premium Support', 'wc-key-manager' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Dedicated assistance for any plugin issues', 'wc-key-manager' ); ?></li>
			<li><?php esc_html_e( 'Ensure smooth operations with prompt support', 'wc-key-manager' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( 'https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro' ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'wc-key-manager' ); ?> &rarr;
		</a>
	</div>
</div>
