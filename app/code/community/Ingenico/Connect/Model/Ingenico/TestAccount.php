<?php

/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Ingenico_Connect
 *
 *
 * Class Ingenico_Connect_Model_Ingenico_TestAccount
 * @see https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/services/testconnection.html
 */
class Ingenico_Connect_Model_Ingenico_TestAccount
    extends Ingenico_Connect_Model_Ingenico_AbstractAction
    implements Ingenico_Connect_Model_Ingenico_ActionInterface
{
    const STATUS_OK = 'OK';
    const STATUS_FAIL = 'FAIL';

    /**
     * @var Ingenico_Connect_Model_Ingenico_Api_ClientInterface $ingenicoClient
     */
    private $ingenicoClient;

    /**
     * Ingenico_Connect_Model_Ingenico_TestAccount constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->ingenicoClient = Mage::getModel('ingenico_connect/ingenico_client');
    }

    /**
     * Process test request with account data
     *
     * @param $scopeId
     * @param array $data
     * @return string $status
     */
    public function process($scopeId, $data = array())
    {
        try {
            $this->ingenicoClient->ingenicoTestAccount($scopeId, $data);
            $status = self::STATUS_OK;
        } catch (\Exception $exception) {
            $status = self::STATUS_FAIL;
        }

        return $status;
    }
}
