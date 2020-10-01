<?php

/**
 * ConversionTableController.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * ConversionTableController Class, the main class
 */
class ConversionTableController extends PageController {
    public $oQueryHolder;
    public $msgs;
    public $action;

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
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : Conversion Table");


        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/conversiontable.php");
    }

    function ajaxGetConversionTableSet() {
        $set_id = $_GET['set_id'];

        echo json_encode($this->getConversionTableCharactersForSet($set_id));
        return;
    }

    /***
     * returns ...
     * @return array
     */
    function getConversionTableSets() {
        return $this->ladeLeitWartePtr->newQuery('conversion_sets')
            ->get('id, frozen');
    }

    /***
     * returns ...
     * @return array
     */
    function getConversionTableCharactersForSet($id) {
        return $this->ladeLeitWartePtr->newQuery('conversion_characters')
            ->where('conversion_set', '=', $id)
            ->get('id, conversion_set, conversion_key, conversion_value');
    }
}
