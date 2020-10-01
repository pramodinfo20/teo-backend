<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 2/15/19
 * Time: 9:59 AM
 */
?>

<div class="columns two">
    <h1>Device / ECU</h1>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="ecus_swversions_form" method="POST">
        <select id="list_of_ecus" name="ecuVersion[selectedEcu]" style="width: 100%;" size="14">
            <option value="0">-- Select ECU --</option>
            <?php foreach ($this->availableECUs as $row) {
                if ($row['permission'] == 'responsible') {
                    $style = 'is_resp';
                } elseif ($row['permission'] == 'deputy') {
                    $style = 'is_deputy';
                } elseif ($row['permission'] == 'writable') {
                    $style = 'is_writable';
                } else {
                    $style = '';
                }
                ?>
                <option value="<?php echo $row['ecu_id']; ?>" class="<?php echo $style; ?>">
                    <?php if ($style != '') {
                        echo '&#9873;&nbsp;';
                    } else {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                    } ?><?php echo $row['name']; ?></option>
            <?php } ?></select>
        </select>
        <h1 id="sw_versions_header">SW versions</h1>
        <select id="sw_versions_list" name="" size="20" style="width: 100%;">
            <option value="1">text1</option>
            <option value="2">text2</option>
        </select>
    </form>
</div>

<script>
    let selectedEcuId;
    let selectedEcuName;
    let selectedSwVersion;

    //
    // To tell user to select ECU and show him a legend
    //
    function resetSwConfigurationContent(ecuSelected) {
        $("#ecu_sw_configuration_content > .row").not(':first').remove();

        if (!ecuSelected) {
            $('#ecu_sw_configuration_content').html("<p>No ECU selected</p>");
            $('#ecu_sw_configuration_content').append("<h4>Choose ECU and software version</h4>");
        } else {
            $('#ecu_sw_configuration_content').html("<p>No software version is selected</p>");
            $('#ecu_sw_configuration_content').append("<h4>Choose software version...</h4>");
        }

        $('#ecu_sw_configuration_content').append("<p>Legend:</p>");
        $('#ecu_sw_configuration_content').append(
            $('<ul>')
                .append($('<li>').append("Red - you are reponsible person").addClass('is_resp'))
                .append($('<li>').append("Green - you are deputy").addClass('is_deputy'))
                .append($('<li>').append("Blue - you are writable person").addClass('is_writable'))
        );

        if (ecuSelected) {
            $('#ecu_sw_configuration_content').append("<h4>...or create new</h4>");
            let buttonDisabled = ""; // set to disabled if you want do disable the button
            $('#ecu_sw_configuration_content').append(
                "<div class=\"MiniButtons\"><ul class=\"submenu_ul\">" +
                "<li><span class=\"sts_submenu W180 " + buttonDisabled + " + \" id=\"create_new_sw_button2\">New " + selectedEcuName + " revision</span></li>" +
                "</ul></div>");
            $("#create_new_sw_button2").on('click', function () {
                createDialog.dialog("open");
            });
        }
    }

    //
    // After login, the ECU where the user is responsible or deputy or where the user has write permissions
    // should automatically be selected. If the user is responsible or deputy or writeable for more than one
    // ECU use this order for auto selection: resp->dep->writable->alphabetical
    //
    function selectEcuIfResponsible() {

        let makeSelection = null;

        // check if responsible for something
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: 'ecuSwConf',
                method: 'ajaxGetFirstResponsibility'
            },
            dataType: "json"
        })
            .done(function (item) {
                // only if id is not 0
                if (item > 0) {
                    $('#list_of_ecus option[value=' + item + ']').prop('selected', 'selected').change();
                }
            });
    }

    /*
        function showSwVersionHeader(revision_id) {
    
            $.ajax({
                method: "GET",
                url: "index.php",
                data: {
                    action: 'ecuSwConf',
                    method: 'ajaxGetSwHeader',
                    software: revision_id
                },
                dataType: "json"
            })
                .done(function(headerValues) {
    
                    let swHeader = $('<table>').addClass('');
    
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("sts_version: " + headerValues['sts_version']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("href_windchill: " + headerValues['href_windchill']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("version_info: " + headerValues['version_info']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("released: " + headerValues['released']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("sw_profile_ok: " + headerValues['sw_profile_ok']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("use_uds: " + headerValues['use_uds']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("use_xcp: " + headerValues['use_xcp']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("request_id: " + headerValues['request_id']))
                    );
                    swHeader.append($('<tr>').addClass('')
                        .append($('<td>').text("response_id: " + headerValues['response_id']))
                    );
    
                    $('#ecu_sw_configuration_content').children().remove();
                    $('#ecu_sw_configuration_content').html(swHeader);
                });
        }
    */

    //
    // User selects ECU
    //
    var changedSelectedEcu = false;
    $('#list_of_ecus').on('change', function (event) {

        selectedEcuId = $(this).find("option:selected").val();
        selectedEcuName = $(this).find("option:selected").text().replace(/⚑/g, "").trim();

        if (selectedEcuId == 0) {
            $('#sw_versions_header').hide(0);
            $('#sw_versions_list').hide(0);
            $('#sw_config_actions').hide(0);
            $('#ecu_sw_configuration_header').text("ECU software parameter management");
            resetSwConfigurationContent(false);
            $("#idEcuRechts").remove();
            return;
        }

        var requestVersion = <?php echo isset($_REQUEST['ecuVersion']['ecu'][$_REQUEST['ecuVersion']['selectedEcu']]) ? $_REQUEST['ecuVersion']['ecu'][$_REQUEST['ecuVersion']['selectedEcu']] : 'null'; ?>;
        var requestEcu = <?php echo isset($_REQUEST['ecuVersion']['selectedEcu']) ? $_REQUEST['ecuVersion']['selectedEcu'] : 'null'; ?>;

        if (requestEcu != selectedEcuId) {
            changedSelectedEcu = true;
        }

        $('#infobox-ecu').children().remove();

        // get all ecu sw versions
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: 'ecuSwConf',
                method: 'ajaxGetSwVersions',
                ecu: selectedEcuId
            },
            dataType: "json"
        })
            .done(function (swVersions) {
                if (swVersions != null) {
                    // clear previous and add options
                    $('#sw_versions_list').children().remove();

                    // sort alphabetically
                    swVersions.sort(function (a, b) {
                        return a['sts_version'].localeCompare(b['sts_version']);
                    });

                    $('#sw_config_actions').hide('fast');
                    var makeReset = false;

                    // different normal SW versions (majors) from subversions (minors)
                    let majors = swVersions.filter(function (e) {
                        return e['subversion_major'] == null;
                    });
                    let minors = swVersions.filter(function (e) {
                        return e['subversion_major'] != null;
                    });

                    // assign empty subversions array for each SW version
                    for (let i = 0; i < majors.length; i++) {
                        let subversions = new Array();
                        majors[i]['subversions'] = subversions;
                    }

                    // attach all minor versions to its majors
                    for (let i = 0; i < minors.length; i++) {
                        let major = minors[i]['subversion_major'];
                        let parent = majors.find(function (element) {
                            if (element['ecu_revision_id'] == major) return element;
                        });
                        if (typeof parent !== 'undefined') {
                            parent.subversions.push(minors[i]);
                        } else {
                            console.warn("Cannot find parent sw version for sw id: " + major);
                        }
                    }

                    // print all sw versions (major, -->minor)
                    for (let i = 0; i < majors.length; i++) {
                        let sw = majors[i];

                        if (changedSelectedEcu == false && requestEcu != null && requestVersion != null && requestVersion == sw['ecu_revision_id'] && requestEcu == selectedEcuId) {
                            $('#sw_versions_list').append($('<option>', {
                                value: sw['ecu_revision_id'],
                                text: sw['sts_version'],
                                selected: requestVersion
                            }));
                            reset(sw['ecu_revision_id']);
                            makeReset = true;
                        } else {
                            $('#sw_versions_list').append($('<option>', {
                                value: sw['ecu_revision_id'],
                                text: sw['sts_version']
                            }));
                        }

                        for (let j = 0; j < sw.subversions.length; j++) {
                            if (changedSelectedEcu == false && requestEcu != null && requestVersion != null && requestVersion == sw.subversions[j]['ecu_revision_id'] && requestEcu == selectedEcuId) {
                                $('#sw_versions_list').append($('<option>', {
                                    value: sw.subversions[j]['ecu_revision_id'],
                                    text: "---> " + sw.subversions[j]['subversion_suffix'],
                                    selected: requestVersion
                                }));
                                reset(sw.subversions[j]['ecu_revision_id']);
                                makeReset = true;
                            } else {
                                $('#sw_versions_list').append($('<option>', {
                                    value: sw.subversions[j]['ecu_revision_id'],
                                    text: "---> " + sw.subversions[j]['subversion_suffix']
                                }));
                            }
                        }
                    }

                    if (!(makeReset)) {
                        resetSwConfigurationContent(true);
                        $("#idEcuRechts").remove();
                    }

                    $('#sw_versions_header').show('fast');
                    $('#sw_versions_list').show('fast');

                    $("#sw_versions_list").attr("name", "ecuVersion[ecu][" + selectedEcuId + "]");

                } else {
                    // no sw versions for this ecu
                    $('#sw_versions_list').children().remove();
                    $('#sw_versions_list').hide('fast');
                    $('#sw_versions_header').hide('fast');
                    $('#sw_config_actions').hide('fast');
                }
            });
    });

    function reset(swVersion) {
        $('#infobox-ecu').children().remove();

        selectedSwVersion = swVersion;
        $('#sw_config_actions').show('fast');
        $('#ecu_sw_configuration_content').children().remove();
        //showSwVersionHeader(selectedSwVersion);
        //showSwVersionDetails(selectedSwVersion);


        //
        // Check if not a subversion is selected -> enable creating subversions
        //
        if (typeof selectedSwVersion !== 'undefined') {
            $.ajax({
                method: "GET",
                url: "index.php",
                data: {
                    action: 'ecuSwConf',
                    method: 'ajaxGetSwHeader',
                    software: selectedSwVersion
                },
                dataType: "json"
            })
                .done(function (res) {
                    // unblock creating SUBVERSIONS only for major sw versions
                    if (!res.subversion_major) {
                        $("#create_subversion_button").removeClass('disabled');
                        $("#create_subversion_button").on('click', function () {
                            createSubversionDialog.dialog("open");
                        });
                    }

                    // unblock creating COPIES only for major sw versions (TB-1874)
                    if (!res.subversion_major) {
                        $("#copy_sw_button").removeClass('disabled');
                        $("#copy_sw_button").on('click', function () {
                            copySWDialog.dialog("open");
                        });
                    }
                });
        }
    }

    //
    // User selects sw version
    //
    $('#sw_versions_list').on('change', function () {
        $("#ecus_swversions_form").append("<input type='hidden' name='tab' value='2'>");
        $("#ecus_swversions_form").append("<input type='hidden' name='command'>");
        $.ajax({
            method: 'POST',
            data: {
                ecuVersion: $('#list_of_ecus').val(),
                selectedEcu: $('#sw_versions_list').val()
            },
            url: "index.php?action=ecuParMan&method=saveUserSettings",
            success: function (result) {
                $("#ecus_swversions_form").submit();
            }
        });
    });


    // ----------------------------------------------------------------------------------
    //
    // Init actions
    //
    // ----------------------------------------------------------------------------------
    $('#sw_versions_header').hide(0); // cannot be inside on document ready
    $('#sw_versions_list').hide(0);  // because of blinking effect

    $(function () {
        //resetSwConfigurationContent(false);
    });

</script>

<?php

if (!(isset($_REQUEST["ecuVersion"]["selectedEcu"]))) {
    echo "<script> $(function () { selectEcuIfResponsible(); }); </script>";
} else {
    echo "<script> $(function () { $('#list_of_ecus option[value=\'" . $_REQUEST['ecuVersion']['selectedEcu'] . "\']').prop('selected', 'selected').change(); }); </script>";
}
?>

<style>

    #ecus_swversions_form {
        font-size: 90%;
    }

    .ecu_assigned {
        color: #880000;
    }

    .is_resp {
        color: #880000;
    }

    .is_deputy {
        color: #226622;
    }

    .is_writable {
        color: #222288;
    }

</style>