<?php
/**
 * Chrginfra_ebgController.class.php
 * Controller for User Role chrginfra_ebg
 * @author Pradeep Mohan
 */


class Chrginfra_ebgController extends ChrginfraController {
    protected $delimiter = NULL;

    function save_stations_upload() {
        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }

        if (($handle = fopen($filename, "r")) !== FALSE) {

            if (substr_count(fgets($handle), ';') == 9)
                $this->delimiter = ';';
            else if (substr_count(fgets($handle), ',') == 9)
                $this->delimiter = ',';
            else
                $this->msgs[] = 'Fehler mit der CSV Datei. Die Datei wird nicht hochgeladen!';

            rewind($handle);

            while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {

                if (sizeof($data) == 9) {

                    if (!preg_match('/^\d+$/', $data[8])) {
                        continue;
                    }

                    $depot_oz = $data[0];
                    if (!preg_match('/^([0-9]){12}$/', $depot_oz)) {
                        $this->csv_stn_msgs[] = 'Korrigieren Sie bitte die ZSP OZ Nummer.';
                        break;
                    }

                    $restriction_protection_values = array($data[1] * 215.0, $data[2] * 215.0, $data[3] * 215.0);  //stored as power

                    $newStation['name'] = $data[4];

                    if (!preg_match('/^([0-9]){7}([rRlL])?$/', $newStation['name'])) {
                        $this->csv_stn_msgs[] = 'Fehler mit Ladepunkte Name ' . $newStation['name'] . '. Korrigieren Sie bitte die CSV Datei';
                        break;
                    }

                    $depot_id = $this->ladeLeitWartePtr->depotsPtr->getIdFromOZ($depot_oz);
                    $station_provider = $this->ladeLeitWartePtr->depotsPtr->newQuery()->where('depot_id', '=', $depot_id)->getVal('stationprovider');
                    if ($station_provider != $this->stationprovider) {
                        $this->csv_stn_msgs[] = 'ZSP ' . $depot_oz . ' gehört nicht diesem Anbieter. Das Hochladen wird abgebrochen.';
                        break;
                    }
                    $phases = $this->ladeLeitWartePtr->restrictionsPtr->getAllPhases($depot_id);
                    $this->ladeLeitWartePtr->restrictionsPtr->updateRestrictionProtection($depot_id, $restriction_protection_values, $phases);

                    $restrictions = $phases;
                    $restrictions_names = array();


                    if (!empty($restrictions)) {
                        foreach ($restrictions as $restriction) {
                            $restriction_id = $restriction['restriction_id'];
                            $restrictions_names[$restriction_id] = $restriction['name'];
                        }

                    }


                    $newStation['restriction_id'] = array_search($data[5], $restrictions_names);
                    $newStation['restriction_id2'] = array_search($data[6], $restrictions_names);
                    $newStation['restriction_id3'] = array_search($data[7], $restrictions_names);

                    $newStation['station_power'] = ( float )$data[8] * 215.0;


                    if (($newStation['restriction_id'] == 'null' || empty($newStation['restriction_id'])) &&
                        ($newStation['restriction_id2'] == 'null' || empty($newStation['restriction_id2'])) &&
                        ($newStation['restriction_id3'] == 'null' || empty($newStation['restriction_id3']))) {

                        $this->csv_stn_msgs[] = 'Fehler mit CSV Datei. Korrigieren Sie bitte die CSV Datei';
                        break;

                    } else {

                        if ($newStation['restriction_id'] == 'null' || empty($newStation['restriction_id'])) $newStation['restriction_id'] = NULL;
                        if ($newStation['restriction_id2'] == 'null' || empty($newStation['restriction_id2'])) $newStation['restriction_id2'] = NULL;
                        if ($newStation['restriction_id3'] == 'null' || empty($newStation['restriction_id3'])) $newStation['restriction_id3'] = NULL;
                        $newStation['depot_id'] = $depot_id;


                        //start update
                        $station = $this->ladeLeitWartePtr->stationsPtr->getFromName($newStation['name'], $depot_id);

                        if (empty($station)) {
                            $newStationId = $this->ladeLeitWartePtr->stationsPtr->add($newStation, $depot_id);
                            $this->csv_stn_msgs[] = $newStation['name'] . " hingezufügt!";

                        } else {

                            $compareStation = $newStation;


                            if ($compareStation['restriction_id'] == 'null' && $compareStation['restriction_id2'] == 'null' && $compareStation['restriction_id3'] == 'null') {
                                $this->csv_stn_msgs[] = 'Bitte wahlen Sie eine Ladegruppe';
                                break;
                            }


                            if ($compareStation['restriction_id'] == 'null' || empty($compareStation['restriction_id'])) $compareStation['restriction_id'] = NULL;
                            if ($compareStation['restriction_id2'] == 'null' || empty($compareStation['restriction_id2'])) $compareStation['restriction_id2'] = NULL;
                            if ($compareStation['restriction_id3'] == 'null' || empty($compareStation['restriction_id3'])) $compareStation['restriction_id3'] = NULL;


                            if (count(array_intersect_assoc($station, $compareStation)) < sizeof($station)) //there is a difference in the arrays and so updated needed
                            {
                                $updateCols = array_keys($compareStation);
                                $updateVals = array_values($compareStation);


                                $this->ladeLeitWartePtr->stationsPtr->save($updateCols, $updateVals, array('station_id', '=', $station["station_id"]));
                                $this->csv_stn_msgs[] = $newStation['name'] . " aktualisiert!";
                                //notifyChanges ( $depotID, $_POST, "editGroup" );
                            }
                        }
                        unset($station);
                        //end update
                    }

                }//end ofwithout untergruppe upload


                else //start 'with Untergruppe' upload
                {
                    if (!preg_match('/^\d+$/', $data[9])) {
                        continue;
                    }

                    $depot_oz = $data[0];
                    if (!preg_match('/^([0-9]){12}$/', $depot_oz)) {
                        $this->csv_stn_msgs[] = 'Korrigieren Sie bitte die ZSP OZ Nummer.';
                        break;
                    }

                    $restriction_protection_values = array($data[1] * 215.0, $data[2] * 215.0, $data[3] * 215.0);  //stored as power

                    $this->depot = $depot_id = $this->ladeLeitWartePtr->depotsPtr->getIdFromOZ($depot_oz);

                    $station_provider = $this->ladeLeitWartePtr->depotsPtr->newQuery()->where('depot_id', '=', $depot_id)->getVal('stationprovider');
                    if ($station_provider != $this->stationprovider) {
                        $this->csv_stn_msgs[] = 'ZSP ' . $depot_oz . ' gehört nicht diesem Anbieter. Das Hochladen wird abgebrochen.';
                        break;
                    }

                    $phases = $this->ladeLeitWartePtr->restrictionsPtr->getAllPhases($depot_id);
                    $this->ladeLeitWartePtr->restrictionsPtr->updateRestrictionProtection($depot_id, $restriction_protection_values, $phases);

                    $typeofentry = $data[4];

                    if ($typeofentry == 'LP' || $typeofentry == 'LPT') {

                        $newStation['name'] = $data[5];

                        if (!preg_match('/^([0-9]){7}([rRlL])?$/', $newStation['name'])) {
                            $this->csv_stn_msgs[] = 'Fehler mit Ladepunkte Name ' . $newStation['name'] . '. Korrigieren Sie bitte die CSV Datei';
                            break;
                        }


                        $this->getRestrictions();
                        $this->getRestrictionsNames();

                        $restrictions_names = $this->restrictionsNames;


                        $newStation['restriction_id'] = array_search($data[6], $restrictions_names);
                        $newStation['restriction_id2'] = array_search($data[7], $restrictions_names);
                        $newStation['restriction_id3'] = array_search($data[8], $restrictions_names);

                        $newStation['station_power'] = ( float )$data[9] * 215.0;


                        if (($newStation['restriction_id'] == 'null' || empty($newStation['restriction_id'])) &&
                            ($newStation['restriction_id2'] == 'null' || empty($newStation['restriction_id2'])) &&
                            ($newStation['restriction_id3'] == 'null' || empty($newStation['restriction_id3']))) {


                            $this->csv_stn_msgs[] = 'Fehler mit CSV Datei (' . $newStation['name'] . '). Korrigieren Sie bitte die CSV Datei';
                            break;

                        } else {

                            if ($newStation['restriction_id'] == 'null' || empty($newStation['restriction_id'])) $newStation['restriction_id'] = NULL;
                            if ($newStation['restriction_id2'] == 'null' || empty($newStation['restriction_id2'])) $newStation['restriction_id2'] = NULL;
                            if ($newStation['restriction_id3'] == 'null' || empty($newStation['restriction_id3'])) $newStation['restriction_id3'] = NULL;
                            $newStation['depot_id'] = $depot_id;


                            //start update
                            $station = $this->ladeLeitWartePtr->stationsPtr->getFromName($newStation['name'], $depot_id);

                            if (empty($station)) {

                                $newStationId = $this->ladeLeitWartePtr->stationsPtr->add($newStation, $depot_id);
                                $this->csv_stn_msgs[] = "Ladepunkte " . $newStation['name'] . " hingezufügt!";

                            } else {

                                $compareStation = $newStation;


                                if ($compareStation['restriction_id'] == 'null' && $compareStation['restriction_id2'] == 'null' && $compareStation['restriction_id3'] == 'null') {
                                    $this->csv_stn_msgs[] = 'Bitte wahlen Sie eine Ladegruppe';
                                    break;
                                }


                                if ($compareStation['restriction_id'] == 'null' || empty($compareStation['restriction_id'])) $compareStation['restriction_id'] = NULL;
                                if ($compareStation['restriction_id2'] == 'null' || empty($compareStation['restriction_id2'])) $compareStation['restriction_id2'] = NULL;
                                if ($compareStation['restriction_id3'] == 'null' || empty($compareStation['restriction_id3'])) $compareStation['restriction_id3'] = NULL;

                                //station_id is not available in the CSV, so we retrieve it from the database and add it to the compare station
                                $compareStation["station_id"] = $station["station_id"];

                                //or it will update all the time since $compareStation does not contain the vehicle_variant_value_allowed
                                unset($station['vehicle_variant_value_allowed']);

                                if (count(array_intersect_assoc($station, $compareStation)) < sizeof($station)) //there is a difference in the arrays and so updated needed
                                {
                                    $updateCols = array_keys($compareStation);
                                    $updateVals = array_values($compareStation);

                                    //@todo check if the update function returned success before adding the msg that the station was updated
                                    $this->ladeLeitWartePtr->stationsPtr->newQuery()->where('station_id', '=', $station["station_id"])->where('depot_id', '=', $depot_id)->update($updateCols, $updateVals);
// 										$this->ladeLeitWartePtr->stationsPtr->save($updateCols,$updateVals,array('station_id','=',$station["station_id"]));
                                    $this->csv_stn_msgs[] = "Ladepunkte " . $newStation['name'] . " aktualisiert!";
                                    //notifyChanges ( $depotID, $_POST, "editGroup" );
                                }
                            }

                            if ($typeofentry == 'LPT') {

                                // can also use the short ternary operator here like so $d = $a ?: $b ?: $c;
                                if (isset($newStation['restriction_id'])) $updateRestrictionId = $newStation['restriction_id'];
                                else if (isset($newStation['restriction_id2'])) $updateRestrictionId = $newStation['restriction_id2'];
                                else if (isset($newStation['restriction_id3'])) $updateRestrictionId = $newStation['restriction_id3'];
                                $this->ladeLeitWartePtr->restrictionsPtr->save(array('trenner'), array(TRUE), array('restriction_id', '=', $updateRestrictionId));

                            }
                            unset($station);

                        }//end update
                    }//if station

                    else if ($typeofentry == "GP") {

                        $currentdepot = $this->currentdepot;

                        $this->getRestrictions();
                        $this->getRestrictionsNames();

                        $post_parent_restrictions = $parentRestrictions = $restrictions = $this->restrictions;


                        $newRestriction = array();
                        $newRestriction['name'] = $data[5];
                        $parent_restriction = $data[6];

                        if ($parent_restriction != '' && !empty($parent_restriction))
                            $newRestriction['parent_restriction_id'] = array_search($data[6], $this->restrictionsNames);
                        else $newRestriction['parent_restriction_id'] = NULL;

                        if ($newRestriction['parent_restriction_id'] == NULL) {
                            $this->msgs[] = 'Fehler CSV Datei (' . $newRestriction['name'] . ')';

                            continue;
                        }
                        $newRestriction['power'] = $data[9];


                        if (array_search($newRestriction['name'], $this->restrictionsNames) === FALSE) {

                            $newRestriction['power'] = ( float )$newRestriction['power'] * 215.0;

                            if ($newRestriction['parent_restriction_id'] == '' || empty($newRestriction['parent_restriction_id']))
                                $newRestriction['parent_restriction_id'] = NULL;


                            $newRestrictionId = $this->ladeLeitWartePtr->restrictionsPtr->add($newRestriction, $this->depot);
                            $this->ladeLeitWartePtr->restrictionsPtr->updatePower($this->depot);
                            $this->msgs[] = 'Ladegruppe ' . $newRestriction['name'] . ' hinzugefügt!';
                        } //end of adding new restriction

                        else {

                            $restriction = array_search($newRestriction['name'], $this->restrictionsNames);

                            if (!$restriction) {
                                $this->msgs[] = 'Fehler beim aktualisieren Ladegruppe ' . $newRestriction['name'];
                                break;
                            }

                            $currentRestriction = $this->ladeLeitWartePtr->restrictionsPtr->getFromId($restriction, array('restriction_id', 'name', 'power', 'parent_restriction_id'));
                            $newRestriction['restriction_id'] = $restriction;
                            $compareRestriction = $newRestriction;

                            if ($newRestriction['parent_restriction_id'] == '' || empty($newRestriction['parent_restriction_id']))
                                $compareRestriction['parent_restriction_id'] = NULL;

                            $compareRestriction['power'] = (float)$compareRestriction['power'] * 215.0;

                            if (count(array_intersect_assoc($currentRestriction, $compareRestriction)) < sizeof($currentRestriction)) //there is a difference in the arrays and so updated needed
                            {
                                if ($compareRestriction['parent_restriction_id'] !== NULL) {
                                    /*@todo check Central_Manager.php 20160224 */
                                    if ($this->ladeLeitWartePtr->restrictionsPtr->checkTreeStructure($compareRestriction['parent_restriction_id'], $post_parent_restrictions) === false) {
                                        $this->msgs[] = 'Gruppen dürfen nicht innerhalb des Baumes an sich selber angeschlossen werden.';
                                        break;
                                    }

                                } else//$v1 != $current_base_depot_restriction_id  is always satisfied
                                {
                                    $this->msgs[] = 'Um eine Gruppe zur neuen obersten Gruppe zu machen, die oberste Gruppe löschen.';
                                    break;
                                }

                                $updateCols = array_keys($compareRestriction);
                                $updateVals = array_values($compareRestriction);


                                $this->ladeLeitWartePtr->restrictionsPtr->save($updateCols, $updateVals, array('restriction_id', '=', $compareRestriction["restriction_id"]));
                                $this->ladeLeitWartePtr->restrictionsPtr->updatePower($this->depot);

                                //@todo notifyChanges ( $depotID, $_POST, "editGroup" );
                                $this->msgs[] = 'Ladegruppe ' . $newRestriction['name'] . ' aktualisiert!';
                            }


                        } //end of update Restriction


                    }
                } //with untergruppe upload
            } //while getting data
        }
        $this->csv_stn_msgs[] = 'CSV Datei hochgeladen und gespeichert!';

    }


}