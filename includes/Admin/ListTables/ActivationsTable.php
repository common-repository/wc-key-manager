<?php

namespace KeyManager\Admin\ListTables;

use KeyManager\Models\Activation;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activations.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\ListTables
 */
class ActivationsTable extends ListTable {
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
					'singular' => 'activation',
					'plural'   => 'activations',
					'screen'   => get_current_screen(),
					'args'     => array(),
				)
			)
		);

		$this->base_url = admin_url( 'admin.php?page=wckm-keys&tab=activations' );
	}

	/**
	 * Prepares the list for display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$this->process_actions();
		$per_page = $this->get_items_per_page( 'wckm_activations_per_page', 20 );
		$paged    = $this->get_pagenum();
		$search   = $this->get_request_search();
		$order_by = $this->get_request_orderby();
		$order    = $this->get_request_order();
		$args     = array(
			'limit'      => $per_page,
			'page'       => $paged,
			'search'     => $search,
			'orderby'    => $order_by,
			'order'      => $order,
			'status'     => $this->get_request_status(),
			'product_id' => filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT ),
			'order_id'   => filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT ),
			'key_id'     => filter_input( INPUT_GET, 'key_id', FILTER_SANITIZE_NUMBER_INT ),
		);

		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'wc_key_manager_activations_table_query_args', $args );

		if ( ! empty( $args['order_id'] ) || ! empty( $args['product_id'] ) ) {
			global $wpdb;
			$query = "SELECT id FROM {$wpdb->prefix}wckm_keys WHERE 1=1";
			if ( ! empty( $args['order_id'] ) ) {
				$query .= $wpdb->prepare( ' AND order_id = %d', $args['order_id'] );
			}
			if ( ! empty( $args['product_id'] ) ) {
				$query .= $wpdb->prepare( ' AND product_id = %d', $args['product_id'] );
			}
			$key_ids            = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared in the query.
			$args['key_id__in'] = ! empty( $key_ids ) ? $key_ids : array( 0 );
		}

		// if product id is set, get all keys for the product.
		$args['no_found_rows'] = false;
		$this->items           = Activation::results( $args );
		$total                 = Activation::count( $args );

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
			if ( wckm_delete_activation( $id ) ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of accounts.
			WCKM()->flash->success( sprintf( __( '%s activations(s) deleted successfully.', 'wc-key-manager' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Handle bulk activation status change.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_activate( $ids ) {
		$performed = 0;
		foreach ( $ids as $id ) {
			$activation = Activation::find( $id );
			if ( $activation ) {
				$saved = $activation->set( 'status', 'active' )->save();
				if ( ! is_wp_error( $saved ) ) {
					++$performed;
				}
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of accounts.
			WCKM()->flash->success( sprintf( __( '%s activations(s) activated successfully.', 'wc-key-manager' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Handle bulk deactivation status change.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_deactivate( $ids ) {
		$performed = 0;
		foreach ( $ids as $id ) {
			$activation = Activation::find( $id );
			if ( $activation ) {
				$saved = $activation->set( 'status', 'inactive' )->save();
				if ( ! is_wp_error( $saved ) ) {
					++$performed;
				}
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of accounts.
			WCKM()->flash->success( sprintf( __( '%s activations(s) deactivated successfully.', 'wc-key-manager' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Outputs 'no results' message.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No activations found.', 'wc-key-manager' );
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
			Activation::get_statuses()
		);

		foreach ( $statuses as $status => $label ) {
			$link  = 'all' === $status ? $this->base_url : add_query_arg( 'status', $status, $this->base_url );
			$args  = 'all' === $status ? array() : array( 'status' => $status );
			$count = Activation::count( $args );
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
			submit_button( __( 'Filter', 'wc-key-manager' ), '', 'filter_action', false );
		}
		echo '</div>';
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
			'delete'     => __( 'Delete', 'wc-key-manager' ),
			'activate'   => __( 'Activate', 'wc-key-manager' ),
			'deactivate' => __( 'Deactivate', 'wc-key-manager' ),
		);
	}

	/**
	 * Gets a list of columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		return apply_filters(
			'wc_key_manager_activations_table_columns',
			array(
				'cb'       => '<input type="checkbox" />',
				'instance' => __( 'Instance', 'wc-key-manager' ),
				'key'      => __( 'Key', 'wc-key-manager' ),
				'product'  => __( 'Product', 'wc-key-manager' ),
				'order'    => __( 'Order', 'wc-key-manager' ),
				'status'   => __( 'Status', 'wc-key-manager' ),
			)
		);
	}

	/**
	 * Gets a list of sortable columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of sortable columns.
	 */
	protected function get_sortable_columns() {
		return apply_filters(
			'wc_key_manager_activations_table_sortable_columns',
			array(
				'instance' => array( 'instance', false ),
				'key'      => array( 'key', false ),
				'product'  => array( 'product', false ),
				'order'    => array( 'order', false ),
				'status'   => array( 'status', false ),
			)
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
		return 'instance';
	}

	/**
	 * Renders the checkbox column.
	 * since 1.0.0
	 *
	 * @param Activation $item The current item.
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%d"/>', esc_attr( $item->id ) );
	}

	/**
	 * Display instance.
	 *
	 * @param Activation $item Activation object.
	 *
	 * @since 1.0.0
	 */
	protected function column_instance( $item ) {
		return sprintf( '<a class="row-title" href="%s"><strong>%s</strong></a>', esc_url( add_query_arg( 'edit', $item->id, $this->base_url ) ), esc_html( $item->instance ) );
	}

	/**
	 * Display key.
	 *
	 * @param Activation $item Activation object.
	 *
	 * @since 1.0.0
	 */
	protected function column_key( $item ) {
		if ( $item->key ) {
			$metadata = array(
				'view' => sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						add_query_arg(
							array(
								'page' => 'wckm-keys',
								'tab'  => 'keys',
								'edit' => $item->key->id,
							),
							admin_url( 'admin.php' )
						)
					),
					__( 'View', 'wc-key-manager' )
				),
			);

			return $item->key->get_key_html() . $this->column_metadata( $metadata );
		}

		return '&mdash;';
	}

	/**
	 * Display product.
	 *
	 * @param Activation $item Activation object.
	 *
	 * @since 1.0.0
	 */
	protected function column_product( $item ) {
		if ( $item->key && $item->key->get_product() ) {
			$product  = $item->key->get_product();
			$name     = sprintf( '#%d - %s', $product->get_id(), $product->get_name() );
			$metadata = array(
				'price' => sprintf(
				/* translators: %s: product price */
					__( 'Price: %s', 'wc-key-manager' ),
					wc_price( $product->get_price() )
				),
			);

			return sprintf( '<a href="%s">%s</a>%s', esc_url( get_edit_post_link( $item->key->get_parent_product_id() ) ), esc_html( $name ), $this->column_metadata( $metadata ) );
		}

		return '&mdash;';
	}

	/**
	 * Display order.
	 *
	 * @param Activation $item Activation object.
	 *
	 * @since 1.0.0
	 */
	protected function column_order( $item ) {
		if ( $item->key && $item->key->get_order() ) {
			$order_link = get_edit_post_link( $item->key->get_order()->get_id() );
			$metadata   = array(
				'date' => $item->key->get_order()->get_date_created()->format( get_option( 'date_format' ) ),
				'time' => $item->key->get_order()->get_date_created()->format( get_option( 'time_format' ) ),
			);

			return sprintf( '<a href="%s">%s</a>%s', esc_url( $order_link ), esc_html( $this->get_order_title( $item->key->get_order() ) ), $this->column_metadata( $metadata ) );
		}

		return '&mdash;';
	}

	/**
	 * Display column status.
	 *
	 * @param Activation $item Activation object.
	 *
	 * @since 1.0.0
	 */
	protected function column_status( $item ) {
		return $item->get_status_html();
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Activation $item The object.
	 * @param string     $column_name Current column name.
	 * @param string     $primary Primary column name.
	 *
	 * @since 1.0.0
	 * @return string Row actions output.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return null;
		}

		$actions = array(
			'id'     => sprintf( '#%d', esc_attr( $item->id ) ),
			'edit'   => sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( 'edit', $item->id, $this->base_url ) ),
				__( 'Edit', 'wc-key-manager' )
			),
			'delete' => sprintf(
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
		// based on the status, add activate or deactivate action.
		if ( 'active' === $item->status ) {
			$actions['deactivate'] = sprintf(
				'<a href="%s" class="deactivate">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'deactivate',
								'id'     => $item->id,
							),
							$this->base_url
						),
						'bulk-' . $this->_args['plural']
					)
				),
				__( 'Deactivate', 'wc-key-manager' )
			);
		} else {
			$actions['activate'] = sprintf(
				'<a href="%s" class="activate">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'activate',
								'id'     => $item->id,
							),
							$this->base_url
						),
						'bulk-' . $this->_args['plural']
					)
				),
				__( 'Activate', 'wc-key-manager' )
			);
		}

		return $this->row_actions( $actions );
	}
}
