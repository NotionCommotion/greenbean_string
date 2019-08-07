<h1>{{ name }}</h1><hr>
<div id="frontContent" onclick="location.href='edit/{{page}}'" data-displayunit="{{ displayUnit }}">
    {{ html|raw }}
</div>
<input id="id" value="{{ id }}" type="hidden">