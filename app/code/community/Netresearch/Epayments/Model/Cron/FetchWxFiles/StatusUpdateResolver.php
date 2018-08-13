<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_StatusFactory as StatusFactory;
use Netresearch_Epayments_Model_Cron_FetchWxFiles_StatusUpdateResolverInterface as StatusUpdateResolverInterface;

class Netresearch_Epayments_Model_Cron_FetchWxFiles_StatusUpdateResolver implements StatusUpdateResolverInterface
{
    /**
     * @var StatusFactory
     */
    private $statusFactory;
    /**
     * @var Netresearch_Epayments_Model_Resource_Order_Collection
     */
    private $orderCollection;

    /** @var Netresearch_Epayments_Model_Cron_FetchWxFiles_Logger */
    private $logger;

    /**
     * Netresearch_Epayments_Model_Cron_FetchWxFiles_StatusUpdateResolver constructor.
     *
     * @param array $args
     */
    public function __construct($args = array())
    {
        if (isset($args['statusFactory'])) {
            $this->statusFactory = $args['statusFactory'];
        } else {
            $this->statusFactory = \Mage::getSingleton('netresearch_epayments/ingenico_statusFactory');
        }
        if (isset($args['orderCollection'])) {
            $this->orderCollection = $args['orderCollection'];
        } else {
            $this->orderCollection = \Mage::getModel('sales/order')->getCollection();
        }
        if (isset($args['logger'])) {
            $this->logger = $args['logger'];
        } else {
            $this->logger = \Mage::getSingleton('netresearch_epayments/cron_fetchWxFiles_logger');
        }
    }

    /**
     * @param AbstractOrderStatus[] $statusList
     */
    public function resolveBatch($statusList)
    {
        $updatedOrders = array();
        $this->orderCollection->addFieldToFilter('increment_id', array('in' => array_keys($statusList)));

        /** @var Mage_Sales_Model_Order $order */
        foreach ($this->orderCollection->getItems() as $order) {
            try {
                $ingenicoOrderStatus = $statusList[$order->getIncrementId()];
                $status = $this->statusFactory->create($ingenicoOrderStatus);
                $status->apply($order);
                $updatedOrders[$order->getEntityId()] = $order->getIncrementId();
            } catch (\Exception $e) {
                $message = sprintf("Error occured for order %s: %s", $order->getIncrementId(), $e->getMessage());
                $this->logger->addError($message);
            }
        }
        $this->orderCollection->save();
        return $updatedOrders;
    }
}
