<h1>Verlaufsdaten</h1>
<br>
<div class="row ">
    <div class="columns twelve">
<!--        <h2 style="margin: 0.4em 0">Verlaufsdaten Abruf</h2>-->
        <?php
        // (Coming Soon) - Beachten : Verlaufsdaten müssen verübergehend noch mit Tool C2C-View angesehn werden. Später Integration hier.
        if (isset($_POST['vid']) && isset($_POST['start_timestamp_selector'])) {
            $ts_start = $this->requestPtr->getProperty('start_timestamp_selector');
            $ts_end = $this->requestPtr->getProperty('end_timestamp_selector');
            $vehicle_id = $this->requestPtr->getProperty('vid');
            $c2cboxid = $this->ladeLeitWartePtr->vehiclesPtr->getC2CBoxIdFromId($vehicle_id);

            preg_match('/(sts-c2cbox)(.*)/', $c2cboxid, $matches);
            $tcpquery = $this->user->getUserEmail() . "/100/" . strtotime($ts_start) . ";" . strtotime($ts_end) . ";" . $matches[2] . "/end";
            if ($_POST['vehicle_online_status'] == 0)
                echo '<br>Zum Fahrzeug besteht im Moment keine Verbindung. Sobald die gewünschten Daten Abfrage rufen werden konnten, erhalten Sie eine E-Mail<br>';

            $fp = fsockopen("10.12.54.173", 3422, $errno, $errstr, 30);
            if (!$fp) {
                echo "$errstr ($errno)<br />\n";
            } else {
                $out = $tcpquery;
                fwrite($fp, $out);
                fclose($fp);
            }
            echo "<br><a href=\"{$_SERVER['PHP_SELF']}?action={$this->action}\" > Zurück</a><br>";
        } else {
            echo <<<HERE_______________________________________DOC
			Bitte wählen Sie ein Fahrzeug aus der Liste aus, oder tippen Sie etwas in das Feld ein, um nach VIN oder Kennzeichen zu suchen. <br><br>
            <form action='' method='post'>
			  <label style="display: inline-block; margin-right: 20px;">Fahrzeug wählen</label>
              <input type="text" id="vehicle_search_new" name="vehicle_search_new" data-targetinput="vid">
              <a href="#" style="margin-left: 8px;" id="reset_vehicle_search_new"><span class="genericon genericon-close"></span>Leeren</a>
              <input type="hidden" name="vid" id="vid">
			  <label style="display: inline-block; margin-left:70px">Start Zeitpunkt</label><input type="text" id="start_timestamp_selector" class="timestamp_selector" name="start_timestamp_selector" style="display: inline-block; margin-left:20px" >
			  <label style="display: inline-block; margin-left:20px">End Zeitpunkt</label><input type="text" id="end_timestamp_selector" class="timestamp_selector" name="end_timestamp_selector" style="display: inline-block; margin-left:20px" >
              <input type="hidden" id="vehicle_online_status" name="vehicle_online_status" value="1" >
			  <input type="submit" name="sendtcp" value="Abfragen" style="display: inline-block; margin-left:20px">              

			</form>
HERE_______________________________________DOC;
        }

        ?>
    </div>
</div>
<?php

if ($this->user->user_can('grafana_view')) {
    ?>
    <iframe src="grafana/d/BIftw5Wmz/verlaufsdaten" width="100%"
            height="100%" style="min-height: 800px"></iframe>
    <?php
}
?>


