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

use Exception;
use WC_Logger_Interface as Logger;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class CredentialVerification
 *
 * @package WCPayPalPlus\WC
 */
class CredentialValidator
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * CredentialValidator constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Verify the API Credentials by making a dummy API call with them.
     *
     * @param ApiContext $context
     * @return array
     */
    public function ensureCredential(ApiContext $context)
    {
        $credential = $context->getCredential();
        if (!$credential->getClientId() || !$credential->getClientSecret()) {
            return [
                false,
                esc_html_x('Credential are Empty', 'credential', 'woo-paypalplus'),
            ];
        }

        try {
            $params = ['count' => 1];
            Payment::all($params, $context);
        } catch (Exception $exc) {
            $this->logger->error($exc);

            return [
                false,
                $exc->getMessage(),
            ];
        }

        return [
            true,
            esc_html_x('Credential are Valid', 'credential', 'woo-paypalplus'),
        ];
    }
}
