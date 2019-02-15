<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC;

use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusRepository;
use WCPayPalPlus\Pui\Renderer;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\WC
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    public function register(Container $container)
    {
        $container[PlusRepository::class] = function (Container $container) {
            return new PlusRepository(
                $container[PayPalPlusGateway::class]
            );
        };
        $container[PayPalPlusGateway::class] = function (Container $container) {
            return new PayPalPlusGateway();
        };
        $container[DefaultGatewayOverride::class] = function (Container $container) {
            return new DefaultGatewayOverride(
                $container[PlusRepository::class]
            );
        };
    }

    public function bootstrap(Container $container)
    {
        $payPalPlusGatewayId = PayPalPlusGateway::GATEWAY_ID;
        $payPalPlusGateway = $container[PayPalPlusGateway::class];

        add_action(
            'wp_loaded',
            [$container[DefaultGatewayOverride::class], 'maybeOverride']
        );

        add_filter('woocommerce_payment_gateways', function ($methods) use ($payPalPlusGateway) {
            $methods[PayPalPlusGateway::class] = $payPalPlusGateway;

            $payPalGatewayIndex = array_search('WC_Gateway_Paypal', $methods, true);
            if ($payPalGatewayIndex !== false) {
                unset($methods[$payPalGatewayIndex]);
            }

            return $methods;
        });

        add_action(
            "woocommerce_update_options_payment_gateways_{$payPalPlusGatewayId}",
            [$payPalPlusGateway, 'process_admin_options'],
            10
        );
        add_action(
            'woocommerce_receipt_' . $payPalPlusGatewayId,
            [$payPalPlusGateway, 'render_receipt_page']
        );
        add_action(
            'woocommerce_api_' . $payPalPlusGatewayId,
            [$payPalPlusGateway, 'execute_payment'],
            12
        );
        add_action(
            'woocommerce_add_to_cart',
            [$payPalPlusGateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_cart_item_removed',
            [$payPalPlusGateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_after_cart_item_quantity_update',
            [$payPalPlusGateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_applied_coupon',
            [$payPalPlusGateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_removed_coupon',
            [$payPalPlusGateway, 'clear_session_data']
        );
    }
}
