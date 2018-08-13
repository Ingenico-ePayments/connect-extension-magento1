"use strict";

var Loader = Class.create();

/**
 * View model for instantiating and removing loading indicators.
 *
 * @type {{templateId: string, template: {}, loaderClass: string, container: {}, visible: boolean, initialize:
 *     Loader.initialize, show: Loader.show, hide: Loader.hide, render: Loader.render}}
 */
Loader.prototype = {

    /**
     * @property {string}
     */
    templateId: 'ingenico_loading_indicator',

    /**
     * @property {HTMLElement}
     */
    template: {},

    /**
     * @property {string}
     */
    loaderClass: 'ingenico-please-wait',

    /**
     * @property {HTMLElement}
     */
    container: {},

    /**
     * @property {boolean}
     */
    visible: false,

    /**
     * @constructor
     * @param {HTMLElement} container
     * @param {string} text
     */
    initialize: function(container, text) {
        // use the parent element to be visible even if the container is hidden.
        this.container = container.parentElement;
        this.text = text;
        this.template = document.querySelector('#' + this.templateId);
    },

    show: function() {
        if (!this.visible) {
            this.render();
            this.visible = true;
        }
    },

    hide: function() {
        if (this.visible) {
            var loaderElement = this.container.querySelector('.' + this.loaderClass);
            this.container.removeChild(loaderElement);
            this.visible = false;
        }
    },

    /**
     * @private
     */
    render: function() {
        this.template.content.querySelector('.loading-text').textContent = this.text;
        this.template.content.querySelector('img').alt = this.text;
        this.template.content.querySelector('img').title = this.text;
        var clone = document.importNode(this.template.content, true);
        this.container.appendChild(clone);
    }
};
