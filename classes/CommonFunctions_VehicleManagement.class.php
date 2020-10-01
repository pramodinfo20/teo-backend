<?php
/**
 * CommonFunctions_VehicleManagement.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 */
require_once $_SERVER['STS_ROOT'] . '/includes/sts-array-tools.php';

class CommonFunctions_VehicleManagement extends CommonFunctions_ShowOZSelect_Vehicles
{

    protected $freestations;

    protected $overview;

    protected $treestructure;

    protected $sopVariants;

    protected $debugmsgs;

    protected $vehicle_variants;

    protected $zsp;


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
        else
            $this->overview->buildOverviewForDivision($this->div, $getaction);

        /*
         * $db_vehicle_variants=$this->ladeLeitWartePtr->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante');
         * $this->vehicle_variants=array_combine ( array_column($db_vehicle_variants,'value_id'),array_column($db_vehicle_variants,'value'));
         */
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

        $mailing_new_vehicles = [];

        $vehicles = $this->requestPtr->getProperty('vehicles');
        $vehicles = explode(',', $vehicles);
        $vehiclesDb = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('id', 'IN', $vehicles)
            ->get('vehicle_id,depot_id,station_id,replacement_vehicles');
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
            $replace = $this->requestPtr->getProperty('replacement_' . $vehicle);
            if (($replace == 'null') || ($replace == ''))
                $replace = 'f';
            if (($station == 'null') || ($depot == 'nl'))
                $station = NULL;

            if ($depot != $old_values[$vehicle]['depot_id'] || $station != $old_values[$vehicle]['station_id']) {
                if ($depot == 'nl') {
                    $depot = $this->requestPtr->getProperty('depoto_' . $vehicle);

                    $this->ladeLeitWartePtr->vehiclesPtr->DepotWechsel($vehicle, $depot, null);

                    $mailing_new_vehicles[$depot][] = $vehicle;
                } else {
                    if ($station !== NULL) {
                        $restrictions = $this->ladeLeitWartePtr->stationsPtr->getRestrictionsForStation($station);
                        // @todo now handling only restriction_id and ignoring restriction_id2 and restriction_id3 since we dont have 3 phase vehicles
                        if (! in_array($restrictions['restriction_id'], $affected_restrictions))
                            $affected_restrictions[] = $restrictions['restriction_id'];
                    }

                    $depots = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                        ->where('vehicle_id', '=', $vehicle)
                        ->update(array(
                        'depot_id',
                        'station_id'
                    ), array(
                        $depot,
                        $station
                    ));
                }
            }
            $depots = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where('vehicle_id', '=', $vehicle)
                ->update(array(
                'replacement_vehicles'
            ), array(
                $replace
            ));
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

                $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vehicle_id', '=', $vehicle)
                    ->update(array(
                    'depot_id',
                    'station_id'
                ), array(
                    $old_values[$vehicle]['depot_id'],
                    $old_values[$vehicle]['station_id']
                ));
            }
        } // else then check that the vehicles with the new depots and stations have their districts set up properly
        else {
            foreach ($vehicles as $vehicle) {
                $depot = $this->requestPtr->getProperty('depotv_' . $vehicle);
                $station = $this->requestPtr->getProperty('stationv_' . $vehicle);
                if ($depot != $old_values[$vehicle]['depot_id']) {
                    $district = $this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicle);
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
                }

                if ($old_values[$vehicle]['station_id'] != $station) {
                    // update vehicle_variant_value_allowed since its no longer relevant
                    $this->ladeLeitWartePtr->stationsPtr->newQuery()
                        ->where('station_id', '=', $old_values[$vehicle]['station_id'])
                        ->update(array(
                        'vehicle_variant_value_allowed'
                    ), array(
                        NULL
                    ));
                }
            }
        }

        if (count($mailing_new_vehicles)) {

            $mailTemplate = <<<HEREDOC
    Sehr geehrte Damen und Herren,

	Ihre Niederlassung wird mit folgenden StreetScootern beliefert werden:

            Kennzeichen     IKZ             VIN                     Fahrzeugtyp
%TABELLE%

	Bitte weisen Sie diese Fahrzeuge im Webinterface des StreetScooter Cloud-Systems unter
	<a href="%DIRECT_LINK%">https://streetscooter-cloud-system.eu</a>
	möglichst zeitnah den jeweiligen ZSPs bzw.Ladepunkte zu.

	Sofern derzeit in der Cloud noch nicht genug freie Ladesäulen für die Zuordnung vorhanden sind,
	sie aber in Kürze mit der Fertigstellung von Ladeinfrastruktur an weiteren Standorten rechnen
    (weil bspw. die Bauarbeiten derzeit im Gange sind), geben Sie die Fahrzeuge bitte nicht in der Cloud zurück,
	sondern warten auf die Fertigstellung der Installationsarbeiten und weisen die Fahrzeuge anschließend zur Auslieferung zu.

	Mit freundlichen Grüßen,

	Ihr StreetScooter Cloud-System Team

	Diese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich.
	Bitte wenden Sie sich bei Fragen an support@streetscooter-cloud-system.eu
HEREDOC;

            foreach ($mailing_new_vehicles as $depot => $vehicles) {
                $division_id = $this->ladeLeitWartePtr->depotsPtr->newQuery()
                    ->where('depot_id', '=', $depot)
                    ->getVal('division_id');
                $sendVehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->join('vehicle_variants', 'vehicle_variant=vehicle_variant_id')
                    ->where('vehicle_id', 'in', $vehicles)
                    ->get('vehicle_id,vin,code,ikz,windchill_variant_name');
                $directLink = "https://streetscooter-cloud-system.eu/index.php?action=auszulieferende&zsp=$depot";
                $tabelle = "";
                $n = 1;

                foreach ($sendVehicles as $vSet) {
                    $tablle .= sprintf("       %2d.  %-11s     %-11s     %-18s      %s\n", $n ++, $vSet['code'], $vSet['ikz'], $vSet['vin'], $vSet['windchill_variant_name']);
                    // 1. BN-PJ 2345E A9876543210 WS5B16DAAHA800030 B1406BPOSBC
                }

                $mailtext = str_replace("\n", "\r\n", str_replace([
                    '%DIRECT_LINK%',
                    '%TABELLE%'
                ], [
                    $directLink,
                    $tablle
                ], $mailTemplate));

                $fps_list = $this->ladeLeitWartePtr->allUsersPtr->getFPSEmails($division_id);
                $fps_emails = array();
                foreach ($fps_list as $fps) {
                    if (! isset($fps['fname']))
                        $fps['fname'] = '';
                    if (! isset($fps['lname']))
                        $fps['lname'] = '';
                    if (! empty($fps['email']))
                        $fps_emails[$fps['email']] = $fps['fname'] . '  ' . $fps['lname'];
                }

                $sentTo = implode(',', array_keys($fps_emails));
                $mailer = new MailerSmimeSwift($sentTo, '', 'StreetScooter Fahrzeug Zustellung', $mailtext, null, true, null);
                // new MailerSmimeSwift
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

        $i12 = ceil($nSOP / 2) * 16 + ($nPVS) * 7;
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
    function getDepotsWithFreeStationsForRole($max_vehicles_at_station = 1)
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

        // $max_vehicles_at_station = 1;
        $depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithFreeStations($unitParams, $max_vehicles_at_station);
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

        // $max_vehicles_at_station = 1;
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

        $DepotsWithOnlyFreeStations = $this->getDepotsWithOnlyFreeStationsForRole($this->max_vehicles_per_station);
        $allowedDepots = $this->getDepotsWithFreeStationsForRole($this->max_vehicles_per_station);

        $except_div = 0;
        if (is_numeric($depots)) {
            $div = $this->ladeLeitWartePtr->depotsPtr->getDivision($depots);
            $except_div = $div['division_id'];
        }

        $otherDivisions = $this->ladeLeitWartePtr->depotsPtr->getOtherDivisionsAusstehendeZuweisung($except_div);
        $listOtherDivs = make_map($otherDivisions, 'depot_id', 'name');

        $DepotsWithOnlyFreeStations[] = [
            'depot_id' => '-',
            'name' => '-'
        ];
        $DepotsWithOnlyFreeStations[] = [
            'depot_id' => 'nl',
            'name' => '&gt;&gt; Abgabe andere NL...'
        ];

        $allowedDepots[] = $this->ladeLeitWartePtr->depotsPtr->getDepotAustehendeZuweisung($div);
        // $allowedDepots[]=$this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
        $allowedDepots[] = $this->ladeLeitWartePtr->depotsPtr->getFleetPoolDepot($div);
        $allowedDepots[] = [
            'depot_id' => '-',
            'name' => '-'
        ];
        $allowedDepots[] = [
            'depot_id' => 'nl',
            'name' => '&gt;&gt; Abgabe andere NL...'
        ];

        $this->qform_vehicle_mgmt = new QuickformHelper($this->displayHeader, "qform_vehicle_mgmt");

        // display ZSP table
        $this->qform_vehicle_mgmt->getVehicleMgmt($this->vehicles, array_column($allowedDepots, 'name', 'depot_id'), $prostations, $profreestations, $this->zsp, $this->vehicle_variants, $listOtherDivs, array_column($DepotsWithOnlyFreeStations, 'name', 'depot_id'));

        // display Tree Structure for ZSP
        $this->treestructure = $this->ladeLeitWartePtr->restrictionsPtr->generateTreeStructureForDepot($this->zsp);

    }


    function printContent()
    {

        include ("pages/common/vehiclemgmt.php");

    }

}
