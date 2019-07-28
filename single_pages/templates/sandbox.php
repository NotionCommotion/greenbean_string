{% extends "main.html" %}

{% set _js = js |merge([
'//code.highcharts.com/highcharts.js',
'//code.highcharts.com/highcharts-more.js',
'//code.highcharts.com/modules/solid-gauge.js',
'//cdn.greenbeantech.net/libraries/greenbean-public/1.0/dynamic_update.js'
]) %}
{% set _css = css |merge([
'//cdn.greenbeantech.net/libraries/greenbean-public/1.0/dynamic_update.css'
]) %}

{% set _jsMin = [] %}
{% set _cssMin = [] %}

{% block content %}
<h1>Page {{ page }}</h1><hr>
<div id="frontContent" onclick="location.href='/preview/edit/{{page}}'" data-displayunit="{{ displayUnit }}">
    {{ html|raw }}
</div>
{% endblock %}