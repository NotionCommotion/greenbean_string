/**
* jQuery Validation Plugin 1.9.0
*
* Added by Michael Reed
*
*/
(function() {

    $.validator.createRemoteFromJSON=function(o) {
        /* remote methods received in JSON are given as:
        {"rules": {"someName": {"remote": {"data": {"someName": {"_function": "return $( '#someID' ).val()"}}}}}}
        and will be converted to:
        {"rules": {"someName": {"remote": {"data": {"someName": function() {return $( '#someID' ).val()}}}}}}
        */
        for (var k1 in o.rules) {
            if(o.rules[k1].remote && o.rules[k1].remote.data) {
                Object.keys(o.rules[k1].remote.data).forEach(function (k2) {
                    if (o.rules[k1].remote.data[k2]._function) {
                        o.rules[k1].remote.data[k2] = new Function(o.rules[k1].remote.data[k2]._function);
                    }
                });
            }
        }
        return o
    }

    $.validator.addMethod("confirmPassword", function(value, element, password) {
        return value == $('#'+password).val();
        }, $.validator.format("Passwords do not match."));

    $.validator.addMethod("defaultInvalid", function(value, element, params) {
        return !(element.value == element.defaultValue);
        }, $.validator.format("This field is required."));

    $.validator.addMethod("noInvalid", function(value, element, params) {
        return this.optional(element) || /^[a-z0-9.,-_()& ]+$/i.test(value);
        }, "Invalid charactors.");

    $.validator.addMethod("domain", function(value, element, params) {
        return this.optional(element) || /^[a-z0-9_-]+$/i.test(value);
        }, "Alphanumerical, underscore, and hyphes only.");

    $.validator.addMethod("ipv4_multiple", function(value, element, param) {
        //console.log(value, element, param)
        return this.optional(element) ||/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?),)+$/i.test(value + ',')
        }, "Please enter a valid IP v4 address.");

    //Following are used serverside only and these are only used to prevent a JS error.
    $.validator.addMethod("isUSstate", function(value, element, params) {
        return true;   //Not implemented
        }, "Must be a state.");

    $.validator.addMethod("timezone", function(value, element, params) {
        return true;   //Not implemented
        }, "Invalid timezone.");

    $.validator.addMethod("maxAccess", function(value, element, params) {
        return true;   //Not implemented
        }, "Invalid access level.");

    $.validator.addMethod("inArray", function(value, element, params) {
        return this.optional(element) || params.indexOf(value)!==-1;
        }, "Not an allowed selection.");

    $.validator.addMethod("array_range", function(value, element, params) {
        return true;   //Not yet implemented
        }, "{$name}'s array length must be between $r[0] and $r[1] characters");

    $.validator.addMethod("bool", function(value, element, params) {
        return this.optional(element) || [0,1,'0','1',true,false,'true','false'].indexOf(value)!==-1;
        }, "Value must be boolean.");

    $.validator.addMethod("exactlength", function(value, element, param) {
        return this.optional(element) || value.length == param;
        }, "Please enter exactly {0} characters.");

    $.validator.addMethod("timecode", function(value, element, param) {
        console.log("timecode method not complete");
        return true;
        }, "Must be valid time unit plus integer.");

})();
