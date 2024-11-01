<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Ecom WC Omnibus
 * Description:       A simple way to make sure your website complies with the EU Omnibus Directive.
 * Version:           1.0.1
 * Requires PHP:      7.2
 * Author:            Rasmus Jaakonmäki, Sakri Koskimies
 * Author URI:        https://lense.fi
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ecom-wc-omnibus
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
const ECOM_OMNIBUS_VERSION = '1.0.1';

/**
 * Plugin directory.
 */
const ECOM_OMNIBUS_PLUGIN_DIR = __DIR__;

/**
 * Plugin file.
 */
const ECOM_OMNIBUS_PLUGIN_FILE = __FILE__;

/**
 * Plugin templates directory.
 */
const ECOM_OMNIBUS_TEMPLATE_DIR = ECOM_OMNIBUS_PLUGIN_DIR . '/templates';

/**
 * Plugin file name
 */
defined( 'ECOM_OMNIBUS_PLUGIN_FILE_NAME' ) || define( 'ECOM_OMNIBUS_PLUGIN_FILE_NAME', plugin_basename( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once ECOM_OMNIBUS_PLUGIN_DIR . '/includes/traits/trait-ecom-wc-omnibus-singleton.php';
require_once ECOM_OMNIBUS_PLUGIN_DIR . '/includes/class-ecom-wc-omnibus.php';

Ecom_Wc_Omnibus::init();
