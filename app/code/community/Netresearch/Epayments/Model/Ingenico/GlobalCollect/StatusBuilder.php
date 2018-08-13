<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord as DataRecord;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_StatusMapper as StatusMapper;

class Netresearch_Epayments_Model_Ingenico_GlobalCollect_StatusBuilder
{
    /**
     * @var \Netresearch_Epayments_Model_Ingenico_GlobalCollect_OrderStatusFactory
     */
    private $orderStatusFactory;

    /**
     * Netresearch_Epayments_Model_Ingenico_GlobalCollect_StatusBuilder constructor.
     *
     * @param array $args
     */
    public function __construct($args = array())
    {
        if (isset($args['orderStatusFactory'])) {
            $this->orderStatusFactory = $args['orderStatusFactory'];
        } else {
            $this->orderStatusFactory = \Mage::getSingleton(
                'netresearch_epayments/ingenico_globalCollect_orderStatusFactory'
            );
        }
    }

    /**
     * @param DataRecord $dataRecord
     * @return AbstractOrderStatus|false
     * @throws Mage_Core_Exception
     */
    public function create(DataRecord $dataRecord)
    {
        /**
         * some statuses are used for multiple use cases (refund vs capture),
         * we therefore have to check some other things too.
         */
        $possibleStatuses = StatusMapper::getConnectStatus(
            $dataRecord->getPaymentData()->getPaymentStatus(),
            $dataRecord->getPaymentData()->getPaymentGroupId()
        );

        if (empty($possibleStatuses)) {
            // no applicable status found
            return false;
        } elseif (count($possibleStatuses) === 1) {
            $definiteStatus = array_shift($possibleStatuses);
            return $this->orderStatusFactory->create($definiteStatus, $dataRecord);
        } else {
            // multiple possible statuses - need to consult record type/category
            $statuses = implode(', ', $possibleStatuses);
            $message = "Got multiple possible statuses, handling not implemented. Statuses: {$statuses}";
            \Mage::throwException($message);
        }
    }
}
