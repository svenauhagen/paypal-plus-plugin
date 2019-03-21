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

use WCPayPalPlus\Setting\SharedPersistor;

/**
 * Class OptionsMigrator
 * @package WCPayPalPlus\Installation
 */
class OptionsMigrator
{
    const ORIGINAL_OPTIONS = 'woocommerce_paypal_plus_settings';

    /**
     * @var SharedPersistor
     */
    private $sharedPersistor;

    /**
     * OptionsMigrator constructor.
     * @param SharedPersistor $sharedPersistor
     */
    public function __construct(SharedPersistor $sharedPersistor)
    {
        $this->sharedPersistor = $sharedPersistor;
    }

    /**
     * Migrate Shared options
     *
     * @return void
     */
    public function migrateSharedOptions()
    {
        $options = get_option(self::ORIGINAL_OPTIONS, true);

        if (!$options) {
            return;
        }

        $this->sharedPersistor->update($options);
    }
}
