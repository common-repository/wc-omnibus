<?php
/**
 * The template for displaying omnibus variation price.
 *
 * @package Ecom_Wc_Omnibus/Templates/Ecom
 */

defined( 'ABSPATH' ) || exit;

?>

<script type="text/template" id="tmpl-ecom-omnibus-variation-price">
	<# if ( data.enabled ) { #>
	<div class="ecom-omnibus__price">
			<span class="text">
			<# if ( data.date ) { #>
				<?php
				echo wp_kses_post(
					sprintf(
					// translators: %1$s is the price, %2$s is the separator, %3$s is the date.
						__( 'Lowest price: %1$s %2$s %3$s', 'ecom-wc-omnibus' ),
						'{{{ data.wc_price }}}',
						'<span class="separator">' . apply_filters( 'ecom_wc_omnibus_price_separator', '@' ) . '</span>',
						'<span class="date"> {{{ data.date }}} </span>'

					)
				);
				?>
			<# } else { #>
				<?php
				echo wp_kses_post(
					sprintf(
					// translators: %1$s is the price.
						__( 'Lowest recent price: %1$s', 'ecom-wc-omnibus' ),
						'{{{ data.wc_price }}}'
					)
				);
				?>
			<# } #>
			</span>
	</div>
	<# } #>
</script>
