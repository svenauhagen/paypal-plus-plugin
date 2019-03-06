<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Ipn;

use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Order\OrderUpdaterFactory;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting;
use WCPayPalPlus\PlusGateway\Gateway;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Ipn
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Request::class] = function () {
            return new Request(filter_input_array(INPUT_POST) ?: []);
        };
        $container[Data::class] = function (Container $container) {
            return new Data(
                $container[Setting\PlusStorable::class]
            );
        };
        $container[Validator::class] = function (Container $container) {
            return new Validator(
                $container[Data::class],
                $container[Request::class]
            );
        };
        $container[Ipn::class] = function (Container $container) {
            return new Ipn(
                $container[Request::class],
                $container[Validator::class],
                $container[OrderUpdaterFactory::class],
                $container[OrderFactory::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        add_action(
            'woocommerce_api_' . Gateway::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX,
            [$container[Ipn::class], 'checkResponse']
        );
    }
}
