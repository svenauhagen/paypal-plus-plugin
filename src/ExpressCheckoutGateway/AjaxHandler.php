<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\NonceInterface;
use const WCPayPalPlus\ACTION_LOG;
use WC_Log_Levels;

/**
 * Class AjaxHandler
 * @package WCPayPalPlus\ExpressCheckout
 */
class AjaxHandler
{
    const ACTION = 'paypal_express_checkout_request';
    const VALID_CONTEXTS = [
        'cart',
        'product',
    ];

    /**
     * @var NonceInterface
     */
    private $nonce;

    /**
     * @var NonceContextInterface
     */
    private $nonceContext;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * AjaxHandler constructor.
     * @param NonceInterface $nonce
     * @param NonceContextInterface $nonceContext
     * @param Dispatcher $dispatcher
     */
    public function __construct(
        NonceInterface $nonce,
        NonceContextInterface $nonceContext,
        Dispatcher $dispatcher
    ) {

        $this->nonce = $nonce;
        $this->nonceContext = $nonceContext;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the request and dispatch the action based on the `context`
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->nonce->validate($this->nonceContext)) {
            return;
        }

        $context = $this->context();
        if (!$context) {
            $this->sendJsonError([
                'message' => $this->invalidContextMessage(),
            ]);
        }

        $response = $this->dispatcher->dispatch($context);
        if (!$response) {
            $this->sendJsonError([
                'message' => $this->invalidResponseMessage(),
            ]);
        }

        wp_send_json_success($response);
    }

    /**
     * @param array $data
     */
    private function sendJsonError(array $data)
    {
        $message = isset($data['message']) ? $data['message'] : 'No Message Provided.';

        do_action(ACTION_LOG, WC_Log_Levels::ERROR, $message, compact($data));

        wp_send_json_error($data);
    }

    /**
     * Retrieve the context from the request
     *
     * @return mixed
     */
    private function context()
    {
        return filter_input(INPUT_POST, 'context', FILTER_SANITIZE_STRING);
    }

    /**
     * The invalid Context Message
     *
     * @return string
     */
    private function invalidContextMessage()
    {
        $message = _x(
            'Invalid context for express checkout request. Allowed are: %s.',
            'express-checkout',
            'woo-paypalplus'
        );
        $validContextList = implode(',', self::VALID_CONTEXTS);

        return sprintf($message, $validContextList);
    }

    /**
     * Invalid Response Message
     *
     * @return string
     */
    private function invalidResponseMessage()
    {
        return _x(
            'Something happened but we do not know what',
            'express-checkout',
            'woo-paypalplus'
        );
    }
}
