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

use WC_Order;

/**
 * Class PaymentValidator
 *
 * @package WCPayPalPlus\Ipn
 */
class PaymentValidator
{
    const TRANSACTION_TYPE_DATA_KEY = 'txn_type';
    const CURRENCY_DATA_KEY = 'mc_currency';
    const AMOUNT_DATA_KEY = 'mc_gross';

    const ACCEPTED_TRANSACTIONS_TYPES = [
        'cart',
        'instant',
        'express_checkout',
        'web_accept',
        'masspay',
        'send_money',
    ];

    /**
     * The last error that occurred during validation
     *
     * @var string
     */
    private $last_error;

    /**
     * WooCommerce Order object
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * @var Request
     */
    private $request;

    /**
     * PaymentValidator constructor.
     * @param Request $request
     * @param WC_Order $order
     */
    public function __construct(Request $request, WC_Order $order)
    {
        $this->request = $request;
        $this->order = $order;
    }

    /**
     * Runs all validation method
     *
     * @return bool
     */
    public function is_valid_payment()
    {
        $transactionType = $this->request->get(self::TRANSACTION_TYPE_DATA_KEY);
        $currency = $this->request->get(self::CURRENCY_DATA_KEY);
        $amount = $this->request->get(self::AMOUNT_DATA_KEY);

        return ($this->validate_transaction_type($transactionType)
            && $this->validate_currency($currency)
            && $this->validate_payment_amount($amount));
    }

    /**
     * Check for a valid transaction type.
     *
     * @param string $transaction_type The transaction type to test against.
     *
     * @return bool
     */
    private function validate_transaction_type($transaction_type)
    {
        if (!in_array(strtolower($transaction_type), self::ACCEPTED_TRANSACTIONS_TYPES, true)) {
            $this->last_error = sprintf(
                __(
                    'Validation error: Invalid transaction type "%s".',
                    'woo-paypalplus'
                ),
                $transaction_type
            );

            return false;
        }

        return true;
    }

    /**
     * Check currency from IPN matches the order.
     *
     * @param string $currency The currency to test against.
     *
     * @return bool
     */
    private function validate_currency($currency)
    {
        $wc_currency = $this->order->get_currency();
        if ($wc_currency !== $currency) {
            $this->last_error = sprintf(
                __(
                    'Validation error: PayPal currencies do not match (PayPal: %1$1s, WooCommerce: %2$2s).',
                    'woo-paypalplus'
                ),
                $currency,
                $wc_currency
            );

            return false;
        }

        return true;
    }

    /**
     * Check payment amount from IPN matches the order.
     *
     * @param int $amount The payment amount.
     *
     * @return bool
     */
    private function validate_payment_amount($amount)
    {
        $wc_total = number_format($this->order->get_total(), 2, '.', '');
        $pp_total = number_format($amount, 2, '.', '');
        if ($pp_total !== $wc_total) {
            $this->last_error = sprintf(
                __(
                    'Validation error: PayPal payment amounts do not match (gross %1$1s, should be %2$2s).',
                    'woo-paypalplus'
                ),
                $amount,
                $wc_total
            );

            return false;
        }

        return true;
    }

    /**
     * Checks if we're dealing with a valid refund request.
     *
     * @return bool
     */
    public function is_valid_refund()
    {
        $currency = $this->request->get(self::CURRENCY_DATA_KEY);

        $wc_total = number_format(
            $this->sanitize_string_amount($this->order->get_total()),
            2,
            '.',
            ''
        );
        $pp_total = number_format(
            $this->sanitize_string_amount($currency) * -1,
            2,
            '.',
            ''
        );

        return ($pp_total === $wc_total);
    }

    private function sanitize_string_amount($amt)
    {
        if (is_string($amt)) {
            $amt = str_replace(',', '.', $amt);
        }

        return $amt;
    }

    /**
     * Returns the last validation error
     *
     * @return string
     */
    public function get_last_error()
    {
        return $this->last_error;
    }
}
