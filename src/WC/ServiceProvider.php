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
use WCPayPalPlus\PlusGateway\Gateway;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\Payment\PaymentPatchFactory;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Service;
use WooCommerce;

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
        $container[WooCommerce::class] = function () {
            return wc();
        };
        $container[CheckoutDropper::class] = function (Container $container) {
            return new CheckoutDropper(
                $container[Session::class]
            );
        };
        $container[RedirectablePatcher::class] = function (Container $container) {
            return new RedirectablePatcher(
                $container[OrderFactory::class],
                $container[PaymentPatchFactory::class],
                $container[PlusStorable::class],
                $container[Session::class],
                $container[CheckoutDropper::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $gatewayId = $container[Gateway::class]->id;

        add_action(
            "woocommerce_receipt_{$gatewayId}",
            [$container[RedirectablePatcher::class], 'patchOrder']
        );
    }
}
