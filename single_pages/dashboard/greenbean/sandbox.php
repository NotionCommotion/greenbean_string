{% import "forms.html" as forms %}
{% include 'dashboard/menu.html' %}
<div id="list-header">
    <button class="btn btn-primary add">Add New</button>
    <h1>Sandbox Pages</h1>
    {{ forms.displayErrors(errors??null) }}
</div>

<div id="list-table-div">
    {% set empty = pages is empty %}

    <table class='table' id='sandbox-page-list'{{ empty?' style="display:none;"'}}>
    <thead>
        <tr>
            <th>Name</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {% for page in pages %}
        <tr data-id="{{ page.id }}">
            <td><a href="sandbox/{{ page.id }}">{{ page.name }}</a></td>
            <td><span class="delete glyphicon glyphicon-trash" data-toggle="tooltip" data-placement="top" title="Delete Sandbox Page"></span></td>
        </tr>
        {% endfor %}
    </tbody>
    </table>
    <h1 class="empty-list{{ not empty?' hidden' }}">You have no sandbox pages.</h1>
</div>

<div id="dialog-addSandboxPage" title="Add New Sandbox Page" style="display:none">
    <form>
        <div class="form-group">
            <label>Sandbox Page Name</label>
            <input type="text" name="name" class="form-control" placeholder="Sandbox Page Name">
        </div>
    </form>
</div>
