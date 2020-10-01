<?php
/**
 * FpvController.class.php
 * Controller for User Role Fuhrparkverwaltung
 * @author Pradeep Mohan
 */


class FpvController extends FuhrparksteuerController {
    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->zspl_id = $user->getAssignedZspl();

        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);
    }


    /**
     * Show list of depots for the ZSP assigned to user
     */
    function showDepots() {
        $this->listObjects = $this->ladeLeitWartePtr->depotsPtr->getAllInZspl($this->zspl_id);
        $this->listObjectsHeading = "ZSPn Email Adressen verwalten";
        $this->listObjectsTableHeadings = array(array('ZSP'), array('Email Adressen'));

    }


    function save_exist_depot() {

        if (!$this->user->user_can('addzspemails')) {
            $this->msgs[] = "Benutzer darf nicht diese ZSP verwalten.";
        } else {
            $depotemails = $this->requestPtr->getProperty('depotemails');
            if ($depotemails)
                $depotemails = serialize(explode("\r\n", trim($depotemails)));
            $depotname = $this->requestPtr->getProperty('depotname');
            $depot_id = $this->requestPtr->getProperty('depot');


            $this->qform = new QuickformHelper ($this->displayHeader, "depot_add_edit_form");

            $currentdepot["depot_id"] = $depot_id;
            $currentdepot["zspl_id"] = $this->zspl_id;
            $currentdepot["emails"] = $depotemails;
            $currentdepot["name"] = $depotname;


            $currentzspl = $this->ladeLeitWartePtr->zsplPtr->getFromId($this->zspl_id);
            $this->qform->depot_edit_form($currentzspl, true, $currentdepot);

            if (!$this->qform->formValidate()) {
                $depot_errors = ""; //@todo What Fehler? Error with submitted data.
                $this->edit_depot();
            } else {
                $this->ladeLeitWartePtr->depotsPtr->save(array("emails", "name"), array($depotemails, $depotname), array('depot_id', '=', $depot_id));

                $this->msgs[] = "ZSP gespeichert!";
            }
        }
    }


    function edit_depot() {
        $depot_id = (int)$this->requestPtr->getProperty('depot');


        $currentzspl = $this->ladeLeitWartePtr->zsplPtr->getFromId($this->zspl_id);
        $currentzspl = $currentzspl; //get just first row

        $editThisdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($depot_id);

        if ($this->qform == "")// this is to ensure form data is saved when validation is done on the SERVER side
        {
            $this->qform = new QuickformHelper ($this->displayHeader, "depot_add_edit_form");

            $this->qform->depot_edit_form($currentzspl, true, $editThisdepot);
        }

    }

    function printContent() {
        include("pages/" . $this->user->getUserRole() . ".php");
    }
}

