<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 14:09
 */

namespace PayPalPlusPlugin\WC;

use PayPal\Api\InputFields;
use PayPal\Api\Presentation;
use PayPal\Api\WebProfile;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class WCWebExperienceProfile {

	/**
	 * @var ApiContext
	 */
	private $api_context;
	/**
	 * @var array
	 */
	private $config;

	/**
	 * WCWebExperienceProfile constructor.
	 *
	 * @param array      $config
	 * @param ApiContext $api_context
	 */
	public function __construct( array $config, ApiContext $api_context ) {

		$this->api_context = $api_context;
		$this->config      = $config;
	}

	/**
	 * Save profile data
	 *
	 * @return string
	 */
	public function save_profile() {

		return $this->update_profile( $this->get_local_id() );

	}

	/**
	 * @param $profile_id
	 *
	 * @return null|WebProfile
	 */
	public function get_existing_profile( $profile_id ) {

		$webProfile = NULL;
		try {
			$webProfile = WebProfile::get( $profile_id, $this->api_context );
		} catch ( PayPalConnectionException $ex ) {
			error_log( $ex->getMessage() );
			error_log( $ex->getData() );
		}

		return $webProfile;

	}

	/**
	 * @param bool $local_id
	 *
	 * @return string
	 */
	private function update_profile( $local_id = FALSE ) {

		if ( $local_id ) {
			$webProfile = $this->get_existing_profile( $local_id );
		} else {
			$webProfile = new WebProfile();

		}
		$brand_name = '';
		if ( ! empty( $this->config['brand_name'] ) ) {
			$brand_name = $this->config['brand_name'];
		}

		$webProfile->setName( substr( $brand_name . uniqid(), 0, 50 ) )
		           ->setInputFields( $this->get_input_fields() )
		           ->setPresentation( $this->get_presentation() );

		$new_id = NULL;
		try {
			if ( $local_id ) {

				if ( $webProfile->update( $this->api_context ) ) {
					$new_id = $local_id;
				}
			} else {
				$response = $webProfile->create( $this->api_context );
				$new_id   = $response->getId();
			}

		} catch ( PayPalConnectionException $ex ) {
			error_log( $ex->getMessage() );
			error_log( $ex->getData() );
		}

		return $new_id;
	}

	private function get_input_fields() {

		$inputFields = new InputFields();

		$no_shipping = ( isset( $this->config['no_shipping'] ) ) ? intval( $this->config['no_shipping'] ) : 1;

		$inputFields->setNoShipping( $no_shipping )
		            ->setAddressOverride( 1 );

		return $inputFields;
	}

	/**
	 * Creates and returns a Presentation object
	 *
	 * @return Presentation
	 */
	private function get_presentation() {

		$presentation = new Presentation();

		if ( ! empty( $this->config['checkout_logo'] ) ) {
			$presentation->setLogoImage( $this->config['checkout_logo'] );
		}
		if ( ! empty( $this->config['brand_name'] ) ) {
			$presentation->setBrandName( $this->config['brand_name'] );
		}
		if ( ! empty( $this->config['country'] ) ) {
			$presentation->setLocaleCode( $this->config['country'] );
		}

		return $presentation;
	}

	/**
	 * Checks if a local profile ID was previously saved
	 *
	 * @return bool
	 */
	private function has_local_id() {

		return is_string( $this->get_local_id() );

	}

	/**
	 * Returns the local profile ID
	 *
	 * @return string
	 */
	private function get_local_id() {

		return isset( $this->config['local_id'] ) ? $this->config['local_id'] : NULL;
	}
}