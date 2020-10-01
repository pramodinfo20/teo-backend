<?php
require_once $_SERVER['STS_ROOT'] . "/includes/sts-array-tools.php";

class Schraubdaten extends AClass_Base {
    private $newdata;
    private $diagnosePtr;
    private $head_must = array("Index Schrauben-ID", "Working station", "Titel AA", "AA- Schritt", "Bezeichnung Arbeitsschritt", "Modell",
        "Artikelnummer", "Bezeichnung", "Erstelldatum", "Änderungsdatum", "Letzter Ausgabestand in Linie",
        "Gültiger Status / Revision", "Anzahl Schrauben", "Drehmoment", "Schraubensicherung", "Markierung", "Drehmomentwerkzeug",
        "Toleranzen für angesetztes Drehmoment", "Dokumentation des Ergebnisses", "Prüfwerkzeug", "Schraubkategorien",
        "Zuständiger Bauteilverantwortlicher / Entwickler", "Schraubnummer", "Geprüft durch:", "Bemerkung durch Engineering:", "Ergänzungen der AA");
    private $column_names = array("Index Schrauben-ID", "Working station", "Titel AA", "AA- Schritt", "Bezeichnung Arbeitsschritt", "Drehmoment",
        "Modell", "Artikelnummer", "Bezeichnung", "Schraubensicherung", "Markierung", "Drehmomentwerkzeug",
        "Prüfwerkzeug", "Schraubkategorien", "Zuständiger Bauteilverantwortlicher / Entwickler", "Toleranzen für angesetztes Drehmoment");
    private $databasemap = array("screw_id", "workstation_id", "workstation_title", "workstep_id", "workstep_name", "torque_setvalue",
        "model", "windchill_partn_screw", "screw_name", "screw_securing", "mark", "torque_tool", "examination_tool", "screw_category",
        "component_responsible", "tolerance");
    private $data;
    private $workstations;
    private $worksteps;


    function __construct($diagnosePtr) {
        parent::__construct();
        $this->newdata = &InitSessionVar($this->S_actionVar['newdata'], null);
        $this->diagnosePtr = $diagnosePtr;
    }

    function getnewdata() {
        if (!empty($this->newdata)) {
            return $this->newdata;
        } else {
            return false;
        }
    }

    function deletenewdata() {
        $this->newdata = false;
    }

    function readCsv($path) {
        $line_count = 0;
        if (($msrmntCsv = fopen($path, "r")) !== FALSE) {
            while (($rows = fgets($msrmntCsv)) !== FALSE) {
                $rows = $temp . iconv('ISO-8859-1', 'UTF-8', $rows);     //lese einzelne reihen um zu konvertieren
                $temp = "";
                $row = str_getcsv($rows, ";", '"');
                $num = count($row);
                if ($num < count($this->head_must)) {
                    $temp = $rows;
                    continue;
                } else if ($num > count($this->head_must)) {
                    $this->deletenewdata();
                    return false;
                }
                if ($line_count == 0) {
                    $collumn_count = 0;
                    foreach ($this->head_must as $h) {

                        if ($h != $row[$collumn_count]) {
                            $this->deletenewdata();
                            return false;
                        }
                        $collumn_count++;
                    }
                }

                if (empty($row[0]) || empty($row[13]) || empty($row[17])) {
                    continue;
                }
                $this->newdata[] = $row;
                $line_count++;
            }
        }
        fclose($msrmntCsv);
        return true;
    }

    function savetodatabase() {
        $loaddata = array();
        $column_numbers = $this->array_select_columns_csv($this->newdata, $this->column_names);
        $loaddata = array_select_columns(array_slice($this->newdata, 1), $column_numbers);
        foreach ($loaddata as $load) {
            $row = $this->diagnosePtr->newQuery('qm_torque_information')->where('screw_id', '=', $load[0])->getOne('*');
            if (empty($row)) {
                $this->insertrow($load);
            } else {
                for ($i; $i < count($load); $i++) {
                    if ($load[$i] != $row[$this->databasemap[$i]]) {
                        $this->diagnosePtr->newQuery('qm_torque_information')->where('screw_id', '=', $load[0])->update($this->databasemap[$i], $load[$i]);
                    }
                }
            }
        }
        $this->deletenewdata();
    }

    function insertrow($load) {
        $count = 0;
        $insertarray = array();
        if (empty($this->diagnosePtr->newQuery('workstations')->where('workstation_id', '=', $load[1])->getOne('*'))) {
            $this->diagnosePtr->newQuery('workstations')->insert(['workstation_id' => $load[1], 'workstation_title' => $load[2]]);
        }
        if (empty($this->diagnosePtr->newQuery('worksteps')->where('workstep_id', '=', $load[3])->getOne('*'))) {
            $this->diagnosePtr->newQuery('worksteps')->insert(['workstep_id' => $load[3], 'workstep_name' => $load[4]]);
        }
        foreach ($load as $l) {


            if ($count == 4 || $count == 2) {
                $count++;
                continue;
            }
            if ($count == 5) {
                $l = intval($l);
            }
            if ($count == 9 || $count == 10) {
                $l = !empty($l);
            }
            if ($count == 15) {

                if (preg_match('/[^0-9]*([0-9]+[.]?[0-9]*)[^0-9.]?.*/', $l, $match))
                    $l = $match[1];
                else
                    $l = 0;
            }
            $insertarray[$this->databasemap[$count]] = $l;
            $count++;
        }
        $this->diagnosePtr->newQuery('qm_torque_information')->insert($insertarray);

    }


    function array_select_columns_csv($array, $column_names) {
        $result = array();
        $column_numbers = array();


        foreach ($column_names as $colname) {
            $position = 0;
            foreach ($array[0] as $arr) {
                if ($colname == $arr) {
                    $column_numbers[] = $position;
                    break;
                }
                $position++;
            }

        }

        return $column_numbers;
    }
}

?>
