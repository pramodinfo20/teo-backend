<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 2/26/19
 * Time: 1:59 PM
 */


include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
//echo 'haha';
?>
<!---->
<!--<div class="inner_container"></div>-->
<?php //if (is_array($this->msgs)) { ?>
<!--  <div class="row ">-->
<!--    <div class="columns six">-->
<!--      --><?php //echo implode('<br>', $this->msgs); ?>
<!--    </div>-->
<!--  </div>-->
<?php //}; ?>

<div class="row">
    <?php require_once($_SERVER['STS_ROOT'] . '/pages/common/ecusw-selection.php'); ?>
    <div class="columns eight">
        <div id="info-management" class="row"></div>

        <h1>ECU software diagnostic value setting</h1>

        <div class="row" id="supportECU">
            <?php
            $selected_id = $_POST["ecuVersion"]["selectedEcu"];
            $selected_ecu = $this->availableECUs[$selected_id - 1];
            //                echo var_dump($selected_ecu);
            ?>
            <h4>You selected: <?php echo $selected_ecu['name'] ?></h4>
            <p>Supports .odx2 for chosen ECU</p>
            <form method="post" action="index.php?action=diagSwValSet"
            <fieldset id="supportFlag">
                <?php if ($selected_ecu['supports_odx02'] == 't'):
                    echo '<label class="supportFlag">TRUE<input type="radio" name="supportFlag" value="true" checked="checked"></label>';
                    echo '<label class="supportFlag">FALSE<input type="radio" name="supportFlag" value="false"></label>';
                else:
                    echo '<label class="supportFlag">TRUE<input type="radio" name="supportFlag" value="true"></label>';
                    echo '<label class="supportFlag">FALSE<input type="radio" name="supportFlag" value="false" checked="checked"></label>';
                endif ?>
            </fieldset>
            </form>
        </div>
        <div class="row">
            <ul class="submenu_ul">
                <li><span id="saveSupportODX" class="sts_submenu W140" style="display:none">Save</span></li>
        </div>
    </div>
</div>

<script>
    $('#saveSupportODX').on("click", function () {
        // location.reload();
        $('#info-management')
            .addClass('success-message')
            .html("<div>Diagnostic value setting - flag 'Supports_ODX2' has been <strong>updated</strong>!</div>")
            .show();
        $('#saveSupportODX').toggle();

        $.ajax({
            method: "POST",
            url: "index.php?action=diagSwValSet&method=ajaxSaveSupportODX2FlagToECU",
            data: $('form').serialize(),
            datatype: 'json'
        }).done(function () {
            console.log("Save support_ODX2 update");
        });
    });

    $('#list_of_ecus').on("change", function () {
        $('#sw_versions_list').remove();
        $('#sw_versions_header').remove();
        console.log($('#list_of_ecus').val());

    });

    $('#list_of_ecus option').click(function () {
        $("#ecus_swversions_form").submit();

        $('#info-management')
            .removeClass('success-message')
            .html("<div></div>")
            .hide();
    });

    $('#supportFlag').on("change", function () {
        // console.log('change');
        $('#info-management').hide();
        $('#saveSupportODX').toggle();
    });

</script>

<style>
    .success-message {
        padding: 20px;
        background-color: #34a34a;
        color: white;
    }
</style>