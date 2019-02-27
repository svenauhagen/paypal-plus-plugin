<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC;

/**
 * Class ReceiptPageView
 *
 * @package WCPayPalPlus\WC
 */
class ReceiptPageView
{
    /**
     * Setup the Receipt page JS
     */
    public function render()
    {
        $message = __(
            'Thank you for your order. We are now redirecting you to PayPal to make payment.',
            'woo-paypalplus'
        );
        ?>
        <script>
            function plusRedirect()
            {
                jQuery.blockUI({
                    message: "<?php echo esc_js($message);?>",
                    baseZ: 99999,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    },
                    css: {
                        padding: '20px',
                        zindex: '9999999',
                        textAlign: 'center',
                        color: '#555',
                        border: '3px solid #aaa',
                        backgroundColor: '#fff',
                        cursor: 'wait',
                        lineHeight: '24px'
                    }
                });

                if (typeof PAYPAL != 'undefined') {
                    PAYPAL.apps.PPP.doCheckout();
                    return;
                }

                setTimeout(function () {
                    PAYPAL.apps.PPP.doCheckout();
                }, 500);
            }

            window.addEventListener('load', function () {
                plusRedirect();
            });
        </script>
        <?php
    }
}
