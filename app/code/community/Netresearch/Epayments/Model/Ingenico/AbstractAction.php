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
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @license   https://opensource.org/licenses/MIT
 * @link      http://www.netresearch.de/
 */

class Netresearch_Epayments_Model_Ingenico_AbstractAction
    implements Netresearch_Epayments_Model_Ingenico_ActionInterface
{
    /**
     * @var Netresearch_Epayments_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * Netresearch_Epayments_Model_Ingenico_AbstractAction constructor.
     */
    public function __construct()
    {
        /** @var Netresearch_Epayments_Model_StatusResponseManager statusResponseManager */
        $this->statusResponseManager = Mage::getModel('netresearch_epayments/statusResponseManager');
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
