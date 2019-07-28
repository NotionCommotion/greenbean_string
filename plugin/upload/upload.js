var Upload = function (elem) {
    var file = $(elem)[0].files[0];

    this.file = file;
    //this.data = data;
    this.progressContainer = document.createElement("div"),
    this.progressBar = document.createElement("div"),
    this.progressStatus = document.createElement("div");
    this.progressContainer.setAttribute("class", "progress-wrp");
    this.progressBar.setAttribute("class", "progress-bar");
    this.progressStatus.setAttribute("class", "status");
    this.progressContainer.appendChild(this.progressBar);
    this.progressContainer.appendChild(this.progressStatus);
    elem.parentNode.insertBefore(this.progressContainer, elem.nextSibling);
}

Upload.prototype.getType = function() {
    return this.file.type;
};
Upload.prototype.getSize = function() {
    return this.file.size;
};
Upload.prototype.getName = function() {
    return this.file.name;
};

Upload.prototype.showProgressBar = function() {
    this.progressContainer.style.display = 'block';
};
Upload.prototype.hideProgressBar = function() {
    this.progressContainer.style.display = 'none';
};

Upload.prototype.start = function (url, successCallback, form, method) {
    if(!method) method='POST';
    //console.log('data', form);

    var formData = new FormData(form);
    this.showProgressBar();

    // add assoc key values, this will be posts values
    formData.append("file", this.file, this.getName());
    formData.append("upload_file", true);

    $.ajax({
        type: method,
        url: url,
        /*
        xhr: () =>  {
        var myXhr = $.ajaxSettings.xhr();
        if (myXhr.upload) {
        myXhr.upload.addEventListener('progress', this.progressHandling.bind(this), false);
        }
        return myXhr;
        },
        */
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                myXhr.upload.addEventListener('progress', this.progressHandling.bind(this), false);
            }
            return myXhr;
        }.bind(this),
        success: successCallback,
        error: function (error) {
            var o=JSON.parse(error.responseText);
            if(typeof o ==='object' && typeof o.message !== 'undefined' && o.message) {
                alert(o.message)
            }
            else if(error.responseText){
                alert(error.responseText)
            }
            else {
                alert('Unknown error')
            }
        },
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000
    });
};

Upload.prototype.progressHandling = function (event) {
    var percent = 0;
    var position = event.loaded || event.position;
    var total = event.total;
    if (event.lengthComputable) {
        percent = Math.ceil(position / total * 100);
    }
    // update progress classes so it fits your code
    percent=percent + "%";
    this.progressStatus.textContent=percent;
    this.progressBar.style.width=percent;
};
