"use strict";

var AliasView = Class.create();

/**
 * View model for the presentation of alias payment methods in checkout
 *
 * @type {{account: {}, product: {}, templateId: string, attributeTemplateId: string, initialize: AliasView.initialize,
 *     render: AliasView.render, updateFields: AliasView.updateFields}}
 */
AliasView.prototype = {
    /**
     * @property {AccountOnFile}
     */
    account: {},

    /**
     * @property {BasicPaymentProduct}
     */
    product: {},

    /**
     * @property {string}
     */
    templateId: 'ingenico_method_alias',

    /**
     * @property {string}
     */
    attributeTemplateId: 'ingenico_method_alias_attribute',

    /**
     * @constructor
     * @param {AccountOnFile} account
     * @param {BasicPaymentProduct} product - the object that the alias belongs to
     */
    initialize: function(account, product) {
        this.account = account;
        this.product = product;
        this.product.autoTokenized = true;
    },

    /**
     * Render payment alias with template into container.
     * @method
     */
    render: function(containerId) {
        var template = document.querySelector('#' + this.templateId);
        var attributeContainer = template.content.querySelector('.alias_attributes');
        var container = document.querySelector('#' + containerId);

        var labelAttributeKey = this.account.displayHints.labelTemplateElementByAttributeKey['alias'].attributeKey;
        var label = this.account.getMaskedValueByAttributeKey(labelAttributeKey).formattedValue;
        var id = 'payment_gc_payment_token_' + this.account.id;
        /** Fill out template */
        attributeContainer.id = 'ingenico_' + this.account.id + '_' + this.product.id + '_attribute_container';
        template.content.querySelector('input[type=radio]').value = this.account.paymentProductId;
        template.content.querySelector('input[type=radio]').id = id;
        template.content.querySelector('.alias_label').textContent = label;
        template.content.querySelector('label').htmlFor = id;
        if (this.product.displayHints.logo) {
            template.content.querySelector('.alias_logo').src = this.product.displayHints.logo;
        }

        /** Insert template into container */
        var clone = document.importNode(template.content, true);
        container.appendChild(clone);

        /** Render form fields for the payment product */
        var fieldView = new ProductFieldsView(this.product);
        fieldView.render(attributeContainer.id);
        this.updateFields(attributeContainer.id);

    },

    /**
     * Render alias data attributes into input form.
     *
     * @method
     * @private
     */
    updateFields: function(containerId) {
        var container = document.querySelector('#' + containerId);
        var inputs = container.querySelectorAll('input');

        for (var input of inputs) {
            var attribute = this.account.attributeByKey[input.dataset.fieldId];
            if (attribute) {
                var fieldConfig = this.product.paymentProductFieldById[input.dataset.fieldId];
                input.parentElement.classList.add('alias_attribute');
                input.value = fieldConfig.applyWildcardMask(attribute.value).formattedValue;
                input.dataset.fieldId = attribute.key;
                if (attribute.status === "READ_ONLY") {
                    input.readOnly = true;
                }
                if (attribute.status === "MUST_WRITE") {
                    input.required = true;
                    input.classList.add('required-entry');
                }
            }
        }
    }
};
