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
use WCPayPalPlus\Utils\AjaxJsonRequest;
use WCPayPalPlus\Request\Request;

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
     * @var Request
     */
    private $request;

    /**
     * @var AjaxJsonRequest
     */
    private $ajaxJsonRequest;

    /**
     * AjaxHandler constructor.
     * @param NonceInterface $nonce
     * @param NonceContextInterface $nonceContext
     * @param Dispatcher $dispatcher
     * @param Request $request
     * @param AjaxJsonRequest $ajaxJsonRequest
     */
    public function __construct(
        NonceInterface $nonce,
        NonceContextInterface $nonceContext,
        Dispatcher $dispatcher,
        Request $request,
        AjaxJsonRequest $ajaxJsonRequest
    ) {

        $this->nonce = $nonce;
        $this->nonceContext = $nonceContext;
        $this->dispatcher = $dispatcher;
        $this->request = $request;
        $this->ajaxJsonRequest = $ajaxJsonRequest;
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
        $task = $this->task();
        $requestData = $this->request->all();

        if (!$context) {
            $this->ajaxJsonRequest->sendJsonError([
                'message' => $this->invalidContextMessage(),
            ]);
        }

        $this->dispatcher->dispatch($context, $task, $requestData);
    }

    /**
     * Retrieve the context from the request data
     *
     * @return mixed
     */
    private function context()
    {
        return $this->request->get('context', FILTER_SANITIZE_STRING);
    }

    /**
     * Retrieve the task name from the request data
     *
     * @return string
     */
    private function task()
    {
        return $this->request->get('task', FILTER_SANITIZE_STRING);
    }

    /**
     * The invalid Context Message
     *
     * @return string
     */
    private function invalidContextMessage()
    {
        $message = esc_html_x(
            'Invalid context for express checkout request. Allowed are: %s.',
            'express-checkout',
            'woo-paypalplus'
        );
        $validContextList = implode(',', self::VALID_CONTEXTS);

        return sprintf($message, $validContextList);
    }
}
