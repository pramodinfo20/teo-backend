<?php
/**
 * aftersales.php
 * Template für die Benutzer Rolle Aftersales
 * @author Pradeep Mohan
 */
?>
<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul">
                <li>
                    <a href="#" data-target="vehicleselect"
                       class="sts_submenu <?php if (!$this->vehicleAttributes) echo 'selected'; ?>">Fahrzeug wählen</a>
                </li>
                <?php if ($this->vehicleAttributes): ?>
                    <li>
                        <a href="#" data-target="viewconfig"
                           class="sts_submenu <?php if ($this->vehicleAttributes) echo 'selected'; ?> ">Fahrzeug
                            Konfiguration abfragen</a>
                    </li>
                    <li>
                        <a href="#" data-target="changeconfig" class="sts_submenu">Fahrzeug Konfiguration ändern</a>
                    </li>
                    <li>
                        <a href="#" data-target="dailystats" class="sts_submenu">Daily Stats</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="row ">
        <div class="columns six">
			<span class="error_msg">
			<?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
			</span>
        </div>
    </div>
    <?php if ($this->vehicle_search_content): ?>
        <div id="vehicleselect" class="row submenu_target_child <?php if (!$this->vehicleAttributes) echo 'current'; ?>"
             style="min-height: 310px">
            <div class="columns three">
                <h2>Fahrzeug wahlen</h2>
                <?php echo $this->vehicle_search_content; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->vehicleAttributes): ?>
        <div id="viewconfig" class="row submenu_target_child <?php if ($this->vehicleAttributes) echo 'current'; ?>"
             style="min-height: 310px">
            <div class="columns twelve">
                <h2>Fahrzeug Konfiguration</h2>
                <?php

                $fahrzeugvariante = $sitzpl = $compartment = '';

                foreach ($this->vehicleAttributes as $row) {
                    if ($row['aname'] == 'Fahrzeugvariante') {
                        $fahrzeugvariante = $row['value'];
                    }

                    if ($row['aname'] == 'Sitzplätze') {
                        $sitzpl = $row['value'];
                    }
                    if ($row['aname'] == 'Kofferaubau Typ') {
                        $compartment = $row['value'];
                    }


                } ?>
                <div class="row">
                    <div class="columns four"><h2>Stammdaten</h2>
                        <table class="noborder">
                            <tr>
                                <th>Zeitpunkt :</th>
                                <td><?php echo $this->showconfigTimestamp; ?></td>
                            </tr>
                            <tr>
                                <th>VIN :</th>
                                <td><?php echo $this->vin; ?></td>
                            </tr>
                            <tr>
                                <th>Kennzeichen :</th>
                                <td><?php echo $this->code; ?></td>
                            </tr>
                            <tr>
                                <th>ZSP :</th>
                                <td><?php echo $this->depotName; ?></td>
                            </tr>
                            <tr>
                                <th>Fahrzeugvariante :</th>
                                <td><?php echo $fahrzeugvariante; ?></td>
                            </tr>
                            <tr>
                                <th>Sitzplätze :</th>
                                <td><?php echo $sitzpl; ?></td>
                            </tr>
                            <tr>
                                <th>Kofferaubau Typ :</th>
                                <td><?php echo $compartment; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="columns six">
                        <h2>Merkmale</h2>
                        <table class=''>
                            <tr>
                                <th>Merkmale</th>
                                <th>Wert</th>
                                <th>Teilnummer</th>
                                <th>Angepasst von</th>
                                <th>Zeitpunk</th>
                            </tr>
                            <?php
                            foreach ($this->vehicleAttributes as $row) {
                                if ($row['editable'] == 'f') continue;
                                echo "<tr><th>" . $row['aname'] . "</th><td>" . $row['value'] . "</td><td>" . $row['partnumber'] . "</td><td>" . $row['user'] .
                                    "</td><td>" . date('Y-m-d H:i:s', $row['timestamp']) . "</td></tr>";
                            }
                            ?>
                        </table>

                        <br><br>Herunterladen als:<br>
                        <input type='button' value='PDF' disabled>
                        <input type='button' value='Open Document(.odx)' disabled>
                        <input type='button' value='Office XML(.xlsx)' disabled>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->vehicleAttributes): ?>
        <div id="changeconfig" class="row submenu_target_child">
            <div class="columns twelve">
                <h1>Fahrzeug Konfiguration ändern </h1>
                <?php echo $this->qform_editConfig->getContent(); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($this->dailyStats): ?>
        <div id="dailystats" class="row submenu_target_child">
            <div class="columns twelve">
                <h2>Daily Stats für Fahrzeug : <?php echo $this->vin . '(' . $this->code . ')'; ?></h2>
                <?php
                $result = $this->dailyStats;
                if (!empty($result)) {
                    echo "<div style=\"overflow: scroll; height: 400px;\"><table>";

                    foreach (array_keys($result[0]) as $value)
                        echo "<th>" . $value . "</th>";

                    foreach ($result as $theobject) {
                        echo "<tr>";
                        $rowdata = $theobject;

                        foreach ($rowdata as $key => $value)
                            echo "<td>" . $value . "</td>";
                        echo "</tr>";
                    }
                    echo "</table><br><br></div>";
                } else {
                    echo "Keine Daten gefunden!";
                }

                ?>
                <br><br>Herunterladen als:<br>
                <input type='button' value='PDF' disabled>
                <input type='button' value='Open Document(.odx)' disabled>
                <input type='button' value='Office XML(.xlsx)' disabled>
            </div>
        </div>
    <?php endif; ?>
</div>