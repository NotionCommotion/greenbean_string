{# Similar to front end but also allows the ability to save/modify/delete a report template. #}
{% import "forms.html" as forms %}

{% set _css = [
'//cdn.greenbeantech.net/libraries/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css'
] %}

{# '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js',#}
{% set _js = [
'//code.highcharts.com/highcharts.js',
'//code.highcharts.com/highcharts-more.js',
'//cdn.greenbeantech.net/libraries/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',
'//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.js',
'//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js',
'//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js',
'//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/additional-methods.js'
] %}
{% set _jsMin = [
'/lib/gb/js/my-validation-methods.js',
'/lib/gb/js/reports.js'
] %}

{% extends "main.html" %}
{% block content %}
<div id="list-header">
    <button type="button" class="btn btn-primary download-data">Download Data</button>
    {% if id %}
    <button type="button" class="btn btn-secondary update-report">Save Report Changes</button>
    <button type="button" class="btn btn-secondary schedule-report">Schedule Report</button>
    <a href="/reports" class="btn btn-info" role="button">Create New</a>
    {% else %}
    <button type="button" class="btn btn-secondary save-report">Save New Report</button>
    {% endif %}
    <button type="button" class="btn btn-success saved-reports">Existing Reports</button>
    <h1>Reports</h1>
</div>
{{ forms.displayErrors(errors??null) }}
<div id="report-header2">
    <a href="javascript:void(0)" id="name">{{ name }}</a>
</div>
<div class="container">
    <div class="row">
        <div class='col-md-3 relative-time'>
            Duration: <a href="javascript:void(0)" id="aggrTimeValue">{{ aggrTimeValue }}</a> <a href="javascript:void(0)" id="aggrTimeUnit" data-value="{{ aggrTimeUnit }}"></a>
        </div>
        <div class='col-md-3 relative-time'>
            End Date: <a href="javascript:void(0)" id="histTimeValue">{{ histTimeValue }}</a> <a href="javascript:void(0)" id="histTimeUnit" data-value="{{ histTimeUnit }}"></a> <input id="end-data-now" type="checkbox"{{ histTimeValue?'':' checked' }}><span>Now</span>
        </div>
        <div class='col-md-3 absolute-date'>
            Start Date:
            <div class="form-group">
                <div class='input-group date' id='datepickerStart'>
                    <input type='text' class="form-control date" name="startDate" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class='col-md-3 absolute-date'>
            End Date:
            <div class="form-group">
                <div class='input-group date' id='datepickerEnd'>
                    <input type='text' class="form-control date" name="endDate" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class='col-md-3'>Download Sample Period: <a href="javascript:void(0)" id="periodTime" data-value="{{ periodTimeValue~periodTimeUnit }}"></a></div>
    </div>
    <div class="row">
        <div class='col-md-6'>
            <div class="form-group" id="date-option">
                Select time range by:
                <label class="radio-inline"><input type="radio" name="date-option" value="relative" checked>Relative Time</label>
                <label class="radio-inline"><input type="radio" name="date-option" value="absolute">Absolute Dates</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class='col-md-2'><a class="open-point" href="javascript:void(0)" data-toggle="tooltip" title="Add point to report"><span class="glyphicon glyphicon-plus"></span>Add Point</a></div>
        <div class='col-md-4'>
            <input type='text' id="add-point" class="form-control" />
        </div>
    </div>
</div>
<table id="points-table" class="table">
    <thead>
        <tr id="clone-point" class="point-row">{{ forms.getPoints('',['mean']) }}</tr>
    </thead>
    <tbody>
        {% for point in points %}
        <tr data-id="{{ point.id }}" class="point-row">{{ forms.getPoints(point.name,point.aggrTypes) }}</tr>
        {% endfor %}
    </tbody>
</table>
<input type="hidden" id="id" value="{{ id }}">

<div id="report-chart"></div>

<div id="dialog-saved-reports" title="Saved Report" style="display:none">
    <h3>Existing Report List</h3>
    <table>
        <thead>
            <tr>
                <td>Name</td>
                <td></td>
            </tr>
            <tr id="clone-report">
                <td><a></a></td>
                <td><img alt="Delete Report" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Report" class="vtip delete" height="16" width="16"></td>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div id="dialog-save-report" title="Save Report" style="display:none">
    <form>
        <div class="form-group">
            <label>Name</label>
            <input type="text" id="report-name" name="name" class="form-control" placeholder="Report Name">
        </div>
    </form>
</div>

<div id="dialog-schedule-report" title="Schedule Report" style="display:none">
    <ul>
        <li><a href='javascript:void(0)'>Select Schedule Date</a></li>
        <li><a href='javascript:void(0)'>Select Delivery Recipients</a></li>
        <li><a href='javascript:void(0)'>Select Report Format</a></li>
    </ul>
</div>

<div id="dialog-download-data" title="Download Data" style="display:none">
    <div class="radio">
        <label><input type="radio" name="format" value="csv" checked>CSV File</label>
    </div>
    <div class="radio">
        <label><input type="radio" name="format" value="pdf">PDF File</label><label class="checkbox-inline"><input type="checkbox" value="1" class='include-charts' disabled>Include Charts</label>
    </div>
</div>
{% endblock %}