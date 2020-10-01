/**
 * StreetScooter cloud System Custom Javascript Functions
 * Pradeep Mohan <Pradeep.Mohan@streetscooter.eu>
 */
function tryParseJSON(jsonString) {
    try {
        var o = jQuery.parseJSON(jsonString);

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

var setdate = '';

(function ($) {
    $.widget("custom.combobox", {
        _create: function () {
            this.wrapper = $("<span>")
                .addClass("custom-combobox")
                .insertAfter(this.element);
            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },
        _createAutocomplete: function () {
            var selected = this.element.children(":selected"),
                value = selected.val() ? selected.text() : "";
            this.input = $("<input>")
                .appendTo(this.wrapper)
                .val(value)
                .attr("title", "")
                .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: $.proxy(this, "_source")
                })
                .tooltip({
                    tooltipClass: "ui-state-highlight"
                });
            this._on(this.input, {
                autocompleteselect: function (event, ui) {
                    ui.item.option.selected = true;
                    this._trigger("select", event, {
                        item: ui.item.option
                    });
                },
                autocompletechange: "_removeIfInvalid"
            });
        },
        _createShowAllButton: function () {
            var input = this.input,
                wasOpen = false;
            $("<a>")
                .attr("tabIndex", -1)
                .attr("title", "Show All Items")
                .tooltip()
                .appendTo(this.wrapper)
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass("ui-corner-all")
                .addClass("custom-combobox-toggle ui-corner-right")
                .mousedown(function () {
                    wasOpen = input.autocomplete("widget").is(":visible");
                })
                .click(function () {
                    input.focus();
                    // Close if already visible
                    if (wasOpen) {
                        return;
                    }
                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete("search", "");
                });
        },
        _source: function (request, response) {
            var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
            response(this.element.children("option").map(function () {
                var text = $(this).text();
                if (this.value && (!request.term || matcher.test(text)))
                    return {
                        label: text,
                        value: text,
                        option: this
                    };
            }));
        },
        _removeIfInvalid: function (event, ui) {
            // Selected an item, nothing to do
            if (ui.item) {
                return;
            }
            // Search for a match (case-insensitive)
            var combobox = this;
            var value = this.input.val(),
                valueLowerCase = value.toLowerCase(),
                valid = false;
            this.element.children("option").each(function () {
                if ($(this).text().toLowerCase() === valueLowerCase) {
                    this.selected = valid = true;
                    combobox._trigger("select", event, {
                        item: $(this)
                    });
                    return false;
                }
            });
            // Found a match, nothing to do
            if (valid) {
                return;
            }
            // Remove invalid value
            this.input
                .val("")
                .attr("title", value + " didn't match any item")
                .tooltip("open");
            this.element.val("");
            this._delay(function () {
                this.input.tooltip("close").attr("title", "");
            }, 2500);
            this.input.data("ui-autocomplete").term = "";
        },
        autocomplete: function (value) {
            this.element.val(value);
            this.input.val(value);
        },
        _destroy: function () {
            this.wrapper.remove();
            this.element.show();
        }
    });
})(jQuery);


genPvsSopCombo = function () {
    $.ajax({
        type: "POST",
        url: "index.php",
        data: {
            ajax: true,
            action: 'genPvsSopCombo',
            zsp: $(this).val()
        }
    })
        .done(function (msg) {
            $('.pvssop_wrap').html(msg);

        });

}


listVehicles = function () {
    $.ajax({
        type: "POST",
        url: "index.php",
        data: {
            ajax: true,
            action: 'getVariantsAllowed',
            zsp: $(this).val()
        }
    })
        .done(function (msg) {
            $('.variantsAllowed_wrap').html(msg);

        });

}


jQuery(document).ready(function ($) {

    $('.modeljahrchange').change(function () {
        $('.submodeljahr').val($(this).val());
    });


    vin_regen = function () {
        var i = $(this).attr('id').replace(/^\D+/g, '');
        $('.ajaxload').show();
        if ($('#feature_' + i).length) featuredata = $('#feature_' + i).val();
        else featuredata = '';
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'vinBuilder',
                batch: $('#batch_' + i).val(),
                flnum: $('#flnum_' + i).val(),
                vehicle_variant: $('#vinBuilder_' + i).val(), //@todo dont hard code this!
                motortyp_in: $('#motor_' + i).val(),
                modeljahr: $('#modeljahr_' + i).val(),
                fahrzeugaufbau_in: $('#aufbau_' + i).val(),
                feature_in: featuredata,
                vin_method: $('#vin_select_' + i).val()
            }
        })
            .done(function (msg) {
                $('.ajaxload').hide();
                $('#vin_' + i).val(msg);

            });

    }
    // New Vehicle Configuration
    $('#vehicle_variant_wc-0').change(function () {
        $('#vehicle_variant_config-0').val($(this).val());
        $('#vehicleadd').submit();
    });

    $('#vehicle_subconfiguraion-0').val($("#vehicle_subconfiguration_wc-0").val());

    $('#vehicle_subconfiguration_wc-0').change(function () {
        $('#vehicle_subconfiguraion-0').val($(this).val());
    });

    $('#fahrzeug_add_step2').click(function () {
        if ($('#vehicle_variant_config-0').val().length === 0) {
            alert('Bitte eine Fahrzeug-subkonfiguration auswählen');
            return false;
        }
    });
    /*    $('#fahrzeug_add_step2').prop('disabled', true);
        if ($('#vehicle_variant_config-0').val().length > 0)
            $('#fahrzeug_add_step2').prop('disabled', false);*/

    $('#vehicle_subconfiguration_wc-0').blur(function () {
        $('#vehicle_subconfiguration-0').val($(this).val());
        // $('#vehicleadd').submit();
    });
    $('#vehicle_subconfiguration_wc-0').change(function () {
        $('#vehicle_subconfiguration-0').val($(this).val());
    });
    //$('.vin_regen').change(vin_regen);
    $('.vin_regen_all_ctrl').click(function () {
        $(this).parents('form').submit();
    });

    $('.save_vehicles_db').click(function () {
        $('.action_newvehicles').val('saveNewVehicles');
        $(this).parents('form').submit();
    });
    $('.motor_select_grp').change(function () {
        $('.motor_select').val($(this).val())
    });
    $('.batch_select_grp').change(function () {
        $('.batch_select').val($(this).val())
    });
    $('.aufbau_select_grp').change(function () {
        $('.aufbau_select').val($(this).val())
    });
    $('.feature_select_grp').change(function () {
        $('.feature_select').val($(this).val())
    });
    $('.vehicle_variant_grp').change(function () {
        $('.vehicle_variant_select').val($(this).val())
    });
    $('.flnum_ctrl_grp').blur(function () {
        if ($(this).val().length == 6) {
            initcnt = $(this).val();
            increm = 0;

            $('.flnum_ctrl').each(
                function () {
                    setval = parseInt(initcnt) + parseInt(increm);
                    setval = String("000000" + setval).slice(-6);
                    $(this).val(setval);
                    increm = increm + 1;
                });
        }

    });


    allsave_push = function () {
        var changedfields = [];
        $('.sts_edited_comment').each(function () {
            changedfields.push([$(this).attr('id'), $(this).val()]);
            $(this).removeClass('sts_edited_comment');
            $(this).removeData('orgval');
            $(this).removeAttr('data-orgval');
            $(this).siblings('a').remove();

        });

        $('.sts_edited').each(function () {

            changedfields.push([$(this).attr('id'), $(this).html()]);
            $(this).removeClass('sts_edited');
            $(this).removeData('orgval');
            $(this).removeAttr('data-orgval');
            $(this).siblings('a').remove();

        });

        return changedfields;
    }

    $('.allsave').click(function (event) {
        changedfields = allsave_push();
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'overviewedit',
                changedfields: changedfields
            }
        })
            .done(function (msg) {
                $('.save_status').html(msg);
                $('#edited_data').find('tbody').children().remove();
            });
        event.preventDefault();
    });

    $('#vehicles_list_table').on('click', 'span.editable', function () {
        $('.currentval').trigger('blur');
        prev = $(this).html();
        previd = $(this).attr('id');
        $(this).parent('td').html('<input type="text" size="' + prev.length + '" id="' + previd + '" class="currentval" data-orgval="' + prev + '" value="' + prev + '" >');
        $('.currentval').trigger('focus');

    });

    $('body').on('change', '.currentval', function () {
        $(this).addClass('sts_edited');
    });

    $('body').on('change', '.checkinside', function () {
        $(this).addClass('sts_edited_comment');
    });


    $('body').on('blur', '.checkinside', function () {
        if ($(this).hasClass('sts_edited_comment')) {
            editid = $(this).attr('id');
            $('#edited_data').append('<tr><td>' + $('#vin-' + editid.split('-')[1]).html() + '</td><td>-</td><td>' + $(this).val() + '</td></tr>');
        }
    });

    $('body').on('blur', '.currentval', function () {
        tdparent = $(this).parent('td');
        editval = $(this).val();
        editid = $(this).attr('id');
        reset_cntrl = orgval = '';

        if ($(this).hasClass('sts_edited')) {
            sts_edited = 'sts_edited';
            orgval = ' data-orgval="' + $('.currentval').data('orgval') + '" ';
            reset_cntrl = '<a href="#" class="reset_val">[x]</a>';

            $('#edited_data').append('<tr><td>' + $('#vin-' + editid.split('-')[1]).html() + '</td><td>' + $('.currentval').data('orgval') + '</td><td>' + editval + '</td></tr>');

        } else sts_edited = ''

        $('.currentval').remove();
        tdparent.html('<span id="' + editid + '" class="editable ' + sts_edited + '" ' + orgval + ' >' + editval + '</span>' + reset_cntrl);
        //$('.allsave').trigger('click');
    });

    $('body').on('click', '.reset_val', function () {
        related_span = $(this).siblings('span');
        related_span.html(related_span.data('orgval'));
        related_span.removeClass('sts_edited');
        $(this).remove();
        return false;
    });
    $('#vehicles_list_table').on('keydown', '.currentval', function (e) {
        var keyCode = e.keyCode || e.which;

        if (keyCode == 9) {
            event.preventDefault();
            tdparent = $(this).parent('td')

            $('.currentval').trigger('blur');

            if (tdparent.next().find('span.editable').length)
                tdparent.next().find('span.editable').trigger('click');
            else if (tdparent.parent('tr').next().children('td').length) {
                tdparent = tdparent.parent('tr').next().children('td').first();
                tdparent.next().find('span.editable').trigger('click');
            }

        }
    });

    $('#zsp_assign').on('click', 'span.depot_search', function () {

        if ($('.savedepot').length > 0)
            return false;

        prev = $(this).html();
        $(this).hide();
        $(this).parent('td').append($('.zsp_selector_sales_wrap').html() + '<a href="" class="savedepot" data-vehicle_id="' + $(this).parent('td').find('.vehicle_id').val() + '" style="margin-left: 40px"><span class="genericon genericon-checkmark" ></span></a>' +
            '<a href="" class="escdepot"><span class="genericon genericon-close" ></span></a>');
        $('.zsp_selector_sales').combobox();
        return false;

    });

    $('#zsp_assign').on('click', 'span.delivery_date', function () {

        if ($('.save_delivery_date').length > 0)
            return false;
        prev = $(this).html();
        $(this).hide();
        $(this).parent('td').append($('.date_selector_sales_wrap').html() + '<a href="" class="save_delivery_date" data-vehicle_id="' + $(this).parent('td').find('.vehicle_id').val() + '" style="margin-left: 40px"><span class="genericon genericon-checkmark" ></span></a>' +
            '<a href="" class="escdate"><span class="genericon genericon-close" ></span></a>');
        $(".date_selector_sales").datepicker();
        $(".date_selector_sales").datepicker('setDate', setdate);


    });

    $('#zsp_assign').on('click', 'a.escdepot', function () {
        tdparent = $(this).parents('td');
        spanvalue = tdparent.find(':not(.depot_search,.vehicle_id)').remove();
        $('.zsp_selector_sales').combobox('destroy');
        $(tdparent).children('.depot_search').show();
        return false;
    });

    $('#zsp_assign').on('click', 'a.escdate', function () {
        tdparent = $(this).parents('td');
        spanvalue = tdparent.find(':not(.delivery_date,.vehicle_id) ').remove();
        $(".date_selector_sales").datepicker('destroy');
        $(tdparent).children('.delivery_date').show();
        return false;
    });


    $('#zsp_assign').on('click', '.save_delivery_date', function () {
        tdparent = $(this).parents('td');
        $('.ajaxload').show();
        setdate = $(".date_selector_sales").datepicker('getDate');
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'saveDeliveryDate',
                vehicleid: $(this).data('vehicle_id'),
                delivery_date: setdate
            }
        })
            .done(function (msg) {
                $('.ajaxload').hide();
                if (msg != '0') {
                    $(".date_selector_sales").datepicker('destroy');
                    tdparent.html(msg);

                } else
                    alert('Fehler!');

            });
        return false;
    });


    $('#zsp_assign').on('click', '.savedepot', function () {
        tdparent = $(this).parents('td');
        $('.ajaxload').show();
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'savedepotassign',
                vehicleid: $(this).data('vehicle_id'),
                depotid: $('.zsp_selector_sales').val()
            }
        })
            .done(function (msg) {
                $('.ajaxload').hide();
                values = tryParseJSON(msg);
                if (values && typeof values === "object") {

                    if (values.error_status == '0') {
                        $('.zsp_selector_sales').combobox('destroy');
                        tdparent.html(values.contentstr);

                    } else
                        alert(values.contentstr);

                }

            });
        return false;
    });


    $('.loadtemplate_ctrl').click(function () {

        $('.ajaxload').show();

        $.getJSON({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'getTemplate',
                templateId: $('.template').val(),
            }
        })
            .done(function (msg) {
                $('.ajaxload').hide();
                $('.selectColsCheck').prop('checked', false);
                $.each(msg, function (index, value) {
                    $('#' + value).prop('checked', true);
                });


            });
        return false;

    });

    $('.savetemplate_ctrl').click(function () {
        var selectCols = [];
        $('.selectColsCheck:checked').each(function () {
            selectCols.push($(this).val());
        });
        $('.ajaxload').show();

        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'saveTemplate',
                template_new_name: $('.template_new_name').val(),
                template: $('.template').val(),
                selectCols: selectCols
            }
        })
            .done(function (msg) {
                $('.ajaxload').hide();
                $('.templateAction').html(msg);
            });
        return false;
    });

    $('body').on('click', '.save_delivery_ctrl', function () {
        vehicles = [];
        $('.save_delivery_ctrl:checked').each(function () {
            $(this).parents('tr').find('td > span').addClass('to_lock');
            $(this).parents('tr').find('td > .delivery_date_input').attr("required", "").addClass('required_input');
            vehicles.push($(this).data("vehicleid"));
        });
        $('.save_delivery_ctrl:not(:checked)').each(function () {
            $(this).parents('tr').find('td > span').removeClass('to_lock');
            $(this).parents('tr').find('td > .delivery_date_input').attr("required", null).removeClass('required_input');
        });

        $(this).parents('tr').find('td > span').addClass('to_lock');
        $('#deliver_vehicles_list').val(vehicles.join());
    });

    $(function () {
        $table = null;

        if ($('#ajax_delivery_print').length) {
            var $table = $('#ajax_delivery_print'),
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
                    size: 20,
                    page: 0,

                    customAjaxUrl: function (table, url) {
                        if ($('#deliver_vehicles_list').val()) {
                            vchecked_dates = [];
                            $('.save_delivery_ctrl:checked').each(function () {
                                delivery_date = $('#delivery_date_' + $(this).data("vehicleid")).val();
                                myObj = { 'vehicle_id': $(this).data("vehicleid"), 'delivery_date': delivery_date };
                                vchecked_dates.push(myObj);
                            });
                            vchecked_dates = JSON.stringify(vchecked_dates);
                            return url + '&vchecked=' + $('#deliver_vehicles_list').val() + '&vchecked_dates=' + vchecked_dates;

                        } else return url;
                    },
                    //			    //processed to index.php?action=ajaxRows&filter[0]=WS5B16BABGA9&column[0]=0  
                    ajaxUrl: 'index.php?action=ajaxRowsDelivery&page={page}&size={size}&{filterList:filter}&{sortList:column}',
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
        } else if ($('#vehicles_list_table').length) {
            var $table = $('#vehicles_list_table,#zsp_assign'),
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

                    ajaxUrl: 'index.php?action=ajaxRows&page={page}&size={size}&{filterList:filter}&{sortList:column}',
                    ajaxProcessing: function (data) {

                        if (data && data.hasOwnProperty('rows')) {
                            var indx, r, row, c, d = data.rows,
                                // total number of rows (required)
                                total = data.total_rows,
                                // array of header names (optional)
                                headers = data.headers,
                                // cross-reference to match JSON key within data (no spaces)
                                //			            headerXref = headers.join(',').replace(/\s+/g,'').split(','),
                                // all rows: array of arrays; each internal array has the table cell data for that row
                                rows = [],
                                // len should match pager set size (c.size)
                                len = d.length;
                            // this will depend on how the json is set up - see City0.json
                            // rows
                            for (r = 0; r < len; r++) {
                                row = []; // new row array
                                // cells
                                indx = 0;
                                for (c in d[r]) {

                                    row[indx] = d[r][c];
                                    indx++;
                                    //			                if (typeof(c) === "string") {
                                    //			                  // match the key with the header to get the proper column index
                                    //			                  indx = $.inArray( c, headerXref );
                                    //			                  // add each table cell data to row array
                                    //			                  if (indx >= 0) {
                                    //			                    row[indx] = d[r][c];
                                    //			                  }
                                    //			                }

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


        }

        // Initialize tablesorter
        // ***********************
        if ($table !== null) {
            $table
                .tablesorter({
                    theme: 'default',
                    headerTemplate: '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!
                    headers: { 11: { sorter: false } }, //, filter: false
                    //			      widthFixed: true,
                    widgets: ['zebra', 'filter']
                })

                // initialize the pager plugin
                // ****************************
                .tablesorterPager(pagerOptions);
        }

    });

    $('.zsp_selector_pvssop').combobox({ select: genPvsSopCombo });
    $('.zsp_selector_sales_dritt').combobox();
    $('.zsp_selector_pvssopcode').combobox({ select: listVehicles });
    $('.vehicle_selector').combobox();

    $('body').on('click', '.salesSelectVehicle', function () {

        var vehicledetails = $('.vehicle_selector option:selected').text();
        pvsvin = "WS5B14";
        sopvin = "WS5B16";

        if (vehicledetails.indexOf(pvsvin) > -1) {
            pvscnt = $('.pvscnt').val();
            pvscnt++;
            diffpvs = $('.allowedpvs').data('totalallowedpvs') - pvscnt;
            if (diffpvs >= 0) {

                $('.pvscnt').val(pvscnt);

                $('.allowedpvs').html(diffpvs);
                $('.pvsvehicles').append($('.vehicle_selector option:selected').text() + '<br>');
            }
        } else if (vehicledetails.indexOf(sopvin) > -1) {
            sopcnt = $('.sopcnt').val();
            sopcnt++;
            diffsop = $('.allowedsop').data('totalallowedsop') - sopcnt;
            if (diffsop >= 0) {

                $('.sopcnt').val(sopcnt);

                $('.allowedsop').html(diffsop);
                $('.sopvehicles').append($('.vehicle_selector option:selected').text() + '<br>');
            }
        }
        return false;

        //to be saved somwhere alert($('.vehicle_selector').val());

    });
    $('.check_for_sum').change(function () {
        $(this).prop('disabled', true);
        var totalQty = 0;
        var currval = $(this).val();
        $('.check_for_sum').each(function (i, n) {
            totalQty += parseInt($(n).val(), 10);
        });
        prosum = parseInt($('.production_plan_sum').val(), 10);
        if (totalQty > prosum) {
            maxval = parseInt($('.check_for_sum').not(this).first().val());
            $('.check_for_sum').not(this).first().val((maxval - (totalQty - prosum)));

        }
        $(this).prop('disabled', false);
    });

    $('.submitdelplan').click(function (event) {
        var totalQty = 0;
        $('.check_for_sum').each(function (i, n) {
            totalQty += parseInt($(n).val(), 10);
        });

        prosum = parseInt($('.production_plan_sum').val(), 10);

        if (totalQty < prosum) {
            result = window.confirm("Noch " + (prosum - totalQty) + " Fahrzeuge übrig! Klicken Sie Ok trotzdem speichern.");
            if (result === false)
                event.preventDefault();
            else

                return true;
        }

    });

    $('#sortable_delivery').sortable({ axis: "y" });

    $(".date_selector_sales_new").datepicker();
    $(".date_selector_sales_new").datepicker('setDate', setdate);


    $(".date_selector_sales_new_set").click(function () {
        setthisdate = $(".date_selector_sales_new").datepicker('getDate');
        setthisdate = $.datepicker.formatDate("dd.mm.yy", setthisdate);

        $('.save_delivery_ctrl:checked').each(function (i, n) {
            $('#delivery_date_' + $(this).data('vehicleid')).val(setthisdate);
        });
    }
    );


    $('.set_all_checks').click(function () {
        $('.save_delivery_date_ctrl').prop('checked', true);
        return false;
    });
    $('.clear_all_checks').click(function () {
        $('.save_delivery_date_ctrl').prop('checked', false);
        return false;
    });


    $('.thirdparty_vehicle_select').combobox();
    $('.ui-autocomplete-input').css('width', '220px')

    $('.reset_to_zero').click(function () {
        $('.check_for_sum').val(0);
    })


    parse_exclude_vehicles = function () {
        var exclude_vehicles = [];
        $('.exclude_vehicle').each(function () {
            if (!$(this).is(':checked')) exclude_vehicles.push($(this).data('vehicleid'));
        });

        $('.exclude_vehicles').val(exclude_vehicles.join(','));

    }
    $('.exclude_vehicle').change(parse_exclude_vehicles);
    $('.set_all').click(function () {
        $('.exclude_vehicle').prop('checked', true);
        parse_exclude_vehicles();
        return false;
    });
    $('.reset_all').click(function () {
        $('.exclude_vehicle').prop('checked', false);
        parse_exclude_vehicles();
        return false;
    });
    $('.production_loc').change(function () {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'getVehiclesForDeliveryAjax',
                vehicle_variant: $('#vehicle_variant').val(),
                selected_production: $(this).val(),
            }
        })
            .done(function (msg) {
                $('.wrap_vehicles_to_deliver').html(msg);
            });
    });

    $('.transporter_order_date').datepicker();
    //setthisdate=$.datepicker.formatDate( "dd.mm.yy", setthisdate );
    $('body').on('click', '.show_delete_data', function () {
        $(this).siblings('a.delete_date').toggle();
    });

    $('body').on('click', '.delete_date', function () {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajaxTransporterDateDelete',
                transporter_date_id: $(this).data('transporter_date_id')
            }
        })
            .done(function (msg) {
                values = tryParseJSON(msg);
                if (values.transporter_date_id) {
                    ctrl_element = $('#wrap_trdate_' + values.transporter_date_id);
                    ctrl_element.remove();
                } else {
                    alert('Kann nicht gelöscht werden!');
                }
            });

    });
    $('#save_transporter_order_date').click(function () {
        if ($('input.station:checked').length) {
            $('input.station:checked').each(function () {
                ctrl_element = $(this);
                ctrl_element.parents('div').siblings('.existing_dates').append('<img src="images/ajax-loader.gif" class="ajaxload">');
                station_id = $(this).data('station_id');
                setdate = $('.transporter_order_date').datepicker('getDate');
                $.ajax({
                    type: "POST",
                    url: "index.php",
                    data: {
                        ajaxquery: true,
                        action: 'ajaxTransporterDateSave',
                        transporter_date: setdate,
                        station_id: station_id,
                        transporter_id: $('.transporter_id').val(),
                        transporter_name: $('.transporter_id option:selected').html(),
                    }
                })
                    .done(function (msg) {
                        values = tryParseJSON(msg);
                        ctrl_element = $('#transporter_' + values.station_id);
                        ctrl_element.parents('div').siblings('.existing_dates').children('.ajaxload').remove();
                        ctrl_element.parents('div').siblings('.existing_dates').append(values.msg);
                        ctrl_element.prop('checked', false);
                    });
            });
        } else {
            alert('Bitte eine/mehrere Ladesäule wählen.')
        }

        return false;
    });

    if ($('.kbob_vehicles').length) $('.kbob_vehicles').combobox();

    $('.save_vehicle_kbob').click(function () {
        select_val = $(this).parent('div').find('select.kbob_vehicles').val();
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'tempSaveKBOB',
                vehicle_id: select_val,
                delivery_id: $(this).data('delivery_id')
            }
        })
            .done(function (msg) {
                alert(msg);
            });

    });

    $('.init_combobox').each(function () {
        $(this).combobox();
    });
    if ($('.workshop_delivery_date').length) $('.workshop_delivery_date').datepicker();
    $('.save_assign_workshop').click(function () {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {
                ajaxquery: true,
                action: 'ajax_assign_vehicle_workshop',
                vehicle_id: $('.vehicle_workshop_select').val(),
                workshop_id: $('.workshop_select').val(),
            }
        })
            .done(function (msg) {
                values = tryParseJSON(msg);
                if (values.error) {
                    alert(values.error);
                } else {
                    workshop_delivery_ids = $('#workshop_delivery_ids').val();
                    workshop_delivery_ids_array = workshop_delivery_ids.split(',');
                    workshop_delivery_ids_array.push(values.workshop_delivery_id);
                    workshop_delivery_ids = workshop_delivery_ids_array.join(',');
                    $('#workshop_delivery_ids').val(workshop_delivery_ids);
                    $('#workshop_assigned_vehicles > tbody').append('<tr><td>' + values.ikz + '</td><td>' +
                        values.vin + '</td><td>' +
                        values.code + '</td><td>' +
                        values.workshop + '</td><td>' + ''
                        + '</td><td>' +
                        values.fleetpool + '</td><td>' +
                        values.form_element + '</td></tr>');
                }

            });


    });

    $('.new_bundle_add').click(function () {
        bundle_id = $(this).data('bundle_id');
        transporter = $(this).data('transporter');
        super_types = $(this).data('super_types_list');
        if (super_types.length) super_types = super_types.split(',');
        inputfield = '';
        $.each(super_types, function (i, val) {
            inputfield += '<input type="number" class="columns three" name="tid_' + transporter + '_bid_' + bundle_id + '_stype_' + val + '" value="">';
        });
        $('#max_bundle_transporter_' + transporter).val(bundle_id);
        //<div class="row pad_large"><span class="columns one no_pad"><strong>2</strong></span><input type="number" class="columns three" name="tid_1_bid_2_stype_2" value="0"><input type="number" class="columns three" name="tid_1_bid_2_stype_3" value="0"></div>
        $('<div class="row pad_large"><span class="columns one no_pad"><strong>' + bundle_id + '</strong></span>' + inputfield + '</div><br>').insertBefore($(this));
        $(this).data('bundle_id', ++bundle_id);

        return false;
    });

    $('#pool_vehicle').combobox();
    $('#pool_vehicle_depot').combobox();
});


// Validate date for Export CoC XML 
$(document).ready(function () {
   $('#qfauto-1').click( function(){
       var dateformat = /^(0?[1-9]|[12][0-9]|3[01])[\/.-](0?[1-9]|1[012])[\/.-]\d{4}$/;
       var Val_date=$('#cocdate-0').val();
       if(Val_date.match(dateformat))
       {
            var seperator1 = Val_date.split('.');
            var seperator2 = Val_date.split('-');

            if (seperator1.length>1)
            {
              var splitdate = Val_date.split('.');
            }
            else if (seperator2.length>1)
            {
              var splitdate = Val_date.split('-');
            }
            var dd = parseInt(splitdate[0]);
            var mm  = parseInt(splitdate[1]);
            var yy = parseInt(splitdate[2]);
            var ListofDays = [31,28,31,30,31,30,31,31,30,31,30,31];

            if (mm==1 || mm>2)
            {
              if (dd>ListofDays[mm-1])
              {
                  alert('Invalid date format!');
                  return false;
              }
            }
            if (mm==2)
            {
                var lyear = false;
                if ( (!(yy % 4) && yy % 100) || !(yy % 400))
                {
                  lyear = true;
                }
                if ((lyear==false) && (dd>=29))
                {
                  alert('Invalid date format!');
                  return false;
                }
                if ((lyear==true) && (dd>29))
                {
                  alert('Invalid date format!');
                  return false;
                }
            }
        }
        else
        {
            alert("Invalid date format!");
            return false;
        }
   });
});