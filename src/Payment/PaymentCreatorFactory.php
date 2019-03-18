<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting\Storable;
use WC_Order;
use WC_Order_Refund;
use RuntimeException;
use Exception;
use WooCommerce;

/**
 * Class PaymentCreatorFactory
 * @package WCPayPalPlus\Payment
 */
class PaymentCreatorFactory
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var Session
     */
    private $session;

    /**
     * PaymentCreatorFactory constructor.
     * @param WooCommerce $wooCommerce
     * @param OrderFactory $orderFactory
     * @param Session $session
     */
    public function __construct(
        WooCommerce $wooCommerce,
        OrderFactory $orderFactory,
        Session $session
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->orderFactory = $orderFactory;
        $this->session = $session;
    }

    /**
     * @param Storable $settings
     * @param $returnUrl
     * @param $notifyUrl
     * @return PaymentCreator
     */
    public function create(Storable $settings, $returnUrl, $notifyUrl)
    {
        assert(is_string($returnUrl));
        assert(is_string($notifyUrl));

        try {
            $orderData = $this->retrieveOrderByRequest($this->session);
            $orderData = new OrderData($orderData);
        } catch (Exception $exc) {
            $orderData = new CartData($this->wooCommerce->cart);
        }

        $cancelUrl = $settings->cancelUrl();
        $experienceProfile = $settings->experienceProfileId();

        $paymentData = new PaymentData(
            $returnUrl,
            $cancelUrl,
            $notifyUrl,
            $experienceProfile,
            ApiContextFactory::getFromConfiguration()
        );

        return new PaymentCreator($paymentData, $orderData);
    }

    /**
     * @param Session $session
     * @return WC_Order|WC_Order_Refund
     * @throws RuntimeException
     */
    private function retrieveOrderByRequest(Session $session)
    {
        $key = filter_input(INPUT_GET, 'key');

        if (!$key) {
            throw new RuntimeException('Key for order not provided by the current request.');
        }

        $order = $this->orderFactory->createByOrderKey($key);

        // TODO Understand why the ppp_order_id is set twice.
        $session->set(Session::ORDER_ID, $order->get_id());

        return $order;
    }
}
