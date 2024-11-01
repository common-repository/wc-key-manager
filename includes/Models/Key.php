<?php

namespace KeyManager\Models;

use KeyManager\ByteKit\Models\Relations\HasMany;

defined( 'ABSPATH' ) || exit;

/**
 * Model class.
 *
 * @since 1.0.0
 * @package KeyManager\Models
 *
 * @property int               $id Key ID.
 * @property string            $code Key value.
 * @property string            $key Key value.
 * @property string            $truncated_key Truncated key value.
 * @property int               $product_id Product ID.
 * @property int               $order_id Order ID.
 * @property int               $order_item_id Order item ID.
 * @property int               $subscription_id Subscription ID.
 * @property int               $vendor_id Vendor ID.
 * @property int               $customer_id Customer ID.
 * @property string            $customer_email Customer email.
 * @property float             $price Per key price.
 * @property string            $source Key source.
 * @property string            $status Key status.
 * @property string            $uuid Key UUID.
 * @property int               $valid_for Key validity in days.
 * @property int               $activation_limit Activation limit.
 * @property int               $activation_count Activation count.
 * @property string            $ordered_at Date ordered.
 * @property string            $expires_at Date expires.
 * @property string            $created_at Date created.
 * @property string            $updated_at Date updated.
 *
 * @property-read Activation[] $activations Activations relationship.
 */
class Key extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'wckm_keys';

	/**
	 * Meta type declaration for the object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $meta_type = true;

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'code',
		'truncated_key',
		'product_id',
		'order_id',
		'order_item_id',
		'subscription_id',
		'vendor_id',
		'customer_id',
		'price',
		'source',
		'status',
		'uuid',
		'valid_for',
		'activation_limit',
		'ordered_at',
		'expires_at',
	);

	/**
	 * The model's attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'status' => 'available',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'code'             => 'string',
		'truncated_key'    => 'string',
		'product_id'       => 'integer',
		'order_id'         => 'integer',
		'order_item_id'    => 'integer',
		'subscription_id'  => 'integer',
		'vendor_id'        => 'integer',
		'customer_id'      => 'integer',
		'price'            => 'float',
		'source'           => 'string',
		'status'           => 'string',
		'uuid'             => 'string',
		'valid_for'        => 'integer',
		'activation_limit' => 'integer',
		'ordered_at'       => 'datetime',
		'expires_at'       => 'date',
	);

	/**
	 * The attributes that have aliases.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $aliases = array(
		'key'            => 'code',
		'truncated_code' => 'truncated_key',
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
		'code',
		'truncated_key',
		'product_id',
		'order_id',
		'subscription_id',
		'customer_id',
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
	 * Get statues.
	 *
	 * @since 1.0.0
	 * @return array Statuses.
	 */
	public static function get_statuses() {
		return apply_filters(
			'wc_key_manager_key_statuses',
			array(
				'available' => __( 'Available', 'wc-key-manager' ),
				'sold'      => __( 'Sold', 'wc-key-manager' ),
				'activated' => __( 'Activated', 'wc-key-manager' ),
				'expired'   => __( 'Expired', 'wc-key-manager' ),
				'cancelled' => __( 'Cancelled', 'wc-key-manager' ),
			)
		);
	}

	/**
	 * Get the key sources.
	 *
	 * @since 1.0.0
	 * @return array Key sources.
	 */
	public static function get_sources() {
		return apply_filters(
			'wc_key_manager_key_sources',
			array(
				'automatic' => __( 'Automatic', 'wc-key-manager' ),
				'preset'    => __( 'Preset', 'wc-key-manager' ),
			)
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
	 * Set the status.
	 *
	 * @param string $status Status.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_status( $status ) {
		$this->attributes['status'] = in_array( $status, array_keys( self::get_statuses() ), true ) ? $status : 'available';
	}

	/**
	 * Get status.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_status() {
		if ( $this->is_expired() ) {
			return 'expired';
		}

		return $this->attributes['status'];
	}

	/**
	 * Get activation count.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_activation_count() {
		return $this->activations()->set( 'status', 'active' )->get_count();
	}

	/**
	 * Activations relationship.
	 *
	 * @since 1.0.0
	 * @return HasMany|Activation[]
	 */
	public function activations() {
		return $this->has_many( Activation::class, 'key_id' );
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
		if ( empty( $this->code ) ) {
			return new \WP_Error( 'missing_required', __( 'Key is required. Please enter a key.', 'wc-key-manager' ) );
		}

		if ( empty( $this->product_id ) ) {
			return new \WP_Error( 'missing_required', __( 'Please select a associated product.', 'wc-key-manager' ) );
		}

		// Check duplicate key.
		if ( 'yes' !== get_option( 'wckm_duplicate_keys', 'no' ) ) {
			$existing_key = self::find( array( 'code' => $this->code ) );
			if ( $existing_key && $existing_key->id !== $this->id && $existing_key->code === $this->code ) {
				return new \WP_Error( 'duplicate_key', __( 'Duplicate key is not allowed. Please enter a unique key.', 'wc-key-manager' ) );
			}
		}

		// If treated key is not set, generate one.
		if ( empty( $this->truncated_key ) || $this->is_dirty( 'code' ) ) {
			$this->truncated_key = substr( $this->code, 0, 7 );
		}

		// valid for and date_expire can not have both values.
		if ( $this->expires_at && $this->valid_for ) {
			$this->valid_for = null;
		}

		// If uuid is not set, generate one.
		if ( empty( $this->uuid ) ) {
			$this->uuid = wp_generate_uuid4();
		}

		// If expired but status is not set, set it to expired.
		if ( $this->is_expired() && 'expired' !== $this->status ) {
			$this->status = 'expired';
		}

		return parent::save();
	}

	/**
	 * Delete the object from the database.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function delete() {
		// Delete all the activations.
		foreach ( $this->activations as $activation ) {
			$activation->delete();
		}

		return parent::delete();
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
	 * Render the key.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_key_html() {
		$html  = '<span class="wckm_key">';
		$html .= sprintf( '<span class="wckm_key__code" title="%s">%s</span>', esc_attr__( 'Click to reveal', 'wc-key-manager' ), esc_html( $this->code ) );
		$html .= sprintf( '<span class="wckm_key__copy" data-key="%s">%s</span>', esc_attr( $this->code ), esc_html__( 'Copy', 'wc-key-manager' ) );
		$html .= '</span>';

		return $html;
	}

	/**
	 * Get view key URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_view_key_url() {
		$account_page = wc_get_page_permalink( 'myaccount' );

		return wc_get_endpoint_url( 'view-key', $this->uuid, $account_page );
	}

	/**
	 * Get the product.
	 *
	 * @since 1.0.0
	 * @return \WC_Product|false Product object or false if not found.
	 */
	public function get_product() {
		return ! empty( $this->product_id ) ? wc_get_product( $this->product_id ) : false;
	}

	/**
	 * Get product name.
	 *
	 * @since 1.0.0
	 * @return string Product name.
	 */
	public function get_product_name() {
		$product = $this->product;
		if ( ! $product ) {
			return '';
		}

		return $product->get_name();
	}

	/**
	 * Get parent product ID.
	 *
	 * @since 1.0.0
	 * @return int|false Parent product ID or false if not found.
	 */
	public function get_parent_product_id() {
		$product = $this->product;

		if ( ! $product ) {
			return false;
		}

		return $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
	}

	/**
	 * Get product URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_product_url() {
		$product = $this->product;
		if ( ! $product ) {
			return '';
		}

		return get_permalink( $this->get_parent_product_id() );
	}

	/**
	 * Get the customer.
	 *
	 * @since 1.0.0
	 * @return \WP_User|false Customer object or false if not found.
	 */
	public function get_customer() {
		return ! empty( $this->customer_id ) ? get_user_by( 'id', $this->customer_id ) : false;
	}

	/**
	 * Get customer email.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_customer_email() {
		$order = $this->get_order();
		if ( $order ) {
			return $order->get_billing_email();
		}

		return '';
	}

	/**
	 * Get the order.
	 *
	 * @since 1.0.0
	 * @return \WC_Order|false Order object or false if not found.
	 */
	public function get_order() {
		return ! empty( $this->order_id ) ? wc_get_order( $this->order_id ) : false;
	}

	/**
	 * Get activations html
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_activations_html() {
		$count = $this->activation_count;
		$limit = empty( $this->activation_limit ) ? '&infin;' : $this->activation_limit;

		return sprintf( '<span class="wckm-activations-count">%d</span> / <span class="wckm-activations-limit">%s</span>', $count, $limit );
	}

	/**
	 * Get the status label.
	 *
	 * @since 1.0.0
	 * @return string Status label.
	 */
	public function get_status_label() {
		$statuses = self::get_statuses();
		$status   = $this->status;

		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
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

	/**
	 * Get expires.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_expires() {
		$date = $this->expires_at;
		if ( empty( $date ) && ! empty( $this->valid_for ) && ! empty( $this->ordered_at ) ) {
			$date = new \DateTime( $this->ordered_at );
			$date->modify( '+' . $this->valid_for . ' days' );
			$date = $date->format( 'Y-m-d H:i:s' );
		}

		return $date;
	}

	/**
	 * Get the expires html.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_expires_html() {
		if ( ! empty( $this->get_expires() ) ) {
			return sprintf( '<time datetime="%s">%s</time>', esc_attr( $this->get_expires() ), esc_html( wp_date( get_option( 'date_format' ), strtotime( $this->get_expires() ) ) ) );
		}
		if ( ! empty( $this->valid_for ) ) {
			// translators: %d: number of days.
			return sprintf( _nx( '%d day <small>After Purchase</small>', '%d days <small>After Purchase</small>', $this->valid_for, 'valid for days', 'wc-key-manager' ), $this->valid_for );
		}

		return esc_html__( 'Never', 'wc-key-manager' );
	}

	/**
	 * Determine if the key reached the activation limit.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_at_limit() {
		return ! empty( $this->activation_limit ) && $this->activation_count >= $this->activation_limit;
	}

	/**
	 * Determine if the key expired.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_expired() {
		$now     = wp_date( 'U' );
		$expires = $this->get_expires();

		return 'expired' === $this->attributes['status'] || ( $expires && $now > strtotime( $expires ) );
	}

	/**
	 * Check if the user can use this key.
	 *
	 * @param string $email Email address. If email address is provided, it will check if the key is valid for the email.
	 * @param int    $product_id Product ID. If product ID is provided, it will check if the key is valid for the product.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_valid( $email = false, $product_id = false ) {
		return in_array( $this->status, array( 'sold', 'active', 'expired' ), true )
				&& $this->order_id > 0 && ( ! $email || ( $this->get_order() && $this->get_customer_email() === $email ) )
				&& ( ! $product_id || $this->product_id === $product_id );
	}

	/**
	 * Get formatted attributes.
	 *
	 * @param string $context The context.
	 *
	 * @return array Formatted attributes.
	 */
	public function get_formatted_attributes( $context = 'myaccount' ) {
		$attributes = array(
			array(
				'key'      => 'product',
				'label'    => __( 'Product', 'wc-key-manager' ),
				'value'    => $this->product_id,
				'render'   => sprintf( '<a href="%s">%s</a>', esc_url( $this->get_product_url() ), $this->get_product_name() ),
				'priority' => 10,
			),
			array(
				'key'      => 'key',
				'label'    => __( 'Key', 'wc-key-manager' ),
				'value'    => $this->code,
				'render'   => $this->get_key_html(),
				'priority' => 10,
			),
			array(
				'key'      => 'expires',
				'label'    => __( 'Expires', 'wc-key-manager' ),
				'value'    => $this->valid_for,
				'render'   => $this->get_expires_html(),
				'priority' => 20,
			),
			array(
				'key'      => 'activations',
				'label'    => __( 'Activations', 'wc-key-manager' ),
				'value'    => $this->activation_limit . '/' . $this->activation_count,
				'render'   => $this->get_activations_html(),
				'priority' => 30,
			),
		);

		/**
		 * Filter to allow adding more key attributes.
		 *
		 * @param array  $attributes The key attributes.
		 * @param Key    $key The key object.
		 * @param string $context The context.
		 */
		$attributes = apply_filters( 'wc_key_manager_formatted_key_attributes', $attributes, $this, $context );

		usort(
			$attributes,
			function ( $a, $b ) {
				$a = isset( $a['priority'] ) ? $a['priority'] : 10;
				$b = isset( $b['priority'] ) ? $b['priority'] : 10;

				return $a - $b;
			}
		);

		foreach ( $attributes as $i => $attribute ) {
			$content = isset( $attribute['value'] ) ? $attribute['value'] : '';

			// if render is set, check if its callable otherwise use the content.
			if ( ! empty( $attribute['render'] ) ) {
				$content = is_callable( $attribute['render'] ) ? call_user_func( $attribute['render'], $content ) : $attribute['render'];
			}

			/**
			 * Filter to allow modifying the key attribute label.
			 *
			 * @param string $label The key attribute label.
			 * @param array  $attribute The key attribute.
			 * @param Key    $key The key object.
			 * @param string $context The context.
			 */
			$attributes[ $i ]['label'] = apply_filters( 'wc_key_manager_key_attribute_label_' . $attribute['key'], $attribute['label'], $attribute, $this, $context );

			/**
			 * Filter to allow modifying the key attribute content.
			 *
			 * @param string $content The key attribute content.
			 * @param array  $attribute The key attribute.
			 * @param Key    $key The key object.
			 * @param string $context The context.
			 */
			$attributes[ $i ]['content'] = apply_filters( 'wc_key_manager_key_attribute_content_' . $attribute['key'], $content, $attribute, $this, $context );
		}

		return $attributes;
	}

	/*
	|--------------------------------------------------------------------------
	| Order related methods
	|--------------------------------------------------------------------------
	| This section contains methods that are related making
	| changes to the order.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set sold status to the key.
	 *
	 * @param int $order_id Order ID.
	 * @param int $order_item_id Order item ID.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function add_order( $order_id, $order_item_id = null ) {
		$order = wc_get_order( $order_id );
		// If order is not found return error.
		if ( ! $order ) {
			return new \WP_Error( 'order_not_found', __( 'Order not found.', 'wc-key-manager' ) );
		}
		// If order item is not provided, find it.
		if ( ! $order_item_id ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				if ( 'line_item' !== $item['type'] || ! $item instanceof \WC_Order_Item_Product ) {
					continue;
				}
				$product = $item->get_product();
				if ( $product && $product->get_id() === $this->product_id ) {
					$order_item_id = $item_id;
					break;
				}
			}
		}

		// If order item is not found return error.
		if ( ! $order_item_id ) {
			return new \WP_Error( 'order_item_not_found', __( 'Order item not found.', 'wc-key-manager' ) );
		}

		$order_item = $order->get_item( $order_item_id );
		$product    = wc_get_product( $order_item->get_product_id() );

		// If product is not found return error.
		if ( ! $product ) {
			return new \WP_Error( 'product_not_found', __( 'Product not found.', 'wc-key-manager' ) );
		}

		if ( ! wckm_is_keyed_product( $product ) ) {
			return new \WP_Error( 'not_keyed_product', __( 'Product is not a keyed product.', 'wc-key-manager' ) );
		}

		if ( $order_item->get_meta( '_wckm_delivery_qty', true ) ) {
			$delivery_qty = max( 1, (int) $order_item->get_meta( '_wckm_delivery_qty', true ) );
		} else {
			$delivery_qty = max( 1, (int) wckm_get_delivery_quantity( $product, 1 ) );
			$order_item->add_meta_data( '_wckm_order_item', 'yes', true );
			$order_item->add_meta_data( '_wckm_delivery_qty', $delivery_qty, true );
			$order_item->save();
		}

		$item_price    = $order_item->get_total();
		$per_key_price = $delivery_qty > 0 && $item_price > 0 ? $item_price / $delivery_qty : $item_price;
		$order_date    = $order->get_date_created() ? $order->get_date_created()->format( 'Y-m-d H:i:s' ) : current_time( 'mysql' );
		WCKM()->log( $order_date );
		$this->set( 'status', 'sold' );
		$this->set( 'order_id', $order_id );
		$this->set( 'order_item_id', $order_item_id );
		$this->set( 'ordered_at', $order_date );
		$this->set( 'price', $per_key_price );
		$this->set( 'customer_id', $order->get_customer_id() );

		$return = $this->save();

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		// if the order does not have the _wckm_order flag, add it.
		if ( ! $order->get_meta( '_wckm_order' ) ) {
			$order->update_meta_data( '_wckm_order', 'yes' );
			$order->save_meta_data();
		}

		/**
		 * Action hook triggered after the key is added to the order.
		 *
		 * @param Key $key Key object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_key_added_to_order', $this );

		return $return;
	}

	/**
	 * Remove order from the key.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function remove_order() {
		if ( $this->subscription_id > 0 ) {
			$this->set( 'expires_at', null );
		}

		$this->set( 'order_id', 0 );
		$this->set( 'order_item_id', 0 );
		$this->set( 'subscription_id', 0 );
		$this->set( 'customer_id', 0 );
		$this->set( 'price', 0 );
		$this->set( 'activation_count', 0 );
		$this->set( 'ordered_at', '' );
		$this->set( 'status', 'available' );

		// remove all the activations.
		foreach ( $this->activations as $activation ) {
			$activation->delete();
		}

		return $this->save();
	}

	/*
	|--------------------------------------------------------------------------
	| Activation related methods
	|--------------------------------------------------------------------------
	| This section contains methods that are related to the key activations.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get activation.
	 *
	 * @param string $instance Instance.
	 *
	 * @since 1.0.0
	 * @return Activation|false Activation object or false if not found.
	 */
	public function get_activation( $instance ) {
		// If instance is not provided, bail.
		if ( empty( $instance ) ) {
			return false;
		}

		$instance = wckm_sanitize_instance( $instance );

		return $this->activations()->set( 'instance', $instance )->set( 'instance__exists', true )->get_result();
	}

	/**
	 * Activate the key.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 * @return Activation|\WP_Error Activation object on success, WP_Error on failure.
	 */
	public function activate( $request ) {
		$defaults = array(
			'instance'   => '',
			'email'      => '',
			'product_id' => '',
			'ip_address' => '',
			'user_agent' => '',
		);

		$request = wp_parse_args( $request, $defaults );

		/**
		 * Filter the request data.
		 *
		 * @param array $request Request data.
		 *
		 * @since 1.0.0
		 */
		$request = apply_filters( 'wc_key_manager_activate_key_args', $request );

		$instance   = isset( $request['instance'] ) ? wckm_sanitize_instance( sanitize_text_field( wp_unslash( $request['instance'] ) ) ) : wckm_get_default_instance();
		$ip_address = isset( $request['ip_address'] ) ? sanitize_text_field( wp_unslash( $request['ip_address'] ) ) : '';
		$user_agent = isset( $request['user_agent'] ) ? sanitize_text_field( wp_unslash( $request['user_agent'] ) ) : '';
		$product_id = ! empty( $request['product_id'] ) ? absint( $request['product_id'] ) : 0;
		$email      = ! empty( $request['email'] ) ? sanitize_email( $request['email'] ) : '';

		// If product id is provided, we will check if the key is valid for the product.
		if ( ! empty( $product_id ) && $this->product_id !== $product_id ) {
			return new \WP_Error( 'invalid_product_id', __( 'The key could not be activated. The key is not valid for the product.', 'wc-key-manager' ) );
		}

		// If email is provided, we will check if the key is valid for the email.
		if ( ! empty( $email ) && $this->customer_email !== $email ) {
			return new \WP_Error( 'invalid_email', __( 'The key could not be activated. The key is not valid for the email.', 'wc-key-manager' ) );
		}

		// If expired.
		if ( $this->is_expired() ) {
			return new \WP_Error( 'expired', __( 'The key could not be activated. The key has expired.', 'wc-key-manager' ) );
		}

		// If instance is missing.
		if ( empty( $instance ) ) {
			return new \WP_Error( 'missing_instance', __( 'The key could not be activated. The instance is missing.', 'wc-key-manager' ) );
		}

		// no activations left.
		if ( $this->is_at_limit() && ! $this->activations()->set( 'instance', $instance )->set( 'status', 'active' )->get_result() ) {
			return new \WP_Error( 'no_activations_left', __( 'The key could not be activated. The activation limit has been reached.', 'wc-key-manager' ) );
		}

		// If we  have the activation, if it is active, return it otherwise update it.
		$activation = $this->get_activation( $instance );
		if ( $activation && 'active' === $activation->status ) {
			return $activation;
		}

		/**
		 * Filter action before the key is activated.
		 *
		 * @param Key   $key Key object.
		 * @param array $request Request data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_pre_activate_key', $this, $request );

		$activation = $this->activations()->insert(
			apply_filters(
			/**
			 * Filter the activation data.
			 *
			 * @param array $data Activation data.
			 * @param Key   $key Key object.
			 * @param array $request Request data.
			 *
			 * @since 1.0.0
			 */
				'wc_key_manager_activation_data',
				array(
					'id'         => $activation ? $activation->id : null,
					'instance'   => $instance,
					'ip_address' => $ip_address,
					'user_agent' => $user_agent,
					'status'     => 'active',
				),
				$this,
				$request
			)
		);

		// if the activation is not created, return error.
		if ( is_wp_error( $activation ) ) {
			return $activation;
		}

		/**
		 * Filter action after the key is activated.
		 *
		 * @param Activation $activation Activation object.
		 * @param Key        $key Key object.
		 * @param array      $request Request data.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		do_action( 'wc_key_manager_activated_key', $activation, $this, $request );

		return $activation;
	}

	/**
	 * Deactivate the key.
	 *
	 * @param array $request Request data.
	 *
	 * @since 1.0.0
	 * @return Activation|\WP_Error True on success, WP_Error on failure.
	 */
	public function deactivate( $request ) {
		$defaults = array(
			'instance' => '',
		);

		$request = wp_parse_args( $request, $defaults );

		/**
		 * Filter the request data.
		 *
		 * @param array $request Request data.
		 *
		 * @since 1.0.0
		 */
		$request = apply_filters( 'wc_key_manager_deactivate_key_args', $request );

		$instance = isset( $request['instance'] ) ? wckm_sanitize_instance( sanitize_text_field( wp_unslash( $request['instance'] ) ) ) : wckm_get_default_instance();

		// If instance is not provided, we can not deactivate the key.
		if ( empty( $instance ) ) {
			return new \WP_Error( 'missing_instance', __( 'The key could not be deactivated. The instance is missing.', 'wc-key-manager' ) );
		}

		// If expired.
		if ( $this->is_expired() ) {
			return new \WP_Error( 'expired', __( 'The key could not be deactivated. The key has expired.', 'wc-key-manager' ) );
		}

		$activation = $this->get_activation( $instance );
		if ( empty( $activation ) ) {
			return new \WP_Error( 'invalid_activation', __( 'The key could not be deactivated. The activation is not found.', 'wc-key-manager' ) );
		}

		// If the activation is already inactive, return true.
		if ( 'inactive' === $activation->status ) {
			return $activation;
		}

		/**
		 * Filter action before the key is deactivated.
		 *
		 * @param Activation $activation Activation object.
		 * @param Key        $key Key object.
		 * @param array      $request Request data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_pre_deactivate_key', $activation, $this, $request );

		$activation->set( 'status', 'inactive' );
		$return = $activation->save();

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		/**
		 * Filter action after the key is deactivated.
		 *
		 * @param Activation $activation Activation object.
		 * @param Key        $key Key object.
		 * @param array      $request Request data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_deactivated_key', $activation, $this, $request );

		return $activation;
	}
}
