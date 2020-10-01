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

    $('.exception_table')
        .tablesorter({
            sortList: [[0, 0]],
            theme: 'default',
            widgets: ['zebra', 'stickyHeaders', 'filter'],
            widgetOptions: {
                // extra class name added to the sticky header row
                stickyHeaders: '',
                // number or jquery selector targeting the position:fixed element
                stickyHeaders_offset: 0,
                // added to table ID, if it exists
                stickyHeaders_cloneId: '-sticky',
                // trigger "resize" event on headers
                stickyHeaders_addResizeEvent: true,
                // if false and a caption exist, it won't be included in the sticky header
                stickyHeaders_includeCaption: true,
                // The zIndex of the stickyHeaders, allows the user to adjust this to their needs
                stickyHeaders_zIndex: 2,
                // jQuery selector or object to attach sticky header to
                stickyHeaders_attachTo: null,
                // jQuery selector or object to monitor horizontal scroll position (defaults: xScroll > attachTo > window)
                stickyHeaders_xScroll: null,
                // jQuery selector or object to monitor vertical scroll position (defaults: yScroll > attachTo > window)
                stickyHeaders_yScroll: null,

                // scroll table top into view after filtering
                stickyHeaders_filteredToTop: true
            }
        });
    $('.nested')
        .tablesorter({
            theme: 'default',
            widgets: ['zebra', 'stickyHeaders', 'filter'],
            widgetOptions: {
                // extra class name added to the sticky header row
                stickyHeaders: '',
                // number or jquery selector targeting the position:fixed element
                stickyHeaders_offset: 0,
                // added to table ID, if it exists
                stickyHeaders_cloneId: '-sticky',
                // trigger "resize" event on headers
                stickyHeaders_addResizeEvent: true,
                // if false and a caption exist, it won't be included in the sticky header
                stickyHeaders_includeCaption: true,
                // The zIndex of the stickyHeaders, allows the user to adjust this to their needs
                stickyHeaders_zIndex: 2,
                // jQuery selector or object to attach sticky header to
                stickyHeaders_attachTo: null,
                // jQuery selector or object to monitor horizontal scroll position (defaults: xScroll > attachTo > window)
                stickyHeaders_xScroll: null,
                // jQuery selector or object to monitor vertical scroll position (defaults: yScroll > attachTo > window)
                stickyHeaders_yScroll: null,

                // scroll table top into view after filtering
                stickyHeaders_filteredToTop: true
            }
        });

    $('.add_teo_pdf').click(function () {
        excp = $(this).data('exception_name');
        $(this).siblings('.pdf_upload_form').dialog({
            modal: true,
            height: 600,
            width: 900,
            title: 'PDF Datei für ' + excp + ' hochladen'
        }).dialog('open');
    });
    $('.applicable_vehicles').change(function () {
        vv_filter = $(this).val();
        targetid = $(this).data('targetid');
        $('.variant_container_dtcs, .vin_container_dtcs').hide();
        $('.variant_container_log, .vin_container_log').hide();
        if (vv_filter != 'alle' && vv_filter != 'byvin') {
            $.ajax({
                type: "POST",
                url: "index.php",
                data: {
                    action: 'ajaxGetVariant',
                    vv_filter: vv_filter
                }
            })
                .done(function (data) {
                    values = tryParseJSON(data);
                    if (values && typeof values === "object") {
                        $('.wcvariant_select').empty();
                        $.each(values, function (i, item) {

                            $('.wcvariant_select').append($('<option>', {
                                value: item.value,
                                text: item.text
                            }));
                        });

                        $('.variant_container_' + targetid).show();
                    }
                });
        } else if (vv_filter == 'byvin') {
            $('.vin_container_' + targetid).show();
        }
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
