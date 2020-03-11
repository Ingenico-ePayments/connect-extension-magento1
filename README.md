# Ingenico ePayments Connect Extension for Magento 1

Payment extension for processing the Magento order workflow via the Ingenico ePayments Connect API

## Requirements

To use this extension you need to have an Ingenico ePayments account.

## Compatibility

This extension is compatible with the following versions of Magento:

- **CE / Open Source**: 1.9.3.6 and upward
- **EE / Commerce**: 1.14.3.6 and upward

This extension is compatible with PHP 5.6, 7.0, 7.1 and 7.2.

## Installation Instructions

### Install the extension

The extension can be installed via Composer (preferred way) or manually.

#### Installation via Composer

Installation via Composer requires the [Magento Composer Installer](https://github.com/Cotya/magento-composer-installer) to be in place.

Add the repository to your `composer.json` by running the following command:

    composer config repositories.ingenico_connect git https://github.com/Ingenico-ePayments/connect-extension-magento1.git

Add the required Composer module:

    composer require ingenico-epayments/connect-extension-magento1
    
After this, log out from the Magento® 1 admin panel, and log in again, and clear the Magento caches.

Proceed with the extension configuration.

#### Manual installation

Unzip the package contents into your Magento root directory.

Verify that the extension files have been copied into this directory:

    app/code/community/Ingenico/Connect

After this, log out from the Magento® 1 admin panel, and log in again, and clear the Magento caches.

Proceed with the extension configuration.

### Configure the extension

The extension adds a new configuration tab which must be configured to process payments with Ingenico ePayments:

    System → Configuration → Sales → Ingenico ePayments
    
Please review and/or update the following settings:

* Section `Account Settings`:
  * `Enabled`: `Yes` 
  * `API Endpoint` and `API Endpoint (Secondary)`: see the [API endpoints reference](https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/endpoints.html).
  * `API Key`, `API Secret` and `MID (Merchant ID)`: see the Configuration Center.
  * `Hosted Checkout Subdomain`: by default, this is `'https://payment.`; if needed, this can be configured on Configuration Center.
* Section `Checkout Settings`: this lets you configure how the payment products are displayed and where the payment data is going to be entered by the customer. The following checkout types are available:
  * `Payment products and input fields on Hosted Checkout`: Display available payment products with input fields on the Hosted Checkout page (external payment site) after placing the order.
  * `Payment products in Magento checkout, input fields on Hosted Checkout`: Display payment products (except input fields) directly in the Magento checkout. The customer enters the payment data afterwards on the Hosted Checkout page after placing the order.
  * `Payment products and input fields in Magento checkout (inline)`: Display payment products with input fields directly in the Magento® 2 checkout (no redirection to external payment page, except for 3D Secure etc.). Important: using inline payments requires your Magento store to be PCI compliant on level SAQ-A EP.
* Section `Webhooks`:
  * `Key ID` and `Secret Key`: see the Configuration Center, from which the webhooks keys can be requested.
  * From the Configuration Center, the webhooks should be configured by adding endpoints:
    * Create a `payment` endpoint with endpoint url https://shop.domain/epayments/webhooks/payment and selected events starting with "payment."
    * Create a `refund` endpoint with endpoint url https://shop.domain/epayments/webhooks/refund and selected events starting with "refund." 
* Section `Cancellation of Pending Orders`:
  * `Number of days before cancellation`: allows specification of the number of numbers after which abandoned carts should be cancelled (requires a working cron).
* Section `Fraud notification`: 
  * `Manager Email`: destination email address for notifications when the Ingenico ePayments Fraud Detection is triggered.

Please make sure that all fields in `Account Settings` and `Fraud Notification` are configured before saving the configuration.

After saving the configuration, the Magento caches need to be flushed.

### Upgrade instructions

If you are upgrading from a version prior to 2.0.0, please read the [upgrade instructions](UPGRADE.md).

## Support

In case of questions or problems, you can contact the Ingenico support team: <https://www.ingenico.com/epayments/support>

## License

Please refer to the included [LICENSE.txt](LICENSE.txt) file.

## Copyright

(c) 2019 Ingenico eCommerce Solutions Bvba
