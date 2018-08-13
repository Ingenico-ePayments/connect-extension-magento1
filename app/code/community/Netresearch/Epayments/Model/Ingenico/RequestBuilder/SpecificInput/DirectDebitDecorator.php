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
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

use Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_AbstractMethodDecorator as
    AbstractMethodDecorator;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_SepaDirectDebitDecorator
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_DirectDebitDecorator extends
    AbstractMethodDecorator
{
    /**
     * @inheritdoc
     */
    public function decorate($request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\NonSepaDirectDebitPaymentMethodSpecificInput();
        $input->paymentProductId = $this->getProductId($order);
        $input->directDebitText = $order->getIncrementId();

        $tokenize = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_TOKENIZE_KEY
        );
        $input->tokenize = $tokenize;

        $request->directDebitPaymentMethodSpecificInput = $input;

        return $request;
    }
}
