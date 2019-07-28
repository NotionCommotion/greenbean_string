$(function(){

    $(".add").click(function() {$("#dialog-add-virtual-lan").dialog("open");});
    $("#dialog-add-virtual-lan").dialog({
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

    $('#dialog-add-virtual-lan form').submit(function(event){
        event.preventDefault();
        $.blockUI();
        $.ajax({
            type:'POST',
            url:'/api/tags/lans',
            data:$(this).find(':input').serializeArray(),
            //dataType: 'json',
            success: function (rsp){
                //Instead of reloading page, do dynamicly
                alert('Success')
                location.reload();
            },
            error: function (xhr) {
                $.unblockUI();
                alert('Error adding virtual LAN: '+xhr.responseJSON.message);
            }
        });
    });

    $("#virtual-lan-list .delete").click(function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $row=$(this).closest('tr');
            $.ajax({
                type:'DELETE',
                url:'/api/tags/lans/'+$row.data('id'),
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    $row.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting virtual LAN: '+xhr.responseJSON.message);
                }
            });
        }
    });

    $('#virtual-lan-list .name').on('init', function(e, edt) {
        edt.options.url = '/api/tags/lans/' +$(this).closest('tr').data('id');
    });
    $("#virtual-lan-list .name").editable({
        type: 'text',
        //pk: null,
        title: 'Virtual LAN Area',
        placement: 'right',
        params: function(params) {
            return {name: params.value};
        },
        ajaxOptions: {type: "PUT"},
        send: 'always',
        //url: '/api/tags/lans/'+$(this).parent().data('id')
    })

    $('ul.xeditable a:not(#timezone)').editable({
        send: 'always',
        ajaxOptions: {type: "PUT"},
        url: '/api/account',
        params: function(params) {
            var parts=params.name.split('.');
            var o={};
            o[parts[0]]={};
            o[parts[0]][parts[1]]=params.value;
            console.log(o)
            return o;
        }
    });

    $( "#timezone" ).editableAutocomplete({
        //type: 'text',
        //placement: 'right',
        ajaxOptions: {type: "PUT"},
        send: 'always',
        //name: 'timezone',
        //id: pk,
        params: function(params) {
            var o={base: {}};
            o.base.timezone=params.value;
            return o;
        },
        //params: {name: 'timezone'},
        title:'Timezone',
        url: '/api/account',
        autocomplete: {
            params:{
                term:null,
                //fields:['id','name']  //id and name are returned by default.
            },
            url: "/api/account/timezones"
        }
    });

    $(".import-data").click(function() {$("#dialog-import-data").dialog("open");});
    $("#dialog-import-data").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        width       : 800,
        modal       : true,
        open        : function() {
            $("#validate-import").show();
            $('#update-import, #display-import').hide();
            $('#importFile').val('');
        }
    });

    $("#checkAll").click(function(){
        $('#dialog-import-data ul input:checkbox').not(this).prop('checked', this.checked);
    });

    $(".cancelImport").click(function(){
        $("#dialog-import-data").dialog('close');
    });

    $( "#dialog-import-data form" ).submit(function( event ) {
        event.preventDefault();
        var upload = new Upload(document.getElementById("importFile"));
        // maby check size or type here with upload.getSize() and upload.getType()
        upload.start("/api/tools/import/update", function (data) {
            console.log(data)
            alert('hello');
            }
            ,this
        );
    });

    $("#importFile").on("change", function (e) {
        var upload = new Upload(this);
        // maby check size or type here with upload.getSize() and upload.getType()
        upload.start("/api/tools/import/validate", function (data) {
            upload.hideProgressBar();
            $("#validate-import").hide();
            $('#update-import').show();
            $('#checkAll').prop('checked', false);
            $('#start-date').text(data.start);
            $('#end-date').text(data.end);
            //$('#update-import-list').empty();
            var list = document.getElementById("update-import-list");
            while (list.firstChild) {
                list.removeChild(list.firstChild);
            }
            for (var i = 0; i < data.points.length; i++) {
                var li = document.createElement("li");
                var input = document.createElement("input");
                input.setAttribute("type", "checkbox");
                input.setAttribute("name", "id[]");
                input.setAttribute("value", data.points[i].id);
                input.checked=data.points[i].remove===0;
                li.appendChild(input);
                var txt=data.points[i].name+' (ID '+data.points[i].id+') Adding '+data.points[i].add+' Removing '+data.points[i].remove;
                li.appendChild(document.createTextNode(txt));
                list.appendChild(li);
            }
        });
    });
});
