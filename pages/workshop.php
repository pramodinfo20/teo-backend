<div class="inner_container">

    <?php
    include __DIR__ . '/menu/workshop.menu.php';
    ?>
    <div class="row ">
        <div class="columns six">
            <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
        </div>
    </div>

    <div id="werkstatt" class="submenu_target_child current">
        <div class="columns eight ">
            <h1>Werkstattbesuche eingeben</h1>

            <form action='' id="fleet_search_form" method='post'>
                Bitte wählen Sie ein Fahrzeug nach VIN/Kennzeichen/IKZ. <br>
                Sie können auch direkt Teile der VIN/Kennzeichen/IKZ Nummer in das Feld zum Suchen eingeben.<br><br>
                <select class="fleet_search" name="vehicle_id">
                    <option value="null"></option><?php

                    $vehicle_id = isset($_POST['vehicle_id']) ? $_POST['vehicle_id'] : 0;
                    foreach ($this->processed_vehicles as $key => $vehicle) {
                        $optSel = ($key == $vehicle_id) ? " selected" : "";
                        echo "<option value=\"$key\"$optSel>$vehicle</option>";
                    }
                    ?>
                </select><br><br>
                <input type="hidden" name="action" value="werkstatt"><br><br>
            </form>
            <form>
                <?php

                if ($vehicle_id) {
                    $qry = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                        ->join('vehicle_variants', 'vehicles.vehicle_variant=vehicle_variants.vehicle_variant_id')
                        ->join('depots', 'using (depot_id)', 'left outer join')
                        ->join('divisions', 'using (division_id)', 'left outer join')
                        ->where('vehicle_id', '=', $vehicle_id);

                    $vehicle = $qry->getOne('depots.name,depots.dp_depot_id,vehicles.vin,vehicles.code,vehicles.ikz, depots.workshop_id,vehicle_variant,windchill_variant_name,dp_division_id');
                    $type = substr($vehicle['windchill_variant_name'], 0, 3);


                    $qry = $this->ladeLeitWartePtr->vehicleVariantsPtr->newQuery()
                        ->where('windchill_variant_name', 'ilike', "$type%")
                        ->orderBy('windchill_variant_name');

                    $allVariants = $qry->get('vehicle_variant_id=>windchill_variant_name');

                    if ($vehicle['dp_division_id'] > 0) {
                        $post_variant = "unknown";
                        if (stripos($vehicle['vin'], 'B14')) $post_variant = 'WORK B14 1-Sitzer Verbund';
                        if (stripos($vehicle['vin'], 'B16')) $post_variant = 'WORK B16 1-Sitzer Verbund';
                        if (stripos($vehicle['vin'], 'D16')) $post_variant = 'WORK L D16 1-Sitzer Verbund';
                        if (stripos($vehicle['vin'], 'D17')) $post_variant = 'WORK L D17 1-Sitzer Verbund';

                        echo <<<HEREDOC
	    <table>
          <tbody>
            <tr>
              <th>ZSP</th>
              <th>OZ</th>
              <th>VIN</th>
              <th>Kennzeichen</th>
              <th>IKZ</th>
              <th>Konfiguration</th>
            </tr>
            <tr>
              <td>{$vehicle['name']}</td>
              <td>{$vehicle['dp_depot_id']}</td>
              <td>{$vehicle['vin']}</td>
              <td>{$vehicle['code']}</td>
              <td>{$vehicle['ikz']}</td>
              <td>{$vehicle_variant}</td>
            </tr>
          </tbody>
        </table>
HEREDOC;
                    } else {
                        echo <<<HEREDOC
	    <table>
          <tbody>
            <tr>
              <th>VIN</th>
              <th>Kennzeichen</th>
              <th>Fahrzeugkonfiguration/Typenbezeichnung</th>
            </tr>
            <tr>
              <td>{$vehicle['vin']}</td>
              <td>{$vehicle['code']}</td>
              <td>{$vehicle['windchill_variant_name']}</td>
            </tr>
          </tbody>
        </table>
HEREDOC;
                    }

                    if (false) {
                        ?>
                        <br><br>
                        Werkstatt-Eingang Datum<br>
                        <input name="date" type="text" value="<?php echo date('Y-m-d'); ?>">
                        <br><br>
                        <div class="seitenteiler">
                            Grund Werkstattaufenthalt<br>
                            <select name="reason"
                                    OnChange="document.getElementById('id_change_config').style.visibility = (this.selectedIndex==2) ? 'visible':'hidden'">
                                <option>Gewaltschaden</option>
                                <option>Reparaturschaden</option>
                                <option>Fahrzeugänderung</option>
                            </select>
                        </div>
                        <div class="seitenteiler" id="id_change_config" style="visibility:hidden; margin-left:30px;">
                            neue Typenbezeichnung<br>
                            <select name="newconfig"><?php

                                $thisVariant = $vehicle['vehicle_variant'];

                                foreach ($allVariants as $variant_id => $name) {
                                    $optSel = ($variant_id == $thisVariant) ? " selected" : "";
                                    echo "<option value=\"$variant_id\"$optSel>$name</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <br><br>
                        Fehlerbeschreibung <br>
                        <textarea name="description" rows="10" cols="50"></textarea><br><br>
                        <input type="submit" value="Speichern">
                        <?php
                    }
                    echo <<<submenu
        <a href="{$_SERVER['PHP_SELF']}?action=flashinterface&vin={$vehicle['vin']}" style="border: 1px solid black; border-radius: 4px; margin: 3px; padding:4px; background: linear-gradient(to bottom,#ffe680 0,#FFCC00 90%,#ffe680 100%); color:black;">BCM flashen</a>
        <!----<a href="{$_SERVER['PHP_SELF']}?action=tauschinterface&vin={$vehicle['vin']}&command=selectMode"  style="border: 1px solid black; border-radius: 4px; margin: 3px; padding:4px; background: linear-gradient(to bottom,#ffe680 0,#FFCC00 90%,#ffe680 100%); color:black;">Tauschinterface</a>--->
submenu;
                    if ($this->diagnosePtr->newQuery('general')
                        ->where('vin', '=', $vehicle['vin'])
                        ->get('*')) {
                        echo <<<submenu
        <a href="{$_SERVER['PHP_SELF']}?action=dtcverwaltungwerk&command=sessionlist&vin={$vehicle['vin']}" style="border: 1px solid black; border-radius: 4px; margin: 3px; padding:4px; background: linear-gradient(to bottom,#ffe680 0,#FFCC00 90%,#ffe680 100%); color:black;">DTCs anzeigen</a>
        
submenu;
                    } else {
                        echo <<<submenu
        <a href=# style="border: 1px solid black; border-radius: 4px; margin: 3px; padding:4px; background: lightgray); color:black; cursor: not-allowed">DTCs anzeigen</a>
        
submenu;
                    }
                }


                ?>
            </form>
        </div>
    </div>
</div>
