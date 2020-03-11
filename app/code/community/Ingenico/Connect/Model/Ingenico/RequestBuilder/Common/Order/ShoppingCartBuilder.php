<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_ShoppingCartBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_ShoppingCartBuilder
{
    /**
     * @var Ingenico_Connect_Helper_Data
     */
    protected $ePaymentsHelper;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_ShoppingCart_ItemsBuilder
     */
    protected $itemsBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_ShoppingCartBuilder constructor.
     */
    public function __construct()
    {
        $this->ePaymentsHelper = Mage::helper('ingenico_connect');
        $this->itemsBuilder = Mage::getModel(
            'ingenico_connect/ingenico_requestBuilder_common_order_shoppingCart_itemsBuilder'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ShoppingCart
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $shoppingCart = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ShoppingCart();

        $shoppingCart->items = $this->itemsBuilder->create($order);

        $reorderIndicator = $this->getReorderIndicator($order);
        if ($reorderIndicator !== null) {
            $shoppingCart->reOrderIndicator = $reorderIndicator;
        }

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

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool|null
     */
    protected function getReorderIndicator(Mage_Sales_Model_Order $order)
    {
        if ($order->getCustomerIsGuest()) {
            return null;
        }
        if (!$order->getCustomerId()) {
            return null;
        }

        /** @var Mage_Sales_Model_Resource_Order_Item_Collection $orderItemCollection */
        $orderItemCollection = Mage::getResourceModel('sales/order_item_collection');
        $orderItemCollection->filterByParent();
        $orderItemCollection->addFieldToFilter('order_id', ['neq' => $order->getId()]);
        $orderItemCollection->addFieldToFilter('sku', ['in' => $this->getVisibleSkusFromOrder($order)]);
        if (method_exists($orderItemCollection, 'addFilterByCustomerId')) {
            $orderItemCollection->addFilterByCustomerId($order->getCustomerId());
        } else {
            // Support for Magento versions prior to 1.9.3.6:
            $orderItemCollection->getSelect()->joinInner(
                ['order' => $orderItemCollection->getTable('sales/order')],
                'main_table.order_id = order.entity_id', [])
                ->where('order.customer_id IN(?)', $order->getCustomerId());
        }
        $orderItemCollection->addFieldToFilter('order.total_due', ['lt' => '0.01']);
        return $orderItemCollection->getSize() > 0;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string[]
     */
    protected function getVisibleSkusFromOrder(Mage_Sales_Model_Order $order)
    {
        $visibleSkus = [];
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $visibleSkus[] = $orderItem->getSku();
        }
        return $visibleSkus;
    }
}
