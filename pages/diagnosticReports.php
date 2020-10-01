<?php
/**
 * Created by PhpStorm.
 * User: Jakub Kotlorz, FEV
 * Date: 2/1/19
 * Time: 10:37 AM
 */
?>

<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/qs.menu.php";
?>

<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>

<div class="row">
    <div id="search_window" class="columns twelve">
        <fieldset class="">
            <legend class="collapsible"><span class="genericon genericon-expand"></span>Search Vehicle</legend>
            <div id="search_window_collapsible" class="collapsible_content">

                <h3>1. Search vehicle in DB</h3>
                <?php echo $this->searchController->form->genSearchForm() ?>
                <?php echo $this->searchController->depotFilterContent; ?>

                <h3>2. Select single vehicle</h3>
                <select id="sel-select-one-vehicle" disabled name="" style="width: 100%;"></select>


                <!--    TODO: Implement search view-->

                <div class="options-bar">
                    <ul class="submenu_ul">
                        <li><span id="generate-raport" class="sts_submenu W140 disabled">Generate raport</span></li>
                    </ul>
                </div>

            </div>
        </fieldset>
    </div>

    <div id="window-1" class="columns twelve" hidden>
        <fieldset class="">
            <legend class="collapsible"><span class="genericon genericon-expand"></span>Window 1</legend>
            <div class="collapsible_content" style="">

                <h2>Header</h2>
                <table>
                    <tr>
                        <th>VIN</th>
                        <th>Date of production</th>
                        <th>date of first TEO session</th>
                        <th>configuration name</th>
                        <th>All TEO/SIA runs</th>
                    </tr>

                    <tr>
                        <td><?php echo $this->vehicleDataArray['vin']; ?></td>
                        <td><?php echo $this->vehicleDataArray['date_of_production']; ?></td>
                        <td><?php echo $this->vehicleDataArray['teo_date']; ?></td>
                        <td><?php echo $this->vehicleDataArray['windchill_variant_name']; ?></td>
                        <td><input id="btnAllTeoSiaRuns" type="button" value="Show all"></td>
                    </tr>
                </table>

                <h2>ECUs</h2>
                <table>
                    <tr>
                        <th>Parameters</th>
                        <th>DTC</th>
                        <th>SW actions status</th>
                        <th>Manual tests status</th>
                        <th>Semi automatic tests status</th>
                        <th>Data not according to CAN matrix</th>
                    </tr>


                </table>
                <h2>Others</h2>

                <table>
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>State of series number</td>
                        <td><?php echo $this->vehicleDataArray['all_series_numbers_registered'] == true ? "All registered" : "Not all registered"; ?>
                            <input id="btnAllSeriesNumbers" type="button" value="Show all series numbers"></td>
                    </tr>
                    <tr>
                        <td>QM lock state</td>
                        <td><?php echo $this->vehicleDataArray['qmLocked'] == "t" ? "TRUE" : "FALSE"; ?>
                            <input id="btnQMLockStateHistory" type="button" value="Show history"></td>
                    </tr>
                    <tr>
                        <td>Status of mechanical errors</td>
                        <td>TODO <input id="btnMechanicalErrors" type="button" value="Show history"></td>
                    </tr>
                    <tr>
                        <td>Vehicle position</td>
                        <td><input id="btnVehiclePosition" type="button" value="Current position"></td>
                    </tr>
                </table>

                <h2>Overview table</h2>
                <table>
                    <tr>
                        <th>Table</th>
                    </tr>
                    <tr>
                        <td>TODO</td>
                    </tr>
                </table>
            </div>
        </fieldset>
    </div>

    <div id="window-2" class="columns twelve" hidden>
        <fieldset class="">
            <legend class="collapsible"><span class="genericon genericon-expand"></span>Window 2</legend>
            <div class="collapsible_content" style="">
                <h2>General</h2>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>VIN</td>
                        <td><?php echo $this->vehicleDataArray['vin']; ?></td>
                    </tr>
                    <tr>
                        <td>ZSP</td>
                        <td><?php echo $this->vehicleDataArray['depot_name']; ?></td>
                    </tr>
                    <tr>
                        <td>Dealer/ <br> service partner/ <br> Workshop</td>
                        <!--                        <td>-->
                        <?php //echo $this->vehicleDataArray['workshop_name'] . '/' . $this->vehicleDataArray['workshop_company_name'] ?><!--</td>-->
                        <td><?php echo $this->vehicleDataArray['workshop_name'] . ' / ' . $this->vehicleDataArray['workshop_location'] . ' / ' . $this->vehicleDataArray['workshop_street'] ?></td>
                    </tr>
                    <tr>
                        <td>Number plate/ <br> Certificate of conformance/ <br>Single vehicle permission (EBE)</td>
                        <td>COC: <?php echo $this->vehicleDataArray['approval_code'] ?> <br>
                            EBE: <?php echo $this->vehicleDataArray['hsn'] ?> <br>
                        </td>
                    </tr>
                    <tr>
                        <td>Vehicle configuration/ <br> sub configuration</td>
                        <td><a><?php echo $this->vehicleDataArray['windchill_variant_name']; ?></a><br>
                            TODO in Phase 8: links to TB-1682 "output GUI vehicle
                            configuration viewer" - directly selecting this vehicle configuration / sub configuration
                        </td>
                    </tr>
                    <tr>
                        <td>Date of EOL</td>
                        <td><?php echo $this->vehicleDataArray['eol_date']; ?></td>
                    <tr>
                        <td>Battery type</td>
                        <td><?php echo $this->vehicleDataArray['battery']; ?></td>
                    </tr>
                    <tr>
                        <td>Number of diagnostic session/ <br> Number of workshop stops/ <br> Maintenance stops</td>
                        <td><?php echo $this->vehicleDataArray['number_of_diagnostic_session'] ?> <br>
                            <?php echo $this->vehicleDataArray['number_of_workshop_stops'] ?> <br>
                            TODO: maintenance stops
                        </td>
                    </tr>
                    <tr>
                        <td>Workshop visits</td>
                        <td>TODO: list of workshop visits with date and accumulated distance ("Gesamtfahrstrecke") and
                            repaired items (Input: "Teiletauschinterface" = part exchange interface - see above)
                        </td>
                    </tr>
                    <tr>
                        <td>Last diagnostic session</td>
                        <td><a>TODO: last diagnostic session (link to view window 1); workshop name</a></td>
                    </tr>
                    <tr>
                        <td>Diagnostic SW version</td>
                        <td><a><?php echo $this->vehicleDataArray['diagnostic_sw_version'] ?></a></td>
                    </tr>
                    <tr>
                        <td>Date of last diagnostic session</td>
                        <td><a><?php echo $this->vehicleDataArray['last_diagnostic_session_date'] ?></a></td>
                    </tr>
                    <tr>
                        <td>Valid deviation permission</td>
                        <td>TODO: valid deviation permission (see TB-2532 "input deviation permissions") at time point
                            of end of line and current state and history
                        </td>
                    </tr>
                </table>

                <h2>Part exchange history</h2>
                <p>TODO</p>
                <!--                <div class="quickform">-->
                <!--                    <form method="post" id="vehicle_fertig_status" action="index.php?action=search"-->
                <!--                          novalidate="novalidate">-->
                <!--                        <input type="hidden" name="todays_vehicles" id="todays_vehicles-0" value="">-->
                <!--                        <input type="hidden" name="to_set_vehicles" id="to_set_vehicles" value="">-->
                <!--                        <input type="hidden" id="qs_qm_action" name="action" value="saveQS">-->
                <!--                        <div style="overflow-y:auto; height: 450px; position:relative;" class="wrapper">-->
                <!--                            --><?php //echo $this->qs_vehicles->getContent(); ?>
                <!--                        </div>-->
                <!--                        <fieldset class="row">-->
                <!--                            <fieldset class="columns four inline_elements" id="qfauto-3966">-->
                <!---->
                <!--                            </fieldset>-->
                <!--                            <fieldset class="columns four inline_elements" id="qfauto-3967">-->
                <!--                            </fieldset>-->
                <!--                            <fieldset class="columns four inline_elements" id="qfauto-3968">-->
                <!--                                <div class="row">-->
                <!--                                    <div class="element">-->
                <!--                                        <input type="submit" value="Speichern" style="float: right; margin: 4px"-->
                <!--                                               name="">-->
                <!--                                    </div>-->
                <!--                                </div>-->
                <!--                            </fieldset>-->
                <!--                        </fieldset>-->
                <!--                    </form>-->
                <!--                </div>-->
            </div>
        </fieldset>
    </div>
</div>

<div id="all-series-numbers" title="All series numbers"></div>

<div id="qm-lock-state-dialog" title="QM lock state history"></div>

<div id="all-teo-sia-runs" title="All TEO and SIA runs"></div>

<div id="mechanical-errors-list" title="Status of mechanical errors"></div>

<div id="listOfManualTests" title="List of manual tests"></div>

<div id="listOfSemiautomaticTests" title="List of semiautomatic tests"></div>

<div id="dataNotAccordingToCANMatrix" title="List of data not according to CAN Matrix"></div>

<div id="vehicle-position" title="Vehicle position"></div>


<script>
    var selectedVin = '';
    var selectedVehicleId = '';
    var selectedTeoSesion = '';

    $(document).ready(function () {
        // if (localStorage.getItem('vehicleId')){
        //     selectedVehicleId = localStorage.getItem('vehicleId');
        //     showWindows();
        // }

        var urlParams1 = new URLSearchParams(window.location.search);
        selectedVehicleId = urlParams1.get('vehicleId');

        if (selectedVehicleId) {
            showWindows();
        }

        // console.log(selectedVehicleId);
        // console.log(getActionParamFromAddress1);
        // localStorage.clear();
    });


    $('#generate-raport').on('click', function () {
        // localStorage.clear();

        selectedVin = $('#sel-select-one-vehicle').find("option:selected").text();
        selectedVehicleId = $('#sel-select-one-vehicle').find("option:selected").val();


        localStorage.setItem("vin", selectedVin);
        localStorage.setItem("vehicleId", selectedVehicleId);

        console.log(selectedVin);
        // console.log(selectedVehicleId);

        // location.href += "&vehicleId=" + selectedVehicleId;
        location.href = "index.php?action=diagnosticReports&vehicleId=" + selectedVehicleId;
        //location.reload();
        // showWindows();
    });


    function ajaxQuery(context) {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetHistory',
                context: context
            },
            dataType: "json"
        })
            .done(function (historyValues) {

            });
    }


    $('#adminCategories').on('change', function () {

    });

    // Dialog windows

    $("#all-teo-sia-runs").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    $("#all-series-numbers").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    $("#qm-lock-state-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    $("#listOfManualTests").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    $("#listOfSemiautomaticTests").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    $("#mechanical-errors-list").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    $("#dataNotAccordingToCANMatrix").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    $("#vehicle-position").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
            "Close": function () {
                $(this).dialog("close");
            }
            // Cancel: function () {
            //     $(this).dialog("close");
            // }
        }
    });

    let parseBool = (charValue) => charValue === 't' ? "true" : "false";

    function typeOfDiagnostic($diagType, $diagSubtype) {

        switch ($diagType) {
            case 'DIAG':
                return 'SIA0';

            case 'EOLT':
                if ($diagSubtype.toLowerCase() == 'rerun') return 'TEO Rerun';
                else return 'TEO';

            case 'ASLS':
                return 'SIA1';
        }
    }


    function readAllTeoSiaRuns() {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetAllTeoSiaRuns',
                vehicleId: selectedVehicleId
            },
            dataType: "json"
        })
            .done(function (seriesNumberData) {
                let resultData = '';
                if (seriesNumberData != null) {
                    let seriesNumberHistoryTable = $('<table>').addClass('historyTable');
                    let headerRow = $('<tr>').addClass('historyTableRow');
                    headerRow.append($('<th>').text("Date"));
                    headerRow.append($('<th>').text("Tester"));
                    headerRow.append($('<th>').text("IP addres from tester data"));
                    headerRow.append($('<th>').text("IP addres from upload data"));
                    headerRow.append($('<th>').text("MAC address"));
                    headerRow.append($('<th>').text("Diagnostic run type"));
                    headerRow.append($('<th>').text("Place of diagnostic"));
                    headerRow.append($('<th>').text("Result"));
                    headerRow.append($('<th>').text("Select TEO run"));
                    seriesNumberHistoryTable.append(headerRow);

                    for (let i = 0; i < seriesNumberData.length; i++) {
                        // let row = $('<tr>').addClass('historyTableRow');
                        let row = $('<tr>');
                        row.append($('<td>').text(seriesNumberData[i]['date']));
                        row.append($('<td>').text("TODO"));
                        row.append($('<td>').text("TODO"));
                        row.append($('<td>').text(seriesNumberData[i]['ipaddress']));
                        row.append($('<td>').text(seriesNumberData[i]['macadress']));
                        row.append($('<td>').text(typeOfDiagnostic(seriesNumberData[i]['system_mode'], seriesNumberData[i]['ignitionkeynumber'])));
                        row.append($('<td>').text("TODO"));
                        row.append($('<td>').text(seriesNumberData[i]['status']));

                        let selectTeo = "index.php?action=diagnosticReports&selectedTeoSession=" + seriesNumberData[i]['diagnostic_session_id'] + "&vehicleId=" + selectedVehicleId;
                        let editBtn = $('<a>').attr({
                            href: selectTeo,
                            style: "margin-right: 5px;"
                        }).append($('<input>').attr({type: 'submit', value: 'Select'}));
                        row.append($('<td>').append(editBtn));
                        seriesNumberHistoryTable.append(row);
                    }
                    resultData = seriesNumberHistoryTable;
                } else {
                    resultData = $('<h1>').text('Data does not exist');
                }

                $('#all-teo-sia-runs').children().remove();
                $('#all-teo-sia-runs').html(resultData);
                $("#all-teo-sia-runs").dialog("open");
            });
    }


    function fillSelectSingleVehicle(selectResult) {
        let select = document.getElementById('sel-select-one-vehicle');
        $('#sel-select-one-vehicle').empty();

        let vehiclesList = selectResult['rows'];

        if (vehiclesList.length) {
            vehiclesList.forEach(function (row) {
                select.options[select.options.length] = new Option(row['vin'].substr(3, 17), row['vehicle_id']);
            });
            enableSelectVehicle();
        } else {
            disableSelectVehicle();
        }

    }

    function enableSelectVehicle() {
        $('#generate-raport').removeClass("disabled");
        // $('#sel-select-one-vehicle').disabled = false;
        document.getElementById('sel-select-one-vehicle').disabled = false;
    }

    function disableSelectVehicle() {
        $('#generate-raport').addClass("disabled");
        // $('#sel-select-one-vehicle').disabled = true;
        document.getElementById('sel-select-one-vehicle').disabled = true;
    }

    function showWindows() {
        document.getElementById('window-1').hidden = false;
        document.getElementById('window-2').hidden = false;
        $('#search_window_collapsible').hide();
    }


    function readStateOfSeriesNumbers() {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetAllSeriesNumbers',
                vehicleId: selectedVehicleId
            },
            dataType: "json"
        })
            .done(function (seriesNumberData) {
                let resultData = '';
                if (seriesNumberData != null) {
                    let seriesNumberHistoryTable = $('<table>').addClass('historyTable');
                    let headerRow = $('<tr>').addClass('historyTableRow');
                    headerRow.append($('<th>').text("PartName"));
                    headerRow.append($('<th>').text("Series Number"));
                    seriesNumberHistoryTable.append(headerRow);

                    for (let i = 0; i < seriesNumberData.length; i++) {
                        // let row = $('<tr>').addClass('historyTableRow');
                        let row = $('<tr>');
                        row.append($('<td>').text(seriesNumberData[i]['part_name']));
                        row.append($('<td>').text("TODO"));
                        seriesNumberHistoryTable.append(row);
                    }
                    resultData = seriesNumberHistoryTable;
                } else {
                    resultData = $('<h1>').text('Data does not exist');
                }

                $('#all-series-numbers').children().remove();
                $('#all-series-numbers').html(resultData);
                $("#all-series-numbers").dialog("open");
            });
    }

    function readQmLockStateHistory() {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetQMLockStateHistory',
                vehicleId: selectedVehicleId
            },
            dataType: "json"
        })
            .done(function (qmLockStateHistoryData) {

                let resultData = '';
                if (qmLockStateHistoryData != null) {
                    let historyTable = $('<table>').addClass('historyTable');
                    let headerRow = $('<tr>').addClass('historyTableRow');
                    headerRow.append($('<th>').text("Date"));
                    headerRow.append($('<th>').text("Lock state"));
                    headerRow.append($('<th>').text("User"));
                    headerRow.append($('<th>').text("Reason"));
                    historyTable.append(headerRow);

                    for (let i = 0; i < qmLockStateHistoryData.length; i++) {
                        // let row = $('<tr>').addClass('historyTableRow');
                        let row = $('<tr>');
                        row.append($('<td>').text(qmLockStateHistoryData[i]['update_ts']));
                        row.append($('<td>').text(parseBool(qmLockStateHistoryData[i]['old_status']) + ' to ' + parseBool(qmLockStateHistoryData[i]['new_status'])));
                        row.append($('<td>').text(qmLockStateHistoryData[i]['username']));
                        row.append($('<td>').text(qmLockStateHistoryData[i]['qmcomment']));
                        historyTable.append(row);
                    }
                    resultData = historyTable;
                } else {
                    resultData = $('<h1>').text('Data does not exist');
                }

                $('#qm-lock-state-dialog').children().remove();
                $('#qm-lock-state-dialog').html(resultData);
                $("#qm-lock-state-dialog").dialog("open");
            });
    }

    function statusOfMechanicalErrorsWindow() {

        // TODO: Implement this table
        let resultData = $('<h1>').text('TODO');

        $('#mechanical-errors-list').children().remove();
        $('#mechanical-errors-list').html(resultData);
        $("#mechanical-errors-list").dialog("open");
    }

    function vehiclePositionWindow() {

        // TODO: Implement this table
        let resultData = $('<h1>').text('TODO');

        $('#vehicle-position').children().remove();
        $('#vehicle-position').html(resultData);
        $("#vehicle-position").dialog("open");
    }


    // Button click actions

    $('#btnAllSeriesNumbers').on('click', function () {
        readStateOfSeriesNumbers();
    });

    $('#btnQMLockStateHistory').on('click', function () {
        readQmLockStateHistory();
        // $("#qm-lock-state-dialog").dialog("open");
    });

    $('#btnMechanicalErrors').on('click', function () {
        statusOfMechanicalErrorsWindow();
    });

    $('#btnVehiclePosition').on('click', function () {
        vehiclePositionWindow();
    });

    $('#btnAllTeoSiaRuns').on('click', function () {
        readAllTeoSiaRuns();
    });

</script>

<style>
    .historyTable {;
    }

    .historyTableRow {;
    }

    .assigned-structure {
        color: #8e4125;
    }

    .success-message {
        padding: 20px;
        background-color: #34a34a;
        color: white;
    }

    .options-bar {
        text-align: center;
    }

    .general_search_tab_container {
        margin-bottom: 10px;
    }

    .submenu_ul li {
        padding: 10px;
    }
</style>