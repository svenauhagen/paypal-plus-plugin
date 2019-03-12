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

use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Payment\Session;

/**
 * Class Session
 */
class StorePaymentData
{
    /**
     * @var \WooCommerce
     */
    private $woocommerce;

    /**
     * CheckoutGatewayOverride constructor.
     *
     * @param \WooCommerce $woocommerce
     */
    public function __construct(\WooCommerce $woocommerce)
    {
        $this->woocommerce = $woocommerce;
    }

    /**
     * Store Payment data from filter
     *
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function addFromFilter(Array $data)
    {
        $this->woocommerce->session->set(Session::PAYER_ID, $data[CartCheckout::INPUT_PAYER_ID_NAME]);
        $this->woocommerce->session->set(Session::PAYMENT_ID, $data[CartCheckout::INPUT_PAYMENT_ID_NAME]);
        $this->woocommerce->session->set(Session::CHOSEN_PAYMENT_METHOD, Gateway::GATEWAY_ID);
        $this->storeAddressToCart($data[CartCheckout::INPUT_PAYMENT_ID_NAME]);
        return $data;
    }

    /**
     * @param string $paymentId
     *
     * @throws \Exception
     */
    public function storeAddressToCart($paymentId)
    {
        \assert(is_string($paymentId));

        $apiContext = ApiContextFactory::getFromConfiguration();

        $this->woocommerce->customer->set_billing_address_1('test');
        $this->woocommerce->customer->set_shipping_address('test');
        $this->woocommerce->customer->save();
    }
}
