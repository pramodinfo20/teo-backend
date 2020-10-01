<?php
/**
 * chrginfra_ebg.php
 * Template für die Benutzer Rolle chrginfra_ebg
 * @author Pradeep Mohan
 */
?>

<div class="inner_container">
	<div class="row ">
		<div class="columns twelve">
			<span class="error_msg">
		<?php if(is_array($this->msgs)) echo implode('<br>',$this->msgs); ?>
		<br>
		<?php if(is_array($this->csv_stn_msgs)) echo implode('<br>',$this->csv_stn_msgs); ?>
		</span>
		</div>
	</div>
	<?php
if (isset($this->depot) && $this->depot) :
    $depotname = $this->ladeLeitWartePtr->depotsPtr->getNameFromId($this->depot);
    $depots = $this->ladeLeitWartePtr->depotsPtr->getFromId($this->depot);
    ?>
	<div class="row ">
		<div class="columns eight">

			<h1>Ladegruppen : <?php echo $depotname; ?></h1>
				<?php if(isset($this->processedRestrictions)) : ?>
					<div class="row">
				<b> <span class="form_res_id">ID</span> <span class="form_res_name">Name</span>
					<span class="form_res_parent">Obergruppe</span> <span
					class="form_res_power">Absicherung</span></b>
			</div>
			<div class="row">
				<b> <span class="form_res_id">&nbsp;</span> <span
					class="form_res_name">&nbsp;</span> <span class="form_res_parent">ID:
						Name</span> <span class="form_res_power">Ampere</span></b>
			</div>
						<?php

echo $this->processedRestrictions;
						endif;

    ?>
		</div>
	</div>

	<div class="row ">
		<div class="columns twelve">
			<h1>Ladepunkte</h1>
			<span class="error_msg">
				<?php

if (is_array($this->stn_msgs))
        echo implode('<br>', $this->stn_msgs);
    ?>
				</span>
				<?php	if(isset($this->autoGenStations)) : ?>
				<div class="row">
				<div class="twelve columns">
					<?php echo $this->autoGenStations;?><br>
					<br>

				</div>
			</div>
				<?php	endif; ?>
				
				<?php	if(isset($this->processedStations)) : ?>
				<div class="row">
				<b> <span class="form_res_id">ID</span> <span class="form_res_name">Name</span>
					<span class="form_res_parent">Gruppe 1</span> <span
					class="form_res_parent">Gruppe 2</span> <span
					class="form_res_parent">Gruppe 3</span> <span
					class="form_res_power" style="width: 8%; text-align: center">Deaktiviert</span>
					<span class="form_res_power" style="width: 8%">Absicherung</span></b>
			</div>
			<div class="row">
				<b> <span class="form_res_id">&nbsp;</span> <span
					class="form_res_name">&nbsp;</span> <span class="form_res_parent">Name</span>
					<span class="form_res_parent">Name</span> <span
					class="form_res_parent">Name</span> <span class="form_res_power"
					style="width: 8%"></span> <span class="form_res_power"
					style="width: 8%">Ampere</span></b>
			</div>
					<?php

echo $this->processedStations;
					endif; 
				 endif;


    // if depot is set ?>
			
			
		</div>
	</div>

	<?php
if (isset($this->depot) && $this->depot) {
    $this->zspname = $depotname;
    $this->zsp = $depots['depot_id'];

    ?>
		<div class="row ">
		<div class="columns twelve">
			<h1>Übersicht ZSP : <?php echo $this->zspname; ?></h1>
			<p>
				<b>Bitte beachten Sie:</b><br> <b><span class="redcolor">Rot</span>
					markierten Fahrzeuge</b> sind Ersatzfahrzeuge.
		
		</div>
	</div>
	<div class="row">
		<div class="columns twelve">
		  	<?php

    $this->treestructure = $this->ladeLeitWartePtr->restrictionsPtr->generateTreeStructureForDepot($this->zsp);
    if (isset($this->treestructure))
        echo $this->treestructure;
    ?>
			</div>
	</div>
	<?php } ?>

	<?php if(isset($this->qform_csv)): ?>			
	<div class="row ">
		<div class="columns twelve">
			<h1>CSV Datei hochladen</h1>
		
			<?php

echo $this->qform_csv->getContent();

    if ($this->user->getUserRole() == 'chrginfra_ebg') :
        ?>
			<br> Beispiel CSV Datei : <br>
			<br> <span style="color: #999"> ZSP OZ Nummmer,Gruppe 1 Absicherung
				(Ampere), Gruppe2 Absicherung, Gruppe3
				Absicherung,Ladepunkte/Untergruppe, Ladepunkte/Ladegruppe
				Name,Ladegruppe1,Ladegruppe2,Ladegruppe3,Absicherung(in Ampere)<br>
				745533710230,30,30,30,LP,1234567R,Phase 1,,Phase 3,6<br>
				745533710230,30,30,30,GP,Untergruppe1,Phase 1,,,24<br>
				745533710230,30,30,30,LP,5532452L,Untergruppe1,,,16<br>
				745533710230,30,30,30,GP,Untergruppe1_1,Untergruppe1,,,8<br>
				745533710230,30,30,30,LP,8716654L,Untergruppe1_1,,,4<br>
				745533710230,30,30,30,LP,8716654R,Untergruppe1_1,,,4<br>
			</span>
			<?php endif; ?>			
		</div>
	</div>	
	<?php endif; ?>	
	
	<div class="row ">
		<div class="columns eight">
			<h1>Lade Infrastruktur</h1>
			<?php if($this->qform_div) echo $this->qform_div->getContent();?>
			<?php if($this->qform_zspl) echo $this->qform_zspl->getContent();?>
			<?php if($this->qform_depot) echo $this->qform_depot->getContent();?>
		</div>
	</div>
	<div class="row ">
		<div class="columns four" style="min-height: 750px">
			<a href="index.php">Zurück</a>

		</div>
	</div>
</div>
