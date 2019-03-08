<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Payment;

/**
 * Trait OrderDataProcessor
 * @package WCPayPalPlus\Payment
 */
trait OrderDataProcessor
{
    /**
     * Wrap around number_format() to return country-specific decimal numbers.
     *
     * @param float $price The unformatted price.
     *
     * @return float
     */
    private function format($price)
    {
        $decimals = 2;

        if ($this->currency_has_decimals()) {
            $decimals = 0;
        }

        return number_format($price, $decimals, '.', '');
    }

    /**
     * Checks if the currency supports decimals.
     *
     * @return bool
     */
    private function currency_has_decimals()
    {
        return in_array(get_woocommerce_currency(), ['HUF', 'JPY', 'TWD'], true);
    }

    /**
     * Rounds a price to 2 decimals.
     *
     * @param float $price The item price.
     *
     * @return float
     */
    private function round($price)
    {
        $precision = 2;

        if ($this->currency_has_decimals()) {
            $precision = 0;
        }

        return round($price, $precision);
    }
}
