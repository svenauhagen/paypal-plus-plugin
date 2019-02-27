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

/**
 * Class AssetManager
 * @package WCPayPalPlus\Assets
 */
class AssetManager
{
    /**
     * @var PluginProperties
     */
    private $pluginProperties;

    /**
     * AssetManager constructor.
     * @param PluginProperties $pluginProperties
     */
    public function __construct(PluginProperties $pluginProperties)
    {
        $this->pluginProperties = $pluginProperties;
    }

    /**
     * Enqueue Admin Scripts
     */
    public function enqueueAdminScripts()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_script(
            'paypalplus-woocommerce-admin',
            "{$assetUrl}/public/js/admin.min.js",
            [],
            filemtime("{$assetPath}/public/js/admin.min.js"),
            true
        );
    }

    /**
     * Enqueue Admin Styles
     */
    public function enqueueAdminStyles()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_style(
            'paypalplus-woocommerce-admin',
            "{$assetUrl}/public/css/admin.min.css",
            [],
            filemtime("{$assetPath}/public/css/admin.min.css"),
            'screen'
        );
    }

    /**
     * Enqueue Frontend Scripts
     */
    public function enqueueFrontEndScripts()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_script(
            'paypalplus-woocommerce-front',
            "{$assetUrl}/public/js/front.min.js",
            ['jquery'],
            filemtime("{$assetPath}/public/js/front.min.js"),
            true
        );

        if (is_checkout() || is_checkout_pay_page()) {
            wp_enqueue_script(
                'ppplus-js',
                'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
                [],
                null
            );
        }
    }

    /**
     * Enqueue Frontend Styles
     */
    public function enqueueFrontendStyles()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_style(
            'paypalplus-woocommerce-front',
            "{$assetUrl}/public/css/front.min.css",
            [],
            filemtime("{$assetPath}/public/css/front.min.css"),
            'screen'
        );
    }

    /**
     * Retrieve the assets and url path for scripts
     *
     * @return array
     */
    private function assetUrlPath()
    {
        $assetPath = untrailingslashit($this->pluginProperties->dirPath());
        $assetUrl = untrailingslashit($this->pluginProperties->dirUrl());

        return [
            $assetPath,
            $assetUrl,
        ];
    }
}
