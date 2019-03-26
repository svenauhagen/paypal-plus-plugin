<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Exception;

/**
 * Class PaymentProcessException
 * @package WCPayPalPlus\Payment
 */
class PaymentProcessException extends Exception
{
    /**
     * @return PaymentProcessException
     */
    public static function forInsufficientData()
    {
        return new self(
            esc_html__(
                'Payment Execution: Insufficient data to make payment.',
                'woo-paypalplus'
            )
        );
    }

    /**
     * @param $orderId
     * @return PaymentProcessException
     */
    public static function becauseInvalidOrderId($orderId)
    {
        assert(is_int($orderId));

        return new self(
            sprintf(
                esc_html__('Invalid Order ID %s', 'woo-paypalplus'),
                $orderId
            )
        );
    }

    /**
     * @param PayPalConnectionException $exc
     * @return PaymentProcessException
     */
    public static function becausePayPalConnection(PayPalConnectionException $exc)
    {
        return new self(
            sprintf(
                esc_html__(
                    'Cannot process the payment because of connection returned invalid response: %s',
                    'woo-paypalplus'
                ),
                $exc->getMessage()
            )
        );
    }

    /**
     * @param $message
     * @return PaymentProcessException
     */
    public static function becauseInvalidPaymentState($message)
    {
        assert(is_string($message));

        return new self($message);
    }
}
