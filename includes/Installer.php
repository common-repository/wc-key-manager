<?php

namespace KeyManager;

defined( 'ABSPATH' ) || exit;

/**
 * Installer Class.
 *
 * @since 1.0.0
 * @package KeyManager
 */
class Installer {
	/**
	 * Update callbacks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array();

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'check_update' ), 5 );
		add_action( 'wckm_run_update_callback', array( $this, 'run_update_callback' ), 10, 2 );
		add_action( 'wckm_update_db_version', array( $this, 'update_db_version' ) );
		add_action( 'init', array( $this, 'create_cron_jobs' ) );
	}

	/**
	 * Check the plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_update() {
		$db_version      = WCKM()->get_db_version();
		$current_version = WCKM()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );
		$can_install     = ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' );
		if ( $can_install && $requires_update && ! WC()->queue()->get_next( 'wckm_run_update_callback' ) ) {
			static::install();
			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			if ( ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' ) ) {
				$this->update();
			} else {
				WCKM()->update_db_version( $current_version );
			}
		}
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update() {
		$db_version = WCKM()->get_db_version();
		$loop       = 0;
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					WC()->queue()->schedule_single(
						time() + $loop,
						'wckm_run_update_callback',
						array(
							'callback' => $callback,
							'version'  => $version,
						)
					);
					++$loop;
				}
			}
			++$loop;
		}

		if ( version_compare( WCKM()->get_db_version(), WCKM()->get_version(), '<' ) &&
			! WC()->queue()->get_next( 'wckm_update_db_version' ) ) {
			WC()->queue()->schedule_single(
				time() + $loop,
				'wckm_update_db_version',
				array(
					'version' => WCKM()->get_version(),
				)
			);
		}
	}

	/**
	 * Run the update callback.
	 *
	 * @param string $callback The callback to run.
	 * @param string $version The version of the callback.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run_update_callback( $callback, $version ) {
		require_once __DIR__ . '/functions/updates.php';
		if ( is_callable( $callback ) ) {
			$result = (bool) call_user_func( $callback );
			if ( $result ) {
				WC()->queue()->add(
					'wckm_run_update_callback',
					array(
						'callback' => $callback,
						'version'  => $version,
					)
				);
			}
		}
	}

	/**
	 * Update the plugin version.
	 *
	 * @param string $version The version to update to.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_db_version( $version ) {
		WCKM()->update_db_version( $version );
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}
		self::create_tables();
		self::save_default_settings();
		flush_rewrite_rules( true );
		add_option( 'wckm_installed', wp_date( 'U' ) );
		WCKM()->add_db_version();
	}

	/**
	 * Create tables.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';
		$tables  = "
CREATE TABLE {$wpdb->prefix}wckm_keys (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
code varchar(191) NOT NULL,
truncated_key varchar(7) NOT NULL,
product_id bigint(20) unsigned NOT NULL,
order_id bigint(20) unsigned NOT NULL,
order_item_id bigint(20) unsigned NOT NULL,
subscription_id bigint(20) unsigned NOT NULL,
vendor_id bigint(20) unsigned NOT NULL,
customer_id bigint(20) unsigned NOT NULL,
valid_for int(4) NOT NULL,
activation_limit int(3) NOT NULL,
price decimal(10,2) NOT NULL,
source varchar(20) DEFAULT 'preset',
status varchar(20) NOT NULL,
uuid varchar(36) NOT NULL,
ordered_at datetime DEFAULT NULL,
expires_at datetime DEFAULT NULL,
activated_at datetime DEFAULT NULL,
created_at datetime DEFAULT NULL,
updated_at datetime DEFAULT NULL,
PRIMARY KEY  (id),
KEY code (code(191)),
KEY truncated_key (truncated_key),
KEY product_id (product_id),
KEY order_id (order_id),
KEY order_item_id (order_item_id),
KEY subscription_id (subscription_id),
KEY vendor_id (vendor_id),
KEY customer_id (customer_id),
KEY source (source),
UNIQUE KEY uuid (uuid),
KEY status (status),
KEY ordered_at (ordered_at),
KEY expires_at (expires_at),
KEY activated_at (activated_at)
) $collate;
CREATE TABLE {$wpdb->prefix}wckm_keymeta (
meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
wckm_key_id bigint(20) unsigned NOT NULL,
meta_key varchar(191) DEFAULT NULL,
meta_value longtext,
PRIMARY KEY  (meta_id),
KEY wckm_key_id (wckm_key_id),
KEY meta_key (`meta_key`(191))
) $collate;
CREATE TABLE {$wpdb->prefix}wckm_generators (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
name varchar(191) NOT NULL,
pattern varchar(191) NOT NULL,
charset varchar(191) NOT NULL,
valid_for int(4) NOT NULL,
activation_limit int(3) NOT NULL,
status varchar(20) NOT NULL DEFAULT 'active',
created_at datetime DEFAULT NULL,
updated_at datetime DEFAULT NULL,
PRIMARY KEY  (id),
KEY pattern (pattern(191)),
KEY charset (charset(191)),
KEY status (status)
) $collate;
CREATE TABLE {$wpdb->prefix}wckm_activations (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
instance varchar(191) DEFAULT NULL,
ip_address varchar(55) NOT NULL,
user_agent varchar(191) NOT NULL,
key_id bigint(20) unsigned NOT NULL,
status varchar(20) NOT NULL DEFAULT 'active',
activated_at datetime DEFAULT NULL,
deactivated_at datetime DEFAULT NULL,
created_at datetime DEFAULT NULL,
updated_at datetime DEFAULT NULL,
PRIMARY KEY  (id),
KEY instance (instance(191)),
KEY key_id (key_id),
KEY ip_address (ip_address),
KEY status (status)
) $collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $tables );
	}

	/**
	 * Save default settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function save_default_settings() {
		Admin\Settings::instance()->save_defaults();

		// check if we have a page with shortcode [wckm_validate_key] if not create one.
		// search for the page with the shortcode.
		$page = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => 1,
				's'           => '[wckm_validate_key]',
			)
		);

		// if no page found with the shortcode create a new page.
		if ( empty( $page ) ) {
			$page_id = wp_insert_post(
				array(
					'post_title'     => 'Validate Key',
					'post_content'   => '[wckm_validate_key]',
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
			update_option( 'wckm_validate_key_page_id', $page_id );
		}
	}

	/**
	 * Create cron jobs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function create_cron_jobs() {
		if ( ! wp_next_scheduled( 'wc_key_manager_update_expired_keys' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_key_manager_update_expired_keys' );
		}
		// create WooCommerce action scheduler action for every 30 minutes.
		if ( function_exists( 'WC' ) && ! WC()->queue()->get_next( 'wc_key_manager_update_expired_keys' ) ) {
			WC()->queue()->schedule_recurring( time(), 1800, 'wc_key_manager_update_expired_keys' );
		}
	}
}
