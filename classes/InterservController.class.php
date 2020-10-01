<?php
/**
 * InterservController.class.php
 * Controller for User Role Fuhrparkverwaltung
 * @author Pradeep Mohan
 */


class InterservController extends ZentraleController {
    protected $displayHeader;
    protected $content;
    protected $msgs;
    protected $zspl_id;
    protected $qform;
    protected $qform_zsp;
    protected $listObjects;
    protected $listObjectsHeading;
    protected $listObjectsTableHeadings;
    protected $objectLabel;
    protected $action;
    protected $listVS;

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
        $this->qform = "";
        $this->action = null;
        $this->listVS = null;


        $this->displayHeader = $this->container->getDisplayHeader();

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());
        $this->displayHeader->enqueueJs("sts-custom-interserv", "js/sts-custom-interserv.js");

        $this->action = $this->requestPtr->getProperty('action');

        if (empty($this->action)) $this->action = 'zspn';

        if (isset($this->action))
            call_user_func(array($this, $this->action));


        $this->displayHeader->printContent();

        $this->printContent();

    }

    function overview() {
        $this->showListObjects(); //inherited from ZentraleClass

    }

    function savenewlp() {
        $newStation = array();
        $newStation['name'] = $this->action = $this->requestPtr->getProperty('newlpname');
        $newStationSafety = 16;
        $zsp = $this->action = $this->requestPtr->getProperty('zsp');

        $depot = $this->ladeLeitWartePtr->depotsPtr->getFromId($zsp);

        $current_restriction_id = $depot['depot_restriction_id'];

        $newStation['station_power'] = ( float )$newStationSafety * 215.0;

        $restrictionId = $this->ladeLeitWartePtr->restrictionsPtr->getTempDepotRes($current_restriction_id);

        if (!$restrictionId) {
            $newRestriction = array();
            $newRestriction['name'] = 'TempDepotRes' . $zsp;
            $newRestriction['parent_restriction_id'] = $current_restriction_id;
            $newRestriction['power'] = $newStation['station_power'];

            $newPhaseId = $this->ladeLeitWartePtr->restrictionsPtr->add($newRestriction, $zsp);
            $this->ladeLeitWartePtr->restrictionsPtr->updatePower($zsp);


            $newStation['restriction_id'] = $newPhaseId;

        } else {
            $newStation['restriction_id'] = $restrictionId;

        }

        $newStation['depot_id'] = $zsp;

        $newStationId = $this->ladeLeitWartePtr->stationsPtr->add($newStation, $zsp);

        $currentPower = $this->ladeLeitWartePtr->restrictionsPtr->getFromId($newStation['restriction_id']);

        $currentPower = $currentPower['power'];
        $newPower = $currentPower + $newStation['station_power'];

        $this->ladeLeitWartePtr->restrictionsPtr->save(array('power'), array($newPower), array('restriction_id', '=', $newStation['restriction_id']));

        $this->ladeLeitWartePtr->restrictionsPtr->updatePower($zsp);

        echo '{ "selabel": "' . $newStation['name'] . '",
				"seval": "' . $newStationId . '"}';
        exit(0);

    }

    function zspn() {
        $depots = $this->ladeLeitWartePtr->depotsPtr->getWhere(null, array(array('depot_id', '>', 0)));
        foreach ($depots as $depot) {
            $vehicles_depot = $this->ladeLeitWartePtr->vehiclesPtr->getWhere(array('vehicle_id', 'vin', 'code', 'station_id'), array(
                array('depot_id', '=', $depot['depot_id'])
            ),
                array(array('code', 'ASC')
                ));

            $stations = $this->ladeLeitWartePtr->stationsPtr->getWhere(null, array(
                array('depot_id', '=', $depot['depot_id'])
            ));
            if (sizeof($stations) < sizeof($vehicles_depot)) {
                $depot['vehicleCnt'] = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesCnt('depot', $depot['depot_id']);
                if (!$depot['vehicleCnt']) $depot['vehicleCnt'] = '';

                $this->listObjects[] = $depot;

            }
        }


        $this->listObjectsHeading = "ZSPs mit weniger Ladepunkten als Fahrzeuge";
        $this->listObjectsTableHeadings = array('ZSP (OZ Nummer)', 'Anzahl der ausgelieferten Sts-Fahrzeuge');

    }

    /** ajax function
     *
     */
    function listAssignedVehiclesStations($ajax = null, $zsp = null) {

        if (!isset($ajax)) {
            $ajax = $this->requestPtr->getProperty('ajax');
            $zsp = $this->requestPtr->getProperty('zsp');
        }

        $vehicles_depot = $this->ladeLeitWartePtr->vehiclesPtr->getWhere(array('vehicle_id', 'vin', 'code', 'station_id'), array(
            array('depot_id', '=', $zsp)
        ),
            array(array('code', 'ASC')
            ));
        $depot = $this->ladeLeitWartePtr->depotsPtr->getWhere(array('depot_id', 'name'), array(
            array('depot_id', '=', $zsp)
        ));

        $depotname = $depot[0]['name'];


        $stations = $this->ladeLeitWartePtr->stationsPtr->getWhere(null, array(
            array('depot_id', '=', $zsp)
        ));

        asort($stations);


        $processedVehicles[] = array('headingone' => array('ZSP', 'Fahrzeug VIN / Kennzeichen', 'Ladepunkte'));
        if (!empty($vehicles_depot)) {
            $cnt = 0;
            foreach ($vehicles_depot as $vehicle) {
                $thisvehicle = array();

                $stationslist = '<option value=NULL>Nicht zugeordnet</option>';
                '';
                $thisvehicle['depot_name'] = $depotname;
                foreach ($stations as $station) {
                    $extra = '';
                    if ($station['station_id'] == $vehicle['station_id']) {
                        $extra = 'selected ';

                    }
                    $stationslist .= '<option value=' . $station['station_id'] . ' ' . $extra . '>' . $station['name'] . '</option>';

                }
                $thisvehicle['vin'] = $vehicle['vin'] . '/' . $vehicle['code'];
                if (sizeof($vehicles_depot) > sizeof($stations))
                    $newlplink = '&nbsp; <a href="#" style="margin-left: 8px" data-depotid="' . $zsp . '" class="neuelp"><span class="genericon genericon-edit"></span><span class="">Neue Ladepunkte</span></a>';
                else
                    $newlplink = '';

                $thisvehicle['station'] = '<select name="station_select[' . $cnt . ']" class="interserv_lps" >' . $stationslist . '</select>' . $newlplink . '<input type="hidden" name="vehicle_id[' . $cnt . ']" value="' . $vehicle['vehicle_id'] . '" >';
                $cnt++;
                $processedVehicles[] = $thisvehicle;
            }

        }
        $displaytable = new DisplayTable ($processedVehicles);
        $htmlmsg = '<h1>Zugeordnete Fahrzeuge</h1>' . $displaytable->getContent() . '<br><br>
										<input type="hidden" name="zsp" value="' . $zsp . '" >
											<input type="hidden" name="action" value="SaveStationAssignment" >
													<input type="submit" name="save_ladepunkte_new" value="Speichern">';

        if ($ajax) {
            echo $htmlmsg;
            exit(0);
        } else {
            return $htmlmsg;
        }

    }

    /**
     *
     */
    //@todo
    function SaveStationAssignment() {
        $vehicle_ids = $this->requestPtr->getProperty('vehicle_id');
        $stationids = $this->requestPtr->getProperty('station_select');

        foreach ($vehicle_ids as $key => $vehicle) {
            if ($stationids[$key] == 'NULL')
                $this->ladeLeitWartePtr->vehiclesPtr->save(array('station_id'), array(null), array('vehicle_id', '=', $vehicle));
            else
                $this->ladeLeitWartePtr->vehiclesPtr->save(array('station_id'), array($stationids[$key]), array('vehicle_id', '=', $vehicle));


        }
        $this->msgs[] = 'Änderungen gespeichert';
        $this->assign();
        $this->action = "assign";
    }

    /**
     * displays the select depot form for assigning vehicles to stations
     *
     */

    function assign() {
        $zsplid = $this->user->getAssignedZspl();
        $defaultDepot = $this->requestPtr->getProperty('depot');

        if ($zsplid == '') //especially Sts.Fpv
        {
            $depots = $this->ladeLeitWartePtr->depotsPtr->getAll(array('depot_id', 'name', 'dp_depot_id'));
        } else {
            $zsplid = $this->user->getAssignedZspl();
            $depots = $this->ladeLeitWartePtr->depotsPtr->getWhere(array('depot_id', 'name', 'dp_depot_id'), array(array('zspl_id', '=', $zsplid)));
        }
        $listofoptions = array('' => '');
        foreach ($depots as $depot) {
            $listofoptions[$depot['depot_id']] = $depot['name'] . '(' . $depot['dp_depot_id'] . ')';
        }

        $this->qform_zsp = new QuickformHelper($this->displayHeader, 'zsp_selector_fpv');
        $this->qform_zsp->depotSelectFPV($listofoptions, $defaultDepot);
        if (!empty($defaultDepot))
            $this->listVS = $this->listAssignedVehiclesStations(false, $defaultDepot);

    }

    /**
     * Show list of depots for the ZSPL assigned to user
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

