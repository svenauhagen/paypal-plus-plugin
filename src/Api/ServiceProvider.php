<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Api;

use Inpsyde\Lib\PayPal\Auth\OAuthTokenCredential;
use Inpsyde\Lib\PayPal\Core\PayPalConfigManager;
use Inpsyde\Lib\PayPal\Core\PayPalCredentialManager;
use WCPayPalPlus\Log\PayPalSdkLogFactory;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\IntegrationServiceProvider;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\PlusGateway\Gateway;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Api
 */
class ServiceProvider implements IntegrationServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[PayPalConfigManager::class] = function () {
            return PayPalConfigManager::getInstance();
        };
        $container[PayPalCredentialManager::class] = function () {
            return PayPalCredentialManager::getInstance();
        };
        $container[CredentialProvider::class] = function () {
            return new CredentialProvider();
        };
        $container[CredentialValidator::class] = function () {
            return new CredentialValidator();
        };
    }

    /**
     * @inheritdoc
     */
    public function integrate(Container $container)
    {
        $container[PayPalConfigManager::class]->addConfigs(
            [
                'mode' => $container[PlusStorable::class]->isSandboxed() ? 'SANDBOX' : 'LIVE',
                'http.headers.PayPal-Partner-Attribution-Id' => 'WooCommerce_Cart_Plus',
            ]
        );

        if (\class_exists(PayPalSdkLogFactory::class)) {
            $container[PayPalConfigManager::class]->addConfigs(
                [
                    'log.LogEnabled' => 1,
                    'log.LogLevel' => $container[PlusStorable::class]->isSandboxed()
                        ? \WC_Log_Levels::DEBUG : \WC_Log_Levels::INFO,
                    'log.AdapterFactory' => PayPalSdkLogFactory::class,
                ]
            );
        }

        if (\is_writable(\get_temp_dir())) {
            $container[PayPalConfigManager::class]->addConfigs(
                [
                    'cache.enabled' => true,
                    'cache.FileName' => \get_temp_dir() . '/.ppp_auth.cache',
                ]
            );
        }

        $container[PayPalCredentialManager::class]->setCredentialObject(
            new OAuthTokenCredential(
                $container[Gateway::class]->get_option('rest_client_id'),
                $container[Gateway::class]->get_option('rest_secret_id')
            )
        );
        if ($container[PlusStorable::class]->isSandboxed()) {
            $container[PayPalCredentialManager::class]->setCredentialObject(
                new OAuthTokenCredential(
                    $container[Gateway::class]->get_option('rest_client_id_sandbox'),
                    $container[Gateway::class]->get_option('rest_secret_id_sandbox')
                )
            );
        }
    }
}
