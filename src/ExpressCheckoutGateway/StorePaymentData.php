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

use WCPayPalPlus\Session\Session;
use WooCommerce;
use OutOfBoundsException;
use Exception;

/**
 * Class Session
 */
class StorePaymentData
{
    /**
     * @var WooCommerce
     */
    private $woocommerce;

    /**
     * @var Session
     */
    private $session;

    /**
     * StorePaymentData constructor.
     * @param WooCommerce $woocommerce
     * @param Session $session
     */
    public function __construct(WooCommerce $woocommerce, Session $session)
    {
        $this->woocommerce = $woocommerce;
        $this->session = $session;
    }

    /**
     * Store Payment data
     *
     * @param $payerId
     * @param $paymentId
     * @throws OutOfBoundsException
     * @throws Exception
     */
    public function addFromAction($payerId, $paymentId)
    {
        $this->session->set(Session::PAYER_ID, $payerId);
        $this->session->set(Session::PAYMENT_ID, $paymentId);
        $this->session->set(Session::CHOSEN_PAYMENT_METHOD, Gateway::GATEWAY_ID);
    }
}
