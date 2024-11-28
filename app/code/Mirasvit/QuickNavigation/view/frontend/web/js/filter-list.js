define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mst.quickNavFilterList', {
        $container: null,

        $scrollPosition: 0,

        _create: function () {
            this.$container = $('[data-element = container]', this.element);

            if (this.$container[0].scrollWidth > this.$container.width()) {
                this.element.addClass('_scrollable');
            }

            $('[data-element = prev]', this.element).on('click', function () {
                this.scrollToStart();
            }.bind(this));

            $('[data-element = next]', this.element).on('click', function () {
                this.scrollToEnd();
            }.bind(this));
        },

        scrollToStart: function () {
            this.scrollTo(this.calculateOffset('start'));
        },

        scrollToEnd: function () {
            this.scrollTo(this.calculateOffset('end'));
        },

        scrollTo: function (left) {
            this.$container.animate({scrollLeft: left}, 500);
        },

        calculateOffset: function (direction) {
            let step = this.$container.width();
            
            if (direction == 'start' && this.$scrollPosition > 0) {
                if (this.$scrollPosition > (this.$container[0].scrollWidth - step)) {
                    step += this.$scrollPosition - (this.$container[0].scrollWidth - step);
                }
                this.$scrollPosition = this.$scrollPosition - step;
            }
            if (direction == 'end' && this.$scrollPosition < (this.$container[0].scrollWidth - step)) {
                if (this.$scrollPosition < 0) {
                    this.$scrollPosition = 0;
                }
                this.$scrollPosition = this.$scrollPosition + step;
            }
            return this.$scrollPosition;
        }
    });

    return $.mst.quickNavFilterList;
});