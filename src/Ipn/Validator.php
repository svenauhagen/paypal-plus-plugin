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
    /**
     * @var Request
     */
    private $ipnRequest;

    /**
     * @var Data
     */
    private $ipnData;

    /**
     * Validator constructor.
     * @param Data $ipnData
     * @param Request $ipnRequest
     */
    public function __construct(Data $ipnData, Request $ipnRequest)
    {
        $this->ipnData = $ipnData;
        $this->ipnRequest = $ipnRequest;
    }

    /**
     * Validates an IPN Request
     *
     * @return bool
     */
    public function validate()
    {
        $params = [
            'body' => ['cmd' => '_notify-validate'] + $this->ipnRequest->all(),
            'timeout' => 60,
            'httpversion' => '1.1',
            'compress' => false,
            'decompress' => false,
            'user-agent' => $this->ipnData->userAgent(),
        ];

        $response = wp_safe_remote_post($this->ipnData->paypalUrl(), $params);

        if ($response instanceof \WP_Error) {
            return false;
        }
        if (!isset($response['response']['code'])) {
            return false;
        }
        if ($response['response']['code'] >= 200
            && $response['response']['code'] < 300
            && strstr($response['body'], 'VERIFIED')
        ) {
            return true;
        }

        return false;
    }
}
