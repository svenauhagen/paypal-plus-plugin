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
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\WC\Payment\PaymentExecutionFactory;
use WCPayPalPlus\WC\Payment\PaymentCreatorFactory;
use WCPayPalPlus\WC\Payment\PaymentPatchFactory;
use WCPayPalPlus\WC\Refund\RefundFactory;
use WCPayPalPlus\Service;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\WC
 */
class ServiceProvider implements Service\ServiceProvider
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
    }
}
