<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Install;

use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\SharedPersistor;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Installation
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Installer::class] = function (Container $container) {
            return new Installer(
                $container[SharedPersistor::class],
                $container[ExpressCheckoutGateway::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        add_action(
            'upgrader_process_complete',
            [$container[Installer::class], 'migrateSharedOptions']
        );
        add_action(
            'upgrader_process_complete',
            [$container[Installer::class], 'activateExpressCheckout']
        );
    }
}
