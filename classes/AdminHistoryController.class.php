<?php

/**
 * AdminHistory.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * AdminHistoryController Class, the main class
 */
class AdminHistoryController extends PageController {

    public $oQueryHolder;
    public $msgs;

    public $sCreateDbTable = "";

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->msgs = null;
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

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/admin-history.php");
    }

    protected function ajaxGetHistory() {
        $context = $_GET['context'];

        $history = $this->getHistory($context);

        echo json_encode($history);
        return;
    }

    private function getHistory($context) {
        return $this->ladeLeitWartePtr->newQuery('change_log_history')
            ->where("context", "=", $context)
            ->join("users", "users.id=change_log_history.user_id", "LEFT JOIN")
            ->orderBy("posting_date", "DESC")
            ->get("users.username, change_log_history.posting_date, change_log_history.description");
    }


}
