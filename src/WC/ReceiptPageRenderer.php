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
use WCPayPalPlus\Payment\PaymentPatchFactory;
use WCPayPalPlus\Payment\Session;
use OutOfBoundsException;
use RuntimeException;

/**
 * Class ReceiptPageRenderer
 * @package WCPayPalPlus\WC
 */
class ReceiptPageRenderer
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
    private $session;

    /**
     * @var CheckoutDropper
     */
    private $checkoutDropper;

    /**
     * ReceiptPageRenderer constructor.
     * @param OrderFactory $orderFactory
     * @param PaymentPatchFactory $paymentPatchFactory
     * @param PlusStorable $settingRepository
     * @param Session $session
     * @param CheckoutDropper $checkoutDropper
     */
    public function __construct(
        OrderFactory $orderFactory,
        PaymentPatchFactory $paymentPatchFactory,
        PlusStorable $settingRepository,
        Session $session,
        CheckoutDropper $checkoutDropper
    ) {

        $this->orderFactory = $orderFactory;
        $this->paymentPatchFactory = $paymentPatchFactory;
        $this->settingRepository = $settingRepository;
        $this->session = $session;
        $this->checkoutDropper = $checkoutDropper;
    }

    /**
     * TODO May be this method can be split into two methods and use action to run the patcher.
     *      See the same code in \WCPayPalPlus\ExpressCheckoutGateway\Gateway::process_payment
     *
     * @param $orderId
     * @throws OutOfBoundsException
     * @throws RuntimeException
     */
    public function render($orderId)
    {
        $this->session->set(Session::ORDER_ID, $orderId);
        $order = $this->orderFactory->createById($orderId);
        $paymentId = $this->session->get(Session::PAYMENT_ID);

        !$paymentId and $this->checkoutDropper->abortCheckout();

        $paymentPatcher = $this->paymentPatchFactory->create(
            $order,
            $paymentId,
            $this->settingRepository->invoicePrefix(),
            ApiContextFactory::getFromConfiguration()
        );

        $isSuccessPatched = $paymentPatcher->execute();
        !$isSuccessPatched and $this->checkoutDropper->abortCheckout();

        wp_enqueue_script('paypalplus-woocommerce-plus-paypal-redirect');
    }
}
