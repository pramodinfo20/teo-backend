<?php
/**
 * responsiblePersons.php
 * @author FEV
 */
?>

<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>
<h1><?php echo $this->translate['manageFunctions']['header']; ?></h1>

<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>

<div class="row">
    <div class="columns two">
        <h1><?php echo $this->translate['manageFunctions']['selectHeader']; ?></h1>
        <p><?php echo $this->translate['manageFunctions']['selectTitle']; ?></p>
        <?php if (isset($this->managementFunctions)) { ?>
            <form action="" id="">
                <select id="managementFunctionsSelect" name="managementFunctions" style="width: 100%;">
                    <option value="0">---</option>
                    <?php foreach ($this->managementFunctions as $row) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php } ?></select>
            </form>

        <?php } else {
            echo "<p> ${$this->translate['manageFunctions']['noSetNotice']} </p>";
        } ?>
    </div>

    <div class="columns eight">
        <div class="row">
            <h1 id="managements_header"></h1>
        </div>
        <div id="usersStructuresAdd" class="row">
            <label for="usersStructuresSearchBox"><h2><?php echo $this->translate['manageFunctions']['addUser']; ?></h2>
                <p><?php echo $this->translate['manageFunctions']['titleAddUser']; ?></p></label>
            <div class="options-bar">
                <ul class="submenu_ul">
                    <li><input id="usersStructuresSearchBox"/></li>
                    <li>
                        <select id="structure_details" id="structure_details"
                                style="padding: 7px 2px 7px 6px; border=0;">
                            <option value="leader"><?php echo $this->translate['manageFunctions']['structureLeader']; ?></option>
                            <option value="deputy"><?php echo $this->translate['manageFunctions']['structureDeputy']; ?></option>
                            <option value="all"><?php echo $this->translate['manageFunctions']['structureAll']; ?></option>
                        </select>
                    </li>
                    <li><span id="saveUserStructure" class="sts_submenu W140 disabled"><?php echo $this->translate['manageFunctions']['structureSave']; ?></span></li>
                </ul>
            </div>
        </div>
        <div id="infobox-management" class="row"></div>
        <div id="managements_div"></div>
    </div>
</div>

<div id="remove-dialog" title="Remove management function assignment">
    <p><span id="remove-dialog-text"></span></p>
</div>

<script>
    let selectedUserOrStructure;
    let selectedFunctionId;
    let selectedFunctionName;

    function resetSearchBox() {
        $('#saveUserStructure').addClass("disabled");
        $('#usersStructuresSearchBox').val('');
        selectedUserOrStructure = null;
        $('#structure_details').hide('fast');
    }

    //
    // Remove user from list
    //
    function confirmRemoving(button, funcId, funcName) {

        $("#remove-dialog").prop('removeId', button.value);
        var removeTextDialog1 = "<?php echo $this->translate['manageFunctions']['removeTextDialog1']; ?>";
        var removeTextDialog2 = "<?php echo $this->translate['manageFunctions']['removeTextDialog2']; ?>";
        $("#remove-dialog-text").html(
            "<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:12px 12px 20px 0;\"></span>" +
            removeTextDialog1 + funcName + removeTextDialog2);
        $("#remove-dialog").dialog("open");
    }

    function removeManagementFunctionsItem(id) {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxRemoveManagementFunction',
                id: id
            },
            dataType: "json"
        })
            .done(function (res) {
                $('#infobox-management').children().remove();
                $('#infobox-management').html(res);

                refreshManagementList();
            });
    }

    //
    // Update list
    //
    function refreshManagementList() {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetManagementsFor', selectedFunction: selectedFunctionId
            },
            dataType: "json"
        })
            .done(function (managements) {
                // $('#managements_header').text("Set management functions");

                $('#managements_div').children().remove();
                if (!managements) {
                    var titleNoAssigned = "<?php echo $this->translate['manageFunctions']['titleNoAssigned']; ?>";
                    $('#managements_div').html("<h2>" + selectedFunctionName + "</h2><p>" + titleNoAssigned + "</p>");
                } else {
                    var titleAssigned = "<?php echo $this->translate['manageFunctions']['titleAssigned']; ?>";
                    $('#managements_div').html("<h2>" + selectedFunctionName + "</h2><p>" + titleAssigned + "</p>");
                    var userListHeader1 = "<?php echo $this->translate['manageFunctions']['userListHeader1']; ?>";
                    var userListHeader2 = "<?php echo $this->translate['manageFunctions']['userListHeader2']; ?>";
                    var detailsHeader = "<?php echo $this->translate['manageFunctions']['detailsHeader']; ?>";
                    var optionsHeader = "<?php echo $this->translate['manageFunctions']['optionsHeader']; ?>";
                    let respList = '<table><tr><th>' + userListHeader1 + '<span class=\"is_structure\">' + userListHeader2 +
                        '</span></th><th>' + detailsHeader + '</th><th>' + optionsHeader + '</th></tr>';
                    for (let i = 0; i < managements.length; i++) {
                        let det = (managements[i]['structure_details'] !== null) ? managements[i]['structure_details'] : '';
                        let sts = (managements[i]['is_structure'] == 't') ? "is_structure" : "";
                        let remove = "<?php echo $this->translate['generalMessages']['btnRemove']; ?>";
                        respList += '<tr><td><span class=\"' + sts + '\">' + managements[i]['displayName'] + '</span></td>' +
                            '<td>' + det + '</td>' +
                            '<td><button value="' + managements[i]['id'] + '" onclick=\'confirmRemoving(this, selectedFunctionId, selectedFunctionName)\'>' + remove + '</button></td>';
                    }
                    respList += '</table>';
                    $('#managements_div').append(respList);
                }
            });
    }

    $(document).ready(function () {
        $('#usersStructuresAdd').hide(0);
        // $('#managements_header').text("Management functions");
        var titleTip = "<?php echo $this->translate['manageFunctions']['titleTip']; ?>";
        $('#managements_div').html("<p>" + titleTip + "</p>");
        resetSearchBox();

        //
        // Prepare dialog for REMOVING entry from list
        //
        $("#remove-dialog").dialog({
            autoOpen: false,
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "Remove": function () {
                    $(this).dialog("close");
                    removeManagementFunctionsItem($(this).prop('removeId'));
                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            }
        });

        //
        // Populating searchbox for ADD NEW USER/STRUCTURE
        //
        $.ajax({
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetAllStsUsersCompanyStructures'
            },
            dataType: "json"
        })
            .then(function (stsUsersAndCompanyStructures) {
                $("#usersStructuresSearchBox").catcomplete({
                    delay: 0,
                    minLength: 3,
                    select: function (event, ui) {
                        if (ui.item) {
                            $('#saveUserStructure').removeClass("disabled");
                            selectedUserOrStructure = ui.item;
                            if (typeof selectedUserOrStructure.csid !== 'undefined') {
                                $('#structure_details').show('fast');

                            } else {
                                $('#structure_details').hide('fast');
                            }
                        } else {
                            console.log("NOTHING SELECTED! Typed: " + this.value);
                        }
                    },
                    source: stsUsersAndCompanyStructures
                });
            });

        //
        // User clicks SAVE
        //
        $('#saveUserStructure').on('click', function (event) {
            if (!selectedFunctionId) {
                console.warn("No ManagementFunction selected");
                return;
            }
            let structure_details = $('#structure_details').children("option:selected").val();
            if (selectedUserOrStructure) {
                $.ajax({
                    method: "GET",
                    url: "index.php",
                    data: {
                        action: '<?php echo $this->action; ?>',
                        method: 'ajaxAddManagementFunction',
                        item: selectedUserOrStructure,
                        func: selectedFunctionId,
                        structure_details: structure_details
                    },
                    dataType: "json"
                })
                    .done(function (res) {
                        $('#infobox-management').children().remove();
                        $('#infobox-management').html(res);
                        $('#usersStructuresSearchBox').value = '';
                    })
                    .then(function () {
                        refreshManagementList();
                        resetSearchBox();
                    });
            } else {
                console.warn("No user or company structure selected!");
                return;
            }
        });

        //
        // User chooses MANAGEMENT FUNCTION
        //
        $('#managementFunctionsSelect').on('change', function () {
            selectedFunctionId = $(this).find("option:selected").val();
            selectedFunctionName = $(this).find("option:selected").text();
            $('#infobox-management').children().remove();
            refreshManagementList();
            $('#usersStructuresAdd').show(0);
        });


    }); // on document ready

</script>

<style>
    .options-bar {
        margin-top: 1.0em;
        text-align: center;
    }

    .is_structure {
        font-weight: bold;
    }

    .ui-autocomplete-category {
        font-weight: bold;
        padding: .2em .4em;
        margin: .8em 0 .2em;
        line-height: 1.5;
    }

    #usersStructuresAdd {
        margin-top: 3.0em;
    }

    #usersStructuresSearchBox {
        width: 400px;
        padding: 10px 2px 10px 6px;
    }

    .error-message {
        padding: 20px;
        background-color: #f45f42;
        color: white;
    }

    .success-message {
        padding: 20px;
        background-color: #34a34a;
        color: white;
    }

    .info-message {
        padding: 20px;
        background-color: #34a3f0;
        color: white;
    }
</style>
