"use strict";

var ProductFieldValidator = Class.create();

/**
 * Validator class that uses the SDKs validate and format functions and hooks into core's Validation.
 *
 * @type {{validationClass: string, validationMessage: string, productsRepository: {}, accountsRepository: {},
 *     initialize: ProductFieldValidator.initialize, format: ProductFieldValidator.format, validate:
 *     ProductFieldValidator.validate, getProductFieldforElement: ProductFieldValidator.getProductFieldforElement,
 *     isToken: ProductFieldValidator.isToken}}
 */
ProductFieldValidator.prototype = {

    /**
     * @property {string} The CSS class that inputs must have to be validated.
     */
    validationClass: 'validate-ingenico',

    /**
     * @property {string} The message that is displayed when validation fails.
     */
    validationMessage: 'Validation failed',

    /**
     * @property {PaymentProductsRepository}
     */
    productsRepository: {},

    /**
     * @property {AccountsOnFileRepository}
     */
    accountsRepository: {},

    /**
     * @constructor
     * @param {PaymentProductsRepository} productsRepository
     * @param {AccountsOnFileRepository} accountsRepository
     */
    initialize: async function(productsRepository, accountsRepository) {
        this.productsRepository = productsRepository;
        this.accountsRepository = accountsRepository;
        /**
         * Register validator with magento
         */
        Validation.add(
            this.validationClass,
            this.validationMessage,
            await this.validate.bind(this)
        );
    },

    /**
     * Format payment method input element value with Ingenico Client SDK.
     *
     * @param {string} value
     * @param {HTMLElement} element
     */
    format: async function(value, element) {
        var paymentProductField = await this.getProductFieldforElement(element);
        var mask = paymentProductField.applyMask(value);
        element.value = mask.formattedValue;
    },

    /**
     * Validate element with Ingenico Client SDK.
     *
     * @param {string} value
     * @param {HTMLElement} element
     * @return {boolean}
     */
    validate: async function(value, element) {
        if (!this.isToken(element)) {
            await this.format(value, element);
            var paymentProductField = await this.getProductFieldforElement(element);
            return paymentProductField.isValid(value);
        }
    },

    /**
     * Get PaymentProduct PaymentProductField object from radio button input.
     *
     * @private
     * @param {HTMLElement} element
     * @return {PaymentProductField}
     */
    getProductFieldforElement: async function(element) {
        var fieldId = element.dataset.fieldId;
        var productId = element.dataset.productId;
        var paymentProduct = await this.productsRepository.getById(productId);

        return paymentProduct.paymentProductFieldById[fieldId];
    },

    /**
     * @param {HTMLElement} input
     * @return {boolean}
     */
    isToken: function(input) {
        return input.parentElement.classList.contains('alias_attribute');
    }
};
