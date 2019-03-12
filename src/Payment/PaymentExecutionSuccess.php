<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 06.12.16
 * Time: 09:37
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Order\OrderStatuses;
use WCPayPalPlus\WC\RequestSuccessHandler;
use WooCommerce;

/**
 * Class PaymentExecutionSuccess
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentExecutionSuccess implements RequestSuccessHandler
{
    const PAY_UPON_INVOICE = 'PAY_UPON_INVOICE';

    /**
     * Payment Data from successful Execution.
     *
     * @var PaymentExecutionData
     */
    private $data;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * PaymentExecutionSuccess constructor.
     * @param WooCommerce $wooCommerce
     * @param PaymentExecutionData $data
     */
    public function __construct(WooCommerce $wooCommerce, PaymentExecutionData $data)
    {
        $this->wooCommerce = $wooCommerce;
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->data->is_approved()) {
            $this->updateOrder();

            $this->wooCommerce->cart->empty_cart();
        } else {
            $notice = sprintf(
                __('There was an error executing the payment. Payment state: %s', 'woo-paypalplus'),
                $this->data->get_payment_state()
            );
            wc_add_notice($notice, 'error');
        }
    }

    /**
     * Update Order details.
     */
    private function updateOrder()
    {
        $sale = $this->data->get_sale();
        $saleId = $sale->getId();
        $order = $this->data->get_order();

        switch ($sale->getState()) {
            case OrderStatuses::ORDER_STATUS_PENDING:
                $note = sprintf(
                    esc_html__('PayPal Reason code: %s.', 'woo-paypalplus'),
                    $sale->getReasonCode()
                );
                $order->add_order_note($note);
                $order->update_status(OrderStatuses::ORDER_STATUS_ON_HOLD);
                break;
            case OrderStatuses::ORDER_STATUS_COMPLETED:
                if (!$this->data->is_pui()) {
                    break;
                }

                $order->add_order_note(__('PayPal PLUS payment completed', 'woo-paypalplus'));
                $order->payment_complete($saleId);
                $note = sprintf(
                    __('PayPal PLUS payment approved! Transaction ID: %s', 'woo-paypalplus'),
                    $saleId
                );
                $order->add_order_note($note);
                $this->wooCommerce->cart->empty_cart();
                break;
            default:
                $order->update_status(
                    OrderStatuses::ORDER_STATUS_ON_HOLD,
                    esc_html__('Awaiting payment', 'woo-paypalplus')
                );
                wc_reduce_stock_levels($order->get_id());
                break;
        }

        if ($this->data->is_pui()) {
            $instruction = $this->data->get_payment_instruction();
            $instructionType = $instruction->getInstructionType();
            if ($instructionType === self::PAY_UPON_INVOICE) {
                $this->updatePaymentData($saleId);
            }
        }

        if ($this->shouldUpdateAddress()) {
            $this->updateBillingAddress();
        }
    }

    /**
     * Update order post meta with payment information.
     *
     * @param string $sale_id PayPal Payment ID.
     */
    private function updatePaymentData($sale_id)
    {
        $order = $this->data->get_order();

        $paymentInstruction = $this->data->get_payment_instruction();
        $referenceNumber = $paymentInstruction->getReferenceNumber();
        $paymentDueDate = $paymentInstruction->getPaymentDueDate();

        $recipient_banking_instruction = $paymentInstruction->getRecipientBankingInstruction();
        $bankName = $recipient_banking_instruction->getBankName();
        $accountHolderName = $recipient_banking_instruction->getAccountHolderName();
        $iban = $recipient_banking_instruction->getInternationalBankAccountNumber();
        $bankIdentifierCode = $recipient_banking_instruction->getBankIdentifierCode();

        $instructionData['reference_number'] = $referenceNumber;
        $instructionData['instruction_type'] = self::PAY_UPON_INVOICE;
        $instructionData['recipient_banking_instruction']['bank_name'] = $bankName;
        $instructionData['recipient_banking_instruction']['account_holder_name'] = $accountHolderName;
        $instructionData['recipient_banking_instruction']['international_bank_account_number'] = $iban;
        $instructionData['recipient_banking_instruction']['bank_identifier_code'] = $bankIdentifierCode;

        $meta_data = [
            'reference_number' => $referenceNumber,
            'instruction_type' => self::PAY_UPON_INVOICE,
            'payment_due_date' => $paymentDueDate,
            'bank_name' => $bankName,
            'account_holder_name' => $accountHolderName,
            'international_bank_account_number' => $iban,
            'bank_identifier_code' => $bankIdentifierCode,
            '_payment_instruction_result' => $instructionData,
            '_transaction_id' => $sale_id,
        ];

        foreach ($meta_data as $key => $value) {
            $order->add_meta_data($key, $value);
        }
        $order->save();
    }

    /**
     * Check if the customer's address needs to be updated.
     *
     * @return bool
     */
    private function shouldUpdateAddress()
    {
        return !empty($this->data->get_payment()->payer->payer_info->billing_address->line1);
    }

    /**
     * Update the Order billing address
     */
    private function updateBillingAddress()
    {
        $payment = $this->data->get_payment();
        $order = $this->data->get_order();
        $billingAddress = [
            'first_name' => $payment->payer->payer_info->first_name,
            'last_name' => $payment->payer->payer_info->last_name,
            'address_1' => $payment->payer->payer_info->billing_address->line1,
            'address_2' => $payment->payer->payer_info->billing_address->line2,
            'city' => $payment->payer->payer_info->billing_address->city,
            'state' => $payment->payer->payer_info->billing_address->state,
            'postcode' => $payment->payer->payer_info->billing_address->postal_code,
            'country' => $payment->payer->payer_info->billing_address->country_code,
        ];
        $order->set_address($billingAddress, $type = 'billing');
    }
}
