<?php
/**
 * Plugin Name: PayPal PLUS for WooCommerce
 * Description: PayPal Plus - the official WordPress Plugin for WooCommerce
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com/
 * Version:     1.0.9-dev
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4.0
 * License:     MIT
 * Text Domain: woo-paypalplus
 * Domain Path: /languages/
 */

namespace WCPayPalPlus;

use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvidersCollection;

const ACTION_ACTIVATION = 'wcpaypalplus.activation';
const ACTION_ADD_SERVICE_PROVIDERS = 'wcpaypalplus.add_service_providers';
const ACTION_LOG = 'wcpaypalplus.log';

$bootstrap = \Closure::bind(function () {

    /**
     * @return bool
     */
    function autoload()
    {
        $autoloader = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoloader)) {
            /** @noinspection PhpIncludeInspection */
            require $autoloader;
        }

        return class_exists(PayPalPlus::class);
    }

    /**
     * Admin Message
     * @param $message
     */
    function adminNotice($message)
    {
        add_action('admin_notices', function () use ($message) {
            $class = 'notice notice-error';
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        });
    }

    /**
     * @return bool
     */
    function versionCheck()
    {
        $minPhpVersion = '5.6';
        if (PHP_VERSION < $minPhpVersion) {
            adminNotice(
                sprintf(
                    __(
                        'PayPal PLUS requires PHP version %1$1s or higher. You are running version %2$2s ',
                        'woo-paypalplus'
                    ),
                    $minPhpVersion,
                    phpversion()
                )
            );

            return false;
        }
    }

    /**
     * @return bool
     */
    function wooCommerceCheck()
    {
        if (!function_exists('WC')) {
            adminNotice(__('PayPal PLUS requires WooCommerce to be active.', 'woo-paypalplus'));
            return false;
        }

        if (version_compare(WC()->version, '3.0.0', '<')) {
            adminNotice(
                __(
                    'PayPal PLUS requires WooCommerce version 3.0 or higher .',
                    'woo-paypalplus'
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Bootstraps PayPal PLUS for WooCommerce
     *
     * @return bool
     *
     * @wp-hook plugins_loaded
     * @throws \Throwable
     * @return bool
     */
    function bootstrap()
    {
        $bootstrapped = false;

        if (!wooCommerceCheck()) {
            return false;
        }

        try {
            /** @var Container $container */
            $container = resolve();
            $container = $container->shareValue(
                PluginProperties::class,
                new PluginProperties(__FILE__)
            );

            $providers = new ServiceProvidersCollection();
            $providers
                ->add(new Notice\ServiceProvider())
                ->add(new Assets\ServiceProvider())
                ->add(new WC\ServiceProvider());

            $payPalPlus = new PayPalPlus($container, $providers);

            /**
             * Fires right before MultilingualPress gets bootstrapped.
             *
             * Hook here to add custom service providers via
             * `ServiceProviderCollection::add_service_provider()`.
             *
             * @param ServiceProvidersCollection $providers
             */
            do_action(ACTION_ADD_SERVICE_PROVIDERS, $providers);

            $bootstrapped = $payPalPlus->bootstrap();

            unset($providers);
        } catch (\Throwable $thr) {
            do_action(ACTION_LOG, 'error', $thr->getMessage(), compact($thr));

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw $thr;
            }
        }

        return $bootstrapped;
    }

    if (!autoload()) {
        return;
    }

    (new PayPalModel72MonkeyPatch)->setHandler();

//    load_plugin_textdomain(
//        'woo-paypalplus',
//        false,
//        plugin_basename(dirname(__FILE__)) . '/languages'
//    );

    add_action('plugins_loaded', __NAMESPACE__ . '\\bootstrap', 0);
}, null);

$bootstrap();
