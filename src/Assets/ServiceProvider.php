<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Assets;

use WCPayPalPlus\PluginProperties;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;

class ServiceProvider implements BootstrappableServiceProvider
{
    public function register(Container $container)
    {
        $container[AssetManager::class] = function (Container $container) {
            return new AssetManager(
                $container[PluginProperties::class]
            );
        };
    }

    public function bootstrap(Container $container)
    {
        if (is_admin()) {
            add_action(
                'admin_enqueue_scripts',
                [$container[AssetManager::class], 'enqueueAdminStyles']
            );
            add_action(
                'admin_enqueue_scripts',
                [$container[AssetManager::class], 'enqueueAdminScripts']
            );

            return;
        }

        add_action(
            'wp_enqueue_scripts',
            [$container[AssetManager::class], 'enqueueFrontEndScripts']
        );
    }
}
