<?php

namespace KeyManager\Admin\ListTables;

use KeyManager\Models\Generator;

defined( 'ABSPATH' ) || exit;

/**
 * Class GeneratorsTable.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\ListTables
 */
class GeneratorsTable extends ListTable {
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
					'singular' => 'generator',
					'plural'   => 'generators',
					'screen'   => get_current_screen(),
					'args'     => array(),
				)
			)
		);

		$this->base_url = admin_url( 'admin.php?page=wckm-keys&tab=generators' );
	}

	/**
	 * Prepares the list for display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$this->process_actions();
		$per_page = $this->get_items_per_page( 'wckm_generators_per_page', 20 );
		$paged    = $this->get_pagenum();
		$search   = $this->get_request_search();
		$order_by = $this->get_request_orderby();
		$order    = $this->get_request_order();
		$args     = array(
			'limit'   => $per_page,
			'page'    => $paged,
			'search'  => $search,
			'orderby' => $order_by,
			'order'   => $order,
			'status'  => $this->get_request_status(),
		);

		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'wc_key_manager_generators_table_query_args', $args );

		$args['no_found_rows'] = false;
		$this->items           = Generator::results( $args );
		$total                 = Generator::count( $args );

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
			$generator = Generator::find( $id );
			if ( $generator && ! is_wp_error( $generator->delete() ) ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of accounts.
			WCKM()->flash->success( sprintf( __( '%s generator(s) deleted successfully.', 'wc-key-manager' ), number_format_i18n( $performed ) ) );
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
			$generator = Generator::find( $id );
			if ( $generator ) {
				$saved = $generator->set( 'status', 'active' )->save();
				if ( ! is_wp_error( $saved ) ) {
					++$performed;
				}
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of accounts.
			WCKM()->flash->success( sprintf( __( '%s generator(s) activated successfully.', 'wc-key-manager' ), number_format_i18n( $performed ) ) );
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
			$generator = Generator::find( $id );
			if ( $generator ) {
				$saved = $generator->set( 'status', 'inactive' )->save();
				if ( ! is_wp_error( $saved ) ) {
					++$performed;
				}
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of accounts.
			WCKM()->flash->success( sprintf( __( '%s generator(s) deactivated successfully.', 'wc-key-manager' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Outputs 'no results' message.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No generators found.', 'wc-key-manager' );
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
			Generator::get_statuses()
		);

		foreach ( $statuses as $status => $label ) {
			$link  = 'all' === $status ? $this->base_url : add_query_arg( 'status', $status, $this->base_url );
			$args  = 'all' === $status ? array() : array( 'status' => $status );
			$count = Generator::count( $args );
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
			'wc_key_manager_generators_table_columns',
			array(
				'cb'               => '<input type="checkbox" />',
				'name'             => __( 'Name', 'wc-key-manager' ),
				'pattern'          => __( 'Pattern', 'wc-key-manager' ),
				'valid_for'        => __( 'Valid For', 'wc-key-manager' ),
				'activation_limit' => __( 'Activation Limit', 'wc-key-manager' ),
				'status'           => __( 'Status', 'wc-key-manager' ),
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
			'wc_key_manager_generators_table_sortable_columns',
			array(
				'name'             => array( 'name', false ),
				'pattern'          => array( 'pattern', false ),
				'valid_for'        => array( 'valid_for', false ),
				'activation_limit' => array( 'activation_limit', false ),
				'status'           => array( 'status', false ),
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
		return 'name';
	}

	/**
	 * Renders the checkbox column.
	 * since 1.0.0
	 *
	 * @param Generator $item The current item.
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%d"/>', esc_attr( $item->id ) );
	}

	/**
	 * Display instance.
	 *
	 * @param Generator $item Generator object.
	 *
	 * @since 1.0.0
	 */
	protected function column_name( $item ) {
		return sprintf(
			'<a class="row-title" href="%s"><strong>%s</strong></a>',
			esc_url( add_query_arg( 'edit', $item->id, $this->base_url ) ),
			esc_html( $item->name )
		);
	}

	/**
	 * Display column pattern.
	 *
	 * @param Generator $item Generator object.
	 *
	 * @since 1.0.0
	 */
	protected function column_pattern( $item ) {
		return wp_kses_post( '<code>' . esc_html( $item->pattern ) . '</code>' );
	}

	/**
	 * Display column valid_for.
	 *
	 * @param Generator $item Generator object.
	 *
	 * @since 1.0.0
	 */
	protected function column_valid_for( $item ) {
		if ( empty( $item->valid_for ) ) {
			return esc_html__( 'Lifetime', 'wc-key-manager' );
		}
		if ( ! empty( $item->valid_for ) ) {
			// translators: %d: number of days.
			return sprintf( _nx( '%d day <small>After Purchase</small>', '%d days <small>After Purchase</small>', $item->valid_for, 'valid for days', 'wc-key-manager' ), $item->valid_for );
		}

		return '&mdash;';
	}

	/**
	 * Display column activation_limit.
	 *
	 * @param Generator $item Generator object.
	 *
	 * @since 1.0.0
	 */
	protected function column_activation_limit( $item ) {
		if ( empty( $item->activation_limit ) ) {
			return esc_html__( 'Unlimited', 'wc-key-manager' );
		}
		if ( ! empty( $item->activation_limit ) ) {
			return number_format( $item->activation_limit );
		}

		return '&mdash;';
	}

	/**
	 * Display column status.
	 *
	 * @param Generator $item Generator object.
	 *
	 * @since 1.0.0
	 */
	protected function column_status( $item ) {
		return $item->get_status_html();
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Generator $item The object.
	 * @param string    $column_name Current column name.
	 * @param string    $primary Primary column name.
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
