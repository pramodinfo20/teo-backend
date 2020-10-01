<?php

class WorkshopController extends FleetController {

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        /*
         $this->displayHeader->enqueueLocalJs("
         function OnReasonChanged(sender)
         {
         if (sender
         }");
         */
        $this->action = "werkstatt";

        $qry = $this->ladeLeitWartePtr->vehicleVariantsPtr->newQuery()
            ->where('windchill_variant_name', '~', '^[BDE][123][0-9]')
            ->orderBy('windchill_variant_name');

        $this->allVariants = $qry->get('vehicle_variant_id=>windchill_variant_name');
        $this->diagnosePtr = $ladeLeitWartePtr->getDiagnoseObject();
    }

    function home() {
        parent::werkstatt();
    }
}
