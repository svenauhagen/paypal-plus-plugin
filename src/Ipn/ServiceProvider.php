<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Ipn;

use WCPayPalPlus\Pui\Renderer;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusRepository;
use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Ipn
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    public function register(Container $container)
    {
        $container[Data::class] = function (Container $container) {
            return new Data(
                filter_input_array(INPUT_POST) ?: [],
                $container[PlusRepository::class]->isSandboxed()
            );
        };
        $container[Validator::class] = function (Container $container) {
            return new Validator(
                $container[Data::class]
            );
        };
        $container[Ipn::class] = function (Container $container) {
            return new Ipn(
                $container[Data::class],
                $container[Validator::class]
            );
        };
    }

    public function bootstrap(Container $container)
    {
        add_action(
            'woocommerce_api_' . PayPalPlusGateway::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX,
            [$container[Ipn::class], 'checkResponse']
        );
    }
}
