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
     * @var \WooCommerce
     */
    private $woocommerce;

    public function __construct(\WooCommerce $woocommerce)
    {
        $this->woocommerce = $woocommerce;
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

    /**
     * @return bool
     */
    public function isExpressCheckout()
    {
        return Gateway::GATEWAY_ID === $this->woocommerce->session->get(Session::CHOSEN_PAYMENT_METHOD);
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
        $address = $this->woocommerce->customer->get_shipping();
        ?>
        <h3><?php esc_attr_e('Shipping details', 'woo-paypalplus'); ?></h3>
        <?php
        echo $this->woocommerce->countries->get_formatted_address(
            $address
        );
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
            if (!empty($field['required'])) {
                $fields[$key]['required'] = false;
            }
            $fields[$key]['custom_attributes'] = ['readonly' => 'readonly'];
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

        $fields['billing_phone']['required'] = false;
        $fields['billing_phone']['custom_attributes'] = ['readonly' => 'readonly'];

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

        $postPaymentMethod =\filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
        if (Gateway::GATEWAY_ID !== $postPaymentMethod) {
            return;
        }

        $customer = $this->woocommerce->customer;

        $_POST['ship_to_different_address'] = 1;
        $billingFields = $this->woocommerce->checkout()->get_checkout_fields('billing');
        foreach ($billingFields as $key => $value) {
            $methodName = "get_{$key}";
            $_POST[$key] = $customer->$methodName();
        }

        $shippingFields = $this->woocommerce->checkout()->get_checkout_fields('shipping');
        foreach ($shippingFields as $key => $value) {
            $methodName = "get_{$key}";
            $_POST[$key] = $customer->$methodName();
        }
    }
}
