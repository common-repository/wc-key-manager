<?php

namespace KeyManager\ByteKit\Models\Relations;

use KeyManager\ByteKit\Models\Model;

/**
 * Has many relation class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package ByteKit/Models
 * @license MIT
 */
class HasMany extends HasOne {

	/**
	 * Add the constraints for the relation.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_constraints() {
		$this->set(
			array(
				$this->foreign_key             => $this->get_parent_key(),
				"{$this->foreign_key}__exists" => true,
				'limit'                        => 0,
			)
		);
	}

	/**
	 * Get the results of the relationship.
	 *
	 * @since 1.0.0
	 * @return Model[] The results.
	 */
	public function get_results() {
		return ! empty( $this->get_parent_key() )
			? $this->query->get_results()
			: array();
	}

	/**
	 * Insert a new instance of the related model.
	 *
	 * @param array $attributes Attributes to set on the related model.
	 *
	 * @return Model The related model instance.
	 */
	public function insert( $attributes = array() ) {
		$instance = $this->related->make( $attributes );
		return $this->save( $instance );
	}
}
