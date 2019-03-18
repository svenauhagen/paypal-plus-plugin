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

/**
 * Class CheckoutAddressOverride
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
class CheckoutAddressOverride
{

    const FIELD_TYPE_ID = 'ppp_ec_field';

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
     * @var WooCommerce
     */
    private $woocommerce;

    /**
     * @var Session
     */
    private $session;

    /**
     * CheckoutAddressOverride constructor.
     * @param WooCommerce $woocommerce
     * @param Session $session
     */
    public function __construct(WooCommerce $woocommerce, Session $session)
    {
        $this->woocommerce = $woocommerce;
        $this->session = $session;
    }

    /**
     * @return bool
     */
    public function isExpressCheckout()
    {
        return Gateway::GATEWAY_ID === $this->session->get(Session::CHOSEN_PAYMENT_METHOD);
    }

    /**
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
            $countries = $this->woocommerce->countries->get_countries();
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
     * @param $default
     * @param $input
     */
    public function filterCheckoutValues($default, $input)
    {
        if (! $this->isExpressCheckout()) {
            return $default;
        }

        if (0 !== strpos($input, 'billing_') && 0 !== strpos($input, 'shipping_')) {
            return $default;
        }

        $customer = $this->woocommerce->customer;
        $methodName = "get_{$input}";
        if (method_exists($customer, $methodName) && $customer->$methodName()) {
            return $customer->$methodName();
        }

        return $default;
    }

    /**
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
     * @param array $fields
     *
     * @return array
     */
    public function filterBillingFields(Array $fields)
    {
        if (! $this->isExpressCheckout()) {
            return $fields;
        }

        $fields['billing_address_1']['required'] = false;
        $fields['billing_address_2']['required'] = false;
        $fields['billing_city']['required'] = false;
        $fields['billing_postcode']['required'] = false;
        $fields['billing_state']['required'] = false;
        $fields['billing_email']['custom_attributes'] = ['readonly' => 'readonly'];
        $fields['billing_email']['type'] = self::FIELD_TYPE_ID;

        return $fields;
    }

    /**
     * Add addresses to checkout post vars
     */
    public function addAddressesToCheckoutPostVars()
    {
        if (! $this->isExpressCheckout()) {
            return;
        }

        $postPaymentMethod = \filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
        if (Gateway::GATEWAY_ID !== $postPaymentMethod) {
            return;
        }

        $customer = $this->woocommerce->customer;

        $billingFields = $this->woocommerce->checkout()->get_checkout_fields('billing');
        foreach ($billingFields as $key => $value) {
            if (!in_array(str_replace('billing_', '', $key), self::ALLOWED_ADDRESS_FIELDS, true) ||
                'billing_email' === $key
            ) {
                continue;
            }
            $methodName = "get_{$key}";
            $_POST[$key] = $customer->$methodName();
        }

        if (!$this->woocommerce->cart->needs_shipping()) {
            return;
        }
        $_POST['ship_to_different_address'] = 1;
        $shippingFields = $this->woocommerce->checkout()->get_checkout_fields('shipping');
        foreach ($shippingFields as $key => $value) {
            if (!in_array(str_replace('shipping_', '', $key), self::ALLOWED_ADDRESS_FIELDS, true)) {
                continue;
            }
            $methodName = "get_{$key}";
            $_POST[$key] = $customer->$methodName();
        }
    }
}
