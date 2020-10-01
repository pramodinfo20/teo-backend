<div class="row ">
    <div class="columns twelve">
        <ul class="submenu_ul">
            <li>
                <a href="index.php" class="sts_submenu <?php if (!isset($this->action)) echo 'selected'; ?>">Home</a>
            </li>
            <li>
                <a href="?action=addvehicle" data-target="addvehicle"
                   class="sts_submenu <?php if ($this->action == "addvehicle") echo 'selected'; ?>">Fahrzeuge
                    beauftragen</a>
            </li>
            <li>
                <a href="?action=fleetauslieferung&reset=1"
                   class="sts_submenu <?php if ($this->action == "fleetauslieferung") echo 'selected'; ?>">Fahrzeuge
                    Ausliefern</a>
            </li>
            <li>
                <a href="?action=werkstatt" data-target="werkstatt"
                   class="sts_submenu <?php if ($this->action == "werkstatt") echo 'selected'; ?>">Werkstattbesuche
                    eingeben</a>
            </li>
            <li>
                <a href="?action=fahrzeugverwaltung" data-target="werkstatt"
                   class="sts_submenu <?php if ($this->action == "fahrzeugverwaltung") echo 'selected'; ?>">Fahrzeugverwaltung</a>
            </li>
        </ul>
    </div>
</div>
