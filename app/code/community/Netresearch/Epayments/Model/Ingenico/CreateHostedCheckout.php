<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_ActionInterface as ActionInterface;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreateHostedCheckout_CreateHostedCheckoutRequestBuilder as
    CreateHostedCheckoutRequestBuilder;

/**
 * Class Netresearch_Epayments_Model_Ingenico_CreateHostedCheckout
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#hostedcheckouts
 */
class Netresearch_Epayments_Model_Ingenico_CreateHostedCheckout implements ActionInterface
{
    /**
     * @var Netresearch_Epayments_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var CreateHostedCheckoutRequestBuilder
     */
    protected $hostedCheckoutRequestBuilder;

    /**
     * Netresearch_Epayments_Model_Ingenico_CreateHostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient  = Mage::getSingleton('netresearch_epayments/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        $this->hostedCheckoutRequestBuilder = Mage::getModel(
            'netresearch_epayments/ingenico_requestBuilder_createHostedCheckout_createHostedCheckoutRequestBuilder'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $getParams = new \Ingenico\Connect\Sdk\Merchant\Products\GetProductParams();
        $getParams->amount = Mage::helper('netresearch_epayments')->formatIngenicoAmount($order->getBaseGrandTotal());
        $getParams->currencyCode = $order->getBaseCurrencyCode();
        $getParams->countryCode = $order->getBillingAddress()->getCountryId();
        $getParams->locale = Mage::app()->getLocale()->getLocaleCode();
        $getParams->hide = "fields";

        $payment = $order->getPayment();

        $hostedCheckoutRequest = $this->hostedCheckoutRequestBuilder->create($order);

        $payment->setAdditionalInformation(
            HostedCheckout::IDEMPOTENCE_KEY,
            uniqid(preg_replace('#\s+#', '.', $order->getStoreName()) . '.', true)
        );

        $response  = $this->ingenicoClient
            ->getIngenicoClient($order->getStoreId())
            ->merchant($this->ePaymentsConfig->getMerchantId($order->getStoreId()))
            ->hostedcheckouts()
            ->create($hostedCheckoutRequest);

        $subdomain = Mage::getSingleton('netresearch_epayments/config')->getHostedCheckoutSubdomain();
        $redirectUrl =  $subdomain . $response->partialRedirectUrl;

        $payment->setAdditionalInformation(HostedCheckout::REDIRECT_URL_KEY, $redirectUrl);
        $payment->setAdditionalInformation(HostedCheckout::HOSTED_CHECKOUT_ID_KEY, $response->hostedCheckoutId);
        $payment->setAdditionalInformation(HostedCheckout::RETURNMAC_KEY, $response->RETURNMAC);

        // Mage_Payment_Model_Method_Abstract::getOrderPlaceRedirectUrl has no access to order
        $order->getQuote()
            ->getPayment()
            ->setAdditionalInformation(HostedCheckout::REDIRECT_URL_KEY, $redirectUrl);
    }
}
