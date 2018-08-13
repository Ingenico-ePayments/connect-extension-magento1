<?php
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Netresearch_Epayments_HostedCheckoutController
 */
class Netresearch_Epayments_HostedCheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * When a customer return to website from gateway.
     *
     * @throws Exception
     */
    public function returnAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $orderId = $session->getLastOrderId();

        if (!$orderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);

        try {
            /** @var Netresearch_Epayments_Model_Ingenico_GetHostedCheckoutStatus $status */
            $status = Mage::getModel('netresearch_epayments/ingenico_getHostedCheckoutStatus');
            $status->process($order);
            $ingenicoPaymentStatus = $order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY);
            $info = Mage::helper('netresearch_epayments')->getPaymentStatusInfo($ingenicoPaymentStatus);


            if ($info) {
                Mage::getSingleton('checkout/session')->addSuccess($this->__('Payment status:') . ' ' . $info);
            }

            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
            Mage::logException($e);
        }

        if ($order->isCanceled()) {
            $items = $order->getItemsCollection();
            $cart = Mage::getSingleton('checkout/cart');
            foreach ($items as $item) {
                $cart->addOrderItem($item);
            }

            $cart->save();
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
}
