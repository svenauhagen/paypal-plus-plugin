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

/**
 * Class PayPalIframeView
 *
 * @package WCPayPalPlus\WC
 */
class PlusFrameView
{
    /**
     * Render the Paywall iframe
     *
     * @param array $data
     */
    public function render(array $data)
    {
        $id = $data['placeholder'];
        $config = wp_json_encode($data);
        ?>
        <div
            id="<?php echo esc_attr($id) ?>"
            class="paypalplus-paywall"
            data-config="<?= esc_attr($config) ?>"
        ></div>
        <?php
    }
}
