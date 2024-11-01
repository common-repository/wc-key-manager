<?php

namespace KeyManager\Handlers;

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Stocks
 *
 * @since  1.0.0
 * @subpackage Handlers
 * @package KeyManager
 */
class Stocks {
	/**
	 * Stocks constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'update_option_wckm_manage_stock', array( __CLASS__, 'maybe_activated_synchronize' ), 10, 2 );
		add_action( 'wc_key_manager_synchronize_stock', array( __CLASS__, 'synchronize_stocks' ) );
		add_action( 'wc_key_manager_key_inserted', array( __CLASS__, 'maybe_update_stock' ) );
		add_action( 'wc_key_manager_key_deleted', array( __CLASS__, 'maybe_update_stock' ) );
		add_action( 'wc_key_manager_key_updated', array( __CLASS__, 'maybe_update_stock' ) );
	}

	/**
	 * Maybe synchronize the stock.
	 *
	 * @param mixed $old_value Old value.
	 * @param mixed $value New value.
	 *
	 * @since 1.0.0
	 */
	public static function maybe_activated_synchronize( $old_value, $value ) {
		if ( 'yes' !== $value ) {
			return;
		}

		// set an action scheduler to synchronize the stock.
		if ( function_exists( 'WC' ) ) {
			$products = wc_get_products(
				array(
					'limit'        => - 1,
					'keyed'        => 'yes',
					'source'       => 'preset',
					'manage_stock' => true,
					'return'       => 'ids',
				)
			);

			if ( empty( $products ) ) {
				return;
			}

			WC()->queue()->cancel_all( 'wc_key_manager_synchronize_stock' );
			WC()->queue()->schedule_single(
				time() - 1,
				'wc_key_manager_synchronize_stock',
				array( $products ),
				'wc_key_manager'
			);
		}
	}

	/**
	 * Synchronize the stocks.
	 *
	 * @param array $product_ids Product IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function synchronize_stocks( $product_ids = array() ) {
		// take the first 10 product_ids.
		$manage_stock = 'yes' === get_option( 'wckm_manage_stock', 'no' );
		$step_ids     = count( $product_ids ) > 10 ? array_splice( $product_ids, 0, 10 ) : $product_ids;
		$product_ids  = array_diff( $product_ids, $step_ids );

		foreach ( $step_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			// bail if the product is not found or the stock management is disabled.
			if ( ! $product || ! $manage_stock || ( 'yes' !== get_option( 'woocommerce_manage_stock' ) ) ) {
				continue;
			}

			// bail if product source is not preset.
			if ( 'preset' !== wckm_get_key_source( $product_id ) ) {
				continue;
			}

			$stock_quantity = wckm_get_key_stock( $product_id );
			$product->set_manage_stock( true );
			$product->set_stock_quantity( $stock_quantity );
			$product->set_stock_status( $stock_quantity > 0 ? 'instock' : 'outofstock' );
			$product->save();
		}

		if ( ! empty( $product_ids ) && function_exists( 'WC' ) ) {
			WC()->queue()->schedule_single(
				time(),
				'wc_key_manager_synchronize_stock',
				array( $product_ids ),
				'wc_key_manager'
			);
		}
	}

	/**
	 * Maybe update the stock.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.0.0
	 */
	public static function maybe_update_stock( $key ) {
		$manage_stock = get_option( 'wckm_manage_stock', 'no' );
		if ( 'yes' !== $manage_stock || ! $key || ! $key->product_id || ( defined( 'WCKM_DISABLE_STOCK_SYNC' ) && WCKM_DISABLE_STOCK_SYNC ) ) {
			return;
		}

		$product = wc_get_product( $key->product_id );
		if ( ! $product || 'yes' !== get_option( 'woocommerce_manage_stock' ) || 'preset' !== wckm_get_key_source( $key->product_id ) ) {
			return;
		}
		$stock = wckm_get_key_stock( $key->product_id );
		$product->set_stock_quantity( $stock );
		$product->set_stock_status( $stock > 0 ? 'instock' : 'outofstock' );
		$product->save();
	}
}
