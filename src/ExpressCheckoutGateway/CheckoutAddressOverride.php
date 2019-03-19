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

use WCPayPalPlus\Payment\Session;

/**
 * Class CheckoutAddressOverride
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
class CheckoutAddressOverride
{
    /**
     * Type id of WooCommerce Address field
     */
    const FIELD_TYPE_ID = 'ppp_ec_field';

    /**
     * Address fields that comes from Paypal
     */
    const ALLOWED_ADDRESS_FIELDS = [
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

    /**
     * @var \WooCommerce
     */
    private $wooCommerce;

    /**
     * CheckoutAddressOverride constructor.
     *
     * @param \WooCommerce $woocommerce
     */
    public function __construct(\WooCommerce $woocommerce)
    {
        $this->wooCommerce = $woocommerce;
    }

    /**
     * Are wee currently in a Express checkout
     *
     * @return bool
     */
    public function isExpressCheckout()
    {
        $postPaymentMethod = \filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
        if (Gateway::GATEWAY_ID === $postPaymentMethod) {
            return true;
        }

        return Gateway::GATEWAY_ID === $this->wooCommerce->session->get(Session::CHOSEN_PAYMENT_METHOD);
    }

    /**
     * Don't save customer adress from paypal
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
        $displayValue = $value;
        if ('billing_country' === $key || 'shipping_country' === $key) {
            $countries = $this->wooCommerce->countries->get_countries();
            if (isset($countries[$value])) {
                $displayValue = $countries[$value];
            }
        }
        if (!$value) {
            return $field . '<input type="hidden" class="input-text ' .
                  esc_attr(implode(' ', $args['input_class'])) .
                  '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) .
                  '" value="' . esc_attr($value) . '" ' . implode(' ', $args['custom_attributes']) . ' />';
        }
        $field .= '<p class="form-row '.esc_attr(implode(' ', $args['class'])) .
                  '" id="' . esc_attr($args['id']) . '_field' . '">' .
                  '<label for="' . esc_attr($args['id']) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . $args['label'] . '</label>' .
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

        $customer = $this->wooCommerce->customer;
        $methodName = "get_{$input}";
        if (method_exists($customer, $methodName) && $customer->$methodName()) {
            return $customer->$methodName();
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

        foreach ($fields as $key => $field) {
            if (!in_array($key, self::ALLOWED_ADDRESS_FIELDS, true)) {
                continue;
            }
            if (!empty($field['required'])) {
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

        $fields['billing_email']['custom_attributes'] = ['readonly' => 'readonly'];
        $fields['billing_email']['type'] = self::FIELD_TYPE_ID;

        return $fields;
    }

    /**
     * Overwrite post vars addresses with customer session fields so that they can't be changed
     *
     * @wp-hook woocommerce_checkout_process
     */
    public function addAddressesToCheckoutPostVars()
    {
        if (! $this->isExpressCheckout()) {
            return;
        }

        $customer = $this->wooCommerce->customer;

        $_POST['payment_method'] = Gateway::GATEWAY_ID;
        $billingFields = $this->wooCommerce->checkout()->get_checkout_fields('billing');
        foreach ($billingFields as $key => $value) {
            if (!in_array(str_replace('billing_', '', $key), self::ALLOWED_ADDRESS_FIELDS, true) ||
                'billing_email' === $key
            ) {
                continue;
            }
            $methodName = "get_{$key}";
            $_POST[$key] = $customer->$methodName();
        }

        if (!$this->wooCommerce->cart->needs_shipping()) {
            return;
        }
        $_POST['ship_to_different_address'] = 1;
        $shippingFields = $this->wooCommerce->checkout()->get_checkout_fields('shipping');
        foreach ($shippingFields as $key => $value) {
            if (!in_array(str_replace('shipping_', '', $key), self::ALLOWED_ADDRESS_FIELDS, true)) {
                continue;
            }
            $methodName = "get_{$key}";
            $_POST[$key] = $customer->$methodName();
        }
    }
}
