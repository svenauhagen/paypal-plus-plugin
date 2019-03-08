<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\PlusGateway;

use WCPayPalPlus\Setting;

/**
 * Class DefaultGatewayOverride
 *
 * Overrides the default Payment Gateway ONCE per user session.
 *
 * Hence, it should never override user input.
 */
class DefaultGatewayOverride
{
    const SESSION_CHECK_KEY = '_ppp_default_override_flag';
    const SESSION_CHECK_ACTIVATE = '1';

    private $repository;

    public function __construct(Setting\PlusStorable $repository)
    {
        $this->repository = $repository;
    }

    public function maybeOverride()
    {
        if (!$this->isValidRequest()
            || !$this->repository->isDefaultGatewayOverrideEnabled()
        ) {
            return;
        }

        $this->setChosenPaymentMethod(Gateway::GATEWAY_ID);
        $this->setSessionFlag();
    }

    /**
     * Check the current request
     *
     * @return bool
     */
    public function isValidRequest()
    {
        $paymentMethod = (string)filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);

        if ($paymentMethod
            || !is_checkout()
            || $this->getSessionFlag()
            || is_ajax()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve our private session flag
     *
     * @return array|string
     */
    private function getSessionFlag()
    {
        return wc()->session->get(self::SESSION_CHECK_KEY);
    }

    /**
     * Set the gateway override
     *
     * @param string $paymentMethod
     */
    private function setChosenPaymentMethod($paymentMethod)
    {
        assert(is_string($paymentMethod));

        wc()->session->set('chosen_payment_method', $paymentMethod);
    }

    /**
     * Set our private session flag
     */
    private function setSessionFlag()
    {
        wc()->session->set(self::SESSION_CHECK_KEY, self::SESSION_CHECK_ACTIVATE);
    }
}
