<?php

use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\Definitions\HostedCheckoutSpecificInput;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_RequestBuilder as RequestBuilder;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreateHostedCheckout_CreateHostedCheckoutRequestBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreateHostedCheckout_CreateHostedCheckoutRequestBuilder
{
    /**
     * @var Netresearch_Epayments_Model_TokenService
     */
    private $tokenService;

    /**
     * @var Netresearch_Epayments_Model_Config
     */
    private $config;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * CreateHostedCheckoutRequestBuilder constructor.
     */
    public function __construct()
    {
        $this->tokenService = Mage::getSingleton('netresearch_epayments/tokenService');
        $this->config = Mage::getSingleton('netresearch_epayments/config');
        $this->requestBuilder = Mage::getSingleton(
            'netresearch_epayments/ingenico_requestBuilder_common_requestBuilder'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return CreateHostedCheckoutRequest
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $request = new CreateHostedCheckoutRequest();
        $request = $this->requestBuilder->create($request, $order);

        $request->hostedCheckoutSpecificInput = $this->buildHostedCheckoutSpecificInput($order);

        return $request;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return HostedCheckoutSpecificInput
     */
    protected function buildHostedCheckoutSpecificInput(Mage_Sales_Model_Order $order)
    {
        $specificInput = new HostedCheckoutSpecificInput();
        $specificInput->locale = Mage::app()->getLocale()->getLocaleCode();
        $specificInput->returnUrl = Mage::getUrl(RequestBuilder::HOSTED_CHECKOUT_RETURN_URL);
        $specificInput->showResultPage = false;
        $specificInput->tokens = $this->getTokens($order);
        $specificInput->validateShoppingCart = true;
        $specificInput->returnCancelState = true;
        if ($variant = $this->config->getHostedCheckoutVariant($order->getStoreId())) {
            $specificInput->variant = $variant;
        }

        return $specificInput;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return null|string  String of comma separated token values
     */
    protected function getTokens(Mage_Sales_Model_Order $order)
    {
        $customerId = $order->getCustomerId();
        $tokenizationRequested = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_TOKENIZE_KEY
        );
        if (!$customerId || !$tokenizationRequested) {
            return null;
        }

        $tokens = $this->tokenService->find($customerId);

        if (empty($tokens)) {
            return null;
        }

        $result = array();
        foreach ($tokens as $token) {
            $result[] = $token->getTokenString();
        }

        return implode(',', $result);
    }
}
