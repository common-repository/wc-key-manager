<?php
namespace KeyManager\Admin\ListTables;

defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded.
if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class ListTable.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\ListTables
 */
class ListTable extends \WP_List_Table {
	/**
	 * Current page URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $base_url;

	/**
	 * Return the sortable column specified for this request to order the results by, if any.
	 *
	 * @param string $fallback Default order.
	 *
	 * @return string
	 */
	protected function get_request_orderby( $fallback = 'id' ) {
		wp_verify_nonce( '_wpnonce' );
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
		return in_array( $orderby, $this->get_sortable_columns(), true ) ? $orderby : $fallback;
	}


	/**
	 * Return the order specified for this request, if any.
	 *
	 * @param string $fallback Default order.
	 *
	 * @return string
	 */
	protected function get_request_order( $fallback = 'DESC' ) {
		wp_verify_nonce( '_wpnonce' );
		$order = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : '';
		return in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $order ) : $fallback;
	}

	/**
	 * Return the status filter for this request, if any.
	 *
	 * @param string $fallback Default status.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_request_status( $fallback = null ) {
		wp_verify_nonce( '_wpnonce' );
		$status = ( ! empty( $_GET['status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		return empty( $status ) ? $fallback : $status;
	}

	/**
	 * Return the search filter for this request, if any.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_request_search() {
		wp_verify_nonce( '_wpnonce' );
		return ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	}

	/**
	 * Checks if the current request has a bulk action. If that is the case it will validate and will
	 * execute the bulk method handler. Regardless if the action is valid or not it will redirect to
	 * the previous page removing the current arguments that makes this request a bulk action.
	 */
	protected function process_actions() {
		$this->_column_headers = array( $this->get_columns(), get_hidden_columns( $this->screen ), $this->get_sortable_columns() );

		// Detect when a bulk action is being triggered.
		$action = $this->current_action();
		if ( ! $action ) {
			return;
		}

		check_admin_referer( 'bulk-' . $this->_args['plural'] );

		$ids    = isset( $_GET['id'] ) ? map_deep( wp_unslash( $_GET['id'] ), 'intval' ) : array();
		$ids    = wp_parse_id_list( $ids );
		$method = 'bulk_' . $action;
		if ( array_key_exists( $action, $this->get_bulk_actions() ) && method_exists( $this, $method ) && ! empty( $ids ) ) {
			$this->$method( $ids );
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) || isset( $_REQUEST['_wp_http_referer'] ) ) {
			wp_safe_redirect(
				remove_query_arg(
					array( '_wp_http_referer', '_wpnonce', 'id', 'action', 'action2' ),
					esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
				)
			);
			exit;
		}
	}

	/**
	 * Render column metadata.
	 *
	 * @param array $items Items to render.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function column_metadata( $items ) {
		if ( ! empty( $items ) ) {
			$items    = array_filter( $items );
			$metadata = sprintf( '<div class="wckm-column-metadata"><span>%s</span></div>', implode( '</span><span>', $items ) );
			return wp_kses_post( $metadata );
		}

		return '';
	}

	/**
	 * Get order title.
	 *
	 * @param int|\WC_Order $order Order ID or object.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_order_title( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( $order ) {
			$billing_name = empty( $order->get_billing_first_name() ) ? esc_html__( 'Guest', 'wc-key-manager' ) : $order->get_formatted_billing_full_name();
			return sprintf( '#%d - %s', $order->get_order_number(), $billing_name );
		}

		return '&mdash;';
	}

	/**
	 * Get order dropdown.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function order_dropdown() {
		$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$order    = ! empty( $order_id ) ? wc_get_order( $order_id ) : null;
		?>
		<label for="filter-by-order-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by order', 'wc-key-manager' ); ?>
		</label>
		<select class="wckm_select2" name="order_id" id="filter-by-order-id" data-action="wckm_json_search" data-type="order" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Filter by order', 'wc-key-manager' ); ?>">
			<?php if ( ! empty( $order ) ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<?php echo esc_html( $order->get_formatted_billing_full_name() ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Get product dropdown.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function product_dropdown() {
		$product_id = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		$product    = ! empty( $product_id ) ? wc_get_product( $product_id ) : null;
		?>
		<label for="filter-by-product-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by product', 'wc-key-manager' ); ?>
		</label>
		<select class="wckm_select2" name="product_id" id="filter-by-product-id" data-action="wckm_json_search" data-type="product" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Filter by product', 'wc-key-manager' ); ?>">
			<?php if ( ! empty( $product ) ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $product->get_id() ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Get customer dropdown.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function customer_dropdown() {
		$customer_id = filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );
		$customer    = ! empty( $customer_id ) ? new \WC_Customer( $customer_id ) : null;
		?>
		<label for="filter-by-customer-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by customer', 'wc-key-manager' ); ?>
		</label>
		<select class="wckm_select2" name="customer_id" id="filter-by-customer-id" data-action="wckm_json_search" data-type="customer" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wckm_json_search' ) ); ?>" data-placeholder="<?php esc_attr_e( 'Filter by customer', 'wc-key-manager' ); ?>">
			<?php if ( $customer && $customer->get_id() ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $customer->get_id() ); ?>">
					<?php echo esc_html( sprintf( '%s (%s)', $customer->get_first_name() . ' ' . $customer->get_last_name(), $customer->get_email() ) ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param Object|array $item The current item.
	 * @param string       $column_name The name of the column.
	 *
	 * @since 1.0.0
	 * @return string The column value.
	 */
	public function column_default( $item, $column_name ) {

		if ( is_object( $item ) && method_exists( $item, "get_$column_name" ) ) {
			$getter = "get_$column_name";

			return empty( $item->$getter( 'view' ) ) ? '&mdash;' : esc_html( $item->$getter( 'view' ) );
		} elseif ( is_array( $item ) && isset( $item[ $column_name ] ) ) {
			return empty( $item[ $column_name ] ) ? '&mdash;' : esc_html( $item[ $column_name ] );
		}

		return '&mdash;';
	}
}
