<?php
/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Ingenico_Connect
 */

interface Ingenico_Connect_Model_Cron_FetchWxFiles_ProcessorInterface
{
    /**
     * Apply .wr file to order
     *
     * @param $storeId
     * @param $date
     */
    public function process($storeId, $date = 'yesterday');
}
