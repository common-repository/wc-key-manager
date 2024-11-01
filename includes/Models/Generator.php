<?php

namespace KeyManager\Models;

defined( 'ABSPATH' ) || exit;


/**
 * Generator model.
 *
 * @since 1.0.0
 * @package KeyManager\Models
 *
 * @property int      $id ID of the generator.
 * @property string   $name Name of the generator.
 * @property string   $pattern Pattern of the generator.
 * @property string   $charset Charset of the generator.
 * @property int      $valid_for Validity period of the generator.
 * @property string   $activation_limit Activation limit of the generator.
 * @property string   $status Generator status.
 * @property string   $created_at Creation date.
 * @property string   $updated_at Update date.
 *
 * @property-read Key $key Key relationship.
 */
class Generator extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'wckm_generators';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'name',
		'pattern',
		'charset',
		'valid_for',
		'activation_limit',
		'status',
	);

	/**
	 * The model's attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'pattern' => '####-####-####-####',
		'charset' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
		'status'  => 'active',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'name'             => 'string',
		'pattern'          => 'string',
		'charset'          => 'string',
		'valid_for'        => 'int',
		'activation_limit' => 'int',
		'status'           => 'string',
	);

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $has_timestamps = true;

	/**
	 * The searchable attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'name',
		'pattern',
		'charset',
		'status',
	);

	/**
	 * Get statues options.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'active'   => __( 'Active', 'wc-key-manager' ),
			'inactive' => __( 'Inactive', 'wc-key-manager' ),
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Accessors, Mutators & Relationships
	|--------------------------------------------------------------------------
	| This section includes methods for accessing, modifying, and assisting with
	| the model's properties.
	| - Getters: Retrieve property values.
	| - Setters: Update property values.
	| - Relationships: Define relationships between models.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set the status attribute.
	 *
	 * @param string $value Status value.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_status( $value ) {
		$this->attributes['status'] = in_array( $value, array_keys( self::get_statuses() ), true ) ? $value : 'active';
	}


	/*
	|--------------------------------------------------------------------------
	| CRUD Methods
	|--------------------------------------------------------------------------
	| This section contains methods for creating, reading, updating, and deleting
	| objects in the database.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Save the object to the database.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|static WP_Error on failure, or the object on success.
	 */
	public function save() {
		if ( empty( $this->name ) ) {
			return new \WP_Error( 'missing_name', __( 'The generator name is required.', 'wc-key-manager' ) );
		}
		if ( empty( $this->pattern ) ) {
			return new \WP_Error( 'missing_pattern', __( 'The generator pattern is required.', 'wc-key-manager' ) );
		}
		if ( empty( $this->charset ) ) {
			return new \WP_Error( 'missing_charset', __( 'The generator charset is required.', 'wc-key-manager' ) );
		}

		return parent::save();
	}

	/*
	|--------------------------------------------------------------------------
	| Helper Methods
	|--------------------------------------------------------------------------
	| This section contains utility methods that are not directly related to this
	| object but can be used to support its functionality.
	|--------------------------------------------------------------------------
	*/
	/**
	 * Get status label.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_status_label() {
		$statuses = self::get_statuses();

		return isset( $statuses[ $this->status ] ) ? $statuses[ $this->status ] : $this->status;
	}

	/**
	 * Get the key status html
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_status_html() {
		return sprintf( '<span class="wckm-key-status is--%s">%s</span>', esc_attr( $this->status ), esc_html( $this->get_status_label() ) );
	}
}
