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

use WCPayPalPlus\Payment\Session;

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
     * CheckoutGatewayOverride constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Overwrite Payment Gateways
     * @param array $availableGateways
     *
     * @return array
     */
    public function maybeOverride(array $availableGateways)
    {
        if (!isset($availableGateways[Gateway::GATEWAY_ID])) {
            return $availableGateways;
        }

        $gateway = $availableGateways[Gateway::GATEWAY_ID];
        unset($availableGateways[Gateway::GATEWAY_ID]);

        if (Gateway::GATEWAY_ID === $this->session->get(Session::CHOSEN_PAYMENT_METHOD)) {
            $availableGateways = [
                Gateway::GATEWAY_ID => $gateway,
            ];
        }

        return $availableGateways;
    }
}
