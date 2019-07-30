{% import "forms.html" as forms %}
<div id="list-header">
    <button class="btn btn-secondary customPointReport">Custom Point Report</button>
    <button class="btn btn-primary add">Add New</button>
    <h1>Points</h1>
    {{ forms.displayErrors(errors??null) }}
</div>

<div id="list-table-div">
    {% set empty = points is empty %}
    <table class='table' id='point-list'{{ empty?' style="display:none;"'}}>
    <thead>
        <tr>
            {#  Removed the following on 10/28/2018
            <th>Last Update</th>
            <th>Value</th>
            <th>Enabled</th>
            <td>{{ row.tsValueUpdated?row.tsValueUpdated|date('n-j-y g:i a') }}</td>
            <td>{{ row.value }}</td>
            <td>{{ row.enabled==0 and row.enabled is not null?'No':'Yes' }}</td>
            #}
            <th>Name</th>
            <th>ID</th>
            <th id="type-pulldown">Type</th>
            <th>Units</th>
            <th>Trend</th>
            <th>BACnet Address</th>
            <th>VirtualLan</th>
            <th>Source</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {% for row in points %}
        <tr data-id="{{ row.id }}">
            <td class="ptName link">{{ row.name }}</td>
            <td><a href="/api/f/data?p={{ row.id }}" target="_blank" class="no-link">{{ row.id }}</a></td>
            <td>{{ row.type }}</td>
            <td>{{ row.unit??'none' }}</td>
            <td>{{ row.trend is defined ? (row.trend ? 'YES' : 'NO') : 'N/A' }}</td>
            <td>{{ row.device_id is defined ? row.device_id~"/"~row.object_id~"/"~row.object_type:'N/A' }}
            <td>{{ row.virtualLanName??'N/A' }}</td>
            <td>{{ row.sourceName??'N/A' }}</td>
            <td><img alt="Delete Point" src="images/delete.png" title="Delete Point" class="vtip delete" height="16" width="16"></td>
        </tr>
        {% endfor %}
    </tbody>
    </table>
    <h1 class="empty-list{{ not empty?' hidden' }}">You have no points.</h1>
</div>

<div id="dialog-addPoint" title="Add New Point" style="display:none">
    <label>
        <span>Type:</span>
        <select id="point_type">
            <option value="default">Select One</option>
            <option value="real">Real</option>
            <option value="custom">Custom</option>
            <option value="aggregate">Aggregate</option>
            <option value="delta">Delta</option>
            <option value="historic">Historic</option>
        </select>
    </label>
    <div id="empty-list">
        <p>Select one of the following point types:</p>
        <ul>
            <li>Real point: Actual BACnet, Modbus, or Webservice point which must be existing in your BAS system.</li>
            <li>Custom point:  Combining real points and other custom points.</li>
            <li>Aggregate point: The sum, mean, minimum, and maximum of a real or custom point.</li>
            <li>Delta point:  Difference between a real, custom, or aggregate point's value today and some duration in the past.</li>
            <li>Historic point: The value of a real, custom, aggregate, or delta point some time in the past.</li>
        </ul>
    </div>
    <div id="point-source" class="next-step">
        <label>
            <span>Point Source: </span>
            <select name="sourceId" id="sourceId">
                <option>Select One</option>
                {% for row in pointSources %}
                <option value="{{ row.id }}" data-protocol="{{ row.protocol }}">{{ row.name }}</option>
                {% endfor %}
            </select>
        </label>
    </div>
    <form id="addPointWebservice" class="next-step">
        <div class="form-group">
            <label>Alias Point Name</label>
            <input type="text" name="name" class="form-control" placeholder="Alias Point Name">
        </div>
        <div class="form-group">
            <label>Webservice Point Name</label>
            <input type="text" name="webserviceName" class="form-control" placeholder="Webservice Point Name">
        </div>
        <div class="form-group">
            <label>Units</label>
            <input type="text" name="unit" class="form-control" placeholder="Units">
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="trend" value="1"{{ defaultValues.realPnts.trend?' checked' }}> Trend
            </label>
        </div>
        <input type="hidden" name="sourceId" />
        <input type="hidden" name="slope" value="1" />
        <input type="hidden" name="intercept" value="0" />
        <button type="submit" class="btn btn-default">Submit</button>
        <p class="tempStuff">For testing, only points electric_meter_1 to 5, gas_meter_1 to 5, water_meter_1 to 5, and temperature_1 5 exist in BAS.<br>After "real point is filled out, pre-fill out client point with same name.</p>
    </form>
    <form id="addPointBacnet" class="next-step">
        <div class="form-group">
            <label>BACnet Device:Object</label>
            <input type="text" class="form-control" id="bacnetPoint" placeholder="BACnet Point">
            <input type="hidden" name="objectId" class="bn-data">
            <input type="hidden" name="objectType" class="bn-data">
            <input type="hidden" name="deviceId" class="bn-data">
        </div>
        <div class="form-group">
            <label>Alias Point Name</label>
            <input type="text" name="name" class="form-control" placeholder="Alias Point Name">
        </div>
        <div class="form-group">
            <label>Units</label>
            <input type="text" name="unit" class="form-control{{ defaultValues.bacnet.covLifetime?" bn-data" }}" placeholder="Units">
        </div>
        <div class="form-group">
            <label>Virtual LAN</label>
            <select class="form-control" name="virtualLanId">{{ forms.select(virtualLans, defaultValues.virtualLanId) }}</select>
        </div>
        <div class="form-group">
            <label>Pollrate</label>
            <input type="text" name="pollrate" class="form-control" placeholder="Pollrate" value={{ defaultValues.bacnet.pollrate }}>
        </div>
        <div class="form-group">
            <label>COV Lifetime</label>
            <input type="text" name="covLifetime" class="form-control" placeholder="COV Lifetime" value={{ defaultValues.bacnet.covLifetime }}>
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="trend" value="1"{{ defaultValues.realPnts.trend?' checked' }}> Trend
            </label>
        </div>
        <input type="hidden" name="sourceId" />
        <input type="hidden" name="slope" value="1" />
        <input type="hidden" name="intercept" value="0" />
        <input type="hidden" name="type" value="real" />
        <input type="hidden" name="protocol" value="bacnet" />
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <form id="addPointCust" class="next-step">
        <div class="form-group">
            <label>Alias Point Name</label>
            <input type="text" name="name" class="form-control" placeholder="Alias Point Name">
        </div>
        <div class="form-group">
            <label>Units</label>
            <input type="text" name="unit" class="form-control" placeholder="Units">
        </div>
        <input type="hidden" name="slope" value="1" />
        <input type="hidden" name="intercept" value="0" />
        <input type="hidden" name="type" value="custom" />
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <form id="addPointAggr" class="next-step">
        <div class="form-group">
            <label>Alias Point Name</label>
            <input type="text" name="name" class="form-control" placeholder="Alias Point Name">
        </div>
        <div class="form-group">
            <label>Units</label>
            <input type="text" name="unit" class="form-control" placeholder="Units">
        </div>
        <div class="form-group">
            <label>Point Name to aggregate</label>
            <input type="text" name="pointName" class="form-control" placeholder="Point Name to aggregate">
            <input type="hidden" name="pointId" />
        </div>
        <div class="form-group">
            <label>Aggregate Type</label>
            <select class="form-control" name="aggrType" id="aggrType">
                {{ forms.select(aggrTypes, 'mean') }}
            </select>
        </div>
        <div class="form-group">
            <label>Time Range</label>
            <input type="text" name="timeValue" class="form-control" placeholder="Time Range" value="1">
        </div>
        <div class="form-group">
            <label>Time Units</label>
            <select class="form-control" name="timeUnit" id="timeUnit">{{ forms.select(timeUnit) }}</select>
        </div>
        <div class="form-group">
            <label>Time Boundaries</label>
            <select class="form-control" name="boundary">{{ forms.select([{id:0,name:'No'},{id:1,name:'Yes'}]) }}</select>
        </div>
        <input type="hidden" name="slope" value="1" />
        <input type="hidden" name="intercept" value="0" />
        <input type="hidden" name="type" value="aggregate" />
        <button type="submit" class="btn btn-default">Submit</button>
        <ul class="tempStuff">
            <li>After "real point is filled out, pre-fill out client point with same name appended with aggregate type.</li>
        </ul>
    </form>
    <form id="addPointDelta" class="next-step">
        <div class="form-group">
            <label>Alias Point Name</label>
            <input type="text" name="name" class="form-control" placeholder="Alias Point Name">
        </div>
        <div class="form-group">
            <label>Point Name to get delta</label>
            <input type="text" name="pointName" class="form-control" placeholder="Point Name to aggregate">
            <input type="hidden" name="pointId" />
        </div>
        <div class="form-group">
            <label>Time Range</label>
            <input type="text" name="timeValue" class="form-control" placeholder="Time Range" value="1">
        </div>
        <div class="form-group">
            <label>Time Units</label>
            <select class="form-control" name="timeUnit">{{ forms.select(timeUnit) }}</select>
        </div>
        <input type="hidden" name="slope" value="1" />
        <input type="hidden" name="intercept" value="0" />
        <input type="hidden" name="type" value="delta" />
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <form id="addPointHistoric" class="next-step">
        <div class="form-group">
            <label>Alias Point Name</label>
            <input type="text" name="name" class="form-control" placeholder="Alias Point Name">
        </div>
        <div class="form-group">
            <label>Point Name to aggregate</label>
            <input type="text" name="pointName" class="form-control" placeholder="Point Name to get history">
            <input type="hidden" name="pointId" />
        </div>
        <div class="form-group">
            <label>Time Range</label>
            <input type="text" name="timeValue" class="form-control" placeholder="Time Range" value="1">
        </div>
        <div class="form-group">
            <label>Time Units</label>
            <select class="form-control" name="timeUnit">{{ forms.select(timeUnit) }}</select>
        </div>
        <input type="hidden" name="slope" value="1" />
        <input type="hidden" name="intercept" value="0" />
        <input type="hidden" name="type" value="historic" />
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>

<div id="dialog-addPointCust" title="Add New Point to Custom Point" style="display:none">
    <form>
        <div class="form-group">
            <label>Existing Point Name</label>
            <input type="text" id="customPointName" class="form-control" placeholder="Existing Point Name">
            <input type="hidden" name="pointId" />
        </div>
        <div class="form-group">
            <label>Additive/Subtractive</label>
            <select class="form-control" name="sign">
                <option value="1">Additive</option>
                <option value="-1">Subtractive</option>
            </select>
        </div>
    </form>
</div>

<div id="dialog-customPointReport" title="Custom Point Report" style="display:none">
    <div id=custom-report-select>
        <p>Select the custom points to run the report on</p>
        <form>
            <p><input type="checkbox" id="checkAll" > Check All <input type="submit" value="Submit"></p>
            <ul id="custom-point-reports"></ul>
        </form>
    </div>
    <div id=custom-report-display></div>
</div>

<div id="dialog-editPoint" title="Point Info" style="display:none"></div>
{% verbatim %}
<script id="hb_bacnet" class="hb-table" type="text/x-handlebars-template">
    <table class="table point-dialog">
    <tr><td>ID</td><td>{{id}}</td></tr>
    <tr><td>Type</td><td>{{type}}</td></tr>
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Units</td><td><a href="javascript:void(0)" class="hb_unit">{{unit}}</a></td></tr>
    <tr><td>Slope</td><td><a href="javascript:void(0)" class="hb_slope">{{slope}}</a></td></tr>
    <tr><td>Intersept</td><td><a href="javascript:void(0)" class="hb_intercept">{{intercept}}</a></td></tr>
    <tr><td>Trend</td><td><a href="javascript:void(0)" class="hb_trend"></a></td></tr>
    <tr><td>Source</td><td>{{datanode.source.name}}</td></tr>
    <!--  <tr><td>Enabled</td><td><a href="javascript:void(0)" class="hb_enabled"></a></td></tr> -->
    <tr><td>Protocol</td><td>{{datanode.source.protocol.type}}</td></tr>
    <tr><td>BACnet Device:Object</td><td><a href="javascript:void(0)" class="hb_bacnet">{{datanode.bacnetObject.bacnetDevice.deviceName}}:{{datanode.bacnetObject.objectName}}</a></td></tr>
    <tr><td>BACnet DeviceId:ObjectId (Type)</td><td>{{datanode.bacnetObject.bacnetDevice.deviceId}}:{{datanode.bacnetObject.objectId}} ({{datanode.bacnetObject.objectType}})</td></tr>
    <tr><td>Virtual LAN</td><td>{{virtualLan.name}}</td></tr>
    <tr><td>BACnet Point Pollrate</td><td><a href="javascript:void(0)" class="hb_pollrate">{{datanode.pollrate}}</a></td></tr>
    <tr><td>BACnet Point COV Lifetime</td><td><a href="javascript:void(0)" class="hb_covLifetime">{{datanode.covLifetime}}</a></td></tr>
    </table>
</script>
<script id="hb_webservice" class="hb-table" type="text/x-handlebars-template">
    <table class="table point-dialog">
    <tr><td>ID</td><td>{{id}}</td></tr>
    <tr><td>Type</td><td>{{type}}</td></tr>
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Units</td><td><a href="javascript:void(0)" class="hb_unit">{{unit}}</a></td></tr>
    <tr><td>Slope</td><td><a href="javascript:void(0)" class="hb_slope">{{slope}}</a></td></tr>
    <!-- <tr><td>Enabled</td><td><a href="javascript:void(0)" class="hb_enabled"></a></td></tr> -->
    <tr><td>Intersept</td><td><a href="javascript:void(0)" class="hb_intercept">{{intercept}}</a></td></tr>
    <tr><td>Trend</td><td><a href="javascript:void(0)" class="hb_trend">{{trend}}</a></td></tr>
    <tr><td>Source</td><td>{{sourceName}}</td></tr>
    <tr><td>Protocol</td><td>{{protocolName}}</td></tr>
    <tr><td>Webservice Point Name</td><td><a href="javascript:void(0)" class="hb_webservice_name">{{webserviceName}}</a></td></tr>
    <tr><td>Virtual LAN</td><td>{{virtualLanName}}</td></tr>
    </table>
</script>

<script id="hb_custom" class="hb-table" type="text/x-handlebars-template">
    <table class="table point-dialog">
    <tr><td>ID</td><td>{{id}}</td></tr>
    <tr><td>Type</td><td>{{type}}</td></tr>
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Units</td><td><a href="javascript:void(0)" class="hb_unit">{{unit}}</a></td></tr>
    <tr><td>Slope</td><td><a href="javascript:void(0)" class="hb_slope">{{slope}}</a></td></tr>
    <tr><td>Intersept</td><td><a href="javascript:void(0)" class="hb_intercept">{{intercept}}</a></td></tr>
    </table>
    <button class="addCustomPoint">Add</button>
    <h1>Points:</h1>
    <table class='table' id="customPointList" data-id={{id}}>
    <thead>
    <tr>
    <th>Name</th>
    <th>Additive/Subtractive</th>
    <th></th>
    </tr>
    <tr id="clone-custom">
    <td><a href="javascript:void(0)" class="hb_subpoint"></a></td>
    <td><a href="javascript:void(0)" class="hb_additive"></a></td>
    <td><img alt="Delete Point" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Point" class="vtip delete" height="16" width="16"></td>
    </tr>
    </thead>
    <tbody>
    {{#each subpoints}}
    <tr data-id={{id}}>
    <td><a href="javascript:void(0)" class="hb_subpoint">{{name}}</a>
    <td><a href="javascript:void(0)" class="hb_additive" data-value="{{sign}}"></a></td>
    <td><img alt="Delete Point" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Point" class="vtip delete" height="16" width="16"></td>
    </tr>
    {{/each}}
    </tbody>
    </table>
</script>

<script id="hb_aggregate" class="hb-table" type="text/x-handlebars-template">
    <table class="table point-dialog">
    <tr><td>ID</td><td>{{id}}</td></tr>
    <tr><td>Type</td><td>{{type}}</td></tr>
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Units</td><td><a href="javascript:void(0)" class="hb_unit">{{unit}}</a></td></tr>
    <tr><td>Slope</td><td><a href="javascript:void(0)" class="hb_slope">{{slope}}</a></td></tr>
    <tr><td>Intersept</td><td><a href="javascript:void(0)" class="hb_intercept">{{intercept}}</a></td></tr>
    <tr><td>Function</td><td><a href="javascript:void(0)" class="hb_type_aggregate_name"></a></td></tr>
    <tr><td>Base Point Name</td><td><a href="javascript:void(0)" class="hb_subpoint">{{subpoint.name}}</a></td></tr>
    <tr><td>Time Range</td><td><a href="javascript:void(0)" class="hb_timeValue">{{timeValue}}</a> <a href="javascript:void(0)" class="hb_timeUnit"></a></td></tr>
    <tr><td>Fixed Time Boundraries</td><td><a href="javascript:void(0)" class="hb_boundary"></a></td></tr>
    </table>
</script>

<script id="hb_delta" class="hb-table" type="text/x-handlebars-template">
    <table class="table point-dialog">
    <tr><td>ID</td><td>{{id}}</td></tr>
    <tr><td>Type</td><td>{{type}}</td></tr>
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Base Point Name</td><td><a href="javascript:void(0)" class="hb_subpoint">{{subpoint.name}}</a></td></tr>
    <tr><td>Time Range</td><td><a href="javascript:void(0)" class="hb_timeValue">{{timeValue}}</a> <a href="javascript:void(0)" class="hb_timeUnit"></a></td></tr>
    <tr><td>Slope</td><td><a href="javascript:void(0)" class="hb_slope">{{slope}}</a></td></tr>
    <tr><td>Intersept</td><td><a href="javascript:void(0)" class="hb_intercept">{{intercept}}</a></td></tr>
    </table>
</script>

<script id="hb_historic" class="hb-table" type="text/x-handlebars-template">
    <table class="table point-dialog">
    <tr><td>ID</td><td>{{id}}</td></tr>
    <tr><td>Type</td><td>{{type}}</td></tr>
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Base Point Name</td><td><a href="javascript:void(0)" class="hb_subpoint">{{subpoint.name}}</a></td></tr>
    <tr><td>Time Setback</td><td><a href="javascript:void(0)" class="hb_timeValue">{{timeValue}}</a> <a href="javascript:void(0)" class="hb_timeUnit"></a></td></tr>
    <tr><td>Slope</td><td><a href="javascript:void(0)" class="hb_slope">{{slope}}</a></td></tr>
    <tr><td>Intersept</td><td><a href="javascript:void(0)" class="hb_intercept">{{intercept}}</a></td></tr>
    </table>
</script>

<script id="hb_custom_report" class="hb-table" type="text/x-handlebars-template">
    {{#each custompoints}}
    <h4>{{name}} (Point ID: {{id}})  Slope: {{slope}}  Intercept: {{intercept}}</h4>
    <table class="table">
    <tr><td>ID</td><td>Name</td><td>Slope</td><td>Intercept</td><td>Instance Number</td><td>Object Name</td><td>Value</td></tr>
    {{#each points}}
    <tr><td>{{id}}</td><td>{{name}}</td><td>{{slope}}</td><td>{{intercept}}</td><td>{{deviceId}}.{{objectId}}</td><td>{{deviceName}}.{{objectName}}</td><td>{{value}}</td></tr>
    {{/each}}
    </table>
    {{/each}}
</script>

{% endverbatim %}
