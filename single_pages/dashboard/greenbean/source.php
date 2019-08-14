{% import "forms.html" as forms %}
{% include 'dashboard/menu.html' %}
<div id="list-header">
    <button class="btn btn-primary" id='addBacnetGateway'>Add New</button>
    <h1>Data Sources - BACnet Gateway</h1>
    {{ forms.displayErrors(errors??null) }}
</div>

<div id="list-table-div">
    <table class='table source-list'>
        <thead>
            <tr>
                <th>Name</th>
                <th id="default-virtual-lans" data-virtual-lans='{{ virtualLans|json_encode() }}'>Default Virtual LAN</th>
                <th>Status</th>
                <th>GUID</th>
                <th>Device ID</th>
                <th>Timeout (s)</th>
                <th>Discovery Timeout (s)</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% for row in sources.bacnetGateways %}
            <tr data-id="{{ row.id }}">
                <td class="sourceName link"><a href='source/{{ row.id }}'>{{ row.name }}</a></td>
                <td>{{ row.virtualLanName }}</td>
                <td>{{ row.status?'ONLINE':'OFFLINE' }}</td>
                <td>{{ row.guid }}</td>
                <td>{{ row.device_id }}</td>
                <td>{{ row.timeout }}</td>
                <td>{{ row.discovery_timeout }}</td>
                <td><img alt="Delete Source" src="{{ gb_img_base }}/delete.png" title="Delete Source" class="vtip delete" height="16" width="16"></td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
</div>
{#
<h2>Gateways - MODBUS</h2>
<table class='table source-list'>
    <thead>
        <tr>
            <td>Name</td>
            <td>Status</td>
            <td>GUID</td>
            <td>Poll Rate</td>
            <td>Modbus URL</td>
            <td>Modbus Port</td>
            <td>Modbus Encryption Code</td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        {% for row in sources.modbusGateways %}
        <tr data-id="{{ row.id }}">
            <td class="sourceName link">{{ row.name }}</td>
            <td>{{ row.status?'ONLINE':'OFFLINE' }}</td>
            <td>{{ row.guid }}</td>
            <td>{{ row.pollrate }}</td>
            <td>{{ row.url }}</td>
            <td>{{ row.port }}</td>
            <td>{{ row.encryptKey }}</td>
            <td><img alt="Delete Source" src="{{ gb_img_base }}/delete.png" title="Delete Source" class="vtip delete" height="16" width="16"></td>
        </tr>
        {% endfor %}
    </tbody>
</table>
<br>
<a href='javascript:void(0)' id='addModbusGateway'>Add New Gateway (Modbus)</a>
<p class="tempStuff">There are no available dataloggers.</p>
<h2>WEBSERVICES</h2>
<table class='table source-list'>
    <thead>
        <tr>
            <td>Name</td>
            <td>Webservice URL</td>
            <td>Webservice Port</td>
            <td>Webservice Encryption Code</td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        {% for row in sources.webServices %}
        <tr data-id="{{ row.id }}">
            <td class="sourceName link">{{ row.name }}</td>
            <td>{{ row.url }}</td>
            <td>{{ row.port }}</td>
            <td>{{ row.encryptKey }}</td>
            <td><img alt="Delete Source" src="{{ gb_img_base }}/delete.png" title="Delete Source" class="vtip delete" height="16" width="16"></td>
        </tr>
        {% endfor %}
    </tbody>
</table>
<a href='javascript:void(0)' id='addWebservice'>Add New Webservice Connection</a>
<p class="tempStuff">The only available Soap server is URL "http://soap.badobe.com", Port 80, encryption key "soap_key".  Pollrate is fixed at 20 seconds (will be changed to 10 minutes).</p>
#}
<div id="dialog-addBacnetGateway" title="Add New BACnet Gateway" style="display:none">
    <form data-type="bacnet/gateway">
        <div class="form-group">
            <label>Source Name</label>
            <input type="text" name="name" class="form-control" placeholder="Source Name">
        </div>
        <div class="form-group">
            <label>Default Virtual LAN</label>
            <select class="form-control" name="defaultVirtualLan">{{ forms.select(virtualLans, defaultValues.base.virtualLanId) }}</select>
        </div>
        <div class="form-group">
            <label>Gateway GUID</label>
            <input type="text" name="guid" class="form-control" placeholder="Gateway GUID">
        </div>
        <div class="form-group">
            <label>BACnet Device ID</label>
            <input type="text" name="deviceId" class="form-control" placeholder="Your desired ID">
        </div>
        <div class="form-group">
            <label>BACnet Device Name</label>
            <input type="text" name="deviceName" class="form-control" placeholder="Your desired name">
        </div>
        <div class="form-group">
            <label>BACnet Port</label>
            <input type="text" name="port" class="form-control" placeholder="As desired">
        </div>
        <div class="form-group">
            <label>Bacnet Discovery Timeout</label>
            <input type="text" name="discoveryTimeout" class="form-control" placeholder="seconds">
        </div>
        <div class="form-group">
            <label>Bacnet Timeout</label>
            <input type="text" name="timeout" class="form-control" placeholder="seconds">
        </div>
        <div class="form-group">
            <label>History Pack Size</label>
            <input type="text" name="historyPackSize" class="form-control" placeholder="Quanity in latent uploads">
        </div>
        <div class="form-group">
            <label>Reconnect Timeout</label>
            <input type="text" name="reconnectTimeout" class="form-control" placeholder="milliseconds">
        </div>
        <div class="form-group">
            <label>Response Timeout</label>
            <input type="text" name="responseTimeout" class="form-control" placeholder="milliseconds">
        </div>
        <input type="hidden" name="type" value="gateway" />
        <input type="hidden" name="protocol" value="bacnet">
    </form>
</div>
{#
<div id="dialog-addModbusGateway" title="Add New Modbus Gateway" style="display:none">
    <form data-type="modbusGateway">
        <div class="form-group">
            <label>Source Name</label>
            <input type="text" name="name" class="form-control" placeholder="Source Name">
        </div>
        <div class="form-group">
            <label>Default Virtual LAN</label>
            <select class="form-control" name="virtualLanId">{{ forms.select(virtualLans, defaultValues.base.virtualLanId) }}</select>
        </div>
        <div class="form-group">
            <label>Gateway GUID</label>
            <input type="text" name="guid" class="form-control" placeholder="Gateway GUID">
        </div>
        <div class="form-group">
            <label>Modbus URL</label>
            <input type="text" name="url" class="form-control" placeholder="Modbus URL">
        </div>
        <div class="form-group">
            <label>Modbus Port</label>
            <input type="text" name="port" class="form-control" value="{{ defaultValues.modbus.port }}" placeholder="Modbus Port">
        </div>
        <div class="form-group">
            <label>Modbus Encryption Key</label>
            <input type="text" name="encryptKey" class="form-control" placeholder="Modbus Encryption Key">
        </div>
        <input type="hidden" name="pollrate" value="{{ defaultValues.modbus.pollrate }}">
        <input type="hidden" name="timeout" value="{{ defaultValues.modbus.timeout }}">
        <input type="hidden" name="reconnectTimeout" value="{{ defaultValues.gateway.reconnectTimeout }}">
        <input type="hidden" name="responseTimeout" value="{{ defaultValues.gateway.responseTimeout }}">
    </form>
</div>
<div id="dialog-addWebservice" title="Add New Webservice Connection" style="display:none">
    <form data-type="webservice">
        <div class="form-group">
            <label>Source Name</label>
            <input type="text" name="name" class="form-control" placeholder="Source Name">
        </div>
        <div class="form-group">
            <label>Default Virtual LAN</label>
            <select class="form-control" name="virtualLanId">{{ forms.select(virtualLans, defaultValues.base.virtualLanId) }}</select>
        </div>
        <div class="form-group">
            <label>Webservice URL</label>
            <input type="text" name="url" class="form-control" placeholder="Webservice URL">
        </div>
        <div class="form-group">
            <label>Webservice Port</label>
            <input type="text" name="port" class="form-control" value="{{ defaultValues.webservice.port }}" placeholder="Webservice Port">
        </div>
        <div class="form-group">
            <label>Webservice Encryption Key</label>
            <input type="text" name="encryptKey" class="form-control" placeholder="Webservice Encryption Key">
        </div>
    </form>
</div>
#}