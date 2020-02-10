<?php
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Ingenico_Connect_RedirectPaymentController
 */
class Ingenico_Connect_RedirectPaymentController extends Mage_Core_Controller_Front_Action
{

    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $checkoutSession;
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $orderModel;
    /**
     * @var Ingenico_Connect_Helper_Data
     */
    protected $epaymentsHelper;
    /**
     * @var Mage_Checkout_Model_Cart
     */
    protected $cart;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RetrievePayment
     */
    protected $retrievePaymentAction;

    /**
     * When a customer returns to website from a redirect payment.
     *
     * @throws Exception
     */
    public function returnAction()
    {
        $cartModel = Mage::getSingleton('checkout/cart');
        $checkoutSession = Mage::getSingleton('checkout/session');

        $orderId = $checkoutSession->getLastOrderId();
        if (!$orderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);

        try {
            Mage::getModel('ingenico_connect/ingenico_retrievePayment')->process($order);
            $paymentStatus = $order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY);
            $info = Mage::helper('ingenico_connect')->getPaymentStatusInfo($paymentStatus);
            if ($info) {
                $checkoutSession->addSuccess($this->__('Payment status:') . ' ' . $info);
            }

            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Exception $e) {
            $checkoutSession->addError($e->getMessage());
            Mage::logException($e);
        }

        if ($order->isCanceled()) {
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                $cartModel->addOrderItem($item);
            }

            $cartModel->save();
        }

        $this->_redirect('checkout/cart');
    }
}
