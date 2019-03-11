<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus;

use Exception;
use WC_Log_Levels as LogLevels;

/**
 * TODO Reuse the trait where needed.
 *
 * Class LogTrait
 * @package WCPayPalPlus
 */
trait LoggerUnawareTrait
{
    /**
     * Log Exceptions and re-throw them if `WP_DEBUG` is set to true
     *
     * @param Exception $exception
     * @throws Exception
     */
    private function logException(Exception $exception)
    {
        $this->log(LogLevels::ERROR, $exception->getMessage(), compact($exception));

        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw $exception;
        }
    }

    /**
     * Log Action
     *
     * @param string $level
     * @param string $message
     * @param array $data
     * @return void
     */
    private function log($level, $message, array $data)
    {
        do_action(ACTION_LOG, $level, $message, $data);
    }
}
