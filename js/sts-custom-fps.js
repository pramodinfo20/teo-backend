/**
 * StreetScooter cloud System Custom Javascript Functions
 * Pradeep Mohan <Pradeep.Mohan@streetscooter.eu>
 */

showLadepunkte = function () {
    $('#ladepunkteTable').html('<img style="position:absolute; left: 240px; top:12px" class="refreshStations_loading" src="images/ajax-loader.gif">');
    $.ajax({
        type: "POST",
        url: 'index.php',
        data: {
            action: "showLadepunkte",
            ajax: true,
            zsp: $(".zsp_selector_fps_auszulieferende").val()
        }
    })
        .done(function (msg) {
            $('#ladepunkteTable').html(msg);
        });


}

jQuery(document).ready(function ($) {


    $('#list_depots_ctrl').change(function () {

        $.ajax({
            type: "POST",
            url: 'index.php',
            data: {
                action: "getZspEmails",
                ajax: true,
                zsp: $(this).val()
            }
        })
            .done(function (msg) {
                $('#depotemails').val(msg);
            });

    });


    $('body').on('change', '.vehicle_variant_selector', function (event) {

        restriction_id = $(this).data('restriction_id');
        sopcnt = $(".vehicle_variant_selector[data-restriction_id=" + restriction_id + "] option:selected[data-vtype='sop']").length;
        pvscnt = $(".vehicle_variant_selector[data-restriction_id=" + restriction_id + "] option:selected[data-vtype='pvs']").length;


        $.ajax({
            type: "POST",
            url: 'index.php',
            data: {
                action: "checkPossibleCombos",
                ajax: true,
                restriction_id: restriction_id,
                sopcnt: sopcnt,
                pvscnt: pvscnt,
            }
        })
            .done(function (msg) {
                if (msg == 0) {
                    $('#ladepunkteTableErrors').html('<span class="error_msg">Kombination aus technischen/Ladeinfrastruktur-gründen nicht möglich!</span>');
                    return false;
                } else
                    $('#ladepunkteTableErrors').html('');
            });

    });

    $('body').on('click', '#savelp_variant', function (event) {
        showdialog = false;
        $(".vehicle_variant_selector").each(function (i, n) {
            if ($(this).val() != 'null' && ($(this).data('allowedvehicle') != "") && ($(this).val() != $(this).data('allowedvehicle'))) {
                showdialog = false; //@todo change this to true if $(this).val() NTO IN array ($(this).data('allowedvehicle')
            }

        });

        $result = true;

        if (showdialog)
            $result = window.confirm('Werden 3-phasige Fahrzeuge an 1-phasigen Ladesäulen angeschlossen kann die (vollständige) Ladung nicht sichergestellt werden.');

        if (!$result) {
            event.preventDefault();
            return false;
        }

    });

    $('body').on('click', '.active_check', function () {
        name = $(this).attr('name');
        day = name.split('_')[1];
        if ($(this).prop('checked')) {
            $('#departure_' + day).prop('disabled', false);
            $('#second_departure_' + day).prop('disabled', false);
        } else {
            $('#departure_' + day).prop('disabled', true);
            $('#second_departure_' + day).prop('disabled', true);
        }

    });
//	
//	$('body').on('click','.pvsctrl',function(){
//		diffpvs=$('.allowedpvs').data('totalallowedpvs')-$('.pvsctrl:checked').length
//		if(diffpvs>=0)
//			$('.allowedpvs').html(diffpvs);
//		else
//			return false;
//	});
//	
//	$('body').on('click','.sopctrl',function(){
//		diffsop=$('.allowedsop').data('totalallowedsop')-$('.sopctrl:checked').length
//		if(diffsop>=0)
//			$('.allowedsop').html(diffsop);
//		else
//			return false;
//	});
//	

    /**
     * retrieve previously assigned station for this vehicle, unassign it and set the new station to the vehicle as null
     */
    //@todo 2016-09-01 move to common
    $('body').on('focus', '.station_for_vehicle', function () {
        previous = $(this).val();

    }).on('change', '.station_for_vehicle', function () {
        selected_station_id = $(this).val();
        selected_vehicle_id = $(this).data('vehicleid');

        $('select.station_for_vehicle option:selected[value=' + selected_station_id + ']').each(function (index, val) {
            if ($(val).parent('select').data('vehicleid') != selected_vehicle_id) //not the same select
            {
                $(val).parent('select').val('null');
                previous = selected_station_id;
                return false;
            }

        });
    });


    $('.set_departures_depot').click(function () {
        window.location.href = "index.php?action=set_departures_oz&zsp=" + $('#depotSelect').val();
        return false;

    });


    $('.set_departures_zspl').click(function () {
        window.location.href = "index.php?action=set_departures_oz&zspl=" + $('#zsplSelect').val();
        return false;

    });

    $('.latechargingtime').change(function () {
        if ($(this).val() == '')
            $(this).parents('tr').find('.latecharging').prop('checked', false);
        else
            $(this).parents('tr').find('.latecharging').prop('checked', true);
    });

    $('.latecharging').change(function () {
        if ($(this).prop('checked') === false)
            $(this).parents('tr').find('.latechargingtime').prop('disabled', true);
        else
            $(this).parents('tr').find('.latechargingtime').prop('disabled', false);
    });


    $('.set_late_charge_all').click(function () {

        $('.latechargingtime').val($('.set_late_charge_time').val());
        if ($('.set_late_charge_time').val() != '') {
            $('.latecharging').prop('checked', true);
            $('.latechargingtime').prop('disabled', false);
        } else {
            $('.latecharging').prop('checked', false);
            $('.latechargingtime').prop('disabled', false);
        }
        return false;

    });
    $('.movedown, .moveup').hover(function () {
        $(this).parents('tr').find('.sname').css('color', 'red');
    }, function () {
        $(this).parents('tr').find('.sname').css('color', '#000');
    });
    $('.movedown').click(function () {
        stations_ids = [];
        currenttr = $(this).parents('tr');
        currenttr.find('.sname').css('font-weight', 'bold');
        nexttr = $(this).parents('tr').next('tr');
        currenttr.insertAfter(nexttr);
        $('#stations_priority_list').find('td.sname').each(function () {
            stations_ids.push($(this).data('station_id'))
        });
        $('#stations_priority').val(stations_ids.join());
        return false;
    });

    $('.moveup').click(function () {
        stations_ids = [];
        currenttr = $(this).parents('tr');
        currenttr.find('.sname').css('font-weight', 'bold');
        prevtr = $(this).parents('tr').prev('tr');
        currenttr.insertBefore(prevtr);
        $('#stations_priority_list').find('td.sname').each(function () {
            stations_ids.push($(this).data('station_id'))
        });
        $('#stations_priority').val(stations_ids.join());
        return false;
    });
});