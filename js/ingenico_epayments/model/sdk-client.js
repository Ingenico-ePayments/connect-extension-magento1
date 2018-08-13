"use strict";

var SdkClient = Class.create();

/**
 * A wrapper for the Javascript Ingenico Connect Client SDK
 *
 * @type {{connectSdk: connectsdk, session: null, initialize: SdkClient.initialize, initSession: SdkClient.initSession,
 *     getPaymentProduct: SdkClient.getPaymentProduct, getPaymentProducts: SdkClient.getPaymentProducts,
 *     getProductGroups: SdkClient.getProductGroups, getPaymentRequest: SdkClient.getPaymentRequest, getEncryptor:
 *     SdkClient.getEncryptor}}
 */
SdkClient.prototype = {
    /**
     * @property {connectsdk} – the global connectsdk object
     */
    connectSdk: {},

    /**
     * @property {Session} - Session object
     */
    session: {},

    /**
     * @constructor
     * @param {string} clientSessionId
     * @param {string} customerId
     * @param {string} assetsBaseUrl
     * @param {string} apiBaseUrl
     * @param {int} amount
     * @param {string} currency
     * @param {string} countryCode
     * @param {bool} isRecurring
     * @param {string} locale
     */
    initialize: function(clientSessionId, customerId, assetsBaseUrl, apiBaseUrl, amount, currency, countryCode, isRecurring, locale) {
        this.paymentDetails = {
            "totalAmount": amount,
            "currency": currency,
            "countryCode": countryCode,
            "isRecurring": isRecurring,
            "locale": locale
        };
        this.connectSdk = connectsdk;
        this.session = this.initSession(clientSessionId, customerId, assetsBaseUrl, apiBaseUrl);
    },

    /**
     * Initialize a new session
     *
     * @param clientSessionId
     * @param customerId
     * @param assetsBaseUrl
     * @param apiBaseUrl
     */
    initSession: function(clientSessionId, customerId, assetsBaseUrl, apiBaseUrl) {
        var sessionDetails = {
            "clientSessionID": clientSessionId,
            "customerId": customerId,
            "assetsBaseUrl": assetsBaseUrl,
            "apiBaseUrl": apiBaseUrl + "/v1",
        };

        return new this.connectSdk.Session(sessionDetails);
    },

    /**
     * Retrieve a payment product by id
     *
     * @param paymentProductId
     * @return Promise for Payment Product Response
     */
    getPaymentProduct: function(paymentProductId) {
        return this.session.getPaymentProduct(
            paymentProductId,
            this.paymentDetails
        );
    },

    /**
     * Retrieve all payment products
     *
     * @return Promise for PaymentProducts response
     */
    getPaymentProducts: function() {
        return this.session.getBasicPaymentProducts(this.paymentDetails);
    },

    /**
     * Retrieve product groups
     *
     * @return Promise for PaymentProductGroups response
     */
    getProductGroups: function() {
        return this.session.getBasicPaymentProductGroups(
            this.paymentDetails
        );
    },

    /**
     * @return {PaymentRequest}
     */
    getPaymentRequest: function() {
        return this.session.getPaymentRequest();
    },

    /**
     * @return {Encryptor}
     */
    getEncryptor: function() {
        return this.session.getEncryptor();
    }
};
