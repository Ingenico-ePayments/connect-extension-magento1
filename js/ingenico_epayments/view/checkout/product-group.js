"use strict";
var ProductGroupView = Class.create();

/**
 * Payment product group view model
 *
 * @type {{groupName: string, paymentProducts: Array, templateId: string, containerId: string, initialize:
 *     ProductGroupView.initialize, render: ProductGroupView.render}}
 */
ProductGroupView.prototype = {

    /**
     * @property {string}
     */
    groupName: '',

    /**
     * PaymentProduct[]
     */
    paymentProducts: [],

    /**
     * @property {string}
     */
    templateId: 'ingenico_group',

    /**
     * @property {string}
     */
    containerId: 'ingenico_groups_container',

    /**
     * @constructor
     * @param {PaymentProduct[]} paymentProducts
     * @param {string} id
     * @param {string} title
     */
    initialize: function(paymentProducts, id, title) {
        this.paymentProducts = paymentProducts;
        this.id = id;
        this.groupName = title;
    },

    /**
     * Render product group with template into container.
     * @method
     */
    render: function() {
        var template = document.querySelector('#' + this.templateId);
        var container = document.querySelector('#' + this.containerId);

        /** Fill out template */
        template.content.querySelector('.group_name').textContent = this.groupName;
        template.content.querySelector('.method_container').id = 'ingenico_' + this.id + '_method_container';

        /** Insert template into container */
        var clone = document.importNode(template.content, true);
        container.appendChild(clone);

        /** Render children */
        for (var product of this.paymentProducts) {
            product.render('ingenico_' + this.id + '_method_container');
        }
    },
};
