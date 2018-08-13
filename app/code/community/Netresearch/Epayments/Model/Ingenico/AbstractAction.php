<?php
/**
 * Netresearch_Epayments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
    protected function postProcess(
        Mage_Sales_Model_Order_Payment $payment,
        $response
    )
    {
        $payment->setTransactionId($response->id);
        $this->statusResponseManager->set($payment, $response->id, $response);
    }
}
