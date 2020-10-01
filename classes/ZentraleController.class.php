<?php
/**
 * ZentraleController.class.php
 * Controller for User Role Zentrale
 * @author Pradeep Mohan
 */


class ZentraleController extends PageController {
    protected $content;
    protected $msgs;
    protected $qform_zspl;
    protected $qform_depot;
    protected $qform_deliveryplan;
    protected $qform_transfer_division;
    protected $qform_transfer_depot;
    protected $qform_transfer_zspl;
    protected $listObjects;
    protected $listObjectsHeading;
    protected $listObjectsTableHeadings;
    protected $objectLabel;
    protected $listVehicleCount;
    protected $listStationCount;
    protected $vehiclesAndStations;
    protected $currentDepot;
    protected $displayHeader;
    protected $qform_csv;
    protected $csv_fname;
    protected $donotsave_flag;

    protected $showingDivisions;
    protected $disable_upload_deadline;

    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->content = "";
        $this->msgs = [];
        $this->listVehicleCount = null;
        $this->listStationCount = null;
        $this->qform_deliveryplan = null;
        $this->qform_deliveryplan_week = null;
        $this->vehiclesAndStations = null;
        $this->currentDepot = null;
        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());
        $this->donotsave_flag = false;
        $this->qform_csv = null;
        $this->showingDivisions = false;
        $this->disable_upload_deadline = false;
        $this->action = $this->requestPtr->getProperty('action');

        $db_vehicle_variants = $this->ladeLeitWartePtr->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante', array('showObsoleteVariants' => true));

        $this->vehicle_variants = array_combine(array_column($db_vehicle_variants, 'value_id'), array_column($db_vehicle_variants, 'value'));

        if (isset($this->action))
            call_user_func(array($this, $this->action));
        //specifying the actions one by one since I do not know what happens when the CSV form is saved.. which actions are called?
        if (!isset($this->action) || (isset($this->action) && $this->action != "exportcsv" && $this->action != "showDeliveryPlan" && $this->action != "uploadDeliveryPlan" && $this->action != "uploadDeliveryPlanWeek")) {
            $this->showListObjects();

            $this->qform_zspl = new QuickformHelper ($this->displayHeader, "zspl_upload_form");
            $this->qform_zspl->csvUpload("ZSPLn", "zspl");

            $this->qform_depot = new QuickformHelper ($this->displayHeader, "depot_upload_form");
            $this->qform_depot->csvUpload("ZSPn", "depot");

            $this->qform_transfer_division = new QuickformHelper ($this->displayHeader, "division_transfer_upload_form");
            $this->qform_transfer_division->csvUpload("Niederlassungen OZ Änderungen", "division_transfer");

            $this->qform_transfer_zspl = new QuickformHelper ($this->displayHeader, "zspl_transfer_upload_form");
            $this->qform_transfer_zspl->csvUpload("ZSPL OZ Änderungen", "zspl_transfer");

            $this->qform_transfer_depot = new QuickformHelper ($this->displayHeader, "depot_transfer_upload_form");
            $this->qform_transfer_depot->csvUpload("ZSPn OZ Änderungen", "depot_transfer");

        }
        $this->displayHeader->enqueueJs("tablesorter-widget-scroller", "js/widget-scroller.js");
        $this->displayHeader->enqueueJs("sts-custom-zentrale", "js/sts-custom-zentrale.js");
        $this->displayHeader->printContent();

        $this->printContent();

    }

    /***
     * exportcsv : Liste aller Fahrzeuge exportieren (temporär)
     */
    function exportcsv() {
        $delim_select = ['semi' => 'Semicolon ( ; )', 'komma' => 'Komma ( , )', 'tab' => 'Tabulator', 'strich' => 'Strich ( | )'];
        $delimiters = ['semi' => ';', 'komma' => ',', 'tab' => "\t", 'strich' => '|'];

        if (isset($_POST['saveexportcsv'])) {

            $delimname = safe_val($_REQUEST, 'delimiter', 'semi');
            $delimiter = safe_val($delimiters, $delimname, ';');

            $requiredcols = array();
            foreach ($_POST['vcol'] as $vcol) {

                if (empty($vcol))
                    $requiredcols[] = "''";
                else if (strpos($vcol, 'name') !== FALSE)
                    $requiredcols[] = $vcol . ' as ' . substr($vcol, 0, 5) . 'name';
                else $requiredcols[] = $vcol;
            }
            unset($vcol);
            $ordercols = array();
            foreach ($_POST['vcol'] as $vcol) {
                if (empty($vcol))
                    continue;
                else
                    $ordercols[] = $vcol;
            }

            $filteroptions = $this->requestPtr->getProperty('filteroptions');

            $zeroCondition = " AND depots.depot_id!=0 AND depots.division_id!=0 AND divisions.production_location='f'";
            $foreignCondition = ' AND depots.division_id!=53';
            $thirdCondition = ' AND depots.division_id!=51';

            if (isset($filteroptions['depot_zero']) && $filteroptions['depot_zero'] == 1) $zeroCondition = ' ';
            if (isset($filteroptions['depot_foreign']) && $filteroptions['depot_foreign'] == 1) $foreignCondition = '';
            if (isset($filteroptions['depot_third']) && $filteroptions['depot_third'] == 1) $thirdCondition = '';

            $query = 'SELECT ' . implode(',', $requiredcols) . ' FROM vehicles
					INNER JOIN depots ON depots.depot_id=vehicles.depot_id
					INNER JOIN vehicles_sales ON vehicles_sales.vehicle_id=vehicles.vehicle_id
					INNER JOIN divisions ON depots.division_id=divisions.division_id
					FULL OUTER JOIN stations ON vehicles.station_id=stations.station_id
					WHERE vehicles.vehicle_id IS NOT NULL' .
                $zeroCondition . $foreignCondition . $thirdCondition .
                ' AND depots.division_id!=50 ' .
                ' ORDER BY ' . implode(',', $ordercols);

            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($query);

            $vcolheadings = array('divisions.name' => 'Niederlassung',
                'divisions.dp_division_id' => 'Niederlassung OZ',
                'depots.name' => 'ZSP',
                'depots.dp_depot_id' => 'ZSP OZ Nummer',
                'depots.address' => 'ZSP Adresse',
                'depots.lat,depots.lon' => 'ZSP Koordinaten',
                'vehicles.code' => 'AKZ',
                'vehicles.ikz' => 'IKZ',
                'vehicles.vin' => 'VIN',
                'vehicles.c2cbox' => 'C2C Box ID',
                'stations.name' => 'Ladepunkte',
                'vehicles_sales.delivery_date' => 'Lieferdatum',
                'vehicles_sales.kostenstelle' => 'Kostenstelle',
                'vehicles_sales.vehicle_variant' => 'Fz.-Konfiguration',
                'vehicles.name' => 'Fahrzeug Name'
            );

            $headings = array();
            foreach ($_POST['vcol'] as $vcol) {
                if (empty($vcol))
                    $headings[] = "";
                else
                    $headings[] = $vcolheadings[$vcol];
            }
            $fname = "/tmp/exportcsv_" . date('Y_m_d') . ".csv";
            $fhandle = fopen($fname, "w");
            $fcontent = array(implode($delimiter, $headings));
            foreach ($vehicles as $vehicle) {
                if (!empty($vehicle['delivery_date']))
                    $vehicle['delivery_date'] = date('d-m-Y', strtotime($vehicle['delivery_date']));
                if (!empty($vehicle['depotname']))
                    $vehicle['depotname'] = '"' . $vehicle['depotname'] . '"';
                if (!empty($vehicle['vehicle_variant'])) {
                    $vehicle['vehicle_variant'] = $this->vehicle_variants[$vehicle['vehicle_variant']];
                }
                if (!empty ($vehicle['lat'])) {
                    $vehicle['lat'] = sprintf('"%s, %s"',
                        str_replace(',', '.', $vehicle['lat']),
                        str_replace(',', '.', $vehicle['lon'])
                    );
                }
                unset ($vehicle['lon']);

                $fcontent[] = implode($delimiter, $vehicle);
            }
            fwrite($fhandle, implode("\r\n", $fcontent) . "\r\n");
            fclose($fhandle);
            $this->csv_fname = "exportcsv_" . date('Y_m_d');
        } else {
            $this->qform_csv = new QuickformHelper ($this->displayHeader, "exportcsv_form");
            $vcols = array('', 'divisions.name' => 'Niederlassung',
                'divisions.dp_division_id' => 'Niederlassung OZ',
                'depots.name' => 'ZSP',
                'depots.dp_depot_id' => 'ZSP OZ Nummer',
                'depots.address' => 'ZSP Adresse',
                'depots.lat,depots.lon' => 'ZSP Koordinaten',
                'vehicles.code' => 'AKZ',
                'vehicles.ikz' => 'IKZ',
                'vehicles.vin' => 'VIN',
                'vehicles.c2cbox' => 'C2C Box ID',
                'stations.name' => 'Ladepunkte',
                'vehicles_sales.delivery_date' => 'Lieferdatum',
                'vehicles_sales.kostenstelle' => 'Kostenstelle',
                'vehicles_sales.vehicle_variant' => 'Fahrzeug Variante',
                'vehicles.name' => 'Fahrzeug Name'
            );
            $this->qform_csv->zentraleexportcsv($vcols, $delim_select);

        }


    }

    /**
     * uploadDeliveryPlan
     * Process and save the CSV File uploaded using Mobilitätsplanung hochladen function
     */
    function uploadDeliveryPlan() {
        if (isset($_FILES["csvfile"])) {

            $filename = $_FILES['csvfile']['tmp_name'];
            $orgName = explode('_', $_FILES['csvfile']['name']);
            $yearselect = $_POST['yearselect'];
            if (!isset($orgName)) {
                $this->msgs[] = 'Bitte korrigieren Sie den Dateiname (Fahrzeugvariant_YYYY.csv)';
            }

            if (($handle = fopen($filename, "r")) !== FALSE) {


                if (substr_count(fgets($handle), ';') == 12)
                    $this->delimiter = ';';
                else if (substr_count(fgets($handle), ',') == 12)
                    $this->delimiter = ',';
                else
                    $this->msgs[] = 'Fehler mit der CSV Datei. Mehr als 12 Splaten Trenner (; oder ,) gefunden. Die Datei wird nicht hochgeladen!';
                rewind($handle);
                $months_data = array();
                while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE && $this->donotsave_flag === false) {

                    if (!preg_match('/^\d+$/', $data[0]))
                        continue;

                    for ($month = 1; $month <= 12; $month++) {
                        if (isset($data[$month]) && is_numeric($data[$month])) {
                            $yearmonth = $yearselect . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                            $division = $this->ladeLeitWartePtr->divisionsPtr->getDPDivision($data[0]);
                            //get delivery_id,division_id,yearmonth,variant,quantity
                            $existrow = $this->ladeLeitWartePtr->deliveryPlanPtr->getForMonthAndVariant($division['division_id'], $yearmonth, $_POST['variantselect']);
                            if (!empty($existrow) && $data[$month] != $existrow['quantity']) {
                                $thistime = strtotime("first day of + 1 months");
                                if ($yearmonth <= date('Y-m-01', $thistime) && !$this->disable_upload_deadline) {
                                    $this->donotsave_flag = true;
                                    $this->msgs[] = 'Daten für Niederlassung ' . $division['name'] . ' - ' . strftime('%B %Y', strtotime(date($yearselect . '-' . $month . '-01'))) . ' dürfen nicht geändert werden. Mobilitätsplan wird nicht gespeichert!';
                                    break;
                                }
                            }
                        }
                    }
                    $divisions_data[] = $data;
                }

                if ($this->donotsave_flag === false) {
                    foreach ($divisions_data as $data) {
                        $division = $this->ladeLeitWartePtr->divisionsPtr->getDPDivision($data[0]);

                        for ($month = 1; $month <= 12; $month++) {

                            if (isset($data[$month]) && is_numeric($data[$month])) {
                                $yearmonth = $yearselect . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                                //get delivery_id,division_id,yearmonth,variant,quantity
                                $existrow = $this->ladeLeitWartePtr->deliveryPlanPtr->getForMonthAndVariant($division['division_id'], $yearmonth, $_POST['variantselect']);

                                if (empty($existrow)) {//make something unique in the table? and change deliveryplanid to serial instead of integer


                                    $thistime = strtotime("first day of + 1 months");
                                    if ($yearmonth > date('Y-m-01', $thistime) || $this->disable_upload_deadline) {
                                        $insertvalues = array('division_id' => $division['division_id'],
                                            'yearmonth' => date($yearselect . '-' . $month . '-01'), //@todo change this to retrieve date from filename
                                            'variant' => $_POST['variantselect'],
                                            'quantity' => $data[$month]);
                                        if ($this->ladeLeitWartePtr->deliveryPlanPtr->add($insertvalues))
                                            $this->msgs[] = 'Daten für Niederlassung ' . $division['name'] . ' - ' . strftime('%B %Y', strtotime(date($yearselect . '-' . $month . '-01'))) . ' hinzugefügt';
                                    }
// 								else
// 								{
// 									$this->msgs[]='Daten für '.strftime('%B %Y', strtotime(date($yearselect.'-'.$month.'-01'))).' dürfen nicht geändert werden. Mobilitätsplan wird nicht gespeichert!';
// 									break;
// 								}
                                } else {
                                    $updateCols = array('division_id', 'yearmonth', 'variant', 'quantity');
                                    $updateVals = array($division['division_id'], date($yearselect . '-' . $month . '-01'), $_POST['variantselect'], $data[$month]);

                                    if ($data[$month] != $existrow['quantity']) {
                                        $thistime = strtotime("first day of + 1 months");

                                        if ($yearmonth > date('Y-m-01', $thistime) || $this->disable_upload_deadline) {
                                            if ($this->ladeLeitWartePtr->deliveryPlanPtr->save($updateCols, $updateVals, array('delivery_id', '=', $existrow['delivery_id']))) ;
                                            $this->msgs[] = 'Daten für Niederlassung ' . $division['name'] . ' - ' . strftime('%B %Y', strtotime(date($yearselect . '-' . $month . '-01'))) . ' gespeichert.';
                                        } else {
                                            $this->msgs[] = 'Daten für Niederlassung ' . $division['name'] . ' - ' . strftime('%B %Y', strtotime(date($yearselect . '-' . $month . '-01'))) . ' dürfen nicht geändert werden. Mobilitätsplan wird nicht gespeichert!';
                                            break;
                                        }
                                    }


                                }
                            }

                        }
                    } //end of foreach
                }//end of if do not save flag is false
            }
        } else {


            $db_vehicle_variants = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getAllExternal();
            $this->dp_vehicle_variants = array_combine(array_column($db_vehicle_variants, 'external_variant_value'), array_column($db_vehicle_variants, 'variant_external_name'));

            $this->qform_deliveryplan = new QuickformHelper ($this->displayHeader, "deliveryplan");
            $this->qform_deliveryplan->deliveryplan_upload("Mobilitätsplanung", "deliveryplan", "uploadDeliveryPlan", $this->dp_vehicle_variants);


        }
    }

    /**
     * uploadDeliveryPlanWeek
     * Parses CSV file, gets the calendar weeks from the first row, saves the parsed data in the table delivery_plan_week
     *
     */
    function uploadDeliveryPlanWeek() {
        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
            $yearselect = $_POST['yearselect'];
            $variantselect = $_POST['variantselect'];
            if (($handle = fopen($filename, "r")) !== FALSE) {

                if (substr_count(fgets($handle), ';') > 0) $this->delimiter = ';';
                else if (substr_count(fgets($handle), ',') > 0) $this->delimiter = ',';
                else $this->msgs[] = 'Fehler mit der CSV Datei. Die Datei wird nicht hochgeladen!';
                rewind($handle);
                $insert_vals = array();
                $headers = NULL;
                while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {
                    if (!preg_match('/^\d+$/', $data[0])) {
                        array_shift($data);
                        $headers = $data;
                    } else {
                        $dp_division_id = array_shift($data);
                        $division = $this->ladeLeitWartePtr->divisionsPtr->getDPDivision($dp_division_id);

                        foreach ($data as $col_number => $calendar_week_quantity) {
                            $calendar_week_quantity = (int)$calendar_week_quantity;
                            $existrow = $this->ladeLeitWartePtr->newQuery('delivery_plan_week')
                                ->where('division_id', '=', $division['division_id'])
                                ->where('variant', '=', $variantselect)
                                ->where('delivery_week', '=', 'kw' . $headers[$col_number])
                                ->where('delivery_year', '=', $yearselect)
                                ->getOne('delivery_id,quantity');

                            if (empty($existrow)) {
                                if (!empty($calendar_week_quantity)) {
                                    $insert_vals[] = array($division['division_id'],
                                        'kw' . $headers[$col_number],
                                        $yearselect,
                                        $variantselect,
                                        $calendar_week_quantity);
                                    $this->msgs[] = "Daten für " . $division['name'] . "/" . $dp_division_id . " für  KW " . $headers[$col_number] . " hinzugefügt!";
                                }
                                //else no quantity defined for this week, so ignore it

                            } else if ($existrow['quantity'] != $calendar_week_quantity) {
                                $result = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getInternalValues($variantselect);
                                $all_internal_variant_values = json_decode($result['internal_variant_values'], true);

                                $existrow_delivery_to_divisions = $this->ladeLeitWartePtr->newQuery('delivery_to_divisions')
                                    ->where('division_id', '=', $division['division_id'])
                                    ->where('delivery_week', '=', 'kw' . $headers[$col_number])
                                    ->where('delivery_year', '=', $yearselect)
                                    ->where('variant_value', 'IN', $all_internal_variant_values)
                                    ->getOne('delivery_id');

                                if (empty($existrow_delivery_to_divisions)) {
                                    $update_cols = array('quantity');
                                    $update_vals = array($calendar_week_quantity);
                                    $result = $this->ladeLeitWartePtr->newQuery('delivery_plan_week')->where('delivery_id', '=', $existrow['delivery_id'])->update($update_cols, $update_vals);

                                    if ($result)
                                        $this->msgs[] = "Daten für " . $division['name'] . "/" . $dp_division_id . " für  KW " . $headers[$col_number] . " " . $existrow['quantity'] . "->" . $calendar_week_quantity . " aktualisiert!";
                                } else
                                    $this->msgs[] = "Daten für " . $division['name'] . "/" . $dp_division_id . " für  KW " . $headers[$col_number] . " wird nicht aktualisiert!";
                            }
                        }
                    }
                }

                $insert_cols = array('division_id', 'delivery_week', 'delivery_year', 'variant', 'quantity');
                if (!empty($insert_vals)) {
                    if ($this->ladeLeitWartePtr->newQuery('delivery_plan_week')->insert_multiple_new($insert_cols, $insert_vals))
                        $this->msgs[] = 'Daten hinzugefügt.';
                }


            }
        } else {
            $db_vehicle_variants = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getAllExternal();
            $this->dp_vehicle_variants = array_combine(array_column($db_vehicle_variants, 'external_variant_value'), array_column($db_vehicle_variants, 'variant_external_name'));

            $this->qform_deliveryplan_week = new QuickformHelper ($this->displayHeader, "deliveryplan");
            $this->qform_deliveryplan_week->deliveryplan_upload("Mobilitätsplanung", "deliveryplan", "uploadDeliveryPlanWeek", $this->dp_vehicle_variants);
        }
    }

    /**
     * Displays data saved in the delivery_plan_week and uses the tablesorter scroller widget to beautify the table.
     */
    function showDeliveryPlanWeek() {
        $this->listObjectsHeading = "Niederlassungen";
        $this->objectLabel = "division";
        $divisions = $this->ladeLeitWartePtr->divisionsPtr->getAllValidDivisions();
        $db_vehicle_variants = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getAllExternal();
        $this->dp_vehicle_variants = array_combine(array_column($db_vehicle_variants, 'external_variant_value'), array_column($db_vehicle_variants, 'variant_external_name'));

        if (isset($_POST['variantselect'])) {
            $variantvalue = (int)$_POST['variantselect'];
            $year = (int)$_POST['yearselect'];
            $this->vehicle_variants_name = $this->dp_vehicle_variants[$variantvalue];

            $deliveryPlanResults =
                $this->ladeLeitWartePtr
                    ->newQuery('delivery_plan_week')
                    ->where('delivery_year', '=', $year)
                    ->where('variant', '=', $variantvalue)
                    ->where('substring(delivery_week from 3 for 2)::int', '>=', date('W'))
                    ->join('divisions', 'divisions.division_id=delivery_plan_week.division_id', 'INNER JOIN')
                    ->groupBy('dp_division_id,name')->get('dp_division_id,name,json_object_agg(delivery_week,quantity) as quantities');

            if (!empty($deliveryPlanResults))
                foreach ($deliveryPlanResults as $fordivision) {
                    $week_quantities = json_decode($fordivision['quantities'], true);

                    $quantities = array();
                    for ($w = date('W'); $w <= 52; $w++) {
                        $this_week = 'kw' . $w;
                        if (isset($week_quantities[$this_week])) $quantities[$this_week] = $week_quantities[$this_week];
                        else   $quantities[$this_week] = '';
                    }

                    $this->listObjects[] = array_merge(array($fordivision['dp_division_id'], $fordivision['name']), $quantities);

                }
            for ($w = date('W'); $w <= 52; $w++) {
                $heading[] = 'KW ' . $w;
            }
            $this->listObjectsTableHeadings = array_merge(array('OZ', 'Niederlassung Name'), $heading);
        } else {
            $this->qform_variantselect = new QuickformHelper ($this->displayHeader, "variantselect");
            $this->qform_variantselect->genVariantSelect($this->dp_vehicle_variants, 'showDeliveryPlanWeek');

        }
    }

    /**
     * Function to replace the manual SQL queries used to report how many stations have been assigned by the FPS for vehicles to be delivered.
     *
     */
    function showAssignment() {
        if (isset($_POST['variantselect'])) {
            $external_variant_value = $_POST['variantselect'];
            $result = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getInternalValues($external_variant_value);
            $all_internal_variant_values = json_decode($result['internal_variant_values'], true);

            $result = $this->ladeLeitWartePtr->newQuery('delivery_to_divisions')
                ->where('variant_value', 'IN', $all_internal_variant_values)
                ->where('delivery_year', '=', date('Y'))
                ->groupBy('added')
                ->orderBy('added', 'DESC')
                ->getOne("to_char(added_timestamp, 'YY-MM-DD') as added,json_agg(distinct delivery_week) as weeks");

            $calendar_weeks = json_decode($result['weeks'], true);

            $divisions = $this->ladeLeitWartePtr->newQuery('delivery_to_divisions')
                ->join('divisions', 'delivery_to_divisions.division_id=divisions.division_id', 'INNER JOIN')
                ->where('delivery_week', 'IN', $calendar_weeks)
                ->where('variant_value', 'IN', $all_internal_variant_values)
                ->where('delivery_year', '=', date('Y'))
                ->groupBy('divisions.division_id,divisions.name')
                ->orderBy('division_id')
                ->get('divisions.division_id,divisions.dp_division_id,divisions.name,sum(delivery_quantity) as planned,
	        sum(delivery_quantity-vehicles_delivered_quantity) as pending');

            foreach ($divisions as &$division) {
                $assigned_stations = $this->ladeLeitWartePtr->newQuery('stations')
                    ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
                    ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
                    ->where('vehicles.station_id', 'IS', 'NULL')
                    ->where('depots.division_id', '=', $division['division_id'])
                    ->where('stations.vehicle_variant_value_allowed', 'IN', $all_internal_variant_values)
                    ->getVal('count(stations.station_id)');

                $division['cnt'] = $assigned_stations;

                $free_stations = $this->ladeLeitWartePtr->newQuery('stations')
                    ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
                    ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
                    ->where('vehicles.station_id', 'IS', 'NULL')
                    ->where('stations.deactivate', '=', 'f')
                    ->where('depots.division_id', '=', $division['division_id'])
                    ->getVal('count(stations.station_id)');

                $division['freestations'] = $free_stations;
            }
            $displaytable = new DisplayTable (array_merge(array(array('headingone' => array('Datenbank ID', 'OZ', 'Niederlassung', 'Geplante Auslieferung', 'Noch Auszulieferende Fahrzeuge', 'Zugewiesen', 'Noch Freie aktive Ladesäule'))), $divisions), array('id' => 'overview_assignment'));
            $this->overview_assignment = '<h2>Kalendar Wochen : ' . implode(',', $calendar_weeks) . '</h2>' . $displaytable->getContent();

        } else {
            $db_vehicle_variants = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getAllExternal();
            $this->dp_vehicle_variants = array_combine(array_column($db_vehicle_variants, 'external_variant_value'), array_column($db_vehicle_variants, 'variant_external_name'));


            $this->qform_variantselect = new QuickformHelper ($this->displayHeader, "variantselect");
            $this->qform_variantselect->genVariantSelect($this->dp_vehicle_variants, 'showAssignment');
        }

    }

    /**
     * showDeliveryPlan
     * Show the  delivery_plan table data using Anzeige Mobilitätsplanung function
     */
    function showDeliveryPlan() {
        $this->listObjectsHeading = "Niederlassungen";
        $this->objectLabel = "division";
        $divisions = $this->ladeLeitWartePtr->divisionsPtr->getAllValidDivisions();
        $db_vehicle_variants = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getAllExternal();
        $this->dp_vehicle_variants = array_combine(array_column($db_vehicle_variants, 'external_variant_value'), array_column($db_vehicle_variants, 'variant_external_name'));

        if (isset($_POST['variantselect'])) {
            $variantvalue = (int)$_POST['variantselect'];
            $year = (int)$_POST['yearselect'];
            $yearmonth = date($year . '-01-01');
            $this->vehicle_variants_name = $this->dp_vehicle_variants[$variantvalue];

            $deliveryPlanResults = $this->ladeLeitWartePtr->deliveryPlanPtr->getAllForVariant($variantvalue, $yearmonth);

            if (!empty($deliveryPlanResults))
                foreach ($deliveryPlanResults as $fordivision) {
                    $monthquantities = json_decode($fordivision['quantities'], true);

                    $quantities = array();
                    for ($m = 0; $m <= 11; $m++) {
                        $month = str_pad($m + 1, 2, '0', STR_PAD_LEFT);
                        $this_yearmonth = date($year . '-' . $month . '-01');
                        if (isset($monthquantities[$this_yearmonth]))
                            $quantities[$m] = $monthquantities[$this_yearmonth];
                        else
                            $quantities[$m] = '';
                    }

                    $this->listObjects[] = array_merge(array($fordivision['dp_division_id'], $fordivision['name']), $quantities);

                }
            for ($m = 0; $m <= 11; $m++) {
                $month = str_pad($m + 1, 2, '0', STR_PAD_LEFT);
                $this_yearmonth = date($year . '-' . $month . '-01');
                $heading[] = date('M', strtotime($this_yearmonth));
            }
            $this->listObjectsTableHeadings = array_merge(array('OZ', 'Niederlassung Name'), $heading);
        } else {
            $this->qform_variantselect = new QuickformHelper ($this->displayHeader, "variantselect");
            $this->qform_variantselect->genVariantSelect($this->dp_vehicle_variants, 'showDeliveryPlan');

        }
    }

    /**
     * newvehicles
     * save to the vehicles_post new vehicles to be processed by Sales..
     * //@todo 20161102 send email to Sales when a new batch is saved
     */
    function newvehicles() {

        if ($this->ladeLeitWartePtr->vehiclesPostPtr->savebatch($_POST, $this->user->getUserId()))
            $this->msgs[] = "Neue Fahrzeuge wurden beaufgetragt!";
    }

    /**
     * Shows list of either Niederlassungen, ZSPLn or ZSPn or the vehicle station assignment table.
     */
    function showListObjects() {
        $div = $this->requestPtr->getProperty('division');
        $zspl = $this->requestPtr->getProperty('zspl');
        $depot = $this->requestPtr->getProperty('zsp');
        $this->overview = new CommonFunctions_VehiclesStationsOverview($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr);
        if ($depot) {
            $this->overview->buildVehiclesStationsTable($depot);
            $this->overview->buildSummaryForDepot($depot);
        } else if ($zspl) {
            $this->overview->buildOverviewForZspl($zspl);
        } else if ($div) {
            $this->overview->buildOverviewForDivision($div);
        } else {
            $this->showingDivisions = true;
            $this->overview->buildOverview();
        }

    }

    /**
     * Process and save the CSV File uploaded ZSPLn hochladen function
     */
    function save_zspl_upload() {
        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }

        if (($handle = fopen($filename, "r")) !== FALSE) {

            $zsplcols = array('dp_zspl_id', 'name', 'division_id');
            $zsplvals = array();
            $update_zspls = array();
            $delete_zspls = array();
            $insert_zspls = array();
            $file_zspl_ids = array();

            $db_zspls = $this->ladeLeitWartePtr->zsplPtr->getAllValidZspl();


            if (!empty ($db_zspls)) $db_zspl_ids = array_column($db_zspls, "dp_zspl_id");
            else $db_zspl_ids = array();

            if (substr_count(fgets($handle), ';') > 0)
                $this->delimiter = ';';
            else if (substr_count(fgets($handle), ',') > 0)
                $this->delimiter = ',';
            else
                $this->msgs[] = 'Fehler mit der CSV Datei. Die Datei wird nicht hochgeladen!';

            rewind($handle);

            while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {

                if (!preg_match('/^\d+$/', $data[0])) {
                    continue;
                }

                if (!empty($db_zspl_ids) && in_array($data[0], $db_zspl_ids)) {
                    $update_zspls[] = $data;
                } else $insert_zspls[] = $data;

                $file_zspl_ids[] = $data[0];
            }

            if (!empty($db_zspl_ids) && !empty($file_zspl_ids))
                $delete_zspls = array_diff($db_zspl_ids, $file_zspl_ids);

            /**
             * ZSPLn Insert
             */
            $currentzspl = array();
            if (!empty($insert_zspls)) {
                foreach ($insert_zspls as $new_zspl) {

                    $currentzspl[0] = $new_zspl[0];
                    $currentzspl[1] = $new_zspl[1];

// 						$thisdivision=$this->ladeLeitWartePtr->divisionsPtr->getDPDivision(substr($new_zspl[0],2,2)); older OZ Id with just 2 digits
                    $thisdivision = $this->ladeLeitWartePtr->divisionsPtr->getDPDivision(substr($new_zspl[0], 0, 4)); //new OZ Number with 4 digits
                    $currentzspl[2] = $thisdivision['division_id'];
                    $zsplvals[] = $currentzspl;

                }
                $this->ladeLeitWartePtr->zsplPtr->addMultiple($zsplcols, $zsplvals);
            }

            /**
             * ZSPLn Update
             */

            if (!empty($update_zspls)) {

                foreach ($update_zspls as $check_zspl) {

                    $key = (int)array_search($check_zspl[0], array_column($db_zspls, 'dp_zspl_id'));
                    if ($check_zspl[1] != $db_zspls[$key]["name"] && $check_zspl[0] == $db_zspls[$key]["dp_zspl_id"]) {
                        //we are updating only the name of the ZSPL and not the division!
                        if ($this->ladeLeitWartePtr->zsplPtr->save(array("name"),
                            array($check_zspl[1]),
                            array('dp_zspl_id', '=', $check_zspl[0])
                        ))
                            $this->msgs[] = $check_zspl[0] . ":" . $check_zspl[1] . " aktualisiert. <br />";
                        else
                            $this->msgs[] = $check_zspl[0] . ":" . $check_zspl[1] . " könnte nicht aktualisiert werden! <br />";;
                    }
                    unset($db_zspls[$key]); //unset this ZSPL from the array since it has already been updated and will not be needed again
                    $db_zspls = array_values($db_zspls); //using array_values since we need  the numerical index to be reordered again

                }
            }

            if (!empty($delete_zspls)) {
                foreach ($delete_zspls as $remove_zspl) {
                    $this->msgs[] = "ZSPL " . $remove_zspl . " kann nicht gelöscht werden. <br />";

                }
                $this->msgs[] = 'Um ZSPLn zu löschen, schicken Sie bitte eine email an support@streetscooter-cloud-system.eu . <br>';

            }

            fclose($handle);
        }
        $this->msgs[] = "Neue CSV Datei erfolgereich hochgeladen.";

    }

    /**
     * * Process and save the CSV File uploaded ZSPn hochladen function
     */
    function save_depot_upload() {
        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }

        if (($handle = fopen($filename, "r")) !== FALSE) {

            $depotcols = array('dp_depot_id', 'name', 'lon', 'lat', 'division_id', 'zspl_id', 'depot_restriction_id', 'wenumber', 'stationprovider', 'street', 'housenr', 'postcode', 'place');
            $depotvals = array();
            $update_depots = array();
            $delete_depots = array();
            $insert_depots = array();
            $file_depot_ids = array();

            $db_depots = $this->ladeLeitWartePtr->depotsPtr->getAllValidDepots();

            if (!empty ($db_depots)) $db_depot_ids = array_column($db_depots, "dp_depot_id");
            else $db_depot_ids = array();

            if (substr_count(fgets($handle), ';') > 1)
                $this->delimiter = ';';
            else if (substr_count(fgets($handle), ',') > 1)
                $this->delimiter = ',';
            else
                $this->msgs[] = 'Fehler mit der CSV Datei. Die Datei wird nicht hochgeladen!';

            rewind($handle);

            while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {

                if (!preg_match('/^\d+$/', $data[0])) {
                    continue;
                }

                if (!empty($db_depot_ids) && in_array($data[0], $db_depot_ids)) {
                    $update_depots[] = $data;
                } else $insert_depots[] = $data;

                $file_depot_ids[] = $data[0];
            }

            if (!empty($db_depot_ids) && !empty($file_depot_ids))
                $delete_depots = array_diff($db_depot_ids, $file_depot_ids);

            /**
             * ZSP Insert
             */
            $currentdepot = array();

            $db_zspls = $this->ladeLeitWartePtr->zsplPtr->getAllValidZspl();


            $cache_zspl_id = array();

            if (!empty($insert_depots)) {
                foreach ($insert_depots as $new_depot) {

                    $currentdepot[0] = $new_depot[0];
                    $currentdepot[1] = $new_depot[1];

                    if (strpos($new_depot[2], ","))
                        $currentdepot[2] = str_replace(",", ".", $new_depot[2]);
                    else if ($new_depot[2] == '' || $new_depot[2] == '#NV')
                        $currentdepot[2] = NULL;
                    else
                        $currentdepot[2] = $new_depot[2];

                    if (strpos($new_depot[3], ","))
                        $currentdepot[3] = str_replace(",", ".", $new_depot[3]);
                    else if ($new_depot[3] == '' || $new_depot[3] == '#NV')
                        $currentdepot[3] = NULL;
                    else
                        $currentdepot[3] = $new_depot[3];


                    $zspl_dp_id = substr($new_depot[0], 0, 8);

                    if (isset($cache_zspl_id["dp_zspl_id"]) && $cache_zspl_id["dp_zspl_id"] == $zspl_dp_id) {
                        $currentdepot[4] = $cache_zspl_id["zspl_id"];
                        $currentdepot[5] = $cache_zspl_id["division_id"];

                    } else {
                        $cache_zspl_id["key"] = $key = array_search($zspl_dp_id, array_column($db_zspls, 'dp_zspl_id'));
                        if ($key === FALSE) {
                            $zspl_dp_id = substr($new_depot[0], 0, 6); // zBMech and other variants of the dp_depot_id
                            $cache_zspl_id["key"] = $key = array_search($zspl_dp_id, array_column($db_zspls, 'dp_zspl_id'));
                            if ($key === FALSE) {
                                $this->msgs[] = 'Fehler : Keine entsprechende ZSPL/Niederlassung gefunden!';
                                continue;
                            }
                        }

                        $cache["dp_zspl_id"] = $new_depot[0];
                        $currentdepot[4] = $cache_zspl_id["division_id"] = $db_zspls[$key]["division_id"];
                        $currentdepot[5] = $cache_zspl_id["zspl_id"] = $db_zspls[$key]["zspl_id"];

                    }

                    $currentdepot[6] = NULL;
                    $currentdepot[7] = $new_depot[4];
                    $currentdepot[8] = $new_depot[5];
                    //Adresse
                    if (isset($new_depot[6])) //street
                        $currentdepot[9] = $new_depot[6];
                    else
                        $currentdepot[9] = NULL;

                    if (isset($new_depot[7]))//housenr
                        $currentdepot[10] = $new_depot[7];
                    else
                        $currentdepot[10] = NULL;

                    if (isset($new_depot[8])) //postcode
                        $currentdepot[11] = $new_depot[8];
                    else
                        $currentdepot[11] = NULL;

                    if (isset($new_depot[9])) //place
                        $currentdepot[12] = $new_depot[9];
                    else
                        $currentdepot[12] = NULL;

                    $depotvals[] = $currentdepot;


                }
                $this->ladeLeitWartePtr->depotsPtr->addMultiple($depotcols, $depotvals);

                /*
                             * find all depots with depot_restriction_id IS NULL and create a basic charging infrastructure for these depots..
                             * does not depend on which depots were just added from the uploaded CSV file..
                             */
                set_time_limit(0);
                $setup_depots = $this->ladeLeitWartePtr->depotsPtr->newQuery()->where('depot_restriction_id', ' IS ', ' NULL')->get('*');
                foreach ($setup_depots as $setup_depot) {

                    $newRestriction = array("parent_restriction_id" => NULL, "name" => "Hauptanschluss", "power" => 39 * 215.0);
                    $new_parent_id = $this->ladeLeitWartePtr->restrictionsPtr->add($newRestriction, $setup_depot['depot_id']);

                    $insertCols = array('parent_restriction_id', 'name', 'power');
                    $insertVals = array(
                        array($new_parent_id, 'Phase 1', 13 * 215.0),
                        array($new_parent_id, 'Phase 2', 13 * 215.0),
                        array($new_parent_id, 'Phase 3', 13 * 215.0)
                    );
                    $newRestrictionId = $this->ladeLeitWartePtr->restrictionsPtr->addMultiple($insertCols, $insertVals);

                }


            }


            /**
             * ZSPn Update
             */

            if (!empty($update_depots)) {
                set_time_limit(0);
                foreach ($update_depots as $check_depot) {

                    $currentdepot[0] = $check_depot[0];
                    $currentdepot[1] = $check_depot[1];

                    if (strpos($check_depot[2], ","))
                        $currentdepot[2] = str_replace(",", ".", $check_depot[2]);
                    else if ($check_depot[2] == '' || $check_depot[2] == '#NV')
                        $currentdepot[2] = NULL;
                    else
                        $currentdepot[2] = $check_depot[2];

                    if (strpos($check_depot[3], ","))
                        $currentdepot[3] = str_replace(",", ".", $check_depot[3]);
                    else if ($check_depot[3] == '' || $check_depot[3] == '#NV')
                        $currentdepot[3] = NULL;
                    else
                        $currentdepot[3] = $check_depot[3];

                    $zspl_dp_id = substr($check_depot[0], 0, 8);

                    if (isset($cache_zspl_id["dp_zspl_id"]) && $cache_zspl_id["dp_zspl_id"] == $zspl_dp_id) {
                        $currentdepot[4] = $cache_zspl_id["zspl_id"];
                        $currentdepot[5] = $cache_zspl_id["division_id"];

                    } else {
                        $cache_zspl_id["key"] = $key = array_search($zspl_dp_id, array_column($db_zspls, 'dp_zspl_id'));
                        if ($key === FALSE) {
                            $zspl_dp_id = substr($check_depot[0], 0, 6); // zBMech and other variants of the dp_depot_id
                            $cache_zspl_id["key"] = $key = array_search($zspl_dp_id, array_column($db_zspls, 'dp_zspl_id'));
                            if ($key === FALSE) {
                                $this->msgs[] = 'Fehler : Keine entsprechende ZSPL/Niederlassung gefunden!';
                                continue;
                            }
                        }
                        $cache["dp_zspl_id"] = $check_depot[0];
                        $currentdepot[4] = $cache_zspl_id["division_id"] = $db_zspls[$key]["division_id"];
                        $currentdepot[5] = $cache_zspl_id["zspl_id"] = $db_zspls[$key]["zspl_id"];

                    }

                    $currentdepot[6] = $check_depot[4];
                    $currentdepot[7] = $check_depot[5];

                    //Adresse
                    //street
                    if (isset($check_depot[6])) $currentdepot[8] = $check_depot[6];
                    else $currentdepot[8] = NULL;
                    //housenr
                    if (isset($check_depot[7])) $currentdepot[9] = $check_depot[7];
                    else $currentdepot[9] = NULL;
                    //postcode
                    if (isset($check_depot[8])) $currentdepot[10] = $check_depot[8];
                    else $currentdepot[10] = NULL;

                    //place
                    if (isset($check_depot[9])) $currentdepot[11] = $check_depot[9];
                    else $currentdepot[11] = NULL;

                    $key = array_search($check_depot[0], array_column($db_depots, 'dp_depot_id'));
                    /**
                     *  $currentdepot structure is array('dp_depot_id','name','lon','lat','division_id','zspl_id',"wenumber","stationprovider");
                     */

                    //@todo 20160810 check this.. URGENT
                    if (!isset($db_depots[$key])) {
                        $this->msgs[] = $check_depot[0] . ":" . $check_depot[1] . " doppelt im Datei! Wird nicht aktualisiert. <br />";

                    }

                    if ($currentdepot[1] != $db_depots[$key]["name"]
                        || $currentdepot[2] != $db_depots[$key]["lon"]
                        || $currentdepot[3] != $db_depots[$key]["lat"]
                        || $currentdepot[4] != $db_depots[$key]["division_id"]
                        || $currentdepot[5] != $db_depots[$key]["zspl_id"]
                        || $currentdepot[6] != $db_depots[$key]["wenumber"]
                        || $currentdepot[7] != $db_depots[$key]["stationprovider"]
                        || $currentdepot[8] != $db_depots[$key]["street"]
                        || $currentdepot[9] != $db_depots[$key]["housenr"]
                        || $currentdepot[10] != $db_depots[$key]["postcode"]
                        || $currentdepot[11] != $db_depots[$key]["place"]
                    ) {
                        $updateparams = array();
                        if ($currentdepot[1] != $db_depots[$key]["name"]) $updateparams[] = 'ZSP Name';
                        if ($currentdepot[2] != $db_depots[$key]["lon"]) $updateparams[] = 'Lon';
                        if ($currentdepot[3] != $db_depots[$key]["lat"]) $updateparams[] = 'Lat';
                        if ($currentdepot[4] != $db_depots[$key]["division_id"]) $updateparams[] = 'Niederlassung';
                        if ($currentdepot[5] != $db_depots[$key]["zspl_id"]) $updateparams[] = 'ZSPL';
                        if ($currentdepot[6] != $db_depots[$key]["wenumber"]) $updateparams[] = 'WE Nr.';
                        if ($currentdepot[7] != $db_depots[$key]["stationprovider"]) $updateparams[] = 'Anbieter';
                        if ($currentdepot[8] != $db_depots[$key]["street"]) $updateparams[] = 'Straße';
                        if ($currentdepot[9] != $db_depots[$key]["housenr"]) $updateparams[] = 'Hausnummer';
                        if ($currentdepot[10] != $db_depots[$key]["postcode"]) $updateparams[] = 'PLZ';
                        if ($currentdepot[11] != $db_depots[$key]["place"]) $updateparams[] = 'Ort';


                        if (!empty($updateparams)) $updateparams = '(' . implode(',', $updateparams) . ')';
                        else $updateparams = '';

                        if ($this->ladeLeitWartePtr->depotsPtr->newQuery()->where('dp_depot_id', '=', $currentdepot[0])->update(array("name", "lon", "lat", "division_id", "zspl_id", "wenumber", "stationprovider",
                            "street", "housenr", "postcode", "place"),
                            array($currentdepot[1], $currentdepot[2], $currentdepot[3], $currentdepot[4], $currentdepot[5], $currentdepot[6], $currentdepot[7], $currentdepot[8], $currentdepot[9], $currentdepot[10], $currentdepot[11])
                        ))
                            $this->msgs[] = $check_depot[0] . ":" . $check_depot[1] . " aktualisiert " . $updateparams . ". <br />";
                        else
                            $this->msgs[] = $check_depot[0] . ":" . $check_depot[1] . "  könnte nicht aktualisiert werden! <br />";;
                    }
                    unset($db_depots[$key]); //unset this depot from the array since it has already been updated and will not be needed again
                    $db_depots = array_values($db_depots); //using array_values since we need  the numerical index to be reordered again

                }
            }


            if (!empty($delete_depots)) {
                foreach ($delete_depots as $remove_depot) {
                    $this->msgs[] = "ZSP " . $remove_depot . " kann nicht gelöscht werden. <br />";

                }
                $this->msgs[] = 'Um ZSPn zu löschen, schicken Sie bitte ein email an support@streetscooter-cloud-system.eu .';

            }

            fclose($handle);
        }
        $this->msgs[] = "Neue CSV Datei erfolgereich hochgeladen.";


    }

    /**
     * Process and save the CSV File uploaded using ZSPn Transfer function
     * Changes the ZSP OZ Number
     */
    function save_depot_transfer_upload() {
        $db_zspls = $this->ladeLeitWartePtr->zsplPtr->getAllValidZspl();
        $db_depots = $this->ladeLeitWartePtr->depotsPtr->getAllValidDepots();

        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }

        if (($handle = fopen($filename, "r")) !== FALSE) {

            if (substr_count(fgets($handle), ';') == 2)
                $this->delimiter = ';';
            else if (substr_count(fgets($handle), ',') == 2)
                $this->delimiter = ',';
            else
                $this->msgs[] = 'Fehler mit der CSV Datei. Die Datei wird nicht hochgeladen!';
            $cnt = 1;
            rewind($handle);
            $db_zspls = $this->ladeLeitWartePtr->zsplPtr->getAllValidZspl();


            if (!empty ($db_zspls)) $db_zspl_ids = array_column($db_zspls, "dp_zspl_id");
            else $db_zspl_ids = array();

            if (!empty ($db_depots)) $db_depot_ids = array_column($db_depots, "dp_depot_id");
            else $db_depot_ids = array();
            // 			if(!empty($db_depot_ids) && in_array($data[0],$db_depot_ids)) { $update_depots[]=$data; }
            $missingzspls = array();
            $updateddepots = array();

            while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {

                if (!preg_match('/^\d+$/', trim($data[2]))) {
                    continue;
                }

                $old_dp = trim($data[1]);

                if ($old_dp != trim($data[2])) {
                    $thisdepot = $this->ladeLeitWartePtr->depotsPtr->getWhere(array('zspl_id', 'division_id', 'name', 'dp_depot_id', 'depot_id'), array(array('dp_depot_id', '=', $old_dp)));

                    if (!empty($thisdepot)) {
                        $thisdepot = $thisdepot[0];
                        if (!in_array($old_dp, $updateddepots)) {
                            $zspl_dp_id = substr($data[2], 0, 8);
                            $currentdepot = array();
                            if (isset($cache_zspl_id["dp_zspl_id"]) && $cache_zspl_id["dp_zspl_id"] == $zspl_dp_id) {
                                $currentdepot[0] = $cache_zspl_id["zspl_id"];
                                $currentdepot[1] = $cache_zspl_id["division_id"];

                            } else {
                                $cache_zspl_id["key"] = $key = array_search($zspl_dp_id, array_column($db_zspls, 'dp_zspl_id'));
                                if ($key === FALSE) {
                                    $zspl_dp_id = substr($data[2], 0, 6); // zBMech and other variants of the dp_depot_id
                                    $cache_zspl_id["key"] = $key = array_search($zspl_dp_id, array_column($db_zspls, 'dp_zspl_id'));
                                    if ($key === FALSE) {
                                        $missingzspls[] = $zspl_dp_id;
                                        continue;
                                    }
                                }
                                $cache["dp_zspl_id"] = substr($data[2], 0, 8);
                                $currentdepot[0] = $cache_zspl_id["zspl_id"] = $db_zspls[$key]["zspl_id"];
                                $currentdepot[1] = $cache_zspl_id["division_id"] = $db_zspls[$key]["division_id"];

                            }
                            $currentdepot[2] = $data[0];
                            $currentdepot[3] = $data[2];
// 									$key = array_search($data[1], array_column($db_depots, 'dp_depot_id'));

                            $updatecols = array('zspl_id', 'division_id', 'name', 'dp_depot_id');
                            $updatevals = $currentdepot;

// 									echo implode(',',$thisdepot).'->'.implode(',',$updatevals).'<br>';
                            $this->ladeLeitWartePtr->depotsPtr->save($updatecols, $updatevals, array('depot_id', '=', $thisdepot['depot_id']));

                            $thisquery = array('updatecols' => serialize($updatecols),
                                'newvals' => serialize($updatevals),
                                'oldvals' => serialize(array($thisdepot['zspl_id'], $thisdepot['division_id'], $thisdepot['name'], $thisdepot['dp_depot_id'])),
                                'userid' => $this->user->getUserId(),
                                'update_timestamp' => date('Y-m-d H:i:sO'),
                                'tablename' => 'depots'
                            );

                            $this->ladeLeitWartePtr->dbHistoryPtr->add($thisquery);
                            if (strcmp($data[0], $thisdepot['name']) == 0)
                                $this->msgs[] = $cnt . ': ' . $data[0] . ' aktualisiert<br>';
                            else
                                $this->msgs[] = $cnt . ': "' . $data[0] . '"->"' . $thisdepot['name'] . '" aktualisiert<br>';

                        } else {
                            $this->msgs[] = $cnt . ': ' . $data[0] . ' muss deaktiviert werden <br>';

                            // but its not deactivating it!

                        }

                        $updateddepots[] = $data[2];
                    } else {
                        $thisdepot = $this->ladeLeitWartePtr->depotsPtr->getWhere(array('zspl_id', 'division_id', 'name', 'dp_depot_id', 'depot_id'), array(array('name', 'LIKE', $data[0])));

                        if (!empty($thisdepot))
                            $this->msgs[] = $cnt . ': ' . $data[0] . ' ' . $data[1] . ' hat vielleicht falsche Alte OZ Nummer <br> In der Datenbank steht OZ als ' . $thisdepot[0]['dp_depot_id'];

                    }

                } else {
                    $this->msgs[] = $cnt . ': ' . $data[0] . ' keine änderung benötigt!<br>';
                }
                $cnt++;

                //
            }
            $this->msgs[] = implode('<br>', array_unique($missingzspls));
        }

    }

    /**
     * Process and save the CSV File uploaded using ZSPLn Transfer function
     * Changes the ZSPL OZ Number
     */
    function save_zspl_transfer_upload() {

        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }

        if (($handle = fopen($filename, "r")) !== FALSE) {

            if (substr_count(fgets($handle), ';') == 2)
                $this->delimiter = ';';
            else if (substr_count(fgets($handle), ',') == 2)
                $this->delimiter = ',';
            else
                $this->msgs[] = 'Fehler mit der CSV Datei. Mehr als 2 Splaten Trenner (; oder ,) gefunden. Die Datei wird nicht hochgeladen!';
            rewind($handle);
            $updated_zspls = array();

            while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {

                if (!preg_match('/^\d+$/', trim($data[2]))) {
                    continue;
                }

                if (!empty(trim($data[1]))) //update existing ZSPL
                {
                    $old_dp = trim($data[1]);
                    $thiszspl = $this->ladeLeitWartePtr->zsplPtr->getWhere(null, array(array('dp_zspl_id', '=', $old_dp)));
                    $thiszspl = $thiszspl[0];
                    if (empty($thiszspl)) //curent ZSPL is empty, meaning false alte OZ
                    {
                        $this->msgs[] = 'ZSPL ' . $thiszspl['name'] . ' ' . $data[1] . '->' . $data[0] . ' ' . $data[2] . ' konnte nicht aktualisiert werden. Falsche Alte OZ';


                    } else //zspl exists
                    {
                        $newdp = trim($data[2]);
                        $newzspl = $this->ladeLeitWartePtr->zsplPtr->getWhere(null, array(array('dp_zspl_id', '=', $newdp)));

                        if (empty($newzspl)) //new OZ does not exist, so update old ZSPL with new DP ZSPL ID
                        {
                            $updatecols = array('dp_zspl_id', 'name', 'division_id');

                            $thisdivision = $this->ladeLeitWartePtr->divisionsPtr->getDPDivision(substr($data[2], 0, 4));
                            $updatevals = array($data[2], $data[0], $thisdivision['division_id']);
                            if (empty($thisdivision))
                                $this->msgs[] = 'ZSPL ' . $thiszspl['name'] . ' ' . $data[1] . '->' . $data[0] . ' ' . $data[2] . ' konnte nicht aktualisiert werden. Keine Niederlassung ' . substr($data[2], 0, 4) . 'gefunden<br>';
                            else {

                                $this->ladeLeitWartePtr->zsplPtr->save($updatecols, $updatevals, array('zspl_id', '=', $thiszspl['zspl_id']));
                                $this->msgs[] = 'ZSPL ' . $thiszspl['name'] . ' ' . $data[1] . '->' . $data[0] . ' ' . $data[2] . ' wird aktualisiert. Neue Niederlassung ' . $thisdivision['name'] . '<br>';
                            }

                        } else //new OZ exists already meaning, two ZSPLS are being merged into one
                        {
                            $this->msgs[] = $thiszspl['name'] . ':' . $thiszspl['dp_zspl_id'] . ' wird jetzt deaktiviert. Die ZSPn müssen aufgepasst werden.';
                            $updatecols = array('active');
                            $updatevals = array('FALSE');
                            $this->ladeLeitWartePtr->zsplPtr->save($updatecols, $updatevals, array('zspl_id', '=', $thiszspl['zspl_id']));
                        }

                        $thisquery = array('updatecols' => serialize($updatecols),
                            'newvals' => serialize($updatevals),
                            'oldvals' => serialize(array($thiszspl['dp_zspl_id'], $thiszspl['name'], $thiszspl['division_id'])),
                            'userid' => $this->user->getUserId(),
                            'update_timestamp' => date('Y-m-d H:i:sO'),
                            'tablename' => 'zspl'
                        );

                        $this->ladeLeitWartePtr->dbHistoryPtr->add($thisquery);

                    }

                } else //new ZSPL
                {
                    $thisdivision = $this->ladeLeitWartePtr->divisionsPtr->getDPDivision(substr($data[2], 0, 4));
                    if (empty($thisdivision))
                        $this->msgs[] = 'ZSPL ' . $data[1] . '->' . $data[0] . ' ' . $data[2] . ' konnte nicht erzeugt werden, keine Niederlassung ' . substr($data[2], 0, 4) . 'gefunden<br>';
                    else {
                        $this->ladeLeitWartePtr->zsplPtr->add(array('dp_zspl_id' => $data[2], 'name' => $data[0], 'division_id' => $thisdivision['division_id']));

                        $thisquery = array('updatecols' => serialize(array('dp_zspl_id', 'name', 'division_id')),
                            'newvals' => serialize(array($data[2], $data[0], $thisdivision['division_id'])),
                            'oldvals' => '',
                            'userid' => $this->user->getUserId(),
                            'update_timestamp' => date('Y-m-d H:i:sO'),
                            'tablename' => 'zspl'
                        );

                        $this->ladeLeitWartePtr->dbHistoryPtr->add($thisquery);

                        $this->msgs[] = 'ZSPL ' . $data[1] . '->' . $data[0] . ' ' . $data[2] . ' wird erzeugt. Niederlassung ' . $thisdivision['name'] . '<br>';
                    }


                }


            }
        }

    }

    /**
     * Process and save the CSV File uploaded using Niderlassung Transfer function
     * Changes the Niderlassung OZ Number
     */
    function save_division_transfer_upload() {

        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }

        if (($handle = fopen($filename, "r")) !== FALSE) {

            if (substr_count(fgets($handle), ';') == 3)
                $this->delimiter = ';';
            else if (substr_count(fgets($handle), ',') == 3)
                $this->delimiter = ',';
            else
                $this->msgs[] = 'Fehler mit der CSV Datei. Mehr als 3 Splaten Trenner (; oder ,) gefunden. Die Datei wird nicht hochgeladen!';
            rewind($handle);
            $updated_divisions = array();
            while (($data = fgetcsv($handle, null, $this->delimiter)) !== FALSE) {

                if (!preg_match('/^\d+$/', trim($data[0]))) {
                    continue;
                }

                $thisdivision = $this->ladeLeitWartePtr->divisionsPtr->getDPDivision(substr($data[0], 2, 2)); //older OZ Id with just 2 digits
                if (!in_array($data[2], $updated_divisions)) //update with new params only if the new OZ number doesnt exist already.
                {
                    $updatecols = array('dp_division_id', 'name');
                    $updatevals = array($data[2], $data[3]);
                    $this->ladeLeitWartePtr->divisionsPtr->save($updatecols, $updatevals, array('division_id', '=', $thisdivision['division_id']));


                } // deactivate this second NL with blank params since it is supposedly merging with another NL, ensure the ZSPL and ZSP transfer list move the respective entities
                else {
                    $this->msgs[] = $thisdivision['name'] . ' wird jetzt deaktiviert. Die ZSPLn and ZSPn müssen aufgepasst werden.';
                    $updatecols = array('active');
                    $updatevals = array('FALSE');
                    $this->ladeLeitWartePtr->divisionsPtr->save($updatecols, $updatevals, array('division_id', '=', $thisdivision['division_id']));
                }

                $thisquery = array('updatecols' => serialize($updatecols),
                    'newvals' => serialize($updatevals),
                    'oldvals' => serialize(array($thisdivision['dp_division_id'], $thisdivision['name'])),
                    'userid' => $this->user->getUserId(),
                    'update_timestamp' => date('Y-m-d H:i:sO'),
                    'tablename' => 'divisions'
                );

                $this->ladeLeitWartePtr->dbHistoryPtr->add($thisquery);

                $updated_divisions[] = $data[2];

                $old_dp = substr($data[0], 2, 2);
                if ($old_dp != substr($data[2], 2, 2)) //move this above and perform updates only if this condition is true.
                {
                    $this->msgs[] = 'NL ' . $data[1] . ' ' . $data[0] . '->' . $data[3] . ' ' . $data[2] . ' muss geändert werden<br>';
// 								$thisdivision=$this->ladeLeitWartePtr->divisionsPtr->getDPDivision(substr($data[0],2,2));
// 								$zspls=$this->ladeLeitWartePtr->zsplPtr->getWhere(null,array(array('division_id','=',$thisdivision['division_id'])));
// 								foreach($zspls as $zspl)
// 								{
// 									if(strlen($zspl['dp_zspl_id'])==6)
// 									{
// 										$this->msgs[]="&nbsp;&nbsp;&nbsp;&nbsp; ZSPL ".$zspl['name'].' '.$zspl['dp_zspl_id'].' -> '.substr($data[2],0,4).substr($zspl['dp_zspl_id'],4,4).'<br>';
// 										$depots=$this->ladeLeitWartePtr->depotsPtr->getWhere(null,array(array('zspl_id','=',$zspl['zspl_id'])));
// 										foreach($depots as $depot)
// 											$this->msgs[]="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ZSP ".$depot['name'].';'.$depot['dp_depot_id'].';'.substr($data[2],0,4).substr($depot['dp_depot_id'],4,8).' <br>';
// 									}

// 								}

                }
            }
        }

    }

    function printContent() {
        include("pages/" . $this->user->getUserRole() . ".php");
    }
}

