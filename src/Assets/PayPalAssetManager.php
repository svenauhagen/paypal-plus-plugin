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
        if (!isGatewayDisabled($this->expressCheckoutGateway)) {
            wp_enqueue_script(
                'paypal-express-checkout',
                'https://www.paypalobjects.com/api/checkout.js',
                [],
                null,
                true
            );
        }

        if ($this->isCheckout() && !isGatewayDisabled($this->plusGateway)) {
            wp_enqueue_script(
                'ppplus',
                'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
                [],
                null
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
