<?php

/**
 * Class Ingenico_Connect_Block_Form_CheckoutMethods
 */
class Ingenico_Connect_Block_Form_CheckoutMethods extends Mage_Payment_Block_Form
{
    /**
     * @var string
     */
    protected $_template;

    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    protected $_ePaymentsConfig;

    /**
     * @var Mage_Checkout_Model_Type_Onepage
     */
    protected $_checkout;

    /**
     * @var Ingenico_Connect_Model_Ingenico_CreateSession
     */
    protected $_createSession;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->_ePaymentsConfig = Mage::getSingleton('ingenico_connect/config');
        $this->_checkout = Mage::getSingleton('checkout/type_onepage');
        $this->_createSession = Mage::getModel('ingenico_connect/ingenico_createSession');

        if ($this->_ePaymentsConfig->getCheckoutType() ===
            Ingenico_Connect_Model_Config::CONFIG_INGENICO_CHECKOUT_TYPE_INLINE) {
            $this->_template = 'epayments/form/inline.phtml';
        } elseif ($this->_ePaymentsConfig->getCheckoutType() ===
            Ingenico_Connect_Model_Config::CONFIG_INGENICO_CHECKOUT_TYPE_HOSTED_CHECKOUT) {
            $this->_template = 'epayments/form/redirect.phtml';
        } else {
            $this->_template = 'epayments/form/fullredirect.phtml';
        }
    }

    /**
     * @return array
     */
    public function getProductGroupTitles()
    {
        return $this->_ePaymentsConfig->getProductGroupTitles();
    }

    /**
     * @return \Ingenico\Connect\Sdk\Domain\Sessions\SessionResponse
     * @throws Exception
     */
    public function getSessionData()
    {
        $customer = $this->_checkout->getCustomerSession()->getCustomer();
        try {
            $sessionData = $this->_createSession->create($customer);
        } catch (Exception $exception) {
            /** @var Ingenico_Connect_Model_Ingenico_Client_CommunicatorLogger $logger */
            $logger = Mage::getSingleton('ingenico_connect/ingenico_client_communicatorLogger');
            $logger->log($exception->getMessage());
            throw $exception;
        }

        return $sessionData;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_checkout->getQuote();
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return Mage::app()->getLocale()->getLocaleCode();
    }

    /**
     * See if checkout is loaded in one go via the OneStepCheckout extension.
     *
     * @return bool
     */
    public function isOneStepCheckout()
    {
        return $this->getRequest()->getControllerModule() === 'Idev_OneStepCheckout';
    }
}
