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
 * Class SharedRepository
 * @package WCPayPalPlus\Setting
 */
class SharedRepository implements Storable
{
    use SharedRepositoryTrait;

    /**
     * Retrieve a Shared Option by the Given Name
     *
     * @param $name
     * @param null $default
     * @return mixed
     */
    private function get_option($name, $default = null)
    {
        assert(is_string($name));

        $option = get_option(SharedPersistor::OPTION_NAME, $default) ?: $default;

        return isset($option[$name]) ? $option[$name] : $default;
    }
}
