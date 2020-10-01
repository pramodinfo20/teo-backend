<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>
<div class="inner_container"></div>
<?php if (is_array($this->msgs)) { ?>
    <div class="row ">
    <div class="columns six">
        <?php echo implode('<br>', $this->msgs); ?>
    </div>
    </div><?php }; ?>

<h1 id="ecu_sw_configuration_header">ECU property settings</h1>
<div class="row">

    <?php require_once($_SERVER['STS_ROOT'] . '/pages/common/ecusw-selection.php'); ?>

    <div class="columns ten">
        <div class="row">
        </div>
        <div id="infobox-ecu" class="row"></div>
        <div id="ecu_sw_configuration_content" class="row"></div>

        <div class="row">
            <?php require_once($_SERVER['STS_ROOT'] . '/pages/common/ecusw-actions.php'); ?>
        </div>
    </div>
</div>
