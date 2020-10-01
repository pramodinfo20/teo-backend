function tryParseJSON(jsonString) {
    try {
        var o = JSON.parse(jsonString);

        // Handle non-exception-throwing cases:
        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
        // but... JSON.parse(null) returns null, and typeof null === "object",
        // so we must check for that, too. Thankfully, null is falsey, so this suffices:
        if (o && typeof o === "object") {
            return o;
        }
    } catch (e) {
    }

    return false;
};
var dtc_codes = [];
jQuery(document).ready(function ($) {

    $('body').on('change', '.has_misc', function (event) {
        qs_cat = $(this).data('qs_cat_id');
        if ($(this).val() == 'sonstiges') {
            $('#has_misc_' + qs_cat).show();
        } else
            $('#has_misc_' + qs_cat).hide();

    });

    $('body').on('change', '.qs_fault_cat_search', function () {
//		$('#qs_fehler_wrap').css('min-height','330px');
        if ($(this).val() == 0) return;
        if (!$(this).hasClass('child_cat'))
            $('.genByAjax').remove();
        $(this).after('<img src="images/ajax-loader.gif" style="margin-left: 4px" class="ajax_loader">');
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajaxCommon',
                common_action: 'ajaxGetFilterForm',
                qs_fcat_id: $(this).val(),
                qs_fcat_label: $(this).children(":selected").html()
            }
        })
            .done(function (data) {
                values = tryParseJSON(data);
                if (values && typeof values === "object") {
                    $('.ajax_loader').remove();
                    $('#mainCat').after(values.subcat);
                    $('#ajaxFieldsWrap').html(values.qs_fields);
                }
            });
    });

    function init_autocomplete(targetid, datasource, targetcontainer) {
        $(targetid)
        // don't navigate away from the field on tab when selecting an item
            .on("keydown", function (event) {
                if (event.keyCode === $.ui.keyCode.TAB &&
                    $(this).autocomplete("instance").menu.active) {
                    event.preventDefault();
                }
            })
            .autocomplete({
                minLength: 0,
                source: datasource,
                /*function( request, response ) {
                    if(request.term!="*")
                    response( $.ui.autocomplete.filter(datasource, request.term ));
                  },*/
                response: function (event, ui) {
                    if (ui.content.length == 0) {
                        if (targetcontainer == '#selected_dtcs_codes' && $('#teo_dtcs_code').val() == "*") {
                            ecu = $('#teo_ecu_select').val();
                            $(targetcontainer).append('<input type="hidden" value="*" name="' + ecu + '_dtcs" id="' + ecu + '_dtcs" >');
                            $(targetcontainer).append('<a href="#" class="selected_dtc_code_tag" data-ecu="' + ecu + '" data-dtc="*" >' + ecu + ' - * <span class="genericon genericon-close"></span></a>');
                        }
                    }

                },

                focus: function () {
                    // prevent value inserted on focus
                    return false;
                },
                select: function (event, ui) {
                    if (targetcontainer == "#selected_dtcs_codes") {
                        ecu = $('#teo_ecu_select').val();

                        if ($('#' + ecu + '_dtcs').length) {
                            dtcs = $('#' + ecu + '_dtcs').val().split(",");
                            var index = dtcs.indexOf(ui.item.value);
                            if (index == -1) {
                                dtcs.push(ui.item.value);
                                $('#' + ecu + '_dtcs').val(dtcs.join(","));
                            } else {
                                this.value = "";
                                return false;
                            }

                        } else {
                            $(targetcontainer).append('<input type="hidden" value="' + ui.item.value + '" name="' + ecu + '_dtcs" id="' + ecu + '_dtcs" >');
                        }
                        $(targetcontainer).append('<a href="#" class="selected_dtc_code_tag" data-ecu="' + ecu + '" data-dtc="' + ui.item.value + '" >' + ecu + ' - ' + ui.item.value + '<span class="genericon genericon-close"></span></a>');

                    } else {

                        if ($('#log_names').length) {
                            if ($('#log_names').val())
                                log_names = $('#log_names').val().split(",");
                            else log_names = [];
                            var index = log_names.indexOf(ui.item.value);
                            if (index == -1) {
                                log_names.push(ui.item.value);
                                $('#log_names').val(log_names.join(","));
                            } else {
                                this.value = "";
                                return false;
                            }
                        } else {
                            $(targetcontainer).append('<input type="hidden" value="' + ui.item.value + '" name="log_names" id="log_names" >');
                        }

                        $(targetcontainer).append('<a href="#" class="selected_log_name_tag" data-log_name="' + ui.item.value + '" >' + ui.item.value + '<span class="genericon genericon-close"></span></a>');

                    }
                    this.value = "";
                    return false;
                }
            });
    }

    $('body').on('click', '.selected_dtc_code_tag', function () {
        ecu = $(this).data('ecu');
        dtc = $(this).data('dtc') + "";
        if (dtc == '*') $('#' + ecu + '_dtcs').remove();
        else if ($('#' + ecu + '_dtcs').length) {
            dtc_array = $('#' + ecu + '_dtcs').val().split(',');
            var index = dtc_array.indexOf(dtc);
            if (index > -1) {
                dtc_array.splice(index, 1);
            }
            if (dtc_array.length) $('#' + ecu + '_dtcs').val(dtc_array.join(','));
            else $('#' + ecu + '_dtcs').remove();
        }
        $(this).remove();
        return false;
    });


    $('body').on('click', '.selected_log_name_tag', function () {
        log_name = $(this).data('log_name') + "";
        if ($('#log_names').length) {
            log_names = $('#log_names').val().split(",");
            var index = log_names.indexOf(log_name);
            if (index > -1) {
                log_names.splice(index, 1);
            }
            if (log_names.length) $('#log_names').val(log_names.join(','));
            else $('#log_names').remove();
        }
        $(this).remove();
        return false;
    });


    $.ajax({
        type: "POST",
        url: "index.php",
        data: {
            ajaxquery: true,
            common_action: 'ajaxGetLogNames'
        }
    })
        .done(function (data) {
            values = tryParseJSON(data);
            if (values && typeof values === "object") {
                log_names = values;
                init_autocomplete("#log_error_code", log_names, '#selected_log_error');
            }
        });

    $('#teo_ecu_select').change(function () {
        if (!$(this).val()) {
            $("#teo_dtcs_code").autocomplete('destroy');
        }
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                common_action: 'ajaxGetDtcCodes',
                ecu: $(this).val()
            }
        })
            .done(function (data) {
                values = tryParseJSON(data);
                if (values && typeof values === "object") {
                    dtc_codes = values;
                    init_autocomplete("#teo_dtcs_code", dtc_codes, '#selected_dtcs_codes');
                }
            });

    });

});

