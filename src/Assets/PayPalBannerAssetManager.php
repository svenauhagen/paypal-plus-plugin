<?php

namespace WCPayPalPlus\Assets;

use WooCommerce;

class PayPalBannerAssetManager
{
    public function enqueuePPBannerFrontEndScripts()
    {
        if ($this->conditionMeet($this->bannerSettings())) {
            $this->conditionallyEnqueueScript();
        }
    }

    private function conditionMeet($settings)
    {
        if (!$settings['enabled_banner']) {
            return false;
        }
        if ($this->isOfRequiredPages()
            || $this->checkEnabledOptionalPages($settings['optional_pages'])
        ) {
            return true;
        }

        return false;
    }

    private function bannerSettings()
    {
        $settings = [
            'enabled_banner' => false,
        ];
        if (get_option('banner_settings_enableBanner') === 'yes') {
            $scriptUrl = $this->paypalScriptUrl();
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
                'enabled_banner' => true,
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
        }


        return $settings;
    }

    private function conditionallyEnqueueScript()
    {
        add_action(
            'wp_head',
            function () {
                $this->showBanner();
            }
        );
        $this->placeBannerOnPage();
    }

    private function showBanner()
    {
        $settings = $this->bannerSettings();
        ?>
        <script src=<?php echo $settings['script_url'] ?> async="true"
                onload="javascript:showPayPalCreditBanners();"
                rel="preload"></script>
        <script>
          const showPayPalCreditBanners = _ => {
            let settings = <?php echo json_encode($settings) ?>;
            if (settings && settings.style.layout === 'flex') {
              paypal.Messages({
                amount: settings.amount,
                currency: 'EUR',
                style: {
                  layout: settings.style.layout,
                  color: settings.style.color,
                  ratio: settings.style.ratio
                },
              }).render('#paypal-credit-banner')
            } else {
              paypal.Messages({
                amount: settings.amount,
                currency: 'EUR',
                style: {
                  layout: settings.style.layout,
                  logo: {
                    type: settings.style.logo.type
                  },
                  text: {
                    color: settings.style.logo.color
                  }
                }
              }).render('#paypal-credit-banner')
            }
          }
        </script>
        <?php

    }

    private function isOfRequiredPages()
    {
        return is_cart() || is_checkout() || is_product();
    }

    private function checkEnabledOptionalPages($settings)
    {
        return is_home() && $settings['show_home']
            || is_shop() && $settings['show_category']
            || is_search() && $settings['show_search'];
    }

    public function calculateAmount()
    {
        $amount = WC()->cart->total;
        if (is_product()) {
            return $amount + wc_get_product()->get_price();
        }
        return $amount;
    }

    public function paypalScriptUrl()
    {
        $shared = get_option('paypalplus_shared_options');
        $clientId = $shared['rest_client_id'];
        //$clientId = 'Abjo5rvqdr44pXYgDRah68H60yGpkTJ_ooWmNAtrzPCro7besceVPiBRonN5rQ5Vby1z1g1kChdYF0KW';
        return "https://www.paypal.com/sdk/js?client-id={$clientId}&components=messages&currency=EUR";
    }

    private function placeBannerOnPage()
    {
        $hook = $this->hookForCurrentPage();
        return add_action(
            $hook,
            function () {
                ?>
                <div id="paypal-credit-banner"></div>
                <?php

            }
        );
    }

    private function hookForCurrentPage()
    {
        if (is_home() || is_search()) {
            return 'the_content';
        }
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
