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
class PlusRepository implements PlusStorable
{
    use PlusRepositoryHelper;

    private $gateway;

    public function __construct(\WC_Payment_Gateway $gateway)
    {
        $this->gateway = $gateway;
    }
}
