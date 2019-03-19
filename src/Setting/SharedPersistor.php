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

/**
 * Class SharedPersistor
 * @package WCPayPalPlus\Setting
 */
class SharedPersistor
{
    const OPTION_NAME = 'paypalplus_shared_options';

    /**
     * Update the Shared Settings
     *
     * @param array $settings
     */
    public function update(array $settings)
    {
        $settings = SharedSettingsFilter::intersect($settings);

        if (!$settings) {
            return;
        }

        update_option(self::OPTION_NAME, $settings);
    }

    /**
     * Delete all Options
     *
     * @return void
     */
    public function deleteAll()
    {
        delete_option(self::OPTION_NAME);
    }
}
