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
     * @var \WooCommerce
     */
    private $woocommerce;

    public function __construct(\WooCommerce $woocommerce)
    {
        $this->woocommerce = $woocommerce;
    }

    /**
     * @return bool
     */
    public function isExpressCheckout()
    {
        return Gateway::GATEWAY_ID === $this->woocommerce->session->get(Session::CHOSEN_PAYMENT_METHOD);
    }

    public function init(\WC_Checkout $checkout)
    {
        if (!$this->isExpressCheckout()) {
            return;
        }
        remove_action('woocommerce_checkout_billing', [$checkout, 'checkout_form_billing']);
        remove_action('woocommerce_checkout_shipping', [$checkout, 'checkout_form_shipping']);

        add_action(
            'woocommerce_checkout_billing',
            [$this, 'billingDetails']
        );
        add_action(
            'woocommerce_checkout_shipping',
            [$this, 'shippingDetails']
        );
    }

    public function billingDetails()
    {
        $address = $this->woocommerce->customer->get_billing()
        ?>
        <h3><?php esc_attr_e('Billing details', 'woo-paypalplus'); ?></h3>
        <?php echo $this->woocommerce->countries->get_formatted_address(
            $address
        ); ?>
        <br />
        <?php echo esc_html($this->woocommerce->customer->get_billing_email());
    }

    public function shippingDetails()
    {
        if (!$this->woocommerce->cart->needs_shipping()) {
            return;
        }
        $address = $this->woocommerce->customer->get_shipping();
        ?>
        <h3><?php esc_attr_e('Shipping details', 'woo-paypalplus'); ?></h3>
        <?php
        echo $this->woocommerce->countries->get_formatted_address(
            $address
        );
    }

    /**
     * @param bool $default
     *
     * @param \WC_Checkout $checkout
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
