<?php

/**
 * FuhrparksteuerController.class.php
 * Controller for User Role Fuhrparksteuerung
 * @author Pradeep Mohan
 */
class FuhrparksteuerController extends ControllerBase
{

    protected $displayHeader;

    protected $msgs;

    protected $div;

    protected $qform;

    // used for editing/saving ZSPL
    protected $qform_zsp;

    protected $listObjects;

    protected $listObjectsTableHeadings;

    protected $listOfDepots;

    protected $action;

    protected $stationsList;

    protected $vehicle_variants;

    protected $vehicle_variants_quantity;

    protected $listLadepunkte;

    protected $listVS;

    protected $zsplname;

    protected $depotname;

    protected $commonVehicleMgmtPtr;

    protected $commonAssignVehiclePtr;

    protected $treestructure;

    /**
     * not all the vehicles in the table have a vehicle_variant_value and thus we set this
     * default variant_value which is the value of the B14 vehicles.
     *
     * @var integer
     */
    protected $defaultVehicleVariant;

    protected $sopVariants;

    protected $pvsVariants;

    protected $kweeks_with_label;


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
        $this->qform = null;
        $this->depots = null;
        $this->zsplname = null;
        $this->vehicle_variants = null;
        $this->vehicle_variants_allowed = null;
        $this->commonfunctions = null;
        $this->displayHeader = $this->container->getDisplayHeader();

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());
        $this->displayHeader->enqueueJs("sts-custom-utils-combobox", "js/sts-custom-utils-combobox.js");
        $this->displayHeader->enqueueJs("sts-fpv", "js/sts-custom-fps.js");

        $this->action = $this->requestPtr->getProperty('action');
        /**
         * division_id only
         *
         * @var FuhrparksteuerController $div
         */
        $this->div = $this->user->getAssignedDiv();
        $this->kweeks_with_label = array();

        // $db_vehicle_variants=$this->ladeLeitWartePtr->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante');

    /**
     * *
     * used in auszulieferende and showLadepunkte functions
     *
     * @var FuhrparksteuerController $vehicle_variants
     */
        // $this->vehicle_variants=array_combine ( array_column($db_vehicle_variants,'value_id'),array_column($db_vehicle_variants,'value'));

        /*
         * $this->sopVariants=$this->pvsVariants=array();
         *
         * foreach($this->vehicle_variants as $vehicle_variant_value=>$vehicle_variant)
         * {
         * if(strpos($vehicle_variant,'B14'))
         * {
         * $this->defaultVehicleVariant=$vehicle_variant_value;
         * $this->pvsVariants[]=$vehicle_variant_value;
         * }
         * else
         * $this->sopVariants[]=$vehicle_variant_value;
         * }
         */
    }


    function Execute()
    {

        if (isset($this->action) && method_exists($this, $this->action))
            call_user_func(array(
                $this,
                $this->action
            ));

        $this->displayHeader->printContent();
        $this->printContent();

    }


    function fahrzeugverwaltung()
    {

        // $this->commonVehicleMgmtPtr=new CommonFunctions_VehicleManagement($this->ladeLeitWartePtr,$this->displayHeader,$this->user,$this->requestPtr);
        $this->commonVehicleMgmtPtr = new CommonFunctions_VehicleManagement($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'fahrzeugverwaltung', $this->sopVariants);
        $defaultDepot = $this->commonVehicleMgmtPtr->getDefaultDepot();
        if ($defaultDepot) {
            /**
             * function to display all vehicles in ZSPL or NL if no ZSPL or ZSP is selected
             * but disabled for now
             * //get vehicles from depots belonging to selected ZSPL or selected Niederlassung
             * if(empty($this->zsp) && ($this->zspl || $this->div) )
             * $this->getVehicles(array_column($depots,'depot_id'));
             * //get vehicles from this depot
             * else
             */
            $this->commonVehicleMgmtPtr->getVehicles($defaultDepot, $this->sopVariants);
        }

    }


    /**
     * Show list of depots for the ZSPL assigned to user
     */
    function showzspls()
    {

        $this->listObjects = $this->ladeLeitWartePtr->zsplPtr->getAllValidInDivision($this->div);
        $this->listObjectsTableHeadings = array(
            array(
                'ZSPL'
            ),
            array(
                'Email Adressen'
            )
        );

    }


    function flottenmonitor()
    {

        $defaultZspl = null;
        // $defaultDepot=$this->requestPtr->getProperty('zsp'); no.. let the common function handle the default depot

        $this->commonOZPtr = new CommonFunctions_ShowOZSelect_Vehicles($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'flottenmonitor');
        $defaultDepot = $this->commonOZPtr->getDefaultDepot();
        $week = $this->commonOZPtr->getSelectedWeek();

        if (! empty($defaultDepot)) {
            switch ($week) {
                default:
                case 'current':
                    $sql = "select
                    date_part('epoch', date_trunc('week', current_timestamp))::int as w_start,
			        date_part('epoch', current_timestamp)::int as w_end";
                    break;

                case 'last':
                    $sql = "select
                    date_part('epoch', date_trunc('week', current_timestamp - interval '1 week'))::int as w_start,
                    date_part('epoch', date_trunc('week', current_timestamp))::int as w_end";
                    break;
            }

            $qry = $this->ladeLeitWartePtr->newQuery();
            if ($qry->query($sql))
                extract($qry->fetchArray());

            $sql = "
select sum (gefahren) as distance_this_week from (
    select vehicle_id, start_km, end_km, end_km-start_km as gefahren from (
        select vehicle_id, min(km_start) as start_km, max(km_end) as end_km from (
            select * from daily_stats
            join vehicles using (vehicle_id)
	        where depot_id=$defaultDepot
            and timestamp_start >= $w_start and timestamp_end < $w_end
            order by timestamp_start desc ) as sub
       group by vehicle_id) as diff
   ) as summe
";

            /*
             * $query="select sum(km_end - km_start) as distance_this_week from (select
             * distinct on (daily_stats.vehicle_id) vehicle_id, km_end
             * from daily_stats
             * where daily_stats.vehicle_id in (select vehicle_id from vehicles where
             * depot_id = $1) and date >=
             * (SELECT current_date - cast(extract(dow from current_date) as int) + 1)
             * order by daily_stats.vehicle_id, timestamp_start desc) as newest,
             * (select distinct on (daily_stats.vehicle_id) vehicle_id, km_start
             * from daily_stats
             * where daily_stats.vehicle_id in (select vehicle_id from vehicles where
             * depot_id = $1) and date >=
             * (SELECT current_date - cast(extract(dow from current_date) as int) + 1)
             * order by daily_stats.vehicle_id, timestamp_start asc) as oldest where
             * oldest.vehicle_id = newest.vehicle_id";
             */

            $qry = $this->ladeLeitWartePtr->newQuery();
            if ($qry->query($sql)) {
                $this->flottenmonitoringcontent[] = $qry->fetchArray(); // $this->ladeLeitWartePtr->specialSqlPrepare($sql, array($defaultDepot));
            }
        }

    }


    // ------------------------------------------------------------------------------------------------------------------------------
    function save_departures_whole()
    {

        if ($_REQUEST['cancel']) {
            $this->action = 'abfahrtszeit';
            $this->requestPtr->unsetProperty('vehicle_id');
            $this->abfahrtszeit();
            return;
        }

        $defaultDepot = $this->requestPtr->getProperty('zsp');
        $defaultZspl = $this->requestPtr->getProperty('zspl');

        if (! empty($defaultDepot)) {
            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesStationsForDepots($defaultDepot);

            foreach ($vehicles as $vehicle)
                $this->save_departures($vehicle['vehicle_id']);
        } else {

            $depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithAssignedVehicles(array(
                'zspl_id' => $defaultZspl
            ));

            foreach ($depots as $depot) {
                $defaultDepot = $depot['depot_id'];
                echo $defaultDepot . ' zsp<br>';
                $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesStationsForDepots($defaultDepot);

                foreach ($vehicles as $vehicle)
                    $this->save_departures($vehicle['vehicle_id']);
            }
        }

    }


    // ------------------------------------------------------------------------------------------------------------------------------
    function save_departures($vehicleid = null)
    {

        if ($_REQUEST['cancel']) {
            $this->action = 'abfahrtszeit';
            $this->requestPtr->unsetProperty('vehicle_id');
            $this->abfahrtszeit();
            return;
        }

        $days = array(
            'mon',
            'tue',
            'wed',
            'thu',
            'fri',
            'sat'
        );
        $updatescols = array();

        if (! isset($vehicleid)) // if it has been passed as an argument to the function
            $vehicleid = $this->requestPtr->getProperty('vehicle_id');
        $depotid = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicleid)
            ->getVal('depot_id');

        // get districts matching vehicle_id and depot_id
        $districts = $this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicleid, $depotid);

        if (! empty($districts))
            $districtid = $districts[0]['district_id'];
        else // all vehicles should have districts, if not then its an error.. add district and send notification email
        {
            // get districts matching just vehicle_id
            $districts = $this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicleid);
            if (! empty($districts)) // this mean the vehicle has a district but the wrong depot_id
            {
                $this->ladeLeitWartePtr->districtsPtr->newQuery()
                    ->where('district_id', '=', $districts[0]['district_id'])
                    ->update(array(
                    'depot_id'
                ), array(
                    $vehicleid
                ));
                $districtid = $districts[0]['district_id'];
            } else {
                // insert new district
                $districtid = $this->ladeLeitWartePtr->districtsPtr->insertNewDistrict($vehicleid, $depotid);
                // inform support
                $mailmsg = "Neuer Bezirk für Fahrzeuge vehicle_id=" . $vehicleid . " hinzugefügt.\r\n" . "District ID :" . $districtid . " \r\n" . "Depot ID :" . $depotid . "\r\n";
                $mailer = new MailerSmimeSwift(array(
                    'support@streetscooter-cloud-system.eu'
                ), '', "Neuer Bezirk hinzugefügt", $mailmsg, null, false, array(
                    'Leon.Schottdorf@streetscooter.eu',
                    'Jens.Frangenheim@streetscooter.eu',
                    'Pradeep.Mohan@streetscooter.eu'
                ));
            }
        }
        // get the existing additional departures from the table
        $second_departures = $this->ladeLeitWartePtr->departuresPtr->getForVehicle($vehicleid);

        foreach ($days as $day) {
            $updatecols[] = 'departure_' . $day;
            // if the time string is empty or if the checkbox has been crossed, then unset departure time
            if (empty($_POST['departure_' . $day]) || ! isset($_POST['active_' . $day])) {
                $updatevals[] = NULL;
            } else
                $updatevals[] = $_POST['departure_' . $day];

            if (isset($second_departures['second_departure_' . $day])) {
                // if the time string is empty or if the checkbox has been crossed, then unset departure time
                if (! isset($_POST['second_departure_' . $day]) || ! isset($_POST['active_' . $day]))
                    $this->ladeLeitWartePtr->departuresPtr->newQuery()
                        ->where('district_id', '=', $districtid)
                        ->where('vehicle_id', '=', $vehicleid)
                        ->where('day', '=', $day)
                        ->delete();
                else
                    $second_updateval = $_POST['second_departure_' . $day];

                // no single column primary key in the departures table and hence we have to find the corresponding row with the huge where statement
                $this->ladeLeitWartePtr->departuresPtr->newQuery()
                    ->where('district_id', '=', $districtid)
                    ->where('vehicle_id', '=', $vehicleid)
                    ->where('day', '=', $day)
                    ->update(array(
                    'time'
                ), array(
                    $second_updateval
                ));
            } else if (isset($_POST['second_departure_' . $day])) {
                if (! empty($_POST['second_departure_' . $day])) {
                    $this->ladeLeitWartePtr->departuresPtr->add(array(
                        'time' => $_POST['second_departure_' . $day],
                        'day' => $day,
                        'soc' => 100,
                        'vehicle_id' => $vehicleid,
                        'district_id' => $districtid
                    ));
                }
            }
        }

        $this->ladeLeitWartePtr->districtsPtr->save($updatecols, $updatevals, array(
            'district_id',
            '=',
            $districtid
        ));

        $this->action = "abfahrtszeit";
        $this->abfahrtszeit();

    }


    // ------------------------------------------------------------------------------------------------------------------------------
    function set_departures_oz()
    {

        $defaultZspl = null;
        $this->commonOZPtr = new CommonFunctions_ShowOZSelect_Vehicles($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'set_departures_oz');
        $defaultDepot = $this->commonOZPtr->getDefaultDepot();
        $defaultZspl = $this->commonOZPtr->getDefaultZspl();

        if (! empty($defaultDepot)) {
            $depotname = $this->ladeLeitWartePtr->depotsPtr->getFromId($defaultDepot);
            $this->depotname = $depotname['name'];
            /*
             * check if this is needed
             *
             * //we have to get previously set departure times for the whole depot..it isn't saved anywhere so just get the details for the first vehicle
             * $vehicleid=$this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('depot_id','=',$defaultDepot)->getVal('vehicle_id');
             * if($vehicleid)
             * {
             * $districts=$this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicleid);
             * $second_departures=$this->ladeLeitWartePtr->departuresPtr->getForVehicle($vehicleid);
             * }
             * else
             * $districts=$second_departures=null;
             */
            $districts = $second_departures = null;
            $this->qform_abfahrtszeit = new QuickformHelper($this->displayHeader, 'abfahrtszeit');
            $this->qform_abfahrtszeit->getAbfahrtszeit(null, $districts, $second_departures, $defaultDepot, 'save_departures_whole');
        } else if (! empty($defaultZspl)) {
            $zsplname = $this->ladeLeitWartePtr->zsplPtr->getFromId($defaultZspl);
            $this->zsplname = $zsplname['name'];
            /*
             * check if this is needed
             *
             * we have to get previously set departure times for the whole zspl..it isn't saved anywhere so just get the details for the first vehicle
             * //from the first depot which has assigned vehicles
             * $depotsWithVehicles=$this->ladeLeitWartePtr->depotsPtr->getDepotsWithAssignedVehicles(array('zspl_id'=>$defaultZspl));
             * if(!empty($depotsWithVehicles))
             * {
             * $vehicleid=$this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('depot_id','=',$depotsWithVehicles[0]['depot_id'])->getVal('vehicle_id');
             * if($vehicleid)
             * {
             * $districts=$this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicleid);
             * $second_departures=$this->ladeLeitWartePtr->departuresPtr->getForVehicle($vehicleid);
             * }
             * else
             * $districts=$second_departures=null;
             * }
             */
            $districts = $second_departures = null;
            $this->qform_abfahrtszeit = new QuickformHelper($this->displayHeader, 'abfahrtszeit');
            $this->qform_abfahrtszeit->getAbfahrtszeit(null, $districts, $second_departures, $defaultDepot, 'save_departures_whole', $defaultZspl);
        }
        $this->action = 'abfahrtszeit';

        // but dont call the abfahrtszeit function.. this is just for the proper display of the navigation menu
    }


    function abfahrtszeit()
    {

        $defaultZspl = null;
        // $defaultDepot=$this->requestPtr->getProperty('zsp'); no.. let the common function handle the default depot
        $this->commonOZPtr = new CommonFunctions_ShowOZSelect_Vehicles($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'abfahrtszeit');
        $defaultDepot = $this->commonOZPtr->getDefaultDepot();
        $vehicleid = $this->requestPtr->getProperty('vehicle_id');

        if (! empty($vehicleid)) {
            $districts = $this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicleid);
            $second_departures = $this->ladeLeitWartePtr->departuresPtr->getForVehicle($vehicleid);
            $this->qform_abfahrtszeit = new QuickformHelper($this->displayHeader, 'abfahrtszeit');
            $this->qform_abfahrtszeit->getAbfahrtszeit($vehicleid, $districts, $second_departures, $defaultDepot, 'save_departures');
        } else if (! empty($defaultDepot)) {
            $this->vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesStationsForDepots($defaultDepot);

            // get the vehicles as well as the departure times to display when a ZSP is selected
            foreach ($this->vehicles as &$vehicle) {
                $districts = $this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicle['vehicle_id']);
                $district = $districts[0];
                $second_departures = $this->ladeLeitWartePtr->departuresPtr->getForVehicle($vehicle['vehicle_id']);
                $days = array(
                    'mon',
                    'tue',
                    'wed',
                    'thu',
                    'fri',
                    'sat',
                    'sun'
                );
                foreach ($days as $day) {
                    if (isset($district['departure_' . $day]))
                        $vehicle[$day] = substr($district['departure_' . $day], 0, 5) . ' Uhr';
                    else
                        $vehicle[$day] = '-';
                    if (isset($second_departures['second_departure_' . $day]))
                        $vehicle[$day] .= '<br>' . substr($second_departures['second_departure_' . $day], 0, 5) . ' Uhr';
                    else
                        $vehicle[$day] .= '<br>-';
                }
            }
        }

    }


    function saveLateCharging()
    {

        if ($_REQUEST['cancel']) {
            $this->action = 'depotprop';
            $this->depotprop();
            return;
        }

        $defaultDepot = $this->requestPtr->getProperty('zsp');
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesStationsForDepots($defaultDepot);

        foreach ($vehicles as $vehicle) {
            $vehicleid = $vehicle['vehicle_id'];

            if (isset($_POST['latecharging_' . $vehicleid])) {
                $this->ladeLeitWartePtr->vehiclesPtr->save(array(
                    'late_charging',
                    'late_charging_time'
                ), array(
                    'TRUE',
                    $_POST['latechargingtime_' . $vehicleid]
                ), array(
                    'vehicle_id',
                    '=',
                    $vehicleid
                ));
            } else {
                $this->ladeLeitWartePtr->vehiclesPtr->save(array(
                    'late_charging',
                    'late_charging_time'
                ), array(
                    'FALSE',
                    NULL
                ), array(
                    'vehicle_id',
                    '=',
                    $vehicleid
                ));
            }
        }
        $this->msgs[] = 'Spätladen Einstellungen gespeichert';
        $this->action = "depotprop";
        $this->depotprop();

    }


    function depotprop()
    {

        $this->commonDepotPropPtr = new CommonFunctions_ShowOZSelect_Vehicles($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'depotprop');
        $defaultDepot = $this->commonDepotPropPtr->getDefaultDepot();

        if (! empty($defaultDepot)) {
            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesStationsForDepots($defaultDepot);

            $this->qform_depotprop = new QuickformHelper($this->displayHeader, 'zsp_props');
            $this->qform_depotprop->getVehicleLateCharging($vehicles, $defaultDepot);
        }

    }


    function save_fahrzeug_zuruck()
    {

        $delivery_week = $_POST['return_week'];
        $variant_value = $_POST['variant_value'];
        $return_quantity = $_POST['return_quantity'];
        $this->action = 'auszulieferende';

        $delivery = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getForDivisionYearWeekVariant($this->div, date('Y'), $delivery_week, $variant_value);
        if (! empty($delivery)) {
            $delivery['delivery_quantity'] -= $return_quantity;
            if ($delivery['delivery_quantity'] >= 0 && ($delivery['delivery_quantity'] - $delivery['vehicles_delivered_quantity']) >= 0) {
                $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
                    ->where('delivery_id', '=', $delivery['delivery_id'])
                    ->update(array(
                    'delivery_quantity'
                ), array(
                    $delivery['delivery_quantity']
                ));
                $divname = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
                    ->where('division_id', '=', $this->div)
                    ->getVal('name');
                $mailmsg = "Fahrzeug zurückgegeben Niederlassung " . $divname . "\r\n";
                $mailmsg .= "delivery_to_divisions table delivery_id=" . $delivery['delivery_id'] . " wird im " . $delivery['delivery_week'] . " " . $return_quantity . " weniger Fahrzeuge bekommen\r\n";

                $mailer = new MailerSmimeSwift('Pradeep.Mohan@streetscooter.eu', '', "Fahrzeug zurückgegeben Niederlassung " . $divname . "\r\n", $mailmsg, null, false, array(
                    'Pradeep.Mohan@streetscooter.eu',
                    'Jens.Frangenheim@streetscooter.eu'
                ));
            } else {
                $this->msgs[] = 'Fehler beim speichern. Bitte überprüfen Sie die Anzahl der Fahrzeuge.';
            }
        }
        $this->auszulieferende();

    }


    function auszulieferende()
    {

        $defaultZspl = null;
        $kweeks = array();
        foreach ($this->vehicle_variants as $vehicle_variant => $vehicle_variant_label) {
            $result = $this->ladeLeitWartePtr->newQuery('delivery_to_divisions')
                ->where('variant_value', '=', $vehicle_variant)
                ->where('delivery_year', '=', date('Y'))
                ->groupBy('added')
                ->orderBy('added', 'DESC')
                ->getOne("to_char(added_timestamp, 'YY-MM-DD') as added,json_agg(distinct delivery_week) as weeks");

            $calendar_weeks = json_decode($result['weeks'], true);
            if (is_array($calendar_weeks)) {
                $kweeks = array_merge($kweeks, $calendar_weeks);
                $result = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getForWeeksAndDivVariant($calendar_weeks, $this->div, $vehicle_variant);
                if (! empty($result))
                    $this->vehicle_variants_quantity[] = $result;
            }
        }
        $kweeks = array_unique($kweeks);
        /*
         * new method
         * $kweeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01'),true);
         * //start quick fix for D16 delivery in April 2017
         * //start quick fix for the deliveries in July August 2017
         * $thistime=strtotime("first day of -1 months");
         * $prevmonth_weeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01',$thistime),true);
         * $kweeks=array_merge($prevmonth_weeks,$kweeks);
         *
         * $lastweek=$kweeks[sizeof($kweeks)-1];
         * $lastweeknum=str_replace('kw','',$lastweek);
         *
         * $moreweeks=$this->ladeLeitWartePtr->deliveryToDivisionsPtr->getWeeksForFPS(date('Y'),$lastweeknum,$this->div);
         * if(!empty($moreweeks)) $kweeks=array_merge($kweeks,$moreweeks);
         */
        // endquick fix for the deliveries in July August 2017
        // end quick fix for D16 delivery in April 2017

        // start quick fix for B16 delivery in March 2017
        /*
         * $thistime=strtotime("first day of +1 months");
         * $nextmonth_weeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01',$thistime),true);
         * $kweeks=array_merge($kweeks,$nextmonth_weeks);
         */
        // end quick fix for B16 delivery in March 2017
        // continue here,but commit the sales function first

        // get the stations for this div where vehicle variant allowed is not NULL and station is not already assigned to a vehicle
        $this->vehicle_variants_assigned = $this->ladeLeitWartePtr->stationsPtr->getCntVehicleVariantsDivAssigned($this->div);
        $this->vehicles_delivered = array();

        foreach ($kweeks as &$kweek) {
            $this->kweeks_with_label[$kweek] = 'KW ' . str_replace('kw', '', $kweek);
        }

        foreach ($this->vehicle_variants_quantity as $vehicle_variant) {
            $kweek_quantities = json_decode($vehicle_variant['byweek'], true);
            $kweek_quantities_delivered = json_decode($vehicle_variant['byweek_delivered'], true);
            // $kweeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01'),true,'KW ','kw');
            /*
             * disabling fahrzeug zurück function
             * $this->qform_zuruck[$vehicle_variant['variant_value']]=new QuickformHelper($this->displayHeader, 'zuruck');
             * $this->qform_zuruck[$vehicle_variant['variant_value']]->fahrzeug_zuruck($kweeks,$kweek_quantities,$kweek_quantities_delivered,$vehicle_variant['variant_value']);
             */
        }

        $this->vehicle_variants_assigned_data = $this->ladeLeitWartePtr->stationsPtr->getAllFreeStationsWithVariantAssignedForDiv($this->div);
        foreach ($this->vehicle_variants_assigned_data as &$station) {
            unset($station['depot_id']);
            unset($station['vehicle_variant_update_ts']);
            $station['vehicle_variant_value_allowed'] = $this->vehicle_variants[$station['vehicle_variant_value_allowed']];
        }
        // $defaultDepot=$this->requestPtr->getProperty('zsp'); no.. let the common function handle the default depot
        $this->commonAssignVehiclePtr = new CommonFunctions_ShowOZSelect_FreeStations($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'auszulieferende');
        $defaultDepot = $this->commonAssignVehiclePtr->getDefaultDepot();
        if (! empty($defaultDepot)) {
            $this->listLadepunkte = $this->showLadepunkte(false, $defaultDepot);

            $depotname = $this->ladeLeitWartePtr->depotsPtr->getFromId($defaultDepot);
            $this->depotname = $depotname['name'];
            $this->treestructure = $this->ladeLeitWartePtr->restrictionsPtr->generateTreeStructureForDepot($defaultDepot);
        }

    }


    function change_stations_prio()
    {

        $stations_prio = $this->requestPtr->getProperty('stations_priority');
        $stations_prio_original = $this->requestPtr->getProperty('stations_priority_original');
        $this->vehicle_variants_assigned_data = $this->ladeLeitWartePtr->stationsPtr->getAllFreeStationsWithVariantAssignedForDiv($this->div);
        $laststation = $this->vehicle_variants_assigned_data[sizeof($this->vehicle_variants_assigned_data) - 1];
        $latest_time = strtotime($laststation['vehicle_variant_update_ts']);
        if (time() > $latest_time)
            $latest_time = time();
        if (! empty($stations_prio) && $stations_prio_original != $stations_prio) {
            $stations_prio = explode(',', $stations_prio);
            foreach ($stations_prio as $station_id) {
                $latest_time_str = date('Y-m-d H:i:sO', $latest_time);
                $this->ladeLeitWartePtr->stationsPtr->newQuery()
                    ->where('station_id', '=', $station_id)
                    ->update(array(
                    'vehicle_variant_update_ts'
                ), array(
                    $latest_time_str
                ));
                $latest_time ++;
            }
            $this->msgs[] = "Priorität geändert!";
        }

        $this->action = 'auszulieferende';
        $this->auszulieferende();

    }


    /**
     * showLadepunkte
     * if default ZSP is given in the url then the output is return to the calling function, if not then it is echoed and function exits
     *
     * @param boolean $ajax
     * @param boolean $zsp
     * @return string
     */
    function showLadepunkte($ajax = null, $zsp = null)
    {

        if (! isset($ajax)) {
            $ajax = $this->requestPtr->getProperty('ajax');
            $zsp = $this->requestPtr->getProperty('zsp');
        }

        $vehicles_depot = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesStationsForDepots($zsp);
        $processedVehicles[] = array(
            'headingone' => array(
                'ZSP',
                'Fahrzeug VIN / Kennzeichen',
                '1/3 phasige Fahrzeug',
                'Fahrzeugtyp',
                'Ladepunkte'
            )
        );

        if (! empty($vehicles_depot)) {
            foreach ($vehicles_depot as $vehicle) {
                if (! empty($vehicle['restriction_id2']) && ! empty($vehicle['restriction_id3']))
                    $stationame = '<span class="threephase_station">' . $vehicle['sname'] . '</span>';
                else
                    $stationame = '<span class="singlephase_station">' . $vehicle['sname'] . '</span>';
                $classname = 'singlephase_vehicle';
                $vehicle_phase = '1-phasig';
                $vehicletype = "WORK B14 1-Sitzer Verbund"; // @todo
                if (strpos($vehicle['vin'], 'B16') !== false) {
                    // $classname='threephase_vehicle';
                    // $vehicle_phase='3-phasig';
                    $vehicletype = "WORK B16 1-Sitzer Verbund";
                } else if (strpos($vehicle['vin'], 'D16') !== false) {
                    $vehicletype = "WORK L D16 1-Sitzer Verbund";
                }

                $vname = '<span class=' . $classname . '>' . $vehicle['vin'] . '/' . $vehicle['code'] . '</span>';

                $processedVehicles[] = array(
                    $vehicle['dname'],
                    $vname,
                    $vehicle_phase,
                    $vehicletype,
                    $stationame
                );
            }
            $displaytable = new DisplayTable($processedVehicles);
            $already_assigned_vehicles = '<h1>Zugeordnete Fahrzeuge</h1>' . $displaytable->getContent() . '<br><br>';
        } else
            $already_assigned_vehicles = 'Keine Fahrzeuge/Ladepunkte Zuordnung diesem ZSP!' . '<br><br>';

        $this->stationsList = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsForDepot($zsp);

        $qform = new QuickformHelper($this->displayHeader, 'fps_stations');

        $zsp = $this->requestPtr->getProperty('zsp');

        $quantities = array_combine(array_column($this->vehicle_variants_quantity, 'variant_value'), array_column($this->vehicle_variants_quantity, 'delivery_quantity'));

        foreach ($this->vehicle_variants as $variant_value => $variant_label) {
            if (isset($quantities[$variant_value]))
                $quantity = $quantities[$variant_value];
            else
                $quantity = 0;

            // @todo 2017-09-25 temporary fix to use the D16 as pvs since the charger is controllable. Need a more refined method to do this.
            $this->chargerControllable = array(
                3
            );

            if (in_array($variant_value, $this->chargerControllable))
                $vehicle_type = 'pvs';
            else if (in_array($variant_value, $this->sopVariants))
                $vehicle_type = 'sop';
            else
                $vehicle_type = 'pvs';
            $processed_vehicle_variants[$variant_value] = array(
                'variant_label' => $variant_label,
                'vehicle_type' => $vehicle_type,
                'quantity' => $quantity
            );
        }

        foreach ($this->stationsList as &$station) {
            $transporter_date_cnt = $this->ladeLeitWartePtr->newQuery('transporter_dates')
                ->where('station_id', '=', $station['station_id'])
                ->getVal('count(station_id)');
            $station['transporter_date'] = $transporter_date_cnt;
        }
        $qform->getDepotStationsVehicleVariants($zsp, $this->stationsList, $processed_vehicle_variants, array_merge($this->pvsVariants, $this->sopVariants));
        $to_be_assigned_vehicles = $qform->getContent();

        if ($ajax) {
            echo $to_be_assigned_vehicles . $already_assigned_vehicles . '<span id="test"></span>';
            exit(0);
        } else {
            return $to_be_assigned_vehicles . $already_assigned_vehicles . '<span id="test"></span>';
        }

    }


    /**
     * called using ajax when assigned vehicle_variant to a station
     *
     * @param
     *            integer (restriction) $restriction_id
     * @param integer $sopcnt
     * @param integer $pvscnt
     */
    function checkPossibleCombos($restriction_id = null, $sopcnt = null, $pvscnt = null)
    {

        if (! isset($restriction_id))
            $restriction_id = $this->requestPtr->getProperty('restriction_id');
        if (! isset($sopcnt))
            $sopcnt = $this->requestPtr->getProperty('sopcnt');
        if (! isset($pvscnt))
            $pvscnt = $this->requestPtr->getProperty('pvscnt');

        $vehicles_assigned_cnt = $this->ladeLeitWartePtr->vehiclesPtr->newGetAssignedVehiclesByVariantCntForRestriction($restriction_id, $this->sopVariants, $this->pvsVariants);

        $pvscnt += $vehicles_assigned_cnt['pvs'];
        $sopcnt += $vehicles_assigned_cnt['sop'];

        $restriction = $this->ladeLeitWartePtr->restrictionsPtr->getFromId($restriction_id);
        $imax = $restriction['power'] / (215.0);
        $n = floor($imax / 8);
        $i = 0;
        $nPVS = 0;

        $possibleCombos = array();
        $flag = 0;
        for ($nSOP = $n; $nSOP > - 1; $nSOP --) {
            $i12 = ceil($nSOP / 2) * 16 + ($n - $nSOP) * 7;
            $temp = $n - $nSOP;

            if ($i12 <= $imax) {

                if ($pvscnt <= $nPVS && $sopcnt <= $nSOP) {
                    $flag = 1;
                    echo '<br>' . $pvscnt . ' ' . $nPVS . ' sop ' . $sopcnt . ' ' . $nSOP . ' ' . $imax . '<br>';
                }
            }
            $nPVS ++;
        }

        exit(0);

    }


    /**
     * function not used anymore
     */
    // function genPossibleCombos($zsp=null)
    // {

    // if(!isset($zsp)) $zsp=$this->requestPtr->getProperty('zsp');
    // if(!isset($depot))$depot=$this->ladeLeitWartePtr->depotsPtr->getFromId($zsp);

    // $restriction=$this->ladeLeitWartePtr->restrictionsPtr->getFromId($depot['depot_restriction_id']);

    // $imax=$restriction['power']/ (215.0);

    // $n=floor($imax/8);
    // $i=0;
    // $nPVS=0;

    // $possibleCombos=array();

    // for($nSOP=$n; $nSOP>-1; $nSOP--)
    // {
    // $i12=ceil($nSOP/2)*16+ ($n-$nSOP)*7;
    // $temp=$n-$nSOP;

    // if($i12<=$imax) {
    // $possibleCombos[]=array('pvs'=>$nPVS,'sop'=>$nSOP);
    // }
    // // echo $nPVS.' PVS und '.$nSOP.' SOP möglich <br>';
    // // else echo $nPVS.' PVS und '.$nSOP.' SOP nicht möglich <br>';
    // $nPVS++;
    // }
    // echo json_encode($possibleCombos);
    // exit(0);
    // }
    function getZspEmails()
    {

        $zsp = $zsp = $this->requestPtr->getProperty('zsp');
        $thisdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($zsp);
        if ($thisdepot)
            $depot_emails = unserialize($thisdepot['emails']);
        if (is_array($depot_emails))
            $depot_emails = implode("\r\n", $depot_emails);
        echo $depot_emails;
        exit(0);

    }


    function saveLadepunkte()
    {

        $updateStatus = TRUE;
        $zsp = $this->requestPtr->getProperty('zsp');
        foreach ($_POST as $station => $vtype) {

            if (strpos($station, "vtype") == 0 && strpos($station, "vtype") !== false) {
                $params = explode('_', $station);
                $station_id = $params[1];

                if ($vtype == 'null')
                    $vtype = NULL;

                $vehicle_variant_value_allowed = $this->ladeLeitWartePtr->stationsPtr->getVariantforStation($station_id);
                // update only if something has to be changed
                if ($vehicle_variant_value_allowed != $vtype) {
                    $updateStatus = $this->ladeLeitWartePtr->stationsPtr->newQuery()
                        ->where('station_id', '=', $station_id)
                        ->update(array(
                        'vehicle_variant_value_allowed',
                        'vehicle_variant_update_ts'
                    ), array(
                        $vtype,
                        date('Y-m-d H:i:sO')
                    ));
                }
                // $updateStatus=$this->ladeLeitWartePtr->stationsPtr->save(array('vehicle_variant_value_allowed'),array($vtype),array('station_id','=',$station_id));
            }
        }

        if ($updateStatus === FALSE)
            $this->msgs[] = "Zuordnung war nicht gespeichert! Bitte schreiben Sie an support@streetscooter-cloud-system.eu";
        else {
            $this->msgs[] = "Zuordnung erfolgreich gespeichert!";
            $div_variant_count = $this->ladeLeitWartePtr->stationsPtr->getStationsCountWithVariantAssignedForDivision($this->div);

            foreach ($div_variant_count as $variant) {
                $divisionDeliveryPlan = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getOnePendingForDivisionYearVariant($this->div, date('Y'), $variant['vehicle_variant_value_allowed']);
                $division = $this->ladeLeitWartePtr->divisionsPtr->getFromId($this->div);
                $variant_name = $this->vehicle_variants[$variant['vehicle_variant_value_allowed']];
                if ($variant['scnt'] >= $divisionDeliveryPlan['delivery_quantity']) {
                    $depots_div = $this->ladeLeitWartePtr->depotsPtr->getAllInDiv($this->div);
                    $depot_count_str = '';
                    foreach ($depots_div as $thisdepot) {
                        $depot_cnt = $this->ladeLeitWartePtr->stationsPtr->getStationsCountWithVariantAssignedForDepotVariant($thisdepot['depot_id'], $variant['vehicle_variant_value_allowed']);
                        if ($depot_cnt['scnt'] > 0) {
                            $depot_count_str .= "<strong>" . $depot_cnt['depname'] . " (" . $depot_cnt['dp_depot_id'] . ")</strong>\r\n" . $variant_name . " : " . $depot_cnt['scnt'] . "\r\n\r\n";
                        }
                    }
                    $mailmsg = "Sehr geehrte Damen und Herren, \r\n\r\n" . "Auszulieferende Fahrzeuge an Ladepunkte zuordnen ist jetzt fertig.\r\n\r\n" . $depot_count_str . "Auslieferung der Fahrzeuge kann jetzt geplant werden." . "\r\n\r\nMfG, StreetScooter Cloud System";
                    $domain = null;
                    if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
                        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
                    }

                    if ($domain == "streetscooter-cloud-system.eu") {
                        $mailer = new MailerSmimeSwift('Philipp.Schnelle@streetscooter.eu', '', 'Auszulieferende Fahrzeuge (' . $variant_name . ') an Ladepunkte zuordnen fertig - Niederlassung - ' . $division['name'], $mailmsg, null, true, array(
                            'Pradeep.Mohan@streetscooter.eu',
                            'Jens.Frangenheim@streetscooter.eu'
                        ));
                    } else
                        $mailer = new MailerSmimeSwift('Pradeep.Mohan@streetscooter.eu', '', 'TEST LOKAL Auszulieferende Fahrzeuge (' . $variant_name . ') an Ladepunkte zuordnen fertig - Niederlassung - ' . $division['name'], $mailmsg, null, true, array(
                            'Pradeep.Mohan@streetscooter.eu'
                        ));
                }
            }
            // $mailmsg="Hallo Sales, \n\n".
            // "Niederlassung Zurodnung fertig. Jetzt kann die Fahrzeuge genau mit Kennzeichen an ZSP zugerodnet werden. \n".
            // "<a href=\"https://".$_SERVER['HTTP_HOST']."/index.php?action=pvssopcode&zsp=".$zsp."\">Klick</a>\n\nMfG, StreetScooter Cloud System";
            // $mailer=new Mailer('Pradeep.Mohan@streetscooter.eu','Pradeep Mohan','Fahrzeug Kombination',$mailmsg);
        }
        $this->action = 'auszulieferende';
        $this->auszulieferende();

    }


    /**
     * saving email addresses for an existing ZSPL
     */
    function save_exist_zspl()
    {

        $this->qform = new QuickformHelper($this->displayHeader, "zspl_add_edit_form");

        $zsplemails = $this->requestPtr->getProperty('zsplemails');
        $depotemails = $this->requestPtr->getProperty('depotemails');

        if ($zsplemails)
            $zsplemails = explode("\r\n", trim($zsplemails));
        if (! empty($zsplemails))
            $zsplemails = serialize($zsplemails);
        if ($depotemails)
            $depotemails = explode("\r\n", trim($depotemails));
        if (! empty($depotemails))
            $depotemails = serialize($depotemails);

        $zsplname = $this->requestPtr->getProperty('zsplname');
        $zspl_id = $this->requestPtr->getProperty('zspl');
        $listdepots = $this->requestPtr->getProperty('listdepots');
        $div = (int) $this->requestPtr->getProperty('division_id');

        $currentzspl["zspl_id"] = $zspl_id;
        $currentzspl["division_id"] = $div;
        $currentzspl["emails"] = $zsplemails;
        $currentzspl["name"] = $zsplname;

        $listofzsps = $this->ladeLeitWartePtr->depotsPtr->getWhere('', array(
            array(
                'zspl_id',
                '=',
                $zspl_id
            )
        ));

        $division = $this->ladeLeitWartePtr->divisionsPtr->getFromId($div);

        $this->qform->zspl_add_edit_form($division, true, $currentzspl, $listofzsps);
        if (! $this->qform->formValidate()) {
            $zspl_errors = ""; // @todo What Fehler? Error with submitted data.
            $this->edit_zspl();
        } else {
            if (! $this->user->user_can('addzsplemails')) {
                $this->msgs[] = "Benutzer darf nicht diese ZSP verwalten.";
            } else {
                $this->ladeLeitWartePtr->zsplPtr->save(array(
                    "emails",
                    "name"
                ), array(
                    $zsplemails,
                    $zsplname
                ), array(
                    'zspl_id',
                    '=',
                    $zspl_id
                ));
                $this->ladeLeitWartePtr->depotsPtr->save(array(
                    "emails"
                ), array(
                    $depotemails
                ), array(
                    'depot_id',
                    '=',
                    $listdepots
                ));
                $this->msgs[] = "ZSPL gespeichert!";
            }

            unset($this->qform);
        }
        $this->edit_zspl();
        $this->action = 'edit_zspl';

    }


    function edit_zspl()
    {

        $this->showZspls();
        $zspl_id = (int) $this->requestPtr->getProperty('zspl');

        $editThisZspl = $this->ladeLeitWartePtr->zsplPtr->getFromId($zspl_id);
        $this->zsplname = $editThisZspl['name'];
        $listOfDepots = $this->ladeLeitWartePtr->depotsPtr->getWhere('', array(
            array(
                'zspl_id',
                '=',
                $zspl_id
            )
        ));

        $currentDiv = $this->ladeLeitWartePtr->divisionsPtr->getFromId($this->div);

        if (! isset($this->qform)) // this is to ensure form data is saved when validation is done on the SERVER side
        {
            $this->qform = new QuickformHelper($this->displayHeader, "zspl_add_edit_form");
            $this->qform->zspl_add_edit_form($currentDiv, true, $editThisZspl, $listOfDepots);
        }

    }


    function printContent()
    {

        include ("pages/" . $this->user->getUserRole() . ".php");

    }

}

