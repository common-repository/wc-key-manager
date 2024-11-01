<?php

namespace KeyManager\Admin;

use KeyManager\Models\Activation;
use KeyManager\Models\Generator;
use KeyManager\Models\Key;

defined( 'ABSPATH' ) || exit();

/**
 * Menus class.
 *
 * @since 1.0.0
 * @package KeyManager\Admin
 */
class Menus {

	/**
	 * Main menu slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const PARENT_SLUG = 'key-manager';

	/**
	 * List tables.
	 *
	 * @var \WP_List_Table
	 */
	private $list_table;

	/**
	 * Menus constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'set-screen-option', array( $this, 'screen_option' ), 10, 3 );
		add_action( 'current_screen', array( $this, 'setup_list_table' ) );

		// Keys page.
		add_action( 'wc_key_manager_keys_content', array( $this, 'render_keys_content' ) );
		add_action( 'wc_key_manager_keys_generators_content', array( $this, 'render_keys_generators_content' ) );
		add_action( 'wc_key_manager_keys_activations_content', array( $this, 'render_activations_content' ) );

		// Tools tabs.
		add_action( 'wc_key_manager_tools_general_content', array( $this, 'render_tools_general_content' ) );
		add_action( 'wc_key_manager_tools_import_content', array( $this, 'render_import_content' ) );

		// Settings tabs.
		add_action( 'wc_key_manager_settings_emails_content', array( $this, 'render_emails_settings' ) );
		add_action( 'wc_key_manager_settings_features_content', array( $this, 'render_features_content' ) );

		// Pro tabs.
		if ( ! WCKM()->is_plugin_active( 'wc-key-manager-pro' ) ) {
			add_action( 'wc_key_manager_tools_export_content', array( $this, 'render_export_content' ) );
			add_action( 'wc_key_manager_settings_custom_fields_content', array( $this, 'render_custom_fields' ) );
			add_action( 'wc_key_manager_settings_misc_content', array( $this, 'render_misc_settings' ) );
			add_action( 'wc_key_manager_settings_webhook_content', array( $this, 'render_webhook_settings' ) );
			add_action( 'wc_key_manager_settings_barcode_content', array( $this, 'render_barcode_settings' ) );
		}
	}

	/**
	 * Register admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu() {
		global $admin_page_hooks;
		add_menu_page(
			__( 'Key Manager', 'wc-key-manager' ),
			__( 'Key Manager', 'wc-key-manager' ),
			'manage_options',
			self::PARENT_SLUG,
			null,
			'dashicons-admin-network',
			'55.9'
		);
		$admin_page_hooks['wc-key-manager'] = 'wc-key-manager';

		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Dashboard', 'wc-key-manager' ),
			__( 'Dashboard', 'wc-key-manager' ),
			'manage_options',
			self::PARENT_SLUG,
			function () {
				$page_id = 'dashboard';
				include_once __DIR__ . '/views/dashboard.php';
			},
		);

		$submenus = Utilities::get_menus();
		usort(
			$submenus,
			function ( $a, $b ) {
				$a = isset( $a['position'] ) ? $a['position'] : PHP_INT_MAX;
				$b = isset( $b['position'] ) ? $b['position'] : PHP_INT_MAX;

				return $a - $b;
			}
		);
		foreach ( $submenus as $submenu ) {
			$submenu = wp_parse_args(
				$submenu,
				array(
					'page_title' => '',
					'menu_title' => '',
					'capability' => 'manage_options',
					'menu_slug'  => '',
					'callback'   => null,
					'position'   => '10',
					'page_id'    => null,
					'tabs'       => array(),
					'load_hook'  => null,
				)
			);
			if ( ! is_callable( $submenu['callback'] ) && ! empty( $submenu['page_id'] ) ) {
				$submenu['callback'] = function () use ( $submenu ) {
					$page_id = $submenu['page_id'];
					$tabs    = $submenu['tabs'];
					include_once __DIR__ . '/views/admin-page.php';
				};
			}
			$load = add_submenu_page(
				self::PARENT_SLUG,
				$submenu['page_title'],
				$submenu['menu_title'],
				$submenu['capability'],
				$submenu['menu_slug'],
				$submenu['callback'],
				$submenu['position']
			);
			if ( ! empty( $submenu['load_hook'] ) && is_callable( $submenu['load_hook'] ) ) {
				add_action( 'load-' . $load, $submenu['load_hook'] );
			}
		}

		if ( ! defined( 'WCKM_PRO_VERSION' ) ) {
			add_submenu_page(
				self::PARENT_SLUG,
				'',
				'<span style="color:#05ef82;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Upgrade to Pro', 'wc-key-manager' ) . '</span>',
				'manage_options',
				'wc-key-manager-pro',
				function () {
					$redirect_to = 'https://wckeymanager.com?utm_source=plugin&utm_medium=upgrade-to-pro&utm_campaign=admin-menu';
					wp_redirect( $redirect_to ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
					exit();
				},
				'55.9'
			);
		}
	}

	/**
	 * Set screen option.
	 *
	 * @param mixed  $status Screen option value. Default false.
	 * @param string $option Option name.
	 * @param mixed  $value New option value.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function screen_option( $status, $option, $value ) {
		$options = apply_filters(
			'wc_key_manager_screen_options',
			array(
				'wckm_keys_per_page',
				'wckm_generators_per_page',
				'wckm_activations_per_page',
			)
		);
		if ( in_array( $option, $options, true ) ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Current screen.
	 *
	 * @since 1.0.0
	 */
	public function setup_list_table() {
		wp_verify_nonce( '_wpnonce' );
		$screen = get_current_screen();
		if ( Utilities::is_add_screen() || Utilities::is_edit_screen() || ! in_array( $screen->id, Utilities::get_screen_ids(), true ) ) {
			return;
		}
		$args = array(
			'label'   => __( 'Per page', 'wc-key-manager' ),
			'default' => 20,
		);
		$page = preg_replace( '/^.*?wckm-/', 'wckm-', $screen->id );
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$page = ! empty( $tab ) ? $page . '-' . $tab : $page;

		switch ( $page ) {
			case 'wckm-keys':
			case 'wckm-keys-keys':
				$this->list_table = new ListTables\KeysTable();
				$this->list_table->prepare_items();
				$args['option'] = 'wckm_keys_per_page';
				add_screen_option( 'per_page', $args );
				break;
			case 'wckm-keys-generators':
				$this->list_table = new ListTables\GeneratorsTable();
				$this->list_table->prepare_items();
				$args['option'] = 'wckm_generators_per_page';
				add_screen_option( 'per_page', $args );
				break;
			case 'wckm-keys-activations':
				$this->list_table = new ListTables\ActivationsTable();
				$this->list_table->prepare_items();
				$args['option'] = 'wckm_activations_per_page';
				add_screen_option( 'per_page', $args );
				break;
		}
	}

	/**
	 * Render keys content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_keys_content() {
		$edit = Utilities::is_edit_screen();
		$key  = Key::make( $edit );
		if ( ! empty( $edit ) && ! $key->exists() ) {
			wp_safe_redirect( remove_query_arg( 'edit' ) );
			exit();
		}

		if ( Utilities::is_add_screen() ) {
			include __DIR__ . '/views/keys/add.php';
		} elseif ( $edit ) {
			include __DIR__ . '/views/keys/edit.php';
		} else {
			include __DIR__ . '/views/keys/list.php';
		}
	}

	/**
	 * Render keys generators content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_keys_generators_content() {
		$edit      = Utilities::is_edit_screen();
		$generator = Generator::make( $edit );
		if ( ! empty( $edit ) && ! $generator->exists() ) {
			wp_safe_redirect( remove_query_arg( 'edit' ) );
			exit();
		}

		if ( Utilities::is_add_screen() ) {
			include __DIR__ . '/views/generators/add.php';
		} elseif ( $edit ) {
			include __DIR__ . '/views/generators/edit.php';
		} else {
			include __DIR__ . '/views/generators/list.php';
		}
	}

	/**
	 * Render activations content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_activations_content() {
		$edit       = Utilities::is_edit_screen();
		$activation = Activation::make( $edit );
		if ( ! empty( $edit ) && ! $activation->exists() ) {
			wp_safe_redirect( remove_query_arg( 'edit' ) );
			exit();
		}
		if ( Utilities::is_add_screen() ) {
			include __DIR__ . '/views/activations/add.php';
		} elseif ( $edit ) {
			include __DIR__ . '/views/activations/edit.php';
		} else {
			include __DIR__ . '/views/activations/list.php';
		}
	}

	/**
	 * Render general content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_tools_general_content() {
		include __DIR__ . '/views/tools/general.php';
	}

	/**
	 * Render import content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_import_content() {
		include __DIR__ . '/views/tools/import.php';
	}

	/**
	 * Render export tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_export_content() {
		// Upgrade to Pro.
		esc_html_e( 'Upgrade to Pro to manage export options.', 'wc-key-manager' );
	}

	/**
	 * Render activations content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_reports_activations_content() {
		include __DIR__ . '/views/activations/activations.php';
	}

	/**
	 * Render settings custom fields tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_custom_fields() {
		// Upgrade to Pro.
		esc_html_e( 'Upgrade to Pro to manage custom fields.', 'wc-key-manager' );
	}

	/**
	 * Render settings SMS tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_misc_settings() {
		// Upgrade to Pro.
		include __DIR__ . '/views/promos/misc-promo.php';
	}

	/**
	 * Render settings emails tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_emails_settings() {
		$mailer          = WC()->mailer();
		$email_templates = $mailer->get_emails();

		$key_emails = array_filter(
			$email_templates,
			function ( $email ) {
				// check if the namespace is KeyManager.
				return false !== strpos( get_class( $email ), 'KeyManager' );
			}
		);

		?>
		<tr valign="top">
			<td class="wc_emails_wrapper" colspan="2">
				<table class="wc_emails widefat" cellspacing="0">
					<thead>
					<tr>
						<?php
						$columns = apply_filters(
							'woocommerce_email_setting_columns',
							array(
								'status'     => '',
								'name'       => __( 'Email', 'wc-key-manager' ),
								'email_type' => __( 'Content type', 'wc-key-manager' ),
								'recipient'  => __( 'Recipient(s)', 'wc-key-manager' ),
								'actions'    => '',
							)
						);
						foreach ( $columns as $key => $column ) {
							echo '<th class="wc-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
						}
						?>
					</tr>
					<tbody>
					<?php

					foreach ( $key_emails as $email_key => $email ) {
						echo '<tr>';

						$manage_url = add_query_arg(
							array(
								'section' => strtolower( $email_key ),
							),
							admin_url( 'admin.php?page=wc-settings&tab=email' )
						);

						foreach ( $columns as $key => $column ) {

							switch ( $key ) {
								case 'name':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
										<a href="' . esc_url( $manage_url ) . '">' . esc_html( $email->get_title() ) . '</a>
										' . wp_kses_post( wc_help_tip( $email->get_description() ) ) . '</td>';
									break;
								case 'recipient':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
										' . esc_html( $email->is_customer_email() ? __( 'Customer', 'wc-key-manager' ) : $email->get_recipient() ) . '</td>';
									break;
								case 'status':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">';

									if ( $email->is_manual() ) {
										echo '<span class="status-manual tips" data-tip="' . esc_attr__( 'Manually sent', 'wc-key-manager' ) . '">' . esc_html__( 'Manual', 'wc-key-manager' ) . '</span>';
									} elseif ( $email->is_enabled() ) {
										echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled', 'wc-key-manager' ) . '">' . esc_html__( 'Yes', 'wc-key-manager' ) . '</span>';
									} else {
										echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled', 'wc-key-manager' ) . '">-</span>';
									}

									echo '</td>';
									break;
								case 'email_type':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
											' . esc_html( $email->get_content_type() ) . '
										</td>';
									break;
								case 'actions':
									echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
											<a class="button alignright" href="' . esc_url( $manage_url ) . '">' . esc_html__( 'Manage', 'wc-key-manager' ) . '</a>
										</td>';
									break;
								default:
									do_action( 'woocommerce_email_setting_column_' . $key, $email );
									break;
							}
						}

						echo '</tr>';
					}
					?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render settings upgrade to pro tab content.
	 *
	 * @since 1.0.5
	 * @return void
	 */
	public function render_features_content() {
		include __DIR__ . '/views/settings/features.php';
	}

	/**
	 * Render settings - webhook tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_webhook_settings() {
		// Upgrade to Pro.
		esc_html_e( 'Upgrade to Pro to manage webhooks.', 'wc-key-manager' );
	}

	/**
	 * Render settings - barcode tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_barcode_settings() {
		// Upgrade to Pro.
		esc_html_e( 'Upgrade to Pro to manage barcodes.', 'wc-key-manager' );
	}

	/**
	 * Render settings - API tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_api_content() {
		// Upgrade to Pro.
		esc_html_e( 'Upgrade to Pro to manage API settings.', 'wc-key-manager' );
	}
}
