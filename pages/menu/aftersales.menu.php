<?php
$selected = [
    "",
    "",
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
    case 'fertigmelden':
        $iSel = 2;
        break;
    case 'verlaufsdaten':
        $iSel = 3;
        break;
    case 'tagesstatistik':
        $iSel = 4;
        break;
    case 'produzierte':
        $iSel = 7;
        break;
    case 'ausgelieferte':
        $iSel = 7;
        break;
    case 'dtcverwaltung':
        $iSel = 7;
        break;
    case 'fehlerliste':
        $iSel = 7;
        break;
    case 'tauschinterface':
        $iSel = 8;
        break;
    case 'ebom':
        $iSel = 9;
        break;
    case 'ebomanzeige':
        $iSel = 9;
        break;
    case 'werkstaettenlogin':
        $iSel = 10;
        break;
    case 'flashinterface':
        $iSel = 11;
        break;
}
$selected[$iSel] = " selected";

echo <<<HEREDOC

	<div class="row ">
		<div class="columns twelve">
			<ul class="submenu_ul">

				<li>
					<a href="?action=home" class="W150 sts_submenu{$selected[1]}">Home</a>
				</li>
				<li>
					<a href="?action=fertigmelden&initPage" data-target="fertigmelden" class="W160 sts_submenu{$selected[2]}">Fahrzeuge fertig melden</a>
				</li>
				<li>
					<a href="?action=verlaufsdaten&initPage" data-target="verlaufsdaten" class="W160 sts_submenu{$selected[3]}">Fahrzeug Verlaufsdaten</a>
				</li>
				<li>
					<a href="?action=tagesstatistik&initPage" data-target="tagesstatistik" class="W160 sts_submenu{$selected[4]}">Tägliche Statistik</a>
				</li>
				
				
				<li class="dropdown">
                    <div class="sts_submenu {$selected[7]}">Fz.-Daten</div>
                    <div class="dropdown-content">
                        <a href="?action=dtcverwaltung&initPage">DTC Verwaltung</a>
                        <a href="?action=fehlerliste&initPage">Fehlerliste</a>
                        <a href="?action=produzierte&initPage" data-target="produzierte" >Produzierte Fahrzeuge</a>
                        <a href="?action=ausgelieferte&initPage" data-target="ausgelieferte">Ausgelieferte Fahrzeuge</a> 
                    </div>
                </li>
                <li>
					<a href="?action=tauschinterface&initPage" class="W160 sts_submenu{$selected[8]}">Tauschinterface</a>
				</li>
                
                <li class="dropdown">
                <div class="sts_submenu {$selected[9]}">Ebom</div>
                <div class="dropdown-content">
                    <a href="?action=ebom&initPage" >Ebom Verwaltung</a>
                    <a href="?action=ebomanzeige&initPage">Ebom Anzeige und Qualitygates</a>
                </div>
                </li>
                <li>
					<a href="?action=werkstaettenlogin&initPage" data-target="werkstaettenlogin" class="W160 sts_submenu{$selected[10]}">Werkstätten Login</a>
				</li>
                <li>
                    <a href="?action=flashinterface&initPage" class="W160 sts_submenu{$selected[11]}">BCM Updaten</a>
                </li>
			</ul>
		</div>
	</div>

HEREDOC;
?>


