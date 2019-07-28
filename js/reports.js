$(function(){

    var UxState=function(o, $noChangeButtons, $getDataButtons) {
        //Future.  Change to use getters and setters

        var dateEnd = new Date(),
        dateStart = new Date(),
        dateNow = new Date();

        var relativeDates=true; //Used to determine whether seconds since midnight should be added to histTime when requesting chart or download data
        this.setDateMode=function(mode){
            relativeDates=mode?true:false;
        }
        var getSecondsHistory = function() {
            //Used for absolute start/end time.  Adds time since midnight
            switch(o.histTimeUnit) {
                case 'i': var historySeconds= o.histTimeValue * 60; break;
                case 'h': var historySeconds= o.histTimeValue * 60*60; break;
                case 'd': var historySeconds= o.histTimeValue * 60*60*24; break;
                case 'w': var historySeconds= o.histTimeValue * 60*60*24*7; break;
                case 'm': var historySeconds= o.histTimeValue * 60*60*24*30; break;
                case 'q': var historySeconds= o.histTimeValue * 60*60*24*120; break;
                case 'y': var historySeconds= o.histTimeValue * 60*60*24*365; break;
                default: throw "Invalid getSecondsHistory unit: "+o.histTimeUnit;
            }
            var d1=new Date();
            var d2=new Date(d1);
            var milliSeconds=d1-d2.setHours(0,0,0,0);
            //console.log(historySeconds, milliSeconds, o.histTimeValue, o.histTimeUnit, Math.round(historySeconds-milliSeconds/1000)+'s')
            return Math.round(historySeconds+milliSeconds/1000);
        }

        var getDays = function(value, unit) {
            switch(unit) {
                case 'i':return value/1440;break;
                case 'h':return value/24;break;
                case 'd':return value;break;
                case 'w':return 7*value;break
                case 'm':return 30*value;break
                case 'q':return 120*value;break
                case 'y':return 365*value;break
                default: throw "Invalid getDays unit: "+unit;
            }
        }
        this.reload=function(){
            changedState();
        }

        var subtractTime = function(date, value, unit) {
            switch(unit) {
                case 'd':date.setDate(date.getDate() - value);break;
                case 'w':date.setDate(date.getDate() - 7*value);break;
                case 'm':date.setMonth(date.getMonth() - value);break;
                case 'q':date.setMonth(date.getMonth() - 3*value);break;
                case 'y':date.setFullYear(date.getFullYear() - value);break;
                default: throw "Invalid subtractTime value: "+unit;
            }
        }
        var getSampleSize = function(aggrTimeValue, aggrTimeUnit, periodTimevalue, periodTimeUnit) {
            return Math.round(getDays(aggrTimeValue, aggrTimeUnit)/getDays(periodTimeValue, periodTimeUnit));
        }

        var getDays = function(value, unit) {
            switch(unit) {
                case 'i':return value/1440;break;
                case 'h':return value/24;break;
                case 'd':return value;break;
                case 'w':return 7*value;break
                case 'm':return 30*value;break
                case 'q':return 120*value;break
                case 'y':return 365*value;break
                default: throw "Invalid getDays unit: "+unit;
            }
        }

        var getHistTime=function(start, end) {
            var dateStartMonth=start.getMonth();
            var dateEndMonth=end.getMonth();
            var dateStartDay=start.getDate();
            var dateEndDay=end.getDate();
            if(dateStartMonth===dateEndMonth && dateStartDay===dateEndDay) {
                //Year histTime
                var years=start.getFullYear()-end.getFullYear();
                var rs={value: years, unit: years?'y':'d'};    //If zero years, same day and use "days" unit
            }
            else if(dateStartDay==dateEndDay) {
                //Month histTime
                var months=12*(start.getFullYear()-end.getFullYear());
                months+=dateEndMonth-dateStartMonth;
                var rs=months % 3 === 0  ?{value: months/3, unit: 'q'}:{value: months, unit: 'm'};
            }
            else {
                //Day histTime
                var days = Math.round(Math.abs((end.getTime() - start.getTime())/(24*60*60*1000)));
                var rs=days % 7 === 0  ?{value: days/7, unit: 'w'}:{value: days, unit: 'd'};
            }
            return rs;
        }

        var formatDate=function(date) {
            // Not used.
            return date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
        }

        this.getValue=function(property){
            console.log(o)
            switch(property) {
                case 'histTimeValue':case 'histTimeUnit':case 'aggrTimeValue':case 'aggrTimeUnit':case 'periodTimeValue':case 'periodTimeUnit':case 'dateStart':case 'dateEnd':case 'points':
                    return o[property];
                    break;
                case 'dataSave': return {
                    aggrTimeValue: o.aggrTimeValue,
                    aggrTimeUnit: o.aggrTimeUnit,
                    histTimeValue: o.histTimeValue,
                    histTimeUnit: o.histTimeUnit,
                    periodTimeValue: o.periodTimeValue,
                    periodTimeUnit: o.periodTimeUnit,
                    points: o.points
                    };
                case 'dataRequestChart':
                    var points={};
                    for (var id in o.points) {
                        if(o.points[id].length) points[id]=o.points[id];
                    }
                    return relativeDates?
                    {
                        aggrTime: o.aggrTimeValue + o.aggrTimeUnit,
                        histTime: o.histTimeValue + o.histTimeUnit,
                        sampleSize: 80,
                        Accept: 'highchart',
                        points: points
                    }
                    :{
                        aggrTime: o.aggrTimeValue + o.aggrTimeUnit,
                        histTime: getSecondsHistory() + 's',
                        sampleSize: 80,
                        Accept: 'highchart',
                        points: points
                    };
                case 'dataRequestDownload':
                    var points={};
                    for (var id in o.points) {
                        if(o.points[id].length) points[id]=o.points[id];
                    }
                    return relativeDates?
                    {
                        aggrTime: o.aggrTimeValue + o.aggrTimeUnit,
                        histTime: o.histTimeValue + o.histTimeUnit,
                        sampleSize: getSampleSize(o.aggrTimeValue, o.aggrTimeUnit, o.periodTimeValue, o.periodTimeUnit),
                        points: points
                    }
                    :{
                        aggrTime: o.aggrTimeValue + o.aggrTimeUnit,
                        histTime: getSecondsHistory() + 's',
                        sampleSize: getSampleSize(o.aggrTimeValue, o.aggrTimeUnit, o.periodTimeValue, o.periodTimeUnit),
                        points: points
                    };
                case 'dates':
                    dateEnd.setTime(Date.now());
                    dateEnd.setHours(0,0,0,0);
                    if(o.histTimeValue) {
                        subtractTime(dateEnd, o.histTimeValue, o.histTimeUnit)
                    }
                    dateStart.setTime(dateEnd.getTime());
                    subtractTime(dateStart, o.aggrTimeValue, o.aggrTimeUnit)
                    return {start: dateStart, end: dateEnd};
                default:  throw('Invalid property in UxState');
            }
        };
        this.setValue=function(property, value, doNotChangeState){
            switch(property) {
                case 'periodTimeValue': case 'periodTimeUnit': //doNotChangeState=true; break;
                case 'histTimeValue':case 'histTimeUnit':case 'aggrTimeValue':case 'aggrTimeUnit':
                    o[property]=value;
                    break;
                case 'dateStart':
                    dateStart=value;
                    var aggrTime=getHistTime(dateStart, dateEnd);
                    o.aggrTimeValue=aggrTime.value;
                    o.aggrTimeUnit=aggrTime.unit;
                    break;
                case 'dateEnd':
                    dateEnd=value;
                    dateNow.setTime(Date.now());
                    dateNow.setHours(0,0,0,0);
                    var end=getHistTime(dateEnd, dateNow);
                    o.histTimeValue=end.value;
                    o.histTimeUnit=end.unit;
                    break;
                default:  throw('Invalid property in UxState');
            }
            if(!doNotChangeState) changedState();
        };

        this.addAggrTypes=function(pointId, aggrType){
            o.points[pointId].push(aggrType);   //Maybe check if already exists?
            changedState();
        };
        this.removeAggrTypes=function(pointId, aggrType){
            o.points[pointId].splice(o.points[pointId].indexOf(aggrType), 1);
            changedState();
        };
        this.addPoints=function(pointId, aggrTypes){
            o.points[pointId]=aggrTypes;
            changedState();
        };
        this.removePoints=function(pointId){
            delete(o.points[pointId]);
            changedState();
        };

        var parent=this;
        var chart;
        var chartName=$('#name').text();
        var changedState=function() {
            console.log('changedState');
            var dataSave=parent.getValue('dataSave');
            $noChangeButtons.prop('disabled', blueprint === JSON.stringify(dataSave));
            var params=parent.getValue('dataRequestChart');
            if(Object.keys(params.points).length) {
                $getDataButtons.prop('disabled', false);
                $.ajax({
                    type:'GET',
                    //dataType: "json",
                    url: '/api/query/trend',
                    data: params,
                    success: function(data){
                        console.log('chart', data)
                        data.subtitle.text=chartName;
                        chart=Highcharts.chart('report-chart',data);
                    }
                });
            }
            else {
                $getDataButtons.prop('disabled', true);
                if(chart) {
                    //var seriesLength = chart.series.length;
                    //for(var i = seriesLength -1; i > -1; i--) chart.series[i].remove();
                    chart.destroy();
                }
            }
        }

        var blueprint=JSON.stringify(this.getValue('dataSave'));
        //changedState();

        this.load=function(){
            changedState();
        };
    }

    var reportId=$('#id').val();
    //console.log(reportId)
    $('#name').editable({title:'Report Name', placement: 'right', ajaxOptions: {type: "PUT"}, url: '/api/reports/'+reportId, send: 'always'}).toggle(reportId!=0);

    var points={};
    $('#points-table tbody tr.point-row').each(function(){
        var row=$(this), id=row.data('id');
        points[id]=[];
        row.find(':checkbox:checked').each(function(){
            points[id].push(this.value);
        })
        points[id].sort();
    })

    var useNowDate=document.getElementById('end-data-now').checked;
    if(useNowDate) $('#histTimeValue, #histTimeUnit').hide();

    //Constructor makes blueprint of certain DOM elements and keeps track of changes.
    var periodTime=$('#periodTime').data('value');
    var periodTimeValue=periodTime.slice(0,-1);
    var periodTimeUnit=periodTime.slice(-1);
    var uxState=new UxState({
        histTimeValue: useNowDate?0:$('#histTimeValue').text(),
        histTimeUnit: $('#histTimeUnit').data('value'),
        aggrTimeValue: $('#aggrTimeValue').text(),
        aggrTimeUnit: $('#aggrTimeUnit').data('value'),
        periodTimeValue: periodTimeValue,
        periodTimeUnit: periodTimeUnit,
        points: points
        },
        $('.update-report'),
        $('.download-data')
    );

    $('#histTimeValue').editable({title:'End Date', placement: 'right', success:function(response, value) {
        uxState.setValue('histTimeValue',value);
    }});
    $('#histTimeUnit').editable({type: 'select', title:'End Date Units', placement: 'right', source: [{value: 'd', text: 'Days from now'},{value: 'w', text: 'Weeks from now'},{value: 'm', text: 'Months from now'},{value: 'q', text: 'Quarters from now'},{value: 'y', text: 'Years from now'}], success: function(response, value) {
        uxState.setValue('histTimeUnit',value);
    }});
    $('#aggrTimeValue').editable({title:'Duration', placement: 'right', success: function(response, value) {
        uxState.setValue('aggrTimeValue',value);
    }});
    $('#aggrTimeUnit').editable({type: 'select', title:'Duration Units', placement: 'right', source: [{value: 'd', text: 'Days'},{value: 'w', text: 'Weeks'},{value: 'm', text: 'Months'},{value: 'q', text: 'Quarters'},{value: 'y', text: 'Years'}], success: function(response, value) {
        uxState.setValue('aggrTimeUnit',value);
    }});
    $('#periodTime').editable({type: 'select', title:'Trend Interval', placement: 'right', source: [{value: '15i', text: '15 minutes'},{value: '30i', text: '30 minutes'},{value: '1h', text: '1 hour'},{value: '6h', text: '6 hours'},{value: '12h', text: '12 hours'},{value: '1d', text: '1 day'},{value: '1w', text: '1 week'},{value: '1m', text: '1 month'}], success:function(response, value){
        var periodTimeValue=value.slice(0,-1);
        var periodTimeUnit=value.slice(-1);
        uxState.setValue('periodTimeValue', periodTimeValue);
        uxState.setValue('periodTimeUnit', periodTimeUnit);
    }});

    $('#end-data-now').change(function() {
        if(this.checked) {
            uxState.setValue('histTimeValue',0);
        }
        else {
            uxState.setValue('histTimeValue',$('#histTimeValue').editable('getValue',true),true);
            uxState.setValue('histTimeUnit',$('#histTimeUnit').editable('getValue',true));
        }
        $('#histTimeValue, #histTimeUnit').toggle();
    });

    //Question???  Should datepicker.setUTCDate or setDate be used?
    var suppressDatePickerUxChange=false;
    $('#datepickerStart').datepicker({format: "mm/dd/yyyy",autoclose: true,endDate: new Date()}).on('changeDate', function(e) {
        $('#datepickerEnd').datepicker('setStartDate', e.date);
        uxState.setValue('dateStart',e.date, suppressDatePickerUxChange);
        suppressDatePickerUxChange=false;
    });
    $('#datepickerEnd').datepicker({format: "mm/dd/yyyy",autoclose: true,endDate: new Date()}).on('changeDate', function(e) {
        $('#datepickerStart').datepicker('setEndDate', e.date);
        uxState.setValue('dateEnd',e.date, suppressDatePickerUxChange);
        suppressDatePickerUxChange=false;
    });

    $('#date-option').on('change', 'input[type=radio][name=date-option]', function() {
        if(this.value=='relative') {
            uxState.setDateMode(true);
            var $endDataNow=$('#end-data-now');
            var isChecked=$endDataNow.prop('checked')
            $('#histTimeValue').editable('setValue', uxState.getValue('histTimeValue'))
            $('#histTimeUnit').editable('setValue', uxState.getValue('histTimeUnit'))
            $('#aggrTimeValue').editable('setValue', uxState.getValue('aggrTimeValue'))
            $('#aggrTimeUnit').editable('setValue', uxState.getValue('aggrTimeUnit'))
            var histTimeValue=uxState.getValue('histTimeValue');
            if((histTimeValue===0 && !isChecked) || histTimeValue!==0 && isChecked) {
                $endDataNow.prop("checked", !isChecked);
                $('#histTimeValue, #histTimeUnit').toggle();
            }
            uxState.reload();   //So that seconds since midnight are no longer used
        }
        else {
            uxState.setDateMode(false);
            var dates=uxState.getValue('dates');
            suppressDatePickerUxChange=true;    //Prevents chart from requesting an ajax request of data (one time only)
            $('#datepickerEnd').datepicker("setDate", dates.end);
            $('#datepickerStart').datepicker("setDate", dates.start);
        }
        $('.absolute-date, .relative-time').toggle();
    });

    $('#content').on('click', '.open-point', function() {
        $('#add-point').show().focus().blur(function(){
            $(this).hide();
        });
    });

    //Currently, only one format (CSV and not PDF, etc) so don't use a dialog to select the fomrat
    $('#list-header').on('click', '.download-data', function() {
        var params=uxState.getValue('dataRequestDownload');
        params.Accept='csv';
        window.location.assign("/api/query/trend?" + $.param(params));
        //$("#dialog-download-data").Dialog("open");}
    });
    $("#dialog-download-data").Dialog({
        buttons     : [
            {
                text    : 'Get Data',
                "class"  : 'green wide',
                click    : function() {
                    var params=uxState.getValue('dataRequestDownload');
                    var cb=$(this).find('input[type=radio][name=format]:checked');
                    //console.log(params,cb)
                    params.Accept=cb.val();
                    if(cb.closest('div').find('input[type=checkbox]').prop('checked')) params.includeCharts=true;
                    window.location.assign("/api/query/trend?" + $.param(params));
                    //window.location.href = "/api/query/trend?" + $.param(params);
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).Dialog("close");}
            }
        ]
    })

    $('#dialog-download-data').on('change', 'input[type=radio][name=format]', function() {
        $('#dialog-download-data .include-charts').prop('disabled', !['pdf'].includes(this.value))
    })

    $('#list-header').on('click', '.schedule-report', function() {$("#dialog-schedule-report").Dialog("open");});
    $("#dialog-schedule-report").Dialog();
    $('#dialog-schedule-report').on('click', 'a', function() {alert('Not complete');})

    $('#list-header').on('click', '.saved-reports', function() {$("#dialog-saved-reports").Dialog("open");});
    $("#dialog-saved-reports").Dialog({
        height      : 500,
        open        : function(){
            $.blockUI();
            $.getJSON( '/api/reports', function(reports) {
                var clone=$('#clone-report');
                var tbody=clone.closest('table').find('tbody').empty();
                $.each(reports, function(i,report){
                    var row=clone.clone(true).removeAttr('id').data('id',report.id);
                    row.find('a').text(report.name).attr('href','/reports/'+report.id);
                    tbody.append(row);
                });
                $.unblockUI();
            });
        }
    });
    $("#dialog-saved-reports").on("click", "img.delete", function(){
        if (confirm("Are you sure?")) {
            $.blockUI();
            var $row=$(this).closest('tr');
            $.ajax({
                type:'DELETE',
                url:'/api/reports/'+$row.data('id'),
                //dataType: 'json',
                success: function (rsp){
                    $.unblockUI();
                    $row.remove();
                },
                error: function (xhr) {
                    $.unblockUI();
                    alert('Error deleting report: '+xhr.responseJSON.message);
                }
            });
        }
    });

    $('#list-header').on('click', '.update-report', function() {
        var data=uxState.getValue('dataSave');
        data.name=$('#name').text();
        $.ajax({
            type:'PUT',
            url:'/api/reports/'+reportId,
            data:data,
            //dataType: 'json',
            success: function(rsp) {
                $('button.update-report').prop('disabled', true);
            },
            error: function (xhr) {
                alert('Error updating report: '+xhr.responseJSON.message);
            }
        });
    });

    $('#list-header').on('click', '.save-report', function() {
        $("#dialog-save-report").Dialog("open");
    });
    $("#dialog-save-report").Dialog({
        open        : function() {
            $("#report-name").val('');
        },
        buttons     : [
            {
                text    : 'SAVE',
                "class"  : 'green wide',
                click    : function() {
                    var data=uxState.getValue('dataSave');
                    data.name=$('#report-name').val();
                    if(!data.name) {
                        alert('Missing report name');
                        return false;
                    }
                    var dialog=$(this)
                    $.ajax({
                        type:'POST',
                        url:'/api/reports',
                        data:data,
                        //dataType: 'json',
                        success: function (rsp){
                            window.location = "reports/"+rsp.id;
                        },
                        error: function (xhr) {
                            alert('Error adding report: '+xhr.responseJSON.message);
                        }
                    });
                }
            },
            {
                text    : 'CANCEL',
                "class"  : 'gray',
                click    : function() {$(this).Dialog("close");}
            }
        ]
    });

    /*
    var validator=false;
    $.getJSON( '/api/reports/validation', function(validObj) {
    console.log(validObj);
    validator = $( "#dialog-trend-report form" ).validate({rules: validObj.rules,messages: validObj.messages});
    });
    */

    $( "#add-point" ).autocomplete({
        source: function( request, response ) {
            $.ajax( {
                type:'GET',
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
                    var selectedPoints=uxState.getValue('points');
                    //console.log(selectedPoints)
                    for (var i = 0; i < json.length; i++) {
                        if(typeof selectedPoints[json[i].id] === 'undefined') data.push({id:json[i].id,label:json[i].name});
                    }
                    response(data);
                }
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            $(this).val('').blur();
            var clone=$('#clone-point');
            var row=clone.clone(true).removeAttr('id').data('id',ui.item.id);
            row.find('.point').text(ui.item.value);

            var aggrTypes=[];
            row.find(':checkbox:checked').each(function(){
                aggrTypes.push(this.value);
            })
            aggrTypes.sort();
            clone.closest('table').find('tbody').append(row);
            uxState.addPoints(ui.item.id, aggrTypes);
            return false;
        }
    } );

    $("#points-table tbody").on("click", "img.delete", function(){
        var row=$(this).closest('tr');
        uxState.removePoints(row.data('id'));
        row.remove();
    });

    $('#points-table td.functions').on('change', 'input:checkbox', function() {
        console.log(this, this.checked)
        if(this.checked) uxState.addAggrTypes($(this).closest('tr').data('id'), this.value);
        else uxState.removeAggrTypes($(this).closest('tr').data('id'), this.value);
    })

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
                else if (!points[i].hasOwnProperty('aggrTypes')) {
                    error='At lease one of the points does not have a aggrTypes property';
                    break;
                }
                if (!Array.isArray(points[i].aggrTypes)) {
                    error='At lease one of the points aggrTypes is not an array';
                    break;
                }
                else if (!points[i].aggrTypes.length) {
                    error='All points must have at lease one function';
                    break;
                }
                else {
                    for (var j = 0; j < points[i].aggrTypes.length; j++) {
                        if(['mean','integral','sum','min','max'].indexOf(aggrType)===-1) {
                            error='Only functions mean, integral, sum, min, and max are allowed';
                            break;
                        }
                    }
                }
            }
        }
        return error;;
    }

    /*
    var proxied = new Proxy(UxState, {
    get: function(target, prop) {
    console.log({ type: 'get', target, prop });
    return Reflect.get(target, prop);
    },
    set: function(target, prop, value) {
    console.log({ type: 'set', target, prop, value });
    return Reflect.set(target, prop, value);
    }
    });
    */

    uxState.load();

});