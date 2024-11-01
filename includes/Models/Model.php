<?php

namespace KeyManager\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Model class.
 *
 * @since 1.0.0
 * @package KeyManager\Models
 */
abstract class Model extends \KeyManager\ByteKit\Models\Model {
	/**
	 * Get hook prefix. Default is the object type.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_hook_prefix() {
		return 'wc_key_manager_' . $this->get_object_type();
	}
}
