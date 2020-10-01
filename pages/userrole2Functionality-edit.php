<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>

<div class="inner_container"></div>
<div class="row">
    <div class="column two">
        <div id="infobox-management" class="row"></div>

        <h1><?php echo $this->translate['userrole2FunctionalityEdit']['headerEditGroup']; ?></h1>
        <p><?php echo $this->translate['userrole2FunctionalityEdit']['titleEditGroup']; ?></p>

        <div class="options-bar">
            <ul class="submenu_ul">
                <li><span id="assignNewFuncGroup" class="sts_submenu W140"><?php echo $this->translate['userrole2FunctionalityEdit']['assignNewFuncGroup']; ?></span></li>
        </div>

        <div id="funcGroupAssign" class="row" hidden>
            <label for="funcGroupSearchBox"><h2><?php echo $this->translate['userrole2FunctionalityEdit']['headerAssignNew']; ?></h2>
                <p><?php echo $this->translate['userrole2FunctionalityEdit']['titleAssignNew']; ?></p></label>
            <div class="options-bar">
                <ul class="submenu_ul">
                    <li><select name="funcGroupSelect" class="funcGroupSelect">
                            <option default="default">---</option>
                            <!--                            <option value="funcgroup1">FuncGroup 1</option>-->
                            <!--                            <option value="funcgroup2">FuncGroup 2</option>-->
                        </select>
                    </li>
                    <li><select name="permission" class="permission" hidden>
                            <option value="read" selected="selected"><?php echo $this->translate['userrole2FunctionalityEdit']['permissionReadOnly']; ?></option>
                            <option value="write"><?php echo $this->translate['userrole2FunctionalityEdit']['permissionWrite']; ?></option>
                        </select>
                    </li>
                    <li><span id="assignFuncGroup" class="sts_submenu W140 disabled"><?php echo $this->translate['userrole2FunctionalityEdit']['btnAssignNew']; ?></span></li>
                </ul>
            </div>
        </div>

        <form method="post" action="index.php?action=userrole2Functionality-edit">
            <div id="userroleFunctionalityContent" class="row">

                <table>
                    <tr>
                        <th><?php echo $this->translate['userrole2FunctionalityEdit']['headerFunctionalityGroup']; ?></th>
                        <th><?php echo $this->translate['userrole2FunctionalityEdit']['headerPermission']; ?></th>
                        <th><?php echo $this->translate['userrole2FunctionalityEdit']['headerEdit']; ?></th>
                    </tr>
                    <?php foreach ($this->aFuncGroups as $key): ?>
                        <tr>
                            <td><?php echo $key['name'] ?></td>
                            <td>
                                <input type="hidden" value="<?php echo $key['id'] ?>" name="ids[]">
                                <input type="hidden" value="<?php echo $key['functionality_group_id'] ?>"
                                       name="func_group_ids[]">
                                <select name="permissions[]" class="permissions">
                                    <?php if ($key['write_permissions'] == 't'): ?>
                                        <option value="read"><?php echo $this->translate['userrole2FunctionalityEdit']['permissionReadOnly']; ?></option>
                                        <option value="write" selected="selected"><?php echo $this->translate['userrole2FunctionalityEdit']['permissionWrite']; ?></option>
                                    <?php else: ?>
                                        <option value="read" selected="selected"><?php echo $this->translate['userrole2FunctionalityEdit']['permissionReadOnly']; ?></option>
                                        <option value="write"><?php echo $this->translate['userrole2FunctionalityEdit']['permissionWrite']; ?></option>
                                    <?php endif ?>
                                </select>

                            </td>
                            <td>
                                <button class="buttonFuncGroup" value="<?php echo $key['id'] ?>"><?php echo $this->translate['generalMessages']['btnRemove']; ?></button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
                <div class="options-bar">
                    <br/>
                    <ul class="submenu_ul">
                        <li>
                            <button id="saveChanges" class="sts_submenu W140 disabled" disabled><?php echo $this->translate['generalMessages']['btnSave']; ?></button>
                        </li>
                </div>
        </form>

    </div>
</div>

<div id="dialog" title="Delete Functionality Groups">
    <p>
        <span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
        <?php echo $this->translate['userrole2FunctionalityEdit']['removeMessage']; ?>
    </p>
</div>

<script>
    $(function () {
        $("#dialog").dialog({
            modal: true,
            autoOpen: false,
            buttons: {
                Yes: function () {
                    $(this).dialog("close");
                    // console.log();
                    $.ajax({
                        method: "GET",
                        url: "index.php?action=userrole2Functionality",
                        data: {
                            method: 'ajaxRemoveUserRole2FunctionalityGroup',
                            remove_id: $("#dialog").data('removeFuncGroup'),
                            userroleid: <?php echo $_GET['userroleid']?>
                        }
                    }).done(function () {
                        // console.log("AJAX method");
                        displayInformationBarAfterReload("Remove");
                        location.reload();
                    });
                },
                Cancel: function () {
                    $(this).dialog("close");
                    // conlose.log('cancel');
                    // location.reload();
                }
            }
        });

        $(document).ready(function () {
            showInformationBar();
        });

        $(".buttonFuncGroup").on("click", function () {
            $("#dialog").data('removeFuncGroup', $(this).val()).dialog("open");
            return false;
        });


        $(".permissions").change(function () {
            console.log("button on change");
            $(this).addClass('update');
            $("#saveChanges").removeClass("disabled");
            $("#saveChanges").prop("disabled", false);
        });

        $("#saveChanges").on("click", function (e) {
            e.preventDefault();
            var selectVal = $('select.update');
            console.log(selectVal);

            $.ajax({
                method: "POST",
                url: "index.php?action=userrole2Functionality&method=ajaxUpdatePermissionFunctionalityGroupByID",
                data: $('form').serialize(),
                dataType: "json"
            }).done(function () {
                // console.log("SAVE Ajax function");
                displayInformationBarAfterReload("PermissionChange");
                location.reload();
            });
        });

        $("#assignNewFuncGroup").on("click", function () {
            console.log("pokaz div");
            $("#funcGroupAssign").show();

            $.ajax({
                method: "POST",
                url: "index.php?action=userrole2Functionality&method=ajaxPrepareAssignNewFuncGroupToUserRoleByID",
                data: $("form").serialize(),
                dataType: "json"
            }).done(function (func_group) {
                $.each(func_group, function (key, value) {
                    str = '<option value="' + value.id + '">' + value.name + '</option>';

                    $('.funcGroupSelect').append(str);
                    // alert( key + ": " + value );
                });
                // console.log("zebralismy brakujace func group");
            });

        });

        $(".funcGroupSelect").change(function () {
            // console.log("mozesz zapisac nowa funcgroup");
            $(".permission").show();
            $("#assignFuncGroup").removeClass("disabled");
        });

        $("#assignFuncGroup").on("click", function () {
            $.ajax({
                method: "GET",
                url: "index.php?action=userrole2Functionality",
                data: {
                    method: "ajaxAddAssignNewFunctionalityGroupByID",
                    userroleid: <?php echo $_GET['userroleid'] ?>,
                    funcgroupid: $('.funcGroupSelect option:selected').val(),
                    permission: $('.permission option:selected').val()
                },
                dataType: "json"
            }).done(function () {
                displayInformationBarAfterReload("Add");
                location.reload();
            });
        });
    });

    function displayInformationBarAfterReload(kindOfAction) {
        localStorage.setItem("actionKind", kindOfAction);
    }

    function showInformationBar() {
        hideInfromationBar();
        let messageText = "";

        let kindOfAction = localStorage.getItem("actionKind");
        switch (kindOfAction) {
            case "Add":
                // console.log("Add");
                messageText = "<?php echo $this->translate['userrole2FunctionalityEdit']['msgFGAssigned']; ?>";
                break;

            case "Remove":
                // console.log("Remove");
                messageText = "<?php echo $this->translate['userrole2FunctionalityEdit']['msgFGRemoved']; ?>";

                break;
            case "PermissionChange":
                // console.log("PermissionChange");
                messageText = "<?php echo $this->translate['userrole2FunctionalityEdit']['msgFGmodified']; ?>";
                break;
        }

        localStorage.clear();

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

    function hideInfromationBar() {
        $('#infobox-management').hide();
    }

</script>

<style>
    .success-message {
        padding: 20px;
        background-color: #34a34a;
        color: white;
    }

    .options-bar {
        text-align: center;
    }
</style>