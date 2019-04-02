<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Admin;

use WCPayPalPlus\Admin\Notice\AjaxDismisser;
use WCPayPalPlus\Admin\Notice\Controller;
use WCPayPalPlus\Admin\Notice\GatewayNotice;
use WCPayPalPlus\Admin\Notice\NoticeRender;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WC_Payment_Gateways;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Admin
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $container[NoticeRender::class] = function () {
            return new NoticeRender();
        };
        $container[GatewayNotice::class] = function () {
            return new GatewayNotice();
        };
        $container[AjaxDismisser::class] = function (Container $container) {
            return new AjaxDismisser(
                $container[Controller::class],
                $container[Request::class]
            );
        };
        $container[Controller::class] = function (Container $container) {
            return new Controller(
                $container[NoticeRender::class]
            );
        };
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(Container $container)
    {
        $gatewaySubString = 'paypal';
        $gatewayNotice = $container[GatewayNotice::class];
        $controller = $container[Controller::class];

        add_action(
            'admin_notices',
            function () use ($controller, $gatewayNotice, $gatewaySubString) {
                $availableGateways = WC_Payment_Gateways::instance()->payment_gateways();
                foreach ($availableGateways as $method) {
                    $name = is_string($method) ? $method : get_class($method);
                    if (stripos($name, $gatewaySubString) !== false) {
                        $controller->maybeRender($gatewayNotice);
                        break;
                    }
                }
            }
        );
        add_action(
            'wp_ajax_' . AjaxDismisser::AJAX_NONCE_ACTION,
            [$container[AjaxDismisser::class], 'handle']
        );
    }
}
