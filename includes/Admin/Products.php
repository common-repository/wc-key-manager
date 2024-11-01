<?php

namespace KeyManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Products class.
 *
 * @since 1.0.0
 */
class Products {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add a column to the products list.
		// add bulk edit fields.
		add_action( 'woocommerce_product_bulk_edit_end', array( __CLASS__, 'bulk_edit_fields' ) );
		add_action( 'woocommerce_product_bulk_edit_save', array( __CLASS__, 'save_bulk_edit_fields' ) );
		add_filter( 'manage_product_posts_columns', array( __CLASS__, 'add_product_columns' ), 10, 1 );
		add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'add_product_columns_content' ), 10, 2 );

		add_action( 'product_type_options', array( __CLASS__, 'product_type_options' ) );
		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tabs' ), 1 );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_data_panels' ) );
		add_action( 'wc_key_manager_product_options', array( __CLASS__, 'render_source_options' ) );
		add_action( 'wc_key_manager_product_options', array( __CLASS__, 'render_key_options' ) );
		add_action( 'wc_key_manager_product_options', array( __CLASS__, 'render_software_options' ) );
		// Save product options.
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_options' ) );
	}

	/**
	 * Add bulk edit fields.
	 *
	 * @since 1.0.0
	 */
	public static function bulk_edit_fields() {
		?>
		<label>
			<span class="title"><?php esc_html_e( 'Keys', 'wc-key-manager' ); ?></span>
			<span class="input-text-wrap">
				<select class="wckm_bulk_edit" name="_wckm_change_keyed">
					<option value=""><?php esc_html_e( '— No change —', 'wc-key-manager' ); ?></option>
					<option value="yes"><?php esc_html_e( 'Yes', 'wc-key-manager' ); ?></option>
					<option value="no"><?php esc_html_e( 'No', 'wc-key-manager' ); ?></option>
				</select>
			</span>
		</label>
		<label>
			<span class="title"><?php esc_html_e( 'Key Source', 'wc-key-manager' ); ?></span>
			<span class="input-text-wrap">
				<select class="wckm_bulk_edit" name="_wckm_change_key_source">
					<option value=""><?php esc_html_e( '— No change —', 'wc-key-manager' ); ?></option>
					<option value="automatic"><?php esc_html_e( 'Automatic', 'wc-key-manager' ); ?></option>
					<option value="preset"><?php esc_html_e( 'Preset', 'wc-key-manager' ); ?></option>
				</select>
			</span>
		</label>
		<?php
	}

	/**
	 * Save bulk edit fields.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 */
	public static function save_bulk_edit_fields( $product ) {
		wp_verify_nonce( '_wpnonce' );
		$change_keyed      = isset( $_REQUEST['_wckm_change_keyed'] ) ? sanitize_key( $_REQUEST['_wckm_change_keyed'] ) : '';
		$change_key_source = isset( $_REQUEST['_wckm_change_key_source'] ) ? sanitize_key( $_REQUEST['_wckm_change_key_source'] ) : '';

		if ( ! empty( $change_keyed ) ) {
			update_post_meta( $product->get_id(), '_wckm_keyed', $change_keyed );
		}
		if ( ! empty( $change_key_source ) ) {
			update_post_meta( $product->get_id(), '_wckm_key_source', $change_key_source );
		}
	}

	/**
	 * Add a column to the products list.
	 *
	 * @param array $columns Default columns.
	 *
	 * @since 1.0.0
	 * @return array Modified columns.
	 */
	public static function add_product_columns( $columns ) {
		$stock_key = array_search( 'is_in_stock', array_keys( $columns ), true );
		if ( false !== $stock_key ) {
			$columns = array_slice( $columns, 0, $stock_key + 1, true ) + array( 'wckm_status' => __( 'Keys', 'wc-key-manager' ) ) + array_slice( $columns, $stock_key + 1, null, true );
		} else {
			$columns['wckm_status'] = __( 'Keys', 'wc-key-manager' );
		}

		return $columns;
	}

	/**
	 * Add content to the new column.
	 *
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_product_columns_content( $column, $post_id ) {
		if ( 'wckm_status' === $column ) {
			if ( ! wckm_is_keyed_product( $post_id ) ) {
				echo '&mdash;';

				return;
			}
			echo '<span class="dashicons dashicons-yes tips" data-tip="' . esc_attr__( 'keyed product', 'wc-key-manager' ) . '" style="color: #7ad03a;"></span>';
		}
	}

	/**
	 * Add 'Key' option to products.
	 *
	 * @param array $options Default options.
	 *
	 * @since 1.0.0
	 * @return array Modified options.
	 */
	public static function product_type_options( $options = array() ) {
		$options['wckm_keyed'] = apply_filters(
			'wc_key_manager_product_type_option',
			array(
				'id'            => '_wckm_keyed',
				'wrapper_class' => 'show_if_simple hide_if_variable hide_if_subscription hide_if_variable-subscription hide_if_grouped hide_if_external',
				'label'         => __( 'Keys', 'wc-key-manager' ),
				'description'   => __( 'Sell keys for this product.', 'wc-key-manager' ),
				'default'       => 'no',
			)
		);

		return $options;
	}

	/**
	 * Add Key related tabs.
	 *
	 * @param array $tabs Default tabs.
	 *
	 * @since 1.0.0
	 * @return array Modified tabs.
	 */
	public static function product_data_tabs( $tabs ) {
		// key options.
		$tabs['wckm_key_manager'] = array(
			'label'    => __( 'Key Manager', 'wc-key-manager' ),
			'target'   => 'wckm_product_options_data',
			'class'    => array( 'show_if__wckm' ),
			'priority' => 10,
		);

		return $tabs;
	}

	/**
	 * Product write panel.
	 *
	 * @since 1.0.0
	 */
	public static function product_data_panels() {
		global $post;
		$product = wc_get_product( $post->ID );

		include __DIR__ . '/views/products/product-options.php';
	}

	/**
	 * Product source options.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 */
	public static function render_source_options( $product ) {
		// if product is variable, do not show the key options.
		if ( strpos( $product->get_type(), 'variable' ) !== false ) {
			return;
		}
		include __DIR__ . '/views/products/source-options.php';
	}

	/**
	 * Product key options.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 */
	public static function render_key_options( $product ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		// if product is variable, do not show the key options.
		if ( strpos( $product->get_type(), 'variable' ) !== false ) {
			return;
		}
		include __DIR__ . '/views/products/key-options.php';
	}

	/**
	 * Product software options.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 */
	public static function render_software_options( $product ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		if ( 'yes' !== get_option( 'wckm_software_license', 'yes' ) ) {
			return;
		}
		include __DIR__ . '/views/products/software-options.php';
	}

	/**
	 * Save product options.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function save_product_options( $post_id ) {
		$product            = wc_get_product( $post_id );
		$is_keyed           = isset( $_POST['_wckm_keyed'] ) ? 'yes' : 'no';
		$key_source         = isset( $_POST['_wckm_key_source'] ) ? sanitize_key( $_POST['_wckm_key_source'] ) : 'automatic';
		$generator_id       = isset( $_POST['_wckm_generator_id'] ) ? absint( $_POST['_wckm_generator_id'] ) : 0;
		$sequential         = isset( $_POST['_wckm_is_sequential'] ) ? 'yes' : 'no';
		$delivery_qty       = isset( $_POST['_wckm_delivery_qty'] ) ? absint( $_POST['_wckm_delivery_qty'] ) : 1;
		$is_software        = isset( $_POST['_wckm_enable_software'] ) ? sanitize_key( $_POST['_wckm_enable_software'] ) : 'no';
		$software_version   = isset( $_POST['_wckm_software_version'] ) ? sanitize_text_field( wp_unslash( $_POST['_wckm_software_version'] ) ) : '';
		$min_php_version    = isset( $_POST['_wckm_software_min_php_version'] ) ? sanitize_text_field( wp_unslash( $_POST['_wckm_software_min_php_version'] ) ) : '';
		$min_wp_version     = isset( $_POST['_wckm_software_min_wp_version'] ) ? sanitize_text_field( wp_unslash( $_POST['_wckm_software_min_wp_version'] ) ) : '';
		$software_file      = isset( $_POST['_wckm_software_file'] ) ? esc_url_raw( wp_unslash( $_POST['_wckm_software_file'] ) ) : '';
		$software_changelog = isset( $_POST['_wckm_software_changelog'] ) ? sanitize_textarea_field( wp_unslash( $_POST['_wckm_software_changelog'] ) ) : '';

		update_post_meta( $post_id, '_wckm_keyed', $is_keyed );
		update_post_meta( $post_id, '_wckm_key_source', $key_source );
		update_post_meta( $post_id, '_wckm_generator_id', $generator_id );
		update_post_meta( $post_id, '_wckm_is_sequential', $sequential );
		update_post_meta( $post_id, '_wckm_delivery_qty', $delivery_qty );

		update_post_meta( $post_id, '_wckm_enable_software', $is_software );
		update_post_meta( $post_id, '_wckm_software_version', $software_version );
		update_post_meta( $post_id, '_wckm_software_min_php_version', $min_php_version );
		update_post_meta( $post_id, '_wckm_software_min_wp_version', $min_wp_version );
		update_post_meta( $post_id, '_wckm_software_file', $software_file );
		update_post_meta( $post_id, '_wckm_software_changelog', $software_changelog );

		// if this is a variable product, if keyed, add the key options to the variations otherwise remove them.
		if ( $product->is_type( 'variable' ) ) {
			$variations = $product->get_children();
			foreach ( $variations as $var_id ) {
				if ( 'yes' === $is_keyed ) {
					update_post_meta( $var_id, '_wckm_keyed', 'yes' );
				} else {
					delete_post_meta( $var_id, '_wckm_keyed' );
				}
			}
		}

		/**
		 * Action hook to save more product options.
		 *
		 * @param int   $post_id The post ID.
		 * @param array $postdata The post data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_process_product_meta', $post_id, $_POST );
	}
}
