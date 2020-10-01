<div class="row ">
    <div class="columns twelve">
        <h1>Tägliche Statistiken</h1>
        <br>
    </div>
</div>
<?php
if (isset($vin) || isset($kennzeichen)) {
    ?>
    <div class="row ">
    <div class="columns twelve" style="min-height: 600px">
    <?php
    $vehicle = "";


    if (isset($vin) && $vin)
        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getWhere(array("vehicle_id", "vin", "code"),
            array(
                array('vin', ' LIKE ', "%" . $vin . "%")
            ));
    elseif (isset($kennzeichen) && $kennzeichen)
        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getWhere(array("vehicle_id", "vin", "code"),
            array(
                array('code', ' LIKE ', "%" . $kennzeichen . "%")
            ));


    // 			 	WS5B14AA3FA100532

    if (!empty($vehicle)) {
        if ($vehicle[0]['code'])
            $knum = $vehicle[0]['code'];
        else
            $knum = "";

        echo "Daten des Fahrzeugs : <b>" . $vehicle[0]['vin'] . "</b><br><br>";

        $csv_fname = "daily_stats_" . $vehicle[0]['vin'] . '_' . date('Y-m-j_H-i-s');
        echo '<a href="/downloadcsv.php?fname=' . $csv_fname . '">Als CSV Datei herunterladen</a><br>';;
        $csvstring = '';

        if ($knum) echo "Kennzeichen : " . $knum . "<br><br>";
        $query = "SELECT * FROM daily_stats WHERE vehicle_id=" . $vehicle[0]['vehicle_id'] . " ";

        $result = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($query);

        if (!empty($result)) {
            echo "<div class=\"dailystats_wrapper\" style=\"position:relative; height: 550px; overflow-y: auto\"><table id=\"dailystats\"><thead><tr>";

            foreach (array_keys($result[0]) as $value) {
                if ($value == 'date') $minwidth = ' style="min-width: 50px "';
                else $minwidth = '';
                echo "<th " . $minwidth . " >" . $value . "</th>";

            }

            echo '</tr></thead>';
            $csvstring = implode(',', array_keys($result[0])) . "\r\n";

            foreach ($result as $theobject) {
                $csvstring .= implode(',', $theobject) . "\r\n";
                echo "<tr>";
                $rowdata = $theobject;

                foreach ($rowdata as $key => $value) {
                    if ($key == 'date')
                        echo '<td><a href="#" class="show_daily_stats" data-vin="' . $vehicle[0]['vin'] . '" >' . $value . "</a></td>";
                    else  echo "<td>" . $value . "</td>";
                }

                echo "</tr>";
            }
            echo "</table><br><br></div>";

            $fname = "/tmp/$csv_fname.csv";
            $fhandle = fopen($fname, "w");
            fwrite($fhandle, $csvstring);
            fclose($fhandle);


        } else {
            echo "Keine Daten gefunden!";
        }
        ?>
        </div>
        </div>
        <?php
    } else {
        echo "Keine Daten gefunden!";
    }

} else {
    ?>
    <div class="row humbug">
        <div class="columns eight" style="min-height: 600px">
            <?php
            $query = "";

            //							$vehicles=$this->ladeLeitWartePtr->vehicles->getAll(array("vehicle_id","vin"));

            $vehicles = $this->ladeLeitWartePtr->dailyStatsPtr->getStatsForAllVehicles();
            $prvehicles[] = array('headingone' => array(array("VIN", ""), array("Kennzeichen", "")));

            // $container=new DisplayContainer();
            if ($vehicles) {
                foreach ($vehicles as $vehicle) {
                    $vlink = '<a href="?action=' . $this->action . '&vin=' . trim($vehicle["vin"]) . '" >' . $vehicle["vin"] . '</a>';
                    $clink = '<a href="?action=' . $this->action . '&vin=' . trim($vehicle["vin"]) . '" >' . $vehicle["code"] . '</a>';
                    $prvehicles[] = array($vlink, $clink);

                }

                $displaytable = new DisplayTable ($prvehicles);
                echo $displaytable->getContent();
            } else
                echo 'Keine Daily Stats gefunden!'

            ?>
        </div>
        <div class="columns four">
            <?php
            echo $formprint;
            ?>
        </div>
    </div>
    <?php
}
