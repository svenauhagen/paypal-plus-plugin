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

use WC_Logger_Interface as Logger;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Order\OrderUpdaterFactory;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting;
use WCPayPalPlus\PlusGateway\Gateway as PlusGateway;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressGateway;

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
        $container[IpnVerifier::class] = function (Container $container) {
            return new IpnVerifier(
                $container[Request::class],
                $container[Setting\Storable::class],
                $container[Logger::class]
            );
        };
        $container[Ipn::class] = function (Container $container) {
            return new Ipn(
                $container[Request::class],
                $container[IpnVerifier::class],
                $container[OrderUpdaterFactory::class],
                $container[OrderFactory::class],
                $container[Logger::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        add_action(
            'woocommerce_api_' . PlusGateway::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX,
            [$container[Ipn::class], 'checkResponse']
        );
        add_action(
            'woocommerce_api_' . ExpressGateway::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX,
            [$container[Ipn::class], 'checkResponse']
        );
    }
}
