<?php

class EcuSwPropertySettingsController extends EcuSwConfigurationController_DEPRECATED {
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->displayHeader = $this->container->getDisplayHeader();
        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/ecu-property-settings-view.php");
    }
}

?>