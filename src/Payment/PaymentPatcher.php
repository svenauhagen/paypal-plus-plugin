<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 18:17
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;

/**
 * Class PaymentPatcher
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentPatcher
{
    /**
     * Patch data object.
     *
     * @var PaymentPatchData
     */
    private $patch_data;

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
        $this->patch_data = $patch_data;
        $this->logger = $logger;
    }

    /**
     * Execute the PatchRequest
     *
     * @return bool
     */
    public function execute()
    {
        $success = false;
        $patch_request = $this->patch_data->get_patch_request();

        try {
            $payment = $this->patch_data->get_payment();
            $success = $payment->update($patch_request, $this->patch_data->get_api_context());
        } catch (PayPalConnectionException $exc) {
            $this->logger->error($exc);
        }

        return $success;
    }
}
