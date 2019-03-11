<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Assets;

/**
 * Class PayPalAssetManager
 * @package WCPayPalPlus\Assets
 */
class PayPalAssetManager
{
    use AssetManagerTrait;

    /**
     * Enqueue PayPal FrontEnd Scripts
     */
    public function enqueueFrontEndScripts()
    {
        wp_enqueue_script(
            'ppplus-express-checkout',
            'https://www.paypalobjects.com/api/checkout.js',
            [],
            null,
            true
        );

        $this->isCheckout() and wp_enqueue_script(
            'ppplus',
            'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
            [],
            null
        );
    }

    /**
     * Is Checkout Page or not
     *
     * @return bool
     */
    private function isCheckout()
    {
        return is_checkout() || is_checkout_pay_page();
    }
}
