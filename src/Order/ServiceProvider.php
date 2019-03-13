<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Order;

use WC_Logger_Interface as Logger;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;
use WCPayPalPlus\Setting\Storable;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Order
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[OrderStatuses::class] = function () {
            return new OrderStatuses();
        };
        $container[OrderFactory::class] = function () {
            return new OrderFactory();
        };
        $container[OrderUpdaterFactory::class] = function (Container $container) {
            return new OrderUpdaterFactory(
                $container[WooCommerce::class],
                $container[OrderStatuses::class],
                $container[OrderFactory::class],
                $container[Request::class],
                $container[Storable::class],
                $container[Logger::class]
            );
        };
    }
}
