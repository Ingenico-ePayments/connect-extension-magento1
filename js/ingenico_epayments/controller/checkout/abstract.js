"use strict";

var AbstractCheckoutController = Class.create();

/**
 * Abstract Payment products controller
 *
 * @abstract
 * @type {{productGroupViews: Array, sdkClient: {}, productGroupsModel: {}, productsRepository: {}, accountsRepository: {}, paymentDataProcessor: {}, initialize: AbstractCheckoutController.initialize, handleProductSelect: AbstractCheckoutController.handleProductSelect, showProductGroups: AbstractCheckoutController.showProductGroups, doWithLoader: AbstractCheckoutController.doWithLoader}}
 */
AbstractCheckoutController.prototype = {

    /**
     * @property {ProductGroupView[]}
     */
    productGroupViews: [],

    /**
     * @property {SdkClient}
     */
    sdkClient: {},

    /**
     * @property {ProductGroupsModel}
     */
    productGroupsModel: {},

    /**
     * @property {PaymentProductsRepository}
     */
    productsRepository: {},

    /**
     * @property {AccountsOnFileRepository}
     */
    accountsRepository: {},

    /**
     * Hooks into the core save action and submits payment data from form fields.
     *
     * @property {PaymentDataProcessor}
     */
    paymentDataProcessor: {},

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
    initialize: function(
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
        this.sdkClient = new SdkClient(
            clientSessionId, customerId, assetUrl, apiUrl, amount, currency, country, recurring, locale
        );
        this.productGroupViews = [];
        this.productsRepository = new PaymentProductsRepository(this.sdkClient);
        this.accountsRepository = new AccountsOnFileRepository(this.sdkClient);
        this.productGroupsModel = new ProductGroupsModel(productGroupTitles, this.productsRepository);
    },

    /**
     * Main controller action. Load product groups and register product radio buttns.
     */
    execute: async function() {
        var loader = new Loader(
            document.querySelector('#' + 'ingenico_groups_container'),
            'Loading payment methods...'
        );
        await this.doWithLoader(loader, await this.showProductGroups.bind(this));

        /**
         * Handle product select event
         */
        var methodRadioButtons = document.querySelectorAll('.ingenico_payment_product_selector');
        for (var element of methodRadioButtons) {
            element.addEventListener('click', this.handleProductSelect.bind(this));
        }
    },

    /**
     * Handle selecting products or aliases.
     *
     * @protected
     * @param event
     */
    handleProductSelect: async function(event) {
        var input = event.target;
        var productId = input.value;

        if (input.hasClassName('product_selector')) {
            /**
             * A product was selected
             */
            var product = await this.productsRepository.getById(productId);
            var account = null;
        } else if (input.hasClassName('alias_selector')) {
            /**
             * An alias was selected
             */
            var account = await this.accountsRepository.getByProductId(productId);
            var product = await this.productsRepository.getById(account.paymentProductId);
        }
        /**
         * Add current payment product to payment data processor.
         */
        this.paymentDataProcessor.setPaymentProduct(product);
        this.paymentDataProcessor.setAccountOnFile(account);
    },

    /**
     * Load and render product groups
     *
     * @protected
     * @return {void}
     */
    showProductGroups: async function() {
        /**
         * This part is not yet optimal, it would be better to let a repository do the sdkClient call.
         */
        var productsResponse = await this.sdkClient.getPaymentProducts();
        this.accountsRepository.add(productsResponse.accountsOnFile);
        this.productGroupViews = await this.productGroupsModel.build(productsResponse);
        for (var view of this.productGroupViews) {
            view.render();
        }
    },

    /**
     * Wrap an action with a loader.
     *
     * @protected
     * @param {Loader} loader
     * @param {function} action
     * @param {*} argument
     * @param {*} argument2
     * @returns {Promise<void>}
     */
    doWithLoader: async function(loader, action, argument, argument2) {
        loader.show();
        try {
            await action(argument, argument2)
        } catch (error) {
            console.error(error);
        } finally {
            loader.hide();
        }
    }
};
