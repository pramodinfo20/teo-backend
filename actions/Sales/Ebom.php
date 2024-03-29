<?php

class ACtion_Ebom extends AClass_Base
{

    private $databaseArray = array(
        "Structure Level" => "structure_level",
        "Struktur Level" => "strucure_level",
        "Strukturebene" => "structure_level",
        "Nummer" => "part_number",
        "Number" => "part_number",
        "Name" => "part_name",
        "Zugewiesene Elementausdrücke" => "element_name",
        "Assigned Item Expressions" => "element_name",
        "Version" => "version",
        "Kontext" => "context",
        "Context" => "context",
        "Lebenszyklusstatus" => "lifecyclestatus",
        "Life Cycle Template" => "lifecyclestatus",
        "State" => "lifecyclestatus",
        "Menge" => "amount",
        "Quantity" => "amount",
        "Unit" => "unit",
        "Einheit" => "unit",
        "Objekttyp" => "objecttype",
        "Object Type" => "objecttype",
        "Phantomfertigungsteil" => "phantomfertigungsteil",
        "Phantom Manufacturing Part" => "phantomfertigungsteil",
        "SML" => "sml",
        "parent_id",
        "workstep"
    );

    private $PentaDataHead = array(
        "penta_number",
        "windchill_part_number",
        "name",
        "second_name"
    );

    private $csvHead = array(
        "Structure Level",
        "Strukturebene",
        "Nummer",
        "Number",
        "Name",
        "Zugewiesene Elementausdrücke",
        "Assigned Item Expressions",
        "Version",
        "Kontext",
        "Context",
        "Lebenszyklusstatus",
        "Life Cycle Template",
        "State",
        "Menge",
        "Quantity",
        "Einheit",
        "Unit",
        "Objekttyp",
        "Object Type",
        "Phantomfertigungsteil",
        "Phantom Manufacturing Part",
        "SML"
    );

    private $newhead = array();

    private $newdata = array();

    private $date;

    private $leitwartePtr;

    private $variant_id = 0;

    private $icountcross = 0;

    private $icountcomp = 0;

    private $variant_types;

    private $variant_type_count;

    private $variant_count;

    private $variant_windchill_names = '';

    private $variant_name;

    private $countduplicate = 0;


    function __construct()
    {

        parent::__construct();
        $this->useCommandMapping = true;
        if ($this->controller) {
            $this->leitwartePtr = $this->controller->getObject('ladeleitwarte');
        }

        $this->date = date('Y-m-d G:i:s O');

    }


    function Init()
    {

        $this->variant_types = $this->leitwartePtr->newQuery('vehicle_variants')
            ->orderBy('type')
            ->groupBy('type')
            ->get('type');
        $this->variant_type_count = count($this->variant_types);
        if (isset($_GET['type'])) {
            $this->variant_windchill_names = $this->leitwartePtr->newQuery('vehicle_variants')
                ->where('type', '=', $_GET['type'])
                ->orderBy('windchill_variant_name', 'desc')
                ->get('windchill_variant_name');
            $this->variant_count = count($this->variant_windchill_names);
        }
        if (isset($_POST['windchill_variant'])) {

            $this->variant_id = $this->leitwartePtr->newQuery('vehicle_variants')
                ->where('windchill_variant_name', '=', $_POST['windchill_variant'])
                ->getOne('vehicle_variant_id')['vehicle_variant_id'];
        }

    }


    function Execute()
    {

        parent::Execute();

    }


    function readCSV()
    {

        $file = fopen($_FILES['ebomcsv']['tmp_name'], 'r');

        $code = 'ISO-8859-1';
        $rowcount = 0;
        while (($row = fgets($file)) !== FALSE) {

            if ($rowcount == 0) {

                if (substr($row, 0, 3) == "\xef\xbb\xbf") {
                    $row = substr($row, 3);
                    $code = 'UTF-8';
                }
            }
            if (preg_match('/^"[^",]*,/', $row)) {
                $row = preg_replace('/"(\s+)$/', '', $row);
                $row = preg_replace('/^"/', '', $row);
                $row = str_replace('""', '"', $row);
            }
            if ($code != 'UTF-8') {
                $row = iconv($code, 'UTF-8', $row);
            }
            $row = str_getcsv($row, ',', '"');
            if ($rowcount == 0) {

                foreach ($row as $element) { // vergleiche Head
                    if (! in_array($element, $this->csvHead)) {
                        return false;
                    } else {
                        $this->newhead[] = $element;
                    }
                }
            } else {
                $this->newdata[$rowcount - 1] = array();
                $columnCount = 0;
                foreach ($row as $element) {
                    $this->newdata[$rowcount - 1][$this->databaseArray[$this->newhead[$columnCount]]] = $element;
                    $columnCount ++;
                }

                if ($this->newdata[$rowcount - 1]['structure_level'] === "0") {
                    unset($this->newdata[$rowcount - 1]['parent_id']);
                    $this->newdata[$rowcount - 1]['part_number'] = $_POST['windchill_variant'];
                    $this->parents[0] = $this->newdata[$rowcount - 1]['part_number'];
                } else {
                    $this->newdata[$rowcount - 1]['parent_id'] = $this->parents[$this->newdata[$rowcount - 1]['structure_level'] - 1];
                    $this->parents[$this->newdata[$rowcount - 1]['structure_level']] = $this->newdata[$rowcount - 1]['part_number'];
                }
                if ($this->newdata[$rowcount - 1]['unit'] != 'each' && $this->newdata[$rowcount - 1]['unit'] != 'Stück') {
                    unset($this->newdata[$rowcount - 1]['unit']);
                    unset($this->newdata[$rowcount - 1]['amount']);
                }
                if (isset($this->newdata[$rowcount - 1]['amount'])) {
                    $this->newdata[$rowcount - 1]['amount'] = str_replace(',', '.', $this->newdata[$rowcount - 1]['amount']);
                }
                $this->newdata[$rowcount - 1]['phantomfertigungsteil'] = ($this->newdata[$rowcount - 1]['phantomfertigungsteil'] == 'Ja' ? 't' : 'f');
                $this->newdata[$rowcount - 1]['workstep'] = (preg_match('/[.]*_' . $_POST['windchill_variant'] . '/', $this->newdata[$rowcount - 1]['part_number'])) ? 't' : 'f';
            }

            $rowcount ++;
        }
        $this->handleduplicates();
        $this->loadintodb();
        $this->msg = "Die Daten wurden erfolgreich in die Datenbank geladen.";

    }


    function handleduplicates()
    {

        // fügt parents in ein array ein und addiert falls unit 'each'(Stück) ist die Menge ansonsten verwerfe Menge und setze Unit auf'nicht Stück'
        $resultarray = array();
        $count = 1;
        foreach ($this->newdata as &$line) {
            $summed = false;
            $parentadded = false;
            for ($i = 0 + $count; $i < count($this->newdata); $i ++) {
                if ($line['part_number'] == $this->newdata[$i]['part_number'] && $line['parent_id'] == $this->newdata[$i]['parent_id'] && isset($this->newdata[$i]['unit'])) {
                    if (! $summed) {
                        $this->newdata[$i]['amount'] += $line['amount'];
                        $summed = true;
                    }
                }
            }
            if (! $summed) {
                $resultarray[$count - 1] = $line;
            }
            $count ++;
        }
        $this->newdata = $resultarray;

    }


    function loadintodb()
    {

        $einfuegearray = array();
        if (! empty($alttabelle = $this->leitwartePtr->newQuery('ebom_variant_components')
            ->where('vehicle_variant_id', '=', $this->variant_id)
            ->where('verbaut_bis', 'is null')
            ->get('*'))) {
            foreach ($this->newdata as $neuzeile) {

                $found = false;
                $count = 0;

                foreach ($alttabelle as $altzeile) {

                    if ($neuzeile['part_number'] == $altzeile['part_number'] && $neuzeile['parent_id'] == $altzeile['parent_id'] && $neuzeile['structure_level'] == $altzeile['structure_level']) { // falls eine Komponente in der Ebom aufgeführt ist und in der Varianten-Komponenten-Tabelle ist vergleiche Komponente

                        $this->checkcomponent($neuzeile);

                        if ($altzeile['amount'] != $neuzeile['amount']) { // falls eine Komponente in einer Ebom mit anderer Anzahl aufgeführt ist

                            $this->leitwartePtr->newQuery('ebom_variant_components')
                                -> // setze endtimestamp für diese Komponente
                            where('vehicle_variant_id', '=', $this->variant_id)
                                ->where('verbaut_bis', 'is null')
                                ->where('part_number', '=', $altzeile['part_number'])
                                ->where('parent_id', '=', $altzeile['parent_id'])
                                ->where('structure_level', '=', $altzeile['structure_level'])
                                ->update([
                                'verbaut_bis'
                            ], [
                                $this->date
                            ]);
                            $this->insertcrosstable($neuzeile);
                        }

                        $einfuegearray[$count] = true;
                        $found = true;
                        // break;
                    }

                    $count ++;
                }
                if (! $found) { // falls eine in der Ebom aufgeführte Komponente in der Varianten-Komponenten-Tabelle nicht der Fahrzeugvariante zugeordnet ist

                    $this->checkcomponent($neuzeile);
                    $this->insertcrosstable($neuzeile);
                }
            }

            /*
             * falls eine in der Datenbank der Fahrzeugvariante zugeordnete Komponente nicht in der Ebom
             * aufgeführt ist
             * setze den endtimestamp für diese Komponente
             */
            $count = 0;
            foreach ($alttabelle as $altzeile) {
                if (! isset($einfuegearray[$count])) {

                    $this->leitwartePtr->newQuery('ebom_variant_components')
                        ->where('vehicle_variant_id', '=', $this->variant_id)
                        ->where('verbaut_bis', 'is null')
                        ->where('part_number', '=', $altzeile['part_number'])
                        ->where('parent_id', '=', $altzeile['parent_id'])
                        ->where('structure_level', '=', $altzeile['structure_level'])
                        ->update([
                        'verbaut_bis'
                    ], [
                        $this->date
                    ]);
                }
                $count ++;
            }
        } else {
            foreach ($this->newdata as $neuzeile) { // falls keine altdaten für dieses fahrzeug gefunden wurden
                $this->checkcomponent($neuzeile);
                $this->insertcrosstable($neuzeile);
            }
        }

    }


    /*
     * prüfe ob diese Komponente in der Komponententabelle vorhanden ist
     * falls ja vergleiche Ebom komponente mit datenbank komponente,
     * ansonsten füge sie der Komponenten Tabelle hinzu
     */
    function checkcomponent($component)
    {

        $updatearray;

        $this->icountcomp ++;
        if (! empty($alt = $this->leitwartePtr->newQuery('components')
            ->where('part_number', '=', $component['part_number'])
            ->getOne('*'))) {
            foreach ($component as $ck => &$cv) {
                if ($ck == 'unit' || $ck == 'parent_id' || $ck == 'ebom_datum' || $ck == 'amount' || $ck == 'structure_level') {
                    continue;
                }

                if ($alt[$ck] != $cv) {
                    $updatearray[$ck] = $cv;
                }
            }
            if (! empty($updatearray)) {
                $updatearray['ebom_datum'] = $this->date;
                $this->leitwartePtr->newQuery('components')
                    ->where('part_number', '=', $component['part_number'])
                    ->update(array_keys($updatearray), array_values($updatearray));
                $this->leitwartePtr->newQuery('components_history')->insert($alt);
            } else {
                $this->leitwartePtr->newQuery('components')
                    ->where('part_number', '=', $component['part_number'])
                    ->update([
                    'ebom_datum'
                ], [
                    $this->date
                ]);
            }
        } else {
            $component['ebom_datum'] = $this->date;
            $this->insertcomponent($component);
        }

    }


    function insertcomponent($component)
    {

        $insertarray = array(
            "part_number" => $component['part_number'],
            "part_name" => $component['part_name'],
            "element_name" => $component['element_name'],
            "version" => $component['version'],
            "context" => $component['context'],
            "lifecyclestatus" => $component['lifecyclestatus'],
            "unit" => $component['unit'],
            "objecttype" => $component['objecttype'],
            "phantomfertigungsteil" => $component['phantomfertigungsteil'],
            "ebom_datum" => $component['ebom_datum'],
            "workstep" => $component['workstep'],
            "sml" => $component['sml']
        );
        if (! $this->leitwartePtr->newQuery('components')->insert($insertarray)) {
            echo "stop";
        }

    }


    /*
     * ordne Sie dann Fahrzeugvariante zu und setze bei der Zuordnung
     * den Start Timestamp auf gegebenen oder jetzigen Timestamp
     */
    function insertcrosstable($component)
    {

        $insertarray = array(
            'part_number' => $component['part_number'],
            'vehicle_variant_id' => $this->variant_id,
            'verbaut_von' => $this->date,
            'parent_id' => $component['parent_id'],
            'structure_level' => $component['structure_level']
        );
        if ($component['unit'] == 'each' || $component['unit'] == 'Stück') {
            $insertarray['amount'] = $component['amount'];
        }
        if (! $this->leitwartePtr->newQuery('ebom_variant_components')->insert($insertarray)) {
            echo "stop";
        }
        $this->icountcross ++;

    }


    function readPentaCSV()
    {

        $file = fopen($_FILES['windpenta']['tmp_name'], 'r');
        $rowcount = 0;
        while (($row = fgets($file)) !== FALSE) {
            $row = iconv('|ISO-8859-1', 'UTF-8', $row);
            $row = str_getcsv($row, ';');
            if (empty($row[0]) || empty($row[1])) {
                continue;
            }
            if ($rowcount == 0) {} else {
                $count = 0;
                $this->newdata[$rowcount - 1] = array();
                foreach ($row as $r) {
                    $this->newdata[$rowcount - 1][$this->PentaDataHead[$count]] = $r;
                    $count ++;
                }
            }
            $rowcount ++;
        }
        $this->insertPentaTable();

    }


    function insertPentaTable()
    {

        foreach ($this->newdata as $row) {
            if (empty($this->leitwartePtr->newQuery('penta_artikel')
                ->where('penta_number', '=', $row['penta_number'])
                ->getOne('*'))) {
                $this->leitwartePtr->newQuery('penta_artikel')->insert($row);
            }
        }

    }


    function WriteHtmlContent()
    {

        parent::WriteHtmlContent();
        if ($this->command_result) {
            echo "CSV Datei wurde erfolgreich gelesen";
        }

        echo <<<HEREDOC

        <form method="GET" name="suchform" action="{$_SERVER['PHP_SELF']}">
            <table style="width:50%">
                <tbody>
                    <tr >
                        <th><b>Fahrzeug Typ</b></th><th><b>Konfiguration</b></th>

                    </tr>
                    <tr >
                        <td>
                            <select name="type" id="selectVariantType" size="{$this->variant_type_count}" style="width:100%; height:100%" onchange="var s=document.getElementById('selectVariant'); if(s)s.selectedIndex=-1; this.form.submit()">
HEREDOC;

        foreach ($this->variant_types as $vtype) {
            $type = empty($vtype['type']) ? '???' : $vtype['type'];
            if ($type != 'TEST') {
                echo "<option value=\"{$vtype['type']}\"";
                if ($_GET['type'] == $type) {
                    echo "selected";
                }
                echo ">$type</option>";
            }
        }

        echo '</select></td>';

        if ($this->variant_windchill_names != '') {
            echo '<td><select name="windchill_variant" id="selectVariant" size="20" style="width:100%" onchange="this.form.submit()">';
            foreach ($this->variant_windchill_names as $vname) {
                if ($vname['windchill_variant_name'] != 'VariableKonfiguration') {
                    echo "<option value=\"" . $vname['windchill_variant_name'] . "\"";

                    if ($_GET['windchill_variant'] == $vname['windchill_variant_name']) {
                        echo " selected";
                    }
                    echo ">" . $vname['windchill_variant_name'] . "</option>";
                }
            }
            echo '</select></td>';
        }
        echo '</tr>';
        echo '<tr><td>' . $this->msg . '</td></tr>';

        echo '</tbody></table><input type="hidden" name="action" value="' . $this->action . '">';

        echo '</form>';
        if (isset($_GET['windchill_variant'])) {
            echo <<<HEREDOC
        <form style="width:400px;"action="{$_SERVER['PHP_SELF']}" method="post" enctype="multipart/form-data">
            <fieldset>
            <legend>Ebom Datei hochladen:</legend>
            <p style="color:red">In D-Baureihe->Struktur setze einen Option-Filter auf die gewünschte Vehicle Variante. Dann exportiere unter Berichte eine Mehrstufige BOM  mit der Anzeige:Webinterface.</p>
            <a href="http://wiki.streetscooter.eu/wiki/images/e/e8/Berichte_von_eBOM_und_mBOM.mp4"> Wie leite ich eine Ebom aus </a><br>
            <a href="http://wiki.streetscooter.eu/wiki/index.php/EBOM-Definition">Wie setzt sich die Ebom zusammen</a>      
                <input type="file" name="ebomcsv"><br> 
            <input type="submit" name="command[readCSV]" value="Hochladen">
            <input type="hidden" name="action" value="{$this->action}">
            <input type="hidden" name="windchill_variant" value="{$_GET['windchill_variant']}">
            </fieldset>
        </form>
HEREDOC;
        }
        echo <<<HEREDOC
        <form style="width:400px" action="{$_SERVER['PHP_SELF']}" method="post" enctype="multipart/form-data">
            <fieldset>
            <legend>Windchill-Pentabezug Datei hochladen: </legend>
            <input type="file" name="windpenta"><br>
            <input type="submit" name="command[readPentaCSV]" value="Hochladen">
            <input type="hidden" name="action" value="{$this->action}">
            </fieldset>
        </form>
        
        
        
        
HEREDOC;

    }

}
?>