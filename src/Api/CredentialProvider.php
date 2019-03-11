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

/**
 * Class CredentialProvider
 * @package WCPayPalPlus\Api
 */
class CredentialProvider
{
    const CLIENT_ID_KEY = 'woocommerce_paypal_plus_rest_client_id';
    const CLIENT_SECRET_ID_KEY = 'woocommerce_paypal_plus_rest_secret_id';
    const CLIENT_ID_KEY_SANDBOX = self::CLIENT_ID_KEY . '_sandbox';
    const CLIENT_SECRET_ID_KEY_SANDBOX = self::CLIENT_SECRET_ID_KEY . '_sandbox';

    /**
     * @param $isSandboxed
     * @return Credential
     */
    public function byRequest($isSandboxed)
    {
        assert(is_bool($isSandboxed));

        $clientIdKey = $isSandboxed ? self::CLIENT_ID_KEY_SANDBOX : self::CLIENT_ID_KEY;
        $clientSecret = $isSandboxed ? self::CLIENT_SECRET_ID_KEY_SANDBOX : self::CLIENT_SECRET_ID_KEY;

        $clientId = (string)filter_input(INPUT_POST, $clientIdKey, FILTER_SANITIZE_STRING);
        $clientSecret = (string)filter_input(INPUT_POST, $clientSecret, FILTER_SANITIZE_STRING);

        return new Credential($clientId, $clientSecret);
    }
}
