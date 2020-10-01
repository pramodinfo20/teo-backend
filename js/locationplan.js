'use strict';

function SetFilter(checkbox) {
    var id = checkbox.id.substr(3);
    var val = checkbox.checked ? 1 : 0;
    var cmd = 'updateTable';
    var par = 'filter=' + id + '&val=' + val;
    var result = Ajaxecute(cmd, par, function (result) {
        var tbl = document.getElementById('fztbody');
        if (tbl)
            tbl.innerHTML = result;
    });
}

function HighlightTable(vid, hl) {
    var td = document.getElementById('id_td_' + vid);
    if (td) {
        DebugOut('VID:' + vid + '  ' + hl);
        td.style.backgroundColor = hl ? '#c66' : '';
    }

}


function SpinningCircle(vid) {
    var spirale = document.getElementById('spirale');

    if (spirale) {
        if (vid) {
            var spot = document.getElementById('id_spot_' + vid);
            if (spot) {
                var x = spot.offsetLeft - 21;
                var y = spot.offsetTop - 21;

                spirale.style.visibility = 'visible';
                spirale.style.left = x + 'px';
                spirale.style.top = y + 'px';
            }
        } else {
            spirale.style.visibility = 'hidden';
        }
    }
}

function ShowSubLocations(bShow) {
    document.getElementById('FahrzeugListe').style.visibility = (bShow) ? 'hidden' : 'visible';
    document.getElementById('SubLocations').style.visibility = (bShow) ? 'visible' : 'hidden';
    document.getElementById('id_showSubLoc').value = (bShow) ? 1 : 0;
}

function ShowHideSpot(vid, visible) {
    var id_spot = 'id_spot_' + vid;
    var id_kenz = 'id_kenz_' + vid;
    var id_line = 'id_canvas_' + vid;

    var spot = document.getElementById(id_spot);
    var kenz = document.getElementById(id_kenz);
    var line = document.getElementById(id_line);
    var vis = (visible) ? 'visible' : 'hidden';

    if (spot) spot.style.visibility = vis;
    if (kenz) kenz.style.visibility = vis;
    if (line) line.style.visibility = vis;
}

function SelectDeselectAll(value) {
    var allInputs = document.getElementsByTagName('input');
    for (var i = 0, input; input = allInputs[i]; i++) {
        if (input.id.substr(0, 6) == 'id_sel') {
            var vid = input.id.substr(6);
            input.checked = value;
            ShowHideSpot(vid, value);
        }
    }
}

function InvertSelection() {
    var allInputs = document.getElementsByTagName('input');
    for (var i = 0, input; input = allInputs[i]; i++) {
        if (input.id.substr(0, 6) == 'id_sel') {
            var vid = input.id.substr(6);
            input.checked = !input.checked;
            ShowHideSpot(vid, input.checked);
        }
    }
}


function Execute(command) {
    var cmd = document.getElementById('id_command');
    cmd.value = command;
    Submit();
}

var lastSelectedTr = null;

function SelectByKzClick(div_kennzeichen) {
    var vid = div_kennzeichen.id.substr(8);
    var tr = document.getElementById('id_tr_' + vid);
    if (tr) {
        tr.scrollIntoView(false);
        tr.style.background = '#dddd88';
    }

    if (lastSelectedTr && (lastSelectedTr != tr)) {
        lastSelectedTr.style.background = 'none';
    }
    lastSelectedTr = tr;
}

function filterByLocation(selBox) {
    var index = selBox.selectedIndex;
    var value = selBox.options[index].value;

}


function ShowHideSeachPanels(strShow) {
    document.getElementById('Umkreissuche').style.visibility = strShow;
    document.getElementById('Einzelsuche').style.visibility = strShow;
    document.getElementById('FahrzeugListe').style.visibility = strShow;
    document.getElementById('id_option_append').style.visibility = strShow;
    // document.getElementById ('SubLocations').style.visibility = strShow;
}

function maximizeTable() {
    document.getElementById('id_maximize').style.visibility = 'hidden';
    document.getElementById('id_restore').style.visibility = 'visible';
    ShowHideSeachPanels('hidden');

    var div = document.getElementById('TabellenFunktionen');
    var top = div.offsetTop;
    div.style.top = (top + 300) + 'px';

    div = document.getElementById('Tabelle');
    var height = div.offsetHeight;
    div.style.height = (height + 300) + 'px';
}


function restoreTable() {
    document.getElementById('id_maximize').style.visibility = 'visible';
    document.getElementById('id_restore').style.visibility = 'hidden';
    ShowHideSeachPanels('visible');

    var div = document.getElementById('TabellenFunktionen');
    var top = div.offsetTop;
    div.style.top = (top - 300) + 'px';

    div = document.getElementById('Tabelle');
    var height = div.offsetHeight;
    div.style.height = (height - 300) + 'px';
}


var preview = null;


function closePrintPrewiew() {
    if (preview && !preview.closed) preview.close();
}

function openPrintPrewiew(url) {
    var args = 'height=900,width=700,location=no,menubar=no,resizable=no,scrollbars=yes,toolbar=no,left=200,top=40';
    closePrintPrewiew();
    preview = window.open(url, 'PrintPreview', args);
    return (preview) ? false : true;
}


(function () {

    var prevKarte = null, prevFaehnchen = null, prevCanvas = null;
    var dieVierKarten = [0, 0, 0, 0, 0];

    function FindePosition(oElement) {
        if (typeof (oElement.offsetParent) != "undefined") {
            for (var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent) {
                posX += oElement.offsetLeft;
                posY += oElement.offsetTop;
            }
            return [posX, posY];
        } else {
            return [oElement.x, oElement.y];
        }
    }

    function Koordinaten(e, k, pos) {
        var PosX = 0,
            PosY = 0,

            lageplan = document.getElementById('id_canvas' + k),
            ImgPos = FindePosition(lageplan);

        alert(ImgPos);

        if (!e) var e = window.event;
        if (e.pageX || e.pageY) {
            PosX = e.pageX;
            PosY = e.pageY;
        } else if (e.clientX || e.clientY) {
            PosX = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            PosY = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }
        pos[0] = PosX - ImgPos[0];
        pos[1] = PosY - ImgPos[1];
    }


    function zeichneKreis(canvas, pos) //, r=-1)
    {
        var ppmx = canvas.dataset.ppmx;
        var ppmy = canvas.dataset.ppmy;
        var vpX = pos[0];
        var vpY = pos[1];
        var radius = document.getElementById('id_radius').value; //(r<0) ? document.getElementById ('id_radius').value : r;

        var cw = Math.round(radius * ppmx);
        var ch = Math.round(radius * ppmy);
        var cntx = canvas.getContext("2d");

        cntx.beginPath();
        cntx.clearRect(0, 0, canvas.offsetWidth, canvas.offsetHeight);
        cntx.fillStyle = "rgba(255,128,128,0.2)";
        cntx.lineWidth = 2;
        cntx.arc(vpX, vpY, cw, 0, Math.PI * 2, false);
        cntx.closePath();
        cntx.strokeStyle = '#003300';
        cntx.stroke();
        cntx.fill();
    }


    function stoereMeineKreiseNicht(canvas) {
        var vpX = document.getElementById('id_vpX').value;
        var vpY = document.getElementById('id_vpY').value;
        var pos = [vpX, vpY];
        zeichneKreis(canvas, pos);
    }

    function releaseViewpoint() {
        if (prevFaehnchen)
            prevFaehnchen.style.visibility = 'hidden';
        prevFaehnchen = null;

        if (prevKarte)
            prevKarte.style.borderColor = '#dddddd';
        prevKarte = null;

        if (prevCanvas) {
            var cntx = prevCanvas.getContext("2d");
            cntx.clearRect(0, 0, prevCanvas.offsetWidth, prevCanvas.offsetHeight);
        }
        prevCanvas = null;
    }


    function setViewpoint(k, pos) //, r=-1, bAdd=false)
    {
        var karte = document.getElementById('karte' + k);
        var canvas = document.getElementById('id_canvas' + k);
        var faehnchen = document.getElementById('faehnchen' + k);

        if (prevKarte && (prevKarte != karte))
            releaseViewpoint();

        prevFaehnchen = faehnchen;
        prevKarte = karte;
        prevCanvas = canvas;

        faehnchen.style.left = (pos[0] + 2) + 'px';
        faehnchen.style.top = (pos[1] - 26) + 'px';

        faehnchen.style.visibility = 'visible';
        karte.style.borderColor = '#dd2200';
        zeichneKreis(canvas, pos); //, r);

        var umkreissuche = document.getElementById('Umkreissuche');
        var allInputs = umkreissuche.getElementsByTagName('input');
        for (var i = 0, input; input = allInputs[i]; i++) {
            input.disabled = false;
        }

    }

    /*
    function onSelectSubLocation(e)
    {
        var index = this.selectedIndex;
        var value = this.options[index].value; 
        var cont  = this.options[index].text; 

        document.getElementById('id_location_name').value = cont;
        
        var ar = value.split(',', 4);
        
        var ID = ar[0]; 
        var num = ar[1]; 
        var i= 1;
        
        for (var n=0; n<num; n++)
        {
            var X = ar[i+1];
            var Y = ar[i+2];
            var R = ar[i+3];
            setViewpoint (1, [X,Y], R);
        }
    }
    */

    function DrawLines() {
        var tabelle = document.getElementById('fztbody');
        var trs = tabelle.getElementsByTagName("tr");
        var karten = [0, 0, 0, 0], ctxe = [0, 0, 0, 0];
        var vid, canvas, ctx, h, w;


        for (var t = 1; t < trs.length; t++) {
            vid = trs[t].dataset.vid;

            canvas = document.getElementById('id_canvas_' + vid);
            if (canvas) {
                ctx = canvas.getContext("2d");
                ctx.lineWidth = 2;
                w = canvas.offsetWidth;
                h = canvas.offsetHeight;
                switch (canvas.dataset.dir) {
                    case 'OL':
                    case 'UR':
                        ctx.beginPath();
                        ctx.moveTo(0, 0);
                        ctx.lineTo(w, h);
                        ctx.stroke();
                        break;
                    case 'OR':
                    case 'UL':
                        ctx.beginPath();
                        ctx.moveTo(0, h);
                        ctx.lineTo(w, 0);
                        ctx.stroke();
                        break;
                }
            }
        }
    }


    function OnImageClick(e) {
        var k = this.dataset.index;
        var pos = [0, 0];

        Koordinaten(e, k, pos);

        document.mainForm.vpX.value = pos[0];
        document.mainForm.vpY.value = pos[1];
        document.mainForm.vpZ.value = k;

        setViewpoint(k, pos);

        //ShowSubLocations (k==1);
    }


    function init() {
        var i, karte, canvas, faehnchen; //, selbox;

        for (i = 1; i <= 4; i++) {
            karte = document.getElementById('karte' + i);
            if (karte) {
                dieVierKarten[i] = karte;
                karte.addEventListener('click', OnImageClick);
            }

            canvas = document.getElementById('id_canvas' + i);
            if (canvas) {
                canvas.addEventListener('click', OnImageClick);
            }

            faehnchen = document.getElementById('faehnchen' + i);
            if (faehnchen && (faehnchen.style.visibility == 'visible')) {
                prevFaehnchen = faehnchen;
                prevKarte = karte;
                stoereMeineKreiseNicht(canvas);
            }

        }

        // selbox = document.getElementById('id_sel_subloc');
        // if (selbox)
        //     selbox.addEventListener('click', onSelectSubLocation);

        var checkbox = document.getElementById('id_option_append');
        if (checkbox)
            checkbox.focus();

        DrawLines();

        if (document.getElementById('printversion')) {
            window.print();
            window.close();
        }

        var submenu = document.getElementById('id_submenu');
        var hrefPrint = document.getElementById('id_print');
        if (submenu && hrefPrint) {
            var li = document.createElement("li");
            var a = document.createElement("a");
            var href = hrefPrint.getAttribute('href');
            a.setAttribute('href', href);
            a.setAttribute('class', 'sts_submenu');
            a.onclick = hrefPrint.onclick;
            a.innerHTML = '<img src="images/symbols/printer-12x12.png">&nbsp;Drucken';
            li.appendChild(a);
            submenu.appendChild(li);
        }
    }

    document.addEventListener('DOMContentLoaded', init);
}());

// Validate VIN, AKZ, IKZ and Pennta_kennword search for Lagerplan in js/locationplan.js
$(document).ready(function () {

    $('#id_suchen').click(function () {
        if ($('#id_suchtext').val() == '') {
            return false;
        } else {
            return true;
        }
    });

    $('#id_suchtext').bind("keypress keyup change click", function () {
        if ($('#id_suchtext').val().match(/[^a-zA-Z0-9-?* ]/)) {
            $('#id_suchtext').val("");
            return false;
        } else {
            return true;
        }
    });

    $('#id_select').click(function () {
        if ($('#id_suchtext').val() == '') {
            return false;
        } else {
            return true;
        }
    });

    $('#loadList').click(function () {
        if ($('#liste').val() == '') {
            return false;
        } else {
            return true;
        }
    });

});











