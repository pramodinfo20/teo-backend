<?php
if (!isset($action)) $action = $this->action;


$sel = [
    'home' => "",
    'overview' => "",
    'newvehicles' => "",
    'changevariant' => "",
    'termine' => "",
    'delivery' => "",
    'export' => "",
];

$actionmap = [
    'locationplan' => 'overview',
    'newvehiclespost' => 'newvehicles',
    'dritt' => 'newvehicles',
    'saveexportcsv' => 'export',
    'exportcsv' => 'export',
    'exportpdf' => 'export',
    'begleitschein' => 'export',
    'gencoc' => 'export',
    'exportxml' => 'export',
    'genpentakennwort' => 'export',
    'ebom' => 'export',
    'ebomanzeige' => 'export',
    'showProPlan' => 'termine',
    'showDivisionsDeliveryPlan' => 'termine',
    'show_finished_vehicles' => 'delivery',
    'auto_fahrzeuge_zuweisen' => 'delivery',
    'manuell_auslieferung' => 'delivery',
    'fahrzeugverwaltung' => 'delivery',
    'thirdparty_delivery' => 'delivery',
    'transporter_manage' => 'delivery',
    'pool_redeliver' => 'delivery',
    'auslieferung' => 'delivery',
];

$selected = safe_val($actionmap, $action, $action);

$specialfunc = '';
if ($_SESSION['sts_username'] == 'Sts.Sales') {
    $specialfunc = '<a href="?action=showDivisionsDeliveryPlan&initPage">Auslieferungsplan</a>';
}

$sel[$selected] = "selected";


$submenu_auslieferung_1 = <<<HEREDOC
                    <a href="?action=show_finished_vehicles&initPage">Liste QS geprüfter Fahrzeuge</a>
            <!--    <a href="?action=delivery&initPage&obj=assign_M">Fahrzeuge manuell zuweisen</a>             -->
            <!--    <a href="?action=auto_fahrzeuge_zuweisen&initPage">Fahrzeuge automatisch zuweisen</a>       -->
                    <a href="?action=fahrzeugverwaltung&initPage">KBOB Auslieferung</a>
            <!--    <a href="?action=manuell_auslieferung&initPage">Auslieferung Deutsche Post (manuell)</a>    -->
            <!--    <a href="?action=transporter_manage&initPage">Spediteure verwalten</a>                      -->
            <!--    <a href="?action=Auslieferung&execmode=table&initPage">POST Auslieferung (Manuell)</a>      -->
            <!--    <a href="?action=Auslieferung&execmode=csv&initPage">POST Auslieferung (CSV Liste)</a>      -->
                    <a href="?action=thirdparty_delivery&initPage">Auslieferung Drittkunden</a>
                    <a href="?action=delivery&initPage">Auslieferungsliste/Unterlagen nachdrucken</a>
                    <a href="?action=workshop_delivery&initPage">Werkstatt Auslieferung</a>
                    <a href="?action=pool_redeliver&initPage">Sts_Pool Fahzeuge an ZSP zuweisen</a>
HEREDOC;


echo <<<END_OF_MENUxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

        <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul" id="id_submenu">
            <li>
                <a href="?action=home" class="sts_submenu {$sel['home']}">Home</a>
            </li>
            <li class="dropdown">
                <!-- Fahrzeug Übersicht -->
                <div class="sts_submenu {$sel['overview']}">Fahrzeug Übersicht</div>
                <div class="dropdown-content">
                    <a href="?action=overview&initPage">Fahrzeug Liste</a>
                    <a href="?action=locationplan&initPage">Fahrzeug Lageplan</a>
                    <a href="?action=parkplaetze&initPage">Parkplätze</a>
                </div>
            </li>
            <li class="dropdown">
                <!--  Neue Fahrzeuge -->
                <div class="sts_submenu {$sel['newvehicles']}">Neue Fahrzeuge</div>
                <div class="dropdown-content">
                    <a href="?action=newvehicles&initPage">Deutsche Post (manuell)</a>
                    <a href="?action=dritt&initPage">Drittkunden (manuell)</a>
                    <a href="?action=newvehiclespost&initPage">von der Post Zentrale angelegte Fahrzeuge </a>
                </div>
            </li>
            <li class="dropdown">
                 <!-- Fahrzeug(e) ändern -->
                <div class="sts_submenu {$sel['changevariant']}">Fahrzeugdaten ändern</div>
                <div class="dropdown-content">
                    <a href="?action=changevariant&initPage&execmode=table">Manuell suchen, ändern &amp; löschen</a>
              <!--      <a href="?action=changevariant&initPage&execmode=range">manuell über Nummerbereiche</a>  -->
                    <a href="?action=changevariant&initPage&execmode=csv">Änderungsdatei (CSV/Excel) einspielen</a>
                </div>
            </li>
            <li class="dropdown">
                <!--  Termine -->
                <div class="sts_submenu {$sel['termine']}">Termine</div>
                <div class="dropdown-content">
                    <a href="?action=showProPlan&iniPage" >Produktionsplan</a>
                    {$specialfunc}
                </div>
            </li>
            <li class="dropdown">
                <div class="sts_submenu {$sel['export']}">Listen / Export</div>
                <div class="dropdown-content">
                    <a href="?action=exportcsv&initPage">Export CSV</a>
                    <a href="?action=begleitschein&initPage">Fahrzeugbegleitschein</a>
                    <a href="?action=exportpdf&initPage">Export PDF</a>
	                <a href="?action=cocGenerationPPS">Export CoC</a>
                    <a href="?action=exportxml&initPage">Export CoC XML</a>
                    <a href="?action=genpentakennwort">Liste Penta-Kennwort</a>
                    <a href="?action=Ebom">Ebom verwalten</a>
                    <a href="?action=EbomAnzeige">Ebom und Qualitygates anzeigen</a>

            <!--        <a href="?action=determiningparts">Variantenbildende Teile verwalten</a>                -->
                </div>
            </li>
            <li class="dropdown">
                <div class="sts_submenu {$sel['delivery']}">Auslieferung</div>
                <div class="dropdown-content">
                    <a href="?action=show_finished_vehicles&initPage">Liste QS geprüfter Fahrzeuge</a>
            <!--    <a href="?action=delivery&initPage&obj=assign_M">Fahrzeuge manuell zuweisen</a>             -->
            <!--    <a href="?action=auto_fahrzeuge_zuweisen&initPage">Fahrzeuge automatisch zuweisen</a>       -->
            <!--    <a href="?action=thirdparty_delivery&initPage">Auslieferung Drittkunden</a>                 -->
            <!--    <a href="?action=manuell_auslieferung&initPage">Auslieferung Deutsche Post (manuell)</a>    -->
            <!--    <a href="?action=transporter_manage&initPage">Spediteure verwalten</a>                      -->
                    <a href="?action=Auslieferung&execmode=table&dp_order=1&initPage">Auslieferung POST</a>
                    <a href="?action=Auslieferung&execmode=table&dp_order=2&initPage">Auslieferung POST Ausland</a>
                    <a href="?action=Auslieferung&execmode=table&dp_order=0&initPage">Auslieferung Drittkunden</a>
                    <a href="?action=fahrzeugverwaltung&initPage">KBOB Auslieferung</a>
                    <a href="?action=delivery&initPage">Auslieferungsliste/Unterlagen nachdrucken</a>
                    <a href="?action=workshop_delivery&initPage">Werkstatt Auslieferung</a>
                    <a href="?action=pool_redeliver&initPage">Sts_Pool Fahzeuge an ZSP zuweisen</a>

                </div>
            </li>
            </ul>
        </div>
    </div>
END_OF_MENUxxxxxxxxxxxxxxxxxxxxxxxxxxxxx;



