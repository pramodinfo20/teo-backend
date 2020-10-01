<?php
$selected = [
    "",
    "",
    "",
    "",
    "",
    "",
    "",
    ""
];
$iSel = 0;

switch ($this->action) {
    case '':
    case 'home':
        $iSel = 1;
        break;
    case "showZspls":
    case "edit_zspl":
    case "save_exist_zspl":
        $iSel = 2;
        break;
    case 'fahrzeugverwaltung':
        $iSel = 3;
        break;
    case 'fahrzeugbestellung':
        $iSel = 4;
        break;
    case 'abfahrtszeit':
        $iSel = 5;
        break;
    case 'depotprop':
        $iSel = 6;
        break;
    case 'flottenmonitor':
        $iSel = 7;
        break;
}
$selected[$iSel] = " selected";

echo <<<HEREDOC
	<div class="row ">
		<div class="columns twelve">
			<ul class="submenu_ul">
				<li>
					<a href="index.php" class="sts_submenu {$selected[1]}">Home</a>
				</li>
				<li>
					<a href="?action=showzspls" data-target="showzspls" class="sts_submenu {$selected[2]}">
                    Email Adressen/Accounts ZSPLn</a>
				</li>
				<li>
					<a href="?action=fahrzeugverwaltung" data-target="fahrzeugverwaltung" class="sts_submenu {$selected[3]}">
                    ZSP-/Ladepunkten-Zuordnung</a><span class="nav_hint">Fahrzeuge zwischen ZSPn verschieben / Fahrzeug-Ladepunkte Zuordnung ändern</span>
				</li>
				<li>
					<a href="?action=fahrzeugbestellung" class="sts_submenu {$selected[4]}">
                    Fahrzeuge bestellen/anfordern</a><span class="nav_hint"></span>
				</li>
				<li>
					<a href="?action=abfahrtszeit" data-target="abfahrtszeit" class="sts_submenu {$selected[5]}">
                    Abfahrtszeiten</a>
				</li>
				<li>
					<a href="?action=depotprop" data-target="depotprop" class="sts_submenu {$selected[6]}">
                    ZSP-Eigenschaften</a><span class="nav_hint">Spätladen einstellen</span>
				</li>
				<li>
					<a href="?action=flottenmonitor" data-target="flottenmonitor" class="sts_submenu {$selected[7]}">
                    Flottenmonitoring</a><span class="nav_hint">Energieverbrauch und Flottenstatistik</span>
				</li>

			</ul>
		</div>
	</div>
HEREDOC;
