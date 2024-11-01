<?php
/**
 * Singleton trait.
 *
 * @package Ecom_Wc_Omnibus/Includes/Traits
 */

/**
 * Singleton trait.
 */
trait Ecom_Wc_Omnibus_Singleton {
	/**
	 * Class instance.
	 *
	 * @var static
	 */
	private static $instance;

	/**
	 * New class instance.
	 *
	 * @return static
	 */
	public static function instance() {
		if ( empty( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Init the class. An alias for instance().
	 *
	 * @return static
	 */
	public static function init() {
		return static::instance();
	}
}
