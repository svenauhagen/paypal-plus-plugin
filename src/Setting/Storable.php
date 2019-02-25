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

interface Storable
{
    const OPTION_ON = 'yes';
    const OPTION_OFF = 'no';

    /**
     * @return bool
     */
    public function isSandboxed();
}