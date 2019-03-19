<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Session;

use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Session
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Session::class] = function (Container $container) {
            return new WooCommerceSession(
                $container[WooCommerce::class]
            );
        };
    }
}
