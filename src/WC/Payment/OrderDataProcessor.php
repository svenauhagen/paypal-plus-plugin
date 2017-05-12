<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 18.01.17
 * Time: 17:07
 */

namespace WCPayPalPlus\WC\Payment;

trait OrderDataProcessor {

	/**
	 * Wrap around number_format() to return country-specific decimal numbers.
	 *
	 * @param float $price The unformatted price.
	 *
	 * @return float
	 */
	protected function format( $price ) {

		$decimals = 2;

		if ( $this->currency_has_decimals() ) {
			$decimals = 0;
		}

		return number_format( $price, $decimals, '.', '' );
	}

	/**
	 * Checks if the currency supports decimals.
	 *
	 * @return bool
	 */
	private function currency_has_decimals() {

		return in_array( get_woocommerce_currency(), [ 'HUF', 'JPY', 'TWD' ], true );

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

		if ( $this->currency_has_decimals() ) {
			$precision = 0;
		}

		return round( $price, $precision );

	}
}