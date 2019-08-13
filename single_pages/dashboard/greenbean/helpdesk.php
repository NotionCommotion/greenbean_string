{% import "forms.html" as forms %}
{% include 'dashboard/menu.html' %}
<h2>Help Desk</h2>
{{ forms.displayErrors(errors??null) }}

<p>Before submitting a ticket, please consult the <a class="manual" href="javascript:void(0)">Help Manual</a>.
    {% if tickets|length %}
    If you have already reported this issue and would like to check the status of your request or add additional comments, please do not enter a new help request, but update the existing ticket.
    {% endif %}
</p>


<button type="button" class="btn btn-primary open-new-ticket">Open New Ticket</button>
<input name="search" type="text" id="search-window-filter" class="search-window-filter default-value" value="Search open request" />

<div class="filter-by">
    <b>Filter by</b>: <a href="#" id="filter-tickets" data-value="{{ statusId|default(0) }}"></a>
</div>

<div id="list-table-div">
    {% set empty = tickets is empty %}

    <table class='table'{{ empty?' style="display:none;"'}}>
    <thead>
        <tr>
            <th>From</th>
            <th>Type Of Request</th>
            <th>Message</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        {% for row in tickets %}
        {% set lastMessage=row.threads|last %}
        <tr data-id="{{ row.ticketId }}"{{ row.newReply?' class="new"' }} data-data='{{ row|json_encode|raw }}'>
        <td>
            <p class="name">{{ lastMessage.name }}</p>
            <p class="date">{{ lastMessage.date|date("m/d/Y g:i A") }}</p>
        </td>
        <td>{{ row.topic }}</td>
        <td>
            <p class="subject"><a href="#" class="open-ticket">{{ row.subject }}</a></p>
            <p class="message">{{ lastMessage.message }}</p>
        </td>
        <td>{{ row.status }}</td>
        </tr>
        {% endfor %}
    </tbody>
    </table>
    <h1 class="empty-list{{ not empty?' hidden' }}">There are no recent tickets in your support history.</h1>
</div>

<div id="dialog-new-ticket" title="Enter Your Ticket" style="display:none">
    <p>All fields marked * are required.</p>
    <p class="validateTips"></p>
    <form>
        <div class="form-group">
            <label for="topicId">TYPE OF REQUEST*</label>
            <select class="form-control" id="topicId" name="topicId">
                <option value="">Select an option</option>
                {{ forms.selectValue(message_types) }}
            </select>
        </div>
        <div class="form-group">
            <label for="subject">SUBJECT</label>
            <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject">
        </div>
        <div class="form-group">
            <label for="message">MESSAGE</label>
            <textarea class="form-control" name="message" rows="10" placeholder="Enter your message"></textarea>
        </div>
    </form>
</div>

<div id="dialog-view-ticket" title="Help Request" style="display:none">
    <div class="row">
        <div class="col-md-4">Subject: <span id="ticket-subject"></span></div>
        <div class="col-md-4">Topic: <span id="ticket-topic"></span></div>
        <div class="col-md-4">Status: <a href="#" id="statusId"></a></div>
    </div>
    <ul id='message-list'>
        <li id="tread-clone" class='hidden'>
            <div class="msg-header"></div>
            <div class="message"></div>
        </li>
    </ul>
    <p>Add an additional comment:</p>
    <p>If you have multiple issues, please open a separate ticket for each issue, as this will allow our multi-tier help desk system to work best and ensure that each request is handled by the most experienced person for each issue.</p>
    <form>
        <div class="form-group">
            <label for="message">MESSAGE</label>
            <textarea class="form-control" name="message" rows="8" placeholder="Enter your message"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Ticket</button>
    </form>

</div>
