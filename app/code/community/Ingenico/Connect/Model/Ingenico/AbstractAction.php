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

class Ingenico_Connect_Model_Ingenico_AbstractAction
    implements Ingenico_Connect_Model_Ingenico_ActionInterface
{
    /**
     * @var Ingenico_Connect_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * Ingenico_Connect_Model_Ingenico_AbstractAction constructor.
     */
    public function __construct()
    {
        /** @var Ingenico_Connect_Model_StatusResponseManager statusResponseManager */
        $this->statusResponseManager = Mage::getModel('ingenico_connect/statusResponseManager');
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus $response
     * @throws Mage_Core_Exception
     */
    protected function postProcess(Mage_Sales_Model_Order_Payment $payment, $response)
    {
        $payment->setTransactionId($response->id);
        $this->statusResponseManager->set($payment, $response->id, $response);
    }
}
