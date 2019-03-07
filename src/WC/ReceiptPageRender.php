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

use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\WC\Payment\PaymentPatchFactory;
use WCPayPalPlus\WC\Payment\Session;

/**
 * Class ReceiptPageRender
 * @package WCPayPalPlus\WC
 */
class ReceiptPageRender
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var PaymentPatchFactory
     */
    private $paymentPatchFactory;

    /**
     * @var PlusStorable
     */
    private $settingRepository;

    /**
     * @var Session
     */
    private $paymentSession;

    /**
     * ReceiptPageRender constructor.
     * @param OrderFactory $orderFactory
     * @param PaymentPatchFactory $paymentPatchFactory
     * @param PlusStorable $settingRepository
     * @param Session $paymentSession
     */
    public function __construct(
        OrderFactory $orderFactory,
        PaymentPatchFactory $paymentPatchFactory,
        PlusStorable $settingRepository,
        Session $paymentSession
    ) {

        $this->orderFactory = $orderFactory;
        $this->paymentPatchFactory = $paymentPatchFactory;
        $this->settingRepository = $settingRepository;
        $this->paymentSession = $paymentSession;
    }

    /**
     * @param $orderId
     */
    public function render($orderId)
    {
        $this->paymentSession->set(Session::ORDER_ID, $orderId);
        $order = $this->orderFactory->createById($orderId);
        $paymentId = $this->paymentSession->get(Session::PAYMENT_ID);

        if (!$paymentId) {
            $this->abortCheckout();

            return;
        }

        $paymentPatcher = $this->paymentPatchFactory->create(
            $order,
            $paymentId,
            $this->settingRepository->invoicePrefix(),
            ApiContextFactory::get()
        );

        if ($paymentPatcher->execute()) {
            wp_enqueue_script('paypalplus-woocommerce-plus-paypal-redirect');
            return;
        }

        $this->abortCheckout();
    }

    /**
     * @return void
     */
    private function abortCheckout()
    {
        $this->paymentSession->clean();

        wc_add_notice(
            esc_html__('Error processing checkout. Please try again.', 'woo-paypalplus'),
            'error'
        );

        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
