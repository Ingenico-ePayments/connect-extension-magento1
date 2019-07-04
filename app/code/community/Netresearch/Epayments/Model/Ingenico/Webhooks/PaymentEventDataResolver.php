<?php

use Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface as EventDataResolverInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Webhooks_PaymentEventDataResolver
 */
class Netresearch_Epayments_Model_Ingenico_Webhooks_PaymentEventDataResolver implements EventDataResolverInterface
{
    /**
     * @var Netresearch_Epayments_Model_Ingenico_MerchantReference
     */
    protected $merchantReference;

    /**
     * Netresearch_Epayments_Model_Ingenico_Webhooks_PaymentEventDataResolver constructor.
     */
    public function __construct(array $data = array())
    {
        if (isset($data['merchant_reference'])) {
            $this->merchantReference = $data['merchant_reference'];
        } else {
            $this->merchantReference = Mage::getSingleton('netresearch_epayments/ingenico_merchantReference');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event)
    {
        $this->assertCorrectEvent($event);
        return $event->payment;
    }

    /**
     * {@inheritdoc}
     */
    public function getMerchantReference(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event)
    {

        $this->assertCorrectEvent($event);
        $merchantOrderId = $this->merchantReference->extractOrderReference(
            $event->payment->paymentOutput->references->merchantReference
        );

        if ($merchantOrderId <= 0) {
            throw new RuntimeException('Merchant reference value is missing in Event response.');
        }

        return $merchantOrderId;
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event
     */
    private function assertCorrectEvent(\Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $event)
    {
        if (!$event
            || !$event->payment
            || !$event->payment instanceof \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse
            || !$event->payment->paymentOutput
            || !$event->payment->paymentOutput instanceof \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentOutput
        ) {
            throw new RuntimeException('Event does not match resolver.');
        }
    }
}
