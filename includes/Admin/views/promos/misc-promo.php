<?php
/**
 * Misc promo.
 * Promo for the WooCommerce Key Manager plugin.
 *
 * @since 1.0.0
 * @package KeyManager
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

?>
<!-- SMS settings. -->
<section class="wckm-promo-section">
	<div class="wckm-promo__header">
		<h2 class="wckm-promo__header-title"><?php esc_html_e( 'SMS Settings - Twilio', 'wc-key-manager' ); ?></h2>
		<p class="wckm-promo__header-description"><?php esc_html_e( 'Configure the Twilio settings to send the key(s) by SMS.', 'wc-key-manager' ); ?></p>
	</div>
	<div class="wckm-promo">
		<div class="wckm-promo__content">
			<h3 class="wckm-promo__title"><?php esc_html_e( 'Premium Feature', 'wc-key-manager' ); ?></h3>
			<a href="https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro" class="wckm-promo__button button button-primary" target="_blank"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-key-manager' ); ?></a>
		</div>
		<div class="wckm-promo__image">
			<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/sms-settings.png' ); ?>" alt="<?php esc_attr_e( 'SMS Settings - Twilio', 'wc-key-manager' ); ?>">
		</div>
	</div>
</section>
<!-- QR and Barcode settings. -->
<section class="wckm-promo-section">
	<div class="wckm-promo__header">
		<h2 class="wckm-promo__header-title"><?php esc_html_e( 'QR and Barcode Settings', 'wc-key-manager' ); ?></h2>
		<p class="wckm-promo__header-description"><?php esc_html_e( 'Configure the QR and Barcode related settings.', 'wc-key-manager' ); ?></p>
	</div>
	<div class="wckm-promo">
		<div class="wckm-promo__content">
			<h3 class="wckm-promo__title"><?php esc_html_e( 'Premium Feature', 'wc-key-manager' ); ?></h3>
			<a href="https://wckeymanager.com/?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro" class="wckm-promo__button button button-primary" target="_blank"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-key-manager' ); ?></a>
		</div>
		<div class="wckm-promo__image">
			<img src="<?php echo esc_url( WCKM_ASSETS_URL . 'images/qr-barcode-settings.png' ); ?>" alt="<?php esc_attr_e( 'QR and Barcode Settings', 'wc-key-manager' ); ?>">
		</div>
	</div>
</section>
