<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Exception;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Api\Transaction;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\Session\Session;
use WooCommerce;

/**
 * Class CheckoutAddressOverride
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
class CheckoutAddressOverride
{

    const FIELD_TYPE_ID = 'ppp_ec_field';

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array Addresses
     */
    private $addresses = [
        'billing_first_name' => '',
        'billing_last_name' => '',
        'billing_company' => '',
        'billing_country' => '',
        'billing_address_1' => '',
        'billing_address_2' => '',
        'billing_city' => '',
        'billing_postcode' => '',
        'billing_state' => '',
        'billing_email' => '',
        'billing_phone' => '',
        'shipping_first_name' => '',
        'shipping_last_name' => '',
        'shipping_company' => '',
        'shipping_country' => '',
        'shipping_address_1' => '',
        'shipping_address_2' => '',
        'shipping_city' => '',
        'shipping_postcode' => '',
        'shipping_state' => '',
    ];

    /**
     * CheckoutAddressOverride constructor.
     *
     * @param WooCommerce $wooCommerce
     * @param CurrentPaymentMethod $currentPaymentMethod
     * @param Logger $logger
     */
    public function __construct(
        WooCommerce $wooCommerce,
        CurrentPaymentMethod $currentPaymentMethod,
        Logger $logger
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->currentPaymentMethod = $currentPaymentMethod;
        $this->logger = $logger;
    }

    /**
     * Don't save customer address from paypal
     *
     * @wp-hook woocommerce_checkout_update_customer_data
     *
     * @param bool $default
     *
     * @return bool
     */
    public function filterSaveCustomerData($default)
    {
        if (! $this->isExpressCheckout()) {
            return $default;
        }

        return false;
    }

    /**
     * We use shipping Address from Paypal and Billing address has not all information
     *
     * @wp-hook woocommerce_cart_needs_shipping_address
     *
     * @param bool $current
     *
     * @return bool
     */
    public function filterCartNeedsShippingAddress($current)
    {
        if (! $this->isExpressCheckout()) {
            return $current;
        }

        if (! $this->wooCommerce->cart->needs_shipping()) {
            return $current;
        }

        return true;
    }

    /**
     * Activate shipping to different address in the form
     *
     * @wp-hook filterShipToDifferentAddress
     *
     * @param int $current
     *
     * @return int
     */
    public function filterShipToDifferentAddress($current)
    {
        if (! $this->isExpressCheckout()) {
            return $current;
        }

        return 1;
    }

    /**
     * Output special form field
     *
     * @wp-hook woocommerce_form_field_{Type ID}
     *
     * @param $field
     * @param $key
     * @param array $args
     * @param $value
     *
     * @return string
     */
    public function filterFieldType($field, $key, Array $args, $value)
    {
        $addresses = $this->getPaymentAddresses();
        if (isset($addresses[$key])) {
            $value = $addresses[$key];
        }
        $displayValue = $value;
        if ('billing_country' === $key || 'shipping_country' === $key) {
            $countries = $this->wooCommerce->countries->get_countries();
            if (isset($countries[$value])) {
                $displayValue = $countries[$value];
            }
        }
        if (! $value) {
            return $field . '<input type="hidden" class="input-text ' .
                   esc_attr(implode(' ', $args['input_class'])) .
                   '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) .
                   '" value="' . esc_attr($value) . '" ' . implode(' ', $args['custom_attributes']) . ' />';
        }
        $field .= '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) .
                  '" id="' . esc_attr($args['id']) . '_field' . '">' .
                  '<label for="' . esc_attr($args['id']) . '" class="' . esc_attr(
                      implode(' ', $args['label_class'])
                  ) . '">' . $args['label'] . '</label>' .
                  '<span class="woocommerce-input-wrapper">' .
                  '<input type="hidden" class="input-text ' .
                  esc_attr(implode(' ', $args['input_class'])) .
                  '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) .
                  '" value="' . esc_attr($value) . '" ' . implode(' ', $args['custom_attributes']) . ' />' .
                  esc_attr($displayValue) .
                  '</span></p>';

        return $field;
    }

    /**
     * Overwrite real customer data with session customer data
     *
     * @wp-hook woocommerce_checkout_get_value
     *
     * @param string $default
     * @param string $input
     *
     * @return string
     */
    public function filterCheckoutValues($default, $input)
    {
        if (! $this->isExpressCheckout()) {
            return $default;
        }

        if (0 !== strpos($input, 'billing_') && 0 !== strpos($input, 'shipping_')) {
            return $default;
        }

        $addresses = $this->getPaymentAddresses();
        if (isset($addresses[$input])) {
            return $addresses[$input];
        }

        return $default;
    }

    /**
     * Change fields to not required and change field type
     *
     * @wp-hook woocommerce_default_address_fields
     *
     * @param array $fields
     *
     * @return array
     */
    public function filterDefaultAddressFields(Array $fields)
    {
        if (! $this->isExpressCheckout()) {
            return $fields;
        }

        $addressFieldsToChange = [
            'first_name',
            'last_name',
            'company',
            'country',
            'address_1',
            'address_2',
            'city',
            'postcode',
            'state',
        ];

        foreach ($fields as $key => $field) {
            if (! in_array($key, $addressFieldsToChange, true)) {
                continue;
            }
            if (! empty($field['required'])) {
                $fields[$key]['required'] = false;
            }
            $fields[$key]['custom_attributes'] = ['readonly' => 'readonly'];
            $fields[$key]['type'] = self::FIELD_TYPE_ID;
        }

        return $fields;
    }

    /**
     * Set billing fields to not required that are not in the default fields
     *
     * @wp-hook woocommerce_billing_fields
     *
     * @param array $fields
     *
     * @return array
     */
    public function filterBillingFields(Array $fields)
    {
        if (! $this->isExpressCheckout()) {
            return $fields;
        }

        if (isset($fields['billing_email'])) {
            $fields['billing_email']['custom_attributes'] = ['readonly' => 'readonly'];
            $fields['billing_email']['type'] = self::FIELD_TYPE_ID;
        }

        return $fields;
    }

    /**
     * Overwrite post vars addresses with payment addresses so that they can't be changed
     *
     * @wp-hook woocommerce_checkout_process
     */
    public function addAddressesToCheckoutPostVars()
    {
        if (! $this->isExpressCheckout()) {
            return;
        }

        $addresses = $this->getPaymentAddresses();
        $needsShipping = $this->wooCommerce->cart->needs_shipping();

        $_POST['payment_method'] = Gateway::GATEWAY_ID;
        $_POST['ship_to_different_address'] = 1;
        foreach ($addresses as $key => $value) {
            if ('billing_phone' === $key || (!$needsShipping && 0 === strpos($key, 'shipping_'))) {
                continue;
            }
            $_POST[$key] = $value;
        }
    }

    /**
     * Are we currently in a Express checkout
     *
     * @return bool
     */
    private function isExpressCheckout()
    {
        $currentPaymentMethod = $this->currentPaymentMethod->payment();

        return Gateway::GATEWAY_ID === $currentPaymentMethod;
    }

    /**
     * Get shipping and billing address
     *
     * @return array
     */
    private function getPaymentAddresses()
    {
        if ($this->addresses['billing_first_name'] || $this->addresses['billing_last_name']) {
             return $this->addresses;
        }

        $paymentId = $this->wooCommerce->session->get(Session::PAYMENT_ID);
        $apiContext = ApiContextFactory::getFromConfiguration();
        $payment = null;
        try {
            $payment = Payment::get($paymentId, $apiContext);
        } catch (PayPalConnectionException $exc) {
            $this->logger->error($exc->getData());
        } catch (Exception $exc) {
            $this->logger->error($exc->getMessage());
        }

        if (!$payment) {
            wc_add_notice(
                __('Can not retrieve address from PayPal, try to checkout again.', 'woo-paypalplus'),
                'error'
            );
            return $this->addresses;
        }

        $payer = $payment->getPayer();
        $payerInfo = $payer->getPayerInfo();
        $billingAddress = $payerInfo->getBillingAddress();

        $this->addresses['billing_first_name'] = $payerInfo->getFirstName();
        $this->addresses['billing_last_name'] = $payerInfo->getLastName();
        $this->addresses['billing_email'] = $payerInfo->getEmail();
        $this->addresses['billing_phone'] = $payerInfo->getPhone();
        $this->addresses['billing_country'] = $payerInfo->getCountryCode();
        if ($billingAddress) {
            $this->addresses['billing_address_1'] = $billingAddress->getLine1();
            $this->addresses['billing_address_2'] = $billingAddress->getLine2();
            $this->addresses['billing_city'] = $billingAddress->getCity();
            $this->addresses['billing_country'] = $billingAddress->getCountryCode();
            $this->addresses['billing_postcode'] = $billingAddress->getPostalCode();
            $this->addresses['billing_state'] = $billingAddress->getState();
        }

        if (! $this->wooCommerce->cart->needs_shipping()) {
            return $this->addresses;
        }

        $transactions = $payment->getTransactions();
        if (! $transactions || ! $transactions[0] instanceof Transaction) {
            return $this->addresses;
        }
        $itemList = $transactions[0]->getItemList();
        $shippingAddress = $itemList->getShippingAddress();
        list($this->addresses['shipping_first_name'], $this->addresses['shipping_last_name']) =
            explode(' ', $shippingAddress->getRecipientName(), 2);
        $this->addresses['shipping_address_1'] = $shippingAddress->getLine1();
        $this->addresses['shipping_address_2'] = $shippingAddress->getLine2();
        $this->addresses['shipping_city'] = $shippingAddress->getCity();
        $this->addresses['shipping_country'] = $shippingAddress->getCountryCode();
        $this->addresses['shipping_postcode'] = $shippingAddress->getPostalCode();
        $this->addresses['shipping_state'] = $shippingAddress->getState();

        return $this->addresses;
    }
}
