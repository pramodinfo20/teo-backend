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
        <div id="search_window" class="seitenteiler">
            <fieldset class="">
                <legend class="collapsible"><span class="genericon genericon-expand"></span>Select ECU
                </legend>
                <!--            <div id="search_window_collapsible" class="collapsible_content">-->
                <div id="search_window_collapsible" class="">
                    <div class="" style="">
                        <label for="ecu_result_list">ECU</label><br>
                        <select id="ecu_result_list" size="5" style="width: 100%"></select>
                    </div>

                    <div class="options-bar">
                        <ul class="submenu_ul">
                            <li><span id="btn_select_ecu" class="sts_submenu W140 disabled">Select ECU</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </fieldset>
        </div>
    </div>
    <div class="row page">
        <div> <?php echo $this->view ?></div>
        <div class="seitenteiler">
            <form>
                <div id="result_page" class="seitenteiler flexTop scrollboxY"
                     style="justify-content: space-between"></div>
            </form>
        </div>

    </div>

</div>

<script>

    $(document).ready(function () {
        //Remove select button - prevent load content
        $('#btn_select_vehicle_configuration').parentsUntil('.options-bar').remove();


        $('#sel_search_result_list').on('change', function () {
            $('#ecu_result_list option').remove();

            $.ajax({
                method: 'GET',
                data: {sub_configuration_id: $(this).val()},
                url: "index.php?action=parameterSettings&call=getSupportedECU",
                success: function (result) {
                    let data = JSON.parse(result);
                    console.log(data);
                    for (let i = 0; i < data.length; ++i) {
                        $('#ecu_result_list').append('<option value="' + data[i]['ecuId'] + '" ' +
                            'data_subConfigurationId="' + data[i]['subConfiguration'] + '">' + data[i]['name'] + '</option>');
                    }
                }
            });
        });

        $('#ecu_result_list').on('change', function (event) {
            $('#btn_select_ecu').removeClass('disabled');
        });


        $('#btn_select_ecu').on('click', function (event) {
            if ($(this).hasClass('disabled'))
                return false;
            $.ajax({
                method: 'GET',
                data: {vehicle_configuration_id: 1},
                url: "index.php?action=parameterSettings&call=prepareView",
                success: function (result) {
                    $("#result_page").append(result);
                }
            });
        });

    });


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