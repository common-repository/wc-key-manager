<?php
/**
 * Plugin Name:          WC Key Manager
 * Plugin URI:           https://wckeymanager.com/
 * Description:          WooCommerce Key Manager is a WooCommerce plugin for selling and managing license keys, game keys, pin codes, gift cards, serial numbers, and other items.
 * Version:              1.0.9
 * Author:               PluginEver
 * Author URI:           https://wckeymanager.com/
 * Text Domain:          wc-key-manager
 * Domain Path:          /languages/
 * Requires Plugins:     woocommerce
 * Tested up to:         6.6
 * Requires at least:    5.0
 * Requires PHP:         8.0
 * WC requires at least: 3.0.0
 * WC tested up to:      9.3
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package KeyManager
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// Don't call the file directly.
defined( 'ABSPATH' ) || exit();

// Require the autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Instantiate the plugin.
KeyManager\Plugin::create(
	array(
		'file'         => __FILE__,
		'settings_url' => admin_url( 'admin.php?page=wckm-settings' ),
		'support_url'  => 'https://pluginever.com/support/',
		'docs_url'     => 'https://wckeymanager.com/docs',
	)
);
