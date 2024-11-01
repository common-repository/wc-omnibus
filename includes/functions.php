<?php
/**
 * Global namespace functions.
 *
 * @package Ecom_Wc_Omnibus/Includes
 */

if ( ! function_exists( 'ECOM_OMNIBUS' ) ) {
	/**
	 * Get the ECOM_OMNIBUS instance.
	 *
	 * @return Ecom_Wc_Omnibus
	 */
	function ECOM_OMNIBUS(): Ecom_Wc_Omnibus { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return Ecom_Wc_Omnibus::instance();
	}
}

if ( ! function_exists( 'ecom_omnibus_get_setting' ) ) {
	/**
	 * Get a setting value.
	 *
	 * @param string $name Setting name.
	 * @param mixed  $default A fallback value.
	 *
	 * @return mixed
	 */
	function ecom_omnibus_get_setting( string $name, $default = null ) {
		return ECOM_OMNIBUS()->get_settings_manager()->get_setting( $name, $default );
	}
}

if ( ! function_exists( 'ecom_omnibus_price' ) ) {
	/**
	 * Display omnibus price markup.
	 *
	 * @return void
	 */
	function ecom_omnibus_price() {
		global $product;

		if ( empty( $product ) || $product->is_type( 'variable' ) ) {
			return;
		}

		if ( ! ecom_product_has_omnibus( $product ) ) {
			return;
		}

		wc_get_template(
			'ecom/omnibus-price.php',
			array(
				'date'  => Ecom_Wc_Omnibus_Admin::get_product_omnibus_date( $product ),
				'price' => Ecom_Wc_Omnibus_Admin::get_product_omnibus_price( $product ),
			)
		);
	}
}

if ( ! function_exists( 'ecom_omnibus_single_variation_price' ) ) {
	/**
	 * Print blank html element to house omnibus variation data.
	 *
	 * @return void
	 */
	function ecom_omnibus_single_variation_price() {
		echo '<div class="single_variation--ecom-omnibus"></div>';
	}
}

if ( ! function_exists( 'ecom_product_has_omnibus' ) ) {
	/**
	 * Whether omnibus price is shown for a product.
	 *
	 * @param WC_Product|int|string $product Product object or ID.
	 *
	 * @return bool
	 */
	function ecom_product_has_omnibus( $product ): bool {
		return ECOM_OMNIBUS()->is_omnibus_shown_for_product( $product );
	}
}

if ( ! function_exists( 'ecom_omnibus_get_date_format' ) ) {
	/**
	 * Get the date format for displaying omnibus price.
	 *
	 * @return string
	 */
	function ecom_omnibus_get_date_format(): string {
		return apply_filters( 'ecom_wc_omnibus_date_format', get_option( 'date_format' ) );
	}
}
