{% import "forms.html" as forms %}
<div class="header">
    <img src="{{ img_url }}/arrow-left-nav.png" class="back" alt="back" height="32" width="32">
    <img src="{{ img_url }}/arrow-right-nav.png" class="forward" alt="forward" height="32" width="32">
    <img src="{{ img_url }}/home.png" class="home" alt="home" height="32" width="32">
    <img src="{{ img_url }}/printer.png" class="print" alt="print" height="32" width="32">
    <input class="searchHelp default-value" type="text" name="search" value="Search Help" />
</div>
<div class="main">
    <p class="name">{{ name }}</p>
    {# <p class="tree"></p> #}
    <hr>
    <div class="content">{{ content|raw }}</div>
    <ul class="list">
        {% for item in topics %}
        <li class="{{ cycle(['even', 'odd'], loop.index0) }}">
            <a href="/dashboard/greenbean/manual/{{ item.id }}">{{ item.name }}</a>
        </li>
        {% endfor %}
        {# {{ forms.linklist(topics, {{ base_url }}'/manual') }} #}
    </ul>
</div>
