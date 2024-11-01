<?php

namespace KeyManager;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Class.
 *
 * @since   1.0.0
 * @package KeyManager
 */
final class Plugin extends ByteKit\Plugin {

	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $data ) {
		parent::__construct( $data );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		$upload_dir = wp_upload_dir();
		$role       = apply_filters( 'wc_key_manager_role', 'manage_woocommerce' );
		$this->define( 'WCKM_VERSION', $this->get_version() );
		$this->define( 'WCKM_FILE', $this->get_file() );
		$this->define( 'WCKM_PATH', $this->get_dir_path() );
		$this->define( 'WCKM_URL', $this->get_dir_url() );
		$this->define( 'WCKM_ASSETS_URL', $this->get_assets_url() );
		$this->define( 'WCKM_ASSETS_PATH', $this->get_assets_path() );
		$this->define( 'WCKM_UPLOAD_DIR', $upload_dir['basedir'] . '/wckm' );
		$this->define( 'WCKM_MANAGER_ROLE', $role );
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/functions.php';
	}

	/**
	 * Initialize the plugin hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( Installer::class, 'install' ) );
		add_filter( 'plugin_action_links_' . $this->get_basename(), array( $this, 'plugin_action_links' ) );
		add_action( 'before_woocommerce_init', array( $this, 'on_before_woocommerce_init' ) );
		add_action( 'woocommerce_init', array( $this, 'on_init' ), 0 );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links The plugin action links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		if ( ! $this->is_plugin_active( 'wc-key-manager-pro/wc-key-manager-pro.php' ) ) {
			$links['go_pro'] = '<a href="https://wckeymanager.com" target="_blank" style="color: #39b54a; font-weight: bold;">' . esc_html__( 'Go Pro', 'wc-key-manager' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Run on before WooCommerce init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function on_before_woocommerce_init() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->get_file(), true );
		}
	}

	/**
	 * Run on init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function on_init() {
		// Common controllers.
		$this->set( Handlers\Account::class );
		$this->set( Handlers\Orders::class );
		$this->set( Handlers\Keys::class );
		$this->set( Handlers\Stocks::class );
		$this->set( Handlers\Emails::class );
		$this->set( Handlers\Misc::class );
		$this->set( Handlers\SoftwareAPI::class );
		$this->set( Handlers\Shortcodes::class );

		// Admin only controllers.
		if ( is_admin() ) {
			$this->set( Admin\Admin::class );
			$this->set( Admin\Menus::class );
			$this->set( Admin\Products::class );
			$this->set( Admin\Orders::class );
			$this->set( Admin\Requests::class );
			$this->set( Admin\Notices::class );
			$this->set( Admin\Settings::instance() );
		}

		/**
		 * Fires when the plugin is initialized.
		 *
		 * @param Plugin $this The plugin instance.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_key_manager_init', $this );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_rest_routes() {
		if ( 'yes' !== get_option( 'wckm_enable_rest_api', 'no' ) ) {
			return;
		}
		$controllers = array(
			API\Keys::class,
			API\Activations::class,
		);
		foreach ( $controllers as $controller ) {
			$controller = new $controller();
			if ( method_exists( $controller, 'register_routes' ) ) {
				$controller->register_routes();
			}
			$this->set( $controller );
		}
	}
}
