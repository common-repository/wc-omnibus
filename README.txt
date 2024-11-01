=== Ecom WC Omnibus ===
Plugin Name: Ecom WC Omnibus
Plugin Slug: wc-omnibus
Text Domain: ecom-wc-omnibus
Domain Path: /languages
Contributors: rasmusjaa, saggre
Tags: omnibus, woocommerce, eu, directive, gdpr, product, price, discount, sale, custom fields, product, ecom
Requires at least: 5.2
Tested Up To: 6.0
Requires PHP: 7.2
Stable tag: 1.0.1
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Set and display the lowest price within 30 days before the sale for a product that is on sale, as requested by the Omnibus Directive.

== Description ==

WC Omnibus allows you to set a custom omnibus price and date for WooCommerce products and variable products and show it on the product page when the product is on sale.

== Installation ==

1. Upload `ecom-wc-omnibus` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. On product edit page add omnibus price and date (optional) for products. This is price is shown for products that are on sale. The price be the lowest price that the product was sold for within 30 days before the sale.
4. Options page is /wp-admin/admin.php?page=ecom-settings and you can enable showing the omnibus price for sale products on front end (default on), enable a warning on product edit page if the product is on sale without omnibus price (default on) and select hooks where the omnibus price is sold on front end (separate for single and variable products)

== Shortcodes ==

`[ecom_wc_omnibus_price]`

Accepted parameters:
type: "simple" | "variable"
For example:

`[ecom_wc_omnibus_price type="variable"]`

Show the omnibus price for current product page. Automatically determines if page is for a variable product, unless specified with type attribute. On variable product page the omnibus price for the variation is shown when the variation is selected.

== Global functions ==

`ecom_omnibus_price();`

Adds omnibus price for a single product. Adds nothing if on variable product page.

`ecom_omnibus_single_variation_price();`

Adds omnibus price for a variable product. Adds an empty tag if not on a variable product page. The omnibus price for the variation is shown when the variation is selected.

== Frequently Asked Questions ==

= When is the omnibus price shown? =

When the product or a variable has a sale price and an omnibus price.

= What is the omnibus date used for? =

It is optional and for your convenience and for the customer to see. You can set the date there when making changes and check that the date is 30 days before the sale started. The customer can also see when the lowest price before the sale was offered.

= Can the plugin fetch the lowest price from last 30 days automatically? =

Not at the moment, but automatic price change log for last 30 days and option to use lowest price automatically is being planned for future release.

= How do I translate the omnibus notification to my language? =

Install a string translation plugin like Loco Translate or WPML and edit the plugin's translations.

= How do I change the displayed date format? =

Option 1: Use the date format option in WordPress general settings.

Option 2: If you only want to change the date format for the omnibus price, you can use the following filter:

`add_filter( 'ecom_wc_omnibus_date_format', fn( $format ) => 'Y-m-d' );`

== Screenshots ==

1. WC Omnibus settings page
2. Simple product edit page with omnibus price and date set
3. Variable product edit page with omnibus price set and no date
4. Simple product page with omnibus price and date
5. Variable product page with omnibus price and no date

== Changelog ==

= 1.0.1 =
6.6.2022
* Use WordPress date format for omnibus price date
* Add shortcodes for showing omnibus price on custom product pages and templates
* Add support for multiple omnibus hooks for single and variable products
* Misc code quality fixes and more documentation

= 1.0.0 =
27.5.2022
* First version of the plugin ready
