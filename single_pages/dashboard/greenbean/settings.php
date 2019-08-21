{% import "forms.html" as forms %}
{% include 'dashboard/menu.html' %}
<h2>Settings</h2>
{{ forms.displayErrors(errors??null) }}
<h3>Virtual Lans</h3>
<p>All real points must be assigned to a virtual LAN.  Recommended maximum number of points per virtual LAN is 100.  Should a typical building fall with in this limit, recommend using each building as a virtual LAN.  Note that there are some features that are not supported between points in different virtual LANs.</p>
{% set empty = virtualLans is empty %}

<table class='table' id='virtual-lan-list'{{ empty?' style="display:none;"'}}>
<thead>
    <tr>
        <td>Name</td>
        <td></td>
    </tr>
</thead>
<tbody>
    {% set virtualLansAvailable = [] %}
    {% for row in virtualLans %}
    {% set virtualLansAvailable = virtualLansAvailable|merge([{value: row.id, text: row.name}]) %}
    <tr data-id="{{ row.id }}">
        <td><a href='javascript:void(0)' class='name'>{{ row.name }}</a>{{ row.id==defaultValues.base.virtualLanId?' (PRIMARY)' }}</td>
        <td><i class="delete fa fa-trash fa-lg" data-toggle="tooltip" data-placement="top" title="Delete Virtual LAN"></i></td>
    </tr>
    {% endfor %}
</tbody>
</table>
<h1 class="empty-list{{ not empty?' hidden' }}">You have no virtual LANs.</h1>
<a href='javascript:void(0)' class='add'>Add New</a>

<div id="dialog-add-virtual-lan" title="Add New Virtual LAN" style="display:none">
    <form>
        <div class="form-group">
            <input type="text" name="name" class="form-control" placeholder="Add New Virtual LAN">
        </div>
    </form>
</div>

<div id="dialog-edit-virtual-lan" title="Virtual LAN Info" style="display:none"></div>

<h3>BACnet Settings</h3>
<ul class='editable'>
    <li>Pollrate: <a href='javascript:void(0)' data-name='bacnet.pollrate' title='Pollrate'>{{ defaultValues.bacnet.pollrate }}</a></li>
    <li>COV Lifetime: <a href='javascript:void(0)' data-name='bacnet.covLifetime' title='COV Lifetime'>{{ defaultValues.bacnet.covLifetime }}</a></li>
    <li>Port: <a href='javascript:void(0)' data-name='bacnet.port' title='Port'>{{ defaultValues.bacnet.port }}</a></li>
    <li>Timeout: <a href='javascript:void(0)' data-name='bacnet.timeout' title='Timeout'>{{ defaultValues.bacnet.timeout }}</a></li>
    <li>Discovery Timeout: <a href='javascript:void(0)' data-name='bacnet.discoveryTimeout' title='Discovery Timeout'>{{ defaultValues.bacnet.discoveryTimeout }}</a></li>
    <li>Units Utilized: <a href='javascript:void(0)' data-name='bacnet.unit' data-value="{{ defaultValues.bacnet.unit }}" title='Use Units' data-type='select' data-source="[{value: 1, text: 'Yes'},{value: 0, text: 'No'}]"></a></li>
</ul>
{#
<h3>Webservice Settings (not currently implemented)</h3>
<ul class='editable'>
    <li>Pollrate: <a href='javascript:void(0)' data-name='webservice.pollrate' title='Pollrate'>{{ defaultValues.webservice.pollrate }}</a></li>
    <li>Port: <a href='javascript:void(0)' data-name='webservice.port' title='Port'>{{ defaultValues.webservice.port }}</a></li>
    <li>Timeout: <a href='javascript:void(0)' data-name='webservice.timeout' title='Timeout'>{{ defaultValues.webservice.timeout }}</a></li>
</ul>
#}
<h3>Other Settings</h3>
<ul class='editable'>
    <li>Timezone: <a href='javascript:void(0)' data-name='base.timezone' title='Timezone' id="timezone">{{ defaultValues.base.timezone }}</a></li>
    <li>Trend Points: <a href='javascript:void(0)' data-name='realPnts.trend' data-value="{{ defaultValues.realPnts.trend }}" title='Trend Points' data-type='select' data-source="[{value: 1, text: 'Yes'},{value: 0, text: 'No'}]"></a></li>
    <li>Default Virtual LAN: <a href='javascript:void(0)' data-name='base.virtualLanId' data-value="{{ defaultValues.base.virtualLanId }}" title='Default Virtual LAN' data-type='select' data-source='{{ virtualLansAvailable|json_encode()|raw }}'></a></li>
    <li>Rounding Precision: <a href='javascript:void(0)' data-name='base.roundingPrecision' data-value="{{ defaultValues.base.roundingPrecision }}" title='Default Rounding Precision' data-type='select' data-source="[{value: 3, text: '3 digits'},{value: 4, text: '4 digits'},{value: 5, text: '5 digits'},{value: 6, text: '6 digits'}]"></a></li>
    <li>Display for NULL Point Values: <a href='javascript:void(0)' data-name='base.setNullPointTo' data-value="{{ defaultValues.base.setNullPointTo }}" title='Display for NULL Point Values'></a></li>
    <li>Allow NULL values in Assembled Points: <a href='javascript:void(0)' data-name='base.allowNullInAssembledPoint' data-value="{{ defaultValues.base.allowNullInAssembledPoint }}" title='Allow NULL values in Assembled Points' data-type='select' data-source="[{value: 1, text: 'Yes'},{value: 0, text: 'No'}]"></a></li>
    <li>Display Corrupt Chart: <a href='javascript:void(0)' data-name='base.displayCorruptChart' data-value="{{ defaultValues.base.displayCorruptChart }}" title='Display Corrupt Chart' data-type='select' data-source="[{value: 1, text: 'With zero as value'},{value: 0, text: 'With NULL as value'}]"></a></li>
    <li>Maximum point shelf life: <a href='javascript:void(0)' data-name='base.staleSecondsTime' data-value="{{ defaultValues.base.staleSecondsTime }}" title='Maximum point shelf life' data-type='select' data-source="[{value: 3600, text: 'One Hour'},{value: 21600, text: 'Six Hours'},{value: 43200, text: 'Twelve Hours'},{value: 86400, text: 'One Day'},{value: 604800, text: 'One Week'}]"></a></li>
</ul>
<!--
<h3>Chart Settings (future - make configurable on a per chart basis)</h3>
<ul class='editable'>
<li>Tickmarks: <a href='javascript:void(0)' data-name='chart.tickmarks' data-value="1" title='Chart Tickmarks' data-type='select' data-source="[{value: 1, text: 'Yes'},{value: 0, text: 'No'}]"></a></li>
</ul>
-->
<h3>REST API <span class="instructions">(Change by editing the configuration file in the root directory)</span></h3>
<ul>
    <li>REST API URL: {{ datalogger.ip }}</li>
    <li>REST API Encryption Code: <span>{{ datalogger.key }}</span></li>
</ul>
<h3>Tools</h3>
<ul>
    <li><a href='javascript:void(0)' class="import-data">Import Data</a></li>
</ul>
<div id="dialog-import-data" title="Import Data" style="display:none">
    <div>
        <h4>WARNING</h4>
        <p>Take care not to delete your data.  All existing data which falls withing the first and last datetime will be deleted, however, whether this will occur will be identified by the system and you will be given the opertunity to cancel.</p>
        <p>The first column of the CSV file must have datetimes in <a href="https://tools.ietf.org/html/rfc3339" target="_blank">RFC 3339</a> format.  The first row of the CSV file must have valid point names.  The top left cell will be ignored.</p>
    </div>
    <hr>
    <div id="validate-import">
        <div class="float-left">
            <label for="importFile">Select CSV File:</label>
            <input type="file" id="importFile" name="importFile" accept=".csv" />
            <input type="button" class="cancelImport" value="CANCEL"/>
        </div>
    </div>
    <div id=update-import>
        <h4>Select the points you wish to update.</h4>
        <p>Date range from <span id="start-date"></span> to <span id="end-date"></span></p>
        <form>
            <p>Reason for import:</p>
            <textarea cols="100" rows="5" name="reason" class="boxsizingBorder"></textarea>
            <div class="float-left">
                <input type="checkbox" id="checkAll" >
                <p>Check All</p>
                <input type="submit" value="Submit">
                <input type="button" class="cancelImport" value="CANCEL"/>
            </div>
            <ul id="update-import-list"></ul>
        </form>
    </div>
    <div id=display-import>Success!</div>
</div>
