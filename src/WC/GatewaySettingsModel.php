<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.11.16
 * Time: 15:34
 */

namespace PayPalPlusPlugin\WC;

/**
 * Class GatewaySettingsModel
 *
 * @package PayPalPlusPlugin\WC
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
				'title'   => __( 'Enable/Disable', 'woo-paypal-plus' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PayPal Plus', 'woo-paypal-plus' ),
				'default' => 'no',
			],
			'title'       => [
				'title'       => __( 'Title', 'woo-paypal-plus' ),
				'type'        => 'text',
				'description' => __(
					'This controls the name of the payment gateway the user sees during checkout.',
					'woo-paypal-plus'
				),
				'default'     => __( 'PayPal Plus', 'woo-paypal-plus' ),
			],
			'description' => [
				'title'       => __( 'Description', 'woo-paypal-plus' ),
				'type'        => 'text',
				'description' => __(
					'This controls the payment gateway description the user sees during checkout.',
					'woo-paypal-plus'
				),
				'default'     => __( 'PayPal Plus', 'woo-paypal-plus' ),
			],
		];

		// Credentials.
		$settings += [
			'credentials_section'           => [
				'title' => __( 'Credentials', 'woo-paypal-plus' ),
				'type'  => 'title',
				'desc'  => '',
			],
			'testmode'                      => [
				'title'       => __( 'PayPal Sandbox', 'woo-paypal-plus' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable PayPal Sandbox', 'woo-paypal-plus' ),
				'default'     => 'yes',
				'description' => __(
					'The PayPal sandbox can be used to test payments. You will need to create a sandbox account to use as a seller in order to test this way.',
					'woo-paypal-plus'
				),
			],
			'rest_client_id_sandbox'        => [
				'title'       => __( 'Sandbox Client ID', 'woo-paypal-plus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Sandbox API Client ID.', 'woo-paypal-plus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'rest_secret_id_sandbox'        => [
				'title'       => __( 'Sandbox Secret ID', 'woo-paypal-plus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Sandbox API Secret ID.', 'woo-paypal-plus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'sandbox_experience_profile_id' => [
				'title'       => __( 'Sandbox Experience Profile ID', 'woo-paypal-plus' ),
				'type'        => 'text',
				'description' => __(
					'This value will be automatically generated and populated here when you save your settings.',
					'woo-paypal-plus'
				),
				'default'     => '',
				'class'       => 'credential_field readonly',
			],
			'rest_client_id'                => [
				'title'       => __( 'Live Client ID', 'woo-paypal-plus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Live API Client ID.', 'woo-paypal-plus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'rest_secret_id'                => [
				'title'       => __( 'Live Secret ID', 'woo-paypal-plus' ),
				'type'        => 'password',
				'description' => __( 'Enter your PayPal REST Live API Secret ID.', 'woo-paypal-plus' ),
				'default'     => '',
				'class'       => 'credential_field',
			],
			'live_experience_profile_id'    => [
				'title'       => __( 'Experience Profile ID', 'woo-paypal-plus' ),
				'type'        => 'text',
				'description' => __(
					'This value will be automatically generated and populated here when you save your settings.',
					'woo-paypal-plus'
				),
				'default'     => '',
				'class'       => 'credential_field readonly',
			],
		];

		$settings += [
			'web_profile_section' => [
				'title' => __( 'Web Profile', 'woo-paypal-plus' ),
				'type'  => 'title',
				'desc'  => '',
			],
			'brand_name'          => [
				'title'       => __( 'Brand Name', 'woo-paypal-plus' ),
				'type'        => 'text',
				'description' => __(
					'This will be displayed as your brand / company name on the PayPal checkout pages.',
					'woo-paypal-plus'
				),
				'default'     => get_bloginfo( 'name' ),
			],
			'checkout_logo'       => [
				'title'       => __( 'PayPal Checkout Logo (190x60px)', 'woo-paypal-plus' ),
				'type'        => 'text',
				'description' => __(
					'Set the URL for a logo to be displayed on the PayPal checkout pages.',
					'woo-paypal-plus'
				),
				'default'     => '',
			],
		];

		// Settings.
		$settings += [
			'settings_section'              => [
				'title' => __( 'Settings', 'woo-paypal-plus' ),
				'type'  => 'title',
				'desc'  => '',
			],
			'country'                       => [
				'title'       => __( 'PayPal Account Country', 'woo-paypal-plus' ),
				'type'        => 'select',
				'description' => __( 'Set this to the country your PayPal account is based in.', 'woo-paypal-plus' ),
				'default'     => 'DE',
				'options'     => [
					'BR' => 'Brazil',
					'MX' => 'Mexico',
					'DE' => 'Germany',
				],
			],
			'invoice_prefix'                => [
				'title'       => __( 'Invoice Prefix', 'woo-paypal-plus' ),
				'type'        => 'text',
				'description' => __(
					'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.',
					'woo-paypal-plus'
				),
				'default'     => 'WC-PP-PLUS-',
				'desc_tip'    => true,
			],
			'cancel_url'                    => [
				'title'       => __( 'Cancel Page', 'woo-paypal-plus' ),
				'description' => __(
					'Sets the page users will be returned to if they click the Cancel link on the PayPal checkout pages.',
					'woo-paypal-plus'
				),
				'type'        => 'select',
				'options'     => $this->get_cancel_page_urls(),
				'default'     => wc_get_page_id( 'checkout' ),
			],
			'legal_note'                    => [
				'title'       => __( 'Legal Note for PAY UPON INVOICE Payment', 'woo-paypal-plus' ),
				'type'        => 'textarea',
				'description' => __(
					'legal note that will be added to the thank you page and emails.',
					'woo-paypal-plus'
				),
				'default'     => __(
					'Händler hat die Forderung gegen Sie im Rahmen eines laufenden Factoringvertrages an die PayPal (Europe) S.àr.l. et Cie, S.C.A. abgetreten. Zahlungen mit schuldbefreiender Wirkung können nur an die PayPal (Europe) S.àr.l. et Cie, S.C.A. geleistet werden.',
					'woo-paypal-plus'
				),
				'desc_tip'    => false,
			],
			'pay_upon_invoice_instructions' => [
				'title'       => __( 'Pay upon Invoice Instructions', 'woo-paypal-plus' ),
				'type'        => 'textarea',
				'description' => __(
					'Pay upon Invoice Instructions that will be added to the thank you page and emails.',
					'woo-paypal-plus'
				),
				'default'     => __(
					'Please transfer the complete amount to the bank account provided below.',
					'woo-paypal-plus'
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
	public function get_cancel_page_urls() {

		$args        = [];
		$pages       = get_pages( $args );
		$cancel_page = [];
		foreach ( $pages as $p ) {
			$cancel_page[ $p->ID ] = $p->post_title;
		}

		return $cancel_page;
	}
}
