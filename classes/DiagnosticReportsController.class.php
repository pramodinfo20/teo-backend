<?php

/**
 * AdminHistory.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * AdminHistoryController Class, the main class
 */

$GLOBALS['search_hide_fieldset'] = true;

class DiagnosticReportsController extends PageController {

    public $oQueryHolder;

//  vehicle data
    public $vehicleDataArray;
    public $allTeoSiaRunsForVin;
    public $qmLockHistoryArray;
    public $statesOfSeriesNumber;

    private $vehicleId = 0;
    private $vehicleVin = 0;
    private $selectedTeoSession = 0;

    public $sCreateDbTable = "";
    public $searchController;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->searchController = new SearchController($ladeLeitWartePtr, $container, $requestPtr, $user);
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->displayFooter = $container->getDisplayFooter();

        $this->setupHeaders();

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
        $this->displayHeader->enqueueJs("sts-custom-search", "js/sts-custom-search.js");
        $this->displayHeader->enqueueJs("search", "js/search.js");
        $this->searchController->initializeHeaders();
        $this->displayHeader->enqueueLocalJs('var depot_list=' . json_encode($this->searchController->setupDepots()));
        $this->msgs = null;

//        $this->vehicleId = 102;
//        $this->vehicleId = 7780;
//        $this->vehicleId = 7785;
//        $this->vehicleId = 5;
//        $this->vehicleId = 18397;

        if (isset($_GET['vehicleId'])) {
            $this->vehicleId = $_GET['vehicleId'];
            $this->vehicleVin = $this->ladeLeitWartePtr->vehiclesPtr->getVinFromId($this->vehicleId);
        }


        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            if ($this->vehicleId > 0) {

                $this->vehicleDataArray = $this->getDataSingleVehicle($this->vehicleId);
                $this->vehicleDataArray += (array)$this->getDateOfProduction($this->vehicleVin);
                $this->vehicleDataArray += $this->getLastDiagnosticDate($this->vehicleVin);
                $this->qmLockHistoryArray = $this->getQMLockStateHistory($this->vehicleId);
                $this->vehicleDataArray += (array)$this->getEOLDate($this->vehicleVin);
                $this->vehicleDataArray += (array)$this->getNumberOfDiagnosticSession($this->vehicleVin);
                $this->vehicleDataArray += (array)$this->sqlGetNumberOfWorkshopStops($this->vehicleId);
                $this->vehicleDataArray += (array)$this->sqlGetCocHsn($this->vehicleId);
                $this->vehicleDataArray += array_values($this->sqlGetDealerServiceWorkshop($this->vehicleId));
                $this->allTeoSiaRunsForVin = (array)$this->sqlGetAllTeoSiaRuns($this->vehicleVin);

                if (isset($_GET['selectedTeoSession'])) {
                    $this->selectedTeoSession = $_GET['selectedTeoSession'];
                    $this->vehicleDataArray += $this->sqlGetSelectedTeo($this->selectedTeoSession);
                } else {
                    $this->vehicleDataArray += $this->getFirstTEODate($this->vehicleVin);
                }

            }


            $this->setupHeaders();

            $result = array();

            $headings[]["headingone"] = $this->headers;
            $result = array_merge($headings, $result);

            $this->qs_vehicles = new DisplayTable($result, array(
                'id' => 'qs_vehicles_list'
            ));

            $this->form = new CommonFunctions_GeneralSearch($ladeLeitWartePtr, $this->displayHeader, $user, $requestPtr, $this->common_action, null);

            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/diagnosticReports.php");
        $this->displayFooter->printContent();
    }

    function checkIfAllSeriesNumberRegistered() {
        $queryResult = $this->getAllSeriesNumbers($this->vehicleVin);

        foreach ($queryResult as $element) {
            if ($element['series_numbers'] == "unknown") return false;
        }
        return true;
    }

    public function getStateOfSeriesNumber($vehicle_id) {

        return $this->sqlGetPartAssignedToVehicle($vehicle_id);
    }


    // Ajax functions

    public function ajaxGetQMLockStateHistory() {
        echo json_encode($this->getQMLockStateHistory($_GET['vehicleId']));
        exit;
    }

    public function ajaxGetAllSeriesNumbers() {
//        todo: Uncomment and remove temporary
        $result = $this->sqlGetPartAssignedToVehicle($_GET['vehicleId']);
        echo json_encode($result);
        exit;
    }

    public function ajaxGetAllTeoSiaRuns() {
        $vin = $this->ladeLeitWartePtr->vehiclesPtr->getVinFromId($_GET['vehicleId']);
        $result = $this->sqlGetAllTeoSiaRuns($vin);
        echo json_encode($result);
        exit;
    }


    // SQL Queries


    private function getDataSingleVehicle($vehicle_id) {
        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->join('vehicle_variants', 'vehicle_variant_id=vehicle_variant', 'LEFT JOIN')
            ->join('penta_numbers', 'penta_numbers.penta_number_id=vehicles.penta_number_id', 'INNER JOIN')
            ->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'LEFT  JOIN')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('processed_teo_status', 'processed_teo_status.diagnostic_session_id=latest_teo_status.diagnostic_session_id', 'FULL OUTER JOIN')
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne(
                "vehicle_id,
                vin,
                ikz,
                code,
                penta_kennwort,
                c2cbox,
                vehicle_variant,
                windchill_variant_name,
                depots.name as depot_name,
                depots.depot_id,
                finished_status,
                qmlocked,
                special_qs_approval,
                vehicle_variant,
                vehicles.penta_number_id,
                penta_number,
                latest_teo_status.diagnose_status_time,
                latest_teo_status.diagnose_status,
                latest_teo_status.diagnostic_session_id,
                processed_diagnose_status,
                processed_teo_status.targetsw_changed,
                battery");
        return $vehicle;
    }
//    public function getDataSingleVehicle($vehicle_id) {
//        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
//            ->join('vehicle_variants', 'vehicle_variant_id=vehicle_variant', 'LEFT JOIN')
//            ->join('penta_numbers', 'penta_numbers.penta_number_id=vehicles.penta_number_id', 'INNER JOIN')
//            ->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'LEFT  JOIN')
//            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
//            ->join('processed_teo_status', 'processed_teo_status.diagnostic_session_id=latest_teo_status.diagnostic_session_id', 'FULL OUTER JOIN')
//            ->where('vehicle_id', '=', $vehicle_id)
//            ->getOne("*");
//        return $vehicle;
//    }

    public function setupHeaders() {
        //if only showing vehicles from production depots then allow searching for vehicles based on finished_status
        if ($this->production_depots_only)
            $finished_status_params = ['id' => 'finished_status_search_ctrl', 'data-placeholder' => "t/f&nbsp;eingeben", 'data-sorter' => 'false'];
        else
            $finished_status_params = ['id' => 'finished_status_search_ctrl', 'data-filter' => 'false', 'data-sorter' => 'false'];

        //keys as table column names, values as header and additional data parameters for jQuery tablesorter
        $this->headers = ['vin' => ['VIN'],
            'ikz' => ['IKZ'],
            'code' => ['AKZ'],
            'penta_kennwort' => ['Penta Kennwort'],
            'c2cbox' => ['C2CBox ID'],
            'windchill_variant_name' => ['Variant'],
            'penta_number' => ['Penta Artikel', ['data-filter' => 'false', 'data-sorter' => 'false']],
            'dname' => ['Produktionsort/Standort'],
            'processed_diagnose_status' => ['TEO Status'],
            'special_qs_approval' => ['Sonder- genehmigung', ['data-filter' => 'false', 'data-sorter' => 'false']],
            'finished_status' => ['QS Fertig Status', $finished_status_params],
            'qmlocked' => ['QM gesperrt', ['data-filter' => 'false', 'data-sorter' => 'false']],
            'qs_fault' => ['Fehler eintragen', ['data-filter' => 'false', 'data-sorter' => 'false']],
            'print_details' => ['Drucken', ['data-filter' => 'false', 'data-sorter' => 'false']]
        ];
        //table column names for jquery tablesorter to be used when processing fetched data
        $this->header_cols = array_keys($this->headers);
        //these columns are only for webinterface display, not to be used when exporting data to a file
        $this->ignore_for_export = ['qs_fault', 'print_details'];
        //these columns are to be mentioned explicitly for export data since they are combined into one column in webinterface display
        $this->add_for_export['diagnose_status_time'] = ['TEO Datum'];
        //merge both headers for use in exported file
        $this->header_labels_for_export = $this->headers + $this->add_for_export;
        //select the available statuses in database for display in the export data function
        $status = $this->ladeLeitWartePtr->newQuery('processed_teo_status')->get('distinct (processed_diagnose_status)');
        if (is_array($status))
            $this->available_status = array_column($status, 'processed_diagnose_status');
        else
            $this->available_status = 'cycki';
    }


    public function getFilteredVehicles() {
        $filter_vehicles = null;
        if (isset($_POST['teo_fehler_search'])) {
            $allEcus = $this->ladeLeitWartePtr->newQuery('ecus')
                ->where('ecu_id', '>', 0)
                ->orderBy('name')
                ->get('ecu_id=>name');
            $result = null;
            foreach ($allEcus as $ecu) {
                $ecu = strtoupper($ecu);
                if (isset($_POST[$ecu . '_dtcs'])) {
                    $dtcs = filter_var($_POST[$ecu . '_dtcs'], FILTER_SANITIZE_STRING);
                    if ($dtcs == '*') {
                        $dtcs_array = '*';
                    } else
                        $dtcs_array = explode(',', $dtcs);
                    if (empty($result)) {
                        $diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
                        $result = $diagnosePtr->newQuery('latest_teo_status')->join('material_dtcs_revocation_processed', 'using(diagnostic_session_id)', 'JOIN');
                    }
                    if (is_array($dtcs_array)) {
                        foreach ($dtcs_array as $dtc_number) $result = $result->multipleOrWhere('material_dtcs_revocation_processed.ecu', '=', $ecu, 'AND', 'material_dtcs_revocation_processed.dtc_number', '=', $dtc_number);
                    } else if ($dtcs_array == '*')
                        $result->orWhere('material_dtcs_revocation_processed.ecu', '=', $ecu);
                }
            }

            if (!empty($result)) {
                $result = $result->join('vin_wc_variant_penta', 'using(vin)', 'JOIN')->get('distinct vehicle_id');

                if (!empty($result)) {
                    $filter_vehicles = array_column($result, 'vehicle_id');

                } else
                    $filter_vehicles = 'error';
            }
            $result = null;
            if (isset($_POST['log_names'])) {
                $diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
                $result = $diagnosePtr->newQuery('latest_teo_status')->join('material_log_revocation_processed', 'using(diagnostic_session_id)', 'JOIN');
                $log_names = filter_var($_POST['log_names'], FILTER_SANITIZE_STRING);
                $log_names = explode(',', $log_names);
                $result = $result->where('material_log_revocation_processed.name', 'IN', $log_names)->where('material_log_revocation_processed.passed', '=', 'f')->join('vin_wc_variant_penta', 'using(vin)', 'JOIN')->get('distinct vehicle_id');

                if (!empty($result)) {
                    $filter_vehicles_log = array_column($result, 'vehicle_id');
                    if (is_array($filter_vehicles)) {
                        if ($_POST['combine_op'] == 'or') $filter_vehicles = array_merge($filter_vehicles, $filter_vehicles_log);
                        else            $filter_vehicles = array_intersect($filter_vehicles, $filter_vehicles_log);
                    } else $filter_vehicles = $filter_vehicles_log;
                } else {
                    $filter_vehicles_log = 'error';
                }
            }
        } else if (isset($_POST['qs_faults_search_input']) || isset($_POST['child_cat'])) {
            $qs_faults_input = filter_var_array($_POST['qs_faults_search_input'], FILTER_SANITIZE_STRING);
            $cat_search = filter_var($_POST['qs_fault_cat_search'], FILTER_SANITIZE_STRING);

            $search_fields_empty = true;
            if (!empty($qs_faults_input)) {
                $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list');
                foreach ($qs_faults_input as $qs_cat_id => $qs_input) {
                    //without a defined callback, array_filter simply removes all the empty values
                    if (!empty(array_filter($qs_input))) {
                        foreach ($qs_input as $field_key => $field_val) {
                            if (!empty($field_val)) {
                                if ($field_val == 'sonstiges') continue;
                                $search_fields_empty = false;
                                $field_val = str_replace('*', '', $field_val);
                                //verify with category too
                                $result->multipleOrWhere('qs_fcat_id', '=', $qs_cat_id, 'AND', 'field_key', '=', $field_key, 'AND', 'field_value', 'LIKE', '%' . $field_val . '%');
                            }
                        }
                    } else {
                        $search_fields_empty = false;
                        $result->orWhere('qs_fcat_id', '=', $qs_cat_id);
                    }
                }
            } else if (!empty($cat_search) && $cat_search != 0) {
                $search_fields_empty = false;
                $child_cats = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')->where('parent_cat', '=', $cat_search)->get('qs_fcat_id');
                $child_cats = array_column($child_cats, 'qs_fcat_id');
                $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list')->where('qs_fcat_id', 'IN', $child_cats);
            }
            if (!$search_fields_empty) {
                $result = $result->get('distinct vehicle_id');
                if (!empty($result)) {
                    $filter_vehicles = array_column($result, 'vehicle_id');

                } else
                    $filter_vehicles = 'error';

            }
        }

        return $filter_vehicles;
    }

    public function search() {

//    $this->qs_fault_search->genSearchForm();

        //  echo 555;
    }

    public function executeAjaxRows() {
//        parent::executeAjaxRows(); // TODO: Change the autogenerated stub
    }


    private function getDateOfProduction($vin) {
        $result = $this->diagnosePtr->newQuery('serial_numbers')
            ->where('vin', '=', $vin)
            ->getOne('timestamp as date_of_production');
        return $result;
    }

    private function getLastDiagnosticDate($vin) {
        $result = $this->diagnosePtr->newQuery('general')
            ->where('vin', '=', $vin)
            ->orderby('date', 'desc')
            ->getOne('date as last_diagnostic_session_date, diagnosis_version as diagnostic_sw_version');
        return $result;
    }

    private function getFirstTEODate($vin) {
        $result = $this->diagnosePtr->newQuery('general')
            ->where('vin', '=', $vin)
            ->where('system_mode', '=', 'EOLT')
            ->orderby('date')
            ->getOne('date as teo_date');
        return $result;
    }

    private function sqlGetSelectedTeo($teoId) {
        $result = $this->diagnosePtr->newQuery('general')
            ->where('diagnostic_session_id', '=', $teoId)
//            ->where('system_mode', '=', 'EOLT')
//            ->orderby('date')
            ->getOne('date as teo_date');
        return $result;
    }

    private function getNumberOfDiagnosticSession($vin) {
        $result = $this->diagnosePtr->newQuery('general')
            ->where('vin', '=', $vin)
            ->getOne('count(*) as number_of_diagnostic_session');
        return $result;
    }

    private function sqlGetNumberOfWorkshopStops($vehicleId) {
        $result = $this->ladeLeitWartePtr->newQuery('workshop_states')
            ->where('vehicle_id', '=', $vehicleId)
            ->getOne('count(*) as number_of_workshop_stops');
        return $result;
    }

    private function getAllSeriesNumbers($vin) {
        $result = $this->diagnosePtr->newQuery('general')
            ->where('vin', '=', $vin)
            ->orderby('date', 'desc')
            ->get('date, ignitionkeynumber as series_numbers');
        return $result;
    }


    private function getEOLDate($vin) {
        $result = $this->diagnosePtr->newQuery('general')
            ->where('vin', '=', $vin)
            ->where('system_mode', '=', 'EOLT')
            ->orderby('date')
            ->getOne('date as eol_date');
        return $result;
    }

    private function sqlGetAllTeoSiaRuns($vin) {
        $result = $this->diagnosePtr->newQuery('general')
            ->where('vin', '=', $vin)
//            ->where('system_mode', '=', 'EOLT')
            ->orderby('date', 'desc')
            ->get('*');
        return $result;
//        return false;
    }


    private function getQMLockStateHistory($vehicle_id) {
        $result = $this->ladeLeitWartePtr->newQuery('qm_lock_history')
            ->join('users', 'qm_lock_history.userid=users.id', 'LEFT JOIN')
            ->where('vehicle_id', '=', $vehicle_id)
            ->orderBy('update_ts', 'desc')
            ->get('update_ts, old_status, new_status, qmcomment, username');
        return $result;
    }

    private function sqlGetCocHsn($vehicle_id) {
        $result = $this->ladeLeitWartePtr->vehicleVariantsPtr->newQuery()
            ->where('vehicle_variant_id', '=', $vehicle_id)
//            ->getOne('hsn, approval_code');
            ->getOne('*');
        return $result;
    }

    private function sqlGetDealerServiceWorkshop($vehicle_id) {
        $result = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'LEFT JOIN')
            ->join('workshops', 'depots.workshop_id=workshops.workshop_id', 'LEFT JOIN')
//            ->join('workshop_companies', 'workshops.workshop_company_id=workshop_companies.workshop_company_id', 'LEFT JOIN')
            ->where('vehicle_id', '=', $vehicle_id)
//            ->get('*');
            ->get('workshops.name as workshop_name, workshops.location as workshop_location, workshops.street as workshop_street');
        return $result;
    }

    private function sqlGetPartAssignedToVehicle($vehicle_id) {
        $result = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->join('variant_parts_mapping', 'vehicles.vehicle_variant=variant_parts_mapping.variant_id', 'LEFT JOIN')
            ->join('parts', 'variant_parts_mapping.part_id=parts.part_id', 'LEFT JOIN')
            ->where('vehicle_id', '=', $vehicle_id)
            ->get('parts.name as part_name');
        return $result;
    }


}
