<?php
/**
 * The core plugin class.
 *
 * @package Ecom_Wc_Omnibus/Includes
 */

/**
 * The core plugin class.
 */
class Ecom_Wc_Omnibus {
	use Ecom_Wc_Omnibus_Singleton;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * Settings class instance.
	 *
	 * @var Ecom_Wc_Omnibus_Settings
	 */
	protected $settings;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->plugin_name = 'ecom-wc-omnibus';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_public_hooks();

		if ( is_admin() ) {
			$this->define_admin_hooks();
		}

		$this->settings = new Ecom_Wc_Omnibus_Settings( $this->plugin_name );
	}

	/**
	 * Get the plugin settings manager.
	 *
	 * @return Ecom_Wc_Omnibus_Settings
	 */
	public function get_settings_manager(): Ecom_Wc_Omnibus_Settings {
		return $this->settings;
	}

	/**
	 * Get omnibus display hook for simple products.
	 *
	 * @return Ecom_Wc_Omnibus_Priority_Hook|null
	 */
	public function get_omnibus_simple_hook(): ?Ecom_Wc_Omnibus_Priority_Hook {
		$setting = ecom_omnibus_get_setting(
			'omnibus_frontend_hook_simple',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_DEFAULT_HOOK
		);

		$hook_map = array(
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_TITLE       => 'woocommerce_single_product_summary|7',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_PRICE       => 'woocommerce_single_product_summary|12',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_EXCERPT     => 'woocommerce_single_product_summary|22',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_ADD_TO_CART => 'woocommerce_single_product_summary|32',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_META        => 'woocommerce_single_product_summary|42',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_SHARING     => 'woocommerce_single_product_summary|51',
		);

		try {
			$priority_hook = Ecom_Wc_Omnibus_Priority_Hook::from_string(
				$hook_map[ $setting ] ?? ''
			);
		} catch ( Exception $e ) {
			$priority_hook = null;
		}

		return apply_filters( 'ecom_wc_omnibus_hook_simple', $priority_hook );
	}

	/**
	 * Get omnibus display hook for variations.
	 *
	 * @return Ecom_Wc_Omnibus_Priority_Hook|null
	 */
	public function get_omnibus_variation_hook(): ?Ecom_Wc_Omnibus_Priority_Hook {
		$setting = ecom_omnibus_get_setting(
			'omnibus_frontend_hook_variation',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_DEFAULT_VARIATION_HOOK
		);

		$hook_map = array(
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_BEFORE_VARIATION_DATA       => 'woocommerce_single_variation|5',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_VARIATION_DATA        => 'woocommerce_single_variation|15',
			Ecom_Wc_Omnibus_Settings::OMNIBUS_HOOK_AFTER_VARIATION_ADD_TO_CART => 'woocommerce_single_variation|25',
		);

		try {
			$priority_hook = Ecom_Wc_Omnibus_Priority_Hook::from_string(
				$hook_map[ $setting ] ?? ''
			);
		} catch ( Exception $e ) {
			$priority_hook = null;
		}

		return apply_filters( 'ecom_wc_omnibus_hook_variation', $priority_hook );
	}

	/**
	 * Whether omnibus functionality is enabled.
	 *
	 * @return bool
	 */
	public function is_omnibus_enabled(): bool {
		$setting = ecom_omnibus_get_setting(
			'omnibus_enabled',
			'yes'
		);

		return 'yes' === $setting;
	}

	/**
	 * Whether omnibus warning on product edit page is enabled.
	 *
	 * @return bool
	 */
	public function is_omnibus_warning_enabled(): bool {
		$setting = ecom_omnibus_get_setting(
			'omnibus_warning_enabled',
			'yes'
		);

		return 'yes' === $setting;
	}

	/**
	 * Whether omnibus price is shown for a product.
	 *
	 * @param WC_Product|int|string $product Product object or ID.
	 *
	 * @return bool
	 */
	public function is_omnibus_shown_for_product( $product ): bool {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		if ( empty( $product ) ) {
			return false;
		}

		$show_omnibus = $product->is_on_sale() && ! empty( Ecom_Wc_Omnibus_Admin::get_product_omnibus_price( $product ) );

		return apply_filters( 'ecom_wc_omnibus_shown_for_product', $show_omnibus, $product );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once ECOM_OMNIBUS_PLUGIN_DIR . '/includes/functions.php';
		require_once ECOM_OMNIBUS_PLUGIN_DIR . '/includes/class-ecom-wc-omnibus-i18n.php';
		require_once ECOM_OMNIBUS_PLUGIN_DIR . '/includes/class-ecom-wc-omnibus-settings.php';
		require_once ECOM_OMNIBUS_PLUGIN_DIR . '/includes/class-ecom-wc-omnibus-priority-hook.php';
		require_once ECOM_OMNIBUS_PLUGIN_DIR . '/admin/class-ecom-wc-omnibus-admin.php';
		require_once ECOM_OMNIBUS_PLUGIN_DIR . '/public/class-ecom-wc-omnibus-public.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ecom_Wc_Omnibus_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	private function set_locale() {
		$plugin_i18n = new Ecom_Wc_Omnibus_I18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Ecom_Wc_Omnibus_Admin( $this->get_plugin_name() );

		add_action( 'plugins_loaded', array( $plugin_admin, 'wc_omnibus_admin_notice' ) );
		add_filter( 'ecom_plugins_list', array( $plugin_admin, 'add_to_ecom_plugins_list' ), 10, 1 );
		add_filter( 'plugin_action_links_' . ECOM_OMNIBUS_PLUGIN_FILE_NAME, array( $plugin_admin, 'add_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $plugin_admin, 'display_product_omnibus_pricing' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( $plugin_admin, 'display_variation_omnibus_pricing' ), 10, 3 );
		add_action( 'woocommerce_process_product_meta', array( $plugin_admin, 'save_product_omnibus_pricing' ) );
		add_action( 'woocommerce_save_product_variation', array( $plugin_admin, 'save_variation_omnibus_pricing' ) );
		add_action( 'admin_notices', array( $plugin_admin, 'edit_product_omnibus_notice' ) );
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks() {
		$plugin_public = new Ecom_Wc_Omnibus_Public( $this->get_plugin_name() );

		add_action( 'plugins_loaded', array( $plugin_public, 'load_template_hooks' ) );
		add_action( 'wc_get_template', array( $plugin_public, 'get_template' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts' ) );
		add_filter( 'woocommerce_get_script_data', array( $plugin_public, 'get_script_data' ), 10, 2 );
		add_filter( 'woocommerce_available_variation', array( $plugin_public, 'add_variation_data' ), 10, 3 );
		add_action( 'init', array( $plugin_public, 'add_shortcode' ) );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}
}
