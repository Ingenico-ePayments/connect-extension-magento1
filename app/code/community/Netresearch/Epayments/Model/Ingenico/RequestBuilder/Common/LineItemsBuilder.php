<?php

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_LineItemsBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_LineItemsBuilder
{
    /**
     * @var Netresearch_Epayments_Helper_Data
     */
    protected $ePaymentsHelper;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_LineItemsBuilder constructor.
     */
    public function __construct()
    {
        $this->ePaymentsHelper = Mage::helper('netresearch_epayments');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $lineItems = array();
        /** @var Mage_Sales_Model_Order_Item[] $orderItems */
        $orderItems = $order->getAllVisibleItems();

        foreach ($orderItems as $item) {
            if ($item->getParentItem()) {
                // skip item if it is only a modifier
                continue;
            }

            $lineItem = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItem();

            $itemAmountOfMoney = new \Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney();
            $itemAmountOfMoney->amount = $this->_formatAmount($item->getBaseRowTotalInclTax());
            $itemAmountOfMoney->currencyCode = $order->getBaseCurrencyCode();
            $lineItem->amountOfMoney = $itemAmountOfMoney;

            $lineItemInvoiceData = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItemInvoiceData();
            $lineItemInvoiceData->nrOfItems = $item->getQtyOrdered();
            $lineItemInvoiceData->description = $item->getName();
            $lineItemInvoiceData->pricePerItem = $this->_formatAmount($item->getBasePriceInclTax());
            $lineItem->invoiceData = $lineItemInvoiceData;

            $orderLineDetails = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderLineDetails();
            $orderLineDetails->discountAmount = $this->_formatAmount($item->getBaseDiscountAmount());
            $orderLineDetails->lineAmountTotal = $this->_formatAmount($item->getBaseRowTotalInclTax());
            $orderLineDetails->productCode = substr($item->getSku(), 0, 12);
            $orderLineDetails->productPrice = $this->_formatAmount($item->getBasePriceInclTax());
            $orderLineDetails->productType = $item->getProductType();
            $orderLineDetails->quantity = $item->getQtyOrdered();
            $orderLineDetails->taxAmount = $this->_formatAmount(
                $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount()
            );
            $orderLineDetails->unit = '';
            $lineItem->orderLineDetails = $orderLineDetails;

            $lineItems[] = $lineItem;
        }

        /**
         * Add shipping amount as fake line item
         */
        if ($order->getBaseShippingAmount() != 0) {
            $lineItems[] = $this->getShippingItem($order);
        }

        /**
         * Add discounts as fake line item
         */
        if ($order->getBaseDiscountAmount() != 0) {
            $lineItems[] = $this->getDiscountsItem($order);
        }

        return $lineItems;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItem
     */
    protected function getDiscountsItem(Mage_Sales_Model_Order $order)
    {
        $formatAmountDisc = $this->_formatAmount($order->getBaseDiscountAmount());
        $discountAmount = new \Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney();
        $discountAmount->amount = $formatAmountDisc;
        $discountAmount->currencyCode = $order->getBaseCurrencyCode();

        $discountDetails = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderLineDetails();
        $discountDetails->productName = 'Discount';
        $discountDetails->quantity = 1;
        $discountDetails->lineAmountTotal = $formatAmountDisc;
        $discountDetails->productPrice = $formatAmountDisc;
        $discountDetails->taxAmount = -$this->_formatAmount($order->getBaseHiddenTaxAmount());
        $discountInvoice = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItemInvoiceData();
        $description = $order->getDiscountDescription() ?: 'Discount';
        $discountInvoice->description = $description;
        $discountInvoice->nrOfItems = 1;
        $discountInvoice->pricePerItem = $formatAmountDisc;

        $discountItem = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItem();
        $discountItem->amountOfMoney = $discountAmount;
        $discountItem->orderLineDetails = $discountDetails;
        $discountItem->invoiceData = $discountInvoice;

        return $discountItem;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItem
     */
    protected function getShippingItem(Mage_Sales_Model_Order $order)
    {
        $formatAmountShip = $this->_formatAmount($order->getBaseShippingInclTax());
        $shippingAmount = new \Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney();
        $shippingAmount->amount = $formatAmountShip;
        $shippingAmount->currencyCode = $order->getBaseCurrencyCode();

        $shippingDetails = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderLineDetails();
        $shippingDetails->productName = 'Shipping';
        $shippingDetails->quantity = 1;
        $shippingDetails->taxAmount = $this->_formatAmount($order->getBaseShippingTaxAmount());
        $shippingDetails->lineAmountTotal = $formatAmountShip;
        $shippingDetails->productPrice = $formatAmountShip;

        $shippingInvoice = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItemInvoiceData();
        $shippingInvoice->description = 'Shipping';
        $shippingInvoice->nrOfItems = 1;
        $shippingInvoice->pricePerItem = $formatAmountShip;

        $shippingItem = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\LineItem();
        $shippingItem->amountOfMoney = $shippingAmount;
        $shippingItem->orderLineDetails = $shippingDetails;
        $shippingItem->invoiceData = $shippingInvoice;

        return $shippingItem;
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
