<?php

class FleetController extends PageController {

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        $this->content = '';
        $this->displayHeader = $this->container->getDisplayHeader();

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . 'test');

// 		$this->displayHeader->enqueueJs("sts-custom-utils-combobox","js/sts-custom-utils-combobox.js");
        $this->displayHeader->enqueueJs("sts-custom-fleet", "js/sts-custom-fleet.js");
        $this->action = $this->requestPtr->getProperty('action');

        $db_vehicle_variants = $this->ladeLeitWartePtr->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante');

        $this->vehicle_variants = array_combine(array_column($db_vehicle_variants, 'value_id'), array_column($db_vehicle_variants, 'value'));
    }

    function Execute() {
        if (isset($this->action))
            call_user_func(array($this, $this->action));

        $this->displayHeader->printContent();
        $this->printContent();
    }

    function addvehicle() {
        $this->qform_vehicle = new QuickformHelper($this->displayHeader, 'vehicle_add');
        $this->qform_vehicle->salesGetVehicleAdd_Step1($this->vehicle_variants);
    }

    function werkstatt() {
        $workshop_id = $this->user->getAssignedWorkshop();
        $qry = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->join('depots', 'using (depot_id)')
            ->join('divisions', 'using(division_id)')
            ->where('divisions.production_location', '=', 'f');

        if ($workshop_id > 0)
            $qry = $qry->multipleAndWhere("divisions.name", '=', 'Drittkunden', 'OR', 'depots.workshop_id', '=', $workshop_id);

        $vehicles = $qry->get("vin,code,ikz,vehicle_id", 'vehicle_id');
        $this->processed_vehicles = array();
        foreach ($vehicles as $vehicle_id => $vehicle) {
            $text = $vehicle["vin"];

            if (!empty($vehicle["code"]))
                $text .= " ({$vehicle["code"]})";

            if (!empty($vehicle["ikz"]))
                $text .= " IKZ {$vehicle["ikz"]}";

            $this->processed_vehicles[$vehicle_id] = $text;
        }
    }

    //=============================================================================================================
    function fahrzeugverwaltung() {
        $this->commonVehicleMgmtPtr = new CommonFunctions_VehicleManagement_Sales($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'fahrzeugverwaltung', $this->sopVariants);
        $defaultDepot = $this->commonVehicleMgmtPtr->getDefaultDepot();
        if ($defaultDepot) {
            $this->commonVehicleMgmtPtr->getVehicles($defaultDepot, $this->sopVariants);
        }

    }
    //=============================================================================================================


}