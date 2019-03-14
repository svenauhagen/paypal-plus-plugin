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
 * Interface ExpressCheckoutStorable
 * @package WCPayPalPlus\Setting
 */
interface ExpressCheckoutStorable extends Storable
{
    const OPTION_SHOW_ON_PRODUCT_PAGE = 'show_on_product_page';
    const OPTION_SHOW_ON_MINI_CART = 'show_on_mini_cart';
    const OPTION_SHOW_ON_CART = 'show_on_cart';

    /**
     * @return bool
     */
    public function showOnProductPage();

    /**
     * @return bool
     */
    public function showOnMiniCart();

    /**
     * @return bool
     */
    public function showOnCart();
}
