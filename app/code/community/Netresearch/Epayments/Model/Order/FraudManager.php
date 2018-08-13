<?php

/**
 * Class Netresearch_Epayments_Model_Order_FraudManager
 */
class Netresearch_Epayments_Model_Order_FraudManager extends Netresearch_Epayments_Model_Order_EmailManager
{
    const FRAUD_EMAIL_EVENT = 'ingenico_fraud';

    /**
     * @param Mage_Sales_Model_Order $order
     * @param $ingenicoPaymentStatus
     * @return $this
     */
    public function process(Mage_Sales_Model_Order $order, $ingenicoPaymentStatus)
    {
        parent::process($order, $ingenicoPaymentStatus);
        $this->sendFraudEmail($order);

        return $this;
    }


    /**
     * Send an email to the configured Fraud Manager as well
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function sendFraudEmail(Mage_Sales_Model_Order $order)
    {
        /** @var Netresearch_Epayments_Model_ConfigInterface $config */
        $config = Mage::getSingleton('netresearch_epayments/config');
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');

        $storeId = $order->getStoreId();
        $orderLink = Mage::helper('adminhtml')->getUrl(
            "adminhtml/sales_order/view",
            array('order_id' => $order->getId())
        );

        $emailInfo->addTo($config->getFraudManagerEmail());
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender($config->getFraudEmailSender());
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($config->getFraudEmailTemplate());
        $mailer->setTemplateParams(
            array(
                'order' => $order,
                'order_link' => $orderLink
            )
        );

        $this->sendEmail($mailer, $order);
    }
}
