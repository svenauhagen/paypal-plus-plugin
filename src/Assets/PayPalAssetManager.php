<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Assets;

use function WCPayPalPlus\areAllExpressCheckoutButtonsDisabled;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use function WCPayPalPlus\isGatewayDisabled;
use WCPayPalPlus\PlusGateway\Gateway as PlusGateway;

/**
 * Class PayPalAssetManager
 * @package WCPayPalPlus\Assets
 */
class PayPalAssetManager
{
    use AssetManagerTrait;

    /**
     * @var ExpressCheckoutGateway
     */
    private $expressCheckoutGateway;

    /**
     * @var PlusGateway
     */
    private $plusGateway;

    /**
     * PayPalAssetManager constructor.
     * @param ExpressCheckoutGateway $expressCheckoutGateway
     * @param PlusGateway $plusGateway
     */
    public function __construct(
        ExpressCheckoutGateway $expressCheckoutGateway,
        PlusGateway $plusGateway
    ) {

        $this->expressCheckoutGateway = $expressCheckoutGateway;
        $this->plusGateway = $plusGateway;
    }

    /**
     * Enqueue PayPal FrontEnd Scripts
     */
    public function enqueueFrontEndScripts()
    {
        $uploadDir = wp_upload_dir();
        $uploadBaseDir = isset($uploadDir['basedir']) ? $uploadDir['basedir'] : '';
        $uploadUrl = isset($uploadDir['baseurl']) ? $uploadDir['baseurl'] : '';

        if (!$uploadBaseDir || !$uploadUrl) {
            return;
        }

        if (!isGatewayDisabled($this->expressCheckoutGateway)
            && !areAllExpressCheckoutButtonsDisabled()
        ) {
            wp_enqueue_script(
                'paypal-express-checkout',
                "{$uploadUrl}/woo-paypalplus/resources/js/paypal/expressCheckout.min.js",
                [],
                filemtime(
                    "{$uploadBaseDir}/woo-paypalplus/resources/js/paypal/expressCheckout.min.js"
                ),
                true
            );
        }

        if ($this->isCheckout() && !isGatewayDisabled($this->plusGateway)) {
            wp_enqueue_script(
                'ppplus',
                "{$uploadUrl}/woo-paypalplus/resources/js/paypal/payPalplus.min.js",
                [],
                filemtime("{$uploadBaseDir}/woo-paypalplus/resources/js/paypal/payPalplus.min.js"),
                true
            );
        }
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
