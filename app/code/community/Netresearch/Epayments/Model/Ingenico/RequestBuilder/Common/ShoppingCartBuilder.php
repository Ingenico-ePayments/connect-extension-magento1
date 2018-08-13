<?php

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_ShoppingCartBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_ShoppingCartBuilder
{
    /**
     * @var Netresearch_Epayments_Helper_Data
     */
    protected $ePaymentsHelper;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_LineItemsBuilder
     */
    protected $lineItemsBuilder;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_ShoppingCartBuilder constructor.
     */
    public function __construct()
    {
        $this->ePaymentsHelper = Mage::helper('netresearch_epayments');
        $this->lineItemsBuilder = Mage::getModel(
            'netresearch_epayments/ingenico_requestBuilder_common_lineItemsBuilder'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ShoppingCart
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $shoppingCart = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ShoppingCart();

        $shoppingCart->items = $this->lineItemsBuilder->create($order);

        return $shoppingCart;
    }

    /**
     * @param float $amount
     * @return mixed
     */
    protected function _formatAmount($amount)
    {
        return $this->ePaymentsHelper->formatIngenicoAmount($amount);
    }
}
