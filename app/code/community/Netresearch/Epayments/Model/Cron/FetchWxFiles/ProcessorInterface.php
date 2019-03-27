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
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @license   https://opensource.org/licenses/MIT
 * @link      http://www.netresearch.de/
 */

interface Netresearch_Epayments_Model_Cron_FetchWxFiles_ProcessorInterface
{
    /**
     * Apply .wr file to order
     *
     * @param $storeId
     * @param $date
     */
    public function process($storeId, $date = 'yesterday');
}
