<?php

use Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface as EventDataResolverInterface;
use Netresearch_Epayments_Model_Ingenico_Webhooks_RequestContext as RequestContext;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Webhooks
 */
class Netresearch_Epayments_Model_Ingenico_Webhooks
{
    /**
     * @var Netresearch_Epayments_Model_Ingenico_Webhooks_HelperAdapter
     */
    private $webhooksHelperAdapter;

    /**
     * Netresearch_Epayments_Model_Ingenico_Webhooks constructor.
     *
     * @param mixed[] $args
     */
    public function __construct(array $args = array())
    {
        if (!isset($args['webhooksHelperAdapter'])) {
            $args['webhooksHelperAdapter'] = Mage::getModel('netresearch_epayments/ingenico_webhooks_helperAdapter');
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

        $this->webhooksHelperAdapter = $args['webhooksHelperAdapter'];
    }

    /**
     * @param RequestContext $requestContext
     * @param EventDataResolverInterface $eventDataResolver
     * @throws Exception
     */
    public function handle(RequestContext $requestContext, EventDataResolverInterface $eventDataResolver)
    {
        $event = $this->webhooksHelperAdapter->unmarshal($requestContext->getBody(), $requestContext->getHeaders());

        if ($this->checkEndpointTest($event)) {
            return;
        }

        Mage::log(
            "Received incoming webhook event with id {$event->id}:\n
            {$event->toJson()}",
            Zend_Log::DEBUG,
            'ingenico_epayments.log'
        );

        try {
            $orderIncrementId = $eventDataResolver->getMerchantReference($event);
        } catch (\InvalidArgumentException $exception) {
            Mage::log(
                'Error with matching event ' . $event->id . ':' . $exception->getMessage(),
                Zend_Log::DEBUG,
                'ingenico_epayments.log'
            );

            return;
        }

        /** @var Netresearch_Epayments_Model_Event $eventModel */
        $eventModel = Mage::getSingleton('netresearch_epayments/event');
        $eventModel->setEventId($event->id);
        $eventModel->setOrderIncrementId($orderIncrementId);
        $eventModel->setPayload($event->toJson());
        $eventModel->setCreatedTimeStamp($event->created);
        $eventModel->save();
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event
     * @return bool
     */
    protected function checkEndpointTest(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event)
    {
        return strpos($event->id, 'TEST') === 0;
    }
}
