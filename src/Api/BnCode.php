<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Api;

use WCPayPalPlus\Payment\Session;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WCPayPalPlus\PlusGateway\Gateway as PlusGateway;

/**
 * Class BnCode
 * @package WCPayPalPlus\Api
 */
class BnCode
{
    const FILTER_API_BN_CODE = 'woopaypalplus.api_bncode';

    const PAYPAL_PLUS_BN_CODE = 'WooCommerce_Cart_Plus';
    const PAYPAL_EXPRESS_CHECKOUT_BN_CODE = 'Woo_Cart_ECS';
    const PAYPAL_DEFAULT_BN_CODE = self::PAYPAL_PLUS_BN_CODE;

    const PAYMENT_METHODS = [
        PlusGateway::GATEWAY_ID => self::PAYPAL_PLUS_BN_CODE,
        ExpressCheckoutGateway::GATEWAY_ID => self::PAYPAL_EXPRESS_CHECKOUT_BN_CODE,
    ];

    /**
     * @var Session
     */
    private $session;

    /**
     * BnCode constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function bnCode()
    {
        $paymentMethod = (string)$this->session->get(Session::CHOSEN_PAYMENT_METHOD);

        if (!array_key_exists($paymentMethod, self::PAYMENT_METHODS)) {
            return self::PAYPAL_DEFAULT_BN_CODE;
        }

        return self::PAYMENT_METHODS[$paymentMethod];
    }
}
