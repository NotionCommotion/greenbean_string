{% import "forms.html" as forms %}
{% include 'dashboard/menu.html' %}
{{ forms.displayErrors(errors??[]) }}
<div id="list-header">
    <div class="btn-group float-right">
        <button class="btn btn-danger dropdown-toggle" data-toggle="dropdown">Control Gateway<span class="caret"></span></button>
        <ul class="dropdown-menu control-gateway">
            <li><a href="javascript:void(0)" class="restartGateway">Restart Gateway</a></li>
            <li><a href="javascript:void(0)" class="restartNetwork">Restart Network</a></li>
            <li><a href="javascript:void(0)" class="rebootDevice">Reboot Gateway</a></li>
        </ul>
    </div>
    <button class="btn btn-info discovery">Discover Devices</button>
    <button class="btn btn-info point-browser">Point Browser</button>
    <button class="btn btn-primary object-browser">Object Browser</button>
    <h1>BACnet Gateway</h1>
</div>

{% if source %}
<table class="table">
    <tr><td>Name</td><td><a href="javascript:void(0)" class="name">{{ source.name }}</a></td></tr>
    <tr><td>ID</td><td>{{ source.id }}</td></tr>
    <tr><td>Default Virtual LAN</td><td><a href="javascript:void(0)" class="defaultVirtualLan" data-value="{{ source.defaultVirtualLan.id }}">{{ source.defaultVirtualLan.name }}</a></td></tr>
    <tr><td>GUID</td><td><a href="javascript:void(0)" class="guid">{{ source.guid }}</a></td></tr>
    <tr><td>BACnet Port</td><td><a href="javascript:void(0)" class="port">{{ source.protocol.port }}</a></td></tr>
    <tr><td>BACnet Device ID</td><td><a href="javascript:void(0)" class="devices_id">{{ source.protocol.deviceId }}</a></td></tr>
    <tr><td>BACnet Device Name</td><td><a href="javascript:void(0)" class="devices_name">{{ source.protocol.deviceName }}</a></td></tr>
    <tr><td>BACnet Timeout (milliseconds)</td><td><a href="javascript:void(0)" class="timeout">{{ source.protocol.timeout }}</a></td></tr>
    <tr><td>BACnet Discovery Timeout (seconds)</td><td><a href="javascript:void(0)" class="discovery_timeout">{{ source.protocol.discoveryTimeout }}</a></td></tr>
    <tr><td>Datalink Reconnect Timeout (seconds)</td><td><a href="javascript:void(0)" class="reconnectTimeout">{{ source.reconnectTimeout }}</a></td></tr>
    <tr><td>Datalink Response Timeout (seconds)</td><td><a href="javascript:void(0)" class="responseTimeout">{{ source.responseTimeout }}</a></td></tr>
    <tr><td>Backup Update Size (records)</td><td><a href="javascript:void(0)" class="historyPackSize">{{ source.historyPackSize }}</a></td></tr>
    <tr><td>Device Model Name</td><td>{{ source.protocol.deviceModelName|default('unknown') }}</tr>
    <tr><td>Device Vendor Id</td><td>{{ source.protocol.deviceVendorId|default('unknown') }}</tr>
    <tr><td>Firmware Revision</td><td>{{ source.firmware|default('unknown') }}</td></tr>
</table>
{% endif %}
<div id="dialog-object-browser" title="Object Browser" style="display:none">
    <div>Include device IDs from <a href="javascript:void(0)" id="lowDeviceId">0</a> and <a href="javascript:void(0)" id="highDeviceId">4194302</a></div>
    <!-- <button class="add-point">Add Point</button> -->
    <div id="object-browser"></div>
</div>
<!-- Add Point (from within dialog-object-browser)-->
<div id="dialog-add-point" title="Add New Point" style="display:none">
    <form id="addPointBacnet">
        <div class="form-group">
            <label>Alias Point Name</label>
            <input type="text" name="name" class="form-control" placeholder="Alias Point Name">
        </div>
        <div class="form-group">
            <label>Virtual LAN</label>
            <select class="form-control" name="virtualLanId" id="virtualLanId">{{ forms.select(virtualLans, source.defaultVirtualLan.id | default("")) }}</select>
        </div>
        <div class="form-group">
            <label>Units</label>
            <input type="text" name="unit" class="form-control bn-data" placeholder="Units">
        </div>
        <div class="form-group">
            <label>Pollrate</label>
            <input type="text" name="pollrate" class="form-control bn-data" placeholder="Pollrate">
        </div>
        <div class="form-group">
            <label>COV Lifetime</label>
            <input type="text" name="covLifetime" class="form-control bn-data" placeholder="COV Lifetime">
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" class="bn-data" name="trend" value="1"> Trend
            </label>
        </div>
        <div class="form-group">
            <label>Object ID</label>
            <input type="text" readonly name="objectId" class="form-control bn-data">
        </div>
        <div class="form-group">
            <label>Object Name</label>
            <input type="text" readonly name="objectName" class="form-control bn-data">
        </div>
        <div class="form-group">
            <label>Object Type</label>
            <input type="text" readonly name="objectTypeName" class="form-control bn-data">
            <input type="hidden" name="objectType" class="bn-data">
        </div>
        <div class="form-group">
            <label>Device id</label>
            <input type="text" readonly name="deviceId" class="form-control bn-data">
        </div>
        <div class="form-group">
            <label>Device Name</label>
            <input type="text" readonly name="deviceName" class="form-control bn-data">
        </div>
        <input type="hidden" name="slope" value="1" />
        <input type="hidden" name="intercept" value="0" />
        <input type="hidden" name="sourceId" />
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>

<!-- Add Sycronize -->
<div id="dialog-point-browser" title="Point Browser" style="display:none">
    <table class="table">
        <thead>
            <tr>
                <th>Device ID</th>
                <th>Object ID</th>
                <th>Object Type</th>
                <th>Pollrate</th>
                <th>COV</th>
            </tr>
            <tr class="point-clone">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Add Discovery -->
<div id="dialog-discovery" title="Discover Devices" style="display:none">
    <div id="dialog-discovery-init">
        <div class="form-group">
            <label>Min Device ID</label>
            <input type="text" name="lowDeviceId" class="form-control bn-data" placeholder="Leave blank for all">
        </div>
        <div class="form-group">
            <label>Max Device ID</label>
            <input type="text" name="highDeviceId" class="form-control bn-data" placeholder="Leave blank for all">
        </div>
        <p>Include previously discovered devices <input type="checkbox" id="include-existing"></p>
    </div>
    <div id="dialog-discovery-final">
        <p>The following devices are on line within the range from <span id="discovery-low"></span> and <span id="discovery-high"></span></p>
        <p><input type="checkbox" id="checkAll" checked> Check All
        <div>
            <table class="table">
                <thead>
                    <tr id="discovery-clone"><td><input type="checkbox" checked></td><td></td><td class="status" title="Discovery for this device is pending">Pending</td><td><i class="fa fa-clock-o fa-lg"></i></td></tr>
                </thead>
                <tbody id="discovery-list"></tbody>
            </table>
        </div>
    </div>
</div>

<input type="hidden" id=sourceId value="{{ source.id | default("") }}" />
