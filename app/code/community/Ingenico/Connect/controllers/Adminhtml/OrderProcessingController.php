<?php

class Ingenico_Connect_Adminhtml_OrderProcessingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Refresh one order action
     */
    public function refreshOrderStatusAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        try {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($orderId);

            /** @var Ingenico_Connect_Model_Ingenico_RetrievePayment $status */
            $status = Mage::getModel('ingenico_connect/ingenico_retrievePayment');
            $orderWasUpdated = $status->process($order);

            if ($orderWasUpdated) {
                $this->_getSession()->addSuccess($this->__('The order status was successfully refreshed.'));
            } else {
                $this->_getSession()->addWarning($this->__('There is nothing to update. Payment status was not changed.'));
            }
        } catch (Exception $e) {
            // @todo add logging
            $this->_getSession()->addError($this->__('Unable to refresh the order.'));
            Mage::logException($e);
        }

        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/updateOrder');
    }
}
