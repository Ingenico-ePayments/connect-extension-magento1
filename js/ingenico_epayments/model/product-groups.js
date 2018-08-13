"use strict";

var ProductGroupsModel = Class.create();

/**
 *
 * @type {{productGroupTitles: Array, initialize: ProductGroupsModel.initialize, build: ProductGroupsModel.build,
 *     sortGroups: ProductGroupsModel.sortGroups}}
 */
ProductGroupsModel.prototype = {

    /**
     * @property {string[]}
     */
    productGroupTitles: [],

    /**
     * @property {PaymentProductsRepository}
     */
    paymentProductsRepository: {},

    /**
     * @property {boolean}
     */
    showAliasMethod: true,

    /**
     * @constructor
     * @param {string[]} productGroupTitles
     * @param {PaymentProductsRepository} paymentProductsRepository
     */
    initialize: function(productGroupTitles, paymentProductsRepository) {
        this.productGroupTitles = productGroupTitles;
        this.paymentProductsRepository = paymentProductsRepository;
    },

    /**
     * @method
     * @param paymentProductsResponse
     * @return {ProductGroupView[]}
     */
    build: async function(paymentProductsResponse) {
        var itemsToGroups = {
            'token': []
        };
        var productGroupViews = [];

        /**
         * Collect token payment methods
         */
        if (this.showAliasMethod) {
            for (var account of paymentProductsResponse.accountsOnFile) {
                var product = await this.paymentProductsRepository.getById(account.paymentProductId);
                var aliasView = new AliasView(account, product);
                itemsToGroups['token'].push(aliasView);
            }
        }
        /**
         * Collect payment methods by group
         */
        for (var product of paymentProductsResponse.basicPaymentProducts) {
            if (!Array.isArray(itemsToGroups[product.paymentMethod])) {
                itemsToGroups[product.paymentMethod] = [];
            }
            var productView = new PaymentProductView(product);
            itemsToGroups[product.paymentMethod].push(productView);
        }
        for (var group in itemsToGroups) {
            if (itemsToGroups[group].length > 0) {
                var productGroup = new ProductGroupView(
                    itemsToGroups[group],
                    group,
                    this.productGroupTitles[group]
                );
                productGroupViews.push(productGroup);
            }
        }

        return this.sortGroups(productGroupViews);
    },

    /**
     * @method
     * @param {ProductGroupView[]} productGroups
     */
    sortGroups: function(productGroups) {
        return productGroups.sort(function(a, b) {
            /**
             * Sort tokens and cards first
             */
            if (b.id === 'token' || b.id === 'card') {
                return 1;
            }
            if (a.id === 'token' || a.id === 'card') {
                return -1;
            }
            if (a.id === 'token' && b.id === 'card') {
                return -1;
            }
            if (b.id === 'token' && a.id === 'card') {
                return 1;
            }
            return 0;
        });
    }
};
