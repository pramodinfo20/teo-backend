<?php

/**
 * AdminHistory.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * AdminHistoryController Class, the main class
 */

$GLOBALS['search_hide_fieldset'] = true;

abstract class VehicleConfigurationControllerBase_DEPRECATED extends PageController {

    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;


        $config_leitwarte = $GLOBALS['config']->get_db('leitwarte');
        $databasePtr = new DatabasePgsql (
            $config_leitwarte['host'],
            $config_leitwarte['port'],
            $config_leitwarte['db'],
            $config_leitwarte['user'],
            $config_leitwarte['password'],
            new DatabaseStructureCommon1()
        );
        $this->oDbPtr = $databasePtr;
        $this->oQueryHolder = new NewQueryPgsql($this->oDbPtr);


        $this->action = $this->requestPtr->getProperty('action');

        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());
        $this->msgs = null;
        $this->displayHeader->enqueueStylesheet("parameterlist", "css/parameterlist.css");

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        }
    }

    // Ajax functions
    protected function ajaxGetTypesList() {
        $result = $this->sqlGetAllTypes();
        echo json_encode($result);
        exit;
    }

    protected function ajaxGetYearsList() {
        $result = $this->sqlGetListOfAllYears($_GET['vehicleType']);
        echo json_encode($result);
        exit;
    }

    protected function ajaxGetSeriesList() {
        $result = $this->sqlGetListOfAllSeries($_GET['vehicleType'], $_GET['vehicleYear']);
        echo json_encode($result);
        exit;
    }

    protected function ajaxGetSearchResult() {
        $result = $this->sqlGetListOfVehicleConfigurations($_GET['vehicleType'], $_GET['vehicleYear'], $_GET['vehicleSeries']);
        echo json_encode($result);
        exit;
    }

    protected function ajaxGetSearchResultByTextInput() {
        $result = $this->sqlGetListOfVehicleConfigurationsViaSearch($_GET['textSearch']);
        echo json_encode($result);
        exit;
    }

    private function sqlGetAllTypes() {
        $types = $this->ladeLeitWartePtr->newQuery('new_vehicle_configurations')
            ->orderBy('type', 'ASC')
            ->get('DISTINCT ON(type) type');
        $result = [];
        foreach ($types as $key => $value)
            array_push($result, $value['type']);

        return $result;
    }

    private function sqlGetListOfAllYears($vehicleType) {
        $years = $this->ladeLeitWartePtr->newQuery('new_vehicle_configurations')
            ->where('type', '=', $vehicleType)
            ->orderBy('year', 'ASC')
            ->get('DISTINCT ON(year) year');
        $result = [];
        foreach ($years as $key => $value)
            array_push($result, $value['year']);

        return $result;
    }

    private function sqlGetListOfAllSeries($vehicleType, $vehicleYear) {
        $series = $this->ladeLeitWartePtr->newQuery('new_vehicle_configurations')
            ->where('type', '=', $vehicleType)
            ->where('year', '=', $vehicleYear)
            ->orderBy('series', 'ASC')
            ->get('DISTINCT ON(series) series');
        $result = [];
        foreach ($series as $key => $value)
            array_push($result, $value['series']);

        return $result;
    }

    private function sqlGetListOfVehicleConfigurations($vehicleType, $vehicleYear, $vehicleSeries) {
        $configurations = $this->ladeLeitWartePtr->newQuery('new_vehicle_configurations')
            ->where('type', '=', $vehicleType)
            ->where('year', '=', $vehicleYear)
            ->where('series', '=', $vehicleSeries)
            ->join('new_sub_vehicle_configurations', 'using(vehicle_configuration_id)', 'INNER JOIN')
            ->orderBy('vehicle_configuration_key', 'ASC')
            ->orderBy('name', 'ASC')
            ->get('vehicle_configuration_id, vehicle_configuration_key, sub_vehicle_configuration_id, name');

        $result = [[]];
        $minor = [];
        foreach ($configurations as $key => $value) {
            $result[$value['vehicle_configuration_id']]['vehicle_configuration_key'] = $value['vehicle_configuration_key'];
            $minor['sub_vehicle_configuration_id'] = $value['sub_vehicle_configuration_id'];
            $minor['name'] = $value['name'];
            if (!(isset($result[$value['vehicle_configuration_id']]['minors'])))
                $result[$value['vehicle_configuration_id']]['minors'] = [];
            array_push($result[$value['vehicle_configuration_id']]['minors'], $minor);
        }

        return $result;
    }

    private function sqlGetListOfVehicleConfigurationsViaSearch($textSearch) {
        $configurations = $this->ladeLeitWartePtr->newQuery('new_vehicle_configurations')
            ->where('vehicle_configuration_key', 'LIKE', '%' . $textSearch . '%')
            ->join('new_sub_vehicle_configurations', 'using(vehicle_configuration_id)', 'INNER JOIN')
            ->orderBy('vehicle_configuration_key', 'ASC')
            ->orderBy('name', 'ASC')
            ->get('vehicle_configuration_id, vehicle_configuration_key, sub_vehicle_configuration_id, name');

        $result = [[]];
        $minor = [];
        foreach ($configurations as $key => $value) {
            $result[$value['vehicle_configuration_id']]['vehicle_configuration_key'] = $value['vehicle_configuration_key'];
            $minor['sub_vehicle_configuration_id'] = $value['sub_vehicle_configuration_id'];
            $minor['name'] = $value['name'];
            if (!(isset($result[$value['vehicle_configuration_id']]['minors'])))
                $result[$value['vehicle_configuration_id']]['minors'] = [];
            array_push($result[$value['vehicle_configuration_id']]['minors'], $minor);
        }

        return $result;
    }
}