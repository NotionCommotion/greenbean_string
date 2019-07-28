(function($){
    //Doesn't support being able to use editable callback functions, and only autocomplete
    $.fn.editableAutocomplete = function(options) {
        // options is standard x-editable options (i.e. type, url, params, placement, send, ajaxOptions, etc)
        // PLUS an extra "autocomplete" property which is like:
        // {select: function(e, ui){},source: function(e, ui){}, params:{term:null, fields:['bla']}, url: "/endpoint"}
        // params.fields is to tell server what to return (id and name by default)
        // Note that select is only used currently for category chart points and source isn't used at all.
        //By default, xeditable will send {thename: thevalue} (can be overridden

        //console.log('on.init','this is the selected collection',this)
        //console.log(this, options); //, options.params, options.params())
        var name=options.name; //Saved for later to return value
        var url=options.autocomplete.url;
        delete(options.autocomplete.url);
        var autocompleteParams=options.autocomplete.params;
        delete(options.autocomplete.params);
        var autocomplete=Object.assign({}, {
            source: function( request, response ) {
                autocompleteParams.term=request.term;
                $.getJSON( url, autocompleteParams, function(json) {
                    var data=[];
                    for (var j = 0; j < json.length; j++) {
                        data.push({id:json[j].id,label:json[j].name});
                    }
                    response(data);
                } );
            },
            minLength: 2,
            position: { my : "left top+20", at: "left bottom" },
            select: function(e, ui) {
                //console.log('on.select','this is input.autocomplete',this)
                var $this=$(this);
                var editable=$this.data("editable");    //How to do this without using jQuery data()?
                $this.blur().parent().next().find('button.editable-submit').css('opacity', 1).off('click.prevent');
                if(options.params) {
                    //set options.params as a function to prevent xeditable from adding pk, name, value
                    //editable.option('params', function() {options.params();});
                    editable.option('params', options.params);
                }
                else {
                    var params={}
                    params[name]=ui.item.id;
                    //Return as a function to prevent xeditable from adding pk, name, value
                    editable.option('params', function() {return params;});
                }
            }
            },options.autocomplete);
        delete(options.autocomplete);
        //console.log('options editable',options, 'options autocomplete',autocomplete)


        //this.each(function () {
        this.editable(options)
        .on('shown', function(e, editable) {
            //console.log('on.show','this is a.editable',this)
            //console.log('editable',editable)
            var $input=editable.input.$input.val('').data('editable',editable); //What is the correct way to allow select to access editable?
            var $button=$input.parent().next().find('button.editable-submit').css('opacity', 0.3)
            .on('click.prevent', function() {return false;});
            //this.editable=editable;
            //var elem=this;    //Needed only for rare case such as category chart where integer PK isn't only used but column/row labels are also needed.
            //autocomplete.select.bind($input);
            //autocomplete.select.bind(this);
            $input.focus(function() {
                $button.css('opacity', 0.3).on('click.prevent', function() {return false;});
            })
            .autocomplete(autocomplete)
            .autocomplete('widget').click(function() {return false;});
        });
        //});
    };
    }(jQuery)
);