<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 2/14/19
 * Time: 10:19 AM
 */

include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>

<div class="inner_container"></div>
<?php if (is_array($this->msgs)) { ?>
    <div class="row ">
    <div class="columns six">
        <?php echo implode('<br>', $this->msgs); ?>
    </div>
    </div><?php }; ?>

<div class="row">

    <?php require_once($_SERVER['STS_ROOT'] . '/pages/common/ecusw-selection.php'); ?>

    <div class="columns ten">
        <div class="row">
            <h1 id="ecu_sw_configuration_header">ECU software parameter management</h1>
        </div>
        <div id="infobox-ecu" class="row"></div>
        <div id="ecu_sw_configuration_content" class="row"></div>

        <div class="row">
            <?php require_once($_SERVER['STS_ROOT'] . '/pages/common/ecusw-actions.php');

            if (isset($_REQUEST['ecuVersion']) && $_REQUEST['ecuVersion']['selectedEcu']) {
                $this->EcuSwTableController->WriteHtmlContent("nomenu");
            } else {
                $settings = $this->ladeLeitWartePtr->newQuery('user_settings')->where('sts_userid', '=', $_SESSION['sts_userid'])->getVal('settings');
                if ($settings) {
                    $settings = unserialize($settings);
                    if (isset($settings['sw_version'])) {
                        header("Location:" . $_SERVER['REQUEST_URI'] . "&ecuVersion[selectedEcu]=" . $settings['sw_version']['ecuVersion'] . "&ecuVersion[ecu][" . $settings['sw_version']['ecuVersion'] . "]=" . $settings['sw_version']['selectedEcu'] . "&tab=2&command");
                    }
                }
                echo "<script> $(function() {resetSwConfigurationContent(true); }); </script>";
            }
            ?>

        </div>
    </div>

    <div id="copy-parameters-dialog" title="Copy parameters into other SW">
        <p>Select SW:</p>
        <p>
        <form action='' method='post' id="check-sw"></form>
        </p>
        <p style="font-weight: bold; color: red">Warning</p>
        <p>Changes in the selected parameters will not be saved or copied.</p>
        <p style="font-weight: bold">Info</p>
        <p>You can select only SW with the same protocol.</p>
    </div>

    <div id="copy-parameters-error-dialog" title="Copy parameters into other SW">
        SW list is empty.
    </div>

    <div id="copy-parameters-conflicts-dialog" title="Overwrite parameters">
        <p style="font-weight: bold">SW: <span id="sw"></span></p>
        <p>Conflicts detected. Overwrite parameters:</p>
        <p>
        <form action='' method='post' id="overwrite-parameters"></form>
        </p>
    </div>

    <div id="copy-parameters-success-dialog" title="Success">
        <p>All parameters have been copied</p>
    </div>

</div>


<script>
    var iterator = 1;
    var rows = $("#idEcuRechts .ecuParamDefinesForOdx02  tbody tr").children("td").children(".colinfo").children("input").length;

    function copySelectedParameters() {
        $("#idEcuRechts .ecuParamDefinesForOdx02  tbody tr").each(function (event) {
            if ($(this).children("td").children(".colinfo").children("input").is(':checked')) {
                let clone = $(this).clone();
                let sw = clone.children("td").children(".colinfo").children("input").attr("name").match(/[0-9][0-9]*/);
                let id = '';
                let udsId = '';
                clone.children("td").children(".colinfo").children("input").remove();
                clone.find('*').each(function (event) {
                    if ($(this).attr('name') !== undefined) {
                        if ($(this).attr('name') == 'ecu[' + sw[0] + '][id]')
                            id = $(this).val();
                        if ($(this).attr('name') == 'ecu[' + sw[0] + '][udsId]')
                            udsId = $(this).val();
                        let name = $(this).attr('name').replace(/[0-9][0-9]*/, '99add' + iterator);
                        $(this).attr('name', name);
                    }
                })
                ++rows;
                let element = clone.children("td").children(".colinfo");
                element.append("<input type='hidden' name='ecu[99add" + iterator + "][previous_id]' value='" + id + "'>");
                element.append("<input type='hidden' name='ecu[99add" + iterator + "][previous_udsId]' value='" + udsId + "'>");
                element.append("<input type='hidden' name='ecu[99add" + iterator + "][previous]' value='" + sw[0] + "'>");
                element.append("<input type='hidden' name='ecu[99add" + iterator + "][order]' value='" + (rows - 1) + "'>");
                $(this).parent().find('tr:last').after(clone);
                ++iterator;
            }
        });
        if ((iterator - 1) > 0) {
            $("#idEcuRechts ").append("<input type='hidden' name='addnum' value='" + (iterator - 1) + "'>");
        }
    }

    var copyArray = [];

    function copySelectedParametersIntoOtherSW() {
        copyArray = [];
        $("#idEcuRechts .ecuParamDefinesForOdx02  tbody tr").each(function (event) {
            if ($(this).children("td").children(".colinfo").children("input").is(':checked')) {
                let p_set_id = $(this).children("td").children(".colinfo").children("input").attr("name").match(/[0-9][0-9]*/)[0];
                copyArray.push(p_set_id);
            }
        });

        if (!(copyArray.length > 0))
            return;

        $.ajax({
            method: 'POST',
            data: {
                ecu: $('#list_of_ecus').val(),
                sw: $('#sw_versions_list').val()
            },
            url: "index.php?action=ecuParMan&call=ajaxGetOtherSW",
            success: function (result) {
                let swVersions = JSON.parse(result);
                if (swVersions[0] != "empty") {
                    $("#check-sw").empty();
                    let text = '';
                    // sort alphabetically
                    swVersions.sort(function (a, b) {
                        return a['sts_version'].localeCompare(b['sts_version']);
                    });

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

                    let disable_current_revision = '';
                    let disable_current_revision_subversion = '';
                    // print all sw versions (major, -->minor)
                    for (let i = 0; i < majors.length; i++) {
                        let sw = majors[i];
                        disable_current_revision = (sw['ecu_revision_id'] == $('#sw_versions_list').val() ? 'disabled' : '');
                        text += "<input type='checkbox' name='ecu_revision_id[]' value='" + sw['ecu_revision_id'] + "' style ='display: inline' sts_version='" + sw['sts_version'] + "' subversion_suffix='" + sw['subversion_suffix'] + "' " + sw['disabled'] + " " + disable_current_revision + ">" + sw['sts_version'] + "<br />";

                        for (let j = 0; j < sw.subversions.length; j++) {
                            disable_current_revision_subversion = (sw.subversions[j]['ecu_revision_id'] == $('#sw_versions_list').val() ? 'disabled' : '');
                            text += "&nbsp&nbsp&nbsp&nbsp <input type='checkbox' name='ecu_revision_id[]' value='" + sw.subversions[j]['ecu_revision_id'] + "' style ='display: inline' sts_version='" + sw.subversions[j]['sts_version'] + "' subversion_suffix='" + sw.subversions[j]['subversion_suffix'] + "' " + sw.subversions[j]['disabled'] + " " + disable_current_revision_subversion + ">" + sw.subversions[j]['subversion_suffix'] + "<br />";
                        }
                    }

                    $("#check-sw").append(text);
                    copyDialog.dialog("open");
                } else {
                    copyErrorDialog.dialog("open");
                }
            }
        });
    }

    var sw_iterator = 0;
    var swArray = [];

    function recursiveCopying(iterator) {
        $.ajax({
            method: 'POST',
            data: {
                ecu: $('#list_of_ecus').val(),
                sw_current: $('#sw_versions_list').val(),
                sw_destination: swArray[iterator].ecu_revision_id,
                ecu_parameter_sets: JSON.stringify(copyArray)
            },
            url: "index.php?action=ecuParMan&call=ajaxCheckConflicts",
            success: function (result) {
                data = JSON.parse(result);
                if (data[0] == 'empty') {
                    if (iterator - 1 < 0) {
                        copySuccessDialog.dialog("open");
                    } else {
                        recursiveCopying(--sw_iterator);
                    }
                } else {
                    let text = '';
                    for (let i = 0; i < data.length; ++i)
                        text += "<input type='checkbox' name='ecu_parameter_set_id[]' destination='" + data[i]['ecu_parameter_set_id']
                            + "' current='" + data[i]['ecu_parameter_set_id_current'] + "' style ='display: inline'>"
                            + data[i]['id'] + "<br />";
                    $("#sw").empty();
                    $("#overwrite-parameters").empty();
                    $("#sw").append(swArray[iterator].sts_version + ((swArray[iterator].subversion_suffix != '') ? (' ---> ' + swArray[iterator].subversion_suffix) : ''));
                    $("#overwrite-parameters").append(text);
                    copyConflictsDialog.dialog('open');
                }
            }
        });
    }

    var copyDialog = $("#copy-parameters-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Copy parameters": function () {
                swArray = [];
                sw_iterator = 0;
                $('#check-sw input:checked').each(function () {
                    swArray.unshift({
                        ecu_revision_id: $(this).val(),
                        sts_version: $(this).attr('sts_version'),
                        subversion_suffix: $(this).attr('subversion_suffix')
                    });
                    ++sw_iterator;
                })
                if (sw_iterator != 0)
                    recursiveCopying(--sw_iterator);
                copyDialog.dialog("close");
            },
            "Cancel": function () {
                copyDialog.dialog("close");
            }
        }
    });

    var copyErrorDialog = $("#copy-parameters-error-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Ok": function () {
                copyErrorDialog.dialog("close");
            }
        }
    });

    var copyConflictsDialog = $("#copy-parameters-conflicts-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Ok": function () {
                let conflictArray = [];
                let withoutConflictArray = copyArray;
                let conflictToRemoveArray = [];
                $('#overwrite-parameters input').each(function () {
                    if ($(this).is(':checked'))
                        conflictArray.push({
                            current: $(this).attr('current'),
                            destination: $(this).attr('destination')
                        });
                    conflictToRemoveArray.push($(this).attr('current'));
                });

                withoutConflictArray = withoutConflictArray.filter(function (el) {
                    return !conflictToRemoveArray.includes(el);
                });

                $.ajax({
                    method: 'POST',
                    data: {
                        ecu: $('#list_of_ecus').val(),
                        sw_current: $('#sw_versions_list').val(),
                        sw_destination: swArray[sw_iterator].ecu_revision_id,
                        sets_conflict: JSON.stringify(conflictArray),
                        sets_without_conflict: JSON.stringify(withoutConflictArray)
                    },
                    url: "index.php?action=ecuParMan&call=ajaxResolveConflicts",
                    success: function (result) {
                        copyConflictsDialog.dialog("close");
                        if (sw_iterator - 1 < 0) {
                            copySuccessDialog.dialog("open");
                        } else {
                            recursiveCopying(--sw_iterator);
                        }
                    }
                });
            }
        }
    });

    var copySuccessDialog = $("#copy-parameters-success-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Ok": function () {
                copySuccessDialog.dialog("close");
                location.reload();
            }
        }
    });


    $(document).ready(function () {
        /* Parameterlist table */


        function hideXCPHeader(protocol) {
            if (protocol == 2) {
                $('.uds').css('display', 'none');
                $('.uds').children().prop('disabled', true);

                $('.request').parent().css('background-color', '#ccc');
                $('.response').parent().css('background-color', '#ccc');
                $('.request').css('visibility', 'hidden');
                $('.response').css('visibility', 'hidden');
            } else {
                $('.uds').css('display', '');
                $('.uds').children().prop('disabled', false);

                $('.request').parent().css('background-color', '');
                $('.response').parent().css('background-color', '');
                $('.request').css('visibility', '');
                $('.response').css('visibility', '');
            }
        }

        function hideXCPUDSProtocol(protocol) {
            if (protocol == 3) {
                $('.protocol').css('display', '');
            } else {
                $('.protocol').css('display', 'none');
            }
        }

        function resetValuesBeforeSave() {
            $('.request[style*="visibility: hidden"]').children().val('0');
            $('.response[style*="visibility: hidden"]').children().val('0');
            $('.uds[style*="display: none"]').children().val('0');
            $('.protocol[style*="display: none"]').children().val('');
            $('.value[style*="display:none"]').prop('disabled', true);
            $('.macroname[style*="display:none"]').prop('disabled', true);
            $('.dyn_token[style*="display:none"]').prop('disabled', true);

            $('.value[style*="display: none"]').prop('disabled', true);
            $('.macroname[style*="display: none"]').prop('disabled', true);
            $('.dyn_token[style*="display: none"]').prop('disabled', true);
            $('.protocol').children('select[style*="display: none"]').prop('disabled', true);
        }

        function resetDisabled() {
            $('.value[style*="display:none"]').prop('disabled', false);
            $('.macroname[style*="display:none"]').prop('disabled', false);
            $('.dyn_token[style*="display:none"]').prop('disabled', false);

            $('.value[style*="display: none"]').prop('disabled', false);
            $('.macroname[style*="display: none"]').prop('disabled', false);
            $('.dyn_token[style*="display: none"]').prop('disabled', false);
        }

        function disableLinking(number) {
            $('select[name="ecu[' + number + '][valuetype]"]').prop('disabled', true);
            $('input[name="ecu[' + number + '][value]"]').prop('disabled', true);
            $('select[name="ecu[' + number + '][macroname]"]').prop('disabled', true);
            $('select[name="ecu[' + number + '][dyn_token]"]').prop('disabled', true);
        }

        function enableLinking(number) {
            $('select[name="ecu[' + number + '][valuetype]"]').prop('disabled', false);
            $('input[name="ecu[' + number + '][value]"]').prop('disabled', false);
            $('select[name="ecu[' + number + '][macroname]"]').prop('disabled', false);
            $('select[name="ecu[' + number + '][dyn_token]"]').prop('disabled', false);
        }

        hideXCPHeader($('td[protocol]').attr('protocol'));
        hideXCPUDSProtocol($('td[protocol]').attr('protocol'));

        $(document).on("change", "select[name='ecuVersion[protocol]']", function () {
            hideXCPHeader($(this).val());
            hideXCPUDSProtocol($(this).val());
        });

        $(document).on("click", "a[href='javascript:AddNewRows()']", function (e) {
            e.preventDefault();
            $("#idr_form").append("<input type='hidden' name='ecuVersion[selectedEcu]' value='" + $('select[name=\'ecuVersion[selectedEcu]\']').val() + "'>");
            window.location.href = 'javascript:AddNewRows()';
        });

        $(document).on("click", 'a[href="javascript:SaveParameters(\'save\')"]', function (e) {
            e.preventDefault();
            $("#idr_form").append("<input type='hidden' name='ecuVersion[selectedEcu]' value='" + $('select[name=\'ecuVersion[selectedEcu]\']').val() + "'>");
            $('.parameter-error').removeClass('parameter-error');
            resetValuesBeforeSave();

            $.ajax({
                method: 'POST',
                data: $('#idr_form').serialize(),
                url: "index.php?action=ecuParMan&call=ajaxValidateForm",
                success: function (result) {
                    data = JSON.parse(result);
                    if (data[0] != "empty") {
                        for (let i = 0; i < data.length; ++i) {
                            $('[name="' + data[i] + '"]').addClass("parameter-error");
                        }
                        resetDisabled();
                    } else {
                        window.location.href = "javascript:SaveParameters('save')";
                    }
                }
            });
        });

        $(document).on("click", '.move_up', function (e) {
            e.preventDefault();
            $("#idr_form").append("<input type='hidden' name='ecuVersion[selectedEcu]' value='" + $('select[name=\'ecuVersion[selectedEcu]\']').val() + "'>");
            window.location.href = $(this).attr("href");
        });

        $(document).on("click", '.move_down', function (e) {
            e.preventDefault();
            $("#idr_form").append("<input type='hidden' name='ecuVersion[selectedEcu]' value='" + $('select[name=\'ecuVersion[selectedEcu]\']').val() + "'>");
            window.location.href = $(this).attr("href");
        });

        $(document).on("change", '.rights', function (e) {
            let number = $(this).attr('name').match(/[0-9][0-9]*/);
            let name = 'ecu[' + number + '][action]';
            if ($('input[name="' + name + '[r]"').is(":checked") && !$('input[name="' + name + '[w]"').is(":checked") && !$('input[name="' + name + '[c]"').is(":checked")) {
                disableLinking(number);
            } else {
                enableLinking(number);
            }
        });
        /* ------------------ */
    });
</script>

<style>
    #ecu_sw_configuration_content {
        /*margin: 1.0em 0.5em 10.0em 0.5em;*/
        /*min-height: 610px;*/
    }

    #idEcuRechts input {
        display: inline-block;
    }

    /* Parameterlist table */
    .stdborder {
        border: 1px solid #b0b0b0;
        margin-bottom: 10px;
        margin-top: 10px;
    }

    #idEcuRechts > div {
        width: 100%;
    }

    .parameter-error {
        outline: 1px solid red;
    }

    /* ------------------- */

</style>