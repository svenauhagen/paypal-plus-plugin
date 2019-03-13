<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Setting;

/**
 * Trait SharedFieldsOptionsTrait
 * @package WCPayPalPlus\Setting
 */
trait SharedFieldsOptionsTrait
{
    /**
     * A list of Options for the Cancel Page Setting
     *
     * @return array
     */
    private function cancelPageOptions()
    {
        return [
            'cart' => esc_html_x('Cart', 'shared-setting', 'woo-paypalplus'),
            'checkout' => esc_html_x('Checkout', 'shared-setting', 'woo-paypalplus'),
            'account' => esc_html_x('Account', 'shared-setting', 'woo-paypalplus'),
            'shop' => esc_html_x('Shop', 'shared-setting', 'woo-paypalplus'),
            'custom' => esc_html_x('Custom', 'shared-setting', 'woo-paypalplus'),
        ];
    }
}
