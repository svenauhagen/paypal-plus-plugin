<?php

namespace WCPayPalPlus;

use WCPayPalPlus\Notice;
use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 12:39
 */
class Plugin
{
    /**
     * Gateway ID.
     *
     * @var string
     */
    private $gateway_id = 'paypal_plus';

    /**
     * Payment Gateway object.
     *
     * @var PayPalPlusGateway
     */
    private $gateway;

    /**
     * Plugin constructor.
     */
    public function __construct()
    {
        $this->gateway = new PayPalPlusGateway(
            $this->gateway_id,
            __('PayPal PLUS', 'woo-paypalplus')
        );
        $this->gateway->register();

        add_filter('woocommerce_payment_gateways', function ($methods) {
            $methods[] = $this->gateway;

            $payPalGatewayIndex = array_search('WC_Gateway_Paypal', $methods, true);
            if ($payPalGatewayIndex !== false) {
                unset($methods[$payPalGatewayIndex]);
            }

            return $methods;
        });

        $adminNotice = new Notice\Admin();
        $adminNotice->init();
    }
}
