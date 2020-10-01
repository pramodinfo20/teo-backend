<div class="inner_container">
    <?php include $_SERVER['STS_ROOT'] . '/pages/menu/fleet.menu.php'; ?>

    <div class="row ">
        <div class="columns six">
            <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
        </div>
    </div>


    <?php if ($this->action == "addvehicle"): ?>

        <div id="addvehicle" class="submenu_target_child current">
            <div class="columns eight ">
                <h1>Fahrzeuge beauftragen</h1>
                <?php
                echo $this->qform_vehicle->getContent();
                ?>
            </div>
        </div>
    <?php elseif ($this->action == "werkstatt"): ?>
        <div id="werkstatt" class="submenu_target_child current">
            <div class="columns eight ">
                <h1>Werkstattbesuche eingeben</h1>

                <form action='' id="fleet_search_form" method='post'>
                    Bitte wählen Sie ein Fahrzeug nach VIN/Kennzeichen/IKZ. <br>
                    Sie können auch direkt Teile der VIN/Kennzeichen/IKZ Nummer in das Feld zum Suchen eingeben.<br><br>
                    <select class="fleet_search" name="vehicle_id">
                        <option value="null"></option><?php
                        foreach ($this->processed_vehicles as $key => $vehicle)
                            echo '<option value="' . $key . '">' . $vehicle . '</option>';
                        ?>
                    </select><br><br>
                    <input type="hidden" name="action" value="werkstatt"><br><br>
                </form>
                <form>
                    <?php

                    if (isset($_POST['vehicle_id'])) {
                        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
                            ->join('divisions', 'divisions.division_id=divisions.division_id', 'INNER JOIN')
                            ->where('vehicle_id', '=', $_POST['vehicle_id'])
                            ->getOne('depots.name,depots.dp_depot_id,vehicles.vin,vehicles.code,vehicles.ikz, depots.workshop_id');
                        $vehicle_variant = "unknown";
                        if (stripos($vehicle['vin'], 'B14')) $vehicle_variant = 'WORK B14 1-Sitzer Verbund';
                        if (stripos($vehicle['vin'], 'B16')) $vehicle_variant = 'WORK B16 1-Sitzer Verbund';
                        if (stripos($vehicle['vin'], 'D16')) $vehicle_variant = 'WORK L D16 1-Sitzer Verbund';
                        if (stripos($vehicle['vin'], 'D17')) $vehicle_variant = 'WORK L D17 1-Sitzer Verbund';

                        $workshopId = $vehicle['workshop_id'];

                        if ($workshopId == '') {
                            $workshopName = "NA";
                        } else {
                            $workshop = $this->ladeLeitWartePtr->vehiclesPtr->newQuery('workshops')
                                ->where('workshop_id', '=', $workshopId)
                                ->getOne('*');
                            $workshopName = $workshop['name'] . ', ' . $workshop['zip_code'] . ' ' . $workshop['location']
                                . ', ' . $workshop['street'] . ' ' . $workshop['house_number'];

                        }

                        echo '<table><tbody><tr>
							<th>ZSP</th>
							<th>OZ</th>
							<th>VIN</th>
							<th>AKZ</th>
							<th>IKZ</th>
							<th>Konfiguration</th>
							<th>Werkstatt</th></tr><tr>
							<td>' . $vehicle['name'] . '</td><td>' . $vehicle['dp_depot_id'] . '</td><td>' . $vehicle['vin'] . '</td><td>' . $vehicle['code'] . '</td><td>' . $vehicle['ikz'] . '</td><td>' . $vehicle_variant . '</td><td>' . $workshopName . '</td></tr></tbody></table>';
                        ?>
                        <br><br>
                        Werkstatt-Eingang Datum<br>
                        <input type="text" value="<?php echo date('Y-m-d'); ?>">
                        <br><br>
                        Grund Werkstattaufenthalt<br><br>
                        <select>
                            <option>Gewaltschaden</option>
                            <option>Reparaturschaden</option>
                        </select>
                        <br><br>
                        Fehlerbeschreibung <br>
                        <textarea rows="10" cols="50"></textarea><br><br>
                        <input type="submit" value="Speichern">
                        <?php
                    }
                    ?>
                </form>
            </div>
        </div>
    <?php elseif (isset($this->commonVehicleMgmtPtr)) : ?>
        <div id="fahrzeugverwaltung" class="submenu_target_child current">
            <div class="columns twelve ">
                <h1>Fahrzeug Verwaltung</h1>
                <?php
                echo $this->commonVehicleMgmtPtr->printContent();
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

