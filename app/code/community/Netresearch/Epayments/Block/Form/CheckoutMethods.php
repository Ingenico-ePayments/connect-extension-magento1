<?php

/**
 * Class Netresearch_Epayments_Block_Form_CheckoutMethods
 */
class Netresearch_Epayments_Block_Form_CheckoutMethods extends Mage_Payment_Block_Form
{
    /**
     * @var string
     */
    protected $_template;

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $_ePaymentsConfig;

    /**
     * @var Mage_Checkout_Model_Type_Onepage
     */
    protected $_checkout;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_CreateSession
     */
    protected $_createSession;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->_ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        $this->_checkout = Mage::getSingleton('checkout/type_onepage');
        $this->_createSession = Mage::getModel('netresearch_epayments/ingenico_createSession');

        if ($this->_ePaymentsConfig->isInlinePaymentsEnabled()) {
            $this->_template = 'epayments/form/inline.phtml';
        } else {
            $this->_template = 'epayments/form/redirect.phtml';
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
     */
    public function getSessionData()
    {
        $customer = $this->_checkout->getCustomerSession()->getCustomer();
        $sessionData = $this->_createSession->create($customer);

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
