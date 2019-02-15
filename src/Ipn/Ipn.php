<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.11.16
 * Time: 10:46
 */

namespace WCPayPalPlus\Ipn;

/**
 * Handles responses from PayPal IPN.
 */
class Ipn
{
    const IPN_ENDPOINT_SUFFIX = '_ipn';

    /**
     * IPN Data Provider
     *
     * @var Data
     */
    private $ipnData;

    /**
     * IPN Validator class
     *
     * @var Validator
     */
    private $ipnValidator;

    public function __construct(Data $ipnData, Validator $validator)
    {
        $this->ipnData = $ipnData;
        $this->ipnValidator = $validator;
    }

    /**
     * Check for PayPal IPN Response.
     */
    public function checkResponse()
    {
        $order = $this->ipnData->woocommerceOrder();

        if ($order
            && $this->ipnValidator->validate()
            && !empty($this->ipnData->get('custom'))
        ) {
            $this->valid_response();
            exit;
        }

        do_action('wc_paypal_plus_log_error', 'Invalid IPN call', $this->ipnData->all());
        wp_die('PayPal IPN Request Failure', 'PayPal IPN', ['response' => 500]);
    }

    /**
     * There was a valid response.
     */
    public function valid_response()
    {
        $payment_status = $this->ipnData->paymentStatus();
        $updater = $this->ipnData->orderUpdater();

        if (method_exists($updater, 'payment_status_' . $payment_status)) {
            do_action(
                'wc_paypal_plus_log',
                'Processing IPN. payment status: ' . $payment_status,
                $this->ipnData->all()
            );
            $updater->{"payment_status_{$payment_status}"}();

            return true;
        }

        return false;
    }
}
