/*
* jQuery toolTip
* Copyright Bidjunction LLC, 2013
* Dual licensed under the MIT and GPL licenses.
*/
(function($){
    var defaults = {
        'class'    : '', // Css class(es) to add to tooltip (along with standardToolTip).
        'mouseMove': true, // A flag indicating whether to move tooltip with mouse.
        'speed'    : 'fast', // The speed at which to fade in the tool tip.
        'delay'    : 250, // Delay (in ms) before opening the popup.
        'xOffset'  : 20,
        'yOffset'  : 10
    };

    var methods = {
        init : function (options) {
            // Create settings using the defaults extended with any options provided.
            var settings = $.extend({}, defaults, options);

            return this.each(function () {
                var timeoutID,
                toolTip,
                $t=$(this);

                $t.data('toolTipTitle', $t.attr('title'))
                .removeAttr('title')  // Remove the title so that it doesn't show on hover.
                .hover(function(e) {
                    if(!$t.hasClass('toolToolActive')) {
                        timeoutID = window.setTimeout(function () {
                            // Create a div to be the tooltip pop up, add the styling as well as
                            // the html (from the display function) to it and then fade the element in
                            // using the speed specified in the settings.
                            toolTip = $('<div />')
                            .addClass('standardToolTip ' + settings['class'])
                            .html($t.data('toolTipTitle'))
                            .css('top', (e.pageY - settings.yOffset) + 'px')
                            .css('left', (e.pageX + settings.xOffset) + 'px')
                            .css('position', 'absolute')
                            .appendTo('body')
                            .fadeIn(settings.speed);

                            $t.addClass('toolToolActive');

                            }, settings.delay);
                    }
                    },
                    function () {
                        window.clearTimeout(timeoutID);
                        if ($t.hasClass('toolToolActive')) {
                            toolTip.remove();
                            $t.removeClass('toolToolActive');
                        }
                });

                $t.mousemove(function (e) {
                    if (settings.mouseMove && $t.hasClass('toolToolActive')) {
                        toolTip.css('top', (e.pageY - settings.yOffset) + 'px')
                        .css('left', (e.pageX + settings.xOffset) + 'px');
                    }
                });
            });
        },
        change : function (title) {
            return this.each(function () {
                $(this).data('toolTipTitle', title);
            });
        },
        destroy : function () {
            return this.each(function () {});
        },
    };

    $.fn.toolTip = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.toolTip');
        }
    };
    }(jQuery)
);