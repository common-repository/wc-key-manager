<?php

namespace KeyManager\API;

use KeyManager\Models\Activation;

defined( 'ABSPATH' ) || exit;

/**
 * Activations controller class
 *
 * @since 1.0.0
 * @package KeyManager
 * @subpackage API
 */
class Activations extends Controller {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'activations';


	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		$get_item_args = array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the account.', 'wc-key-manager' ),
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $get_item_args,
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Retrieves a list of items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$params = $this->get_collection_params();
		$args   = array();
		foreach ( $params as $key => $value ) {
			if ( isset( $request[ $key ] ) ) {
				$args[ $key ] = $request[ $key ];
			}
		}

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a item request.
		 *
		 * @param array            $args Key value array of query var to query value.
		 * @param \WP_REST_Request $request The request used.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'wc_key_manager_activations_rest_query_args', $args, $request );

		$items = Activation::results( $args );
		$total = Activation::count( $args );

		$results = array();
		foreach ( $items as $item ) {
			$data      = $this->prepare_item_for_response( $item, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );
		$response->header( 'X-WP-Total', (int) $total );

		return $response;
	}

	/**
	 * Creates a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new \WP_Error(
				'rest_exists',
				__( 'Cannot create existing item.', 'wc-key-manager' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$item = Activation::insert( $data );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $item->id ) ) );

		return $response;
	}


	/**
	 * Updates a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$item = Activation::find( $request['id'] );

		// if not found.
		if ( ! $item ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'The activation was not found.', 'wc-key-manager' ),
				array( 'status' => 404 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$response = $item->fill( $data )->save();
		if ( is_wp_error( $item ) ) {
			return $response;
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Deletes a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$item = Activation::find( $request['id'] );
		if ( ! $item ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'The activation was not found.', 'wc-key-manager' ),
				array( 'status' => 404 )
			);
		}
		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $item, $request );

		if ( ! $item->delete() ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'The key cannot be deleted.', 'wc-key-manager' ),
				array( 'status' => 500 )
			);
		}

		$response = new \WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $this->prepare_response_for_collection( $data ),
			)
		);

		return $response;
	}

	/**
	 * Retrieves a single list.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$item = Activation::find( $request['id'] );
		$data = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Prepares a single item output for response.
	 *
	 * @param Activation       $item Item object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array();

		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {
			switch ( $key ) {
				case 'ordered_at':
				case 'created_at':
				case 'updated_at':
					$value = $this->prepare_date_response( $item->$key );
					break;
				default:
					$value = $item->$key;
					if ( is_object( $value ) && method_exists( $value, 'to_array' ) ) {
						$value = $value->to_array();
					}

					break;
			}

			$data[ $key ] = $value;
		}

		$context  = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );

		return $response;
	}


	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'activation',
			'type'       => 'object',
			'properties' => array(
				'id'         => array(
					'description' => __( 'Unique identifier for the item.', 'wc-key-manager' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'absint',
					),
				),
				'instance'   => array(
					'description' => __( 'Instance.', 'wc-key-manager' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'key_id'     => array(
					'description' => __( 'Key identifier.', 'wc-key-manager' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'absint',
					),
				),
				'key'        => array(
					'description' => __( 'Unique key.', 'wc-key-manager' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'ip_address' => array(
					'description' => __( 'IP address.', 'wc-key-manager' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'user_agent' => array(
					'description' => __( 'User agent.', 'wc-key-manager' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'status'     => array(
					'description' => __( 'Key status.', 'wc-key-manager' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'created_at' => array(
					'description' => __( 'The date the item was created.', 'wc-key-manager' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'embed' ),
					'readonly'    => true,
				),
				'updated_at' => array(
					'description' => __( 'The date the item was last updated.', 'wc-key-manager' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'embed' ),
					'readonly'    => true,
				),
			),
		);
	}

	/**
	 * Retrieves the query params for the items' collection.
	 *
	 * @since 1.0.0
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params           = parent::get_collection_params();
		$params['key_id'] = array(
			'description'       => __( 'Unique key identifier.', 'wc-key-manager' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
		);
		$params['status'] = array(
			'description'       => __( 'Activation status.', 'wc-key-manager' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		);

		return $params;
	}
}
