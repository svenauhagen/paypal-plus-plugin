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

use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\WpNonce;
use WCPayPalPlus\Api\CredentialProvider;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Payment\Session;
use WCPayPalPlus\Refund\RefundFactory;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\ExpressCheckout
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[GatewaySettingsModel::class] = function () {
            return new GatewaySettingsModel();
        };
        $container[Gateway::class] = function (Container $container) {
            return new Gateway(
                $container[CredentialProvider::class],
                $container[CredentialValidator::class],
                $container[GatewaySettingsModel::class],
                $container[RefundFactory::class],
                $container[OrderFactory::class],
                $container[PaymentExecutionFactory::class],
                $container[Session::class]
            );
        };
        $container[CheckoutGatewayOverride::class] = function (Container $container) {
            return new CheckoutGatewayOverride(
                $container[\WooCommerce::class]
            );
        };

        $ajaxNonce = new WpNonce(AjaxHandler::ACTION . '_nonce');

        $container[SingleProductButtonView::class] = function () use ($ajaxNonce) {
            return new SingleProductButtonView($ajaxNonce);
        };
        $container[CartButtonView::class] = function (Container $container) use ($ajaxNonce) {
            return new CartButtonView(
                $ajaxNonce,
                $container[\WooCommerce::class]
            );
        };
        $container[Dispatcher::class] = function () {
            return new Dispatcher();
        };
        $container[AjaxHandler::class] = function (Container $container) use ($ajaxNonce) {
            return new AjaxHandler(
                $ajaxNonce,
                $container[NonceContextInterface::class],
                $container[Dispatcher::class]
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

        add_filter('woocommerce_payment_gateways', function ($methods) use ($gateway) {
            $methods[Gateway::class] = $gateway;

            return $methods;
        });

        add_action(
            "woocommerce_update_options_payment_gateways_{$gatewayId}",
            [$gateway, 'process_admin_options'],
            10
        );
        add_action(
            'woocommerce_api_' . $gatewayId,
            [$gateway, 'execute_payment'],
            12
        );

        add_filter(
            'woocommerce_available_payment_gateways',
            [$container[CheckoutGatewayOverride::class], 'maybeOverride'],
            999
        );

        add_action(
            'woocommerce_after_add_to_cart_button',
            [$container[SingleProductButtonView::class], 'render']
        );
        add_action(
            'woocommerce_after_mini_cart',
            [$container[CartButtonView::class], 'render']
        );
        // After WooCommerce woocommerce_button_proceed_to_checkout
        add_action(
            'woocommerce_proceed_to_checkout',
            [$container[CartButtonView::class], 'render'],
            25
        );

        add_action(
            'wp_ajax_' . AjaxHandler::ACTION,
            [$container[AjaxHandler::class], 'handle']
        );
        add_action(
            'wp_ajax_nopriv_' . AjaxHandler::ACTION,
            [$container[AjaxHandler::class], 'handle']
        );
    }
}
