$(function(){
    $(".add").click(function() {$("#dialog-addSandboxPage").dialog("open");});
    $("#dialog-addSandboxPage").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 240,
        width       : 'auto',
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

    $('#dialog-addSandboxPage form').myValid({"rules":{"message":"required"}}, {url:gb_api_base+'/sandbox'});

    $("#sandbox-page-list .delete").click(function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $row=$(this).closest('tr');
            $.ajax({
                type:'delete',
                url:gb_api_base+'/sandbox/'+$row.data('id'),
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    $row.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting sandbox page: '+xhr.responseJSON.errors.join(', '));
                }
            });
        }
    });

});