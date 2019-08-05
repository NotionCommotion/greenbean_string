{% import "forms.html" as forms %}
<h1>Modbus Gateway</h1>
<h1>Not Complete</h1>
<table class="table">
    <tr><td>Name</td><td><a href="javascript:void(0)" class="hb_name">{{name}}</a></td></tr>
    <tr><td>Default Virtual LAN</td><td><a href="javascript:void(0)" class="hb_virtualLan">{{virtualLan}}</a></td></tr>
    <tr><td>GUID</td><td><a href="javascript:void(0)" class="hb_guid">{{guid}}</a></td></tr>
    <tr><td>Modbus URL</td><td><a href="javascript:void(0)" class="hb_url">{{url}}</a></td></tr>
    <tr><td>Modbus Port</td><td><a href="javascript:void(0)" class="hb_port">{{port}}</a></td></tr>
    <tr><td>Modbus Encryption Key</td><td><a href="javascript:void(0)" class="hb_encrypt_key">{{encryptKey}}</a></td></tr>
    <tr><td>Poll Rate</td><td><a href="javascript:void(0)" class="hb_pollrate">{{pollrate}}</a></td></tr>
    <tr><td>Modbus Timeout (seconds)</td><td><a href="javascript:void(0)" class="hb_timeout">{{timeout}}</a></td></tr>
    <tr><td>Datalink Reconnect Timeout (seconds)</td><td><a href="javascript:void(0)" class="hb_reconnectTimeout">{{reconnectTimeout}}</a></td></tr>
    <tr><td>Datalink Response Timeout (seconds)</td><td><a href="javascript:void(0)" class="hb_responseTimeout">{{responseTimeout}}</a></td></tr>
    <tr><td>Backup Update Size (records)</td><td><a href="javascript:void(0)" class="hb_historyPackSize">{{historyPackSize}}</a></td></tr>
    <tr><td>Firmware Revision</td><td>{{firmware}}</td></tr>
</table>
