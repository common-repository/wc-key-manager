<?php
namespace KeyManager\Admin\ListTables;

use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class KeysTable.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\ListTables
 */
class KeysTable extends ListTable {
	/**
	 * Constructor.
	 *
	 * @param array $args An associative array of arguments.
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 * @since 1.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct(
			wp_parse_args(
				$args,
				array(
					'singular' => 'key',
					'plural'   => 'keys',
					'screen'   => get_current_screen(),
					'args'     => array(),
				)
			)
		);

		$this->base_url = admin_url( 'admin.php?page=wckm-keys' );
	}

	/**
	 * Prepares the list for display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$this->process_actions();
		$per_page = $this->get_items_per_page( 'wckm_keys_per_page', 20 );
		$paged    = $this->get_pagenum();
		$search   = $this->get_request_search();
		$order_by = $this->get_request_orderby( 'order_id' );
		$order    = $this->get_request_order();
		$args     = array(
			'limit'       => $per_page,
			'page'        => $paged,
			'search'      => $search,
			'orderby'     => $order_by,
			'order'       => $order,
			'status'      => $this->get_request_status(),
			'product_id'  => filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT ),
			'order_id'    => filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT ),
			'customer_id' => filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT ),
		);

		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'wc_key_manager_keys_table_query_args', $args );

		$args['no_found_rows'] = false;
		$this->items           = Key::results( $args );
		$total                 = Key::count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
			)
		);
	}


	/**
	 * handle bulk delete action.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_delete( $ids ) {
		$performed = 0;
		foreach ( $ids as $id ) {
			if ( wckm_delete_key( $id ) ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of accounts.
			WCKM()->flash->success( sprintf( __( '%s key(s) deleted successfully.', 'wc-key-manager' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Outputs 'no results' message.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No keys found.', 'wc-key-manager' );
	}

	/**
	 * Returns an associative array listing all the views that can be used
	 * with this table.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] An array of HTML links keyed by their view.
	 */
	protected function get_views() {
		$current      = $this->get_request_status( 'all' );
		$status_links = array();
		$statuses     = array_merge(
			array(
				'all' => __( 'All', 'wc-key-manager' ),
			),
			Key::get_statuses()
		);

		foreach ( $statuses as $status => $label ) {
			$link  = 'all' === $status ? $this->base_url : add_query_arg( 'status', $status, $this->base_url );
			$args  = 'all' === $status ? array() : array( 'status' => $status );
			$count = Key::count( $args );
			$label = sprintf( '%s <span class="count">(%s)</span>', esc_html( $label ), number_format_i18n( $count ) );

			$status_links[ $status ] = array(
				'url'     => $link,
				'label'   => $label,
				'current' => $current === $status,
			);
		}

		return $this->get_views_links( $status_links );
	}

	/**
	 * Retrieves an associative array of bulk actions available on this table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of bulk action labels keyed by their action.
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'wc-key-manager' ),
		);
	}

	/**
	 * Outputs the controls to allow user roles to be changed in bulk.
	 *
	 * @param string $which Whether invoked above ("top") or below the table ("bottom").
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		static $has_items;
		if ( ! isset( $has_items ) ) {
			$has_items = $this->has_items();
		}

		echo '<div class="alignleft actions">';
		if ( 'top' === $which ) {
			$this->product_dropdown();
			$this->order_dropdown();
			$this->customer_dropdown();
			submit_button( __( 'Filter', 'wc-key-manager' ), '', 'filter_action', false );
		}
		echo '</div>';
	}


	/**
	 * Gets a list of columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'key'         => __( 'Key', 'wc-key-manager' ),
			'product'     => __( 'Product', 'wc-key-manager' ),
			'order'       => __( 'Order', 'wc-key-manager' ),
			'expires'     => __( 'Expires', 'wc-key-manager' ),
			'activations' => __( 'Activations', 'wc-key-manager' ),
			'status'      => __( 'Status', 'wc-key-manager' ),
		);

		/**
		 * Filter the columns for the list table.
		 *
		 * @param array $columns An associative array of column titles keyed by their column name.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'wc_key_manager_keys_table_columns', $columns );
	}

	/**
	 * Gets a list of sortable columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of sortable columns.
	 */
	protected function get_sortable_columns() {
		return array(
			'key'         => array( 'key', true ),
			'product'     => array( 'product_id', true ),
			'order'       => array( 'order_id', false ),
			'expires'     => array( 'date_expires', true ),
			'activations' => array( 'activation_limit', true ),
			'status'      => array( 'status', true ),
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'key';
	}

	/**
	 * Renders the checkbox column.
	 * since 1.0.0
	 *
	 * @param Key $item The current item.
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%d"/>', esc_attr( $item->id ) );
	}

	/**
	 * Display key.
	 *
	 * @param Key $item Item.
	 *
	 * @since 1.0.0
	 */
	protected function column_key( $item ) {
		return $item->get_key_html();
	}

	/**
	 * Display column order.
	 *
	 * @param Key $item Key object.
	 *
	 * @since 1.0.0
	 */
	protected function column_order( $item ) {
		if ( $item->get_order() ) {
			$order_link = get_edit_post_link( $item->get_order()->get_id() );
			$metadata   = array(
				'date' => $item->get_order()->get_date_created()->format( get_option( 'date_format' ) ),
				'time' => $item->get_order()->get_date_created()->format( get_option( 'time_format' ) ),
			);
			return sprintf( '<a href="%s">%s</a>%s', esc_url( $order_link ), esc_html( $this->get_order_title( $item->get_order() ) ), $this->column_metadata( $metadata ) );
		}

		return '&mdash;';
	}

	/**
	 * Display column product.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.0.0
	 */
	protected function column_product( $key ) {
		if ( $key->get_product() ) {
			$product  = $key->get_product();
			$name     = sprintf( '#%d - %s', $product->get_id(), $product->get_name() );
			$metadata = array(
				'price' => sprintf(
					/* translators: %s: product price */
					__( 'Price: %s', 'wc-key-manager' ),
					wc_price( $product->get_price() )
				),
			);

			return sprintf( '<a href="%s">%s</a>%s', esc_url( get_edit_post_link( $key->get_parent_product_id() ) ), esc_html( $name ), $this->column_metadata( $metadata ) );
		}

		return '&mdash;';
	}

	/**
	 * Display column expires.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.0.0
	 */
	protected function column_expires( $key ) {
		return $key->get_expires_html();
	}

	/**
	 * Display column activations.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.0.0
	 */
	protected function column_activations( $key ) {
		$metadata = array();
		if ( $key->activated_at ) {
			$metadata[] = sprintf(
				/* translators: %s: activation date */
				__( 'Activated at: %s', 'wc-key-manager' ),
				wp_date( get_option( 'date_format' ), strtotime( $key->activated_at ) )
			);
		}
		return $key->get_activations_html() . $this->column_metadata( $metadata );
	}

	/**
	 * Display column status.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.0.0
	 */
	protected function column_status( $key ) {
		return $key->get_status_html();
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Key    $item The object.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
	 *
	 * @since 1.0.0
	 * @return string Row actions output.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return null;
		}

		$actions = array(
			'edit'     => sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( 'edit', $item->id, $this->base_url ) ),
				__( 'Edit', 'wc-key-manager' )
			),
			'activate' => sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'tab'    => 'activations',
							'key_id' => $item->id,
							'add'    => 'yes',
						),
						$this->base_url
					)
				),
				__( 'Activate', 'wc-key-manager' )
			),
			'delete'   => sprintf(
				'<a href="%s" class="del">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $item->id,
							),
							$this->base_url
						),
						'bulk-' . $this->_args['plural']
					)
				),
				__( 'Delete', 'wc-key-manager' )
			),
		);

		return $this->row_actions( $actions );
	}
}
