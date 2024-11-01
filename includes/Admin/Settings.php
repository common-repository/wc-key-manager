<?php

namespace KeyManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class.
 *
 * @since 1.0.0
 * @package KeyManager\Admin
 */
class Settings extends \KeyManager\ByteKit\Admin\Settings {

	/**
	 * Settings constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
		add_filter( 'wc_key_manager_advanced_settings', array( __CLASS__, 'add_api_settings' ), 20 );
		add_filter( 'wc_key_manager_settings_tabs', array( __CLASS__, 'add_features_tab' ), PHP_INT_MAX, 1 );
	}


	/**
	 * Get settings tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_tabs() {
		return apply_filters(
			'wc_key_manager_settings_tabs',
			array(
				'general'  => __( 'General', 'wc-key-manager' ),
				'advanced' => __( 'Advanced', 'wc-key-manager' ),
				'misc'     => __( 'Misc', 'wc-key-manager' ),
				'emails'   => __( 'Emails', 'wc-key-manager' ),
			)
		);
	}


	/**
	 * Add features tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function add_features_tab( $tabs ) {
		$tabs['features'] = __( '🔥 Features', 'wc-key-manager' );
		return $tabs;
	}

	/**
	 * Add API settings.
	 *
	 * @param array $settings Settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function add_api_settings( $settings ) {
		return array_merge(
			$settings,
			array(
				array(
					'id'    => 'wckm_api_settings',
					'title' => __( 'API Settings', 'wc-key-manager' ),
					'type'  => 'title',
					'desc'  => __( 'Configure API related settings.', 'wc-key-manager' ),
				),
				array(
					'id'       => 'wckm_enable_rest_api',
					'title'    => __( 'Enable REST API', 'wc-key-manager' ),
					'desc'     => __( 'Enable the REST API Endpoints.', 'wc-key-manager' ),
					'desc_tip' => __( 'Enable creating, updating, and deleting keys using the REST API.', 'wc-key-manager' ),
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'id'       => 'wckm_enable_software_api',
					'title'    => __( 'Software API', 'wc-key-manager' ),
					'desc'     => __( 'Enable the Software API', 'wc-key-manager' ),
					'desc_tip' => __( 'This will enable using "?wckm-api={action}" endpoint for performing software licensing operations.', 'wc-key-manager' ),
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wckm_api_settings',
				),
			)
		);
	}

	/**
	 * Get settings.
	 *
	 * @param string $tab Tab name.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings( $tab ) {
		$settings = array();
		switch ( $tab ) {
			case 'general':
				$settings = array(
					array(
						'title' => __( 'Key Settings', 'wc-key-manager' ),
						'type'  => 'title',
						'desc'  => __( 'Configure the key related settings.', 'wc-key-manager' ),
						'id'    => 'wckm_key_settings',
					),
					array(
						'id'       => 'wckm_recycle_keys',
						'title'    => __( 'Recycle Keys', 'wc-key-manager' ),
						'desc'     => __( 'Automatically recover keys when an order is cancelled, refunded, or fails.', 'wc-key-manager' ),
						'desc_tip' => __( 'Enabling this option will allow keys from cancelled, refunded, or failed orders to be reused for new orders.', 'wc-key-manager' ),
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'id'       => 'wckm_duplicate_keys',
						'title'    => __( 'Allow Duplicate Keys', 'wc-key-manager' ),
						'desc'     => __( 'Enable creating duplicate keys [Not Recommended].', 'wc-key-manager' ),
						'desc_tip' => __( 'By default, creating duplicate keys is disabled. Enabling this option will allow the generation of duplicate keys. This is generally not recommended as it can cause issues with software licensing.', 'wc-key-manager' ),
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wckm_key_settings',
					),
					array(
						'title' => __( 'Delivery Settings', 'wc-key-manager' ),
						'type'  => 'title',
						'desc'  => __( 'Configure how & when keys are delivered to customers.', 'wc-key-manager' ),
						'id'    => 'wckm_delivery_settings',
					),
					array(
						'id'       => 'wckm_automatic_delivery',
						'title'    => __( 'Auto Key Delivery', 'wc-key-manager' ),
						'desc'     => __( 'Send the key to the customer automatically after payment.', 'wc-key-manager' ),
						'desc_tip' => __( 'This option will send the key to the customer once the payment is completed. If disabled, you will need to deliver the key manually.', 'wc-key-manager' ),
						'default'  => 'yes',
						'type'     => 'checkbox',
					),
					array(
						'id'       => 'wckm_proc_key_delivery',
						'title'    => __( 'Deliver on Processing', 'wc-key-manager' ),
						'desc'     => __( 'Deliver the key when the order status is processing.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will deliver the key when the order status is processing. If disabled, the key will be delivered when the order status is completed.', 'wc-key-manager' ),
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'id'            => 'wckm_hide_order_details',
						'title'         => __( 'Hide Keys On', 'wc-key-manager' ),
						'desc'          => __( 'Order details page. If checked, keys will not be shown on the order details page.', 'wc-key-manager' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'id'            => 'wckm_hide_keys_from_processing_email',
						'desc'          => __( 'Order processing email notifications. If checked, keys will not be shown in order processing email notifications.', 'wc-key-manager' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => '',
					),
					array(
						'id'            => 'wckm_hide_keys_from_completed_email',
						'desc'          => __( 'Order completed email notifications. If checked, keys will not be shown in order completed email notifications.', 'wc-key-manager' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'end',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wckm_delivery_settings',
					),

					array(
						'id'    => 'wckm_product',
						'title' => __( 'Product Settings', 'wc-key-manager' ),
						'type'  => 'title',
						'desc'  => __( 'Configure product related settings.', 'wc-key-manager' ),
					),
					array(
						'id'       => 'wckm_manage_stock',
						'title'    => __( 'Stock Management', 'wc-key-manager' ),
						'desc'     => __( 'Enable automatic stock management for WooCommerce products.', 'wc-key-manager' ),
						'desc_tip' => wp_kses_post( __( 'To use this function, you need to enable the following settings at the product level:<br>1: WooCommerce → Settings → Products → Inventory management<br>2: Product → Key Manager → Key Source → Preset', 'wc-key-manager' ) ),
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'id'       => 'wckm_software_license',
						'title'    => __( 'Software Licensing', 'wc-key-manager' ),
						'desc'     => __( 'Enable software licensing for WooCommerce products.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will enable software licensing for the product.', 'wc-key-manager' ),
						'default'  => 'yes',
						'type'     => 'checkbox',
					),
					array(
						'id'       => 'wckm_disable_oos_keys',
						'title'    => __( 'Out of Stock Keys', 'wc-key-manager' ),
						'desc'     => __( 'Disable selling keys when preset key is not available.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will disable selling keys when the preset key is not available.', 'wc-key-manager' ),
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wckm_product',
					),
					array(
						'id'    => 'wckm_my_account',
						'title' => __( 'My Account Settings', 'wc-key-manager' ),
						'type'  => 'title',
						'desc'  => __( 'Configure settings for the WooCommerce My Account page.', 'wc-key-manager' ),
					),
					array(
						'id'       => 'wckm_enable_my_account_keys_page',
						'title'    => __( 'Keys Page', 'wc-key-manager' ),
						'desc'     => __( 'Enable the keys page.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will add a keys page in the My Account section where customers can view their keys.', 'wc-key-manager' ),
						'default'  => 'yes',
						'type'     => 'checkbox',
					),
					array(
						'id'       => 'wckm_my_account_keys_columns',
						'title'    => __( 'Keys Page Columns', 'wc-key-manager' ),
						'desc'     => __( 'Select the columns to display on the keys page. Leave blank to show all columns.', 'wc-key-manager' ),
						'desc_tip' => __( 'Choose which columns to show on the keys page.', 'wc-key-manager' ),
						'default'  => array( 'key', 'product', 'expires', 'actions' ),
						'type'     => 'multiselect',
						'css'      => 'width: 450px;',
						'class'    => 'wc-enhanced-select',
						'options'  => array(
							'key'     => __( 'Key', 'wc-key-manager' ),
							'product' => __( 'Product', 'wc-key-manager' ),
							'expires' => __( 'Expires', 'wc-key-manager' ),
							'actions' => __( 'Actions', 'wc-key-manager' ),
						),
					),
					array(
						'id'       => 'wckm_my_enable_account_activations',
						'title'    => __( 'Show Activations', 'wc-key-manager' ),
						'desc'     => __( 'Display the activations list for each key.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will show the activations list for each key in the My Account page.', 'wc-key-manager' ),
						'default'  => 'yes',
						'type'     => 'checkbox',
					),
					array(
						'id'       => 'wckm_my_account_allow_activation',
						'title'    => __( 'Allow Activation', 'wc-key-manager' ),
						'desc'     => __( 'Allow customers to activate their keys.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will enable customers to activate their keys from the My Account page.', 'wc-key-manager' ),
						'type'     => 'checkbox',
						'default'  => 'yes',
					),
					array(
						'id'       => 'wckm_my_account_allow_deactivation',
						'title'    => __( 'Allow Deactivation', 'wc-key-manager' ),
						'desc'     => __( 'Allow customers to deactivate their keys.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will enable customers to deactivate their keys from the My Account page.', 'wc-key-manager' ),
						'type'     => 'checkbox',
						'default'  => 'yes',
					),
					array(
						'id'       => 'wckm_validate_key_page_id',
						'title'    => __( 'Validate Key Page', 'wc-key-manager' ),
						'desc'     => __( 'Select the page for key validation.', 'wc-key-manager' ),
						'desc_tip' => __( 'This will add a key validation page where customers can validate their keys. Must use the shortcode [wckm_validate_key].', 'wc-key-manager' ),
						'type'     => 'single_select_page',
						'class'    => 'wc-page-search',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wckm_my_account',
					),
				);

				break;

			case 'advanced':
				$settings = array(
					array(
						'id'    => 'wckm_key_options',
						'title' => __( 'Default Automatic Key Settings', 'wc-key-manager' ),
						'type'  => 'title',
						'desc'  => __( 'Configure how automatic keys will be generated. You can override these settings in the product edit page.', 'wc-key-manager' ),
					),
					array(
						'id'          => 'wckm_pattern',
						'title'       => __( 'Pattern', 'wc-key-manager' ),
						'desc'        => wp_kses_post( __( 'The pattern will be used to generate keys. Hashes (#) will be replaced by random characters. You can use the following placeholders too:<br>{id} - Product ID<br>{sku} - Product SKU<br>{Y} - Year<br>{m} - Month<br>{d} - Day<br>{H} - Hour<br>{i} - Minute<br>{s} - Second', 'wc-key-manager' ) ),
						'placeholder' => '####-####-####-####',
						'default'     => '####-####-####-####',
						'type'        => 'text',
					),
					array(
						'id'          => 'wckm_charset',
						'title'       => __( 'Charset', 'wc-key-manager' ),
						'desc'        => __( 'Hashes (#) in the key pattern will be replaced by these characters.', 'wc-key-manager' ),
						'placeholder' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
						'default'     => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
						'type'        => 'text',
					),
					array(
						'id'      => 'wckm_valid_for',
						'title'   => __( 'Valid For (days)', 'wc-key-manager' ),
						'desc'    => __( 'Relative expiration date in number from the date of purchase. Leave blank for no expiration.', 'wc-key-manager' ),
						'type'    => 'number',
						'default' => 0,
					),
					array(
						'id'      => 'wckm_activation_limit',
						'title'   => __( 'Activation Limit', 'wc-key-manager' ),
						'desc'    => __( 'Number of times the key can be activated. Leave blank for no limit.', 'wc-key-manager' ),
						'type'    => 'number',
						'default' => 0,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wckm_key_options',
					),
				);
				break;
			case 'misc':
				$settings = array();
				break;

			default:
				break;
		}

		/**
		 * Filter the settings for the plugin.
		 *
		 * @param array $settings The settings.
		 *
		 * @deprecated 1.0.0
		 */
		$settings = apply_filters( 'wc_key_manager_' . $tab . '_settings', $settings );

		/**
		 * Filter the settings for the plugin.
		 *
		 * @param array  $settings The settings.
		 * @param string $tab The current tab.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'wc_key_manager_settings', $settings, $tab );
	}

	/**
	 * Output tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tabs( $tabs ) {
		$doc_link = 'https://wckeymanager.com/docs?utm_source=plugin-settings&utm_medium=link&utm_campaign=docs';
		parent::output_tabs( $tabs );
		printf( '<a  class="nav-tab" href="%s" target="_blank">%s</a>', esc_url( $doc_link ), esc_html__( 'Documentation', 'wc-key-manager' ) );
		if ( ! WCKM()->is_plugin_active( 'wc-key-manager-pro/wc-key-manager-pro.php' ) ) {
			$pro_link = 'https://wckeymanager.com?utm_source=plugin-settings&utm_medium=link&utm_campaign=pro';
			printf( '<a  class="nav-tab" href="%s" target="_blank">%s</a>', esc_url( $pro_link ), esc_html__( 'Upgrade to Pro', 'wc-key-manager' ) );
		}
	}

	/**
	 * Output settings form.
	 *
	 * @param array $settings Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_form( $settings ) {
		$current_tab = $this->get_current_tab();
		$hook        = 'wc_key_manager_settings_' . $current_tab . '_content';
		if ( has_action( $hook ) ) {
			/**
			 * Action hook to output settings form.
			 *
			 * @since 1.0.0
			 */
			do_action( $hook );

			return;
		}
		parent::output_form( $settings );
	}
}
