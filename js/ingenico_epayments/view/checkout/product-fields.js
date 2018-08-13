"use strict";

var ProductFieldsView = Class.create();

/**
 * View model for the presentation of inline payment product fields in checkout
 *
 * @type {{productId: undefined, fields: Array, textTemplateId: string, initialize: ProductFieldsView.initialize,
 *     render: ProductFieldsView.render}}
 */
ProductFieldsView.prototype = {
    /**
     * @property {PaymentProduct}
     */
    product: {},

    /**
     * @property {PaymentProductField[]}
     */
    fields: [],

    /**
     * @property {string}
     */
    textTemplateId: 'ingenico_inline_field',

    /**
     * @property {string}
     */
    selectTemplateId: 'ingenico_inline_select',

    /**
     * @property {string}
     */
    checkBoxTemplateId: 'ingenico_inline_checkbox',

    /**
     * @property {string}
     */
    toolTipTemplateId: 'ingenico_tooltip',



    /**
     * @constructor
     * @param product PaymentProduct
     */
    initialize: function(product) {
        this.product = product;
        this.fields = product.paymentProductFields;
    },

    /**
     * Render product fields with template into container.
     *
     * @method
     */
    render: function(containerId) {
        var container = document.querySelector('#' + containerId);
        for (var field of this.fields) {

            var fieldElement;
            if (field.displayHints.formElement.type === 'list') {
                fieldElement = this.buildSelectField(container, field);
            } else {
                fieldElement = this.buildTextField(container, field);
            }

            container.appendChild(fieldElement);

            if (field.displayHints.tooltip) {
                /**
                 * Add tool tip
                 */
                var toolTipTemplate = document.querySelector('#' + this.toolTipTemplateId);
                var toolTip = document.importNode(toolTipTemplate.content, true);
                toolTip.querySelector('.tool-tip-content img').src = field.displayHints.tooltip.image;
                toolTip.querySelector('.tool-tip-content p').textContent = field.displayHints.tooltip.label;
                container.appendChild(toolTip);
            }
        }
        if (this.product.allowsTokenization && !this.product.autoTokenized) {
            var tokenCheckbox = this.buildTokenCheckbox();
            container.appendChild(tokenCheckbox);
        }
    },

    buildTextField: function(container, field) {
        var template = document.querySelector('#' + this.textTemplateId);
        var id = field.id + '-' + this.product.id;
        /**
         * Get input type
         */
        var inputTypes = {
            'PhoneNumberKeyboard': 'tel',
            'IntegerKeyboard': 'text',
            'StringKeyboard': 'text',
            'EmailAddressKeyboard': 'email',
        };
        var type;
        if (field.displayHints.preferredInputType && inputTypes[field.displayHints.preferredInputType]) {
            type = inputTypes[field.displayHints.preferredInputType];
        } else if (field.displayHints.formElement.type) {
            type = field.displayHints.formElement.type;
        } else {
            type = 'text';
        }

        /**
         * Assembe autocomplete maps (only works over https)
         */
        var names = {
            'cardNumber': 'cardnumber',
            'expiryDate': 'exp-date',
            'cvv': 'cvc',
            'phoneNumber': 'phone',
        };
        var autocompletes = {
            'cardNumber': 'cc-number',
            'expiryDate': 'cc-exp',
            'cvv': 'cc-csc',
            'phoneNumber': 'tel',
        };

        /**
         * Create element from template
         */
        var fieldElement = document.importNode(template.content, true);
        var label = fieldElement.querySelector('label');
        var input = fieldElement.querySelector('input');

        label.htmlFor = id;
        label.innerHTML = field.displayHints.label;
        if (field.dataRestrictions.isRequired) {
            label.required = true;
            label.classList.add('required');
            input.classList.add('required-entry');
            label.innerHTML = '<em>*</em> ' + label.innerHTML;
        }
        input.type = type;
        input.id = id;
        input.title = field.displayHints.label;
        if (field.dataRestrictions.length) {
            input.maxLength = field.dataRestrictions.length.maxLength;
            input.minLength = field.dataRestrictions.length.minLength;
        }
        input.classList.add('input-' + type);
        input.placeholder = field.displayHints.placeholderLabel;
        input.name = names[field.id];
        input.dataset.fieldId = field.id;
        input.dataset.productId = this.product.id;
        input.autocomplete = autocompletes[field.id];

        return fieldElement;
    },

    buildSelectField: function(container, field) {
        var template = document.querySelector('#' + this.selectTemplateId);
        var id = field.id + '-' + this.product.id;
        /**
         * Create element from template
         */
        var fieldElement = document.importNode(template.content, true);
        var label = fieldElement.querySelector('label');
        var select = fieldElement.querySelector('select');

        label.htmlFor = id;
        label.innerHTML = field.displayHints.label;
        if (field.dataRestrictions.isRequired) {
            label.required = true;
            label.classList.add('required');
            select.classList.add('required-entry');
            label.innerHTML = '<em>*</em> ' + label.innerHTML;
        }
        select.id = id;
        select.title = field.displayHints.label;
        select.classList.add('input-select');
        select.placeholder = field.displayHints.placeholderLabel;
        select.name = field.id;
        select.dataset.fieldId = field.id;
        select.dataset.productId = this.product.id;
        select.innerHTML += '<option disabled selected value="">' + field.displayHints.placeholderLabel + '</option>';
        for (var item of field.displayHints.formElement.valueMapping) {
            select.innerHTML += '<option value="' + item.value + '">' + item.displayName + '</option>';
        }

        return fieldElement;
    },

    buildTokenCheckbox: function () {
        var template = document.querySelector('#' + this.checkBoxTemplateId);
        var id = 'tokenize_' + this.product.id;
        /**
         * Create element from template
         */
        var fieldElement = document.importNode(template.content, true);
        var label = fieldElement.querySelector('label');
        var checkbox = fieldElement.querySelector('input');

        label.htmlFor = id;
        label.innerHTML = 'Save for later';
        checkbox.id = id;
        checkbox.name = 'tokenization_requested_' + this.product.id;
        checkbox.dataset.productId = this.product.id;

        return fieldElement;
    }
};
