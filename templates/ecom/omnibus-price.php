<?php
/**
 * The template for displaying omnibus price.
 *
 * @var \WC_DateTime|false $date
 * @var string|false       $price
 *
 * @package Ecom_Wc_Omnibus/Templates/Ecom
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $price ) ) {
	return;
}
?>

<div class="ecom-omnibus__price">
	<span class="text">
	<?php
	if ( empty( $date ) ) {
		echo wp_kses_post(
			sprintf(
			// translators: %1$s is the price.
				__( 'Lowest recent price: %1$s', 'ecom-wc-omnibus' ),
				wc_price( $price )
			)
		);
	} else {
		echo wp_kses_post(
			sprintf(
			// translators: %1$s is the price, %2$s is the separator, %3$s is the date.
				__( 'Lowest price: %1$s %2$s %3$s', 'ecom-wc-omnibus' ),
				wc_price( $price ),
				'<span class="separator">' . apply_filters( 'ecom_wc_omnibus_price_separator', '@' ) . '</span>',
				'<span class="date">' . $date->format( ecom_omnibus_get_date_format() ) . '</span>'
			)
		);
	}
	?>
		</span>
</div>
