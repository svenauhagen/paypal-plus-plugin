<?php # -*- coding: utf-8 -*-

namespace WCPayPalPlus\WC;

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

        $this->setChosenPaymentMethod(PlusGateway::GATEWAY_ID);
        $this->setSessionFlag();
    }

    /**
     * Check the current request
     *
     * @return bool
     */
    public function isValidRequest()
    {
        if (is_ajax()) {
            return false;
        }

        // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
        if (isset($_POST['payment_method'])) {
            return false;
        }

        if (!is_checkout()) {
            return false;
        }

        if ($this->getSessionFlag()) {
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
        return WC()->session->get(self::SESSION_CHECK_KEY);
    }

    /**
     * Set the gateway override
     *
     * @param string $paymentMethod
     */
    private function setChosenPaymentMethod($paymentMethod)
    {
        assert(is_string($paymentMethod));

        WC()->session->set('chosen_payment_method', $paymentMethod);
    }

    /**
     * Set our private session flag
     */
    private function setSessionFlag()
    {
        WC()->session->set(self::SESSION_CHECK_KEY, self::SESSION_CHECK_ACTIVATE);
    }
}
