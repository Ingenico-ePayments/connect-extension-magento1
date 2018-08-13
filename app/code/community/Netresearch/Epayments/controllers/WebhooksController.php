<?php

class Netresearch_Epayments_WebhooksController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handles payment.* events
     */
    public function paymentAction()
    {
        if ($this->checkVerification()) {
            return;
        }
        /** @var Netresearch_Epayments_Model_Ingenico_Webhooks_PaymentEventDataResolver $eventDataResolver */
        $eventDataResolver = Mage::getModel('netresearch_epayments/ingenico_webhooks_paymentEventDataResolver');
        $this->handle($eventDataResolver);
    }

    /**
     * Handles refund.* events
     */
    public function refundAction()
    {
        if ($this->checkVerification()) {
            return;
        }
        /** @var Netresearch_Epayments_Model_Ingenico_Webhooks_RefundEventDataResolver $eventDataResolver */
        $eventDataResolver = Mage::getModel('netresearch_epayments/ingenico_webhooks_refundEventDataResolver');
        $this->handle($eventDataResolver);
    }

    /**
     * @return Netresearch_Epayments_Model_Ingenico_Webhooks_RequestContext
     * @throws Zend_Controller_Request_Exception
     */
    protected function _buildWebhooksRequestContext()
    {
        return Mage::getModel(
            'netresearch_epayments/ingenico_webhooks_requestContext',
            array(
                'headers' => array(
                    'X-GCS-Signature' => $this->getRequest()->getHeader('X-GCS-Signature'),
                    'X-GCS-KeyId' => $this->getRequest()->getHeader('X-GCS-KeyId'),
                ),
                'body' => $this->getRequest()->getRawBody()
            )
        );
    }

    /**
     * @param Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface $eventDataResolver
     *
     * @throws Zend_Controller_Request_Exception
     */
    protected function handle(
        Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface $eventDataResolver
    ) {
        /** @var Netresearch_Epayments_Model_Ingenico_Webhooks $webhooks */
        $webhooks = Mage::getModel('netresearch_epayments/ingenico_webhooks');
        $requestContext = $this->_buildWebhooksRequestContext();
        $webhooks->handle(
            $requestContext,
            $eventDataResolver
        );
    }

    /**
     * Checks the headers of the request for a special endpoint verification
     *
     * @return Zend_Controller_Response_Abstract
     */
    protected function checkVerification()
    {
        $verificationString = $this->getRequest()->getHeader('X-GCS-Webhooks-Endpoint-Verification');
        if ($verificationString) {
            $this->getResponse()->setHeader('Content-Type', 'text/plain');
            return $this->getResponse()->setBody(
                $verificationString
            );
        }
    }
}
