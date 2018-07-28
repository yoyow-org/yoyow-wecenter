/*
SliderJS - jQuery Slider with CSS Transitions
*/
var supports = (function () {
    'use strict';
    var style   = document.createElement('div').style,
        vendors = ['', 'Moz', 'Webkit', 'Khtml', 'O', 'ms'],
        prefix, i, l;

    return function (prop) {
        if (typeof style[prop] === 'string') {
            return true;
        }

        prop = prop.replace(/^[a-z]/, function (val) {
            return val.toUpperCase();
        });

        for (i = 0, l = vendors.length; i < l; i += 1) {
            prefix = vendors[i] + prop;
            if (typeof style[prefix] === 'string') {
                return true;
            }
        }
        return false;
    };
})();

var Slider = (function ($) {
    'use strict';
    /*global jQuery, setTimeout, clearTimeout*/
    var module = {
        npos: 0,
        timer: null,
        config: function (config) {
            module.target    = config.target;
            module.container = module.target.find('.slider-wrapper');
            module.sWidth    = module.container.find('.slide').outerWidth(true);
            module.max       = module.container.find('.slide').length;
            module.tWidth    = module.sWidth * module.max;
            module.time      = config.time || 5000;
        },
        early: function () {
            var self   = this,
                slider = self.target,
                i, l;

            self.container.css({ width: self.tWidth });

            for (i = 0, l = self.max; i < l; i += 1) {
                self.items(i + 1).insertBefore($('.slider-nav .next').parents('li'));
            }

            slider.find('.bullet:first').addClass('active');
        },
        events: function () {
            var self   = this,
                slider = self.target;

            self.container.on({
                mouseenter: function () {
                    clearTimeout(self.timer);
                },
                mouseleave: function () {
                    module.auto();
                }
            });
        },
        slip: function () {
            if (supports('transition')) {
                module.container.css({ left: -module.npos * module.sWidth });
            } else {
                module.container.animate({ left: -module.npos * module.sWidth }, 800);
            }
        },
        bullets: function (index) {
            clearTimeout(module.timer);
            module.auto();

            module.npos = parseInt(index, null) - 1;
            module.slip();
        },
        next: function () {
            clearTimeout(module.timer);
            module.auto();

            module.npos += 1;

            if (module.npos > (module.max - 1)) {
                module.npos = 0;
            }

            module.slip();
            module.update();
        },
        update: function () {
            var self = this,
                slider = self.target;

            slider.find('.bullet').removeClass('active');
            slider.find('.bullet').eq(self.npos).addClass('active');
        },
        auto: function () {
            var self = this;

            self.timer = setTimeout(self.next, self.time);
        },
        items: function (i) {
            var item = $('');
            return item;
        },
        init: function (config) {
            module.config(config);

            if (!module.max || module.max === 1) {
                return;
            }

            module.auto();
            module.events();
            module.early();
        }
    };

    return {
        init: module.init
    };

}(jQuery));