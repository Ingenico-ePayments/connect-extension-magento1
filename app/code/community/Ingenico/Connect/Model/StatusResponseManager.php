<?php
/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  ePayments
 * @package   Ingenico_Connect
 */

use \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Domain\Errors\Definitions\APIError;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment as IngenicoPayment;
use \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult as IngenicoRefund;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Ingenico_Connect_Model_StatusResponseManager
 */
class Ingenico_Connect_Model_StatusResponseManager
{
    /**
     * Retrieve last PaymentResponse object stored in transaction additionalInformation. It contains canonical
     * information about a payment, such as isCancellable or isRefundable and isAuthorized values.
     *
     * @param Mage_Payment_Model_Info $payment
     * @param $transactionId
     * @return AbstractOrderStatus|IngenicoRefund|IngenicoPayment
     */
    public function get(Mage_Payment_Model_Info $payment, $transactionId)
    {
        $orderStatus = false;
        /** @var Mage_Sales_Model_Order_Payment | Mage_Payment_Model_Info $payment */
        $transaction = $payment->getTransaction($transactionId);
        if ($transaction) {
            $classPath = $transaction->getAdditionalInformation(HostedCheckout::TRANSACTION_CLASS_KEY);
            /** @var AbstractOrderStatus $orderStatus */
            $orderStatus = new $classPath();
            $orderStatus = $orderStatus->fromJson(
                $transaction->getAdditionalInformation(HostedCheckout::TRANSACTION_INFO_KEY)
            );
        } else {
            // If transaction does not (yet) exist
            $classPath = $payment->getTransactionAdditionalInfo(HostedCheckout::TRANSACTION_CLASS_KEY);
            if ($classPath) {
                /** @var AbstractOrderStatus $orderStatus */
                $orderStatus = new $classPath();
                $orderStatus = $orderStatus->fromJson(
                    $payment->getTransactionAdditionalInfo(HostedCheckout::TRANSACTION_INFO_KEY)
                );
            }
        }

        return $orderStatus;
    }

    /**
     * Update the PaymentResponse object stored in a transaction.
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $transactionId
     * @param AbstractOrderStatus $orderStatus
     * @throws Mage_Core_Exception
     */
    public function set(
        Mage_Sales_Model_Order_Payment $payment,
        $transactionId,
        AbstractOrderStatus $orderStatus
    ) {
        if (!isset($orderStatus->status) || !isset($orderStatus->statusOutput)) {
            Mage::throwException('Unknown payment status');
        }

        $transaction = $payment->getTransaction($transactionId);
        $objectClassName = get_class($orderStatus);
        $objectJson = $orderStatus->toJson();

        if ($transaction) {
            $transaction->setAdditionalInformation(HostedCheckout::TRANSACTION_CLASS_KEY, $objectClassName);
            $transaction->setAdditionalInformation(HostedCheckout::TRANSACTION_INFO_KEY, $objectJson);
            $transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                $this->getVisibleInfo($orderStatus)
            );
            $payment->getOrder()->addRelatedObject($transaction);
        } else {
            /**
             * If transaction does not (yet) exist.
             * setTransactionAdditionalInfo's doc block type hints are broken, but passing (string, array) works.
             */
            $payment->setTransactionAdditionalInfo(HostedCheckout::TRANSACTION_CLASS_KEY, $objectClassName);
            $payment->setTransactionAdditionalInfo(HostedCheckout::TRANSACTION_INFO_KEY, $objectJson);
            $payment->setTransactionAdditionalInfo(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                $this->getVisibleInfo($orderStatus)
            );
        }
    }

    /**
     * @param AbstractOrderStatus|IngenicoPayment|IngenicoRefund $orderStatus
     * @return array
     */
    protected function getVisibleInfo(AbstractOrderStatus $orderStatus)
    {
        $visibleInfo = array();
        $visibleInfo['status'] = $orderStatus->status;

        $visibleInfo = array_merge(
            $visibleInfo,
            get_object_vars($orderStatus->statusOutput)
        );

        /**
         * Read errors array
         */
        if (isset($visibleInfo['errors'])) {
            $errors = array();
            /** @var APIError $error */
            foreach ($visibleInfo['errors'] as $error) {
                $errors[] = $error->id;
            }

            $visibleInfo['errors'] = implode(', ', $errors);
        }

        /**
         * Translate booleans into human-readable format
         */
        array_walk(
            $visibleInfo,
            function (&$info) {
                if (is_bool($info)) {
                    $info = $info ? __('Yes') : __('No');
                }
            }
        );

        return array_filter($visibleInfo);
    }
}
