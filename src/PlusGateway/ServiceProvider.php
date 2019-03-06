<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\PlusGateway;

use WCPayPalPlus\Api\CredentialProvider;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\WC\Payment\PaymentExecutionFactory;
use WCPayPalPlus\WC\Payment\PaymentCreatorFactory;
use WCPayPalPlus\WC\Payment\PaymentPatchFactory;
use WCPayPalPlus\WC\Refund\RefundFactory;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\PlusGateway
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[PlusFrameView::class] = function () {
            return new PlusFrameView();
        };
        $container[GatewaySettingsModel::class] = function () {
            return new GatewaySettingsModel();
        };
        $container[DefaultGatewayOverride::class] = function (Container $container) {
            return new DefaultGatewayOverride(
                $container[PlusStorable::class]
            );
        };
        $container[Gateway::class] = function (Container $container) {
            return new Gateway(
                $container[PlusFrameView::class],
                $container[CredentialProvider::class],
                $container[CredentialValidator::class],
                $container[GatewaySettingsModel::class],
                $container[RefundFactory::class],
                $container[PaymentPatchFactory::class],
                $container[OrderFactory::class],
                $container[PaymentExecutionFactory::class],
                $container[PaymentCreatorFactory::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $gatewayId = Gateway::GATEWAY_ID;
        $gateway = $container[Gateway::class];

        add_action(
            'wp_loaded',
            [$container[DefaultGatewayOverride::class], 'maybeOverride']
        );

        add_filter('woocommerce_payment_gateways', function ($methods) use ($gateway) {
            $methods[Gateway::class] = $gateway;

            $payPalGatewayIndex = array_search('WC_Gateway_Paypal', $methods, true);
            if ($payPalGatewayIndex !== false) {
                unset($methods[$payPalGatewayIndex]);
            }

            return $methods;
        });

        add_action(
            "woocommerce_update_options_payment_gateways_{$gatewayId}",
            [$gateway, 'process_admin_options'],
            10
        );
        add_action(
            'woocommerce_receipt_' . $gatewayId,
            [$gateway, 'render_receipt_page']
        );
        add_action(
            'woocommerce_api_' . $gatewayId,
            [$gateway, 'execute_payment'],
            12
        );
        add_action(
            'woocommerce_add_to_cart',
            [$gateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_cart_item_removed',
            [$gateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_after_cart_item_quantity_update',
            [$gateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_applied_coupon',
            [$gateway, 'clear_session_data']
        );
        add_action(
            'woocommerce_removed_coupon',
            [$gateway, 'clear_session_data']
        );
    }
}
