$(function(){

    $.fn.myEdit = function(type, pk, name, options, optionsAutocomplete, method) {
        //Helper plugin to reduce script content
        if (typeof method === 'undefined') {
            method='PUT';
        }
        options = Object.assign({
            type: type,
            placement: 'right',
            ajaxOptions: {type: method},
            send: 'always',
            name: name,
            url: 'api/points/'+pk
            }, options);
        if(type=='autocomplete') {
            //editable.params callback set in editableAutocomplete (or callback in these scripts)
            options.type='text';
            var defaults={params:{term:null, type:['real','custom']/* , fields:['id','name'] */}, url: "/api/points"};
            options.autocomplete=optionsAutocomplete?Object.assign(defaults, optionsAutocomplete):defaults;
            this.editableAutocomplete(options);
        }
        else this.editable(options);
        return this;
    };

    $.fn.myEditRows = function(type, pointId) {
        //Helper plugin to reduce script content
        switch(type) {
            case 'custom.sign':
                return this.myEdit('select',null,'sign',{title:'Addition or Subtractive', source: [{value:1,text:'Positive'},{value:0,text:'Negative'}], validate: function(){
                    //kludge workaround
                    var $this=$(this);
                    $this.editable('option', 'url', 'api/points/'+pointId+'/custom/'+$this.closest('tr').data('id'));
                }});
                break;
            case 'custom.subpoint':
                return this.myEdit('autocomplete',null,'pointId',{title:'Select point to include', success: function(response, newValue){
                    //newValue is point name and not ID, so do this way?
                    var $this=$(this);
                    var pointId=$this.data("editable").options.params().pointId;
                    $this.closest('tr').data('id', pointId);
                    }
                    }, {
                        select: function(e, ui) {
                            var $this=$(this);
                            console.log(this)
                            var editable=$this.data("editable");    //How to do this without using jQuery data()?
                            $this.blur().parent().next().find('button.editable-submit').css('opacity', 1).off('click.prevent');
                            editable.option('params', function() {return {pointId: ui.item.id};});
                            editable.option('url', 'api/points/'+pointId+'/custom/'+$this.closest('tr').data('id'));
                        }
                });
                break;
        }
    };

    $('#point-list').sortfixedtable({
        //Need to fix table widths
        'height'    : '600',  //Height of tbody in pixals (not total table!).  Don't include px
        //'myclass'   : 'child-table-div',
        'sortAll'   : false,    //Defaults to sorting all columns
        'sort'      : {0:true,2:true,3:true,4:true,5:true,6:true,7:true,8:true}        //Sort indexes.  Reverses sortAll
    });

    $("#point_type").change(function() {
        switch($(this).val()){
            case 'real':
                $('#addPointWebservice,#addPointBacnet,#addPointAggr,#addPointCust,#addPointDelta,#addPointHistoric, #empty-list').hide();
                $('#point-source').show();
                $("#sourceId").prop('selectedIndex',0);
                break;
            case 'custom':
                $('#addPointWebservice,#addPointBacnet,#point-source, #addPointAggr,#addPointDelta,#addPointHistoric, #empty-list').hide();
                $('#addPointCust').show();
                break;
            case 'aggregate':
                $('#addPointWebservice,#addPointBacnet,#point-source, #addPointCust,#addPointDelta,#addPointHistoric, #empty-list').hide();
                $('#addPointAggr').show();
                break;
            case 'delta':
                $('#addPointWebservice,#addPointBacnet,#point-source, #addPointAggr,#addPointHistoric, #empty-list').hide();
                $('#addPointDelta').show();
                break;
            case 'historic':
                $('#addPointWebservice,#addPointBacnet,#point-source, #addPointAggr,#addPointDelta, #empty-list').hide();
                $('#addPointHistoric').show();
                break;
            default:
                $('#dialog-addPoint .next-step').hide();
                $('#empty-list').show();
        }
    });
    $("#sourceId").change(function() {
        var $this=$(this);
        console.log($this.find(':selected').data('protocol'))
        switch($this.find(':selected').data('protocol')){
            case 'bacnet':
                $('#addPointWebservice').hide();
                var sources_id=$this.val();
                $('#addPointBacnet').show().find('input[name=sourceId]').val(sources_id);
                $('#addPointBacnet .bn-data').each(function(i) {
                    $(this).val('');
                });
                $("#bacnetPoint" ).val('').autocomplete({
                    source: function( request, response ) { //Get existing bacnet objects
                        $.getJSON( "/api/sources/"+sources_id+"/bacnet/deviceobjects", {term:request.term}, function(json) {
                            var data=[];
                            for (var i = 0; i < json.length; i++) {
                                data.push({obj:$.jfUtils.stipObj(json[i],['objectId','objectType','deviceId','unit']),label:json[i].deviceName+':'+json[i].objectName});
                            }
                            response(data);
                        } );
                    },
                    minLength: 4,
                    select: function( event, ui ) {
                        $(this).parent().parent().find('.bn-data').each(function(i,v) {
                            var $v=$(v);
                            $v.val(ui.item.obj[$v.attr('name')]);
                        });
                    }
                });
                break;
            case 'webservice':
                $('#addPointBacnet').hide();
                $('#addPointWebservice').show().find('input[name=sourceId]').val($this.val());
                break;
            default:
                $('#addPointWebservice,#addPointBacnet').hide();
        }
    });

    $.getJSON( 'api/points/validation', function(validObj) {

        $('#addPointBacnet').myValid(validObj.bacnet, {url:'api/points'});
        $('#addPointCust').myValid(validObj.custom, {url:'api/points'});
        $('#addPointAggr').myValid(validObj.aggregate, {url:'api/points'});
        $('#addPointDelta').myValid(validObj.delta, {url:'api/points'});
        $('#addPointHistoric').myValid(validObj.historic, {url:'api/points'});
        $('#dialog-addPointCust form').myValid(validObj.custom_add, {
            url: function(){
                return 'api/points/'+$(this).data('id')+'/custom';
            }, success: function(rsp){
                $.unblockUI();
                var clone=$('#clone-custom');
                var row=clone.clone(false).removeAttr('id').data('id',rsp.id);
                row.find('.hb_subpoint').text(rsp.name).myEditRows('custom.subpoint', rsp.id);
                row.find('.hb_additive').myEditRows('custom.sign', rsp.id).editable('setValue',rsp.sign);//.data('value', rsp.sign)
                clone.closest('table').find('tbody').append(row);
        }});
    });

    $(".add").click(function() {$("#dialog-addPoint").dialog("open");});
    $("#dialog-addPoint").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            $("#point_type").val('default');
            $(this).find('.next-step').hide();
            $('#empty-list').show();
            //reset all form fields?
        }
    });

    $("#dialog-addPointCust").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            $(this).find('form').data('id',$('#customPointList').data('id'))[0].reset();
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

    $.each([ ['#addPointAggr',['real','custom'],true],['#addPointDelta',['real','custom','aggregate'],true],['#addPointHistoric',['real','custom','aggregate','delta'],true]], function( index, value ) {
        $(value[0]+" input[name='pointName']" ).autocomplete({
            source: function( request, response ) {
                var o={term:request.term, type:value[1], fields:['id','name'] };
                if(value[2]) o.trend=1;
                $.getJSON( "/api/points", o, function(json) {
                    var data=[];
                    for (var i = 0; i < json.length; i++) {
                        data.push({id:json[i].id,label:json[i].name});
                    }
                    response(data);
                } );
            },
            minLength: 2,
            select: function( event, ui ) {
                $(this).parent().find("input[name='pointId']").val(ui.item.id);
            }
        });
    });
    $("#dialog-addPointCust #customPointName" ).autocomplete({
        source: function( request, response ) {
            var pointId=$("#dialog-addPointCust" ).find('form').data('id');
            $.getJSON( "/api/points/"+pointId+'/custom', {term:request.term}, function(json) {
                var data=[];
                for (var i = 0; i < json.length; i++) {
                    data.push({id:json[i].id,label:json[i].name});
                }
                response(data);
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            $(this).parent().find("input[name='pointId']").val(ui.item.id);
        }
    });

    $("#point-list .delete").click(function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $row=$(this).closest('tr');
            $.ajax({
                type:'DELETE',
                url:'api/points/'+$row.data('id'),
                //dataType: 'json',
                //data: {force:true}, //force will delete point even if datalogger is not available
                success: function (rsp){
                    $.unblockUI();
                    $row.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting point: '+xhr.responseJSON.message);
                }
            });
        }
    });

    var dialogEditPoint=$('#dialog-editPoint')
    .on('click','.addCustomPoint',function(){
        $("#dialog-addPointCust").dialog("open");
    }).on('click','#customPointList img.delete',function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $this=$(this),
            row=$this.closest('tr');
            $.ajax({
                type:'DELETE',
                url:'api/points/'+$this.closest('table').data('id')+'/custom/'+row.data('id'),
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    row.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting point: '+xhr.responseJSON.message);
                }
            });
        }
    }).dialog({
        autoOpen    : false,
        resizable   : true,
        height      : 'auto',
        maxHeight   : 800,
        width       : 800,
        modal       : true
    });


    var hb = {
        bacnet_gateway:Handlebars.compile($("#hb_bacnet").html()),
        webservice:Handlebars.compile($("#hb_webservice").html()),
        custom:Handlebars.compile($("#hb_custom").html()),
        aggregate:Handlebars.compile($("#hb_aggregate").html()),
        delta:Handlebars.compile($("#hb_delta").html()),
        historic:Handlebars.compile($("#hb_historic").html()),
        custom_report:Handlebars.compile($("#hb_custom_report").html()),
    };

    function getEditDialog(type, pointId, json, nodes) {
        var dialog=dialogEditPoint.html(hb[type](json)).data('id',json.id).dialog('open');
        var unitsTime=[];
        $('#timeUnit > option').each(function(i){
            unitsTime.push({value:this.value,text:this.text})
        })
        var aggrTypes=[];
        $('#aggrType > option').each(function(i){
            aggrTypes.push({value:this.value,text:this.text})
        })


        for (var i = 0; i < nodes.length; i++) {
            var name=typeof nodes[i] === 'object'?nodes[i].name:nodes[i];
            switch(name) {
                case 'name': dialog.find('a.hb_name').myEdit('text',pointId,'name',{title:'Name'});break;
                case 'unit': dialog.find('a.hb_unit').myEdit('text',pointId,'unit',{title:'Units'});break;
                case 'slope': dialog.find('a.hb_slope').myEdit('text',pointId,'slope',{title:'Slope'});break;
                case 'intercept': dialog.find('a.hb_intercept').myEdit('text',pointId,'intercept',{title:'Intercept'});break;
                case 'trend': dialog.find('a.hb_trend').myEdit('select',pointId,'trend',{title:'Trend', source: $.jfUtils.getYesNo(),value:json.trend});break;
                case 'enabled': dialog.find('a.hb_enabled').myEdit('select',pointId,'enabled',{title:'Enabled', source: $.jfUtils.getYesNo(),value:json.enabled});break;
                case 'pollrate': dialog.find('a.hb_pollrate').myEdit('text',pointId,'pollrate',{title:'BACnet Point Pollrate'});break;
                case 'covLifetime': dialog.find('a.hb_covLifetime').myEdit('text',pointId,'covLifetime',{title:'BACnet Point COV Lifetime'});break;
                case 'bacnetPoint':
                    dialog.find('a.hb_bacnet').myEdit('autocomplete',pointId,'pointId',{title:'Point Name', placement: 'bottom'}, { //, url: 'api/points/'+pointId
                        source: function( request, response ) { //Get existing bacnet objects
                            $.getJSON( "/api/sources/"+json.source.id+"/bacnet/deviceobjects", {term:request.term}, function(json) {
                                var data=[];
                                for (var i = 0; i < json.length; i++) {
                                    data.push({obj:$.jfUtils.stipObj(json[i],['objectId','objectType','deviceId','unit']),label:json[i].deviceName+':'+json[i].objectName});
                                }
                                response(data);
                            } )
                        },
                        select: function(e, ui) {
                            var params={deviceId: ui.item.obj.deviceId, objectId:ui.item.obj.objectId, objectType:ui.item.obj.objectType};
                            console.log(ui, params)
                            var $this=$(this);
                            var editable=$this.data("editable");    //How to do this without using jQuery data()?
                            console.log(editable)
                            $this.blur().parent().next().find('button.editable-submit').css('opacity', 1).off('click.prevent');
                            editable.option('params', {bacnetObject: params});
                            editable.option('url', 'api/points/'+pointId);
                        }
                    });
                    break;
                case 'webservice_name': dialog.find('a.hb_webservice_name').myEdit('text',pointId,'webserviceName',{title:'Webservice Point Name'});break;
                //subpoints is for custom point only.
                case 'subpoints': dialog.find('a.hb_subpoint').myEditRows('custom.subpoint',pointId);break;
                case 'additive': dialog.find('a.hb_additive').myEditRows('custom.sign',pointId);break;
                case 'timeValue': dialog.find('a.hb_timeValue').myEdit('text',pointId,'timeValue',{title:'Time Range'});break;
                case 'timeUnit': dialog.find('a.hb_timeUnit').myEdit('select',pointId,'timeUnit',{title:'Time Range Units', source: unitsTime, value:json.timeUnit});break;
                case 'boundary': dialog.find('a.hb_boundary').myEdit('select',pointId,'boundary',{title:'Fixed Boundaries', source: $.jfUtils.getYesNo(), value:json.boundary});break;
                case 'type_aggregate_name': dialog.find('a.hb_type_aggregate_name').myEdit('select',pointId,'aggrType',{title:'Function', source:aggrTypes, value:json.aggrType});break;
                case 'subpoint': dialog.find('a.hb_subpoint').myEdit('autocomplete',pointId,'pointId',{title:nodes[i].title},{params:{trend:1}});break;
                default: alert('invalid editable type to add to dialog: '+name);
            }
        }
        return dialog;
    }

    $("#point-list td.ptName").click(function(e){
        var pointId=$(this).parent().data('id');
        $.getJSON( 'api/points/'+pointId, function(json) {
            //console.log(json)
            //console.log(JSON.stringify(json))
            var nodes=['name', 'unit', 'slope', 'intercept'];
            var type=json.type;
            switch(json.type){
                case 'real':
                    type=json.datanode.type;    //Override type
                    nodes=nodes.concat(['unit', 'trend', 'enabled'], json.datanode.source.type=='bacnet'?['pollrate', 'covLifetime', 'bacnetPoint']:['webservice_name']);
                    break;
                case 'custom':
                    nodes=nodes.concat(['unit', 'subpoints', 'additive']);
                    break;
                case 'aggregate':
                    nodes=nodes.concat(['unit', 'timeValue', 'timeUnit', 'boundary', 'type_aggregate_name', {name:'subpoint', title: 'Select point to aggregate'}]);
                    break;
                case 'delta':
                    nodes=nodes.concat(['unit', 'timeValue', 'timeUnit', {name:'subpoint', title: 'Select point to get delta'}]);
                    break;
                case 'historic':
                    nodes=nodes.concat(['unit', 'timeValue', 'timeUnit', {name:'subpoint', title: 'Select point to get history'}]);
                    break;
                default: alert('invalid type: '+json.type)
            }
            var dialog=getEditDialog(type, pointId, json, nodes);
        });
    });

    /*
    $('#type-pulldown').editable({
    value: 'all',
    type: 'select',
    source: [
    {value: 'all', text: 'All'},
    {value: 'real', text: 'Real'},
    {value: 'custom', text: 'Custom'},
    {value: 'aggregate', text: 'Aggregate'},
    {value: 'historic', text: 'Historic'},
    {value: 'delta', text: 'Delta'},
    ],
    validate: function(value) {
    $.jfUtils.filter('type',value);
    }
    });
    */

    $("#checkAll").click(function(){
        $('#dialog-customPointReport ul input:checkbox').not(this).prop('checked', this.checked);
    });
    $(".customPointReport").click(function() {$("#dialog-customPointReport").dialog("open");});
    $("#dialog-customPointReport").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 1200,
        width       : 1200,
        modal       : true,
        open        : function() {
            $(this).find('form')[0].reset();
            var parent = document.getElementById("parentDiv");
            $('#custom-report-select').show();
            $('#custom-report-display').hide();
            $.getJSON( "/api/points", {type:['custom']/* , fields:['id','name'] */}, function(json) {
                var list = document.getElementById("custom-point-reports");
                while (list.firstChild) {
                    list.removeChild(list.firstChild);
                }
                for (var i = 0; i < json.length; i++) {
                    var li = document.createElement("li");
                    var input = document.createElement("input");
                    input.setAttribute("type", "checkbox");
                    input.setAttribute("name", "id[]");
                    input.setAttribute("value", json[i].id);
                    li.appendChild(input);
                    li.appendChild(document.createTextNode(json[i].name));
                    list.appendChild(li);
                }
            } );
        }
    });
    $( "#dialog-customPointReport form" ).submit(function( event ) {
        event.preventDefault();
        $.getJSON( "/api/points/custom/report", $( this ).serializeArray(), function(json) {
            console.log(json)
            $('#custom-report-select').hide();
            $('#custom-report-display').show().html(hb['custom_report']({custompoints:json}));
        } );
    });

    $('#custom-report-display').click(function(){
        console.log(this)
        /*
        var copyText = document.getElementById("custom-report-display");
        console.log(copyText);
        copyText.select();

        document.execCommand("copy");
        alert("Copied the text: " + copyText.value);
        */
    });
});
