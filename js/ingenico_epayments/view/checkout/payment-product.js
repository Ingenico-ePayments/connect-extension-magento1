"use strict";

var PaymentProductView = Class.create();


/**
 * View model for the presentation of payment products in checkout
 *
 * @type {{product: undefined, templateId: string, initialize: PaymentProductView.initialize, render:
 *     PaymentProductView.render}}
 */
PaymentProductView.prototype = {
    /**
     * @property {PaymentProduct}
     */
    product: {},

    /**
     * @property {string}
     */
    templateId: 'ingenico_method',

    /**
     * @constructor
     * @param {PaymentProduct} product
     */
    initialize: function(product) {
        this.product = product;
    },

    /**
     * Render payment product with template into container.
     * @method
     */
    render: function(containerId) {
        var template = document.querySelector('#' + this.templateId);
        var container = document.querySelector('#' + containerId);

        /** Fill out template */
        template.content.querySelector('input[type=radio]').value = this.product.id;
        template.content.querySelector('input[type=radio]').id = 'payment_gc_payment_product_' + this.product.id;
        template.content.querySelector('.product_input_label').htmlFor = 'payment_gc_payment_product_' + this.product.id;
        template.content.querySelector('.product_label').textContent = this.product.displayHints.label;
        template.content.querySelector('.product_fields').id = 'ingenico_' + this.product.id + '_fields_container';
        template.content.querySelector('img.product_logo').src = this.product.displayHints.logo + '?size=120x80';

        /** Insert template into container */
        var clone = document.importNode(template.content, true);
        container.appendChild(clone);
    },
};
