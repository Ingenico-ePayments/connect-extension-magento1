<?php
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Netresearch_Epayments_HostedCheckoutController
 */
class Netresearch_Epayments_HostedCheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * When a customer returns to website from the hosted checkout.
     */
    public function returnAction()
    {
        try {
            $hostedCheckoutId = $this->retrieveHostedCheckoutId();
            /** @var Netresearch_Epayments_Model_Ingenico_GetHostedCheckoutStatus $status */
            $status = Mage::getModel('netresearch_epayments/ingenico_getHostedCheckoutStatus');
            $order = $status->process($hostedCheckoutId);
            $paymentStatus = $order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY);
            $info = Mage::helper('netresearch_epayments')->getPaymentStatusInfo($paymentStatus);

            if ($info) {
                $this->getCheckoutSession()->addSuccess($this->__('Payment status:') . ' ' . $info);
            }

            $this->_redirect('checkout/onepage/success');
        } catch (Exception $e) {
            $this->getCheckoutSession()->addError($e->getMessage());
            Mage::logException($e);
            $this->refillCart();
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function retrieveHostedCheckoutId()
    {
        $hostedCheckoutId = $this->getRequest()->getParam('hostedCheckoutId', false);
        $session = $this->getCheckoutSession();

        if ($hostedCheckoutId === false && $session->getLastRealOrder()->getPayment() !== null) {
            $hostedCheckoutId = $session
                ->getLastRealOrder()
                ->getPayment()
                ->getAdditionalInformation(HostedCheckout::HOSTED_CHECKOUT_ID_KEY);
        }

        if (!$hostedCheckoutId) {
            Mage::throwException(
                Mage::helper('netresearch_epayments')->__('Could not retrieve payment status.')
            );
        }

        return $hostedCheckoutId;
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getModel('checkout/session');

        return $session;
    }

    /**
     * Refill cart from oder items.
     */
    protected function refillCart()
    {
        $oldOrder = $this->getCheckoutSession()->getLastRealOrder();
        if ($oldOrder->getId()) {
            $items = $oldOrder->getItemsCollection();
            $cart = Mage::getSingleton('checkout/cart');
            foreach ($items as $item) {
                $cart->addOrderItem($item);
            }

            $cart->save();
        }
    }
}
