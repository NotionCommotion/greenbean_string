$(function() {

    $.getJSON( '/api/helpdesk/validation', function(validObj) {
        $('#dialog-new-ticket form').myValid(validObj.newTicket, {url:'/api/helpdesk'});
        $('#dialog-view-ticket form').myValid(validObj.updateTicket, {type:'put', url:function(){return '/api/helpdesk/'+$(this).data('ticketId');}});
    });

    //$("#ticket-list").advancedtable({searchField: "#search-window-filter", loadElement: "#loader", searchCaseSensitive: false, ascImage: "/lib/stdimages/up.png", descImage: "/lib/stdimages/down.png"});

    $(".open-new-ticket").click(function() {$("#dialog-new-ticket").dialog("open");});
    $("#dialog-new-ticket").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 600,
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

    $(".subject").click(function() {
        var data=$(this).closest('tr').data('data')
        $('#ticket-subject').text(data.subject);
        $('#ticket-topic').text(data.topic);
        $('#dialog-view-ticket form').data('ticketId', data.ticketId).find('textarea').val('');
        for (var i = 0; i < data.threads.length; i++) {
            var clone=$('#tread-clone').clone().removeAttr('id').removeClass('hidden');
            clone.find('.message').text(data.threads[i].message);
            clone.find('.msg-header').text('['+(i===0?'Created: ':'Updated: ')+data.threads[i].date+' by '+data.threads[i].name+']');
            $('#message-list').append(clone);
        }
        $('#statusId').editable('setValue',data.statusId).editable('option','url','/api/helpdesk/'+data.ticketId);
        $("#dialog-view-ticket").dialog("open");
    });
    $('#statusId').editable({
        placement: 'bottom',
        source: [{value: 1, text: 'Open'},{value: 3, text: 'Closed'}],
        send: 'always',
        type: "select",
        title: "Change Status",
        validate: function(value) {
            $(this).editable('option', 'ajaxOptions', {type: value==3?'DELETE':'POST'});
        }
    });

    $('#filter-tickets').editable({
        placement: 'right',
        source: [{value: 0, text: 'All Tickets'},{value: 1, text: 'Open Tickets'},{value: 3, text: 'Closed Tickets'}],
        validate: function(value) {
            window.location.href=window.location.pathname+(value&&value!='0'?'?statusId='+value:'');
        },
        type: "select",
        title: "Filter By"
    });

    $("#dialog-view-ticket").dialog({
        autoOpen    : false,
        resizable   : false,
        height      :'auto',
        maxHeight   : 700,
        width       : 1000,
        modal       : true
    });
});
