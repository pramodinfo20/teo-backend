<?php
/**
 * vehiclemgmt.php
 * Template for common function vehicle management
 * @author Pradeep Mohan
 */
?>
<div class="row">
	<div class="columns twelve">
		<h1>ZSP-/Ladepunkten-Zuordnung</h1>
		<p>In der folgenden Übersicht werden den Ladepunkten einer ZSP jeweils
			ein Fahrzeug und ggf. ein weiteres Ersatzfahrzeug zugeordnet, an dem
			die Fahrzeuge geladen werden sollen.</p>

	<?php
/*
 * $handle_sparecars = ! (safe_val ($_REQUEST, 'handle_sparecars', 0) == 0);
 * $checked = $handle_sparecars ? ' checked' : '';
 * $nextval = $handle_sparecars ? 0 : 1;
 * $addclass = $handle_sparecars ? '' : ' inactive_text"';
 * $checkbox = "<input type=\"checkbox\" name=\"handle_sparecars\" value=\"1\" $checked onClick=\"document.location.href='{$_SERVER['PHP_SELF']}?action=fahrzeugverwaltung&zsp=$zsp&handle_sparecars=$nextval';\">";
 * echo "<span class=\"LabelX$addclass\">$checkbox ein weiteres Ersatzfahrzeug</span>";
 */
?>

	<br>
		<strong>Wichtig:</strong> Diese Zuordnung muss seitens der Zusteller
		stets eingehalten werden, da es sonst zu einem Sicherungsausfall des
		Standortes kommen kann. <br>
		<br>
		<strong>Bitte wählen Sie einen ZSPL/ZSP</strong>
			<?php if($this->qform_div) echo $this->qform_div->getContent();?>
			<?php if($this->qform_zspl) echo $this->qform_zspl->getContent();?>
			<?php if($this->qform_depot) echo $this->qform_depot->getContent();?>
			<br>Sie können auch direkt Teile des Namens oder der OZ-Nummer in das
		Feld zum Suchen eingeben. <br>Es werden nur ZSPLn/ZSPn mit
		Ladepunkten/StreetScooter Fahrzeugen angezeigt. <input type='hidden'
			name='action' class='action_get' value='fahrzeugverwaltung'>
			<?php //action get used by the javascript to add to the url that will be called upon selecting a div zsp or zspl?>
	</div>
</div>
<?php	if(isset($this->qform_vehicle_mgmt)): ?>
<div class="row ">
	<div class="columns twelve">
		<h2>Übersicht ZSP : <?php echo $this->zspname; ?> </h2>
		Hier können Sie Fahrzeuge zu anderen ZSPn oder/und Ladepunkte
		verschieben.<br>
		<br>
		<p>
			<b>Bitte beachten Sie:</b><br> <b><span class="redcolor">Rot</span>
				markierten Fahrzeuge</b> sind Ersatzfahrzeuge.<br>
			<br> <span>Für Ersatzfahrzeuge mit <b>(kein Ladepunk)</b> als
				Ladepunkt ist der Status nicht änderbar! <br>Erst wenn sie einem
				Ladepunk zugeordnet sind, ist das Ändern des Status <b>(Ersatzfahrzeuge)</b>
				möglich.
			</span><br>
			<br>
		</p>
		<span class="error_msg">Änderungen werden erst beim Klicken auf <strong>Änderungen
				speichern</strong> übernommen<br>
		<br></span>
	</div>
</div>
<div class="row">
	<div class="columns twelve">
  	<?php

if (isset($this->treestructure))
        echo $this->treestructure;
    ?>
	</div>
</div>
<div class="row">
	<div class="columns six">
		<span class="error_msg">
	<?php

if (! empty($this->debugmsgs))
        echo 'Änderungen werden nicht gespeichert!<br><br>Mögliche Kombination : <br>' . implode('<br>', $this->debugmsgs);
    ?>
		</span>
	</div>
</div>
<div class="row ">
	<div class="columns twelve">

		<?php
    echo $this->qform_vehicle_mgmt->getContent();
    ?>
		<br>
		<br>
		<br>
		<br>
	</div>
</div>
<?php	endif;

if (! empty($this->overview)) :
    ?>
<div class="row ">
	<div class="columns twelve">
		<?php $this->overview->printContent();?>
	</div>
</div>
<?php endif; ?>

