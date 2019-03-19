<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Session\Session;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Payment
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[PaymentCreatorFactory::class] = function (Container $container) {
            return new PaymentCreatorFactory(
                $container[WooCommerce::class],
                $container[OrderFactory::class],
                $container[Session::class]
            );
        };
        $container[PaymentExecutionFactory::class] = function (Container $container) {
            return new PaymentExecutionFactory(
                $container[WooCommerce::class]
            );
        };
        $container[PaymentPatchFactory::class] = function (Container $container) {
            return new PaymentPatchFactory(
                $container[Logger::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
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
