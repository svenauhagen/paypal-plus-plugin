<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.11.16
 * Time: 15:34
 */

namespace WCPayPalPlus\WC;

/**
 * Class GatewaySettingsModel
 *
 * @package WCPayPalPlus\WC
 */
class GatewaySettingsModel
{
    public function settings()
    {
        $settings =
            $this->generalSettings()
            + $this->credentialsSettings()
            + $this->webProfileSettings()
            + $this->gatewaySettings();

        return $settings;
    }

    private function generalSettings()
    {
        return [
            'enabled' => [
                'title' => _x('Enable/Disable', 'gateway-settings', 'woo-paypalplus'),
                'type' => 'checkbox',
                'label' => _x('Enable PayPal PLUS', 'gateway-settings', 'woo-paypalplus'),
                'default' => 'no',
            ],
            'title' => [
                'title' => _x('Title', 'gateway-settings', 'woo-paypalplus'),
                'type' => 'text',
                'description' => _x(
                    'This controls the name of the payment gateway the user sees during checkout.',
                    'gateway-settings',
                    'woo-paypalplus'
                ),
                'default' => _x(
                    'PayPal, Direct Debit, Credit Card and Invoice (if available)',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            'description' => [
                'title' => _x('Description', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => _x(
                    'This controls the payment gateway description the user sees during checkout.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => _x(
                    'Please choose a payment method:',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
        ];
    }

    private function credentialsSettings()
    {
        return [
            'credentials_section' => [
                'title' => _x('Credentials', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'title',
                'desc' => '',
            ],
            'testmode' => [
                'title' => _x('PayPal Sandbox', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'checkbox',
                'label' => _x('Enable PayPal Sandbox', 'gateway-setting', 'woo-paypalplus'),
                'default' => 'yes',
                'description' => sprintf(
                    _x(
                        'PayPal sandbox can be used to test payments. Sign up for a <a href="%s">developer account</a>.',
                        'gateway-setting',
                        'woo-paypalplus'
                    ),
                    'https://developer.paypal.com/'
                ),
            ],
            'rest_client_id_sandbox' => [
                'title' => _x('Sandbox Client ID', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => _x(
                    'Enter your PayPal REST Sandbox API Client ID.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
            ],
            'rest_secret_id_sandbox' => [
                'title' => _x('Sandbox Secret ID', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => _x(
                    'Enter your PayPal REST Sandbox API Secret ID.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
            ],
            'sandbox_experience_profile_id' => [
                'title' => _x('Sandbox Experience Profile ID', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => _x(
                    'This value will be automatically generated and populated here when you save your settings.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field readonly',
            ],
            'rest_client_id' => [
                'title' => _x('Live Client ID', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => _x(
                    'Enter your PayPal REST Live API Client ID.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
            ],
            'rest_secret_id' => [
                'title' => _x('Live Secret ID', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'password',
                'description' => _x(
                    'Enter your PayPal REST Live API Secret ID.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field',
            ],
            'live_experience_profile_id' => [
                'title' => _x('Experience Profile ID', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => _x(
                    'This value will be automatically generated and populated here when you save your settings.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => '',
                'class' => 'credential_field readonly',
            ],
        ];
    }

    private function webProfileSettings()
    {
        return [
            'web_profile_section' => [
                'title' => _x('Web Profile', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'title',
                'desc' => '',
            ],
            'brand_name' => [
                'title' => _x('Brand Name', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => _x(
                    'This will be displayed as your brand / company name on the PayPal checkout pages.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => get_bloginfo('name'),
            ],
            'checkout_logo' => [
                'title' => __('PayPal Checkout Logo (190x60px)', 'woo-paypalplus'),
                'type' => 'text',
                'description' => sprintf(
                    _x(
                        'Set the absolute URL for a logo to be displayed on the PayPal checkout pages. <br/> Use https and max 127 characters.(E.G., %s).',
                        'gateway-setting',
                        'woo-paypalplus'
                    ),
                    get_site_url() . '/path/to/logo.jpg'
                ),
                'default' => '',
                'custom_attributes' => [
                    'required' => 'required',
                    'pattern' => '^https://.*',
                ],
            ],
        ];
    }

    private function gatewaySettings()
    {
        $wcTabsLogUrl = get_admin_url(null, 'admin.php') . '?page=wc-status&tab=logs';

        return [
            'settings_section' => [
                'title' => _x('Settings', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'title',
                'desc' => '',
            ],
            'invoice_prefix' => [
                'title' => _x('Invoice Prefix', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => _x(
                    'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => $this->defaultInvoicePrefix(),
                'desc_tip' => true,
            ],
            'cancel_url' => [
                'title' => _x('Cancel Page', 'gateway-setting', 'woo-paypalplus'),
                'description' => _x(
                    'Sets the page users will be returned to if they click the Cancel link on the PayPal checkout pages.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'select',
                'options' => $this->cancelPageOptions(),
                'default' => wc_get_page_id('checkout'),
            ],
            'cancel_custom_url' => [
                'title' => _x('Custom Cancelation URL', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => _x(
                    'URL to a custom page to be used for cancelation. Please select "custom" above first.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            'legal_note' => [
                'title' => _x(
                    'Legal Note for PAY UPON INVOICE Payment',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'textarea',
                'description' => __(
                    'legal note that will be added to the thank you page and emails.',
                    'woo-paypalplus'
                ),
                'default' => __(
                    'Dealer has ceeded the claim against you within the framework of an ongoing factoring contract to the PayPal (Europe) S.àr.l. et Cie, S.C.A.. Payments with a debt-free effect can only be paid to the PayPal (Europe) S.àr.l. et Cie, S.C.A.',
                    'woo-paypalplus'
                ),
                'desc_tip' => false,
            ],
            'pay_upon_invoice_instructions' => [
                'title' => _x('Pay upon Invoice Instructions', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'textarea',
                'description' => _x(
                    'Pay upon Invoice Instructions that will be added to the thank you page and emails.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => _x(
                    'Please transfer the complete amount to the bank account provided below.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'desc_tip' => false,
            ],
            'download_log' => [
                'title' => _x('Download Log File', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'html',
                'html' => '<p>' .
                    sprintf(
                        _x(
                            'Please go to <a href="%s">WooCommerce => System Status => Logs</a>, select the file <em>paypal_plus-....log</em>, copy the content and attach it to your ticket when contacting support.',
                            'gateway-setting',
                            'woo-paypalplus'
                        ),
                        $wcTabsLogUrl
                    )
                    . '</p>',
            ],
            'disable_gateway_override' => [
                'title' => _x(
                    'Disable default gateway override',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'checkbox',
                'label' => _x('Disable', 'gateway-setting', 'woo-paypalplus'),
                'default' => 'no',
                'description' => _x(
                    'PayPal PLUS will be selected as default payment gateway regardless of its position in the list of enabled gateways. You can turn off this behaviour here',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
        ];
    }

    private function defaultInvoicePrefix()
    {
        return 'WC-PPP-' . strtoupper(sanitize_title(get_bloginfo('name'))) . '-';
    }

    private function cancelPageOptions()
    {
        $keys = [
            'cart' => _x('Cart', 'gateway-setting', 'woo-paypalplus'),
            'checkout' => _x('Checkout', 'gateway-setting', 'woo-paypalplus'),
            'account' => _x('Account', 'gateway-setting', 'woo-paypalplus'),
            'shop' => _x('Shop', 'gateway-setting', 'woo-paypalplus'),
            'custom' => _x('Custom', 'gateway-setting', 'woo-paypalplus'),
        ];

        $options = [];
        foreach ($keys as $key => $title) {
            $options[$key] = $title;
        }

        return $options;
    }
}
