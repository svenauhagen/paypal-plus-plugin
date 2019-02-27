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
 * Class OrderUpdater
 *
 * @package WCPayPalPlus\Ipn
 */
class OrderUpdater
{
    const ORDER_STATUS_COMPLETED = 'completed';
    const ORDER_STATUS_ON_HOLD = 'on-hold';
    const ORDER_STATUS_REFUNDED = 'refunded';

    /**
     * WooCommerce Order object
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * Request Data
     *
     * @var Data
     */
    private $ipnData;

    /**
     * Payment Validation handler
     *
     * @var PaymentValidator
     */
    private $validator;

    /**
     * @var Request
     */
    private $ipnRequest;

    /**
     * OrderUpdater constructor.
     * @param \WC_Order $order
     * @param Data $ipnData
     * @param Request $ipnRequest
     * @param PaymentValidator $validator
     */
    public function __construct(
        \WC_Order $order,
        Data $ipnData,
        Request $ipnRequest,
        PaymentValidator $validator
    ) {

        $this->order = $order;
        $this->ipnData = $ipnData;
        $this->ipnRequest = $ipnRequest;
        $this->validator = $validator;
    }

    /**
     * Handle a pending payment.
     *
     * @return bool
     */
    public function payment_status_pending()
    {
        return $this->payment_status_completed();
    }

    /**
     * Handle a completed payment.
     *
     * @return bool
     */
    public function payment_status_completed()
    {
        if ($this->order->has_status(self::ORDER_STATUS_COMPLETED)) {
            do_action(
                'wc_paypal_plus_log_error',
                'IPN Error. Payment already completed: ',
                []
            );

            return true;
        }

        if (!$this->validator->is_valid_payment()) {
            $last_error = $this->validator->get_last_error();
            $this->order->update_status(self::ORDER_STATUS_ON_HOLD, $last_error);
            do_action(
                'wc_paypal_plus_log_error',
                'IPN Error. Payment validation failed: ' . $last_error,
                []
            );

            return false;
        }

        $this->save_paypal_meta_data();

        $paymentStatus = $this->ipnRequest->get(Request::KEY_PAYMENT_STATUS);
        if ($paymentStatus === self::ORDER_STATUS_COMPLETED) {
            $transaction_id = wc_clean($this->ipnRequest->get(Request::KEY_TXN_ID));
            $note = __('IPN payment completed', 'woo-paypalplus');
            $fee = $this->ipnRequest->get(Request::KEY_MC_FEE);

            $this->payment_complete($transaction_id, $note);

            if (!empty($fee)) {
                update_post_meta($this->order->get_id(), 'PayPal Transaction Fee', wc_clean($fee));
            }

            do_action('wc_paypal_plus__log', 'Payment completed successfully ', []);

            return true;
        }

        $this->payment_on_hold(
            sprintf(
                __('Payment pending: %s', 'woo-paypalplus'),
                $this->ipnRequest->get(Request::KEY_PENDING_REASON)
            )
        );
        do_action('wc_paypal_plus__log', 'Payment put on hold ', []);

        return true;
    }

    /**
     * Save relevant data from the IPN to the order.
     */
    private function save_paypal_meta_data()
    {
        $postMeta = [
            'payer_email' => 'Payer PayPal address',
            'first_name' => 'Payer first name',
            'last_name' => 'Payer last name',
            'payment_type' => 'Payment type',
        ];

        foreach ($postMeta as $key => $name) {
            $value = $this->ipnRequest->get($key);
            $value and update_post_meta($this->order->get_id(), $name, wc_clean($value));
        }
    }

    /**
     * Complete order, add transaction ID and note.
     *
     * @param string $transaction_id The Transaction ID.
     * @param string $note Payment note.
     */
    private function payment_complete($transaction_id, $note)
    {
        $this->order->add_order_note($note);
        $this->order->payment_complete($transaction_id);
    }

    /**
     * Hold order and add note.
     *
     * @param string $reason Reason for refunding.
     */
    private function payment_on_hold($reason)
    {
        $this->order->update_status(self::ORDER_STATUS_ON_HOLD, $reason);
        wc_reduce_stock_levels($this->order->get_id());
        wc()->cart->empty_cart();
    }

    /**
     * Handle a denied payment.
     *
     * @return bool
     */
    public function payment_status_denied()
    {
        return $this->payment_status_failed();
    }

    /**
     * Handle a failed payment.
     *
     * @return bool
     */
    public function payment_status_failed()
    {
        return $this->order->update_status(
            'failed',
            sprintf(
                __('Payment %s via IPN.', 'woo-paypalplus'),
                wc_clean($this->ipnRequest->get(Request::KEY_PAYMENT_STATUS))
            )
        );
    }

    /**
     * Handle an expired payment.
     *
     * @return bool
     */
    public function payment_status_expired()
    {
        return $this->payment_status_failed();
    }

    /**
     * Handle a voided payment.
     *
     * @return bool
     */
    public function payment_status_voided()
    {
        return $this->payment_status_failed();
    }

    /**
     * Handle a refunded order.
     */
    public function payment_status_refunded()
    {
        if ($this->validator->is_valid_refund()) {
            $this->order->update_status(
                self::ORDER_STATUS_REFUNDED,
                sprintf(
                    __('Payment %s via IPN.', 'woo-paypalplus'),
                    $this->ipnRequest->get(Request::KEY_PAYMENT_STATUS)
                )
            );
            do_action(
                'wc_paypal_plus__ipn_payment_update',
                self::ORDER_STATUS_REFUNDED,
                $this->ipnData
            );
        }
    }

    /**
     * Handle a payment reversal.
     */
    public function payment_status_reversed()
    {
        $this->order->update_status(
            self::ORDER_STATUS_ON_HOLD,
            sprintf(
                __('Payment %s via IPN.', 'woo-paypalplus'),
                wc_clean(
                    $this->ipnRequest->get(Request::KEY_PAYMENT_STATUS)
                )
            )
        );

        do_action('wc_paypal_plus__ipn_payment_update', 'reversed', $this->ipnData);
    }

    /**
     * Handle a cancelled reversal.
     */
    public function payment_status_canceled_reversal()
    {
        do_action('wc_paypal_plus__ipn_payment_update', 'canceled_reversal', $this->ipnData);
    }
}
