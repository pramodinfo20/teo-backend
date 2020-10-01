<?php
/**
 * fpv.php
 * Template für die Benutzer Rolle Fuhrparkverwalters
 * @author Pradeep Mohan
 */
?>

<div class="inner_container" style="min-height:700px">


    <?php
    include "{$_SERVER['STS_ROOT']}/pages/menu/fpv.menu.php";

    if (is_array($this->msgs)):?>
        <div class="row ">
            <div class="columns twelve">
		<span class="error_msg">
		 <?php echo '<br><br>' . implode('<br>', $this->msgs); ?>
		</span>
            </div>
        </div>
    <?php endif; ?>


    <?php
    if (isset($this->action) && $this->action == "fahrzeugverwaltung"):
        echo $this->commonVehicleMgmtPtr->printContent();

    elseif (isset($this->action) && $this->action == "depotprop"): ?>
        <div class="row ">
            <div class="columns eight">
                <h1>ZSP-Eigenschaften</h1>
                <br> In der folgenden Übersicht kann ein späterer Ladebeginn der Fahrzeuge eingestellt werden. Die
                Fahrzeuge laden dann erst zu der eingestellten Uhrzeit mit voller Leistung. Bis dahin können die
                Fahrzeuge
                mit maximal halber Maximal-Ladeleistung laden.
                Dies ist notwendig, sofern Elektrofahrzeuge anderer Hersteller (nicht StreetScooter) am Standort
                eingesetzt werden, um eine Überlastung des Stromanschlusses zu verhindern.
                <br>Empfehlung: Sofern Elektrofahrzeuge anderer Hersteller am ZSP eingesetzt sind, stellen Sie die
                Uhrzeit
                am gesamten ZSP auf 20:00 h ein.

                <br><br>
                <?php echo $this->commonDepotPropPtr->printContent();
                if (isset($this->qform_depotprop)):
                    ?><h1>ZSP Eigenschaften</h1><?php
                    echo $this->qform_depotprop->getContent();
                endif;
                ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "flottenmonitor"): ?>
        <div class="row ">
            <div class="columns eight">
                <h1>Flottenmonitoring</h1>
                In dieser Übersicht können einige Statistiken der Fahrzeuge abgerufen werden. Der Umfang wird in Zukunft
                ausgebaut.
                <br>
                <?php

                echo $this->commonOZPtr->printContent();

                $week = $this->commonOZPtr->getLastOrCurrentWeek();
                $woche = ($week == 'last') ? 'letzten' : 'laufenden';

                if (isset($this->flottenmonitoringcontent)):
                    ?>
                    <h2>Zurückgelegte Strecke aller StreetScooter Fahrzeuge
                        der <?php echo "$woche Woche: " . number_format($this->flottenmonitoringcontent[0]['distance_this_week'], 2, ',', '.'); ?>
                        km</h2>
                    Liste der Funktion wird in Zukunft ausgebaut.
                <?php
                endif;
                ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "abfahrtszeit"): ?>
        <div class="row ">
            <div class="columns eight">
                <h1>Abfahrtszeiten</h1>
                <br>In der folgenden Übersicht kann die morgendliche Abfahrtszeit jedes Fahrzeuges (bzw. des ganzen
                ZSPs)
                eingestellt werden.
                <br>Die Abfahrtszeit ist entscheidend für die Vorkonditionierung der Fahrzeuge. Diese schaltet sich bei
                niedrigen Temperaturen für an der Ladepunkte eingesteckten Fahrzeugen automatisch ein, sodass diese zur
                eingestellten Uhrzeit aufgeheizt sind. Das heißt: Der Zusteller muss im Winter nicht mehr kratzen und
                startet seine Zustelltour in einem angenehm warmen Fahrzeug ohne, dass die Reichweite darunter
                leidet!<br><br>
                Bitte beachten Sie, dass sich die eingestellten Abfahrtszeiten nicht nur auf den Zeitpunkt der
                Vorkonditionierung sondern auch auf den Ladevorgang auswirken. Die Ladung eines Fahrzeug wird (mit
                geringem Sicherheitspuffer) erst zur eingestellten Abfahrtszeit sichergestellt.<br>
            </div>
        </div>
        <div class="row ">
            <div class="columns ten">
                <?php echo $this->commonOZPtr->printContent();

                if (isset($this->vehicles)):
                    echo '<h2>Fahrzeuge - Abfahrtszeiten</h2>';

                    $dayheadings = array('mon' => 'Montag', 'tue' => 'Dienstag', 'wed' => 'Mittwoch', 'thu' => 'Donnerstag', 'fri' => 'Freitag', 'sat' => 'Samstag'); //,'sun'=>'Sonntag'
                    //show vehicles as well as the departure times when a ZSP is selected
                    $proVehicles = array(array('headingone' => array_merge(array('VIN / AKZ', 'Ladepunkte'), $dayheadings)));
                    foreach ($this->vehicles as $vehicle) {
                        $proVehicles[] = array('<a href="?action=abfahrtszeit&zsp=' . $vehicle['depot_id'] . '&vehicle_id=' . $vehicle['vehicle_id'] . '">' . $vehicle['vin'] . ' / ' . $vehicle['code'] . '</a>',
                            $vehicle['sname'],
                            $vehicle['mon'],
                            $vehicle['tue'],
                            $vehicle['wed'],
                            $vehicle['thu'],
                            $vehicle['fri'],
                            $vehicle['sat']
                            //$vehicle['sun']

                        );

                    }

                    $displaytable = new DisplayTable ($proVehicles);
                    echo $displaytable->getContent();
                    echo "<p>&nbsp;</p>\n";

                elseif (isset($this->qform_abfahrtszeit)):
                    {
                        if (isset($_GET['vehicle_id'])) {
                            $vehicletemp = $this->ladeLeitWartePtr->vehiclesPtr->getFromId($_GET['vehicle_id']);
                            echo '<h2>Abfahrszeiten für Fahrzeug ' . $vehicletemp['vin'] . '/' . $vehicletemp['code'] . ' setzen</h2>';
                        } else if (isset($_GET['zsp']))
                            echo '<h2>Die gleiche Abfahrstzeiten für gesamten ZSP ' . $this->depotname . ' setzen</h2>';
                        else if (isset($_GET['zspl']))
                            echo '<h2>Die gleiche Abfahrstzeiten für gesamten ZSPL ' . $this->zsplname . ' setzen</h2>';

                        echo $this->qform_abfahrtszeit->getContent();
                    }
                endif;
                ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "auszulieferende"):
        ?>
        <div class="row ">
            <div class="columns eight">
                <h1>Auszulieferende Fahrzeuge</h1>
                In der folgenden Übersicht können neu auszulieferende Fahrzeuge den zukünftigen Ladepunkten zugeordnet
                werden. <br>Die Auslieferung der Neufahrzeuge wird anhand dieser Zuordnung gesteuert.
            </div>
        </div>
        <div class="row ">
            <div class="columns six">
                <h2><?php echo 'Anzahl der auszulieferenden Fahrzeuge'; ?></h2>
                <?php
                if (!empty($this->vehicle_variants_quantity))
                    foreach ($this->vehicle_variants_quantity as $vehicle_variant) {
                        echo '<div style=" background: #F1F1F1;padding: 1.2em; border-radius: 4px">';
                        echo $this->vehicle_variants[$vehicle_variant['variant_value']] . ': <strong>' . $vehicle_variant['delivery_quantity'] . '</strong> ';
                        $this->kweek_quantities = json_decode($vehicle_variant['byweek'], true);
                        $this->kweek_quantities_delivered = json_decode($vehicle_variant['byweek_delivered'], true);
                        $kweeks = $this->kweeks_with_label;
                        echo '<div style="padding-left: 2em;" >';
                        $kweekstr = '';
                        foreach ($kweeks as $kweek => $kweek_label) {
                            if (isset($this->kweek_quantities[$kweek])) {
                                if ($this->kweek_quantities[$kweek] > $this->kweek_quantities_delivered[$kweek])
                                    $kweekstr[] = $kweek_label . ': ' . $this->kweek_quantities[$kweek];
                                else if ($this->kweek_quantities[$kweek] == 0)
                                    $kweekstr[] = $kweek_label . ': 0';
                                else {
                                    $kweekstr[] = $kweek_label . ': ' . $this->kweek_quantities[$kweek] . ' <a class="open_target_as_dialog" data-targetid="vehicles_delivered_' . $kweek . '" style="color: #333">(bereits ausgeliefert)</a>';
                                    if (isset($this->vehicles_delivered[$kweek])) {
                                        $headings = array(array('headingone' => array('ZSP', 'OZ Nummer', 'AKZ', 'Ladesäule', 'Anlieferungsdatum')));
                                        $displaytable = new DisplayTable (array_merge($headings, $this->vehicles_delivered[$kweek]));
                                        echo '<div id="vehicles_delivered_' . $kweek . '" title="Ausgelieferte Fahrzeuge in ' . strtoupper($kweek) . '" class="init_hidden">' . $displaytable->getContent() . '</div>';
                                        //depots.name as dname,depots.dp_depot_id,vehicles.vin,vehicles.code,vehicles.vin,stations.name as sname,vehicles_sales.delivery_week,vehicles_sales.delivery_date');
                                    }
                                }
                            } else
                                $kweekstr[] = $kweek_label . ': NV ';
                        }
                        echo '<br>' . implode('<br>', $kweekstr);
                        echo '</div>';
                        //start quick fix for March 2017 delivery
                        /*if(isset($this->qform_zuruck[$vehicle_variant['variant_value']]))
                  echo '<br><strong>Fahrzeuge zurückgeben</strong><br>'.$this->qform_zuruck[$vehicle_variant['variant_value']]->getContent();'*/
                        echo '</div><br>';
                        //end quick fix for March 2017 delivery
                    }


                ?>
            </div>
            <div class="columns six">
                <h2><?php echo 'Anzahl der noch zuzuweisenden Ladesäulen'; ?></h2>
                <?php
                if (!empty($this->vehicle_variants_assigned))
                    $production_quantities = array_combine(array_column($this->vehicle_variants_assigned, 'vehicle_variant_value_allowed'), array_column($this->vehicle_variants_assigned, 'scnt'));

                if (!empty($this->vehicle_variants_quantity))
                    foreach ($this->vehicle_variants_quantity as $vehicle_variant) {
                        if (!isset($production_quantities[$vehicle_variant['variant_value']])) $production_quantities[$vehicle_variant['variant_value']] = 0;

                        $to_be_assigned_stations_cnt = $vehicle_variant['to_deliver_cnt'] - $production_quantities[$vehicle_variant['variant_value']];
                        if ($to_be_assigned_stations_cnt < 0) $to_be_assigned_stations_cnt = 0;

                        echo 'Es müssen noch <strong>' . $to_be_assigned_stations_cnt . ' </strong> Ladesäulen für ' . $this->vehicle_variants[$vehicle_variant['variant_value']] . ' zugewiesen werden.<br>';
                    }

                ?>
                <span style="font-size: 0.9em">(Wird beim Speichern aktualisiert)</span>
            </div>
        </div>

        <div class="row ">
            <div class="columns twelve	">
                <h1>Auszulieferende Fahrzeuge an Ladepunkte zuordnen</h1>
            </div>
        </div>
        <?php
        echo $this->commonAssignVehiclePtr->printContent();
        ?>
        <div class="row ">
            <div class="columns eight">
                <form id="ladepunkteTable" method="post">
                    <?php if (isset($this->listLadepunkte)) echo $this->listLadepunkte;

                    ?>
                </form>
            </div>
        </div>

        <div class="row ">
            <div class="columns eight">
                <form id="ladepunkteTable" method="post">
                    <h1>Zugewiesene Ladesäulen </h1>
                    <?php
                    if (!empty($this->vehicle_variants_assigned_data)) {
                        $headings = array(array('headingone' => array('OZ Nummer', 'ZSP', 'Ladesäule', 'Fz.-Konfiguration')));
                        $displaytable = new DisplayTable (array_merge($headings, $this->vehicle_variants_assigned_data));
                        echo $displaytable->getContent();
                    } else
                        echo 'Keine zugewiesene Ladesäulen!'
                    ?>
                </form>
            </div>
        </div>

    <?php
    elseif (isset($this->action) && $this->action == "edit_depot"):
        if (isset($this->qform)):
            echo '<h1 id="depot_edit">ZSPL: ' . $this->zsplname . '</h1>';
            echo $this->qform->getContent();
        endif;
    elseif (isset($this->action) && $this->action == "showDepots"):
        ?>
        <div class="row ">
            <div class="columns eight" style="min-height: 400px">
                <h2>ZSP Email Adressen verwalten</h2>
                <p>In der folgenden Übersicht können die Mailadressen der Personen eingetragen werden, die bei
                    Unregelmäßigkeiten im Betrieb der Fahrzeuge informiert werden.</p>
                <?php
                $processed_listObjects[] = array('headingone' => $this->listObjectsTableHeadings);
                if (!empty($this->listObjects)) {
                    foreach ($this->listObjects as $listObject) {
                        $listObjectLink = '<a href="?action=edit_depot&depot=' . $listObject["depot_id"] . '" ><span class="genericon genericon-edit"> </span><span class="">Email Adressen bearbeiten</span></a>';
                        if (!empty($listObject["emails"])) {
                            $listObject["emails"] = unserialize($listObject["emails"]);

                            foreach ($listObject["emails"] as &$zsplemail) {
                                $depotuser = $this->ladeLeitWartePtr->allUsersPtr->getFromEmailId($zsplemail);
                                if (!empty($depotuser))
                                    $zsplemail = '<a href="?page=mitarbeiter&action=aktuelle&id=' . $depotuser['id'] . '">' . $zsplemail .
                                        '<span class="genericon genericon-edit"> </span>' .
                                        '<span style="color: #333333">Benutzerkonto bearbeiten</span>' .
                                        '</a>';
                                else
                                    $zsplemail = '<a href="?page=mitarbeiter&action=neu&zspl=' . $listObject["zspl_id"] .
                                        '&givenemail=' . $zsplemail . '&role=fpv" >' . $zsplemail .
                                        '<span class="genericon genericon-plus"> </span>' .
                                        '<span style="color: #333333">Benutzerkonto anlegen</span>' .
                                        '</a>';
                            }
                            unset($zsplemail); //break reference with last element
                            $listObject["emails"] = implode("<br>", $listObject["emails"]);
                        }

                        $processed_listObjects[] = array($listObject["name"] . '<br>' . $listObjectLink, $listObject["emails"]);

                    }

                    $displaytable = new DisplayTable ($processed_listObjects);
                    echo $displaytable->getContent();
                } else
                    echo "Keine ZSPLn gefunden!";
                ?>

            </div>
        </div>
    <?php
    else:?>
        <div class="row ">
            <div class="columns twelve" style="min-height: 500px">

            </div>
        </div>
    <?php endif; ?>
    <!--
       <div class="row ">
        <div class="columns six">
         <a href="#" onClick="window.history.back();" > Zurück</a>
         </div>
       </div>
    -->
</div>