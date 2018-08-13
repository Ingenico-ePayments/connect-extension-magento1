"use strict";

/**
 * @type {GenericPaymentDataProcessor}
 */
var InlinePaymentDataProcessor = Class.create(GenericPaymentDataProcessor, {

    payloadInputName: 'payment[gc_payment_client_payload]',

    tokenizeInputName: 'payment[gc_payment_product_tokenize]',

    /**
     * Wrap payment save function
     *
     * @constructor
     * @param {SdkClient} sdkClient
     * @param {ProductFieldValidator} validator
     */
    initialize: function(sdkClient, validator) {
        this.sdkClient = sdkClient;
        this.payment = window.payment;
        this.validator = validator;
        this.payment.save = this.payment.save.wrap(this.save.bind(this));
    },

    /**
     * @method
     * @protected
     * @param $super
     */
    save: async function($super) {
        if (this.paymentProduct && this.validateFieldInputs()) {
            await this.assemblePaymentInput();
            this.disableFieldInputs();
        }
        $super();
    },

    /**
     * @method
     * @private
     * @return {boolean}
     */
    validateFieldInputs: function() {
        var isValid = true;
        var inputs = document.querySelectorAll('.ingenico_field input, .ingenico_field select');
        for (var input of inputs) {
            if (this.isVisible(input)) {
                if (this.validator.validate(input.value, input) === false) {
                    isValid = false;
                }
            }
        }

        return isValid;
    },

    /**
     * Disables all fields input so that no unencrypted payment data is transmitted to the server.
     *
     * @method
     * @private
     */
    disableFieldInputs: function() {
        var inputs = document.querySelectorAll('.ingenico_field input, .ingenico_field select');
        for (var input of inputs) {
            input.disabled = true;
        }
    },

    /**
     * Creates hidden inputs that submit the payment data to the Magento server.
     *
     * @method
     * @protected
     */
    assemblePaymentInput: async function() {
        /**
         * Collect payload data
         */
        var data = {};
        var hasPayload = false;
        var inputs = document.querySelectorAll('.ingenico_field input, .ingenico_field select');
        for (var input of inputs) {
            if (this.isVisible(input)) {
                /**
                 * Only submit non-token fields or token fields that are writeable.
                 */
                /**
                 * Attribute data submission is not possible right now and is therefore disabled.
                 * AccountOnFile::getMaskedValueByAttributeKey() has no masks to apply to the input values.
                 * Unmasked values can not be submitted in the PaymentRequest.
                 */
                if (!this.validator.isToken(input) || input.readOnly === false) {
                    data[input.dataset.fieldId] = input.value;
                    hasPayload = true;
                }
            }
        }

        /**
         * Handle payload data
         */
        if (hasPayload) {
            data['paymentProduct'] = this.paymentProduct;
            if (this.accountOnFile) {
                data['accountOnFile'] = this.accountOnFile;
            }
            var payloadFactory = new PayloadFactory(this.sdkClient);
            var payload = await payloadFactory.create(data);
            var payloadView = new HiddenInputView(this.payloadInputName, payload);
            payloadView.render();
        }

        /**
         * Handle token data
         */
        var tokenizeData = this.useTokenization(this.paymentProduct.id);
        var tokenizeView = new HiddenInputView(this.tokenizeInputName, tokenizeData);
        tokenizeView.render();

        await GenericPaymentDataProcessor.prototype.assemblePaymentInput.call(this);
    },

    /**
     * Check if input element is visible and has a value
     *
     * @method
     * @private
     * @param {HTMLElement} input
     * @return {boolean}
     */
    isVisible: function(input) {
        if (input.offsetWidth !== 0 &&
            input.offsetHeight !== 0 &&
            input.value) {
            return true;
        }
        return false;
    },

    /**
     * Determine whether tokenisation should be used for the selected payment
     *
     * @method
     * @private
     * @param {int} productId
     * @return {boolean}
     */
    useTokenization: function(productId) {
        var tokenInput = document.querySelector('#tokenize_' + productId);
        if (!tokenInput || !this.paymentProduct.allowsTokenization) {
            return false;
        }
        return tokenInput.checked;
    }
});
