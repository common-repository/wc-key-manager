<?php

use KeyManager\Models\Key;
use KeyManager\Models\Activation;
use KeyManager\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Get the plugin instance.
 *
 * @since 1.0.0
 * @return KeyManager\Plugin
 */
function WCKM() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Plugin::instance();
}

/**
 * Check if a product is a key product.
 *
 * @param int|WC_Product $product The product.
 *
 * @since 1.0.0
 * @return bool
 */
function wckm_is_keyed_product( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	// Bail if product is not found.
	if ( ! $product ) {
		return false;
	}

	// if variable product, then get the parent product.
	if ( $product->is_type( 'variation' ) ) {
		$product = wc_get_product( $product->get_parent_id() );
	}

	return 'yes' === $product->get_meta( '_wckm_keyed', true );
}

/**
 * Get key source.
 *
 * @param int|WC_Product $product The product.
 *
 * @since 1.0.0
 * @return string The key source.
 */
function wckm_get_key_source( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	// Bail if product is not found.
	if ( ! $product ) {
		return '';
	}

	// order of priority: variation, parent, global.
	$source = $product->get_meta( '_wckm_key_source', true );
	if ( empty( $source ) && $product->is_type( 'variation' ) ) {
		$source = wckm_get_key_source( $product->get_parent_id() );
	}
	if ( empty( $source ) ) {
		$source = get_option( 'wckm_key_source', 'automatic' );
	}

	return apply_filters( 'wc_key_manager_key_source', $source, $product );
}


/**
 * Get delivery quantity.
 *
 * @param int|WC_Product $product The product.
 * @param int            $quantity The quantity.
 *
 * @since 1.0.0
 * @return int The delivery quantity.
 */
function wckm_get_delivery_quantity( $product, $quantity = 1 ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	// Bail if product is not found.
	if ( ! $product ) {
		return 0;
	}

	$delivery_qty = (int) $product->get_meta( '_wckm_delivery_qty', true );
	if ( empty( $delivery_qty ) && $product->is_type( 'variation' ) ) {
		$delivery_qty = wckm_get_delivery_quantity( $product->get_parent_id(), $quantity );
	}

	if ( empty( $delivery_qty ) ) {
		$delivery_qty = 1;
	}

	/**
	 * Filter to allow altering the delivery quantity.
	 *
	 * @param int            $delivery_qty The delivery quantity.
	 * @param WC_Product     $product The product.
	 * @param int            $quantity The quantity.
	 */
	$delivery_quantity = apply_filters( 'wc_key_manager_delivery_quantity', $delivery_qty, $product, $quantity );

	return $delivery_quantity * $quantity;
}

/**
 * Get sequential position.
 *
 * @param int $product_id The product.
 *
 * @since 1.0.0
 * @return int The sequential position.
 */
function wckm_get_sequential_position( $product_id ) {
	$position = (int) get_post_meta( $product_id, '_wckm_sequential_position', true );
	/**
	 * Filter to allow altering the sequential position.
	 *
	 * @param int $position The sequential position.
	 * @param int $product_id The product.
	 */
	return apply_filters( 'wc_key_manager_sequential_position', $position, $product_id );
}

/**
 * Update sequential position.
 *
 * @param int $product_id The product.
 * @param int $position The position.
 *
 * @since 1.0.0
 * @return void
 */
function wckm_update_sequential_position( $product_id, $position ) {
	update_post_meta( $product_id, '_wckm_sequential_position', $position );
}

/**
 * Reset sequential position.
 *
 * @param int $product_id The product.
 *
 * @since 1.0.0
 * @return void
 */
function wckm_reset_sequential_position( $product_id ) {
	wckm_update_sequential_position( $product_id, 0 );
}

/**
 * Get keys.
 *
 * @param array $args The arguments.
 * @param bool  $count Whether to return the count.
 *
 * @since 1.0.0
 * @return Key[]|int The keys or count.
 */
function wckm_get_keys( $args = array(), $count = false ) {
	if ( $count ) {
		return Key::count( $args );
	}

	return Key::results( $args );
}

/**
 * Get key.
 *
 * @param mixed $id The key ID.
 *
 * @since 1.0.0
 * @return Key|false The key or false.
 */
function wckm_get_key( $id ) {
	return Key::find( $id );
}

/**
 * Insert key.
 *
 * @param array $data The key data.
 * @param bool  $wp_error Whether to return a WP_Error object on failure.
 *
 * @since 1.0.0
 * @return Key|\WP_Error|false The key or WP_Error or false.
 */
function wckm_insert_key( $data, $wp_error = true ) {
	return Key::insert( $data, $wp_error );
}

/**
 * Delete key.
 *
 * @param int $id The key ID.
 *
 * @since 1.0.0
 * @return bool Whether the key was deleted.
 */
function wckm_delete_key( $id ) {
	$key = wckm_get_key( $id );
	if ( ! $key ) {
		return false;
	}

	return $key->delete();
}

/**
 * Get order keys.
 *
 * @param int   $order_id The order ID.
 * @param array $args The arguments.
 *
 * @since 1.0.0
 * @return array|Key[]|int The keys or count.
 */
function wckm_get_order_keys( $order_id, $args = array() ) {
	$args = apply_filters(
		'wc_key_manager_order_keys_query_args',
		wp_parse_args(
			$args,
			array(
				'order_id'         => $order_id,
				'order_id__exists' => true,
				'limit'            => - 1,
			)
		),
		$order_id
	);

	return wckm_get_keys( $args );
}

/**
 * Generate key based on the settings of the product.
 *
 * @param array $args Additional arguments.
 *
 * @since 1.0.0
 * @return Key[]|false The generated keys or false.
 */
function wckm_generate_keys( $args = array() ) {
	$defaults = array(
		'product_id'   => 0,
		'generator_id' => 0,
		'quantity'     => 1,
	);

	$args = wp_parse_args( $args, $defaults );

	$product_id   = ! empty( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;
	$generator_id = ! empty( $args['generator_id'] ) ? absint( $args['generator_id'] ) : 0;
	$quantity     = ! empty( $args['quantity'] ) ? absint( $args['quantity'] ) : 1;
	$product      = wc_get_product( $product_id );

	// If product ID is not set, then return false.
	if ( empty( $product ) ) {
		return false;
	}

	// If generator is set, we will check if product has a generator set otherwise we will use default settings.
	if ( empty( $generator_id ) ) {
		$generator_id = get_post_meta( $product_id, '_wckm_generator_id', true );
	}

	// If we get the generator ID and generator is found, then we will use the generator settings.
	$generator = null;
	if ( ! empty( $generator_id ) ) {
		$generator = \KeyManager\Models\Generator::find( $generator_id );
	}

	// Prepare required variables.
	$pattern          = $generator ? $generator->pattern : get_option( 'wckm_pattern', '####-####-####-####' );
	$charset          = $generator ? $generator->charset : get_option( 'wckm_charset', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' );
	$valid_for        = $generator ? $generator->valid_for : get_option( 'wckm_valid_for', 0 );
	$activation_limit = $generator ? $generator->activation_limit : get_option( 'wckm_activation_limit', 0 );
	$is_sequential    = isset( $args['is_sequential'] ) ? $args['is_sequential'] : 'yes' === $product->get_meta( '_wckm_is_sequential', true );
	$source           = ! empty( $args['source'] ) ? $args['source'] : 'automatic';
	$seq_position     = wckm_get_sequential_position( $product->get_id() );
	$mask_count       = substr_count( $pattern, '#' );

	// Prepare replacers array.
	$replacers = array(
		'product_id'  => $product->get_id(),
		'product_sku' => $product->get_sku(),
		'y'           => wp_date( 'Y' ),
		'm'           => wp_date( 'm' ),
		'd'           => wp_date( 'd' ),
		'h'           => wp_date( 'H' ),
		'i'           => wp_date( 'i' ),
		's'           => wp_date( 's' ),
	);

	// Replace placeholders with actual values.
	$pattern = preg_replace_callback(
		'/{(\w+)}/',
		function ( $matches ) use ( $replacers ) {
			return $replacers[ $matches[1] ] ?? '';
		},
		$pattern
	);

	$keys = array();
	for ( $i = 0; $i < $quantity; $i++ ) {
		$code = $pattern;
		// Generate key characters.
		if ( $is_sequential ) {
			$sequential_key = str_pad( $seq_position + 1, $mask_count, '0', STR_PAD_LEFT );
			$chars          = str_split( $sequential_key );
			++$seq_position;
		} else {
			$chars = array_map(
				function () use ( $charset ) {
					return $charset[ wp_rand( 0, strlen( $charset ) - 1 ) ];
				},
				range( 1, $mask_count )
			);
		}

		// Replace '#' with characters.
		foreach ( $chars as $char ) {
			if ( strpos( $code, '#' ) !== false ) {
				$pos  = strpos( $code, '#' );
				$code = substr_replace( $code, $char, $pos, 1 );
			} else {
				$code .= $char;
			}
		}

		$data = array(
			'key'              => $code,
			'product_id'       => $product->get_id(),
			'valid_for'        => $valid_for,
			'activation_limit' => $activation_limit,
			'status'           => 'available',
			'source'           => $source,
			'metadata'         => array(),
		);
		if ( $generator ) {
			$data['metadata']['generator_id'] = $generator->id;
		}

		/**
		 * Filter hook to alter the key data before inserting.
		 *
		 * @param array                                        $data The key data.
		 * @param array                                        $args The arguments.
		 * @param \KeyManager\Models\Generator|null $generator The generator object.
		 */
		$data = apply_filters( 'wc_key_manager_generated_key_data', $data, $args, $generator );

		$key = wckm_insert_key( $data );
		if ( ! is_wp_error( $key ) ) {
			$keys[] = $key;
		}
	}

	if ( $is_sequential ) {
		wckm_update_sequential_position( $product->get_id(), $seq_position );
	}

	return $keys;
}

/**
 * Get stock of a product.
 *
 * @param int $product_id The product ID.
 *
 * @since 1.0.0
 * @return int The stock.
 */
function wckm_get_key_stock( $product_id ) {
	$stock = wckm_get_keys(
		array(
			'product_id' => $product_id,
			'status'     => 'available',
			'source'     => 'preset',
		),
		true
	);

	/**
	 * Filter to allow altering the stock of the product.
	 *
	 * @param int $stock The stock.
	 * @param int $product_id The product ID.
	 */
	return apply_filters( 'wc_key_manager_product_stock', $stock, $product_id );
}

/**
 * Sync stock of given product ids.
 *
 * @param array $product_ids The product IDs.
 *
 * @since 1.0.0
 * @return void
 */
function wckm_sync_stock( $product_ids = array() ) {
	\KeyManager\Handlers\Stocks::synchronize_stocks( $product_ids );
}

/**
 * Order has keyed products.
 *
 * @param int $order_id The order ID.
 *
 * @since 1.0.0
 * @return bool Whether the order has keyed products.
 */
function wckm_order_has_products( $order_id ) {
	$order = wc_get_order( $order_id );

	// Bail if order is not found.
	if ( ! $order ) {
		return false;
	}

	$keyed = false;
	foreach ( $order->get_items() as $item ) {
		// If the item is not a product, then skip.
		if ( 'line_item' !== $item['type'] || ! $item instanceof \WC_Order_Item_Product ) {
			continue;
		}

		$product = $item->get_product();

		// If it's not a keyed product, then skip.
		if ( wckm_is_keyed_product( $product ) ) {
			$keyed = true;
			break;
		}
	}

	return apply_filters( 'wc_key_manager_order_has_products', $keyed, $order );
}

/**
 * Order add keys.
 *
 * @param int $order_id The order ID.
 * @param int $order_item_id The order item ID.
 *
 * @since 1.0.0
 * @return int The number of keys added.
 */
function wckm_add_order_keys( $order_id, $order_item_id = null ) {
	$order = wc_get_order( $order_id );

	// Bail if order is not found.
	if ( ! $order ) {
		return false;
	}

	/**
	 * Filter to allow processing the order.
	 *
	 * @param bool      $allow Should the order be processed.
	 * @param \WC_Order $order Order object.
	 */
	$allow = apply_filters( 'wc_key_manager_add_order_keys', true, $order );

	// Bail if order processing is not allowed.
	if ( ! $allow ) {
		WCKM()->log(
			sprintf(
			// translators: %d: order ID.
				esc_html__( 'Processing of order #%d is not allowed.', 'wc-key-manager' ),
				$order_id
			)
		);

		return false;
	}

	if ( ! wckm_order_has_products( $order_id ) ) {
		WCKM()->log(
			sprintf(
			// translators: %d: order ID.
				esc_html__( 'Order #%d does not contain keyed products. Skipping processing order.', 'wc-key-manager' ),
				$order_id
			)
		);

		if ( 'yes' === $order->get_meta( '_wckm_order', true ) ) {
			$order->delete_meta_data( '_wckm_order' );
			$order->save();
		}

		return false;
	}

	$keys_added = 0;
	foreach ( $order->get_items() as $item ) {

		// If the item is not a product, then skip.
		if ( 'line_item' !== $item['type'] || ! $item instanceof \WC_Order_Item_Product ) {
			continue;
		}

		// If order item ID is set, and it does not match, then skip.
		if ( ! empty( $order_item_id ) && $order_item_id !== $item->get_id() ) {
			continue;
		}

		WCKM()->log(
			sprintf(
			// translators: %d: order item ID.
				esc_html__( 'Processing order item #%d', 'wc-key-manager' ),
				$item->get_id()
			)
		);

		$product = $item->get_product();

		// If it's not a keyed product, then skip.
		if ( ! wckm_is_keyed_product( $product ) ) {
			WCKM()->log(
				sprintf(
				// translators: %d: product ID.
					esc_html__( 'Product #%d is not a keyed product. Skipping processing.', 'wc-key-manager' ),
					$product->get_id()
				)
			);
			continue;
		}

		// check if the item have delivery quantity set otherwise calculate it.
		if ( $item->get_meta( '_wckm_delivery_qty', true ) ) {
			$delivery_qty = max( 1, (int) $item->get_meta( '_wckm_delivery_qty', true ) );
			WCKM()->log(
				sprintf(
				// translators: %d: item ID %d: delivery quantity.
					esc_html__( 'Delivery quantity for order item #%1$d was set previously. Using the saved delivery quantity %2$d.', 'wc-key-manager' ),
					$item->get_id(),
					$delivery_qty
				)
			);
		} else {
			$delivery_qty = max( 1, (int) wckm_get_delivery_quantity( $product, 1 ) );
			$item->add_meta_data( '_wckm_order_item', 'yes', true );
			$item->add_meta_data( '_wckm_delivery_qty', $delivery_qty, true );
			$item->save();
			WCKM()->log(
				sprintf(
				// translators: %d: item ID %d: delivery quantity.
					esc_html__( 'Delivery quantity for order item #%1$d was not set. Saved delivery quantity as %2$d.', 'wc-key-manager' ),
					$item->get_id(),
					$delivery_qty
				)
			);
		}

		$total_qty = $delivery_qty * $item->get_quantity();

		WCKM()->log(
			sprintf(
			// translators: %d: total quantity.
				esc_html__( 'The item should have %d keys in total.', 'wc-key-manager' ),
				$total_qty
			)
		);

		$added_count = Key::count(
			array(
				'product_id'    => $product->get_id(),
				'order_id'      => $order_id,
				'order_item_id' => $item->get_id(),
				'status__not'   => 'cancelled',
			)
		);

		if ( $added_count >= $total_qty ) {
			WCKM()->log(
				sprintf(
				// translators: %d: total quantity, %d: added count.
					esc_html__( 'The item already has %2$d keys. Total keys needed %1$d. Skipping processing.', 'wc-key-manager' ),
					$total_qty,
					$added_count
				)
			);
			continue;
		}

		$pending_count = $total_qty - $added_count;

		WCKM()->log(
			sprintf(
			// translators: %d: total quantity, %d: added count.
				esc_html__( 'Previously added %1$d keys for the item. Need to add %2$d more keys.', 'wc-key-manager' ),
				$added_count,
				$pending_count
			)
		);

		/**
		 * Action hook before adding order item keys.
		 *
		 * @param int            $quantity The quantity of keys to be added.
		 * @param \WC_Order_Item $item Order item.
		 * @param \WC_Order      $order Order object.
		 */
		do_action( 'wc_key_manager_pre_add_order_item_keys', $pending_count, $item, $order );

		// Get keys.
		$keys = Key::results(
			array(
				'product_id' => $product->get_id(),
				'status'     => 'available',
				'orderby'    => 'id',
				'order'      => 'ASC',
				'limit'      => $pending_count,
			)
		);

		WCKM()->log(
			sprintf(
			// translators: %d: total quantity, %d: added count.
				esc_html__( 'Found %1$d available keys for the item.', 'wc-key-manager' ),
				count( $keys )
			)
		);

		/**
		 * Filter hook to alter the keys before assigning them to the order item.
		 *
		 * @param Key[]          $keys The keys.
		 * @param \WC_Order_Item $item Order item.
		 * @param \WC_Order      $order Order object.
		 */
		$keys = apply_filters( 'wc_key_manager_order_item_keys', $keys, $item, $order );

		// If keys are found, assign them to the order item.
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $key ) {
				if ( ! is_wp_error( $key->add_order( $order_id, $item->get_id() ) ) ) {

					WCKM()->log(
						sprintf(
						// translators: %d: key ID.
							esc_html__( 'Key #%1$d added to the order item %2$d.', 'wc-key-manager' ),
							$key->id,
							$item->get_id()
						)
					);

					--$pending_count;
					++$keys_added;
				}
			}
		}

		// If we still need more keys, add order notes about the shortage of keys and send admin notification.
		if ( $pending_count > 0 ) {
			// translators: %1$s: product name, %2$d: total quantity, %3$d: needed count.
			$note = sprintf( esc_html__( 'Not enough keys available for product %1$s. Needed %2$d, but only %3$d available.', 'wc-key-manager' ), $product->get_name(), $pending_count, count( $keys ) );
			$order->add_order_note( $note );

			WCKM()->log(
				sprintf(
				// translators: %1$s: product name, %2$d: total quantity, %3$d: needed count.
					esc_html__( 'Not enough keys available for product %1$s. Needed %2$d, but only %3$d available.', 'wc-key-manager' ),
					$product->get_name(),
					$pending_count,
					count( $keys )
				)
			);

			/**
			 * Action hook when there is a shortage of keys for an order item.
			 *
			 * @param int            $pending_count The number of keys needed.
			 * @param \WC_Order_Item $item Order item.
			 * @param \WC_Order      $order Order object.
			 */
			do_action( 'wc_key_manager_order_item_keys_shortage', $pending_count, $item, $order );
		} else {
			WCKM()->log(
				sprintf(
				// translators: %d: order item ID.
					esc_html__( 'All keys added for order item #%1$d.', 'wc-key-manager' ),
					$item->get_id()
				)
			);
		}
	}

	// Set a flag to indicate that we have processed the order.
	if ( 'yes' !== $order->get_meta( '_wckm_order', true ) ) {
		$order->update_meta_data( '_wckm_order', 'yes' );
		$order->save();
	}

	return $keys_added;
}

/**
 * Order remove keys.
 *
 * @param int $order_id The order ID.
 * @param int $order_item_id The order item ID.
 *
 * @since 1.0.0
 * @return int The number of keys revoked.
 */
function wckm_remove_order_keys( $order_id, $order_item_id = 0 ) {
	$order = wc_get_order( $order_id );

	// Bail if order is not found.
	if ( ! $order ) {
		return false;
	}

	// if not a keyed order, then return.
	if ( 'yes' !== $order->get_meta( '_wckm_order', true ) ) {
		WCKM()->log(
			sprintf(
			// translators: %d: order ID.
				esc_html__( 'Order #%d is not a keyed order. Skipping revoking keys.', 'wc-key-manager' ),
				$order_id
			)
		);

		return false;
	}

	$keys_revoked = 0;
	$recycle      = 'yes' === get_option( 'wckm_recycle_keys', 'no' );
	foreach ( $order->get_items() as $item ) {
		// If the item is not a product, then skip.
		if ( 'line_item' !== $item['type'] || ! $item instanceof \WC_Order_Item_Product ) {
			continue;
		}

		// If order item ID is set, and it does not match, then skip.
		if ( ! empty( $order_item_id ) && $order_item_id !== $item->get_id() ) {
			continue;
		}

		$product = $item->get_product();

		// If it's not a keyed product, then skip.
		if ( ! wckm_is_keyed_product( $product ) ) {
			continue;
		}

		WCKM()->log(
			sprintf(
			// translators: %d: order item ID.
				esc_html__( 'Revoking keys for order item #%d.', 'wc-key-manager' ),
				$item->get_id()
			)
		);

		$keys = wckm_get_order_keys( $order_id, array( 'order_item_id' => $item->get_id() ) );

		if ( empty( $keys ) ) {
			WCKM()->log(
				sprintf(
				// translators: %d: order item ID.
					esc_html__( 'No keys found for order item #%d.', 'wc-key-manager' ),
					$item->get_id()
				)
			);
			continue;
		}

		foreach ( $keys as $key ) {
			$result = $recycle ? $key->remove_order() : $key->delete();
			if ( ! is_wp_error( $result ) ) {
				WCKM()->log(
					sprintf(
					// translators: %d: key ID %s: action, %d: order item ID.
						esc_html__( 'Key #%1$d %2$s from the order item %3$d.', 'wc-key-manager' ),
						$key->id,
						$recycle ? esc_html__( 'revoked', 'wc-key-manager' ) : esc_html__( 'deleted', 'wc-key-manager' ),
						$item->get_id()
					)
				);
				++$keys_revoked;
			}
		}
	}

	return $keys_revoked;
}

/**
 * Get enabled products.
 *
 * @param array $args Query args.
 * @param bool  $count Whether to return the count.
 *
 * @since 1.0.0
 * @return \WC_Product[]|int The products or count.
 */
function wckm_get_products( $args = array(), $count = false ) {
	$defaults = array(
		'status'     => 'publish',
		'type'       => array( 'simple' ),
		'keyed'      => 'no',
		'meta_query' => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Meta query is required.
	);

	$args = wp_parse_args( $args, $defaults );

	/**
	 * Filter to allow filtering of the query args.
	 *
	 * @param array $args The query args.
	 */
	$args = apply_filters( 'wc_key_manager_get_products_args', $args );

	if ( $count ) {
		$args['paginate'] = true;

		return wc_get_products( $args )->total;
	}

	return wc_get_products( $args );
}

/**
 * Get key display attributes.
 *
 * @param Key    $key The key object.
 * @param string $context The context.
 *
 * @since 1.0.0
 * @return array The key properties.
 */
function wckm_get_formatted_key_attributes( $key, $context = 'myaccount' ) {
	// if key is not instance of Key, then return empty array.
	if ( ! $key instanceof Key ) {
		return array();
	}

	$attributes = array(
		array(
			'key'      => 'product',
			'label'    => __( 'Product', 'wc-key-manager' ),
			'value'    => $key->product_id,
			'render'   => sprintf( '<a href="%s">%s</a>', esc_url( $key->get_product_url() ), $key->get_product_name() ),
			'priority' => 10,
		),
		array(
			'key'      => 'key',
			'label'    => __( 'Key', 'wc-key-manager' ),
			'value'    => $key->code,
			'render'   => $key->get_key_html(),
			'priority' => 10,
		),
		array(
			'key'      => 'expires',
			'label'    => __( 'Expires', 'wc-key-manager' ),
			'value'    => $key->valid_for,
			'render'   => $key->get_expires_html(),
			'priority' => 20,
		),
		array(
			'key'      => 'activations',
			'label'    => __( 'Activations', 'wc-key-manager' ),
			'value'    => $key->activation_limit . '/' . $key->activation_count,
			'render'   => $key->get_activations_html(),
			'priority' => 30,
		),
	);
	/**
	 * Filter to allow adding more key attributes.
	 *
	 * @param array $attributes The key attributes.
	 * @param Key   $key The key object.
	 * @param string $context The context.
	 */
	$attributes = apply_filters( 'wc_key_manager_formatted_key_attributes', $attributes, $key, $context );

	usort(
		$attributes,
		function ( $a, $b ) {
			$a = isset( $a['priority'] ) ? $a['priority'] : 10;
			$b = isset( $b['priority'] ) ? $b['priority'] : 10;

			return $a - $b;
		}
	);

	foreach ( $attributes as $i => $attribute ) {
		$render                     = ! empty( $attribute['render'] ) && is_callable( $attribute['render'] ) ? call_user_func( $attribute['render'], $attribute ) : ( ! empty( $attribute['render'] ) ? $attribute['render'] : ( ! empty( $attribute['value'] ) ? $attribute['value'] : '' ) );
		$render                     = apply_filters( "wc_key_manager_formatted_attribute_{$attribute['key']}", $render, $attribute, $attribute, $context );
		$attributes[ $i ]['render'] = apply_filters( "wc_key_manager_formatted_attribute_{$attribute['key']}_{$context}", $render, $attribute, $attribute, $context );
	}

	return $attributes;
}

/**
 * Get activations
 *
 * @param array $args The arguments.
 * @param bool  $count Whether to return the count.
 *
 * @since 1.0.0
 * @return array|int The activations or count.
 */
function wckm_get_activations( $args = array(), $count = false ) {
	if ( $count ) {
		return Activation::count( $args );
	}

	return Activation::results( $args );
}

/**
 * Get activation.
 *
 * @param mixed $id The activation ID.
 *
 * @since 1.0.0
 * @return Activation|false The activation or false.
 */
function wckm_get_activation( $id ) {
	return Activation::find( $id );
}

/**
 * Delete activation.
 *
 * @param int $id The activation ID.
 *
 * @since 1.0.0
 * @return bool Whether the activation was deleted.
 */
function wckm_delete_activation( $id ) {
	$activation = wckm_get_activation( $id );
	if ( ! $activation ) {
		return false;
	}

	return $activation->delete();
}

/**
 * Get order complete statuses.
 *
 * @since 1.0.0
 * @return array
 */
function wckm_get_order_paid_statuses() {
	$statuses = array( 'completed' );
	if ( 'yes' === get_option( 'wckm_proc_key_delivery', 'no' ) ) {
		$statuses[] = 'processing';
	}

	/**
	 * Filter to allow adding more order statuses to consider as paid.
	 *
	 * @param array $statuses The order statuses.
	 */
	return apply_filters( 'wc_key_manager_order_paid_statuses', $statuses );
}

/**
 * Get use IP address.
 *
 * @since 1.0.0
 * @return string
 */
function wckm_get_ip_address() {
	if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
		// Make sure we always only send through the first IP in the list which should always be the client IP.
		return (string) rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) );
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return '';
}

/**
 * Get user agent.
 *
 * @since 1.0.0
 * @return string
 */
function wckm_get_user_agent() {
	return isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
}

/**
 * Get platform name and version.
 *
 * @since 1.0.0
 * @return string
 */
function wckm_get_default_instance() {
	$user_agent = wckm_get_user_agent();
	$platform   = 'Unknown OS Platform';
	$platforms  = array(
		'/windows nt 10/i'      => 'Windows 10',
		'/windows nt 6.3/i'     => 'Windows 8.1',
		'/windows nt 6.2/i'     => 'Windows 8',
		'/windows nt 6.1/i'     => 'Windows 7',
		'/windows nt 6.0/i'     => 'Windows Vista',
		'/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     => 'Windows XP',
		'/windows xp/i'         => 'Windows XP',
		'/windows nt 5.0/i'     => 'Windows 2000',
		'/windows me/i'         => 'Windows ME',
		'/win98/i'              => 'Windows 98',
		'/win95/i'              => 'Windows 95',
		'/win16/i'              => 'Windows 3.11',
		'/macintosh|mac os x/i' => 'Mac OS X',
		'/mac_powerpc/i'        => 'Mac OS 9',
		'/linux/i'              => 'Linux',
		'/ubuntu/i'             => 'Ubuntu',
		'/iphone/i'             => 'iPhone',
		'/ipod/i'               => 'iPod',
		'/ipad/i'               => 'iPad',
		'/android/i'            => 'Android',
		'/blackberry/i'         => 'BlackBerry',
		'/webos/i'              => 'Mobile',
	);

	foreach ( $platforms as $regex => $value ) {
		if ( preg_match( $regex, $user_agent ) ) {
			$platform = $value;
			break;
		}
	}

	// If we can detect the browser name, then append it to the platform.
	if ( preg_match( '/(opera|chrome|safari|firefox|msie|trident|edge)/i', $user_agent, $matches ) ) {
		$platform .= ' - ' . $matches[0];
	}

	/**
	 * Filter to allow altering the default instance.
	 *
	 * @param string $platform The platform name.
	 * @param string $user_agent The user agent.
	 */
	return apply_filters( 'wc_key_manager_default_instance', $platform, $user_agent );
}

/**
 * Clean activation instance.
 *
 * @param string $instance The activation instance.
 *
 * @since 1.0.0
 * @return string The cleaned activation instance.
 */
function wckm_sanitize_instance( $instance ) {
	$instance = sanitize_text_field( $instance );

	// If instance is URL, clean the URL and get only the domain.
	if ( filter_var( $instance, FILTER_VALIDATE_URL ) ) {
		$domain = wp_parse_url( $instance, PHP_URL_HOST );
		if ( $domain ) {
			$instance = $domain;
		}
	}

	// Now check if this is a possible IDN url and seach both the IDN and ASCII version.
	if ( function_exists( 'idn_to_utf8' ) && idn_to_utf8( $instance ) !== $instance ) {
		$instance = idn_to_utf8( $instance );
	} elseif ( function_exists( 'idn_to_ascii' ) && idn_to_ascii( $instance ) !== $instance ) {
		$instance = idn_to_ascii( $instance );
	}

	return $instance;
}
