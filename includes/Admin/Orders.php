<?php

namespace KeyManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders.
 *
 * @since   1.0.0
 * @package KeyManager\Admin
 */
class Orders {
	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add shop order columns.
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_columns' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'order_columns_content' ), 20, 2 );
		// HPOS compatibility filter & action.
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( __CLASS__, 'add_order_columns' ), 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( __CLASS__, 'order_columns_content' ), 20, 2 );

		// Add order actions.
		add_filter( 'woocommerce_order_actions', array( __CLASS__, 'add_order_actions' ), 10, 2 );
		add_action( 'woocommerce_order_action_wckm_add_keys', array( __CLASS__, 'add_keys_to_order' ) );
		add_action( 'woocommerce_order_action_wckm_remove_keys', array( __CLASS__, 'remove_keys_from_order' ) );
		add_action( 'woocommerce_order_action_wckm_send_keys', array( __CLASS__, 'email_keys_to_customer' ) );

		// Order item meta.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'add_hidden_order_itemmeta' ) );
		add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'display_order_item_meta' ), 10, 3 );
	}


	/**
	 * Add order key status column.
	 *
	 * @param array $columns Order columns.
	 *
	 * @since 1.0.0
	 * @return array|string[]
	 */
	public static function add_order_columns( $columns ) {
		$position = 3;
		$column   = array(
			'order_keys' => sprintf(
				'<span class="wckm-keys-status-icon tips" data-tip="%s"></span>',
				esc_html__( 'Whether the order contains keyed products.', 'wc-key-manager' )
			),
		);

		return array_slice( $columns, 0, $position, true ) + $column + array_slice( $columns, $position, count( $columns ) - $position, true );
	}

	/**
	 * Add order key status column content.
	 *
	 * @param string $column Column name.
	 * @param int    $order_id Order ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function order_columns_content( $column, $order_id ) {
		$order = wc_get_order( $order_id );

		if ( 'order_keys' !== $column ) {
			return;
		}

		if ( ! in_array( $order->get_status(), wckm_get_order_paid_statuses(), true ) || 'yes' !== $order->get_meta( '_wckm_order' ) ) {
			echo '&mdash;';

			return;
		}

		$style = 'color:green';
		$title = __( 'Order contains keyed products.', 'wc-key-manager' );

		printf( '<span class="dashicons dashicons-yes tips" style="%s" data-tip="%s"></span>', esc_attr( $style ), esc_html( $title ) );
	}


	/**
	 * Add order actions.
	 *
	 * @param array     $actions Order actions.
	 * @param \WC_Order $order Order object.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function add_order_actions( $actions, $order ) {
		if ( ! wckm_order_has_products( $order->get_id() ) ) {
			return $actions;
		}

		$actions = array_merge(
			$actions,
			apply_filters(
				'wc_key_manager_admin_order_actions',
				array(
					'wckm_add_keys'    => esc_html__( 'Add pending keys to the order', 'wc-key-manager' ),
					'wckm_remove_keys' => esc_html__( 'Remove all keys from the order', 'wc-key-manager' ),
					'wckm_send_keys'   => esc_html__( 'Send keys to the customer', 'wc-key-manager' ),
				),
				$order
			)
		);

		return $actions;
	}

	/**
	 * Add keys to order.
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @since 1.0.0
	 */
	public static function add_keys_to_order( $order ) {
		$order_id = $order->get_id();
		$user_id  = get_current_user_id();

		WCKM()->log(
			sprintf(
			// translators: %d: order ID %d: user ID.
				esc_html__( 'Manually adding keys to order #%1$d by user #%2$d.', 'wc-key-manager' ),
				$order_id,
				$user_id
			)
		);

		$keys = wckm_add_order_keys( $order_id );

		if ( $keys ) {
			WCKM()->flash->success(
				sprintf(
				// translators: %s: number of keys added.
					esc_html__( 'Added %s keys to the order.', 'wc-key-manager' ),
					number_format_i18n( $keys ),
				)
			);

			return;
		}

		WCKM()->flash->error( esc_html__( 'No keys were added to the order.', 'wc-key-manager' ) );
	}

	/**
	 * Remove keys from order.
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @since 1.0.0
	 */
	public static function remove_keys_from_order( $order ) {
		$order_id = $order->get_id();
		$user_id  = get_current_user_id();

		WCKM()->log(
			sprintf(
			// translators: %d: order ID %d: user ID.
				esc_html__( 'Manually removing keys from order #%1$d by user #%2$d.', 'wc-key-manager' ),
				$order_id,
				$user_id
			)
		);

		$removed = wckm_remove_order_keys( $order_id );

		if ( ! empty( $removed ) ) {
			WCKM()->flash->success(
				sprintf(
				// translators: %s: number of keys removed.
					esc_html__( 'Removed %s keys from the order.', 'wc-key-manager' ),
					number_format_i18n( $removed ),
				)
			);

			return;
		}

		WCKM()->flash->error( esc_html__( 'No keys were removed from the order.', 'wc-key-manager' ) );
	}

	/**
	 * Send keys to customer.
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @since 1.0.0
	 */
	public static function email_keys_to_customer( $order ) {
		if ( ! wckm_order_has_products( $order->get_id() ) ) {
			WCKM()->flash->error( esc_html__( 'The order does not contain any keyed products. No keys were sent.', 'wc-key-manager' ) );

			return;
		}
		WC()->mailer()->emails['WCKM_Email_Customer_Keys']->trigger( $order->get_id() );
		WCKM()->flash->success( esc_html__( 'Keys have been sent to the customer.', 'wc-key-manager' ) );
	}

	/**
	 * Add hidden order item meta.
	 *
	 * @param array $hidden_order_itemmeta Hidden order item meta.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function add_hidden_order_itemmeta( $hidden_order_itemmeta ) {
		return array_merge(
			$hidden_order_itemmeta,
			array(
				'_wckm_order_item',
				'_wckm_delivery_qty',
			)
		);
	}

	/**
	 * Show order item meta.
	 *
	 * @param int            $item_id Item ID.
	 * @param \WC_Order_Item $item Item.
	 * @param \WC_Product    $product Product.
	 *
	 * @since 1.0.0
	 */
	public static function display_order_item_meta( $item_id, $item, $product ) {
		$order_id = wc_get_order_id_by_order_item_id( $item_id );
		if ( ! wckm_order_has_products( $order_id ) || ! $product || ! 'yes' === $item->get_meta( '_wckm_order_item' ) ) {
			return;
		}

		$delivery_qty = max( 1, (int) $item->get_meta( '_wckm_delivery_qty', true ) );
		$ordered_qty  = $delivery_qty * $item->get_quantity();
		$keys         = wckm_get_keys(
			array(
				'order_id'      => $order_id,
				'order_item_id' => $item_id,
				'product_id'    => $product->get_id(),
				'limit'         => - 1,
			)
		);

		echo '<p style="color: #888;font-weight: 600;">' . esc_html__( 'Related keys:', 'wc-key-manager' ) . '</p>';

		// If keys count is less than the ordered quantity, show a notice that keys are not fulfilled.
		if ( count( $keys ) < $ordered_qty && in_array( $item->get_order()->get_status(), wckm_get_order_paid_statuses(), true ) ) {
			$notice = sprintf(
			// translators: %s: number of keys pending.
				esc_html__( 'Alert: There are %s key(s) pending for this item.', 'wc-key-manager' ),
				number_format( $ordered_qty - count( $keys ) )
			);
			echo '<p class="wckm-notfulfiled-notice">' . esc_html( $notice ) . '</p>';
		}

		foreach ( $keys as $key ) {
			$data = array(
				array(
					'key'    => 'key',
					'label'  => __( 'Key', 'wc-key-manager' ),
					'value'  => $key->code,
					'render' => $key->get_key_html(),
				),
				array(
					'key'   => 'expires',
					'label' => __( 'Expires', 'wc-key-manager' ),
					'value' => $key->get_expires_html(),
				),
				array(
					'key'   => 'activations',
					'label' => __( 'Activations', 'wc-key-manager' ),
					'value' => $key->get_activations_html(),
				),
				array(
					'key'    => 'actions',
					'label'  => __( 'Actions', 'wc-key-manager' ),
					'value'  => '',
					'render' => '<a href="' . esc_url( admin_url( 'admin.php?page=wckm-keys&edit=' . $key->id ) ) . '" target="_blank">' . esc_html__( 'Edit key &rarr;', 'wc-key-manager' ) . '</a>',
				),
			);

			$data = apply_filters( 'wc_key_manager_admin_order_item_key_attributes', $data, $key, $item, $product, $order_id );
			if ( empty( $data ) ) {
				continue;
			}

			?>

			<table cellspacing="0" class="display_meta wckm-admin-order-item-meta">
				<tbody>
				<?php foreach ( $data as $field ) : ?>
					<tr class="wckm-order-item-meta__row wckm-order-item-meta_row--<?php echo sanitize_html_class( $field['key'] ); ?>">
						<th class="wckm-order-item-meta__label wckm-order-item-meta__label--<?php echo sanitize_html_class( $field['key'] ); ?>" scope="row"><?php echo esc_html( $field['label'] ); ?>:</th>
						<td class="wckm-order-item-meta__value wckm-order-item-meta__value--<?php echo sanitize_html_class( $field['key'] ); ?>">
							<?php echo isset( $field['render'] ) ? wp_kses_post( $field['render'] ) : wp_kses_post( $field['value'] ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		}
	}
}
