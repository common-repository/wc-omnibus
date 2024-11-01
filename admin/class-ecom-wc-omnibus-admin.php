<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Ecom_Wc_Omnibus/Admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @author Rasmus Jaakonmäki & Sakri Koskimies <sakri.koskimies@hotmail.com>
 */
class Ecom_Wc_Omnibus_Admin {
	private const OMNIBUS_PRICE_KEY = 'ecom_omnibus_price';
	private const OMNIBUS_DATE_KEY  = 'ecom_omnibus_date';

	/**
	 * The ID of this plugin.
	 *
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 */
	public function __construct( string $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		$script_path = '/build/admin.css';

		wp_enqueue_style(
			$this->plugin_name,
			plugins_url( $script_path, ECOM_OMNIBUS_PLUGIN_FILE ),
			array(),
			filemtime( ECOM_OMNIBUS_PLUGIN_DIR . $script_path )
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		$script_path       = '/build/admin.js';
		$script_asset_path = ECOM_OMNIBUS_PLUGIN_DIR . '/build/admin.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => time(),
			);

		$script_asset['dependencies'][] = 'jquery';
		$script_asset['dependencies'][] = 'jquery-ui-datepicker';

		wp_enqueue_script(
			$this->plugin_name,
			plugins_url( $script_path, ECOM_OMNIBUS_PLUGIN_FILE ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * Display simple product omnibus input fields.
	 *
	 * @return void
	 */
	public function display_product_omnibus_pricing() {
		global $post;

		$product = wc_get_product( $post->ID );
		if ( $product && $product->is_type( 'variable' ) ) {
			return;
		}

		$this->display_omnibus_fields( $post );
	}

	/**
	 * Display variation omnibus input fields.
	 *
	 * @param mixed   $loop Unused.
	 * @param mixed   $variation_data Unused.
	 * @param WP_Post $variation Variation post object.
	 *
	 * @return void
	 */
	public function display_variation_omnibus_pricing( $loop, $variation_data, WP_Post $variation ) {
		$this->display_omnibus_fields( $variation, true );
	}

	/**
	 * Save simple product omnibus fields.
	 *
	 * @param int|string $post_id Post ID.
	 *
	 * @return void
	 */
	public function save_product_omnibus_pricing( $post_id ) {
		$this->save_omnibus_fields( $post_id );
	}

	/**
	 * Save variation omnibus fields.
	 *
	 * @param int|string $post_id Post ID.
	 *
	 * @return void
	 */
	public function save_variation_omnibus_pricing( $post_id ) {
		$this->save_omnibus_fields( $post_id, true );
	}

	/**
	 * Display omnibus admin fields.
	 *
	 * @param WP_Post $post Post object.
	 * @param bool    $is_variation Whether the post is a variation.
	 *
	 * @return void
	 */
	public function display_omnibus_fields( WP_Post $post, bool $is_variation = false ) {
		?>
		<div class="options_group ecom_wc_omnibus">
			<?php
			woocommerce_wp_text_input(
				array(
					'id'            => $is_variation ? sprintf( '%s[%s]', self::OMNIBUS_PRICE_KEY, $post->ID ) : self::OMNIBUS_PRICE_KEY,
					'wrapper_class' => $is_variation ? 'form-row form-row-first' : '',
					'class'         => 'wc_input_price wc_omnibus_price short',
					'label'         => sprintf(
					// translators: %s: currency symbol.
						esc_attr__( 'Omnibus price (%s)', 'ecom-wc-omnibus' ),
						get_woocommerce_currency_symbol()
					),
					'description'   => esc_attr__( 'Lowest price within 30 days', 'ecom-wc-omnibus' ),
					'value'         => str_replace(
						'.',
						wc_get_price_decimal_separator(),
						get_post_meta( $post->ID, self::OMNIBUS_PRICE_KEY, true )
					),
					'type'          => 'text',
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'            => $is_variation ? sprintf( '%s[%s]', self::OMNIBUS_DATE_KEY, $post->ID ) : self::OMNIBUS_DATE_KEY,
					'wrapper_class' => $is_variation ? 'form-row form-row-last' : '',
					'class'         => 'wc_omnibus_date short',
					'label'         => esc_attr__( 'Omnibus date', 'ecom-wc-omnibus' ),
					'description'   => esc_attr__( 'Date of lowest price', 'ecom-wc-omnibus' ),
					'value'         => wp_date( 'Y-m-d', get_post_meta( $post->ID, self::OMNIBUS_DATE_KEY, true ) ),
					'type'          => 'date',
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Save omnibus product fields.
	 *
	 * @param int|string $post_id Post ID.
	 * @param bool       $is_variation Whether the post is a variation.
	 *
	 * @return void
	 */
	public function save_omnibus_fields( $post_id, bool $is_variation = false ) {
		$this->save_product_field( $post_id, self::OMNIBUS_PRICE_KEY, $is_variation, 'number' );
		$this->save_product_field( $post_id, self::OMNIBUS_DATE_KEY, $is_variation, 'date' );
	}

	/**
	 * Save product fields.
	 *
	 * @param int|string $post_id Post ID.
	 * @param string     $meta_key Field key/post meta key.
	 * @param bool       $is_variation Whether the post is a variation.
	 * @param string     $type Field type. number|date|string.
	 *
	 * @return void
	 */
	public function save_product_field( $post_id, string $meta_key, bool $is_variation = false, string $type = 'number' ) {
		if ( $is_variation ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = sanitize_text_field( wp_unslash( $_POST[ $meta_key ][ $post_id ] ?? '' ) );
		} else {
			//phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ?? '' ) );
		}

		if ( empty( $value ) ) {
			update_post_meta( $post_id, $meta_key, '' );

			return;
		}

		switch ( $type ) {
			case 'number':
				$value = wc_format_decimal( trim( stripslashes( $value ) ), 2, false );
				break;
			case 'date':
				$value = \WC_DateTime::createFromFormat( 'Y-m-d H:i:s', "$value 00:00:00" );
				$value = $value ? $value->getTimestamp() : 0;
				break;
			default:
				$value = sanitize_text_field( $value );
		}

		update_post_meta( $post_id, $meta_key, $value );
	}

	/**
	 * Add notice if necessary.
	 *
	 * @return void
	 */
	public function edit_product_omnibus_notice() {
		global $pagenow;
		global $post;

		if ( 'post-new.php' !== $pagenow && 'post.php' !== $pagenow ) {
			return;
		}

		if ( 'product' !== get_post_type( $post ) || ! ECOM_OMNIBUS()->is_omnibus_warning_enabled() ) {
			return;
		}

		$product = wc_get_product( $post );

		$default_product_types_with_omnibus = array(
			'simple',
			'variable',
			'external',
		);

		$product_types_with_omnibus = apply_filters( 'ecom_wc_omnibus_product_types', $default_product_types_with_omnibus );

		if ( ! in_array( $product->get_type(), $product_types_with_omnibus, true ) ) {
			return;
		}

		if ( $product->is_type( 'variable' ) ) {
			$variation_ids = $product->get_visible_children();

			foreach ( $variation_ids as $variation_id ) {
				$variation = wc_get_product( $variation_id );
				if ( $variation->is_on_sale() && ! $this::get_product_omnibus_price( $variation ) ) {
					echo '<div class="notice notice-error">';
					echo '<p>' . esc_html__( 'Product variation is on sale without omnibus price.', 'ecom-wc-omnibus' ) . '</p>';
					echo '</div>';
					break;
				}
			}
		} else {
			if ( $product->is_on_sale() && ! $this::get_product_omnibus_price( $product ) ) {
				echo '<div class="notice notice-error">';
				echo '<p>' . esc_html__( 'Product is on sale without omnibus price.', 'ecom-wc-omnibus' ) . '</p>';
				echo '</div>';
			}
		}
	}

	/**
	 * Get product omnibus price.
	 *
	 * @param \WC_Product|int|string $product Product object or ID.
	 *
	 * @return string|false Omnibus price or false if one doesn't exist.
	 */
	public static function get_product_omnibus_price( $product ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		if ( empty( $product ) ) {
			return false;
		}

		return $product->get_meta( self::OMNIBUS_PRICE_KEY );
	}

	/**
	 * Get product omnibus date.
	 *
	 * @param \WC_Product|int|string $product Product object or ID.
	 *
	 * @return int|false Omnibus unix timestamp or false if one doesn't exist.
	 */
	public static function get_product_omnibus_timestamp( $product ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		if ( empty( $product ) ) {
			return false;
		}

		return $product->get_meta( self::OMNIBUS_DATE_KEY );
	}

	/**
	 * Get product omnibus date.
	 *
	 * @param \WC_Product|int|string $product Product object or ID.
	 *
	 * @return \WC_DateTime|false Omnibus date or false if one doesn't exist or omnibus timestamp cannot be parsed.
	 */
	public static function get_product_omnibus_date( $product ) {
		$timestamp = self::get_product_omnibus_timestamp( $product );

		if ( empty( $timestamp ) ) {
			return false;
		}

		$date = new WC_DateTime();
		$date->setTimestamp( $timestamp );

		return $date;
	}

	/**
	 * Print missing requirement admin notice.
	 *
	 * @return void
	 */
	public function wc_omnibus_admin_notice() {
		if ( function_exists( 'WC' ) ) {
			return;
		}

		printf(
			'<div class="%1$s"><p>%2$s</p></div><button type="button" class="notice-dismiss">
		<span class="screen-reader-text">%3$s</span>
	</button>',
			esc_attr( 'notice notice-error is-dismissible' ),
			esc_html__( 'WC Omnibus requires WooCommerce to be installed and active.', 'ecom-wc-omnibus' ),
			esc_html__( 'Dismiss this notice.', 'ecom-wc-omnibus' )
		);
	}

	/**
	 * Add this plugin to ecom plugins list.
	 *
	 * @param array $array Existing plugins.
	 *
	 * @return array
	 */
	public function add_to_ecom_plugins_list( array $array ): array {
		$array[] = $this->plugin_name;

		return $array;
	}

	/**
	 * Add plugin actions links.
	 *
	 * @param array $actions Existing actions.
	 *
	 * @return mixed
	 */
	public function add_action_links( array $actions ): array {
		if ( ! function_exists( 'WC' ) ) {
			return $actions;
		}

		$actions[] = sprintf(
			'<a href="%s">%s</a>',
			esc_attr( ECOM_OMNIBUS()->get_settings_manager()->get_settings_tab_url() ),
			esc_html__( 'Settings', 'ecom-wc-omnibus' )
		);

		return $actions;
	}
}
