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
class GatewaySettingsModel {

	/**
	 * Returns all settings options
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = [];

		// General.
		$settings += [
			'enabled'     => [
				'title'   => __( 'Enable/Disable', 'paypalplus-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PayPal Plus', 'paypalplus-woocommerce' ),
				'default' => 'no',
			],
			'title'       => [
				'title'       => __( 'Title', 'paypalplus-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'This controls the name of the payment gateway the user sees during checkout.',
					'paypalplus-woocommerce'
				),
				'default'     => __( 'PayPal Plus', 'paypalplus-woocommerce' ),
			],
			'description' => [
				'title'       => __( 'Description', 'paypalplus-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'This controls the payment gateway description the user sees during checkout.',
					'paypalplus-woocommerce'
				),
				'default'     => __( 'PayPal Plus', 'paypalplus-woocommerce' ),
			],
		];

		// Credentials.
		$settings += [
			'credentials_section'           => [
				'title' => __( 'Credentials', 'paypalplus-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
			],
			'testmode'                      => [
				'title'       => __( 'PayPal Sandbox', 'paypalplus-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable PayPal Sandbox', 'paypalplus-woocommerce' ),
				'default'     => 'yes',
				'description' => sprintf(
					__( 'PayPal sandbox can be used to test payments. Sign up for a <a href="%s">developer account</a>.',
						'paypalplus-woocommerce'
					),
					'https://developer.paypal.com/'
				),
			],
			'rest_client_id_sandbox'        => [
				'title'       => __( 'Sandbox Client ID', 'paypalplus-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Sandbox API Client ID.', 'paypalplus-woocommerce' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'rest_secret_id_sandbox'        => [
				'title'       => __( 'Sandbox Secret ID', 'paypalplus-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Sandbox API Secret ID.', 'paypalplus-woocommerce' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'sandbox_experience_profile_id' => [
				'title'       => __( 'Sandbox Experience Profile ID', 'paypalplus-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'This value will be automatically generated and populated here when you save your settings.',
					'paypalplus-woocommerce'
				),
				'default'     => '',
				'class'       => 'credential_field readonly',
			],
			'rest_client_id'                => [
				'title'       => __( 'Live Client ID', 'paypalplus-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Live API Client ID.', 'paypalplus-woocommerce' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'rest_secret_id'                => [
				'title'       => __( 'Live Secret ID', 'paypalplus-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Live API Secret ID.', 'paypalplus-woocommerce' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'live_experience_profile_id'    => [
				'title'       => __( 'Experience Profile ID', 'paypalplus-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'This value will be automatically generated and populated here when you save your settings.',
					'paypalplus-woocommerce'
				),
				'default'     => '',
				'class'       => 'credential_field readonly',
			],
		];

		$settings += [
			'web_profile_section' => [
				'title' => __( 'Web Profile', 'paypalplus-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
			],
			'brand_name'          => [
				'title'       => __( 'Brand Name', 'paypalplus-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'This will be displayed as your brand / company name on the PayPal checkout pages.',
					'paypalplus-woocommerce'
				),
				'default'     => get_bloginfo( 'name' ),
			],
			'checkout_logo'       => [
				'title'       => __( 'PayPal Checkout Logo (190x60px)', 'paypalplus-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'Set the URL for a logo to be displayed on the PayPal checkout pages.',
					'paypalplus-woocommerce'
				),
				'default'     => '',
			],
		];

		// Settings.
		$settings += [
			'settings_section'              => [
				'title' => __( 'Settings', 'paypalplus-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
			],
			//'country'                       => [
			//	'title'       => __( 'PayPal Account Country', 'paypalplus-woocommerce' ),
			//	'type'        => 'select',
			//	'description' => __( 'Set this to the country your PayPal account is based in.', 'paypalplus-woocommerce' ),
			//	'default'     => 'DE',
			//	'options'     => [
			//		'BR' => 'Brazil',
			//		'MX' => 'Mexico',
			//		'DE' => 'Germany',
			//	],
			//],
			'invoice_prefix'                => [
				'title'       => __( 'Invoice Prefix', 'paypalplus-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.',
					'paypalplus-woocommerce'
				),
				'default'     => $this->get_default_invoice_prefix(),
				'desc_tip'    => true,
			],
			'cancel_url'                    => [
				'title'       => __( 'Cancel Page', 'paypalplus-woocommerce' ),
				'description' => __(
					'Sets the page users will be returned to if they click the Cancel link on the PayPal checkout pages.',
					'paypalplus-woocommerce'
				),
				'type'        => 'select',
				'options'     => $this->get_cancel_page_options(),
				'default'     => wc_get_page_id( 'checkout' ),
			],
			'legal_note'                    => [
				'title'       => __( 'Legal Note for PAY UPON INVOICE Payment', 'paypalplus-woocommerce' ),
				'type'        => 'textarea',
				'description' => __(
					'legal note that will be added to the thank you page and emails.',
					'paypalplus-woocommerce'
				),
				'default'     => __(
					'Dealer has ceeded the claim against you within the framework of an ongoing factoring contract to the PayPal (Europe) S.àr.l. et Cie, S.C.A.. Payments with a debt-free effect can only be paid to the PayPal (Europe) S.àr.l. et Cie, S.C.A.',
					'paypalplus-woocommerce'
				),
				'desc_tip'    => false,
			],
			'pay_upon_invoice_instructions' => [
				'title'       => __( 'Pay upon Invoice Instructions', 'paypalplus-woocommerce' ),
				'type'        => 'textarea',
				'description' => __(
					'Pay upon Invoice Instructions that will be added to the thank you page and emails.',
					'paypalplus-woocommerce'
				),
				'default'     => __(
					'Please transfer the complete amount to the bank account provided below.',
					'paypalplus-woocommerce'
				),
				'desc_tip'    => false,
			],
		];

		return $settings;
	}

	/**
	 * Retrieves all possible Cancel page URLs
	 *
	 * @return array
	 */
	protected function get_cancel_page_options() {

		$keys    = [
			'cart'     => __( 'Cart', 'paypalplus-woocommerce' ),
			'checkout' => __( 'Checkout', 'paypalplus-woocommerce' ),
			'account'  => __( 'Account', 'paypalplus-woocommerce' ),
			'shop'     => __( 'Shop', 'paypalplus-woocommerce' ),
		];
		$options = [];
		foreach ( $keys as $key => $title ) {
			$options[ $key ] = $title;
		}

		return $options;
	}

	/**
	 * Returns a generic invoice prefix based on the site title.
	 *
	 * @return string
	 */
	protected function get_default_invoice_prefix() {

		return 'WC-PPP-' . strtoupper( sanitize_title( get_bloginfo( 'name' ) ) ) . '-';
	}
}
