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
				'title'   => __( 'Enable/Disable', 'woo-paypalplus' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PayPal Plus', 'woo-paypalplus' ),
				'default' => 'no',
			],
			'title'       => [
				'title'       => __( 'Title', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => __(
					'This controls the name of the payment gateway the user sees during checkout.',
					'woo-paypalplus'
				),
				'default'     => __( 'PayPal Plus', 'woo-paypalplus' ),
			],
			'description' => [
				'title'       => __( 'Description', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => __(
					'This controls the payment gateway description the user sees during checkout.',
					'woo-paypalplus'
				),
				'default'     => __( 'PayPal Plus', 'woo-paypalplus' ),
			],
		];

		// Credentials.
		$settings += [
			'credentials_section'           => [
				'title' => __( 'Credentials', 'woo-paypalplus' ),
				'type'  => 'title',
				'desc'  => '',
			],
			'testmode'                      => [
				'title'       => __( 'PayPal Sandbox', 'woo-paypalplus' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable PayPal Sandbox', 'woo-paypalplus' ),
				'default'     => 'yes',
				'description' => sprintf(
					__( 'PayPal sandbox can be used to test payments. Sign up for a <a href="%s">developer account</a>.',
						'woo-paypalplus'
					),
					'https://developer.paypal.com/'
				),
			],
			'rest_client_id_sandbox'        => [
				'title'       => __( 'Sandbox Client ID', 'woo-paypalplus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Sandbox API Client ID.', 'woo-paypalplus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'rest_secret_id_sandbox'        => [
				'title'       => __( 'Sandbox Secret ID', 'woo-paypalplus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Sandbox API Secret ID.', 'woo-paypalplus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'sandbox_experience_profile_id' => [
				'title'       => __( 'Sandbox Experience Profile ID', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => __(
					'This value will be automatically generated and populated here when you save your settings.',
					'woo-paypalplus'
				),
				'default'     => '',
				'class'       => 'credential_field readonly',
			],
			'rest_client_id'                => [
				'title'       => __( 'Live Client ID', 'woo-paypalplus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Live API Client ID.', 'woo-paypalplus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'rest_secret_id'                => [
				'title'       => __( 'Live Secret ID', 'woo-paypalplus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Live API Secret ID.', 'woo-paypalplus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'live_experience_profile_id'    => [
				'title'       => __( 'Experience Profile ID', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => __(
					'This value will be automatically generated and populated here when you save your settings.',
					'woo-paypalplus'
				),
				'default'     => '',
				'class'       => 'credential_field readonly',
			],
		];

		$settings += [
			'web_profile_section' => [
				'title' => __( 'Web Profile', 'woo-paypalplus' ),
				'type'  => 'title',
				'desc'  => '',
			],
			'brand_name'          => [
				'title'       => __( 'Brand Name', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => __(
					'This will be displayed as your brand / company name on the PayPal checkout pages.',
					'woo-paypalplus'
				),
				'default'     => get_bloginfo( 'name' ),
			],
			'checkout_logo'       => [
				'title'       => __( 'PayPal Checkout Logo (190x60px)', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => sprintf(
					__(
						'Set the absolute URL for a logo to be displayed on the PayPal checkout pages. (E.G., %s)',
						'woo-paypalplus'
					),
					get_site_url() . '/path/to/logo.jpg'
				),
				'default'     => '',
			],
		];

		// Settings.
		$upload_dir = wp_upload_dir();
		$logfile    = wc_get_log_file_path( 'paypal_plus' );
		$log_url    = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $logfile );
		$settings   += [
			'settings_section'              => [
				'title' => __( 'Settings', 'woo-paypalplus' ),
				'type'  => 'title',
				'desc'  => '',
			],
			//'country'                       => [
			//	'title'       => __( 'PayPal Account Country', 'woo-paypalplus' ),
			//	'type'        => 'select',
			//	'description' => __( 'Set this to the country your PayPal account is based in.', 'woo-paypalplus' ),
			//	'default'     => 'DE',
			//	'options'     => [
			//		'BR' => 'Brazil',
			//		'MX' => 'Mexico',
			//		'DE' => 'Germany',
			//	],
			//],
			'invoice_prefix'                => [
				'title'       => __( 'Invoice Prefix', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => __(
					'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.',
					'woo-paypalplus'
				),
				'default'     => $this->get_default_invoice_prefix(),
				'desc_tip'    => true,
			],
			'cancel_url'                    => [
				'title'       => __( 'Cancel Page', 'woo-paypalplus' ),
				'description' => __(
					'Sets the page users will be returned to if they click the Cancel link on the PayPal checkout pages.',
					'woo-paypalplus'
				),
				'type'        => 'select',
				'options'     => $this->get_cancel_page_options(),
				'default'     => wc_get_page_id( 'checkout' ),
			],
			'cancel_custom_url'                    => [
				'title'       => __( 'Custom Cancelation URL', 'woo-paypalplus' ),
				'type'        => 'text',
				'description' => __(
					'URL to a custom page to be used for cancelation. Please select "custom" above first.',
					'woo-paypalplus'
				),
			],
			'legal_note'                    => [
				'title'       => __( 'Legal Note for PAY UPON INVOICE Payment', 'woo-paypalplus' ),
				'type'        => 'textarea',
				'description' => __(
					'legal note that will be added to the thank you page and emails.',
					'woo-paypalplus'
				),
				'default'     => __(
					'Dealer has ceeded the claim against you within the framework of an ongoing factoring contract to the PayPal (Europe) S.àr.l. et Cie, S.C.A.. Payments with a debt-free effect can only be paid to the PayPal (Europe) S.àr.l. et Cie, S.C.A.',
					'woo-paypalplus'
				),
				'desc_tip'    => false,
			],
			'pay_upon_invoice_instructions' => [
				'title'       => __( 'Pay upon Invoice Instructions', 'woo-paypalplus' ),
				'type'        => 'textarea',
				'description' => __(
					'Pay upon Invoice Instructions that will be added to the thank you page and emails.',
					'woo-paypalplus'
				),
				'default'     => __(
					'Please transfer the complete amount to the bank account provided below.',
					'woo-paypalplus'
				),
				'desc_tip'    => false,
			],
			'download_log'                  => [
				'title' => __( 'Download Log File', 'woo-paypalplus' ),
				'type'  => 'html',
				'html'  => '<p>' . sprintf( __( 'Please go to <a href="%s">WooCommerce => System Status => Logs</a>, select the file <em>paypal_plus-....log</em>, copy the content and attach it to your ticket when contacting support.',
						'woo-paypalplus' ), get_admin_url(null,'admin.php') . '?page=wc-status&tab=logs') .'</p>',
			],
			'disable_gateway_override'      => [
				'title'       => __( 'Disable default gateway override', 'woo-paypalplus' ),
				'type'        => 'checkbox',
				'label'       => __( 'Disable', 'woo-paypalplus' ),
				'default'     => 'no',
				'description' =>
					__( 'PayPal Plus will be selected as default payment gateway regardless of its position in the list of enabled gateways. You can turn off this behaviour here',
						'woo-paypalplus'
					),
			],
		];

		return $settings;
	}

	/**
	 * Returns a generic invoice prefix based on the site title.
	 *
	 * @return string
	 */
	protected function get_default_invoice_prefix() {

		return 'WC-PPP-' . strtoupper( sanitize_title( get_bloginfo( 'name' ) ) ) . '-';
	}

	/**
	 * Retrieves all possible Cancel page URLs
	 *
	 * @return array
	 */
	protected function get_cancel_page_options() {

		$keys    = [
			'cart'     => __( 'Cart', 'woo-paypalplus' ),
			'checkout' => __( 'Checkout', 'woo-paypalplus' ),
			'account'  => __( 'Account', 'woo-paypalplus' ),
			'shop'     => __( 'Shop', 'woo-paypalplus' ),
			'custom'   => __( 'Custom', 'woo-paypalplus' ),
		];
		$options = [];
		foreach ( $keys as $key => $title ) {
			$options[ $key ] = $title;
		}

		return $options;
	}
}
