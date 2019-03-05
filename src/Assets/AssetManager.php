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

use WCPayPalPlus\ExpressCheckout\AjaxHandler;
use WCPayPalPlus\PluginProperties;

/**
 * Class AssetManager
 * @package WCPayPalPlus\Assets
 */
class AssetManager
{
    use AssetManagerTrait;

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
            ['underscore', 'jquery', 'ppplus-express-checkout'],
            filemtime("{$assetPath}/public/js/front.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-woocommerce-front',
            'wooPayPalPlusExpressCheckout',
            [
                'validContexts' => AjaxHandler::VALID_CONTEXTS,
                'request' => [
                    'action' => AjaxHandler::ACTION,
                    'ajaxUrl' => home_url('/wp-admin/admin-ajax.php'),
                ],
            ]
        );

        $this->enqueuePayPalFrontEndScripts();
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
     * Enqueue PayPal Specific Scripts
     */
    private function enqueuePayPalFrontEndScripts()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_register_script(
            'paypalplus-woocommerce-plus-paypal-redirect',
            "{$assetUrl}/public/js/payPalRedirect.min.js",
            ['jquery'],
            filemtime("{$assetPath}/public/js/payPalRedirect.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-woocommerce-plus-paypal-redirect',
            'payPalRedirect',
            [
                'message' => __(
                    'Thank you for your order. We are now redirecting you to PayPal to make payment.',
                    'woo-paypalplus'
                ),
            ]
        );
    }

    /**
     * Localize Scripts
     * @param $handle
     * @param $objName
     * @param array $data
     */
    private function loadScriptsData($handle, $objName, array $data)
    {
        assert(is_string($handle));
        assert(is_string($objName));

        wp_localize_script($handle, $objName, $data);
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
