<?php
/**
 * Created by PhpStorm.
 * User: Jakub Kotlorz, FEV
 * Date: 2/1/19
 * Time: 10:37 AM
 */
?>

<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>
<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>
<div class="mainframe">


    <div class="select">
        <?php require_once($_SERVER['STS_ROOT'] . '/pages/common/vehicle-configuration-selection.php'); ?>
    </div>
    <div class="row page">

        <div class="seitenteiler">
            <form>
                <div id="result_page" class="seitenteiler flexTop scrollboxY"
                     style="justify-content: space-between"></div>
            </form>
        </div>
        <div id="operation_buttons" class="variantPartsButtons border-buttons" style="display: none">
            <div class="MiniButtons">
                <ul class="submenu_ul">
                    <li><span id="id_save_variant" class="sts_submenu W140" style="display:none;">Save</span></li>
                    <li><span id="id_cancel_variant" class="sts_submenu W140" style="display:none;">Cancel</span></li>
                    <li><span id="id_edit_variant" class="sts_submenu W140">Edit</span></li>
                    <li><span id="id_delete_variant" class="sts_submenu W140">Delete</span></li>
                    <li><span id="id_create_variant" class="sts_submenu W140">Create new</span></li>
                    <li><span id="id_create_variant_via_copy" class="sts_submenu W140">Create via Copy</span></li>
                    <li><span id="id_export_variant" class="sts_submenu W140 disabled">Export variant</span></li>
                </ul>
            </div>
        </div>

    </div>

</div>


<script>

    $('#id_cancel_variant').on('click', function () {
        console.log("Cancel operation on VC");

        $('#id_edit_variant').show();
        $('#id_delete_variant').show();
        $('#id_create_variant').show();
        $('#id_create_variant_via_copy').show();
        $('#id_export_variant').show();
        $('#id_save_variant').hide();
        $('#id_cancel_variant').hide();

        let selectedConfiguration = $('#sel_search_result_list').val();
        console.log(selectedConfiguration);
        executeSelectedOperationOnVC(selectedConfiguration, '0');
    });

    $('#id_save_variant').on('click', function () {
        console.log("Save VC");

        $('#id_edit_variant').hide();
        $('#id_delete_variant').hide();
        $('#id_create_variant').hide();
        $('#id_create_variant_via_copy').hide();
        $('#id_export_variant').hide();
        $('#id_save_variant').show();
        $('#id_cancel_variant').show();

        saveVehicleConfiguration();
    });

    $('#id_edit_variant').on('click', function () {
        console.log("Edit VC");

        $('#id_edit_variant').hide();
        $('#id_delete_variant').hide();
        $('#id_create_variant').hide();
        $('#id_create_variant_via_copy').hide();
        $('#id_export_variant').hide();
        $('#id_save_variant').show();
        $('#id_cancel_variant').show();

        let selectedConfiguration = $('#sel_search_result_list').val();
        console.log(selectedConfiguration);
        executeSelectedOperationOnVC(selectedConfiguration, "1");
        // executeSelectedOperationOnVC(selectedConfiguration, "1");
        displayEditView(selectedConfiguration);
    });

    $('#btn_select_vehicle_configuration').on('click', function () {
        if ($(this).hasClass('disabled'))
            return false;

        let selectedConfiguration = $('#sel_search_result_list').val();

        // todo: Remove hardcoded
        selectVehicleConfiguration('1');
    });

    $('#id_delete_variant').on('click', function () {
        console.log("Remove VC");
    });

    $('#id_create_variant').on('click', function () {
        console.log("Create new VC");
    });

    $('#id_create_variant_via_copy').on('click', function () {
        console.log("Create new VC via copy");
    });

    function executeSelectedOperationOnVC(selectedConfiguration, operationType) {
        $.ajax({
            method: "GET",
            url: "index.php?action=vehicleConfiguration",
            data: {
                method: 'ajaxExecuteSelectedOperation',
                selectedConfiguration: selectedConfiguration,
                operationType: operationType
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                $('#result_page').children().remove();
                $('#result_page').html(ajaxResultData);

                $('#operation_buttons').show();
            });
    }

    function selectVehicleConfiguration(selectedConfigurationId) {
        $.ajax({
            method: "GET",
            url: "index.php?action=vehicleConfiguration",
            data: {
                method: 'ajaxExecuteSelectedOperation',
                selectedConfigurationId: selectedConfigurationId,
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                $('#result_page').children().remove();
                $('#result_page').html(ajaxResultData);

                // $('#operation_buttons').show();
            });
    }

    function saveVehicleConfiguration() {
        $.ajax({
            method: "POST",
            url: "index.php?action=vehicleConfiguration&method=ajaxUpdateCurrentVehicleConfiguration",
            data: $('form').serialize(),
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                console.log('Configuration saved');

                // executeSelectedOperationOnVC( obecna konfiguracja ,0);
            });
    }

    function displayEditView(selectedConfiguration) {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetEditView',
                selectedConfiguration: selectedConfiguration
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                $('#result_page').children().remove();
                $('#result_page').html(ajaxResultData);

                $('#operation_buttons').show();
            });
    }

</script>

<style>
    .seitenteiler {
        padding-bottom: 10px;
        padding-top: 15px;

    }

    .border-buttons {
        border: 1px solid #888888;
    }

    .page {
        padding-left: 10px;
        width: 100%;
    }

</style>