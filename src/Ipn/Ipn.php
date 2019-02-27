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

use const WCPayPalPlus\ACTION_LOG;
use WCPayPalPlus\OrderFactory;

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
    private $ipnRequest;

    /**
     * IPN Validator class
     *
     * @var Validator
     */
    private $ipnValidator;

    /**
     * @var Data
     */
    private $ipnData;

    /**
     * Ipn constructor.
     * @param Data $ipnData
     * @param Request $ipnRequest
     * @param Validator $validator
     */
    public function __construct(Data $ipnData, Request $ipnRequest, Validator $validator)
    {
        $this->ipnData = $ipnData;
        $this->ipnRequest = $ipnRequest;
        $this->ipnValidator = $validator;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function checkResponse()
    {
        try {
            // Ensure an order exists
            OrderFactory::createByIpnRequest($this->ipnRequest);
        } catch (\Exception $exc) {
            do_action(ACTION_LOG, 'error', $exc->getMessage(), compact($exc));

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw $exc;
            }

            return;
        }

        if ($this->ipnValidator->validate()) {
            $this->valid_response();
            exit;
        }

        do_action('wc_paypal_plus_log_error', 'Invalid IPN call', $this->ipnRequest->all());
        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        wp_die('PayPal IPN Request Failure', 'PayPal IPN', ['response' => 500]);
    }

    /**
     * There was a valid response.
     */
    public function valid_response()
    {
        $payment_status = $this->ipnRequest->get(Request::KEY_PAYMENT_STATUS);
        $updater = OrderUpdaterFactory::create($this->ipnData, $this->ipnRequest);

        if (method_exists($updater, 'payment_status_' . $payment_status)) {
            do_action(
                'wc_paypal_plus_log',
                'Processing IPN. payment status: ' . $payment_status,
                $this->ipnRequest->all()
            );
            $updater->{"payment_status_{$payment_status}"}();

            return true;
        }

        return false;
    }
}
