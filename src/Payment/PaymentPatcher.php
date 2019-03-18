<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 18:17
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use WC_Logger_Interface as Logger;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway;

/**
 * Class PaymentPatcher
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentPatcher
{
    const ACTION_AFTER_PAYMENT_PATCH = 'woopaypalplus.after_express_checkout_payment_patch';

    /**
     * Patch data object.
     *
     * @var PaymentPatchData
     */
    private $patchData;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * PaymentPatcher constructor.
     *
     * @param PaymentPatchData $patch_data You guessed it: The Patch data.
     * @param Logger $logger
     */
    public function __construct(PaymentPatchData $patch_data, Logger $logger)
    {
        $this->patchData = $patch_data;
        $this->logger = $logger;
    }

    /**
     * Execute the PatchRequest
     *
     * @return bool
     */
    public function execute()
    {
        $isSuccessPatched = false;
        $patchRequest = $this->patchData->get_patch_request();

        try {
            $payment = $this->patchData->get_payment();
            $isSuccessPatched = $payment->update(
                $patchRequest,
                $this->patchData->get_api_context()
            );
        } catch (PayPalConnectionException $exc) {
            $this->logger->error($exc);
        }

        /**
         * Action After Payment Patch
         *
         * @param PaymentPatcher $paymentPatcher
         * @oparam bool $isSuccessPatched
         */
        do_action(
            self::ACTION_AFTER_PAYMENT_PATCH,
            $this,
            $isSuccessPatched,
            $this->patchData
        );

        return $isSuccessPatched;
    }
}
