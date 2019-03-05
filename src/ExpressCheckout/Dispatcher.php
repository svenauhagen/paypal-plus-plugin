<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\ExpressCheckout;

/**
 * Class Dispatcher
 * @package WCPayPalPlus\ExpressCheckout
 */
class Dispatcher
{
    const ACTION_DISPATCH_CONTEXT = 'woopaypalplus.express_checkout_request';

    /**
     * Dispatch a request by the given context
     *
     * @param $context
     * @return mixed
     */
    public function dispatch($context)
    {
        assert(is_string($context));

        return apply_filters(self::ACTION_DISPATCH_CONTEXT . "_{$context}", null);
    }
}
