<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_AbstractRequestBuilder as AbstractRequestBuilder;
use \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use \Ingenico\Connect\Sdk\Domain\Hostedcheckout\Definitions\HostedCheckoutSpecificInput;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreateHostedCheckout_CreateHostedCheckoutRequestBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreateHostedCheckout_CreateHostedCheckoutRequestBuilder
    extends AbstractRequestBuilder
{
    /**
     * @var Netresearch_Epayments_Model_TokenService
     */
    protected $tokenService;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreateHostedCheckout_CreateHostedCheckoutRequestBuilder
     * constructor.
     */
    public function __construct()
    {
        $this->tokenService = Mage::getModel('netresearch_epayments/tokenService');
        $this->requestObject = new CreateHostedCheckoutRequest();

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return CreateHostedCheckoutRequest
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $request = parent::create($order);
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
        $specificInput->returnUrl = Mage::getUrl(self::HOSTED_CHECKOUT_RETURN_URL);
        $specificInput->showResultPage = false;
        $specificInput->tokens = $this->getTokens($order);
        $specificInput->validateShoppingCart = true;

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
