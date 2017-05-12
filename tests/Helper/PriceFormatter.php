<?php # -*- coding: utf-8 -*-
namespace WCPayPalPlus\Test;

class PriceFormatter {

	public static function format( $price ) {

		$decimals = 2;

		return number_format( $price, $decimals, '.', '' );

	}
}