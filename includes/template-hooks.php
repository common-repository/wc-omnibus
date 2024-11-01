<?php
/**
 * Template hooks.
 *
 * @package Ecom_Wc_Omnibus/Includes
 */

$omnibus_enabled        = ECOM_OMNIBUS()->is_omnibus_enabled();
$omnibus_simple_hook    = ECOM_OMNIBUS()->get_omnibus_simple_hook();
$omnibus_variation_hook = ECOM_OMNIBUS()->get_omnibus_variation_hook();

if ( $omnibus_enabled && ! empty( $omnibus_simple_hook ) ) {
	$simple_hook     = $omnibus_simple_hook->get_hook();
	$simple_priority = $omnibus_simple_hook->get_priority();
	/**
	 * Add omnibus price to product page.
	 */
	if ( ! empty( $simple_hook ) ) {
		add_action( $simple_hook, 'ecom_omnibus_price', $simple_priority );
	}
}


if ( $omnibus_enabled && ! empty( $omnibus_variation_hook ) ) {
	$variation_hook     = $omnibus_variation_hook->get_hook();
	$variation_priority = $omnibus_variation_hook->get_priority();
	/**
	 * Add empty html element to house omnibus variation data.
	 */
	if ( ! empty( $variation_hook ) ) {
		add_action( $variation_hook, 'ecom_omnibus_single_variation_price', $variation_priority );
	}
}
