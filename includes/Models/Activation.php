<?php

namespace KeyManager\Models;

use KeyManager\ByteKit\Models\Relations\BelongsTo;

defined( 'ABSPATH' ) || exit;

/**
 * Activation model.
 *
 * @since 1.0.0
 * @package KeyManager\Models
 *
 * @property int      $id ID of the activation.
 * @property int      $key_id ID of the key.
 * @property string   $instance Activation instance.
 * @property string   $ip_address IP address of the activation.
 * @property string   $user_agent User agent.
 * @property string   $status Activation status.
 * @property string   $created_at Creation date.
 * @property string   $updated_at Update date.
 *
 * @property-read Key $key Key relationship.
 */
class Activation extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'wckm_activations';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'instance',
		'key_id',
		'ip_address',
		'user_agent',
		'status',
	);

	/**
	 * The model's attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'status' => 'active',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'instance'   => 'string',
		'key_id'     => 'integer',
		'ip_address' => 'string',
		'user_agent' => 'string',
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
		'instance',
		'ip_address',
	);

	/**
	 * Attributes that have transition effects when changed.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $transitionable = array(
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
	 * Set instance attribute.
	 *
	 * @param string $value Instance value.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_instance( $value ) {
		$this->attributes['instance'] = wckm_sanitize_instance( $value );
	}

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

	/**
	 * Key relationship.
	 *
	 * @since 1.0.0
	 * @return BelongsTo Key relationship.
	 */
	public function key() {
		return $this->belongs_to( Key::class, 'key_id' );
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
		// Token is required.
		if ( empty( $this->instance ) ) {
			return new \WP_Error( 'missing_required', __( 'An instance to identify the activation is required.', 'wc-key-manager' ) );
		}

		// Key ID is required.
		if ( empty( $this->key_id ) ) {
			return new \WP_Error( 'missing_required', __( 'Key ID is required.', 'wc-key-manager' ) );
		}

		// Same key id and token should not exist.
		$existing = $this->find(
			array(
				'key_id'   => $this->key_id,
				'instance' => $this->instance,
			)
		);

		if ( $existing && $existing->id !== $this->id && $existing->key_id === $this->key_id ) {
			return new \WP_Error( 'duplicate_activation', __( 'Activation with the same instance already exists.', 'wc-key-manager' ) );
		}

		$key = Key::find( $this->key_id );

		if ( $key ) {
			// if the status is not active then activate the key.
			if ( 'activated' !== $key->status ) {
				$key->status = 'activated';
				$key->save();
			}
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
