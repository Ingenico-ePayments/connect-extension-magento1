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
 */

class Ingenico_Connect_Model_Config implements Ingenico_Connect_Model_ConfigInterface
{
    const CONFIG_INGENICO_GENERAL_TITLE = 'ingenico_epayments/general/title';

    const CONFIG_INGENICO_API_ENDPOINT = 'ingenico_epayments/settings/api_endpoint';
    const CONFIG_INGENICO_API_ENDPOINT_SECONDARY = 'ingenico_epayments/settings/api_endpoint_secondary';
    const CONFIG_INGENICO_WEBHOOKS_KEY_ID = 'ingenico_epayments/webhooks/key_id';
    const CONFIG_INGENICO_WEBHOOKS_SECRET_KEY = 'ingenico_epayments/webhooks/secret_key';
    const CONFIG_INGENICO_WEBHOOKS_KEY_ID_SECONDARY = 'ingenico_epayments/webhooks/key_id_secondary';
    const CONFIG_INGENICO_WEBHOOKS_SECRET_KEY_SECONDARY = 'ingenico_epayments/webhooks/secret_key_secondary';
    const CONFIG_INGENICO_API_KEY_ID = 'ingenico_epayments/settings/api_key';
    const CONFIG_INGENICO_API_SECRET = 'ingenico_epayments/settings/api_secret';
    const CONFIG_INGENICO_MERCHANT_ID = 'ingenico_epayments/settings/merchant_id';
    const CONFIG_INGENICO_FIXED_DESCRIPTOR = 'ingenico_epayments/settings/descriptor';
    const CONFIG_INGENICO_HOSTED_CHECKOUT_SUBDOMAIN = 'ingenico_epayments/settings/hosted_checkout_subdomain';
    const CONFIG_INGENICO_LOG_ALL_REQUESTS = 'ingenico_epayments/settings/log_all_requests';
    const CONFIG_INGENICO_LOG_ALL_REQUESTS_FILE = 'ingenico_epayments/settings/log_all_requests_file';
    const CONFIG_INGENICO_FRAUD_MANAGER_EMAIL = 'ingenico_epayments/fraud/manager_email';

    const CONFIG_INGENICO_METHODS_TOKEN = 'ingenico_epayments/payment_method_groups/TOKEN';
    const CONFIG_INGENICO_METHODS_BANKTRANSFER = 'ingenico_epayments/payment_method_groups/BANKTRANSFER';
    const CONFIG_INGENICO_METHODS_CARD = 'ingenico_epayments/payment_method_groups/CARD';
    const CONFIG_INGENICO_METHODS_CASH = 'ingenico_epayments/payment_method_groups/CASH';
    const CONFIG_INGENICO_METHODS_DIRECTDEBIT = 'ingenico_epayments/payment_method_groups/DIRECTDEBIT';
    const CONFIG_INGENICO_METHODS_EINVOICE = 'ingenico_epayments/payment_method_groups/EINVOICE';
    const CONFIG_INGENICO_METHODS_INVOICE = 'ingenico_epayments/payment_method_groups/INVOICE';
    const CONFIG_INGENICO_METHODS_REDIRECT = 'ingenico_epayments/payment_method_groups/REDIRECT';

    const CONFIG_INGENICO_UPDATE_EMAIL_TEMPLATE = 'ingenico_epayments/email_template';
    const CONFIG_INGENICO_UPDATE_LEGACY_EMAIL_TEMPLATE = 'ingenico_epayments/legacy_email_template';
    const CONFIG_INGENICO_FRAUD_EMAIL_TEMPLATE = 'ingenico_epayments/fraud/email_template';
    const CONFIG_INGENICO_FRAUD_LEGACY_EMAIL_TEMPLATE = 'ingenico_epayments/fraud/legacy_email_template';

    const CONFIG_INGENICO_PENDING_ORDERS_DAYS = 'ingenico_epayments/pending_orders_cancelation/days';
    const CONFIG_INGENICO_UPDATE_EMAIL = 'ingenico_epayments/email_settings/';
    const CONFIG_SALES_EMAIL_IDENTITY = 'sales_email/order/identity';
    const CONFIG_INGENICO_SFTP_ACTIVE = 'ingenico_epayments/sftp_settings/active';
    const CONFIG_INGENICO_SFTP_HOST = 'ingenico_epayments/sftp_settings/sftp_host';
    const CONFIG_INGENICO_SFTP_USERNAME = 'ingenico_epayments/sftp_settings/sftp_username';
    const CONFIG_INGENICO_SFTP_PASSWORD = 'ingenico_epayments/sftp_settings/sftp_password';
    const CONFIG_INGENICO_SFTP_REMOTE_PATH = 'ingenico_epayments/sftp_settings/sftp_remote_path';

    const CONFIG_INGENICO_CAPTURES_MODE = 'ingenico_epayments/captures/capture_mode';

    const CONFIG_INGENICO_CHECKOUT_TYPE = 'ingenico_epayments/checkout/inline_payments';
    const CONFIG_INGENICO_CHECKOUT_TYPE_HOSTED_CHECKOUT = '0';
    const CONFIG_INGENICO_CHECKOUT_TYPE_INLINE = '1';
    const CONFIG_INGENICO_CHECKOUT_TYPE_REDIRECT = '2';

    const CONFIG_INGENICO_HOSTED_CHECKOUT_VARIANT = 'ingenico_epayments/checkout/hosted_checkout_variant';
    const CONFIG_INGENICO_ACCOUNT_VERIFIED = 'ingenico_epayments/account_verified';
    const CONFIG_INGENICO_SYSTEM_PREFIX = 'ingenico_epayments/settings/system_prefix';

    /**
     * @param int $storeId
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_GENERAL_TITLE, $storeId);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/Ingenico_Connect/version');
    }

    /**
     * {@inheritDoc}
     */
    public function getApiKey($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_API_KEY_ID, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getApiSecret($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_API_SECRET, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getMerchantId($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_MERCHANT_ID, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getApiEndpoint($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_API_ENDPOINT, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getSecondaryApiEndpoint($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_API_ENDPOINT_SECONDARY, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCheckoutType($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_CHECKOUT_TYPE, $storeId);
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getHostedCheckoutVariant($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_HOSTED_CHECKOUT_VARIANT, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getCaptureMode($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_CAPTURES_MODE, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getWebhooksKeyId($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_WEBHOOKS_KEY_ID, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getWebhooksSecretKey($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_WEBHOOKS_SECRET_KEY, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getSecondaryWebhooksKeyId($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_WEBHOOKS_KEY_ID_SECONDARY, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getSecondaryWebhooksSecretKey($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_WEBHOOKS_SECRET_KEY_SECONDARY, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getFraudManagerEmail($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_FRAUD_MANAGER_EMAIL, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getProductGroupTitles($storeId = null)
    {
        return array(
            'token' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_TOKEN, $storeId),
            'bankTransfer' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_BANKTRANSFER, $storeId),
            'card' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_CARD, $storeId),
            'cash' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_CASH, $storeId),
            'directDebit' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_DIRECTDEBIT, $storeId),
            'eInvoice' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_EINVOICE, $storeId),
            'invoice' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_INVOICE, $storeId),
            'redirect' => Mage::getStoreConfig(self::CONFIG_INGENICO_METHODS_REDIRECT, $storeId),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFraudEmailSender($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_SALES_EMAIL_IDENTITY, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getFraudEmailTemplate($storeId = null)
    {
        $magentoVersion = Mage::getVersionInfo();
        if ($magentoVersion['major'] === '1' && $magentoVersion['minor'] === '9') {
            $result = Mage::getStoreConfig(self::CONFIG_INGENICO_FRAUD_EMAIL_TEMPLATE, $storeId);
        } else {
            $result = Mage::getStoreConfig(self::CONFIG_INGENICO_FRAUD_LEGACY_EMAIL_TEMPLATE, $storeId);
        }

        return $result;
    }
    /**
     * {@inheritDoc}
     */
    public function getUpdateEmailTemplate($storeId = null)
    {
        $magentoVersion = Mage::getVersionInfo();
        if ($magentoVersion['major'] === '1' && $magentoVersion['minor'] === '9') {
            $result = Mage::getStoreConfig(self::CONFIG_INGENICO_UPDATE_EMAIL_TEMPLATE, $storeId);
        } else {
            $result = Mage::getStoreConfig(self::CONFIG_INGENICO_UPDATE_LEGACY_EMAIL_TEMPLATE, $storeId);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescriptor($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_FIXED_DESCRIPTOR, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getHostedCheckoutSubdomain($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_HOSTED_CHECKOUT_SUBDOMAIN, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getPendingOrdersCancellationPeriod($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_PENDING_ORDERS_DAYS, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getLogAllRequests($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::CONFIG_INGENICO_LOG_ALL_REQUESTS, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getLogAllRequestsFile()
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_LOG_ALL_REQUESTS_FILE);
    }

    /**
     * (@inheritDoc}
     */
    public function getUpdateEmailEnabled($code)
    {
        return (bool)Mage::getStoreConfigFlag(self::CONFIG_INGENICO_UPDATE_EMAIL . $code);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdateEmailSender()
    {
        return Mage::getStoreConfig(self::CONFIG_SALES_EMAIL_IDENTITY);
    }

    /**
     * (@inheritDoc}
     */
    public function getShoppingCartExtensionName()
    {
        return 'M1.Connect';
    }

    /**
     * (@inheritDoc}
     */
    public function getIntegrator()
    {
        return 'Ingenico';
    }

    /**
     * (@inheritDoc}
     */
    public function getMagentoVersion()
    {
        return sprintf(
            'M%s %s',
            Mage::getVersion(),
            Mage::getEdition()
        );
    }

    /**
     * @inheritdoc
     */
    public function getSftpActive($storeId= null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_SFTP_ACTIVE, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getSftpHost($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_SFTP_HOST, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getSftpUsername($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_SFTP_USERNAME, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getSftpPassword($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_SFTP_PASSWORD, $storeId);
    }

    /**
     * (@inheritDoc}
     */
    public function getSftpRemotePath($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_SFTP_REMOTE_PATH, $storeId);
    }

    /**
     * @return bool
     */
    public function isAccountVerified()
    {
        return Mage::getStoreConfig(self::CONFIG_INGENICO_ACCOUNT_VERIFIED) !== '0';
    }

    /**
     * @param int $value
     */
    public function setAccountVerified($value)
    {
        Mage::getModel('core/config')->saveConfig(self::CONFIG_INGENICO_ACCOUNT_VERIFIED, $value);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isFullRedirect($storeId = null)
    {
        return $this->getCheckoutType($storeId) === self::CONFIG_INGENICO_CHECKOUT_TYPE_REDIRECT;
    }

    /**
     * @return string
     */
    public function getReferencePrefix()
    {
        return (string)Mage::getStoreConfig(self::CONFIG_INGENICO_SYSTEM_PREFIX);
    }
}
