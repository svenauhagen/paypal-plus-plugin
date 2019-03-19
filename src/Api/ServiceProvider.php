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
use WC_Logger_Interface as Logger;
use WCPayPalPlus\Log\PayPalSdkLogFactory;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\IntegrationServiceProvider;
use WCPayPalPlus\Setting\SharedRepository;
use WCPayPalPlus\Setting\Storable;
use WCPayPalPlus\PlusGateway\Gateway;
use WC_Log_Levels as LogLevels;

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
        $container[CredentialValidator::class] = function (Container $container) {
            return new CredentialValidator(
                $container[Logger::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function integrate(Container $container)
    {
        $isSandBoxed = $container[Storable::class]->isSandboxed();

        $container[PayPalConfigManager::class]->addConfigs(
            [
                'mode' => $isSandBoxed ? 'SANDBOX' : 'LIVE',
                'http.headers.PayPal-Partner-Attribution-Id' => 'WooCommerce_Cart_Plus',
            ]
        );

        $container[PayPalConfigManager::class]->addConfigs(
            [
                'log.LogEnabled' => '1',
                'log.LogLevel' => $isSandBoxed ? LogLevels::DEBUG : LogLevels::INFO,
                'log.AdapterFactory' => PayPalSdkLogFactory::class,
            ]
        );

        if (\is_writable(\get_temp_dir())) {
            $container[PayPalConfigManager::class]->addConfigs(
                [
                    'cache.enabled' => 'true',
                    'cache.FileName' => \get_temp_dir() . '/.ppp_auth.cache',
                ]
            );
        }

        // TODO Credentials have to be provided by a `CredentialProvider` class
        //      Them are needed by Express Checkout
        $container[PayPalCredentialManager::class]->setCredentialObject(
            new OAuthTokenCredential(
                $container[SharedRepository::class]->clientIdProduction(),
                $container[SharedRepository::class]->secretIdProduction()
            )
        );
        if ($isSandBoxed) {
            $container[PayPalCredentialManager::class]->setCredentialObject(
                new OAuthTokenCredential(
                    $container[SharedRepository::class]->clientIdSandBox(),
                    $container[SharedRepository::class]->secretIdSandBox()
                )
            );
        }
    }
}
