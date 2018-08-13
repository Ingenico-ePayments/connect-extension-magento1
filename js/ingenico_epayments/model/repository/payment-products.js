"use strict";

var PaymentProductsRepository = Class.create();

/**
 * Resource model for full PaymentProduct objects
 *
 * @type {{paymentProducts: Array, initialize: PaymentProductsRepository.initialize, add:
 *     PaymentProductsRepository.addProduct, getById: PaymentProductsRepository.getById}}
 */
PaymentProductsRepository.prototype = {

    /**
     * @property {PaymentProduct[]}
     */
    paymentProducts: [],

    /**
     * @constructor
     * @param {SdkClient} sdkClient
     */
    initialize: function (sdkClient) {
        this.sdkClient = sdkClient;
    },

    /**
     *
     * @param {PaymentProduct} product
     */
    add: function (product) {
        this.paymentProducts.push(product);
    },

    /**
     *
     * @param {string} id
     * @return {PaymentProduct|boolean}
     */
    getById: async function (id) {
        var result = false;
        for (var product of this.paymentProducts) {
            if (product.id.toString() == id) {
                result = product;
            }
        }
        if (!result) {
            try {
                result = await this.sdkClient.getPaymentProduct(id);
                this.add(result)
            } catch (error) {
                console.error('Could not load payment product with id ' + id, error)
            }
        }

        return result;
    }
};
