var refreshStations = function() {

	vehicle_id = $(this).data('vehicleid');
	/* start change */
	var v = $(this).val();

	if ($(this).val() == 'nl') {
		// var value = $('#stationv_'+vehicle_id).val();
		// $('#depotv_'+vehicle_id).data('station', value);
		$('#depoto_' + vehicle_id).next('.custom-combobox').show();
		$('#stationv_' + vehicle_id).val('');
		$('#stationv_' + vehicle_id).hide();
		return false;
	} else {
		// var value = $('#depotv_'+vehicle_id).data('station');

		$('#stationv_' + vehicle_id).show();
		$('#depoto_' + vehicle_id).next('.custom-combobox').hide();

	}

	// vehicle_id=$(this).data('vehicleid');
	// if the depot of the vehicle has not been changed and if the vehicle
	// already has been assigned a station, then show all stations
	if ($(this).data('depotid') == $(this).val() && $(this).data('stationid'))
		action = 'getStationsListForDepot'
		// if assigning a station for a vehicle without a station, or if
		// changing the depot of a vehicle, then get only free stations
	else
		action = 'getFreeStationsListForDepot'

	$(this)
			.next('.custom-combobox')
			.after(
					'<img style="position:absolute; left: 240px; top:12px" class="refreshStations_loading" src="images/ajax-loader.gif">');
	$.ajax({
		type : "POST",
		url : 'index.php',
		data : {
			ajax : true,
			action : action,
			page : "commonajax",
			zsp : $(this).val()
		}
	}).done(
			function(msg) {
				$('#stationv_' + vehicle_id).children('option').remove();
				$('<option>').val('null').text('').appendTo(
						'#stationv_' + vehicle_id);
				if (msg != 'false') {
					stations = jQuery.parseJSON(msg);

					$.each(stations, function(index, station) {
						$('<option>').val(station.val).text(station.label)
								.appendTo('#stationv_' + vehicle_id);
					});

				}
				$('.refreshStations_loading').remove();
				return false;
			});

}

var onSelectWeek = function() {
	if ($('#depotSelect').val())
		gotoUrl();
}

var gotoUrl = function() {
	if ($('.action_get').length > 0)
		appendthis = 'action=' + $('.action_get').val();
	else
		appendthis = '';

	if ($('#zsplSelect').val())
		appendthis += '&zspl=' + $('#zsplSelect').val();

	if ($('#divisionSelect').val())
		appendthis += '&div=' + $('#divisionSelect').val();

	if ($('#depotSelect').val())
		appendthis += '&zsp=' + $('#depotSelect').val();

	// VIN Select for "Auslieferungsparkplatz Dortmund"
	if ($('#vinSelect').val())
		appendthis += '&penta_id=' + $('#vinSelect').val();

	// string='index.php?'+this.element[0].name+'='+ui.item.option.value+appendthis;

	if ($('#lastWeek').is(":checked"))
		appendthis += '&week=last';
	if ($('#currentWeek').is(":checked"))
		appendthis += '&week=current';

	string = 'index.php?' + appendthis;
	window.location = string;
}

jQuery(document)
		.ready(
				function($) {
					if ($("#divisionSelect").length)
						$("#divisionSelect").combobox({
							select : gotoUrl
						});
					if ($("#zsplSelect").length)
						$("#zsplSelect").combobox({
							select : gotoUrl
						});
					if ($("#depotSelect").length)
						$("#depotSelect").combobox({
							select : gotoUrl
						});

					// VIN Select for "Auslieferungsparkplatz Dortmund"
					if ($("#vinSelect").length)
						$("#vinSelect").combobox({
							select : gotoUrl
						});

					if ($('#lastWeek').length)
						$("#lastWeek").click(function() {
							if ($('#depotSelect').val())
								gotoUrl();
						});

					if ($('#currentWeek').length)
						$('#currentWeek').click(function() {
							if ($('#depotSelect').val())
								gotoUrl();
						});

					$('.ui-autocomplete-input').css('width', '250px');

					if ($(".depot_for_vehicle").length)
						$(".depot_for_vehicle").combobox({
							select : refreshStations
						});

					// Zuweisung von Ersatzfahrzeug zum Bestandfahreug
					$(".enablecheckbox").change(
							function() {
								vehicle_id = $(this).data('vehicleid');
								/* start change */
								var v = $(this).val();

								if ($(this).val() != 'null') {
									/* $('#replacement_'+vehicle_id).val('t'); */
									$('#replacement_' + vehicle_id).prop(
											'checked', true);
									showdialogcheckbox = true;
									return false;
								} else {
									$('#replacement_' + vehicle_id).val('t');
									$('#replacement_' + vehicle_id).prop(
											'checked', true);
									showdialogcheckbox = false;
								}
							});
					//

					if ($(".depot_abgabe").length) {
						$(".depot_abgabe").combobox();
						$('.depot_abgabe').next('.custom-combobox').hide();
					}

					/*
					 * $( ".station_for_vehicle" ).change(function () {
					 * vehicle_id=$(this).data('vehicleid'); if (
					 * ($(this).find(":selected").text().toLowerCase().indexOf('1-phasig') >=
					 * 0) && ($('#vehicle_phasentype_'+vehicle_id).val()=='t') ) {
					 * //window.confirm('Werden 3-phasige Fahrzeuge an
					 * 1-phasigen Ladesäulen angeschlossen kann die
					 * (vollständige) Ladung nicht sichergestellt werden.'); if
					 * (!confirm("Werden 3-phasige Fahrzeuge an 1-phasigen
					 * Ladesäulen angeschlossen kann die (vollständige) Ladung
					 * nicht sichergestellt werden.")) {
					 * $(this).val($(this).data('vehicleid')); //form.reset();
					 * return false; } } });
					 */

					// Reset Auswahl falls 3-phasiger Fahrzeug an 1-phasige
					// Ladesäule angeschlossen wird.
					var select = $('.station_for_vehicle');
					var previouslySelected;

					select.focus(function() {
						previouslySelected = this.value;
					});
					select
							.change(function() {
								vehicle_id = $(this).data('vehicleid');
								if (($(this).find(":selected").text()
										.toLowerCase().indexOf('1-phasig') >= 0)
										&& ($(
												'#vehicle_phasentype_'
														+ vehicle_id).val() == 't')) {
									conf = confirm('Werden 3-phasige Fahrzeuge an 1-phasigen Ladesäulen angeschlossen kann die (vollständige) Ladung nicht sichergestellt werden.');
									if (!conf) {
										// reset the select back to previous
										this.value = previouslySelected;
										return;
									}
								}
								return true;
							});

					$('body')
							.on(
									'click',
									'#saveVehMgmt',
									function() {
										showdialog = false;
										showdialogladepunkt = false;

										/*
										 * $(".station_for_vehicle").each(function(i,n){
										 * 
										 * if($(this).val()!=='null' &&
										 * $(this).find("option:selected:contains("+$(this).data('allowedstationtype')+")").length==0) {
										 * showdialog=true; }
										 * 
										 * if( $(this).val()=='null' &&
										 * $(this).find("option:selected:contains("+$(this).data('allowedstationtype')+")").length!=0) {
										 * showdialogladepunkt=true; }
										 * 
										 * });
										 */

										$(".lp_not_empty")
												.each(
														function(i, n) {

															/*
															 * if($(this).val()!=='null' &&
															 * $(this).find("option:selected:contains("+$(this).data('allowedstationtype')+")").length==0)
															 * showdialog=true;
															 */

															if (($(this).val() == 't')
																	&& ($(this)
																			.find(
																					"option:selected:contains("
																							+ $(
																									this)
																									.data(
																											'allowedstationtype')
																							+ ")").length != 0)) {
																showdialogladepunkt = true;
															}

														});

										$(".station_for_replace_vehicle")
												.each(
														function(i, n) {

															if ($(this).val() !== 'null'
																	&& $(this)
																			.find(
																					"option:selected:contains("
																							+ $(
																									this)
																									.data(
																											'allowedstationtype')
																							+ ")").length == 0)
																showdialog = false;
															/*
															 * if(
															 * $(this).val()!=='null' &&
															 * $('replacement_checkbox') )
															 * showdialog=false;
															 */

														});

										if (showdialogladepunkt == true) {

											if (!confirm("Bitte wählen Sie einen Ladepunk aus! Ladepunkt darf nicht leer sein!\n\nMöchten Sie Trotzdem fortfahren, klicken Sie bitte auf OK.")) {
												return false;
											}
											/*
											 * alert("Bitte wählen Sie eine
											 * Ladepunk aus! Ladepunkt darf
											 * nicht leer sein!"); return false;
											 */
										}

										if (showdialog)
											$result = window
													.confirm('Werden 3-phasige Fahrzeuge an 1-phasigen Ladesäulen angeschlossen kann die (vollständige) Ladung nicht sichergestellt werden.');

										if (!$result) {
											event.preventDefault();
											return false;
										}

									});
				});

$(document)
		.ready(
				function() {
					showdialogcheckbox = true;
					$(".lp_not_empty")
							.each(
									function(i, n) {
										if ($(this).is(':checked')) {
											$(this)
													.on(
															'click',
															function() {
																if (showdialogcheckbox == true) {
																	alert('Das Speichern ist nicht möglich, da mehr als ein Bestandsfahrzeug an einem LP!\n\nBitte Ladepunkt erstmal freigeben.');
																	return false;
																}
															});
										}
									});

					$("#debugmsgs_button").click(function() {
						$("#debugmsgsshow").hide();
						$(".msgboxbg").hide();
					});

				});
