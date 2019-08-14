{% include 'dashboard/menu.html' %}
<h1>{{ name }} <a id="edit-sandbox" href='edit/{{ id }}'>Edit Page</a></h1><hr>
<div id="frontContent" data-displayunit="{{ displayUnit }}">
    {{ html|raw }}
</div>
<input id="id" value="{{ id }}" type="hidden">