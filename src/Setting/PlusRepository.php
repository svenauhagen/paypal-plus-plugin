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
 * Class PlusRepository
 * @package WCPayPalPlus\Setting
 */
class PlusRepository implements Repository
{
    const OPTION_DISABLE_GATEWAY_OVERRIDE_NAME = 'disable_gateway_override';
    const OPTION_TEST_MODE_NAME = 'testmode';
    const OPTION_LEGAL_NOTE_NAME = 'legal_note';

    private $gateway;

    public function __construct(\WC_Payment_Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function isDefaultGatewayOverrideEnabled()
    {
        $option = $this->option(self::OPTION_DISABLE_GATEWAY_OVERRIDE_NAME, self::OPTION_OFF);

        return $option === self::OPTION_ON;
    }

    public function isSandboxed()
    {
        $option = $this->option(self::OPTION_TEST_MODE_NAME, self::OPTION_ON);

        return $option === self::OPTION_ON;
    }

    public function legalNotes()
    {
        return $this->option('legal_note', '');
    }

    private function option($key, $default)
    {
        assert(is_string($key));

        return $this->gateway->get_option($key, $default);
    }
}
