<?php
/**
 * Intermediate class for representing target hooks to be registered.
 *
 * @package Ecom_Wc_Omnibus/Includes
 */

/**
 * Intermediate class for representing target hooks to be registered.
 */
class Ecom_Wc_Omnibus_Priority_Hook {
	/**
	 * Hook name.
	 *
	 * @var string
	 */
	protected $hook;
	/**
	 * Hook priority.
	 *
	 * @var int
	 */
	protected $priority;

	/**
	 * Constructor.
	 *
	 * @param string $hook Hook name.
	 * @param int    $priority Hook priority.
	 */
	public function __construct( string $hook, int $priority ) {
		$this->hook     = $hook;
		$this->priority = $priority;
	}

	/**
	 * Create a new priority hook from a string.
	 *
	 * @param string $priority_hook Priority hook string with hook name and priority, separated by $separator.
	 * @param string $separator Priority hook string separator.
	 *
	 * @return static
	 * @throws Exception Throws on invalid format.
	 */
	public static function from_string( string $priority_hook, string $separator = '|' ): self {
		$parts = explode( $separator, $priority_hook );

		if ( count( $parts ) === 2 ) {
			return new self( $parts[0], $parts[1] );
		} elseif ( count( $parts ) === 1 ) {
			// For backwards compatibility.
			return new self( $parts[0], 10 );
		} else {
			throw new Exception( "Priority hook must consist of two parts, separated by $separator" );
		}
	}

	/**
	 * Get hook name.
	 *
	 * @return string
	 */
	public function get_hook(): string {
		return $this->hook;
	}

	/**
	 * Get hook priority.
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return $this->priority;
	}
}
