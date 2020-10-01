/**
 * StreetScooter cloud System Custom Javascript Functions
 * Pradeep Mohan <Pradeep.Mohan@streetscooter.eu>
 */
var reload_page = false;

jQuery(document).ready(function ($) {

    $('.dep_req_confirm').click(function () {
        if (window.confirm('Diese Mitarbeiter/in wirklich löschen?')) return true;
        else return false;
    });
    //  $('.zspl_email_list_entry').click(function(){
    //      return false;
    //  });
    $('.require_confirm').click(function () {
        if (window.confirm($(this).data('confirmtxt'))) return true;
        else return false;
    });
    $('.neuepassctrl').click(function () {
        nupass = $('.neuepass').data("rpasswd");
        $('#resetpass').val(nupass);
        $('.neuepass').toggle();
        $(this).hide();
        return false;
    });


    $('.parent_hidden_text').click(function () {
        $(this).children('span.genericon').toggleClass("genericon-plus genericon-minus")
        $targetst = $(this).data('target');
        $('.' + $targetst).slideToggle();
        return false;
    });


    $('.open_target_as_dialog').click(function (event) {
        targetid = $(this).data('targetid');
        var wWidth = $(window).width();
        var dWidth = wWidth * 0.8;
        $('#' + targetid).dialog({ width: dWidth, minHeight: 300, maxHeight: 600, modal: true });
        event.preventDefault();
    });


    if ($('#sort_filter_table').length >= 1) {
        var $table = $('#sort_filter_table');
        // Initialize tablesorter
        // ***********************
        $table
            .tablesorter({
                theme: 'default',
                headerTemplate: '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!
                //            widthFixed: true,
                widgets: ['zebra', 'filter', 'resizable']
            });
    }


    if ($('.privacyButtons').length) {
        $('.privacyButtons').children('span').click(function () {
            var what = $(this).data('msg');
            var result = Ajaxecute(what).trim();
            //reload_page = result=='reload';
            var msgdiv = $(this).closest('div');
            msgdiv.css({
                opacity: 0
            });
            if (result == 'reload') {
                window.setTimeout(function () {
                    window.location.href = _this_page;
                }, 1000);
            } else {
                window.setTimeout(function () {
                    msgdiv.css({ display: 'none' });
                }, 2000);
            }
        });
    }

    if ($('.privacyButtonsIntern').length) {
        $('.privacyButton').click(function () {
            var what = $(this).data('msg');

            if ((what == 'Privacy2-accept') && ($('#id_privacy2:visible').length > 0)) {
                alert('Bitte zuerst den "Hinweis Verwendung von Cookies" zustimmen');
                return;
            }
            Ajaxecute(what);
            window.location.href = _this_page;
        });
    }


    /*
    if ($('#cookies').length && (getCookie("acceptCoookies")=='')){
        $('#cookies').css({
            opacity: 0.7    
        });
        
        $('#cookies').children('span').click(function(){
            Ajaxecute ('acceptCoookies','accept=yes');
            setCookie("acceptCoookies", '1');
            $('#cookies').css({
                opacity: 0  
            });
        });
    }
    */
    // $('#delete_sw_button').addClass("disabled");
    // $( '#copy_sw_button' ).addClass( "disabled" );

    $('#dtc_search, #ebomconfig_search, #startval-0, #endval-0').bind("keyup change", function () {
        var input = $(this).val();

        if (input.match(/[^a-zA-Z0-9*\-]/)) {
            $(this).val("");
        }

    });
});