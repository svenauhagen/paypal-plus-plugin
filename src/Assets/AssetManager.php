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

use WCPayPalPlus\ExpressCheckoutGateway\AjaxHandler;
use WCPayPalPlus\PluginProperties;
use WCPayPalPlus\Session\Session;

/**
 * Class AssetManager
 * @package WCPayPalPlus\Assets
 */
class AssetManager
{
    use AssetManagerTrait;

    /**
     * @var PluginProperties
     */
    private $pluginProperties;

    /**
     * @var SmartButtonArguments
     */
    private $smartButtonArguments;

    /**
     * @var Session
     */
    private $session;

    /**
     * AssetManager constructor.
     * @param PluginProperties $pluginProperties
     * @param SmartButtonArguments $smartButtonArguments
     * @param Session $session
     */
    public function __construct(
        PluginProperties $pluginProperties,
        SmartButtonArguments $smartButtonArguments,
        Session $session
    ) {

        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->pluginProperties = $pluginProperties;
        $this->smartButtonArguments = $smartButtonArguments;
        $this->session = $session;
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
            ['underscore', 'jquery'],
            filemtime("{$assetPath}/public/js/front.min.js"),
            true
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

        $message = $this->session->get(Session::SESSION_INVALID_PAYMENT_EXECUTION)
            ? esc_html__(
                'Sorry, there was an error during the payment. We are now redirecting you to PayPal.',
                'woo-paypalplus'
            )
            : esc_html__(
                'Thank you for your order. We are now redirecting you to PayPal to make payment.',
                'woo-paypalplus'
            );

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
                'message' => $message,
            ]
        );

        wp_enqueue_script(
            'paypalplus-express-checkout',
            "{$assetUrl}/public/js/expressCheckout.min.js",
            ['underscore', 'jquery', 'paypal-express-checkout'],
            filemtime("{$assetPath}/public/js/expressCheckout.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-express-checkout',
            'wooPayPalPlusExpressCheckout',
            $this->expressCheckoutScriptData()
        );
    }

    /**
     * Build the Express Checkout Data
     *
     * @return array
     */
    private function expressCheckoutScriptData()
    {
        $data = [
            'validContexts' => AjaxHandler::VALID_CONTEXTS,
            'request' => [
                'action' => AjaxHandler::ACTION,
                'ajaxUrl' => home_url('/wp-admin/admin-ajax.php'),
            ],
        ];

        /** @noinspection AdditionOperationOnArraysInspection */
        return $data + $this->smartButtonArguments->toArray();
    }
}
