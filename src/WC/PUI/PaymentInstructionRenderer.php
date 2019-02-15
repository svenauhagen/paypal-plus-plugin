<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 16.01.17
 * Time: 15:22
 */

namespace WCPayPalPlus\WC\PUI;

use WCPayPalPlus\Setting\PlusRepository;

/**
 * Class PaymentInstructionRenderer
 *
 * @package WCPayPalPlus\WC\PUI
 */
class PaymentInstructionRenderer
{
    private $settingRepository;

    public function __construct(PlusRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Gather needed data and then render the view, if possible
     */
    public function delegate_thankyou($order_id)
    {
        $order = wc_get_order($order_id);
        $puiData = PaymentInstructionFactory::createData(
            $order,
            $this->settingRepository->legalNotes()
        );

        if (!$puiData->has_payment_instructions()) {
            return;
        }

        $puiView = PaymentInstructionFactory::createViewFromData($puiData);
        $puiView->thankyouPage();
    }

    /**
     * Gather needed data and then render the view, if possible
     *
     * @param \WC_Order $order WooCommerce order.
     * @param bool $sentToAdmin Will the eMail be sent to the site admin?.
     * @param bool $plain_text Should we render as plain text?.
     */
    public function delegate_email(\WC_Order $order, $sentToAdmin, $plain_text = false)
    {
        $puiData = PaymentInstructionFactory::createData(
            $order,
            $this->settingRepository->legalNotes()
        );

        if (!$puiData->has_payment_instructions()) {
            return;
        }

        $puiView = PaymentInstructionFactory::createViewFromData($puiData);
        $puiView->emailInstructions($plain_text);
    }

    /**
     * Gather needed data and then render the view, if possible.
     *
     * @param int $order_id WooCommerce order ID.
     */
    public function delegate_view_order($order_id)
    {
        $order = wc_get_order($order_id);
        $puiData = PaymentInstructionFactory::createData(
            $order,
            $this->settingRepository->legalNotes()
        );

        if (!$puiData->has_payment_instructions()) {
            return;
        }

        $puiView = PaymentInstructionFactory::createViewFromData($puiData);
        $puiView->thankyouPage();
    }
}
