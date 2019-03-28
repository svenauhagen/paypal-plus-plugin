<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Api\ErrorData;

/**
 * Interface ApiErrorData
 * @package WCPayPalPlus\Api
 */
interface ErrorData
{
    const INSTRUMENT_DECLINED = 'INSTRUMENT_DECLINED';
    const INSUFFICIENT_FUNDS = 'INSUFFICIENT_FUNDS';

    const REDIRECTABLE_ERROR_CODES = [
        self::INSTRUMENT_DECLINED,
        self::INSUFFICIENT_FUNDS,
    ];

    /**
     * Retrieve the Error Code
     *
     * @return string
     */
    public function code();

    /**
     * @return Detail[]
     */
    public function details();

    /**
     * Retrieve the Error Message
     *
     * @return string
     */
    public function message();

    /**
     * Retrieve the Debug ID for support
     *
     * @return mixed
     */
    public function debugId();
}
