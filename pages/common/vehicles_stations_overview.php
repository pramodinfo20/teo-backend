<?php
/*** vehicles_stations_overview.php
 * Template for Vehicles and Stations Overview pro NL,ZSPL or ZSP
 * @author Pradeep Mohan
 */
?>

<?php	if(isset($this->qform_vehicle_mgmt)): ?>
<div class="row ">
	<div class="columns twelve">
		<h2>Übersicht ZSP : <?php echo $this->zspname; ?> </h2>
		Hier können Sie Fahrzeuge zu anderen ZSPn oder/und Ladepunkte
		verschieben.<br>
		<br> <span class="error_msg">Änderungen werden erst beim Klicken auf <strong>Änderungen
				speichern</strong> übernommen<br>
		<br></span>
		<?php	echo $this->qform_vehicle_mgmt->getContent();?>
		<br>
		<br>
		<br>
		<br>
	</div>
</div>
<?php	endif; ?>

<?php	if(isset($this->vehiclesAndStations)): ?>
<div class="row ">
	<div class="columns twelve">
		<h1><?php echo $this->overviewHeading; ?> </h1>
		<?php

if (! $this->vehiclesAndStations) {
        echo 'Keine Fahrzeuge an diesem ZSP zugeordnet!';
    } else {

        $processed_listObjects[] = array(
            'headingone' => array(
                'Fahrzeug',
                'Ladepunkt',
                'Anlieferungsdatum'
            )
        );

        foreach ($this->vehiclesAndStations as $vehicle) {
            if (! empty($vehicle['delivery_date']))
                $vehicle['delivery_date'] = date('d.m.Y', strtotime($vehicle['delivery_date']));
            else
                $vehicle['delivery_date'] = '';
            $processed_listObjects[] = array(
                $vehicle['code'],
                $vehicle['sname'],
                $vehicle['delivery_date']
            );
        }

        $displaytable = new DisplayTable($processed_listObjects, array(
            'id' => 'zentralelist'
        ));
        echo $displaytable->getContent();
    }
    ?>
	</div>
</div>
<?php	endif; ?>
<?php if(!empty($this->overview)): ?>
<div class="row ">
	<div class="columns twelve">
	<?php
    echo "
<h1>{$this->overviewHeading}</h1>
{$this->HeadingInfo}
{$this->overview}";
    ?>
	</div>
</div>
<?php endif; ?>

<div class="row ">
	<div class="columns twelve">
		<!--  <h3>Summe der eingetragenen Ladepunkten (EBG / AixACCT / andere): <?php echo $this->stationstr; ?></h3>  -->
		<!--  <h3>Ladepunkte zugewiesene Fahrzeuge (Anteil): <?php echo $this->ratioAssignedVehicles; ?> </h3>  -->
		<?php
if ($this->showSupportEmails) {
    if ($this->sumTotalES)
        echo '<br>- Bitte melden Sie fehlerhaft eingetragene oder fehlende Ladepunkte an: EBG – Compleo (dpdhl@ebg-compleo.de)';
    if ($this->sumTotalAS)
        echo '<br>- Bitte melden Sie fehlerhaft eingetragene oder fehlende Ladepunkte an: Jochen Olivier - olivier@aixmec.com';
    if ($this->sumTotalS - ($this->sumTotalES + $this->sumTotalAS))
        echo '<br>- Bitte melden Sie fehlerhaft eingetragene Schuko-Steckdosen (Schukosäulen) an Jens Frangenheim - Jens.Frangenheim@streetscooter.eu';
}

		?>
	</div>
</div>
