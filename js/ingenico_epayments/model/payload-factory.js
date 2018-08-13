"use strict";

var PayloadFactory = Class.create();

/**
 * Assembles and encrypts a payment request
 *
 * @type {{paymentRequest: undefined, encryptor: undefined, sdkClient: undefined, initialize:
 *     PayloadFactory.initialize, create: PayloadFactory.create}}
 */
PayloadFactory.prototype = {

    /**
     * @property {PaymentRequest}
     */
    paymentRequest: {},

    /**
     * @property {Encryptor}
     */
    encryptor: {},

    /**
     * @property {SdkClient}
     */
    sdkClient: {},

    /**
     * @constructor
     * @param {SdkClient} sdkClient
     */
    initialize: function(sdkClient) {
        this.sdkClient = sdkClient;
        this.encryptor = this.sdkClient.getEncryptor();
    },

    /**
     *
     * @param {object} data - available keys: 'cardNumber', 'expiryDate', 'cvv', 'phoneNumber', 'paymentProduct',
     *                        'accountOnFile'.
     * @return {Promise<string>|boolean}
     */
    create: async function(data) {
        this.paymentRequest = this.sdkClient.getPaymentRequest();
        if (data['paymentProduct']) {
            this.paymentRequest.setPaymentProduct(data['paymentProduct']);
            delete data['paymentProduct'];
        }
        if (data['accountOnFile']) {
            this.paymentRequest.setAccountOnFile(data['accountOnFile']);
            delete data['accountOnFile'];
        }
        for (var key in data) {
            this.paymentRequest.setValue(key, data[key]);
        }
        if (!this.paymentRequest.isValid()) {
            console.error('Error creating payment request, data not valid.',);
            return false;
        }
        try {
            var encryptedPayload = await this.encryptor.encrypt(this.paymentRequest);
        } catch (error) {
            console.error('Error encrypting payload.', error);
        }

        return encryptedPayload;
    },
};
