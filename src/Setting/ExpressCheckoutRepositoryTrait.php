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
 * Trait ExpressCheckoutRepositoryTrait
 * @package WCPayPalPlus\Setting
 */
trait ExpressCheckoutRepositoryTrait
{
    /**
     * @return bool
     */
    public function showOnProductPage()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_SHOW_ON_PRODUCT_PAGE,
            Storable::OPTION_ON
        );

        return $option === Storable::OPTION_ON;
    }

    /**
     * @return bool
     */
    public function showOnMiniCart()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_SHOW_ON_MINI_CART,
            Storable::OPTION_ON
        );

        return $option === Storable::OPTION_ON;
    }

    /**
     * @return bool
     */
    public function showOnCart()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_SHOW_ON_CART,
            Storable::OPTION_ON
        );

        return $option === Storable::OPTION_ON;
    }
}
