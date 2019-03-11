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

use const WCPayPalPlus\ACTION_LOG;
use WCPayPalPlus\Payment\Session;
use WC_Log_Levels as LogLevels;

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

        do_action(ACTION_LOG, LogLevels::ERROR, 'Checkout Dropped', compact($this->session));

        wc_add_notice(
            esc_html__('Error processing checkout. Please try again.', 'woo-paypalplus'),
            'error'
        );

        // TODO Shouldn't be the same of the `Cancel Url` option?
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
