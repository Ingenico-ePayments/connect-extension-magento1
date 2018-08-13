<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_ActionInterface as ActionInterface;
use Netresearch_Epayments_Model_Ingenico_Api_ClientInterface as Client;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder as
    CreatePaymentRequestBuilder;
use Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentResponse;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\CreatePaymentResult;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\MerchantAction;

/**
 * The CreatePayment action is used for orders that have an encrypted client payload
 * that is used to bypass the hosted checkout page.
 *
 * @link https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/payments/create.html
 */
class Netresearch_Epayments_Model_Ingenico_CreatePayment implements ActionInterface
{
    const ACTION_TYPE_REDIRECT = 'REDIRECT';
    const ACTION_TYPE_SHOW_FORM = 'SHOW_FORM';
    const ACTION_TYPE_SHOW_INSTRUCTIONS = 'SHOW_INSTRUCTIONS';
    const ACTION_TYPE_SHOW_TRANSACTION_RESULTS = 'SHOW_TRANSACTION_RESULTS';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CreatePaymentRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_StatusFactory
     */
    protected $statusFactory;

    /**
     * @var Netresearch_Epayments_Model_TokenService
     */
    protected $tokenService;

    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $checkoutSession;

    /**
     * @var Netresearch_Epayments_Helper_Data
     */
    protected $epaymentsHelper;

    /**
     * Netresearch_Epayments_Model_Ingenico_CreatePayment constructor.
     */
    public function __construct()
    {
        $this->client = Mage::getModel('netresearch_epayments/ingenico_client');
        $this->statusFactory = Mage::getModel('netresearch_epayments/ingenico_statusFactory');
        $this->tokenService = Mage::getModel('netresearch_epayments/tokenService');
        $this->checkoutSession = Mage::getSingleton('checkout/session');
        $this->epaymentsHelper = Mage::helper('netresearch_epayments');
        $this->requestBuilder = Mage::getModel(
            'netresearch_epayments/ingenico_requestBuilder_createPayment_createPaymentRequestBuilder'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Mage_Payment_Model_Info_Exception
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $request = $this->requestBuilder->create($order);
        try {
            $response = $this->client->createPayment($request);
        } catch (\Ingenico\Connect\Sdk\ResponseException $e) {
            throw new Mage_Payment_Model_Info_Exception(
                Mage::helper('checkout')->__(
                    'There was an error processing your order. Please contact us or try again later.'
                )
            );
        }

        $paymentResponse = $response->payment;

        $this->processToken($order, $response);

        if ($response->merchantAction && $response->merchantAction->actionType) {
            $this->handleMerchantAction($order, $response->merchantAction);
        }

        $status = $this->statusFactory->create($paymentResponse);
        $status->apply($order);

        $this->handleSuccessfulPayment($order, $response);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param CreatePaymentResponse $response
     */
    protected function processToken(
        Mage_Sales_Model_Order $order,
        CreatePaymentResponse $response
    )
    {
        if ($order->getCustomerId() && $response->creationOutput->token) {
            $tokenString = $response->creationOutput->token;
            $this->tokenService->assignToken($order->getCustomerId(), $tokenString);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param MerchantAction $merchantAction
     *
     * @url https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/java/payments/create.html#payments-create-response-201
     */
    protected function handleMerchantAction(
        Mage_Sales_Model_Order $order,
        MerchantAction $merchantAction
    )
    {
        switch ($merchantAction->actionType) {
            case self::ACTION_TYPE_REDIRECT:
                $url = $merchantAction->redirectData->redirectURL;
                $returnmac = $merchantAction->redirectData->RETURNMAC;
                $order->getPayment()->setAdditionalInformation(HostedCheckout::RETURNMAC_KEY, $returnmac);
                $order->getPayment()->setAdditionalInformation(HostedCheckout::REDIRECT_URL_KEY, $url);
                // Mage_Payment_Model_Method_Abstract::getOrderPlaceRedirectUrl has no access to order
                $order->getQuote()->getPayment()->setAdditionalInformation(HostedCheckout::REDIRECT_URL_KEY, $url);
                break;
            case self::ACTION_TYPE_SHOW_INSTRUCTIONS:
                $displayData = new \Ingenico\Connect\Sdk\Domain\Hostedcheckout\Definitions\DisplayedData();
                $displayData->fromObject($merchantAction);
                $order->getPayment()->setAdditionalInformation(
                    HostedCheckout::PAYMENT_SHOW_DATA_KEY,
                    $displayData->toJson()
                );
                break;
            case self::ACTION_TYPE_SHOW_TRANSACTION_RESULTS:
                $data = array();
                foreach ($merchantAction->showData as $item) {
                    if ($item->key !== 'BARCODE') {
                        $data[] = $item->key . ': ' . $item->value;
                    }
                }
                $this->checkoutSession->addNotice(implode('; ', $data));
                break;
            case self::ACTION_TYPE_SHOW_FORM:
                /** @TODO(nr) Implement form field page for Bancontact. */
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param CreatePaymentResponse $statusResponse
     */
    protected function handleSuccessfulPayment(
        Mage_Sales_Model_Order $order,
        CreatePaymentResponse $statusResponse
    )
    {
        $paymentId = $statusResponse->payment->id;
        $paymentStatus = $statusResponse->payment->status;
        $paymentStatusCode = $statusResponse->payment->statusOutput->statusCode;

        $payment = $order->getPayment();
        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY, $paymentId);
        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY, $paymentStatus);
        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_STATUS_CODE_KEY, $paymentStatusCode);

        $info = $this->epaymentsHelper->getPaymentStatusInfo($paymentStatus);
        /**
         * Add checkout message
         */
        if ($info) {
            $this->checkoutSession->addSuccess('Status: ' . $info);
        }

        /**
         * Add payment history comment
         */
        if ($info) {
            $order->addStatusHistoryComment(
                sprintf(
                    "%s (payment status: '%s', payment status code: '%s')",
                    $info,
                    $paymentStatus,
                    $paymentStatusCode
                )
            );
        }

        $order->addRelatedObject($payment);
        $order->save();

        /**
         * Send new Order Email
         */
        try {
            $order->sendNewOrderEmail();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
