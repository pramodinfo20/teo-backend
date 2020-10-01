<?php
/**
 * zentrale.php
 * Template für die Benutzer Rolle Zentrale
 * @author Pradeep Mohan
 */
?>

<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul">

                <li>
                    <a href="index.php"
                       class="sts_submenu <?php if (!isset($this->action)) echo 'selected'; ?>">Home</a>
                </li>
                <li>
                    <a href="?action=exportcsv" data-target="exportcsv"
                       class="sts_submenu <?php if ($this->action == "exportcsv") echo 'selected'; ?>">Liste aller
                        Fahrzeuge exportieren (temporär)</a>
                </li>
                <li>
                    <a href="?action=uploadDeliveryPlan" data-target="uploadDeliveryPlan"
                       class="sts_submenu <?php if ($this->action == "uploadDeliveryPlan") echo 'selected'; ?>">Mobilitätsplanung
                        hochladen</a>
                </li>
                <li>
                    <a href="?action=showDeliveryPlan" data-target="showDeliveryPlan"
                       class="sts_submenu <?php if ($this->action == "showDeliveryPlan") echo 'selected'; ?>">Anzeige
                        Mobilitätsplanung</a>
                </li>
                <li>
                    <a href="?action=uploadDeliveryPlanWeek" data-target="uploadDeliveryPlanWeek"
                       class="sts_submenu <?php if ($this->action == "uploadDeliveryPlanWeek") echo 'selected'; ?>">KW
                        basierend Mobilitätsplanung hochladen</a>
                </li>
                <li>
                    <a href="?action=showDeliveryPlanWeek" data-target="showDeliveryPlanWeek"
                       class="sts_submenu <?php if ($this->action == "showDeliveryPlanWeek") echo 'selected'; ?>">KW
                        basierend Mobilitätsplanung anzeigen</a>
                </li>
                <li>
                    <a href="?action=showAssignment" data-target="showAssignment"
                       class="sts_submenu <?php if ($this->action == "showAssignment") echo 'selected'; ?>">Übersicht
                        Auslieferung Zuweisungen</a>
                </li>
            </ul>
        </div>
    </div>
    <?php if (is_array($this->msgs)): ?>
        <div class="row ">
            <div class="columns twelve">
		<span class="error_msg">
			<?php echo implode('<br>', $this->msgs); ?>
		</span>
            </div>
        </div>
    <?php endif; ?>
    <?php
    if (isset($this->action) && $this->action == "exportcsv") : ?>
        <div class="row ">
            <div class="columns twelve">
                <h1>Als CSV Datei exportieren</h1>
                <?php
                if (isset($this->qform_csv))
                    echo 'Bitte wählen Sie die benötigten Spalten.<br> (keine Auswahl = leere Spalte) <br>' . $this->qform_csv->getContent();
                else
                    echo '<a href="/downloadcsv.php?fname=' . $this->csv_fname . '">CSV Datei herunterladen</a><br>' ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "uploadDeliveryPlan") : ?>
        <div class="row ">
            <div class="columns twelve">
                <?php
                if (isset($this->qform_deliveryplan))
                    echo '<h1>Mobilitätsplanung hochladen</h1>' . $this->qform_deliveryplan->getContent(); ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "uploadDeliveryPlanWeek") : ?>
        <div class="row ">
            <div class="columns twelve">
                <?php
                if (isset($this->qform_deliveryplan_week)) :
                    echo '<h1>KW basierend Mobilitätsplanung hochladen</h1>' . $this->qform_deliveryplan_week->getContent(); ?>
                    <h2>Beispiel CSV</h2>
                    RGB;41;42;43;44<br>
                    7101;8;15;0;0<br>
                    7104;5;29;0;0<br>
                    7106;3;32;56;53<br>
                    7108;15;5;8;0<br>
                <?php endif; ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "showDeliveryPlan") : ?>
        <div class="row ">
            <div class="columns twelve" style="overflow-x: scroll; max-height: 550px; display: block">
                <h1>Mobilitätsplanung <?php echo date('Y'); ?>
                    <?php if (isset($this->vehicle_variants_name)) echo ': ' . $this->vehicle_variants_name; ?></h1>
                <?php
                if (isset($this->qform_variantselect)) {
                    echo $this->qform_variantselect->getContent();
                } else {
                    if (!empty($this->listObjects)) {
                        $processed_listObjects[] = array('headingone' => $this->listObjectsTableHeadings);
                        $processed_listObjects = array_merge($processed_listObjects, $this->listObjects);
                        $displaytable = new DisplayTable ($processed_listObjects, array('id' => 'deliveryplanshow'));
                        echo $displaytable->getContent();
                    } else
                        echo 'Keine Daten zu dieser Konfiguration gefunden';
                }


                ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "showDeliveryPlanWeek") : ?>
        <div class="row ">
            <div class="columns twelve">
                <h1>Mobilitätsplanung <?php echo date('Y'); ?>
                    <?php if (isset($this->vehicle_variants_name)) echo ': ' . $this->vehicle_variants_name; ?></h1>
                <?php
                if (isset($this->qform_variantselect)) {
                    echo $this->qform_variantselect->getContent();
                } else {
                    if (!empty($this->listObjects)) {
                        $processed_listObjects[] = array('headingone' => $this->listObjectsTableHeadings);
                        $processed_listObjects = array_merge($processed_listObjects, $this->listObjects);
                        $displaytable = new DisplayTable ($processed_listObjects, array('id' => 'deliveryplanshow'));
                        echo $displaytable->getContent();
                    } else
                        echo 'Keine Daten dieser Fahrzeug Variante gefunden';
                }


                ?>
            </div>
        </div>
    <?php
    elseif (isset($this->action) && $this->action == "showAssignment") : ?>
        <div class="row ">
            <div class="columns twelve">
                <h1>Übersicht auszulieferende Fahrzeuge/Ladesäulen Zuweisung :
                    <?php if (isset($this->vehicle_variants_name)) echo ': ' . $this->vehicle_variants_name; ?></h1>
                <?php
                if (isset($this->qform_variantselect)) {
                    echo $this->qform_variantselect->getContent();
                } else {
                    if (!empty($this->overview_assignment)) {
                        echo $this->overview_assignment;
                    } else
                        echo 'Keine Daten zu dieser Konfiguration gefunden';
                }


                ?>
            </div>
        </div>
    <?php
    else:
        //endofifsetaction
        ?>
        <?php if ($this->showingDivisions === true) { ?>
        <div class="row ">
            <div class="columns twelve">
                <a href="#" data-target="division_list_content" class="parent_hidden_text">
                    <span class="genericon genericon-plus"> </span><span>Niederlassungen zeigen</span>
                </a>
            </div>
        </div>
        <?php
    } ?>
        <div class="row">
            <div class="columns eight
			<?php
            if ($this->showingDivisions === true) echo ' child_hidden_text division_list_content';
            ?>">
                <?php if (!empty($this->overview)) $this->overview->printContent(); ?>

            </div>
        </div>
        <?php


        if ($this->showingDivisions === true) {

            ?>
            <div class="row " style="display: none">
                <div class="columns twelve">
                    <h1>OZ Änderungsfunktion</h1>

                </div>
            </div>

            <div class="row " style="display: none">
                <div class="columns four">
                    <?php if (isset($this->qform_transfer_division)) echo $this->qform_transfer_division->getContent(); ?>
                    <br><br>
                    Beispiel CSV Datei: <br><br>
                    Standort;Alte OZ;Neue OZ;<br><br>
                    Berlin 21;711033700020;711133700020;<br>
                    Berlin 57;711033700050;711133700050;<br>
                    <br><br><br>
                </div>
                <div class="columns four">
                    <?php if (isset($this->qform_transfer_zspl)) echo $this->qform_transfer_zspl->getContent(); ?>
                    <br><br>

                </div>
                <div class="columns four">
                    <?php if (isset($this->qform_transfer_depot)) echo $this->qform_transfer_depot->getContent(); ?>
                    <br><br>

                </div>
            </div>

            <!--  here  -->
            <div class="row ">
                <div class="columns twelve">
                    <h1>CSV Datein Hochladen</h1>
                </div>
            </div>
            <div class="row ">
                <div class="columns six">
                    <?php echo $this->qform_zspl->getContent(); ?>
                    <br><br>
                    Beispiel CSV Datei: <br><br>
                    71013370,Bautzen<br>
                    71013371,Meißen<br>
                    71013373,Dresden<br><br><br>
                </div>
                <div class="columns six">
                    <?php echo $this->qform_depot->getContent(); ?>
                    <br><br>
                    Beispiel CSV Datei: <br><br>
                    710133700470;Ebersbach-Neugersdorf 5;"14,610361","50,980138";341890;0;Addresse 1<br>
                    710133700480;Cunewalde 4;"14,474499","51,0947494507";832494;0;<br>
                    710133700490;Görlitz 1;"14,972277","51,1479148865";341521;0;Addresse 3<br>
                </div>
            </div>
        <?php }

    endif;
    ?>

    <div class="row ">
        <div class="columns six">
            <a href="#" onClick="window.history.back();"> Zurück</a>
        </div>
    </div>
</div>