<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico_Connect_Model_Ingenico_GlobalCollect_Wx_DataRecord as DataRecord;
use Ingenico_Connect_Model_Cron_FetchWxFiles_ProcessorInterface as ProcessorInterface;

/**
 * Class Ingenico_Connect_Model_Cron_FetchWxFiles_Processor
 */
class Ingenico_Connect_Model_Cron_FetchWxFiles_Processor implements ProcessorInterface
{
    /**
     * @var Ingenico_Connect_Model_WxTransfer_ClientInterface
     */
    private $wxClient;

    /**
     * @var Ingenico_Connect_Model_Cron_FetchWxFiles_Logger
     */
    private $logger;

    /**
     * @var Ingenico_Connect_Model_Ingenico_GlobalCollect_StatusBuilder
     */
    private $statusBuilder;

    /**
     * @var Ingenico_Connect_Model_Cron_FetchWxFiles_StatusUpdateResolverInterface
     */
    private $statusUpdateResolver;

    /**
     * Ingenico_Connect_Model_Cron_FetchWxFiles_Processor constructor.
     *
     * @param array $args   Used by unit test
     */
    public function __construct($args = array())
    {
        if (isset($args['wxClient'])) {
            $this->wxClient = $args['wxClient'];
        } else {
            $this->wxClient = \Mage::getSingleton('ingenico_connect/wxTransfer_client');
        }

        if (isset($args['logger'])) {
            $this->logger = $args['logger'];
        } else {
            $this->logger = \Mage::getSingleton('ingenico_connect/cron_fetchWxFiles_logger');
        }

        if (isset($args['statusBuilder'])) {
            $this->statusBuilder = $args['statusBuilder'];
        } else {
            $this->statusBuilder = \Mage::getSingleton('ingenico_connect/ingenico_globalCollect_statusBuilder');
        }

        if (isset($args['statusUpdateResolver'])) {
            $this->statusUpdateResolver = $args['statusUpdateResolver'];
        } else {
            $this->statusUpdateResolver = \Mage::getSingleton('ingenico_connect/cron_fetchWxFiles_statusUpdateResolver');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process($scopeId, $date = 'yesterday')
    {
        // get xml object for scope
        try {
            $responseXml = $this->wxClient->loadDailyWx($date, $scopeId);
        } catch (\Exception $exception) {
            $this->logger->addError($exception->getMessage());
            return;
        }

        if (!$responseXml || $responseXml->getElementsByTagName('NumberOfRecords')->item(0)->nodeValue === 0) {
            // No Data to process
            $this->logger->addInfo('No file or entries found, aborting');
            return;
        }

        $transactionEntries = $responseXml->getElementsByTagName('DataRecord');
        $statusList = $this->emulatePaymentResponse($transactionEntries);
        if (!empty($statusList)) {
            $orderIds = implode(', ', array_keys($statusList));
            $this->logger->addInfo("Found informations about the following orders: {$orderIds}");
            $updatedOrders = $this->statusUpdateResolver->resolveBatch($statusList);
            if (!empty($updatedOrders)) {
                $updated = implode(', ', $updatedOrders);
                $this->logger->addInfo("Successfully updated the following orders: {$updated}");
            } else {
                $this->logger->addInfo('No update performed.');
            }
        } else {
            $this->logger->addInfo('No relevant entries.');
        }
    }

    /**
     * @param $transactionEntries
     * @return AbstractOrderStatus[] with Magento order IncrementIds as keys
     */
    private function emulatePaymentResponse($transactionEntries)
    {
        $statusObjects = array();
        /** @var \DOMElement $dataRecord */
        foreach ($transactionEntries as $dataRecord) {
            $record = DataRecord::fromDomElement($dataRecord);

            if ($record->getPaymentData()->getRecordcategory() === 'X') {
                // Recordcategory X means that no actual influence on the amounts is due yet
                continue;
            }

            try {
                $emulatedResponse = $this->statusBuilder->create($record);
                if ($emulatedResponse) {
                    $statusObjects[$record->getPaymentData()->getAdditionalReference()] = $emulatedResponse;
                }
            } catch (\Exception $exception) {
                $this->logger->addError($exception->getMessage());
            }
        }

        return $statusObjects;
    }
}
