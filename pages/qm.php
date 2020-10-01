<?php
?>
<div class="inner_container">
    <?php
    include $_SERVER['STS_ROOT'] . "/pages/menu/qm.menu.php";

    if ($this->action == 'schraubdaten_admin') {

        if (!empty($this->msgs)) {
            print $this->msgs[0];
        }
        if ($table = $this->screwdata->getnewdata()) {
            echo <<<CONFIRM
        <form action="{$_SERVER['PHP_SELF']}" method="post">
    		Wollen Sie diese Daten speichern?<input type="submit" name="save" value="Speichern">
            <input type="submit" name="discard" value="Verwerfen">
            <input type="hidden" name="action" value="{$this->action}">
    	</form>
CONFIRM;
            $count = 0;
            $count = count($table);
            echo "<div style= \"overflow-x: scroll; overflow-y:visible; width: 1500px;\"><div style=\"width:7000px; overflow:visible\">";


            $this->sticktable->WriteHtml_Content(array_slice($table, 1, $count - 1), $table[0]);

            echo "</div></div>";
        } else {
            echo <<<HEREDOC
    	<form action="{$_SERVER['PHP_SELF']}" method="post" enctype="multipart/form-data">
    	Schraubdaten:	<input type="file" name="datencsv"><br>
    		<input type="submit" value="Hochladen">
            <input type="hidden" name="action" value="{$this->action}">
    	</form>
HEREDOC;
        }
    } else if ($this->action == 'schraubdaten_messung') {

        if (!empty($this->msgs)) {
            print $this->msgs[0];
        }
        if ($dropdown = $this->mesurements->getdropdown()) { //holt sich ein array dass das dropdown befüllt und prüft somit ob schraubinformationen vorhanden sind
            echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"post\">";
            echo 'SchraubenID:';
            echo '<select name="screw_id">';
            foreach ($dropdown as $value) {
                echo '<option value="' . $value['screw_id'] . '"';
                if ($_POST['screw_id'] == $value['screw_id']) {
                    echo "selected";
                }
                echo '>' . $value['screw_id'] . '</option>';
            }
            echo '</select>';
            echo <<<HEREDOC
            Anzahl der letzten Messungen: <input type="number" name="max" min=1 max=100>
            <input type="submit" value="filtern">
            <input type="hidden" name="action" value="{$this->action}">
	    </form>
        
HEREDOC;
        }
        if ($table = $this->mesurements->getnewdata()) {

            echo "<div style= \"overflow-x: scroll; overflow-y:visible; width:1500;\"><div style=\"width:7000; overflow:visible\">";


            $table = $this->mesurements->filter($table);
            $this->sticktable->WriteHtml_Content(array_slice($table, 1, count($table) - 1), $table[0]);

            echo "</div></div>";
            echo <<<CONFIRM
        <form action="{$_SERVER['PHP_SELF']}" method="post">
    		Wollen Sie diese Daten speichern?<input type="submit" name="save" value="Speichern">
            <input type="submit" name="discard" value="Verwerfen">
            <input type="hidden" name="action" value="{$this->action}">
    	</form>
CONFIRM;
        } else {


            if (!empty($table = $this->mesurements->getolddata())) {
                $mess = $this->mesurements->getmessungshead();
                echo '<div style="margin: 20px">';
                echo "<div style= \"overflow-x: scroll; overflow-y:visible; width:4000;\"><div style=\"width:4000; overflow:visible\">";
                $this->sticktable->WriteHtml_Content($table, $mess);
                echo '</div><div class="seitenteiler" style="margin:20px; height:auto">';
                echo '<img src="bild.svg" style="width: 800px"></div>';


            }
            echo '<div class="seitenteiler" style="margin:20px">';
            echo "Mittel: " . $mittel = $this->mesurements->mittelwerttorque($table);
            echo "<br>";
            echo "Standardabweichung: " . $standabw = $this->mesurements->standardabwtorque($table, $mittel);
            echo "<br>";
            echo "cp: " . $this->mesurements->cptorque($this->mesurements->otg, $this->mesurements->utg, $standabw);
            echo "<br><br>";
            echo <<<HEREDOC
	        
        
    	<form action="{$_SERVER['PHP_SELF']}" method="post" enctype="multipart/form-data">
    	Neue Messdaten:	<input type="file" name="messungcsv"><br>
    		<input type="submit" value="Hochladen">
            <input type="hidden" name="action" value="{$this->action}">
    	</form>
        </div>
HEREDOC;


        }
    } else if ($this->action == 'schraubdaten_uebersicht') {
        if ($table = $this->mesurements->getoverview()) {
            $this->sticktable->WriteHtml_Content($table, $this->mesurements->getuebersicht());
        }
    } else {

        ?>
        <div class="row ">
            <div class="columns six">
                <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
            </div>
        </div>
        <div class="row ">
            <div id="qs_fehler_wrap" class="columns twelve">
                <div>
                    <?php if (isset($this->qs_fault_search)) echo $this->qs_fault_search->getHtml(); ?>
                </div>
            </div>
            <div class="columns twelve">
                <?php echo $this->depotFilterContent; ?>
            </div>
        </div>
        <div class="row ">
            <div class="columns twelve">
                <?php
                // 			echo $this->qform_vehicles->getContent();
                $pageoptions = '
			<div class="pager">
			<a href="#" class="first"  title="Erste Seite" >Erste</a>
			<a href="#" class="prev"  title="Vorherige Seite" ><span class="genericon genericon-previous"></span>Vorherige Seite</a>
			<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
			<a href="#" class="next" title="Nächste Seite" >Nächste Seite<span class="genericon genericon-next"></span></a>
			<a href="#" class="last"  title="Letzte Seite" >Letzte</a>

			Seite: <select class="gotoPage"></select>
			Zeile pro Seite: <select class="pagesize">
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="300">300</option>
			</select>
			</div>';
                echo $pageoptions;
                ?>
                <div class="quickform">
                    <form method="post" id="vehicle_fertig_status" action="index.php" novalidate="novalidate">
                        <input type="hidden" name="todays_vehicles" id="todays_vehicles-0"
                               value="<?php echo implode(',', $this->todays_vehicles) ?>">
                        <input type="hidden" name="to_lock_vehicles" id="to_lock_vehicles" value="">
                        <input type="hidden" name="to_unlock_vehicles" id="to_unlock_vehicles" value="">
                        <input type="hidden" id="qs_qm_action" name="action" value="saveQM">
                        <div style="overflow-y:auto; height: 450px; position:relative;" class="wrapper">
                            <?php echo $this->qs_vehicles->getContent(); ?>
                        </div>
                        <fieldset class="row">
                            <fieldset class="columns four inline_elements" id="qfauto-3966">
                                <div class="row">
                                    <div class="element">
                                        <input type="submit" value="Speichern" style="float: right; margin: 4px"
                                               name="">
                                    </div>
                                </div>
                            </fieldset>
                        </fieldset>
                    </form>
                </div>
                <?php echo $pageoptions; ?>
            </div>
        </div>
    <?php } ?>
</div>
<div id="dialog-form"></div>