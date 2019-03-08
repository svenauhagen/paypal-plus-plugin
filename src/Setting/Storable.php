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
 * Interface Storable
 * @package WCPayPalPlus\Setting
 */
interface Storable
{
    const OPTION_ON = 'yes';
    const OPTION_OFF = 'no';

    const PAYPAL_SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    const PAYPAL_LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * @return bool
     */
    public function isSandboxed();

    /**
     * @return string
     */
    public function paypalUrl();

    /**
     * @return string
     */
    public function userAgent();
}
