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

    /**
     * @var \WC_Cart
     */
    private $cart;

    /**
     * @var \WC_Customer
     */
    private $customer;

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
        ?>
        <h3><?php _e('Billing details', '' ); ?></h3>
        <?php
        print_r($this->woocommerce->customer->get_billing());
    }

    public function shippingDetails()
    {
        ?>
        <h3><?php _e('Shipping details', '' ); ?></h3>
        <?php
        print_r($this->woocommerce->customer->get_shipping());
    }

    public function filterDefaultAddressFields(Array $fields)
    {
        if (! $this->isExpressCheckout()) {
            return $fields;
        }

        if (method_exists($this->cart, 'needs_shipping') &&
            ! $this->cart->needs_shipping()
        ) {
            $notRequiredFields = ['address_1', 'city', 'postcode', 'country'];
            foreach ($notRequiredFields as $notRequiredField) {
                if (array_key_exists($notRequiredField, $fields)) {
                    $fields[$notRequiredField]['required'] = false;
                }
            }
        }

        if (array_key_exists('state', $fields)) {
            $fields['state']['required'] = false;
        }

        return $fields;
    }

    public function filterBillingFields(Array $fields)
    {
        if (! $this->isExpressCheckout()) {
            return $fields;
        }

        if (array_key_exists('billing_phone', $fields)) {
            $fields['billing_phone']['required'] = 'no';
        }

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

        $_POST['ship_to_different_address'] = $this->woocommerce->cart->needs_shipping_address() ? 1 : 0;

        $billingFields = [
            'first_name',
            'last_name',
            'company',
            'address_1',
            'address_2',
            'city',
            'state',
            'postcode',
            'country',
            'email',
            'phone',
        ];

        foreach ($billingFields as $key) {
            $_POST['billing_' . $key] = $this->woocommerce->customer->get_billing_{$key};
        }

        $shippingFields = [
            'first_name',
            'last_name',
            'company',
            'address_1',
            'address_2',
            'city',
            'state',
            'postcode',
            'country',
        ];

        foreach ($shippingFields as $key) {
            $_POST['shipping_' . $key] = $this->woocommerce->customer->get_shipping_{$key};
        }
    }
}
