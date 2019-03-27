<?php
/**
 * Netresearch_Epayments
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @license   https://opensource.org/licenses/MIT
 * @link      http://www.netresearch.de/
 */

use Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent;

/**
 * Class Netresearch_Epayments_Model_Event_Processor
 */
class Netresearch_Epayments_Model_Event_Processor
{
    /**
     * @var Netresearch_Epayments_Model_Ingenico_Status_Resolver
     */
    protected $statusResolver;

    /**
     * Netresearch_Epayments_Model_Event_Processor constructor.
     */
    public function __construct()
    {
        $this->statusResolver = Mage::getSingleton('netresearch_epayments/ingenico_status_resolver');
    }

    /**
     * @param int $limit
     * @throws Exception
     */
    public function processBatch($limit = 20)
    {
        /** @var Netresearch_Epayments_Model_Resource_Event_Collection $collection */
        $collection = Mage::getModel('netresearch_epayments/event')->getCollection();
        $collection->addFieldToFilter(
            Netresearch_Epayments_Model_Event::STATUS,
            array(
                'in' => array(
                    Netresearch_Epayments_Model_Event::STATUS_NEW,
                    Netresearch_Epayments_Model_Event::STATUS_FAILED,
                ),
            )
        );
        $collection->setOrder(
            Netresearch_Epayments_Model_Event::CREATED_TIMESTAMP,
            Varien_Data_Collection_Db::SORT_ORDER_ASC
        );
        $collection->setPageSize($limit);
        $events = $collection->getItems();

        $orderIncrementIds = array_reduce(
            $events,
            /**
             * @param string[] $carry
             * @param Netresearch_Epayments_Model_Event $event
             * @return string[]
             */
            function ($carry, $event) {
                $carry[] = $event->getOrderIncrementId();

                return $carry;
            },
            array()
        );
        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getModel('sales/order')
                               ->getCollection()
                               ->addFieldToFilter('increment_id', array('in' => $orderIncrementIds));
        $orders = $orderCollection->getItems();

        foreach ($events as $event) {
            $this->processEvent($event, $orders);
        }
    }

    /**
     * @param Netresearch_Epayments_Model_Event $event
     * @param array $orders
     * @throws Exception
     */
    protected function processEvent($event, array $orders)
    {
        $webhookEvent = new WebhooksEvent();
        $webhookEvent = $webhookEvent->fromJson($event->getPayload());
        $order = $this->getOrderForEvent($orders, $event);
        $event->setStatus(Netresearch_Epayments_Model_Event::STATUS_PROCESSING);
        $event->save();
        try {
            $this->statusResolver->resolve($order, $this->extractStatusObject($webhookEvent));
            $order->setHasDataChanges(true);
            $order->save();
            $event->setStatus(Netresearch_Epayments_Model_Event::STATUS_SUCCESS);
            $event->save();
        } catch (\Exception $exception) {
            $event->setStatus(Netresearch_Epayments_Model_Event::STATUS_FAILED);
            $event->save();
        }
    }

    /**
     * @param Mage_Sales_Model_Order[] $orders
     * @param Netresearch_Epayments_Model_Event $event
     * @return Mage_Sales_Model_Order
     */
    protected function getOrderForEvent($orders, $event)
    {
        $result = array_filter(
            $orders,
            /**
             * @param Mage_Sales_Model_Order $order
             * @return bool
             */
            function ($order) use ($event) {
                return $order->getIncrementId() === $event->getOrderIncrementId();
            }
        );

        return array_shift($result);
    }

    /**
     * @param WebhooksEvent $event
     * @return \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse|\Ingenico\Connect\Sdk\Domain\Refund\RefundResponse
     */
    protected function extractStatusObject(WebhooksEvent $event)
    {
        $objectType = explode('.', $event->type)[0];
        switch ($objectType) {
            case 'payment':
                return $event->payment;
            case 'refund':
                return $event->refund;
            case 'payout':
            default:
                throw new \RuntimeException("Event type {$event->type} not supported.");
        }
    }
}
