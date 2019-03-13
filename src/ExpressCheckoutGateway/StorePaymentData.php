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

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Api\Transaction;
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
    public function addFromAction(Array $data)
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
        $payment = Payment::get($paymentId, $apiContext);
        $payer = $payment->getPayer();
        $payerInfo = $payer->getPayerInfo();
        $billingAddress = $payerInfo->getBillingAddress();
        $transactions = $payment->getTransactions();
        if (!$transactions || ! $transactions[0] instanceof Transaction) {
            return;
        }
        $itemList = $transactions[0]->getItemList();
        $shippingAddress = $itemList->getShippingAddress();

        $this->woocommerce->customer->set_billing_company('');
        $this->woocommerce->customer->set_billing_first_name($payerInfo->getFirstName());
        $this->woocommerce->customer->set_billing_last_name($payerInfo->getLastName());
        $this->woocommerce->customer->set_billing_email($payerInfo->getEmail());
        $this->woocommerce->customer->set_billing_phone($payerInfo->getPhone());
        $this->woocommerce->customer->set_billing_address_1('');
        $this->woocommerce->customer->set_billing_address_2('');
        $this->woocommerce->customer->set_billing_city('');
        $this->woocommerce->customer->set_billing_country($payerInfo->getCountryCode());
        $this->woocommerce->customer->set_billing_postcode('');
        $this->woocommerce->customer->set_billing_state($payment->getState());
        if ($billingAddress) {
            $this->woocommerce->customer->set_billing_address_1($billingAddress->getLine1());
            $this->woocommerce->customer->set_billing_address_2($billingAddress->getLine2());
            $this->woocommerce->customer->set_billing_city($billingAddress->getCity());
            $this->woocommerce->customer->set_billing_country($billingAddress->getCountryCode());
            $this->woocommerce->customer->set_billing_postcode($billingAddress->getPostalCode());
            $this->woocommerce->customer->set_billing_state($billingAddress->getState());
        }

        $this->woocommerce->customer->set_shipping_company('');
        list($firstName, $lastName) = explode(' ', $shippingAddress->getRecipientName(), 2);
        $this->woocommerce->customer->set_shipping_first_name($firstName);
        $this->woocommerce->customer->set_shipping_last_name($lastName);
        $this->woocommerce->customer->set_shipping_address_1($shippingAddress->getLine1());
        $this->woocommerce->customer->set_shipping_address_2($shippingAddress->getLine2());
        $this->woocommerce->customer->set_shipping_city($shippingAddress->getCity());
        $this->woocommerce->customer->set_shipping_country($shippingAddress->getCountryCode());
        $this->woocommerce->customer->set_shipping_postcode($shippingAddress->getPostalCode());
        $this->woocommerce->customer->set_shipping_state($shippingAddress->getState());

        $this->woocommerce->customer->save();
    }
}
