<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 17:32
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
