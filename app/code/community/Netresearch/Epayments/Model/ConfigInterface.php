<?php

/**
 * Interface Netresearch_Epayments_Model_ConfigInterface
 */
interface Netresearch_Epayments_Model_ConfigInterface
{
    /**
     * @param int|null $storeId
     * @return string
     */
    public function getTitle($storeId = null);

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getApiKey($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getApiSecret($storeId = null);

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantId($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getApiEndpoint($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSecondaryApiEndpoint($storeId = null);

    /**
     * @param null $storeId
     * @return string
     */
    public function getCheckoutType($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getCaptureMode($storeId = null);

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getHostedCheckoutVariant($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getWebhooksKeyId($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getWebhooksSecretKey($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSecondaryWebhooksKeyId($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSecondaryWebhooksSecretKey($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getFraudManagerEmail($storeId = null);

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getProductGroupTitles($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getFraudEmailSender($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getFraudEmailTemplate($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getUpdateEmailTemplate($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getDescriptor($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getHostedCheckoutSubdomain($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getPendingOrdersCancellationPeriod($storeId = null);

    /**
     * @return bool
     */
    public function getLogAllRequests($storeId = null);

    /**
     * @return mixed
     */
    public function getLogAllRequestsFile();

    /**
     * @return mixed
     */
    public function getUpdateEmailSender();

    /**
     * @param $code
     *
     * @return bool
     */
    public function getUpdateEmailEnabled($code);

    /**
     * @return mixed
     */
    public function getIntegrator();

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSftpActive($storeId= null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSftpHost($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSftpUsername($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSftpPassword($storeId = null);

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSftpRemotePath($storeId = null);

    /**
     * @return bool
     */
    public function isAccountVerified();

    /**
     * @param int $value
     */
    public function setAccountVerified($value);

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isFullRedirect($storeId = null);

    /**
     * @return string
     */
    public function getReferencePrefix();
}
