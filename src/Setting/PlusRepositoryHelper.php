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
 * Class PlusRepositoryHelper
 * @package WCPayPalPlus\Setting
 */
trait PlusRepositoryHelper
{
    /**
     * @return bool
     */
    public function isDefaultGatewayOverrideEnabled()
    {
        $option = $this->option(self::OPTION_DISABLE_GATEWAY_OVERRIDE_NAME, self::OPTION_OFF);

        return $option === self::OPTION_ON;
    }

    /**
     * @return bool
     */
    public function isSandboxed()
    {
        $option = $this->option(self::OPTION_TEST_MODE_NAME, self::OPTION_ON);

        return $option === self::OPTION_ON;
    }

    /**
     * @return mixed
     */
    public function legalNotes()
    {
        return $this->option('legal_note', '');
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    private function option($key, $default)
    {
        assert(is_string($key));
        assert($this->gateway instanceof \WC_Payment_Gateway);

        return $this->gateway->get_option($key, $default);
    }
}
