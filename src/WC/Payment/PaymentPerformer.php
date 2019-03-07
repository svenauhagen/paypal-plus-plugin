<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC\Payment;

use WCPayPalPlus\WC\RequestSuccessHandler;

/**
 * Class PaymentPerformer
 *
 * @package WCPayPalPlus\WC\Payment
 */
class PaymentPerformer
{
    /**
     * PaymentExecutionData object.
     *
     * @var PaymentExecutionData
     */
    private $data;

    /**
     * SuccessHandler object.
     *
     * @var RequestSuccessHandler[]
     */
    private $successHandlers;

    /**
     * PaymentPerformer constructor.
     *
     * @param PaymentExecutionData $data PaymentExecutionData object.
     * @param RequestSuccessHandler[] $successHandlers Array of SuccessHandler objects.
     */
    public function __construct(
        PaymentExecutionData $data,
        RequestSuccessHandler ...$successHandlers
    ) {

        $this->data = $data;
        $this->successHandlers = $successHandlers;
    }

    /**
     * Execute the Payment.
     *
     * @return bool
     */
    public function execute()
    {
        $payment = $this->data->get_payment();
        $payment->execute($this->data->get_payment_execution(), $this->data->get_context());

        foreach ($this->successHandlers as $success_handler) {
            $success_handler->execute();
        }

        return true;
    }
}
