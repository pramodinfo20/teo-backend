function setCookie(cname, cvalue, exdays) {
    var expires = '';

    if (!(exdays === undefined)) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        expires = "expires=" + d.toUTCString();
    }

    document.cookie = cname + "=" + cvalue + ";" + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function Ajaxecute(command, params, asyncCallback) {
    var url = "index.php?" + _urlparams + '&ajaxcmd=' + command + '&' + params;
    var xhttp = new XMLHttpRequest();

    if (asyncCallback === undefined) {
        xhttp.open("GET", url, false);
        xhttp.send(null);

        if (xhttp.status === 200)
            return xhttp.responseText.trim();
        return "";
    }


    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            asyncCallback(this.responseText.trim());
        }
    };

    xhttp.open("GET", url, true);
    xhttp.send(null);

    return "";
}
