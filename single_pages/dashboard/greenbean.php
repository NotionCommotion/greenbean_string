<div class="container-fluid bg-3 text-center">
    <div class="row">
        <a href="greenbean/report">
            <div class="col-sm-3">
                <p>Report Manager</p>
                <img src="https://placehold.it/150x80?text=Report Manager" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
        <a href="greenbean/point">
            <div class="col-sm-3">
                <p>Point Manager</p>
                <img src="https://placehold.it/150x80?text=Point Manager" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
        <a href="greenbean/chart">
            <div class="col-sm-3">
                <p>Chart Manager</p>
                <img src="https://placehold.it/150x80?text=Chart Manager" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
        <a href="greenbean/source">
            <div class="col-sm-3">
                <p>Data Source Manager</p>
                <img src="https://placehold.it/150x80?text=Data Source Manager" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
    </div>

    <div class="row">
        <a href="greenbean/sandbox">
            <div class="col-sm-3">
                <p>Sandbox</p>
                <img src="https://placehold.it/150x80?text=Sandbox" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
        <a href="greenbean/settings">
            <div class="col-sm-3">
                <p>Account Settings</p>
                <img src="https://placehold.it/150x80?text=Account Settings" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
        <a class="manual" href="javascript:void(0)">
            <div class="col-sm-3">
                <p>Users Manual</p>
                <img src="https://placehold.it/150x80?text=Users Manual" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
        <a href="greenbean/helpdesk">
            <div class="col-sm-3">
                <p>Help Desk</p>
                <img src="https://placehold.it/150x80?text=Help Desk" class="img-responsive" style="width:100%" alt="Image">
            </div>
        </a>
    </div>
</div>
<input type='hidden' id='gb_img_base' value='{{ gb_img_base }}'>
<input type='hidden' id='gb_url_base' value='{{ gb_url_base }}'>
<input type='hidden' id='gb_api_base' value='{{ gb_api_base }}'>
{% include 'dashboard/manual.html' %}