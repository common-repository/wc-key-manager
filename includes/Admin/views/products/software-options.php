<?php
/**
 * Product software options.
 *
 * @since   1.0.0
 * @package KeyManager\Admin\views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit;
?>
	<div class="options_group">
		<?php
		$software = get_post_meta( $product->get_id(), '_wckm_enable_software', true );
		woocommerce_wp_radio(
			array(
				'id'            => '_wckm_enable_software',
				'label'         => __( 'Software', 'wc-key-manager' ),
				'wrapper_class' => 'wckm-inline-radio',
				'value'         => empty( $software ) ? 'no' : $software,
				'desc_tip'      => true,
				'options'       => array(
					'no'  => __( 'No', 'wc-key-manager' ),
					'yes' => __( 'Yes', 'wc-key-manager' ),
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_wckm_software_version',
				'label'             => __( 'Software Version', 'wc-key-manager' ),
				'placeholder'       => 'e.g. 1.0.0',
				'value'             => get_post_meta( $product->get_id(), '_wckm_software_version', true ),
				'wrapper_class'     => 'wckm_show_if__software',
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
			)
		);

		// Min PHP version.
		woocommerce_wp_text_input(
			array(
				'id'                => '_wckm_software_min_php_version',
				'label'             => __( 'Min PHP Version', 'wc-key-manager' ),
				'placeholder'       => 'e.g. 7.0',
				'value'             => get_post_meta( $product->get_id(), '_wckm_software_min_php_version', true ),
				'wrapper_class'     => 'wckm_show_if__software',
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
			)
		);

		// Min WP version.
		woocommerce_wp_text_input(
			array(
				'id'                => '_wckm_software_min_wp_version',
				'label'             => __( 'Min WP Version', 'wc-key-manager' ),
				'placeholder'       => 'e.g. 5.0',
				'value'             => get_post_meta( $product->get_id(), '_wckm_software_min_wp_version', true ),
				'wrapper_class'     => 'wckm_show_if__software',
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'            => '_wckm_software_file',
				'label'         => __( 'Software File', 'wc-key-manager' ),
				'placeholder'   => 'e.g. www.your-domain.com/files/software.zip',
				'wrapper_class' => 'wckm_show_if__software',
			)
		);


		woocommerce_wp_textarea_input(
			array(
				'id'            => '_wckm_software_changelog',
				'label'         => __( 'Changelog', 'wc-key-manager' ),
				'placeholder'   => 'e.g. Fixed bug with...',
				'value'         => get_post_meta( $product->get_id(), '_wckm_software_changelog', true ),
				'wrapper_class' => 'wckm_show_if__software',
				'style'         => 'min-height: 100px;height:auto;',
			)
		);


		/**
		 * Action hook to add more software options.
		 *
		 * @param WC_Product $product Product object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_product_software_options', $product );
		?>
	</div><!-- .options_group -->
<?php
