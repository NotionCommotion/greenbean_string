$(function(){

    $(".custom-report").click(function() {
        $('#custom-report-button').show();
        $('#save-report-button, #report-name').hide();
        $("#dialog-trend-report").dialog('option', 'title', 'Run Custom Report').dialog("open");
    });
    $(".new-report").click(function() {
        $('#custom-report-button').hide();
        $('#save-report-button, #report-name').show();
        $("#dialog-trend-report").dialog('option', 'title', 'Save Stardard Report').dialog("open");
    });

    var validator=false;
    $.getJSON( '/api/reports/validation', function(validObj) {
        console.log(validObj);
        validator = $( "#dialog-trend-report form" ).validate({rules: validObj.rules,messages: validObj.messages});
    });

    $("#dialog-trend-report").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        height      : 500,
        width       : 600,
        modal       : true,
        open        : function() {
            var $dialog=$(this);
            $dialog.find('form')[0].reset();
            $dialog.find('table.points tbody').empty();
            var start=new Date();
            start.setDate(start.getDate()-7)
            var end = new Date();
            end.setHours(0,0,0,0);
            $('#datepickerStart').datepicker("setDate", start);
            $('#datepickerEnd').datepicker("setDate", end );
            selectedPoints=[];
        },
        buttons     : [
            {
                text    : 'Get Data',
                "class"  : 'green wide',
                "id"    :   'custom-report-button',
                click    : function() {
                    var reportName=$( "#report-name input" );   //Name is not required
                    reportName.rules( "remove", 'required' );
                    var valid=validator.form();
                    reportName.rules( "add", 'required' );
                    if(valid) {
                        //Remove name from form!!! (yet it won't hurt anything)
                        var $form=$(this).find('form');
                        //validate points
                        if(selectedPoints.length===0) {
                            alert('At least one point must be selected');
                            return false;
                        }
                        $form.find('table.points tbody tr.point-row').each(function(){
                            if($(this).find("[type='checkbox']:checked").length===0) {
                                alert('At least one point must be selected');
                                return false;
                            }
                        });
                        window.location.href = "/api/reports/custom?data=" + $form.serialize();
                    }
                }
            },
            {
                text    : 'Save Report',
                "class"  : 'green wide',
                "id"    :   'save-report-button',
                click    : function() {
                    $('.existing-reports').removeClass('hidden');
                    $('.empty-list').addClass('hidden');
                    if(validator.form()) {
                        var $form=$(this).find('form');
                        //validate points
                        if(selectedPoints.length===0) {
                            alert('At least one point must be selected');
                            return false;
                        }
                        $form.find('table.points tbody tr.point-row').each(function(){
                            if($(this).find("[type='checkbox']:checked").length===0) {
                                alert('At least one point must be selected');
                                return false;
                            }
                        });
                        $.blockUI();
                        $.ajax({
                            type:'POST',
                            url:'/api/reports',
                            data:$form.serializeArray(),
                            //dataType: 'json',
                            success: function (rsp){
                                //Instead of reloading page, do dynamicly
                                console.log(rsp)
                                alert('Success')
                                location.reload();
                            },
                            error: function (xhr) {
                                $.unblockUI();
                                alert(xhr.responseJSON.message);
                            }
                        });
                    }
                    /*
                    var dialog=$(this);
                    var $form=dialog.find('form');
                    var form=$form[0]
                    var formData = new FormData(form);
                    console.log($form,form,formData)
                    //for (var pair of formData.entries()) {console.log(pair[0]+ ', ' + pair[1]);}
                    */
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).dialog("close");}
            }
        ]
    });

    var dtObj={
        format: "mm/dd/yyyy",
        autoclose: true,
        endDate: new Date(),
    };
    $('#datepickerStart').datepicker(dtObj).on('changeDate', function(e) {
        $('#datepickerEnd').datepicker('setStartDate', e.date);
    });
    $('#datepickerEnd').datepicker(dtObj).on('changeDate', function(e) {
        $('#datepickerStart').datepicker('setEndDate', e.date);
    });

    $( "#group-by" ).selectmenu({width:'100%'});

    var selectedPoints=[];  //Used to ensure given point is not submitted twice

    var formIndex=0;    //Used to link point functions to given point

    $( "#add-point" ).autocomplete({
        source: function( request, response ) {
            $.ajax( {
                url: "/api/points",
                //dataType: "json",
                data: {
                    term:request.term,
                    type:['real','custom'],
                    trend:1,
                    //fields:['id','name']  //id and name are returned by default.
                },
                success: function( json ) {
                    var data=[];
                    for (var i = 0; i < json.length; i++) {
                        if(selectedPoints.indexOf(json[i].id)==-1) data.push({id:json[i].id,label:json[i].name});
                    }
                    response(data);
                }
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            $(this).val('').blur();
            selectedPoints.push(ui.item.id);
            var clone=$('#clone-point');
            var row=clone.clone(true).removeAttr('id');
            row.find('.point').text(ui.item.value);
            row.find('.functions input').attr('name','functions['+formIndex+'][]');
            row.find('.point-id').val(ui.item.id).attr('name','points['+formIndex+']');
            clone.closest('table').find('tbody').append(row);
            formIndex++;
            return false;
        }
    } );

    $("#dialog-trend-report table.points .delete").click(function(){
        var $t=$(this);
        selectedPoints.splice(selectedPoints.indexOf(parseInt($t.prev().val())), 1);
        $t.closest('tr').remove();
    });

    $('#dialog-editReport').dialog({
        autoOpen    : false,
        resizable   : true,
        height      : 'auto',
        maxHeight   : 800,
        width       : 800,
        maxWidth    : 1400,
        modal       : true
    });
    var editTemplate=Handlebars.compile($("#hb_reports").html());
    $(".table.existing-reports tbody td.link").click(function(e){
        var reportsId=$(this).parent().data('id');
        $.getJSON( '/api/reports/'+reportsId, function(json) {
            var dialog=$('#dialog-editReport').html(editTemplate).data('id',json.id).dialog('open');
        });
    });

    $(".schedule-report").click(function() {$("#dialog-schedule-report").dialog("open");});
    $("#dialog-schedule-report").dialog({
        autoOpen    : false,
        resizable   : false,
        height      : 'auto',
        height      : 500,
        width       : 600,
        modal       : true,
        buttons     : [
            {
                text    : 'Get Data',
                "class"  : 'green wide',
                click    : function() {
                    $(this).dialog("close");
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).dialog("close");}
            }
        ]
    });

    function validatePoints(points) {
        //Not currently used
        var error=false;
        if (!Array.isArray(points)) {
            error='Points variable is not an array';
        }
        else if (!points.length) {
            error='At least one point must be provided';
        }
        else {
            for (var i = 0; i < points.length; i++) {
                if(typeof points[i] !== 'object') {
                    error='At lease one of the points is not an object';
                    break;
                }
                else if (!points[i].hasOwnProperty('functions')) {
                    error='At lease one of the points does not have a functions property';
                    break;
                }
                if (!Array.isArray(points[i].functions)) {
                    error='At lease one of the points functions is not an array';
                    break;
                }
                else if (!points[i].functions.length) {
                    error='All points must have at lease one function';
                    break;
                }
                else {
                    for (var j = 0; j < points[i].functions.length; j++) {
                        if(fruits.indexOf(['mean','integral','sum','min','max'])===-1) {
                            error='Only functions mean, integral, sum, min, and max are allowed';
                            break;
                        }
                    }
                }
            }
        }
        return error;;
    }
});