<?php
require_once $_SERVER['STS_ROOT'] . '/includes/sts-datetime.php';

class SearchController extends PageController {

    public $production_depots_only;


    /**
     * @var CommonFunctions_GeneralSearch
     */
    public $form;

    public $formQs;

    public $fullView = false;

    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);
//r($ladeLeitWartePtr);

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        $this->content = '';
        $this->vehicles = null;
        $this->common_action = null;

        $this->action = $_GET['action'];
        $this->setupHeaders();


        $filter_vehicles = $this->getFilteredVehicles();


        $result = array();

        $headings[]["headingone"] = $this->headers;
        $result = array_merge($headings, $result);

        $this->qs_vehicles = new DisplayTable($result, array(
            'id' => 'qs_vehicles_list'
        ));


        $this->form = new CommonFunctions_GeneralSearch($ladeLeitWartePtr, $this->displayHeader, $user, $requestPtr, $this->common_action, null);

//    $this->teo_search = new CommonFunctions_TeoSearch($ladeLeitWartePtr, $this->displayHeader, $user, $requestPtr, $this->common_action, $filter_vehicles);
//    $this->teo_search->genSearchForm();

//    $this->formQs = new CommonFunctions_QsFaultSearch($ladeLeitWartePtr, $this->displayHeader, $user, $requestPtr, $this->common_action, $filter_vehicles);
//    $this->formQs->genSearchForm();
    }

    //print pdf
    public function printpdf() {
        $vehicle_id = filter_var($_REQUEST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $vehicle = $this->getDataSingleVehicle($vehicle_id);
        $this->addBodySerialNumber($vehicle);

        $this->ladeLeitWartePtr->vehiclesPtr->diagnoseDetails($vehicle);
        $this->ladeLeitWartePtr->vehiclesPtr->fetchTeoError($vehicle, $this->user, true);
        $eol_protokoll = new QSEOLProtokollPDF($vehicle);
    }


    public function getDataSingleVehicle($vehicle_id) {
        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->join('vehicle_variants', 'vehicle_variant_id=vehicle_variant', 'LEFT JOIN')
            ->join('penta_numbers', 'penta_numbers.penta_number_id=vehicles.penta_number_id', 'INNER JOIN')
            ->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'LEFT  JOIN')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('processed_teo_status', 'processed_teo_status.diagnostic_session_id=latest_teo_status.diagnostic_session_id', 'FULL OUTER JOIN')
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne("vehicle_id,vin,ikz,code,penta_kennwort,c2cbox,vehicle_variant,windchill_variant_name,
            depots.name as dname,depots.depot_id,finished_status,qmlocked,special_qs_approval,vehicle_variant,vehicles.penta_number_id,penta_number,
            latest_teo_status.diagnose_status_time,latest_teo_status.diagnose_status,latest_teo_status.diagnostic_session_id,processed_diagnose_status,processed_teo_status.targetsw_changed");
        return $vehicle;
    }

    protected function addBodySerialNumber(&$vehicle) {
        $vehicle['body_serial'] = '';
        $vehicle['body_date'] = '';

        if ($this->diagnosePtr) {
            $set = $this->diagnosePtr->newQuery('serial_numbers')
                ->where('vin', '=', $vehicle['vin'])
                ->where('part_number', '=', 'D17X06A00200_01')
                ->getOne('serial_number, timestamp');

            $vehicle['body_serial'] = safe_val($set, 'serial_number', '');
            $vehicle['body_date'] = to_locale_date(safe_val($set, 'timestamp', ''));
        }
    }
//-----------------------------
    public function initFullPageView() {
        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayFooter = $this->container->getDisplayFooter();

        $this->displayHeader->setTitle("StreetScooter Cloud System : QS");

        $this->displayHeader->enqueueJs("jquery-datepicker-de", "js/jquery.ui.datepicker-de.js");
        $this->displayHeader->enqueueJs("sts-custom-search", "js/sts-custom-search.js");
        $this->displayHeader->enqueueJs("search", "js/search.js");

        $this->setupDepots();

            $this->displayHeader->printContent();

            $this->fullView = 1;
    }

    public function initializeHeaders() {
        $this->displayHeader = $this->container->getDisplayHeader();
    }

    public function setupHeaders() {
        //if only showing vehicles from production depots then allow searching for vehicles based on finished_status
        if ($this->production_depots_only)
            $finished_status_params = ['id' => 'finished_status_search_ctrl', 'data-placeholder' => "t/f&nbsp;eingeben", 'data-sorter' => 'true'];
        else
            $finished_status_params = ['id' => 'finished_status_search_ctrl', 'data-filter' => 'false', 'data-sorter' => 'true'];

        //keys as table column names, values as header and additional data parameters for jQuery tablesorter
        $this->headers = ['vin' => ['VIN'],
            'ikz' => ['IKZ'],
            'code' => ['AKZ'],
            'penta_kennwort' => ['Penta Kennwort'],
            'c2cbox' => ['C2CBox ID'],
            'windchill_variant_name' => ['Variant'],
            'penta_number' => ['Penta Artikel', ['data-filter' => 'false', 'data-sorter' => 'true']],
            'dname' => ['Produktionsort/Standort'],
            'processed_diagnose_status' => ['TEO Status'],
            'special_qs_approval' => ['Sonder- genehmigung', ['data-filter' => 'false', 'data-sorter' => 'true']],
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


    public function setupDepots() {
        $json_depots = $this->ladeLeitWartePtr->newQuery('depots')->get("concat(name::text,' ( ',dp_depot_id,')') as label,depot_id::text as value");
        $db_depots = array_combine(array_column($json_depots, 'value'), array_column($json_depots, 'label'));
        $depots_str = '';
        $settings = $this->ladeLeitWartePtr->newQuery('user_settings')->where('sts_userid', '=', $_SESSION['sts_userid'])->get('settings');

        $databaseDepots = unserialize($settings[0]['settings'])['depots'];
        if (!empty($settings)) {
            $depots = [];
            foreach ($databaseDepots as $key => $value) {
                $value == 1 ? array_push($depots, $key) : "";
            }
        }

        if (isset($_POST['selected_depots'])) $depots = $_POST['selected_depots'];

        $default_depots = '';
        if (empty($depots)) {
            $production_depots = $this->ladeLeitWartePtr->newQuery('depots')->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
                ->where('divisions.production_location', '=', 't')
                ->get('depot_id');

            $this->productionDepots = array_column($production_depots, 'depot_id');//(0,3348,3367);
            $depots = $this->productionDepots;
            $default_depots = 'default_depots';
            $this->production_depots_only = true;
        }
        $depot_checkbox_str = '';

        if (!empty($settings)) {
            foreach ($databaseDepots as $depot => $value) {
                if ($value == 1) {
                    $depot_checkbox_str .= '<label for="depot_check_' . $depot . '"><input type="checkbox" name="selected_depots[]" checked=checked id="depot_check_' . $depot . '" class="' . $default_depots . '" value="' . $depot . '">' . $db_depots[$depot] . '</label>';

                } else {
                    $depot_checkbox_str .= '<label for="depot_check_' . $depot . '"><input type="checkbox" name="selected_depots[]"  id="depot_check_' . $depot . '" class="' . $default_depots . '" value="' . $depot . '">' . $db_depots[$depot] . '</label>';
                }
            }
        } else {
            foreach ($depots as $depot) {
                $depot_checkbox_str .= '<label for="depot_check_' . $depot . '"><input type="checkbox" name="selected_depots[]" checked=checked id="depot_check_' . $depot . '" class="' . $default_depots . '" value="' . $depot . '">' . $db_depots[$depot] . '</label>';
            }
        }

        if (!empty($depots)) $depots_str = implode(',', $depots);

        $locationFilter = $this->translate['generalSearch']['locationFilter'];
        $this->depotFilterContent = '
        <fieldset class="qs_faults_tab_container" style="display: block">
            <legend class="collapsible"><span class="genericon genericon-expand"></span>' . $locationFilter . '</legend>
        <div>';

        if ($default_depots) $produktion_label = $this->translate['generalSearch']['locationSelectedTitle'];
        else $produktion_label = $this->translate['generalSearch']['selectedLocation'];

        $selectedLocationLabel = $this->translate['generalSearch']['locationFilter'];
        $locationSelectedPlaceholder = $this->translate['generalSearch']['locationSelectedPlaceholder'];
        $onlyProductionLocations = $this->translate['generalSearch']['onlyProductionLocations'];
        $this->depotFilterContent .= '<div id="selected_depot_wrap">' . $produktion_label . $depot_checkbox_str . '
                                    </div></div>
                                  
                                    <div class="collapsible_content" style="display: none">
                                     ' . $selectedLocationLabel . '
                                   <input type="text" name="depots_search" id="depots_search" value="" placeholder="' . $locationSelectedPlaceholder . '" style="width: 200px">
                                   <input type="hidden" name="depot_vals__unused__" id="depot_vals" value="' . $depots_str . '"><br><br>
                                   <input type="submit" value="' . $this->translate['generalSearch']['btnSearch'] . '"> <a href="' . $_SERVER['PHP_SELF'] . '">' . $onlyProductionLocations . '</a>
                                    </div>

                                    </fieldset>
                                   </form> ';

        $this->displayHeader->enqueueLocalJs('var depot_list=' . json_encode($json_depots));

        return $json_depots;
    }


    /**
     * Method for preparing module. Generate fields, enque scripts, override qs methods etc
     */
    public function search() {

//    $this->qs_fault_search->genSearchForm();

        //  echo 555;
    }

    function printContent() {
            include("pages/search.php");

            $this->displayFooter->printContent();
    }
}