<?php

namespace KeyManager\Handlers;

use KeyManager\Models\Key;

/**
 * Class Orders
 *
 * @since   1.0.0
 * @package KeyManager\Handlers
 */
class Orders {
	/**
	 * Order constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'is_purchasable' ), 10, 2 );

		// Process order actions.
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'add_order_keys' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'add_order_keys' ) );

		// When order is refunded or cancelled, reverse the order.
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'remove_order_keys' ) );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'remove_order_keys' ) );
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'remove_order_keys' ) );
		add_action( 'woocommerce_delete_order', array( __CLASS__, 'remove_order_keys' ) );

		// Add key to order.
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_details' ), - 1, 1 );
	}

	/**
	 * Check if product is purchasable.
	 *
	 * When a product is not managing stock and is a keyed product with preset keys,
	 * check if there are available keys before allowing the product to be purchased.
	 *
	 * @param bool        $purchasable Is purchasable.
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_purchasable( $purchasable, $product ) {
		if ( $purchasable && ! $product->managing_stock() && wckm_is_keyed_product( $product ) && 'yes' === get_option( 'wckm_disable_oos_keys' ) && 'preset' === wckm_get_key_source( $product ) ) {
			$stock_quantity = Key::count(
				array(
					'product_id' => $product->get_id(),
					'status'     => 'available',
				)
			);
			$purchasable    = $stock_quantity > 0;
		}

		return $purchasable;
	}

	/**
	 * Process order.
	 *
	 * If the order is paid and contains keyed products, process the order.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_order_keys( $order_id ) {
		$order = wc_get_order( $order_id );

		// If order is not found, return.
		if ( ! $order ) {
			return;
		}

		WCKM()->log(
			sprintf(
			// translators: %d: order ID %s: order status.
				esc_html__( 'Order #%1$d status changed to %2$s. Maybe we need to process the order.', 'wc-key-manager' ),
				$order_id,
				$order->get_status()
			)
		);

		// If the order is not paid, return.
		if ( ! wckm_order_has_products( $order_id ) || ! in_array( $order->get_status(), wckm_get_order_paid_statuses(), true ) ) {
			WCKM()->log(
				sprintf(
				// translators: %d: order ID.
					esc_html__( 'Order #%d is not paid or does not contain keyed products. Skipping processing order.', 'wc-key-manager' ),
					$order_id
				)
			);

			return;
		}

		// If automatic delivery is disabled, return.
		if ( 'yes' !== get_option( 'wckm_automatic_delivery', 'yes' ) ) {
			WCKM()->log(
				sprintf(
				// translators: %d: order ID.
					esc_html__( 'Automatic delivery is disabled. Skipping processing order #%d.', 'wc-key-manager' ),
					$order_id
				)
			);

			return;
		}

		WCKM()->log(
			sprintf(
			// translators: %d: order ID.
				esc_html__( 'Processing order #%d. The order is paid and contains keyed products.', 'wc-key-manager' ),
				$order_id
			)
		);

		// Now process the order.
		wckm_add_order_keys( $order_id );

		WCKM()->log(
			sprintf(
			// translators: %1$d: order ID.
				esc_html__( 'Completed processing order #%1$d.', 'wc-key-manager' ),
				$order_id,
			)
		);
	}

	/**
	 * Reverse order.
	 *
	 * If the order is refunded or cancelled, reverse the order.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function remove_order_keys( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		WCKM()->log(
			sprintf(
			// translators: %d: order ID.
				esc_html__( 'Revoking keys for order #%d', 'wc-key-manager' ),
				$order_id
			)
		);

		if ( ! wckm_order_has_products( $order_id ) ) {
			WCKM()->log(
				sprintf(
				// translators: %d: order ID.
					esc_html__( 'Order #%d does not contain keyed products. Skipping revoking keys.', 'wc-key-manager' ),
					$order_id
				)
			);

			return;
		}

		// Remove keys from the order.
		$revoked = wckm_remove_order_keys( $order_id );
		WCKM()->log(
			sprintf(
			// translators: %1$d: order ID, %2$d: number of keys revoked.
				esc_html__( 'Revoked %2$d keys from order #%1$d', 'wc-key-manager' ),
				$order_id,
				$revoked
			)
		);
	}

	/**
	 * Display keys for an order.
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @since 1.0.0
	 */
	public static function order_details( $order ) {
		// bail if the order is not a keyed order or disabled showing order details.
		if ( ! wckm_order_has_products( $order->get_id() ) || 'yes' === get_option( 'wckm_hide_order_details' ) ) {
			return;
		}

		// Get the keys for the order.
		$keys = wckm_get_order_keys( $order->get_id() );

		$columns = apply_filters(
			'wc_key_manager_order_details_keys_table_columns',
			array(
				'product' => __( 'Product', 'wc-key-manager' ),
				'key'     => __( 'Key', 'wc-key-manager' ),
				'expires' => __( 'Expires', 'wc-key-manager' ),
				'actions' => '',
			)
		);

		// Remove actions column if my account keys feature is disabled.
		if ( 'no' === get_option( 'wckm_my_account_keys_page' ) ) {
			unset( $columns['actions'] );
		}

		wc_get_template(
			'order/keys.php',
			array(
				'order'   => $order,
				'keys'    => $keys,
				'columns' => $columns,
			),
			'',
			WCKM()->get_template_path()
		);
	}
}
