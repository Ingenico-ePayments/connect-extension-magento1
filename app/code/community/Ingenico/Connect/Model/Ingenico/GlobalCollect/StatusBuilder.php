<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico_Connect_Model_Ingenico_GlobalCollect_Wx_DataRecord as DataRecord;
use Ingenico_Connect_Model_Ingenico_GlobalCollect_StatusMapper as StatusMapper;

class Ingenico_Connect_Model_Ingenico_GlobalCollect_StatusBuilder
{
    /**
     * @var \Ingenico_Connect_Model_Ingenico_GlobalCollect_OrderStatusFactory
     */
    private $orderStatusFactory;

    /**
     * Ingenico_Connect_Model_Ingenico_GlobalCollect_StatusBuilder constructor.
     *
     * @param array $args
     */
    public function __construct($args = array())
    {
        if (isset($args['orderStatusFactory'])) {
            $this->orderStatusFactory = $args['orderStatusFactory'];
        } else {
            $this->orderStatusFactory = \Mage::getSingleton(
                'ingenico_connect/ingenico_globalCollect_orderStatusFactory'
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
        }

        if (count($possibleStatuses) !== 1) {
            // multiple possible statuses - need to consult record type/category
            $statuses = implode(', ', $possibleStatuses);
            $message = "Got multiple possible statuses, handling not implemented. Statuses: {$statuses}";
            \Mage::throwException($message);
        }

        $definiteStatus = array_shift($possibleStatuses);
        return $this->orderStatusFactory->create($definiteStatus, $dataRecord);
    }
}
