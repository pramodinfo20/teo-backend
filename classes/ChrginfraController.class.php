<?php

/**
 * Chrginfra_Controller.class.php
 * Parent class for classes Chrginfra_ebgController Chrginfra_aixController 
 * @author Pradeep Mohan
 */
class ChrginfraController extends PageController
{

    protected $display_header;

    protected $content;

    protected $stationprovider;

    protected $div;

    protected $zspl;

    protected $depot;

    protected $qform_div;

    protected $qform_zspl;

    protected $qform_depot;

    protected $qform_csv;

    protected $msgs;

    protected $csv_stn_msgs;

    protected $stn_msgs;

    protected $currentdepot;

    protected $depotRestrictionId;

    protected $restrictions;

    protected $restrictionsNames;

    protected $processedRestrictions;

    protected $processedStations;

    protected $listObjects;

    protected $listObjectsTableHeadings;


    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user)
    {

        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->content = "";
        $this->msgs = [];
        $this->stn_msgs = [];
        $this->div = null;
        $this->zspl = null;
        $this->depot = null;

        $this->display_header = $this->container->getDisplayHeader();

        $this->qform_div = new QuickformHelper($this->display_header, "chrg_infra_div_form");
        $this->qform_zspl = new QuickformHelper($this->display_header, "chrg_infra_zspl_form");
        $this->qform_depot = new QuickformHelper($this->display_header, "chrg_infra_depot_form");
        $this->qform_csv = null;

        $this->display_header->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());
        $this->display_header->enqueueJs("sts-custom-chrginfra", "js/sts-custom-chrginfra.js");

        $action = $this->requestPtr->getProperty('action');

        $this->div = $this->requestPtr->getProperty('div');
        $this->zspl = $this->requestPtr->getProperty('zspl');
        $this->depot = $this->requestPtr->getProperty('depot');

        if ($this->user->getUserRole() == "chrginfra_ebg")
            $this->stationprovider = 1;
        else if ($this->user->getUserRole() == "chrginfra_aix")
            $this->stationprovider = 2;
        else if ($this->user->getUserRole() == "chrginfra_innogy")
            $this->stationprovider = 3;
        else if ($this->user->getUserRole() == "chrginfra_csg")
            $this->stationprovider = 5;

        $this->getDivs();
        $this->getZspls();
        $this->getDepots();

        // @todo -> completed on 2017-04-26 needs to be integrated into the part where new depots are added to the database ZentraleController.class.php
        // $setup_restrictions= $this->requestPtr->getProperty ( 'setup_res' );

        // if(isset($setup_restrictions))
        // {
        // set_time_limit(0);
        // $setup_depots=$this->ladeLeitWartePtr->depotsPtr->getWhere('',array(
        // array('depot_restriction_id',' IS ',' NULL')
        // ));

        // $cnt=1;
        // foreach ($setup_depots as $setup_depot)
        // {

        // echo $cnt.' . '.$setup_depot['name'].' <br>'; $cnt++;
        // $newRestriction=array("parent_restriction_id"=>NULL,"name"=>"Hauptanschluss","power"=>39*215.0);
        // $new_parent_id=$this->ladeLeitWartePtr->restrictionsPtr->add($newRestriction,$setup_depot['depot_id']);
        // echo $new_parent_id.'<br>';
        // $insertCols=array('parent_restriction_id','name','power');
        // $insertVals=array(
        // array($new_parent_id,'Phase 1',13*215.0),
        // array($new_parent_id,'Phase 2',13*215.0),
        // array($new_parent_id,'Phase 3',13*215.0)
        // );
        // $newRestrictionId=$this->ladeLeitWartePtr->restrictionsPtr->addMultiple($insertCols,$insertVals);

        // }
        // }

        if (isset($this->depot) && $this->depot) {

            $this->getRestrictions();
            $this->getRestrictionsNames();

            if (isset($action))
                call_user_func(array(
                    $this,
                    $action
                ));

            $this->getLadegruppen();
            $this->getLadepunkte();

            if ($this->user->getUserRole() == "chrginfra_aix" || $this->user->getUserRole() == "chrginfra_csg") {
                // $this->getCSVForm(); @todo removing this temporarily since AixACCT does not use it
                $this->getAutoGenCtrl();
            }
        } else {
            if ($this->user->getUserRole() == "chrginfra_ebg") {
                if ($action == 'save_stations_upload')
                    $this->save_stations_upload();
                else
                    $this->getCSVEbgForm();
            }
        }

        $this->display_header->printContent();

        $this->printContent();

    }


    function getCSVEbgForm()
    {

        $this->qform_csv = new QuickformHelper($this->display_header, "stations_upload_form");
        $this->qform_csv->csvUpload("Ladepunkte/Ladegruppe", "stations");
        $this->qform_csv->addElement('hidden', 'depot', array(
            'value' => $this->depot
        ));
        $this->qform_csv->addElement('hidden', 'zspl', array(
            'value' => $this->zspl
        ));
        $this->qform_csv->addElement('hidden', 'div', array(
            'value' => $this->div
        ));

    }


    function getCSVForm()
    {

        $this->qform_csv = new QuickformHelper($this->display_header, "stations_upload_form");
        $this->qform_csv->csvUpload("Ladepunkte", "stations");
        $this->qform_csv->addElement('hidden', 'depot', array(
            'value' => $this->depot
        ));
        $this->qform_csv->addElement('hidden', 'zspl', array(
            'value' => $this->zspl
        ));
        $this->qform_csv->addElement('hidden', 'div', array(
            'value' => $this->div
        ));

    }


    /**
     * returns only those divisions whose depots are assigned to the user role.
     */
    function getDivs()
    {

        $divisions = $this->ladeLeitWartePtr->divisionsPtr->getDivsForChrgInfra($this->stationprovider);
        $processedDivisions = array(
            '' => ''
        );

        foreach ($divisions as $singleDiv) {
            $processedDivisions[$singleDiv['division_id']] = $singleDiv['name'] . ' : ' . $singleDiv['dp_division_id'];
        }

        $displaySelect = $this->qform_div->genSelect("div", array(
            "id" => "divisionSelect"
        ), $processedDivisions, 'Niederlassung wählen/eintippen', $this->div);

    }


    function getZspls()
    {

        $zspls = $this->ladeLeitWartePtr->zsplPtr->getZsplsForChrgInfra($this->stationprovider, $this->div);
        $processedZspl = array(
            '' => ''
        );

        foreach ($zspls as $singlezspl) {
            $processedZspl[$singlezspl['zspl_id']] = $singlezspl['name'] . ' : ' . $singlezspl['dp_zspl_id'];
        }

        $this->qform_zspl->genSelect("zspl", array(
            "id" => "zsplSelect"
        ), $processedZspl, 'ZSPL wählen/eintippen', $this->zspl, array(
            'div' => $this->div
        ));

    }


    function getDepots()
    {

        $depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsForChrgInfra($this->stationprovider, $this->div, $this->zspl);

        $processedDepots = array(
            '' => ''
        );

        foreach ($depots as $singleDepot) {
            $processedDepots[$singleDepot['depot_id']] = $singleDepot['name'] . ' : ' . $singleDepot['dp_depot_id'];
        }

        $this->qform_depot->genSelect("depot", array(
            "id" => "depotSelect"
        ), $processedDepots, 'oder direkt ZSP wählen/eintippen', $this->depot, array(
            'div' => $this->div,
            'zspl' => $this->zspl
        ));

    }


    function getRestrictions()
    {

        $this->currentdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($this->depot);

        if (isset($this->currentdepot['depot_restriction_id'])) {
            $restrictions = array(
                $this->currentdepot['depot_restriction_id']
            ); // @todo code repeat
            $this->ladeLeitWartePtr->restrictionsPtr->iterativeRestrictions($this->currentdepot['depot_restriction_id'], $restrictions);
        } else
            $restrictions = array();

        $this->restrictions = $restrictions;

    }


    function getRestrictionsNames()
    {

        $this->restrictionsNames = array();

        if (! empty($this->restrictions)) {
            foreach ($this->restrictions as $restriction) {
                $currentRestriction = $this->ladeLeitWartePtr->restrictionsPtr->getFromId($restriction, array(
                    'restriction_id',
                    'name',
                    'power',
                    'parent_restriction_id'
                ));

                $this->restrictionsNames[$restriction] = $currentRestriction['name'];
            }
        }

    }


    function getLadegruppen()
    {

        $post_parent_restrictions = $parentRestrictions = $restrictions = $this->restrictions;

        $qform_restriction = new QuickformHelper($this->display_header, "restriction_select");
        sort($restrictions);
        $currentdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($this->depot);
        if (! empty($restrictions)) {
            foreach ($restrictions as $restriction) {
                $currentRestriction = $this->ladeLeitWartePtr->restrictionsPtr->getFromId($restriction, array(
                    'restriction_id',
                    'name',
                    'power',
                    'parent_restriction_id'
                ));
                $allowedParentIds = $this->ladeLeitWartePtr->restrictionsPtr->getAllowedParentsIds($restriction, $parentRestrictions, $currentdepot['depot_restriction_id']);
                $qform_restriction->getRestrictionEdit($currentRestriction, $allowedParentIds); // @todo edited to allow adding subgroups
            }

            $allowedParentIds = $this->ladeLeitWartePtr->restrictionsPtr->getAllowedParentsIds(null, $parentRestrictions, $currentdepot['depot_restriction_id']);
            $qform_restriction->addNewRestriction($this->depot, $allowedParentIds);
        }

        $qform_restriction->addElement('hidden', 'depot', array(
            'value' => $this->depot
        ));
        $qform_restriction->addElement('hidden', 'zspl', array(
            'value' => $this->zspl
        ));
        $qform_restriction->addElement('hidden', 'div', array(
            'value' => $this->div
        ));
        $qform_restriction->addElement('hidden', 'action', array(
            'id' => 'action',
            'value' => "saveRestriction"
        ));
        $qform_restriction->addElement('hidden', 'deleteGrp', array(
            'id' => 'deleteGrp',
            'value' => 'null'
        ));
        $qform_restriction->addElement('submit', 'restriction_save', array(
            'id' => 'restriction_save',
            'value' => 'Speichern'
        ));
        $this->processedRestrictions = $qform_restriction->getContent();

    }


    function delRestriction()
    {

        $delgrp = $_POST['deleteGrp'];
        $delgrp = $_POST[$delgrp];

        if ($this->ladeLeitWartePtr->restrictionsPtr->delete($delgrp, $this->depot) === true) {
            $this->ladeLeitWartePtr->restrictionsPtr->updatePower($this->depot);
            $this->msgs[] = 'Ladegruppe gelöscht.';
        } else {
            $this->msgs = $this->ladeLeitWartePtr->restrictionsPtr->getErrorMsgs();
        }

        unset($_POST['deleteGrp']);
        unset($_POST['action']);

        // do not delete.. we need to reset the restrictions after deleting/saving a restriction
        $this->getRestrictions();
        $this->getRestrictionsNames();

    }


    function saveRestriction()
    {

        $currentdepot = $this->currentdepot;
        $post_parent_restrictions = $parentRestrictions = $restrictions = $this->restrictions;

        if (isset($_POST['grp_new']))
            $newRestriction = $_POST['grp_new'];
        if (! empty($newRestriction['name'])) {
            $newRestriction['power'] = (float) $_POST['grp_new']['power'] * 215.0;

            if ($newRestriction['parent_restriction_id'] == 'null')
                $newRestriction['parent_restriction_id'] = NULL;

            $newRestrictionId = $this->ladeLeitWartePtr->restrictionsPtr->add($newRestriction, $this->depot);
            $this->ladeLeitWartePtr->restrictionsPtr->updatePower($this->depot);
        } else {
            foreach ($parentRestrictions as $restriction) {
                $currentRestriction = $this->ladeLeitWartePtr->restrictionsPtr->getFromId($restriction, array(
                    'restriction_id',
                    'name',
                    'power',
                    'parent_restriction_id'
                ));

                $compareRestriction = $_POST['grp_' . $restriction];

                if ($compareRestriction['parent_restriction_id'] == 'null')
                    $compareRestriction['parent_restriction_id'] = NULL;

                $compareRestriction['power'] = (float) $compareRestriction['power'] * 215.0;

                if (count(array_intersect_assoc($currentRestriction, $compareRestriction)) < sizeof($currentRestriction)) // there is a difference in the arrays and so updated needed
                {
                    if ($compareRestriction['parent_restriction_id'] !== NULL) {
                        /* @todo check Central_Manager.php 20160224 */
                        if ($this->ladeLeitWartePtr->restrictionsPtr->checkTreeStructure($compareRestriction['parent_restriction_id'], $post_parent_restrictions) === false) {
                            $this->msgs[] = 'Gruppen dürfen nicht innerhalb des Baumes an sich selber angeschlossen werden.';
                            break;
                        }
                    } else // $v1 != $current_base_depot_restriction_id is always satisfied
                    {
                        $this->msgs[] = 'Um eine Gruppe zur neuen obersten Gruppe zu machen, die oberste Gruppe löschen.';
                        break;
                    }

                    $updateCols = array_keys($compareRestriction);
                    $updateVals = array_values($compareRestriction);

                    $this->ladeLeitWartePtr->restrictionsPtr->save($updateCols, $updateVals, array(
                        'restriction_id',
                        '=',
                        $compareRestriction["restriction_id"]
                    ));
                    $this->ladeLeitWartePtr->restrictionsPtr->updatePower($this->depot);

                    // @todo notifyChanges ( $depotID, $_POST, "editGroup" );
                }
            }
        }
        // do not delete.. we need to reset the restrictions after deleting/saving a restriction
        $this->getRestrictions();
        $this->getRestrictionsNames();

    }


    function getLadepunkte()
    {

        $qform_station = new QuickformHelper($this->display_header, "station_select");

        $stations = $this->ladeLeitWartePtr->stationsPtr->getStationsAndVehiclesForDepots($this->depot);

        $restrictions_names = $this->restrictionsNames;

        $restrictions_names = array_filter($restrictions_names, function ($v) {
            return $v != "Hauptanschluss";
        });
        if (! empty($stations)) {
            foreach ($stations as $station) {
                $qform_station->getStationEdit($station, $restrictions_names, $this->user->getUserRole());
            }
        }

        $qform_station->addNewStation($restrictions_names, $this->user->getUserRole());

        $qform_station->addElement('hidden', 'depot', array(
            'value' => $this->depot
        ));
        $qform_station->addElement('hidden', 'zspl', array(
            'value' => $this->zspl
        ));
        $qform_station->addElement('hidden', 'div', array(
            'value' => $this->div
        ));
        $qform_station->addElement('hidden', 'deleteStn', array(
            'id' => 'deleteStn',
            'value' => 'null'
        )); // @todo
        $qform_station->addElement('hidden', 'action', array(
            'id' => 'action_station',
            'value' => 'saveStation'
        ));
        $qform_station->addElement('submit', 'station_save', array(
            'id' => 'station_save',
            'value' => 'Speichern'
        )); // @todo

        $this->processedStations = $qform_station->getContent();

    }


    function saveStation()
    {

        $newStation = $_POST['stn_new'];
        if (! empty($newStation['name'])) {

            $newStation['station_power'] = (float) $_POST['stn_new']['station_power'] * 215.0;

            if ($newStation['restriction_id'] == 'null' && $newStation['restriction_id2'] == 'null' && $newStation['restriction_id3'] == 'null') {
                $this->stn_msgs[] = '<span class="error_message">Bitte wahlen Sie eine Ladegruppe</span>';
            } else {
                if ($newStation['restriction_id'] == 'null')
                    $newStation['restriction_id'] = NULL;
                if ($newStation['restriction_id2'] == 'null')
                    $newStation['restriction_id2'] = NULL;
                if ($newStation['restriction_id3'] == 'null')
                    $newStation['restriction_id3'] = NULL;
                $newStation['depot_id'] = $this->depot;
                $newStationId = $this->ladeLeitWartePtr->stationsPtr->add($newStation, $this->depot);
            }
        } else {
            $stations = $this->ladeLeitWartePtr->stationsPtr->getStationsForDepot($this->depot);
            foreach ($stations as $station) {

                $compareStation = $_POST['stn_' . $station["station_id"]];
                if (! isset($compareStation['deactivate']))
                    $compareStation['deactivate'] = 'f';

                if ($compareStation['restriction_id'] == 'null' && $compareStation['restriction_id2'] == 'null' && $compareStation['restriction_id3'] == 'null') {
                    $this->stn_msgs[] = '<span class="error_message">Bitte wahlen Sie eine Ladegruppe</span>';
                    break;
                }

                if ($compareStation['restriction_id'] == 'null')
                    $compareStation['restriction_id'] = NULL;
                if ($compareStation['restriction_id2'] == 'null')
                    $compareStation['restriction_id2'] = NULL;
                if ($compareStation['restriction_id3'] == 'null')
                    $compareStation['restriction_id3'] = NULL;

                $compareStation['station_power'] = (float) $compareStation['station_power'] * 215.0;

                if ($station != $compareStation) // there is a difference in the arrays and so updated needed
                {

                    $updateCols = array_keys($compareStation);
                    $updateVals = array_values($compareStation);

                    $this->ladeLeitWartePtr->stationsPtr->save($updateCols, $updateVals, array(
                        'station_id',
                        '=',
                        $compareStation["station_id"]
                    ));
                    // notifyChanges ( $depotID, $_POST, "editGroup" );
                }
            }
        }

    }


    function deleteStn()
    {

        $deleteStn = $_POST['deleteStn']; // stn_14
        $deleteStn = $_POST[$deleteStn]; // gets the post variables of stn_14 .. now an array containing details of station is stored in delgrp
        if (! empty($deleteStn)) {
            if ($this->ladeLeitWartePtr->stationsPtr->delete($deleteStn) === true) {
                $this->stn_msgs[] = '<span class="error_message">Ladepunkt gelöscht.</span>';
            } else {
                $this->stn_msgs = $this->ladeLeitWartePtr->stationsPtr->getErrorMsgs();
            }
        } else
            $this->stn_msgs[] = '<span class="error_message">Fehler!</span>';

        unset($_POST['deleteStn']);
        unset($_POST['action']);

    }


    function printContent()
    {

        include ("pages/chrginfra.php");

    }

}

