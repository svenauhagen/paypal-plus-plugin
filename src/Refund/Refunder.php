<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Refund;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ErrorData\ApiErrorDataExtractor;
use WCPayPalPlus\Order\OrderStatuses;

/**
 * Class Refunder
 *
 * @package WCPayPalPlus\Refund
 */
class Refunder
{
    /**
     * RefundData object.
     *
     * @var ApiContext
     */
    private $context;

    /**
     * PayPal Api Context object.
     *
     * @var RefundData
     */
    private $refund_data;

    /**
     * @var OrderStatuses
     */
    private $orderStatuses;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ApiErrorDataExtractor
     */
    private $apiErrorDataExtractor;

    /**
     * WCRefund constructor.
     * @param RefundData $refund_data
     * @param ApiContext $context
     * @param OrderStatuses $orderStatuses
     * @param Logger $logger
     * @param ApiErrorDataExtractor $apiErrorDataExtractor
     */
    public function __construct(
        RefundData $refund_data,
        ApiContext $context,
        OrderStatuses $orderStatuses,
        Logger $logger,
        ApiErrorDataExtractor $apiErrorDataExtractor
    ) {

        $this->context = $context;
        $this->refund_data = $refund_data;
        $this->orderStatuses = $orderStatuses;
        $this->logger = $logger;
        $this->apiErrorDataExtractor = $apiErrorDataExtractor;
    }

    /**
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $sale = $this->refund_data->get_sale();
        $refund = $this->refund_data->get_refund();

        try {
            $refundedSale = $sale->refundSale($refund, $this->context);
            $isOrderCompleted = $this->orderStatuses->orderStatusIs(
                $refundedSale->state,
                OrderStatuses::ORDER_STATUS_COMPLETED
            );

            $isOrderCompleted and $this
                ->refund_data
                ->get_success_handler($refundedSale->getId())
                ->execute();
        } catch (PayPalConnectionException $exc) {
            $errorData = $this->apiErrorDataExtractor->extractByException($exc);
            $this->logger->error($errorData);
            return false;
        }

        return true;
    }
}
