<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Install;

use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WCPayPalPlus\Setting\SharedPersistor;

/**
 * Class Installer
 * @package WCPayPalPlus\Installation
 */
class Installer
{
    const ORIGINAL_OPTIONS = 'woocommerce_paypal_plus_settings';

    /**
     * @var SharedPersistor
     */
    private $sharedPersistor;

    /**
     * @var ExpressCheckoutGateway
     */
    private $expressCheckoutGateway;

    /**
     * Installer constructor.
     * @param SharedPersistor $sharedPersistor
     * @param ExpressCheckoutGateway $expressCheckoutGateway
     */
    public function __construct(
        SharedPersistor $sharedPersistor,
        ExpressCheckoutGateway $expressCheckoutGateway
    ) {

        $this->sharedPersistor = $sharedPersistor;
        $this->expressCheckoutGateway = $expressCheckoutGateway;
    }

    /**
     * Perform Tasks After Plugin is Installed or Upgraded
     */
    public function afterInstall()
    {
        $this->migrateSharedOptions();
        $this->activateExpressCheckout();
    }

    /**
     * Migrate Shared options
     *
     * @return void
     */
    private function migrateSharedOptions()
    {
        $options = get_option(self::ORIGINAL_OPTIONS, []);

        if (!$options) {
            return;
        }

        $this->sharedPersistor->update($options);
    }

    /**
     * Activate Express Checkout Gateway When Plugin get Installed
     */
    private function activateExpressCheckout()
    {
        $enabled = $this->expressCheckoutGateway->get_option('enabled', 'no');
        $enabled = wc_string_to_bool($enabled);

        if ($enabled) {
            return;
        }

        if (!method_exists($this->expressCheckoutGateway, 'update_option')) {
            $this->gatewayUpdateOption('enabled', 'yes');
            return;
        }

        $this->expressCheckoutGateway->update_option('enabled', 'yes');
    }

    /**
     * Backward Compatibility Method Because `WC_Settings_API::update_option` does not Exists
     * Prior to WooCommerce 3.4.x
     *
     * @param $key
     * @param null $empty_value
     * @return mixed
     */
    private function gatewayUpdateOption($key, $empty_value = null)
    {
        if (empty($this->expressCheckoutGateway->settings)) {
            $this->expressCheckoutGateway->init_settings();
        }

        // Get option default if unset.
        if (!isset($this->expressCheckoutGateway->settings[$key])) {
            $form_fields = $this->expressCheckoutGateway->get_form_fields();
            $this->expressCheckoutGateway->settings[$key] = isset($form_fields[$key])
                ? $this->expressCheckoutGateway->get_field_default($form_fields[$key])
                : '';
        }

        if (!is_null($empty_value) && $this->expressCheckoutGateway->settings[$key] === '') {
            $this->expressCheckoutGateway->settings[$key] = $empty_value;
        }

        return $this->expressCheckoutGateway->settings[$key];
    }
}
