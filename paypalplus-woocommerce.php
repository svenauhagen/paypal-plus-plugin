<?php
/**
 * Plugin Name: PayPal PLUS for WooCommerce
 * Description: PayPal Plus - the official WordPress Plugin for WooCommerce
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com/
 * Version:     1.1.0
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.5
 * License:     MIT
 * Text Domain: woo-paypalplus
 * Domain Path: /languages/
 */

namespace WCPayPalPlus;

add_action('plugins_loaded', function () {

    $minPhpVersion = '5.6';
    $adminNotice = function ($message) {
        add_action('admin_notices', function () use ($message) {
            $class = 'notice notice-error';
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        });
    };

    load_plugin_textdomain(
        'woo-paypalplus',
        false,
        plugin_basename(dirname(__FILE__)) . '/languages'
    );

    if (!version_compare(phpversion(), $minPhpVersion, '>=')) {
        $adminNotice(
            sprintf(
                __(
                    'PayPal PLUS requires PHP version %1$1s or higher. You are running version %2$2s ',
                    'woo-paypalplus'
                ),
                $minPhpVersion,
                phpversion()
            )
        );

        return;
    }

    if (!class_exists('WCPayPalPlus\\Plugin')) {
        if (!file_exists($autoloader = __DIR__ . '/vendor/autoload.php')) {
            $adminNotice(
                __(
                    'Could not find a working autoloader for PayPal PLUS.',
                    'woo-paypalplus'
                )
            );
            return;
        }

        /** @noinspection PhpIncludeInspection */
        require $autoloader;
    }

    if (!class_exists('WooCommerce')) {
        $adminNotice(__('PayPal PLUS requires WooCommerce to be active.', 'woo-paypalplus'));
        return;
    }

    if (version_compare(WC()->version, '3.0.0', '<=')) {
        add_action('admin_notices', function () {
            $class = 'notice notice-error';
            $message = __(
                'PayPal PLUS requires WooCommerce version 3.0 or higher .',
                'woo-paypalplus'
            );

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        });
        return;
    }

    (new PayPalModel72MonkeyPatch)->setHandler();

    $plugin = new Plugin(__FILE__);
    $plugin->init();
});
