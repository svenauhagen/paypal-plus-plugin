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

use WC_Payment_Gateway as Gateway;

/**
 * Class SharedSettingsModel
 * @package WCPayPalPlus\Setting
 */
class SharedSettingsModel
{
    const OPTION_DOWNLOAD_LOG = 'download_log';

    const SHARED_OPTIONS = [
        Storable::OPTION_TEST_MODE_NAME => FILTER_DEFAULT,
        Storable::OPTION_CLIENT_ID_SANDBOX => FILTER_SANITIZE_STRING,
        Storable::OPTION_SECRET_ID_SANDBOX => FILTER_SANITIZE_STRING,
        Storable::OPTION_CLIENT_ID => FILTER_SANITIZE_STRING,
        Storable::OPTION_SECRET_ID => FILTER_SANITIZE_STRING,
        Storable::OPTION_PROFILE_ID_SANDBOX_NAME => FILTER_SANITIZE_STRING,
        Storable::OPTION_PROFILE_ID_PRODUCTION_NAME => FILTER_SANITIZE_STRING,
        Storable::OPTION_PROFILE_CHECKOUT_LOGO => FILTER_VALIDATE_URL,
        Storable::OPTION_PROFILE_BRAND_NAME => FILTER_SANITIZE_STRING,
        self::OPTION_DOWNLOAD_LOG => FILTER_DEFAULT,
    ];

    /**
     * @return array
     */
    public function credentials()
    {
        return [
            'credentials_section' => [
                'title' => esc_html_x('Credentials', 'shared-setting', 'woo-paypalplus'),
                'type' => 'title',
                'desc' => '',
            ],
            Storable::OPTION_TEST_MODE_NAME => [
                'title' => esc_html_x('PayPal Sandbox', 'shared-setting', 'woo-paypalplus'),
                'type' => 'checkbox',
                'label' => esc_html_x('Enable PayPal Sandbox', 'shared-setting', 'woo-paypalplus'),
                'default' => 'yes',
                'description' => sprintf(
                    esc_html_x(
                        'PayPal sandbox can be used to test payments. Sign up for a <a href="%s">developer account</a>.',
                        'shared-setting',
                        'woo-paypalplus'
                    ),
                    'https://developer.paypal.com/'
                ),
            ],
            Storable::OPTION_CLIENT_ID_SANDBOX => [
                'title' => esc_html_x('Sandbox Client ID', 'shared-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => esc_html_x(
                    'Enter your PayPal REST Sandbox API Client ID.',
                    'shared-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
                'sanitize_callback' => function ($value) {
                    return (string)filter_var(
                        $value,
                        self::SHARED_OPTIONS[Storable::OPTION_CLIENT_ID_SANDBOX]
                    );
                },
            ],
            Storable::OPTION_SECRET_ID_SANDBOX => [
                'title' => esc_html_x('Sandbox Secret ID', 'shared-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => esc_html_x(
                    'Enter your PayPal REST Sandbox API Secret ID.',
                    'shared-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
                'sanitize_callback' => function ($value) {
                    return (string)filter_var(
                        $value,
                        self::SHARED_OPTIONS[Storable::OPTION_SECRET_ID_SANDBOX]
                    );
                },
            ],
            Storable::OPTION_CLIENT_ID => [
                'title' => esc_html_x('Live Client ID', 'shared-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => esc_html_x(
                    'Enter your PayPal REST Live API Client ID.',
                    'shared-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
                'sanitize_callback' => function ($value) {
                    return (string)filter_var(
                        $value,
                        self::SHARED_OPTIONS[Storable::OPTION_CLIENT_ID]
                    );
                },
            ],
            Storable::OPTION_SECRET_ID => [
                'title' => esc_html_x('Live Secret ID', 'shared-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => esc_html_x(
                    'Enter your PayPal REST Live API Secret ID.',
                    'shared-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
                'sanitize_callback' => function ($value) {
                    return (string)filter_var(
                        $value,
                        self::SHARED_OPTIONS[Storable::OPTION_SECRET_ID]
                    );
                },
            ],
            Storable::OPTION_PROFILE_ID_SANDBOX_NAME => [
                'title' => esc_html_x(
                    'Sandbox Experience Profile ID',
                    'shared-setting',
                    'woo-paypalplus'
                ),
                'type' => 'text',
                'description' => esc_html_x(
                    'This value will be automatically generated and populated here when you save your settings.',
                    'shared-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'custom_attributes' => [
                    'readonly' => 'readonly',
                ],
                'class' => 'credential_field readonly',
                'sanitize_callback' => function ($value) {
                    return (string)filter_var(
                        $value,
                        self::SHARED_OPTIONS[Storable::OPTION_PROFILE_ID_SANDBOX_NAME]
                    );
                },
            ],
            Storable::OPTION_PROFILE_ID_PRODUCTION_NAME => [
                'title' => esc_html_x('Experience Profile ID', 'shared-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => esc_html_x(
                    'This value will be automatically generated and populated here when you save your settings.',
                    'shared-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'custom_attributes' => [
                    'readonly' => 'readonly',
                ],
                'class' => 'credential_field readonly',
                'sanitize_callback' => function ($value) {
                    return (string)filter_var(
                        $value,
                        self::SHARED_OPTIONS[Storable::OPTION_PROFILE_ID_PRODUCTION_NAME]
                    );
                },
            ],
        ];
    }

    /**
     * @param Gateway $gateway
     * @return array
     */
    public function webProfile(Gateway $gateway)
    {
        return [
            'web_profile_section' => [
                'title' => esc_html_x('Web Profile', 'shared-settings', 'woo-paypalplus'),
                'type' => 'title',
                'desc' => '',
            ],
            Storable::OPTION_PROFILE_BRAND_NAME => [
                'title' => esc_html_x('Brand Name', 'shared-settings', 'woo-paypalplus'),
                'type' => 'text',
                'description' => esc_html_x(
                    'This will be displayed as your brand / company name on the PayPal checkout pages.',
                    'shared-settings',
                    'woo-paypalplus'
                ),
                'default' => get_bloginfo('name'),
                'sanitize_callback' => function ($value) {
                    return (string)filter_var(
                        $value,
                        self::SHARED_OPTIONS[Storable::OPTION_PROFILE_BRAND_NAME]
                    );
                },
            ],
            Storable::OPTION_PROFILE_CHECKOUT_LOGO => [
                'title' => __('PayPal Checkout Logo (190x60px)', 'woo-paypalplus'),
                'type' => 'text',
                'description' => sprintf(
                    esc_html_x(
                        'Set the absolute URL for a logo to be displayed on the PayPal checkout pages. <br/> Use https and max 127 characters.(E.G., %s).',
                        'shared-settings',
                        'woo-paypalplus'
                    ),
                    get_site_url() . '/path/to/logo.jpg'
                ),
                'default' => '',
                'custom_attributes' => [
                    'required' => 'required',
                    'pattern' => '^https://.*',
                ],
                'sanitize_callback' => function ($url) use ($gateway) {
                    return $this->sanitizeLogoUrl($url, $gateway);
                },
            ],
        ];
    }

    /**
     * @return array
     */
    public function downloadLog()
    {
        $settingTabLogUrl = add_query_arg(
            [
                'page' => 'wc-status',
                'tab' => 'logs',
            ],
            get_admin_url(null, 'admin.php')
        );

        return [
            self::OPTION_DOWNLOAD_LOG => [
                'title' => esc_html_x('Download Log File', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'html',
                'html' => '<p>' .
                    sprintf(
                        _x(
                            'Please go to <a href="%s">WooCommerce => System Status => Logs</a>, select the file <em>paypal_plus-....log</em>, copy the content and attach it to your ticket when contacting support.',
                            'gateway-setting',
                            'woo-paypalplus'
                        ),
                        $settingTabLogUrl
                    )
                    . '</p>',
            ],
        ];
    }

    /**
     * Sanitize Logo Url
     *
     * @param $url
     * @param $gateway
     * @return string
     */
    private function sanitizeLogoUrl($url, $gateway)
    {
        assert($gateway instanceof Gateway);

        $url = (string)filter_var($url, FILTER_VALIDATE_URL);

        if (!$url) {
            $gateway->add_error(
                esc_html__(
                    'Checkout Logo does not match a valid url value.',
                    'woo-paypalplus'
                )
            );
            return $url;
        }

        if (strlen($url) > 127) {
            $gateway->add_error(
                esc_html_x(
                    'Checkout Logo cannot contains more than 127 characters.',
                    'shared-settings',
                    'woo-paypalplus'
                )
            );
            return '';
        }

        if (strpos($url, 'https') === false) {
            $gateway->add_error(
                esc_html_x(
                    'Checkout Logo must use the http secure protocol HTTPS. EG. (https://my-url)',
                    'shared-settings',
                    'woo-paypalplus'
                )
            );
            return '';
        }

        return $url;
    }
}
