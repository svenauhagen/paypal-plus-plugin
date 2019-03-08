<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Setting;

use WCPayPalPlus\PlusGateway\Gateway;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;
use WCPayPalPlus\Service\Container;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Setting
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[PlusStorable::class] = function (Container $container) {
            return $container[Gateway::class];
        };
        $container[Storable::class] = function (Container $container) {
            return $container[Gateway::class];
        };
    }
}
