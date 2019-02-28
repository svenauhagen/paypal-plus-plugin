<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Api;

use Inpsyde\Lib\PayPal\Auth\OAuthTokenCredential;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Api
 */
class ApiContextFactory
{
    /**
     * @param OAuthTokenCredential|null $credentials
     *
     * @return ApiContext
     */
    public static function get($credentials = null)
    {
        assert(null === $credentials || $credentials instanceof OAuthTokenCredential);

        return new ApiContext(
            $credentials,
            uniqid(home_url(), false)
        );
    }
}
