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
 * Class Netresearch_Epayments_Model_Ingenico_TestAccount
 * @see https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/services/testconnection.html
 */
class Netresearch_Epayments_Model_Ingenico_TestAccount
    extends Netresearch_Epayments_Model_Ingenico_AbstractAction
    implements Netresearch_Epayments_Model_Ingenico_ActionInterface
{
    const STATUS_OK = 'OK';
    const STATUS_FAIL = 'FAIL';

    /**
     * @var Netresearch_Epayments_Model_Ingenico_Api_ClientInterface $ingenicoClient
     */
    private $ingenicoClient;

    /**
     * Netresearch_Epayments_Model_Ingenico_TestAccount constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->ingenicoClient = Mage::getModel('netresearch_epayments/ingenico_client');
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
