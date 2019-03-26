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

use Inpsyde\Lib\PayPal\Api;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use InvalidArgumentException;

/**
 * Class PaymentCreator
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentCreator
{
    /**
     * The PaymentData object.
     *
     * @var PaymentData
     */
    private $paymentData;

    /**
     * The Order data provider object.
     *
     * @var OrderDataProvider
     */
    private $orderDataProvider;

    /**
     * WCPayPalPayment constructor.
     *
     * @param PaymentData $paymentData The PaymentData object.
     * @param OrderDataProvider $orderData WooCommerce order object.
     */
    public function __construct(PaymentData $paymentData, OrderDataProvider $orderData)
    {
        $this->paymentData = $paymentData;
        $this->orderDataProvider = $orderData;
    }

    /**
     * Create a new payment on PayPal.
     * Be aware that this method may indirectly throw a PayPalConnectionException.
     *
     * @return Api\Payment
     * @throws InvalidArgumentException
     * @throws PayPalConnectionException
     */
    public function create()
    {
        return $this->payment()->create($this->paymentData->get_api_context());
    }

    /**
     * @return Api\Payment
     * @throws InvalidArgumentException
     */
    private function payment()
    {
        $payer = new Api\Payer();
        $payer->setPaymentMethod('paypal');
        $item_list = $this->orderDataProvider->get_item_list();
        $amount = new Api\Amount();
        $amount
            ->setCurrency(get_woocommerce_currency())
            ->setTotal($this->orderDataProvider->get_total())
            ->setDetails($this->details());

        $redirect_urls = new Api\RedirectUrls();
        $redirect_urls
            ->setReturnUrl($this->paymentData->get_return_url())
            ->setCancelUrl($this->paymentData->get_cancel_url());

        $payment = new Api\Payment();
        $payment
            ->setIntent('sale')
            ->setExperienceProfileId($this->paymentData->get_web_profile_id())
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions([$this->transaction($amount, $item_list)]);

        return $payment;
    }

    /**
     * Created a Details object for the Paypal API
     *
     * @return Api\Details
     * @throws \InvalidArgumentException
     */
    private function details()
    {
        $tax = 0;
        $shipping = (float)$this->orderDataProvider->get_total_shipping();

        if (!wc_prices_include_tax()) {
            $tax = $this->orderDataProvider->get_total_tax();
        }

        $tax or $shipping += (float)$this->orderDataProvider->get_shipping_tax();

        $sub_total = $this->orderDataProvider->get_subtotal();

        $details = new Api\Details();
        $details
            ->setShipping($shipping)
            ->setSubtotal($sub_total);

        if ($tax > 0) {
            $details->setTax($tax);
        }

        return $details;
    }

    /**
     * Create a configured Transaction object.
     *
     * @param Api\Amount $amount Amount object.
     * @param Api\ItemList $item_list ItemList object.
     *
     * @return Api\Transaction
     * @throws \InvalidArgumentException
     */
    private function transaction(Api\Amount $amount, Api\ItemList $item_list)
    {
        $transaction = new Api\Transaction();
        $transaction
            ->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Payment description')
            ->setInvoiceNumber(uniqid())
            ->setNotifyUrl($this->paymentData->get_notify_url());

        return $transaction;
    }
}
