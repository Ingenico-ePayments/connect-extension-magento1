<?php

/**
 * Interface Ingenico_Connect_Model_Ingenico_Webhooks_EventDataResolverInterface
 */
interface Ingenico_Connect_Model_Ingenico_Webhooks_EventDataResolverInterface
{
    /**
     * @param \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event
     * @return \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus
     * @throws RuntimeException if event does not match certain resolver
     */
    public function getResponse(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event);

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event
     * @return string
     * @throws RuntimeException if event does not match certain resolver or merchant order id is missing
     * @throws \InvalidArgumentException if event reference does not originate from this system
     */
    public function getMerchantReference(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event);
}
