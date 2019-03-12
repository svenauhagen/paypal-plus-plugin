<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Utils;

use WC_Logger_Interface as Logger;

/**
 * Class JsonParser
 * @package WCPayPalPlus
 */
class AjaxJsonRequest
{
    const DEFAULT_LOG_MESSAGE = 'Unknown error message.';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * AjaxJsonRequest constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Send a JSON response back to an Ajax request, indicating success.
     *
     * @param array $data
     * @param int $status
     */
    public function sendJsonSuccess(array $data, $status = null)
    {
        wp_send_json_success($data, $status);
    }

    /**
     * Send a JSON response back to an Ajax request, indicating failure.
     *
     * @param array $data
     * @param int $status
     */
    public function sendJsonError(array $data, $status = null)
    {
        $message = isset($data['exception']) ? $data['exception'] : '';
        $message or $message = isset($data['message']) ? $data['message'] : self::DEFAULT_LOG_MESSAGE;

        unset($data['exception']);

        $this->logger->error($message);

        wp_send_json_error($data, $status);
    }
}
