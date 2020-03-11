# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 2.1.1 - 2020-03-11

### Changed

- Changed the server meta data that is sent to Ingenico to include Magento and module version.
- Fixes JavaScript error for inline payments for guest customers.
- Fixes `addFilterByCustomerId` error when using an old version of Magento 1.
- Dropped support for Magento Community versions prior to 1.9.3.6 (released on September 2017).
- Dropped support for Magento Enterprise versions prior to 1.14.3.6 (released on September 2017).

## 2.1.0 - 2020-01-21

### Changed

- In previous versions, the "pending payment" status was ambiguous: it could either mean that the customer is still in the payment process or that an action of the merchant is required (like a capture for example). From this version onward:
    - If an action is required by the customer the default order status will be "pending".
    - If an action is required by the merchant the default order status will be "pending payment".
- Added a default configuration settings for "number of days before cancellation" (set to 3).
- Stale orders will now be cancelled if their status is "pending" instead of "pending payment".
- Added a sanity check if "number of days before cancellation" is empty or below 1. 

### Fixed

- In case of a `REDIRECT` the order will now be set to "pending" instead of "processing". This goes for all redirect cases: challenges, hosted checkouts, payment methods that are redirect-based, etc.
- After a successful challenge the order will be set to "processing" for direct capture and "pending payment" for a delayed settlement.
- Previously, order amount paid and order amount due did not reflect the paid-status of the invoice / payment. This is now fixed.
- Previously, the invoice status did not reflect the payment status. This is now fixed.
    - A status of `PAID`, `CAPTURED` and `CAPTURE_REQUESTED` now mark an invoice as paid.

## 2.0.0 - 2019-09-11

###  Changed

- **BC Breaking:** the namespace of the module is changed from `Netresearch_Epayments` to `Ingenico_Connect`. See [the upgrade guide](UPGRADE.md) for more details what this means for you.
- Updated JavaScript Client SDK from `3.8.0` to `3.13.2`

### Added

- Added support for 3DSv2 by adding 18 properties to the payment request

### Fixed

- Use correct decryption method for EE
- In the payment request the shipping address took the street details from the billing address

## 1.5.1 - 2019-06-20

### Fixed

- No online capture possible for 3ds transactions

## 1.5.0 - 2019-05-20

### Added

- add support for status 935

## 1.4.0 - 2019-01-18

### Added
- automatic configuration validation against API when saving changed account settings
- basic sepa direct debit support
- full redirect checkout method, with payment product selection on Ingenico's HostedCheckout
- ability to configure custom HostedCheckout variant
- custom system identifier prefix for merchant reference

### Changed
- handling of CAPTURE_REQUESTED status for credit cards on GlobalCollect backend to allow orders to be shipped earlier
- webhook event handling now happens asynchronously

### Removed
- WX file retrieval

### Fixed
- compatibility issue with onestepcheckout.com's OneStepCheckout
- inline card payments with redirect not properly processed on return to shop
- customer gender is transmitted wrong format
- customer birth date is transmitted wrong format

## 1.3.2 - 2018-06-15

### Added
- check for webhook endpoint test to return a success response to the Webhook checker

### Fixed
- webhook event resolver using wrong order reference

## 1.3.1 - 2018-06-13

### Changed
- adjusted order item transmission to be compatible with more tax calculation settings

## 1.3.0 - 2018-05-30

### Added
- Compatibility with [OneStepCheckout](https://www.onestepcheckout.com/) in version 4.5.8

## 1.2.0 - 2018-04-20

### Added
- Javascript SDK integration to fetch payment products from client
- inline payment workflow to allow payment creation directly in checkout
- WX File polling to automatically poll the daily transaction report file in xml format and update the order status accordingly.

### Changed
- Update order information transmission to allow for the correct display of discounts and shipping amounts on the Hosted Checkout page
- Properly support handling for the AUTHORIZATION_REQUESTED status and all other previously not supported statuses.

### Fixed
- automatic order cancellation not picking up all necessary orders
- Some statuses from the server API can provide advanced information about the status' cause through an error list

## 1.1.1 - 2018-06/2018-07

### Fixed
- code style issues for Magento Marketplace

## 1.1.0 - 2018-06/2018-07

### Added
- option for direct sale workflow
- compatibility with Magento CE 1.8
- human readable information about order status to transaction information
- advanced refund functionality
- Webhooks endpoint verification
- end user documentation

### Changed
- improved update payment information button to be more robust
- improvements for cronjob operations

### Fixed
- an issue where 'Payment instructions' from a payment provider would corrupt the order
- payment fraud approval workflow

## 1.0.2 - 2018-06/2018-07
- Initial release
