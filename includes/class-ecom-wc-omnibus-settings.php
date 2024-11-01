<?php
/**
 * Plugin settings definitions.
 *
 * @package Ecom_Wc_Omnibus/Includes
 */

/**
 * Plugin settings definitions.
 *
 * @author Rasmus Jaakonmäki & Sakri Koskimies <sakri.koskimies@hotmail.com>
 */
class Ecom_Wc_Omnibus_Settings {
	public const OMNIBUS_HOOK_AFTER_TITLE       = 'after_title';
	public const OMNIBUS_HOOK_AFTER_PRICE       = 'after_price';
	public const OMNIBUS_HOOK_AFTER_EXCERPT     = 'after_excerpt';
	public const OMNIBUS_HOOK_AFTER_ADD_TO_CART = 'after_add_to_cart';
	public const OMNIBUS_HOOK_AFTER_META        = 'after_meta';
	public const OMNIBUS_HOOK_AFTER_SHARING     = 'after_sharing';
	public const OMNIBUS_HOOK_SINGLE_MANUAL     = 'single_manual';

	public const OMNIBUS_HOOK_BEFORE_VARIATION_DATA       = 'before_variation_data';
	public const OMNIBUS_HOOK_AFTER_VARIATION_DATA        = 'after_variation_data';
	public const OMNIBUS_HOOK_AFTER_VARIATION_ADD_TO_CART = 'after_variation_add_to_cart';
	public const OMNIBUS_HOOK_VARIATION_MANUAL            = 'variation_manual';

	public const OMNIBUS_DEFAULT_HOOK           = self::OMNIBUS_HOOK_AFTER_PRICE;
	public const OMNIBUS_DEFAULT_VARIATION_HOOK = self::OMNIBUS_HOOK_AFTER_VARIATION_DATA;

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin name. Used in setting ids.
	 */
	public function __construct( string $plugin_name ) {
		$this->plugin_name = $plugin_name;

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_wc_settings_tab' ), 60 );
		add_action( 'woocommerce_settings_tabs_' . $this->plugin_name, array( $this, 'add_wc_settings_tab_content' ) );
		add_action( 'woocommerce_update_options_' . $this->plugin_name, array( $this, 'update_wc_settings_tab_content' ) );
		add_action( 'admin_menu', array( $this, 'maybe_add_ecom_menu_link' ) );
	}

	/**
	 * Get a setting value.
	 *
	 * @param string $name Setting name.
	 * @param mixed  $default A fallback value.
	 *
	 * @return mixed
	 */
	public static function get_setting( string $name, $default = null ) {
		return get_option( self::get_prefixed_setting_name( $name ), $default );
	}

	/**
	 * Get settings tab url.
	 *
	 * @return string
	 */
	public function get_settings_tab_url(): string {
		return admin_url( 'admin.php?page=wc-settings&tab=' . $this->plugin_name );
	}

	/**
	 * Whether ecom menu link exists in wp sidebar.
	 *
	 * @param string $slug Menu slug.
	 *
	 * @return bool
	 */
	protected function ecom_menu_link_exists( string $slug ): bool {
		global $menu;

		if ( is_array( $menu ) ) {
			$slugs = array_map(
				function ( $item ) {
					return $item[2] ?? null;
				},
				$menu
			);
			$slugs = array_filter( $slugs, 'is_string' );
			if ( in_array( $slug, $slugs, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add ECOM menu link to wp sidebar if it doesn't already exist.
	 *
	 * @return void
	 */
	public function maybe_add_ecom_menu_link() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		$slug = 'ecom-settings';

		if ( ! $this->ecom_menu_link_exists( $slug ) ) {
			add_menu_page(
				'ECOM',
				'ECOM',
				'manage_woocommerce',
				$slug,
				array( $this, 'redirect_to_settings_page' ),
				'dashicons-text',
				90
			);
		}

		add_submenu_page(
			$slug,
			__( 'Omnibus', 'ecom-wc-omnibus' ),
			__( 'Omnibus', 'ecom-wc-omnibus' ),
			'manage_woocommerce',
			$this->plugin_name,
			array( $this, 'redirect_to_settings_page' )
		);
	}

	/**
	 * Redirect to settings page.
	 *
	 * @return void
	 */
	public function redirect_to_settings_page() {
		wp_safe_redirect( $this->get_settings_tab_url() );
		exit;
	}

	/**
	 * Add a new settings tab for omnibus.
	 *
	 * @param array $tabs Existing tabs.
	 *
	 * @return array
	 */
	public function add_wc_settings_tab( array $tabs ): array {
		$tabs[ Ecom_Wc_Omnibus::instance()->get_plugin_name() ] = __( 'Omnibus', 'ecom-wc-omnibus' );

		return $tabs;
	}

	/**
	 * Add content to omnibus settings tab.
	 *
	 * @return void
	 */
	public function add_wc_settings_tab_content() {
		woocommerce_admin_fields( $this->get_omnibus_settings_fields( $this->plugin_name ) );
	}

	/**
	 * Update content in omnibus settings tab.
	 *
	 * @return void
	 */
	public function update_wc_settings_tab_content() {
		woocommerce_update_options( $this->get_omnibus_settings_fields( $this->plugin_name ) );
	}

	/**
	 * Get a prefixed setting name.
	 *
	 * @param string $name Setting name.
	 *
	 * @return string
	 */
	public static function get_prefixed_setting_name( string $name ): string {
		return Ecom_Wc_Omnibus::instance()->get_plugin_name() . "-wc-setting-$name";
	}

	/**
	 * Get omnibus settings fields.
	 *
	 * @param string $prefix Prefix to add to settings fields.
	 *
	 * @return array
	 */
	private static function get_omnibus_settings_fields( string $prefix ): array {
		$settings = array(
			'title'                           => array(
				'title' => __( 'Ecom WC Omnibus settings', 'ecom-wc-omnibus' ),
				'desc'  => sprintf(
					'<p>%s</p><p>%s</p><p>%s<br></p><b><p>[ecom_wc_omnibus_price]</b> (%s)<br> <b>[ecom_wc_omnibus_price type=simple]</b><br> <b>[ecom_wc_omnibus_price type=variable]</b></p>',
					__( 'Display options are for regular WooCommerce product page templates.', 'ecom-wc-omnibus' ),
					__(
						'Use option "Manual (with shortcode) when using custom theme or template without regular hooks.',
						'ecom-wc-omnibus'
					),
					__( 'Shortcodes:', 'ecom-wc-omnibus' ),
					__(
						'determines automatically if on simple or variation product page',
						'ecom-wc-omnibus'
					)
				),
				'type'  => 'title',
			),
			'omnibus_enabled'                 => array(
				'id'      => self::get_prefixed_setting_name( 'omnibus_enabled' ),
				'title'   => __( 'Enabled', 'ecom-wc-omnibus' ),
				'desc'    => __( 'Show omnibus notification on product pages', 'ecom-wc-omnibus' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			'omnibus_warning_enabled'         => array(
				'id'      => self::get_prefixed_setting_name( 'omnibus_warning_enabled' ),
				'title'   => __( 'Product edit warning', 'ecom-wc-omnibus' ),
				'desc'    => __( 'Show omnibus warning on product edit page if product or variation is on sale without omnibus price', 'ecom-wc-omnibus' ),
				'type'    => 'checkbox',
				'tooltip' => __( 'This option is only available if you have WooCommerce Product Vendors plugin installed.', 'ecom-wc-omnibus' ),
				'default' => 'yes',
			),
			'omnibus_frontend_hook_simple'    => array(
				'id'      => self::get_prefixed_setting_name( 'omnibus_frontend_hook_simple' ),
				'title'   => __( 'Simple product price display', 'ecom-wc-omnibus' ),
				'desc'    => __( 'Where to display the omnibus box on simple product pages', 'ecom-wc-omnibus' ),
				'type'    => 'select',
				'default' => self::OMNIBUS_DEFAULT_HOOK,
				'options' => array(
					self::OMNIBUS_HOOK_AFTER_TITLE       => __( 'After product title', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_AFTER_PRICE       => __( 'After product price', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_AFTER_EXCERPT     => __( 'After product excerpt', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_AFTER_ADD_TO_CART => __( 'After product add-to-cart button', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_AFTER_META        => __( 'After product meta', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_AFTER_SHARING     => __( 'After product sharing', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_SINGLE_MANUAL     => __( 'Manual (with shortcode)', 'ecom-wc-omnibus' ),
				),
			),
			'omnibus_frontend_hook_variation' => array(
				'id'      => self::get_prefixed_setting_name( 'omnibus_frontend_hook_variation' ),
				'title'   => __( 'Variation price display', 'ecom-wc-omnibus' ),
				'desc'    => __( 'Where to display the omnibus box on variation pages', 'ecom-wc-omnibus' ),
				'type'    => 'select',
				'default' => self::OMNIBUS_DEFAULT_VARIATION_HOOK,
				'options' => array(
					self::OMNIBUS_HOOK_BEFORE_VARIATION_DATA => __( 'Before variation product data', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_AFTER_VARIATION_DATA => __( 'After variation product data', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_AFTER_VARIATION_ADD_TO_CART => __( 'After variation add-to-cart button', 'ecom-wc-omnibus' ),
					self::OMNIBUS_HOOK_VARIATION_MANUAL => __( 'Manual (with shortcode)', 'ecom-wc-omnibus' ),
				),
			),
			'sectionend'                      => array(
				'type' => 'sectionend',
			),
		);

		return apply_filters( 'ecom_wc_omnibus_settings_fields', $settings );
	}
}
