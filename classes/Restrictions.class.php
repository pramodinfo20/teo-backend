<?php

/**
 * restrictions.class.php
 * Klasse für 'restrictions'
 * @author Pradeep Mohan
 */

/**
 * Class to handle restrictions
 */
class Restrictions extends LadeLeitWarte
{

    protected $dataSrcPtr;

    protected $tableName;

    protected $errormsgs;

    protected $possibleCombosError;

    const MAX_POWER = 0x7FFFFFFF;


    function __construct(DataSrc $dataSrcPtr, $tableName)
    {

        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
        $this->possibleCombosError = '';

    }


    /**
     * function to generate the unordered html lists for sub restrictions for the depot
     * called iteratively
     * uses css styling .tree_graph to generate the tree structure visually
     *
     * @param array $singlesubres
     *            array of restriction ids
     * @param array $restriction_names
     *            array of restriction names
     * @param array $restriction_power
     *            array of restriction powers
     * @param integer $level
     *            1 denotes the primary phases of the depot, higher levels indicate sub restrictions
     * @param integer $primary_phases_size
     *            either 3 if depot has only the primary phases, 4 if depot also has tempdepot restriction
     * @return string the unordered lists concatenated into a string
     */
    function genStructSubRes($singlesubres, &$restriction_names, &$restriction_power, $level, $primary_phases_size = null)
    {

        $zsp = $_GET['zsp'];
        $newstations = $this->newQuery('stations')
            ->where('stations.depot_id', '=', $zsp)
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->orderBy('stations.name')
            ->get('stations.name');

        /*
         * $searchresult = array_search('0003654R', array_column($newstations, 'name'));
         * // echo count($newstations).'<br>';
         * if($searchresult){
         *
         * $display = 'restriction_hide';
         * }
         * else
         * $display = 'restriction_show';
         */

        /*
         * echo $display;
         *
         * foreach ($newstations as $key => $newstation) {
         * $arraylist = $newstation['name'].'<br />';
         * echo $arraylist;
         * }
         * die();
         */

        $debugcontent = '';
        $restriction = $singlesubres;

        $stations = $this->newQuery('stations')
            ->where('stations.restriction_id', '=', $restriction)
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->orderBy('stations.name')
            ->orderBy('vehicles.replacement_vehicles', 'ASC')
            ->get('stations.station_id,stations.name,stations.restriction_id2,stations.restriction_id3,vehicles.vin,vehicles.code,stations.deactivate,vehicles.replacement_vehicles,vehicles.three_phase_charger');

        $result = $this->newQuery('restrictions')
            ->where('parent_restriction_id', '=', $restriction)
            ->get('restriction_id,name,power');

        $threephasestations = $this->newQuery('stations')
            ->multipleOrWhere('stations.restriction_id2', '=', $restriction, 'OR', 'stations.restriction_id3', '=', $restriction)
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
        /*->where('vehicles.three_phase_charger','=','f')*/
        ->where('stations.restriction_id2', 'IS', 'NOT NULL')
            ->where('stations.restriction_id3', 'IS', 'NOT NULL')
            ->orderBy('stations.name')
            ->orderBy('vehicles.replacement_vehicles', 'ASC')
            ->get('stations.station_id,stations.name,stations.restriction_id2,stations.restriction_id3,vehicles.vin,vehicles.code,stations.deactivate,vehicles.replacement_vehicles,vehicles.three_phase_charger');

        /*
         * $zsp = $_GET['zsp'];
         * $newstations = $this->newQuery('stations')->where('stations.depot_id','=',$zsp)
         * ->join('vehicles','vehicles.station_id=stations.station_id','FULL OUTER JOIN')
         * ->join('depots','vehicles.depot_id=depots.depot_id','INNER JOIN')
         * ->get('stations.name');
         * echo '<pre>';
         * print_r($newstations);
         * echo '</pre>';
         */

        if (isset($primary_phases_size)) {
            $style_attr = 'width: ' . ((96 / ($primary_phases_size + 1))) . '%; ';
            /*
             * if($primary_phases_size==3)
             * $style_attr="width: 24%; ";
             * else if($primary_phases_size==4)
             * $style_attr="width: 19%; ";
             */
        } else
            $style_attr = '';
        $debugcontent .= '<li style="' . $style_attr . '"><span class="resname_' . $level . '" >' . $restriction_names[$restriction] . ' <br>Absicherung : ' . ($restriction_power[$restriction] / 215) . ' A</span>';

        if (! empty($stations)) {
            if ($threephasestations[0]['three_phase_charger'] == 'f' && ! empty($threephasestations[0]['restriction_id2']) && ! empty($threephasestations[0]['restriction_id3']))
                $stations = array_merge($stations, $threephasestations);
            else
                $stations = $stations;

            $debugcontent .= '<ul class="stationslist">';
            foreach ($stations as $station) {
                if ($station['deactivate'] == 't')
                    $deactive_status = ' (deaktiviert) ';
                else
                    $deactive_status = '';

                // 3-phasig Ladepunkte
                if (! empty($station['restriction_id2']) && ! empty($station['restriction_id3']))
                    $restriction_check = 'threephase_restriction ';
                else
                    $restriction_check = '';

                // Check Ladepunkt nach Ersatzfahrzeug
                if ($station['name'] && $station['replacement_vehicles'] == 'f')
                    $restriction_status = ' hidde_restriction ';
                else
                    $restriction_status = ' show_restriction ';

                // 3-phasige Fahrzeuge
                if ($station['three_phase_charger'] == 't')
                    $threephase_vehicle_check = 'threephase_vehicle ';
                else
                    $threephase_vehicle_check = '';

                // Check Count of restrictions with replacement_vehicles
                $search_restriction = array_column($stations, 'name');
                $counts = array_count_values($search_restriction);
                if ($counts[$station['name']] > 1 && $station['replacement_vehicles'] == 'f') {
                    $display = ' hide ';
                    $paddingtop = 'paddingtop';
                } // else $display = ' show ';

                if (isset($tempStations[$restriction][$station['station_id']]))
                    $debugcontent .= '<li>' . $station['name'] . $deactive_status . '<br><span class="vehicle_vin">' . $tempStations[$restriction_id][$station['station_id']] . '</span><br><span class="akz-code">' . $station['code'] . '</span></li>';

                else if ($station['three_phase_charger'] == 't' && (! empty($station['restriction_id2']) && ! empty($station['restriction_id3'])))
                    $threephaserestriction .= '<li><span class="' . $restriction_check . 'station_name">' . $station['name'] . $deactive_status . '</span><br><span class="' . $threephase_vehicle_check . ' vehicle_vin">' . $station['vin'] . '</span><br><span class="akz-code">' . $station['code'] . '</span></li>';

                // Ersatzfahrzeuge
                else if ($station['replacement_vehicles'] == 't')
                    $debugcontent .= '<li class="' . $paddingtop . '"><span class="' . $restriction_check . $display . 'station_name">' . $station['name'] . $deactive_status . '</span><br><span class="' . $threephase_vehicle_check . 'replacement_vehicles vehicle_vin">' . $station['vin'] . '</span><br><span class="akz-code">' . $station['code'] . '</span></li>';

                else
                    $debugcontent .= '<li><span class="' . $restriction_check . ' station_name">' . $station['name'] . $deactive_status . '</span><br><span class="' . $threephase_vehicle_check . ' vehicle_vin">' . $station['vin'] . '</span><br><span class="akz-code">' . $station['code'] . '</span></li>';
            }
            $debugcontent .= '</ul>';
        }

        if (! empty($result)) {
            $newsubres = array_column($result, 'restriction_id');
            $new_restriction_names = array_combine(array_column($result, 'restriction_id'), array_column($result, 'name'));
            $new_restriction_power = array_combine(array_column($result, 'restriction_id'), array_column($result, 'power'));

            $restriction_names = $restriction_names + $new_restriction_names;
            $restriction_power = $restriction_power + $new_restriction_power;
            if ($level == 0)
                $debugcontent .= '<ul>';
            else
                $debugcontent .= '<ul class="subres level_' . $level . '">';
            foreach ($newsubres as $restriction) {
                $debugcontent .= $this->genStructSubRes($restriction, $restriction_names, $restriction_power, $level + 1);
            }
            $debugcontent .= '</ul>';
        }
        $debugcontent .= '</li>';
        return $debugcontent;

    }


    /**
     * function to generate the unordered html lists for sub restrictions for the depot (only 3-Phase)
     * called iteratively
     * uses css styling .tree_graph to generate the tree structure visually
     *
     * @param array $singlesubres
     *            array of restriction ids
     * @param array $restriction_names
     *            array of restriction names
     * @param array $restriction_power
     *            array of restriction powers
     * @param integer $level
     *            1 denotes the primary phases of the depot, higher levels indicate sub restrictions
     * @param integer $primary_phases_size
     *            either 3 if depot has only the primary phases, 4 if depot also has tempdepot restriction
     * @return string the unordered lists concatenated into a string
     */
    function genStructThreephase($singlesubres, &$restriction_names, &$restriction_power, $level, $primary_phases_size = null)
    {

        $threephaserestriction = '';
        $restriction = $singlesubres;

        $stations = $this->newQuery('stations')
            ->where('stations.restriction_id', '=', $restriction)
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->where('stations.restriction_id2', 'IS', 'NOT NULL')
            ->where('stations.restriction_id3', 'IS', 'NOT NULL')
            ->orderBy('stations.name')
            ->orderBy('vehicles.replacement_vehicles', 'ASC')
            ->get('stations.station_id,stations.name,stations.restriction_id2,stations.restriction_id3,vehicles.vin,vehicles.code,stations.deactivate,vehicles.replacement_vehicles,vehicles.three_phase_charger');

        $result = $this->newQuery('restrictions')
            ->where('parent_restriction_id', '=', $restriction)
            ->get('restriction_id,name,power');

        if (isset($primary_phases_size)) {
            $style_attr = 'width: ' . ((96 / ($primary_phases_size + 1))) . '%; ';
            /*
             * if($primary_phases_size==3)
             * $style_attr="width: 24%; ";
             * else if($primary_phases_size==4)
             * $style_attr="width: 19%; ";
             */
        } else
            $style_attr = '';

        if (! empty($stations)) {
            $threephaserestriction .= '<span class="threephaseresname resname_' . $level . '" >' . $restriction_names[$restriction] . ' <br>Absicherung : ' . ($restriction_power[$restriction] / 215) . ' A</span>';

            $threephaserestriction .= '<ul class="stationslist">';
            foreach ($stations as $station) {
                if ($station['deactivate'] == 't')
                    $deactive_status = ' (deaktiviert) ';
                else
                    $deactive_status = '';

                // 3-phasig Ladepunkt
                if (! empty($station['restriction_id2']) && ! empty($station['restriction_id3']))
                    $restriction_check = 'threephase_restriction';
                else
                    $restriction_check = '';

                // Check Ladepunkt nach Ersatzfahrzeug
                if ($station['name'] && $station['replacement_vehicles'] == 'f')
                    $restriction_status = ' hidde_restriction ';
                else
                    $restriction_status = ' show_restriction ';

                // Ersatzfahrzeug
                if ($station['replacement_vehicles'] == 't')
                    $replacement_vehicle_check = ' replacement_vehicles ';
                else
                    $replacement_vehicle_check = '';

                // 3-phasige Fahrzeug
                if ($station['three_phase_charger'] == 't')
                    $threephase_vehicle_check = 'threephase_vehicle';
                else
                    $threephase_vehicle_check = '';

                if (isset($tempStations[$restriction][$station['station_id']]))
                    $threephaserestriction .= '<li><span>' . $station['name'] . $deactive_status . '</span><br><span class="vehicle_vin">' . $tempStations[$restriction_id][$station['station_id']] . '</span></li>';

                else if ($station['three_phase_charger'] == 't')
                    $threephaserestriction .= '<li><span class="' . $restriction_check . $restriction_status . ' station_name">' . $station['name'] . $deactive_status . '</span><br><span class="' . $threephase_vehicle_check . $replacement_vehicle_check . ' vehicle_vin">' . $station['vin'] . '</span><br><span class="akz-code">' . $station['code'] . '</span></li>';

                else
                    $threephaserestriction .= '<li><span class="' . $restriction_check . $restriction_status . ' station_name">' . $station['name'] . $deactive_status . '</span><br><span class="' . $threephase_vehicle_check . ' vehicle_vin">'./*$station['vin'].*/'</span></li>';
            }
            $threephaserestriction .= '</ul>';
        }

        if (! empty($result)) {
            $newsubres = array_column($result, 'restriction_id');
            $new_restriction_names = array_combine(array_column($result, 'restriction_id'), array_column($result, 'name'));
            $new_restriction_power = array_combine(array_column($result, 'restriction_id'), array_column($result, 'power'));

            $restriction_names = $restriction_names + $new_restriction_names;
            $restriction_power = $restriction_power + $new_restriction_power;
            if ($level == 0)
                $threephaserestriction .= '<ul>';
            else
                $threephaserestriction .= '<ul class="threephase subres level_' . $level . '">';

            foreach ($newsubres as $restriction) {
                $threephaserestriction .= $this->genStructThreephase($restriction, $restriction_names, $restriction_power, $level + 1);
            }
            $threephaserestriction .= '</ul>';
        }
        return $threephaserestriction;

    }


    /**
     * generates the tree diagram for the charging infrastructure of the depot
     * uses css styling .tree_graph to generate the tree structure visually
     *
     * @param integer $depot_id
     * @return string concatenated string of unordered HTML lists
     */
    function generateTreeStructureForDepot($depot_id)
    {

        $depot = $this->newQuery('depots')
            ->where('depot_id', '=', $depot_id)
            ->getOne('*');
        $result = $this->newQuery('restrictions')
            ->where('parent_restriction_id', '=', $depot['depot_restriction_id'])
            ->orderBy('restriction_id')
            ->get('restriction_id,name,power');

        $subres = array_column($result, 'restriction_id');
        $debugcontent = $depot['name'];
        $debugcontent .= '<div class="tree_graph"><ul class="primary"><li style="width:100%"><span>Hauptanschluss</span><ul class="phases">';
        $restriction_names = array_combine(array_column($result, 'restriction_id'), array_column($result, 'name'));
        $restriction_power = array_combine(array_column($result, 'restriction_id'), array_column($result, 'power'));
        $level = 1;
        $primary_phases_size = sizeof($subres);
        foreach ($subres as $key => $singlesubres) {
            // Phase 1 2 3
            $debugcontent .= $this->genStructSubRes($singlesubres, $restriction_names, $restriction_power, $level, $primary_phases_size);
        }

        // Restriction with 3-phase
        if (isset($primary_phases_size)) {
            $style_attr = 'width: ' . ((96 / ($primary_phases_size + 1))) . '%; ';
            /*
             * if($primary_phases_size==3)
             * $style_attr="width: 24%; ";
             * else if($primary_phases_size==4)
             * $style_attr="width: 19%; ";
             */
        } else
            $style_attr = '';

        $threephaserestriction .= '<li style="' . $style_attr . '"><span class="resname_' . $level . '" >3-Phasige <br>Ladepunkte</span><br>';

        foreach ($subres as $key => $singlesubres) {
            $threephaserestriction .= $this->genStructThreephase($singlesubres, $restriction_names, $restriction_power, $level, $primary_phases_size);
        }
        $threephaserestriction .= '</li>';

        $debugcontent .= $threephaserestriction;
        $debugcontent .= '</ul></li></ul></div>';

        return $debugcontent;

    }


    function getPossibleCombosError()
    {

        return $this->possibleCombosError;

    }


    function genPossibleCombos($free_stations, $count_vehicles, $sopVariants, $vehicles, $consider_even_odd = true)
    {

        $tempStations = array();
        $assigned_vehicles_count = 0;
        $result = array();
        $debug = 1;
        if (empty($free_stations)) {
            echo '<br>';
            print_r($free_stations);
            echo '<br>' . $count_vehicles;

            echo '<br>';
        }
        // set $status to empty initially, if $consider_even_odd is false, then we do not need to worry about even or odd
        $status = '';

        foreach ($free_stations as $station) {
            if ($assigned_vehicles_count >= $count_vehicles)
                break;

            if ($consider_even_odd) {
                // @todo oddeven passing only the first restriction .. should check for three phases
                $status = $this->getEvenOdd($station['restriction_id'], $sopVariants, $tempStations);
                if ($status === false)
                    continue;
                if ($debug == 1)
                    echo $station['sname'] . ':' . $status . '<br>';

                if ($status == '') {

                    $vins = array_column($vehicles, 'vin');
                    $even_vins = $odd_vins = 0;
                    foreach ($vins as $this_vin) {
                        $lastTwo = (int) substr($this_vin, - 2);
                        if ($lastTwo % 2 == 0)
                            $even_vins ++;
                        else
                            $odd_vins ++;
                    }

                    if ($odd_vins > $even_vins)
                        $status = 'getodd';
                    else
                        $status = 'geteven';
                }

                $vehicle_array_key = $this->getOddEvenVehicle($vehicles, $status);
            } else // since we use array_values() below when unsetting the selected vehicle, it should be just get the element with index 0
            {
                $vehicle_array_key = 0;
            }

            if ($vehicle_array_key !== false) {
                $tempStations[$station['restriction_id']][$station['station_id']] = $vehicles[$vehicle_array_key];
                $result[] = array(
                    'station' => $station,
                    'vehicle' => $vehicles[$vehicle_array_key]
                );
                $assigned_vehicles_count ++;
                unset($vehicles[$vehicle_array_key]);
                $vehicles = array_values($vehicles);
            } else {
                if ($status == '')
                    $this->possibleCombosError = 'Nicht genug Fahrzeuge';
                else if ($status == 'getodd')
                    $this->possibleCombosError = 'Nicht genug Fahrzeuge mit ungerade VIN';
                else if ($status == 'geteven')
                    $this->possibleCombosError = 'Nicht genug Fahrzeuge mit gerade VIN';
                return null;
            }
        }
        // @todo 2017-08-31 here check if the combo leads to $i12 less than $imax
        if (! empty($result) && $assigned_vehicles_count >= $count_vehicles)
            return $result;
        else {
            $this->possibleCombosError = 'Nicht genug Fahrzeuge mit ungerade VIN';
            return null;
        }

        // print_r($tempStations);
        // $debug=$this->debugReport($station['depot_id'],$tempStations);
        // echo $debug;
    }


    function getOddEvenVehicle(&$vehicles, $status) // @todo 2016-01-12 why does this have to be passed by reference
    {

        foreach ($vehicles as $key => $vehicle) {
            $lastTwo = (int) substr($vehicle['vin'], - 2);
            if ($lastTwo % 2 == 0 && $status == 'geteven')
                return $key; // @todo 2016-01-12 set fallback power here correctly just to be sure
            else if ($lastTwo % 2 == 1 && $status == 'getodd')
                return $key; // @todo 2016-01-12 set fallback power here correctly just to be sure
        }

        return false;

    }


    /**
     * *
     *
     * @param array $restrictions
     *            restriction ids
     * @param array $sopVariants
     *            variant values of B14 and B16
     * @return string getodd or geteven
     */
    function countEvenOddRestriction($restrictions, $sopVariants, $tempStations = array())
    {

        $evenCnt = $oddCnt = 0;
        // check count for the current restriction as well as all its children
        $result = $this->newQuery()
            ->join('stations', 'stations.restriction_id=restrictions.restriction_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
            ->where('restrictions.restriction_id', 'IN', $restrictions)
            ->where('vehicles_sales.vehicle_variant', 'IN', $sopVariants)
            ->where('vehicles.charger_controllable', '=', 'f')
            ->get('vin,fallback_power_even,fallback_power_odd');

        // $resVehicles=array_column($result,'vin');
        if (! empty($tempStations)) {
            foreach ($tempStations as $restriction_id => $restriction) {
                if (in_array($restriction_id, $restrictions)) {
                    if (! empty($restriction))
                        foreach ($restriction as $station_id => $vehicle) {
                            $vin = $vehicle['vin'];
                            $lastTwo = (int) substr($vin, - 2);
                            if ($lastTwo % 2 == 0)
                                $evenCnt ++;
                            else
                                $oddCnt ++;
                        }
                }
            }
        }
        if (! empty($result))
            foreach ($result as $eovehicle) {
                if ($eovehicle['fallback_power_even'] > 1500)
                    $evenCnt ++;
                else
                    $oddCnt ++;
            }

        if (($evenCnt - $oddCnt) > 0)
            $status = 'getodd';
        else if ((($evenCnt - $oddCnt) < 0))
            $status = 'geteven';
        else
            $status = '';

        return $status;

    }


    /**
     * *
     * getEvenOdd
     * used in SalesController.class.php and CronController.class.php
     *
     * @param integer $restriction_id
     * @param array $sopVariants
     *            variant values of B14 and B16
     * @return string
     */
    function getEvenOdd($restriction_id, $sopVariants, $tempStations = array())
    {

        /**
         * each iteration loops through restriction_id and its children, next loop start with the parent of restriction_id and its children and so on
         */
        $status = $newstatus = '';

        do {
            // get the parent restriction id, only if this is not the top most restriction..
            $parent_restriction = $this->newQuery()
                ->where('restriction_id', '=', $restriction_id)
                ->where('parent_restriction_id', 'IS', 'NOT NULL')
                ->getVal('parent_restriction_id');
            $parents_parent = $this->newQuery()
                ->where('restriction_id', '=', $parent_restriction)
                ->getVal('parent_restriction_id');

            // do not check for hauptanschluss
            if (empty($parents_parent))
                $parent_restriction = false;

            $restrictions = array(
                $restriction_id
            );
            $result = $this->newQuery()
                ->where('parent_restriction_id', '=', $restriction_id)
                ->get('restriction_id');

            if (! empty($result)) {
                $newsubres = array_column($result, 'restriction_id');
                // merge the restriction_id with its children
                $restrictions = array_merge($restrictions, $newsubres);
                $restrictions = array_unique($restrictions, SORT_NUMERIC);
            }

            // get the status again for the parent and children restrictions
            $newstatus = $this->countEvenOddRestriction($restrictions, $sopVariants, $tempStations);

            /**
             * if $status of the restriction at the start of the loop is different from new status (with the restriction and children) is different, then we cannot decide odd or even vehicle vin to assign
             */
            if ($newstatus != '' && $status != '' && $newstatus != $status) {
                echo 'Probleme mit restriction_id ' . $restriction_id . ':' . $status . '->' . $newstatus . '<br>';
                return false;
            }

            $status = $newstatus;

            if (! empty($parent_restriction) && $status != '')
                $restriction_id = $parent_restriction;
            else
                $restriction_id = null;
        } while (isset($restriction_id));

        return $status;

    }


    function updateRestrictionProtection($depot, $restriction_protection_power, $phases)
    {

        foreach ($phases as $key => $phase) {

            if ($restriction_protection_power[$key] != $phase['power'])
                $this->save(array(
                    'power'
                ), array(
                    $restriction_protection_power[$key]
                ), array(
                    'restriction_id',
                    '=',
                    $phase['restriction_id']
                ));
        }

        $this->updatePower($depot);

    }


    /**
     * *
     *
     * @param integer $depot
     * @return array of the three phases for this depot
     */
    function getAllPhases($depot)
    {

        $depot_restriction_id = $this->newQuery('depots')
            ->where('depot_id', '=', $depot)
            ->getVal('depot_restriction_id');

        $result = $this->newQuery()
            ->where('parent_restriction_id', '=', $depot_restriction_id)
            ->orderBy('restriction_id', 'ASC')
            ->get('*');

        return $result;

    }


    /**
     * getTempDepotRes
     *
     * @param integer $parentRes
     *            Parent Restriction Id
     * @return false if no restriction found, or return restriction id of the Temporary Restriction for the Depot with parent as parentRes
     */
    function getTempDepotRes($parentRes)
    {

        return $this->newQuery('')
            ->where('parent_restriction_id', '=', $parentRes)
            ->where('name', 'LIKE', 'TempDepotRes%')
            ->getVal('restriction_id');

    }


    /**
     * iterativeRestrictions
     *
     * @param string $restriction_id
     * @param array $restriction_ids
     * @param string $exception
     * @param array $restriction_names
     */
    function iterativeRestrictions($restriction_id, array &$restrictions, $exception = "")
    {

        $result = $this->newQuery()
            ->where('parent_restriction_id', '=', $restriction_id)
            ->get('restriction_id');

        if ($result) {
            foreach ($result as $row) {

                if ($row['restriction_id'] == $exception) {
                    continue;
                } else
                    $restrictions[] = $row['restriction_id'];
                $this->iterativeRestrictions($row['restriction_id'], $restrictions);
            }
        } else {
            return;
        }

    }


    function getAllowedParentsIds($restriction_id, $parent_restriction_ids, $depotRestrictionId)
    {

        if ($restriction_id === null) {
            foreach ($parent_restriction_ids as $single_parent_id) {
                if ($single_parent_id != $depotRestrictionId)
                    $allowed_parent_ids[$single_parent_id] = $single_parent_id . ":" . $this->getNameFromId($single_parent_id);
            }

            return $allowed_parent_ids;
        }
        $allowed_parent_ids = array();
        $current_child_restrictions = array();
        $this->iterativeRestrictions($restriction_id, $current_child_restrictions);

        $i = 0;
        $group_label = "";
        foreach ($parent_restriction_ids as $single_parent_id) {

            if ($single_parent_id == $depotRestrictionId) { // dont show Hauptanschluss in the Select List
                $i ++;
                continue;
            }

            // Gruppe darf sich nicht selbst als parent haben
            if ($single_parent_id == $restriction_id) {
                $i ++;
                continue;
            }
            // Gruppe darf keine Ihrer Untergruppen als parent haben
            if (in_array($single_parent_id, $current_child_restrictions)) {
                $i ++;
                continue;
            }

            $allowed_parent_ids[$single_parent_id] = $single_parent_id . ":" . $this->getNameFromId($single_parent_id);

            $i ++;
        }

        return $allowed_parent_ids;

    }


    /**
     * *
     * review
     *
     * {@inheritdoc}
     * @see LadeLeitWarte::add()
     */
    function add($insertVals, $currentdepot = null)
    {

        $restriction = $this->newQuery('depots')
            ->where('depot_id', '=', $currentdepot)
            ->getVal('depot_restriction_id');

        $id = $this->newQuery()->insert($insertVals);

        if ($insertVals['parent_restriction_id'] === NULL) {
            // @todo disable this for the time being 20160717
            // update older parent to be a sub group of the newly inserted parent

            // if(isset($restriction))
            // {

            // $this->save(array('parent_restriction_id'),array($id), array('restriction_id','=',$restriction));
            // }

            // @todo 2016-10-17 if a new group is added with empty parent then the current parent restriction is not moved since the code is commmented aboves
            // Id der neuen obersten Gruppe im Depot eintragen
            $updateCols = array(
                'depot_restriction_id'
            );
            $updateVals = array(
                $id
            );
            $this->newQuery('depots')
                ->where('depot_id', '=', $currentdepot)
                ->update($updateCols, $updateVals);
        }

        return $id;

    }


    function updatePower($currentdepot)
    {

        $whereParams = array();
        $whereParams = array(
            array(
                'colname' => 'depot_id',
                'whereop' => '=',
                'colval' => $currentdepot
            )
        );
        $result = $this->dataSrcPtr->selectAll('depots', array(
            'depot_restriction_id'
        ), $whereParams, null, false);
        $restriction = $result[0]['depot_restriction_id'];

        $whereParams = array();
        $whereParams = array(
            array(
                'colname' => 'parent_restriction_id',
                'whereop' => '=',
                'colval' => $restriction
            )
        );
        $result = $this->dataSrcPtr->selectAll('restrictions', array(
            'power'
        ), $whereParams, null, false);

        $sumSubGroupPower = 0;
        if (! empty($result)) {
            foreach ($result as $restrictionpower) {
                $sumSubGroupPower += (float) $restrictionpower['power'];
            }

            if ($sumSubGroupPower > self::MAX_POWER)
                $sumSubGroupPower = self::MAX_POWER;

            $this->save(array(
                'power'
            ), array(
                $sumSubGroupPower
            ), array(
                'restriction_id',
                '=',
                $restriction
            ));
        }

    }


    function getParentID($post_parent_restrictions, $parentID)
    {

        $whereParams = array(
            array(
                'colname' => 'restriction_id',
                'whereop' => '=',
                'colval' => $parentID
            )
        );

        $result = $this->dataSrcPtr->selectAll('restrictions', array(
            'parent_restriction_id'
        ), $whereParams, null, false);

        if (! empty($result))
            return $result[0]['parent_restriction_id'];
        else
            die("Parent restriction_id für parent_id " . $parentID . " could not be found!");

        // @todo dont need this old code.. foreach($post_parent_restrictions as $restriction)
        // {
        // if($_POST['grp_'.$restriction]['restriction_id'] == $parentID)
        // {

        // return (int)$_POST['grp_'.$restriction]['parent_restriction_id'];
        // }

        // }
        // die("Parent restriction_id für parent_id " . $parentID . " could not be found!");
    }


    function checkTreeStructure($parent_restriction, $post_parent_restrictions)
    {

        $parentIDs = array();
        $test = 0;
        $parentID = (int) $parent_restriction;
        while (1) {
            // wenn parentID Null oberste Gruppe erreicht
            if ($parentID == "null" || $parentID == NULL || $parentID == "" || $parentID == 0) {
                break;
            }
            $parentID = $this->getParentID($post_parent_restrictions, $parentID);

            if ($parentID == "null" || $parentID == NULL || $parentID == "" || $parentID == 0) {
                break;
            }

            if ($test > 200) {
                die("tiemout in function \"checkTreeStructure\"");
            }

            if ($test > 0) {
                if (in_array($parentID, $parentIDs)) // Loop in Gruppen struktur= Fehler
                    return false;
            }
            $test ++;
            $parentIDs[] = $parentID;
        }

        return true;

    }


    function getErrorMsgs()
    {

        return $this->errormsgs;

    }


    function delete($restriction, $currentdepot = null)
    {

        $result = '';

        if ($restriction['parent_restriction_id'] == 'null') {

            // check for substations
            $whereParams = array();
            $whereParams = array(
                array(
                    'colname' => 'restriction_id',
                    'whereop' => '=',
                    'colval' => $restriction['restriction_id']
                )
            );
            $result = $this->dataSrcPtr->selectAll('stations', null, $whereParams, null, false);

            if (is_array($result) && ! empty($result)) {
                $this->errormsgs[] = "Kann oberste Ladegruppe nicht löschen, weil Ladepunkte an Sie angeschlossen sind.";
                return false;
            } else {
                /**
                 * check for subgroups
                 * if count(subgroups) > 1 do not allow deletion
                 * else if count(subgroups) = 0 do not allow deletion
                 * else (count(subgroup) = 1) set the depot_restriction_id for current depot to the child restriction and then proceed with deletion of the parent restriction
                 */
                $whereParams = array();
                $whereParams = array(
                    array(
                        'colname' => 'parent_restriction_id',
                        'whereop' => '=',
                        'colval' => $restriction['restriction_id']
                    )
                );
                $result = $this->dataSrcPtr->selectAll('restrictions', array(
                    'restriction_id'
                ), $whereParams, null, false);

                if (is_array($result) && sizeof($result) > 1) {
                    $this->errormsgs[] = "Kann oberste Ladegruppe nicht löschen, weil mehr als ein Ladegruppe an Sie angeschlossen ist.";
                    return false;
                } else if (is_array($result) && sizeof($result) == 0) {
                    $this->errormsgs[] = "Kann oberste Ladegruppe nicht löschen, weil es keine Untergruppen gibt.";
                    return false;
                } 
                else {
                    // Id der neuen obersten Gruppe im Depot eintragen
                    $whereParams = array();
                    $whereParams = array(
                        array(
                            'colname' => 'depot_id',
                            'whereop' => '=',
                            'colval' => $currentdepot
                        )
                    );
                    $updateCols = array(
                        'depot_restriction_id'
                    );
                    $updateVals = array(
                        $result[0]['restriction_id']
                    );
                    $result = $this->dataSrcPtr->update('depots', $updateCols, $updateVals, $whereParams);
                    // do not return now..
                }
            } // end of if has a substation
        } // if($restriction['parent_restriction_id']=='null')
        else {
            // check for substations
            $whereParams = array();
            $whereParams = array(
                array(
                    'colname' => 'restriction_id',
                    'whereop' => '=',
                    'colval' => $restriction['restriction_id']
                )
            );
            $result = $this->dataSrcPtr->selectAll('stations', null, $whereParams, null, false);

            if (is_array($result) && ! empty($result)) {
                $this->errormsgs[] = "Kann Ladegruppe nicht löschen, weil Ladepunkte an Sie angeschlossen sind.";
                return false;
            }
        }

        // Parent ID aller Untergruppen der zu löschen Gruppe auf deren Parent_ID setzen

        $whereParams = array();
        $whereParams = array(
            array(
                'colname' => 'parent_restriction_id',
                'whereop' => '=',
                'colval' => $restriction['restriction_id']
            )
        );
        $updateCols = array(
            'parent_restriction_id'
        );

        if ($restriction['parent_restriction_id'] == 'null') // convert from string 'null' to actual null
            $updateVals = array(
                null
            );

        else
            $updateVals = array(
                $restriction['parent_restriction_id']
            );

        $result = $this->dataSrcPtr->update('restrictions', $updateCols, $updateVals, $whereParams);

        // delete the restriction now
        $whereParams = array();
        $whereParams = array(
            array(
                'colname' => 'restriction_id',
                'whereop' => '=',
                'colval' => $restriction['restriction_id']
            )
        );

        $result = $this->dataSrcPtr->delete('restrictions', $whereParams);

        return true;

    }

}
