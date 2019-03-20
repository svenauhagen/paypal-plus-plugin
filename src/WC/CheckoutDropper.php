<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC;

use WCPayPalPlus\Session\Session;

/**
 * Class CheckoutDropper
 * @package WCPayPalPlus\WC
 */
class CheckoutDropper
{
    /**
     * @var Session
     */
    private $session;

    /**
     * CheckoutDropper constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Abort Checkout and Redirect User
     */
    public function abortSession()
    {
        wc_add_notice($this->errorMessage(), 'error');
        $this->session->clean();
        wp_safe_redirect($this->url());
        exit;
    }

    /**
     * Abort Order
     */
    public function abort()
    {
        wc_add_notice($this->errorMessage(), 'error');
    }

    /**
     * @return string
     */
    private function errorMessage()
    {
        // TODO May be a more useful message for the user.
        return esc_html__('Error processing checkout. Please try again.', 'woo-paypalplus');
    }

    /**
     * @return string
     */
    private function url()
    {
        // TODO Still need to clarify which url to use, for both payment methods or they'll have different?
        return wc_get_cart_url();
    }
}
