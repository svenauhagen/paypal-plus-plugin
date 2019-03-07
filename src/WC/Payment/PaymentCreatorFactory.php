<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC\Payment;

use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Ipn\Ipn;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\PlusGateway\Gateway;
use WC_Payment_Gateway;
use WC_Order;
use WC_Order_Refund;
use RuntimeException;
use Exception;

/**
 * Class PaymentCreatorFactory
 * @package WCPayPalPlus\WC\Payment
 */
class PaymentCreatorFactory
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * PaymentFactory constructor.
     * @param OrderFactory $orderFactory
     */
    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param PlusStorable $settings
     * @param WC_Payment_Gateway $gateway
     * @param Session $session
     * @return PaymentCreator
     */
    public function create(PlusStorable $settings, WC_Payment_Gateway $gateway, Session $session)
    {
        try {
            $orderData = $this->retrieveOrderByRequest($session);
            $orderData = new OrderData($orderData);
        } catch (Exception $exc) {
            $orderData = new CartData(wc()->cart);
        }

        $returnUrl = wc()->api_request_url($gateway->id);
        $notifyUrl = wc()->api_request_url(Gateway::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX);
        $cancelUrl = $settings->cancelUrl();
        $experienceProfile = $settings->experienceProfileId();

        $paymentData = new PaymentData(
            $returnUrl,
            $cancelUrl,
            $notifyUrl,
            $experienceProfile,
            ApiContextFactory::get()
        );

        return new PaymentCreator($paymentData, $orderData);
    }

    /**
     * @param Session $session
     * @return WC_Order|WC_Order_Refund
     * @throws \RuntimeException
     */
    public function retrieveOrderByRequest(Session $session)
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
