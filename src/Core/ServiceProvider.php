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
    const HOSTNAME = 'hostname';
    const USERNAME = 'username';
    const PASSWORD = 'password';

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
            'cache_PayPal_Js_Files',
            function () {
                $uploadDir = wp_upload_dir();
                $uploadBaseDir = isset($uploadDir['basedir']) ? $uploadDir['basedir'] : '';
                $uploadUrl = isset($uploadDir['baseurl']) ? $uploadDir['baseurl'] : '';
                $option = get_option('paypalplus_shared_options');
                $cachePayPalJsFiles = isset($option['cache_paypal_js_files']) ? $option['cache_paypal_js_files'] : false;
                $cachedPayPalJsFiles = wc_string_to_bool($cachePayPalJsFiles);
                $expressCheckoutFilePath = "{$uploadBaseDir}/woo-paypalplus/resources/js/paypal/expressCheckout.min.js";
                $paypalPlusFilePath = "{$uploadBaseDir}/woo-paypalplus/resources/js/paypal/payPalplus.min.js";
                if (!$uploadBaseDir || !$uploadUrl
                ) {
                    return false;
                }
                if (file_exists($expressCheckoutFilePath)
                    || file_exists($paypalPlusFilePath)
                ) {
                    update_option(
                        'paypalplus_shared_options[\'cache_paypal_js_files\']',
                        'yes'
                    );
                    return true;
                }
                return $cachedPayPalJsFiles;
            }
        );
        try {
            $cachedPayPalJsFiles = $container->get('cache_PayPal_Js_Files');
        } catch (\Exception $exception) {
            $cachedPayPalJsFiles = false;
        }

        if (!$cachedPayPalJsFiles) {
            return;
        }
        $container->share(
            'wp_filesystem',
            function () {
                global $wp_filesystem;

                if (!function_exists('WP_Filesystem')) {
                    require_once ABSPATH
                        . '/wp-admin/includes/file.php';
                }
                $args = [];
                $ftpCredentials = get_option('ftp_credentials');
                if (is_array($ftpCredentials)) {
                    $args = [
                        self::HOSTNAME => self::findKeyOrDefault($ftpCredentials, self::HOSTNAME, ''),
                        self::USERNAME => self::findKeyOrDefault($ftpCredentials, self::USERNAME, ''),
                        self::PASSWORD => self::findKeyOrDefault($ftpCredentials, self::PASSWORD, ''),
                    ];
                }

                $initilized = WP_Filesystem($args);

                if (!$initilized || !$wp_filesystem instanceof WP_Filesystem_Base) {
                    throw new UnexpectedValueException('Wp_FileSystem cannot be initialized');
                }

                if ($wp_filesystem->errors->has_errors()) {
                    throw new WPFilesystemException(
                        $wp_filesystem->errors,
                        "There where problems in setup the filesystem {$wp_filesystem->method}"
                    );
                }

                return $wp_filesystem;
            }
        );
    }

    protected static function findKeyOrDefault(array $haystack, $key, $default)
    {
        return isset($haystack[$key]) ? $haystack[$key] : $default;
    }
}
