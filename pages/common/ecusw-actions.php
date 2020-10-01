<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 2/15/19
 * Time: 10:04 AM
 */

// to be set in controller for locking sw indicator
$this->isSwLocked = parent::isSoftwareLocked();
$this->isSwCreateAvailable = false;
$this->isSwSubversionCreateAvailable = false;
$this->isSwRemoveAvailable = false;
$this->isSwCopyAvailable = false;
$this->isListExportAvailable = false;
$this->isHistoryAvailable = false;
$this->isRvSAvailable = false;
?>


<div id="sw_config_actions" class="variantPartsButtons">
    <div class="MiniButtons">
        <ul class="submenu_ul">
            <li><span class="sts_submenu W130 <?php echo ($this->isSwCreateAvailable !== true) ? 'disabled' : ''; ?>"
                      id="create_new_sw_button">Create new SW version</span></li>
            <li>
                <span class="sts_submenu W130 <?php echo ($this->isSwSubversionCreateAvailable !== true) ? 'disabled' : ''; ?>"
                      id="create_subversion_button">Create subversion</span></li>
            <li><span class="sts_submenu W130 <?php echo ($this->isSwRemoveAvailable !== true) ? 'disabled' : ''; ?>"
                      id="delete_new_sw_button">Delete SW version</span></li>
            <li><span class="sts_submenu W130 <?php echo ($this->isSwCopyAvailable !== true) ? 'disabled' : ''; ?>"
                      id="copy_sw_button">Copy SW version</span></li>
            <li>
                <span class="sts_submenu W080 <?php if ($this->isSwLocked === false) { ?>locked-no">Not locked<?php } else { ?>locked-yes">Locked<?php } ?></span>
            </li>
            <li><span class="sts_submenu W130 <?php echo ($this->isListExportAvailable !== true) ? 'disabled' : ''; ?>">List export</span>
            </li>
            <li><span class="sts_submenu W130 <?php echo ($this->isHistoryAvailable !== true) ? 'disabled' : ''; ?>">History</span>
            </li>
            <li><span class="sts_submenu W130 <?php echo ($this->isRvSAvailable !== true) ? 'disabled' : ''; ?>">Release via signature</span>
            </li>
        </ul>
    </div>
</div>
<div id="add-revision-dialog" title="Add new software version">
    <p>A valid StS part number has to be given for the SW version. Allowed pattern:</p>
    <ul>
        <li>ABYXXXXXX_XX</li>
        <li>ABYXXXXXXXX_XX</li>
        <li>ABYXXXXXX_XX_XX</li>
    </ul>
    <p>A=B, D, E, F<br/>B=14,16,17,18, ....<br/>Y=character (capital letter)<br/>X=number (1 digit)</p>
    <p><span id="remove-dialog-text"></span></p>
    <p class="validateTips">All form fields are required.</p>
    <label for="newSwName">Name</label>
    <input type="text" name="newSwName" id="revisionName" value="" class="text ui-widget-content ui-corner-all">
</div>
<div id="copy-revision-dialog" title="Copy software version">
    <p>A valid StS part number has to be given for the SW version. Allowed pattern:</p>
    <ul>
        <li>ABYXXXXXX_XX</li>
        <li>ABYXXXXXXXX_XX</li>
        <li>ABYXXXXXX_XX_XX</li>
    </ul>
    <p>A=B, D, E, F<br/>B=14,16,17,18, ....<br/>Y=character (capital letter)<br/>X=number (1 digit)</p>
    <p><span id="remove-dialog-text"></span></p>
    <p class="validateTips">All form fields are required.</p>
    <label for="newSwName">Name</label>
    <input type="text" name="newSwName" id="copyRevisionName" value="" class="text ui-widget-content ui-corner-all">
</div>
<div id="add-subversion-dialog" title="Add subversion">
    <p>A valid suffix has to be given </p>
    <p><span id="remove-dialog-text"></span></p>
    <p class="validateTips">All form fields are required.</p>
    <label for="subName">Name</label>
    <input type="text" name="subName" id="subversionSuffixText" value="" class="text ui-widget-content ui-corner-all">
    <p>Copy from main version</p>
    <div style="float: left; clear: none;">
        <input style="float: left; " type="radio" name="copyFromMainVersion" value="yes" checked="checked"/>
        <label style="display: block; padding: 2px 1em 0 0;">Everything</label>
        <input style="float: left; " type="radio" name="copyFromMainVersion" value="no"/>
        <label style="float: left; display: block; padding: 2px 1em 0 0;">Nothing (but StS Partnumber)</label>
    </div>
</div>
<div id="delete-revision-dialog" title="Delete SW version">
    <div id="assigned-configurations-list"></div>
</div>
<script>

    //
    // Dialog-related validating functions
    //
    function updateTips(t) {
        let tips = $(".validateTips");
        tips.text(t).addClass("ui-state-highlight");
        setTimeout(function () {
            tips.removeClass("ui-state-highlight", 5000);
        }, 500);
    }

    function checkLength(name, len) {
        if (name.length < len) {
            updateTips("This name is too short");
            return false;
        } else {
            return true;
        }
    }

    function checkIfExists(name) {
        if (getThisEcuSwNames().includes(name)) {
            updateTips("This name already exists for this ECU");
            return false;
        } else {
            return true;
        }
    }

    function checkIfSubversionExists(name) {

        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: 'ecuSwConf',
                method: 'ajaxGetSubversionsFor',
                parent: selectedSwVersion
            },
            dataType: "json",
            async: false
        })
            .done(function (res) {
                if (res.map(a => a.sts_version).includes(name)) {
                    updateTips("This subversion suffix already exists for this SW version");
                    return false;
                } else {
                    return true;
                }
            });
    }

    function checkPattern(name) {
        let regexp = /^[ABCDEF]{1}[1-3]{1}[0-9]{1}[A-Z]{1}([0-9]*)([_]{1}[0-9]{1,8})*$/;
        if (!(regexp.test(name))) {
            updateTips("This name does not match a pattern");
            return false;
        } else {
            return true;
        }
    }

    function getThisEcuSwNames() {
        let list = $("#sw_versions_list").children();
        let names = new Array();
        for (let i = 0; i < list.length; i++) {
            names.push(list[i].text);
        }
        return names;
    }

    //
    // Prepare dialog for Creating new revision
    //
    var createDialog = $("#add-revision-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Create new revision": function () {
                let valid = true;
                let revisionName = $('#revisionName').val().trim();
                valid = valid && checkLength(revisionName, 5);
                valid = valid && checkIfExists(revisionName);
                valid = valid && checkPattern(revisionName);
                let dialog = $(this);
                if (valid) {
                    $.ajax({
                        method: 'POST',
                        data: {
                            sts_version: revisionName,
                        },
                        url: "index.php?action=ecuParMan&method=ajaxCheckIfExistsInDB",
                        dataType: "text",
                        success: function (result) {
                            if (result == 1) {
                                updateTips("This name already exists");
                            } else {
                                dialog.dialog("close");
                                $('#revisionName').val = "";
                                // add new sw version
                                $.ajax({
                                    method: "GET",
                                    url: "index.php",
                                    data: {
                                        action: 'ecuSwConf',
                                        method: 'ajaxAddNewSwRevision',
                                        selectedName: revisionName,
                                        selectedEcu: selectedEcuId
                                    },
                                    dataType: "json"
                                })
                                    .done(function (res) {
                                        $('#list_of_ecus option[value=' + selectedEcuId + ']').prop('selected', 'selected').change();

                                        // must be run after timeout because there is no #sw_versions_list at this moment
                                        setTimeout(function () {
                                            $('#sw_versions_list option[value=' + parseInt(res.inserted, 10) + ']').prop('selected', 'selected').change();
                                        }, 500);

                                        createDialog.dialog("close");
                                    })
                                    .then(function (res) {
                                        setTimeout(function () {
                                            $("#infobox-ecu").html(res.msg);
                                        }, 500);
                                    });
                            }
                        }
                    });
                }
            },
            "Cancel": function () {
                createDialog.dialog("close");
            }
        }
    });

    //
    // Prepare dialog for copying software
    //
    var copySWDialog = $("#copy-revision-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Copy revision": function () {
                let valid = true;
                let revisionName = $('#copyRevisionName').val().trim();
                valid = valid && checkLength(revisionName, 5);
                valid = valid && checkIfExists(revisionName);
                valid = valid && checkPattern(revisionName);
                if (valid) {
                    $(this).dialog("close");
                    $('#copyRevisionName').val = "";
                    // add new sw version
                    $.ajax({
                        method: "GET",
                        url: "index.php",
                        data: {
                            action: 'ecuSwConf',
                            method: 'ajaxAddNewSwRevision',
                            selectedName: revisionName,
                            selectedEcu: selectedEcuId,
                            selectedSwVersion: selectedSwVersion
                        },
                        dataType: "json"
                    })
                        .done(function (res) {
                            $('#list_of_ecus option[value=' + selectedEcuId + ']').prop('selected', 'selected').change();

                            // must be run after timeout because there is no #sw_versions_list at this moment
                            setTimeout(function () {
                                $('#sw_versions_list option[value=' + parseInt(res.inserted, 10) + ']').prop('selected', 'selected').change();
                            }, 500);

                            copySWDialog.dialog("close");
                        })
                        .then(function (res) {
                            setTimeout(function () {
                                $("#infobox-ecu").html(res.msg);
                            }, 500);
                        });
                }
            },
            "Cancel": function () {
                copySWDialog.dialog("close");
            }
        }
    });

    //
    // Prepare dialog for Creating new subversion
    //
    var createSubversionDialog = $("#add-subversion-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Create subversion": function () {
                let valid = true;
                let revisionName = $('#subversionSuffixText').val().trim();
                valid = valid && checkLength(revisionName, 2);

                if (valid) {

                    let copyFromMain = false;
                    if (document.querySelector('input[name="copyFromMainVersion"]:checked').value === "yes") {
                        copyFromMain = true;
                    }

                    // get other subversions assigned to this sw version to check for unique names
                    $.ajax({
                        method: "GET",
                        url: "index.php",
                        data: {
                            action: 'ecuSwConf',
                            method: 'ajaxGetSubversionsFor',
                            parent: selectedSwVersion
                        },
                        dataType: "json"
                    })
                        .done(function (res) {

                            if (res && res.map(a => a.subversion_suffix).includes(revisionName)) {
                                updateTips("This subversion suffix already exists for this SW version");
                            } else {
                                // add new sw version
                                $.ajax({
                                    method: "GET",
                                    url: "index.php",
                                    data: {
                                        action: 'ecuSwConf',
                                        method: 'ajaxAddNewSubversion',
                                        suffix: revisionName,
                                        major: selectedSwVersion,
                                        copyFromMain: copyFromMain
                                    },
                                    dataType: "json"
                                })
                                    .done(function (res) {
                                        $('#list_of_ecus option[value=' + selectedEcuId + ']').prop('selected', 'selected').change();

                                        // must be run after timeout because there is no #sw_versions_list at this moment
                                        setTimeout(function () {
                                            $('#sw_versions_list option[value=' + parseInt(res.inserted, 10) + ']').prop('selected', 'selected').change();
                                        }, 500);

                                        $('#subversionSuffixText').val = "";
                                        createSubversionDialog.dialog("close");
                                    }).then(function (res) {
                                    setTimeout(function () {
                                        $("#infobox-ecu").html(res.msg);
                                    }, 500);
                                });
                            }
                        });


                    // add new sw version
                    // $.ajax({
                    //     method: "GET",
                    //     url: "index.php",
                    //     data: {
                    //         action: 'ecuSwConf',
                    //         method: 'ajaxAddNewSwRevision',
                    //         selectedName: revisionName,
                    //         selectedEcu: selectedEcuId
                    //     },
                    //     dataType: "json"
                    // })
                    //     .done(function (res) {
                    //         $('#list_of_ecus option[value=' + selectedEcuId + ']').prop('selected', 'selected').change();
                    //
                    //         // must be run after timeout because there is no #sw_versions_list at this moment
                    //         setTimeout(function () {
                    //             $('#sw_versions_list option[value=' + parseInt(res, 10) + ']').prop('selected', 'selected').change();
                    //         }, 100);
                    //     });
                }
            },
            "Cancel": function () {
                createSubversionDialog.dialog("close");
            }
        }
    });

    //
    // Prepare dialog for Removing new revision
    //
    var deleteDialog = $("#delete-revision-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: [
            {
                id: "confirm-delete-button",
                text: "Delete",
                click: function () {
                    deleteDialog.dialog("close");
                    $.ajax({
                        method: "GET",
                        url: "index.php",
                        data: {
                            action: 'ecuSwConf',
                            method: 'ajaxRemoveSwRevision',
                            revision: selectedSwVersion
                        },
                        dataType: "json"
                    })
                        .done(function (res) {
                            $('#list_of_ecus option[value=' + selectedEcuId + ']').prop('selected', 'selected').change();
                            return res;
                        }).then(function (res) {
                        setTimeout(function () {
                            $("#infobox-ecu").html(res);
                        }, 500);
                    });
                }
            },
            {
                text: "Cancel",
                click: function () {
                    deleteDialog.dialog("close");
                }
            }
        ]
    });

    //
    // Init
    //
    $("#sw_config_actions").hide(0);


    $(function () {
        $("#create_new_sw_button").removeClass('disabled');
        $("#delete_new_sw_button").removeClass('disabled');

        $("#create_new_sw_button").on('click', function () {
            createDialog.dialog("open");
        });

        //
        // TB-1884 - delete SW version
        //
        $("#delete_new_sw_button").on('click', function () {
            deleteDialog.dialog("open");

            $.ajax({
                method: "GET",
                url: "index.php",
                data: {
                    action: 'ecuSwConf',
                    method: 'ajaxGetConfigsForSwVersion',
                    revision: selectedSwVersion
                },
                dataType: "json"
            })
                .done(function (configs) {
                    $("#assigned-configurations-list").children().remove();

                    // Delete when: Software version is not assigned to any vehicle configuration.
                    if (configs.length === 0) {
                        $("<p>").text("OK: No configurations assigned to this SW version").appendTo($("#assigned-configurations-list"));

                        // Delete when: No SW sub version (see TB-1753 "creating a new software sub version") of it exists.
                        $.ajax({
                            method: "GET",
                            url: "index.php",
                            data: {
                                action: 'ecuSwConf',
                                method: 'ajaxGetSubversionsFor',
                                parent: selectedSwVersion
                            },
                            dataType: "json"
                        })
                            .done(function (res) {
                                if (res != null) {
                                    // subversion exists! no removing
                                    $("<p>").text("Error: There are some subversions assigned!").appendTo($("#assigned-configurations-list"));
                                    $("#confirm-delete-button").button("disable");
                                } else {
                                    // REMOVE
                                    $("<p>").text("OK: No subversions assigned to this SW version").appendTo($("#assigned-configurations-list"));
                                    $("<p>").text("Are you sure?").appendTo($("#assigned-configurations-list"));
                                    $("#confirm-delete-button").button("enable");
                                }
                            });
                    } else {
                        $("<p>").text("Error: This software is assigned to following configurations:").appendTo($("#assigned-configurations-list"));
                        let confList = $('<ul/>');
                        $.each(configs, function (index, value) {
                            $('<li/>').addClass().text(value).appendTo(confList);
                        });
                        confList.appendTo($("#assigned-configurations-list"));
                        if (configs.length >= 20) {
                            $("<p>").text("Only first 20 results showed").appendTo($("#assigned-configurations-list"));
                        }
                        $("#confirm-delete-button").button("disable");
                    }
                });
        });
    })


</script>

<style>
    #sw_config_actions {
        border: 1px solid #b0b0b0;
        height: auto;
        padding: 5px;
    }

    label, input {
        display: block;
    }

    input.text {
        margin-bottom: 12px;
        width: 95%;
        padding: .4em;
    }

    fieldset {
        padding: 0;
        border: 0;
        margin-top: 25px;
    }

    .ui-state-highlight,
    .ui-widget-content .ui-state-highlight,
    .ui-widget-header .ui-state-highlight {
        border: 1px solid #dad55e;
        background: #fffa90;
        color: #777620;
    }

    .validateTips {
        border: 1px solid transparent;
        padding: 0.3em;
    }

    .locked-yes {
        background: linear-gradient(to bottom, #880000 0, #884400 90%, #880000 100%) !important;
        color: #ffdddd !important;
    }

    .locked-no {
        background: linear-gradient(to bottom, #008800 0, #668844 90%, #008800 100%) !important;
        color: #ddffdd !important;
    }
</style>