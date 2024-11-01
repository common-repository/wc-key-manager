<?php

namespace KeyManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Notices class.
 *
 * @since 1.0.0
 */
class Notices {

	/**
	 * Notices constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		$installed_time = get_option( 'wckm_installed' );
		$current_time   = wp_date( 'U' );

		/*
		// TODO: Uncomment this code to show the upgrade notice if the halloween offer is ended.
		if ( ! defined( 'WCKM_PRO_VERSION' ) ) {
			WCKM()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/upgrade.php',
					'notice_id'   => 'wckm_upgrade',
					'style'       => 'border-left-color: #0542fa;',
					'dismissible' => false,
				)
			);
		}
		*/

		// Halloween offer notice.
		$halloween_time = date_i18n( strtotime( '2024-11-11 00:00:00' ) );
		if ( $current_time < $halloween_time ) {
			WCKM()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/halloween.php',
					'dismissible' => false,
					'notice_id'   => 'wckm_halloween_promotion',
					'style'       => 'border-left-color: #8500ff;',
					'class'       => 'notice-halloween',
				)
			);
		}

		// Show after 5 days.
		if ( $installed_time && $current_time > ( $installed_time + ( 5 * DAY_IN_SECONDS ) ) ) {
			WCKM()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/review.php',
					'dismissible' => false,
					'notice_id'   => 'wckm_review',
					'style'       => 'border-left-color: #0542fa;',
				)
			);
		}
	}
}
