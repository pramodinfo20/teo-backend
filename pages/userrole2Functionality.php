<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";


//r($this->aUserroles);
//r($this->test)


?>
<div id="infobox-management" class="row">
    <?php

    if (isset($this->removeMsg)) {

        if ($this->removeMsg == 'deleted') {
            echo '<div id="infobox-management" class="row success-message"><div>'.$this->translate['userrole2Functionality']['msgRoleRemoved'].'</div></div>';
        } else if ($this->removeMsg == "wrong") {
            echo '<div id="infobox-management" class="row info-message"><div>'.$this->translate['userrole2Functionality']['msgRoleWrongRemoved'].'</div></div>';
        }
    }

    ?>
</div>

<div class="row">
    <h1><?php echo $this->translate['userrole2Functionality']['titleAssignUserRole']; ?></h1>
    <p><?php echo $this->translate['userrole2Functionality']['textUserGroup']; ?></p>
</div>

<div class="row">
    <div class="options-bar">
        <ul class="submenu_ul">
            <li><span id="addNewUserRole" class="sts_submenu W140"><?php echo $this->translate['userrole2Functionality']['addRole']; ?></span></li>
        </ul>
    </div>

    <div id="userRoleAdd" class="row" hidden>
        <label for="userRoleBox"><h2></h2>
            <p></p></label>
        <div class="options-bar">
            <ul class="submenu_ul">
                <li><input type="text" name="createUserRole" id="createUserRole"><br></li>
                <li>
                    <button id="createButton" class="sts_submenu W140 disabled" disabled><?php echo $this->translate['userrole2Functionality']['btnCreate']; ?></button>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row" id="userRolesFuncGroups"></div>
</div>
<div id="dialog" title="Delete User Role">
    <p>
        <span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
      <?php echo $this->translate['userrole2Functionality']['msgConfirmRemoveRole']; ?>
    </p>
</div>

<script>
    //
    // refresh main table
    //
    function refreshUserRolesFunGroups() {

        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetUserRolesFuncGroups'
            },
            dataType: "json"
        })
            .done(function (userRolesFuncGroups) {
                let table = $('<table>').addClass('');
                let header = $('<tr>').addClass('');
                header.append($('<th>').text("<?php echo $this->translate['userrole2Functionality']['headerRole']; ?>"));
                header.append($('<th>').text("<?php echo $this->translate['userrole2Functionality']['headerGroup']; ?>"));
                header.append($('<th>').text("<?php echo $this->translate['userrole2Functionality']['headerActions']; ?>"));
                table.append(header);

                for (let i = 0; i < userRolesFuncGroups.length; i++) {
                    let row = $('<tr>').addClass('');
                    row.append($('<td>').text(userRolesFuncGroups[i]['header']['name']));
                    if (userRolesFuncGroups[i]['fcg'] !== null) {
                        let funcGroups = "";
                        for (let fg = 0; fg < userRolesFuncGroups[i]['fcg'].length; fg++) {
                            if (fg > 0)
                                funcGroups += ", ";
                            funcGroups += userRolesFuncGroups[i]['fcg'][fg]['name'];
                            funcGroups += (userRolesFuncGroups[i]['fcg'][fg]['write_permissions'] === 't') ? ' [w]' : ' [r]';
                        }
                        row.append($('<td>').text(funcGroups));
                    } else {
                        row.append($('<td>').text("<?php echo $this->translate['userrole2Functionality']['noFunctionalityGroup']; ?>"));
                    }

                    let userRoleId = userRolesFuncGroups[i]['header']['id'];

                    let actedit = "index.php?action=userrole2Functionality&userroleid=" + userRoleId;
                    let actremove = "index.php?action=userrole2Functionality&method=removeUserRole&userroleid=" + userRoleId;
                    let editBtn = $('<a>').attr({
                        href: actedit,
                        style: "margin-right: 5px;"
                    }).append($('<input>').attr({type: 'submit', value: "<?php echo $this->translate['generalMessages']['btnEdit']; ?>" }));
                    let removeBtn = $('<a>').attr({
                        class: 'clickablerem',
                        href: actremove,
                        style: "margin-right: 5px;"
                    }).append($('<input>').attr({type: 'submit', value: "<?php echo $this->translate['generalMessages']['btnRemove']; ?>" , class: 'buttonUserRole'}));
                    // let removeBtn = $('<input>').attr({ type: 'button', value: 'Remove', id: userRoleId, class: 'buttonUserRole' });

                    row.append($('<td>').append(editBtn).append(removeBtn));
                    table.append(row);
                }

                $('#userRolesFuncGroups').children().remove();
                $('#userRolesFuncGroups').html(table);
            })
            .then(function () {
                $(".clickablerem").on("click", function () {

                    href = $(this).attr('href') + '&msg=deleted';

                    $('#dialog').dialog({
                        resizable: false,
                        autoOpen: true,
                        modal: true,
                        buttons: {
                            "<?php echo $this->translate['generalMessages']['btnOk']; ?>": function () {
                                window.location = href;
                                $(this).dialog("close");
                            },
                            "<?php echo $this->translate['generalMessages']['btnCancel']; ?>": function () {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                });
            });
    }

    $(function () {
        refreshUserRolesFunGroups();

        //
        // Events on documents start - cannot be after table refresh
        //
        $("#addNewUserRole").on("click", function () {
            $("#userRoleAdd").show();
        });

        $("#createUserRole").on("input", function () {
            $("#createButton").removeClass("disabled");
            $("#createButton").prop("disabled", false);
        });

        $("#createButton").on("click", function () {

            let urName = $('#createUserRole').val();

            if ($.trim(urName) == '') {
                $('<div id="empty-field-dialog" title="Error"><p>'+"<?php echo $this->translate['userrole2Functionality']['msgEmptyName']; ?>"+'</p></div>').dialog(
                    {
                        resizable: false,
                        height: "auto",
                        width: 200,
                        modal: true,
                        buttons: {
                            "<?php echo $this->translate['generalMessages']['btnOk']; ?>": function () {
                                $(this).dialog("close");
                            }
                        }
                    });
                return;
            }

            $.ajax({
                method: "GET",
                url: "index.php?action=userrole2Functionality",
                data: {
                    method: 'ajaxAddNewUserRole',
                    userRoleName: urName
                },
                dataType: "json"
            }).done(function (res) {
                $('#infobox-management').children().remove();
                $('#infobox-management').html(res);

                refreshUserRolesFunGroups();
                $('#createUserRole').val('');
            });
        });
    });
</script>

<style>
    #userRolesFuncGroups {
        margin-bottom: 15px;
    }
    #dialog {
        display: None;
    }
    .options-bar {
        margin-top: 1.0em;
        text-align: center;
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
