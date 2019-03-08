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

use WCPayPalPlus\PluginProperties;

/**
 * Class PayPalAssetManager
 * @package WCPayPalPlus\Assets
 */
class PayPalAssetManager
{
    use AssetManagerTrait;

    /**
     * @var PayPalSdkScriptArguments
     */
    private $sdkArguments;

    /**
     * PayPalAssetManager constructor.
     * @param PluginProperties $pluginProperties
     * @param PayPalSdkScriptArguments $sdkArguments
     */
    public function __construct(
        PluginProperties $pluginProperties,
        PayPalSdkScriptArguments $sdkArguments
    ) {

        $this->pluginProperties = $pluginProperties;
        $this->sdkArguments = $sdkArguments;
    }

    /**
     * Enqueue PayPal FrontEnd Scripts
     */
    public function enqueueFrontEndScripts()
    {
        $url = add_query_arg(
            $this->sdkArguments->toArray(),
            'https://www.paypal.com/sdk/js'
        );
        wp_enqueue_script('ppplus-express-checkout', $url, [], null, true);

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
