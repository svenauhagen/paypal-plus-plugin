<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 10:54
 */

namespace WCPayPalPlus\WC\Refund;

use WCPayPalPlus\WC\RequestSuccessHandler;

/**
 * Class RefundSuccess
 *
 * @package WCPayPalPlus\WC\Refund
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
