<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC;

/**
 * Interface RequestSuccessHandler
 * @package WCPayPalPlus\WC
 */
interface RequestSuccessHandler
{
    /**
     * Handles a successful REST call
     *
     * @return void
     */
    public function execute();
}
