{% import "forms.html" as forms %}

{% set _css = [
'//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css'
] %}
{% set _js = [
'//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js',
'//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js',
'//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js',
'//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/additional-methods.js',
'//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js'
] %}
{% set _jsMin = [
'/lib/gb/js/jquery.editableAutocomplete.js',
'/lib/gb/js/my-validation-methods.js',
'/lib/gb/js/sources.js'
] %}
{% extends "main.html" %}

{% block content %}
<h1>Web Service</h1>
<h1>NOT COMPLETE</h1>
<table class="table">
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Default Virtual LAN</td><td><a href="javascript:void(0)" class="hb_virtualLan">{{virtualLan}}</a></td></tr>
    <tr><td>Webservice URL</td><td><a href="javascript:void(0)" class="hb_url">{{url}}</a></td></tr>
    <tr><td>Webservice Port</td><td><a href="javascript:void(0)" class="hb_port">{{port}}</a></td></tr>
    <tr><td>Webservice Encryption Key</td><td><a href="javascript:void(0)" class="hb_encrypt_key">{{encryptKey}}</a></td></tr>
</table>

{% endblock %}
