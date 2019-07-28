//Api sources.js
$(function(){

    $('.source-listxxx').sortfixedtable({
        //Must fix before using
        'height'    : '600',  //Height of tbody in pixals (not total table!).  Don't include px
        //'myclass'   : 'child-table-div',
        'sortAll'   : false,    //Defaults to sorting all columns
        'sort'      : {0:true,1:true,2:true,3:true,4:true,5:true,6:true}        //Sort indexes.  Reverses sortAll
    });

    //Only BacnetGateway and not ModbusGateway or Webservice is currently  supported.
    $("#addBacnetGateway").click(function() {$("#dialog-addBacnetGateway").dialog("open");});
    /*
    $("#addModbusGateway").click(function() {$("#dialog-addModbusGateway").dialog("open");});
    $("#addWebservice").click(function() {$("#dialog-addWebservice").dialog("open");});
    */
    $("#dialog-addBacnetGateway,#dialog-addModbusGateway,#dialog-addWebservice").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            var t=$(this);
            $.ajax({
                type: "GET",
                url:'/api/sources/bacnet/defaults',
                success: function(defaults){
                    defaults.name='';
                    defaults.guid='';
                    defaults.bacnetId='';
                    defaults.bacnetName='';
                    defaults = Object.entries(defaults);
                    for (var i = 0; i < defaults.length; i++) {
                        t.find(':input[name='+defaults[i][0]+']').val(defaults[i][1]);
                    }
                },
                error: function(jqXHR, status, err) {
                    console.log(jqXHR, status, err)
                    alert('error')
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

    $('#dialog-addBacnetGateway form, #dialog-addModbusGateway form, #dialog-addWebservice form').submit(function(event){
        event.preventDefault();
        $.blockUI();
        $.ajax({
            type:'POST',
            url:'/api/sources',
            data:$(this).find(':input').serializeArray(),   //Make sure type=$(this).data('type')
            //dataType: 'json',
            success: function (rsp){
                //Instead of reloading page, do dynamicly
                alert('Success')
                location.reload();
            },
            error: function (xhr) {
                $.unblockUI();
                alert('Error adding source: '+xhr.responseJSON.message);
            }
        });
    });

    $(".source-list .delete").click(function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $row=$(this).closest('tr');
            $.ajax({
                type:'DELETE',
                url:'/api/sources/'+$row.data('id'),
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    $row.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting source: '+xhr.responseJSON.message);
                }
            });
        }
    });
});
