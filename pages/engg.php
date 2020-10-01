<?php
/**
 * aftersales.php temporarily engineering
 * Template für die Benutzer Rolle Aftersales
 * @author Pradeep Mohan
 */

$div = $this->requestPtr->getProperty('div');
$zspl = $this->requestPtr->getProperty('zspl');
$depot = $this->requestPtr->getProperty('depot');
$msgs = '';


$displayheader = $this->displayHeader;
//$qform_fr = new QuickformHelper ($displayheader, "zspl_upload_form");
//$qform_fr->add_fahrzeug_suchen();
//$formprint = $qform_fr->getContent();


$vin = $this->requestPtr->getProperty('vin');
$kennzeichen = $this->requestPtr->getProperty('kennzeichen');

echo '<div class="inner_container">';
include $_SERVER['STS_ROOT'] . '/pages/menu/engg.menu.php';

if (is_array($msgs)) {
    echo '<div class="row ">
                    <div class="columns six">' . implode('<br>', $msgs) . '</div>
              </div>';
}

if (isset($this->action) && $this->action == 'produzierte') {
    ?>
    <h1>Liste produzierter Fahrzeuge</h1>
    <br>
    <div class="row ">
        <div class="columns twelve">
            <?php
            $headings[0]['headingone'] = array('VIN', 'Datum');
            $table = new DisplayTable(array_merge($headings, $this->producedVehicles), array('id' => 'sort_filter_table'));
            echo $table->getContent();
            ?>
        </div>
    </div>
    <?php
} else if (isset($this->action) && $this->action == 'ausgelieferte') {
    ?>
    <div class="row ">
        <div class="columns twelve">
            <h1>Liste ausgelieferter Fahrzeuge</h1>
            <br>
            <?php
            $headings[0]['headingone'] = array('Datenbank ID', 'VIN', 'AKZ', 'Anlieferungsdatum');
            $table = new DisplayTable(array_merge($headings, $this->deliveredVehicles), array('id' => 'sort_filter_table'));
            echo $table->getContent();
            ?>
        </div>
    </div>
    <?php
} else if (isset($this->action) && $this->action == 'verlaufsdaten') {
    include $_SERVER['STS_ROOT'] . "/actions/html/Form@Engg_Verlaufsdaten.php";
} else if (isset($this->action) && $this->action == 'tagesstatistik') {
    include $_SERVER['STS_ROOT'] . "/actions/html/Form@Engg_Tagesstatistik.php";
} else if (isset($this->action) && $this->action == 'vehiclebooking') {
    include $_SERVER['STS_ROOT'] . "/actions/html/booking_vehicles.php";
} else if (isset($this->action) && $this->action == 'documentvehicles') {
    include $_SERVER['STS_ROOT'] . "/actions/html/document_vehicles.php";
} else if (isset($this->action) && $this->action == 'specialapp') {
    ?>
    <h1>Fahrzeug Sondergenehmigung</h1>
    <div class="row ">
        <div class="columns twelve">
            <?php echo $this->specialapp_content; ?>
        </div>
    </div>
    <?php
}
?>
<div id="dialog-form"></div>
</div>
