$(function(){

    $.fn.myEdit = function(type, pk, name, options, optionsAutocomplete, method) {
        //Helper plugin to reduce script content
        if (typeof method === 'undefined') {
            method='PUT';
        }
        if(!name && options.url) {
            //Used for chart option only.  Kind of a kludge.
            //console.log(type, pk, name, options, optionsAutocomplete, method)
            options.url=gb_api_base+'/chart/'+pk+'/options/'+options.url;
            options.params=function(params) {
                return params.value;
            }
        }
        options = Object.assign({
            type: type,
            placement: 'right',
            ajaxOptions: {type: method},
            send: 'always',
            name: name,
            url: gb_api_base+'/chart/'+pk
            }, options);
        if(type=='autocomplete') {
            //editable.params callback set in editableAutocomplete (or callback in these scripts)
            options.type='text';
            var defaults={params:{term:null/* , fields:['id','name'] */}, url: gb_api_base+"/points"};
            options.autocomplete=optionsAutocomplete?Object.assign({}, defaults, optionsAutocomplete):defaults;
            this.editableAutocomplete(options);
        }
        else this.editable(options);
        return this;
    };

    $('#chart-listxxx').sortfixedtable({
        //Must fix before using
        'height'    : '600',  //Height of tbody in pixals (not total table!).  Don't include px
        //'myclass'   : 'child-table-div',
        'sortAll'   : false,    //Defaults to sorting all columns
        'sort'      : {0:true,2:true}        //Sort indexes.  Reverses sortAll
    });

    var unitsTime=[];
    $('#rangeTimeUnit > option').each(function(i){
        unitsTime.push({value:this.value,text:this.text})
    })
    var aggrTypes=[];
    $('#aggrType > option').each(function(i){
        aggrTypes.push({value:this.value,text:this.text})
    })

    $.fn.myEditRows = function(type, chartId) {
        //Helper plugin to reduce script content
        switch(type) {
            case 'pie.data':
                return this.myEdit('text',chartId,'name',{title:'Legend', validate: function(){
                    //kludge workaround
                    var $this=$(this);
                    $this.editable('option', 'url', gb_api_base+'/chart/'+chartId+'/series/0/data/'+$this.closest('tr').index());
                }});
                break;
            case 'pie.point':
                return this.myEdit('autocomplete',chartId,'pointId',{title:'Point Name'}, {
                    select: function(e, ui) {
                        var $this=$(this);
                        var editable=$this.data("editable");    //How to do this without using jQuery data()?
                        $this.blur().parent().next().find('button.editable-submit').css('opacity', 1).off('click.prevent');
                        editable.option('params', function() {return {pointId: ui.item.id};});
                        //Currently, pie charts only support a single series
                        editable.option('url', gb_api_base+'/chart/'+chartId+'/series/0/data/'+editable.$element.parent().closest('tr').index())
                    }
                });
                break;
            case 'gauge.point':
                return this.myEdit('autocomplete',chartId,'pointId',{title:'Point Name'}, {
                    select: function(e, ui) {
                        var $this=$(this);
                        var editable=$this.data("editable");    //How to do this without using jQuery data()?
                        $this.blur().parent().next().find('button.editable-submit').css('opacity', 1).off('click.prevent');
                        editable.option('params', function() {return {pointId: ui.item.id};});
                        //Currently, gauge charts only support a single series, and gauges per Highchart can only have a single point
                        editable.option('url', gb_api_base+'/chart/'+chartId+'/series/0/data/0')
                    }
                });
                break;
            case 'time.series':
                return this.myEdit('text',chartId,'name',{title:'Series Name',validate: function(){
                    //kludge workaround
                    var $this=$(this);
                    $this.editable('option', 'url', gb_api_base+'/chart/'+chartId+'/series/'+$this.closest('tr').index());
                }});
                break;
            case 'time.historyTimeValue':
                return this.myEdit('text',chartId,'historyTimeValue',{title:'Time Offset',validate: function(){
                    //kludge workaround
                    var $this=$(this);
                    $this.editable('option', 'url', gb_api_base+'/chart/'+chartId+'/series/'+$this.closest('tr').index());
                }});
                break;
            case 'time.historyTimeUnit':
                return this.myEdit('select',chartId,'historyTimeUnit',{title:'Time Offset Units', source: unitsTime, validate: function(){
                    //kludge workaround
                    var $this=$(this);
                    $this.editable('option', 'url', gb_api_base+'/chart/'+chartId+'/series/'+$this.closest('tr').index());
                }});
                break;
            case 'time.aggrType':
                return this.myEdit('select',chartId,'aggrType',{title:'Aggregate Function', source: aggrTypes, validate: function(){
                    //kludge workaround
                    var $this=$(this);
                    $this.editable('option', 'url', gb_api_base+'/chart/'+chartId+'/series/'+$this.closest('tr').index());
                }});
                break;
            case 'time.point':
                return this.myEdit('autocomplete',chartId,'pointId',{title:'Point Name'}, {
                    params:{trend:1},
                    select: function(e, ui) {
                        var $this=$(this);
                        var editable=$this.data("editable");    //How to do this without using jQuery data()?
                        $this.blur().parent().next().find('button.editable-submit').css('opacity', 1).off('click.prevent');
                        editable.option('params', function() {return {pointId: ui.item.id};});
                        editable.option('url', gb_api_base+'/chart/'+chartId+'/series/'+editable.$element.parent().closest('tr').index())
                        // {term:request.term, type:['real','custom'],trend:1/* , fields:['id','name'] */}, function(json) {
                    }
                });
                break;
        }
    };

    Handlebars.registerHelper('timeUnit', function(unit, value) {
        return {d:'Day',w:'Week',m:'Month',q:'Quarter',y:'Year'}[unit]+(value==1?'':'s');
    });

    //Currently not used.  Might be needed for category chart matrix.
    Handlebars.registerHelper('ifeq', function (a, b, options) {
        if (a == b) { return options.fn(this); }
        return options.inverse(this);
    });

    Handlebars.registerHelper('ifnoteq', function (a, b, options) {
        if (a != b) { return options.fn(this); }
        return options.inverse(this);
    });

    var chartObj=$('#chart-list').data('types');

    function getThemes(masterChartType, highchartType) {
        var themes = [];
        for (var i = 0; i < chartObj[masterChartType][highchartType].themes.length; i++) {
            themes.push({value: chartObj[masterChartType][highchartType].themes[i].id, text: chartObj[masterChartType][highchartType].themes[i].theme});
        }
        return themes;
    }
    function getTypes(masterChartType) {
        var types=[];
        for (var type in chartObj[masterChartType]) {
            types.push({value:type,text:chartObj[masterChartType][type].name});
        }
        return types;
    }

    /* ************  Adding and removing charts ******************* */

    $("#type").change(function() {
        var t=$(this), masterChartType=t.val(), highchartType=t.children("option:selected").data('type');
        if(type=='default') {
            $('#add-category-chart, #add-time-chart, #add-gauge-chart, #theme-div').hide();
        }
        else {
            var themes=getThemes(masterChartType, highchartType);
            $("#theme-div").show();
            var select=$("#themesId").empty();
            for (var i = 0; i < themes.length; i++) {
                select.append($("<option />").val(themes[i].value).text(themes[i].text));
            }
            switch(masterChartType){
                case 'category':
                    $('#add-pie-chart, #add-time-chart, #add-gauge-chart').hide();
                    $("#add-category-chart").show();
                    break;
                case 'pie':
                    $('#add-category-chart, #add-time-chart, #add-gauge-chart').hide();
                    $("#add-pie-chart").show();
                    break;
                case 'time':
                    $('#add-category-chart, #add-gauge-chart, #add-pie-chart, #add-time-chart div.time-interval').hide();
                    $("#add-time-chart").show();
                    break;
                case 'gauge':
                    $('#add-category-chart, #add-pie-chart, #add-time-chart').hide();
                    $("#add-gauge-chart").show();
                    break;
                default:
                    console.log('invalid theme type');
            }
        }
    });

    $(".add").click(function() {$("#dialog-addChart").dialog("open");});
    $("#dialog-addChart").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            var forms = this.querySelectorAll("form"), l=forms.length;
            for (var i = 0; i < l; i++) {
                forms[i].reset();
            }
            $("#type").val('default');
            $('#add-category-chart, #add-pie-chart, #add-time-chart, #add-gauge-chart, #theme-div').hide();
        }
    });

    $("#chart-list .delete").click(function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $row=$(this).closest('tr');
            $.ajax({
                type:'DELETE',
                url:gb_api_base+'/chart/'+$row.data('id'),
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    $row.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting chart: '+xhr.responseJSON.message);
                }
            });
        }
    });

    /* ************  Viewing chart details ******************* */

    var hb = {
        prefix:Handlebars.compile($("#hb_prefix").html()),
        category:Handlebars.compile($("#hb_category").html()),
        pie:Handlebars.compile($("#hb_pie").html()),
        time:Handlebars.compile($("#hb_time").html()),
        gauge:Handlebars.compile($("#hb_gauge").html())
    };
    var dialogEditChart=$('#dialog-editChart')
    .on('click','a.clone-chart',function() {$("#dialog-cloneChart").dialog("open");});
    dialogEditChart.dialog({
        autoOpen    : false,
        resizable   : true,
        height      : 'auto',
        maxHeight   : 1200,
        width       : 1200,
        modal       : true
    });

    $("#dialog-cloneChart").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 300,
        width       : 600,
        modal       : true,
        open        : function() {
            $("#clone-name").val('');
        },
        buttons     : [
            {
                text    : 'SAVE',
                "class"  : 'green wide',
                click    : function() {
                    var name=$('#clone-name').val();
                    if(!name) {
                        alert('Missing chart name');
                        return false;
                    }
                    var dialog=$(this)
                    $.ajax({
                        type:'POST',
                        url:gb_api_base+'/chart/clone/'+dialog.data('id'),
                        data:{name:name},
                        //dataType: 'json',
                        success: function (rsp){dialog.dialog("close");},
                        error: function (xhr) {
                            alert('Error adding chart: '+xhr.responseJSON.message);
                        }
                    });
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).dialog("close");}
            }
        ]
    });

    $('#groupByTime').change(function(){
        if($(this).val()==1) $('#add-time-chart div.time-interval').show()
        else $('#add-time-chart div.time-interval').hide()
    })

    function getEditDialog(chartId, json, nodes) {
        //console.log(nodes)
        $("#dialog-editChartOptions").data('optionsObj', json.optionsObj)
        var html=hb['prefix'](json);
        var dialog=dialogEditChart.html(html+hb[json.type](json)).data('id',json.id);
        dialogEditChart.dialog('open');
        for (var i = 0; i < nodes.length; i++) {
            var name=typeof nodes[i] === 'object'?nodes[i].name:nodes[i];
            switch(name) {
                case 'name': dialog.find('a.hb_name').myEdit('text',chartId,'name',{title:'Name'}); break;
                case 'type': dialog.find('a.hb_type').myEdit('select',chartId,'highchartType',{title:'Type', source: getTypes(json.type), value: json.theme.type}); break;
                case 'theme': dialog.find('a.hb_theme').myEdit('select',chartId,'themesId',{title:'Theme', source: getThemes(json.type, json.theme.type), value: json.theme.id}); break;
                case 'pointNames': dialog.find('a.hb_pointNames').myEdit('select',chartId,'showPointName',{title:'Show Point Names', source: $.jfUtils.getYesNo(), value: json.showPointName?1:0}); break;
                case 'title': dialog.find('a.hb_title').myEdit('text',chartId,null,{title:'Title', url:gb_api_base+'/chart/title/text'}); break;
                case 'subtitle': dialog.find('a.hb_subtitle').myEdit('text',chartId,null,{title:'Subtitle', url:gb_api_base+'/chart/subtitle/text'}); break;
                case 'legend': dialog.find('a.hb_legend').myEdit('select',chartId,null,{title:'Display Legend', url:gb_api_base+'/chart/legend/enabled', source: $.jfUtils.getYesNo(), value: $.jfUtils.isset(() => json.optionsObj.legend.enabled, 1)}); break;
                case 'crosshairY': dialog.find('a.hb_crosshairY').myEdit('select',chartId,null,{title:'Display Y Crosshairs', url:gb_api_base+'/chart/yAxis/crosshair', source: $.jfUtils.getYesNo(), value: $.jfUtils.isset(() => json.optionsObj.yAxis.crosshair, 0)}); break;
                case 'crosshairX': dialog.find('a.hb_crosshairX').myEdit('select',chartId,null,{title:'Display X Crosshairs', url:gb_api_base+'/chart/xAxis/crosshair', source: $.jfUtils.getYesNo(), value: $.jfUtils.isset(() => json.optionsObj.xAxis.crosshair, 0)}); break;
                case 'xaxis': dialog.find('a.hb_xaxis').myEdit('text',chartId,null,{title:'X Axis Title', url:gb_api_base+'/chart/xAxis/title/text'}); break;
                case 'yaxis': dialog.find('a.hb_yaxis').myEdit('text',chartId,null,{title:'Y Axis Title', url:gb_api_base+'/chart/yAxis/title/text'}); break;
                case 'marker': dialog.find('a.hb_marker').myEdit('select',chartId,null,{title:'Display Markers', url:gb_api_base+'/chart/plotOptions/series/marker/enabled', source: $.jfUtils.getYesNo(), value: $.jfUtils.isset(() => json.optionsObj.plotOptions.series.marker.enabled, 1)}); break;

                case 'category.series':
                    dialog.find('a.hb_series').myEdit('text',chartId,'name',{title:'Series Name',validate: function(params){
                        //kludge workaround
                        console.log('url callback', params, this)
                        var $this=$(this);
                        $this.editable('option', 'url', gb_api_base+'/chart/'+chartId+'/series/'+$this.closest('tr').index());
                    }});
                    break;
                case 'time.series': dialog.find('.hb_series').myEditRows('time.series', chartId); break;

                case 'category.point':
                    dialog.find('a.hb_point').myEdit('autocomplete',chartId,'pointId',{title:'Point Name'}, {
                        select: function(e, ui) {
                            var $this=$(this);
                            var editable=$this.data("editable");    //How to do this without using jQuery data()?
                            $this.blur().parent().next().find('button.editable-submit').css('opacity', 1).off('click.prevent');
                            editable.option('params', function() {return {pointId: ui.item.id};});
                            var $td=editable.$element.parent()
                            var category = $td.closest('table').find('th').eq($td.index()).index()-1;
                            var series=$td.parent().index();
                            editable.option('url', gb_api_base+'/chart/'+chartId+'/series/'+series+'/data/'+category)
                        }
                    }).on("shown", function(ev, editable) {
                        const buttons = editable.container.$form.find(".editable-buttons")[0];
                        buttons.insertAdjacentHTML("beforeend", '<button type="button" class="btn btn-default btn-sm editable-delete"><i class="fa fa-trash fa-lg"></i></button>');
                        buttons.children.item(2).addEventListener("click", function(){
                            //Change UX to allow deleting of series and categories this way instead of the X to delete?
                            console.log('delete')
                        })
                    });
                    break;
                case 'time.point':dialog.find('.hb_point').myEditRows('time.point', chartId); break;
                case 'pie.point':dialog.find('.hb_point').myEditRows('pie.point', chartId); break;
                case 'gauge.point':dialog.find('.hb_point').myEditRows('gauge.point', chartId); break;

                case 'category.category':
                    dialog.find('a.hb_category').myEdit('text',chartId,'name',{title:'Category Name', validate: function(){
                        //kludge workaround
                        var $this=$(this);
                        $this.editable('option', 'url', gb_api_base+'/chart/'+chartId+'/category/'+($this.parent().index()-1) );
                    }});
                    break;
                case 'pie.data': dialog.find('a.hb_category').myEditRows('pie.data',chartId);break;

                //Only used by time chart
                case 'rangeTimeValue': dialog.find('a.hb_rangeTimeValue').myEdit('text',chartId,'rangeTimeValue',{title:'Time Range'}); break;
                case 'rangeTimeUnit': dialog.find('a.hb_rangeTimeUnit').myEdit('select',chartId,'rangeTimeUnit',{title:'Time Range Units', source: unitsTime,value:json.rangeTimeUnit}); break;
                case 'intervalTimeValue': dialog.find('a.hb_intervalTimeValue').myEdit('text',chartId,'intervalTimeValue',{title:'Time Interval'}); break;
                case 'intervalTimeUnit': dialog.find('a.hb_intervalTimeUnit').myEdit('select',chartId,'intervalTimeUnit',{title:'Time Interval Units', source: unitsTime,value:json.intervalTimeUnit}); break;
                case 'groupByTime': dialog.find('a.hb_groupByTime').myEdit('select',chartId,'groupByTime',{title:'Group on TimeIntervals', source: $.jfUtils.getYesNo(),value:json.groupByTime}); break;
                case 'boundary': dialog.find('a.hb_boundary').myEdit('select',chartId,'boundary',{title:'Fixed Boundaries', source: $.jfUtils.getYesNo(),value:json.boundary}); break;
                case 'historyTimeValue': dialog.find('.hb_historyTimeValue').myEditRows('time.historyTimeValue', chartId); break;
                case 'historyTimeUnit': dialog.find('.hb_historyTimeUnit').myEditRows('time.historyTimeUnit', chartId); break;
                case 'aggrType': dialog.find('.hb_aggrType').myEditRows('time.aggrType', chartId); break;
                default: console.log('invalid editable type to add to dialog: '+name);
            }
        }
        return dialog;
    }
    $("#chart-list td.chName").click(function(e){
        var chartId=$(this).parent().data('id');
        $("#dialog-cloneChart").data('id',chartId);
        $.getJSON( gb_api_base+'/chart/'+chartId, function(json) {
            var nodes=['name', 'type', 'theme', 'pointNames', 'title', 'subtitle', 'legend', 'crosshairY'];
            switch(json.type){
                case 'category':
                    //series-vertical and categories-horizontal
                    //console.log(JSON.stringify(json));
                    for (var i = 0; i < json.series.length; i++) {
                        for (var j = 0; j < json.categories.length; j++) {
                            if(typeof json.series[i].points[j] === 'undefined') {
                                //No more points so just add to array
                                json.series[i].points.push({id: null, name: null, position: j});
                            }
                            if(json.series[i].points[j].position!==j) {
                                //More point but just not one for this position, so insert empty element
                                json.series[i].points.splice( j, 0, {id: null, name: null, position: j});
                            }
                        }
                    }
                    //console.log(JSON.stringify(json));

                    nodes=nodes.concat(['category.series', 'category.category', 'category.point', 'crosshairX', 'xaxis', 'yaxis']);
                    break;
                case 'pie':
                    //Currently only supports a single series and url is adjusted here and series name is moved to the main data.
                    nodes=nodes.concat(['pie.point', 'pie.data']);
                    break;
                case 'time':
                    //series-vertical and categories are time ranges thus only one
                    $('#hb_groupByTime').change(function(){
                        if(json.groupByTime==1) $('#hb_intervalTimeValue').show()
                        else $('#hb_intervalTimeValue').hide()
                    })
                    nodes=nodes.concat(['time.point', 'time.series', 'crosshairX', 'xaxis', 'yaxis', 'marker', 'rangeTimeValue', 'rangeTimeUnit', 'intervalTimeValue', 'groupByTime', 'boundary', 'historyTimeValue', 'historyTimeUnit', 'aggrType']);
                    break;
                case 'gauge':
                    //highchart gauge charts can have only one data point per series.  Currently only one series is supported
                    json.point=json.series[0].point;
                    nodes=nodes.concat(['gauge.point', 'yaxis']);
                    break;
                default:
                    console.log('json.type of the following is invalid: '+json.type)
            }
            var dialog=getEditDialog(chartId, json, nodes);

            //Do after dialog is complete.
            switch(json.type){
                case 'category':
                    //Need to fix tableDragger to prevent dragging the header
                    tableDragger(document.querySelector('#chartListCategory'), {mode: 'row'})
                    .on('drop', function (oldIndex, newIndex, el, mode) {
                        console.log(oldIndex, newIndex, el, mode)
                        //Start at 1 to skip label row
                        $.ajax({
                            type:'PUT',
                            url:gb_api_base+'/chart/'+chartId+'/series/'+(oldIndex-1),
                            data: {position: newIndex-1},
                            error: function (xhr) {
                                console.log('Error updating position: '+xhr.responseJSON.message);
                                //location.reload();

                            }
                        });
                    });
                    //Need to fix tableDragger to prevent dragging the header
                    tableDragger(document.querySelector('#chartListCategory'), {mode: 'column'})
                    .on('drop', function (oldIndex, newIndex, el, mode) {
                        console.log(oldIndex, newIndex, el, mode)
                        //Subtract 1 for the label column
                        $.ajax({
                            type:'PUT',
                            url:gb_api_base+'/chart/'+chartId+'/category/'+(oldIndex-1),
                            data: {position: newIndex-1},
                            error: function (xhr) {
                                console.log('Error updating position: '+xhr.responseJSON.message);
                                //location.reload();

                            }
                        });
                    });
                    break;
                case 'pie':
                    //Need to fix tableDragger to prevent dragging the header
                    tableDragger(document.querySelector('#chartListPie'), {mode: 'row'})
                    .on('drop', function (oldIndex, newIndex, el, mode) {
                        console.log(oldIndex, newIndex, el, mode)
                        //Subtract 2 for the label row and clone
                        var seriesPosition=0;  //API supports multiple series, but currently this app only supports one series
                        $.ajax({
                            type:'PUT',
                            url:gb_api_base+'/chart/'+chartId+'/series/'+seriesPosition+'/data/'+(oldIndex-2),
                            data: {position: newIndex-2},
                            error: function (xhr) {
                                console.log('Error updating position: '+xhr.responseJSON.message);
                                //location.reload();

                            }
                        });
                    });
                    break;
                case 'time':
                    //Need to fix tableDragger to prevent dragging the header
                    tableDragger(document.querySelector('#chartListTime'), {mode: 'row'})
                    .on('drop', function (oldIndex, newIndex, el, mode) {
                        console.log(oldIndex, newIndex, el, mode)
                        //Subtract 2 for the label row and clone
                        $.ajax({
                            type:'PUT',
                            url:gb_api_base+'/chart/'+chartId+'/series/'+(oldIndex-2),
                            data: {position: newIndex-2},
                            error: function (xhr) {
                                console.log('Error updating position: '+xhr.responseJSON.message);
                                //location.reload();

                            }
                        });
                    });
                    break;
                case 'gauge':
                    //highchart gauge charts can have only one data point per series.  Currently only one series is supported
                    break;
                default:
                    console.log('json.type of the following is invalid: '+json.type)
            }
            //console.log(dialog)
        });
    });

    $('#type-pulldown').editable({
        value: 'all',
        type: 'select',
        source: [
            {value: 'all', text: 'All'},
            {value: 'area', text: 'Area chart'},
            {value: 'bar', text: 'Bar chart'},
            {value: 'column', text: 'Column Chart'},
            {value: 'gauge', text: 'Angular gauge'},
            {value: 'line', text: 'Line chart (time)'},
            {value: 'line_cat', text: 'Line chart (categories)'},
            {value: 'pie', text: 'Pie chart'},
            {value: 'solidgauge', text: 'Solid gauge'},
            {value: 'spline', text: 'Spline chart (time)'},
            {value: 'spline_cat', text: 'Spline chart (categories)'},
        ],
        validate: function(value) {
            $.jfUtils.filter('type',value);
        }
    });

    dialogEditChart.on('click','i.deleteSeries',function(){
        console.log(this)
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $this=$(this);
            $.ajax({
                type:'DELETE',
                //url:gb_api_base+'/chart/'+$this.closest('div.dialog-editChart').data('id')+'/series/'+$this.closest('tr').data('id'),
                url:gb_api_base+'/chart/'+$this.closest('div.dialog-editChart').data('id')+'/series/'+$this.closest('tr').index(),
                //dataType: 'json',
                success: function (rsp){
                    //Instead of reloading page, do dynamicly
                    alert('Success')
                    location.reload();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting chart: '+xhr.responseJSON.message);
                }
            });
        }
    });
    dialogEditChart.on('click','#chartListCategory i.deleteCategory',function(){
        console.log(this)
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $this=$(this);
            $.ajax({
                type:'DELETE',
                //url:gb_api_base+'/chart/'+$this.closest('div.dialog-editChart').data('id')+'/category/'+$this.closest('th').index(),
                url:gb_api_base+'/chart/'+$this.closest('div.dialog-editChart').data('id')+'/category/'+($this.closest('th').index()-1),
                //dataType: 'json',
                success: function (rsp){
                    //Instead of reloading page, do dynamicly
                    alert('Success')
                    location.reload();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting chart: '+xhr.responseJSON.message);
                }
            });
        }
    });
    dialogEditChart.on('click','#chartListPie i.deleteData',function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $this=$(this),tr=$this.closest('tr');
            $.ajax({
                type:'DELETE',
                url:gb_api_base+'/chart/'+$this.closest('div.dialog-editChart').data('id')+'/series/0/data/'+tr.index(),  //Currently, pie only support a single series
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    tr.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting chart: '+xhr.responseJSON.message);
                }
            });
        }
    });
    dialogEditChart.on('click','i.deleteTimeSeries',function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $this=$(this),tr=$this.closest('tr');
            //rangeTimeValue only used for time points
            $.ajax({
                type:'DELETE',
                //url:gb_api_base+'/chart/'+$this.closest('div.dialog-editChart').data('id')+'/series/'+tr.index(),
                url:gb_api_base+'/chart/'+$this.closest('div.dialog-editChart').data('id')+'/series/'+tr.index(),
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    tr.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting chart: '+xhr.responseJSON.message);
                }
            });
        }
    });


    /* ************** Adding category categories and series ********** */
    dialogEditChart.on('click','.addPointCategory',function(){
        $("#dialog-addPointCategory").data('id',$(this).closest('div.dialog-editChart').data('id')).dialog("open");
    });
    $('input[type=radio][name=newCategory]').change(function() {
        $('#categoryPosition').toggle().prev().toggle();
    });
    $('input[type=radio][name=newSeries]').change(function() {
        $('#seriesPosition').toggle().prev().toggle();
    });
    $("#dialog-addPointCategory").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            $(this).find('form')[0].reset();
            var series=$('#seriesPosition').prev().show().next().hide().empty().append($("<option>").attr('value','').text('Select a Series')).change(function(e){
                var $t=$(this),v=$t.val();
                if(v) {$t.prev().val(v).hide();}
                else  {$t.prev().val('').show();}
            });
            var category=$('#categoryPosition').prev().show().next().hide().empty().append($("<option>").attr('value','').text('Select a Category')).change(function(e){
                var $t=$(this),v=$t.val();
                if(v) {$t.prev().val(v).hide();}
                else  {$t.prev().val('').show();}
            });
            var position=0;
            $('#chartListCategory tbody tr').each(function(i){
                //console.log('#chartListCategory tbody tr', this)
                var $t=$(this);
                series.append($("<option>").val(position++).text($t.find('a').first().text()));
            });
            var position=0;
            $('#chartListCategory th').each(function(i){
                if(i) { // Don't add header row
                    var $t=$(this);
                    //console.log('#chartListCategory th', this)
                    category.append($("<option>").val(position++).text($t.first('a').text()));
                }
            });
        },
        buttons     : [
            {
                text    : 'SAVE',
                "class"  : 'green wide',
                click    : function() {
                    //form will be closed only upon success in submit callback
                    $(this).find('form').submit();
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).dialog("close");}
            }
        ]
    });

    /* ************** Adding points to Pie and Time charts ********** */
    dialogEditChart.on('click','.addPointPie',function(){
        var dialog=$(this).closest('div.dialog-editChart');
        $("#dialog-addPointPie").data('id',dialog.data('id')).dialog("open");
    });

    dialogEditChart.on('click','.addTimeSeries',function(){
        var $t=$(this),
        dialog=$('#dialog-addTimeSeries');
        dialog.find('span.historyTimeUnit').text($t.prev().find('a.hb_rangeTimeUnit').text());
        console.log($t,$t.closest('div.dialog-editChart'),$t.closest('div.dialog-editChart').data('id'))
        dialog.data('id',$t.closest('div.dialog-editChart').data('id')).dialog("open");
        //dialog.data('id',$t.parent().find('div.dialog-editChart').data('id')).dialog("open");
        //$("#dialog-addTimeSeries").data('id',dialogEditChart.data('id')).dialog("open");
    });

    $("#dialog-addPointPie, #dialog-addTimeSeries").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            $(this).find('form')[0].reset();
        },
        buttons     : [
            {
                text    : 'SAVE',
                "class"  : 'green wide',
                click    : function() {
                    //form will be closed only upon success in submit callback
                    $(this).find('form').submit();
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).dialog("close");}
            }
        ]
    });

    /* ************** Adding Time Graphs ********** */
    $("#dialog-addTimeSeries input.point_name" ).autocomplete({
        source: function( request, response ) { //Get real trended points
            $.getJSON( gb_api_base+"/points", {term:request.term, type:['real','custom'],trend:1/* , fields:['id','name'] */}, function(json) {
                var data=[];
                for (var i = 0; i < json.length; i++) {
                    data.push({id:json[i].id,label:json[i].name});
                }
                response(data);
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            var $this=$(this),
            $parent=$this.parent();
            $parent.find("input[name='pointId']").val(ui.item.id);
            var legend=$parent.next().find("input.legend");
            if(legend.val()=='') legend.val(ui.item.value);
        }
    });


    $("#dialog-addPointCategory input.point_name,#dialog-addPointPie input.point_name, #add-gauge-chart input.point_name" )
    .autocomplete({
        source: function( request, response ) { //get All Points
            $.getJSON( gb_api_base+"/points", {term:request.term/* , fields:['id','name'] */}, function(json) {
                var data=[];
                for (var i = 0; i < json.length; i++) {
                    data.push({id:json[i].id,label:json[i].name});
                }
                response(data);
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            var $this=$(this),
            $parent=$this.parent();
            $parent.find("input[name='pointId']").val(ui.item.id);
            if($this.data('type')==='pie') {
                var legend=$parent.next().find("input.legend");
                if(legend.val()=='') legend.val(ui.item.value);
            }
        }
    });

    /* **************  Direct editing of highchart option object *********** */

    dialogEditChart.on('click','.editChartOptions',function(){
        $("#dialog-editChartOptions").data('id',$(this).closest('div.dialog-editChart').data('id')).dialog("open");
    });

    $("#dialog-editChartOptions").dialog({
        autoOpen    : false,
        resizable   : true,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            var t=$(this);
            t.children('textarea').val(JSON.stringify(t.data('optionsObj'), null, "\t"));
        },
        buttons     : [
            {
                text    : 'SAVE',
                "class"  : 'green wide',
                click    : function() {
                    var t=$(this);
                    var optionsObj=$.jfUtils.jsonToObj(t.children('textarea').val());
                    if(!optionsObj) return false;
                    $.ajax({
                        type:'PUT',
                        url:gb_api_base+'/chart/'+t.data('id')+'/options',
                        data: JSON.stringify(optionsObj),
                        contentType: "application/json; charset=utf-8",
                        success: function (){
                            t.dialog("close");
                        },
                        error: function (xhr) {
                            console.log(xhr)
                            $.unblockUI();
                            alert('Error adding chart: '+xhr.responseJSON.message);
                        }
                    });
                }
            },
            {
                text    : 'RESET',
                "class"  : 'gray',
                click    : function() {
                    var t=$(this);
                    $.get( gb_api_base+'/chart/'+t.data('id')+'/options',function(optionsObj){
                        t.children('textarea').val(JSON.stringify(optionsObj, null, "\t"));
                    });
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).dialog("close");}
            }
        ]
    });

    $.getJSON( gb_api_base+'/charts/validation', function(validObj) {
        //console.log(validObj)
        /*
        At a minimum, all charts must be send the required base chart data (see api documents) to create the chart without any series or series data.
        If a series is included, it must be included in an array, and must include a name and position (int).
        If data is included, it must be included in an array in a given series, and must include a position and id (point ID).  It may also include a name, otherwise the point name will be used.
        highchartOptions (object) is optional for both the base chart, each series, and each data, and will be included in the highchart option only.
        Example: {"name":"myChartName","type":"solidgauge","themesId":"12","highchartOptions":{},"series":[{"name":"mySeriesName","position":0,"highchartOptions":{},"data":[{"id":"114","position":0,"name":"myDataName","highchartOptions":{}}]}]}
        */
        //Either add contentType: 'application/json' and use JSON.stringify or don't add contentType and use param()
        $('#add-category-chart').myValid(validObj.category, {
            url:gb_api_base+'/charts',
            contentType: "application/json; charset=utf-8",
            data: function(form){
                var commonInputs=document.getElementById('common-inputs');
                console.log(commonInputs)
                var obj = $.jfUtils.formToJSON(commonInputs.elements);
                console.log(obj)
                return JSON.stringify(obj);
            }
        });
        $('#add-pie-chart').myValid(validObj.pie, {
            url:gb_api_base+'/charts',
            contentType: "application/json; charset=utf-8",
            data: function(form){
                //Include series without a point.  This frontend application only implements single series for pie charts, thus calls series "dummy"
                var commonInputs=document.getElementById('common-inputs');
                var obj = $.jfUtils.formToJSON(commonInputs.elements);
                var dataName=this.find('.point_name').val();
                obj.series=[{name:'dummy'}];
                return JSON.stringify(obj);
            }
        });
        $('#add-time-chart').myValid(validObj.time, {
            url:gb_api_base+'/charts',
            contentType: "application/json; charset=utf-8",
            data: function(form){
                var commonInputs=document.getElementById('common-inputs');
                var timeInputs=document.getElementById('add-time-chart');
                var allInputs=Object.assign($.jfUtils.formToJSON(commonInputs.elements), $.jfUtils.formToJSON(timeInputs.elements));
                return JSON.stringify(allInputs);
            }
        });
        $('#add-gauge-chart').myValid(validObj.gauge, {
            url:gb_api_base+'/charts',
            contentType: "application/json; charset=utf-8",
            data: function(form){
                //Include series with a point.  This frontend application only implements single series for gauge charts, thus calls series "dummy"
                var commonInputs=document.getElementById('common-inputs');
                var obj = $.jfUtils.formToJSON(commonInputs.elements);
                var dataName=this.find('.point_name').val();
                obj.series=[{name:null, data: [{pointId:form.pointId.value}] }];
                return JSON.stringify(obj);
            }
        });
        $('#dialog-addPointCategory form').myValid(validObj.categoryPoint, {
            url:function(){return gb_api_base+'/chart/'+$(this).parent().data('id')},
            data: function(form) {
                var data = new FormData(form);
                var arr=[{name: 'pointId', value: data.get('pointId')}];
                arr.push(data.get('newCategory')==1?{name: 'categoryName', value: data.get('categoryName')}:{name: 'categoryPosition', value: data.get('categoryPosition')});
                arr.push(data.get('newSeries')==1?{name: 'seriesName', value: data.get('seriesName')}:{name: 'seriesPosition', value: data.get('seriesPosition')});
                return arr;
            },
        });
        $('#dialog-addPointPie form').myValid(validObj.pieData, {
            //Endpoint expects seriesName||seriesPosition && dataName||dataPosition
            //Will provide 0 for seriesPosition and dataName only
            url:function(){
                var dialog=$(this).parent();
                //return gb_api_base+'/chart/'+dialog.data('id');
                return gb_api_base+'/chart/'+dialog.data('id')+'/series/0';
            },
            success:function (rsp){
                $.unblockUI();
                //{position: 4, name: "LC1 Energy 1 Day", pointId: 239, pointName: "LC1 Energy 1 Week", pointUnit: null}
                var chartId=$('#dialog-addPointPie').data('id');
                var clone=$('#clone-pie');
                console.log(clone.clone(false))
                //Needs to be fixed
                var row=clone.clone(false).removeAttr('id');
                row.find('.hb_category').text(rsp.name).myEditRows('pie.data', chartId);
                row.find('.hb_point').data('value', rsp.point.id).text(rsp.point.name).myEditRows('pie.point', chartId);
                clone.closest('table').find('tbody').append(row);
            }
        });
        $('#dialog-addTimeSeries form').myValid(validObj.timeSeries, {
            url:function(){return gb_api_base+'/chart/'+$(this).parent().data('id')+'/series'},
            //data: function(form) {console.log(this, form)},
            success:function (rsp){
                console.log(rsp, rsp.historyTimeUnit, rsp.aggrType)
                //chartTimeId, aggrType, id, name, pointId, position, timeUnit, timeValue
                var chartId=$("#dialog-cloneChart").data('id');;
                $.unblockUI();
                var clone=$('#clone-time');
                var row=clone.clone(false).removeAttr('id').data('id',chartId);
                console.log(row.find('.hb_historyTimeUnit'), row.find('.hb_aggrType'))
                row.find('.hb_series').text(rsp.name).myEditRows('time.series', chartId);
                row.find('.hb_point').data('value', rsp.point.id).text(rsp.point.name).myEditRows('time.point', chartId);
                row.find('.hb_historyTimeValue').text(rsp.historyTimeValue).myEditRows('time.historyTimeValue', chartId);
                row.find('.hb_historyTimeUnit').data('value', rsp.historyTimeUnit).myEditRows('time.historyTimeUnit', chartId);
                row.find('.hb_aggrType').data('value', rsp.aggrType).myEditRows('time.aggrType', chartId);
                clone.closest('table').find('tbody').append(row);
            }
        });
    });

});