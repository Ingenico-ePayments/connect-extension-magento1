"use strict";

var GenericPaymentDataProcessor = Class.create();

/**
 * Handles the overriding of the payment.save method to process the Ingenico payment data.
 *
 * @type {{productIdInputName: string, productLabelInputName: string, paymentMethodInputName: string, paymentProduct:
 *     null, initialize: GenericPaymentDataProcessor.initialize, setPaymentProduct:
 *     GenericPaymentDataProcessor.setPaymentProduct, setAccountOnFile: GenericPaymentDataProcessor.setAccountOnFile,
 *     save: GenericPaymentDataProcessor.save, assemblePaymentInput: GenericPaymentDataProcessor.assemblePaymentInput}}
 */
GenericPaymentDataProcessor.prototype = {

    productIdInputName: 'payment[gc_payment_product_id]',

    productLabelInputName: 'payment[gc_payment_product_label]',

    paymentMethodInputName: 'payment[gc_payment_product_method]',

    /**
     * @property {PaymentProduct}
     * @protected
     */
    paymentProduct: null,

    /**
     * Wrap payment save function
     *
     * @constructor
     * @param {SdkClient} sdkClient
     */
    initialize: function(sdkClient) {
        this.sdkClient = sdkClient;
        this.payment = window.payment;
        this.payment.save = this.payment.save.wrap(this.save.bind(this));
    },

    /**
     * @method
     * @public
     * @param {PaymentProduct} product
     */
    setPaymentProduct: function(product) {
        this.paymentProduct = product;
    },

    /**
     * @method
     * @public
     * @param {AccountOnFile} account
     */
    setAccountOnFile: function(account) {
        this.accountOnFile = account;
    },

    /**
     * @method
     * @protected
     * @param $super
     */
    save: async function($super) {
        if (this.paymentProduct) {
            await this.assemblePaymentInput();
        }
        $super();
    },

    /**
     * Creates hidden inputs that submit the payment data to the Magento server.
     *
     * @method
     * @protected
     */
    assemblePaymentInput: async function() {
        /**
         * Build other hidden input data
         */
        var productId = this.paymentProduct.id;
        var productLabel = this.paymentProduct.displayHints.label;
        var paymentMethod = this.paymentProduct.paymentMethod;

        /**
         * Render other hidden inputs
         */
        var productIdView = new HiddenInputView(this.productIdInputName, productId);
        var productLabelView = new HiddenInputView(this.productLabelInputName, productLabel);
        var paymentMethodView = new HiddenInputView(this.paymentMethodInputName, paymentMethod);

        productIdView.render();
        productLabelView.render();
        paymentMethodView.render();
    },
};
