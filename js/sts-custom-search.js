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


$(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const getActionParamFromAddress = urlParams.get('action');

    if (getActionParamFromAddress == 'search') {
        var extraPartOfAction = 'Search';
    } else {
        var extraPartOfAction = '';
    }

    var loadingTableHtml = '<div class="loadingtable" ' +
        'style="border: 1px solid #fc0; border-radius: 200px; margin-left: -100px; margin-top: -100px; width: 200px;height: 200px;display: block; background: rgba(255,255,255,0.8) url(images/ajax-loader.gif) 50% 50% no-repeat;' +
        'position: fixed;left: 50%;top: 50%; z-index: 200 "></div>';
    var $table = $('#qs_vehicles_list'),
        // define pager options
        pagerOptions = {
            // target the pager markup - see the HTML block below
            container: $(".pager"),
            // output string - default is '{page}/{totalPages}';
            // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
            // also {page:input} & {startRow:input} will add a modifiable input in place of the value
            output: '{startRow} - {endRow} / {filteredRows} ({totalRows})',
            // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
            // table row set to a height to compensate; default is false
            fixedHeight: true,
            // remove rows from the table to speed up the sort of large tables.
            // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
            removeRows: false,
            size: 50,
            page: 0,

            customAjaxUrl: function (table, url) {
                if ($('#search_value').val()) {
                    if ($("#general_search_cat option:selected").val() == '0') {

                    }
                } else {
                    search_value = '';
                }
                // search_value='&search_value='+$('#search_value').val()+'&general_search_cat='+$( "#general_search_cat option:selected" ).val();

                search_value = $("#general_search").serialize();


                url = url + search_value;

                // if($('#depot_vals').val()) depot_vals='&depot_vals='+$('#depot_vals').val();
                // else depot_vals='';
                // url=url+depot_vals

                if ($('#to_set_vehicles').val()) return url + '&vchecked=' + $('#to_set_vehicles').val(); //used by qs
                if ($('#to_lock_vehicles').val() || $('#to_unlock_vehicles').val()) return url + '&to_lock_vehicles=' + $('#to_lock_vehicles').val() + '&to_unlock_vehicles=' + $('#to_unlock_vehicles').val();
                //used by qm
                else
                    return url;
            },
//	    //processed to index.php?action=ajaxRows&filter[0]=WS5B16BABGA9&column[0]=0  
            ajaxUrl: 'index.php?action=ajaxRows' + extraPartOfAction + '&page={page}&size={size}&{filterList:filter}&{sortList:column}',
            ajaxProcessing: function (data) {
                if (data && data.hasOwnProperty('rows')) {
                    var indx, r, row, c, d = data.rows,
                        // total number of rows (required)
                        total = data.total_rows,
                        // array of header names (optional)
                        headers = data.headers,
                        // cross-reference to match JSON key within data (no spaces)
                        headerXref = headers.join(',').replace(/\s+/g, '').split(','),
                        // all rows: array of arrays; each internal array has the table cell data for that row
                        rows = [],
                        // len should match pager set size (c.size)
                        len = d.length;
                    // this will depend on how the json is set up - see City0.json
                    // rows
                    for (r = 0; r < len; r++) {
                        row = []; // new row array
                        // cells
                        for (c in d[r]) {
                            if (typeof (c) === "string") {
                                // match the key with the header to get the proper column index
                                indx = $.inArray(c, headerXref);
                                // add each table cell data to row array
                                if (indx >= 0) {
                                    row[indx] = d[r][c];
                                }
                            }
                        }
                        rows.push(row); // add new row array to rows array
                    }
                    // in version 2.10, you can optionally return $(rows) a set of table rows within a jQuery object
                    return [total, rows];
                }
            },
            processAjaxOnInit: true,
            // go to page selector - select dropdown that sets the current page
            cssGoto: '.gotoPage'
        };

    // Initialize tablesorter
    // ***********************
    $table
        .tablesorter({
            theme: 'default',
            headerTemplate: '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!
//	      widthFixed: false,
            widgets: ['zebra', 'filter', 'resizable', 'stickyHeaders'],
            widgetOptions: {
                resizable: true,
                stickyHeaders_attachTo: '.wrapper'
            },
            sortList: [[0, 1]],
        })

        // initialize the pager plugin
        // ****************************
        .tablesorterPager(pagerOptions);


    $('body').on('click', '#main_search_button', function (event) {
        event.preventDefault();

        $('#qs_vehicles_list')

        var resort = true, // re-apply the current sort
            callback = function () {
                // do something after the updateAll method has completed

            };


        $('#qs_vehicles_list').trigger("destroy", [resort, callback]);

        var urlParams1 = new URLSearchParams(window.location.search);
        var getActionParamFromAddress1 = urlParams.get('action');

        if (getActionParamFromAddress1 == 'search') {
            var extraPartOfAction1 = 'Search';
            $table
                .tablesorter({
                    theme: 'default',
                    headerTemplate: '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!
//	      widthFixed: false,
                    widgets: ['zebra', 'filter', 'resizable', 'stickyHeaders'],
                    widgetOptions: {
                        resizable: true,
                        stickyHeaders_attachTo: '.wrapper'
                    }
                })

                // re-initialize the pager plugin
                // ****************************
                .tablesorterPager(pagerOptions);
        } else if (getActionParamFromAddress1 == "diagnosticReports") {
            var extraPartOfAction1 = 'diagnosticReports';
            var search_value1 =
                $.ajax({
                    url: "index.php?action=diagnosticSearch",
                    type: 'GET',
                    data: $("#general_search").serialize(),
                    dataType: "json",
                    success: function (data) {
                        fillSelectSingleVehicle(data);
                    }
                });
        }

        return false;
    });


    $('body').on('click', '.show_all_faults', function (event) {
        $('body').append(loadingTableHtml);
        vin = $(this).data("vin");
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajaxGetAllFaults',
                vehicle_id: $(this).data("vehicle_id"),
            }
        })
            .done(function (data) {
                $('.loadingtable').remove();
                $('#dialog-form').html(data).dialog({
                    modal: true,
                    height: 600,
                    width: 900,
                    title: 'Fehler Anzeige ' + vin
                }).dialog('open');
            });
        event.preventDefault();
    });

    var autocomplete_cache = {};
    $('body').on('change', '.qs_fault_cat', function () {
        if ($(this).val() == 0) return;
//		$('<tr><td colspan=12>TEST</td></tr>').insertAfter($(this).parents('tr'));
        $('body').append(loadingTableHtml);
        vin = $(this).data("vin");
        vehicle_id = $(this).data("vehicle_id");
        fault_select = $(this);
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajaxGetQSFaultForm',
                qs_fcat_id: $(this).val(),
                qs_fcat_label: $(this).children(":selected").html(),
                vehicle_id: vehicle_id,
            }
        })
            .done(function (data) {
                $('.loadingtable').remove();
                $('#dialog-form').html(data).dialog({
                    modal: true,
                    height: 700,
                    width: 1024,
                    close: function (event, ui) {
                        $.ajax({
                            type: "POST",
                            url: "index.php",
                            data: {
                                ajaxquery: true,
                                action: 'ajaxUpdateCount',
                                vehicle_id: vehicle_id,
                            }
                        }).done(function (msg) {
                            $('#show_all_faults_wrap_' + vehicle_id).html(msg);
                        });
                    },
                    title: 'Fehler eintragen ' + vin
                }).dialog('open');
                $(".hasAutoComp").autocomplete({
                    minLength: 4,
                    source: function (request, response) {
                        var term = request.term;
                        if (term in autocomplete_cache) {
                            response(autocomplete_cache[term]);
                            return;
                        }

                        action = this.element.data('targetaction');
                        append_params = '';
                        vehicle_variant = this.element.data('vehicle_variant');
                        if (vehicle_variant)
                            append_params += "&vehicle_variant=" + vehicle_variant;
                        $.getJSON("index.php?action=" + action + append_params, request, function (data, status, xhr) {
                            autocomplete_cache[term] = data;
                            response(data);
                        });
                    }
                });

                fault_select.val(0);
            });
    });
    $('body').on('change', '.has_misc', function (event) {
        qs_cat = $(this).data('qs_cat_id');
        fault_sno = $(this).data('fault_sno');
        if ($(this).val() == 'sonstiges') {
            $('#has_misc_' + qs_cat + '_' + fault_sno).show();
        } else
            $('#has_misc_' + qs_cat + '_' + fault_sno).hide();

    });
    $('body').on('click', '.save_qs_faults', function (event) {
        vehicle_id = $(this).data('vehicle_id');
        if (!$('.loadingtable').length)
            $('body').append(loadingTableHtml);

        $.ajax({
            type: "POST",
            url: "index.php",
            data: $('#qs_faults_list').serialize(),
        })
            .done(function (msg) {
                $('.loadingtable').remove();
//			$('#show_all_faults_wrap_'+vehicle_id).html(msg);
                $('#dialog-form').dialog('close');
            });
        event.preventDefault();
    });

    $('body').on('click', '.set_qs_fault_status', function (event) {
        vehicle_id = $(this).data('vehicle_id');
        qs_fcat_id = $(this).data('qs_fcat_id');
        fault_sno = $(this).data('fault_sno');
        link = $(this);
        $(this).children('span').removeClass('genericon-checkmark').html('<img src="images/ajax-loader.gif" class="ajax_loader">');
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                vehicle_id: vehicle_id,
                qs_fcat_id: qs_fcat_id,
                fault_sno: fault_sno,
                action: "ajaxSaveFaultStatus"
            }
        })
            .done(function (msg) {
                link.after(msg);
                link.remove();
            });
        event.preventDefault();
    });

    $('body').on('click', '.delete_fault', function (event) {
        result = window.confirm('Dieser Fehler wirklich löschen?');

        if (!result) {
            event.preventDefault();
            return false;
        }

        vehicle_id = $(this).data('vehicle_id');
        qs_fcat_id = $(this).data('qs_fcat_id');
        fault_sno = $(this).data('fault_sno');
        link = $(this);
        parent_tr = $(this).parents('tr');
        cnt = $(this).parents('tr').children('td').length;

        $(this).children('span').removeClass('genericon-checkmark').html('<img src="images/ajax-loader.gif" class="ajax_loader">');
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                vehicle_id: vehicle_id,
                qs_fcat_id: qs_fcat_id,
                fault_sno: fault_sno,
                action: "ajaxDeleteFault"
            }
        })
            .done(function (msg) {
                parent_tr.find('td').remove();
                parent_tr.append('<td colspan=' + cnt + '>' + msg + '</td>');
            });
        event.preventDefault();
    });


    //used by QS
    $('body').on('change', '.setQSCheck', function () {
        currentvehicles = $('#to_set_vehicles').val();
        if (currentvehicles)
            vehicles = currentvehicles.split(",");
        else
            vehicles = []
        if ($(this).is(':checked')) {
            vehicles.push($(this).data('vehicleid'));
            $('#to_set_vehicles').val(vehicles.join());
        } else {
            var a = vehicles.indexOf($(this).data('vehicleid').toString());
            if (a != -1) vehicles.splice(a, 1);
            $('#to_set_vehicles').val(vehicles.join());
        }

    });

    //used by QM
    $('body').on('change', '.setQMLock', function () {

        to_lock_vehicles = $('#to_lock_vehicles').val();
        if (to_lock_vehicles) to_lock_vehicles = to_lock_vehicles.split(",");
        else to_lock_vehicles = []

        to_unlock_vehicles = $('#to_unlock_vehicles').val();
        if (to_unlock_vehicles) to_unlock_vehicles = to_unlock_vehicles.split(",");
        else to_unlock_vehicles = []

        //checked and originally unlocked, then QM Lock this vehicle
        if ($(this).is(':checked') & $(this).data('orgval') == 'f') {

            $(this).parents('tr').children('td:first-child').children('span').addClass('to_lock');
            to_lock_vehicles.push($(this).data('vehicleid'));
            $('#to_lock_vehicles').val(to_lock_vehicles.join());
        }
        //unchecked and originally unlocked, then remove this from the to_lock_list
        else if (!$(this).is(':checked') & $(this).data('orgval') == 'f') {

            if (to_lock_vehicles.length) {
                $(this).parents('tr').children('td:first-child').children('span').removeClass('to_lock');
                var a = to_lock_vehicles.indexOf($(this).data('vehicleid').toString());
                if (a != -1) to_lock_vehicles.splice(a, 1);
                $('#to_lock_vehicles').val(to_lock_vehicles.join());
            }
        } else if ($(this).is(':checked') & $(this).data('orgval') == 't') {
            if (to_unlock_vehicles.length) {
                $(this).parents('tr').children('td:first-child').children('span').removeClass('to_unlock')
                var a = to_unlock_vehicles.indexOf($(this).data('vehicleid').toString());
                if (a != -1) to_unlock_vehicles.splice(a, 1);
                $('#to_unlock_vehicles').val(to_unlock_vehicles.join());
            }

        }
        //checked and originally checked, then QM unlock this vehicle
        else if (!$(this).is(':checked') & $(this).data('orgval') == 't') {
            $(this).parents('tr').children('td:first-child').children('span').addClass('to_unlock')
            to_unlock_vehicles.push($(this).data('vehicleid'));
            $('#to_unlock_vehicles').val(to_unlock_vehicles.join());
        }
        vid = $(this).data('vehicleid');
        if (!$('#qmlock_comment_' + vid).length)
            $(this).parent('td').append('<br><input type="text" name="qmlock_comment_' + vid + '" placeholder="Bitte Kommentare hinzufügen" id="qmlock_comment_' + vid + '">');
    });
    if ($('#qs_qm_user').length) {
        $('#vehicle_fertig_status').validate({
            rules: {
                qs_qm_user: {
                    required: true
                },
                qs_qm_pass: {
                    required: true
                }
            },
            messages:
                {
                    qs_qm_user: ' Benutzername erforderlich!',
                    qs_qm_pass:
                        {
                            required: ' Passwort erforderlich!'
                        }

                }
        });
    }

    $('body').on('click', '.open_status_control', function (event) {
        targetid = $(this).data('targetid');
        $('#' + targetid).dialog({minWidth: 700, minHeight: 300, modal: true});
        event.preventDefault();
    });

    $('body').on('click', '.qm_func_ctrl', function (event) {
        action = $(this).data('action');
        $('#qs_qm_action').val(action);
        if (action == 'lockAllToday')
            $('.all_lock_status').html('Bitte Benutzername und Passwort eingeben und auf Speichern klicken um alle Fahrzeuge des Tages zu sperren.')
        else if (action == 'unlockAllToday')
            $('.all_lock_status').html('Bitte Benutzername und Passwort eingeben und auf Speichern klicken um alle Fahrzeuge des Tages zu entsperren.')
        else
            $('.all_lock_status').html('Bitte Benutzername und Passwort eingeben und auf Speichern klicken. Nur ausgewählte Fahrzeuge werden gesperrt.')
        event.preventDefault();
    });

    function init_autocomplete(targetid, datasource, valcontainer) {
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
                source: function (request, response) {
                    // delegate back to autocomplete, but extract the last term
                    response($.ui.autocomplete.filter(
                        datasource, request.term));
                },
                focus: function () {
                    // prevent value inserted on focus
                    return false;
                },
                select: function (event, ui) {
                    $('.default_depots').prop('checked', false);
                    depot_id = ui.item.value;
                    depot_name = ui.item.label;
                    depot_str = '<label for="depot_check_' + depot_id + '"><input type="checkbox" name="selected_depots[]" checked=checked id="depot_check_' + depot_id + '" value="' + depot_id + '">' + depot_name + '</label><br>';
                    $('#selected_depot_wrap').append(depot_str);
                    return false;
                }
            });
    }

    init_autocomplete("#depots_search", depot_list, "#depot_vals");

    $('body').on('click', '.edit_program_version', function (event) {
        td = $(this).parent('td');
        oldsw = $(this).data('oldsw');
        diagid = $(this).data('diagid');
        ecu = $(this).data('ecu');
        vehicleid = $(this).data('vehicleid');
        $(this).remove();
        td.children('span').remove();
        td.children('#available_ecu_sw').show();
        td.append('<button data-oldsw="' + oldsw + '" data-diagid="' + diagid + '" data-ecu="' + ecu + '"  data-vehicleid="' + vehicleid + '" class="save_new_program_version">Speichern</button>');
        event.preventDefault();
    });

    $('body').on('click', '.save_new_program_version', function (event) {
        $('body').append(loadingTableHtml);
        vehicleid = $(this).data("vehicleid");
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajaxSaveManualSW',
                diagid: $(this).data("diagid"),
                vehicleid: vehicleid,
                ecu: $(this).data("ecu"),
                swver: $('#available_ecu_sw').val(),
                oldsw: $(this).data("oldsw")
            }
        })
            .done(function (data) {
                $('#dialog-form').dialog('close');
                $('.loadingtable').remove();
                values = tryParseJSON(data);
                if (values && typeof values === "object") {
                    $('#processed_status_wrap_' + vehicleid).html(values.status);
                    alert(values.msg);
                }
            });
        event.preventDefault();
    });

    $('body').on('click', '.show_info', function (event) {
        $('#' + $(this).data('target_id')).toggle();
        event.preventDefault();
    });

    $('body').on('click', '.fetchswentry', function (event) {
        $('body').append(loadingTableHtml);
        vin = $(this).data("vin");
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                action: 'ajaxFetchSWEntry',
                vehicle_id: $(this).data("vehicle_id"),
                vin: vin
            }
        })
            .done(function (data) {
                $('.loadingtable').remove();
                $('#dialog-form').html(data).dialog({
                    modal: true,
                    height: 600,
                    width: 800,
                    title: 'SW Stand eingeben für ' + vin
                }).dialog('open');
            });
        event.preventDefault();
    });

    $('body').on('click', '.fetchbodyentry', function (event) {

        $('body').append(loadingTableHtml);
        vin = $(this).data("vin");
        vehicle_id = $(this).data("vehicle_id");
        data = $(this).data("serial");
        serial = (data === undefined) ? '' : String(data);
        data = $(this).data("date");
        datum = (data === undefined) ? '' : String(data);

        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                action: 'ajaxGetFormBodySN',
                vehicle_id: vehicle_id,
                vin: vin
            }
        })
            .done(function (data) {
                $('.loadingtable').remove();
                $('#h2-body-vin').html(vin);
                $('#body-vehicle_id').val(vehicle_id);
                $('#body-sn').val(serial);
                $('#body-previous').val(serial);
                $('#body-date').val(datum);

                $('#body-sn-input').dialog({
                    modal: true,
                    height: 300,
                    width: 320,
                    title: "Body Seriennummer eingeben für... "
                }).dialog('open');

            });

        event.preventDefault();
    });

    $('#body-submit').click(function () {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                action: 'ajaxSaveManualBodySN',
                vin: $('#h2-body-vin').text(),
                prev: $('#body-previous').val(),
                bodysn: $('#body-sn').val(),
                date: $('#body-date').val()
            }
        })
            .done(function (data) {
                vehicle_id = $('#body-vehicle_id').val();
                update = tryParseJSON(data);
                if (update && (typeof update === "object")) {
                    if (update.error.length)
                        ;

                    $('#id-bodysn_' + vehicle_id).text(update.serial_number);
                    $('#id-bodydate_' + vehicle_id).text(update.timestamp);
                    $('#id-bodyedit_' + vehicle_id).data('serial', update.serial_number);
                    $('#id-bodyedit_' + vehicle_id).data('date', update.timestamp);
                    if (update.action == 'insert') {
                        $('#id-newbodysn_' + vehicle_id).hide();
                        $('#id-editbodysn_' + vehicle_id).show();
                    }
                    $('#body-sn-input').dialog('close');
                }
            });
    });
    $('#body-date').datepicker({
        dateFormat: "dd.mm.yy",
    });


    $('body').on('click', '.fetcherror', function (event) {
        vin = $(this).data("vin");
        $('body').append(loadingTableHtml);
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajaxFetchErrorDetails',
                vehicle_id: $(this).data("vehicle_id"),
                vin: $(this).data("vin"),
                vehicle_variant: $(this).data("vehicle_variant"),
                diagnostic_session_id: $(this).data("diagnostic_session_id")
            }
        })
            .done(function (data) {
                $('.loadingtable').remove();
                $('#dialog-form').html(data).dialog({
                    modal: true,
                    height: 600,
                    width: 800,
                    title: 'Fehler ' + vin
                }).dialog('open');
            });
        event.preventDefault();
    });

    $('.collapsible').click(function () {
        $(this).children('span').toggleClass('genericon-collapse genericon-expand');
        $(this).parent('fieldset').children('.collapsible_content').slideToggle();
    });

    $('body').on('click', '.show_qmlock_info', function (event) {
//            vin=$(this).data("vin");
        $('body').append(loadingTableHtml);
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajaxFetchLockInfo',
                vehicle_id: $(this).data("vehicle_id")
            }
        })
            .done(function (data) {
                $('.loadingtable').remove();
                $('#dialog-form').html(data).dialog({
                    modal: true,
                    height: 600,
                    width: 800,
                    title: 'QM Historie'
                }).dialog('open');
            });
        event.preventDefault();
    });

    var submit_export_csv = function (event) {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: $('#export_teo_form').serialize()
        })
            .done(function (data) {
                values = tryParseJSON(data);
                if (values && typeof values === "object") {
                    if (values.progress) {
                        $('#teo-export-meter').val(values.progress);
                        submit_export_csv();
                    } else {
                        $('#teo-export-meter').val(100);
                        $('#file-url').html(values.file_url);
                    }
                }
            });
    };

    $('body').on('click', '.reset_export_session', function () {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                action: 'reset_export_session',
                export_token: $('#export_token').val()
            }
        })
            .done(function (data) {
                if (data) {
                    $('#teo-export-meter').val(0);
                    $('#file-url').html('');
                    $('#export_token').val($.trim(data));
                    $('#export_teo').show();
                }

            });
    });

    $('#start_date,#end_date').datepicker({
        dateFormat: "yy-mm-dd",
        showWeek: true
    });

    $.datepicker.regional["de"];


    $('#start_prod,#end_prod').datepicker({
        dateFormat: "yy-mm-dd",
        showWeek: true
    });

    $.datepicker.regional["de"];

    $('#export_teo').click(function (event) {
        submit_export_csv(event);
        $(this).hide();
        event.preventDefault();

    });

    var cache = {};
    $("#start_vin, #end_vin").autocomplete({
        minLength: 4,
        source: function (request, response) {
            var term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }

            $.getJSON("index.php?action=ajaxVehicleVinSearch", request, function (data, status, xhr) {
                cache[term] = data;
                response(data);
            });
        },
        select: function (event, ui) {
            this.value = ui.item.label;
            return false;
        }
    });
});

