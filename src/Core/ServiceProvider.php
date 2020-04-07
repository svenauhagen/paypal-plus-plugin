<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Core;

use UnexpectedValueException;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as PluginServiceProvider;
use WP_Filesystem_Base;
use wpdb;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Core
 */
class ServiceProvider implements PluginServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $container->share(
            wpdb::class,
            function () {
                global $wpdb;
                return $wpdb;
            }
        );

        $container->share(
            'wp_filesystem',
            function () {
                global $wp_filesystem;

                if (!function_exists('WP_Filesystem')) {
                    require_once ABSPATH . '/wp-admin/includes/file.php';
                }
                $args = [];
                if (get_option('ftp_credentials')) {
                    $credentials = get_option('ftp_credentials');
                    $args = [
                        'hostname' => $credentials['hostname'],
                        'username' => $credentials['username'],
                        'password' => $credentials['password'],
                    ];
                }

                $initilized = WP_Filesystem($args);

                if (!$initilized || !$wp_filesystem instanceof WP_Filesystem_Base) {
                    throw new UnexpectedValueException('There were problem in initializing Wp FileSystem');
                }

                return $wp_filesystem;
            }
        );
    }
}
