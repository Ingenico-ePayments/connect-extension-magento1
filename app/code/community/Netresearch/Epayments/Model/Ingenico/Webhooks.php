<?php

/**
 * Class Netresearch_Epayments_Model_Ingenico_Webhooks
 */
class Netresearch_Epayments_Model_Ingenico_Webhooks
{
    /** @var Netresearch_Epayments_Model_Ingenico_Webhooks_HelperAdapter */
    private $webhooksHelperAdapter;

    /** @var Netresearch_Epayments_Model_Ingenico_StatusFactory */
    private $statusFactory;

    /** @var Mage_Sales_Model_Order */
    private $orderModel;

    /**
     * Netresearch_Epayments_Model_Ingenico_Webhooks constructor.
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        if (!isset($args['webhooksHelperAdapter'])) {
            $args['webhooksHelperAdapter'] = Mage::getModel('netresearch_epayments/ingenico_webhooks_helperAdapter');
        }
        if (!isset($args['statusFactory'])) {
            $args['statusFactory'] = Mage::getModel('netresearch_epayments/ingenico_statusFactory');
        }
        if (!isset($args['orderModel'])) {
            $args['orderModel'] = Mage::getModel('sales/order');
        }

        if (!$args['webhooksHelperAdapter'] instanceof Netresearch_Epayments_Model_Ingenico_Webhooks_HelperAdapter) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" must be instance of %s class.',
                    'webhooksHelperAdapter',
                    'Netresearch_Epayments_Model_Ingenico_Webhooks_HelperAdapter'
                )
            );
        }

        if (!$args['statusFactory'] instanceof Netresearch_Epayments_Model_Ingenico_StatusFactory) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" must be instance of %s class.',
                    'statusFactory',
                    'Netresearch_Epayments_Model_Ingenico_StatusFactory'
                )
            );
        }

        if (!$args['orderModel'] instanceof Mage_Sales_Model_Order) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" must be instance of %s class.',
                    'orderModel',
                    'Mage_Sales_Model_Order'
                )
            );
        }

        $this->webhooksHelperAdapter = $args['webhooksHelperAdapter'];
        $this->statusFactory = $args['statusFactory'];
        $this->orderModel = $args['orderModel'];
    }

    /**
     * @param Netresearch_Epayments_Model_Ingenico_Webhooks_RequestContext $requestContext
     * @param Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface $eventDataResolver
     * @throws Exception
     */
    public function handle(
        Netresearch_Epayments_Model_Ingenico_Webhooks_RequestContext $requestContext,
        Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface $eventDataResolver
    )
    {
        $event = $this->webhooksHelperAdapter->unmarshal($requestContext->getBody(), $requestContext->getHeaders());

        if ($this->checkEndpointTest($event)) {
            return;
        }
        $eventResponse = $eventDataResolver->getResponse($event);
        $orderIncrementId = $eventDataResolver->getMerchantReference($event);

        $this->orderModel->unsetData(); // prevent using of previous order data
        $this->orderModel->loadByIncrementId($orderIncrementId);

        if (!$this->orderModel->getId() || $this->orderModel->getIncrementId() != $orderIncrementId) {
            throw new RuntimeException('System can not load order mentioned in the Event.');
        }

        $status = $this->statusFactory->create($eventResponse);
        $status->apply($this->orderModel);

        $this->orderModel->save();
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event
     * @return bool
     */
    protected function checkEndpointTest(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event)
    {
        return strpos($event->id, "TEST") === 0;
    }
}
