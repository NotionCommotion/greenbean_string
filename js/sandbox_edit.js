$(function(){
    tinymce.init({
        selector: '#frontContent',
        //extended_valid_elements: 'span.point',
        height: 500,
        menubar: false,
        relative_urls: false,
        extended_valid_elements: 'script[language|type|src]',
        plugins: [
            'link table paste save code lists'
        ],
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent table | link image addPoint addChart | code | save',
        content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            '//www.tinymce.com/css/codepen.min.css',
            '//stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
            //'/lib/gb/css/tinymce.css'
        ],
        setup : function(ed) {
            ed.addButton('addPoint', {
                icon: 'fa fa-info-circle',
                tooltip: 'Add Point',
                onclick : function() {$("#dialog-addPoint").dialog("open");}
            });
            ed.addButton('addChart', {
                icon: 'fa fa-bar-chart',
                tooltip : 'Add Chart',
                onclick : function() {$("#dialog-addChart").dialog("open");}
            });
        },
        save_onsavecallback: function () {
            $.ajax({
                type:'PUT',
                url:gb_api_base+'/sandbox/'+$('#page').val(),
                //Can post either json or form
                data: {html: tinyMCE.activeEditor.getContent()},
                //data: JSON.stringify({html: tinyMCE.activeEditor.getContent()}),
                //contentType: "application/json",
                success: function (rsp){
                    window.location = "../"+$('#page').val();
                }
            });
        }
    });

    var addPointDialog = $("#dialog-addPoint");
    addPointDialog.dialog({
        autoOpen    :false,
        resizable   :false,
        height      :'auto',
        maxHeight   : 400,
        width       :'auto',
        maxWidth   : 300,
        modal       :true
    });
    addPointDialog.find('li').click(function(){
        var $t=$(this),
        ed=tinymce.get('frontContent');
        ed.focus();
        ed.selection.setContent('<span class="GB_point fa fa-info-circle fa-5x" data-id="'+$t.data('id')+'" title="'+$t.text()+'">&nbsp;</span>');
        $t.closest('div.dialog').dialog("close");
    });
    var addChartDialog = $("#dialog-addChart");
    addChartDialog.dialog({
        autoOpen    :false,
        resizable   :false,
        height      :'auto',
        maxHeight   : 400,
        width       :'auto',
        maxWidth   : 300,
        modal       :true
    });
    addChartDialog.find('li').click(function(){
        var $t=$(this),
        ed=tinymce.get('frontContent');
        ed.focus();
        ed.selection.setContent('<span class="GB_chart fa fa-bar-chart-o fa-5x" data-id="'+$t.data('id')+'" title="'+$t.text()+'">&nbsp;</span>');
        $t.closest('div.dialog').dialog("close");
    });

    $("#addResources").click(function() {$("#dialog-addResources").dialog("open");});
    $("#dialog-addResources").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        maxHeight   : 600,
        width       : 600,
        modal       : true,
        open        : function() {
            var table=$(this).find('tbody').empty();
            $.getJSON( "/resources/"+$("#page").val(), function(resources) {
                var clone=$('#resource-clone');
                for (var i = 0; i < resources.length; i++) {
                    addFile(resources[i], clone, table);
                }
            } );
        }
    });

    $('#file').change(function() {
        var fileInput=$(this);  //causes problems? .prop("disabled", true);
        var form = fileInput.parent()[0];
        var data = new FormData(form);  // Create an FormData object
        //data.append("pageId", $("#dialog-addResources").data('page-id'));
        fileInput.prop("disabled", true);
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: gb_api_base+"/resources/"+$("#page").val(),
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 6000,  //Currently small files only
            success: function (file) {
                fileInput.prop("disabled", false).val('');
                var addNew=true;
                $("#dialog-addResources tbody tr").each(function(){
                    var row=$(this);
                    if(file.id==row.data('id')) {
                        addNew=false;
                        var tds=row.find('td');
                        tds.eq(0).find('input').eq(0).prop('checked', true);
                        tds.eq(3).text(file.size);
                        tds.eq(4).text(file.date);
                    }
                })
                if(addNew) {
                    file.linked=true;
                    addFile(file, $('#resource-clone'), $("#dialog-addResources tbody"));
                }
            },
            error: function (e) {
                $("#result").text(e.responseText);
                console.log("ERROR : ", e);
                fileInput.prop("disabled", false);
            }
        });

    });

    $('#dialog-addResources').on('click','.delete',function(){
        var row=$(this).closest('tr');
        if(confirm("Are you sure you wish to delete this file?")) {
            $.ajax({
                type:'DELETE',
                url: gb_api_base+"/resources/"+row.data('id'),
                //dataType: 'json',
                success: function (rsp){
                    row.remove();
                },
                error: function (xhr) {
                    alert('Error deleting resources: '+xhr.responseJSON.message);
                }
            });
        }
    });

    $('#dialog-addResources tbody').on('change','input',function(){
        var input=$(this);
        var row=input.closest('tr');
        var linked=input.prop("checked");
        $.ajax({
            type:'PUT',
            url: gb_api_base+"/resources/"+$("#page").val()+"/"+row.data('id'),
            data: {linked: linked?1:0},
            //dataType: 'json',
            error: function (xhr) {
                input.prop("checked", !linked);
                alert('Error updating resources: '+xhr.responseJSON.message);
            }
        });
    });

    function addFile(resource, clone, tbody) {
        var row = clone.clone().removeAttr('id').data('id', resource.id);
        var tds=row.find('td');
        tds.eq(0).find('input').eq(0).prop('checked', resource.linked);
        tds.eq(1).find('a').eq(0).text(resource.filename).attr('href', resource.file);
        tds.eq(2).text(resource.type);
        tds.eq(3).text(resource.size);
        tds.eq(4).text(resource.date);
        tbody.append(row);
    }

});