<?php


namespace WCPayPalPlus\Banner;


use WC_Admin_Settings;
use WC_Settings_Page;

class BannerSettings extends WC_Settings_Page
{
    public function __construct()
    {
        $this->id = 'paypalplus-banner';
        $this->label = __('Paypal Banner', 'woo-paypalplus');

        parent::__construct();
    }

    public function output()
    {
        $settings = $this->get_settings();
        WC_Admin_Settings::output_fields($settings);
    }


    public function get_settings()
    {
        return $this->generalSettings();
    }

    /**
     * @return array
     */
    private function generalSettings()
    {
        return [
            'title' => [
                'title' => esc_html_x(
                    'Paypal Banner Settings',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'type' => 'title'
            ],
            'enableBanner' => [
                'title' => esc_html_x(
                    'Enable/disable Paypal Banner',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_enableBanner',
                'type' => 'checkbox',
                'default' => 'no',
            ],
            'enableHome' => [
                'title' => esc_html_x(
                    'Show in Homepage',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_home',
                'type' => 'checkbox',
                'default' => 'no',
            ],
            'enableProducts' => [
                'title' => esc_html_x(
                    'Show in Products Page',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_products',
                'type' => 'checkbox',
                'default' => 'no',
            ],
            'enableSearch' => [
                'title' => esc_html_x(
                    'Show in Search Page',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_search',
                'type' => 'checkbox',
                'default' => 'no',
            ],
            'layout' => [
                'title' => esc_html_x(
                    'Layout',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_layout',
                'type' => 'select',
                'desc' => esc_html_x(
                    'This controls the form of the banner: Text or Graphical',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'options' => [
                    'text' => esc_html_x(
                        'Text',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'flex' => esc_html_x(
                        'Graphical',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                ],
                'default' => 'text',
                'desc_tip' => true,
            ],
            'textSize' => [
                'title' => esc_html_x(
                    'Layout Text: Size',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_textSize',
                'type' => 'select',
                'desc' => esc_html_x(
                    'This controls the banner size when layout selected is Text.',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'options' => [
                    'primary' => esc_html_x(
                        'Primary',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'alternative' => esc_html_x(
                        'Alternative',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'inline' => esc_html_x(
                        'Inline',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'none' => esc_html_x(
                        'None',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                ],
                'default' => 'primary',
                'desc_tip' => true,
            ],
            'textColor' => [
                'title' => esc_html_x(
                    'Layout Text: Color',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_textColor',
                'type' => 'select',
                'desc' => esc_html_x(
                    'This controls the banner color when the layout selected is Text.',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'options' => [
                    'black' => esc_html_x(
                        'Black',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'white' => esc_html_x(
                        'White',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                ],
                'default' => 'primary',
                'desc_tip' => true,
            ],
            'flexSize' => [
                'title' => esc_html_x(
                    'Graphical Layout: Size',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_flexSize',
                'type' => 'select',
                'desc' => esc_html_x(
                    'This controls the banner size when the layout selected is Graphical.',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'options' => [
                    '8x1' => esc_html_x(
                        '8x1',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    '20x1' => esc_html_x(
                        '20x1',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    '1x1' => esc_html_x(
                        '1x1',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    '1x4' => esc_html_x(
                        '1x4',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                ],
                'default' => '8x1',
                'desc_tip' => true,
            ],
            'flexColor' => [
                'title' => esc_html_x(
                    'Graphical Color',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'id' => 'banner_settings_flexColor',
                'type' => 'select',
                'desc' => esc_html_x(
                    'This controls the banner color when the layout selected is Graphical.',
                    'banner_settings',
                    'woo-paypalplus'
                ),
                'options' => [
                    'black' => esc_html_x(
                        'Black',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'white' => esc_html_x(
                        'White',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'gray' => esc_html_x(
                        'Gray',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                    'blue' => esc_html_x(
                        'Blue',
                        'banner_settings',
                        'woo-paypalplus'
                    ),
                ],
                'default' => 'blue',
                'desc_tip' => true,
            ],
            'sectionend' => [
                'type' => 'sectionend',
                'id' => 'banner_settings',
            ]
        ];
    }
}