<?php
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

class Netresearch_Epayments_Model_Order_EmailManager implements Netresearch_Epayments_Model_Order_EmailInterface
{
    const PAYMENT_UPDATE_EVENT = 'payment_update';
    const PAYMENT_OUTPUT_SHOW_INSTRUCTIONS = 'SHOW_INSTRUCTIONS';

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    protected $statusMapping = array(
        'action_needed' => array(
            Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_PAYMENT
        ),
        'payment_successful' => array(
            Netresearch_Epayments_Model_Ingenico_StatusInterface::CAPTURE_REQUESTED,
            Netresearch_Epayments_Model_Ingenico_StatusInterface::CAPTURED,
            Netresearch_Epayments_Model_Ingenico_StatusInterface::PAID
        ),
        'payment_rejected' => array(
            Netresearch_Epayments_Model_Ingenico_StatusInterface::REJECTED,
            Netresearch_Epayments_Model_Ingenico_StatusInterface::REJECTED_CAPTURE
        ),
        'fraud_suspicion' => array(
            Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_FRAUD_APPROVAL
        ),
        'delayed_settlement' => array(
            Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_APPROVAL
        ),
        'slow_3rd_party' => array(
            Netresearch_Epayments_Model_Ingenico_StatusInterface::REDIRECTED
        )
    );

    /**
     * Netresearch_Epayments_Model_Order_EmailManager constructor.
     */
    public function __construct()
    {
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
    }

    /**
     * @param \Mage_Sales_Model_Order $order
     * @param $ingenicoPaymentStatus
     *
     * @return void
     */
    public function process(Mage_Sales_Model_Order $order, $ingenicoPaymentStatus)
    {
        if (!$this->ePaymentsConfig->getUpdateEmailEnabled($this->_getMappedStatusValue($ingenicoPaymentStatus))) {
            return;
        }

        if (!$order->getPayment()->getTransactionId()
        && $ingenicoPaymentStatus != Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_PAYMENT
        ) {
            return;
        }

        $info = Mage::helper('netresearch_epayments')->getPaymentStatusInfo($ingenicoPaymentStatus);
        $instructions = null;
        if ($ingenicoPaymentStatus == Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_PAYMENT) {
            if ($order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_SHOW_DATA_KEY)) {
                $displayedData = new \Ingenico\Connect\Sdk\Domain\Hostedcheckout\Definitions\DisplayedData();
                $displayedData->fromJson($order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_SHOW_DATA_KEY));
                $instructions = $this->_formatInstructions(
                    $displayedData->showData
                );
            }
        }
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        $storeId = $order->getStoreId();
        $emailInfo->addTo($order->getCustomerEmail());
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender($this->ePaymentsConfig->getUpdateEmailSender());
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($this->ePaymentsConfig->getUpdateEmailTemplate());
        $mailer->setTemplateParams(
            array(
                'order'          => $order,
                'comment'        => $info,
                'billing'        => $order->getBillingAddress(),
                'instructions' => $instructions
            )
        );

        $this->sendEmail($mailer, $order);

    }

    /**
     * @param $ingenicoPaymentStatus
     *
     * @return bool|int|string
     */
    protected function _getMappedStatusValue($ingenicoPaymentStatus)
    {
        $value = false;

        foreach ($this->statusMapping as $key => $statuses) {
            if (in_array($ingenicoPaymentStatus, $statuses)) {
                $value = $key;
                break;
            }
        }

        return $value;
    }

    /**
     * @param $instructionsArray
     *
     * @return string
     */
    protected function _formatInstructions($instructionsArray)
    {
        $instructions = '';
        $instructionsCurrency = '';
        $instructionsAmount = '';
        foreach ($instructionsArray as $pair) {
            if ($pair->key == 'CURRENCYCODE') {
                $instructionsCurrency = $pair->value;
                continue;
            }
            if ($pair->key == 'AMOUNT') {
                $instructionsAmount = $pair->value;
                continue;
            }
            if ($pair->key === 'BARCODE') {
                $pair->value = "<img src='data:image/gif;base64{$pair->value}' />";
            }

            $instructions .= '<tr><td>' . $pair->key . '</td><td>' . $pair->value . '</td></tr>';
        }

        if ($instructions && $instructionsAmount) {
            $instructionsAmount /= 100;
            $instructions .= '<tr><td>AMOUNT</td><td>' . $instructionsCurrency . $instructionsAmount. '</td></tr>';
        }

        return $instructions;
    }

    /**
     * Decide which mailing method to use according to the current magento version
     *
     * @param Mage_Core_Model_Email_Template_Mailer $mailer
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    protected function sendEmail(
        Mage_Core_Model_Email_Template_Mailer $mailer,
        Mage_Sales_Model_Order $order
    )
    {
        $magentoVersion = Mage::getVersionInfo();
        if ($magentoVersion['major'] === '1' && $magentoVersion['minor'] === '9') {
            $result = $this->addToEmailQueue($mailer, $order);
        } else {
            $result = $mailer->send();
        }
        return $result;
    }

    /**
     * @param Mage_Core_Model_Email_Template_Mailer $mailer
     * @param Mage_Sales_Model_Order $order
     */
    protected function addToEmailQueue(
        Mage_Core_Model_Email_Template_Mailer $mailer,
        Mage_Sales_Model_Order $order
    )
    {
        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($order->getId())
            ->setEntityType(Mage_Sales_Model_Order::ENTITY)
            ->setEventType(self::PAYMENT_UPDATE_EVENT)
            ->setIsForceCheck(false);
        $mailer->setQueue($emailQueue)->send();
    }
}
