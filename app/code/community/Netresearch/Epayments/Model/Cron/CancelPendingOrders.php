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

class Netresearch_Epayments_Model_Cron_CancelPendingOrders
{
    protected $logfile = 'automatic_cancellation.log';

    public function __construct()
    {
        Netresearch_Epayments_Model_Autoloader::register();
    }

    /**
     * Cancel pending orders, which are older then number days (look at admin Ingenico configuration).
     * Called via cron each day at 5a.m.
     *
     * @return Netresearch_Epayments_Model_Cron
     */
    public function execute()
    {
        /** @var Netresearch_Epayments_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getResourceModel('netresearch_epayments/order_collection');
        $cancelationPeriod = Mage::getSingleton('netresearch_epayments/config')->getPendingOrdersCancellationPeriod();
        $orderCollection->addPendingStatusFilter()->addCreatedAtFilter($cancelationPeriod);
        /** @var Mage_Sales_Model_Order $order */
        foreach ($orderCollection as $order) {
            Mage::log(
                'Attempting to automatically cancel order ' . $order->getIncrementId(),
                Zend_Log::INFO,
                $this->logfile
            );

            try {
                // trigger the online invalidation of the payment through the payment object
                $order->getPayment()->cancel();
            } catch (\Ingenico\Connect\Sdk\ResponseException $exception) {
                Mage::log(
                    'Could not cancel order on platform due to error: ' . $exception->getMessage(),
                    Zend_Log::DEBUG,
                    $this->logfile
                );
            }

            try {
                // trigger the actual Magento order cancellation
                $order->registerCancellation('Automatic order cancellation');
            } catch (Mage_Core_Exception $exception) {
                Mage::log(
                    'Could not cancel order automatically: ' . $exception->getMessage(),
                    Zend_Log::ERR,
                    $this->logfile
                );
            }
        }

        $orderCollection->save();

        return $this;
    }
}
