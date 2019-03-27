<?php
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Netresearch_Epayments_RedirectPaymentController
 */
class Netresearch_Epayments_RedirectPaymentController extends Mage_Core_Controller_Front_Action
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
     * @var Netresearch_Epayments_Helper_Data
     */
    protected $epaymentsHelper;
    /**
     * @var Mage_Checkout_Model_Cart
     */
    protected $cart;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RetrievePayment
     */
    protected $retrievePaymentAction;

    public function _construct()
    {
        parent::_construct();

        $this->checkoutSession = Mage::getSingleton('checkout/session');
        $this->orderModel = Mage::getModel('sales/order');
        $this->epaymentsHelper = Mage::helper('netresearch_epayments');
        $this->cart = Mage::getSingleton('checkout/cart');
        $this->retrievePaymentAction = Mage::getModel('netresearch_epayments/ingenico_retrievePayment');
    }

    /**
     * When a customer returns to website from a redirect payment.
     *
     * @throws Exception
     */
    public function returnAction()
    {
        $orderId = $this->checkoutSession->getLastOrderId();

        if (!$orderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $this->orderModel->load($orderId);

        try {
            $this->retrievePaymentAction->process($order);
            $paymentStatus = $order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY);
            $info = $this->epaymentsHelper->getPaymentStatusInfo($paymentStatus);
            if ($info) {
                $this->checkoutSession->addSuccess($this->__('Payment status:') . ' ' . $info);
            }

            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Exception $e) {
            $this->checkoutSession->addError($e->getMessage());
            Mage::logException($e);
        }

        if ($order->isCanceled()) {
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                $this->cart->addOrderItem($item);
            }

            $this->cart->save();
        }

        $this->_redirect('checkout/cart');
    }
}
