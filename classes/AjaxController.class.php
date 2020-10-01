<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 1/14/19
 * Time: 10:05 AM
 */

class AjaxController extends PageController {

    public $production_depots_only, $qs_fault_top_select, $enable_qs_fault_check_finish_status;

    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);


        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        $this->content = '';
        $this->vehicles = null;


        $this->action = $this->requestPtr->getProperty('action');
        $this->setupHeaders();
        $this->setupDepots();


        $result = array();

        $headings[]["headingone"] = $this->headers;
        $result = array_merge($headings, $result);

        $this->qs_vehicles = new DisplayTable($result, array(
            'id' => 'qs_vehicles_list'
        ));

        //$qS = new QsController($ladeLeitWartePtr, $container, $requestPtr, $user);
        //echo $qS->getFilteredVehicles();
    }

    public function Run($skipTable = false) {
        $this->ajaxRows($skipTable);
        //echo $this->getFilteredVehicles();
    }

    public function getAllEcus() {
        echo json_encode($this->ladeLeitWartePtr->newQuery('ecus')
            ->get('name'));
    }

    /**
     * Method in action to load json with cubcategories
     */
    public function runFailuresSubCats() {

        $aCats = array(
            1 => array('fields' => array(
                'part_number' => 'Artikel Nummer',
                'error_desc' => 'Beschreibung',
                'solution' => 'Lösungsvorschlag',
                'comment' => 'Kommentar',
            )),
            2 => array('fields' => array(),
                'subcat' => array(
                    3 => 'Fahrertüre',
                    5 => 'Koffertüre Fahrerseite',
                    4 => 'Beifahrertüre',
                    6 => 'Koffertüre Beifahrerseite',
                    7 => 'Heckklappe',
                    8 => 'Spaltbild'
                ),
                'subcat_fields' => array(
                    'error_desc' => 'Beschreibung',
                    'solution' => 'Lösungsvorschlag',
                    'comment' => 'Kommentar',
                )),
            9 => array('fields' => array(
                'part_number' => 'Sts Teilenummer',
                'part_desc' => 'Bauteilbeschreibung',
                'error_desc' => 'Fehlerbeschreibung',
                'solution' => 'Lösungsvorschlag',
                'comment' => 'Kommentar',
            )),
            11 => array(
                'fields' => array(),
                'subcat' => array(
                    12 => array(
                        'label' => 'Koffer',
                        'fields' => array(
                            //'koffer_select' => 'Betroffenes Bauteil',
                            //'_sonstiges_text' => 'if[sonstiges]',
                            'comment' => 'Kommentar'
                        ),
                        'subcat' => array(
                            'beifahrerseite' => 'Beifahrerseite',
                            'fahrerseite' => 'Fahrerseite',
                            'heckklappe' => 'Heckklappe',
                            'sonstiges' => 'Sonstiges Bauteil'
                        ),
                        'subcat_name' => 'koffer_select'
                    ),

                    13 => array(
                        'label' => 'Front',
                        'fields' => array(
                            'part_number' => 'Bauteilnummer betroffenen Bauteil',
                            'comment' => 'Kommentar',
                        )),
                    14 => array(
                        'label' => 'Fahrerseite',
                        'fields' => array(
                            'part_number' => 'Bauteilnummer betroffenen Bauteil',
                            'comment' => 'Kommentar',
                        )),
                    15 => array(
                        'label' => 'Beifahrerseite',
                        'fields' => array(
                            'part_number' => 'Bauteilnummer betroffenen Bauteil',
                            'comment' => 'Kommentar',
                        )),
                )/**,
                 * 'subcat_fields' => array(
                 * 'error_desc' => 'Description',
                 * 'solution' => 'Solution',
                 * 'comment' => 'Comment', **/
            ),
            16 => array( //Dichtigkeit
                'fields' => array(
                    'error_desc' => 'Fehlerbeschreibung',
                    'solution' => 'Lösungsvorschlag',
                    'comment' => 'Kommentar',
                ),
                'subcat' => array(
                    'fahrertuere' => 'Fahrertüre undicht',
                    'beifahrertuere' => 'Beifahrertüre undicht',
                    'sonstiges' => 'Sonstige Undichtigkeit',
                ),
                'subcat_name' => 'dichtigkeit_select'
            ),
            10 => array( //Montageprobleme
                'fields' => array(
                    'part_number' => 'Sts Teilenummer',
                    'part_desc' => 'Bauteilbeschreibung',
                    'error_desc' => 'Fehlerbeschreibung',
                    'solution' => 'Lösungsvorschlag',
                    'comment' => 'Kommentar',
                )),
            17 => array( //Software
                'fields' => array(
                    'error_desc' => 'Fehlerbeschreibung',
                    'solution' => 'Lösungsvorschlag',
                    'comment' => 'Kommentar',
                ),
                'subcat' => array(
                    'ACDC' => 'ACDC',
                    'BCM' => 'BCM',
                    'BMS' => 'BMS',
                    'BMS Slave' => 'BMS Slave',
                    'C2C' => 'C2C',
                    'CDIS' => 'CDIS', //todo: finish list of ECU's
                ))
        );

        $searchIdCat = $_GET['qs_cat_id'];

        if (isset($aCats[$searchIdCat])) {
            if (isset($_GET['qs_subcat_id']) && $_GET['qs_subcat_id'] != 0) {
                echo json_encode($aCats[$searchIdCat]['subcat'][$_GET['qs_subcat_id']]);
            } else {
                echo json_encode($aCats[$searchIdCat]);
            }
        }

    }


    public function setupDepots() {
        $json_depots = $this->ladeLeitWartePtr->newQuery('depots')->get("concat(name::text,' ( ',dp_depot_id,')') as label,depot_id::text as value");
        $db_depots = array_combine(array_column($json_depots, 'value'), array_column($json_depots, 'label'));
        $depots_str = '';

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

    }


    public function ajaxRows($skipTable = false) {
        // $_SESSION['timeperf'].=microtime(true).' start ajaxRows<br>';
        $page = $this->requestPtr->getProperty('page');
        $size = $this->requestPtr->getProperty('size');
        $fcol = $this->requestPtr->getProperty('filter');
        $scol = $this->requestPtr->getProperty('column'); // 1 desc 0 asc
        $filtered_vehicles = $this->requestPtr->getProperty('filtered_vehicles'); // 1 desc 0 asc
        $vchecked = $this->requestPtr->getProperty('vchecked'); // 1 desc 0 asc

//    $depots = $this->requestPtr->getProperty('depot_vals');
        $depots = '3348,3368,0,3367,3170'; //we override it further in code - not used

        if ($size == 'all') {
            $size = 100;
        }

//    $depots = trim(filter_var($depots, FILTER_SANITIZE_STRING));
//    if (!empty($depots)) {
//
//      $depots = explode(',', $depots);
//      if (!array_diff($depots, $this->productionDepots))
//        $productionDepotsOnly = true;
//      else
//        $productionDepotsOnly = false;
//    }

        $productionDepotsOnly = false;

        $result['headers'] = $this->header_cols;
        if (!empty($filtered_vehicles) && $filtered_vehicles != 'error')
            $filtered_vehicles = explode(',', $filtered_vehicles);
        elseif (!empty($filtered_vehicles) && $filtered_vehicles == 'error') {
            echo json_encode($result); // just output empty rows to satisfy tablesorter and exit from here
            exit(0);
        } else
            $filtered_vehicles = null;

        $aSearch = $this->ladeLeitWartePtr->vehiclesPtr->getEOLVehiclesNewWithSearch($depots, 'saveQS', $page, $size, $fcol, $scol, $filtered_vehicles, null, $productionDepotsOnly, $skipTable);

        $rows = $aSearch['records'];

        if (!empty($vchecked))
            $vchecked = explode(',', $vchecked);
        else
            $vchecked = null;

        $this->processVehicles($rows, $vchecked);

        $result['total_rows'] = $aSearch['my_total_count'] ? $aSearch['my_total_count'] : 0;
        //todo: run query twice first without limit, second with limits to count number of all results, not only shown
        $result['fcol'] = json_encode($fcol);
        $result['page'] = $page;
        $result['size'] = $size;
        $result['rows'] = $rows ? $rows : 0;
        // $_SESSION['timeperf'].=microtime(true).' end ajaxRows<br>';
        echo json_encode($result);
        exit(0);
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
        }
        if (isset($_POST['qs_faults_search_input']) || isset($_POST['child_cat'])) {
            $qs_faults_input = filter_var_array($_POST['qs_faults_search_input'], FILTER_SANITIZE_STRING);
            $cat_search = filter_var($_POST['general_search_cat_qs_errors'], FILTER_SANITIZE_STRING);

            $search_fields_empty = true;
            if (!empty($qs_faults_input)) {
                $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list');//->join('qs_fault_cat_inputs','qs_fault_cat_inputs.qs_fcat_id=qs_fault_list.qs_fcat_id', 'INNER JOIN');
                foreach ($qs_faults_input as $qs_cat_id => $qs_input) {
                    //without a defined callback, array_filter simply removes all the empty values
                    //var_dump($qs_input);
                    if (!empty(array_filter($qs_input))) {
                        foreach ($qs_input as $field_key => $field_val) {
                            if (!empty($field_val)) {
                                if ($field_val == 'sonstiges') continue;
                                $search_fields_empty = false;
                                $field_val = str_replace('*', '', $field_val);
                                $result->multipleOrWhere('qs_fcat_id', '=', $qs_cat_id, 'AND', 'field_key', '=', $field_key, 'AND', 'field_value', 'LIKE', '%' . $field_val . '%');

                            }
                        }
                    } else {
                        echo $qs_input;
                        $search_fields_empty = false;
                        $result->multipleOrWhere('field_key', '=', $qs_cat_id, 'AND', 'field_value', '=', $qs_input);

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


    public function processVehicles(&$vehicles, $vchecked) {
        foreach ($vehicles as &$vehicle) {

            $allow_qs_fault_edit = false;

            if ($this->user->user_can('edit_qs_faults'))
                $allow_qs_fault_edit = true;

            if (empty($vehicle['code']))
                $vehicle['code'] = ' ';
            if (empty($vehicle['ikz']))
                $vehicle['ikz'] = ' ';
            if (empty($vehicle['code']))
                $vehicle['code'] = ' ';
            if (empty($vehicle['penta_kennwort']))
                $vehicle['penta_kennwort'] = ' ';

            if (!empty($vehicle['diagnose_status_time']))
                $vehicle['diagnose_status_time'] = date('Y-m-d H:i', strtotime($vehicle['diagnose_status_time']));
            else
                $vehicle['diagnose_status_time'] = '';

            if (empty($vehicle['tables']))
                $vehicle['status_extra_data'] = 'Keine Daten Verfügbar!';

            if (empty($vehicle['processed_diagnose_status'])) {
                $status_content = 'Kein TEO Status<br><a href="#" class="fetchswentry"
                data-vehicle_id="' . $vehicle['vehicle_id'] . '"
                data-vin="' . $vehicle['vin'] . '"
                >SW Version Eingabe</a>';
            } else {
                if ($vehicle['processed_diagnose_status'] != 'PASSED') {
                    $status_content = '<a href="#" class="fetcherror"
                data-vehicle_id="' . $vehicle['vehicle_id'] . '"
                data-vin="' . $vehicle['vin'] . '"
                data-vehicle_variant="' . $vehicle['vehicle_variant'] . '"
                data-diagnostic_session_id="' . $vehicle['diagnostic_session_id'] . '"
                id="processed_status_wrap_' . $vehicle['vehicle_id'] . '"
                >' . $vehicle['processed_diagnose_status'] . '</a><br>' . $vehicle['diagnose_status_time'];
                } else {
                    $status_content = '<span id="processed_status_wrap_' . $vehicle['vehicle_id'] . '" >' . $vehicle['processed_diagnose_status'] . '</span><br>' . $vehicle['diagnose_status_time'];
                }

            }
            $params_str = '';

            $vehicle_id = $vehicle['vehicle_id'];
            $vin_text = $vehicle['vin'];
            $this->addBodySerialNumber($vehicle);

            $vin = $vehicle['vin'];
            $vehicle['vin'] = "<b>{$vin}</b><br>\n";
            $display_bodysn = 'inline';

            if (empty ($vehicle['body_date']))
                $vehicle['body_date'] = date('d.m.Y');

            if (empty ($vehicle['body_serial'])) {
                $display_bodysn = 'none';
                $vehicle['vin'] .= <<<HEREDOC
<a href="#" class="fetchbodyentry" id="id-newbodysn_$vehicle_id" data-vehicle_id="$vehicle_id" data-vin="$vin" data-date="{$vehicle['body_date']}">Body SN eingeben</a>
HEREDOC;
            };

            $vehicle['vin'] .= <<<HEREDOC
<span id="id-editbodysn_$vehicle_id" style="display: $display_bodysn">
    <span id="id-bodysn_$vehicle_id">{$vehicle['body_serial']}</span> <a href="#" class="fetchbodyentry icon icon-edit" id="id-bodyedit_$vehicle_id" data-vin="$vin" data-serial="{$vehicle['body_serial']}" data-date="{$vehicle['body_date']}" data-vehicle_id="$vehicle_id">ändern</a><br>
    <span style="font-style: italic; color: #666;" id="id-bodydate">({$vehicle['body_date']})</span>
</span>
HEREDOC;

            if (empty($vehicle['c2cbox']))
                $params_str .= 'disabled="disabled"';
            else {
                if ($vehicle['online'] == 't')
                    $vehicle['c2cbox'] = $vehicle['c2cbox'] . '<br> Online seit ' . $vehicle['timestamp'];
                else
                    $vehicle['c2cbox'] = $vehicle['c2cbox'] . '<br> Offline seit ' . $vehicle['timestamp'];
            }

            if (substr($vehicle['processed_diagnose_status'], 0, 6) != 'PASSED' && $vehicle['special_qs_approval'] != 't')
                $params_str .= 'disabled="disabled"';

            if ($vehicle['qmlocked'] == 't') {
                $vehicle['qmlocked'] = 'ja';
                $params_str .= 'disabled="disabled"';
            } else
                $vehicle['qmlocked'] = 'nein';

            $qm_lock_history = $this->ladeLeitWartePtr->newQuery('qm_lock_history')->where('vehicle_id', '=', $vehicle['vehicle_id'])->getVal('count(*)');
            if ($qm_lock_history) $vehicle['qmlocked'] .= '<a href="#" class="show_qmlock_info" data-vehicle_id="' . $vehicle['vehicle_id'] . '" data-vin="' . strip_tags($vehicle['vin']) . '" >Kommentare<span class="genericon genericon-info"></span></a>';

            if ($this->enable_qs_fault_check_finish_status) {
                if ($vehicle['open_fault_cnt'])
                    $params_str .= 'disabled="disabled"';
            }

            $vehicle_finished_status = false;
            if ($vehicle['finished_status'] == 't' || (is_array($vchecked) && in_array($vehicle['vehicle_id'], $vchecked))) {
                $vehicle_finished_status = true;
                $params_str .= 'checked="checked"';
            }

            if ($this->user->user_can('set_finished_status')) {
                if ($vehicle_finished_status) {
                    $vehicle['finished_status'] = 'QS geprüft';
                    $allow_qs_fault_edit = false;
                } else {
                    $vehicle['finished_status'] = '<input type="checkbox" class="setQSCheck" data-vehicleid="' . $vehicle['vehicle_id'] . '" name="finishedstatus_' . $vehicle['vehicle_id'] . '" ' . $params_str . '>';
                }
            } else {
                if ($vehicle_finished_status)
                    $vehicle['finished_status'] = 'QS geprüft';
                else
                    $vehicle['finished_status'] = 'Nein';
            }

            $vehicle['print_details'] = '<form action="" method="GET" target="_blank">
										<input type="hidden" name="vehicle_id" value="' . $vehicle['vehicle_id'] . '">
										<input type="hidden" name="action" value="search">
										<input type="hidden" name="method" value="printpdf">
										<input type="submit" value="Drucken">
										</form>';

            unset($vehicle['diagnose_status']);

            $vehicle['processed_diagnose_status'] = $status_content;

            if ($vehicle['special_qs_approval'] == 't')
                $vehicle['special_qs_approval'] = 'ja';
            else
                $vehicle['special_qs_approval'] = 'nein';

            if ($vehicle['rectified_cnt'] || $vehicle['open_fault_cnt']) {
                $fault_cnt_ctrl = '<br><span id="show_all_faults_wrap_' . $vehicle['vehicle_id'] . '"><a href="#" class="show_all_faults" data-vehicle_id="{vehicle_id}" data-vin="{vin}">' . $vehicle['open_fault_cnt'] . ' offene / ' . $vehicle['rectified_cnt'] . ' behobene Fehler anzeigen</a></span>';
            } else
                $fault_cnt_ctrl = '<br><span id="show_all_faults_wrap_' . $vehicle['vehicle_id'] . '">Keine Fehler eingetragen</span>';

            $vehicle['qs_fault'] = str_replace(array("{vehicle_id}", "{vin}"), array($vehicle['vehicle_id'], $vin_text),
                $this->qs_fault_top_select . $fault_cnt_ctrl);

            if (!in_array($vehicle['depot_id'], $this->productionDepots)) {
                $vehicle['finished_status'] = '';
                $vehicle['qmlocked'] = '';
                $vehicle['qs_fault'] = '';
            }

            if (!$allow_qs_fault_edit) {
                $vehicle['qs_fault'] = '';
            }
        }
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
}