<?php
/**
 * AdminController.class.php
 * Controller for User Role Admin
 * @author Pradeep Mohan
 */


class AdminController extends PageController {
    protected $content;
    protected $msgs;

    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->content = "";
        $this->msgs = "";
        $this->trees = array();
        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());

        $this->action = $this->requestPtr->getProperty('action');
        if (isset($this->action))
            call_user_func(array($this, $this->action));


        $this->displayHeader->printContent();

        $this->printContent();

    }

    function checkphases() {
        $depots = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')->where('vin', 'LIKE', 'WS5B16%')->get('distinct(vehicles.depot_id),depots.name,depots.depot_restriction_id');
        $sopvariants = $this->ladeLeitWartePtr->vehiclesPtr->getSopVariants();

        $vehicleMgmt = new CommonFunctions_VehicleManagement_Sales($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'fahrzeugverwaltung', $sopvariants);
        foreach ($depots as $depot) {
            echo $depot['name'] . ' ';
            if ($vehicleMgmt->checkCombo($depot['depot_restriction_id'], $sopvariants) === true)
                echo $depot['name'] . ' ok <br>';
            else {
                $processed_listObjects = array();
                $vehicles_stations = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicles.depot_id', '=', $depot['depot_id'])
                    ->join('stations', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
                    ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
                    ->get('vehicles.vehicle_id,vehicles.station_id,stations.name as sname, vehicles.vin,vehicles.name,vehicles.code,vehicles.fallback_power_even,vehicles.fallback_power_odd');

                $processed_listObjects[] = array('headingone' => array('Fahrzeug', 'Ladepunkt', 'Fallback Power Even', 'Fallback Power Odd'));
                $vehiclesstationsstr = '';
                if (!empty($vehicles_stations)) {
                    foreach ($vehicles_stations as $vehicle) {
                        $processed_listObjects[] = array($vehicle['vin'], $vehicle['sname'], $vehicle['fallback_power_even'], $vehicle['fallback_power_odd']);
                    }

                    $displaytable = new DisplayTable ($processed_listObjects, array('id' => 'zentralelist'));
                    $vehiclesstationsstr = $displaytable->getContent();
                    echo $vehiclesstationsstr . '<br>';
                }

            }
        }
    }

    function duplicatedepots() {
        $this->depots = $this->ladeLeitWartePtr->depotsPtr->newQuery()->groupBy('name')->having('count(depot_id)', '>', 1)->get('name,json_agg(depot_id) as depot_ids');

        foreach ($this->depots as $thisdepot) {
            $affected_depots = (json_decode($thisdepot['depot_ids']));
            $temp_trees = array();
            foreach ($affected_depots as $depot_id) {
                $depot = $this->ladeLeitWartePtr->depotsPtr->newQuery()->where('depot_id', '=', $depot_id)->getOne('*');
                $restrictions = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()->where('parent_restriction_id', '=', $depot['depot_restriction_id'])->get('name,power');
                $address_str = $depot['housenr'] . ' ' . $depot['street'] . $depot['postcode'];
                $restrictions_str = '';
                foreach ($restrictions as $restriction) {
                    $restrictions_str .= $restriction['name'] . ':' . ($restriction['power'] / 215) . 'A <br>';
                }
                $processed_listObjects = array();
                $vehicles_stations = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesAndStations($depot_id);
                $processed_listObjects[] = array('headingone' => array('Fahrzeug', 'Ladepunkt', 'Anlieferungsdatum'));
                $vehiclesstationsstr = '';
                if (!empty($vehicles_stations)) {
                    foreach ($vehicles_stations as $vehicle) {
                        if (!empty($vehicle['delivery_date']))
                            $vehicle['delivery_date'] = date('d.m.Y', strtotime($vehicle['delivery_date']));
                        else
                            $vehicle['delivery_date'] = '';
                        $processed_listObjects[] = array($vehicle['code'], $vehicle['sname'], $vehicle['delivery_date']);
                    }

                    $displaytable = new DisplayTable ($processed_listObjects, array('id' => 'zentralelist'));
                    $vehiclesstationsstr = $displaytable->getContent();
                }
                $temp_trees[] = '<h1>' . $depot['name'] . '(' . $depot['dp_depot_id'] . ')</h1>' . $address_str . '<br>' . $restrictions_str . $vehiclesstationsstr;

                //$this->ladeLeitWartePtr->restrictionsPtr->generateTreeStructureForDepot($depot_id);
            }
            $this->trees[] = $temp_trees;
        }
    }

    function printContent() {
        include("pages/" . $this->user->getUserRole() . ".php");
    }
}

