var stcytbl_hdrMinWidth = [];
var stcytbl_hdrNewWidth = [];


function stcytbl_update() {
    var hdrChanged = false;
    var stcytbl_sum = 0;
    var num_cols = 0;
    var new_width, back_width;

    $('table.stcytbl tbody tr:nth-child(1) td').each(function (index, td) {
        var w = $(this).width();
        var h = stcytbl_hdrMinWidth[index];
        if (h > w) {
            m = h;
            $(this).width(m);
        } else {
            hdrChanged = true;
            m = w;
        }
        num_cols++;
        stcytbl_sum += m;
        stcytbl_hdrNewWidth[index] = m;
    });


    new_width = stcytbl_sum + 11 * num_cols - 1;
    back_width = new_width + 16;


    $('table.stcytbl-header').width(back_width)
    $('.stcytbl-scroll').width(back_width);
    $('.stcytbl-back').width(back_width);

    if (hdrChanged) {
        $('tr.stcytbl-header td').each(function (index, td) {
            $(this).width(stcytbl_hdrNewWidth[index]);
        });
    }


    var tblHeight = $('.stcytbl-back').height();
    var bodyHeight = tblHeight - $('.stcytbl-header').height();
    $('.stcytbl-scroll').height(bodyHeight);


    /*
    DebugOut (hdrChanged);
    DebugOut (stcytbl_hdrNewWidth);
    DebugOut (stcytbl_hdrMinWidth);
    DebugOut (tblWidth);
    */
}


$(document).ready(function () {
    $('tr.stcytbl-header td').each(function (index, td) {
        var w = $(this).width();
        stcytbl_hdrMinWidth.push(w);
        stcytbl_hdrNewWidth.push(w);
    });
    stcytbl_update();
});

$(window).resize(stcytbl_update);
