{% import "forms.html" as forms %}
<div id="list-header">
    <button class="btn btn-primary add">Add New</button>
    <h1>Charts</h1>
    {{ forms.displayErrors(errors??null) }}
</div>

<div id="list-table-div">
    {% set empty = charts is empty %}

    <table class='table' id='chart-list'{{ empty?' style="display:none;"'}} data-types="{{ chartTypes|json_encode() }}">
    <thead>
        <tr>
            <th>Name</th>
            <th>ID</th>
            <th id="type-pulldown">Type</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {% for row in charts %}
        <tr data-id="{{ row.id }}">
            <td class="chName link">{{ row.name }}</td>
            <td>{{ row.id }}</td>
            <td>{{ row.type }}</td>
            <td><img alt="Delete Chart" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Chart" class="vtip delete" height="16" width="16"></td>
        </tr>
        {% endfor %}
    </tbody>
    </table>
    <h1 class="empty-list{{ not empty?' hidden' }}">You have no charts.</h1>
</div>

<div id="dialog-addChart" title="Add New Chart" style="display:none">
    <form id="common-inputs">
        <div class="form-group">
            <label>Chart Name</label>
            <input type="text" name="name" class="form-control" placeholder="Chart Name">
        </div>
        <div class="form-group">
            <label>Chart Type</label>
            <select class="form-control" name="type" id="type">
                <option value="default">Select One</option>
                {% for masterType, subTypes in chartTypes %}
                {% for subType, types in subTypes %}
                <option value="{{ masterType }}" data-type="{{ subType }}">{{ types.name }}</option>
                {% endfor %}
                {% endfor %}
            </select>
        </div>
        <div class="form-group" id="theme-div">
            <label>Chart Theme</label>
            <select class="form-control" name="themesId" id="themesId"></select>
        </div>
    </form>
    <form id="add-category-chart">
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <form id="add-pie-chart">
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <form id="add-time-chart">
        <div class="form-group">
            <label>Time Range</label>
            <input type="text" name="rangeTimeValue" class="form-control" placeholder="Time Range" value="1">
        </div>
        <div class="form-group">
            <label>Time Range Units</label>
            <select class="form-control" name="rangeTimeUnit" id="rangeTimeUnit">{{ forms.select(timeUnit, 'w') }}</select>
        </div>
        <div class="form-group">
            <label>Group on Time Intervals</label>
            <select class="form-control" name="groupByTime" id="groupByTime">{{ forms.select([{id:0,name:'No'},{id:1,name:'Yes'}]) }}</select>
        </div>
        <div class="form-group time-interval">
            <label>Time Interval</label>
            <input type="text" name="intervalTimeValue" class="form-control" placeholder="Time Interval" value="1">
        </div>
        <div class="form-group time-interval">
            <label>Time Interval Units</label>
            <select class="form-control" name="intervalTimeUnit">{{ forms.select(timeUnit) }}</select>
        </div>
        <div class="form-group">
            <label>Time Boundaries</label>
            <select class="form-control" name="boundary">{{ forms.select([{id:0,name:'No'},{id:1,name:'Yes'}]) }}</select>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <form id="add-gauge-chart">
        <div class="form-group">
            <label>Point Name</label>
            <input type="text" class="point_name form-control" placeholder="Point Name">
            <input type="hidden" name="pointId" disabled="disabled">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>

<div id="dialog-addPointCategory" title="Add New Point" style="display:none">
    <form>
        <div class="form-group">
            <label>Existing Point Name</label>
            <input type="text" class="point_name form-control" placeholder="Existing Point Name" data-type="category">
            <input type="hidden" name="pointId" />
        </div>
        <div class="form-group">
            <label>Category</label>
            <label class="radio-inline"><input type="radio" name="newCategory" value="1" checked>New</label>
            <label class="radio-inline"><input type="radio" name="newCategory" value="0">Existing</label>
            <input type="text" name="categoryName" class="form-control" placeholder="Add New Category">
            <select class="form-control" id="categoryPosition" name="categoryPosition"></select>
        </div>
        <div class="form-group">
            <label>Series</label>
            <label class="radio-inline"><input type="radio" name="newSeries" value="1" checked>New</label>
            <label class="radio-inline"><input type="radio" name="newSeries" value="0">Existing</label>
            <input type="text" name="seriesName" class="form-control" placeholder="Add New Series">
            <select class="form-control" id="seriesPosition" name="seriesPosition"></select>
        </div>
    </form>
</div>

<div id="dialog-addPointPie" title="Add New Point" style="display:none">
    <form>
        <div class="form-group">
            <label>Existing Point Name</label>
            <input type="text" class="point_name form-control" placeholder="Existing Point Name" data-type="pie">
            <input type="hidden" name="pointId" />
        </div>
        <div class="form-group">
            <label>Legend</label>
            <input type="text" name="name" class="legend form-control" placeholder="Legend">
            <!-- <input type="hidden" name="seriesOffset" value="0"> -->
        </div>
    </form>
</div>

<div id="dialog-addTimeSeries" title="Add New Trend" style="display:none">
    <form>
        <div class="form-group">
            <label>Existing Point Name</label>
            <input type="text" class="point_name form-control" placeholder="Existing Point Name" data-type="time">
            <input type="hidden" name="pointId" />
        </div>
        <div class="form-group">
            <label>Legend</label>
            <input type="text" name="name" class="legend form-control" placeholder="Legend">
        </div>
        <div class="form-group">
            <label>Aggregate Type</label>
            <select class="form-control" name="aggrType" id="aggrType">
                {{ forms.select(aggrTypes, 'mean') }}
            </select>
        </div>
        <div class="form-group">
            <label>Time Offset</label>
            <input type="text" name="historyTimeValue" class="form-control" placeholder="Time Offset" value="0">
        </div>
        <div class="form-group">
            <label>Time Offset Units</label>
            <select class="form-control" name="historyTimeUnit">{{ forms.select(timeUnit) }}</select>
        </div>
        <div class="form-group hide">
            <label>Time Delta (not currently implemented)</label>
            <input type="text" name="deltaTimeValue" class="form-control" placeholder="Time Delta" value="0" readonly>
        </div>
        <div class="form-group hide">
            <label>Time Delta Units (not currently implemented)</label>
            <select class="form-control" name="deltaTimeUnit" readonly>{{ forms.select(timeUnit) }}</select>
        </div>
    </form>
</div>

<div id="dialog-cloneChart" title="Clone Chart" style="display:none">
    <form>
        <div class="form-group">
            <label>Name</label>
            <input type="text" id="clone-name" name="name" class="form-control" placeholder="Chart to clone">
        </div>
    </form>
</div>

<div id="dialog-editChartOptions" title="Edit Highchart Options" style="display:none">
    <textarea></textarea>
</div>

<div id="dialog-editChart" class="dialog-editChart" title="Chart Info" style="display:none"></div>

{% verbatim %}
<script id="hb_prefix" class="hb-table" type="text/x-handlebars-template">
    <h1>General:</h1>
    <table class="table">
    <tr><td>ID</td><td>{{id}}</td></tr>
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Type</td><td><a href="javascript:void(0)" class="hb_type"></a></td></tr>
    <tr><td>Theme</td><td><a href="javascript:void(0)" class="hb_theme"></a></td></tr>
    <tr><td>Show Point Names</td><td><a href="javascript:void(0)" class="hb_pointNames"></a></td></tr>
    <tr><td>Display Legend</td><td><a href="javascript:void(0)" class="hb_legend"></a></td></tr>
    <tr><td>Title</td><td><a href="javascript:void(0)" class="hb_title">{{optionsObj.title.text}}</a></td></tr>
    <tr><td>Sub Title</td><td><a href="javascript:void(0)" class="hb_subtitle">{{optionsObj.subtitle.text}}</a></td></tr>
</script>
<script id="hb_category" class="hb-table" type="text/x-handlebars-template">
    <tr><td>Display X Crosshairs</td><td><a href="javascript:void(0)" class="hb_crosshairX"></a></td></tr>
    <tr><td>Display Y Crosshairs</td><td><a href="javascript:void(0)" class="hb_crosshairY"></a></td></tr>
    <tr><td>X Title</td><td><a href="javascript:void(0)" class="hb_xaxis">{{optionsObj.xAxis.title.text}}</a></td></tr>
    <tr><td>Y Title</td><td><a href="javascript:void(0)" class="hb_yaxis">{{optionsObj.yAxis.title.text}}</a></td></tr>
    </table>
    <div class="chart-action">
    <button class="addPointCategory">Add Point</button>
    <button class="editChartOptions">Advanced</button>
    <a href="javascript:void(0)" class="clone-chart">Clone Chart</a>
    </div>
    <h1>Points:</h1>
    <table class='tabledragger table' id="chartListCategory">
    <thead>
    <tr>
    <th></th>
    {{#each categories}}
    <th><a href="javascript:void(0)" class="hb_category">{{this.name}}</a><img alt="Delete Category" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Category" class="vtip deleteCategory" height="16" width="16"></th>
    {{/each}}
            </tr>
            </thead>
            <tbody>
            {{#each series}}
        <tr>
        <td><a href="javascript:void(0)" class="hb_series">{{this.name}}</a><img alt="Delete Series" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Series" class="vtip deleteSeries" height="16" width="16"></td>
    {{#each this.points}}
    <td><a href="javascript:void(0)" class="hb_point" data-id="{{this.id}}">{{this.name}}</a></td>
    {{/each}}
    {{!--
            {{#each this.categories as |value key|}}
        {#ifeq points.key.position value.position}}
    <td><a href="javascript:void(0)" class="hb_point" data-id="{{points.key.id}}">{{points.key.name}}</a></td>
    {{/ifeq}}
    {#ifnoteq points.key.position value.position}}
    <td>Empty</td>
    {{/ifnoteq}}
    {{/each}}
        --}}
    </tr>
    {{/each}}
    </tbody>
    </table>
</script>
<script id="hb_pie" class="hb-table" type="text/x-handlebars-template">
    </table>
    <div class="chart-action">
    <button class="addPointPie">Add Point</button>
    <button class="editChartOptions">Advanced</button>
    <a href="javascript:void(0)" class="clone-chart">Clone Chart</a>
    </div>
    <h1>Points:</h1>
    <table class='tabledragger table' id="chartListPie">
    <thead>
    <tr><th>Legend</th><th>Point</th><th></th></tr>
    <tr id="clone-pie">
    <td><a href="javascript:void(0)" class="hb_category"></a></td>
    <td><a href="javascript:void(0)" class="hb_point"></a></td>
    <td><img alt="Delete Point" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Point" class="vtip deleteData" height="16" width="16"></td>
    </tr>
    </thead>
    <tbody>
    {{#each series.0.nodes}}
    <tr>
    <td><a href="javascript:void(0)" class="hb_category">{{this.name}}</a></td>
    <td><a href="javascript:void(0)" class="hb_point" data-id="{{this.point.id}}">{{this.point.name}}</a></td>
    <td><img alt="Delete Point" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Point" class="vtip deleteData" height="16" width="16"></td>
    </tr>
    {{/each}}
    </tbody>
    </table>
</script>
<script id="hb_time" class="hb-table" type="text/x-handlebars-template">
    <tr><td>Display X Crosshairs</td><td><a href="javascript:void(0)" class="hb_crosshairX"></a></td></tr>
    <tr><td>Display Y Crosshairs</td><td><a href="javascript:void(0)" class="hb_crosshairY"></a></td></tr>
    <tr><td>X Title</td><td><a href="javascript:void(0)" class="hb_xaxis">{{optionsObj.xAxis.title.text}}</a></td></tr>
    <tr><td>Y Title</td><td><a href="javascript:void(0)" class="hb_yaxis">{{optionsObj.yAxis.title.text}}</a></td></tr>
    <tr><td>Time Range</td><td><a href="javascript:void(0)" class="hb_rangeTimeValue">{{rangeTimeValue}}</a> <a href="javascript:void(0)" class="hb_rangeTimeUnit"></a></td></tr>
    <tr><td>Group by Time Interval</td><td><a href="javascript:void(0)" class="hb_groupByTime" id="hb_groupByTime"></a></td></tr>
    <tr id="hb_intervalTimeValue"><td>Time Group Interval</td><td><a href="javascript:void(0)" class="hb_intervalTimeValue">{{intervalTimeValue}}</a> <a href="javascript:void(0)" class="hb_intervalTimeUnit"></a></td></tr>
    <tr><td>Fixed Time Boundraries</td><td><a href="javascript:void(0)" class="hb_boundary"></a></td></tr>
    <tr><td>Display Markers</td><td><a href="javascript:void(0)" class="hb_marker"></a></td></tr>
    </table>
    <div class="chart-action">
    <button class="addTimeSeries">Add</button>
    <button class="editChartOptions">Advanced</button>
    <a href="javascript:void(0)" class="clone-chart">Clone Chart</a>
    </div>
    <h1>Series:</h1>
    <table class='tabledragger table' id="chartListTime">
    <thead>
    <tr><th>Legend</th><th>Point Name</th><th>Offset</th><th>Function</th><th></th></tr>
    <tr id="clone-time">
    <td><a href="javascript:void(0)" class="hb_series"></a></td>
    <td><a href="javascript:void(0)" class="hb_point"></a></td>
    <td class="timeOffset"><a href="javascript:void(0)" class="hb_historyTimeValue"></a> <a href="javascript:void(0)" class="hb_historyTimeUnit"></a></td>
    <td><a href="javascript:void(0)" class="hb_aggrType"></a></a></td>
    <td><img alt="Delete Point" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Series" class="vtip deleteTimeSeries" height="16" width="16"></td></tr>
    </tr>
    </thead>
    <tbody>
    {{#each series}}
    <tr>
    <td><a href="javascript:void(0)" class="hb_series">{{this.name}}</a></td>
    <td><a href="javascript:void(0)" class="hb_point" data-id="{{this.point.id}}">{{this.point.name}}</a></td>
    <td class="timeOffset"><a href="javascript:void(0)" class="hb_historyTimeValue">{{this.historyTimeValue}}</a> <a href="javascript:void(0)" class="hb_historyTimeUnit" data-value="{{this.historyTimeUnit}}"></a></td>
    <td><a href="javascript:void(0)" class="hb_aggrType" data-value='{{this.aggrType}}'></a></a></td>
    <td><img alt="Delete Point" src="/lib/gb/stdimages/icon_16/delete.png" title="Delete Series" class="vtip deleteTimeSeries" height="16" width="16"></td></tr>
    {{/each}}
    </tbody>
    </table>
</script>
<script id="hb_gauge" class="hb-table" type="text/x-handlebars-template">
    <tr><td>Display Y Crosshairs</td><td><a href="javascript:void(0)" class="hb_crosshairY"></a></td></tr>
    <tr><td>Y Title</td><td><a href="javascript:void(0)" class="hb_yaxis">{{optionsObj.yAxis.title.text}}</a></td></tr>
    <tr><td>Point Name</td><td id="{{point.id}}"><a href="javascript:void(0)" class="hb_point">{{point.name}}</a></td></tr>
    </table>
    <div class="chart-action">
    <button class="editChartOptions">Advanced</button>
    <a href="javascript:void(0)" class="clone-chart">Clone Chart</a>
    </div>
    <table class='table'></table>
</script>
{% endverbatim %}
