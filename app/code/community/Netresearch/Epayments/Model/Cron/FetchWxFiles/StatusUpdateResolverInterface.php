<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;

interface Netresearch_Epayments_Model_Cron_FetchWxFiles_StatusUpdateResolverInterface
{
    /**
     * @param AbstractOrderStatus[] $statusList
     */
    public function resolveBatch($statusList);
}