<?php
/**
 * responsiblePersons.php
 * @author FEV
 */
?>

<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>
<h1><?php echo $this->translate['responsiblePersons']['titleResponsibilities']; ?></h1>

<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>

<div class="row">
    <div class="columns two">
        <h1><?php echo $this->translate['responsiblePersons']['titleCategories']; ?></h1>
        <p><?php echo $this->translate['responsiblePersons']['textChooseCategories']; ?></p>
        <?php if (isset($this->responsibilityCategories)) { ?>
            <form action="" id="responsibility_cat">
                <select id="firstCategories" name="category" style="width: 100%;">
                    <option value="0">---</option>
                    <?php foreach ($this->responsibilityCategories as $row) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php } ?></select>
                <h1 id="responsibility_cat_name"><?php echo $this->translate['responsiblePersons']['titleSubcategories']; ?></h1>
                <select id="responsibility_cat_val" name="category" size="8" style="width: 100%;">
                    <option value="1">text1</option>
                    <option value="2">text2</option>
                </select>
            </form>

        <?php } else {
            echo '<p>' . $this->translate['responsiblePersons']['infoNoCategoriesResponsibility'] . '</p>';
        } ?>
    </div>
    <div class="columns eight">
        <div class="row">
            <h1 id="responsibilities_header"></h1>
        </div>
        <div id="deviation-management" class="row">
            <div class="error-message"></div>
        </div>
        <div id="usersStructuresAdd" class="row">
            <label for="usersStructuresSearchBox"><h2><?php echo $this->translate['responsiblePersons']['textAddNew']; ?></h2>
                <p><?php echo $this->translate['responsiblePersons']['infoEnterName']; ?></p></label>
            <div class="options-bar">
                <ul class="submenu_ul">
                    <li><input id="usersStructuresSearchBox"/></li>
                    <li>
                        <select id="structure_details" id="structure_details"
                                style="padding: 7px 2px 7px 6px; border=0;">
                            <option value="leader"><?php echo $this->translate['responsiblePersons']['optLeader']; ?></option>
                            <option value="deputy"><?php echo $this->translate['responsiblePersons']['optDeputy']; ?></option>
                            <option value="all"><?php echo $this->translate['responsiblePersons']['optAll']; ?></option>
                        </select>
                    </li>
                    <li>
                        <button id="saveUserStructure" class="sts_submenu W140 disabled" disabled><?php echo $this->translate['generalMessages']['btnAdd']; ?></button>
                    </li>
                    <li style="text-align: left;">
                        <div id="possibilityRespon"><input id="responsibleCheckbox" name="responsibleCheckbox"
                                                           type="checkbox" value="responsible"/>
                            <label for="responsibleCheckbox"><?php echo $this->translate['responsiblePersons']['lblSetResponsible']; ?></label></div>
                        <div id="possibilityDeputy"><input id="deputyCheckbox" name="deputyCheckbox" type="checkbox"
                                                           value="deputy"/>
                            <label for="deputyCheckbox"><?php echo $this->translate['responsiblePersons']['lblSetDeputy']; ?></label></div>
                    </li>
                </ul>
            </div>
        </div>
        <div id="infobox-management" class="row"></div>
        <div id="responsibilities_content">
        </div>
    </div>
</div>

<div id="remove-dialog" title="Remove responsible person">
    <p><span id="remove-dialog-text"></span></p>
</div>

<div id="switch-role-dialog" title="Switch user role to:">
    <form id="switch-user-role">
        <select name="role" id="role">
            <option value="responsible"><?php echo $this->translate['responsiblePersons']['optResponsible']; ?></option>
            <option value="deputy"><?php echo $this->translate['responsiblePersons']['optDeputy']; ?></option>
            <option value="writable"><?php echo $this->translate['responsiblePersons']['optWritable']; ?></option>
        </select>
        <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </form>
</div>

<script>
    let selectedUserOrStructure;
    let selectedCategoryId;
    let selectedCategoryName = '';
    let deviationPermissionsCategory;

    //
    // Prepare search box: clear all previous content
    //
    function resetSearchBox() {
        $('#saveUserStructure').addClass("disabled");
        $('#saveUSerStructure').prop("disabled", true);
        $('#usersStructuresSearchBox').val('');
        selectedUserOrStructure = null;
        $('#structure_details').hide('fast');
        $('#deputyCheckbox').prop('checked', false);
        $('#responsibleCheckbox').prop('checked', false);
        $('#possibilityRespon').hide();
        $('#possibilityDeputy').hide();
    }

    //
    // Show user available checkbox if added person can be responsible / deputy
    //
    function addPossibility(type) {
        if (type == 'responsible') {
            $('#possibilityRespon').show();
        } else if (type == 'deputy') {
            $('#possibilityDeputy').show();
        } else {
            $('#possibilityRespon').hide();
            $('#possibilityDeputy').hide();
        }
    }

    //
    // Remove user from list
    //
    function confirmRemoving(button, funcId, funcName) {

        $("#remove-dialog").prop('removeId', button.value);
        $("#remove-dialog-text").html(
            "<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:12px 12px 20px 0;\"></span>" +
            "<?php echo str_replace('{*var3*}', 'funcName', $this->translate['responsiblePersons']['msgConfirmRemove']); ?>");
        $("#remove-dialog").dialog("open");
    }

    //
    // Switch user role
    //
    function switchUserRole(button) {
        $("#switch-role-dialog").prop('switchRoleId', button.value);
        $("#switch-role-dialog").dialog("open");
    }

    //
    // Prepare results
    //
    function showResponsibilitiesFor(selectedCategory, nameOfCategory) {

        let no_respon_assigned = true;
        let no_deputy_assigned = true;

        $('#deviation-management').hide();

        $.ajax({
            method: "GET",
            url: "index.php",
            data: {id: selectedCategory, action: "responsiblePersons", method: "ajaxGetResponsiblePersonsAssignments"},
            dataType: "json"
        })
            .done(function (responsibilityAssignments) {
                // $('#responsibilities_header').text("Set responsible persons");
                if (responsibilityAssignments == null || responsibilityAssignments == false) {
                    $('#responsibilities_content').children().remove();
                    $('#responsibilities_content').html("<h2>" + nameOfCategory + "</h2><p><?php echo $this->translate['responsiblePersons']['infoNoResponsibilities']; ?></p>");

                    disableDeviationSelection(false);
                } else {
                    $('#responsibilities_content').children().remove();
                    $('#responsibilities_content').html("<h2>" + nameOfCategory + "</h2>");
                    var respList = "<?php echo $this->translate['responsiblePersons']['titleOfTableColumns']; ?></p>";
                    for (let i = 0; i < responsibilityAssignments.length; i++) {
                        let entry = responsibilityAssignments[i];
                        let det = (entry['structure_details'] !== null) ? entry['structure_details'] : '';
                        let sts = (entry['is_structure'] == 't') ? "is_structure" : "";
                        let resp_background = ";";
                        let resp_col = '<td></td>';
                        if (entry['is_responsible'] == 't') {
                            resp_col = "<td><strong><?php echo $this->translate['responsiblePersons']['optResponsible']; ?></strong></td>";
                            resp_background = "background-color: #ffb3b3;";
                            no_respon_assigned = false;
                        } else if (entry['is_deputy'] == 't') {
                            resp_col = "<?php echo $this->translate['responsiblePersons']['optDeputy']; ?>";
                            resp_background = "background-color: #ecffb3;";
                            no_deputy_assigned = false;
                        } else {
                            resp_col = "<td><?php echo $this->translate['responsiblePersons']['opWritable']; ?></td>";
                        }
                        respList +=
                            '<tr style="' + resp_background + '">' + resp_col +
                            '<td><span class=' + sts + '>' + entry['displayName'] + '</span></td>' +
                            '<td>' + det + '</td>' +
                            '<td>' +
                            '<button value="' + entry['id'] + '" onclick="confirmRemoving(this, selectedCategoryId, selectedCategoryName)"><?php echo $this->translate['generalMessages']['btnRemove']; ?></button>' +
                            '<button value="' + entry['id'] + '" onclick="switchUserRole(this)"><?php echo $this->translate['generalMessages']['btnSwitchRole']; ?></button>' +
                            '</td>' +
                            '</tr>';
                    }
                    respList += '</table>';
                    $('#responsibilities_content').append(respList);

                    if (deviationPermissionsCategory && responsibilityAssignments.length === 4) {
                        disableDeviationSelection(true);
                    } else {
                        disableDeviationSelection(false);
                    }
                }
            })
            .then(function () {
                $('#possibilityRespon').hide();
                $('#possibilityDeputy').hide();
                if (no_respon_assigned) {
                    addPossibility('responsible');
                }
                if (no_deputy_assigned) {
                    addPossibility('deputy');
                }
            });
    }

    function disableDeviationSelection(enable) {
        if (enable) {
            $('#deviation-management').show();
            $("#usersStructuresSearchBox").prop('disabled', 'disabled');
            $('#responsibleCheckbox').attr('disabled', 'disabled');
            $('#deputyCheckbox').attr('disabled', 'disabled');
        } else {
            $('#deviation-management').hide();
            $("#usersStructuresSearchBox").prop('disabled', null);
            $('#responsibleCheckbox').attr('disabled', null);
            $('#deputyCheckbox').attr('disabled', null);
        }
    }

    // right after load
    $('#usersStructuresAdd').hide(0);
    $('#responsibility_cat_name').hide(0); // cannot be inside on document ready
    $('#responsibility_cat_val').hide(0);  // because of blinking effect
    $('#deviation-management').hide(); // disable error message for deviation permissions
    resetSearchBox();

    //
    // after document loads
    //
    $(document).ready(function () {
        // $('#responsibilities_header').text("Responsible persons");
        $('#responsibilities_content').html("<p><?php echo $this->translate['responsiblePersons']['textSelectCategory']; ?></p>");

        //
        // Only one checkbox can be checked
        //
        $('#deputyCheckbox').change(function () {
            if (this.checked) {
                $('#responsibleCheckbox').prop('checked', false);
            }
        });
        $('#responsibleCheckbox').change(function () {
            if (this.checked) {
                $('#deputyCheckbox').prop('checked', false);
            }
        });

        function removeManagementFunctionsItem(id) {
            $.ajax({
                method: "GET",
                url: "index.php",
                data: {
                    action: '<?php echo $this->action; ?>',
                    method: 'ajaxRemoveResponsibility',
                    id: id
                },
                dataType: "json"
            })
                .done(function (res) {
                    $('#infobox-management').children().remove();
                    $('#infobox-management').html(res);

                    showResponsibilitiesFor(selectedCategoryId, selectedCategoryName);
                    resetSearchBox();
                });
        }

        function switchUserRoleFunctionItem(id, category, role) {
            $.ajax({
                method: "GET",
                url: "index.php",
                data: {
                    action: '<?php echo $this->action; ?>',
                    method: 'ajaxSwitchRole',
                    id: id,
                    category: category,
                    role: role
                },
                dataType: "json"
            })
                .done(function (res) {
                    console.log(res);
                    $('#infobox-management').children().remove();
                    $('#infobox-management').html(res);

                    showResponsibilitiesFor(selectedCategoryId, selectedCategoryName);
                    resetSearchBox();
                });
        }

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
                "<?php echo $this->translate['generalMessages']['btnRemove']; ?>": function () {
                    $(this).dialog("close");
                    removeManagementFunctionsItem($(this).prop('removeId'));
                },
                "<?php echo $this->translate['generalMessages']['btnCancel']; ?>": function () {
                    $(this).dialog("close");
                }
            }
        });

        //
        // Prepare dialog for SWITCHING USER ROLE entry
        //
        $("#switch-role-dialog").dialog({
            autoOpen: false,
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "<?php echo $this->translate['generalMessages']['btnSwitch']; ?>": function () {
                    $(this).dialog("close");
                    switchUserRoleFunctionItem($(this).prop('switchRoleId'), selectedCategoryId, $('#role').val());
                },
                "<?php echo $this->translate['generalMessages']['btnCancel']; ?>": function () {
                    $(this).dialog("close");
                }
            }
        });

        $('#role').selectmenu({
            width: 280
        });

        //
        // User changes FIRST category
        //
        $('#firstCategories').on('change', function (event) {

            selectedCategoryId = $(this).find("option:selected").val();
            selectedCategoryName = $(this).find("option:selected").text();
            deviationPermissionsCategory = (selectedCategoryName && selectedCategoryName === 'Deviation permissions') ? 1 : 0;

            if (selectedCategoryId == 0) {
                $('#usersStructuresAdd').hide(0);
                $('#responsibility_cat_name').hide(0); // cannot be inside on document ready
                $('#responsibility_cat_val').hide(0);  // because of blinking effect
                // $('#responsibilities_header').text("Responsible persons");
                $('#responsibilities_content').html("<p><?php echo $this->translate['generalMessages']['textSelectCategory']; ?></p>");
                return;
            }

            // is any subcategory?
            $.ajax({
                method: "GET",
                url: "index.php",
                data: {
                    id: selectedCategoryId,
                    action: '<?php echo $this->action; ?>',
                    method: 'ajaxGetSubcategory'
                },
                dataType: "json"
            })
                .done(function (secondList) {
                    if (secondList != null) {
                        $('#deviation-management').hide();

                        $('#responsibility_cat_name').text("<?php echo $this->translate['generalMessages']['textSelect']; ?> " + selectedCategoryName);

                        // clear previous and add options
                        $('#responsibility_cat_val').children().remove();
                        for (let i = 0; i < secondList.length; i++) {
                            $('#responsibility_cat_val').append($('<option>', {
                                value: secondList[i]['id'],
                                text: secondList[i]['name']
                            }));
                        }
                        $('#responsibility_cat_name').show('fast');
                        $('#responsibility_cat_val').show('fast');
                    } else {
                        $('#responsibility_cat_val').children().remove();
                        $('#responsibility_cat_val').hide('fast');
                        $('#responsibility_cat_name').hide('fast');
                        showResponsibilitiesFor(selectedCategoryId, selectedCategoryName);
                    }
                });
            $('#usersStructuresAdd').show(0);
            $('#infobox-management').children().remove();
        });

        //
        // User changes SECOND category
        //
        $('#responsibility_cat_val').on('change', function (event) {

            selectedCategoryId = $(this).find("option:selected").val();
            selectedCategoryName = $(this).find("option:selected").text();
            $('#usersStructuresAdd').show(0);
            showResponsibilitiesFor(selectedCategoryId, selectedCategoryName);
            $('#infobox-management').children().remove();
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
                            $('#saveUserStructure').prop('disabled', false);
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
        // User clicks ADD
        //
        $('#saveUserStructure').on('click', function (event) {
            if (!selectedCategoryId) {
                console.warn("No category selected");
                return;
            }

            if ($('#responsibility_cat_val:visible').length > 0 && !($('#responsibility_cat_val option:selected').length > 0)) {
                console.warn("No option selected");
                $('<div id="no-option-selected-dialog" title="Error">'+"<p><?php echo $this->translate['responsiblePersons']['textNoOption']; ?></p></div>").dialog(
                    {
                        resizable: false,
                        height: "auto",
                        width: 200,
                        modal: true,
                        buttons: {
                            "<?php echo $this->translate['generalMessages']['textNoOption']; ?>": function () {
                                $(this).dialog("close");
                            }
                        }
                    });
                return;
            }

            let strDetails = $('#structure_details').children("option:selected").val();
            if (selectedUserOrStructure) {
                let depCheck = $('#deputyCheckbox').is(':checked');
                let resCheck = $('#responsibleCheckbox').is(':checked');
                $.ajax({
                    method: "GET",
                    url: "index.php",
                    data: {
                        action: '<?php echo $this->action; ?>',
                        method: 'ajaxAddResponsiblePersonsAssignment',
                        category: selectedCategoryId,
                        item: selectedUserOrStructure,
                        structure_details: strDetails,
                        set_as_deputy: depCheck,
                        set_as_responsible: resCheck
                    },
                    dataType: "json"
                })
                    .done(function (res) {
                        $('#infobox-management').children().remove();
                        $('#infobox-management').html(res);
                    })
                    .then(function () {
                        showResponsibilitiesFor(selectedCategoryId, selectedCategoryName);
                        resetSearchBox();
                    });
            } else {
                console.warn("No user or company structure selected!");
                return;
            }
        });

    }); // DOCUMENT READY

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