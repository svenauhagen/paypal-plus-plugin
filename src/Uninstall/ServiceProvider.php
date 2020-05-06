<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Uninstall;

use WCPayPalPlus\Service\Container;
use wpdb;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Deactivation
 */
class ServiceProvider implements \WCPayPalPlus\Service\ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $option = get_option('paypalplus_shared_options');
        $cachePayPalJsFiles = wc_string_to_bool($option['cache_paypal_js_files']);
        $container->share(
            Uninstaller::class,
            function (Container $container) use ($cachePayPalJsFiles) {
                return new Uninstaller(
                    $container->get(wpdb::class),
                    $cachePayPalJsFiles ? $container->get('wp_filesystem')
                        : null
                );
            }
        );
    }
}
