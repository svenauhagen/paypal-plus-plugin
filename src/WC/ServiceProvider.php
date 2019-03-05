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
use WCPayPalPlus\Setting;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\WC
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    public function register(Container $container)
    {
        $container[\WooCommerce::class] = function () {
            return wc();
        };
        $container[Setting\PlusStorable::class] = function (Container $container) {
            return new Setting\PlusRepository(
                $container[PlusGateway::class]
            );
        };
        $container[PlusGateway::class] = function (Container $container) {
            return new PlusGateway(
                $container[PlusFrameView::class]
            );
        };
        $container[DefaultGatewayOverride::class] = function (Container $container) {
            return new DefaultGatewayOverride(
                $container[Setting\PlusStorable::class]
            );
        };
        $container[PlusFrameView::class] = function () {
            return new PlusFrameView();
        };
    }

    public function bootstrap(Container $container)
    {
        $payPalPlusGatewayId = PlusGateway::GATEWAY_ID;
        $payPalPlusGateway = $container[PlusGateway::class];

        add_action(
            'wp_loaded',
            [$container[DefaultGatewayOverride::class], 'maybeOverride']
        );

        add_filter('woocommerce_payment_gateways', function ($methods) use ($payPalPlusGateway) {
            $methods[PlusGateway::class] = $payPalPlusGateway;

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
