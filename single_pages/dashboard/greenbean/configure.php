{% import "forms.html" as forms %}
<h1>Update Information</h1>
<p>Prior to using this application, you must provide the host server name and your Greenbean API key.</p>
{{ forms.displayErrors(errors) }}
<form action="{{ action }}" method="post">
    <div class="form-group">
        <label>Host Name</label>
        <input type="text" name="host" class="form-control" value="{{ host }}">
    </div>
    <div class="form-group">
        <label>API Key</label>
        <input type="text" name="api" class="form-control" value="{{ api }}"  placeholder="{{ api_sample }}">
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" name="displayUnit" checked value="{{ displayUnit }}"> Display Units
        </label>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
