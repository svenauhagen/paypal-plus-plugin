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
        $this->storeAddressToCart($paymentId);
    }

    /**
     * @param string $paymentId
     *
     * @throws \Exception
     */
    public function storeAddressToCart($paymentId)
    {
        \assert(is_string($paymentId));

        $customer = $this->woocommerce->customer;
        $apiContext = ApiContextFactory::getFromConfiguration();
        $payment = Payment::get($paymentId, $apiContext);
        $payer = $payment->getPayer();
        $payerInfo = $payer->getPayerInfo();
        $billingAddress = $payerInfo->getBillingAddress();

        $customer->set_billing_company('');
        $customer->set_billing_first_name($payerInfo->getFirstName());
        $customer->set_billing_last_name($payerInfo->getLastName());
        $customer->set_billing_email($payerInfo->getEmail());
        $customer->set_billing_phone($payerInfo->getPhone());
        $customer->set_billing_address_1('');
        $customer->set_billing_address_2('');
        $customer->set_billing_city('');
        $customer->set_billing_country($payerInfo->getCountryCode());
        $customer->set_billing_postcode('');
        $customer->set_billing_state('');
        if ($billingAddress) {
            $customer->set_billing_address_1($billingAddress->getLine1());
            $customer->set_billing_address_2($billingAddress->getLine2());
            $customer->set_billing_city($billingAddress->getCity());
            $customer->set_billing_country($billingAddress->getCountryCode());
            $customer->set_billing_postcode($billingAddress->getPostalCode());
            $customer->set_billing_state($billingAddress->getState());
        }
        $customer->save();

        if (!$this->woocommerce->cart->needs_shipping()) {
            return;
        }

        $transactions = $payment->getTransactions();
        if (!$transactions || ! $transactions[0] instanceof Transaction) {
            return;
        }
        $itemList = $transactions[0]->getItemList();
        $shippingAddress = $itemList->getShippingAddress();
        list($firstName, $lastName) = explode(' ', $shippingAddress->getRecipientName(), 2);

        $customer->set_shipping_company('');
        $customer->set_shipping_first_name($firstName);
        $customer->set_shipping_last_name($lastName);
        $customer->set_shipping_address_1($shippingAddress->getLine1());
        $customer->set_shipping_address_2($shippingAddress->getLine2());
        $customer->set_shipping_city($shippingAddress->getCity());
        $customer->set_shipping_country($shippingAddress->getCountryCode());
        $customer->set_shipping_postcode($shippingAddress->getPostalCode());
        $customer->set_shipping_state($shippingAddress->getState());
        $customer->save();
    }
}
