//Api source_bacnet_gateway.js
$(function(){

    const FONT = 'fa';     //FONT can be fa for font-awesome, glyphicon for twitter bootstrap, or null or raw for just data
    $.fn.myEdit = function(type, pk, name, options, optionsAutocomplete, method) {
        //Helper plugin to reduce script content
        //Doesn't support autocomplete
        if (typeof method === 'undefined') {
            method='PUT';
        }
        options = Object.assign({
            type: type,
            placement: 'right',
            ajaxOptions: {type: method},
            send: 'always',
            name: name,
            url: gb_api_base+'/sources/'+pk
            }, options);
        this.editable(options);
        return this;
    };

    var sourceId=$('#sourceId').val();

    var virtualLans=[];
    $.each($('#virtualLanId > option'), function( index, value ) {
        virtualLans.push({value:this.value, text:this.text})
    });

    $('ul.control-gateway a.restartGateway, ul.control-gateway a.restartNetwork, ul.control-gateway a.rebootDevice').click(function(){
        var $button=$(this),
        throbber=$button.parent().next().toggle(),
        data={};
        data.method=$button.attr('class');
        $.ajax({
            type: "POST",
            url: gb_api_base+'/sources/'+sourceId+'/gateway',
            dataType: 'json',
            data: data,
            success: function(response){
                throbber.toggle();
                alert('Success');
            },
            error: function(jqXHR, status, err) {
                throbber.toggle();
                console.log(jqXHR, status, err)
                alert('This gateway does not exist.')
            }
        });
    });

    $('a.name').myEdit('text',sourceId,'name',{title:'Name'});
    $('a.defaultVirtualLan').myEdit('select',sourceId,'defaultVirtualLan',{title:'Virtual LAN', source:virtualLans});
    $('a.guid').myEdit('text',sourceId,'guid',{title:'GUID'});
    $('a.reconnectTimeout').myEdit('text',sourceId,'reconnectTimeout',{title:'Reconnect Timeout (seconds)'});
    $('a.responseTimeout').myEdit('text',sourceId,'responseTimeout',{title:'Response Timeout (seconds)'});
    $('a.historyPackSize').myEdit('text',sourceId,'historyPackSize',{title:'Backup Update Size (records)'});
    $('a.devices_id').myEdit('text',sourceId,'deviceId',{title:'BACnet ID'});
    $('a.devices_name').myEdit('text',sourceId,'deviceName',{title:'BACnet Name'});
    $('a.port').myEdit('text',sourceId,'port',{title:'BACnet Port'});
    $('a.timeout').myEdit('text',sourceId,'timeout',{title:'BACnet Timeout (milliseconds)'});
    $('a.discovery_timeout').myEdit('text',sourceId,'discoveryTimeout',{title:'BACnet Discovery Timeout (seconds)'});

    //Discovery start

    var cancel=true;
    var discoverThrobberDiv=document.createElement("div");
    var discoverThrobber=Throbber({size: 20, color: 'black'}).appendTo(discoverThrobberDiv);
    $('button.discovery').click(function(){
        $("#dialog-discovery").dialog('open');
    });
    $("#dialog-discovery").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 800,
        width       : 800,
        modal       : true,
        open        : function() {
            $('#discovery-list').empty();
            $('#dialog-discovery-init, #discover-btn-online').show();
            $('#dialog-discovery-final, #discover-btn-info, #discover-btn-close').hide();
            discoverThrobber.stop();
            $("#checkAll").attr("disabled", false).prop("checked", true); //If previous discovery was performed, will be left as disabled.
        },
        buttons     : [
            {
                text    : 'DISCOVER ONLINE DEVICES',
                id      :   'discover-btn-online',
                "class"  : 'green wide',
                click    : function() {
                    var $t=$(this);
                    discoverThrobber.start();
                    var val;
                    var data={
                        lowDeviceId:(val=$t.find('input[name=lowDeviceId]').val())?val:0,
                        highDeviceId:(val=$t.find('input[name=highDeviceId]').val())?val:4194302,
                        incPrevDiscovered:$('#include-existing').is(':checked')?1:0
                    };
                    $.ajax({
                        type: "GET",
                        url: gb_api_base+'/sources/'+sourceId+'/discoveryDevicesOnline',
                        data: data,
                        success: function(response){
                            discoverThrobber.stop();
                            $('#dialog-discovery-init').hide();
                            $('#dialog-discovery-final').show();
                            $('#discovery-low').text(response.lowDeviceId);
                            $('#discovery-high').text(response.highDeviceId);
                            $("#discover-btn-online").hide();
                            if(response.devices.length) {
                                $("#discover-btn-info").show();
                                var tbody = $("#discovery-list"),
                                clone=$("#discovery-clone");
                                for (var i = 0; i < response.devices.length; i++) {
                                    var row=clone.clone(true).removeAttr('id');
                                    row.find('input').first().val(response.devices[i].id);
                                    row.children('td').eq(1).text('#'+response.devices[i].id);
                                    if(response.devices[i].previouslyDiscovered) {
                                        row.addClass( "previouslyDiscovered" );
                                    }
                                    tbody.append(row);
                                }
                                tbody.find('td.status').toolTip();
                            }
                        },
                        error: function(jqXHR, status, err) {
                            discoverThrobber.stop();
                            console.log(jqXHR, status, err)
                            alert('Error. '+jqXHR.responseJSON.message)
                        },
                        //dataType: 'json'
                    });
                }
            },
            {
                text    : 'DISCOVER DEVICE INFO',
                id      :   'discover-btn-info',
                "class"  : 'green wide',
                click    : function() {
                    discoverThrobber.start();
                    cancel=false;
                    $('#discovery-list input[type="checkbox"]').attr("disabled", true);
                    $("#checkAll").attr("disabled", true);
                    $("#discover-btn-info").hide();
                    processDiscovery(sourceId, $('#discovery-list input[type="checkbox"]:checked:not(.complete)'))
                }
            },
            {
                text    : 'CANCEL',
                id      :   'discover-btn-cancel',
                "class"  : 'gray',
                click    : function() {
                    cancel=true;
                    discoverThrobber.stop();
                    $(this).dialog("close");
                }
            },
            {
                text    : 'CLOSE',
                id      :   'discover-btn-close',
                "class"  : 'gray',
                click    : function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
    $("#dialog-discovery").next('div.ui-dialog-buttonpane').append(discoverThrobberDiv);

    $("#checkAll").click(function(){
        $('#discovery-list input[type="checkbox"]:not(.complete)').not(this)
        .prop('checked', this.checked).change()
        .closest('tr').children('td.status').toolTip('change', this.checked?'Discovery for this device is pending':'Discovery is cancelled')
    });

    $('#discovery-list').on('change', 'input[type="checkbox"]', function() {
        var tds=$(this).closest('tr').children('td')
        if(this.checked) {
            tds.eq(2).text('Pending').toolTip('change', 'Discovery for this device is pending');
            tds.eq(3).children('span').removeClass('glyphicon-ban-circle').addClass('glyphicon-time');
        }
        else {
            tds.eq(2).text('Cancelled').toolTip('change', 'Discovery for this device is cancelled');
            tds.eq(3).children('span').addClass('glyphicon-ban-circle');
        }
    });

    function processDiscovery(id, inputs){
        if(!inputs.exists() || cancel) {
            discoverThrobber.stop();
            $("#discover-btn-cancel").hide();
            $("#discover-btn-close").show();
            $("#discover-btn-info").show();
            $('#discovery-list input[type="checkbox"]:not(.complete)').attr("disabled", false);
            $("#checkAll").attr("disabled", false);
            return;
        }
        var input=$(inputs.shift());
        var tds=input.closest('tr').children('td');
        tds.eq(2).text('In Progress').toolTip('change', 'Discovery for this device is in progress');
        tds.eq(3).children('span').removeClass('glyphicon-time').addClass('glyphicon-refresh');
        $.ajax({
            type: "POST",
            url: gb_api_base+'/sources/'+sourceId+'/discovery/'+input.val(),
            timeout: 240000,
            //async: false,
            success: function(response){
                console.log('success', response)
                tds.eq(2).text('Complete').toolTip('change', response.object_name+': '+response.model_name+' ('+response.vendor_name+')');
                tds.eq(3).children('span').removeClass('glyphicon-refresh').addClass('glyphicon-ok');
                tds.eq(0).find('input:checkbox').addClass('complete');
                processDiscovery(id, inputs)
            },
            error: function(jqXHR, status, err) {
                console.log('error', jqXHR, status, err) //status=error, err=Internal Server Error
                tds.eq(2).text('Error').toolTip('change', 'Error: '+jqXHR.responseJSON.message); //responseJSON not set, only statusText
                tds.eq(3).children('span').removeClass('glyphicon-refresh').addClass('glyphicon-remove-circle');
                processDiscovery(id, inputs)
            },
            dataType: 'json'
        });
        console.log('done')
    }

    //Discovery end

    $('button.point-browser').click(function(){
        $("#dialog-point-browser").dialog('open');
    });
    $("#dialog-point-browser").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 900,
        width       : 900,
        modal       : true,
        open        : function() {
            var t=$(this);
            $.ajax({
                type: "GET",
                url: gb_api_base+'/sources/'+sourceId+'/points',
                data: {font:FONT},
                success: function(points){
                    var tbody=$('#dialog-point-browser tbody').empty();
                    var clone=$('#dialog-point-browser tr.point-clone');
                    $.each(points, function(i,o) {
                        console.log(i,o)
                        var row = clone.clone().removeAttr('id');
                        var tds=row.find('td');
                        tds.eq(0).text(o.deviceId);
                        tds.eq(1).text(o.objectId);
                        tds.eq(2).text(o.objectType);
                        tds.eq(3).text(o.pollrate);
                        tds.eq(4).text(o.covLifetime);
                        tbody.append(row);
                    });
                },
                error: function(jqXHR, status, err) {
                    alert('This gateway does not exist.')
                },
                //dataType: 'json'
            });
        }
    });

    $('button.object-browser').click(function(){
        $("#dialog-object-browser").dialog('open');
    });

    $('#lowDeviceId').editable({title:'Low device ID', placement: 'right', success:function(response, value) {
        var objectBrowser=$('#object-browser');
        objectBrowser.jstree(true).settings.core.data = getJsTreeObj(value, $('#highDeviceId').editable('getValue', true));
        objectBrowser.jstree(true).refresh();
    }});
    $('#highDeviceId').editable({title:'High device ID', placement: 'right', success:function(response, value) {
        var objectBrowser=$('#object-browser');
        objectBrowser.jstree(true).settings.core.data = getJsTreeObj($('#lowDeviceId').editable('getValue', true), value);
        objectBrowser.jstree(true).refresh();
    }});

    function getJsTreeObj(lowDeviceId, highDeviceId) {
        return function (node, cb) {
            var t=this;
            var data={font: FONT};
            switch(node.parents.length) {
                case 0:
                    //Initial load.  Get devices
                    var url=gb_api_base+'/sources/'+sourceId+'/bacnet/devices';
                    data={lowDeviceId: lowDeviceId, highDeviceId:highDeviceId, font: FONT};
                    break;
                case 1:
                    //Get device
                    var url=gb_api_base+'/sources/'+sourceId+'/bacnet/devices/'+node.id;
                    break;
                case 2:
                    //get objects in device
                    //bacnet/{sourceId:[0-9]+}/devices/{deviceId:[0-9]+}/objects/{typeId:[0-9]+}/{objectId:[0-9]+}
                    var url=gb_api_base+'/sources/'+sourceId+'/bacnet/devices/'+node.parent+'/objects';
                    break;
                case 3:
                    //get specific object in device
                    var arr=node.id.split('.');
                    var url=gb_api_base+'/sources/'+sourceId+'/bacnet/devices/'+node.parents[1]+'/objects/'+arr[0]+'/'+arr[1];
                    break;
                default:
                    throw 'Node depth of '+node.parents.length+' is not supported';
            }
            var data={font: FONT};
            $.ajax({
                type: "GET",
                url: url,
                //dataType: 'json',
                data: data,
                success: function(response){
                    cb(response)
                },
                error: function(jqXHR, status, err) {
                    console.log(jqXHR, status, err)
                    alert('error')
                }
            });

        };
    }

    $("#dialog-object-browser").dialog({
        autoOpen    : false,
        resizable   : true,
        height      : 800,
        width       : 1600,
        modal       : true,
        open        : function() {
            $('#object-browser').jstree({
                'core' : {
                    'data' : getJsTreeObj($('#lowDeviceId').editable('getValue', true), $('#highDeviceId').editable('getValue', true))
                }
            });
        }
    });

    $.initialize("#dialog-object-browser a.addPoint", function() {
        $(this).editable({
            pk: 1,
            value: '',
            url: gb_api_base+'/points',
            placement: 'right',
            params: function(params) {
                //originally params contain pk, name and value
                var object=$(this).closest('li:has(ul)');
                var idAndType=object.attr('id').split('.');
                var device=object.parents('li').parents('li');
                var data={
                    name: params.value,
                    sourceId: sourceId,
                    objectId: idAndType[0],
                    objectType: idAndType[1],
                    deviceId: device.attr('id'),
                    //Use default or bacnet values for: unit, slope, intercept, virtualLanId, trend, pollrate, covLifetime.  objectName, deviceName not used.
                    type: "real",
                    protocol: "bacnet",
                    font: FONT
                };
                return data;
            },
            success: function(response, newValue) {
                console.log('success', response, newValue, this);
                //Still need to apply JS Tree to new point.
                return {newValue:'Point'};
            },
            title: 'Add New Point'
        });
        //myEdit('text',sourceId,'name',{title:'Add New Point'});
    });

    $( "#dialog-object-browser" ).on( "click", "a.deletePoint", function() {
        if (confirm("Are you sure?")) {
            console.log('replace')
        }
    })
});
