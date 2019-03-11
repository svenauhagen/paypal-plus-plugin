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

use WooCommerce;

/**
 * Class DefaultGatewayOverride
 *
 * Overrides the default Payment Gateway ONCE per user session.
 *
 * Hence, it should never override user input.
 */
class CheckoutGatewayOverride
{

    public function __construct(WooCommerce $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }

    /**
     * Overwrite Payment Gateways
     * @param array $availableGateways
     *
     * @return array
     */
    public function maybeOverride(array $availableGateways)
    {
        if (! isset($availableGateways[Gateway::GATEWAY_ID])) {
            return $availableGateways;
        }

        $ecGateway = $availableGateways[Gateway::GATEWAY_ID];
        unset($availableGateways[Gateway::GATEWAY_ID]);

        if (Gateway::GATEWAY_ID === $this->wooCommerce->session->get('chosen_payment_method')) {
            $availableGateways = [
                Gateway::GATEWAY_ID => $ecGateway,
            ];
        }

        return $availableGateways;
    }
}
