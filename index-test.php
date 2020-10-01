<?php
$specialapp = '';
$selected = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""];
$iSel = 0;

switch ($this->action) {
    case '':
    case 'home':
        $iSel = 1;
        break;
    case 'produzierte':
        $iSel = 2;
        break;
    case 'ausgelieferte':
        $iSel = 2;
        break;

    case 'parameterlist':
        $iSel = 3;
        break;
    case 'ebom':
        $iSel = 3;
        break;
    case 'ebomanzeige':
        $iSel = 3;
        break;
    case 'updateinterface':
        $iSel = 3;
        break;

    case 'specialapp':
        $iSel = 4;
        break;
    case 'teoexceptions':
        $iSel = 4;
        break;

    case 'vehiclebooking':
        $iSel = 5;
        break;
    case 'documentvehicles':
        $iSel = 5;
        break;
    case 'entwicklung':
        $iSel = 5;
        break;

    case 'dtcverwaltung':
        $iSel = 6;
        break;
    case 'verlaufsdaten':
        $iSel = 6;
        break;
    case 'tagesstatistik':
        $iSel = 6;
        break;
    //case 'c2cconnects':     $iSel=6;    break;
}
$selected[$iSel] = " selected";
if (isset ($GLOBALS['pageController'])) {
    $user = $GLOBALS['pageController']->GetObject('user');
}

if ($user->user_can('specialapproval')) {
    $menu_specialapp = <<<HEREDOC

                <a href="?action=specialapp&initPage">Sondergenehmigung</a>
HEREDOC;

    //     <a href="?action=entwicklung&initPage">Entwicklungsseite</a>';
}

if ($user->IsAdmin()) {
    $menu_admin_only = '
                <a href="?action=updateinterface&initPage">Update-Interface</a>';
}

echo <<<HEREDOC
     <div class="row ">
       <div class="columns twelve">
          <ul class="submenu_ul engg_menu">
			<li>
			  <a href="?action=home" class="W150 sts_submenu{$selected[1]}">Home</a>
			</li>

            <li class="dropdown">
              <div class="sts_submenu {$selected[2]}">Fz.-Listen</div>
              <div class="dropdown-content">
                <a href="?action=produzierte&initPage">Produzierte Fahrzeuge</a>
                <a href="?action=ausgelieferte&initPage">Ausgelieferte Fahrzeuge</a>
              </div>
            </li>

            <li class="dropdown">
              <div class="sts_submenu {$selected[3]}">Fz.-Konfiguration</div>
              <div class="dropdown-content">
                <a href="?action=parameterlist&initPage">Fahrzeug Parameter</a>
                <a href="?action=ebom&initPage" >Ebom Verwaltung</a>
                <a href="?action=ebomanzeige&initPage">Ebom Anzeige und Qualitygates</a>$menu_admin_only
              </div>
            </li>

            <li class="dropdown">
              <div class="sts_submenu {$selected[4]}">Qualitätssicherung</div>
              <div class="dropdown-content">
                <a href="?action=teoexceptions&initPage" >Abweicherlaubnis</a>{$menu_specialapp}

              </div>
            </li>

            <li class="dropdown">
              <div class="sts_submenu {$selected[5]}">Testfahrzeuge</div>
              <div class="dropdown-content">
                <a href="?action=vehiclebooking&initPage" >Fzg. buchen/Benutzungsplan</a>
                <a href="?action=documentvehicles&initPage" >Fz. Dokumente</a>
              </div>
            </li>


            <li class="dropdown">
              <div class="sts_submenu {$selected[6]}">Fz.-Daten</div>
              <div class="dropdown-content">
                <a href="?action=tagesstatistik&initPage">Tägliche Statistik</a>
                <a href="?action=verlaufsdaten&initPage" >Fahrzeug Verlaufsdaten</a>
                <a href="?action=dtcverwaltung&initPage">DTC Verwaltung</a>
              </div>
            </li>
	      </ul>
	   </div>
	</div>
HEREDOC;

// <a href="?action=c2cconnects&initPage">C2C Verbindungsübersicht</a>
?>


