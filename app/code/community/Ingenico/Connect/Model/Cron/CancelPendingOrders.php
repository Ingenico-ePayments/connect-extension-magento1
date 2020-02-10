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

class Ingenico_Connect_Model_Cron_CancelPendingOrders
{
    protected $logfile = 'automatic_cancellation.log';

    public function __construct()
    {
        Ingenico_Connect_Model_Autoloader::register();
    }

    /**
     * Cancel pending orders, which are older then number days (look at admin Ingenico configuration).
     * Called via cron each day at 5a.m.
     */
    public function execute()
    {
        /** @var Ingenico_Connect_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getResourceModel('ingenico_connect/order_collection');
        $cancellationPeriod = (int) Mage::getSingleton('ingenico_connect/config')->getPendingOrdersCancellationPeriod();
        if ($cancellationPeriod < 1) {
            Mage::log('Cancellation period for stale orders should at least be 1 day', Zend_Log::WARN, $this->logfile);
            return;
        }

        $orderCollection->addPendingStatusFilter()->addCreatedAtFilter($cancellationPeriod);
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
    }
}
