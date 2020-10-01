<?php

/**
 * AdminHistory.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * AdminHistoryController Class, the main class
 */
class VehicleConfigurationController_DEPRECATED extends VehicleConfigurationControllerBase_DEPRECATED {
    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        if (isset($_GET['call'])) {
            $name = $_GET['call'];
            $this->$name();
        } else {
            $this->printContent();
        }

        $result = $this->getMiddleware()->makeRequest(Middleware::REQUEST_TYPE_GET, 'users/new');
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/vehicleConfiguration.php");
    }

    protected function ajaxUpdateCurrentVehicleConfiguration() {

        $vehicleConfiguration = new VehicleConfiguration($this->ladeLeitWartePtr, "188:704", 'DeprecatedParameter');

        $updateResult = $vehicleConfiguration->updateVehicleConfiguration($_POST);


        echo json_encode($vehicleConfiguration->returnView());
        exit;
    }

    protected function ajaxExecuteSelectedOperation() {
        $returnView = $this->getMiddleware()
            ->prepare(middleware::REQUEST_TYPE_GET, "/vehicles/")
            ->sentRequest();

        echo json_encode($returnView);
        exit;
    }

    protected function ajaxGetEditView() {
        $vehicleConfiguration = new VehicleConfiguration($this->ladeLeitWartePtr, $_GET['selectedConfiguration'], $_GET['operationType']);

        echo json_encode($vehicleConfiguration->returnEditView());
        exit;
    }
}