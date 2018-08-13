"use strict";

var HiddenInputView = Class.create();

/**
 * View model for hidden inputs that are submitted on place order.
 *
 * @type {{templateId: string, template: undefined, loaderClass: string,
 * container: undefined, visible: boolean, initialize: HiddenInputView.initialize,
 * show: HiddenInputView.show, hide: HiddenInputView.hide, render: HiddenInputView.render}}
 */
HiddenInputView.prototype = {

    /**
     * @property {string}
     */
    templateId: 'ingenico_hidden_input',

    /**
     * @property {HTMLElement}
     */
    template: {},

    /**
     * @property {HTMLElement}
     */
    container: '',

    /**
     * @constructor
     * @param {string} name
     * @param {string} value
     */
    initialize: function(name, value) {
        this.container = document.querySelector('#ingenico_groups_container');
        this.template = document.querySelector('#'+this.templateId);
        this.name = name;
        this.value = value;
    },

    render: function() {
        var clone = document.importNode(this.template.content, true);
        clone.querySelector('input').value = this.value;
        clone.querySelector('input').name = this.name;

        this.container.appendChild(clone);
    }
};
