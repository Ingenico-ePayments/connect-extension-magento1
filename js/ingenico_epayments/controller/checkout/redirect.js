"use strict";

/**
 *
 * @type {AbstractCheckoutController}
 */
var RedirectController = Class.create(AbstractCheckoutController, {
    /**
     * @constructor
     * @param {string} clientSessionId
     * @param {string} customerId
     * @param {string} assetUrl
     * @param {string} apiUrl
     * @param {int} amount
     * @param {string} currency
     * @param {string} country
     * @param {bool} recurring
     * @param {string} locale
     * @param {array} productGroupTitles - set by the Magento configuration
     */
    initialize: async function(
        clientSessionId,
        customerId,
        assetUrl,
        apiUrl,
        amount,
        currency,
        country,
        recurring,
        locale,
        productGroupTitles
    ) {
        /**
         * Call parent constructor
         */
        await AbstractCheckoutController.prototype.initialize.call(
            this,
            clientSessionId,
            customerId,
            assetUrl,
            apiUrl,
            amount,
            currency,
            country,
            recurring,
            locale,
            productGroupTitles
        );
        this.productGroupsModel.showAliasMethod = false;
        this.paymentDataProcessor = new RedirectPaymentDataProcessor(this.sdkClient);
    },
});
