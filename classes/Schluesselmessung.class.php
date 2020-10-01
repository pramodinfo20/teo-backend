<?php

require_once $_SERVER['STS_ROOT'] . "/includes/sts-array-tools.php";

class Schluesselmessung extends AClass_Base {
    private $diagnosePtr;
    private $data;
    private $head_must = array("Pset number", "Status", "Torque status", "Angle status", "Torque peak status", "Result number", "Strategy",
        "Torque result", "Angle result", "Torque peak result", "Angle at torque peak result", "Description", "Date", "Time",
        "Batch status", "Batch number", "Batch size", "Max. coherent NOK", "Unit of measurement", "Cycle start", "Torque min.", "Torque target",
        "Torque max.", "Start angle", "Angle min.", "Angle target", "Angle max.", "Barcode", "Barcode 2", "Barcode 3", "Barcode 4",
        "Transducer S/N", "Torque compensation", "Result Type", "Detailed status");
    private $column_names = array("Barcode", "Barcode 2", "Transducer S/N", "Date", "Time", "Pset number", "Description", "Strategy",
        "Torque result", "Angle result", "Torque peak result", "Angle at torque peak result");
    private $databasemap = array("screw_ident", "vin", "transducer_sn", "time_of_measurement", "pset_no", "description",
        "strategy", "torque_result", "angle_result", "torque_peak", "angle_torque_peak");
    private $messungshead = array("Schrauben-IdentifikationsNr", "VIN", "Messwerkzeug", "Messungs-Zeitpunkt", "Psatz-Nr",
        "Beschreibung", "Strategie", "Drehmoment-Ergebnis", "Winkel-Ergebnis", "Drehmoment-Spitzenwert", "Winkel-Spitzenwert", "Messung");
    private $uebersicht = array("Schrauben-IdentifikationsNr", "Schraubenklasse", "Solldrehmoment", "Anzahl Messungen", "Fahrzeug pro Verschraubung", "letzte Messung");
    private $dropdown = array();
    private $newdata;
    private $olddata;
    private $max;
    private $screw_id;
    private $overview;
    public $utg;
    public $otg;
    public $neuEintrag = false;


    function __construct($diagnosePtr) {
        parent::__construct();
        $this->newdata = &InitSessionVar($this->S_actionVar['newdata'], null);
        $this->diagnosePtr = $diagnosePtr;
    }

    function getmessungshead() {
        return $this->messungshead;
    }

    function print_graph() {
        $info = $this->diagnosePtr->newQuery('qm_torque_information')->where('screw_id', '=', $this->screw_id)->getOne('*');
//         echo shell_exec("/var/projects/torquePlotter/build/TORQUEPLOTTER "+$info['screw_ident']);
        shell_exec("/home/sts_rz5q/torquePlotter/build/TORQUEPLOTTER " . $info['screw_ident'] . " " . $this->max);
    }

    function setmax($max) {
        $this->max = $max;
    }

    function getmax() {
        return $this->max;
    }

    function setscrew_id($screw_id) {
        $this->screw_id = $screw_id;
    }

    function getnewdata() {
        if (!empty($this->newdata)) {
            return $this->newdata;
        } else {
            return false;
        }
    }

    function setdropdown($alle = false) {
        if ($alle) {
            $this->dropdown[0]['screw_id'] = "Alle";
        }
        $drop = $this->diagnosePtr->newQuery('qm_torque_information')->get('screw_id');
        $this->dropdown = array_merge($this->dropdown, $drop);

    }

    function getdropdown() {
        if (!empty($this->dropdown)) {
            return $this->dropdown;
        } else {
            return false;
        }
    }


    function setolddata() { //setzt ein Array mit Daten aus der Datenbank für die gesetzte screw id
        $info = $this->diagnosePtr->newQuery('qm_torque_information')->where('screw_id', '=', $this->screw_id)->getOne('*');
        if (empty($info) || empty($this->olddata = $this->diagnosePtr->newQuery('qm_torque_mesurements')->where('screw_ident', '=', $info['screw_ident'])->orderBy('time_of_measurement', 'desc')->limit($this->max)->get('*'))) {
            return false;
        }
        $count = 0;

        foreach ($this->olddata as &$old) {
            $old['Messung'] = $count;
            $old['screw_ident'] = $this->screw_id;

            $count++;
        }
        $this->utg = $info['torque_setvalue'] - $info['torque_setvalue'] * $info['tolerance'] / 100;
        $this->otg = $info['torque_setvalue'] + $info['torque_setvalue'] * $info['tolerance'] / 100;
        return true;


    }


    function getolddata() { //holt daten aus der datenbank
        return $this->olddata;
    }

    function deletenewdata() { //löscht neue hochzuladene daten
        $this->newdata = false;
    }

    function readfromDatabase() {
        //select distinct schraub_ident from schraub_messungen  order by schraub_ident;
    }

    function savetodatabase() { //speichert hochgeladene daten in die Datenbank
        $loaddata = array_slice($this->newdata, 1);
        foreach ($loaddata as $load) {
            $load[0] = str_replace('_', '-', $load[0]);
//             $load[3]=date('Y-m-d h:i:s');
            $ts = strtotime($load[3] . ' ' . $load[4]);
            $load[3] = date('Y-m-d H:i:s', $ts);

//             str_replace('.', '-', $load[3]);
//             $load[3] = $load[3]." ".$load[4];

            if (!empty($this->diagnosePtr->newQuery('qm_torque_information')->where('screw_id', '=', $load[0])->getOne('*'))) {
                if (empty($row = $this->diagnosePtr->newQuery('qm_torque_mesurements')->where('transducer_sn', '=', $load[2])->where('time_of_measurement', '=', $load[3])->getOne('*'))) {
                    $this->insertrow($load);
                }
            } else {
                return false;
            }


        }
        $this->deletenewdata();
        return true;
    }


    function insertrow($load) { //fügt eine Reihe in die Datenbank ein
        $count = 0;
        $insertarray = array();
        foreach ($load as $l) {
            if ($count == 0) {
                $temp = $this->diagnosePtr->newQuery('qm_torque_information')->where('screw_id', '=', $l)->getOne('*');
                $l = $temp['screw_ident'];
            }

            if ($l == $load[4]) {
                continue;
            }
            if ($count == 4 && empty($l)) {
                $l = 0;
            }
            if ($count == 7 || $count == 8 || $count == 9 || $count == 10) {
                if (empty($l)) {
                    $l = '0.0';
                }
                $l = str_replace(',', '.', $l);
            }


            $insertarray[$this->databasemap[$count]] = $l;
            $count++;

        }
        $this->diagnosePtr->newQuery('qm_torque_mesurements')->insert($insertarray);
    }

    function readCsv($path) { //liesst eine CSV mit messwerten ein
        $line_count = 0;
        $dummysec = 0;
        $dummymin = 0;
        $dummyh = 0;
        if (($msrmntCsv = fopen($path, "r")) !== FALSE) {
            while (($rows = fgets($msrmntCsv)) !== FALSE) {
                $rows = iconv('|ISO-8859-1', 'UTF-8', $rows);      //lese einzelne reihen um zu konvertieren
                $temp = "";
                $row = str_getcsv($rows, ";");
                $num = count($row);
                if ($num < count($this->head_must)) {
                    $temp = $rows;
                    continue;
                }
                if ($num > count($this->head_must)) {
                    return false;
                }
                if ($line_count == 0) {
                    $collumn_count = 0;
                    foreach ($this->head_must as $h) {

                        if ($h != $row[$collumn_count]) {
                            return false;
                        }
                        $collumn_count++;
                    }
                } else {
                    if (!empty($row[7]) && !empty($row[12])) {
                        if (empty($row[13])) {
                            $row[13] = $this->makedummytime($dummyh, $dummymin, $dummysec);
                        }

                    } else {
                        return false;
                    }

                }
                $this->newdata[] = $row;
                $line_count++;
            }
            fclose($msrmntCsv);
        }
    }

    function makedummytime(&$dummyh, &$dummymin, &$dummysec) { //erstellt dummyzeiten falls die zeit nicht befüllt ist
        if ($dummysec == 59) {
            $dummymin += 1;
            if ($dummymin == 60) {
                $dummymin = 0;
                $dummyh += 1;
            }
            $dummysec = 0;

        } else {
            $dummysec += 1;
        }
        return sprintf("%02d:%02d:%02d", $dummyh, $dummymin, $dummysec);
    }

    function reduceNew() {   //reduziert das array mit den hochgeladenen Daten aus der CSV auf die Spalten der Datenbank
        $column_numbers = $this->array_select_columns_csv($this->newdata, $this->column_names);
        $this->newdata = array_select_columns($this->newdata, $column_numbers);
    }

    function filter($array) { //filtert neue daten
        $result = array();
        $count = 0;
        $head = $array[0];

        $count = 0;
        foreach ($array as $arr) {
            $arr[0] = str_replace('_', '-', $arr[0]);
            if ($count == 0) {
                $result[] = $arr;
                $count = 1;
            }
            if ($arr[0] == $this->screw_id || $this->screw_id == "Alle") {
                $result[] = $arr;
            }
        }


        $temp[] = $head;
        $result[0] = NULL;
        for ($i = count($result); $i > count($result) - $this->max; $i--) {

            $temp[] = $result[$i - 1];
        }

        $result = $temp;
        return $result;
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

    function setoverview() {
        $this->overview = $this->diagnosePtr->newQuery('qm_torque_mesurements')
            ->join('qm_torque_information', 'using (screw_ident)', 'left join')
            ->groupBy('screw_ident, screw_id, screw_category, torque_setvalue')
            ->get_no_parse('screw_id, screw_category, torque_setvalue,
            count(time_of_measurement) AS anzahl_messungen,
            COUNT (DISTINCT vin) AS gemessene_fahrzeuge, 
            max(time_of_measurement) as letzte_messung');
    }

    function getoverview() {
        if (!empty($this->overview)) {
            return $this->overview;
        }
        return false;
    }

    function getuebersicht() {
        return $this->uebersicht;
    }

    function mittelwerttorque($data) {
        $mittelwert = 0;
        $count = 0;
        foreach ($data as $d) {
            $mittelwert += $d['torque_result'];
            $count++;
        }
        return $mittelwert / $count;
    }

    function standardabwtorque($data, $mittelw) {
        $stda = 0;
        $mittelwert = $mittelw;
        $count = 0;
        foreach ($data as $d) {
            $stda += pow(($d['torque_result'] - $mittelwert), 2);
            $count++;
        }
        return $stda / $count;
    }

    function cptorque($otg, $utg, $standabw) {
        return ($otg - $utg) / (6 * $standabw);
    }
}

