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
     * Migrate Shared options
     *
     * @return void
     */
    public function migrateSharedOptions()
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
    public function activateExpressCheckout()
    {
        $enabled = $this->expressCheckoutGateway->get_option('enabled', 'no');
        $enabled = wc_string_to_bool($enabled);

        if ($enabled) {
            return;
        }

        $this->expressCheckoutGateway->update_option('enabled', 'yes');
    }
}
