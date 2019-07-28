/*
* jQuery makeInput
* Copyright Bidjunction LLC, 2009
* Dual licensed under the MIT and GPL licenses.
* Creates a hidden file upload input over selected elements
*/

(function($){
    function getHiddenDim(e,p)   {
        /* Get's dimensions of a hidden element or an element that is located in a hidden anchestor.
        Since I am using jQuery to get element dimensions, make this a jQuery utility method.
        Consider not using jQuery!
        Future.  Allow user to pass those variables desired, and only return those to improve performance.
        Assumes BODY is never hidden
        */
        var hidden=[], style, $e=$(e);
        while(e.parentNode.tagName!='HTML') {
            style=e.currentStyle?e.currentStyle:getComputedStyle(e,null);
            if((style.display=='none')) {
                hidden.push({'e':e,'position':style.position,'visibility':style.visibility,'display':style.display});
                e.style.position='absolute';
                e.style.visibility='hidden';
                e.style.display='block';
            }
            e = e.parentNode;
        }
        /* Change to use native JavaScript.
        If changes to accept desired attributes instead of all, using the following:
        var results={}; for (var i = p.length; i > 0; --i) {results[p[i-1]]=this[p[i-1]]();}
        */
        var results={
            width:$e.width(),
            height:$e.height(),
            innerWidth:$e.innerWidth(),
            innerHeight:$e.innerHeight(),
            outerWidth:$e.outerWidth(),
            outerHeight:$e.outerHeight(),
            outerWidthTrue:$e.outerWidth(true),
            outerHeightTrue:$e.outerHeight(true),
        };

        //Set it back to what it was
        for (var i = hidden.length; i > 0; --i) {
            hidden[i-1].e.style.position=hidden[i-1].position;
            hidden[i-1].e.style.visibility=hidden[i-1].visibility;
            hidden[i-1].e.style.display=hidden[i-1].display;
        }
        return results;
    }

    var defaults = {
        name:           'file',    //Name of the input
        getHiddenDim:   false      //If true, get real size of target even if hidden or located in a hidden ancestor.
    };

    var methods = {
        init : function (options) {

            var settings = $.extend({},defaults, options  || {}),
            getHiddenDim=settings.getHiddenDim;
            arr = [];
            delete settings.getHiddenDim;

            this.each(function () {
                var $targ = $(this);
                if ( $targ.is( "input:file" ) ) {arr.push(this);}
                else {

                    //Not yet implement.  If getHiddenDim is true, use getHiddenDim() to get dimensions.  If not, must use resize method after target is made visible.
                    
                    var position=$targ.css('position'),
                    display=$targ.css('display'),
                    float=$targ.css('float'),
                    nodeName=$targ.prop('nodeName'),
                    outerHeight=$targ.outerHeight(),
                    cssTarget={float:'none'};

                    if(position!=='static') {
                        var styleDiv='position:'+position+';top:'+$targ.css('top')+';left:'+$targ.css('left');
                        cssTarget.position='static';
                    }
                    else {
                        var styleDiv='position:relative'
                    }
                    styleDiv+=';float:'+float+';display:'+((nodeName=='IMG')?'inline-block':display)+';margin:0;padding:0';

                    if((position=='absolute' || position=='fixed' || nodeName=='BUTTON' || nodeName=='IMG' || float!='none')) {
                        var topInput=$targ.css('margin-top');
                    }
                    /*
                    else if(nodeName=='IMG'){
                    //Not quit correct.  Instead, turn parent div to inline-block and set topInput to $targ.css('margin-top')?
                    //var topInput=(-outerHeight-parseInt($targ.css('border-top-width'),10))+'px';
                    }
                    */
                    else if(display=='inline'){
                        var topInput=-Math.round(parseFloat($targ.css('padding-top'),10)+parseFloat($targ.css('border-top-width'),10))+'px';
                    }
                    else {
                        var topInput='0px';
                    }

                    var input=$('<input/>', $.extend({},settings,{
                        type: 'file',
                        style: 'position:absolute;top:'+topInput+';left:'+$targ.css('margin-left')+';height:'+outerHeight+'px;width:'+$targ.outerWidth()+'px;padding:0;margin:0;display:inline;cursor:pointer;z-index:9999;opacity:0;filter:alpha(opacity=0);'//'border:dashed 1px red;'//testing
                    }));

                    $targ.wrap($('<div/>',{style:styleDiv})).css(cssTarget).parent().append(input);

                    //console.log('nodeName',nodeName,'float',float,'display',display,'position',position,input[0])
                    arr.push(input[0]);
                }
            })
            return $(arr);
        },
        resize : function() {
            //Resize hidden input.  Useful if targe size was changed or if target was originally located in a hidden ancestor or hidden itself
            //Uplike main plugin, returns this so is chainable
            return $(this).each(function(){
                var $targ=$(this);
                $targ.next().css({height:$targ.outerHeight(),width:$targ.outerWidth()});
            })
        },
        hide : function() {
            return $(this).each(function(){
                $(this).parent().hide();
            })
        },
        show : function() {
            return $(this).each(function(){
                $(this).parent().show();
            })
        },
        destroy : function () {
            return this.each(function () {
                return this.each(function () {});
            });
        }
    };


    $.fn.makeInput = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.makeInput');
        }    
    };
    }(jQuery));