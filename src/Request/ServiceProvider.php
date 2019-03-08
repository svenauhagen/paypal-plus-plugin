<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Request;

use WCPayPalPlus\Service;
use WCPayPalPlus\Service\Container;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Request
 */
class ServiceProvider implements Service\ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Request::class] = function () {
            return new Request(filter_input_array(INPUT_POST) ?: []);
        };
    }
}
