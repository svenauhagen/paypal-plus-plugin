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

use const WCPayPalPlus\ACTION_LOG;
use WCPayPalPlus\Setting\Storable;
use WP_Error;
use RuntimeException;
use Exception;
use WC_Log_Levels;

/**
 * Class IPNValidator
 *
 * @package WCPayPalPlus\Ipn
 */
class IpnVerifier
{
    const STATUS_VERIFIED = 'VERIFIED';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Storable
     */
    private $settingRepository;

    /**
     * Validator constructor.
     * @param Request $request
     * @param Storable $settingRepository
     */
    public function __construct(Request $request, Storable $settingRepository)
    {
        $this->request = $request;
        $this->settingRepository = $settingRepository;
    }

    /**
     * Validates an IPN Request
     *
     * @return bool
     */
    public function isVerified()
    {
        $params = [
            'body' => ['cmd' => '_notify-validate'] + $this->request->all(),
            'timeout' => 60,
            'httpversion' => '1.1',
            'compress' => false,
            'decompress' => false,
            'user-agent' => $this->settingRepository->userAgent(),
        ];

        try {
            list($code, $body) = $this->remotePost($this->settingRepository->paypalUrl(), $params);
        } catch (Exception $exc) {
            do_action(ACTION_LOG, WC_Log_Levels::ERROR, $exc->getMessage(), compact($exc));

            $code = 0;
            $body = '';
        }

        if ($code >= 200 && $code < 300 && stripos($body, self::STATUS_VERIFIED) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param $url
     * @param $params
     * @return array
     * @throws RuntimeException
     */
    private function remotePost($url, $params)
    {
        assert(is_string($url));
        assert(is_array($params));

        $response = wp_safe_remote_post($url, $params);

        if ($response instanceof WP_Error) {
            throw new RuntimeException($response->get_error_message());
        }

        if (!isset($response['body'], $response['response']['code'])) {
            throw new RuntimeException('No valid response was provided trying to contact paypal for an IPN validation.');
        }

        return [
            $response['response']['code'],
            $response['body'],
        ];
    }
}
