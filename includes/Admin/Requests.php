<?php

namespace KeyManager\Admin;

use KeyManager\Handlers\Stocks;
use KeyManager\Models\Activation;
use KeyManager\Models\Generator;
use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Requests
 *
 * Handles all the requests from the admin panel.
 *
 * @since 1.0.0
 * @subpackage Admin
 * @package KeyManager
 */
class Requests {

	/**
	 * Requests constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wckm_json_search', array( __CLASS__, 'handle_json_search' ) );
		add_action( 'admin_post_wckm_add_key', array( __CLASS__, 'handle_add_key' ) );
		add_action( 'admin_post_wckm_edit_key', array( __CLASS__, 'handle_edit_key' ) );
		add_action( 'admin_post_wckm_edit_generator', array( __CLASS__, 'handle_edit_generator' ) );
		add_action( 'admin_post_wckm_add_activation', array( __CLASS__, 'handle_add_activation' ) );
		add_action( 'admin_post_wckm_edit_activation', array( __CLASS__, 'handle_edit_activation' ) );
		add_action( 'admin_post_wckm_generate_bulk_keys', array( __CLASS__, 'generate_bulk_keys' ) );
		add_action( 'admin_post_wc_key_manager_import_csv', array( __CLASS__, 'handle_csv_import' ) );
		add_action( 'admin_post_wc_key_manager_import_txt', array( __CLASS__, 'handle_txt_import' ) );
	}

	/**
	 * Handle JSON search.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_json_search() {
		check_ajax_referer( 'wckm_json_search' );

		// must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to access this endpoint.', 'wc-key-manager' ) ) );
		}

		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$term    = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		$limit   = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 20;
		$page    = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$results = array();
		$total   = 0;

		$args = array(
			'paged' => $page,
			'limit' => $limit,
			's'     => $term,
		);

		switch ( $type ) {
			case 'product':
				$products = wckm_get_products( $args );
				$total    = wckm_get_products( $args, true );

				foreach ( $products as $product ) {
					$text = sprintf(
						'(#%1$s) %2$s',
						$product->get_id(),
						wp_strip_all_tags( $product->get_formatted_name() )
					);

					$results[] = array(
						'id'   => $product->get_id(),
						'text' => $text,
					);
				}
				break;

			case 'order':
				$ids = array();
				if ( is_numeric( $term ) ) {
					$order = wc_get_order( intval( $term ) );

					// Order does exist.
					if ( $order && 0 !== $order->get_id() ) {
						$ids[] = $order->get_id();
					}
				}

				if ( empty( $ids ) && ! is_numeric( $term ) ) {
					$data_store = \WC_Data_Store::load( 'order' );
					if ( 3 > strlen( $term ) ) {
						$per_page = 20;
					}
					$ids = $data_store->search_orders(
						$term,
						array(
							'limit' => $per_page,
							'page'  => $page,
						)
					);
				}

				$results = array();
				foreach ( $ids as $order_id ) {
					$order = wc_get_order( $order_id );

					if ( ! $order ) {
						continue;
					}

					$text = sprintf(
						'(#%1$s) %2$s',
						$order->get_id(),
						wp_strip_all_tags( $order->get_formatted_billing_full_name() )
					);

					$results[] = array(
						'id'   => $order->get_id(),
						'text' => $text,
					);
				}

				$total = count( $ids );

				break;

			case 'customer':
				$ids = array();
				// Search by ID.
				if ( is_numeric( $term ) ) {
					$customer = new \WC_Customer( intval( $term ) );

					// Customer does not exists.
					if ( $customer && 0 !== $customer->get_id() ) {
						$ids = array( $customer->get_id() );
					}
				}

				// Usernames can be numeric so we first check that no users was found by ID before searching for numeric username, this prevents performance issues with ID lookups.
				if ( empty( $ids ) ) {
					$data_store = \WC_Data_Store::load( 'customer' );

					// If search is smaller than 3 characters, limit result set to avoid
					// too many rows being returned.
					if ( 3 > strlen( $term ) ) {
						$per_page = 20;
					}
					$ids = $data_store->search_customers( $term, $per_page );
				}

				$results = array();
				foreach ( $ids as $id ) {
					$customer = new \WC_Customer( $id );
					$text     = sprintf(
					/* translators: $1: customer name, $2 customer id, $3: customer email */
						esc_html__( '%1$s (#%2$s - %3$s)', 'wc-key-manager' ),
						$customer->get_first_name() . ' ' . $customer->get_last_name(),
						$customer->get_id(),
						$customer->get_email()
					);

					$results[] = array(
						'id'   => $id,
						'text' => $text,
					);
				}
				$total = count( $ids );
				break;

			case 'generator':
				$args    = array(
					'status'        => 'active',
					'no_found_rows' => false,
					'paged'         => $page,
					'limit'         => $limit,
					'search'        => $term,
				);
				$results = Generator::results( $args );
				$total   = Generator::count( $args );
				$results = array_map(
					function ( $generator ) {
						return array(
							'id'   => $generator->id,
							'text' => $generator->name,
						);
					},
					$results
				);
				break;

		}

		wp_send_json(
			array(
				'results'    => $results,
				'total'      => $total,
				'page'       => $page,
				'pagination' => array(
					'more' => ( $page * $limit ) < $total,
				),
			)
		);

		exit();
	}

	/**
	 * Add a key.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_add_key() {
		check_admin_referer( 'wckm_add_key' );
		// must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$referer          = wp_get_referer();
		$product_id       = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$code             = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$valid_for        = isset( $_POST['valid_for'] ) ? absint( $_POST['valid_for'] ) : 0;
		$expires_at       = isset( $_POST['expires_at'] ) ? sanitize_text_field( wp_unslash( $_POST['expires_at'] ) ) : '';
		$activation_limit = isset( $_POST['activation_limit'] ) ? absint( $_POST['activation_limit'] ) : 0;
		$order_id         = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$customer_id      = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$action_type      = isset( $_POST['action_type'] ) ? sanitize_key( $_POST['action_type'] ) : 'new_key';
		$metadata         = isset( $_POST['metadata'] ) ? map_deep( wp_unslash( $_POST['metadata'] ), 'sanitize_text_field' ) : array();

		$data = array(
			'key'              => $code,
			'product_id'       => $product_id,
			'valid_for'        => $valid_for,
			'expires_at'       => $expires_at,
			'activation_limit' => $activation_limit,
			'status'           => 'available',
			'metadata'         => $metadata,
		);

		$key = wckm_insert_key( $data );
		if ( is_wp_error( $key ) ) {
			WCKM()->flash->error( $key->get_error_message() );
			wp_safe_redirect( $referer );
			exit;
		}

		// set the product as key product. If the is a variable product, then set the parent product as key product as well.
		update_post_meta( $product_id, '_wckm_keyed', 'yes' );
		$product = wc_get_product( $product_id );

		if ( $product && $product->is_type( 'variation' ) ) {
			update_post_meta( $product->get_parent_id(), '_wckm_keyed', 'yes' );
			update_post_meta( $product->get_id(), '_wckm_key_source', 'preset' );
		} elseif ( $product && $product->is_type( 'simple' ) ) {
			update_post_meta( $product->get_id(), '_wckm_key_source', 'preset' );
		}

		// If the status is new, then we are done here.
		if ( 'create_order' === $action_type ) {
			$order = wc_create_order( array( 'customer_id' => $customer_id ) );
			$order->update_status( 'pending' );
			$order_item_id = $order->add_product( wc_get_product( $product_id ) );
			$order_id      = $order->get_id();

			// If order item id is not found, then we can't add key to order item.
			if ( empty( $order_item_id ) ) {
				WCKM()->flash->error( __( 'Order item not found.', 'wc-key-manager' ) );
				wp_safe_redirect( $referer );
				exit;
			}

			$saved = $key->add_order( $order_id, $order_item_id );
			if ( is_wp_error( $saved ) ) {
				WCKM()->flash->error( $saved->get_error_message() );
				wp_safe_redirect( $referer );
				exit;
			}

			$notice = __( 'Successfully created order and added key to it.', 'wc-key-manager' );
			WCKM()->flash->success( $notice );
			wp_safe_redirect( add_query_arg( array( 'post' => $order_id ), admin_url( 'post.php?&action=edit' ) ) );
			exit();

		} elseif ( 'existing_order' === $action_type ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				WCKM()->flash->error( __( 'Selected order not found.', 'wc-key-manager' ) );
				wp_safe_redirect( $referer );
				exit;
			}

			// first check if we have the product in the order and if it does not have a key, then assign the key to that product.
			$order_item_id = false;
			foreach ( $order->get_items() as $item_id => $item ) {
				if ( ! wckm_is_keyed_product( $item->get_product_id() ) ) {
					continue;
				}
				$key_count = Key::count(
					array(
						'order_id'      => $order->get_id(),
						'order_item_id' => $item_id,
					)
				);
				if ( $item->get_product_id() === $product_id && 0 === $key_count ) {
					$order_item_id = $item_id;
					break;
				}
			}

			if ( ! $order_item_id ) {
				$order_item_id = $order->add_product( wc_get_product( $product_id ) );
			}
			$saved = $key->add_order( $order_id, $order_item_id );
			if ( is_wp_error( $saved ) ) {
				WCKM()->flash->error( $saved->get_error_message() );
				wp_safe_redirect( $referer );
				exit;
			}

			$notice = sprintf(
			/* translators: %s: order id */
				__( 'Key has been created and added to order #%s.', 'wc-key-manager' ),
				$order_id
			);
			WCKM()->flash->success( $notice );
			wp_safe_redirect( add_query_arg( array( 'post' => $order_id ), admin_url( 'post.php?&action=edit' ) ) );
			exit();
		}

		$notice = __( 'Key has been added successfully.', 'wc-key-manager' );
		WCKM()->flash->success( $notice );

		wp_safe_redirect( add_query_arg( array( 'edit' => $key->id ), admin_url( 'admin.php?page=wckm-keys' ) ) );
		exit;
	}

	/**
	 * Edit a key.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit_key() {
		check_admin_referer( 'wckm_edit_key' );
		// must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$referer          = wp_get_referer();
		$id               = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$code             = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$valid_for        = isset( $_POST['valid_for'] ) ? absint( $_POST['valid_for'] ) : 0;
		$expires_at       = isset( $_POST['expires_at'] ) ? sanitize_text_field( wp_unslash( $_POST['expires_at'] ) ) : '';
		$activation_limit = isset( $_POST['activation_limit'] ) ? absint( $_POST['activation_limit'] ) : 0;
		$status           = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'available';
		$metadata         = isset( $_POST['metadata'] ) ? map_deep( wp_unslash( $_POST['metadata'] ), 'sanitize_text_field' ) : array();

		$key = Key::make(
			array(
				'id'               => $id,
				'key'              => $code,
				'valid_for'        => $valid_for,
				'expires_at'       => $expires_at,
				'activation_limit' => $activation_limit,
				'status'           => $status,
				'metadata'         => $metadata,
			)
		);

		$saved = $key->save();
		if ( is_wp_error( $saved ) ) {
			WCKM()->flash->error( $saved->get_error_message() );
			wp_safe_redirect( $referer );
			exit;
		}

		$notice = __( 'Key has been updated successfully.', 'wc-key-manager' );
		WCKM()->flash->success( $notice );
		wp_safe_redirect( $referer );
		exit();
	}

	/**
	 * Edit a generator.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit_generator() {
		check_admin_referer( 'wckm_edit_generator' );

		// must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$referer          = wp_get_referer();
		$id               = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$name             = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$pattern          = isset( $_POST['pattern'] ) ? sanitize_text_field( wp_unslash( $_POST['pattern'] ) ) : '';
		$charset          = isset( $_POST['charset'] ) ? sanitize_text_field( wp_unslash( $_POST['charset'] ) ) : '';
		$valid_for        = isset( $_POST['valid_for'] ) ? absint( $_POST['valid_for'] ) : 0;
		$activation_limit = isset( $_POST['activation_limit'] ) ? absint( $_POST['activation_limit'] ) : 0;
		$status           = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'active';

		$generator = Generator::make(
			array(
				'id'               => $id,
				'name'             => $name,
				'pattern'          => $pattern,
				'charset'          => $charset,
				'valid_for'        => $valid_for,
				'activation_limit' => $activation_limit,
				'status'           => $status,
			)
		);

		$saved = $generator->save();
		if ( is_wp_error( $saved ) ) {
			WCKM()->flash->error( $saved->get_error_message() );
			wp_safe_redirect( $referer );
			exit;
		}

		$notice = __( 'Generator has been saved successfully.', 'wc-key-manager' );
		WCKM()->flash->success( $notice );
		wp_safe_redirect( add_query_arg( array( 'edit' => $generator->id ), admin_url( 'admin.php?page=wckm-keys&tab=generators' ) ) );
		exit;
	}

	/**
	 * Add activation.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_add_activation() {
		check_admin_referer( 'wckm_add_activation' );

		// must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$referer  = wp_get_referer();
		$code     = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$instance = isset( $_POST['instance'] ) ? sanitize_text_field( wp_unslash( $_POST['instance'] ) ) : '';

		$key = Key::find( array( 'key' => $code ) );
		if ( ! $key ) {
			WCKM()->flash->error( __( 'Key not found.', 'wc-key-manager' ) );
			wp_safe_redirect( $referer );
			exit;
		}

		$activation = $key->activate(
			array(
				'instance' => $instance,
			)
		);

		if ( is_wp_error( $activation ) ) {
			WCKM()->flash->error( $activation->get_error_message() );
			wp_safe_redirect( $referer );
			exit;
		}

		$notice = __( 'Activation has been added successfully.', 'wc-key-manager' );
		WCKM()->flash->success( $notice );
		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Edit activation.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit_activation() {
		check_admin_referer( 'wckm_edit_activation' );

		// must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$referer    = wp_get_referer();
		$id         = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$instance   = isset( $_POST['instance'] ) ? sanitize_text_field( wp_unslash( $_POST['instance'] ) ) : '';
		$ip_address = isset( $_POST['ip_address'] ) ? sanitize_text_field( wp_unslash( $_POST['ip_address'] ) ) : '';
		$user_agent = isset( $_POST['user_agent'] ) ? sanitize_text_field( wp_unslash( $_POST['user_agent'] ) ) : '';
		$status     = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'active';

		$activation = Activation::insert(
			array(
				'id'         => $id,
				'instance'   => $instance,
				'ip_address' => $ip_address,
				'user_agent' => $user_agent,
				'status'     => $status,
			)
		);

		$saved = $activation->save();
		if ( is_wp_error( $saved ) ) {
			WCKM()->flash->error( $saved->get_error_message() );
			wp_safe_redirect( $referer );
			exit;
		}

		$notice = __( 'Activation has been updated successfully.', 'wc-key-manager' );
		WCKM()->flash->success( $notice );
		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Bulk key generator.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function generate_bulk_keys() {
		check_admin_referer( 'wckm_generate_bulk_keys' );

		// must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$referer      = wp_get_referer();
		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$generator_id = isset( $_POST['generator_id'] ) ? absint( $_POST['generator_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

		define( 'WCKM_DISABLE_STOCK_SYNC', true );

		$keys = wckm_generate_keys(
			array(
				'product_id'   => $product_id,
				'generator_id' => $generator_id,
				'quantity'     => $quantity,
				'source'       => 'preset',
			)
		);

		if ( empty( $keys ) ) {
			WCKM()->flash->error( __( 'Could not generate any keys. Please check the generator settings.', 'wc-key-manager' ) );
			wp_safe_redirect( $referer );
			exit;
		}

		update_post_meta( $product_id, '_wckm_keyed', 'yes' );
		update_post_meta( $product_id, '_wckm_key_source', 'preset' );
		// update stock.
		Stocks::synchronize_stocks( array( $product_id ) );

		// Translators: %s: number of keys generated.
		$notice = sprintf( esc_html__( '%s keys have been generated successfully.', 'wc-key-manager' ), number_format( count( $keys ) ) );
		WCKM()->flash->success( $notice );
		wp_safe_redirect( $referer );
		exit();
	}

	/**
	 * Import keys from the CSV file.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_csv_import() {
		check_admin_referer( 'wc_key_manager_import_csv' );

		// Must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$file = isset( $_FILES['csv_file'] ) ? map_deep( $_FILES['csv_file'], 'sanitize_text_field' ) : array();

		// bail if no file is uploaded.
		if ( empty( $file ) || empty( $file['name'] ) || ! empty( $file['error'] ) ) {
			WCKM()->flash->error( __( 'Error: No file uploaded.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$info = wp_check_filetype( $file['name'] );
		if ( ! in_array( $info['type'], array( 'application/vnd.ms-excel', 'text/csv', 'text/tsv' ), true ) ) {
			WCKM()->flash->error( __( 'Invalid file type, only CSV allowed.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		if ( ! is_readable( $file['tmp_name'] ) ) {
			WCKM()->flash->error( __( 'Error: File is not readable.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		if ( 0 === filesize( $file['tmp_name'] ) ) {
			WCKM()->flash->error( __( 'Error: File is empty.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// Open WordPress file for reading. If the file does not exist, throw an exception.
		$handle = fopen( $file['tmp_name'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- We need to read the file.
		if ( ! $handle ) {
			WCKM()->flash->error( __( 'Error: File does not exist.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// Get the header of the CSV file.
		$csv_header = apply_filters(
			'wc_key_manager_csv_import_headers',
			array_map( 'strtolower', array_map( 'trim', fgetcsv( $handle ) ) )
		);

		// Check required columns are present.
		$required_columns = apply_filters(
			'wc_key_manager_csv_import_required_columns',
			array(
				'product_id',
				'key',
			)
		);

		foreach ( $required_columns as $column ) {
			if ( ! in_array( $column, $csv_header, true ) ) {
				// Translators: %s is the missing column name.
				WCKM()->flash->error( sprintf( __( 'Error: Required column "%s" is missing.', 'wc-key-manager' ), $column ) );
				wp_safe_redirect( wp_get_referer() );
				exit;
			}
		}

		$data = array();
		while ( ( $row = fgetcsv( $handle ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- We need to read the file.
			// bail if row is empty.
			if ( empty( $row ) || ( count( $csv_header ) !== count( $row ) ) ) {
				continue;
			}
			$data[] = apply_filters(
				'wc_key_manager_csv_import_row_data',
				array_combine( $csv_header, $row )
			);
			// only process 1000 rows.
			if ( count( $data ) > 1000 ) {
				break;
			}
		}

		// Close the file.
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		// Remove the file.
		if ( file_exists( $file['tmp_name'] ) ) {
			wp_delete_file( $file['tmp_name'] );
		}

		// Check if records are present.
		if ( empty( $data ) ) {
			WCKM()->flash->error( __( 'Error: No records found.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$total_records = count( $data );
		$success       = 0;
		$failed        = 0;

		define( 'WCKM_DISABLE_STOCK_SYNC', true );

		// Loop through the CSV data.
		foreach ( $data as $item ) {
			// Check if product id is present.
			if ( empty( $item['product_id'] ) ) {
				++$failed;
				continue;
			}

			// Check if the key/code is present.
			if ( empty( $item['key'] ) ) {
				++$failed;
				continue;
			}

			$key = wckm_insert_key( $item, true );
			if ( is_wp_error( $key ) ) {
				++$failed;
			} else {
				++$success;
				// Enable product for selling key.
				update_post_meta( $key->product_id, '_wckm_keyed', 'yes' );
				update_post_meta( $key->product_id, '_wckm_key_source', 'preset' );
			}
		}

		$product_ids = array_unique( wp_list_pluck( $data, 'product_id' ) );
		wckm_sync_stock( $product_ids );

		// Translators: %1$d: total records, %2$d: success, %3$d: failed.
		WCKM()->flash->success( sprintf( esc_html__( 'CSV import completed. Total records: %1$d, Success: %2$d, Failed: %3$d', 'wc-key-manager' ), $total_records, $success, $failed ) );
		wp_safe_redirect( wp_get_referer() );
		exit;
	}

	/**
	 * Import keys from the TXT file.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_txt_import() {
		check_admin_referer( 'wc_key_manager_import_txt' );

		// Must have WCKM_MANAGER_ROLE to access this endpoint.
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			WCKM()->flash->error( __( 'You do not have permission to perform this action.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$product_id       = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$activation_limit = isset( $_POST['activation_limit'] ) ? absint( $_POST['activation_limit'] ) : 0;
		$valid_for        = isset( $_POST['valid_for'] ) ? absint( $_POST['valid_for'] ) : 0;
		$file             = isset( $_FILES['txt_file'] ) ? map_deep( $_FILES['txt_file'], 'sanitize_text_field' ) : array();

		if ( empty( $product_id ) ) {
			WCKM()->flash->error( __( 'Error: Please select a product.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// bail if no file is uploaded.
		if ( empty( $file ) || empty( $file['name'] ) || ! empty( $file['error'] ) ) {
			WCKM()->flash->error( __( 'Error: No file uploaded.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$info = wp_check_filetype( $file['name'] );
		if ( ! in_array( $info['type'], array( 'text/plain' ), true ) ) {
			WCKM()->flash->error( __( 'Error: Invalid file type.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		if ( ! is_readable( $file['tmp_name'] ) ) {
			WCKM()->flash->error( __( 'Error: File is not readable.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		if ( 0 === filesize( $file['tmp_name'] ) ) {
			WCKM()->flash->error( __( 'Error: File is empty.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$data = file( $file['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		$data = array_map( 'sanitize_text_field', $data );

		// Check if records are present.
		if ( empty( $data ) ) {
			WCKM()->flash->error( __( 'Error: No records found.', 'wc-key-manager' ) );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$records = count( $data );
		$success = 0;
		$failed  = 0;

		define( 'WCKM_DISABLE_STOCK_SYNC', true );

		// Loop through the data.
		foreach ( $data as $key ) {
			$key = wckm_insert_key(
				apply_filters(
					'wc_key_manager_import_txt_key_data',
					array(
						'key'              => $key,
						'product_id'       => $product_id,
						'activation_limit' => $activation_limit,
						'valid_for'        => $valid_for,
						'status'           => 'available',
					)
				)
			);

			if ( is_wp_error( $key ) ) {
				++$failed;
			} else {
				++$success;
				// Enable product for selling key.
				update_post_meta( $key->product_id, '_wckm_keyed', 'yes' );
				update_post_meta( $key->product_id, '_wckm_key_source', 'preset' );
			}
		}

		wckm_sync_stock( array( $product_id ) );

		// Show notice.
		// translators: %1$d: total records, %2$d: success, %3$d: failed.
		WCKM()->flash->success( sprintf( esc_html__( 'TXT import completed. Total records: %1$d, Success: %2$d, Failed: %3$d', 'wc-key-manager' ), $records, $success, $failed ) );
		wp_safe_redirect( wp_get_referer() );
	}
}
