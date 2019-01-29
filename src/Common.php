<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 25.10.16
 * Time: 15:45
 */

namespace WCPayPalPlus;

use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Class Common
 *
 * @package WCPayPalPlus
 */
final class Common implements Controller
{
    /**
     * @var PayPalPlusGateway
     */
    private $gateway;

    public function __construct(PayPalPlusGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function init()
    {
        add_filter('woocommerce_payment_gateways', function ($methods) {
            $methods[] = $this->gateway;

            $payPalGatewayIndex = array_search('WC_Gateway_Paypal', $methods, true);
            if ($payPalGatewayIndex !== false) {
                unset($methods[$payPalGatewayIndex]);
            }

            return $methods;
        });
        add_action('init', function () {
            load_plugin_textdomain(
                'woo-paypalplus',
                false,
                basename(dirname(__FILE__)) . '/languages'
            );
        });
    }
}
