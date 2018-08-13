<?php

interface Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface
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
     */
    public function getMerchantReference(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event);
}
