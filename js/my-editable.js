/* Used to validate editiable inline forms
First, use initialize method to set validation object.
Then use validate method in editiable validate callback
For instance:
$('#email').editable({
type: 'text',
title: 'Name',
url: '/echo/json/',
pk: 123,
validate: function (value) {return $.validator.editable.validate(value,this);}
});
*/
// Not used
$(function () {
    $.validator.editable={};
    $.validator.editable.form=$('<form />',{css:{position:'absolute',top:'-9999px'}}).append($('<input />', {type: 'text'})).appendTo('body');

    $.validator.editable.initialize = function(validateObj) {
        //Get rid of remote rules since they don't work correctly
        validateObj.messages=validateObj.messages||{};
        for (var elem in validateObj.rules) {
            if(validateObj.rules[elem].hasOwnProperty('remote')){
                delete validateObj.rules[elem].remote;
                if(validateObj.messages[elem] && validateObj.messages[elem].hasOwnProperty('remote')){
                    delete validateObj.messages[elem].remote;
                }
            }
        }
        $.validator.editable.form.validate(validateObj);
    }

    $.validator.editable.validate = function(value,t) {
        var input=$.validator.editable.form.find('input').attr('name',(name=t.getAttribute("name"))?name:t.id).val(value);
        if(!input.valid()){return input.next().html();}
    }
});

var blur_mask=function() {
    // Used if both mask and validate is used on same input.
    //force revalidate on blur.
    var frm = $(this).parents("form");
    // if form has a validator
    if ($.data( frm[0], 'validator' )) {
        var validator = $(this).parents("form").validate();
        validator.settings.onfocusout.apply(validator, [this]);
    }
}
jQuery.fn.extend({
    myeditable: function(obj) {
        var extra1={},extra2={},    //In order of precidence: extra2, obj, extra1.  What is this for?  Why not use params?
        autosource=false,           //Used for autocomplete
        mask=false;                 //Used with masking plugin
        switch(obj.type) {
            case 'phone': extra2={inputclass: 'phone',type:'text'};mask='(999) 999-9999';break;
            case 'dollar': extra2={inputclass: 'dollar',type:'text'};mask='$9?99999999';break;
            case 'percent': extra2={inputclass: 'percent',type:'text'};mask='9?9%';break;
            case 'autocomplete': extra2={type:'text'},autosource=obj.autosource;delete(obj.autosource);break;
            case 'date': extra1={format: "mm/dd/yyyy", viewformat: "mm/dd/yyyy"};break;
            case 'datetimeui': extra1={format: "mm/dd/yyyy", viewformat: "mm/dd/yyyy",trimMidnightTime: true};break;
        }
        obj=$.extend(true, {
            type: 'text',
            placement: 'right',
            title: 'Edit Detail',
            url: 'component/controller/saveEditable',
            pk: function(){return $('#id').val();},
            error: function (response, newValue) {
                //Unlike other validation, save function to return non 200 header.
                return response.responseText;
            },
            validate: function (value) {
                //Be sure to remove if no validation is used
                return $.validator.editable.validate(value,this);
            }
            },extra1,obj,extra2
        );
        return this.each(function () {
            if(autosource){
                var id;
                obj.send='never';
                obj.validate=function (value) {
                    //Doesn't use client side validation since autcomplete should not have errors.
                    var options=$(this).data('editable').options;
                    var data=options.params||{};
                    data.pk=options.pk;
                    data.name=options.name;
                    data.value=id;
                    $.post(options.url,data);
                };
                $(this).editable(obj)
                .on('shown', function(e, editable) {
                    var $input=editable.input.$input.val('');
                    var $button=$input.parent().next().find('button.editable-submit').css('opacity', 0.3)
                    .bind('click.prevent', function() {return false;});
                    $input.focus(function() {
                        $button.css('opacity', 0.3).bind('click.prevent', function() {return false;});
                    })
                    .autocomplete({
                        source: autosource,
                        minLength: 2,
                        select: function(e, ui) {
                            $input.blur();
                            $button.css('opacity', 1).unbind('click.prevent');
                            id = ui.item.id;
                        }
                    })
                    .autocomplete('widget').css('padding-top',10).click(function() {return false;});
                });
            }
            else {$(this).editable(obj).on('shown', function(e, editable) {
                if(mask){
                    if(mask=='9?9%'){
                        editable.input.$input.mask(mask).bind("blur",blur_mask)
                        .on("blur", function() {
                            var jObj = $(this);
                            jObj.val(jObj.val()+ '%');
                            //var jVal = jObj.val();
                            //console.log(jObj,jVal,jVal.length)
                            //jObj.val((jVal.length === 1) ? jVal + '%' : jVal);
                        });
                    }
                    else {editable.input.$input.mask(mask).bind("blur",blur_mask);}
                }
                });}
        });
    }

});

