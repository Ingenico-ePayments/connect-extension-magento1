"use strict";

var Tooltips = Class.create();

/**
 * Register buttons for showing and hiding tool tips.
 *
 * @type {{openClass: string, tooltipClass: string, closeClass: string,
 * initialize: Tooltips.initialize, registerEvents: Tooltips.registerEvents,
 * open: Tooltips.open, close: Tooltips.close}}
 */
Tooltips.prototype = {
    /**
     * @property {string}
     */
    tooltipClass: 'tooltip',

    /**
     * @property {string}
     */
    openClass: 'tooltip-open',

    /**
     * @property {string}
     */
    closeClass: 'tooltip-close',

    /**
     * @constructor
     */
    initialize: function() {},

    /**
     * (Re-)apply bindings to all tooltip buttons on the page.
     */
    registerEvents: function() {
        var buttons = document.querySelectorAll('.' + this.openClass);
        for (var button of buttons) {
            button.addEventListener('click', this.open.bind(this));
        }

        var closeButtons = document.querySelectorAll('.' + this.closeClass);
        for (var button of closeButtons) {
            button.addEventListener('click', this.close.bind(this));
        }
    },

    open: function (event) {
        event.preventDefault();
        var container = event.target.parentElement.parentElement;
        container.querySelector('.' + this.openClass).style.display = 'none';
        container.querySelector('.' + this.tooltipClass).removeAttribute('style');
    },

    close: function (event) {
        event.preventDefault();
        var container = event.target.parentElement.parentElement.parentElement;
        container.querySelector('.' + this.tooltipClass).style.display = 'none';
        container.querySelector('.' + this.openClass).removeAttribute('style');
    },
};
