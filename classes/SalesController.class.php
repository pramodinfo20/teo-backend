<?php
require_once $_SERVER['STS_ROOT'] . '/includes/sts-array-tools.php';

class SalesController extends PageController
{

    protected $msgs;

    protected $vehicles;

    protected $vehiclesHeadings;

    protected $qform_vehicle;

    protected $qform_vehicles;

    protected $qform_csv;

    protected $qform_pdf;

    protected $qform_zsp;

    protected $qform_combos;

    protected $newVehiclesList;

    protected $action;

    protected $exportCSVOptions;

    protected $listCombos;

    protected $depots;

    protected $csvTemplates;

    protected $listofoptions;

    protected $variantsAllowed;

    protected $depotassignresult;

    protected $vehiclesPost;

    protected $vehicle_variants;

    protected $lieferscheinFname;

    protected $cocLink;

    protected $pentaCSVLink;

    /**
     * valid time to show the 6) Fahrzeug zuweisen button
     */
    protected $start_time;

    protected $end_time;

    protected $finished_vehicles;

    protected $vehicles_for_delivery;

    protected $person_designation;

    protected $pdfLink;

    protected $quickform_thirdparties;

    protected $qform_save_transporter_date;

    protected $html_content = null;


    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user)
    {

        ini_set('max_execution_time', '300');
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->qform_vehicle = null;
        $this->qform_vehicles = null;
        $this->newVehiclesList = null;
        $this->variantsAllowed = null;
        $this->action = null;
        $this->displayHeader = $this->container->getDisplayHeader();
        $this->qform_zsp = null;
        $this->depots = null;
        $this->listCombos = null;
        $this->vehiclesPost = null;
        $this->qform_dritt = null;
        $this->qform_pdf = null;
        $this->qform_xml = null;
        $this->qform_delplan = null;
        $this->vehicle_variants = null;
        $this->lieferscheinFname = null;
        $this->cocLink = null;
        $this->pentaCSVLink = null;
        $this->finished_vehicles = null;
        $this->start_time = strtotime(date('Y-m-d 10:00:00'));
        $this->end_time = strtotime(date('Y-m-d 17:00:00'));
        $this->pdfLink = null;
        $this->content = '';
        $this->transporter_manage = '';

        // used in the ajaxRowsDelivery function
        $count_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->ajaxGetVehiclesToDeliver(null, null, null, null, null);
        $this->totalDeliveryRows = sizeof($count_vehicles);

        $this->person_designation = &$GLOBALS['person_designation'];

        $this->quickform_thirdparties = null;

        $this->displayHeader->enqueueStylesheet('tablesorter-default', "css/theme.default.css");
        $this->displayHeader->enqueueJs("jquery-tablesorter", "js/jquery.tablesorter.min.js");
        $this->displayHeader->enqueueJs("jquery-tablesorter-pager", "js/jquery.tablesorter.pager.js");
        $this->displayHeader->enqueueJs("jquery-tablesorter-widgets", "js/jquery.tablesorter.widgets.js");
        $this->displayHeader->enqueueJs("jquery-datepicker", "js/jquery.ui.datepicker-de.js");
        $this->displayHeader->enqueueJs("jquery-timepicker", "js/jquery-ui-timepicker-addon.js");

        $this->depots = $this->ladeLeitWartePtr->depotsPtr->getAll();
        $this->displayHeader->enqueueJs("sts-custom-sales", "js/sts-custom-sales.js");
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());

        $db_vehicle_variants = $this->ladeLeitWartePtr->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante');
        $this->vehicle_variants = array_combine(array_column($db_vehicle_variants, 'value_id'), array_column($db_vehicle_variants, 'value'));

        $this->sopVariants = $this->pvsVariants = array();

        foreach ($this->vehicle_variants as $vehicle_variant_value => $vehicle_variant) {
            if (strpos($vehicle_variant, 'B14')) {
                $this->defaultVehicleVariant = $vehicle_variant_value;
                $this->pvsVariants[] = $vehicle_variant_value;
            } else
                $this->sopVariants[] = $vehicle_variant_value;
        }

        $this->action = $this->requestPtr->getProperty('action');

        // @todo 2017-09-08 to get this from the database
        $this->vin_methods = array(
            "sop2017" => "SOP 2017 Verfahren",
            "sop" => "SOP Verfahren",
            "pvs" => "PVS Verfahren",
            "pvsold" => "1000 Verfahren"
        );

        $this->emailtext = "
					Sehr geehrte Damen und Herren ,\r\n
						\r\n
						voraussichtlich zu unten genannten Zeitpunkten wird Ihre Niederlassung mit der folgenden
				Anzahl an StreetScootern beliefert werden: \r\n
						\r\n
						{deliveryStr}\r\n
						\r\n
						Bitte weisen Sie im Webinterface des StreetScooter Cloud-Systems unter
						<a href='https://streetscooter-cloud-system.eu/index.php?action=auszulieferende'>https://streetscooter-cloud-system.eu/index.php?action=auszulieferende</a>
						möglichst innerhalb einer Woche diesen Fahrzeugen einen ZSP und eine
						Ladepunkte zu. \r\n
						\r\n
						Sofern derzeit in der Cloud noch nicht genug freie Ladesäulen für die Zuordnung vorhanden sind,
						sie aber in Kürze mit der Fertigstellung von Ladeinfrastruktur an weiteren Standorten rechnen (weil bspw. die Bauarbeiten derzeit im Gange sind),
						geben Sie die Fahrzeuge bitte nicht in der Cloud zurück,
						sondern warten auf die Fertigstellung der Installationsarbeiten und weisen die Fahrzeuge anschließend zur Auslieferung zu.\r\n
						\r\n
						Bitte nehmen Sie die Eintragung innerhalb von 5 Arbeitstagen vor, weil sonst leider andere Niederlasungen vorgezogen werden müssen.
						\r\n
						\r\n
						Die Reihenfolge Ihrer Zuweisung bestimmt die zeitliche Abfolge der Auslieferung.\r\n\r\n\r\n
						Mit freundlichen Grüßen,\r\n
						\r\n
						Ihr StreetScooter Cloud-System Team\r\n
						\r\n
						\r\n
						\r\n
						Diese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich. \r\n
						Bitte wenden Sie sich bei Fragen an support@streetscooter-cloud-system.eu \r\n";

        $this->salestext = "
					Sehr geehrte Damen und Herren ,\r\n
						\r\n
						Auslieferungsplan diesen Monat ist jetzt im Webinterface des StreetScooter Cloud-Systems verfügbar.\r\n
						\r\n

						<a href='https://streetscooter-cloud-system.eu/index.php?action=showDivisionsDeliveryPlan'>Auslieferungsplan</a>
						\r\n\r\n
						Mit freundlichen Grüßen,\r\n
						\r\n
						Ihr StreetScooter Cloud-System Team\r\n
						\r\n
						\r\n
						\r\n
						Diese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich. \r\n
						Bitte wenden Sie sich bei Fragen an support@streetscooter-cloud-system.eu \r\n";

        $this->emailDeliveryWithDate = 'Sehr geehrte Damen und Herren, <br><br>
										wie bereits angekündigt erhalten Sie die folgenden Fahrzeuge <br><br>{deliveryStr}
										<br>Die Fahrzeuge werden voraussichtlich zwischen 8:00 – 17:00 Uhr am oben genannten Datum ausgeliefert.
                                        Bitte senden Sie uns einen Ansprechpartner und die Kontaktdaten, damit der Spediteur sich rechtzeitig vor Ankunft bei Ihnen melden kann.
										<br><br>Mit freundlichen Grüßen,
										<br>Ihr StreetScooter Cloud-System Team';
        if (isset($this->action))
            call_user_func(array(
                $this,
                $this->action
            ));
        else {
            $this->action = 'home';
            $this->overview();
        }

        $this->displayHeader->printContent();

        $this->printContent();

    }


    // =============================================================================================================
    function home()
    {

    }


    // =============================================================================================================
    function GetVinMethodProperties($vinMethod)
    {

        $result = [
            'pvsold' => false,
            'pvs' => false,
            'sop' => false,
            'sop2017' => false,
            'ext_import' => false
        ];

        if (! empty($vinMethod)) {
            $result[$vinMethod] = true;
        }
        return $result;

    }


    // =============================================================================================================
    function show_finished_vehicles()
    {

        $heading = array(
            array(
                'headingone' => array(
                    'VIN',
                    'AKZ',
                    'Datenbank ID'
                )
            )
        );

        $pool_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariants(null, 'pool', 'all');

        if (empty($pool_vehicles))
            $pool_vehicles = array();

        $result = array_merge($heading, $pool_vehicles);

        $table = new DisplayTable($result);
        $this->finished_vehicles['pool'] = $table->getContent();

        $production_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariants(null, 'production', 'all');

        if (empty($production_vehicles))
            $production_vehicles = array();

        $result = array_merge($heading, $production_vehicles);
        $table = new DisplayTable($result);

        $this->finished_vehicles['production'] = $table->getContent();

    }


    // =============================================================================================================
    function fahrzeugverwaltung()
    {

        $this->commonVehicleMgmtPtr = new CommonFunctions_VehicleManagement_Sales($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr, 'fahrzeugverwaltung', $this->sopVariants);
        $defaultDepot = $this->commonVehicleMgmtPtr->getDefaultDepot();
        if ($defaultDepot) {
            $this->commonVehicleMgmtPtr->getVehicles($defaultDepot, $this->sopVariants);
        }

    }


    // =============================================================================================================
    function depotShow()
    {

        $this->depotShowContent = $this->ladeLeitWartePtr->stationsPtr->getAllDivisionsFreeStationsWithVariantAssigned();

    }


    // =============================================================================================================
    function manuell_auslieferung()
    {

        $this->manual_delivery_content = 'Zurzeit nicht verfügbar (wegen mögliche Fehlfunktion)';

    }


    // =============================================================================================================
    function assignStationsVehicles($variant_value, $count_vehicles, $divisionsDeliveryPlan, $possibleCombos)
    {

        $debug = 1;
        $showveh = 1;

        $assigned_vehicles = array();
        $this->count_vehicles_assigned = 0;
        $mailmsg = '';
        $lastzsp = $zsp = null;

        // get vehicle available count (by variant) and compare
        foreach ($possibleCombos as $combosKey => &$possibleCombo) {
            $station = $possibleCombo['station'];
            $zsp = $station['depot_id'];

            if (isset($lastzsp) && $lastzsp != $zsp) {
                $this->debugcontent = $this->ladeLeitWartePtr->restrictionsPtr->generateTreeStructureForDepot($lastzsp);

                $extraemails = array(
                    'Ismail.Sbika@streetscooter.eu',
                    'Lothar.Juergens@streetscooter.eu',
                    'Leon.Schottdorf@streetscooter.eu'
                );
                $mailer = new MailerSmimeSwift('Pradeep.Mohan@streetscooter.eu', '', 'StreetScooter Fahrzeuge Auslieferung Log ' . date('Y-m-j H:m:s'), $mailmsg . $this->debugcontent, null, true, $extraemails);
                $this->content .= $mailmsg;
                $mailmsg = '';
            }

            $lastzsp = $zsp;

            $vehicle = $possibleCombo['vehicle'];

            $vehicle_id = $vehicle['vehicle_id'];

            $this->ladeLeitWartePtr->vehiclesPtr->assignVehicleToStation($vehicle_id, $station['station_id'], $station['depot_id'], $divisionsDeliveryPlan['delivery_week'], $station['cost_center']);
            $assigned_vehicles[] = array(
                'vehicle_id' => $vehicle_id,
                'station_id' => $station['station_id']
            );
            // reset the finished status back to FALSE after it has been assigned for delivery
            $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where('vehicle_id', '=', $vehicle_id)
                ->update(array(
                'finished_status'
            ), array(
                'FALSE'
            ));
            $divisionsDeliveryPlan['vehicles_delivered'] = unserialize($divisionsDeliveryPlan['vehicles_delivered']);
            $divisionsDeliveryPlan['vehicles_delivered'][] = $vehicle_id;
            $divisionsDeliveryPlan['vehicles_delivered'] = serialize($divisionsDeliveryPlan['vehicles_delivered']);
            $divisionsDeliveryPlan['vehicles_delivered_quantity'] ++;
            $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
                ->where('delivery_id', '=', $divisionsDeliveryPlan['delivery_id'])
                ->update(array_keys($divisionsDeliveryPlan), array_values($divisionsDeliveryPlan));
            $this->count_vehicles_assigned ++;

            $mailmsg .= $station['sname'] . '/' . $station['dname'] . '(' . $station['restriction_id'] . ') -> ' . $vehicle['vin'] . '/' . $vehicle['code'] . ' ' . $divisionsDeliveryPlan["delivery_id"] . '<br>';

            unset($possibleCombos[$combosKey]);
        } // end foreach free stations

        $this->debugcontent = $this->ladeLeitWartePtr->restrictionsPtr->generateTreeStructureForDepot($zsp);

        $extraemails = array(
            'Ismail.Sbika@streetscooter.eu',
            'Lothar.Juergens@streetscooter.eu',
            'Leon.Schottdorf@streetscooter.eu'
        );
        $mailer = new MailerSmimeSwift('Pradeep.Mohan@streetscooter.eu', '', 'StreetScooter Fahrzeuge Auslieferung Log ' . date('Y-m-j H:m:s'), $mailmsg . $this->debugcontent, null, true, $extraemails);
        $this->content .= $mailmsg;

        $this->updateDeliveryPlanZentrale($divisionsDeliveryPlan, $this->count_vehicles_assigned, $variant_value);

        return $assigned_vehicles;

    }


    // =============================================================================================================
    /**
     * Temporary function built only for Sts.Sales so that a vehicle assigned to be delivered to a depot can be taken back into the pool or production and corresponding
     * changes in delivery_to_division an delivery_plan can be implemented
     */
    function fahrzeug_zuruck($vehicle_id = null)
    {

        if (empty($vehicle_id))
            $vehicle_id = $this->requestPtr->getProperty('vehicle_id');
        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getFromId($vehicle_id);
        if ($vehicle['station_id'])
            $station = $this->ladeLeitWartePtr->stationsPtr->getFromId($vehicle['station_id']);
        $depot = $this->ladeLeitWartePtr->depotsPtr->getFromId($vehicle['depot_id']);

        $vehicle = $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne('qs_user,delivery_week,vehicle_variant');

        $delivery_to_divisions = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
            ->where('delivery_week', '=', $vehicle['delivery_week'])
            ->where('division_id', '=', $depot['division_id'])
            ->where('variant_value', '=', $vehicle['vehicle_variant'])
            ->getOne('*');

        if ($vehicle['qs_user'] == - 1) {
            $sts_pool = $this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
            $depot_id = $sts_pool['depot_id'];
        } else
            $depot_id = 0;

        $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->update(array(
            'depot_id',
            'station_id',
            'finished_status'
        ), array(
            $depot_id,
            NULL,
            't'
        ));

        $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->update(array(
            'kostenstelle',
            'delivery_date',
            'delivery_week',
            'delivery_status'
        ), array(
            NULL,
            NULL,
            NULL,
            NULL
        ));

        $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($vehicle['vehicle_variant']);

        $delivery_plan = $this->ladeLeitWartePtr->deliveryPlanPtr->getDeliveryPlanResetVehicleToProduction($vehicle['delivery_week'], $delivery_to_divisions['delivery_year'], $depot['division_id'], $external_post_variant_value);

        $vehicles = $delivery_to_divisions['vehicles_delivered'];
        $vehicles = unserialize($vehicles);
        foreach ($vehicles as $key => &$svehicle) {
            if ($svehicle == $vehicle_id)
                unset($vehicles[$key]);
        }
        $vehicles = serialize($vehicles);

        $delivery_to_divisions['vehicles_delivered_quantity'] --;
        $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
            ->where('delivery_id', '=', $delivery_to_divisions['delivery_id'])
            ->update(array(
            'vehicles_delivered_quantity',
            'vehicles_delivered'
        ), array(
            $delivery_to_divisions['vehicles_delivered_quantity'],
            $vehicles
        ));

        $delivery_plan['requirement_met'] --;
        $this->ladeLeitWartePtr->deliveryPlanPtr->newQuery()
            ->where('delivery_id', '=', $delivery_plan['delivery_id'])
            ->update(array(
            'requirement_met'
        ), array(
            $delivery_plan['requirement_met']
        ));
        $this->action = 'delivery';
        $this->delivery();

    }


    // =============================================================================================================
    function fahrzeug_tauschen()
    {

        if (empty($vehicle_id))
            $vehicle_id = $this->requestPtr->getProperty('vehicle_id');

        // $vehicle contains info form the vehicles_sales table
        $vehicle = $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne('qs_user,delivery_week,vehicle_variant');

        $variant_value = $vehicle['vehicle_variant'];

        if ($vehicle['qs_user'] == - 1) {

            $eol_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariantsWithChargerInfo($variant_value, 'pool');
            $return_to = 'pool';
        } else {
            $eol_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariantsWithChargerInfo($variant_value, 'production');
            $return_to = 'produktion';
        }

        // $vehicle contains info form the vehiclestable
        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne('vehicle_id,vin,code,charger_controllable');

        if ($vehicle['charger_controllable'] == 'f') {
            $lastTwo = (int) substr($vehicle['vin'], - 2);
            $orgininal_vehicle_vin_type = $lastTwo % 2;

            if (in_array($variant_value, $this->sopVariants)) {
                foreach ($eol_vehicles as $key => &$eol_vehicle) {
                    $lastTwo = (int) substr($eol_vehicle['vin'], - 2);
                    if ($orgininal_vehicle_vin_type != $lastTwo % 2)
                        unset($eol_vehicles[$key]);
                }
            }
        }

        $this->qform_vehicle_exchange = new QuickformHelper($this->displayHeader, 'vehicle_exchange');
        $this->qform_vehicle_exchange->getSalesVehicleExchangeForm($vehicle, $eol_vehicles, 'vehicle_exchange_save', $return_to);

    }


    // =============================================================================================================
    function vehicle_exchange_save()
    {

        if (empty($vehicle_id))
            $vehicle_id = $this->requestPtr->getProperty('vehicle_id');
        $exchange_vehicle = $this->requestPtr->getProperty('exchange_vehicle');
        $return_to = $this->requestPtr->getProperty('return_to');

        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getFromId($vehicle_id);
        $original_vehicle_vin = $vehicle['vin'];
        $station = $this->ladeLeitWartePtr->stationsPtr->getFromId($vehicle['station_id']);
        $depot = $this->ladeLeitWartePtr->depotsPtr->getFromId($vehicle['depot_id']);

        if ($return_to == 'pool') {
            $sts_pool = $this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
            $depot_id = $sts_pool['depot_id'];
        } else
            $depot_id = 0;

        $vehicle_sales_info = $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne('kostenstelle,delivery_date,delivery_week,delivery_status,vehicle_variant');

        $delivery_to_divisions = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
            ->where('delivery_week', '=', $vehicle_sales_info['delivery_week'])
            ->where('division_id', '=', $depot['division_id'])
            ->where('variant_value', '=', $vehicle_sales_info['vehicle_variant'])
            ->getOne('*');

        $vehicles = $delivery_to_divisions['vehicles_delivered'];
        $vehicles = unserialize($vehicles);

        if (! empty($vehicles)) {
            foreach ($vehicles as $key => &$svehicle) {
                if ($svehicle == $vehicle_id)
                    unset($vehicles[$key]);
            }
            $vehicles[] = $exchange_vehicle;
            $vehicles = serialize($vehicles);
        } else
            $vehicles = '';

        $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
            ->where('delivery_id', '=', $delivery_to_divisions['delivery_id'])
            ->update(array(
            'vehicles_delivered'
        ), array(
            $vehicles
        ));

        // set new vehicles depot id and station id
        $this->ladeLeitWartePtr->vehiclesPtr->assignVehicleToStation($exchange_vehicle, $station['station_id'], $station['depot_id'], $vehicle_sales_info['delivery_week'], $vehicle_sales_info['kostenstelle']);
        $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $exchange_vehicle)
            ->update(array(
            'finished_status'
        ), array(
            'f'
        ));

        // set finished status as false since we do not want this vehicle to appear in the EOL vehicles list again
        $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->update(array(
            'depot_id',
            'station_id',
            'finished_status'
        ), array(
            $depot_id,
            NULL,
            'f'
        ));

        // unset the vehicle_variant since we wont be updating it
        unset($vehicle_sales_info['vehicle_variant']);

        // update sales info the new vehicle with info from old vehicle
        $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
            ->where('vehicle_id', '=', $exchange_vehicle)
            ->update(array_keys($vehicle_sales_info), array_values($vehicle_sales_info));
        // reset values for the old vehicle
        $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->update(array(
            'kostenstelle',
            'delivery_date',
            'delivery_week',
            'delivery_status'
        ), array(
            NULL,
            NULL,
            NULL,
            NULL
        ));

        $exchange_vehicle_vin = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $exchange_vehicle)
            ->getVal('vin');
        $notification_msg = $exchange_vehicle_vin . ' getauscht gegen ' . $original_vehicle_vin . '<br>Keine Benachrichtigung Emails wurden an FPS/FPV versandt.';
        ;
        $this->msgs[] = $notification_msg;

        $extraemails = array(
            'Pradeep.Mohan@streetscooter.eu',
            'Team.Auslieferung@streetscooter.eu'
        );
        $mailer = new MailerSmimeSwift('Philipp.Schnelle@streetscooter.eu', '', 'StreetScooter Fahrzeuge Auslieferung - Auszulieferende Fahrzeuge getauscht', $notification_msg . "<br><br>Benutzer " . $this->user->getUserName(), null, true, $extraemails);

        $this->action = 'delivery';
        $this->delivery();

    }


    // =============================================================================================================

    /**
     * Commit #bff92c01bcfa510569169c18404be7558c14718a
     * Update the requirement_met column for the first available pending delivery (in Mobilitätsplanung, table delivery_plan) instead of doing it for this month alone.
     * This has to be done so because, the Vom Vormonat in the 'Auslieferungsplan' function, gets the Vormonat numbers from not just the last month but all the previous months!
     * The sum (delivery_to_divisions.delivery_quantity) > delivery_plan.quantity if the division has greater than 0 vom Vormonat numbers
     *
     * @param array $divisionsDeliveryPlan
     * @param integer $count_vehicles
     * @param integer $variant_value
     *            internal vehicle variant value
     */
    function updateDeliveryPlanZentrale($divisionsDeliveryPlan, $count_vehicles, $variant_value)
    {

        // we have the delivery week, but we do not now which month this week falls in.
        // The mobilitätsplanung (delivery_plan) has entries only according to 2016-08-01.. year month.. so we need to find
        // the month to update the requirement_met in the delivery_plan table
        while ($count_vehicles) {
            $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($variant_value);
            $deliveryPlanZentrale = $this->ladeLeitWartePtr->deliveryPlanPtr->getOnePendingDeliveryPlansVariant($divisionsDeliveryPlan['division_id'], $external_post_variant_value);
            if (! empty($deliveryPlanZentrale)) {
                $increment_requirement_met = $deliveryPlanZentrale['quantity'] - $deliveryPlanZentrale['requirement_met'];

                if ($count_vehicles <= $increment_requirement_met) {
                    $deliveryPlanZentrale['requirement_met'] += $count_vehicles;
                    $this->ladeLeitWartePtr->deliveryPlanPtr->newQuery()
                        ->where('delivery_id', '=', $deliveryPlanZentrale['delivery_id'])
                        ->update(array(
                        'requirement_met'
                    ), array(
                        $deliveryPlanZentrale['requirement_met']
                    ));
                    $count_vehicles = 0;
                } else {
                    $deliveryPlanZentrale['requirement_met'] += $increment_requirement_met;
                    $this->ladeLeitWartePtr->deliveryPlanPtr->newQuery()
                        ->where('delivery_id', '=', $deliveryPlanZentrale['delivery_id'])
                        ->update(array(
                        'requirement_met'
                    ), array(
                        $deliveryPlanZentrale['requirement_met']
                    ));
                    $count_vehicles = $count_vehicles - $increment_requirement_met;
                }
            } else {
                echo 'cannot retreive month or check delivery_plan table';
                return false;
            }
        }

        return true;

    }


    // =============================================================================================================
    function auto_fahrzeuge_zuweisen()
    {

        if (! isset($_POST['vehicle_variant'])) {
            $this->qform_vehicles_deliver_variant_select = new QuickformHelper($this->displayHeader, 'fahrzeuge_ausliefern');
            $this->qform_vehicles_deliver_variant_select->vehicles_deliver_variant_select($this->vehicle_variants);
        } else {
            if (! isset($_POST['date_selector_delivery'])) {
                $vehicle_variant = $_POST['vehicle_variant'];
                // $this->available_vehicle_count=$this->ladeLeitWartePtr->vehiclesPtr->getCountFinishedEOLVehicleVariant();
                $this->aftersalescount[$vehicle_variant] = sizeof($this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariants($vehicle_variant, 'pool'));
                $this->produktioncount[$vehicle_variant] = sizeof($this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariants($vehicle_variant, 'production'));
                $this->qform_vehicles_deliver_request = new QuickformHelper($this->displayHeader, 'fahrzeuge_ausliefern');
                $processedDivs = NULL;
                $pendingDivisions = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getPendingForYearVariant(date('Y'), $vehicle_variant);
                if (! empty($pendingDivisions)) {
                    $division_ids = array_column($pendingDivisions, 'division_id');

                    $divisions = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
                        ->where('division_id', 'IN', $division_ids)
                        ->orderBy('name')
                        ->get('division_id,name');

                    $processedDivs = array(
                        '' => 'Alle'
                    );

                    foreach ($divisions as $division) {
                        $processedDivs[$division['division_id']] = $division['name'];
                    }
                }
                $production_location = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
                    ->where('production_location', '=', 't')
                    ->orderBy('division_id')
                    ->get('division_id,name');
                $locations = array_combine(array_column($production_location, 'division_id'), array_column($production_location, 'name'));
                $this->qform_vehicles_deliver_request->qform_vehicles_deliver_request('autodelivery', $processedDivs, $locations);
                $this->qform_vehicles_deliver_request->addElement('hidden', 'vehicle_variant', array(
                    'id' => 'vehicle_variant'
                ))->setValue($vehicle_variant);
                $this->getVehiclesForDeliveryAjax();
                $this->showorder($vehicle_variant);
            }
        }

    }


    // =============================================================================================================
    function getVehiclesForDeliveryAjax()
    {

        // get production standort and change the vehicles as per
        // javascript to update the table
        // exclude these vehicles from the final list
        // add ikz as per drittkunden
        $production_location = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
            ->where('production_location', '=', 't')
            ->orderBy('division_id')
            ->get('division_id,name');
        $locations = array_combine(array_column($production_location, 'division_id'), array_column($production_location, 'name'));
        $variant_value = $this->requestPtr->getProperty('vehicle_variant');
        $ajaxquery = $this->requestPtr->getProperty('ajaxquery');
        $selected_production = $this->requestPtr->getProperty('selected_production');
        if (! is_numeric($selected_production))
            $selected_production = array_keys($locations)[0];

        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariants($variant_value, $selected_production);
        $processedVehicles = array();
        foreach ($vehicles as $key => $vehicle) {
            $vehicle_input = '<label style="display:block"><input type="checkbox" class="exclude_vehicle" name="exclude-' . $vehicle['vehicle_id'] . '" checked=checked data-vehicleid="' . $vehicle['vehicle_id'] . '"></label>';
            $processedVehicles[] = array(
                $key + 1,
                $vehicle['code'],
                $vehicle['vin'],
                $vehicle_input
            );
        }
        $table_header = array(
            'headingone' => array(
                'Pos.',
                'AKZ',
                'VIN',
                'ausliefern?<br><a href="#" class="set_all">Alle wählen</a><br><a href="#" class="reset_all">Alle deaktivieren</a>'
            )
        );
        $vehicles_table = new DisplayTable(array_merge($table_header, $processedVehicles));

        if ($ajaxquery) {
            echo '<h2>Fahrzeuge: ' . $locations[$selected_production] . '</h2>' . $vehicles_table->getContent();
            exit(0);
        } else
            $this->vehicles_for_delivery = '<h2>Fahrzeuge: ' . $locations[$selected_production] . '</h2>' . $vehicles_table->getContent();

    }


    // =============================================================================================================
    function autodelivery()
    {

        $delivery_date = $this->requestPtr->getProperty('date_selector_delivery');
        $selected_div = NULL;
        $selected_div = $this->requestPtr->getProperty('selected_div');
        // total quantity of vehicles to be delivered as specified by the user
        $delivery_quantity = $this->requestPtr->getProperty('count_vehicles');
        $vehicle_variant = $this->requestPtr->getProperty('vehicle_variant');
        $end_process = false;
        $exclude_vehicles = $this->requestPtr->getProperty('exclude_vehicles');
        $selected_production = $this->requestPtr->getProperty('selected_production');
        // needs to be outside the loop so that these stations re not included in the next loop
        // $assigned_stations holds the station_ids which have already been processed by the autodelivery function
        $assigned_stations = array();
        // $exclude_station_ids copies the value of $assigned_stations for each iteration of foreach($pendingDivisions as $division)
        $exclude_station_ids = array();
        $do_cnt = 1;

        do {
            // get all pending divisions
            $pendingDivisions = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getPendingForYearVariant(date('Y'), $vehicle_variant, $selected_div);

            // undeliverable quantities, like 1,2 or 3 will be moved here to be delivered next week
            $moved_quantities = array();
            $print_delivery_docs = true;
            // CHANGE FOR PRODUCTION To false

            // CHANGE FOR PRODUCTION To false

            // break flag for the process..set initially to false, then true when required number of stations have been found
            $flag = false;
            // sum of selected stations for delivery, from all divisions
            $all_division_count = 0;
            // count of selected stations for delivery, from one division, reset with each iteration of foreach($pendingDivisions as $division)
            $cnt = 0;
            $debug = 0;

            $assigned_vehicles = array();

            foreach ($pendingDivisions as $division) {
                /*
                 * example
                 * Ravensburg (kw2) Auszulieferende Fahrzeuge : 5 + 0
                 * Ravensburg (kw3) Auszulieferende Fahrzeuge : 12 + 1
                 */
                // set up an empty array
                $temp_assigned_stations = array();

                $division_vehicles = array();

                $division_name = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
                    ->where('division_id', '=', $division['division_id'])
                    ->getVal('name');

                $division_id = $division['division_id'];

                // pending delivery - vehicles already delivered
                $pendingquantity = $division['delivery_quantity'] - $division['vehicles_delivered_quantity'];

                // stations which have already been processed, but no suitable vehicles found, so exclude them on the second iteration
                $exclude_station_ids = $assigned_stations;

                // get all stations ordered by the vehicle_variant_update_ts
                $stations = $this->ladeLeitWartePtr->stationsPtr->newGetFreeStationsVariantDiv($division['division_id'], $division['variant_value'], $exclude_station_ids);

                if ($vehicle_variant == 1 || $vehicle_variant == 2 || $vehicle_variant == 9)
                    $bundle_qty = 4;
                else
                    $bundle_qty = 3;

                // if not enough stations to fulfil the bundling condition of $bundle_qty, then skip this division
                // start quick fix for July August 2017 deliveries
                // if(sizeof($stations)<$bundle_qty &&
                if (sizeof($stations) < $delivery_quantity)
                    continue;
                // endquick fix for July August 2017 deliveries
                if (! empty($stations)) {
                    /**
                     * example Ravensburg (kw2) : 5 + 0
                     * $moved_quantities[$division_id]) is not set..will not enter if(isset($moved_quantities[$division_id]))
                     *
                     *
                     * however for example Ravensburg (kw3) Auszulieferende Fahrzeuge : 12 + 1 will enter this if
                     */
                    if (isset($moved_quantities[$division_id])) {
                        $moved = array_sum(array_column($moved_quantities[$division_id], 'moved_quantity'));
                    } else
                        $moved = 0;

                    if ($debug == 1)
                        echo '<h2>' . $division['delivery_id'] . ' ' . $division_name . ' (' . $division['delivery_week'] . ')  Auszulieferende Fahrzeuge : ' . $pendingquantity . ' + ' . $moved . '</h2><br>';

                    // total quantity is the quantity to be delivered this week and the previous
                    /**
                     * example Ravensburg (kw2) : 5 + 0
                     */
                    $totalquantity = $pendingquantity + $moved;

                    // a number divisible by $bundle_qty
                    /**
                     * example Ravensburg (kw2) : 5 + 0
                     * $totalquantity=5+0=5
                     * floor(5/4) => $quotient=1, $remainder=1
                     */
                    $quotient = floor($totalquantity / $bundle_qty);

                    // move the remainder to the moved quantities variable
                    $remainder = $totalquantity % $bundle_qty;
                    /**
                     * example Ravensburg (kw2) : 5 + 0
                     * $moved_quantities[$division_id]=array('delivery_id'=>$division['delivery_id'],
                     * 'moved_quantity'=>1);
                     */
                    $moved_quantities[$division_id][] = array(
                        'delivery_id' => $division['delivery_id'],
                        'moved_quantity' => $remainder
                    );
                    if ($debug == 2) {
                        echo '<pre>';
                        print_r($moved_quantities[$division_id]);
                        echo '</pre>';
                    }

                    // start quick fix for July August 2017 deliveries
                    // if the total quantity for this division is <$bundle_qty then no transport is required, continue to next division
                    // if($totalquantity<$bundle_qty) continue;
                    // end quick fix for July August 2017 deliveries
                    // rounded to multiple $bundle_qty is the actual quantity for this division
                    /**
                     * example Ravensburg (kw2) : 5 + 0
                     * $actual=1*4=4;
                     */
                    $actual = $quotient * $bundle_qty;
                    // count of selected stations for delivery, from one division, reset with each iteration of foreach($pendingDivisions as $division)
                    $cnt = 0;
                    $lastdepot = '';

                    if ($debug == 1) {
                        echo $cnt . ' cnt ' . $actual . ' actual ' . $totalquantity . 'total<br>';
                        echo $all_division_count . ' all div cnt <br>';
                    }

                    // loop through each station
                    foreach ($stations as $station) {
                        // if the count of selected for delivery is greater than the bundled quantity to be delivered in this division, the break
                        // start quick fix for July August 2017 deliveries
                        if ($cnt >= $totalquantity) {
                            // @todo: for review: is this correct? add the count of stations found in this division to the all_division_cnt
                            $all_division_count += $cnt;
                            break;
                        }
                        // if($cnt>=$actual) // bundle in 4s or 3s for this division is complete, then skip
                        // {
                        // break;
                        // }

                        // end quick fix for July August 2017 deliveries

                        // if the delivery quantity (total quantity of vehicles to be delivered as specified by the user) has not been achieved continue assigning stations
                        if (($all_division_count + $cnt) < $delivery_quantity) {
                            $depot_id = $station['depot_id'];
                            // store this station in assigned_stations so it is excluded from the next iteration
                            $assigned_stations[] = $station['station_id'];
                            // temp_assigned_stations holds the stations for each depot for this division
                            $temp_assigned_stations[] = $station['station_id'];
                            if ($debug == 1)
                                echo ($cnt + 1) . '. ' . $station['dname'] . ' (' . $station['station_id'] . ') ' . $all_division_count . ' < ' . $delivery_quantity . '<br>';
                            // increase cnt by one since one station has been found
                            $cnt ++;
                        } // if the delivery quantity (total quantity of vehicles to be delivered as specified by the user) has been achieved
                        else {
                            $thissize = sizeof($temp_assigned_stations); // @todo 2017-01-11 where is this variable used? nowhere! delete after review!
                            if ($debug == 1)
                                echo 'breaking ' . $all_division_count . '+' . $cnt . '<=' . $delivery_quantity . '<br>';
                            // add the count of stations found in this division to the all_division_cnt
                            $all_division_count += $cnt;
                            // set the break flag to true
                            $flag = true;
                            break;
                        }
                    } // end of foreach $stations

                    if ($debug == 1)
                        echo $cnt . ' cnt ' . $all_division_count . ' all div cnt <br>';
                } // if(!empty($stations))

                // $temp_assigned_stations holds the found stations for this division
                if (! empty($temp_assigned_stations)) {
                    // we have all available stations for this division
                    // now check if we have suitable/enough vehicles for all these stations, if not move on the next division

                    $count_vehicles = sizeof($temp_assigned_stations);

                    $variant_value = $division['variant_value'];

                    $free_stations = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsAlreadyVariantAssigned($variant_value, $temp_assigned_stations);
                    $possibleCombos = array();
                    if (isset($selected_production) && is_numeric($selected_production)) {
                        $provehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedVehiclesChargerInfoExclude($variant_value, $selected_production, 'post', $exclude_vehicles);

                        $consider_even_odd = false;
                        // check if any one of the vehicles has charger controllable as false, then we need to check for even odd
                        if (array_search('f', array_column($provehicles, 'charger_controllable')) !== false)
                            $consider_even_odd = true;

                        $possibleCombos = $this->ladeLeitWartePtr->restrictionsPtr->genPossibleCombos($free_stations, $count_vehicles, $this->sopVariants, $provehicles, $consider_even_odd);
                    } else {
                        $aftersales_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariantsWithChargerInfo($variant_value, 'pool');
                        $cntaftersales = sizeof($aftersales_vehicles);
                        // try with aftersales vehicles first
                        if (! empty($aftersales_vehicles) && $count_vehicles <= $cntaftersales) {
                            $consider_even_odd = false;
                            // check if any one of the vehicles has charger controllable as false, then we need to check for even odd
                            if (array_search('f', array_column($aftersales_vehicles, 'charger_controllable')) !== false)
                                $consider_even_odd = true;

                            $possibleCombos = $this->ladeLeitWartePtr->restrictionsPtr->genPossibleCombos($free_stations, $count_vehicles, $this->sopVariants, $aftersales_vehicles, $consider_even_odd);
                        }
                        // try with production vehicles next
                        if (empty($possibleCombos)) {
                            $production_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariantsWithChargerInfo($variant_value, 'production');
                        }
                        if (! empty($production_vehicles)) {
                            if (sizeof($production_vehicles) < $count_vehicles) {
                                $this->msgs[] = 'Nicht genug Fahrzeuge für ' . $division_name . ' (' . strtoupper($division['delivery_week']) . ')<br>';
                                continue;
                            }

                            $consider_even_odd = false;
                            // check if any one of the vehicles has charger controllable as false, then we need to check for even odd
                            if (array_search('f', array_column($production_vehicles, 'charger_controllable')) !== false)
                                $consider_even_odd = true;
                            $possibleCombos = $this->ladeLeitWartePtr->restrictionsPtr->genPossibleCombos($free_stations, $count_vehicles, $this->sopVariants, $production_vehicles, $consider_even_odd);
                        }
                    }

                    if (empty($possibleCombos)) {
                        // remove these stations from the all_division_count so that when the next iteration continues more stations
                        // can be found
                        if ($count_vehicles > 0)
                            $all_division_count -= $count_vehicles;
                        $this->msgs[] = $this->ladeLeitWartePtr->restrictionsPtr->getPossibleCombosError() . ' für ' . $division_name . ' (' . strtoupper($division['delivery_week']) . ')<br>';
                        continue;
                    }
                    $assigned_cnt = 0;

                    // continue HERE... do not assign depot by depot!

                    if (! empty($moved_quantities[$division_id]))
                        foreach ($moved_quantities[$division_id] as &$data) {
                            $delivery_id = $data['delivery_id'];
                            $moved_quantity = $data['moved_quantity'];

                            if ($moved_quantity == 0 || ($delivery_id == $division['delivery_id']))
                                continue;

                            if ($debug == 1)
                                echo $moved_quantity . ' moved process deliveryid' . $delivery_id . '<br>';

                            if ($count_vehicles < $moved_quantity)
                                $count_vehicles_to_assign_previous_kw = $count_vehicles;
                            else
                                $count_vehicles_to_assign_previous_kw = $moved_quantity;

                            $divisionsDeliveryPlanMoved = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
                                ->where('delivery_id', '=', $delivery_id)
                                ->getOne('delivery_id,division_id,delivery_week,delivery_quantity,vehicles_delivered_quantity,vehicles_delivered');

                            if ($count_vehicles_to_assign_previous_kw)
                                $status = $this->assignStationsVehicles($variant_value, $count_vehicles_to_assign_previous_kw, $divisionsDeliveryPlanMoved, $possibleCombos);

                            if ($status !== false)
                                $assigned_stations_division = (array_column($status, 'station_id'));

                            if (! empty($assigned_stations_division))
                                $temp_assigned_stations = array_diff($temp_assigned_stations, $assigned_stations_division); // @todo review immdt

                            if (is_array($status))
                                $division_vehicles = $status;

                            $count_vehicles -= sizeof($status);
                            $data['moved_quantity'] -= sizeof($status);
                        }

                    if ($count_vehicles)
                        $status = $this->assignStationsVehicles($variant_value, $count_vehicles, $division, $possibleCombos);
                    if ($status !== false)
                        $assigned_stations_division = (array_column($status, 'station_id'));

                    if (! empty($assigned_stations_division))
                        $temp_assigned_stations = array_diff($temp_assigned_stations, $assigned_stations_division);

                    if (is_array($status))
                        $division_vehicles = array_merge($division_vehicles, $status);

                    $assigned_vehicles = array_merge($assigned_vehicles, $division_vehicles);
                }

                $all_division_count += $cnt;

                if ($debug == 1) {
                    echo $cnt . ' cnt ' . $all_division_count . ' all div cnt <br>';
                }

                if ($flag === true)
                    break;
            } // end of foreach $pendingDivisions
            if ($debug == 1) {
                echo '<br><pre>stations<br>';
                print_r($assigned_stations);
                echo '</pre><br>';
            }

            if ($debug == 1) {
                echo '<br><pre>vehicles<br>';
                print_r($assigned_vehicles);
                echo '</pre><br>';
            }

            if ($print_delivery_docs && ! empty($assigned_vehicles)) {

                $processed_vehicle_ids = array();
                foreach ($assigned_vehicles as $vehicle) {
                    $delivery_status = '';
                    $vehicle_id = $vehicle['vehicle_id'];
                    if (empty($delivery_date)) {
                        $delivery_date = NULL;
                        $delivery_status = 'FALSE';
                    } else {
                        $ts_deliverydate = strtotime($delivery_date);
                        $delivery_date = date('Y-m-d 00:00:00O', $ts_deliverydate);
                        $delivery_status = 'TRUE';
                    }

                    $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
                        ->where('vehicle_id', '=', $vehicle_id)
                        ->update(array(
                        'delivery_date',
                        'delivery_status'
                    ), array(
                        $delivery_date,
                        $delivery_status
                    ));

                    $processed_vehicle_ids[] = $vehicle_id;
                }

                // if(isset($_POST['send_notification_emails']) && $_POST['send_notification_emails']==1)
                // $send_notification_emails=true;
                // else
                $send_notification_emails = true;
                $this->saveExportLieferschein('vehicles.vehicle_id', $processed_vehicle_ids, $send_notification_emails);
                $this->pentaCSVExport($processed_vehicle_ids);
            } // if($print_delivery_docs)

            if (sizeof($assigned_vehicles) >= $delivery_quantity) {
                $end_process = true;
                $delivery_quantity -= sizeof($assigned_vehicles);
            }
            $do_cnt ++;
        } while ($end_process === false && $do_cnt <= 5);

        // reset all assigned vehicles
        $testing_assignment = false;
        if ($testing_assignment === true) {
            foreach ($assigned_vehicles as $vehicle)
                $this->fahrzeug_zuruck($vehicle['vehicle_id']);
        }
        $this->action = "delivery";
        $this->delivery();

    }


    function getDistance($lat1, $lon1, $lat2, $lon2)
    {

        $x = (float) 111.3 * cos(($lat1 + $lat2) / 2 * 0.01745) * ($lon1 - $lon2);
        $y = (float) 111.3 * ($lat1 - $lat2);
        return sqrt($x * $x + $y * $y);

    }


    function saveProPlan()
    {

        $quantities = $this->requestPtr->getProperty('quantities');
        $yearmonth = $this->requestPtr->getProperty('yearmonth');

        foreach ($quantities as $kweek => $variant_kweek_quantity) {

            $primary_details = array(
                'added_timestamp' => date('Y-m-d H:i:sO'),
                'production_year' => date('Y', strtotime($yearmonth))
            ); // removed yearmonth.. we dont need it
            foreach ($variant_kweek_quantity as $variant_value => $kweek_quantity) {

                $result = $this->ladeLeitWartePtr->productionPlanPtr->getForYearMonthVariantWeek($yearmonth, $kweek, $variant_value);

                $newdata = array_merge($primary_details, array(
                    'production_week' => $kweek,
                    'production_quantity' => $kweek_quantity,
                    'variant_value' => $variant_value
                ));
                if (! empty($result)) {
                    if (empty($kweek_quantity)) {
                        $result = $this->ladeLeitWartePtr->productionPlanPtr->newQuery()
                            ->where('production_plan_id', '=', $result['production_plan_id'])
                            ->delete();
                    } else
                        $result = $this->ladeLeitWartePtr->productionPlanPtr->newQuery()
                            ->where('production_plan_id', '=', $result['production_plan_id'])
                            ->update(array_keys($newdata), array_values($newdata));
                    if ($result) {} else
                        $this->msgs[] = 'Fehler beim Speichern!';
                } else {
                    if (empty($kweek_quantity))
                        continue;
                    $this->ladeLeitWartePtr->productionPlanPtr->newQuery()->insert($newdata);
                }
            }
        }

        $this->action = 'showProPlan';
        $this->msgs[] = 'Änderungen gespeichert!';
        $this->showProPlan();

    }


    // =============================================================================================================
    function showProPlan()
    {

        $this->qform_monthyear = new QuickformHelper($this->displayHeader, 'yearmonth');
        // set locale so thar we can use strftime to display in german
        setlocale(LC_TIME, "de_DE.UTF-8");
        // $thistime=strtotime("first day + $i months");
        $months[date('Y-m-01')] = strftime('%B %Y');
        for ($i = 0; $i < 12; $i ++) {
            $thistime = strtotime("first day + $i months");
            if (date('Y-m-01') == date('Y-m-01', $thistime))
                continue;
            $months[date('Y-m-01', $thistime)] = strftime('%B %Y', $thistime);
        }

        $this->qform_monthyear->genMonthYearSelect($months, 'showProPlan');

        if (isset($_POST['yearmonth'])) {
            $yearmonth = $_POST['yearmonth'];
            // need to get the current values for production plan to the quickform helper
            $productionPlan = $this->ladeLeitWartePtr->productionPlanPtr->getForYearMonth($yearmonth);
            $this->qform_proplan = new QuickformHelper($this->displayHeader, 'proplan_save');

            // need to pass the weeks of this month to the quickform helper
            $weeks = $this->ladeLeitWartePtr->getWeeksFromYearMonth($yearmonth, true, ' KW ', 'kw');

            $this->qform_proplan->getProPlanForm($productionPlan, $this->vehicle_variants, $yearmonth, $weeks);
        }

    }


    // =============================================================================================================
    /**
     * *
     * showDivisionsDeliveryPlan
     */
    function showDivisionsDeliveryPlan()
    {

        /**
         * start comment
         */
        if ($this->user->getUserName() != 'Sts.Sales') {
            $this->msgs = 'Zurzeit nicht verfügbar (wegen mögliche Fehlfunktion)';
            return;
        }
        /**
         * end comment
         */

        $debug = 0;
        $this->qform_monthyear = new QuickformHelper($this->displayHeader, 'yearmonth');
        // set locale so thar we can use strftime to display in german
        setlocale(LC_TIME, "de_DE.UTF-8");
        $thistime = strtotime("first day of - 1 months");
        $months[date('Y-m-01', $thistime)] = strftime('%B %Y', $thistime);
        for ($i = 0; $i < 12; $i ++) {
            $thistime = strtotime("first day of + $i months"); // http://php.net/manual/en/function.strtotime.php#107331
            $months[date('Y-m-01', $thistime)] = strftime('%B %Y', $thistime);
        }

        $this->qform_monthyear->genMonthYearVariantSelect($months, $this->vehicle_variants, 'showDivisionsDeliveryPlan', true);

        if (isset($_POST['calendar_weeks']) && isset($_POST['variant_value'])) {
            $year = date('Y');
            $calendar_weeks = $_POST['calendar_weeks'];
            $variant = $_POST['variant_value'];
            $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($variant);

            $deliveryPlanResults = $this->ladeLeitWartePtr->newQuery('delivery_plan_week')
                ->where('delivery_year', '=', $year)
                ->where('variant', '=', $external_post_variant_value)
                ->where('delivery_week', 'IN', $calendar_weeks)
                ->join('divisions', 'divisions.division_id=delivery_plan_week.division_id', 'INNER JOIN')
                ->groupBy('divisions.division_id,dp_division_id,name')
                ->get('divisions.division_id,dp_division_id,name,json_object_agg(delivery_week,quantity) as quantities');

            $rows = array();
            if (! empty($deliveryPlanResults))
                foreach ($deliveryPlanResults as $fordivision) {
                    $week_quantities = json_decode($fordivision['quantities'], true);

                    $quantities = array();
                    foreach ($calendar_weeks as $kweek) {
                        $existrow_delivery_to_divisions = $this->ladeLeitWartePtr->newQuery('delivery_to_divisions')
                            ->where('division_id', '=', $fordivision['division_id'])
                            ->where('delivery_week', '=', $kweek)
                            ->where('delivery_year', '=', $year)
                            ->where('variant_value', '=', $variant)
                            ->getOne('delivery_id');

                        if (empty($existrow_delivery_to_divisions)) {
                            if (isset($week_quantities[$kweek]))
                                $quantities[$kweek] = $week_quantities[$kweek];
                            else
                                $quantities[$kweek] = '';
                        } else
                            $quantities[$kweek] = '<span style="color:#999">' . $week_quantities[$kweek] . '/</span>';
                    }
                    if (! empty($quantities))
                        $rows[] = array_merge(array(
                            $fordivision['dp_division_id'],
                            $fordivision['name']
                        ), $quantities);
                }
            $table_data = array();
            $table_data[] = array(
                'headingone' => array_merge(array(
                    'OZ',
                    'Niederlassung Name'
                ), $calendar_weeks)
            );
            $processed_listObjects = array_merge($table_data, $rows);
            $this->delivery_plan_week = new DisplayTable($processed_listObjects, array(
                'id' => 'deliveryplanshow'
            ));
            $this->qform_delplan_week = new QuickformHelper($this->displayHeader, 'delplan_save_week');
            $this->qform_delplan_week->addElement('hidden', 'delivery_year')->setValue($year);
            $this->qform_delplan_week->addElement('hidden', 'action')->setValue('saveDivisionsDeliveryPlanByWeek');
            $this->qform_delplan_week->addElement('hidden', 'variant')->setValue($variant);
            $this->qform_delplan_week->addElement('hidden', 'calendar_weeks')->setValue(implode(',', $calendar_weeks));
            $this->qform_delplan_week->addElement('submit', 'delplan_save_week', array(
                'value' => "Speichern"
            ));
        } else if (isset($_POST['yearmonth']) && isset($_POST['variant_value'])) {

            $post_variant_value = $_POST['variant_value'];
            /**
             * $_POST['yearmonth'] can be set by two actions
             * 1.
             * When the yearmonth and variant are selected in this self function (showDivisionsDeliveryPlan) and form is submitted
             * 2. When this function showDivisionsDeliveryPlan is called from saveDivisionsDeliveryPlan where it is set as a hidden form element
             */
            if (is_array($_POST['yearmonth']))
                $yearmonth = $_POST['yearmonth'];
            else if (unserialize($_POST['yearmonth']) !== FALSE)
                $yearmonth = unserialize($_POST['yearmonth']);
            else
                $yearmonth = $_POST['yearmonth'];

            /* check if we are delivering for December 2016 and January 2017. System cannot calculate delivery plan for two months from different years. */
            $prev_delivery_year = '';
            if (is_array($yearmonth)) {
                foreach ($yearmonth as $single_yearmonth)
                    $delivery_year = date('Y', strtotime($single_yearmonth));
                if ($prev_delivery_year != '' && $prev_delivery_year != $delivery_year) {
                    $this->msgs[] = "Auslieferung für Monaten zwei verschiedenen Jahren kann nicht gleichzeitig durchgeführt werden.";
                    return;
                }
                $prev_delivery_year = $delivery_year;
            } else
                $delivery_year = date('Y', strtotime($yearmonth));

            $weeks = $this->ladeLeitWartePtr->getWeeksFromYearMonth($yearmonth, true, ' KW ', 'kw');

            $processed_divisions = array();

            // variable to store the values of the quantites already processed from the mobilitatsplanung into the delivery_to_divisions table
            // this value has to be subtracted from the division requirement in the foreach loop
            // this does not work for pending divisions
            $subtract_from_division_quantity = array();

            $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($post_variant_value);

            $result = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getInternalValues($external_post_variant_value);
            $all_internal_variant_values = json_decode($result['internal_variant_values'], true);
            $sum_already_assigned_vehicles = 0;
            // @todo 2016-09-22 just add the quantity to the pending division right?
            foreach ($all_internal_variant_values as $internal_variant_value) {
                $deliveryToDivisionResults = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getForYearMonthVariant($yearmonth, $internal_variant_value, $delivery_year);

                if (! empty($deliveryToDivisionResults)) {

                    // if this internal_variant_value is the same variant value selected by the user, then we need it for the table
                    if ($post_variant_value == $internal_variant_value) {
                        $this->deliveryToDivisionResults[] = array(
                            'headingone' => array_merge(array(
                                'Niederlassung',
                                'Freie Ladesäulen',
                                'Gespeichert am'
                            ), $weeks)
                        );
                        foreach ($deliveryToDivisionResults as $division) {

                            $division['delivery_quantities'] = json_decode($division['delivery_quantities'], true);
                            $stations = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsToBeAssignedCountForDiv($division['division_id']);

                            $processed_divisions[] = $division['division_id'];
                            $proDiv = array();
                            $proDiv['name'] = $division['name'];
                            $proDiv['scnt'] = $stations['scnt'];
                            $proDiv['added'] = date('Y-m-d H:i', strtotime($division['added_timestamp']));
                            $weekskeys = array_keys($weeks);

                            foreach ($weekskeys as $thisweek) {
                                if (isset($division['delivery_quantities'][$thisweek])) {
                                    $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->vehiclesDeliveredThisWeekForDiv($thisweek, $division['division_id'], $delivery_year, $internal_variant_value);

                                    if (! empty($vehicles))
                                        $vehicle_details = '/<span class="error_msg" data-vehicle_ids=' . implode(',', $vehicles) . '>' . sizeof($vehicles) . '</span>';
                                    else
                                        $vehicle_details = '/<span class="error_msg" >0</span>';
                                    $proDiv[$thisweek] = $division['delivery_quantities'][$thisweek] . $vehicle_details;
                                    $sum_already_assigned_vehicles += $division['delivery_quantities'][$thisweek];
                                } else
                                    $proDiv[$thisweek] = 0;
                            }

                            $this->deliveryToDivisionResults[] = $proDiv;
                        }
                    }
                    // if internal_variant_value is not variant value selected by the user, then we need to subtract the assigned quantities of the other
                    // variant from the mobilitätsplanung number
                    // else
                    // {
                    // foreach($deliveryToDivisionResults as $division)
                    // {

                    // $division['delivery_quantities']=json_decode($division['delivery_quantities'],true);
                    // $subtract_from_division_quantity[$division['division_id']]=array_sum($division['delivery_quantities']);
                    // }
                    // }
                }
            }

            // get results where processed_status is true but remaining quantity is >0
            // however problem is even if we've assigned the weekly delivery schedule, we still retreive the same pending again and again.
            // should check in the deliverytodivisions plan if this quantity is accounted for.
            $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($post_variant_value);

            $other_internal_values = array_diff($all_internal_variant_values, array(
                $post_variant_value
            ));

            $weeksfromnow = $this->ladeLeitWartePtr->getWeeksFromYearMonthStartingNow($yearmonth, true);

            // get the delivery requirement for divisions which have non zero delivery requirement quantities, also accounts for the pending from previous months
            $result = $this->ladeLeitWartePtr->deliveryPlanPtr->getUnprocessedDeliveryPlansMonthVariant($yearmonth, $external_post_variant_value, $post_variant_value, $other_internal_values, $processed_divisions, $weeksfromnow, $delivery_year);
            $deliveryPlanResults = $result['data'];

            // sum of all the deliveries to be made this month for all divisions
            $deliveryPlanSum = $result['sum'];

            // $this->ladeLeitWartePtr->deliveryPlanPtr->getUnprocessedSumDeliveryPlansMonthVariant($yearmonth,$external_post_variant_value,$post_variant_value,$other_internal_values,$processed_divisions);

            if ($deliveryPlanSum == 0) {
                $this->msgs[] = 'Delivery Requirements have been processed, or no vehicles to deliver';
                return;
            }

            // get the production_plan sum
            $productionPlanSum = $this->ladeLeitWartePtr->productionPlanPtr->getSumYearMonthVariant($yearmonth, $post_variant_value, $delivery_year);
            // pool can also have B16 vehicles now!
            // if(strpos($this->vehicle_variants[$post_variant_value],'B14')!==FALSE)

            // @todo 2016-11-08 remove fleet vehicles from being included in the Auslieferungsplan
            // $productionPlanSum+=$this->ladeLeitWartePtr->vehiclesPtr->getCountStsPoolVehiclesForVariant($post_variant_value);
            $productionPlanSum -= $sum_already_assigned_vehicles;

            $weeksfromnow = $this->ladeLeitWartePtr->getWeeksFromYearMonthStartingNow($yearmonth, true, ' KW ', 'kw');

            $this->msgs[] = 'Summe der auszulieferende Fahrzeuge im ' . implode(',', $weeks) . ': ' . $deliveryPlanSum . '<br>';
            $this->msgs[] = 'Summe der produzierende Fahrzeuge im ' . implode(',', $weeksfromnow) . ' + Fahrzeuge im Pool: ' . $productionPlanSum . '<br>';

            if ($productionPlanSum == 0) {
                $this->msgs[] = 'Bitte Produktionsplan dieser Fahrzeugkonfiguration angeben';
                return;
            }

            $sum = 0;
            $cnt = 0;

            // sort this array so that the divisions with the highest Vom Vormonat quantity gets the highest priority
            // usort($deliveryPlanResults, function($a, $b) {
            // return $b['pendingqty']-$a['pendingqty']; //if $a['pendingqty'] is greater then return -1 so that it gets higher priority
            // });
            /**
             * formula $adjust_factor=floor((($deliveryPlanSum-$sum)-$remainingQty)/($remainingDivisionsCnt/$divide_factor));
             * floor to round down
             * do {} while($productionPlanSum<$deliveryPlanSum && $sum<$productionPlanSum && $sum<$deliveryPlanSum);
             * loca
             * $productionPlanSum<$deliveryPlanSum
             * $productionPlanSum<$deliveryPlanSum run this loop more than once only if the productionPlanSum is less than the deliveryPlanSum
             *
             * $sum<$productionPlanSum
             * sometimes, the algorithm generates quantities so that the sum of vehicles assigned to divisions is less than the actual production sum!
             * so use the divide_factor so that $adjust_factor is reduced and thus more vehicles are assigned per division
             *
             * $sum<$deliveryPlanSum
             */

            do {
                // run this for a maximum of fifty iterations only. Sometimes trying to assign multiples of four for each divisions results in a infinite loop
                // where the $sum is always less than the $deliveryPlanSum
                $cnt ++;
                if ($cnt >= 50)
                    break;
                // adjust_factor is the reduction in the number of vehicles to one division so that the requirement for other divisions can be met
                $adjust_factor = 0;
                $sum = 0;
                // remainingQty keeps track of remaining vehicles from the production plan
                $remainingQty = $productionPlanSum;
                $remainingDivisionsCnt = sizeof($deliveryPlanResults);
                // foreach division
                foreach ($deliveryPlanResults as &$division) {
                    // if the delivery requirements cannot be met with the current production plan, then set the adjust factor
                    // calculate numbers of vehicles that cannot be produced and divide it by number of divisions which have non zero delivery_requirement
                    // runs recursively using numbers from the remaining divisions
                    if (($deliveryPlanSum - $sum) > $remainingQty)
                        $adjust_factor = floor($division['quantity'] * ($deliveryPlanSum - $productionPlanSum) / $deliveryPlanSum);
                    // if this divisions requirements can be met with remaining production quantity
                    if (($remainingQty - ($division['quantity'])) >= 0) {

                        // if $division['quantity'] is less than the adjust factor then just
                        if ($division['quantity'] <= $adjust_factor) {
                            $division['delivery_to_division_quantity'] = $division['quantity'];
                        } // assign delivery quantity for this division subtracting the adjust factor
                        else
                            $division['delivery_to_division_quantity'] = $division['quantity'] - $adjust_factor;
                    } else {
                        // if there are not enough remaining vehicles to meet the delivery requirement for this division
                        // assign the remaining vehiles to this division and
                        $division['delivery_to_division_quantity'] = $remainingQty;
                    }
                    $this->round_to_four($division);

                    if ($debug == 1)
                        $division['name'] .= ' |' . $division['quantity'] . '->' . $division['delivery_to_division_quantity'] . ' : ' . $adjust_factor . ' :: ' . ($deliveryPlanSum - $sum) . ' - ' . $remainingQty . ' : ' . $remainingDivisionsCnt . ' : ' . $adjust_factor . '|<br>';

                    // reduce the remainingQty by number of vehicles just assigned to this division
                    $remainingQty = $remainingQty - $division['delivery_to_division_quantity'];

                    $sum += $division['delivery_to_division_quantity'];
                }
            } while ($productionPlanSum < $deliveryPlanSum && $sum < $productionPlanSum && $sum < $deliveryPlanSum);

            $this->qform_delplan = new QuickformHelper($this->displayHeader, 'delplan_save');
            $variantname = $this->vehicle_variants[$post_variant_value];

            // sort here

            $this->qform_delplan->getDeliveryPlanForm($deliveryPlanResults, $yearmonth, $post_variant_value, $variantname, $productionPlanSum);
        }

    }


    function round_to_four(&$division)
    {

        // round to the nearest multiple of 4 http://stackoverflow.com/questions/4133859/round-up-to-nearest-multiple-of-five-in-php
        if ($division['delivery_to_division_quantity'] > 4) {
            $n = $division['delivery_to_division_quantity'];
            $x = 4;

            $multiplefour = (round($n) % $x === 0) ? round($n) : round(($n + $x / 2) / $x) * $x;
            while ($multiplefour > $division['quantity'])
                $multiplefour -= $x;
            while ($multiplefour > $division['delivery_to_division_quantity'])
                $multiplefour -= $x;
            $division['delivery_to_division_quantity'] = $multiplefour;
        }

    }


    // =============================================================================================================

    /**
     * saveDivisionsDeliveryPlanByWeek()
     * If the user chooses to use the calendar based Mobilitätsplanung, this action is called which retrieves data
     * from the delivery_plan_week table and saves it to delivery_to_divisions.
     */
    function saveDivisionsDeliveryPlanByWeek()
    {

        if (isset($_POST['calendar_weeks'])) {
            $calendar_weeks = $_POST['calendar_weeks'];
            $calendar_weeks = explode(',', $calendar_weeks);
        }
        if (isset($_POST['delivery_year']))
            $delivery_year = $_POST['delivery_year'];

        $variant_value = $_POST['variant'];

        $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($variant_value);

        $deliveryPlanResults = $this->ladeLeitWartePtr->newQuery('delivery_plan_week')
            ->where('delivery_year', '=', $delivery_year)
            ->where('variant', '=', $external_post_variant_value)
            ->where('delivery_week', 'IN', $calendar_weeks)
            ->get('division_id,quantity,delivery_week');

        $rows = array();
        $insert_cols = array(
            'division_id',
            'delivery_week',
            'delivery_year',
            'variant_value',
            'delivery_quantity',
            'added_timestamp'
        );
        $divisions = array();
        foreach ($deliveryPlanResults as $row) {
            $existrow_delivery_to_divisions = $this->ladeLeitWartePtr->newQuery('delivery_to_divisions')
                ->where('division_id', '=', $row['division_id'])
                ->where('delivery_week', '=', $row['delivery_week'])
                ->where('delivery_year', '=', $delivery_year)
                ->where('variant_value', '=', $variant_value)
                ->getOne('delivery_id');

            if (empty($existrow_delivery_to_divisions)) {
                $divisions[] = $row['division_id'];
                $insert_vals[] = array(
                    $row['division_id'],
                    $row['delivery_week'],
                    $delivery_year,
                    $variant_value,
                    $row['quantity'],
                    date('Y-m-d H:i:sO')
                );
            }
        }

        if ($this->ladeLeitWartePtr->newQuery('delivery_to_divisions')->insert_multiple_new($insert_cols, $insert_vals))
            $this->msgs[] = 'Daten hinzugefügt.';

        foreach ($divisions as $division_id)
            $this->fpsMail($division_id, $delivery_year);

        $this->action = 'showDivisionsDeliveryPlan';
        $this->showDivisionsDeliveryPlan();

    }


    // =============================================================================================================

    /**
     * calculates the deliveries to divisions per week based on total deliveries to a division in a month from showDeliveryPlan
     */
    function saveDivisionsDeliveryPlan()
    {

        $debug = 1;
        // get the quantities post variable. it is an array with the division id as key and quantity as value
        // this array is not ordered by index. order is set by the user who sorts the divisions the priority
        $quantities = $this->requestPtr->getProperty('quantities');

        $yearmonth = unserialize($this->requestPtr->getProperty('yearmonth'));
        $prev_delivery_year = '';
        if (is_array($yearmonth)) {
            foreach ($yearmonth as $single_yearmonth)
                $delivery_year = date('Y', strtotime($single_yearmonth));
            if ($prev_delivery_year != '' && $prev_delivery_year != $delivery_year) {
                $this->msgs[] = "Auslieferung für Monaten zwei verschiedenen Jahren kann nicht gleichzeitig durchgeführt werden.";
                return;
            }
            $prev_delivery_year = $delivery_year;
        } else
            $delivery_year = date('Y', strtotime($yearmonth));

        $variant_value = $this->requestPtr->getProperty('variant_value');

        // get the production plan for each weeek as a json encoded string
        $productionPlan = $this->ladeLeitWartePtr->productionPlanPtr->getGroupedForYearMonthVariant($yearmonth, $variant_value, $delivery_year);

        if (! empty($productionPlan))
            $productionQuantities = json_decode($productionPlan, true);

        ksort($productionQuantities);
        // ###
        $weeks = $this->ladeLeitWartePtr->getWeeksFromYearMonthStartingNow($yearmonth, true);

        // @todo 2016-11-08 remove fleet vehicles from being included in the Auslieferungsplan
        // // pool can also have B16 vehicles now!
        // // if(strpos($this->vehicle_variants[$variant_value],'B14')!==FALSE)
        // {
        // $pool_vehicles=$this->ladeLeitWartePtr->vehiclesPtr->getCountStsPoolVehiclesForVariant($variant_value);
        // echo $pool_vehicles.'cnt pool vehicles<br>';
        // $weeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth($yearmonth,true);
        // // current week and we are workingon the current month then assign the current week
        // if(in_array('kw'.date('W'),$weeks))
        // {
        // //continue here.. rather include the pool_vehicles in the week by week loop moving
        // //the remaining vehicles to the next week
        // $pool_vehicles_week='kw'.date('W');
        // // @todo reintroduce this and check the process again if(!isset($productionQuantities['kw'.date('W')]))
        // // $productionQuantities['kw'.date('W')]=0;
        // $productionQuantities['kw'.date('W')]+=$pool_vehicles;
        // }
        // //if not then use the first month of the month being worked on
        // else
        // {
        // $pool_vehicles_week=$weeks[0];
        // $productionQuantities[$weeks[0]]+=$pool_vehicles;
        // }

        // }
        if ($debug == 1) {
            echo '<span style="display: inline-block; width: 120px"> </span>';
            foreach ($productionQuantities as $k => $p) {
                echo '<span style="display: inline-block; width: 120px">' . $k . '-' . $p . '</span>';
            }
            echo '<br>';
        }

        $deliveryToDivisionResults = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getForYearMonthVariant($yearmonth, $variant_value, $delivery_year);
        $sum_already_assigned_vehicles = 0;
        if (! empty($deliveryToDivisionResults)) {
            foreach ($deliveryToDivisionResults as $division) {

                $division['delivery_quantities'] = json_decode($division['delivery_quantities'], true);
                $processed_divisions[] = $division['division_id'];
                foreach ($weeks as $thisweek) {
                    if (isset($division['delivery_quantities'][$thisweek])) {
                        // @todo 2016-10-14 error here.. plan saves negative values here if there are no production values for this week
                        $productionQuantities[$thisweek] -= $division['delivery_quantities'][$thisweek];
                        $sum_already_assigned_vehicles += $division['delivery_quantities'][$thisweek];
                    }
                }
            }
        }

        if ($debug == 1) {
            echo '<span style="display: inline-block; width: 120px"> </span>';
            foreach ($productionQuantities as $k => $p) {
                echo '<span style="display: inline-block; width: 120px">' . $k . '-' . $p . '</span>';
            }
            echo '<br>';
        }

        // foreach division
        $priority = 1;
        $sendmail_flag = false;
        foreach ($quantities as $key => $quantity) {
            $params = explode('_', $key);
            $division_id = $params[0];
            $yearmonth = $params[1];
            $quantity = (int) $quantity;

            if (! $quantity)
                continue;
            else {
                $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($variant_value);
                // @todo #reprocess handling reprocessing a division as well as handling backlog
                // even if this division is not getting any vehicles, set the processed status as TRUE so that this number can be compensated next month
                // $this->ladeLeitWartePtr->deliveryPlanPtr->setProcessedTrue($division_id,$yearmonth,$external_post_variant_value);
            }
            // ###
            $primary_details = array(
                'division_id' => $division_id,
                'variant_value' => $variant_value,
                'delivery_year' => $delivery_year,
                'added_timestamp' => date('Y-m-d H:i:sO')
            );

            // calculate number of weeks in the month (previously saved in the productionPlan table)
            $numweeks = sizeof($productionQuantities);

            if ($debug == 1) {
                echo '<span style="display: inline-block; width: 140px">' . $division_id . '  : ' . $quantity . '</span> ';
            }

            // number of vehicles already assigned to this division in previous weeks
            $quantity_assigned = 0;

            $calculatedDelivery = array();

            foreach ($productionQuantities as $kweek => &$kweek_quantity) {
                // round up the decimal value per week to nearest integer
                $n = ceil(($quantity - $quantity_assigned) / $numweeks);
                $x = 4;
                // round up to the nearest multiple of four http://stackoverflow.com/questions/4133859/round-up-to-nearest-multiple-of-five-in-php
                $four_multiple = (round($n) % $x === 0) ? round($n) : round(($n + $x / 2) / $x) * $x;

                $perweek = $four_multiple;
                if (($quantity - $quantity_assigned) < $four_multiple)
                    $perweek = $quantity - $quantity_assigned;
                // if delivery quantity for this division, for this month has been achieved, set to zero
                // dont change $kweek_quantity or $quantity_assigned
                /**
                 * $kweek_quantity==0 should be the correct condition.
                 * However, due to manual changes in the database
                 * sometimes we have the production quantity as 0 even though vehicle deliveries are planned for that week!
                 */
                if ($quantity_assigned == $quantity || $kweek_quantity <= 0) {
                    $calculatedDelivery[$kweek] = 0;
                } else {
                    // if the perweek quantity cannot be met with the remaining to-be-produced vehicles this month
                    if (($kweek_quantity - $perweek) < 0) {
                        // try reducing the number of vehicles to be delivered this week
                        while (($kweek_quantity - $perweek) < 0)
                            -- $perweek;

                        $calculatedDelivery[$kweek] = $perweek;
                    } // if the delivery_requirements for the division have been met already, then reduce until it matches the requirement
                    else if (($quantity_assigned + $perweek) > $quantity) {
                        while (($quantity_assigned + $perweek) > $quantity)
                            -- $perweek;

                        $calculatedDelivery[$kweek] = $perweek;
                    } 
                    else
                        $calculatedDelivery[$kweek] = $perweek;

                    // if $quantity_assigned==$quantity || $kweek_quantity==0 are not fulfilled, recalculate the remaining to-be-produced quantity and
                    // number of vehicles to be delivered to this division in previous weeks

                    $kweek_quantity -= $calculatedDelivery[$kweek];

                    $quantity_assigned += $calculatedDelivery[$kweek];
                }
                if ($debug == 1) {
                    echo '<span style="display: inline-block; width: 120px">' . $four_multiple . '->' . $calculatedDelivery[$kweek] . '</span>';
                }
                $newdata = array_merge($primary_details, array(
                    'delivery_week' => $kweek,
                    'delivery_quantity' => $calculatedDelivery[$kweek],
                    'priority' => $priority
                ));
                $result = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getForDivisionYearWeekVariant($division_id, date('Y', strtotime($yearmonth)), $kweek, $variant_value);
                if (! empty($result)) {
                    // pending deliveries
                    // works in case in August there were a couple of deliveries pending and in september they need to be delivered.
                    // however the priorities of the current month delivery is overwritten and instead it takes the value from the previous month priority
                    $newdata['delivery_quantity'] += $result['delivery_quantity'];
                    $newdata['priority'] = $result['priority'];
                    $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
                        ->where('delivery_id', '=', $result['delivery_id'])
                        ->update(array_keys($newdata), array_values($newdata));
                    $sendmail_flag = true;
                } else {
                    $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()->insert($newdata);
                    $sendmail_flag = true;
                }
                // reduce the number of weeks still available so that we can calculate the $four_multiple for the remaining weeks
                $numweeks --;
            }
            $priority ++;
            if ($debug == 1) {
                echo 'sum' . $quantity_assigned . '<br>';
            }

            if ($sendmail_flag === true) {
                $this->fpsMail($division_id, $delivery_year);
            }
        }
        $this->action = 'showDivisionsDeliveryPlan';
        $this->showDivisionsDeliveryPlan();

    }


    function fpsMail($division_id, $delivery_year)
    {

        // gets station count grouped by division
        $division = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsToBeAssignedCountForDiv($division_id);
        $this->mailcontent = '';
        $delivery_quantity_total = array();

        $deliveryStr = '';

        // $weeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01'),true);
        $weeks = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getDeliveriesForNotification($delivery_year);

        // start quick fix for B16 delivery in March 2017
        /*
         * $thistime=strtotime("first day of +1 months");
         * $nextmonth_weeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01',$thistime),true);
         * $weeks=array_merge($weeks,$nextmonth_weeks);
         */
        // end quick fix for B16 delivery in March 2017

        foreach ($weeks as $kweek) {
            $delivery_quantity_total = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getSumQtyAllVariantsForWeekAndDiv($kweek, $division_id);

            // @todo cron job here to check and send email
            if (! empty($delivery_quantity_total) && $delivery_quantity_total != 0) 
            // && $division['scnt']>=$delivery_quantity_total
            {

                $delivery_quantity_by_variant = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getQtyAllVariantsForWeekAndDiv($kweek, $division_id);

                foreach ($delivery_quantity_by_variant as $key => $single) {
                    if ($single['delivery_notification_email_sent'] == 't' || $single['delivery_quantity'] == 0)
                        continue;

                    // if($key==0)
                    $deliveryStr .= '<strong>' . strtoupper($kweek) . '</strong><br>';

                    $variant_name = $this->vehicle_variants[$single['variant_value']];
                    $deliveryStr .= 'Fahrzeugkonfiguration ' . $variant_name . ' : ' . $single['delivery_quantity'] . '<br>';
                    $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
                        ->where('delivery_id', '=', $single['delivery_id'])
                        ->update(array(
                        'delivery_notification_email_sent'
                    ), array(
                        't'
                    ));
                }
            }
        }

        if ($deliveryStr != '') {
            $this->mailcontent .= '<h2>' . $division['name'] . '</h2>';
            $this->mailcontent .= 'Freie Ladepunkten: ' . $division['scnt'] . '<br>';

            $mailmsg = str_replace(array(
                '{deliveryStr}'
            ), array(
                $deliveryStr
            ), $this->emailtext);
            $this->mailcontent .= $mailmsg;

            $fps_list = $this->ladeLeitWartePtr->allUsersPtr->getFPSEmails($division_id);
            $fps_emails = array();
            foreach ($fps_list as $fps) {
                if (! isset($fps['fname']))
                    $fps['fname'] = '';
                if (! isset($fps['lname']))
                    $fps['lname'] = '';
                if (! empty($fps['email']))
                    $fps_emails[$fps['email']] = $fps['fname'] . '  ' . $fps['lname'];
            }

            $extraemails = null;
            if (empty($division['name'])) {
                $division['name'] = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
                    ->where('division_id', '=', $division_id)
                    ->getVal('name');
            }
            $extraemails = array(
                'Philipp.Schnelle@streetscooter.eu',
                'Wilfried.Baltruweit@streetscooter.eu',
                'Pradeep.Mohan@streetscooter.eu',
                'Ismail.Sbika@streetscooter.eu',
                'thorben.doum@streetscooter.eu'
            );
            if (! empty($fps_emails)) {
                $mailer = new MailerSmimeSwift($fps_emails, '', 'StreetScooter Fahrzeuge Auslieferung - Niederlassung ' . $division['name'], $mailmsg, null, true, $extraemails);
            }

            $this->mailcontent .= '<br><a href="' . $_SERVER['PHP_SELF'] . '?action=auszulieferende">Auszulieferende Fahrzeuge an Ladepunkte zuordnen</a><br>
								Mail an : ' . implode(',', $fps_emails) . '<br>';
        }

    }


    /**
     *
     * @param array $selectColsParam
     * @param string $dbColParam
     * @param string $startidParam
     * @param string $endidParam
     */
    function saveexportcsv($selectColsCheck = '', $dbColParam = '', $startidParam = '', $endidParam = '')
    {

        $this->listofoptions = '';

        if (empty($selectColsCheck))
            $selectColsCheck = $this->requestPtr->getProperty('selectColsCheck');

        $selectColsHeadings = array(
            'tsnumber' => 'TS Nummer',
            'penta_kennwort' => 'Penta Kennwort',
            'penta_number' => 'Penta Artikel',
            'vin' => 'VIN',
            'code' => 'Kennzeichen',
            'ikz' => 'IKZ',
            'delivery_date' => 'Auslieferungsdatum',
            'delivery_week' => 'Auslieferungswoche',
            'coc' => 'CoC Nr.',
            'vorhaben' => 'Vorhaben',
            'dname' => 'Zugeordnet ZSP'
        );

        if (empty($selectColsCheck))
            $selectColsCheck = array_keys($selectColsHeadings);

        $selectColsCheckRaw = $selectColsCheck;

        $vehicleHeadings = array();

        foreach ($selectColsCheck as $selectCol) {
            $vehicleHeadings[] = $selectColsHeadings[$selectCol];
        }

        foreach ($selectColsCheck as &$selectCol) {
            if ($selectCol == 'dname')
                $selectCol = 'depots.name as dname';
            if ($selectCol == 'vin')
                $selectCol = 'vehicles.vin';
            if ($selectCol == 'code')
                $selectCol = 'vehicles.code';
            if ($selectCol == 'ikz')
                $selectCol = 'vehicles.ikz';
        }
        if (empty($dbColParam))
            $dbColParam = $this->requestPtr->getProperty('db_col');
        if (empty($startidParam))
            $startidParam = $this->requestPtr->getProperty('startval');
        if (empty($endidParam))
            $endidParam = $this->requestPtr->getProperty('endval');

        if ($dbColParam != 'null') {
            $fname = "/tmp/vin_numbers.csv";
            $fhandle = fopen($fname, "w");
            $fcontent = array();
            $fcontent[] = implode(',', $vehicleHeadings);
            $db_col = $dbColParam;
            $startval = $startidParam;

            if (! empty($endidParam) && $dbColParam != 'vorhaben') {
                $endval = $endidParam;
                $start_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where($db_col, '=', $startval)
                    ->getVal('vehicle_id');
                $end_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where($db_col, '=', $endval)
                    ->getVal('vehicle_id');
                $vehicle_ids = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vehicle_id', '>=', $start_vehicle_id)
                    ->where('vehicle_id', '<=', $end_vehicle_id)
                    ->get('vehicle_id');

                if (! empty($vehicle_ids))
                    $vehicle_ids = array_column($vehicle_ids, 'vehicle_id');

                $vehicles = $this->ladeLeitWartePtr->vehiclesSalesPtr->getVehicleOverview($selectColsCheck, $vehicle_ids, 'ASC');
            } else {
                $vehicle_ids = $this->ladeLeitWartePtr->vehiclesPtr->newQuery('vehicles_sales')
                    ->where('vehicles_sales.vorhaben', '=', $startval)
                    ->get('vehicle_id');

                if (! empty($vehicle_ids))
                    $vehicle_ids = array_column($vehicle_ids, 'vehicle_id');

                $vehicles = $this->ladeLeitWartePtr->vehiclesSalesPtr->getVehicleOverview($selectColsCheck, $vehicle_ids);
            }
        } else // $dbColParam can never be null, right? needs to be reviewed
        {
            $vehicle_ids = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where('vehicle_id', '>=', $startidParam)
                ->where('vehicle_id', '<=', $endidParam)
                ->get('vehicle_id');

            if (! empty($vehicle_ids))
                $vehicle_ids = array_column($vehicle_ids, 'vehicle_id');

            $vehicles = $this->ladeLeitWartePtr->vehiclesSalesPtr->getVehicleOverview($selectColsCheck, $vehicle_ids, 'ASC');
        }

        if (! empty($vehicles)) {
            foreach ($vehicles as &$vehicle) {
                unset($vehicle['color_id']);
                unset($vehicle['vehicle_variant']);
                $processedVehicle = array();
                foreach ($selectColsCheckRaw as $column_name) {
                    $processedVehicle[$column_name] = $vehicle[$column_name];
                }

                $fcontent[] = implode(",", $processedVehicle);
            }

            fwrite($fhandle, implode("\r\n", $fcontent) . "\r\n");
            fclose($fhandle);
            $this->listofoptions .= '<span class="s_exportcsv"><a class="exportcsv" href="/downloadcsv.php?fname=vin_numbers">CSV Datei herunterladen</a></span><br>';
        } else {
            $this->listofoptions .= 'Keine Fahrzeuge von vehicle_id ' . $startval . ' bis ' . $endval . ' gefunden!';
        }

        return $this->listofoptions;

        // return only when calling this functions from the vin generation function
    }


    function exportcsv()
    {

        $selectCols = array(
            'tsnumber' => 'TS Nummer',
            'penta_kennwort' => 'Penta Kennwort',
            'penta_number' => 'Penta Artikel',
            'vin' => 'VIN',
            'code' => 'Kennzeichen',
            'ikz' => 'IKZ',
            'delivery_date' => 'Auslieferungsdatum',
            'delivery_week' => 'Auslieferungswoche',
            'coc' => 'CoC Nr.',
            'vorhaben' => 'Vorhaben',
            'dname' => 'Zugeordnet ZSP'
        );

        // $this->vehicles=$this->ladeLeitWartePtr->vehiclesSalesPtr->getVehicleOverview($selectCols);
        $this->csvTemplates = $this->ladeLeitWartePtr->csvTemplatesPtr->getAll();

        $templates = array(
            'null' => 'Keine Vorlage'
        );
        foreach ($this->csvTemplates as $template) {
            $templates[$template['template_id']] = $template['template_name'];
        }

        $this->qform_csv = new QuickformHelper($this->displayHeader, 'exportcsv_fahrzeuge');
        $this->qform_csv->getSalesExportCSVOptions($selectCols, $templates);

    }


    function exportxml()
    {

        $this->qform_xml = new QuickformHelper($this->displayHeader, 'exportxml_fahrzeuge');
        $this->qform_xml->exportXMLoptions('saveexportxml');

    }

    function exportpdf()
    {

        $this->qform_pdf = new QuickformHelper($this->displayHeader, 'exportpdf_fahrzeuge');
        $this->qform_pdf->exportPDFoptions('saveexportpdf');

    }

    function begleitschein()
    {

        $this->qform_pdf = new QuickformHelper($this->displayHeader, 'exportpdf_fahrzeuge');
        $this->qform_pdf->exportPDFoptions('saveexportpdf');

    }

    function gencoc()
    {

        /*
         * $this->qform_pdf=new QuickformHelper($this->displayHeader, 'gencoc_pdf');
         * $this->qform_pdf->exportCOCoptions('gencoc_pdf',$this->person_designation);
         */
        $cocWriter = new AClass_Coc();
        $cocWriter->Execute();
        $this->page = $cocWriter->GetHtml_StandardForm();

    }


    function gencoc_pdf()
    {


        /*
         * // if arguments are empty, then get it from the POST variables
         * if(empty($dbColParam))
         * $dbColParam=$this->requestPtr->getProperty('db_col');
         * if(empty($startidParam))
         * $startidParam=$this->requestPtr->getProperty('startval');
         * if(empty($endidParam))
         * $endidParam=$this->requestPtr->getProperty('endval');
         *
         * $flnumber=(int) $this->requestPtr->getProperty('startcoc');
         * $sitzer=$this->requestPtr->getProperty('sitzer');
         * $aufbau=$this->requestPtr->getProperty('aufbau');
         * $person_designation =$this->requestPtr->getProperty('person_designation');
         *
         * $db_col=$dbColParam;
         * $startval=$startidParam;
         *
         * if(!empty($endidParam))
         * {
         * $endval=$endidParam;
         * $vehicles=$this->ladeLeitWartePtr->vehiclesPtr->newQuery()
         * ->where($db_col,">=",$startval)
         * ->where($db_col,"<=",$endval)
         * ->orderBy($db_col,'ASC')
         * ->get('vehicle_id,vin,code,ikz,vin');
         * }
         * else
         * {
         *
         * $vehicles=$this->ladeLeitWartePtr->vehiclesPtr->newQuery()
         * ->join('vehicles_sales','vehicles_sales.vehicle_id=vehicles.vehicle_id','INNER JOIN')
         * ->where("vehicles_sales.vorhaben","=",$startval)
         * ->get('vehicles.vehicle_id,vin,code,vehicles.ikz,vehicles.vin');
         * }
         *
         * $pdf_merge_str='';
         * foreach($vehicles as $vehicle)
         * {
         * $flnumber_str=str_pad((string) $flnumber,4,'0',STR_PAD_LEFT);
         *
         * $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()->where('vehicle_id','=',$vehicle['vehicle_id'])->update(array('coc'),array($flnumber_str));
         *
         * $fhandle=fopen('/var/www/coc_template.fods', 'r');
         * $fcontent=fread($fhandle, filesize('/var/www/coc_template.fods'));
         * $fhandle_new=fopen('/var/www/coc_'.$vehicle['vin'].'_ohneAKZ.fods', 'w');
         *
         * $variant_name=$vorhaben=$vehicle_variant='';
         *
         * if(isset($vehicle['vorhaben'])) $vorhaben=$vehicle['vorhaben'];
         * if(isset($vehicle['vehicle_variant']))
         * {
         * $vehicle_variant=$vehicle['vehicle_variant'];
         * if(isset($this->vehicle_variants[$vehicle['vehicle_variant']]))
         * $variant_name=$this->vehicle_variants[$vehicle['vehicle_variant']];
         * }
         *
         * $cocversions=array('einsitz'=>'X21ABABC',
         * 'zweisitz'=>'X21BBABC');
         *
         * $cocvariants=array('koffer'=>'B16BAAEX11',
         * 'fahrgestell'=>'B16BXBEX11');
         *
         * $compartment_kind=array('koffer'=>'BA',
         * 'fahrgestell'=>'BX');
         * $number_of_seats=array('einsitz'=>1,
         * 'zweisitz'=>2);
         *
         * $coc_infos=$this->ladeLeitWartePtr->vehicleVariantsPtr->getCOCDetails($number_of_seats[$sitzer],$compartment_kind[$aufbau]);
         *
         * $replacevals=array('VIN_HERE_VIN'=>$vehicle['vin'],
         * 'PERMIT_HERE_PERMIT'=>$coc_infos['approval_code'],
         * 'YEAR_HERE_YEAR'=>date('Y'),
         * 'DATE_HERE_DATE'=>date('j.m.Y'),
         * 'SNO_HERE_SNO'=>$flnumber_str,
         * 'CODE_HERE_CODE'=>'',
         * 'HSN_HERE_HSN'=>$coc_infos['hsn'],
         * 'TSN_HERE_TSN'=>$coc_infos['tsn'],
         * // "5.Länge:"
         * 'LEN_HERE_LEN'=>$coc_infos['length'],
         * // "6. Breite:"
         * 'WIDTH_HERE_WIDTH'=>$coc_infos['width'],
         * // "7. Höhe:"
         * 'HEIGHT_HERE_HEIGHT'=>$coc_infos['height'],
         * // "11. Länge der Ladefläche:"
         * 'length_cargo_area'=>$coc_infos['length_cargo_area'],
         * // "13. Masse Fz. Fahrbereit:" (zu berechnen)
         * 'mass_ready_to_start'=>$coc_infos['mass_ready_to_start_min'].'-'.$coc_infos['mass_ready_to_start_max'],
         * // "38. Art des Aufbaus:"
         * 'HERE_compartment_kind_HERE'=>$coc_infos['compartment_kind'],
         * // "42. Anz. Sitzpl."
         * 'number_of_seats'=>$coc_infos['number_of_seats'],
         * // "4 Amtl. Aufbau:"
         * 'HERE_official_compartment_kind_HERE'=>$coc_infos['official_compartment_kind'],
         * // Name in COC-Papier
         * // 'title_name_title'=>$coc_infos['name'], not needed..remove it
         * // "2.2. VV/PZ:"
         * 'vv_pz'=>$coc_infos['vv_pz'],
         * // "0.2. Version:"
         * 'VERSION_HERE_VERSION'=>$coc_infos['version'], //$cocversions[$sitzer],
         * // "0.2. Variante:"
         * 'VARIANT_HERE_VARIANT'=>$coc_infos['variant'], //$cocvariants[$aufbau],
         * // 5.Amtl. Text Aufbau
         * 'official_compartment_text'=>$coc_infos['official_compartment_text'], //$cocvariants[$aufbau],
         * 'PERSON_HERE_PERSON'=>$this->person_designation[$person_designation]['person'],
         * 'DES_HERE_DES'=>$this->person_designation[$person_designation]['designation']
         *
         * );
         *
         * $fcontent=str_replace(array_keys($replacevals),array_values($replacevals),$fcontent);
         *
         * fwrite($fhandle_new,$fcontent);
         * fclose($fhandle);
         * fclose($fhandle_new);
         * exec('libreoffice --headless --convert-to pdf '.'/var/www/coc_'.$vehicle['vin'].'_ohneAKZ.fods --outdir /var/www/');
         *
         * $fhandle=fopen('/var/www/coc_template.fods', 'r');
         * $fcontent=fread($fhandle, filesize('/var/www/coc_template.fods'));
         * $fhandle_new=fopen('/var/www/coc_'.$vehicle['vin'].'.fods', 'w');
         * //this one is with the AKZ
         * $replacevals['CODE_HERE_CODE']=$vehicle['code'];
         *
         * $fcontent=str_replace(array_keys($replacevals),array_values($replacevals),$fcontent);
         *
         * fwrite($fhandle_new,$fcontent);
         * fclose($fhandle);
         * fclose($fhandle_new);
         * exec('libreoffice --headless --convert-to pdf '.'/var/www/coc_'.$vehicle['vin'].'.fods --outdir /var/www/');
         * $pdf_merge_str.='/var/www/coc_'.$vehicle['vin'].'_ohneAKZ.pdf ';
         * $pdf_merge_str.='/var/www/coc_'.$vehicle['vin'].'.pdf ';
         * $flnumber++;
         * }
         *
         * if($pdf_merge_str)
         * {
         * $pdfmerged='coc_'.date('Y-m-j_h_i_s');
         * exec('pdftk '.$pdf_merge_str.' cat output /var/www/'.$pdfmerged.'.pdf');
         * }
         *
         * foreach($vehicles as $vehicle)
         * {
         * if(file_exists('/var/www/coc_'.$vehicle['vin'].'.fods'))
         * unlink('/var/www/coc_'.$vehicle['vin'].'.fods');
         * if(file_exists('/var/www/coc_'.$vehicle['vin'].'_ohneAKZ.fods'))
         * unlink('/var/www/coc_'.$vehicle['vin'].'_ohneAKZ.fods');
         *
         * }
         * $this->cocLink='<a href="/downloadlieferschein.php?fname='.$pdfmerged.'" >COC herunterladen</a><br>';
         *
         * $this->action='gencoc';
         * $this->gencoc();
         */
    }


    function fahrzeuge_zuweisen()
    {

        if (time() >= $this->start_time && time() <= $this->end_time) {
            if (! isset($_POST['date_selector_delivery'])) {
                $this->qform_vehicles_deliver_request = new QuickformHelper($this->displayHeader, 'fahrzeuge_ausliefern');
                $this->qform_vehicles_deliver_request->qform_vehicles_deliver_request('send_email_fahrzeuge_zuweisen');
            }
        } else {
            $this->msgs[] = 'Funktion nur zwischen 10.00 Uhr und 17.00 Uhr verfügbar!';
        }

    }


    function tempSaveKBOB()
    {

        $vehicle_id = $this->requestPtr->getProperty('vehicle_id');
        $delivery_id = $this->requestPtr->getProperty('delivery_id');
        $divisionsDeliveryPlan = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
            ->where('delivery_id', '=', $delivery_id)
            ->getOne('vehicles_delivered,delivery_quantity,vehicles_delivered_quantity,delivery_id');

        $divisionsDeliveryPlan['vehicles_delivered'] = unserialize($divisionsDeliveryPlan['vehicles_delivered']);
        $divisionsDeliveryPlan['vehicles_delivered'][] = $vehicle_id;
        $divisionsDeliveryPlan['vehicles_delivered'] = serialize($divisionsDeliveryPlan['vehicles_delivered']);
        $divisionsDeliveryPlan['vehicles_delivered_quantity'] ++;

        $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()
            ->where('delivery_id', '=', $divisionsDeliveryPlan['delivery_id'])
            ->update(array_keys($divisionsDeliveryPlan), array_values($divisionsDeliveryPlan));
        $vin = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('vin');
        echo $vin . ' : ' . ($divisionsDeliveryPlan['delivery_quantity'] - $divisionsDeliveryPlan['vehicles_delivered_quantity']);
        exit(0);

    }


    function showorder($vehicle_variant)
    {

        $this->showorder_content = '<form action="" method="post">';
        $pendingDivisions = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getPendingForYearVariant(date('Y'), $vehicle_variant);
        $assigned_stations = array();

        $moved_quantities = array();

        if (empty($pendingDivisions)) {
            $this->msgs[] = 'Keine Auslieferungsplan gespeichert!';
            return;
        }

        $transporters = $this->ladeLeitWartePtr->newQuery('transporter')->get('transporter_id,name');
        $transporters_list = array();
        if (! empty($transporters)) {
            $transporters_list = array_combine(array_column($transporters, 'transporter_id'), array_column($transporters, 'name'));
        }

        $this->qform_save_transporter_date = new QuickformHelper($this->displayHeader, 'transporter_save_date');
        $this->qform_save_transporter_date->set_transporter_date($transporters_list);

        foreach ($pendingDivisions as $division) {
            $division_name = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
                ->where('division_id', '=', $division['division_id'])
                ->getVal('name');
            $pendingquantity = $division['delivery_quantity'] - $division['vehicles_delivered_quantity'];

            $stations = $this->ladeLeitWartePtr->stationsPtr->newGetFreeStationsVariantDiv($division['division_id'], $division['variant_value'], $assigned_stations);
            // $depots=$this->ladeLeitWartePtr->stationsPtr->getFreeStationsAlreadyVariantAssignedForDiv($division['division_id'],$division['variant_value']);

            if (! empty($stations)) {
                if (isset($moved_quantities[$division['division_id']]))
                    $moved = $moved_quantities[$division['division_id']];
                else
                    $moved = 0;

                $totalquantity = $pendingquantity + $moved;
                if ($vehicle_variant == 1 || $vehicle_variant == 2 || $vehicle_variant == 9)
                    $bundle_qty = 4;
                else
                    $bundle_qty = 3;

                $quotient = floor($totalquantity / $bundle_qty);

                $moved_quantities[$division['division_id']] = $totalquantity % $bundle_qty;

                $actual = $quotient * $bundle_qty;

                $cnt = 0;
                $lastdepot = '';

                // start quick fix for July August 2017 deliveries
                // disabling this check for this month if($totalquantity<$bundle_qty) continue;
                if (empty($stations))
                    continue;
                // end quick fix for July August 2017 deliveries

                $add_vehicle = '';
                if ($this->user->getUserName() == 'Sts.Sales') {
                    $add_vehicle = '<a class="show_vehicles_kbob parent_hidden_text " data-target="wrap_kbob_' . $division['delivery_id'] . '" style="margin: 2px 0">
                                        <span class="genericon genericon-plus"></span>KBOB Delivery</a>';
                    $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                        ->where('depots.division_id', '=', $division['division_id'])
                        ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
                        ->get('vehicle_id,vin');
                    $add_vehicle .= '<div class="wrap_kbob_' . $division['delivery_id'] . '" style="display: none"><br><select name="kbob_vehicle_' . $division['delivery_id'] . '" class="kbob_vehicles"> ';
                    foreach ($vehicles as $vehicle) {
                        $add_vehicle .= '<option value=' . $vehicle['vehicle_id'] . '>' . $vehicle['vin'] . '</option>';
                    }
                    $add_vehicle .= '</select>';
                    $add_vehicle .= '<a class="save_vehicle_kbob" data-delivery_id="' . $division['delivery_id'] . '" style="margin: 2px 0 2px 32px">
                                        <span class="genericon genericon-checkmark"></span></a></div>';
                }

                $this->showorder_content .= '<h2>' . $division_name . ' (' . $division['delivery_week'] . ')  Auszulieferende Fahrzeuge : ' . $pendingquantity . ' + ' . $moved . '</h2>' . $add_vehicle . '<br>';

                foreach ($stations as $station) {
                    if (($totalquantity >= $bundle_qty && $actual > $cnt) || ($totalquantity < $bundle_qty && $totalquantity > $cnt)) // start quick fix for July August 2017 deliveries replacing if $actual > $cnt
                    {
                        if ($lastdepot != $station['depot_id'] && $lastdepot != '') {
                            $prevdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($lastdepot);
                            $thisdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($station['depot_id']);

                            $distance = $this->getDistance($prevdepot['lat'], $prevdepot['lon'], $thisdepot['lat'], $thisdepot['lon']);

                            $this->showorder_content .= '<br>Entfernung zwischen ' . $prevdepot['name'] . ' und ' . $thisdepot['name'] . '  :  <strong>' . ceil($distance) . ' km</strong><br><br>';
                        }
                        $assigned_stations[] = $station['station_id'];
                        $listno = $cnt + 1;
                        // $this->showorder_content.=$listno.'. '.$station['dname'].' ('.$station['dp_depot_id'].') <br>';

                        /**
                         * start edit for showing the transporter_order date *
                         */
                        $transporter_dates = $this->ladeLeitWartePtr->newQuery('transporter_dates')
                            ->where('station_id', '=', $station['station_id'])
                            ->get('transporter_date_id,transporter_id,transporter_date');

                        $existing_transporter_dates = '';

                        if (! empty($transporter_dates)) {
                            // $existing_transporter_dates='<table style="width:80%" ><tr><th>Spediteur</th><th>Angefragt am</th></tr>';
                            $existing_transporter_dates = '';
                            foreach ($transporter_dates as $transporter_date) {
                                $existing_transporter_dates .= '';
                                $transporter_date_value = date('d.m.Y', strtotime($transporter_date['transporter_date']));
                                $transporter_id = $transporter_date['transporter_id'];
                                $delete_ctrl = '<a class="show_delete_data" style="margin; 2px 0"><span class="genericon genericon-close"></span></a> ';
                                $delete_string = '<a class="delete_date" id="delete_date_' . $transporter_date['transporter_date_id'] . '" style="display:none" data-transporter_date_id="' . $transporter_date['transporter_date_id'] . '">löschen</a>';
                                $existing_transporter_dates .= '<span id="wrap_trdate_' . $transporter_date['transporter_date_id'] . '"><strong>' . $transporters_list[$transporter_id] . '</strong> am ' . $transporter_date_value . $delete_ctrl . $delete_string . '<br></span>';
                            }
                            // $existing_transporter_dates.='</table>';
                        }

                        $info = '<label for="transporter_' . $station['station_id'] . '">' . $listno . '. ' . $station['dname'] . ' (' . $station['dp_depot_id'] . ')
                                   <input type="checkbox" class="station" data-station_id="' . $station['station_id'] . '"
                                    style="margin:0; vertical-align:bottom" id="transporter_' . $station['station_id'] . '"> </label>';

                        $this->showorder_content .= '<div class="row"><div class="column one-half">' . $info . '</div>';
                        $this->showorder_content .= '<div class="column one-half existing_dates">' . $existing_transporter_dates . '</div></div>';

                        // $this->showorder_content.=$info.'<br><br>'.$existing_transporter_dates.'<br>';
                        /**
                         * end edit for showing the transporter_order date *
                         */
                        $lastdepot = $station['depot_id'];
                        $cnt ++;
                    }
                }
            }
        }

        $this->showorder_content .= '</form>';

    }


    function ajaxTransporterDateDelete()
    {

        $transporter_date_id = $this->requestPtr->getProperty('transporter_date_id');
        $result = $this->ladeLeitWartePtr->newQuery('transporter_dates')
            ->where('transporter_date_id', '=', $transporter_date_id)
            ->delete();
        if ($result)
            echo json_encode(array(
                'transporter_date_id' => $transporter_date_id
            ));
        else
            echo json_encode(array(
                'result' => 0
            ));
        exit(0);

    }


    /**
     * Transporter Anfrage Datum Function, called when #save_transporter_order_date is clicked
     * Saves the transporter_date to the transporter_dates table
     * echoes the station_id and msg as json to be parsed by javascript
     */
    function ajaxTransporterDateSave()
    {

        $transporter_id = $this->requestPtr->getProperty('transporter_id');
        $transporter_name = $this->requestPtr->getProperty('transporter_name');
        $station_id = $this->requestPtr->getProperty('station_id');
        $transporter_date = $this->requestPtr->getProperty('transporter_date');
        $transporter_date_time = strtotime($transporter_date);
        $transporter_data_timestamp = date('Y-m-d 00:00:00O', $transporter_date_time);
        $transporter_date_id = $this->ladeLeitWartePtr->newQuery('transporter_dates')->insert(array(
            'station_id' => $station_id,
            'transporter_id' => $transporter_id,
            'transporter_date' => $transporter_data_timestamp
        ));
        $result = array();
        $result['station_id'] = $station_id;
        $delete_ctrl = '<a class="show_delete_data" style="margin; 2px 0"><span class="genericon genericon-close"></span></a> ';
        $delete_string = '<a class="delete_date" style="display:none" id="delete_date_' . $transporter_date_id . '" data-transporter_date_id="' . $transporter_date_id . '">löschen</a>';
        $result['msg'] = '<span id="wrap_trdate_' . $transporter_date_id . '"><strong>' . $transporter_name . '</strong> am ' . date('d.m.Y', $transporter_date_time) . $delete_ctrl . $delete_string . '<br></span>';
        echo json_encode($result);
        exit(0);

    }


    function send_email_fahrzeuge_zuweisen()
    {

        $date_selector_delivery = $this->requestPtr->getProperty('date_selector_delivery');
        $count_vehicles = $this->requestPtr->getProperty('count_vehicles');
        $mailmsg = "Bitte QS geprüfte Fahrzeuge zuweisen.\r\n\r\n" . "Anlieferungsdatum : " . $date_selector_delivery . "\r\n" . "Anzahl der zuzuweisende Fahrzeuge: " . $count_vehicles . "\r\n";

        if (time() >= $this->start_time && time() <= $this->end_time) {
            $emails = array(
                'Lothar.Juergens@streetscooter.eu',
                'Pradeep.Mohan@streetscooter.eu',
                'Ismail.Sbika@streetscooter.eu',
                'Leon.Schottdorf@streetscooter.eu'
            );
            $mailer = new MailerSmimeSwift($emails, '', 'Auslieferung Request von Sales', $mailmsg, null, true);
        } else {
            $this->msgs[] = 'Funktion nur zwischen 10.00 Uhr und 17.00 Uhr verfügbar!';
        }
        $this->action = 'fahrzeuge_zuweisen';
        $this->fahrzeuge_zuweisen();

    }


    function genpdf()
    {

        $this->qform_pdf = new QuickformHelper($this->displayHeader, 'exportpdf_fahrzeuge');
        $this->qform_pdf->exportPDFoptions('saveExportLieferschein');

    }


    function processVehiclesToDeliver(&$vehicles, $selected_vehicles = false, $delivery_dates = null)
    {

        // continue here parse the delivery_dates
        $result['headers'] = explode(',', 'vin,code,zsp,delivery_week,delivery_date,delivery_status,production_location,select_vehicle,vehicle_reset,vehicle_exchange');

        $production_depots = $this->ladeLeitWartePtr->depotsPtr->newQuery('')
            ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
            ->where('divisions.production_location', '=', 't')
            ->get('depot_id,depots.name as dname');
        $production_depots = array_combine(array_column($production_depots, 'depot_id'), array_column($production_depots, 'dname'));

        $sts_pool = $this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
        $pool_depot_id = $sts_pool['depot_id'];

        foreach ($vehicles as &$vehicle) {
            if ($selected_vehicles)
                $classname = 'to_lock';
            else
                $classname = '';

            $vehicle['vin'] = '<span class=' . $classname . '>' . $vehicle['vin'] . '</span>';
            $vehicle['code'] = '<span class=' . $classname . '>' . $vehicle['code'] . '</span>';
            if ($vehicle['delivery_status'] == 't')
                $vehicle['delivery_status'] = 'Ja';
            else
                $vehicle['delivery_status'] = 'Nein';
            $vehicle['zsp'] = $vehicle['depot_name'] . '(' . $vehicle['dp_depot_id'] . ')';

            if (isset($vehicle['production_location']) && ! empty($vehicle['production_location'])) {
                $vehicle['production_location'] = $production_depots[$vehicle['production_location']];
            } else if (isset($vehicle['production_date']) && isset($vehicle['qs_user'])) {
                if ($vehicle['qs_user'] == - 1)
                    $vehicle['production_location'] = $production_depots[$pool_depot_id];
                else
                    $vehicle['production_location'] = 'Nicht Zugewiesen/Düren';
            } else
                $vehicle['production_location'] = $production_depots[$pool_depot_id];

            if ($selected_vehicles !== false)
                $check_param = ' checked="checked" ';
            else
                $check_param = ' ';
            $vehicle['select_vehicle'] = '<label for="save_delivery_ctrl' . $vehicle['vehicle_id'] . '">
                                        <input type="checkbox" ' . $check_param . ' data-vehicleid="' . $vehicle['vehicle_id'] . '" class="save_delivery_ctrl" name="save_delivery_ctrl' . $vehicle['vehicle_id'] . '" id="save_delivery_ctrl' . $vehicle['vehicle_id'] . '">
                                         wählen</label>';
            if (isset($delivery_dates[$vehicle['vehicle_id']])) {
                $formatted_date = $delivery_dates[$vehicle['vehicle_id']];
            } else if (isset($vehicle['delivery_date']) && ! empty($vehicle['delivery_date'])) {
                $formatted_date = date('d.m.Y', strtotime($vehicle['delivery_date']));
            } else
                $formatted_date = '';

            $vehicle['delivery_date'] = '<input type="text" name="deliverydate[' . $vehicle['vehicle_id'] . ']" id="delivery_date_' . $vehicle['vehicle_id'] . '" class="delivery_date_input" value="' . $formatted_date . '">';
            $vehicle['vehicle_reset'] = '<a href="?action=fahrzeug_zuruck&vehicle_id=' . $vehicle['vehicle_id'] . '" class="require_confirm" data-confirmtxt="Fahrzeug wird wieder ins Produktion/Aftersales zurückgenommen und wird wieder in QS geprüfte Fahrzeuge auftauchen.">Zurücksetzen</a>';
            $vehicle['vehicle_exchange'] = '<a href="?action=fahrzeug_tauschen&vehicle_id=' . $vehicle['vehicle_id'] . '" >Tauschen</a>';

            unset($vehicle['dp_depot_id']);
            unset($vehicle['depot_name']);
            unset($vehicle['vehicle_variant']);
            unset($vehicle['vehicle_id']);
            unset($vehicle['production_date']);
            unset($vehicle['qs_user']);
        }

    }


    function ajaxRowsDelivery()
    {

        $result = array();
        $page = $this->requestPtr->getProperty('page');
        $size = $this->requestPtr->getProperty('size');
        $fcol = $this->requestPtr->getProperty('filter');
        $scol = $this->requestPtr->getProperty('column'); // 1 desc 0 asc
        $vchecked = $this->requestPtr->getProperty('vchecked');

        if (! empty($vchecked))
            $vchecked = explode(',', $vchecked);

        $result['headers'] = explode(',', 'vin,code,zsp,delivery_week,delivery_date,production_location,select_vehicle,delivery_status,vehicle_reset,vehicle_exchange');
        //
        $selected_vehicles = array();
        if (! empty($vchecked)) {
            $vchecked_dates = $this->requestPtr->getProperty('vchecked_dates');
            $vchecked_dates = json_decode($vchecked_dates, true);
            $delivery_dates = array();
            foreach ($vchecked_dates as $vehicle_date) {
                $delivery_dates[$vehicle_date['vehicle_id']] = $vehicle_date['delivery_date'];
            }
            $selected_vehicles = $this->ladeLeitWartePtr->vehiclesPtr->ajaxGetVehiclesToDeliver(0, sizeof($vchecked), null, null, $vchecked);
            $this->processVehiclesToDeliver($selected_vehicles, true, $delivery_dates);
        }
        if (empty($selected_vehicles))
            $selected_vehicles = array();

        $rows = $this->ladeLeitWartePtr->vehiclesPtr->ajaxGetVehiclesToDeliver($page, $size, $fcol, $scol, null, $vchecked);
        $this->processVehiclesToDeliver($rows);

        if (empty($rows))
            $rows = array();

        $rows = array_merge($selected_vehicles, $rows);

        $result['total_rows'] = $this->totalDeliveryRows;
        $result['fcol'] = json_encode($fcol);
        $result['page'] = $page;
        $result['size'] = 20;
        $result['rows'] = $rows;

        echo json_encode($result);
        exit(0);

    }


    function delivery()
    {

        $headers = array(
            'VIN',
            'AKZ',
            'ZSP',
            'Auslieferungswoche',
            'Anlieferungsdatum',
            array(
                'Produktionsort',
                array(
                    'data-filter' => 'false',
                    'data-sorter' => 'false'
                )
            ),
            array(
                'Auswählen',
                array(
                    'data-filter' => 'false',
                    'data-sorter' => 'false'
                )
            ),
            array(
                'Ausgeliefert?',
                array(
                    'data-filter' => 'false',
                    'data-sorter' => 'false'
                )
            ),
            array(
                'Fahrzeug zurücksetzen',
                array(
                    'data-filter' => 'false',
                    'data-sorter' => 'false'
                )
            ),
            array(
                'Fahrzeug tauschen',
                array(
                    'data-filter' => 'false',
                    'data-sorter' => 'false'
                )
            )
        );
        $headings[]["headingone"] = $headers;
        $result = array();
        $result = array_merge($headings, $result);
        $this->ajax_delivery_table = new DisplayTable($result, array(
            'id' => 'ajax_delivery_print'
        ));
        $this->qform_delivery_sales = new QuickformHelper($this->displayHeader, 'auszulieferende_fahrzeuge');
        $this->qform_delivery_sales->getVehiclesToDeliverForm();

        // $yearmonth=$this->requestPtr->getProperty('yearmonth');
        // $delivered_filter=$this->requestPtr->getProperty('delivered_filter');

        // $this->qform_delivery_sales_filter=new QuickformHelper($this->displayHeader, 'auszulieferende_fahrzeuge_filter');
        // $this->qform_delivery_sales_filter->qform_delivery_sales_filter();

        // $deliveryToDivisionResults=$this->ladeLeitWartePtr->vehiclesPtr->getVehiclesToDeliver($yearmonth,$delivered_filter);

        // $this->qform_delivery_sales=new QuickformHelper($this->displayHeader, 'auszulieferende_fahrzeuge');
        // $this->qform_delivery_sales->getVehiclesToDeliver($deliveryToDivisionResults);
    }


    function delivery_old()
    {

        if (isset($_POST['db_col'])) {
            $vehicle_ids = $this->ladeLeitWartePtr->vehiclesPtr->getAllVehicleIdsinRange($_POST['db_col'], $_POST['startval'], $_POST['endval']);
            $vehicle_ids = array_column($vehicle_ids, 'vehicle_id');
            $this->saveExportLieferschein('vehicles.vehicle_id', $vehicle_ids);
        } else {
            $this->qform_pdf = new QuickformHelper($this->displayHeader, 'exportpdf_fahrzeuge');
            $this->qform_pdf->exportPDFoptions('delivery_old');
        }

    }


    /**
     */
    function ajax_assign_vehicle_workshop()
    {

        $vehicle_id = $this->requestPtr->getProperty('vehicle_id');
        $workshop_id = $this->requestPtr->getProperty('workshop_id');
        $division_id = $this->ladeLeitWartePtr->newQuery('depots')
            ->where('workshop_id', '=', $workshop_id)
            ->getVal('division_id');

        if (! $division_id) {
            $location = $this->ladeLeitWartePtr->newQuery('workshops')
                ->where('workshop_id', '=', $workshop_id)
                ->getVal('location');
            $division_id = $this->ladeLeitWartePtr->newQuery('depots')
                ->where('name', 'LIKE', '%' . $location . '%')
                ->getVal('division_id');
        }

        if ($division_id) {
            $fleet_pool = $this->ladeLeitWartePtr->depotsPtr->newQuery()
                ->where('division_id', '=', $division_id)
                ->where('name', 'LIKE', 'Fleet_Pool_%')
                ->getOne('depot_id,name');
            $cost_center = $this->ladeLeitWartePtr->divisionsPtr->newQuery()
                ->where('division_id', '=', $division_id)
                ->getVal('cost_center');
        }

        $vehicle_info = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne('vin,ikz,code,depot_id');
        $production_location = $vehicle_info['depot_id'];

        $workshop = $this->ladeLeitWartePtr->newQuery('workshops')
            ->where('workshop_id', '=', $workshop_id)
            ->getOne('name,location,street,zip_code');

        $workshop_name = $workshop['name'] . ',' . $workshop['street'] . ',' . $workshop['location'] . ' ' . $workshop['zip_code'];

        $workshop_delivery = array(
            'workshop_delivery_id' => NULL,
            'vehicle_id' => $vehicle_id,
            'workshop_id' => $workshop_id,
            'update_timestamp' => date('Y-m-d H:i:sO')
        );

        $existing_row = $this->ladeLeitWartePtr->newQuery('workshop_delivery')
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('workshop_delivery_id');

        if (empty($existing_row)) {
            $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where('vehicle_id', '=', $vehicle_id)
                ->update(array(
                'depot_id'
            ), array(
                $fleet_pool['depot_id']
            ));
            $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
                ->where('vehicle_id', '=', $vehicle_id)
                ->update(array(
                'kostenstelle',
                'production_location'
            ), array(
                $cost_center,
                $production_location
            ));

            $this->ladeLeitWartePtr->newQuery('workshop_delivery')->insert($workshop_delivery);
            $workshop_delivery_id = $this->ladeLeitWartePtr->newQuery('workshop_delivery')
                ->where('vehicle_id', '=', $vehicle_id)
                ->where('workshop_id', '=', $workshop_id)
                ->getVal('workshop_delivery_id');
            $result = array(
                'ikz' => $vehicle_info['ikz'],
                'code' => $vehicle_info['code'],
                'vin' => $vehicle_info['vin'],
                'workshop' => $workshop_name,
                'fleetpool' => $fleet_pool['name'],
                'workshop_delivery_id' => $workshop_delivery_id,
                'form_element' => '<input type="checkbox" name="select_vehicle_' . $workshop_delivery_id . '"><label for="select_vehicle_' . $workshop_delivery_id . '">auswählen</label>'
            );
        } else {
            /*
             * unset($workshop_delivery['workshop_delivery_id']);
             * $this->ladeLeitWartePtr->newQuery('workshop_delivery')->where('workshop_delivery_id','=',$existing_row)->update(array_keys($workshop_delivery),array_values($workshop_delivery));
             */
            $result = array(
                'error' => 'Fahrzeug schon zugewiesen!'
            );
        }

        echo json_encode($result);
        exit(0);

    }


    /**
     */
    function workshop_delivery()
    {

        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('divisions.production_location', '=', 't')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
            ->get('vehicle_id,vin,code');
        $processedVehicles = array(
            ''
        );
        foreach ($vehicles as $vehicle) {
            $processedVehicles[$vehicle['vehicle_id']] = $vehicle['vin'] . '/' . $vehicle['code'];
        }

        $workshops = $this->ladeLeitWartePtr->newQuery('workshops')->get('workshop_id,name,location,street,zip_code');

        $processedWorkshops = array(
            ''
        );
        foreach ($workshops as $workshop) {
            $processedWorkshops[$workshop['workshop_id']] = $workshop['name'] . ',' . $workshop['street'] . ',' . $workshop['location'] . ' ' . $workshop['zip_code'];
        }

        $this->qform_workshop_delivery = new QuickformHelper($this->displayHeader, 'workshop_delivery');
        $workshop_deliveries = $this->ladeLeitWartePtr->newQuery('workshop_delivery')
            ->join('vehicles', 'vehicles.vehicle_id=workshop_delivery.vehicle_id', 'INNER JOIN')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('workshops', 'workshops.workshop_id=workshop_delivery.workshop_id', 'INNER JOIN')
            ->get('workshop_delivery_id,delivery_date,
            vehicles.ikz,vehicles.code,vehicles.vin,vehicles.vehicle_id,
            workshops.name as wname,workshops.workshop_id,workshops.location,workshops.street,workshops.zip_code,
            depots.name as dname');
        $this->qform_workshop_delivery->gen_workshop_delivery($processedVehicles, $processedWorkshops, $workshop_deliveries);

    }


    /**
     */
    function print_workshop_delivery()
    {

        $workshop_delivery_date = $this->requestPtr->getProperty('workshop_delivery_date');
        $workshop_delivery_time = strtotime($workshop_delivery_date);
        $workshop_delivery_timestamp = date('Y-m-d 00:00:00O', $workshop_delivery_time);
        $workshop_delivery_ids = $this->requestPtr->getProperty('workshop_delivery_ids');
        $workshop_delivery_ids = explode(',', $workshop_delivery_ids);
        $vehicle_ids = array();
        foreach ($workshop_delivery_ids as $workshop_delivery_id) {
            if (isset($_POST['select_vehicle_' . $workshop_delivery_id])) {
                $vehicle_id = $this->ladeLeitWartePtr->newQuery('workshop_delivery')
                    ->where('workshop_delivery_id', '=', $workshop_delivery_id)
                    ->getVal('vehicle_id');
                $vehicle_ids[] = $vehicle_id;
                $this->ladeLeitWartePtr->newQuery('workshop_delivery')
                    ->where('workshop_delivery_id', '=', $workshop_delivery_id)
                    ->update(array(
                    'delivery_date'
                ), array(
                    $workshop_delivery_timestamp
                ));
            }
        }
        $this->saveExportLieferschein('vehicles.vehicle_id', $vehicle_ids, false, true);
        $this->action = 'workshop_delivery';
        $this->action = 'workshop_delivery';

    }


    /**
     *
     * @param string $dbColParam
     *            usually vehicles.vehicle_id
     * @param array|string $startidParam
     *            usually an array of vehicle ids
     */
    function saveExportLieferschein($dbColParam = '', $startidParam = '', $send_notification_emails = false, $workshop_delivery = false)
    {

        if (empty($dbColParam))
            $dbColParam = $this->requestPtr->getProperty('db_col');
        if (empty($startidParam))
            $startidParam = $this->requestPtr->getProperty('startval');

        $db_col = $dbColParam;
        $startval = $startidParam;

        $division_emails = array();
        $zspl_emails = array();
        $depots = $this->ladeLeitWartePtr->vehiclesPtr->getDepotsForTransferProto($db_col, $startval);

        $pickup = '';

        if ($workshop_delivery === false) {
            // table headings
            $header = array(
                'Pos.',
                'AKZ',
                'VIN',
                'IKZ',
                iconv('UTF-8', 'windows-1252', 'Ladesäule')
            );
            // Column widths
            $w = array(
                10,
                30,
                55,
                45,
                40
            );
        } else {
            $header = array(
                'Pos.',
                'AKZ',
                'VIN',
                'IKZ'
            );
            // Column widths
            $w = array(
                10,
                30,
                55,
                45
            );
        }

        $pickup_pdf = new FPDF();

        $fill = false;
        $pickup_pdf->SetFillColor(241, 241, 241);
        $cnt = 1;

        foreach ($depots as $depot) {
            $cnt = 1;
            $pickup_pdf->AddPage();

            $pickup_pdf_break = false;
            $pickup_pdf_header = true;

            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getDetailsForTransferProto($db_col, $startval, $depot['depot_id'], $workshop_delivery);
            /*
             * @todo
             * $vehicles can be empty if the vehicles are assigned to the depot but no stations are assigned. If empty then break process
             * and show message. Maybe consider not showing these vehicles at all in the Auslieferung page.
             *
             */
            $pdf_merge_str = '';

            $table_vehicles = array();
            foreach ($vehicles as $vehicle) {
                $fhandle = fopen('/var/www/lieferschein_template.fodt', 'r');
                $fcontent = fread($fhandle, filesize('/var/www/lieferschein_template.fodt'));
                $fhandle_new = fopen('/var/www/lieferschein_template_' . $vehicle['vehicle_id'] . '.fodt', 'w');

                $variant_name = $vorhaben = $vehicle_variant = '';

                if (isset($vehicle['vorhaben']))
                    $vorhaben = $vehicle['vorhaben'];
                if (isset($vehicle['vehicle_variant'])) {
                    $vehicle_variant = $vehicle['vehicle_variant'];
                    if (isset($this->vehicle_variants[$vehicle['vehicle_variant']]))
                        $variant_name = $this->vehicle_variants[$vehicle['vehicle_variant']];
                }

                $replacevals = array(
                    'CA_HERE_CA' => '',
                    'CI_HERE_CI' => '',
                    'KSNL_HERE_KSNL' => $vehicle['cost_center'],
                    'I_HERE_I' => $vehicle['ikz'],
                    'AK_HERE_AK' => $vehicle['code'],
                    'VIN_HERE_VIN' => $vehicle['vin'],
                    'US_HERE_US' => $this->user->getUserLastName(),
                    'DA_HERE_DA' => date('j.m.Y'),
                    'OZ_HERE_OZ' => $depot['dp_depot_id'] . ' (' . $depot['name'] . ') ',
                    'VNR_HERE_VNR' => $vorhaben,
                    'NL_HERE_NL' => $vehicle['name'],
                    'VehicleType_HERE_VehicleType' => $variant_name
                );

                if ($send_notification_emails) {
                    if (isset($vehicle['delivery_date']))
                        $delivery_date = date('Y-m-d', strtotime($vehicle['delivery_date']));
                    $table_vehicles[] = array(
                        $vehicle['ikz'],
                        $vehicle['code'],
                        $vehicle['vin'],
                        $variant_name,
                        $vehicle['sname'],
                        $delivery_date
                    );
                }
                $fcontent = str_replace(array_keys($replacevals), array_values($replacevals), $fcontent);

                fwrite($fhandle_new, $fcontent);
                fclose($fhandle);
                fclose($fhandle_new);

                exec('libreoffice --headless --convert-to pdf ' . '/var/www/lieferschein_template_' . $vehicle['vehicle_id'] . '.fodt --outdir /var/www/');

                $pdf_merge_str .= '/var/www/lieferschein_template_' . $vehicle['vehicle_id'] . '.pdf ';
            }

            if ($pdf_merge_str) {
                $pdfmerged = 'lf_' . str_replace(' ', '_', $depot['name']) . '_' . $depot["dp_depot_id"] . '_' . date('Y-m-j');
                $pdfmerged = preg_replace("([^a-zA-Z0-9_-])", '', $pdfmerged);
                exec('pdftk ' . $pdf_merge_str . ' cat output /var/www/' . $pdfmerged . '.pdf');
                if (file_exists("/var/www/WebinterfaceNew/fpdf/fpdf.php"))
                    require_once ('/var/www/WebinterfaceNew/fpdf/fpdf.php');
                if ($workshop_delivery === false)
                    $pdf = new FPDF();

                foreach ($vehicles as $vehicle) {
                    if ($workshop_delivery === false) {
                        $pdf->AddPage('L');
                        $pdf->SetFont('Arial', 'B', 16);
                        $pdf->Cell(40, 20, 'Auslieferung : ' . iconv('UTF-8', 'windows-1252', $depot['name']) . " (" . $depot["dp_depot_id"] . ") ");
                        $pdf->Ln();
                        $pdf->SetFont('Arial', '', 80);
                        $pdf->Cell(150, 30, $vehicle['code']);
                        $pdf->Ln();
                        $pdf->SetFont('Arial', '', 60);
                        $pdf->Cell(150, 30, $vehicle['vin']);
                        $pdf->Ln();
                        $pdf->Cell(150, 30, 'IKZ ' . $vehicle['ikz']);
                        $pdf->Ln();
                        $pdf->SetFont('Arial', '', 30);
                        $pdf->Cell(150, 30, iconv('UTF-8', 'windows-1252', 'Ladesäule'));
                        $pdf->SetFont('Arial', '', 80);
                        $pdf->Ln();
                        $pdf->Cell(150, 30, $vehicle['sname']);
                    }
                    $prev_pickup = $pickup;

                    if (isset($vehicle['production_location']) && ! empty($vehicle['production_location'])) {
                        $production_loc_name = $this->ladeLeitWartePtr->depotsPtr->newQuery()
                            ->where('depot_id', '=', $vehicle['production_location'])
                            ->getVal('name');
                        $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: ' . $production_loc_name);
                    } else if (isset($vehicle['production_date']) && isset($vehicle['qs_user'])) {
                        if ($vehicle['qs_user'] == - 1)
                            $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Sts-Pool (Würselen)');
                        else
                            $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Produktion (Aachen Jülicher Straße)');
                    } else
                        $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Sts-Pool (Würselen)');

                    // break every four vehicles within the same depot
                    if ((($cnt - 1) % 4 == 0 && $cnt != 1))
                        $pickup_pdf_break = true;

                    // if pdf_break is required then show the Abholort, same code repeated below at the end of each depot L1514
                    if ($pickup_pdf_break) {
                        $pickup_pdf->Cell(array_sum($w), 0, '', 'T');
                        $pickup_pdf->SetFont('Arial', 'B', 16);
                        $pickup_pdf->Ln();

                        if (! empty($prev_pickup) && $prev_pickup != $pickup)
                            $pickup_pdf->Cell(40, 20, $prev_pickup);
                        else
                            $pickup_pdf->Cell(40, 20, $pickup);
                        $pickup_pdf->Ln();
                        $pickup_pdf->SetFont('Arial', '', 12);
                        $pickup_pdf->Cell(40, 20, 'Abgeholt am ' . '_________________' . ' durch Spedition' . ' _________________________');
                        $pickup_pdf->Ln();
                        $pickup_pdf->Cell(40, 20, 'Unterschrift Fahrer: ');
                        $cnt = 1;
                        $pickup_pdf->AddPage();
                        $pickup_pdf_header = true;
                        $pickup_pdf_break = false;
                    }

                    if ($pickup_pdf_header) {
                        if ($workshop_delivery === false) {
                            $destination_name = iconv('UTF-8', 'windows-1252', $depot['name']) . " (" . $depot["dp_depot_id"] . "), P : " . $depot["penta_folge_id"];
                            $destination_addr_one = $depot['street'] . ',' . $depot['housenr'];
                            $destination_addr_two = $depot['place'] . ' ' . $depot['postcode'];
                        } else {
                            $workshop_id = $this->ladeLeitWartePtr->newQuery('workshop_delivery')
                                ->where('vehicle_id', '=', $vehicle['vehicle_id'])
                                ->getVal('workshop_id');
                            $workshop = $this->ladeLeitWartePtr->newQuery('workshops')
                                ->where('workshop_id', '=', $workshop_id)
                                ->getOne('name,location,street,zip_code');
                            $destination_name = iconv('UTF-8', 'windows-1252', $workshop['name']);
                            $destination_addr_one = $workshop['street'];
                            $destination_addr_two = $workshop['location'] . ' ' . $workshop['zip_code'];
                        }
                        $pickup_pdf->SetFont('Arial', 'B', 16);
                        $pickup_pdf->Cell(40, 20, $destination_name);
                        $pickup_pdf->Ln(16);
                        $pickup_pdf->SetFont('Arial', '', 12);
                        $pickup_pdf->Cell(40, 6, iconv('UTF-8', 'windows-1252', $destination_addr_one));
                        $pickup_pdf->Ln();
                        $pickup_pdf->Cell(40, 6, iconv('UTF-8', 'windows-1252', $destination_addr_two));
                        $pickup_pdf->Ln(10);

                        for ($i = 0; $i < count($header); $i ++)
                            $pickup_pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
                        $pickup_pdf->Ln();
                        $pickup_pdf_header = false;
                    }

                    $pickup_pdf->Cell($w[0], 6, $cnt, 'LR', 0, 'L', $fill);
                    $pickup_pdf->Cell($w[1], 6, $vehicle['code'], 'LR', 0, 'L', $fill);
                    $pickup_pdf->Cell($w[2], 6, $vehicle['vin'], 'LR', 0, 'L', $fill);
                    $pickup_pdf->Cell($w[3], 6, $vehicle['ikz'], 'LR', 0, 'L', $fill);
                    if ($workshop_delivery === false) {
                        $pickup_pdf->Cell($w[4], 6, $vehicle['sname'], 'LR', 0, 'L', $fill);
                    }
                    $pickup_pdf->Ln();

                    $cnt ++;
                    $fill = ! $fill;

                    if (file_exists('/var/www/lieferschein_template_' . $vehicle['vehicle_id'] . '.fodt'))
                        unlink('/var/www/lieferschein_template_' . $vehicle['vehicle_id'] . '.fodt');
                    if (file_exists('/var/www/lieferschein_template_' . $vehicle['vehicle_id'] . '.pdf'))
                        unlink('/var/www/lieferschein_template_' . $vehicle['vehicle_id'] . '.pdf');
                }
                $zuordnung_fname = str_replace(' ', '_', $depot['name']) . '_' . $depot["dp_depot_id"];
                $zuordnung_fname = preg_replace("([^a-zA-Z0-9_-])", '', $zuordnung_fname);

                if ($workshop_delivery === false) {
                    $pdf->Output('F', '/var/www/zuordnung_' . $zuordnung_fname . ".pdf", true);
                    $this->lieferscheinFname .= '<a href="/downloadlieferschein.php?fname=zuordnung_' . $zuordnung_fname . '" >Ladesaule Zuordnung ' . $depot['name'] . ' (' . $depot['dp_depot_id'] . ') ' . 'herunterladen</a><br>';
                }
                $this->lieferscheinFname .= '<a href="/downloadlieferschein.php?fname=' . $pdfmerged . '" >Lieferschein ' . $depot['name'] . ' (' . $depot['dp_depot_id'] . ') ' . 'herunterladen</a><br>';
            }

            if ($send_notification_emails) {
                $table_header = array(
                    'headingone' => array(
                        'IKZ',
                        'AKZ',
                        'VIN',
                        'FahrzeugTyp',
                        'Ladesaule',
                        'Auslieferungsdatum'
                    )
                );
                $depot_table = new DisplayTable(array_merge($table_header, $table_vehicles), array(
                    'style' => '"border:1px solid #CCC; "'
                ));

                $division_id = $depot['division_id'];
                $zspl_id = $depot['zspl_id'];

                if (! isset($division_emails[$division_id]))
                    $division_emails[$division_id] = '';

                if (! isset($zspl_emails[$zspl_id]))
                    $zspl_emails[$zspl_id] = '';

                $depot_str = '<strong>' . $depot['name'] . '(' . $depot["dp_depot_id"] . ')</strong><br>' . $depot['street'] . ',' . $depot['housenr'] . ',' . $depot['place'] . '-' . $depot['postcode'] . '<br>' . $depot_table->getContent() . '<br><br>';
                $depot_email_content = str_replace(array(
                    '{deliveryStr}'
                ), array(
                    $depot_str
                ), $this->emailDeliveryWithDate);
                $division_emails[$division_id] .= $depot_str;
                $zspl_emails[$zspl_id] .= $depot_str;

                $styledef = '<style type="text/css">tr:nth-child(even) {    background: #f1f1f1;}
								table, th, td {    border-collapse: collapse;    border: 1px solid #CCCCCC; }
							  </style>';
                $depot_emails = $this->ladeLeitWartePtr->depotsPtr->getEmails($depot['depot_id']);
                if (! empty($depot_emails)) {
                    // echo 'depot'.implode(',',array_keys($depot_emails)).'emails <br>';
                    // echo $styledef.$depot_email_content;

                    $mailer = new MailerSmimeSwift($depot_emails, '', 'StreetScooter Fahrzeuge Auslieferung - ' . $depot['name'], $styledef . $depot_email_content, null, true);
                }
            }

            $pickup_pdf->Cell(array_sum($w), 0, '', 'T');
            $pickup_pdf->SetFont('Arial', 'B', 16);
            $pickup_pdf->Ln();
            $pickup_pdf->Cell(40, 20, $pickup);
            $pickup_pdf->Ln();
            $pickup_pdf->SetFont('Arial', '', 12);
            $pickup_pdf->Cell(40, 20, 'Abgeholt am ' . '_________________' . ' durch Spedition' . ' _________________________');
            $pickup_pdf->Ln();
            $pickup_pdf->Cell(40, 20, 'Unterschrift Fahrer: ');
        } // end of foreach depot

        $pickup_filename = 'abholung_' . date('Y_m_j_H_i');
        $pickup_pdf->Output('F', '/var/www/' . $pickup_filename . '.pdf', true);

        $this->lieferscheinFname .= '<a href="/downloadlieferschein.php?fname=' . $pickup_filename . '" >Abholungsdatei</a><br>';

        if ($send_notification_emails) {

            foreach ($zspl_emails as $zspl_id => $zspl_email) {
                $zspl_name = $this->ladeLeitWartePtr->zsplPtr->getNameFromId($zspl_id);
                $fpv_list = $this->ladeLeitWartePtr->allUsersPtr->getFPVEmails($zspl_id);

                if (empty($fpv_list))
                    continue;
                $fpv_emails = array();
                foreach ($fpv_list as $fpv) {
                    if (! isset($fpv['fname']))
                        $fpv['fname'] = '';
                    if (! isset($fpv['lname']))
                        $fpv['lname'] = '';
                    if (! empty($fpv['email']))
                        $fpv_emails[$fpv['email']] = $fpv['fname'] . '  ' . $fpv['lname'];
                }
                if (! empty($zspl_email)) {
                    $zspl_email = str_replace(array(
                        '{deliveryStr}'
                    ), array(
                        $zspl_email
                    ), $this->emailDeliveryWithDate);
                    $styledef = '<style type="text/css">tr:nth-child(even) {    background: #f1f1f1;}
							table, th, td {    border-collapse: collapse;    border: 1px solid #CCCCCC; }
						  </style>';
                    // echo 'FPV'.implode(',',array_keys($fpv_emails)).'<br>';
                    // echo $styledef.$zspl_email;
                    $mailer = new MailerSmimeSwift($fpv_emails, '', 'StreetScooter Fahrzeuge Auslieferung - ZSPL ' . $zspl_name, $styledef . $zspl_email, null, true);
                }
            }

            foreach ($division_emails as $division_id => $division_email) {
                $division_name = $this->ladeLeitWartePtr->divisionsPtr->getNameFromId($division_id);
                $fps_list = $this->ladeLeitWartePtr->allUsersPtr->getFPSEmails($division_id);
                if (empty($fps_list))
                    continue;
                $fps_emails = array();
                foreach ($fps_list as $fps) {
                    if (! isset($fps['fname']))
                        $fps['fname'] = '';
                    if (! isset($fps['lname']))
                        $fps['lname'] = '';

                    if (! empty($fps['email']))
                        $fps_emails[$fps['email']] = $fps['fname'] . '  ' . $fps['lname'];
                }
                if (! empty($division_email)) {
                    $division_email = str_replace(array(
                        '{deliveryStr}'
                    ), array(
                        $division_email
                    ), $this->emailDeliveryWithDate);
                    $styledef = '<style type="text/css">tr:nth-child(even) {    background: #f1f1f1;}
							table, th, td {    border-collapse: collapse;    border: 1px solid #CCCCCC; }
						  </style>';
                    // echo 'div '.implode(',',array_keys($fps_emails)).' emails <br>';
                    // echo $styledef.$division_email;

                    $mailer = new MailerSmimeSwift($fps_emails, '', 'StreetScooter Fahrzeuge Auslieferung - Niederlassung ' . $division_name, $styledef . $division_email, null, true, array(
                        'Pradeep.Mohan@streetscooter.eu',
                        'Philipp.Schnelle@streetscooter.eu',
                        'Ismail.Sbika@streetscooter.eu',
                        'Lothar.Juergens@streetscooter.eu',
                        'Team.Auslieferung@streetscooter.eu'
                    ));
                }
            }
        }

        // endo if send emails
    }


    /**
     * *
     * Either called from the exportpdf form (where no arguments are passed to this function), or from the saveNewVehicles
     *
     * @param string $dbColParam
     * @param string $startidParam
     * @param string $endidParam
     * @return string|void IF function is called from saveNewVehicles, then return form with button clicking which will generate the PDF, ELSE return nothing
     */
    function saveexportpdf($dbColParam = '', $startidParam = '', $endidParam = '')
    {

        // if arguments are empty, then get it from the POST variables
        if (empty($dbColParam))
            $dbColParam = $this->requestPtr->getProperty('db_col');
        if (empty($startidParam))
            $startidParam = $this->requestPtr->getProperty('startval');
        if (empty($endidParam))
            $endidParam = $this->requestPtr->getProperty('endval');

        $db_col = $dbColParam;
        $startval = $startidParam;

        if (! empty($endidParam)) {
            $endval = $endidParam;
            $start_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where($db_col, '=', $startval)
                ->getVal('vehicle_id');
            $end_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where($db_col, '=', $endval)
                ->getVal('vehicle_id');
            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where("vehicle_id", ">=", $start_vehicle_id)
                ->where("vehicle_id", "<=", $end_vehicle_id)
                ->join('colors', 'vehicles.color_id=colors.color_id', 'FULL OUTER JOIN')
                ->orderBy($db_col, 'ASC')
                ->get('vehicle_id,vin,code,ikz,colors.name as colorname');
        } else {

            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
                ->join('colors', 'vehicles.color_id=colors.color_id', 'FULL OUTER JOIN')
                ->where("vehicles_sales.vorhaben", "=", $startval)
                ->get('vehicles.vehicle_id,vin,code,vehicles.ikz,colors.name  as colorname');
        }

        // $this->qform_pdf=new QuickformHelper($this->displayHeader, 'exportpdf_fahrzeuge','POST',array('action'=>'genpdf.php','target'=>'_blank'));
        // $this->qform_pdf->genPdfLink($vehicles);

        $sales_protokoll = new SalesProtokoll($vehicles, $this->ladeLeitWartePtr);
        $pdfName = $sales_protokoll->getPdfName();
        $this->pdfLink = '<span class="s_exportpdf"><a class="exportpdf" href="/downloadlieferschein.php?fname=' . $pdfName . '" >Fahrzeugbegleitschein herunterladen</a></span><br>';

        /**
         * IF isset($_POST['db_col'] then it means this function was called by submitting post variables to this function saveexportpdf, so just set the action
         * to exportpdf and the link will be displayed from the sales.php page
         * ELSE it was called from the the saveNewVehicles function, in which case return the generated link to download the PDF
         */
        if (isset($_POST['db_col'])) {
            $this->action = 'exportpdf';
        } else
            return $this->pdfLink;

        // $this->qform_pdf->getContent();
    }

    function saveexportxml($dbColParam = '', $startidParam = '', $endidParam = '', $dateParam = '', $auth_signatory = '')
    {
        if (empty($dbColParam))
            $dbColParam = $this->requestPtr->getProperty('db_col');
        if (empty($startidParam))
            $startidParam = $this->requestPtr->getProperty('startval');
        if (empty($endidParam))
            $endidParam = $this->requestPtr->getProperty('endval');
        if (empty($dateParam))
            $dateParam = $this->requestPtr->getProperty('cocdate');
        if (empty($auth_signatory))
            $auth_signatory = $this->requestPtr->getProperty('auth_signatory');

        $db_col = $dbColParam;
        $startval = $startidParam;

        $position = explode(", ", $auth_signatory);
        $auth_signer = $position[0];
        $auth_position = $position[1];
        $vin = $startidParam;
        // $signer = $auth_signer;
        if (empty($dateParam))
            $signature_date = date("d.m.Y");
        else
            $signature_date = $dateParam;
        $escaped_position = $auth_position;

        $escaped_vin = escapeshellarg($vin);
        $escaped_signer = escapeshellarg($auth_signer);
        $escaped_date = escapeshellarg($signature_date);
        $escaped_position = escapeshellarg($auth_position);

        // $xml_merge_str = implode(' ', $pdf_merge_files);
        $this->xmlmerge = 'protokoll_' . date('Y-m-j_H_i');
        $xmlcommandline = escapeshellcmd("python3 /var/www/ivicocgenerator/ivicocgenerator.py --vin {$escaped_vin} --prefix /var/www/ivicocgenerator/tmp/ --signer {$escaped_signer} --signature-date {$escaped_date} --position-of-signer {$escaped_position}");
        // $xmlcommandline = 'python3 /var/www/ivicocgenerator/ivicocgenerator.py --vin WS5B16GAAJA801397 --prefix /var/www/ivicocgenerator/tmp/ --signer "Martin Glagla" --signature-date "2020-01-01" --position-of-signer "CEO"';
        exec($xmlcommandline);

        $sales_protokoll = new SalesProtokoll($vehicles, $this->ladeLeitWartePtr);
        $pdfName = $sales_protokoll->getXmlName();
        $this->pdfLink = '<br><span class="s_exportxml"><a class="exportpdf" href="/downloadxml.php?fname=' . $pdfName . $startidParam . '" >XML herunterladen</a></span><br>';

        if (isset($_POST['db_col'])) {
            $this->action = 'exportxml';
        } else
            return $this->pdfLink;
    }

    function saveTemplate()
    {

        $ajaxquery = $this->requestPtr->getProperty('ajaxquery');
        $selectCols = $this->requestPtr->getProperty('selectCols');
        $template_new_name = $this->requestPtr->getProperty('template_new_name');
        if ($template_new_name)
            $template = $template_new_name;
        else
            $template = $this->requestPtr->getProperty('template');

        $result = $this->ladeLeitWartePtr->csvTemplatesPtr->add(array(
            'userid' => $this->user->getUserId(),
            'template_name' => $template,
            'csvfields' => serialize($selectCols)
        ));

        if ($ajaxquery) {
            if ($result)
                echo 'Gespeichert';
            else
                echo 'Fehler';
            exit(0);
        }

    }


    function getTemplate()
    {

        $ajaxquery = $this->requestPtr->getProperty('ajaxquery');
        $templateId = $this->requestPtr->getProperty('templateId');

        $result = $this->ladeLeitWartePtr->csvTemplatesPtr->getFromId($templateId);

        $selectCols = unserialize($result['csvfields']);
        if ($ajaxquery) {
            if ($result)
                echo json_encode($selectCols);
            else
                echo 'Fehler';
            exit(0);
        }

    }


    function saveDeliveryDateNew()
    {

        $deliverydates = $this->requestPtr->getProperty('deliverydate');
        $processed_vehicle_ids = array();
        foreach ($deliverydates as $vehicleid => $delivery_date) {
            $delivery_status = '';
            if (empty($delivery_date)) {
                $delivery_date = NULL;
                $delivery_status = 'FALSE';
            } else {
                $ts_deliverydate = strtotime($delivery_date);
                $delivery_date = date('Y-m-d 00:00:00O', $ts_deliverydate);
                $delivery_status = 'TRUE';
            }

            $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
                ->where('vehicle_id', '=', $vehicleid)
                ->update(array(
                'delivery_date',
                'delivery_status'
            ), array(
                $delivery_date,
                $delivery_status
            ));

            if (isset($_POST['save_delivery_ctrl' . $vehicleid]))
                $processed_vehicle_ids[] = $vehicleid;
        }

        if (isset($_POST['send_notification_emails']) && $_POST['send_notification_emails'] == 1)
            $send_notification_emails = true;
        else
            $send_notification_emails = false;
        $this->saveExportLieferschein('vehicles.vehicle_id', $processed_vehicle_ids, $send_notification_emails);
        $this->pentaCSVExport($processed_vehicle_ids);
        $this->action = "delivery";
        $this->delivery();

    }


    function pentaCSVExport($vehicle_ids)
    {

        return $this->ladeLeitWartePtr->vehiclesPtr->pentaCSVExport($vehicle_ids);

    }


    function saveDeliveryDate()
    {

        $ajaxquery = $this->requestPtr->getProperty('ajaxquery');
        $delivery_date = $this->requestPtr->getProperty('delivery_date');
        $vehicleid = $this->requestPtr->getProperty('vehicleid');
        $ts_deliverydate = strtotime($delivery_date);
        $delivery_date = date('Y-m-d 00:00:00O', $ts_deliverydate);
        if ($ts_deliverydate)
            $vdd = date('d-m-Y', $ts_deliverydate);
        else
            $vdd = '--';

        $result = $this->ladeLeitWartePtr->vehiclesSalesPtr->save(array(
            'delivery_date'
        ), array(
            $delivery_date
        ), array(
            'vehicle_id',
            '=',
            $vehicleid
        ));
        if ($result)
            echo '<input type="hidden" name="vehicle_id" class="vehicle_id" value="' . $vehicleid . '" ><span class="delivery_date">' . $vdd . '</span>';
        else
            echo 0;
        exit(0);

    }


    function savedepotassign()
    {

        $ajaxquery = $this->requestPtr->getProperty('ajaxquery');
        $depotid = $this->requestPtr->getProperty('depotid');
        $vehicleid = $this->requestPtr->getProperty('vehicleid');
        $current_depot_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicleid)
            ->getVal('depot_id');

        if ($current_depot_id == 0) {

            $sts_pool = $this->ladeLeitWartePtr->depotsPtr->getStsPoolDepot();
            if ($sts_pool['depot_id'] == $depotid) {
                $success_flag = 0;
                $variant_value = $this->ladeLeitWartePtr->vehiclesSalesPtr->getVehicleVariant($vehicleid);
                if (empty($variant_value)) {
                    $result = array(
                        'error_status' => 1,
                        'contentstr' => 'Konfiguration unbekannt!'
                    );
                    echo json_encode($result);
                    exit(0);
                }

                $weeks = $this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01'), true);

                foreach ($weeks as $kweek) {
                    $sum_already_assigned_vehicles = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getCountForYearWeekVariant(date('Y'), $kweek, $variant_value);
                    if (empty($sum_already_assigned_vehicles))
                        $sum_already_assigned_vehicles = 0;

                    $quantity = $this->ladeLeitWartePtr->productionPlanPtr->getQtyForYearVariantWeek(date('Y'), $kweek, $variant_value);

                    if ($quantity !== false && $quantity != 0) {
                        // adjustProductionToFleetQty only for those weeks where there are vehicles to be produced that are not already reserved for delivery to a division
                        $quantity -= $sum_already_assigned_vehicles;
                        if ($quantity > 0) {
                            $this->ladeLeitWartePtr->productionPlanPtr->adjustProductionToFleetQty($kweek, $variant_value, date('Y'));
                            $success_flag = 1;
                            // adjust production quantity for this week and exit
                            break;
                        }
                    }
                } // endforeachweek

                if ($success_flag == 0) {
                    $result = array(
                        'error_status' => 1,
                        'contentstr' => 'Fehler beim Speichern! Kein Produktionsplan angegeben oder produzierende Fahrzeuge sind alle schon zugewiesen!'
                    );
                    echo json_encode($result);
                    exit(0);
                }
            }
        }

        $result = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicleid)
            ->update(array(
            'depot_id',
            'station_id'
        ), array(
            $depotid,
            NULL
        ));

        $district = $this->ladeLeitWartePtr->districtsPtr->getForVehicle($vehicleid);

        if (! empty($district))
            $this->ladeLeitWartePtr->districtsPtr->save(array(
                'depot_id'
            ), array(
                $depotid
            ), array(
                'district_id',
                '=',
                $district[0]['district_id']
            ));
        else
            $this->ladeLeitWartePtr->districtsPtr->insertNewDistrict($vehicleid, $depotid);

        $depot = $this->ladeLeitWartePtr->depotsPtr->getFromId($depotid);

        if ($result) {
            $content = '<input type="hidden" name="vehicle_id" class="vehicle_id" value="' . $vehicleid . '" ><span class="depot_search">' . $depot['name'] . '(' . $depot['dp_depot_id'] . ')</span>';
            $error_status = 0;
        } 
        else {
            $error_status = 1;
            $content = 'Fehler beim Speichern!';
        }
        $result = array(
            'error_status' => $error_status,
            'contentstr' => $content
        );
        echo json_encode($result);
        exit(0);

    }


    function depotassign()
    {

        return false; // remove the depotassign function to prevetn unauthorized moving of vehicles between depots
        $this->depotassignresult = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesDepots(array(
            'vehicles.vehicle_id',
            'vehicles.vin',
            'vehicles.ikz',
            'vehicles.code',
            'depots.name as dname',
            'depots.dp_depot_id',
            'vehicles_sales.delivery_date'
        ));

    }


    function dritt()
    {

        $this->newvehicles(TRUE);

    }


    function getBatteryPartGroup()
    {

        return $this->ladeLeitWartePtr->newQuery('part_groups')
            ->where('group_name', 'ilike', '%batter%')
            ->getVal('group_id');

    }


    function newvehicles_variant_list($thirdparty)
    {

        $query = $this->ladeLeitWartePtr->newQuery('vehicle_variants')
            ->where('windchill_variant_name', '~*', '^[BDE][12][0-9][0-2][0-9].*')
            ->orderBy('windchill_variant_name', 'desc');

        if ($thirdparty)
            $query = $query->where('vehicle_variants.is_dp', '!=', 't');
        else
            $query = $query->where('vehicle_variants.is_dp', '=', 't');

        return $query->get('vehicle_variant_id=>windchill_variant_name');

    }

    // New Vehicle Configuration
    function vehicle_configurations_list($thirdparty)
    {

        // $query = $this->ladeLeitWartePtr->newQuery('vehicle_configurations')
        //     ->where('vehicle_configuration_key', '~*', '^[BDE][12][0-9][0-2][0-9].*')
        //     ->orderBy('vehicle_configuration_key', 'desc');

        $query = $this->ladeLeitWartePtr->newQuery('vehicle_configurations')
            ->join('special_vehicle_properties_mapping', 'vehicle_configurations.vehicle_configuration_id = special_vehicle_properties_mapping.vehicle_configuration_id', 'INNER JOIN')
            ->join('special_vehicle_property_values', 'special_vehicle_properties_mapping.special_vehicle_property_value_id = special_vehicle_property_values.svpv_id', 'INNER JOIN')
            ->where('special_vehicle_properties_mapping.special_vehicle_property_id', '=', 6)
            ->orderBy('vehicle_configurations.vehicle_configuration_id', 'desc');

        if ($thirdparty)
            $query = $query->where('special_vehicle_property_values.value_bool', '!=', 't');
        else
            $query = $query->where('special_vehicle_property_values.value_bool', '=', 't');

        return $query->get_no_parse('DISTINCT ON (vehicle_configuration_id) vehicle_configurations.vehicle_configuration_id=>vehicle_configuration_key');

    }

    function vehicle_subconfigurations_list($thirdparty)
    {
        $vehicle_variant_config = @$_REQUEST['vehicle_variant_config'];
        if($vehicle_variant_config) {

            // $query = $this->ladeLeitWartePtr->newQuery('sub_vehicle_configurations')
            //     ->where('vehicle_configuration_id', '=', $vehicle_variant_config)
            //     ->orderBy('sub_vehicle_configuration_name', 'desc');

            // return $query->get('sub_vehicle_configuration_id=>sub_vehicle_configuration_name');
            $query = $this->ladeLeitWartePtr->newQuery('sub_vehicle_configurations')
                ->join('special_vehicle_properties_mapping', 'sub_vehicle_configurations.sub_vehicle_configuration_id = special_vehicle_properties_mapping.sub_vehicle_configuration_id', 'INNER JOIN')
                ->join('special_vehicle_property_values', 'special_vehicle_properties_mapping.special_vehicle_property_value_id = special_vehicle_property_values.svpv_id', 'INNER JOIN')
                ->where('vehicle_configuration_id', '=', $vehicle_variant_config)
                ->where('special_vehicle_properties_mapping.special_vehicle_property_id', '=', 6)
                ->orderBy('sub_vehicle_configurations.sub_vehicle_configuration_id', 'desc');

            if ($thirdparty)
                $query = $query->where('special_vehicle_property_values.value_bool', '!=', 't');
            else
                $query = $query->where('special_vehicle_property_values.value_bool', '=', 't');

            // return $query->get_no_parse('DISTINCT ON (vehicle_configuration_id) sub_vehicle_configurations.sub_vehicle_configuration_id=>sub_vehicle_configuration_name');
            return $query->get('sub_vehicle_configurations.sub_vehicle_configuration_id=>sub_vehicle_configuration_name');
        }

    }

    function newvehicles($thirdparty = FALSE)
    {

        $this->batteryGroup = $this->getBatteryPartGroup();
        $llwPtr = $this->ladeLeitWartePtr;

        if (isset($_REQUEST['thirdparty']))
            $thirdparty = toBool($_REQUEST['thirdparty']);

        // $this->wc_variants = $this->newvehicles_variant_list($thirdparty);
        $this->wc_variants = $this->vehicle_configurations_list($thirdparty);
        $this->wc_subconfig = $this->vehicle_subconfigurations_list($thirdparty);

        $this->part_groups = $llwPtr->newQuery('part_groups')->get('*', 'group_id');
        $this->setVariant = 0;
        $query = $llwPtr->newQuery('colors');
        if ($thirdparty)
            $query = $query->where('color_id', '>', '2');
        else
            $query = $query->where('color_id', '!=', '3');

        $this->setsColor = $query->orderBy('color_id')->get('*', 'color_id'); // 'color_id,name');
        $colors = array_combine(array_column($this->setsColor, 'color_id'), array_column($this->setsColor, 'name'));

        if (isset($_REQUEST['vehicle_variant_wc']))
            $variant_id = $_REQUEST['vehicle_variant_wc'];
        $windchill_name = $dp_alt_list ? $this->alt_variants[$variant_id] : $this->wc_variants[$variant_id];
        $color_id = $_REQUEST['vehicle_color'];
        $part_id_list = [];

        if ($variant_id) {
/*            $result = $this->ladeLeitWartePtr->newQuery('vehicle_variants')
                ->where("vehicle_variant_id", "=", $variant_id)
                ->get('vehicle_variant_id,windchill_variant_name,default_color,vin_batch,number_of_seats,vin_method,default_production_location,battery');*/

            $result = $this->ladeLeitWartePtr->newQuery('vehicle_configurations')
                ->JOIN('vehicle_variants', 'vehicle_variants.vehicle_variant_id = vehicle_configurations.old_vehicle_variant_id', 'INNER JOIN')
                ->where("vehicle_configuration_id", "=", $variant_id)
                ->get('vehicle_configuration_id,vehicle_configuration_key,default_configuration_color_id,default_production_location_id,old_vehicle_variant_id,vehicle_type_name,vehicle_type_year,battery,vin_method,vin_batch');

            $result_sub_vehicle_configurations = $this->ladeLeitWartePtr->newQuery('sub_vehicle_configurations')
                ->where("vehicle_configuration_id", "=", $variant_id)
                ->get('vehicle_configuration_id,sub_vehicle_configuration_id,sub_vehicle_configuration_name,short_production_description');
                 // var_dump($result);
                 
                 // var_dump($result_sub_vehicle_configurations);

            $this->setVariant = safe_val($result, 0, false);
            // $windchill_name = $this->setVariant['windchill_variant_name'];
            $windchill_name = $this->setVariant['vehicle_configuration_key'];
            $color_name = $this->setVariant['default_configuration_color_id'];
            $vehicle_variant_id = $this->setVariant['old_vehicle_variant_id'];
            $vin_method = $this->setVariant['vin_method'];
            $vehicle_type_name = $this->setVariant['vehicle_type_name'];
            $vehicle_type_year = $this->setVariant['vehicle_type_year'];
            $fahrzeug_typ = $vehicle_type_name . $vehicle_type_year;
            $vin_batch = $this->setVariant['vin_batch'];

            /*if (isset($_REQUEST['vehicle_subconfiguration_wc']))
                $sub_configuraion_id = $_REQUEST['vehicle_subconfiguration_wc'];*/

            $result_set_sub_vehicle_configurations = $this->ladeLeitWartePtr->newQuery('sub_vehicle_configurations')
                ->where("sub_vehicle_configuration_id", "=", $sub_configuraion_id)
                ->get('vehicle_configuration_id,sub_vehicle_configuration_id,sub_vehicle_configuration_name,short_production_description');
                 
                 // var_dump($result_set_sub_vehicle_configurations);

            $this->setSubVariant = safe_val($result_set_sub_vehicle_configurations, 0, false);
            $sub_vehicle_configuration_id = $this->setSubVariant['sub_vehicle_configuration_id'];
            $short_production_description = $this->setSubVariant['short_production_description'];

            // var_dump($sub_vehicle_configuration_id);
            // var_dump($short_production_description);

            $result_penta_variant = $this->ladeLeitWartePtr->newQuery('penta_variants')
                ->where("sub_vehicle_configuration_id", "=", $sub_configuraion_id)
                ->get('penta_variant_id,penta_variant_name,old_penta_number_id,sub_vehicle_configuration_id');
                 
                 // var_dump($result_penta_variant);

            $this->setPentaVariant = safe_val($result_penta_variant, 0, false);
            $penta_variant_id = $this->setPentaVariant['penta_variant_id'];
            $penta_variant_name = $this->setPentaVariant['penta_variant_name'];
            $penta_number_id = $this->setPentaVariant['old_penta_number_id'];
            // $sub_vehicle_configuration_id = $this->setPentaVariant['sub_vehicle_configuration_id'];
            // var_dump($penta_variant_id);
            // var_dump($penta_variant_name);

            $variant_battery_part = $llwPtr->newQuery('parts')
                ->where('name', '=', $this->setVariant['battery'])
                ->getVal('part_id');

            if (isset($_POST['vehicle_options'])) {
                if (isset($_POST['vehicle_options'][$variant_battery_part]))
                    unset($_POST['vehicle_options'][$variant_battery_part]);

                $part_id_list = array_keys($_POST['vehicle_options']);
            }

            // Lese HTTP-POST-Werte aus
            foreach ($this->part_groups as $group_id => $group_set) {
                $groupValue = safe_val($_POST['group_value'], $group_id, 0);
                if ($groupValue && ($groupValue != $variant_battery_part) && (substr($groupValue, - 1) != '*')) {
                    $part_id_list[] = $groupValue;
                }
            }
        }

        if (isset($_POST['fahrzeug_add_generieren'])) {
            // $penta_result = $this->checkPentaVariantExists ($this->setVariant, $this->setsColor, $color_id, $part_id_list);
            $edit_penta_number = false;
            $this->PentaMode = "";

            if (substr($windchill_name, 0, 3) == 'E17') {}

            if (! isset($_REQUEST['pentaform']['penta_number'])) {
                $windchill_id = $this->setVariant['vehicle_configuration_id'];
                $windchill_name = $this->setVariant['vehicle_configuration_key'];
                // $color_key = $this->setsColor[$color_id]['color_key'];
                $color_key = $this->setVariant['default_configuration_color_id'];

                $this->penta_result = $llwPtr->vehiclesSalesPtr->getVariantPentaNumber($windchill_id, $windchill_name, $color_id, $color_key, $part_id_list);
                // // var_dump($this->penta_result);

                $_REQUEST['pentaform']['penta_number'] = $this->penta_result['penta_number'];
                $_REQUEST['pentaform']['penta_config_id'] = $this->penta_result['penta_config_id'];
                $_REQUEST['pentaform']['suffix'] = $this->penta_result['suffix'];
                $_REQUEST['pentaform']['penta_parts'] = implode(',', $part_id_list);

                unset($_REQUEST['vehicle_options']);
                unset($_REQUEST['group_value']);

                $edit_penta_number = $this->penta_result['edit_penta_number'];
            }

            // Teste ob durch Penta-Form eingegebene PentaNummer existiert
            if (isset($_POST['pentaform']['create_regular'])) {
                $result = $llwPtr->newQuery('penta_numbers')
                    ->where("penta_number", "=", $_REQUEST['pentaform']['penta_number'] . $_REQUEST['pentaform']['suffix'])
                    ->getVal('penta_number_id');

                $edit_penta_number = ($result ? (count($result) > 0) : false);
            }
            // var_dump($result);

            if ($edit_penta_number) {
                $this->newVehiclesList .= $llwPtr->vehiclesSalesPtr->getPentaForm($_REQUEST['pentaform']['penta_number'], $_REQUEST['pentaform']['suffix'], 'pentaform');
                return;
            } else {
                if (! ($this->penta_result['penta_config_id']))
                    $this->PentaMode = 'new';
                else if (! ($this->penta_result['existing_penta_id']))
                    $this->PentaMode = 'auto';
            }

            $this->PentaNr = $_REQUEST['pentaform']['penta_number'];
            if ($_REQUEST['pentaform']['create_regular']) {
                $this->PentaNr .= $_REQUEST['pentaform']['suffix'];
                $this->PentaMode = 'regular';
            }
            if ($_REQUEST['pentaform']['create_prototype']) {
                $this->PentaMode = 'prototype';
            }

            $this->showNewVehicles();
            return;
        } else if (isset($_POST['fahrzeug_add_step2'])) {
            $this->getVehicleAdd_Step2($thirdparty);
        } else {
            $this->getVehicleAdd_Step1($thirdparty);
        }

    }


    function newvehiclespost()
    {

        if (isset($_GET['batch'])) {
            $this->showNewVehicles();
        } else
            $this->showDPvehicles();

    }


    function showDPvehicles()
    {

        $this->vehiclesPost = $this->ladeLeitWartePtr->vehiclesPostPtr->getUnprocessedBatches();

    }


    // ================================================================================================================================
    function getVehicleAdd_Step1($thirdparty = FALSE)
    {

        $allownoikz = true; // $thirdparty;
        $allownoakz = true; // $thirdparty;

        $this->qform_vehicle = new QuickformHelper($this->displayHeader, "vehicleadd");

        $this->qform_vehicle->salesGetVehicleAdd_Step1($this->vehicle_variants, $allownoikz, $allownoakz, $this->wc_variants, $this->wc_subconfig, $thirdparty);

    }


    // ================================================================================================================================
    function getVehicleAdd_Step2($thirdparty = FALSE)
    {

        $variant_id = $this->setVariant['vehicle_configuration_id'];
        $color_id = $this->setVariant['default_configuration_color_id'];
        $vehicle_variant_wc = $this->setVariant['vehicle_configuration_key'];
        $vehicle_vin_method = $this->setVariant['vin_method'];
        $start_penta_kennwort = $_REQUEST['start_penta_kennwort'];
        $herstellungswerk = $_REQUEST['herstellungswerk'];
        $vehicle_subconfiguration_wc = $_REQUEST['vehicle_subconfiguration_wc'];
        $color_name = $this->setsColor[$color_id]['color_key'];

        // var_dump($vehicle_variant_wc);
        // var_dump($sub_vehicle_configuration_id);

        // @todo 2017-09-08 handle error if vin method is not found for this windchill variant
        $cntvehicles = $_REQUEST['cntvehicles'];
        $tsnummer = $_REQUEST['tsnummer'];
        $vorhaben = $_REQUEST['vorhaben'];
        $part_groups = [];
        $variant_battery = 0;
        $default_battery = 0;
        $herstellungswerke = [];

        $qry = $this->ladeLeitWartePtr->newQuery('divisions')
            ->join('depots', 'using (division_id)')
            ->where('production_location', '=', 't')
            ->where('divisions.production_vin_key', '>=', 'A')
            ->where('divisions.production_vin_key', '<=', 'Z')
            ->orderBy('divisions.production_vin_key');

        $result = $qry->get('*', 'depot_id');

        $default_prod = $this->setVariant['default_production_location_id'];

        foreach ($result as $depot_id => &$set) {
            $name = ($depot_id ? $set['name'] : 'Streetscooter Aachen');

            if ($depot_id == $default_prod)
                $herstellungswerke["*$depot_id"] = $name;
            else
                $herstellungswerke["$depot_id"] = $name;
        }

        $query = $this->ladeLeitWartePtr->newQuery('variant_parts_mapping')
            ->join('parts', 'variant_parts_mapping.part_id=parts.part_id', 'INNER JOIN')
            ->where('variant_id', '=', $variant_id)
            ->where('parts.visible_sales', '=', 't')
            ->orderBy('parts.part_id');

        $result = $query->get('parts.part_id, parts.name, parts.begleitscheinname,group_id');
        if (is_array($result))
            $windchill_parts = array_combine(array_column($result, 'part_id'), array_column($result, 'name'));
        else
            $windchill_parts = [];

        $ql = $this->ladeLeitWartePtr->newQuery('parts')
            ->join('part_groups', 'parts.group_id=part_groups.group_id')
            ->orderBy('parts.group_id')
            ->orderBy('parts.name')
            ->where('visible_sales', '=', 't');

        $qlResult = $ql->get('*');

        foreach ($qlResult as $part) {
            $group_id = $part['group_id'];
            $group_name = $part['group_name'];
            $part_id = $part['part_id'];

            if ($group_id && ($group_id == $this->batteryGroup)) {
                if (strcasecmp($part['name'], $this->setVariant['battery']) == 0) {
                    $variant_battery = $part;
                }

                if (isset($windchill_parts[$part_id])) {
                    $default_battery = $part;
                }
            }

            if (! isset($part_groups[$group_name]))
                $part_groups[$group_name] = [
                    'group' => [
                        'group_id' => $part['group_id'],
                        'group_name' => $part['group_name'],
                        'allow_none' => $part['allow_none'],
                        'allow_multi' => $part['allow_multi'],
                        'group_hidden' => true
                    ],
                    'parts' => []
                ];

            $part_groups[$group_name]['parts'][$part_id] = $part;
            if (toBool($part['visible_sales']))
                $part_groups[$group_name]['group']['group_hidden'] = false;
        }

        if ($default_battery == 0)
            $default_battery = $variant_battery;

        if ($default_battery != 0) {
            $part_id = $default_battery['part_id'];
            $windchill_parts[$part_id] = $default_battery['name'];
        }

        $info_from_step1 = "";
        $info_from_step1 .= '<input type="hidden" name="vehicle_vin_method" value="' . $vehicle_vin_method . "\">\n";
        $info_from_step1 .= '<input type="hidden" name="vehicle_variant_wc" value="' . $variant_id . "\">\n";
        $info_from_step1 .= '<input type="hidden" name="vehicle_subconfiguration_wc" value="' . $vehicle_subconfiguration_wc . "\">\n";
        $info_from_step1 .= '<input type="hidden" name="cntvehicles" value="' . $cntvehicles . "\">\n";
        $info_from_step1 .= '<input type="hidden" name="start_penta_kennwort" value="' . $start_penta_kennwort . "\">\n";

        if (! $thirdparty) {
            $info_from_step1 .= '<input type="hidden" name="tsnummer" value="' . $tsnummer . "\">\n";
            $info_from_step1 .= '<input type="hidden" name="vorhaben" value="' . $vorhaben . "\">\n";
        }

        $colspan = $thirdparty ? 4 : 8;

        $info_from_step1 .= '        <table style="width:800px">' . "
            <tr><th colspan=\"$colspan\" style=\"text-align:center;\"><strong>Fahrzeugtyp</strong></th></tr>
            <tr><th>Vin Verfahren</th><th>Anzahl der Fahrzeuge</th><th>Fahrzeug Windchill Variante</th><th>Start Penta Kennwort</th>";

        if (! $thirdparty) {
            $info_from_step1 .= "<th>Start TS Nummer</th><th>Vorhaben Nummer</th>";
        }

        $info_from_step1 .= "</tr>
            <tr><td>$vehicle_vin_method</td><td>$cntvehicles</td><td>$vehicle_variant_wc</td><td>$start_penta_kennwort</td>";

        if (! $thirdparty) {
            $info_from_step1 .= "<td>$tsnummer</td><td>$vorhaben</td>";
        }

        $info_from_step1 .= "</tr>
        </table>\n";

        $this->qform_vehicle = new QuickformHelper($this->displayHeader, "vehicleadd");

        $this->qform_vehicle->salesGetVehicleAdd_Step2($thirdparty, $info_from_step1, $this->setsColor, $this->setVariant, $vehicle_vin_method, $herstellungswerke, $windchill_parts, null, $part_groups, $default_battery, $this->batteryGroup);

    }


    function genAufbauSelect($classname, $ident = null, $default = 'compartment')
    {

        if ($ident)
            $name_select = "aufbau_" . $ident;
        else
            $name_select = "fahrzeugaufbau_in";
        return '
	<select class="' . $classname . '" id="' . $name_select . '" name="' . $name_select . '">
		<option value="largecompartment" ' . (($default == 'largecompartment') ? ' selected ' : '') . ' >D16 Koffer</option>
        <option value="compartment" ' . (($default == 'compartment') ? ' selected ' : '') . ' >B16 Koffer</option>
		<option value="pritsche" ' . (($default == 'pritsche') ? ' selected ' : '') . '>Pritsche</option>
		<option value="fahrgestell" ' . (($default == 'fahrgestell') ? ' selected ' : '') . '>Fahrgestell/Pure</option>
	</select>';

    }


    /**
     * *
     *
     * @param string $classname
     * @param int $ident
     * @param string $default
     * @return string
     */
    function genAufbauSelectSOP2017($classname, $ident = null, $default = 'epos')
    {

        if ($ident)
            $name_select = "aufbau_" . $ident;
        else
            $name_select = "fahrzeugaufbau_in";
        return '
	<select class="' . $classname . '" id="' . $name_select . '" name="' . $name_select . '">
		<option value="epos" ' . (($default == 'epos') ? ' selected ' : '') . ' >EPOS Post mit Eigenbaukoffer</option>
       	<option value="bpos" ' . (($default == 'bpos') ? ' selected ' : '') . ' >BPOS Post mit Zuliefererkoffer</option>
       	<option value="ebox" ' . (($default == 'ebox') ? ' selected ' : '') . ' >Box mit Eigenbaukoffer</option>
       	<option value="bbox" ' . (($default == 'bbox') ? ' selected ' : '') . ' >Box mit Zuliefererkoffer</option>
		<option value="pick" ' . (($default == 'pick') ? ' selected ' : '') . ' >Pickup</option>
		<option value="pure" ' . (($default == 'pure') ? ' selected ' : '') . ' >Pure</option>
	</select>';

    }


    /**
     * *
     *
     * @param mixed $classname
     * @param mixed $ident
     * @param string $default
     * @return string
     */
    function genFeatureSelectSOP2017($classname, $ident = null, $default = 'epos')
    {

        if ($ident)
            $name_select = "feature_" . $ident;
        else
            $name_select = "feature_in";
        return '
	<select class="' . $classname . '" id="' . $name_select . '" name="' . $name_select . '">
		<option value="doppelsitz" ' . (($default == 'doppelsitz') ? ' selected ' : '') . ' >Doppelsitz</option>
		<option value="notsitz" ' . (($default == 'notsitz') ? ' selected ' : '') . ' >Notsitz</option>
		<option value="letterbox" ' . (($default == 'letterbox') ? ' selected ' : '') . ' >Letterbox</option>
		<option value="doppelsitzrl" ' . (($default == 'doppelsitzrl') ? ' selected ' : '') . ' >Doppelsitzer mit Rundumleuchte</option>
		<option value="doppelsitzlv" ' . (($default == 'doppelsitzlv') ? ' selected ' : '') . ' >Doppelsitzer für Linksverkehr</option>

	</select>';

    }


    /**
     * generates the options for batch/Serien ..
     * currently hardcoded, to be dynamically calculated
     *
     * @param mixed $classname
     * @param mixed $ident
     * @param string $default
     * @return string
     */
    function genBatchSelect($classname, $ident = null, $default = 'A')
    {

        if ($ident)
            $name_select = "batch_" . $ident;
        else
            $name_select = "batch_in";
        return '
	<select class="' . $classname . '" id="' . $name_select . '" name="' . $name_select . '" OnChange="ShowWarning()">
		<option value="A" ' . (($default == 'A') ? ' selected ' : '') . ' >Batch 01</option>
		<option value="B" ' . (($default == 'B') ? ' selected ' : '') . ' >Batch 02</option>
		<option value="C" ' . (($default == 'C') ? ' selected ' : '') . ' >Batch 03</option>
		<option value="D" ' . (($default == 'D') ? ' selected ' : '') . ' >Batch 04</option>
		<option value="E" ' . (($default == 'E') ? ' selected ' : '') . ' >Batch 05</option>
		<option value="F" ' . (($default == 'F') ? ' selected ' : '') . ' >Batch 06</option>
		<option value="G" ' . (($default == 'G') ? ' selected ' : '') . ' >Batch 07</option>
		<option value="H" ' . (($default == 'H') ? ' selected ' : '') . ' >Batch 08</option>
		<option value="I" ' . (($default == 'I') ? ' selected ' : '') . ' >Batch 09</option>
		<option value="J" ' . (($default == 'J') ? ' selected ' : '') . ' >Batch 10</option>
	</select>';

    }


    function parseVariantValForVin($vehicle_variant)
    {

        $parsedvalue = '';
        if (strpos(trim($vehicle_variant), 'B14') !== FALSE)
            $parsedvalue = 'B14';
        else if (strpos(trim($vehicle_variant), 'D16') !== FALSE)
            $parsedvalue = 'D16';
        else
            $parsedvalue = 'B16';
        // @todo 2017-09-09 better method to get this value
        return $parsedvalue;

    }


    function genFahrzeugVar($classname, $ident = null, $default)
    {

        if ($ident)
            $name_select = "vehicle_variant_" . $ident;
        else
            $name_select = "vehicle_variant";

        $returnval = '
		<select class="' . $classname . '" id="' . $name_select . '" name="' . $name_select . '">';
        foreach ($this->vehicle_variants as $key => $vehicle_variant) {
            $returnval .= '<option value="' . $key . '" ' . (($default == $key) ? ' selected ' : '') . '>' . $vehicle_variant . '</option>';
        }

        $returnval .= '</select>';
        return $returnval;

    }


    function genMotorSelect($classname, $ident = null, $default = 'bosch')
    {

        if ($ident)
            $name_select = "motor_" . $ident;
        else
            $name_select = "motortyp_in";
        return '
	<select class="' . $classname . '" id="' . $name_select . '" name="' . $name_select . '">
		<option value="abm" ' . (($default == 'abm') ? ' selected ' : '') . '>ABM</option>
		<option value="bosch" ' . (($default == 'bosch') ? ' selected ' : '') . '>Bosch</option>
	</select>
	';

    }


    function genVinSelect($classname, $ident = null, $default = 'sop2017')
    {

        if ($ident)
            $name_select = "vin_select_" . $ident;
        else
            $name_select = "vin_method";
        return '    <select class="' . $classname . '"  id="' . $name_select . '" name="' . $name_select . '">';

        foreach ($this->vin_methods as $value => $label) {
            echo "		<option value=\"$value\" " . (($default == $value) ? ' selected ' : '') . " >$label</option>\n";
        }

        echo "    </select>\n";

    }


    // $vehicle_variant has to have a default value in order for this function to work as an AJAX request..
    function vinBuilder($windchill_variant_set, $flnum = '00000', $post_variant = null, $motortyp_in = 'bosch', $fahrzeugaufbau_in = 'compartment', $modeljahr = '2015', $vinMethod = 'sop2017', $batch = 'A', $feature_in = 'doppelsitz', $color_code = 1, $herstellungswerk = "A")
    {

        $ajaxquery = $this->requestPtr->getProperty('ajaxquery');
        if ($ajaxquery) {
            $flnum = $this->requestPtr->getProperty('flnum');
            $post_variant = $this->requestPtr->getProperty('vehicle_variant');
            $motortyp_in = $this->requestPtr->getProperty('motortyp_in');
            $modeljahr = $this->requestPtr->getProperty('modeljahr');
            $batch = $this->requestPtr->getProperty('batch');
            $fahrzeugaufbau_in = $this->requestPtr->getProperty('fahrzeugaufbau_in');
            $feature_in = $this->requestPtr->getProperty('feature_in');
            $vinMethod = $this->requestPtr->getProperty('vin_method');
            $herstellungswerk = $this->requestPtr->getProperty('herstellungswerk');
        }

        $vehicle_configuration_country_id = $this->setVariant['vehicle_configuration_id'];
        $query_country_code = $this->ladeLeitWartePtr->newQuery('vehicle_configuration_properties_mapping')
            ->join('allowed_symbols', 'using (allowed_symbols_id)')
            ->where('vehicle_configuration_id', '=', $vehicle_configuration_country_id)
            ->where('vc_property_id', '=', 25)
            ->get('vehicle_configuration_id, vc_property_id, vehicle_configuration_properties_mapping.allowed_symbols_id,symbol');

        $this->country_code = safe_val($query_country_code, 0, false);
        $country_symbol = $this->country_code['symbol'];
        $eu_country = array(A,B,C,D,F,G,H,N);
        $uk_country = array(E);
        $jp_country = array(J);

        switch (true) {
            case in_array($country_symbol, $eu_country):
                $markt = "A";
                break;

            case in_array($country_symbol, $uk_country):
                $markt = "B";
                break;

            case in_array($country_symbol, $jp_country):
                $markt = "C";
                break;

            default:
                $markt = "A";
                break;
        }
        // var_dump($markt);

        $wmi = "WS5"; // 17 16 15

        if (empty($herstellungswerk))
            $herstellungswerk = 'A';

        if (empty($vinMethod))
            $vinMethod = 'sop2017';

        $vmp = $this->GetVinMethodProperties($vinMethod);
        // var_dump($vmp);

        if ($vmp['sop2017']) {
            if ($ajaxquery) {
                if (! isset($feature_in))
                    $feature_in = 'doppelsitz';
                if (! isset($fahrzeugaufbau_in))
                    $fahrzeugaufbau_in = 'epos';
            }
            $windchill_variant_set = $this->setVariant['vehicle_type_name'] . $this->setVariant['vehicle_type_year'];
            // $fahrzeug_typ = substr($windchill_variant_set['type'], 0, 3);
            $fahrzeug_typ = $windchill_variant_set;
            // $batch = $windchill_variant_set['vin_batch']; // 11 becomes batch/serie
            $vin_batch = $this->setVariant['vin_batch'];
            // var_dump($fahrzeug_typ);
            // var_dump($batch);
            
            $fahrzeugaufbau = $markt;
            // $fahrzeugaufbau = "A";
        } else {
            if (is_null($post_variant)) {
                if ($ajaxquery) {
                    echo 'Fehler bei der VIN Erzeugung!';
                    exit(0);
                } else
                    return 'null Fehler bei der VIN Erzeugung!';
            }

            $fahrzeug_typ = $this->parseVariantValForVin($post_variant); // 14 13 12
            // var_dump($fahrzeug_typ);

            if ($motortyp_in == "abm")
                $motortyp = "A"; // 11 motortyp
            else if ($motortyp_in == "bosch")
                $motortyp = "B";

            if ($fahrzeugaufbau_in == "compartment")
                $fahrzeugaufbau = "A"; // position 10
            else if ($fahrzeugaufbau_in == "pritsche")
                $fahrzeugaufbau = "B";
            else if ($fahrzeugaufbau_in == "fahrgestell")
                $fahrzeugaufbau = "C";
            else if ($fahrzeugaufbau_in == "largecompartment")
                $fahrzeugaufbau = "D";
        }

        /**
         * position 9
         */
        if ($vmp['sop']) {
            $batch = $batch; // 9 batch
        } else if ($vmp['sop2017']) {
            $feature = "A";
        } else
            $batch = 1; // 9 Prüfziffer

        $inc = $modeljahr - 2016; // 2016 here since we are finding the code relative to 2016 which is G whose char code is 71
        if ($inc > 1)
            $inc ++;
        $modeljahr = chr(71 + $inc);

        // 7 Aachen

        // $flnum=substr($currentvin,-6);

        if ($flnum >= 99999)
            $flnum = 0;
        else
            $flnum;

        if ($vmp['sop']) {
            $vin = $wmi . $fahrzeug_typ . $motortyp . $fahrzeugaufbau . $batch . $modeljahr . $herstellungswerk . $color_code . str_pad((string) $flnum, 5, '0', STR_PAD_LEFT);
        } else if ($vmp['sop2017']) {
            $vin = "{$wmi}{$fahrzeug_typ}{$batch}{$fahrzeugaufbau}{$feature}{$modeljahr}{$herstellungswerk}";

            if ($flnum < 0) {
                $tmpvin = "{$wmi}{$fahrzeug_typ}_{$fahrzeugaufbau}{$feature}{$modeljahr}{$herstellungswerk}%";

                $flnum = 1;
                $sql = "select max(substring (vin from 13 for 5)) as flnum " . "from vehicles " . "where vin like '$tmpvin%';";

                $query = $this->ladeLeitWartePtr->newQuery();

                if ($query->query($sql)) {
                    $found = $query->fetchArray();
                    if ($found)
                        $flnum = ($found['flnum'] + 1);
                    else
                        $flnum = 1;
                }
            } else {
                $tmpvin = "{$wmi}{$fahrzeug_typ}_{$fahrzeugaufbau}{$feature}{$modeljahr}{$herstellungswerk}";
                while (true) {
                    $testvin = $tmpvin . $color_code . str_pad((string) $flnum, 5, '0', STR_PAD_LEFT);
                    $vin_exists = $this->ladeLeitWartePtr->newQuery('vehicles')
                        ->where('vin', 'ilike', $testvin)
                        ->getVal('count(*)');
                    if (! $vin_exists)
                        break;

                    $flnum ++;
                }
            }
            $vin .= $color_code . str_pad((string) $flnum, 5, '0', STR_PAD_LEFT);
        } else {
            $prufziffer = $this->vinModEleven($wmi . $fahrzeug_typ . $motortyp . $fahrzeugaufbau . $batch . $modeljahr . $herstellungswerk . str_pad((string) $flnum, 5, '0', STR_PAD_LEFT), $vinMethod);
            $vin = $wmi . $fahrzeug_typ . $motortyp . $fahrzeugaufbau . $prufziffer . $modeljahr . $herstellungswerk . $color_code . str_pad((string) $flnum, 5, '0', STR_PAD_LEFT);
        }
        // var_dump($tmpvin);
        // var_dump($vin);
        // var_dump($prufziffer);

        if ($ajaxquery) {
            echo $vin;
            exit(0);
        } else
            return $vin;

    }


    function vinModEleven($vin, $vinMethod)
    {

        // does not do anything about the batchnum... does it have to?
        $alphanum = array(
            'A' => 1,
            'Ä' => 1,
            'B' => 2,
            'C' => 3,
            'D' => 4,
            'E' => 5,
            'F' => 6,
            'G' => 7,
            'H' => 8,
            'I' => 9,
            'J' => 1,
            'K' => 2,
            'L' => 3,
            'M' => 4,
            'N' => 5,
            'O' => 0,
            'Ö' => 0,
            'P' => 7,
            'Q' => 8,
            'R' => 9,
            'S' => 2,
            'T' => 3,
            'U' => 4,
            'Ü' => 4,
            'V' => 5,
            'W' => 6,
            'X' => 7,
            'Y' => 8,
            'Z' => 9
        );

        $vinwert = str_split($vin);
        foreach ($vinwert as $key => $vindigit) {
            if (! is_numeric($vindigit)) {
                if ($key == 9 && $vinMethod == 'pvsold')
                    $vinwert[$key] = 5;
                else
                    $vinwert[$key] = $alphanum[$vindigit];
            }
            if ($vindigit == 10)
                $vinwert[$key] = 'X';
        }

        $summe = '0';

        if ($vinMethod == 'pvs' || $vinMethod == 'pvsold') // 9th position, Pruffziffer to be ignored if using this method. in next loop when i=8, then this 0 is multiplied by ($i+2)=10 ..
            $vinwert[8] = 0;

        // we can also pop out the 8th element instead of doing the following loops
        for ($i = 0; $i <= 8; $i ++) {
            $vinindex = 16 - $i;
            // echo ($i+2).'*'.$vinwert[$vinindex]."<br>";
            $summe += ($i + 2) * $vinwert[$vinindex];
        }
        if ($vinMethod == 'pvs' || $vinMethod == 'pvsold') {
            $summe += 10 * $vinwert[7]; // since we ignored the previous
                                        // echo "10*".$vinwert[7]."<br>";
            for ($i = 10; $i <= 16; $i ++) {
                $vinindex = 16 - $i;
                // echo ($i-8).'*'.$vinwert[$vinindex]."<br>";
                $summe += (($i - 8)) * $vinwert[$vinindex];
            }
        } else
            for ($i = 9; $i <= 16; $i ++) {
                $vinindex = 16 - $i;
                // echo ($i-7).'*'.$vinwert[$vinindex]."<br>";
                $summe += (($i - 7)) * $vinwert[$vinindex];
            }

        return (($summe % 11 == 10) ? 'X' : $summe % 11);

    }


    function showNewVehicles()
    {

        $i = 0;
        $refreshbutton = '<a href="#" class="vin_regen_all_ctrl"><span class="genericon genericon-refresh"></span></a>';

        if (isset($_GET['batch'])) {
            $tempid = (int) $_GET['batch'];
            $thisbatch = $this->ladeLeitWartePtr->vehiclesPostPtr->getWhere(null, array(
                array(
                    'tempid',
                    '=',
                    $tempid
                )
            ));
            extract($thisbatch[0]);
            $cnt = $cntvehicles;
            $flnum = 0;
        } else {
            $cnt = $this->requestPtr->getProperty('cntvehicles');
            $flnum = $this->requestPtr->getProperty('flnum', - 1);
            $vehiclecolor = $this->requestPtr->getProperty('vehiclecolor');
            $vorhaben = $this->requestPtr->getProperty('vorhaben');
            $tsnummer = $this->requestPtr->getProperty('tsnummer');
            $start_penta_kennwort = $this->requestPtr->getProperty('start_penta_kennwort');
            $vehicle_variant = $this->requestPtr->getProperty('vehicle_variant');
            $post_variant = $this->requestPtr->getProperty('post_variant');
            $vehicle_color = $this->requestPtr->getProperty('vehicle_color');
            $vehicle_variant_wc = $this->requestPtr->getProperty('vehicle_variant_wc');
            $vehicle_subconfiguration_wc = $this->requestPtr->getProperty('vehicle_subconfiguration_wc');
            $penta_config_id = $_REQUEST['pentaform']['penta_config_id'];
            $penta_parts = $_REQUEST['pentaform']['penta_parts'];
            $herstellungswerk = $this->requestPtr->getProperty('herstellungswerk');
            // $penta_part_list = empty ($penta_parts) ? [] : explode (',', $penta_parts);
            // var_dump($vehicle_subconfiguration_wc);
        }

        if (empty($this->PentaNr))
            $this->PentaNr = $_REQUEST['pentaform']['penta_number'] . $_REQUEST['pentaform']['suffix'];

        $vehicle_variant_id = $this->setVariant['old_vehicle_variant_id'];
        $penta_number_id = $this->setPentaVariant['old_penta_number_id'];
        $penta_config_id = $this->setPentaVariant['old_penta_number_id'];
        $color_id = $this->setVariant['default_configuration_color_id'];
        $color_name = $this->ladeLeitWartePtr->newQuery('configuration_colors')
            ->where('configuration_color_id', '=', $color_id)
            ->getVal('configuration_color_name');

/*        $color_name = $this->ladeLeitWartePtr->newQuery('colors')
            ->where('color_id', '=', $vehicle_color)
            ->getVal('name');*/

        $vehicle_configuration_set = $this->ladeLeitWartePtr->newQuery('vehicle_configurations')
            ->where("vehicle_configuration_id", "=", $vehicle_variant_wc)
            ->get('vehicle_configuration_id,vehicle_configuration_key,default_configuration_color_id,default_production_location_id');
            // var_dump($vehicle_configuration_set);

         $this->setVehicleConfiguration = safe_val($vehicle_configuration_set, 0, false);
         $vehicle_configuration = $this->setVehicleConfiguration['vehicle_configuration_key'];
         // $herstellungswerk = $this->setVehicleConfiguration['default_production_location_id'];

        $vehicle_sub_configuration_set = $this->ladeLeitWartePtr->newQuery('sub_vehicle_configurations')
            ->where("sub_vehicle_configuration_id", "=", $vehicle_subconfiguration_wc)
            ->get('vehicle_configuration_id,sub_vehicle_configuration_id,sub_vehicle_configuration_name,short_production_description');
        // var_dump($vehicle_sub_configuration_set);

        $this->setVehicleSubConfiguration = safe_val($vehicle_sub_configuration_set, 0, false);
        // $sub_vehicle_configuration_id = $this->setVehicleSubConfiguration['sub_vehicle_configuration_id'];
        $sub_vehicle_configuration_name = $this->setVehicleSubConfiguration['sub_vehicle_configuration_name'];
        $short_production_description = $this->setVehicleSubConfiguration['short_production_description'];

        $result_sub_vehicle_configurations = $this->ladeLeitWartePtr->newQuery('sub_vehicle_configurations')
            ->JOIN('penta_variants', 'using(sub_vehicle_configuration_id)')
            ->where("sub_vehicle_configuration_id", "=", $vehicle_subconfiguration_wc)
            ->get('vehicle_configuration_id,sub_vehicle_configuration_id,sub_vehicle_configuration_name,short_production_description,penta_variant_id');
            // var_dump($result_sub_vehicle_configurations);

            $this->set_vehicle_configurations = safe_val($result_sub_vehicle_configurations, 0, false);
            $vehicle_configuration_id = $this->set_vehicle_configurations['vehicle_configuration_id'];
            // $sub_vehicle_configuration_id = $this->set_vehicle_configurations['sub_vehicle_configuration_id'];
            $sub_vehicle_configuration_name = $this->set_vehicle_configurations['sub_vehicle_configuration_name'];
            $short_production_description = $this->set_vehicle_configurations['short_production_description'];
            $penta_variant_id = $this->set_vehicle_configurations['penta_variant_id'];

        $vehicle_variant_set = $this->ladeLeitWartePtr->newQuery('vehicle_variants')
            ->where('vehicle_variant_id', '=', $vehicle_variant_id)
            ->getOne('vehicle_variant_id,windchill_variant_name,number_of_seats,type,vin_batch,battery,vin_batch,is_dp,fahrzeugvariante');
            // var_dump($vehicle_variant_wc);
            // var_dump($vehicle_variant_set);

        $prod_loc = $this->ladeLeitWartePtr->newQuery('divisions')
            ->join('depots', 'using (division_id)')
            ->where('depots.depot_id', '=', $herstellungswerk)
            ->getOne('production_vin_key, divisions.name');

        $prod_vin_key = $prod_loc ? $prod_loc['production_vin_key'] : 'A';

        if ($herstellungswerk == 0)
            $prod_loc_name = 'Aachen';
        else
            $prod_loc_name = $prod_loc ? $prod_loc['name'] : '{n.d.}';

        if (toBool($vehicle_variant_set['is_dp'])) {
            $ex = $this->ladeLeitWartePtr->vehicleVariantsPtr->GetExternalName($vehicle_variant_wc, $penta_part_list);
            $post_name = $ex['external_name'];
            $post_id = $ex['external_id'];
        } else {
            $post_name = "";
            $post_id = 0;
        }

        $motortyp_in = $this->requestPtr->getProperty('motortyp_in');
        $batch_in = $this->requestPtr->getProperty('batch_in');
        $modeljahr = $this->requestPtr->getProperty('modeljahr');
        $fahrzeugaufbau_in = $this->requestPtr->getProperty('fahrzeugaufbau_in');

        $vinMethod = $this->requestPtr->getProperty('vehicle_vin_method', null);
        $thirdparty = $this->requestPtr->getProperty('thirdparty');

        if (empty($vinMethod))
            $vinMethod = 'sop2017';

        $vmp = $this->GetVinMethodProperties($vinMethod);

        if ($vmp['sop2017']) {
            $feature_in = $this->requestPtr->getProperty('feature_in');
            if (! isset($feature_in))
                $feature_in = 'doppelsitz';

            if (! isset($fahrzeugaufbau_in))
                $fahrzeugaufbau_in = 'epos';
        }

        if ($vmp['sop']) {
            if (! isset($fahrzeugaufbau_in)) {
                $vehicle_variants_aufbau = $this->ladeLeitWartePtr->newQuery('vehicle_variants')
                    ->where('vehicle_variant_id', '=', $vehicle_variant_wc)
                    ->getVal('fahrzeugvariante');

                if ($vehicle_variants_aufbau == 'Koffer' && strpos($post_name, 'D16') !== false)
                    $fahrzeugaufbau_in = 'largecompartment';
                else if ($vehicle_variants_aufbau == 'Koffer' && strpos($post_name, 'B16') !== false)
                    $fahrzeugaufbau_in = 'compartment';
                else if ($vehicle_variants_aufbau == 'Pritsche')
                    $fahrzeugaufbau_in = 'pritsche';
                else if ($vehicle_variants_aufbau == 'Pure')
                    $fahrzeugaufbau_in = 'fahrgestell';
                else
                    $fahrzeugaufbau_in = 'compartment';
            }
        }

        if (! isset($motortyp_in))
            $motortyp_in = "bosch";

        $ordA = ord('A') - 1;
        if (preg_match('/^[BDE][123][0-9]([012][0-9]).*/', $vehicle_variant_set['windchill_variant_name'], $match)) {
            $batch_no = intval($match[1]);
            $batch_in = chr($ordA + ($batch_no >= 9 ? $batch_no + 1 : $batch_no));
        } else {
            $batch_in = $vehicle_variant_set['vin_batch'];
            $batch_i = ord($batch_in);
            if ($batch_i > 9)
                $batch_i --;
            $batch_no = sprintf('%02d', ord($batch_in) - $ordA);
        }

        // move to the block above since this depends on the vin method chosen if(!isset($fahrzeugaufbau_in)) $fahrzeugaufbau_in='compartment';
        if (! isset($modeljahr))
            $modeljahr = date('Y'); // default at 2015 until we fix the VIN problem

        $heading = array();
        $heading[] = array(
            'Nr.',
            ''
        );
        if (! $thirdparty)
            $heading[] = array(
                'TS Nummer',
                ''
            );
        $heading[] = array(
            'Penta Kennwort',
            ''
        );
        
        $vehicle_color = $this->setVariant['default_configuration_color_id'];
        if ($vehicle_color) {
            $color_code = $this->ladeLeitWartePtr->newQuery('configuration_colors')
                ->where('configuration_color_id', '=', $vehicle_color)
                ->getVal('vin_color_code');
        }
        if (empty($color_code))
            $color_code = 1;
        
        if ($flnum < 0) {
            $max_vin_num = 0;
            $tmpVIN = $this->vinBuilder($vehicle_variant_set, - 1, $post_name, $motortyp_in, $fahrzeugaufbau_in, $modeljahr, $vinMethod, $batch_in, $feature_in, $color_code, $prod_vin_key);
            $flnum = intval(substr($tmpVIN, - 5));
        }

        // $heading[]=array('Fahrzeug Typ<br>'.$this->genFahrzeugVar('vehicle_variant_grp vin_regen_all',null,$post_name).$refreshbutton);
        // todo: style="width" durch eine css-Klasse ersetzen.
        if (! $thirdparty)
            $heading[] = array(
                'Vorhaben <br><input type="text" style="width:120px;" class="vorhaben_grp" name="vorhaben" value="' . $vorhaben . '">' . $refreshbutton,
                ''
            );
        if ($vinMethod == 'sop2017') {
            // $heading[]=array('Batch<br>'.$this->genBatchSelect('batch_select_grp vin_regen_all',null,$batch_in).$refreshbutton,'');
        } else if ($vinMethod != 'ext_import') {
            // $heading[]=array('Batch<br>'.$this->genBatchSelect('batch_select_grp vin_regen_all',null,$batch_in).$refreshbutton,'');
            $heading[] = array(
                'Fahrzeug Aufbau <br>' . $this->genAufbauSelect('aufbau_select_grp vin_regen_all', null, $fahrzeugaufbau_in) . $refreshbutton,
                ''
            );
        }

        if ($vinMethod != 'ext_import') {
            $heading[] = array(
                'Modeljahr <br><input type="number" style="width:60px;" class="modeljahrchange vin_regen_all" name="modeljahr" value="' . $modeljahr . '">' . $refreshbutton,
                ''
            );
            $heading[] = array(
                'Fortlaufende Nummer <br><input type="text" maxlength="5" class="flnum_ctrl_grp vin_regen_all" name="flnum" value="' . str_pad($flnum, 5, '0', STR_PAD_LEFT) . '">' . $refreshbutton,
                ''
            );
        }

        // if(!$thirdparty)
        $heading[] = array(
            'VIN Nummer <img src="images/ajax-loader.gif" class="ajaxload init_hidden" >',
            ''
        );

        $vehicleshdng[] = array(
            'headingone' => $heading
        );

        if ($vinMethod == 'ext_import') {
            $vehicle_pre_vins = [];
            if (isset($_FILES["csvfilevins"])) {
                $delimiter = ';';
                $filename = $_FILES['csvfilevins']['tmp_name'];

                if (($handle = fopen($filename, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, null, $delimiter)) !== FALSE) {
                        $vehicle_pre_vins[] = $data[0];
                    }
                }
            }

            if (! empty($_POST['vinliste'])) {
                $vinliste = str_replace([
                    "\r\n",
                    "\r",
                    "\n",
                    ";"
                ], [
                    ",",
                    ",",
                    ",",
                    ","
                ], $_POST['vinliste']);
                $vinliste = str_replace([
                    " ",
                    '"',
                    "'"
                ], [
                    '',
                    '',
                    ''
                ], $vinliste);
                $vinarray = explode(',', $vinliste);
                foreach ($vinarray as $vin)
                    if (! empty($vin))
                        $vehicle_pre_vins[] = $vin;
            }
            $cnt = count($vehicle_pre_vins);
        }

        $tsnumbertext = '';
        $next_number = $flnum;

        for ($i = 0; $i < $cnt; $i ++) {
            $vehicle = array();
            $inputref = $i + 1;

            $vehicle = array();
            $vehicle["cnt"] = $i + 1;

            if (! empty($tsnumbertext)) {
                $tsnumberval ++;
            } else if (! empty($tsnummer)) {
                preg_match('#([0-9]+)$#', $tsnummer, $tmatches);
                $tsnumberval = $tmatches[0];
                $tsnumbertext = preg_replace('#([0-9]+)$#', '', $tsnummer);
            } else
                $tsnumbertext = $tsnumberval = '';

            if (! empty($pnumbertext)) {
                $pnumberval ++;
            } else if (! empty($start_penta_kennwort)) {
                preg_match('#([0-9]+)$#', $start_penta_kennwort, $matches);
                $pnumberval = $matches[0];
                $pnumbertext = preg_replace('#([0-9]+)$#', '', $start_penta_kennwort);
            } else
                $pnumbertext = $pnumberval = '';

            if (! $thirdparty)
                $vehicle["tsnumber"] = '<input type="text" id="tsnumber_' . $inputref . '" name="tsnumber_' . $inputref . '" value="' . $tsnumbertext . $tsnumberval . '">';
            $vehicle["penta_kennwort"] = '<input type="text" id="penta_kennwort_' . $inputref . '" name="penta_kennwort_' . $inputref . '" value="' . $pnumbertext . $pnumberval . '">';

            if (! $thirdparty)
                $vehicle["vorhaben"] = '<input type="text" style="width:120px;" id="vorhaben_' . $inputref . '" name="vorhaben_' . $inputref . '" value="' . $vorhaben . '">';
            $vehicle["cnt"] = $i + 1;

            if ($vinMethod == 'sop2017') {
                // $vehicle["batch"] =$this->genBatchSelect('batch_select vin_regen',$inputref,$batch_in);
                $vin = $this->vinBuilder($vehicle_variant_set, $next_number, $post_name, $motortyp_in, $fahrzeugaufbau_in, $modeljahr, $vinMethod, $batch_in, $feature_in, $color_code, $prod_vin_key);
                $flnum = intval(substr($vin, - 5));
                $next_number = $flnum + 1;
            } else if ($vinMethod != 'ext_import') {
                $vehicle["batch"] = $this->genBatchSelect('batch_select vin_regen', $inputref, $batch_in);
                $vehicle["aufbau"] = $this->genAufbauSelect('aufbau_select vin_regen', $inputref, $fahrzeugaufbau_in);
                $vin = $this->vinBuilder($vehicle_variant_set, $flnum + $i, $post_name, $motortyp_in, $fahrzeugaufbau_in, $modeljahr, $vinMethod, $batch_in, $feature_in, $color_code, $prod_vin_key);
            }

            if ($vinMethod != 'ext_import') {
                $vehicle["modeljahr"] = '<input type="text" size="4" style="width:60px;" class="submodeljahr vin_regen" id="modeljahr_' . $inputref . '" name="modeljahr_' . $inputref . '" value="' . $modeljahr . '" OnChange="javascript:ShowWarning()">'; // @todo should not be hardcoded..

                if ($vehiclecolor)
                    $flnum = $vehiclecolor * 100000;
                $vehicle["flnum"] = '<input type="text" class="vin_regen" id="flnum_' . $inputref . '" name="flnum_' . $inputref . '" value="' . str_pad($flnum, 5, '0', STR_PAD_LEFT) . '">';
                $vehicle["vin"] = '<input type="text" class="vin" id="vin_' . $inputref . '" name="vin_' . $inputref . '" value="' . $vin . '" OnChange="javascript:ShowWarning()">';
            } else {
                $vehicle["vin"] = $vehicle_pre_vins[$i] . '<input type="hidden" class="vin" id="vin_' . $inputref . '" name="vin_' . $inputref . '" value="' . $vehicle_pre_vins[$i] . '">';
            }

            $vehicles[] = $vehicle;
        }

        $vehicleshdng = array_merge($vehicleshdng, $vehicles);
        $displaytable = new DisplayTable($vehicleshdng);

        $this->newVehiclesList .= '<div class="warnbox" id="warnbox">Das Ändern von "Batch", "Modelljahr" oder der "VIN Nummer" führt zur Inkonsistenz in Bezug auf das VIN-Verfahren!</div>';
        $this->newVehiclesList .= "
<script>
function ShowWarning()
{
    warnbox = document.getElementById ('warnbox');
    if (warnbox)
    {
         warnbox.style.visibility = 'visible';
    }
}
</script>
";

        $this->newVehiclesList = <<<HEREDOC
<div style="overflow-x:scroll">
  <form method="post" name="form_vehicles" action="{$_SERVER['PHP_SELF']}">
    <h3>Neue Fahrzeuge anlegen</h3>
    <table class="white noborder" style="margin-bottom: 20px;">
      <tr>
        <th>Konfiguration:</th>
        <th>PPS / Penta Artikelnummer:</th>
        <th>Postbezeichnung:</th>
        <th>Batch:</th>
        <th>Farbe:</th>
        <th>Herstellungswerk:</th>
      </tr>
      <tr>
        <td>{$vehicle_configuration}</td>
        <td>{$sub_vehicle_configuration_name}</td>
        <td>$post_name</td>
        <td>$batch_no ($batch_in)</td>
        <td>$color_name</td>
        <td>$prod_loc_name</td>
      </tr>
    </table>

    <input type="hidden" name="action" class="action_newvehicles" value="{$this->action}">
    <input type="hidden" name="fahrzeug_add_generieren" class="" value="generieren">
    <input type="hidden" name="tsnummer" class="" value="$tsnummer">
    <input type="hidden" name="start_penta_kennwort" class="" value="$start_penta_kennwort">
    <input type="hidden" name="motortyp_in" class="" value="$motortyp_in">
    <input type="hidden" name="vehicle_color" class="" value="$color_id">
    <input type="hidden" name="penta_parts" class="" value="$penta_parts">
    <input type="hidden" name="vehicle_vin_method" class="" value="$vinMethod">
    <input type="hidden" name="post_name" class="" value="$post_name">
    <input type="hidden" name="post_id" class="" value="$post_id">
    <input type="hidden" name="vehicle_variant_wc" class="" value="$vehicle_variant_wc">
    <input type="hidden" name="vehicle_variant_id" class="" value="$vehicle_variant_id">
    <input type="hidden" name="penta_number" class="" value="$vehicle_configuration">
    <input type="hidden" name="penta_number_id" class="" value="$penta_variant_id">
    <input type="hidden" name="penta_config_id" class="" value="$penta_variant_id">
    <input type="hidden" name="penta_variant_id" class="" value="$penta_variant_id">
    <input type="hidden" name="vehicle_subconfiguration_wc" class="" value="$vehicle_subconfiguration_wc">
    <input type="hidden" name="sub_configuration_id" id="sub_configuration_id" value="">
    <input type="hidden" name="penta_create_mode" class="" value="{$this->PentaMode}">
    <input type="hidden" name="herstellungswerk" class="" value="$herstellungswerk">
    <input type="hidden" name="thirdparty" class="" value="$thirdparty">
    <input type="hidden" name="cntvehicles" class="cntvehicles" value="$cnt">
HEREDOC;

        $this->newVehiclesList .= $displaytable->getContent();
        $this->newVehiclesList .= '<br>
    <input type="submit" name="save_vehicles_db" class="save_vehicles_db" value="Neue Fahrzeuge in Datenbank speichern">
  </form>
</div>
';

    }


    /**
     * save vehicle after generating the VINs
     */

    function saveNewVehicles()
    {

        // if(isset($_POST['save_vehicles_db']))
        {
            $vehicles = array();

            $penta_part_list = [];
            if (isset($_POST['penta_parts']))
                $penta_part_list = explode(',', $_POST['penta_parts']);

            $cntvehicles = $_POST['cntvehicles'];
            $variant_id = $_POST['vehicle_variant_wc'];
            $vehicle_color = $_POST['vehicle_color'];
            $vehicle_variant_id = $_POST['vehicle_variant_id'];
            $penta_number = $_POST['penta_number'];
            $penta_config_id = $_POST['penta_config_id'];
            $penta_number_id = $_POST['penta_number_id'];
            $vehicle_subconfiguration_wc = $_POST['vehicle_subconfiguration_wc'];
            $penta_variant_id = $_POST['penta_variant_id'];
            $penta_create_mode = $_POST['penta_create_mode'];
            $thirdparty = $_POST['thirdparty'];

            $external_id = 0;
            $penta_num_create = 1;
            $penta_numbers = [];
            $penta_numbers_ids = [];
            $penta_internal_use = 'f';
            $penta_distincts = [];

            $mapping_insert_cols = [
                'penta_number_id',
                'part_id'
            ];

/*            if ($penta_create_mode == 'prototype') {
                $penta_internal_use = 't';
                $penta_num_create = $cntvehicles;
                for ($i = 1; $i <= $penta_num_create; $i ++) {
                    $vin = $_POST["vin_$i"];
                    $penta_numbers[] = $penta_number . '_' . $vin;
                }
            } else {
                $penta_num_create = 1;
                $penta_numbers[] = $penta_number;
            }

            for ($i = 0; $i < $penta_num_create; $i ++) {
                $res = $this->ladeLeitWartePtr->vehiclesPtr->newQuery('penta_numbers')
                    ->where('penta_number', '=', $penta_numbers[$i])
                    ->where('color_id', '=', $vehicle_color)
                    ->getOne('penta_number_id,penta_config_id');

                $pid = safe_val($res, 'penta_number_id', false);
                $penta_config_id = safe_val($res, 'penta_config_id', 0);

                if ($pid === false) {
                    $insertCols = [
                        'penta_number' => $penta_numbers[$i],
                        'vehicle_variant_id' => $variant_id,
                        'color_id' => $vehicle_color,
                        'penta_config_id' => $penta_config_id
                    ];

                    $this->ladeLeitWartePtr->vehiclesPtr->newQuery('penta_numbers')->insert($insertCols);

                    $pid = $this->ladeLeitWartePtr->vehiclesPtr->newQuery('penta_numbers')
                        ->where('penta_number', '=', $penta_numbers[$i])
                        ->where('color_id', '=', $vehicle_color)
                        ->getVal('penta_number_id');

                    if ($pid === false) {
                        echo "Kann Penta-Nummber '{$penta_numbers[$i]}' nicht anlegen";
                        die();
                    } else if (! $penta_config_id) {
                        $penta_config_id = $pid;
                        $this->ladeLeitWartePtr->vehiclesPtr->newQuery('penta_numbers')
                            ->where('penta_number', '=', $penta_numbers[$i])
                            ->update([
                            'penta_config_id'
                        ], [
                            $penta_config_id
                        ]);
                    }

                    foreach ($penta_part_list as $part_id)
                        $mapping_insert_vals[] = [
                            $pid,
                            $part_id
                        ];

                    $this->ladeLeitWartePtr->vehiclesPtr->newQuery('penta_number_parts_mapping')->insertMultiple($mapping_insert_cols, $mapping_insert_vals);
                }

                $penta_distincts[$pid] = 1;
                if ($penta_create_mode == 'prototype')
                    $penta_numbers_ids[$i] = $pid;
                else
                    $penta_numbers_ids = array_fill(0, $cntvehicles, $pid);
            }*/

            for ($i = 1; $i <= $cntvehicles; $i ++) {

                $vehicle = array();
                $vinindex = 'vin_' . $i;
                $akzindex = 'akzval_' . $i;
                $ikzindex = 'ikzval_' . $i;
                $tsindex = 'tsnumber_' . $i;
                $penta_index = 'penta_kennwort_' . $i;

                $vehicle['vin'] = $_POST[$vinindex];
                $vehicle['usable_battery_capacity'] = 20480;
                $vehicle['penta_kennwort'] = $_POST[$penta_index];
                $vehicle['post_id'] = $_POST['post_id'];
                $vehicle['tsnumber'] = $_POST[$tsindex];
                $vehicle['vorhaben'] = $_POST['vorhaben_' . $i];
                $vehicle['color_id'] = $vehicle_color;
                $vehicle['vehicle_variant_wc'] = $variant_id;
                $vehicle['penta_number_id'] = $penta_number_id; // $penta_numbers_ids[$i - 1];
                $vehicle['vehicle_variant_id'] = $vehicle_variant_id;
                $vehicle['sub_vehicle_configuration_id'] = $vehicle_subconfiguration_wc;
                $vehicle['penta_variant_id'] = $penta_variant_id;
                $vehicle['vehicle_options'] = null; // $vehicle_options;
                $vehicle['production_location'] = intval(safe_val($_POST, 'herstellungswerk', 0));
                $vehicle['depot_id'] = intval(safe_val($_POST, 'herstellungswerk', 0));

                $vehicles[] = $vehicle;
            }
             // var_dump($vehicle);

            $startakz = $vehicles[0]['code'];
            $lastindex = $_POST['cntvehicles'] - 1;
            $endakz = $vehicles[$lastindex]['code'];

            $startvin = $vehicles[0]['vin'];
            $endvin = $vehicles[$lastindex]['vin'];
            $dbcol = 'vin';

            $vehicles_saved = $this->ladeLeitWartePtr->vehiclesPtr->addMultipleVehicles($vehicles);

            if (is_numeric($vehicles_saved)) {

                $csvlink = $this->saveexportcsv('', $dbcol, $startvin, $endvin);
                $pdflink = $this->saveexportpdf($dbcol, $startvin, $endvin);
                $this->msgs[] = $vehicles_saved . ' Fahrzeuge in Datenbank gespeichert <br><h2>CSV Datei</h2>' . $csvlink . '<h2>PDF Datei</h2>' . $pdflink;

                // find the VINs added to the database and send them to web2:3423 to initiate ODX files creation
                $start_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vin', '=', $startvin)
                    ->getVal('vehicle_id');
                $end_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vin', '=', $endvin)
                    ->getVal('vehicle_id');
                $result = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                    ->where('vehicle_id', '>=', $start_vehicle_id)
                    ->where('vehicle_id', '<=', $end_vehicle_id)
                    ->get('vin');


                 // $vins=array_column($result,'vin');

                 // $odx_result=$this->ladeLeitWartePtr->vehiclesPtr->sendODXCreate($vins);

                 // if($odx_result!==true)
                 // {
                 // $this->msgs[]='ODX Datein für die Fahrzeuge würden nicht erstellt. Bitte <a href="?action=sendodx&startvid='.$start_vehicle_id.'&endvid='.$end_vehicle_id.'">hier</a> klicken nochmal zu versuchen.';
                 // }

            } else {
                $this->msgs[] = ' 0 Fahrzeuge in Datenbank gespeichert. Fehler: ' . $vehicles_saved;
            }
        }

    }

    /**
     * *
     * sendodx
     * When vehicle vins are added to the database and the sendODXCreate function returns an error, then
     * this function is called to try again.
     * Function takes fetches start vehicle_id and end vehicle_id as GET/POST parameters
     */
    function sendodx()
    {

        $start_vehicle_id = $this->requestPtr->getProperty('startvid');
        $end_vehicle_id = $this->requestPtr->getProperty('endvid');
        $result = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '>=', $start_vehicle_id)
            ->where('vehicle_id', '<=', $end_vehicle_id)
            ->get('vin');
        $vins = array_column($result, 'vin');

        $odx_result = $this->ladeLeitWartePtr->vehiclesPtr->sendODXCreate($vins);

        if ($odx_result !== true) {
            $this->msgs[] = 'ODX Datein für die Fahrzeuge würden nicht erstellt. Bitte <a href="?action=sendodx&startvid=' . $start_vehicle_id . '&endvid=' . $end_vehicle_id . '">hier</a> klicken nochmal zu versuchen.';
        }

    }


    function overviewedit()
    {

        $changedfields = $this->requestPtr->getProperty('changedfields');
        if (! empty($changedfields))
            foreach ($changedfields as $field) {
                $newval = $field[1];
                $params = explode('-', $field[0]);
                $newcol = $params[0];
                $vehicle_id = $params[1];

                if ($newval == '--')
                    continue;
                else if (empty($newval))
                    $newval = NULL;

                if ($newcol == 'vin' || $newcol == 'code' || $newcol == 'ikz' || $newcol == 'penta_kennwort') {
                    $result = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                        ->where('vehicle_id', '=', $vehicle_id)
                        ->update(array(
                        $newcol
                    ), array(
                        $newval
                    ));
                } else // @todo 20160719 what if the vehicle_id does not exist in the vehiclesSales table?
                       // update 2016-01-12 All entries in the vehicles table will have corresponding entry in the vehicles_sales table since we have cleaned up the DB now
                {
                    $result = $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()
                        ->where('vehicle_id', '=', $vehicle_id)
                        ->update(array(
                        $newcol
                    ), array(
                        $newval
                    ));
                }

                if (! $result)
                    $output[] = 'Fehler beim Speichern : Vehicle Id ' . $vehicle_id;
            }

        if (! empty($output))
            echo implode("\n", $output);
        else
            echo 'Alle änderungen gespeichert!';
        exit(0);

    }


    function processResultSet($result)
    {

        // error_reporting(0); // send error to the error log instead of directly echoing it. This prevents uncaught error for the json
        foreach ($result as &$vehicle) {
            $vehicleid = $vehicle['vehicle_id'];

            foreach ($vehicle as $key => &$eachcol) {
                if (! $eachcol)
                    $eachcol = '--';

                if ($key == 'vehicle_id' || $key == 'dname')
                    $eachcol = '<span id="' . $key . '-' . $vehicleid . '" >' . $eachcol . '</span>';
                else if ($key == 'production_date' || $key == 'delivery_date') {
                    if ($eachcol != '--')
                        $eachcol = '<span id="' . $key . '-' . $vehicleid . '" class="editable">' . date('Y-m-d', strtotime($eachcol)) . '</span>';
                    else
                        $eachcol = '<span id="' . $key . '-' . $vehicleid . '" class="editable">--</span>';
                } else if ($key == 'comments') {
                    $eachcol = '<textarea id="' . $key . '-' . $vehicleid . '" class="checkinside">' . $eachcol . '</textarea>';
                } else
                    $eachcol = '<span id="' . $key . '-' . $vehicleid . '" class="editable">' . $eachcol . '</span>';
            }
        }
        return $result;

    }


    function ajaxRows()
    {

        $page = $this->requestPtr->getProperty('page');
        $size = $this->requestPtr->getProperty('size');
        $fcol = $this->requestPtr->getProperty('filter');
        $scol = $this->requestPtr->getProperty('column'); // 1 desc 0 asc

        $selectCols = array(
            'vehicles.vehicle_id',
            'vehicles.penta_kennwort',
            'vehicles_sales.tsnumber',
            'vehicles.vin',
            'vehicles.code',
            'vehicles.ikz',
            'vehicles_sales.production_date',
            'vehicles_sales.delivery_date',
            'vehicles_sales.delivery_week',
            'vehicles_sales.coc',
            'vehicles_sales.vorhaben',
            'depots.name as dname',
            'vehicles_sales.comments'
        );

        $rows = $this->ladeLeitWartePtr->vehiclesSalesPtr->populateTable($selectCols, $page, $size, $fcol, $scol);

        $rows = $this->processResultSet($rows);
        $totalrows = $this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()->getVal('count(vehicle_id)');
        $result['total_rows'] = $totalrows;
        $result['fcol'] = json_encode($fcol);
        $result['page'] = $page;
        $result['size'] = $size;
        $result['rows'] = $rows;

        echo json_encode($result);
        exit(0);

        // $headings[]["headingone"]=array('ID','Tabelle','Update Columns','Alte Werte','Neue Werte','Benutzer Name','Timestamp','WHERE','Affected Ids');
    }


    function overview()
    {

        $selectCols = array(
            'vehicles.vehicle_id',
            'vehicles_sales.tsnumber',
            'vehicles.penta_kennwort',
            'vehicles.vin',
            'vehicles.code',
            'vehicles.ikz',
            'vehicles_sales.production_date',
            'vehicles_sales.shipping_date',
            'vehicles_sales.delivery_date',
            'vehicles_sales.delivery_week',
            'vehicles_sales.coc',
            'vehicles_sales.vorhaben',
            'depots.name as dname',
            'vehicles_sales.comments'
        );
        $this->vehicles = $this->ladeLeitWartePtr->vehiclesSalesPtr->populateTable($selectCols, 0, 25);

        // $this->vehicles=$this->ladeLeitWartePtr->vehiclesSalesPtr->getVehicleOverview($selectCols);

        $this->vehiclesHeadings = array(
            'Datenbank ID',
            'Penta Kennwort',
            'TS Nummer',
            'VIN',
            'Kennzeichen',
            'IKZ',
            'Fertigungsdatum',
            'Auslieferungsdatum',
            'Anlieferungsdatum',
            'Auslieferungswoche',
            'CoC Nr.',
            'Vorhaben Nr.',
            'Zugeordente ZSP',
            'Kommentare'
        );

        // array('Kommentare',array('class'=>"filter-false sorting-false"))
    }


    function save_thirdparty_vehicle()
    {

        // assign vehicles to this order..
        // set new depot_id
        // generate the lieferschein
        $order_num = (int) $_POST['order_num'];
        $vehicle_id = (int) $_POST['vehicle_id'];
        $third_party_order = $this->ladeLeitWartePtr->thirdpartyOrdersPtr->newQuery()
            ->where('order_num', '=', $order_num)
            ->getOne('order_num,delivery_date,vehicle_variant_label,vehicle_color,depot_id,pr_contact,pr_tel,penta_folge_id,vehicle_delivered');
        $depot_id = $third_party_order['depot_id'];
        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->update(array(
            'depot_id',
            'finished_status'
        ), array(
            $depot_id,
            'FALSE'
        ));
        // update delivery_date
        $this->msgs[] = $_POST['order_num'] . ' saved' . $vehicle_id . ' depot ' . $depot_id;
        $this->action = 'thirdparty_delivery';
        // $this->saveDrittkundenExportLieferschein($vehicle_id,$depot_id);
        // $this->pentaCSVExport($processed_vehicle_ids);
        $this->thirdparty_delivery();

    }


    function thirdparty_delivery()
    {

        $this->quickform_thirdparties = array();
        $vehicle_color = $vehicle_construction = NULL;
        if (isset($_POST['vehicle_color']))
            $vehicle_color = $this->requestPtr->getProperty('vehicle_color');
        if (isset($_POST['vehicle_construction']))
            $vehicle_construction = $this->requestPtr->getProperty('vehicle_construction');

        $quickform_helper = new QuickformHelper($this->displayHeader, 'filter_vehicles');
        $quickform_helper->form->addElement('static', '')->setContent('<strong>Verfügbare Fahrzeuge filtern nach :</strong>');
        $vehicle_color_select = $quickform_helper->form->addElement('select', 'vehicle_color', null, array(
            'options' => array(
                '' => 'Alle',
                '8' => 'Weiß',
                '7' => 'Orange',
                '6' => 'Gelb'
            )
        ))->setLabel('Farbe');
        if ($vehicle_color)
            $vehicle_color_select->setValue($vehicle_color);
        $vehicle_construction_select = $quickform_helper->form->addElement('select', 'vehicle_construction', null, array(
            'options' => array(
                '' => 'Alle',
                'A' => 'Compartment',
                'B' => 'Pritsche',
                'C' => 'Fahrgestell',
                'D' => 'Large Compartment'
            )
        ))->setLabel('Fahrzeug Aufbau');
        if ($vehicle_construction)
            $vehicle_construction_select->setValue($vehicle_construction);
        $quickform_helper->form->addElement('hidden', 'action')->setValue('thirdparty_delivery');
        $quickform_helper->form->addElement('submit', 'vehicle_filter_submit', array(
            'value' => 'Filtern'
        ));

        $this->quickform_thirdparties[] = $quickform_helper->getContent();

        $third_party_orders = $this->ladeLeitWartePtr->thirdpartyOrdersPtr->newQuery()
            ->where('vehicle_delivered', 'IS', 'NULL')
            ->orderBy('delivery_date')
            ->limit(100)
            ->get('order_num,delivery_date,vehicle_variant_label,vehicle_color,depot_id,pr_contact,pr_tel,penta_folge_id,vehicle_delivered');
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariants(null, 'production', 'thirdparty', $vehicle_color, $vehicle_construction);

        $pro_vehicles = array();
        foreach ($vehicles as $vehicle) {
            $pro_vehicles[$vehicle['vehicle_id']] = $vehicle['vin'] . '(' . $vehicle['code'] . ')';
        }

        foreach ($third_party_orders as $order) {
            $depot = $this->ladeLeitWartePtr->depotsPtr->newQuery()
                ->where('depot_id', '=', $order['depot_id'])
                ->getOne('name,street,housenr,postcode,place');
            if (is_array($depot))
                $order = $order + $depot;
            $order['name'] = str_replace('Dummy ZSP', '', $depot['name']);
            unset($order['depot_id']);
            $quickform_helper = new QuickformHelper($this->displayHeader, 'quickform' . $order['order_num']);
            $quickform_helper->genThirdPartyDelivery($order, $pro_vehicles);
            $this->quickform_thirdparties[] = $quickform_helper->getContent();
        }

    }


    function saveDrittkundenExportLieferschein($vehicle_id, $depot_id)
    {

        $pickup = '';

        // table headings
        $header = array(
            'Pos.',
            'AKZ',
            'VIN',
            'IKZ',
            iconv('UTF-8', 'windows-1252', 'Ladesäule')
        );
        // Column widths
        $w = array(
            10,
            30,
            55,
            45,
            40
        );

        $pickup_pdf = new FPDF();

        $fill = false;
        $pickup_pdf->SetFillColor(241, 241, 241);
        $cnt = 1;

        $cnt = 1;
        $pickup_pdf->AddPage();

        $pickup_pdf_break = false;
        $pickup_pdf_header = true;

        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicles.vehicle_id', '=', $vehicle_id)
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
            ->join('penta_numbers', 'using(penta_number_id')
            ->orderBy('vehicles_sales.qs_user,vehicles_sales.production_date', 'ASC')
            ->getOne('vehicles.vehicle_id,vehicles.ikz,vehicles.vin,
                        vehicles_sales.vorhaben,vehicles_sales.vehicle_variant,
                        vehicles_sales.delivery_date,vehicles.code,
                        vehicles_sales.production_date,vehicles_sales.qs_user');

        $depot = $this->ladeLeitWartePtr->depotsPtr->newQuery()
            ->where('depot_id', '=', $depot_id)
            ->getOne('*');

        $fhandle = fopen('/var/www/lieferschein_template_drittkunden.fodt', 'r');
        $fcontent = fread($fhandle, filesize('/var/www/lieferschein_template_drittkunden.fodt'));
        $fhandle_new = fopen('/var/www/lieferschein_template_drittkunden_' . $vehicle['vehicle_id'] . '.fodt', 'w');

        $variant_name = $vorhaben = $vehicle_variant = '';

        if (isset($vehicle['vorhaben']))
            $vorhaben = $vehicle['vorhaben'];
        if (isset($vehicle['vehicle_variant'])) {
            $vehicle_variant = $vehicle['vehicle_variant'];
            if (isset($this->vehicle_variants[$vehicle['vehicle_variant']]))
                $variant_name = $this->vehicle_variants[$vehicle['vehicle_variant']];
        }

        $replacevals = array(
            'VIN_HERE_VIN' => $vehicle['vin'],
            'US_HERE_US' => $this->user->getUserLastName(),
            'DA_HERE_DA' => date('j.m.Y'),
            'VNR_HERE_VNR' => $vorhaben,
            'PENTA_ARTICLE' => $vehicle['penta_number'],
            'CLIENT_HERE_CLIENT' => str_replace('Dummy ZSP', '', $depot['name']),
            'STREET_NUM' => $depot['street'] . ',' . $depot['housenr'],
            'PLACE_PLZ' => $depot['place'] . ' ' . $depot['postcode'],
            'VehicleType_HERE_VehicleType' => $variant_name
        );

        $vehicle['sname'] = '';

        if (isset($vehicle['delivery_date']))
            $delivery_date = date('Y-m-d', strtotime($vehicle['delivery_date']));

        $table_vehicles[] = array(
            $vehicle['ikz'],
            $vehicle['code'],
            $vehicle['vin'],
            $variant_name,
            $vehicle['sname'],
            $delivery_date
        );

        $fcontent = str_replace(array_keys($replacevals), array_values($replacevals), $fcontent);

        fwrite($fhandle_new, $fcontent);
        fclose($fhandle);
        fclose($fhandle_new);

        exec('libreoffice --headless --convert-to pdf ' . '/var/www/lieferschein_template_drittkunden_' . $vehicle['vehicle_id'] . '.fodt --outdir /var/www/');

        if (file_exists('/var/www/lieferschein_template_drittkunden_' . $vehicle['vehicle_id'] . '.fodt'))
            unlink('/var/www/lieferschein_template_drittkunden_' . $vehicle['vehicle_id'] . '.fodt');

        if (file_exists("/var/www/WebinterfaceNew/fpdf/fpdf.php"))
            require_once ('/var/www/WebinterfaceNew/fpdf/fpdf.php');

        if (isset($vehicle['production_date']) && isset($vehicle['qs_user'])) {
            if ($vehicle['qs_user'] == - 1)
                $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Sts-Pool (Würselen)');
            else
                $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Produktion (Aachen Jülicher Straße)');
        } else
            $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Sts-Pool (Würselen)');

        $pickup_pdf->SetFont('Arial', 'B', 16);
        $pickup_pdf->Cell(40, 20, iconv('UTF-8', 'windows-1252', $depot['name']) . " (" . $depot["dp_depot_id"] . "), P : " . $depot["penta_folge_id"]);
        $pickup_pdf->Ln(16);
        $pickup_pdf->SetFont('Arial', '', 12);
        $pickup_pdf->Cell(40, 6, iconv('UTF-8', 'windows-1252', $depot['street'] . ',' . $depot['housenr']));
        $pickup_pdf->Ln();
        $pickup_pdf->Cell(40, 6, iconv('UTF-8', 'windows-1252', $depot['place'] . ' ' . $depot['postcode']));
        $pickup_pdf->Ln(10);

        for ($i = 0; $i < count($header); $i ++)
            $pickup_pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        $pickup_pdf->Ln();
        $pickup_pdf_header = false;

        $pickup_pdf->Cell($w[0], 6, $cnt, 'LR', 0, 'L', $fill);
        $pickup_pdf->Cell($w[1], 6, $vehicle['code'], 'LR', 0, 'L', $fill);
        $pickup_pdf->Cell($w[2], 6, $vehicle['vin'], 'LR', 0, 'L', $fill);
        $pickup_pdf->Cell($w[3], 6, $vehicle['ikz'], 'LR', 0, 'L', $fill);
        $pickup_pdf->Cell($w[4], 6, $vehicle['sname'], 'LR', 0, 'L', $fill);
        $pickup_pdf->Ln();

        $cnt ++;
        $fill = ! $fill;

        $pickup_pdf->Cell(array_sum($w), 0, '', 'T');
        $pickup_pdf->SetFont('Arial', 'B', 16);
        $pickup_pdf->Ln();
        $pickup_pdf->Cell(40, 20, $pickup);
        $pickup_pdf->Ln();
        $pickup_pdf->SetFont('Arial', '', 12);
        $pickup_pdf->Cell(40, 20, 'Abgeholt am ' . '_________________' . ' durch Spedition' . ' _________________________');
        $pickup_pdf->Ln();
        $pickup_pdf->Cell(40, 20, 'Unterschrift Fahrer: ');

        // end of foreach depot

        $pickup_filename = 'abholung_' . date('Y_m_j_H_i');
        $pickup_pdf->Output('F', '/var/www/' . $pickup_filename . '.pdf', true);
        $this->lieferscheinFname .= '<a href="/downloadlieferschein.php?fname=lieferschein_template_drittkunden_' . $vehicle['vehicle_id'] . '" >Lieferschein herunterladen</a><br>';
        $this->lieferscheinFname .= '<a href="/downloadlieferschein.php?fname=' . $pickup_filename . '" >Abholungsdatei</a><br>';

    }


    function transporter_manage()
    {

        $transporters = $this->ladeLeitWartePtr->newQuery('transporter')->get('transporter_id,name');
        $super_types = $this->ladeLeitWartePtr->newQuery('super_types')->get('super_type_id,name,letter');
        $super_type_names = array_column($super_types, 'name');
        $super_type_ids = array_column($super_types, 'super_type_id');
        $headings = array_merge(array(
            'ID',
            'Spediteur'
        ), $super_type_names);
        $heading = array(
            array(
                'headingone' => $headings
            )
        );

        foreach ($transporters as &$transport) {
            $count_st = array();
            foreach ($super_type_ids as $super_type) {
                $count = $this->ladeLeitWartePtr->newQuery('transport')
                    ->where('transporter', '=', $transport['transporter_id'])
                    ->where('super_type', '=', $super_type)
                    ->getVal('count');
                if (! empty($count))
                    $count_st[] = '<input type="number" name="" value="' . $count . '">';
                else
                    $count_st[] = '<input type="number" name="" value="">';
            }
            $transport = array_merge($transport, $count_st);
        }
        $transporters = array_merge($heading, $transporters);

        $ttable = new DisplayTable($transporters);
        $this->transporter_manage = $ttable->getContent();

    }


    function genpentakennwort()
    {

        $today = date('Y-m-d');
        $csv = '';
        $qry = $this->ladeLeitWartePtr->vehiclesPtr->newQuery();
        $qry = $qry->orderBy('penta_kennwort')->orderBy('vin');

        $liste = $qry->get('penta_kennwort,vin');
        foreach ($liste as $rec)
            if ($rec['vin'][0] == 'W')
                $csv .= "\"{$rec['penta_kennwort']}\";\"{$rec['vin']}\"\r\n";

        $csv_size = strlen($csv);

        header('Pragma: public');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="penta_kennwort-vin-' . $today . '.csv"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . $csv_size);
        header('Content-Type: application/csv');
        echo $csv;
        exit();

    }

}
