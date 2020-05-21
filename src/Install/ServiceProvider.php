<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Install;

use WCPayPalPlus\Http\PayPalAssetsCache\AssetsStoreUpdater;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\SharedPersistor;
use WCPayPalPlus\Setting\Storable;

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
        try {
            $cachedPayPalJsFiles = $container->get('cache_PayPal_Js_Files');
        } catch (\Exception $exception) {
            $cachedPayPalJsFiles = false;
        }
        $container[Installer::class] = function (Container $container) use (
            $cachedPayPalJsFiles
        ) {
            return new Installer(
                $container[SharedPersistor::class],
                $cachedPayPalJsFiles ? $container->get(AssetsStoreUpdater::class)
                    : null
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $installer = $container[Installer::class];

        add_action('upgrader_process_complete', [$installer, 'afterInstall']);
        add_action('wp_loaded', function () use ($installer) {
            get_option(SharedPersistor::OPTION_NAME, null) or $installer->afterInstall();
        });
    }
}
