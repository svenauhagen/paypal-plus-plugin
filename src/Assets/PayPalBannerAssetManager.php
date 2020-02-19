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
            'paypalplus-woocommerce-paypalBanner',
            "{$assetUrl}/public/js/paypalBanner.min.js",
            ['jquery'],
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

        return $this->isWooCommerceRequiredContext()
            || $this->isBannerEnabledWCContext($settings['optional_pages']);
    }

    protected function bannerSettings()
    {
        $scriptUrl = $this->paypalScriptUrl();
        $enabledBanner = wc_string_to_bool(
            get_option('banner_settings_enableBanner', 'no')
        );
        $showHome = wc_string_to_bool(
            get_option('banner_settings_home', 'no')
        );
        $showCategoryProducts = wc_string_to_bool(
            get_option('banner_settings_products', 'no')
        );
        $showSearchResults = wc_string_to_bool(
            get_option('banner_settings_search', 'no')
        );
        $amount = $this->calculateAmount();

        $settings = [
            'amount' => $amount,
            'script_url' => $scriptUrl,
            'enabled_banner' => $enabledBanner,
            'optional_pages' => [
                'show_home' => $showHome,
                'show_category' => $showCategoryProducts,
                'show_search' => $showSearchResults,
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

    protected function isWooCommerceRequiredContext()
    {
        return is_cart() || is_checkout() || is_product();
    }

    protected function isBannerEnabledWCContext($settings)
    {
        return (is_home() && isset($settings['show_home'])
                ? $settings['show_home'] : false)
            || (is_shop() && isset($settings['show_category'])
                ? $settings['show_category'] : false)
            || (is_search() && isset($settings['show_search'])
                ? $settings['show_search'] : false);
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
        $clientId = $this->sharedRepository->clientIdProduction();
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
        if (is_shop() || is_category()) {
            return 'woocommerce_before_shop_loop';
        }
    }
}
