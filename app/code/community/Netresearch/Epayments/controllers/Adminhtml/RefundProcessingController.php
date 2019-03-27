<?php

class Netresearch_Epayments_Adminhtml_RefundProcessingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Approve Credit memo action.
     * The Magento Core does not offer a controller for
     * approving credit memos, thats why we implement our own.
     */
    public function approveCreditMemoAction()
    {
        $this->_redirectReferer();

        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        /** @var Mage_Sales_Model_Resource_Order_Creditmemo_Collection $creditmemos */
        $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection');
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $creditmemos->getItemById($creditmemoId);

        $paymentMethod = $creditmemo->getOrder()->getPayment()->getMethodInstance();
        if (!$paymentMethod instanceof Netresearch_Epayments_Model_Method_HostedCheckout) {
            return;
        }

        if (!$creditmemo->canRefund()) {
            $this->_getSession()->addError($this->__('Credit memo is not refundable.'));
            return;
        }

        /** @var Netresearch_Epayments_Model_Ingenico_ApproveRefund $approveRefund */
        $approveRefund = Mage::getSingleton('netresearch_epayments/ingenico_approveRefund');
        try {
            $approveRefund->process($creditmemo);
        } catch (\Ingenico\Connect\Sdk\ResponseException $e) {
            $errors = $e->getErrors();
            $message = array_reduce(
                $errors,
                function (
                    $message,
                    \Ingenico\Connect\Sdk\Domain\Errors\Definitions\APIError $error
                ) {
                    $message .= sprintf(
                        "HTTP: %s Message: %s \n",
                        $error->httpStatusCode,
                        $error->message
                    );
                    return $message;
                },
                ''
            );
            $this->_getSession()->addError($this->__('Error during refund approval: %s', $message));
            return;
        }

        $this->_getSession()->addSuccess($this->__('The credit memo has been approved.'));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/creditmemo');
    }
}
