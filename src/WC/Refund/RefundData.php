<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC\Refund;

use Inpsyde\Lib\PayPal\Api\Amount;
use Inpsyde\Lib\PayPal\Api\RefundRequest;
use Inpsyde\Lib\PayPal\Api\Sale;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WC_Order_Refund;

/**
 * Class RefundData
 *
 * Bridge between WooCommcerce and PayPal objects.
 * Provides WooCommcerce with the objects needed to perform a refund
 *
 * @package WCPayPalPlus\WC
 */
class RefundData
{
    /**
     * WooCommcerce Order object.
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * Refund amount.
     *
     * @var float
     */
    private $amount;
    /**
     * PayPal API Context object.
     *
     * @var ApiContext
     */
    private $context;
    /**
     * Refund reason.
     *
     * @var string
     */
    private $reason;

    /**
     * RefundData constructor.
     *
     * @param WC_Order_Refund $order WooCommerce Order object.
     * @param float $amount Refund amount.
     * @param string $reason Refund reason.
     * @param ApiContext $context PayPal API Context object.
     */
    public function __construct(WC_Order_Refund $order, $amount, $reason, ApiContext $context)
    {
        $this->order = $order;
        $this->amount = (float)$amount;
        $this->context = $context;
        $this->reason = $reason;
    }

    /**
     * Returns the refund amount.
     *
     * @return float
     */
    public function get_amount()
    {
        return $this->amount;
    }

    /**
     * Returns the refund reason.
     *
     * @return string
     */
    public function get_reason()
    {
        return $this->reason;
    }

    /**
     * Returns the Sale object.
     *
     * @return Sale
     */
    public function get_sale()
    {
        return Sale::get($this->order->get_transaction_id(), $this->context);
    }

    /**
     * Returns a configured RefundRequest object.
     *
     * @return RefundRequest
     */
    public function get_refund()
    {
        $amt = new Amount();
        $amt->setCurrency($this->order->get_currency());
        $amt->setTotal($this->number_format($this->amount));
        $refund = new RefundRequest();
        $refund->setAmount($amt);

        return $refund;
    }

    /**
     * Sanitize function for price display.
     *
     * @param float $price The price to format.
     *
     * @return string
     */
    private function number_format($price)
    {
        $decimals = 2;

        if (in_array(get_woocommerce_currency(), ['HUF', 'JPY', 'TWD'], true)) {
            $decimals = 0;
        }

        return number_format($price, $decimals, '.', '');
    }

    /**
     * Returns the success handler object
     *
     * @param string $transaction_id PayPal transaction ID.
     *
     * @return RefundSuccess
     */
    public function get_success_handler($transaction_id)
    {
        return new RefundSuccess($this->order, $transaction_id, $this->reason);
    }
}
