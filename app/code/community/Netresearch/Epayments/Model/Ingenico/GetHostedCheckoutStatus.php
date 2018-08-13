<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse;

/**
 * Class Netresearch_Epayments_Model_Ingenico_HostedCheckoutPaymentStatus
 *
 * Uses to update Magento Order state/status after payment creation via HostedCheckout Payment method.
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__hostedcheckouts__hostedCheckoutId__get
 */
class Netresearch_Epayments_Model_Ingenico_GetHostedCheckoutStatus
    implements Netresearch_Epayments_Model_Ingenico_ActionInterface
{
    const PAYMENT_CREATED                    = 'PAYMENT_CREATED';
    const IN_PROGRESS                        = 'IN_PROGRESS';
    const RETURNMAC                          = 'RETURNMAC';
    const PAYMENT_STATUS_CATEGORY_SUCCESSFUL = 'SUCCESSFUL';
    const PAYMENT_STATUS_CATEGORY_UNKNOWN    = 'STATUS_UNKNOWN';
    const PAYMENT_STATUS_CATEGORY_REJECTED   = 'REJECTED';
    const PAYMENT_OUTPUT_SHOW_INSTRUCTIONS   = 'SHOW_INSTRUCTIONS';

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        $statusResponse = $this->getStatusResponse($order);

        $this->checkPaymentStatusCategory($statusResponse, $order);

        if ($statusResponse->status === self::PAYMENT_CREATED) {
            $this->checkReturnMac($order);
            $token = $statusResponse->createdPaymentOutput->tokens;
            $this->processOrder($order, $statusResponse);
            $customerId = $order->getCustomerId();
            if ($customerId && $token !== null) {
                $this->processCustomerToken($customerId, $token);
            }
            // save the order before sendNewOrderEmail is called due to possible unexpected behaviour in CE1.8
            $order->save();
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            $order->save();
        }
    }

    /**
     * @param $customerId
     * @param $tokenString
     * @throws Exception
     */
    protected function processCustomerToken($customerId, $tokenString)
    {
        Mage::getModel('netresearch_epayments/tokenService')->assignToken($customerId, $tokenString);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return GetHostedCheckoutResponse
     */
    protected function getStatusResponse(Mage_Sales_Model_Order $order)
    {
        $hostedCheckoutId = $order->getPayment()->getAdditionalInformation(HostedCheckout::HOSTED_CHECKOUT_ID_KEY);

        /** @var Netresearch_Epayments_Model_ConfigInterface $ingenicoConfig */
        $ingenicoConfig = Mage::getSingleton('netresearch_epayments/config');
        /** @var Netresearch_Epayments_Model_Ingenico_Api_ClientInterface $ingenicoClient */
        $ingenicoClient = Mage::getModel('netresearch_epayments/ingenico_client');

        /** @var GetHostedCheckoutResponse $statusResponse */
        $statusResponse = $ingenicoClient->getIngenicoClient($order->getStoreId())
            ->merchant($ingenicoConfig->getMerchantId($order->getStoreId()))
            ->hostedcheckouts()
            ->get($hostedCheckoutId);

        return $statusResponse;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param GetHostedCheckoutResponse $statusResponse
     * @return Mage_Sales_Model_Order
     * @throws Mage_Core_Exception
     */
    protected function processOrder(
        Mage_Sales_Model_Order $order,
        GetHostedCheckoutResponse $statusResponse
    )
    {
        $paymentId = $statusResponse->createdPaymentOutput->payment->id;
        $paymentStatus = $statusResponse->createdPaymentOutput->payment->status;
        $paymentStatusCode = $statusResponse->createdPaymentOutput->payment->statusOutput->statusCode;

        $payment = $order->getPayment();
        if (isset($statusResponse->createdPaymentOutput->displayedData)
            && $statusResponse->createdPaymentOutput->displayedData->displayedDataType
            == self::PAYMENT_OUTPUT_SHOW_INSTRUCTIONS
        ) {
            $payment->setAdditionalInformation(
                HostedCheckout::PAYMENT_SHOW_DATA_KEY,
                $statusResponse->createdPaymentOutput->displayedData->toJson()
            );
        }

        /** @var Netresearch_Epayments_Model_Ingenico_StatusFactory $statusFactory */
        $statusFactory = Mage::getSingleton('netresearch_epayments/ingenico_statusFactory');
        $status = $statusFactory->create($statusResponse->createdPaymentOutput->payment);
        $status->apply($order);

        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY, $paymentId);
        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY, $paymentStatus);
        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_STATUS_CODE_KEY, $paymentStatusCode);
        
        $info = Mage::helper('netresearch_epayments')->getPaymentStatusInfo($paymentStatus);
        if ($info) {
            $order->addStatusHistoryComment(
                sprintf(
                    "%s (payment status: '%s', payment status code: '%s')",
                    $info,
                    $paymentStatus,
                    $paymentStatusCode
                )
            );
        }
        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception
     */
    protected function checkReturnMac(Mage_Sales_Model_Order $order)
    {
        $orderReturnmac = $order->getPayment()->getAdditionalInformation(HostedCheckout::RETURNMAC_KEY);
        $returnmac = Mage::app()->getRequest()->get(self::RETURNMAC);

        if ($returnmac != $orderReturnmac) {
            Mage::throwException(Mage::helper('netresearch_epayments')->__('RETURNMAC doesn\'t match.'));
        }
    }

    /**
     * Handles rejected or faulty orders by checking paymentStatusCategory, will escalate through exception
     *
     * @param GetHostedCheckoutResponse $statusResponse
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception if order is faulty or rejected by platform
     *
     */
    protected function checkPaymentStatusCategory(
        GetHostedCheckoutResponse $statusResponse,
        Mage_Sales_Model_Order $order
    )
    {
        // handle faulty responses or rejected/cancelled orders
        $createdPaymentOutput = $statusResponse->createdPaymentOutput;
        if (!$createdPaymentOutput
            || $createdPaymentOutput->paymentStatusCategory == self::PAYMENT_STATUS_CATEGORY_REJECTED
        ) {
            if ($createdPaymentOutput) {
                $status = $createdPaymentOutput->payment->status;
            } else {
                $status = $statusResponse->status;
            }
            /** @var Netresearch_Epayments_Helper_Data $helper */
            $helper = Mage::helper('netresearch_epayments');
            $info = $helper->getPaymentStatusInfo($status);
            if ($info) {
                $msg = $helper->__('Payment error:') . ' ' . $info;
            } else {
                $msg = $helper->__('Payment status is rejected or unknown');
                $info = $msg;
            }

            $order->registerCancellation("<b>Payment error, status</b><br />{$statusResponse->status}: $info");
            $order->save();
            Mage::throwException($msg);
        }
    }
}
