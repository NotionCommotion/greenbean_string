$(function(){

    alert('source_webservice.js is not complete');
    $(".source-list td.sourceName").click(function(e){
        var sourceId=$(this).parent().data('id');
        $.getJSON( 'api/sources/'+sourceId, function(json) {
            function virtualLans(){
                var virtualLans=[];
                $.each($('#default-virtual-lans').data('virtual-lans'), function( index, value ) {
                    virtualLans.push({value:this.id,text:this.name})
                });
                return virtualLans;
            }
            var dialog=$('#dialog-editSource').html(hb[json.type+json.protocol](json)).data('id',json.id).dialog('open');
            dialog.find('a.hb_name').myEdit('text',sourceId,'name',{title:'Name'});
            dialog.find('a.hb_virtualLan').myEdit('select',sourceId,'virtualLanId',{title:'Virtual LAN', source:virtualLans,value:json.virtualLanId, pk: sourceId, url: 'api/sources/'+sourceId});
            switch(json.type){
                case 'gateway':
                    dialog.find('a.hb_guid').myEdit('text',sourceId,'guid',{title:'GUID'});
                    dialog.find('a.hb_reconnectTimeout').myEdit('text',sourceId,'reconnectTimeout',{title:'Reconnect Timeout (seconds)'});
                    dialog.find('a.hb_responseTimeout').myEdit('text',sourceId,'responseTimeout',{title:'Response Timeout (seconds)'});
                    dialog.find('a.hb_historyPackSize').myEdit('text',sourceId,'historyPackSize',{title:'Backup Update Size (records)'});
                    break;
                case 'server':
                    break;
            }
            switch(json.protocol){
                case 'bacnet':
                    var discovery=dialog.find('button.discovery').eq(0),
                    id=discovery.parent().parent().data('id'),
                    discoveredDevices=discovery.parent().next().next().next().show();
                    syncDevice=discoveredDevices.next().hide();
                    createDiscoveryTable(discoveredDevices,id);
                    discovery.click(function(){
                        $("#dialog-discovery").data('id',id).dialog('open');
                    });
                    dialog.find('button.syncronize').click(function(){
                        $("#dialog-syncBacnet").data('id',id).dialog('open');
                    });
                    dialog.find('button.restartGateway, button.restartNetwork, button.reboot').click(function(){
                        ajaxPost(this,id)
                    });
                    dialog.find('a.hb_devices_id').myEdit('text',sourceId,'deviceId',{title:'BACnet ID'});
                    dialog.find('a.hb_devices_name').myEdit('text',sourceId,'deviceName',{title:'BACnet Name'});
                    dialog.find('a.hb_port').myEdit('text',sourceId,'port',{title:'BACnet Port'});
                    dialog.find('a.hb_timeout').myEdit('text',sourceId,'timeout',{title:'BACnet Timeout (milliseconds)'});
                    dialog.find('a.hb_discovery_timeout').myEdit('text',sourceId,'discoveryTimeout',{title:'BACnet Discovery Timeout (seconds)'});
                    break;
                case 'webservice':
                    //Still need to implement various scope for Webservice gateway.
                    dialog.find('a.hb_encrypt_key').myEdit('text',sourceId,'encryptKey',{title:'Webservice Encrypt Key'});
                    dialog.find('a.hb_url').myEdit('text',sourceId,'url',{title:'Webservice URL'});
                    dialog.find('a.hb_port').myEdit('text',sourceId,'port',{title:'Webservice Port'});
                    dialog.find('a.hb_timeout').myEdit('text',sourceId,'timeout',{title:'Webservice Timeout (seconds)'}); //Not used for server
                    dialog.find('a.hb_pollrate').myEdit('text',sourceId,'pollrate',{title:'Poll Rate (seconds)'});  //Not used for server
                    break;
            }
        });
    });
});
