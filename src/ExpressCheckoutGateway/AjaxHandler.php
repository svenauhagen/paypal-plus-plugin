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
    // TODO Add log system for json error responses
    public function handle()
    {
        if (!$this->nonce->validate($this->nonceContext)) {
            return;
        }

        $context = $this->context();
        if (!$context) {
            wp_send_json_error([
                'message' => $this->invalidContextMessage(),
            ]);
        }

        $response = $this->dispatcher->dispatch($context);
        if (!$response) {
            wp_send_json_error([
                'message' => $this->invalidResponseMessage(),
            ]);
        }

        wp_send_json_success($response);
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
     * @return string|void
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
