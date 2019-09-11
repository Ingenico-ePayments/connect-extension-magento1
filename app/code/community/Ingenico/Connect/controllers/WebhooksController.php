<?php

use Ingenico_Connect_Model_Ingenico_Webhooks_EventDataResolverInterface as EventDataResolverInterface;

/**
 * Class Ingenico_Connect_WebhooksController
 */
class Ingenico_Connect_WebhooksController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handles payment.* events
     *
     * @throws Zend_Controller_Request_Exception
     */
    public function paymentAction()
    {
        if ($this->checkVerification()) {
            return;
        }

        /** @var Ingenico_Connect_Model_Ingenico_Webhooks_PaymentEventDataResolver $eventDataResolver */
        $eventDataResolver = Mage::getSingleton('ingenico_connect/ingenico_webhooks_paymentEventDataResolver');
        $this->handle($eventDataResolver);
    }

    /**
     * Handles refund.* events
     *
     * @throws Zend_Controller_Request_Exception
     */
    public function refundAction()
    {
        if ($this->checkVerification()) {
            return;
        }

        /** @var Ingenico_Connect_Model_Ingenico_Webhooks_RefundEventDataResolver $eventDataResolver */
        $eventDataResolver = Mage::getSingleton('ingenico_connect/ingenico_webhooks_refundEventDataResolver');
        $this->handle($eventDataResolver);
    }

    /**
     * @return Ingenico_Connect_Model_Ingenico_Webhooks_RequestContext
     * @throws Zend_Controller_Request_Exception
     */
    protected function _buildWebhooksRequestContext()
    {
        /** @var Ingenico_Connect_Model_Ingenico_Webhooks_RequestContext $context */
        $context = Mage::getModel(
            'ingenico_connect/ingenico_webhooks_requestContext',
            array(
                'headers' => array(
                    'X-GCS-Signature' => $this->getRequest()->getHeader('X-GCS-Signature'),
                    'X-GCS-KeyId' => $this->getRequest()->getHeader('X-GCS-KeyId'),
                ),
                'body' => $this->getRequest()->getRawBody()
            )
        );

        return $context;
    }

    /**
     * @param Ingenico_Connect_Model_Ingenico_Webhooks_EventDataResolverInterface $eventDataResolver
     */
    protected function handle(EventDataResolverInterface $eventDataResolver)
    {
        /** @var Ingenico_Connect_Model_Ingenico_Webhooks $webhooks */
        $webhooks = Mage::getModel('ingenico_connect/ingenico_webhooks');
        try {
            $requestContext = $this->_buildWebhooksRequestContext();
            $webhooks->handle(
                $requestContext,
                $eventDataResolver
            );
        } catch (\Exception $exception) {
            Mage::logException($exception);
        }
    }

    /**
     * Checks the headers of the request for a special endpoint verification
     *
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    protected function checkVerification()
    {
        $verificationString = $this->getRequest()->getHeader('X-GCS-Webhooks-Endpoint-Verification');
        if ($verificationString) {
            $this->getResponse()->setHeader('Content-Type', 'text/plain');
            $this->getResponse()->setBody(
                $verificationString
            );

            return true;
        }

        return false;
    }
}
