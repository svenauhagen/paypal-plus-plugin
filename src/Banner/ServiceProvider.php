<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Banner;

use WCPayPalPlus\Admin\Notice\AjaxDismisser;
use WCPayPalPlus\Admin\Notice\Notice;
use WCPayPalPlus\Admin\Notice\Controller;
use WCPayPalPlus\Admin\Notice\Noticeable;
use WCPayPalPlus\Admin\Notice\NoticeRender;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\SharedSettingsModel;

/**
 * Class ServiceProvider
 *
 * @package WCPayPalPlus\PlusGateway
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $urlBannerSettings = admin_url(
            'admin.php?page=wc-settings&tab=paypalplus-banner'
        );
        $container[NoticeRender::class] = function () {
            return new NoticeRender();
        };
        $container['banner_notice'] = function () use ($urlBannerSettings) {
            return new Notice(
                Noticeable::WARNING,
                "<p>Check out the new Paypal Banner feature. <a id='bannerLink' href={$urlBannerSettings}>To enable it click here</a></p>",
                true,
                'WCPayPalPlus\Admin\Notice\BannerNotice'
            );
        };
        $container[Controller::class] = function (Container $container) {
            return new Controller(
                $container[NoticeRender::class]
            );
        };
        $container[AjaxDismisser::class] = function (Container $container) {
            return new AjaxDismisser(
                $container[Controller::class],
                $container[Request::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $bannerNotice = $container['banner_notice'];
        $controller = $container[Controller::class];
        add_action(
            'admin_notices',
            function () use (
                $controller,
                $bannerNotice
            ) {
                global $pagenow;
                if ($pagenow == 'plugins.php' || $pagenow == 'index.php'
                    || $pagenow == 'admin.php'
                ) {
                    $controller->maybeRender($bannerNotice);
                }
            }
        );

        add_action(
            'wp_ajax_enable_banner',
            function () use ($controller) {
                update_option('banner_settings_enableBanner', 'yes');
                $controller->dismiss('WCPayPalPlus\Admin\Notice\BannerNotice');
            }
        );

        add_filter(
            'woocommerce_get_settings_pages',
            function ($settings) {
                $settings[] = new BannerSettings();

                return $settings;
            }
        );
    }
}
