<?php
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

class AssetManager
{
    private $pluginProperties;

    public function __construct(PluginProperties $pluginProperties)
    {
        $this->pluginProperties = $pluginProperties;
    }

    public function enqueueAdminScripts()
    {
        $assetUrl = untrailingslashit($this->pluginProperties->dirUrl());
        $min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        $adminScript = "{$assetUrl}/public/js/admin{$min}.js";

        wp_enqueue_script('paypalplus-woocommerce-admin', $adminScript, ['jquery']);
    }

    public function enqueueAdminStyles()
    {
        $assetUrl = untrailingslashit($this->pluginProperties->dirUrl());
        $min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        $adminStyle = "{$assetUrl}/public/css/admin{$min}.css";

        wp_enqueue_style('paypalplus-woocommerce-admin', $adminStyle, []);
    }

    public function enqueueFrontEndScripts()
    {
        if (is_checkout() || is_checkout_pay_page()) {
            wp_enqueue_script(
                'ppplus-js',
                'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
                [],
                null
            );
        }
    }
}
