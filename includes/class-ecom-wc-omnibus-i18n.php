<?php
/**
 * Define the internationalization functionality.
 *
 * @package Ecom_Wc_Omnibus/Includes
 */

/**
 * Define the internationalization functionality.
 */
class Ecom_Wc_Omnibus_I18n {
	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'ecom-wc-omnibus',
			false,
			dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/'
		);
	}
}
