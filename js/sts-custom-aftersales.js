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

var showTimeSelect = function () {
    $("#fs_config_timestamp").slideDown();
};

//      
//      var showConfig = function(){
//          $.ajax({
//              type: "POST",
//              url: "index.php",
//              data: {action:"abfrage",
//                  ajax: true,
//                  vid: $("#vehicle_search").val(),
//                  showconfig_timestamp: $("#showconfig_timestamp").datetimepicker('getDate')}
//          })
//          .done(function( msg ) {
//              $("#vehicle_configuration").html(msg);
//              $("#vehicle_configuration").slideDown();
//          }); 
//          return false;
//          
//      }
//      

var reload_values = function () {
    $.ajax({
        type: "POST",
        url: "index.php",
        data: {
            cmd: "attr_values",
            attr: $(".attribute_selector").val()
        }
    })
        .done(function (msg) {
            var $el = $(".value_selector_cell");
            $el.empty();
            $el.append('<select class="value_selector" />');
            var values = jQuery.parseJSON(msg);
            $vsel = $(".value_selector", $el);
            $.each(values, function (i, v) {
                $vsel.append($("<option>" + v.value + "</option>").attr("value", v.id));
            });
            $vsel.combobox();
        });
};


var reload_values_2 = function () {
    $.ajax({
        type: "POST",
        url: "ajax/load_configuration_data.php",
        data: {
            cmd: "add_attr_values",
            attr: $(".merkmal_selector").val()
        }
    })
        .done(function (msg) {
            var $el = $(".attribute_selector");
            $el.empty();
            $el.append('<select class="attribute_selector" />');
            var values = jQuery.parseJSON(msg);
            $vsel = $(".attribute_selector", $el);
            $.each(values, function (i, v) {
                $vsel.append($("<option>" + v.value + "</option>").attr("value", v.id));
            });
            $vsel.combobox();
        });
    div_hide();
    $(document).ajaxStop()
};

//Toggle SlideUp and SlideDown()
function show(id) {
    if (document.getElementById) {
        var myDiv = document.getElementById(id);
        myDiv.style.display = (myDiv.style.display == 'block' ? 'none' : 'block');
    }
}

function show_attribute_menu() {
    document.getElementById('newValue').style.display = "inline-flex";
    document.getElementById('AddVehicleConf_div').style.display = "block";
    document.getElementById('Input_AutoFocus').focus();
}

var selectVehicleAftersales = function () {
    $.ajax({
        type: "GET",
        url: "index.php",
        data: {
            vid: $(".vehicle_selector").val(),
            action: 'getOffline',
        }
    })
        .done(function (msg) {
            $('#vehicle_online_status').val(msg);
        });

    $(document).ajaxStop();


};

var global_vehicles = [];
$(document).ready(function () {

    $('.open_status_control').click(function (event) {
        targetid = $(this).data('targetid');
        $('#' + targetid).dialog({minWidth: 500, minHeight: 300, modal: true});
        event.preventDefault();
    });

    $(".vehicle_selector").combobox({select: selectVehicleAftersales}); //@todo remove.. only for temp aftersales


    $("#vehicle_search").combobox({select: showTimeSelect});
    $(".attribute_selector").combobox({select: reload_values});
    $(".add_attribute_selector").combobox(); //remove?
    $(".timestamp_selector").datetimepicker();
    if ($(".timestamp_selector").val() == "")
        $(".timestamp_selector").datetimepicker("setDate", new Date());
    $(".value_selector").combobox();
    $.datepicker.setDefaults($.datepicker.regional["de"]);


    if ($('#showconfig_timestamp').length > 0) {
        $("#showconfig_timestamp").datetimepicker();
        $("#showconfig_timestamp").datetimepicker("setDate", new Date());

    }

    $(".save").click(function () {
        if ($(".update_user").val() == "") {
            alert("Bitte Person, die das Update ausgeführt hat eintragen!");
            return;
        }
        data = [{
            vehicle: $(".vehicle_selector").val(),
            attribute: $(".attribute_selector").val(),
            value: $(".value_selector").val(),
            timestamp: $(".timestamp_selector").datetimepicker('getDate'),
            user: $(".update_user").val(),
            description: $(".update_description").val()
        }];
        $.ajax({
            type: "POST",
            url: "ajax/save_configuration_data.php",
            data: {add: JSON.stringify(data)}
        })
            .done(function (msg) {
                if (msg != "") {
                    alert(msg);
                    return;
                }

                $("#changelog").append("Merkmal " + $(".attribute_selector").val() + " für Fahrzeug " + $(".vehicle_selector").val() + " auf " + $(".value_selector").val() + " geändert<br />");
            });
    });


    $("#add").click(function () {

        if ($(".add_attribute_text").val() == "") {
            alert("Bitte geben Sie einen Wert ein, der hinzugefügt werden soll.");
            return;
        } else if ($(".attribute_selector").val() == "") {
            alert("Bitte wählen Sie ein Merkmal aus, für das ein neuer Wert hinzugefügt werden soll.");
            return;
        }
        data = [{
            attribute: $(".attribute_selector").val(),
            value: $(".add_attribute_text").val(),
            description: $(".add_attribute_description").val()
        }];
        $.ajax({
            type: "POST",
            url: "save_configuration_data.php",
            data: {add_attr: JSON.stringify(data)}
        })
            .done(function (msg) {
                if (msg != "")
                    alert(msg);
                reload_values();
                document.getElementById('Input_AutoFocus').value = '';
            });
    });

    // PopUp POST Function
    $(".add_value").click(function () {
        if (document.getElementById('merkmale').value == "") {
            alert("Bitte geben Sie einen Wert ein, der hinzugefügt werden soll.");
            return;
        }
        data = [{
            value: $(".merkmal_selector").val(),
            description: $(".description_merkmal_selector").val()
        }];
        $.ajax({
            type: "POST",
            url: "save_configuration_data.php",
            data: {add_attr_value: JSON.stringify(data)}
        })
            .done(function (msg) {
                if (msg != "")
                    alert(msg);
                /*reload_values_2();*/
                location.reload();
            });
    });

    $(document).ajaxStart(function () {
        $('.ajaxload').css('visibility', 'visible');
    });
    $(document).ajaxStop(function () {
        $('.ajaxload').css('visibility', 'hidden');
    });


    sts_toggleSlidefn = function sts_toggleSlide(event) {

        ctrl = event.currentTarget;

        target = '#' + $(ctrl).data('target');

        hidetext = $(ctrl).data('hidetext');
        showtext = $(ctrl).data('showtext');

        if ($(target).css('display') == "none") {
            $(target).slideDown();
            $(ctrl).val(hidetext);
        } else {
            $(target).slideUp();
            $(ctrl).val(showtext);
        }


    };


    $(".popup_ctrl").click(sts_toggleSlidefn);


//          
//           $( "#TestingPurpose" ).click(function() {
//                  $( "#AddVehicleConf_div" ).slideToggle();
//                });


//            $( "#popupContainer" ).dialog({autoOpen: false,modal:true});
//          $( "#popup" ).click(function() {
//              $( "#popupContainer" ).dialog( "open" );
//            });

//      add again when using the actual aftersales page
//          $('.sts_submenu').click(function(){
//              $('.sts_submenu').removeClass('selected');
//              $(this).addClass('selected');
//              $('.submenu_target_child').hide();
//              $('#'+$(this).data('target')).show().toggleClass('current');
//              return false;
//          }); 


    var cache = {};
    $("#vehicle_search_new").autocomplete({
        minLength: 4,
        source: function (request, response) {
            var term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }

            $.getJSON("index.php?action=ajaxVehicleSearch", request, function (data, status, xhr) {
                cache[term] = data;
                response(data);
            });
        },
        select: function (event, ui) {
            this.value = ui.item.label;
            targetinput = $("#vehicle_search_new").data('targetinput');
            $('#' + targetinput).val(ui.item.value);
            if (targetinput == 'selected_vehicle')
                $('#vehicle_search_form').submit();

            if ($('#vehicle_online_status').length) {
                $.ajax({
                    type: "GET",
                    url: "index.php",
                    data: {
                        vid: ui.item.value,
                        action: 'getOffline',
                    }
                })
                    .done(function (msg) {
                        $('#vehicle_online_status').val(msg);
                    });

                $(document).ajaxStop();
            }


            return false;
        }
    });

    $('#reset_vehicle_search_new').click(function () {
        $('#vehicle_search_new').val('');
        $('#selected_vehicle').val('');
        return false;
    });

    $('#dailystats')
        .tablesorter({
            theme: 'default',
            widgets: ['zebra', 'stickyHeaders'],
            widgetOptions: {
                stickyHeaders_attachTo: '.dailystats_wrapper'
            }
        });

    $('.show_daily_stats').click(function () {
        thead = [];
        th = $(this).parents('tbody').siblings('thead').find('th').each(function () {
            thead.push($(this).children('div').html());
        });
        tr = $(this).parents('tr');
        tvalues = [];
        tr.children('td').each(function (index) {

            tvalues.push($(this).text())
        });
        data = '<table>';
        $.each(tvalues, function (index, value) {
            data += '<tr><td width="20%" >' + thead[index] + '</td><td>' + value + '</td></tr>';
        });
        $('#dialog-form').html(data).dialog({
            modal: true,
            height: 600,
            width: 900,
            title: 'Daily Stats ' + $(this).data('vin')
        }).dialog('open');
    });
});


function createAccount(workshop_id) {
    var login;
    var edLogin = document.getElementById('id_login_' + workshop_id);
    if (edLogin)
        login = edLogin.value;

    document.location.href = href_wks_login + '&create_account=' + workshop_id + '&login=' + login;
}


