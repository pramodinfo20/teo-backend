<?php
define('FLASHSCREEN', 0);
define('EVALUATION', 1);

class ACtion_Flashinterface extends AClass_Base {
    function __construct() {
        parent::__construct();
        if ($this->controller) {
            $this->leitwartePtr = $this->controller->GetObject('ladeleitwarte');
            $this->diagnosePtr = $this->controller->GetObject('diagnose');
        }

    }

    function Init() {
        if (!empty($_GET['vin'])) {
            $_SESSION['vin'] = $_GET['vin'];
        }
    }

    function Execute() {

        parent::Execute();
        switch ($_GET['command']) {
            case 'flash':
                $this->flashbcm();
                $this->state = EVALUATION;
                break;
            default:
                $this->state = FLASHSCREEN;
        }
    }

    function flashbcm() {
        $this->diagnosePtr->newQuery('sota_tasks')->insert(['vin' => $_SESSION['vin'], 'task_type_id' => 1, 'workshop_id' => $_SESSION['role']['workshop_id'], 'timestamp_created' => date('Y-m-d G:i:s O')]);
    }

    function WriteHtmlContent() {
        parent::WriteHtmlContent();
        switch ($this->state) {
            case FLASHSCREEN:
                if (ord($_SESSION['vin'][6]) > 69) {
                    echo 'Wollen Sie das Fahrzeug ' . $_SESSION['vin'] . ' flashen?<a href="' . $_SERVER['PHP_SELF'] . '?action=flashinterface&command=flash" style="border: 1px solid black; border-radius: 4px; margin: 3px; padding:4px; background: linear-gradient(to bottom,#ffe680 0,#FFCC00 90%,#ffe680 100%); color:black;">Bestätigen</a>';
                } else {
                    echo 'Es können nur Fahrzeuge der Serie F oder neuer geflashed werden';
                }
                break;
            case EVALUATION:
                echo 'Flashvorgang wurde in Auftrag gegeben';
                break;
        }

        echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?action=home&initPage&command=back">Zurück zur Auswahl</a>';

    }
}