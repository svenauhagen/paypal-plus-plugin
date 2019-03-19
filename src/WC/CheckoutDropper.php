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
     * TODO May be a reason as parameter? Just for log and notice.
     * TODO May be passing the url to be different based on cases?
     *
     * Abort checkout
     */
    public function abortCheckout()
    {
        $this->session->clean();

        // TODO May be a more useful message for the user.
        wc_add_notice(
            esc_html__('Error processing checkout. Please try again.', 'woo-paypalplus'),
            'error'
        );

        // TODO Shouldn't be the same of the `Cancel Url` option?
        //      Also, would be better if we could manage the case when the user want to cancel the order.
        //      Otherwise do not do any redirection here and let's do it by the caller.
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
