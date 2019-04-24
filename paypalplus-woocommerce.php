<?php # -*- coding: utf-8 -*-
// phpcs:disable

/**
 * Plugin Name: PayPal PLUS for WooCommerce
 * Description: PayPal Plus - the official WordPress Plugin for WooCommerce
 * Author: Inpsyde GmbH
 * Author URI: https://inpsyde.com/
 * Version: 2.0.2
 * WC requires at least: 3.2.0
 * WC tested up to: 3.6.1
 * License: GPLv2+
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
                    PHP_VERSION
                )
            );

            return false;
        }

        return true;
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

        if (version_compare(wc()->version, '3.0.0', '<')) {
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
     * @return bool
     * @throws \Exception
     */
    function bootstrap()
    {
        if (!versionCheck()) {
            return false;
        }
        if (!wooCommerceCheck()) {
            return false;
        }
        // Plugin doesn't work well with cron because of WooCommerce Session.
        // To now spread conditional here and there since we don't actually need to do stuffs
        // during cron I have disabled the plugin here.
        if (defined('DOING_CRON') && DOING_CRON) {
            return false;
        }

        /** @noinspection BadExceptionsProcessingInspection */
        try {
            /** @var Container $container */
            $container = resolve();
            $container = $container->shareValue(
                PluginProperties::class,
                new PluginProperties(__FILE__)
            );

            $providers = new ServiceProvidersCollection();
            $providers
                ->add(new Install\ServiceProvider())
                ->add(new Utils\ServiceProvider())
                ->add(new Notice\ServiceProvider())
                ->add(new Assets\ServiceProvider())
                ->add(new Session\ServiceProvider())
                ->add(new Setting\ServiceProvider())
                ->add(new Request\ServiceProvider())
                ->add(new Admin\ServiceProvider())
                ->add(new Gateway\ServiceProvider())
                ->add(new WC\ServiceProvider())
                ->add(new Ipn\ServiceProvider())
                ->add(new Pui\ServiceProvider())
                ->add(new Log\ServiceProvider())
                ->add(new Api\ServiceProvider())
                ->add(new Order\ServiceProvider())
                ->add(new Refund\ServiceProvider())
                ->add(new Payment\ServiceProvider())
                ->add(new ExpressCheckoutGateway\ServiceProvider())
                ->add(new PlusGateway\ServiceProvider());

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
        } catch (\Exception $exc) {
            do_action(ACTION_LOG, \WC_Log_Levels::ERROR, $exc->getMessage(), compact($exc));

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw $exc;
            }

            $bootstrapped = false;
        }

        return $bootstrapped;
    }

    if (!autoload()) {
        return;
    }

    add_action('plugins_loaded', __NAMESPACE__ . '\\bootstrap', 0);
    add_action('init', function () {
        load_plugin_textdomain('woo-paypalplus');
    });
}, null);

$bootstrap();
