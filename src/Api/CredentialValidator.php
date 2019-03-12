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
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CredentialValidator constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
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
            _x('Credential are Valid', 'credential', ' woo-paypalplus'),
        ];
    }
}
