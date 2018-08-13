# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2018-06/2018-07
### Added

### Changed

### Deprecated

### Removed

### Fixed
- webhook event resolver using wrong order reference
### Security

## [1.3.2] - 2018-06-15
### Added
- check for webhook endpoint test to return a success response to the Webhook checker
### Fixed
- webhook event resolver using wrong order reference
## [1.3.1] - 2018-06-13
### Changed
- adjusted order item transmission to be compatible with more tax calculation settings
## [1.3.0] - 2018-05-30
### Added
- Compatibility with [OneStepCheckout](https://www.onestepcheckout.com/) in version 4.5.8

## [1.2.0] - 2018-04-20
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

## [1.1.1] - 2018-06/2018-07
### Fixed
- code style issues for Magento Marketplace

## [1.1.0] - 2018-06/2018-07
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
### Deprecated

### Removed

### Fixed
- an issue where 'Payment instructions' from a payment provider would corrupt the order
- payment fraud approval workflow
### Security

## 1.0.2 - 2018-06/2018-07
- Initial release


[Unreleased]: https://git.netresearch.de/ingenico/connect/module-epayments-m1/compare/1.3.0...develop
[1.3.0]: https://git.netresearch.de/ingenico/connect/module-epayments-m1/compare/1.2.0...1.3.0
[1.2.0]: https://git.netresearch.de/ingenico/connect/module-epayments-m1/compare/1.1.1...1.2.0
[1.1.1]: https://git.netresearch.de/ingenico/connect/module-epayments-m1/compare/1.1.0...1.1.1
[1.1.0]: https://git.netresearch.de/ingenico/connect/module-epayments-m1/compare/1.0.2...1.1.0