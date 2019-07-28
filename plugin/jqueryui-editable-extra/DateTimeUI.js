(function ($) {
    var formatTime = function (value, trimMidnightTime) {
        if (!value) {
            return '';
        }

        var resTime = {
            hour: value.getHours(),
            minute: value.getMinutes()
        };

        if (!resTime.hour && !resTime.minute) {
            return trimMidnightTime ? '' : ' 12:00 AM';
        }

        return ' ' + $.datepicker.formatTime('hh:mm TT', resTime);
    };

    var DateTimeUI = function (options) {
        this.init('datetimeui', options, DateTimeUI.defaults);
        this.initPicker(options, DateTimeUI.defaults);
    };

    $.fn.editableutils.inherit(DateTimeUI, $.fn.editabletypes.abstractinput);

    $.extend(DateTimeUI.prototype, {

        initPicker: function (options, defaults) {
            if (options.trimMidnightTime) {
                var $scope = $(options.scope);
                if ($scope.text().substr(-9) == ' 12:00 AM' || $scope.text().substr(-6) == ' 00:00') {
                    $scope.html($scope.text().substr(0, 10));
                }
            }

            if (!this.options.viewformat) {
                this.options.viewformat = this.options.format;
            }
            this.options.viewformat = this.options.viewformat.replace('yyyy', 'yy');
            this.options.format = this.options.format.replace('yyyy', 'yy');
            this.options.datetimepicker = $.extend({}, defaults.datetimepicker, options.datetimepicker, {
                dateFormat: this.options.viewformat,
                onSelect: function() {
                    setTimeout(function() {
                        $('.ui-timepicker-div + .ui-datepicker-buttonpane .ui-datepicker-close').hide();
                        }, 10);
                }
            });
        },

        render: function () {
            $.timepicker.log = function (err) {};
            this.$input.datetimepicker(this.options.datetimepicker);
            $.timepicker.log = function (err) {};

            //"clear" link
            if (this.options.clear) {
                this.$clear = $('<a href="#"></a>').html(this.options.clear).click($.proxy(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.clear();
                    }, this));

                this.$tpl.parent().append($('<div class="editable-clear">').append(this.$clear));
            }
        },

        value2html: function (value, element) {
            var text = $.datepicker.formatDate(this.options.viewformat, value) + formatTime(value, this.options.trimMidnightTime);
            DateTimeUI.superclass.value2html.call(this, text, element);
        },

        html2value: function (html) {
            if (typeof html !== 'string') {
                return html;
            }

            var d, t, s;
            try {
                s = html.split(' ');
                d = $.datepicker.parseDate(this.options.viewformat, s[0]);

                if (s[1]) {
                    if (s[2]) {
                        s[1] += ' ' + s[2];
                    }

                    t = $.datepicker.parseTime('hh:mm TT', s[1]);

                    d.setHours(t.hour);
                    d.setMinutes(t.minute);
                }
            } catch (e) {}

            return d;
        },

        value2str: function (value) {
            return $.datepicker.formatDate(this.options.format, value) + formatTime(value, this.options.trimMidnightTime);
        },

        str2value: function (str) {
            if (typeof str !== 'string') {
                return str;
            }

            var d;
            try {
                d = $.datepicker.parseDate(this.options.format, str);
            } catch (e) {}

            return d;
        },

        value2submit: function (value) {
            return this.value2str(value);
        },

        value2input: function (value) {
            this.$input.datetimepicker('setDate', value);
            this.$input.datetimepicker('setTime', value);
        },

        input2value: function () {
            return this.$input.datetimepicker('getDate');
        },

        activate: function () {},

        clear: function () {
            this.$input.datetimepicker('setDate', null);
            if (this.isAutosubmit) {
                this.submit();
            }
        },

        autosubmit: function () {
            this.isAutosubmit = true;
            this.$input.on('mouseup', 'table.ui-datetimepicker-calendar a.ui-state-default', $.proxy(this.submit, this));
        },

        submit: function () {
            var $form = this.$input.closest('form');
            setTimeout(function () {
                $form.submit();
                }, 200);
        }

    });

    DateTimeUI.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-date"></div>',
        inputclass: null,
        format: 'yyyy-mm-dd',
        timeFormat: 'hh:mm TT',
        viewformat: null,
        trimMidnightTime: true,
        datetimepicker: {
            firstDay: 0,
            changeYear: true,
            changeMonth: true,
            showOtherMonths: true,
            timeFormat: 'hh:mm TT'
        },
        clear: '&times; clear'
    });

    $.fn.editabletypes.datetimeui = DateTimeUI;

    }(window.jQuery));