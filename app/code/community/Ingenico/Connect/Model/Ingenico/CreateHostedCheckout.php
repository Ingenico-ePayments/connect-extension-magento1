<?php

use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico_Connect_Model_Ingenico_ActionInterface as ActionInterface;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_CreateHostedCheckout_CreateHostedCheckoutRequestBuilder as
    CreateHostedCheckoutRequestBuilder;

/**
 * Class Ingenico_Connect_Model_Ingenico_CreateHostedCheckout
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#hostedcheckouts
 */
class Ingenico_Connect_Model_Ingenico_CreateHostedCheckout implements ActionInterface
{
    /**
     * @var Ingenico_Connect_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var CreateHostedCheckoutRequestBuilder
     */
    protected $hostedCheckoutRequestBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_CreateHostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient  = Mage::getSingleton('ingenico_connect/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('ingenico_connect/config');
        $this->hostedCheckoutRequestBuilder = Mage::getModel(
            'ingenico_connect/ingenico_requestBuilder_createHostedCheckout_createHostedCheckoutRequestBuilder'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $getParams = new \Ingenico\Connect\Sdk\Merchant\Products\GetProductParams();
        $getParams->amount = Mage::helper('ingenico_connect')->formatIngenicoAmount($order->getBaseGrandTotal());
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

        $subdomain = Mage::getSingleton('ingenico_connect/config')->getHostedCheckoutSubdomain();
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
