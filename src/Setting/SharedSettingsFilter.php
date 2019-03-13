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
 * Class SharedSettingsFilter
 * @package WCPayPalPlus\Setting
 */
class SharedSettingsFilter
{
    /**
     * Filter the Gateway Settings by Remove the Shared Ones
     *
     * @param array $settings
     * @return array
     */
    public static function diff(array $settings)
    {
        $settings = array_diff_key($settings, SharedSettingsModel::SHARED_OPTIONS);

        return $settings;
    }

    /**
     * Filter the Gateway Settings to Retrieve Only the Shared Ones
     *
     * @param array $settings
     * @return array
     */
    public static function intersect(array $settings)
    {
        $settings = array_intersect_key($settings, SharedSettingsModel::SHARED_OPTIONS);

        return $settings;
    }
}
