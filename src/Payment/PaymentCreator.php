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
use Inpsyde\Lib\PayPal\Api\Item;
use Inpsyde\Lib\PayPal\Api\ItemList;
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
        $item_list = $this->itemsList();
        $amount = new Api\Amount();
        $amount
            ->setCurrency(get_woocommerce_currency())
            ->setTotal($this->orderDataProvider->total())
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
     * Retrieve the Order Items List
     *
     * Address Rounding problems
     *
     * The main problem here is how WooCommerce tackle the taxes applied to prices and how
     * PayPal needs to have those prices.
     *
     * Because of intrinsic rounding problems WooCommerce deal with taxes as pow10 values, means
     * the numbers used to calculate taxes contains a lot of information.
     *
     * PayPal want to have prices 2 decimal rounded, if not the Item::setPrice will format in that way.
     *
     * What we did in previous versions was to get the price of the product (total) divided by
     * the quantity see $data->get_price(), this could produce rounding problems because
     * the division result could contains more than 2 decimal points, then we pass that value
     * to Item::setPrice that will round it, practically adding some more cents to the price.
     *
     * So the price calculated by WooCommerce will not fit the price calculated by our implementation.
     * The good solution would work as WooCommerce (using pow10 values) but actually the logic
     * is too complicated that need a complete separated implementation.
     *
     * As workaround for now we'll send one single product where the name contains all of the product
     * names + quantities and the amount is the subtotal.
     *
     * @return ItemList
     * @throws InvalidArgumentException
     */
    private function itemsList()
    {
        $orderItemsList = $this->orderDataProvider->itemsList();

        if (!wc_prices_include_tax()) {
            return $orderItemsList;
        }

        $itemList = new ItemList;
        $item = new Item;
        $itemNamesList = $this->extractItemsNames($orderItemsList);

        $item
            ->setName($itemNamesList)
            ->setCurrency(get_woocommerce_currency())
            ->setQuantity(1)
            ->setPrice($this->orderDataProvider->subTotal());

        $itemList->addItem($item);

        return $itemList;
    }

    /**
     * Extract the Item Names x Quantity from ItemList Items
     *
     * @param ItemList $itemsList
     * @return string
     */
    private function extractItemsNames(ItemList $itemsList)
    {
        $names = [];

        /** @var Item $item */
        foreach ($itemsList->getItems() as $item) {
            $names[] = $item->getName() . 'x' . $item->getQuantity();
        }

        return implode(',', $names);
    }

    /**
     * Created a Details object for the Paypal API
     *
     * @return Api\Details
     * @throws \InvalidArgumentException
     */
    private function details()
    {
        $shipping = $this->orderDataProvider->shippingTotal();
        $subTotal = $this->orderDataProvider->subTotal();

        $tax = !wc_prices_include_tax()
            ? $this->orderDataProvider->totalTaxes()
            : $this->orderDataProvider->shippingTax();

        $details = new Api\Details();
        $details
            ->setShipping($shipping)
            ->setSubtotal($subTotal)
            ->setTax($tax);

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
