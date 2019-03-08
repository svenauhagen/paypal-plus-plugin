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

use Inpsyde\Lib\PayPal\Api\Patch;
use Inpsyde\Lib\PayPal\Api\PatchRequest;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WC_Order;

/**
 * Class PaymentPatchData
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentPatchData
{
    /**
     * WooCommerce Order object.
     *
     * @var WC_Order
     */
    private $order;

    /**
     * The Payment ID.
     *
     * @var string
     */
    private $paymentId;

    /**
     * The invoice prefix.
     *
     * @var string
     */
    private $invoicePrefix;

    /**
     * The PayPal SDK ApiContext object.
     *
     * @var ApiContext
     */
    private $apiContext;

    /**
     * The PatchProvider object
     *
     * @var PatchProvider
     */
    private $patchProvider;

    /**
     * PaymentPatchData constructor.
     *
     * @param WC_Order $order WooCommerce Order object.
     * @param string $paymentId The Payment ID.
     * @param string $invoicePrefix The invoice prefix.
     * @param ApiContext $apiContext The PayPal SDK ApiContext object.
     * @param PatchProvider $patchProvider The PatchProvider object.
     */
    public function __construct(
        WC_Order $order,
        $paymentId,
        $invoicePrefix,
        ApiContext $apiContext,
        PatchProvider $patchProvider
    ) {

        assert(is_string($paymentId));
        assert(is_string($invoicePrefix));

        $this->order = $order;
        $this->paymentId = $paymentId;
        $this->invoicePrefix = $invoicePrefix;
        $this->apiContext = $apiContext;
        $this->patchProvider = $patchProvider;
    }

    /**
     * Returns the WooCommerce Order object
     *
     * @return WC_Order
     */
    public function get_order()
    {
        return $this->order;
    }

    /**
     * Fetches an existing Payment object via API call
     *
     * @return Payment
     */
    public function get_payment()
    {
        return Payment::get($this->get_payment_id(), $this->get_api_context());
    }

    /**
     * Returns the payment ID.
     *
     * @return string
     */
    public function get_payment_id()
    {
        return $this->paymentId;
    }

    /**
     * Returns the APIContext object.
     *
     * @return ApiContext
     */
    public function get_api_context()
    {
        return $this->apiContext;
    }

    /**
     * Returns a configured PatchRequest object.
     *
     * @return PatchRequest
     */
    public function get_patch_request()
    {
        $patch_request = new PatchRequest();

        $patch_request->setPatches($this->get_patches());

        return $patch_request;
    }

    /**
     * Returns an array of configured Patch objects relevant to the current request
     *
     * @return Patch[]
     */
    private function get_patches()
    {
        $patches = [
            $this->patchProvider->get_payment_amount_patch(),
            $this->patchProvider->get_custom_patch(),
            $this->patchProvider->get_invoice_patch($this->get_invoice_prefix()),
            $this->patchProvider->get_billing_patch(),
        ];

        return $patches;
    }

    /**
     * Returns the invoice prefix.
     *
     * @return string
     */
    public function get_invoice_prefix()
    {
        return $this->invoicePrefix;
    }
}
