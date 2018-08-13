<?php

/**
 * Class Client
 * @package Netresearch\Epayments\WxTransfer
 */
class Netresearch_Epayments_Model_WxTransfer_Client implements Netresearch_Epayments_Model_WxTransfer_ClientInterface
{
    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    private $epaymentConfig;

    /**
     * @var \Netresearch_Epayments_Model_WxTransfer_Sftp_ClientInterface
     */
    private $sftpClient;

    /**
     * @var \Mage_Core_Model_Store
     */
    private $storeConfig;

    /**
     * @var string
     */
    private $pattern;

    public function __construct()
    {
        $this->epaymentConfig = Mage::getModel('netresearch_epayments/config');
        $this->sftpClient = Mage::getModel('netresearch_epayments/wxTransfer_sftp_client');
        $this->storeConfig = Mage::getModel('core/store');
    }

    /**
     * @param string $date
     * @param int $scopeId
     * @return \DOMDocument|false
     * @throws Mage_Core_Exception
     */
    public function loadDailyWx($date, $scopeId)
    {
        if (!$this->epaymentConfig->getSftpActive($scopeId)) {
            return false;
        }
        $this->init($scopeId);

        $timeString = date('Ymd', strtotime($date));
        if ($timeString === false) {
            Mage::throwException("The string '{$date}' could not be parsed into a date.");
        }

        $baseCurrency = $this->storeConfig->getBaseCurrencyCode();

        $this->pattern = sprintf(
            self::WX_FILE_PATTERN,
            $this->epaymentConfig->getMerchantId($scopeId),
            $timeString,
            $baseCurrency
        );

        $fileList = $this->sftpClient->getFileCollection(
            $this->pattern,
            $this->epaymentConfig->getSftpRemotePath($scopeId)
        );

        if (empty($fileList)) {
            return false;
        }

        $fileToLoad = $this->determineLatestVersion($fileList);

        $response = $this->sftpClient->loadFile($fileToLoad);

        $this->sftpClient->disconnect();

        return $this->parseResponse($response);
    }

    /**
     * Initialize transfer client
     *
     * @param int|null $scopeId
     * @throws Mage_Core_Exception
     */
    private function init($scopeId = null)
    {
        $host = $this->epaymentConfig->getSftpHost($scopeId);
        $user = $this->epaymentConfig->getSftpUsername($scopeId);
        $password = $this->epaymentConfig->getSftpPassword($scopeId);
        try {
            $this->sftpClient->connect($host, $user, $password);
        } catch (\Exception $exception) {
            Mage::throwException($exception->getMessage());
        }
    }

    /**
     * Checks the list of matching files for the latest version and returns the corresponding filename
     *
     * @param $fileList
     * @return string filename to load
     */
    private function determineLatestVersion($fileList)
    {
        // append additional meta data (version) to the file metadata
        $pattern = $this->pattern;
        array_walk(
            $fileList,
            function (&$value, $key) use ($pattern) {
                $matches = array();
                preg_match($pattern, $key, $matches);
                $value = array_merge($value, $matches);
                $value['filename'] = $key;
            }
        );

        $latestVersion = array_reduce(
            $fileList,
            function ($carry, $item) {
                if ($carry['version'] < $item['version']) {
                    return $item;
                }
                return $carry;
            },
            array('version' => '0')
        );

        return $latestVersion['filename'];
    }

    /**
     * @param $response
     * @return \DOMDocument
     * @throws Mage_Core_Exception
     */
    private function parseResponse($response)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        if (!$dom->loadXML($response, LIBXML_PARSEHUGE)) {
            Mage::throwException('Could not load response XML.');
        }
        return $dom;
    }

}
