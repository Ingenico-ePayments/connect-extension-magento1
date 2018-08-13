"use strict";

var AccountsOnFileRepository = Class.create();

/**
 * Resource model for accounts on file
 *
 * @type {{accountsOnFile: Array, initialize: AccountsOnFileRepository.initialize, add: AccountsOnFileRepository.add, getById:
 *     AccountsOnFileRepository.getById, getByProductId: AccountsOnFileRepository.getByProductId}}
 */
AccountsOnFileRepository.prototype = {

    /**
     * @property {AccountOnFile[]}
     */
    accountsOnFile: null,

    /**
     * @constructor
     * @param {SdkClient} sdkClient
     */
    initialize: function (sdkClient) {
        this.sdkClient = sdkClient;
    },

    /**
     * @param {string} id
     * @return {boolean|AccountOnFile}
     */
    getById: async function (id) {
        if (this.accountsOnFile === null) {
            await this.reloadAccounts();
        }
        var result = false;
        for (var account of this.accountsOnFile) {
            if (account.id.toString() == id) {
                result = account;
            }
        }

        return result;
    },

    /**
     * @param {string} id
     * @return {boolean|AccountOnFile}
     */
    getByProductId: async function (id) {
        if (this.accountsOnFile === null) {
            await this.reloadAccounts();
        }
        var result = false;
        for (var account of this.accountsOnFile) {
            if (account.paymentProductId.toString() == id) {
                result = account;
            }
        }

        return result;
    },

    /**
     * @private
     * @returns {Promise<void>}
     */
    reloadAccounts: async function () {
        var paymentProducts = await this.sdkClient.getPaymentProducts();
        this.add(paymentProducts.accountsOnFile)
    },

    /**
     * @param {AccountOnFile|AccountOnFile[]} account
     */
    add: function (account) {
        if (this.accountsOnFile === null) {
            this.accountsOnFile = [];
        }
        if (Array.isArray(account)) {
            this.accountsOnFile = this.accountsOnFile.concat(account);
        } else {
            this.accountsOnFile.push(account);
        }
    }
};
