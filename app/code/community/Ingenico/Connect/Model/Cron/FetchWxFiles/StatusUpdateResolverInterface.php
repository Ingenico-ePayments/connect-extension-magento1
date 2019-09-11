<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;

interface Ingenico_Connect_Model_Cron_FetchWxFiles_StatusUpdateResolverInterface
{
    /**
     * @param AbstractOrderStatus[] $statusList
     */
    public function resolveBatch($statusList);
}