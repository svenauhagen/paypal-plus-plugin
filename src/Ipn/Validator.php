<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Ipn;

/**
 * Class IPNValidator
 *
 * @package WCPayPalPlus\Ipn
 */
class Validator
{
    private $ipnData;

    public function __construct(Data $ipnData)
    {
        $this->ipnData = $ipnData;
    }

    /**
     * Validates an IPN Request
     *
     * @return bool
     */
    public function validate()
    {
        if (defined('PPP_DEBUG') and PPP_DEBUG) {
            return true;
        }
        $params = [
            'body' => ['cmd' => '_notify-validate'] + $this->data->get_all(),
            'timeout' => 60,
            'httpversion' => '1.1',
            'compress' => false,
            'decompress' => false,
            'user-agent' => $this->data->get_user_agent(),
        ];

        $response = wp_safe_remote_post($this->data->get_paypal_url(), $params);

        if ($response instanceof \WP_Error) {
            return false;
        }
        if (!isset($response['response']['code'])) {
            return false;
        }
        if ($response['response']['code'] >= 200 && $response['response']['code'] < 300
            && strstr($response['body'], 'VERIFIED')
        ) {
            return true;
        }

        return false;
    }
}
