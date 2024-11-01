<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package Ecom_Wc_Omnibus/Public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @author Rasmus Jaakonmäki & Sakri Koskimies <sakri.koskimies@hotmail.com>
 */
class Ecom_Wc_Omnibus_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 */
	public function __construct( string $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Add a shortcode for displaying omnibus price.
	 *
	 * @return void
	 */
	public function add_shortcode() {
		add_shortcode( 'ecom_wc_omnibus_price', array( $this, 'omnibus_price_shortcode' ) );
	}

	/**
	 * Load js template when registering variation script.
	 *
	 * @param array|false $params The parameters.
	 * @param string      $handle Script handle.
	 *
	 * @return array|false
	 */
	public function get_script_data( $params, string $handle ) {
		if ( 'wc-add-to-cart-variation' === $handle ) {
			wc_get_template(
				'ecom/omnibus-variation-price.php',
				array()
			);
		}

		return $params;
	}

	/**
	 * Add omnibus variation data to be displayed.
	 *
	 * @param array                             $array Existing data.
	 * @param \WC_Product|\WC_Product_Variable  $variable Product. Unused.
	 * @param \WC_Product|\WC_Product_Variation $variation Variation.
	 *
	 * @return array
	 */
	public function add_variation_data( array $array, $variable, $variation ): array {
		$date  = Ecom_Wc_Omnibus_Admin::get_product_omnibus_date( $variation );
		$date  = $date ? $date->format( ecom_omnibus_get_date_format() ) : null;
		$price = Ecom_Wc_Omnibus_Admin::get_product_omnibus_price( $variation );

		$array['ecom_omnibus_data'] = array(
			'enabled'  => ecom_product_has_omnibus( $variation ),
			'date'     => $date,
			'wc_price' => $price ? wc_price( $price ) : null,
		);

		return $array;
	}

	/**
	 * Get template file absolute path by template name.
	 *
	 * @param string $template Returned template path.
	 * @param string $template_name Template name.
	 *
	 * @return false|string
	 */
	public function get_template( string $template, string $template_name ) {
		if ( file_exists( ECOM_OMNIBUS_TEMPLATE_DIR . '/' . $template_name ) ) {
			$template = realpath( ECOM_OMNIBUS_TEMPLATE_DIR . '/' . $template_name );
		} elseif ( locate_template( $template_name ) ) {
			$template = locate_template( $template_name );
		}

		return $template;
	}

	/**
	 * Load template hooks. Called after plugins have loaded so that all singleton instances hava inited.
	 *
	 * @return void
	 */
	public function load_template_hooks() {
		require_once ECOM_OMNIBUS_PLUGIN_DIR . '/includes/template-hooks.php';
	}

	/**
	 * The [ecom_wc_omnibus_price] shortcode.
	 *
	 * Accepts a type arg and will display the omnibus price.
	 *
	 * @param array $atts Shortcode attributes. Default empty.
	 *
	 * @return string Shortcode output.
	 */
	public function omnibus_price_shortcode( $atts ): string {
		global $product;

		if ( empty( $product ) || ! ECOM_OMNIBUS()->is_omnibus_enabled() ) {
			return '';
		}

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$atts = shortcode_atts(
			array(
				'type' => '',
			),
			$atts,
			'ecom_wc_omnibus_price'
		);

		$type = $atts['type'];

		ob_start();

		if ( 'simple' === $type ) {
			ecom_omnibus_price();
		} elseif ( 'variable' === $type ) {
			ecom_omnibus_single_variation_price();
		} else {
			if ( $product->is_type( 'variable' ) ) {
				ecom_omnibus_single_variation_price();
			} else {
				ecom_omnibus_price();
			}
		}

		return ob_get_clean();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		$script_path = '/build/public.css';

		wp_enqueue_style(
			$this->plugin_name,
			plugins_url( $script_path, ECOM_OMNIBUS_PLUGIN_FILE ),
			array(),
			filemtime( ECOM_OMNIBUS_PLUGIN_DIR . $script_path )
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		$script_path       = '/build/public.js';
		$script_asset_path = ECOM_OMNIBUS_PLUGIN_DIR . '/build/public.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => time(),
			);

		$script_asset['dependencies'][] = 'wp-util';

		wp_enqueue_script(
			$this->plugin_name,
			plugins_url( $script_path, ECOM_OMNIBUS_PLUGIN_FILE ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}
}
