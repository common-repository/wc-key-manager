<?php

namespace KeyManager\API;

defined( 'ABSPATH' ) || exit;

/**
 * Controller class
 *
 * @since 1.0.0
 * @package KeyManager
 * @subpackage API
 */
abstract class Controller extends \WP_REST_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Returns the value of schema['properties']
	 *
	 * i.e. Schema fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_schema_properties() {

		$schema     = $this->get_item_schema();
		$properties = isset( $schema['properties'] ) ? $schema['properties'] : array();

		// For back-compat, include any field with an empty schema
		// because it won't be present in $this->get_item_schema().
		foreach ( $this->get_additional_fields() as $field_name => $field_options ) {
			if ( is_null( $field_options['schema'] ) ) {
				$properties[ $field_name ] = $field_options;
			}
		}

		return $properties;
	}

	/**
	 * Filters fields by context.
	 *
	 * @param array       $fields Array of fields.
	 * @param string|null $context view, edit or embed.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function filter_response_fields_by_context( $fields, $context ) {

		if ( empty( $context ) ) {
			return $fields;
		}

		foreach ( $fields as $name => $options ) {
			if ( ! empty( $options['context'] ) && ! in_array( $context, $options['context'], true ) ) {
				unset( $fields[ $name ] );
			}
		}

		return $fields;
	}

	/**
	 * Filters fields by an array of requested fields.
	 *
	 * @param array $fields Array of available fields.
	 * @param array $requested array of requested fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function filter_response_fields_by_array( $fields, $requested ) {

		// Trim off any whitespace from the list array.
		$requested = array_map( 'trim', $requested );

		// Always persist 'id', because it can be needed for add_additional_fields_to_object().
		if ( in_array( 'id', $fields, true ) ) {
			$requested[] = 'id';
		}

		// Get rid of duplicate fields.
		$requested = array_unique( $requested );

		// Return the list of all included fields which are available.
		return array_reduce(
			$requested,
			function ( $response_fields, $field ) use ( $fields ) {

				if ( in_array( $field, $fields, true ) ) {
					$response_fields[] = $field;

					return $response_fields;
				}

				// Check for nested fields if $field is not a direct match.
				$nested_fields = explode( '.', $field );

				// A nested field is included so long as its top-level property is
				// present in the schema.
				if ( in_array( $nested_fields[0], $fields, true ) ) {
					$response_fields[] = $field;
				}

				return $response_fields;
			},
			array()
		);
	}

	/**
	 * Gets an array of fields to be included on the response.
	 *
	 * Included fields are based on item schema and `_fields=` request argument.
	 * Copied from WordPress 5.3 to support old versions.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return array Fields to be included in the response.
	 */
	public function get_fields_for_response( $request ) {

		// Retrieve fields in the schema.
		$properties = $this->get_schema_properties();

		// Exclude fields that specify a different context than the request context.
		$properties = $this->filter_response_fields_by_context( $properties, $request['context'] );

		// We only need the field keys.
		$fields = array_keys( $properties );

		// Is the user filtering the response fields??
		if ( empty( $request['_fields'] ) ) {
			return $fields;
		}

		return $this->filter_response_fields_by_array( $fields, wp_parse_list( $request['_fields'] ) );
	}

	/**
	 * Limits an object to the requested fields.
	 *
	 * Included fields are based on the `_fields` request argument.
	 *
	 * @param array  $data Fields to include in the response.
	 * @param array  $fields Requested fields.
	 * @param string $prefix Prefix for the current field.
	 *
	 * @since 1.0.0
	 * @return array Fields to be included in the response.
	 */
	public function limit_object_to_requested_fields( $data, $fields, $prefix = '' ) {

		// Is the user filtering the response fields??
		if ( empty( $fields ) ) {
			return $data;
		}

		foreach ( $data as $key => $value ) {

			// Numeric arrays.
			if ( is_numeric( $key ) && is_array( $value ) ) {
				$data[ $key ] = $this->limit_object_to_requested_fields( $value, $fields, $prefix );
				continue;
			}

			// Generate a new prefix.
			$new_prefix = empty( $prefix ) ? $key : "$prefix.$key";

			// Check if it was requested.
			if ( ! empty( $key ) && ! $this->is_field_included( $new_prefix, $fields ) ) {
				unset( $data[ $key ] );
				continue;
			}

			if ( 'meta_data' !== $key && is_array( $value ) ) {
				$data[ $key ] = $this->limit_object_to_requested_fields( $value, $fields, $new_prefix );
			}
		}

		return $data;
	}

	/**
	 * Given an array of fields to include in a response, some of which may be
	 * `nested.fields`, determine whether the provided field should be included
	 * in the response body.
	 *
	 * Copied from WordPress 5.3 to support old versions.
	 *
	 * @param string $field A field to test for inclusion in the response body.
	 * @param array  $fields An array of string fields supported by the endpoint.
	 *
	 * @see   rest_is_field_included()
	 *
	 * @since 1.0.0
	 * @return bool Whether to include the field or not.
	 */
	public function is_field_included( $field, $fields ) {
		if ( in_array( $field, $fields, true ) ) {
			return true;
		}

		foreach ( $fields as $accepted_field ) {
			// Check to see if $field is the parent of any item in $fields.
			// A field "parent" should be accepted if "parent.child" is accepted.
			if ( strpos( $accepted_field, "$field." ) === 0 ) {
				return true;
			}
			// Conversely, if "parent" is accepted, all "parent.child" fields
			// should also be accepted.
			if ( strpos( $field, "$accepted_field." ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Only return writable props from schema.
	 *
	 * @param array $schema Schema.
	 *
	 * @return bool
	 */
	protected function filter_writable_props( $schema ) {
		return empty( $schema['readonly'] );
	}

	/**
	 * Convert date to RFC format
	 *
	 * @param string|null $date Date. Default null.
	 *
	 * @since 1.0.0
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date = null ) {
		// Use the date if passed.
		if ( ! empty( $date ) || '0000-00-00 00:00:00' !== $date ) {
			return mysql_to_rfc3339( $date );
		}

		return null;
	}

	/**
	 * Checks if a given request has access to read items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view items.', 'wc-key-manager' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create a key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create item.', 'wc-key-manager' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view item.', 'wc-key-manager' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update a key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update item.', 'wc-key-manager' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a account.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! current_user_can( WCKM_MANAGER_ROLE ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete item.', 'wc-key-manager' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Prepares a single item for create or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return array|\WP_Error Item object or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {
		$schema    = $this->get_item_schema();
		$prop_keys = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );
		$props     = array();
		// Handle all writable props.
		foreach ( $prop_keys as $prop_key ) {
			if ( isset( $request[ $prop_key ] ) ) {
				$props[ $prop_key ] = $request[ $prop_key ];
			}
		}
		return $props;
	}

	/**
	 * Retrieves the query params for the items collection.
	 *
	 * @since 1.1.2
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params = array(
			'context' => $this->get_context_param(),
			'page'    => array(
				'description'       => __( 'Current page of the collection.', 'wc-key-manager' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			),
			'limit'   => array(
				'description'       => __( 'Maximum number of items to be returned in result set.', 'wc-key-manager' ),
				'type'              => 'integer',
				'default'           => 20,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'search'  => array(
				'description'       => __( 'Limit results to those matching a string.', 'wc-key-manager' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'include' => array(
				'description'       => __( 'Limit result set to specific ids.', 'wc-key-manager' ),
				'type'              => 'array',
				'items'             => array( 'type' => 'integer' ),
				'default'           => array(),
				'sanitize_callback' => 'wp_parse_id_list',
			),
			'order'   => array(
				'description'       => __( 'Order sort attribute ascending or descending.', 'wc-key-manager' ),
				'type'              => 'string',
				'default'           => 'desc',
				'enum'              => array( 'asc', 'desc' ),
				'validate_callback' => 'rest_validate_request_arg',
			),
			'orderby' => array(
				'description'       => __( 'Sort collection by object attribute.', 'wc-key-manager' ),
				'type'              => 'string',
				'default'           => 'date_created',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	}
}
