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

use WCPayPalPlus\Api\ErrorData\ApiErrorExtractor;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use function WCPayPalPlus\isGatewayDisabled;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Refund\RefundFactory;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting\SharedSettingsModel;
use WCPayPalPlus\WC\CheckoutDropper;
use WooCommerce;

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
        $container[FrameRenderer::class] = function () {
            return new FrameRenderer();
        };
        $container[GatewaySettingsModel::class] = function (Container $container) {
            return new GatewaySettingsModel(
                $container[SharedSettingsModel::class]
            );
        };
        $container[DefaultGatewayOverride::class] = function (Container $container) {
            return new DefaultGatewayOverride(
                $container[PlusStorable::class],
                $container[Session::class],
                $container[CurrentPaymentMethod::class]
            );
        };
        $container[Gateway::class] = function (Container $container) {
            return new Gateway(
                $container[WooCommerce::class],
                $container[FrameRenderer::class],
                $container[CredentialValidator::class],
                $container[GatewaySettingsModel::class],
                $container[RefundFactory::class],
                $container[OrderFactory::class],
                $container[PaymentExecutionFactory::class],
                $container[PaymentCreatorFactory::class],
                $container[CheckoutDropper::class],
                $container[Session::class],
                $container[Logger::class]
            );
        };
        $container[PaymentExecution::class] = function (Container $container) {
            return new PaymentExecution(
                $container[OrderFactory::class],
                $container[Session::class],
                $container[PaymentExecutionFactory::class],
                $container[Logger::class],
                $container[CheckoutDropper::class],
                $container[ApiErrorExtractor::class]
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

        if (!is_admin() && isGatewayDisabled($gateway)) {
            return;
        }

        add_action(
            'wp',
            [$container[DefaultGatewayOverride::class], 'maybeOverride']
        );

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
            [$container[PaymentExecution::class], 'execute'],
            12
        );
    }
}
