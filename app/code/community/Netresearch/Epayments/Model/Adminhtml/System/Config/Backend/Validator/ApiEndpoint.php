<?php

/**
 * Netresearch_Epayments
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @license   https://opensource.org/licenses/MIT
 * @link      http://www.netresearch.de/
 *
 *
 * Class Netresearch_Epayments_Model_Adminhtml_System_Config_Backend_Validator_ApiEndpoint
 */
class Netresearch_Epayments_Model_Adminhtml_System_Config_Backend_Validator_ApiEndpoint
    extends Mage_Core_Model_Config_Data
{
    protected static $failureMessage = 'Could not establish connection to Ingenico Connect platform. Please check your account settings.';
    protected static $successMessage = 'Connection to the Ingenico Connect platform could successfully be established.';

    protected $keys = array(
        'api_key',
        'api_secret',
        'merchant_id',
        'api_endpoint',
    );

    /** @var Netresearch_Epayments_Helper_Data */
    protected $helper;

    /** @var Netresearch_Epayments_Model_Ingenico_TestAccount */
    protected $testAccountAction;

    /** @var Mage_Adminhtml_Model_Session */
    protected $adminSession;

    /** @var  Netresearch_Epayments_Model_Config $epaymentsConfig */
    protected $epaymentsConfig;

    protected function _construct()
    {
        parent::_construct();
        $this->helper = Mage::helper('netresearch_epayments');
        $this->testAccountAction = Mage::getSingleton('netresearch_epayments/ingenico_testAccount');
        $this->adminSession = Mage::getSingleton('adminhtml/session');
        $this->epaymentsConfig = Mage::getModel('netresearch_epayments/config');
    }


    protected function _afterSave()
    {
        $epaymentsConfig = Mage::getModel('netresearch_epayments/config');
        $fieldsetData = $this->getData('fieldset_data');

        $configData = array(
            'api_key' => $epaymentsConfig->getApiKey($this->getScopeId()),
            'api_secret' => $epaymentsConfig->getApiSecret($this->getScopeId()),
            'merchant_id' => $epaymentsConfig->getMerchantId($this->getScopeId()),
            'api_endpoint' => $epaymentsConfig->getApiEndpoint($this->getScopeId()),
        );

        $data = array_intersect_key($fieldsetData, array_flip($this->keys));


        if (isset($data['api_secret']) && $this->isNotPasswordInputChanged($data['api_secret'])) {
            $data['api_secret'] = $configData['api_secret'];
        }

        $equals = $this->compareValues($configData, $data);
        $filled = $this->isArrayComplete($data);
        $isVerified = $this->epaymentsConfig->isAccountVerified();

        if (!$equals || !$isVerified) {
            if (!$filled) {
                $this->adminSession->addWarning(
                    $this->helper->__('Your credentials are incorrect')
                );
            } else {
                $this->runTest($data);
            }
        }

        return parent::_afterSave();
    }

    /**
     * @param $data
     * @return false|int
     */
    public function isNotPasswordInputChanged($data)
    {
        return preg_match('/^\*+$/', $data);
    }

    /**
     * @param array $configData
     * @param array $data
     * @return bool
     */
    public function compareValues($configData = array(), $data = array())
    {
        $result = false;
        if ($data === array_intersect($data, $configData) && $configData === array_intersect($configData, $data)) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param array $array
     * @return bool
     */
    public function isArrayComplete($array = array())
    {
        $result = true;
        foreach ($array as $value) {
            if ($value === '') {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param array $data
     */
    public function runTest($data = array())
    {
        $testResponse = $this->testAccountAction
            ->process($this->getScopeId(), $data);

        if ($testResponse === Netresearch_Epayments_Model_Ingenico_TestAccount::STATUS_OK) {
            $this->adminSession->addSuccess($this->helper->__(self::$successMessage));
            $this->epaymentsConfig->setAccountVerified(1);
        } else {
            $this->adminSession->addWarning($this->helper->__(self::$failureMessage));
            $this->epaymentsConfig->setAccountVerified(0);
        }
    }
}
