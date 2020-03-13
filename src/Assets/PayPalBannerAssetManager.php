<?php

namespace WCPayPalPlus\Assets;

use WCPayPalPlus\PluginProperties;
use WCPayPalPlus\Setting\SharedRepository;

class PayPalBannerAssetManager
{
    use AssetManagerTrait;
    /**
     * @var PluginProperties
     */
    private $pluginProperties;
    /**
     * @var SharedRepository
     */
    private $sharedRepository;

    /**
     * AssetManager constructor.
     *
     * @param PluginProperties $pluginProperties
     * @param SharedRepository $sharedRepository
     */
    public function __construct(
        PluginProperties $pluginProperties,
        SharedRepository $sharedRepository
    ) {
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->pluginProperties = $pluginProperties;
        $this->sharedRepository = $sharedRepository;
    }

    public function enqueuePPBannerFrontEndScripts()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();
        wp_register_script(
            'paypal-sdk',
            $this->paypalScriptUrl(),
            [],
            null,
            true
        );
        wp_register_script(
            'paypalplus-woocommerce-paypalBanner',
            "{$assetUrl}/public/js/paypalBanner.min.js",
            ['jquery', 'paypal-sdk'],
            filemtime("{$assetPath}/public/js/paypalBanner.min.js"),
            true
        );

        if (!$this->isAllowedContext($this->bannerSettings())) {
            return;
        }
        $this->conditionallyEnqueueScript();
    }

    protected function isAllowedContext(array $settings)
    {
        if (!$settings['enabled_banner']) {
            return false;
        }

        return $this->isBannerEnabledWCContext($settings['optional_pages']);
    }

    protected function bannerSettings()
    {
        $scriptUrl = $this->paypalScriptUrl();
        $amount = $this->calculateAmount();

        $settings = [
            'amount' => $amount,
            'script_url' => $scriptUrl,
            'enabled_banner' => $this->isEnabledShowBannerInPage(
                'banner_settings_enableBanner'
            ),
            'optional_pages' => [
                'show_home' => $this->isEnabledShowBannerInPage(
                    'banner_settings_home'
                ),
                'show_category' => $this->isEnabledShowBannerInPage(
                    'banner_settings_products'
                ),
                'show_search' => $this->isEnabledShowBannerInPage(
                    'banner_settings_search'
                ),
                'show_product' => $this->isEnabledShowBannerInPage(
                    'banner_settings_product_detail'
                ),
                'show_cart' => $this->isEnabledShowBannerInPage(
                    'banner_settings_cart'
                ),
                'show_checkout' => $this->isEnabledShowBannerInPage(
                    'banner_settings_checkout'
                ),
            ],
            'style' => [
                'layout' => get_option('banner_settings_layout'),
                'logo' => [
                    'type' => get_option('banner_settings_textSize'),
                    'color' => get_option('banner_settings_textColor'),
                ],
                'color' => get_option('banner_settings_flexColor'),
                'ratio' => get_option('banner_settings_flexSize'),
            ],
        ];

        return $settings;
    }

    protected function conditionallyEnqueueScript()
    {
        add_action(
            'wp_footer',
            function () {
                $this->showBanner();
            }
        );
        $this->placeBannerOnPage();
    }

    protected function showBanner()
    {
        $settings = $this->bannerSettings();
        list($assetPath, $assetUrl) = $this->assetUrlPath();
        wp_enqueue_script(
            'paypalplus-woocommerce-paypalBanner',
            "{$assetUrl}/public/js/paypalBanner.min.js",
            ['jquery'],
            filemtime("{$assetPath}/public/js/paypalBanner.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-woocommerce-paypalBanner',
            'paypalBannerFrontData',
            [
                'settings' => $settings,
            ]
        );
    }

    protected function isBannerEnabledWCContext($settings)
    {
        return (is_home() && isset($settings['show_home'])
                ? $settings['show_home'] : false)
            || (is_shop() && isset($settings['show_category'])
                ? $settings['show_category'] : false)
            || (is_search() && isset($settings['show_search'])
                ? $settings['show_search'] : false)
            || (is_product() && isset($settings['show_product'])
                ? $settings['show_product'] : false)
            || (is_cart() && isset($settings['show_cart'])
                ? $settings['show_cart'] : false)
            || (is_checkout() && isset($settings['show_checkout'])
                ? $settings['show_checkout'] : false);
    }

    protected function calculateAmount()
    {
        wc_load_cart();

        $amount = WC()->cart->get_total('edit');
        if (is_product()) {
            return $amount + wc_get_product()->get_price('edit');
        }

        return $amount;
    }

    protected function paypalScriptUrl()
    {
        $clientId = get_option('banner_settings_clientID');
        if (empty($clientId)) {
            $clientId = $this->sharedRepository->clientIdProduction();
            update_option('banner_settings_clientID', $clientId);
        }
        $currency = get_woocommerce_currency();
        if (!isset($clientId) || !isset($currency)) {
            return '';
        }

        return "https://www.paypal.com/sdk/js?client-id={$clientId}&components=messages&currency={$currency}";
    }

    protected function placeBannerOnPage()
    {
        $hook = $this->hookForCurrentPage();
        add_action(
            $hook,
            function () {
                ?>
                <div id="paypal-credit-banner"></div>
                <?php
            }
        );
        if (is_home()) {
            add_filter(
                'the_content',
                function ($content) {
                    return '<div id="paypal-credit-banner"></div>' . $content;
                }
            );
        }
        if (is_search()) {
            do_action('show_paypal_banner_search');
        }
    }

    protected function hookForCurrentPage()
    {
        if (is_cart()) {
            return 'woocommerce_before_cart';
        }
        if (is_checkout()) {
            return 'woocommerce_checkout_before_customer_details';
        }
        if (is_product()) {
            return 'woocommerce_before_single_product_summary';
        }
        if (is_shop() || is_category() || is_search()) {
            return 'woocommerce_before_shop_loop';
        }
    }

    /**
     * @param $option
     *
     * @return bool
     */
    protected function isEnabledShowBannerInPage($option)
    {
        return wc_string_to_bool(
            get_option($option, 'no')
        );
    }
}
