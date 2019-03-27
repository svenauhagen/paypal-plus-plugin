<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\Session\Session;

/**
 * Class CheckoutGatewayOverride
 */
class CheckoutGatewayOverride
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * CheckoutGatewayOverride constructor.
     * @param Session $session
     * @param CurrentPaymentMethod $currentPaymentMethod
     */
    public function __construct(Session $session, CurrentPaymentMethod $currentPaymentMethod)
    {
        $this->session = $session;
        $this->currentPaymentMethod = $currentPaymentMethod;
    }

    /**
     * Overwrite Payment Gateways
     * @param array $availableGateways
     *
     * @return array
     */
    public function maybeOverridden(array $availableGateways)
    {
        if (!isset($availableGateways[Gateway::GATEWAY_ID])) {
            return $availableGateways;
        }

        $gateway = $availableGateways[Gateway::GATEWAY_ID];
        unset($availableGateways[Gateway::GATEWAY_ID]);

        if (Gateway::GATEWAY_ID === $this->currentPaymentMethod->payment()) {
            $availableGateways = [
                Gateway::GATEWAY_ID => $gateway,
            ];
        }

        return $availableGateways;
    }

    /**
     * Clean Session when User get out By the Express Checkout Page
     *
     * The session is cleared only if the refer page is including the checkout url and only if
     * the current page isn't the checkout it self or the checkout pay page.
     *
     * Users will then be able to choose a different payment method if they want.
     *
     * @return void
     */
    public function maybeReset()
    {
        $refer = wp_get_referer();
        $checkoutPageUrl = wc_get_checkout_url();

        if (is_checkout() || is_checkout_pay_page()) {
            return;
        }

        if (strpos($refer, $checkoutPageUrl) !== false
            && Gateway::GATEWAY_ID === $this->currentPaymentMethod->payment()
        ) {
            $this->session->clean();
        }
    }
}
