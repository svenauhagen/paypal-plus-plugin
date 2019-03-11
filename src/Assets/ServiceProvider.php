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
use WCPayPalPlus\Setting\PlusStorable;

class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[AssetManager::class] = function (Container $container) {
            return new AssetManager(
                $container[PluginProperties::class],
                $container[SmartButtonArguments::class]
            );
        };
        $container[SmartButtonArguments::class] = function (Container $container) {
            return new SmartButtonArguments(
                $container[PlusStorable::class]
            );
        };
        $container[PayPalAssetManager::class] = function () {
            return new PayPalAssetManager();
        };
    }

    /**
     * @inheritdoc
     */
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
            [$container[PayPalAssetManager::class], 'enqueueFrontEndScripts']
        );

        add_action(
            'wp_enqueue_scripts',
            [$container[AssetManager::class], 'enqueueFrontEndScripts']
        );
        add_action(
            'wp_enqueue_scripts',
            [$container[AssetManager::class], 'enqueueFrontendStyles']
        );
    }
}
