{% import "forms.html" as forms %}
{% include 'dashboard/menu.html' %}
<input type="hidden" value="{{page}}" id="page" />
<a href="javascript:void(0)" id="addResources">Add Resources</a>
<div id="frontContent" class="frontContentEdit">
    {{ html|raw }}
</div>
<div id="dialog-addPoint" class="dialog" title="Add Point">
    <ul>
        {% for item in pointList %}
        <li data-id="{{ item.id }}" class="{{ cycle(['even', 'odd'], loop.index0) }}">{{ item.name }}</li>
        {% endfor %}
    </ul>
</div>
<div id="dialog-addChart" class="dialog" title="Add Chart">
    <ul>
        {% for item in chartList %}
        <li data-id="{{ item.id }}" class="{{ cycle(['even', 'odd'], loop.index0) }}">{{ item.name }}</li>
        {% endfor %}
    </ul>
</div>
<div id="dialog-addResources" title="Add JS or CSS Resource" style="display:none">
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="resource" id="file"/>
    </form>
    <table class="table">
        <thead>
            <tr>
                <td></td>
                <td>Name</td>
                <td>Type</td>
                <td>Size</td>
                <td>Date</td>
                <td></td>
            </tr>
            <tr id="resource-clone">
                <td><input type="checkbox" value="1"></td>
                <td><a href="#" target="_blank"></a></td>
                <td></td>
                <td></td>
                <td></td>
                <td><i class="delete fa fa-trash fa-lg" data-toggle="tooltip" data-placement="top" title="Delete Resource"></i></td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
