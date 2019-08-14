window.onerror = function(msg, url, line, col, error) {
    var extra = !col ? '' : '\ncolumn: ' + col;
    extra += !error ? '' : '\nerror: ' + error;

    // You can view the information in an alert to see things working like this:
    alert("Error: " + msg + "\nurl: " + url + "\nline: " + line + extra);

    // TODO: Report this error via ajax so you can keep track
    //       of what pages have JS issues

    var suppressErrorAlert = true;
    // If you return true, then error alerts (like in older versions of
    // Internet Explorer) will be suppressed.
    return suppressErrorAlert;
};

var gb_img_base='/packages/greenbean_data_integrator/images/';
(function (global) {
    'use strict';

    var param = function (a) {
        var s = [];
        var add = function (k, v) {
            v = typeof v === 'function' ? v() : v;
            v = v === null ? '' : v === undefined ? '' : v;
            s[s.length] = encodeURIComponent(k) + '=' + encodeURIComponent(v);
        };
        var buildParams = function (prefix, obj) {
            var i, len, key;

            if (prefix) {
                if (Array.isArray(obj)) {
                    for (i = 0, len = obj.length; i < len; i++) {
                        buildParams(
                            prefix + '[' + (typeof obj[i] === 'object' && obj[i] ? i : '') + ']',
                            obj[i]
                        );
                    }
                } else if (String(obj) === '[object Object]') {
                    for (key in obj) {
                        buildParams(prefix + '[' + key + ']', obj[key]);
                    }
                } else {
                    add(prefix, obj);
                }
            } else if (Array.isArray(obj)) {
                for (i = 0, len = obj.length; i < len; i++) {
                    add(obj[i].name, obj[i].value);
                }
            } else {
                for (key in obj) {
                    buildParams(key, obj[key]);
                }
            }
            return s;
        };

        return buildParams('', a).join('&');
    };

    if (typeof module === 'object' && typeof module.exports === 'object') {
        module.exports = param;
    } else if (typeof define === 'function' && define.amd) {
        define([], function () {
            return param;
        });
    } else {
        global.param = param;
    }

    }(this)
);

$.jfUtils = (function (my) {
    var throbber;
    if(throbber=document.getElementById( 'throbber' )) {
        my.Throbber=Throbber({
            size: 60,
            fade: 200,
            rotationspeed: 0,
            lines: 14,
            strokewidth: 1.8,
            //alpha: 0.4, // this will also be applied to the gif
            color: 'black',
            fallback: gb_img_base+'throbber.gif',
            //padding: 30
        }).appendTo( throbber );
    }

    my.isset=function(accessor, defaultValue){
        try {
            return accessor()?1:0;
            //return typeof accessor() !== 'undefined'
        } catch (e) {
            return defaultValue
        }
    }

    my.jsonToObj=function(json){
        try {
            return JSON.parse(json);
        }
        catch(e) {
            alert('Invalid JSON');
            return false;
        }
    }
    my.formToJSON = elements => [].reduce.call(elements, (data, element) => {
            const getSelectValues = options => [].reduce.call(options, (values, option) => {
                    return option.selected ? values.concat(option.value) : values;
                }, []);
            if (element.name && element.value && (!['checkbox', 'radio'].includes(element.type) || element.checked)) {
                if (element.type === 'checkbox') {
                    data[element.name] = (data[element.name] || []).concat(element.value);
                } else if (element.options && element.multiple) {
                    data[element.name] = getSelectValues(element);
                } else {
                    data[element.name] = element.value;
                }
            }
            return data;
        }, {}
    );

    /*
    Accepts an existing object or creates a new object, and set's property as defined by keys to value.
    Not currently being used, but might be useful to allow xeditable request to modify sub-object.
    */
    my.setDeepValue = function(keys, value, object) {
        object=object?object:{};
        var last = keys.pop();
        keys.reduce((o, k) => o[k] = o[k] || {}, object)[last] = value;
        return object;
    }

    my.dialogError=function(title,errors){
        var ul=$('<ul/>');
        if(typeof stringValue){errors=[errors];}
        $.each(errors, function(i,error) {
            ul.append($('<li/>',{text: error}));
        });
        $('<div/>', {title: title, html: ul}).dialog({
            modal       : true,
            buttons     : [
                {
                    text    : 'CLOSE',
                    click    : function() {$(this).dialog("close");}
                }
            ]
        });
    }

    my.stipObj=function(obj,properties){
        var newObj={};
        for (var i = 0; i < properties.length; i++) {
            newObj[properties[i]]=obj[properties[i]];
        }
        return newObj;
    }

    my.filter=function(type,value){
        var searchParams = new URLSearchParams(window.location.search);
        if(value=='all') searchParams.delete(type);
        else {
            var search=searchParams.get(type);
            if(search) {
                search=search.split(',');
                if(search.indexOf(value)==-1) {
                    search.push(value);
                    searchParams.set(type,search.join(','));
                }
            }
            else searchParams.set(type,value);
        }
        window.location.search=searchParams.toString();
    }

    my.getYesNo = function () {
        return [{value:0,text:'No'},{value:1,text:'Yes'}];
    }

    return my;
    }($.jfUtils||{})

);

$(function(){

    $.ajaxSetup({
        "error": function(xhr, status, error) {
            if((typeof xhr.responseJSON === 'object') && (typeof xhr.responseJSON.message !== 'undefined') && xhr.responseJSON.message) {
                var message = xhr.responseJSON.message;
            }
            else if(xhr.responseText) {
                var message=JSON.parse(xhr.responseText);
                if(typeof message ==='object' && typeof message.message !== 'undefined' && message.message) {
                    message = message.message
                }
                else {
                    message = xhr.responseText
                }
            }
            else {
                var message = status+' '+error
            }
            alert(message);
        }
    });

    $.fn.pop = function() {
        var top = this.get(-1);
        this.splice(this.length-1,1);
        return top;
    };

    $.fn.shift = function() {
        var bottom = this.get(0);
        this.splice(0,1);
        return bottom;
    };

    $.fn.exists = function () {
        return this.length !== 0;
    }

    if($.fn.hasOwnProperty('editable')){
        $.fn.editable.defaults.error = function(response, newValue) {
            return response.message;
        };
        /* Converts name/value pair or an array of name/value pairs to object.  ie.
        {name: propname, value: propvalue} => {propname: propvalue}
        [{name: propname1, value: propvalue1}, {name: propname2, value: propvalue2}] => {propname1: propvalue1, propname2: propvalue2}
        {name: propname, value: [1,2,3]} => {propname: [1,2,3]} //used by checkboxes
        If params does not contain only name/value or is not an object, return as is.
        Reference https://github.com/vitalets/x-editable/issues/134
        */
        $.fn.editable.defaults.params = function(params){
            if (typeof params === 'object') {
                if (params instanceof Array) {
                    //If an array of objects
                    for (i = 0, len = params.length; i < len; i++) {
                        params[i]=nameValue2Obj(params[i]);
                    }
                } else {
                    params=nameValue2Obj(params);
                }
            }
            //else don't change
            return params;
        }
        //$.fn.editable.defaults.ajaxOptions = { traditional: true };

        function nameValue2Obj(params){
            if(params.name && params.value && typeof params.name === 'string') {
                if (params.value instanceof Array || typeof params.value !== 'object') {
                    var name=params.name;
                    params[name] = params.value;
                    if(name !=='name') {
                        delete params.name;
                    }
                    delete params.value;
                    delete params.pk;   //My implementation doesn't use this
                }
                //else do nothing
            }
            //else do nothing
            return params;
        }
    }

    $.fn.Dialog = function(given) {
        if(typeof given === 'undefined') given={};
        if(typeof given === 'object') given=Object.assign({
            autoOpen    : false,
            resizable   : false,
            height      : 'auto',
            width       : 600,
            modal       : true
            }, given);
        return this.dialog(given);
    };
    $.fn.myValid = function(rules, options) {
        //Helper plugin to reduce script content
        //console.log(this, rules, options)
        var element=this;
        this.validate({
            rules: rules.rules,
            messages: rules.messages,
            submitHandler: function(form) {
                var o = Object.assign({},{
                    type:'POST',
                    url:null,
                    data: function(form) {
                        console.log(form)
                        return $(form).serializeArray()
                    },
                    //dataType: 'json',
                    success: function (rsp){
                        //Instead of reloading page, do dynamicly
                        $.unblockUI();
                        console.log(rsp)
                        alert('record submitted')
                        element.closest("div.ui-dialog-content").dialog("close");
                        location.reload();
                    },
                    error: function (xhr) {
                        console.log(xhr);
                        $.unblockUI();
                        if(xhr.status==200) {
                            //Only reason here is responder isn't set up to allow for _test.php.
                            alert('record submitted (2)')
                            element.closest("div.ui-dialog-content").dialog("close");
                            location.reload();
                        }
                        else {
                            alert(xhr.responseJSON.message);
                        }
                    }
                    }, options);
                if (typeof o.data === "function") o.data=o.data.call(element, form);
                if (typeof o.url === "function") o.url=o.url.call(element);
                $.blockUI();
                $.ajax(o);
            }
        });
    };
    $('.default-value').each(function() {
        var $t=$(this), default_value = this.value;
        $t.css('color', '#929292');
        $t.focus(function() {
            if(this.value == default_value) {
                this.value = '';
                $t.css('color', 'black');
            }
        });
        $t.blur(function() {
            if($.trim(this.value) == '') {
                $t.css('color', '#929292');
                this.value = default_value;
            }
        });
    });
    $("footer a.licence").click(function() {$("#dialog-licence").dialog("open");});
    $("#dialog-licence").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
    });

});

$.extend($.ui.dialog.prototype.options, {
    create: function() {
        var $this = $(this);

        // focus first button and bind enter to it
        $this.parent().find('.ui-dialog-buttonpane button:first').focus();
        $this.keypress(function(e) {
            if( e.keyCode == $.ui.keyCode.ENTER ) {
                $this.parent().find('.ui-dialog-buttonpane button:first').click();
                return false;
            }
        });
    }
});
