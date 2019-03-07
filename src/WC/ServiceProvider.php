<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC;

use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Order\OrderStatuses;
use WCPayPalPlus\PlusGateway\Gateway;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\WC\Payment\PaymentExecutionFactory;
use WCPayPalPlus\WC\Payment\PaymentCreatorFactory;
use WCPayPalPlus\WC\Payment\PaymentPatchFactory;
use WCPayPalPlus\WC\Payment\Session;
use WCPayPalPlus\WC\Refund\RefundFactory;
use WCPayPalPlus\Service;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\WC
 */
class ServiceProvider implements Service\BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[RefundFactory::class] = function (Container $container) {
            return new RefundFactory(
                $container[OrderStatuses::class]
            );
        };

        $container[ReceiptPageRenderer::class] = function (Container $container) {
            return new ReceiptPageRenderer(
                $container[OrderFactory::class],
                $container[PaymentPatchFactory::class],
                $container[PlusStorable::class],
                $container[Session::class]
            );
        };

        $container[PaymentCreatorFactory::class] = function (Container $container) {
            return new PaymentCreatorFactory(
                $container[OrderFactory::class]
            );
        };
        $container[PaymentExecutionFactory::class] = function () {
            return new PaymentExecutionFactory();
        };
        $container[PaymentPatchFactory::class] = function () {
            return new PaymentPatchFactory();
        };
        $container[Session::class] = function () {
            return new Session();
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $gatewayId = $container[Gateway::class]->id;

        $this->bootstrapPaymentSession($container);

        add_action(
            "woocommerce_receipt_{$gatewayId}",
            [$container[ReceiptPageRenderer::class], 'render']
        );
    }

    /**
     * Bootstrap the Session for Payment
     *
     * @param Container $container
     */
    private function bootstrapPaymentSession(Container $container)
    {
        $session = $container[Session::class];
        $sessionCleanHooks = [
            'woocommerce_add_to_cart',
            'woocommerce_cart_item_removed',
            'woocommerce_after_cart_item_quantity_update',
            'woocommerce_applied_coupon',
            'woocommerce_removed_coupon',
        ];

        foreach ($sessionCleanHooks as $hook) {
            add_action($hook, [$session, 'clean']);
        }
    }
}
