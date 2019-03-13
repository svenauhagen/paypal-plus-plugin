<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Setting;

use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\PlusGateway\Gateway as PlusGateway;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Setting
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[PlusStorable::class] = function (Container $container) {
            return $container[PlusGateway::class];
        };
        $container[Storable::class] = function (Container $container) {
            return $container[SharedRepository::class];
        };
        $container[SharedRepository::class] = function () {
            return new SharedRepository();
        };
        $container[SharedSettingsModel::class] = function () {
            return new SharedSettingsModel();
        };
        $container[SharedPersistor::class] = function () {
            return new SharedPersistor();
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $sharedPersistor = $container[SharedPersistor::class];

        add_filter(
            'woocommerce_settings_api_sanitized_fields_' . PlusGateway::GATEWAY_ID,
            function (array $settings) use ($sharedPersistor) {
                $sharedPersistor->update($settings);
                return $settings;
            }
        );
        add_filter(
            'woocommerce_settings_api_sanitized_fields_' . PlusGateway::GATEWAY_ID,
            function (array $settings) use ($sharedPersistor) {
                $sharedPersistor->update($settings);
                return $settings;
            }
        );

        add_filter(
            'woocommerce_settings_api_sanitized_fields_' . ExpressCheckoutGateway::GATEWAY_ID,
            [SharedSettingsFilter::class, 'diff']
        );
        add_filter(
            'woocommerce_settings_api_sanitized_fields_' . PlusGateway::GATEWAY_ID,
            [SharedSettingsFilter::class, 'diff']
        );
    }
}
