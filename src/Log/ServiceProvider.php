<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Log;

use Inpsyde\Lib\PayPal\Core\PayPalConfigManager;
use Inpsyde\Lib\Psr\Log\LoggerInterface;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Log
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    public function register(Container $container)
    {
        $container['is_wp_debug'] = function () {
            return (\defined(\WP_DEBUG) && \WP_DEBUG);
        };

        $container['is_script_debug'] = function () {
            return (\defined(\SCRIPT_DEBUG) && \SCRIPT_DEBUG);
        };

        $container[LoggerInterface::class] = function ($container) {
            return new WcPsrLoggerAdapter(
                \wc_get_logger(),
                $container['is_wp_debug'] ? \WC_Log_Levels::DEBUG : \WC_Log_Levels::INFO
            );
        };
    }

    public function bootstrap(Container $container)
    {
        $sdkConfig = PayPalConfigManager::getInstance();
        $sdkConfig->addConfigs(['log.AdapterFactory' => PayPalSdkLogFactory::class]);
    }
}
