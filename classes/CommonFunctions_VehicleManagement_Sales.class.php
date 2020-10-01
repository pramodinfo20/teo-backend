<?php
/**
 * CommonFunctions_VehicleManagement_Sales.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 */
include_once ($_SERVER['STS_ROOT'] . '/includes/sts-defines.php');

class CommonFunctions_VehicleManagement_Sales extends CommonFunctions_ShowOZSelect
{

    protected $freestations;

    protected $overview;

    protected $treestructure;

    protected $sopVariants;

    protected $debugmsgs;

    protected $vehicle_variants;


    function __construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $getaction, $sopVariants)
    {

        $this->sopVariants = $sopVariants;
        $this->debugmsgs = array();
        parent::__construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $getaction);

        $this->overview = new CommonFunctions_VehiclesStationsOverview($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'fahrzeugverwaltung');

        if ($this->zsp)
            $this->overview->buildSummaryForDepot($this->zsp, $getaction);
        else if ($this->zspl)
            $this->overview->buildOverviewForZspl($this->zspl, $getaction);
        else if ($this->div)
            $this->overview->buildOverviewForDivision($this->div, $getaction);

        $db_vehicle_variants = $this->ladeLeitWartePtr->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante');
        $this->vehicle_variants = array_combine(array_column($db_vehicle_variants, 'value_id'), array_column($db_vehicle_variants, 'value'));

    }


    /**
     * *
     *
     * @todo 20160910 if for a vehicle, the depot is new, then unassign the previously assigned station and reassign new station to the currene vehicle
     *       // update for todo.. changed it so that you get only free stations when depot is changed for a vehicle
     *       saves the table of vehicles and the depot/stations assignments
     */
    function saveVehiclesStations()
    {

        $vehicles = $this->requestPtr->getProperty('vehicles');
        $vehicles = explode(',', $vehicles);
        $vehiclesDb = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('id', 'IN', $vehicles)
            ->get('vehicle_id,depot_id,station_id');
        $production_location = NULL;
        foreach ($vehiclesDb as $vehicle) {
            $old_values[$vehicle['vehicle_id']] = array(
                'depot_id' => $vehicle['depot_id'],
                'station_id' => $vehicle['station_id']
            );
        }
        $affected_restrictions = array();

        foreach ($vehicles as $vehicle) {
            $depot = $this->requestPtr->getProperty('depotv_' . $vehicle);
            $station = $this->requestPtr->getProperty('stationv_' . $vehicle);

            $division = $this->ladeLeitWartePtr->depotsPtr->newQuery()
                ->where('depot_id', '=', $depot)
                ->getVal('division_id');
            $isTestFz = ($division == DIVISION_TESTFAHRZEUGE);

            if ($station == 'null') {
                $station = NULL;
            }

            if ($depot != $old_values[$vehicle]['depot_id'] || $station != $old_values[$vehicle]['station_id']) {
                if ($station !== NULL) {
                    $restrictions = $this->ladeLeitWartePtr->stationsPtr->getRestrictionsForStation($station);
                    // @todo now handling only restriction_id and ignoring restriction_id2 and restriction_id3 since we dont have 3 phase vehicles
                    if (! in_array($restrictions['restriction_id'], $affected_restrictions))
                        $affected_restrictions[] = $restrictions['restriction_id'];
                }

                $qry = $this->ladeLeitWartePtr->depotsPtr->newQuery('')
                    ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
                    ->where('divisions.production_location', '=', 't');

                $production_depots = $qry->get('depot_id');
                $production_depot_ids = array_column($production_depots, 'depot_id');

                $qry = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', '=', $vehicle);

                $current_depot_id = $qry->getVal('depot_id');
                $current_production_location = $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
                    ->where('vehicle_id', '=', $vehicle)
                    ->getVal('production_location');

                if (in_array($current_depot_id, $production_depot_ids)) {
                    $production_location = $current_depot_id;
                } else if (! empty($current_production_location) || $current_production_location == 0) {
                    $production_location = $current_production_location;
                } else {
                    $production_location = NULL;
                }

                $update_cols = [
                    'depot_id',
                    'station_id'
                ];
                $update_vals = [
                    $depot,
                    $station
                ];
                if ($isTestFz) {
                    $update_cols[] = 'fallback_power_even';
                    $update_cols[] = 'fallback_power_odd';
                    $update_vals[] = 3000;
                    $update_vals[] = 3000;
                }

                $depots = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vehicle_id', '=', $vehicle)
                    ->update($update_cols, $update_vals);
            }
        }

        foreach ($affected_restrictions as $restriction) {
            $possibleCombos = $this->iterateThroughRestrictions($restriction, $this->sopVariants);
            if ($possibleCombos !== true) // if not true, returns array with possible combinations
            {

                $restriction_name = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()
                    ->where('restriction_id', '=', $restriction)
                    ->getVal('name');
                // if problem then revert back to the previous configuration
                $this->debugmsgs[] = $restriction_name . ' : <br>' . implode('<br>', $possibleCombos);
            }
        }
        // if there are errors, then reset the vehicles and stations assignments
        if (! empty($this->debugmsgs)) {
            foreach ($vehicles as $vehicle) {

                $depot = $this->requestPtr->getProperty('depotv_' . $vehicle);
                $station = $this->requestPtr->getProperty('stationv_' . $vehicle);
                if ($station == 'null') {
                    $station = NULL;
                }

                $update_cols = array(
                    'depot_id',
                    'station_id'
                );
                $update_vals = array(
                    $old_values[$vehicle]['depot_id'],
                    $old_values[$vehicle]['station_id']
                );

                $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vehicle_id', '=', $vehicle)
                    ->update($update_cols, $update_vals);
            }
        } // else then check that the vehicles with the new depots and stations have their districts set up properly
        else {
            foreach ($vehicles as $vehicle) {
                $depot = $this->requestPtr->getProperty('depotv_' . $vehicle);
                if ($depot != $old_values[$vehicle]['depot_id']) {
                    $district = $this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicle); //

                    if (! empty($district))
                        $this->ladeLeitWartePtr->districtsPtr->newQuery()
                            ->where('district_id', '=', $district[0]['district_id'])
                            ->update(array(
                            'depot_id'
                        ), array(
                            $depot
                        ));
                    else
                        $this->ladeLeitWartePtr->districtsPtr->insertNewDistrict($vehicle, $depot);

                    $sts_pool = $this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
                    $pool_depot_id = $sts_pool['depot_id'];

                    if ($old_values[$vehicle]['depot_id'] == 0 || $old_values[$vehicle]['depot_id'] == $pool_depot_id) {
                        $calendarweek = (int) date('W');
                        $update_cols = [
                            'delivery_week',
                            'production_location'
                        ];
                        $update_vals = [
                            'kw' . $calendarweek,
                            $production_location
                        ];

                        $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
                            ->where('vehicle_id', '=', $vehicle)
                            ->update($update_cols, $update_vals);

                        $update_cols = [
                            'finished_status'
                        ];
                        $update_vals = [
                            'f'
                        ];

                        $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                            ->where('vehicle_id', '=', $vehicle)
                            ->update($update_cols, $update_vals);
                    }
                }
            }
        }

    }


    function checkCombo($restriction_id, $sopVariants)
    {

        $restrictions = array(
            $restriction_id
        );
        $result = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()
            ->where('parent_restriction_id', '=', $restriction_id)
            ->get('restriction_id');
        if (! empty($result)) {
            $newsubres = array_column($result, 'restriction_id');
            // merge the restriction_id with its children
            $restrictions = array_merge($restrictions, $newsubres);
            $restrictions = array_unique($restrictions, SORT_NUMERIC);
        }

        $restrictionsvehicles = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()
            ->join('stations', 'stations.restriction_id=restrictions.restriction_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
            ->where('restrictions.restriction_id', 'IN', $restrictions)
            ->where('vehicles_sales.vehicle_variant', 'IN', $sopVariants)
            ->groupBy('restrictions.restriction_id,restrictions.power')
            ->get('restrictions.restriction_id,restrictions.power,json_agg(vehicles.vehicle_id) as vids');

        $pvscnt = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()
            ->join('stations', 'stations.restriction_id=restrictions.restriction_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
            ->where('restrictions.restriction_id', 'IN', $restrictions)
            ->where('vehicles_sales.vehicle_variant', 'NOT IN', $sopVariants)
            ->getVal('count(vehicles.vehicle_id) as pvscnt');

        $restriction_power = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()
            ->where('restriction_id', '=', $restriction_id)
            ->getVal('power');

        if (empty($pvscnt))
            $pvscnt = 0;

        $resVehicles = array();
        $cnteven = $cntodd = $controllable_sop = 0;

        if (! empty($restrictionsvehicles)) {

            foreach ($restrictionsvehicles as $restriction) {

                $resVehicles = array_merge($resVehicles, json_decode($restriction['vids'], true));
            }
            foreach ($resVehicles as $vehicle_id) {
                $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vehicle_id', '=', $vehicle_id)
                    ->getOne('fallback_power_odd,fallback_power_even,charger_controllable');

                if ($vehicle['charger_controllable'] == 't')
                    $controllable_sop ++;
                else if ($vehicle['fallback_power_even'])
                    $cnteven ++;
                else if ($vehicle['fallback_power_odd'])
                    $cntodd ++;
            }
        }
        $nSOP = min($cnteven, $cntodd) + abs($cnteven - $cntodd);
        $nPVS = $pvscnt + $controllable_sop;
        $imax = $restriction_power / (215.0);
        $n = floor($imax / 8);

        $i12 = $nSOP * 16 + ($nPVS) * 7;
        if ($i12 <= $imax) {
            return true;
        } else {
            $i = 0;
            $nPVS = 0;

            $possibleCombos = array();

            $possibleCombos[] = 'Berechnete Stromverbrauch :' . $i12 . 'A<br>';

            // for($nSOP=$n; $nSOP>-1; $nSOP--)
            // {
            // $i12=ceil($nSOP/2)*16+ ($n-$nSOP)*7;
            // $temp=$n-$nSOP;

            // if($i12<=$imax)
            // {
            // $possibleCombos[]=$nPVS.' PVS (B14) und '.$nSOP. ' SOP (B16/D16)';
            // }
            // $nPVS++;
            // }
        }
        return $possibleCombos;

    }


    function iterateThroughRestrictions($restriction_id, $sopVariants, $tempStations = array())
    {

        $thisrestriction = $restriction_id;

        /**
         * each iteration loops through restriction_id and its children, next loop start with the parent of restriction_id and its children and so on
         */
        do {
            // get the parent restriction id, only if this is not the top most restriction..
            $parent_restriction = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()
                ->where('restriction_id', '=', $restriction_id)
                ->where('parent_restriction_id', 'IS', 'NOT NULL')
                ->getVal('parent_restriction_id');
            $parents_parent_restriction = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()
                ->where('restriction_id', '=', $parent_restriction)
                ->getVal('parent_restriction_id');

            $possibleCombos = $this->checkCombo($restriction_id, $sopVariants);
            if ($possibleCombos !== true)
                return $possibleCombos;
            if (! empty($parents_parent_restriction) && ! empty($parent_restriction)) {
                $restriction_id = $parent_restriction;
            } else
                $restriction_id = null;
        } while ($restriction_id);

        return true;

    }


    /**
     * returns list of depots based on the user role and his/her assigned ZSPL/Division
     *
     * @return array list of depots
     */
    function getDepotsWithFreeStationsForRole()
    {

        $div = $zspl = null;

        if ($this->user->getUserRole() == 'fuhrparksteuer')
            $depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithFreeStations(array(
                'division_id' => $this->user->getAssignedDiv()
            ));
        else if ($this->user->getUserRole() == 'fpv')
            $depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithFreeStations(array(
                'zspl_id' => $this->user->getAssignedZspl()
            ));
        else {
            $whereStmt = array();
            if (! empty($this->zspl))
                $whereStmt['zspl_id'] = $this->zspl;
            if (! empty($this->div))
                $whereStmt['division_id'] = $this->div;
            $depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithFreeStations($whereStmt);
        }

        return $depots;

    }


    /**
     * returns list of depots based on the user role and his/her assigned ZSPL/Division
     *
     * @return array list of depots
     */
    function getDepotsWithOnlyFreeStationsForRole($max_vehicles_at_station = 1)
    {

        $div = $zspl = null;
        $unitParams = [];

        switch ($this->user->getUserRole()) {
            case 'fuhrparksteuer':
                $unitParams['division_id'] = $this->user->getAssignedDiv();
                break;

            case 'fpv':
                $unitParams['zspl_id'] = $this->user->getAssignedZspl();
                break;
        }

        $DepotsOnlyFreeStations = $this->ladeLeitWartePtr->depotsPtr->getDepotsForOnlyFreeStations($unitParams, $max_vehicles_at_station);
        return $DepotsOnlyFreeStations;

    }


    /**
     * *
     * calls the quickform function to generate a table of vehicles and stations
     *
     * @param
     *            array or integer $depots is currently only integer since the function is called only when a single ZSP is selected, can also be expanded in the future to display vehicles for a whole ZSPL too
     *            
     */
    function getVehicles($depots)
    {

        $this->vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesVariantsStationsForDepots($depots);
        if (empty($this->vehicles))
            $this->vehicles = array();

        $sts_pool = $this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
        $pool_depot_id = $sts_pool['depot_id'];
        $production_locations = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
            ->where('production_location', '=', 't')
            ->where('name', 'NOT ILIKE', '%Düren%')
            -> // uncomment to allow Düren to be selected too
        join('depots', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->orderBy('divisions.division_id')
            ->get('depot_id');
        $production_depots = array_column($production_locations, 'depot_id');

        if (! empty($production_depots))
            $finished_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesVariantsStationsForDepots($production_depots, true);
        if (! empty($finished_vehicles))
            $this->vehicles = array_merge($this->vehicles, $finished_vehicles);

        $this->stations = $this->ladeLeitWartePtr->stationsPtr->getStationsForDepots($depots);
        $this->freestations = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsForDepot($depots);

        $prostations = array();
        foreach ($this->stations as $station) {
            if (! empty($station['restriction_id2']) && ! empty($station['restriction_id3']))
                $stationame = $station['name'] . ' (3-phasig)';
            else
                $stationame = $station['name'] . ' (1-phasig)';

            if ($station['deactivate'] == 'f')
                $prostations[$station['station_id']] = $stationame;
            else
                $prostations[$station['station_id']] = $stationame . ' (deaktiviert)';
        }
        unset($station); // just a reset so its safe for the next loop

        $profreestations = array();
        foreach ($this->freestations as $station) {
            if (! empty($station['restriction_id2']) && ! empty($station['restriction_id3']))
                $stationame = $station['name'] . ' (3-phasig)';
            else
                $stationame = $station['name'] . ' (1-phasig)';

            if ($station['deactivate'] == 'f')
                $profreestations[$station['station_id']] = $stationame;
            else
                $profreestations[$station['station_id']] = $stationame . ' (deaktiviert)';
        }

        $DepotsWithOnlyFreeStations = $this->getDepotsWithOnlyFreeStationsForRole();
        $allowedDepots = $this->getDepotsWithFreeStationsForRole();

        if (is_numeric($depots))
            $div = $this->ladeLeitWartePtr->depotsPtr->getDivision($depots);

        /*
         * $otherDivisions = $this->ladeLeitWartePtr->depotsPtr->getOtherDivisionsAusstehendeZuweisung($except_div);
         * $listOtherDivs = make_map ($otherDivisions, 'depot_id', 'name');
         */

        /*
         * $DepotsWithOnlyFreeStations[]=['depot_id'=>'-', 'name'=>'-'];
         * $DepotsWithOnlyFreeStations[]=['depot_id'=>'nl', 'name'=>'&gt;&gt; Abgabe andere NL...'];
         */

        $allowedDepots[] = $this->ladeLeitWartePtr->depotsPtr->getDepotAustehendeZuweisung($div);
        $allowedDepots[] = $this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
        $allowedDepots[] = $this->ladeLeitWartePtr->depotsPtr->getFleetPoolDepot($div);

        $this->qform_vehicle_mgmt = new QuickformHelper($this->displayHeader, "qform_vehicle_mgmt");
        $this->qform_vehicle_mgmt->getVehicleMgmt($this->vehicles, array_column($allowedDepots, 'name', 'depot_id'), $prostations, $profreestations, $this->zsp, $this->vehicle_variants, $listOtherDivs, array_column($DepotsWithOnlyFreeStations, 'name', 'depot_id'));

        $this->treestructure = $this->ladeLeitWartePtr->restrictionsPtr->generateTreeStructureForDepot($this->zsp);

    }


    function printContent()
    {

        include ("pages/common/vehiclemgmt.php");

    }

}
