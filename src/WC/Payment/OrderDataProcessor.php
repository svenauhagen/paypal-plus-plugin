<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 18.01.17
 * Time: 17:07
 */

namespace PayPalPlusPlugin\WC\Payment;

trait OrderDataProcessor {

	/**
	 * Wrap around number_format() to return country-specific decimal numbers.
	 *
	 * @param float $price The unformatted price.
	 *
	 * @return string
	 */
	protected function format( $price ) {

		$decimals = 2;

		if ( in_array( get_woocommerce_currency(), [ 'HUF', 'JPY', 'TWD' ], true ) ) {
			$decimals = 0;
		}

		return number_format( $price, $decimals, '.', '' );
	}

	/**
	 * Rounds a price to 2 decimals.
	 *
	 * @param float $price The item price.
	 *
	 * @return float
	 */
	protected function round( $price ) {

		$precision = 2;

		if ( in_array( get_woocommerce_currency(), [ 'HUF', 'JPY', 'TWD' ], true ) ) {
			$precision = 0;
		}

		return round( $price, $precision );

	}
}