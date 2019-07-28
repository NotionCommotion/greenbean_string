/*
* jQuery sortfixedtable R3
* Copyright Bidjunction LLC, 2009
* Dual licensed under the MIT and GPL licenses.
*
* Notes
* Style table directly using CSS. Targetting table using ID will not apply to thead and tfoot, therefore use class instead.
* Note that your table width will increate by approximately 17px to account for scroll bar.
* Note that your table width might also increase if you add padding to sortfixedtable-sortable or similar.
* Options are desired table height without px (height), optional class to apply to parent div (myClass), whether all columns are sortable (sortAll), and override on individual columns (sort)
* Tables with colspan will have problems is sorting is required left of the colspan
*/
(function( $ ){

    var defaults={
        'height'    : '100',    //Height of tbody in pixals (not total table!).  Don't include px
        'myclass'   : null,     //Applies custom class to the parent DIV so user may override
        'autoHeader':true,      //If true, will hide header when opened and empty or if row is deleted making it empty, and will show header if added to
        'updateLast':true,      //If true, will add class sortfixedtable-even to appended row if it is even, otherwise must call updateLast()
        'noText'    : {},       //The column has no text or number node to sort upon (for instance, an image).  Defaults to false
        'regex'     : {},       //Regular expression to search column on (array with regex and flags)
        'autoSort'  : null,     //Initially sorts on given column (index)
        'sort'      : {},       //Explicite sorting column.  Overrides sortAll.  Assign by index:true
        'sortAll'   : false     //Defaults to sorting all columns
    };
    var internalPrefix = '_sortfixedtable';

    var methods = {
        init : function( options ) {
            //console.log('init');

            //update settings variable by extending defaults with any options that were provided
            var settings = $.extend({},defaults, options  || {}),     //Just in case user doesn't provide options

            //ms_extra=($.browser.msie  && $.browser.version>=9)?1:0, //Hack for problems with IE9
            scrollWidth=null;       //scroll bar width

            return this.each(function(){
                var $t=$(this).css('margin-bottom', 0).wrap('<div />').wrap('<div />'),
                isText=[],   //Will be set for each tbody column to indicate that all values are numbers
                extra_classes=$t.attr('class'), //Used to add classes applied to original table to new thead and tfoot
                tmp,

                //When not using jQuery.
                //thead_cells=(this.tHead && this.tHead.rows[0])?this.tHead.rows[0].cells:undefined,
                //tfoot_cells=(this.tFoot && this.tFoot.rows[0])?this.tFoot.rows[0].cells:undefined,
                //tbody_cells=(this.tBodies && this.tBodies[0].rows && this.tBodies[0].rows[0])?this.tBodies[0].rows[0].cells:undefined,

                //When using jQuery
                thead=$t.children( 'thead' ),
                tfoot=$t.children( 'tfoot' ),
                tbody=$t.children( 'tbody' ),
                tbody_rows=tbody.find('tr'),
                thead_cells=thead.find('tr').eq(0).find('th'),
                tfoot_cells=tfoot.find('tr').eq(0).find('td');

                //Figure out if all values in a given column are numbers.
                for (var i = 0; i < this.tBodies.length; i++) {
                    for (var j = 0; j < this.tBodies[i].rows.length; j++) {
                        for (var k = 0; k < this.tBodies[i].rows[j].cells.length; k++) {
                            tmp = (settings.noText[k])?(this.tBodies[i].rows[j].cells[k].innerHTML)
                            :(this.tBodies[i].rows[j].cells[k].textContent || this.tBodies[i].rows[j].cells[k].innerText);
                            if(settings.regex[k])
                            {
                                var reg = (settings.regex[k][1])
                                ?new RegExp (settings.regex[k][0],settings.regex[k][1])   //Pattern and modifiers
                                :new RegExp (settings.regex[k][0]); //Just pattern
                                tmp=tmp.match(reg);
                                tmp=(tmp)?tmp[1]:null;
                            }
                            isText[k]=(isText && isText[k])?true:(!tmp || (!isNaN(parseFloat(tmp)) && isFinite(tmp)))?false:true;
                        }
                    }
                }

                //Add any classes to the header.  Uncertain whether this should be done before fixing column widths
                for (var i = 0; i < thead_cells.length; i++) {
                    if((typeof settings.sort[i] === "undefined")?settings.sortAll:settings.sort[i]){
                        thead_cells.eq(i)
                        .addClass('sortfixedtable-sortable')
                        .on('click.sortfixedtable',function(){
                            var $t=$(this),
                            column = $t.index(), //Index to sort on.
                            table = $t.parent().parent().parent().next().find('table');
                            if (table.find('tr').length) {
                                sort(table,column);
                                methods.update.call(table);   //Add style to even and odd rows.
                            }
                        })
                        .append($('<span>&nbsp;</span>'));
                    }
                }

                //Override widths for columns
                for (var i = 0; i < thead_cells.length; i++) {
                    //thead_cells[i].style.width=thead_cells[i].offsetWidth+'px';   //Couldn't get this working
                    tmp=thead_cells.eq(i);
                    tmp.outerWidth(tmp.outerWidth(true));
                }
                for (var i = 0; i < tfoot_cells.length; i++) {
                    //tfoot_cells[i].style.width=tfoot_cells[i].offsetWidth+'px';
                    tmp=tfoot_cells.eq(i);
                    tmp.outerWidth(tmp.outerWidth(true));
                }
                //Create a hidden row.  Use first row of tbody if it exists, else the thead row
                //tbody_cells[i].style.width=tbody_cells[i].offsetWidth+'px';
                tmp=(tbody_rows.length)?tbody_rows.eq(0).find('td'):thead_cells;
                var shimRow=$('<tr />',{height:0,padding:0,margin:0});
                for (var i = 0; i < tmp.length; i++) {
                    shimRow.append($('<td />',{height:0,outerWidth:tmp.eq(i).outerWidth(true)}).css('padding',0).css('margin',0).html(''));
                }
                tbody.prepend(shimRow);

                //Calculate width of scroll bar for given browser (will be 0 for IE6 and IE7)
                if(!scrollWidth)
                {
                    var outer = document.createElement("div");
                    document.body.appendChild(outer);
                    var inner = document.createElement("div");
                    outer.appendChild(inner);
                    outer.style.visibility = "hidden";
                    outer.style.width = "100%";
                    outer.style.overflow = "scroll";
                    inner.style.width = "100%";
                    scrollWidth = outer.offsetWidth-inner.offsetWidth;
                    outer.parentNode.removeChild(outer);
                }

                var sortfixedtable=$t
                .append($('<thead />').append(shimRow)) //Move shim to thead so it doesn't effect counts
                .parent()   //Move to DIV
                .width($t.width()+scrollWidth+1)
                .css('overflow-y','auto')
                .before(
                    $('<table'+((extra_classes)?' class="'+extra_classes+'"':'')+' />' )
                    .css('margin-bottom',0) //Remove margin-bottom added by bootstrap.
                    .append( thead )
                )
                .after(tfoot.length?$('<table'+((extra_classes)?' class="'+extra_classes+'"':'')+' />' ).append( tfoot ):'')
                .css('max-height',settings.height+'px')
                .parent()
                .addClass('sortfixedtable'+((settings.myclass)?' '+settings.myclass:''));   //Do later so it doesn't effect widths?

                //Apply events done above!
                //sortfixedtable.on('click.sortfixedtable-sortable',methods.sortClick);

                // Store data to be used by other methods
                settings.data= {
                    target : $t,     //Not really sure where this will be used
                    parent : sortfixedtable,  //Not really sure where this will be used
                    isText   :   isText,  //Array of each body column to tell if it has some text values.
                    scrollWidth : scrollWidth,    //Width of scroll bar.  Probably not needed anymore
                    settings: settings  //Save here so other methods have access
                };
                $t.data(internalPrefix,settings);

                if(tbody.find('tr').length) {
                    //Since sortClick requires data(), do after data is defined.
                    if(settings.autoSort) {
                        sort(this,settings.autoSort);
                    }
                }
                else if(settings.autoHeader) {
                    //hide header
                    thead.parent().hide();
                }

                methods.update.call(this);   //Add style to even and odd rows (after sorting).
            });
        },

        destroy : function() {
            //console.log('destroy');
            return this.each(function(){
                var $t = $(this),
                p=$t.parent().parent(),
                ts=p.find('table'),
                t_head=ts.eq(0).children('thead'),
                t_body=ts.eq(1),
                t_foot=ts.eq(2).children('tfoot');
                t_body.find('thead').remove();  //Remove shim row
                t_head.find('th')
                .removeAttr('style')
                .removeClass('sortfixedtable-sortable sortfixedtable-accending sortfixedtable-decending')
                .off('.sortfixedtable') //Removes any events in sortfixedtable namespace
                .find('span').remove();

                t_body.find('tr').removeAttr('style').removeClass('sortfixedtable-even sortfixedtable-odd');
                t_foot.find('tr').removeAttr('style');

                $t
                .prepend(t_foot)
                .prepend(t_head)
                .removeData('sortfixedtable');   //Removes data in sortfixedtable namespace

                p.replaceWith($t);
            })
        },

        update : function() {
            //console.log('update');
            //Adds alternating style classes to rows
            return $(this).each(function(){
                var $this=$(this),
                $rows=$this.children('tbody').children('tr');
                if($rows.length) {
                    $rows.removeClass('sortfixedtable-even').filter(':even');
                }
                else if($this.data(internalPrefix).autoHeader) {
                    $this.parent().prev().hide();
                }
            })
        },

        updateHide : function() {
            //console.log('updateHide');
            //Adds alternating style classes to rows if one or more row, else hides header.  Not required if autoHeader is true
            return $(this).each(function(){
                $this=$(this),
                $rows=$this.children('tbody').children('tr');
                if($tbody.find('tr').length) {
                    $tbody.children('tr').addClass('sortfixedtable-odd').removeClass('sortfixedtable-even');
                    $tbody.children('tr:even').addClass('sortfixedtable-even').removeClass('sortfixedtable-odd');
                }
                else {
                    $this.parent().prev().hide();
                }
            })
        },

        sort : function(column) {
            //console.log('sort',this);
            //Manually sorts table
            return this.each(function(){
                if ($(this).children('tbody').children('tr').length) {
                    sort(this,column);
                    methods.update.call(this);   //Add style to even and odd rows.
                }
            })
        },

        updateLast : function() {
            //console.log('updateLast');
            //Adds alternating classes to last row (used when a row is added and faster than update)
            return this.each(function(){
                var r=$(this).find('tr');
                if(r.length%2) {r.eq(r.length-1).addClass('sortfixedtable-even');}
                else {r.eq(r.length-1).removeClass('sortfixedtable-even');}
            })
        },

        appendRow : function(row) {
            //console.log('appendRow');
            //Appends a row
            return this.each(function(){
                var $this=$(this);
                var settings=$this.data(internalPrefix);
                if(settings.autoHeader) {
                    $this.parent().prev().show();
                }
                if(settings.updateLast) {
                    var tbody=$this.find('tbody');
                    if(!tbody.find('tr').length%2) {row.addClass('sortfixedtable-even');}
                    tbody.append(row);
                }
                else {$this.find('tbody').append(row);}
            })
        },

        //appendRow : function(row) {},   //Similar to appendRow, but appends multiple rows
        //emptyTable : function() {},   //Empties table.  Hides header if settings.autoHeader is true

        showTable : function() {
            //console.log('showTable');
            //Shows headers
            return this.each(function(){
                $(this).parent().parent().show();
            })
        },
        hideTable : function() {
            //console.log('hideTable');
            //Hides headers
            return this.each(function(){
                $(this).parent().parent().hide();
            })
        },

        showHeader : function() {
            //console.log('showHeader');
            //Shows headers
            return this.each(function(){
                $(this).parent().prev().children('thead').show();
            })
        },
        hideHeader : function() {
            //console.log('hideHeader');
            //Hides headers
            return this.each(function(){
                $(this).parent().prev().children('thead').hide();
            })
        },

        getFooter : function() {
            alert('sortfixedtable.getFooter not used')
            //Get's the footer (sometimes used as clone).  Note chainable.  Only used for single element
            return $(this).parent().next().children('tfoot');
        },

        getFooterRow : function() {
            alert('sortfixedtable.getFooterRow not used')
            //Get's the footer row (sometimes used as clone).  Note chainable.  Only used for single element
            return $(this).parent().next().find('> tfoot > tr');
        }

    };

    $.fn.sortfixedtable = function(method) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.sortfixedtable' );
        }
    };

    function sort(table,column) {
        //private function.  Given table to sort on and column to sort by
        var headers=table.parent().parent().find('table thead tr').eq(0).find('th'),
        header=headers.eq(column),
        rows=table.children('tbody').children('tr'),
        itemA,
        itemB,
        reverse=header.hasClass('sortfixedtable-decending'),    //If just did a sort, next reverse it
        data=table.data(internalPrefix).data;
        headers.removeClass('sortfixedtable-decending').removeClass('sortfixedtable-accending');
        header.addClass((reverse)?'sortfixedtable-accending':'sortfixedtable-decending');
        //console.log(data)

        rows.sort(function(itemAElem, itemBElem) {

            if(data.settings.noText[column]) {
                itemA = itemAElem.cells[column].innerHTML;
                itemB = itemBElem.cells[column].innerHTML;
            }
            else {
                itemA = itemAElem.cells[column].textContent  || itemAElem.cells[column].innerText;
                itemB = itemBElem.cells[column].textContent  || itemBElem.cells[column].innerText;
            }

            if(data.settings.regex[column])
            {
                var reg = (data.settings.regex[column][1])
                ?new RegExp (data.settings.regex[column][0],data.settings.regex[column][1])   //Pattern and modifiers
                :new RegExp (data.settings.regex[column][0]); //Just pattern

                itemA=itemA.match(reg);
                itemB=itemB.match(reg);
                itemA=(itemA)?itemA[1]:null;
                itemB=(itemB)?itemB[1]:null;
            }

            //Empty strings to fall to the bottom
            if(!itemA&&!itemB) return 0;
            if(!itemA) return 1;
            if(!itemB) return -1;

            if(data.isText[column])
            {
                return reverse?itemB.localeCompare(itemA):itemA.localeCompare(itemB);
            }
            else
            {
                itemA = parseFloat(itemA);
                itemB = parseFloat(itemB);
                return !reverse?(itemA == itemB ? 0 : (itemA < itemB ? -1 : 1)):(itemA == itemB ? 0 : (itemA < itemB ? 1 : -1))
            }
        });

        var parent = rows[0].parentNode;
        $.each(rows, function(index, row) {
            parent.appendChild(row);
        });

    }


    }( jQuery ));