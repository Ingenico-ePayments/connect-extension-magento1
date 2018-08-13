"use strict";

/**
 * @type {AbstractCheckoutController}
 */
var InlineController = Class.create(AbstractCheckoutController, {
    /**
     * @property {ProductFieldValidator}
     */
    productFieldValidator: {},

    /**
     * @property {Tooltips}
     */
    tooltips: {},

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

        this.productFieldValidator = await new ProductFieldValidator(this.productsRepository, this.accountsRepository);
        this.paymentDataProcessor = new InlinePaymentDataProcessor(this.sdkClient, this.productFieldValidator);
        this.tooltips = new Tooltips();
    },

    /**
     * Load and render the product fields for a product.
     *
     * @private
     * @param {int} productId
     * @param {HTMLElement} input - radio button to which product fields belong.
     */
    loadProductFields: async function(productId, input) {
        var product = await this.productsRepository.getById(productId);
        var fieldsView = new ProductFieldsView(product);
        var containerId = 'ingenico_' + productId + '_fields_container';

        fieldsView.render(containerId);
        /** Init tooltip logic */
        this.tooltips.registerEvents();
        /** Reformat product fields on input */
        var productFields = input.parentElement.querySelectorAll('.ingenico_field input');
        for (var fieldInput of productFields) {
            fieldInput.addEventListener('input', function(event) {
                this.productFieldValidator.format(event.target.value, event.target);
            }.bind(this));
        }
    },

    /**
     * Handle selecting products or aliases. Retrieves and/or shows additional fields.
     *
     * @protected
     * @param event
     */
    handleProductSelect: async function(event) {
        var input = event.target;
        var productId = input.value;

        /**
         * Hide all fields
         */
        var fieldsContainers = document.querySelectorAll('.product_fields, .alias_attributes');
        for (var element of fieldsContainers) {
            element.style.display = "none";
        }
        /**
         * Show fields for selected
         */
        var fieldsContainer = input.parentElement.querySelector('.product_fields, .alias_attributes');
        fieldsContainer.removeAttribute('style');
        if (input.hasClassName('product_selector')) {
            if (!fieldsContainer.querySelector('.ingenico_field')) {
                var loader = new Loader(fieldsContainer, 'Loading details...');
                await this.doWithLoader(loader, await this.loadProductFields.bind(this), productId, input);
            }
        }

        await AbstractCheckoutController.prototype.handleProductSelect.call(this, event);
    },
});
