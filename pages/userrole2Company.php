<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>

<h1><?php echo $this->translate['userrole2Company']['titleAssignUserStructure']; ?></h1>
<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>

<div class="row">
    <div class="columns three">
        <h2><?php echo $this->translate['userrole2Company']['titleSelect']; ?></h2>
        <select id="userRoles">
            <option value="0">---</option>
            <?php foreach ($this->aUserroles as $record): ?>
                <option value="<?php echo $record['id'] ?>"><?php echo $record['name'] ?></option>
            <?php endforeach ?>
        </select>
        <p><?php echo $this->translate['userrole2Company']['tipInSelect']; ?></p>
    </div>

    <div class="columns nine">
        <div id="infobox-management" class="row"></div>
        <p><?php echo $this->translate['userrole2Company']['infoAboutSave']; ?></p>
        <p><?php echo $this->translate['userrole2Company']['infoAboutAssigned']; ?></p>
        <form method="post" action="index.php?action=userrole2Company">
            <?php
            /*
             * TODO: refctoring do przemyslenia POZNIEJ: funkcja powinna byc jakos poza formem (chyba)
             * */
            function createTreeView($array, $currentParent, $currLevel = 0, $prevLevel = -1)
            {
                foreach ($array as $category) {

                    if ($currentParent == $category['parent_id']) {
                        if ($currLevel > $prevLevel) echo " <ul class='tree' style='list-style-type: none;'> ";
                        if ($currLevel == $prevLevel) echo " </li>";

                        echo '<li><span title="Assigned to company structure ' . $category['name'] . '. Re assigned it will remove this assignment."><input type="checkbox" name="cbid[]" value="' . $category['id'] . '"/><label for="lorganisation[]" id="' . $category['id'] . '" class="">' . $category['name'] . '</label></span>';

                        if ($currLevel > $prevLevel) {
                            $prevLevel = $currLevel;
                        }

                        $currLevel++;
                        createTreeView($array, $category['id'], $currLevel, $prevLevel);
                        $currLevel--;
                    }
                }
                if ($currLevel == $prevLevel) echo " </li></ul> ";
            }

            createTreeView($this->getResponseIntResult($this->getOrganizationStructure()), 0);
            ?>
        </form>

        <div class="options-bar">
            <ul class="submenu_ul">
                <li>
                    <span id="saveStructures" class="sts_submenu W140 disabled"><?php echo $this->translate['generalMessages']['btnSave']; ?></span>
                </li>
                <li>
                    <a href="index.php?action=userrole2Company&method=displayTreeStructure" class="sts_submenu W140"><?php echo $this->translate['userrole2Company']['btnShowTree']; ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div id="overwrite-dialog" title="Confirm update!">
    <p><span id="overwrite-dialog-text"></span></p>
</div>

<script>
    let arrayOfAssignedStsOrganization = [];
    let arrayOfAssignedStsOrganizationForCurrentUserRole = [];
    let selectedUserRoleId;

    $("#overwrite-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "<?php echo $this->translate['generalMessages']['btnOverwrite']; ?>": function () {
                $(this).dialog("close");
                updateOrganizationStSForUserRoles();
            },
            "<?php echo $this->translate['generalMessages']['btnCancel']; ?>": function () {
                $(this).dialog("close");
            }
        }
    });

    function openOverwriteConfirmationDialog() {
        $("#overwrite-dialog-text").html(
            "<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:12px 12px 20px 0;\"></span>" +
            "<?php echo $this->translate['userrole2Company']['dialogElementsReasigned']; ?>");
        $("#overwrite-dialog").dialog("open");
    }


    function checkCheckBoxByValue(cBValue) {
        $(":checkbox").filter(function () {
            return this.value == cBValue;
        }).prop("checked", "true");
    }

    function clearAllCheckBoxes() {
        $("input[name='cbid[]']").each(function () {
            this.checked = false;
        });
    }

    function generateArrayOfCurrentlySelected() {
        let arrayOfCurrentlySelected = [];

        $("input[name='cbid[]']").each(function () {
            if (this.checked === true) arrayOfCurrentlySelected.push(parseInt(this.value));
        });
        return arrayOfCurrentlySelected;
    }

    function compareIfAssignationDuplicated() {
        let arrayOfCurrentlySelected = generateArrayOfCurrentlySelected();

        let difference = arrayDifferences(arrayOfCurrentlySelected, arrayOfAssignedStsOrganizationForCurrentUserRole);
        let intersection = arrayIntersection(arrayOfAssignedStsOrganization, difference);

        return !!intersection.length;
    }

    let arrayDifferences = (array1, array2) => array1.filter(x => !array2.includes(x));
    let arrayIntersection = (array1, array2) => array1.filter(x => array2.includes(x));

    $(document).ready(function () {
        getAssignedStsOrganizationStructuresFromDb().then(markAllAssignedStsOrganizationStructures);
    });

    $('#userRoles').on('change', function (event) {
        selectedUserRoleId = $('#userRoles').find("option:selected").val();
        clearAllCheckBoxes(selectedUserRoleId);
        getRolesForCurrentUserRoles(checkCheckBoxByValue);
        hideInformationAboutDbUpdate();

        if (selectedUserRoleId != 0) enableSaveButton();
        else disableSaveButton();
    });

    $('#saveStructures').on('click', function () {
        if (!($('#saveStructures').hasClass('disabled'))) {

            getRolesForCurrentUserRoles().then(getAssignedStsOrganizationStructuresFromDb().then(function () {
                let isOverwrite = compareIfAssignationDuplicated();

                if (isOverwrite == true) {
                    openOverwriteConfirmationDialog();
                } else {
                    updateOrganizationStSForUserRoles();
                }
            }));
        }
    });

    function enableSaveButton() {
        $('#saveStructures').removeClass("disabled");
    }

    function disableSaveButton() {
        $('#saveStructures').addClass("disabled");
    }

    function changeColorInCurrentLabel(labelId) {
        $("label[for='lorganisation[]']").filter(function () {
            return this.id == labelId;
        }).addClass("assigned-structure");
    }

    function clearColorInAllLabels() {
        $("label[for='lorganisation[]']").removeClass();
    }


    function markAllAssignedStsOrganizationStructures() {
        clearColorInAllLabels();
        for (let i = 0; i <= arrayOfAssignedStsOrganization.length; i++) {
            changeColorInCurrentLabel(arrayOfAssignedStsOrganization[i]);
        }
    }

    function getAssignedStsOrganizationStructuresFromDb() {
        return $.ajax({
            method: "GET",
            url: "index.php?action=userrole2Company&method=ajaxGetAssignedStsOrganisationStructures",
            dataType: "json"
        })
            .done(function (dataReceived) {
                arrayOfAssignedStsOrganization = dataReceived;
            });
    }

    function updateOrganizationStSForUserRoles() {
        $.ajax({
            method: "POST",
            url: "index.php?action=userrole2Company&method=ajaxUpdateStsOrganizationForUserRoles&id=" + selectedUserRoleId,
            data: $('form').serialize(),
            dataType: "json"
        })
            .done(function (resultOfUpdate) {
                getAssignedStsOrganizationStructuresFromDb().then(markAllAssignedStsOrganizationStructures);
                showInformationAboutDbUpdate(resultOfUpdate[0], resultOfUpdate[1]);
            });
    }

    function showInformationAboutDbUpdate(added, removed) {
        hideInformationAboutDbUpdate();
        let messageText = "";

        if (added) messageText += "<?php echo str_replace('{*var1*}', 'added', $this->translate['userrole2Company']['infoAddedStructure']); ?>";
        if (removed) messageText += "<?php echo str_replace('{*var2*}', 'removed', $this->translate['userrole2Company']['infoRemovedStructure']); ?>";

        if (messageText) {
            let res = '<div>' + messageText + '</div>';
            $('#infobox-management')
                .children()
                .remove();
            $('#infobox-management')
                .addClass("success-message")
                .html(res)
                .show();
        }
    }


    function hideInformationAboutDbUpdate() {
        $('#infobox-management').hide();
    }


    function getRolesForCurrentUserRoles(callbackCheckFunction = null) {
        return $.ajax({
            method: "GET",
            url: "index.php?action=userrole2Company&method=ajaxGetCompStructuresByUserRoleId",
            data: {id: selectedUserRoleId},
            dataType: "json"
        })
            .done(function (companyStructures) {
                if (companyStructures != null) {
                    arrayOfAssignedStsOrganizationForCurrentUserRole = [];
                    for (let i = 0; i < companyStructures.length; i++) {
                        if (callbackCheckFunction) callbackCheckFunction(companyStructures[i][('sts_organization_structure_id')]);
                        arrayOfAssignedStsOrganizationForCurrentUserRole.push(parseInt(companyStructures[i][('sts_organization_structure_id')]));
                    }
                }
            });
    }

</script>


<style>
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
</style>