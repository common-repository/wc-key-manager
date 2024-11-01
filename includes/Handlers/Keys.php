<?php

namespace KeyManager\Handlers;

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Keys class
 */
class Keys {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'wc_key_manager_pre_add_order_item_keys', array( __CLASS__, 'generate_order_item_keys' ), 10, 2 );
		add_action( 'wc_key_manager_update_expired_keys', array( __CLASS__, 'update_expired_keys' ) );
	}

	/**
	 * Generate order item keys.
	 *
	 * @param int            $quantity The quantity of keys to be added.
	 * @param \WC_Order_Item $item Order item.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function generate_order_item_keys( $quantity, $item ) {
		$product_id = $item->get_product_id();
		if ( 'automatic' !== wckm_get_key_source( $product_id ) ) {
			return;
		}

		$count = Key::count(
			array(
				'product_id' => $product_id,
				'source'     => 'automatic',
				'status'     => 'available',
			)
		);

		if ( $count >= $quantity ) {
			return;
		}

		// Keep generating keys until we have enough generated required keys.
		wckm_generate_keys(
			array(
				'product_id' => $product_id,
				'quantity'   => $quantity - $count,
			)
		);
	}

	/**
	 * Update expired keys.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function update_expired_keys() {
		global $wpdb;
		// when valid_for is more than 0 and date_ordered is not null or date_expires is not null and more than current date and status is available.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}wckm_keys SET status = 'expired' WHERE (valid_for > 0 AND (ordered_at IS NOT NULL AND ordered_at < %s) OR (expires_at IS NOT NULL AND expires_at < %s)) AND status != 'expired'",
				current_time( 'mysql' ),
				current_time( 'mysql' )
			)
		);
		// flush cache.
		( new Key() )->flush_cache();
	}
}
