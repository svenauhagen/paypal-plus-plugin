<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Refund;

use WCPayPalPlus\WC\RequestSuccessHandler;

/**
 * Class RefundSuccess
 *
 * @package WCPayPalPlus\Refund
 */
class RefundSuccess implements RequestSuccessHandler
{
    /**
     * WooCommerce Order object.
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * PayPal transaction ID.
     *
     * @var string
     */
    private $transaction_id;

    /**
     * @var string
     */
    private $reason;

    /**
     * RefundSuccess constructor.
     *
     * @param \WC_Order $order WooCommerce Order object.
     * @param string $transaction_id PayPal transaction ID.
     * @param string $reason Refund reason.
     */
    public function __construct(\WC_Order $order, $transaction_id, $reason)
    {
        $this->order = $order;
        $this->transaction_id = $transaction_id;
        $this->reason = $reason;
    }

    /**
     * Handle the successful request.
     *
     * @return bool
     */
    public function execute()
    {
        $this->order->add_order_note('Refund Transaction ID:' . $this->transaction_id);
        $this->reason and $this->order->add_order_note('Reason for Refund :' . $this->reason);

        $max_remaining_refund = wc_format_decimal($this->order->get_total() - $this->order->get_total_refunded());
        if ($max_remaining_refund <= 0) {
            $this->order->update_status('refunded');
        }

        return true;
    }
}
