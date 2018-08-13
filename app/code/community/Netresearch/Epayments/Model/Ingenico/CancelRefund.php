<?php

/**
 * Netresearch_Epayments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 *
 *
 * Class Netresearch_Epayments_Model_Ingenico_CancelRefund
 * @see https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/refunds/cancel.html
 */
class Netresearch_Epayments_Model_Ingenico_CancelRefund
    extends Netresearch_Epayments_Model_Ingenico_AbstractAction
    implements Netresearch_Epayments_Model_Ingenico_ActionInterface
{
    /**
     * @var string[]
     */
    protected $allowedStates = array(
        Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_APPROVAL,
        Netresearch_Epayments_Model_Ingenico_StatusInterface::REFUND_REQUESTED
    );

    /**
     * @var  Netresearch_Epayments_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RetrievePayment
     */
    protected $retrievePayment;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_StatusFactory
     */
    protected $statusFactory;

    /**
     * Netresearch_Epayments_Model_Ingenico_CancelRefund constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient = Mage::getSingleton('netresearch_epayments/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        $this->retrievePayment = Mage::getSingleton('netresearch_epayments/ingenico_retrievePayment');
        $this->statusFactory = Mage::getModel('netresearch_epayments/ingenico_statusFactory');

        parent::__construct();
    }

    /**
     * Cancel the creditmemo at the Ingenico API
     * and within Magento itself.
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @throws Mage_Core_Exception
     */
    public function process(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $refundId = $creditmemo->getTransactionId();
        $payment = $order->getPayment();

        $refundResponse = $this->statusResponseManager->get($payment, $refundId);

        $isAllowedStatus = in_array(
            $refundResponse->status,
            $this->allowedStates
        );
        if (!$isAllowedStatus) {
            Mage::throwException("Cannot cancel refund with status $refundResponse->status");
        }

        // Cancel refund via Ingenico API
        $this->cancelRefund($creditmemo);

        // Retrieve current status from api because
        // cancelRefund only returns a HTTP status code
        $this->retrievePayment->process($order);

        $refundResponse = $this->statusResponseManager->get($payment, $refundId);

        $status = $this->statusFactory->create($refundResponse);

        $status->apply($order);

        $creditmemo->cancel();
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function cancelRefund(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $this->ingenicoClient->getIngenicoClient($creditmemo->getStoreId())
            ->merchant($this->ePaymentsConfig->getMerchantId($creditmemo->getStoreId()))
            ->refunds()
            ->cancel($creditmemo->getTransactionId());
    }
}
