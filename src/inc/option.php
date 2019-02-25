<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus;

use WCPayPalPlus\Notice\DismissibleNoticeOption;

/**
 * Delete the plugins options
 *
 * @returns null
 */
function delete_options()
{
    global $wpdb;

    delete_option('woocommerce_paypal_plus_settings');
    delete_site_transient('ppplus_message_id');
    delete_site_transient('ppplus_message_content');

    $noticePrefix = DismissibleNoticeOption::OPTION_PREFIX;

    $wpdb->query("DELETE FROM {$wpdb->options} WHERE 'option_name' LIKE '{$noticePrefix}%'");
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE 'option_name' LIKE 'woocommerce_ppec_payer_id_%'"
    );
}
