<?php

/**
 * EcuSwDiagnosticValueSettingController.class.php
 * The sub-class
 * @author Sebastian Warszawa, FEV Polska
 */

class EcuSwDiagnosticValueSettingController extends PageController {
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);
        $this->translate = parent::getTranslationsForDomain();

        $this->displayHeader = $container->getDisplayHeader();
        $this->displayFooter = $container->getDisplayFooter();

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();

        $this->symfonyView = $this->prepareDiagnosticParameterValueSettingView();
        include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";

        echo $this->symfonyView;

        $this->displayFooter->printContent();
    }

    public function prepareDiagnosticParameterValueSettingView() {
        return $this->getMiddleware()->prepare(Middleware::REQUEST_TYPE_GET, "ecu/diagnostic/parameter")
            ->sentRequest();
    }

    function ajaxSaveSupportODX2FlagToECU() {
        return $this->getMiddleware()->prepare(Middleware::REQUEST_TYPE_POST, "ecu/diagnostic/parameter/save_support/ecu/" . $_GET['ecu'])
            ->sentRequest();
    }

    function saveSupportODX2FlagToECU($ecu, $flag) {
//    $q = "";
        if ($flag == "true") {
            $q = "UPDATE public.ecus SET supports_odx02='TRUE' WHERE ecu_id=$ecu";
        } else {
            $q = "UPDATE public.ecus SET supports_odx02='FALSE' WHERE ecu_id=$ecu";
        }
        return $this->oQueryHolder->query($q);
    }
}