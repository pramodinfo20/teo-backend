<?php
require_once $_SERVER['STS_ROOT'] . '/includes/sts-datetime.php';

class UploadController extends PageController {

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {

//    var_dump($this);
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        $this->content = '';
        $this->vehicles = null;
        $this->displayHeader = $this->container->getDisplayHeader();

        $this->displayHeader->setTitle("StreetScooter Cloud System : QS");

        $this->action = $this->requestPtr->getProperty('action');

        // rows per page
        $this->numrows = 50;
        // total rows
        $this->totalrows = 500;

        $this->cnt_vehicles_in_batch = 30;

        //QS-Freigabe nur nach Fehlerabstellung möglich, true setzen wenn feature aktiviert werden muss
        $this->enable_qs_fault_check_finish_status = false;

        $this->production_depots_only = false;
        $this->setupDepots();
        $this->setupHeaders();
        $this->setupQSUsers();

        $this->common_action = $this->requestPtr->getProperty('common_action');

        $filter_vehicles = $this->getFilteredVehicles();

        $this->setupQSForms();

        $this->qs_fault_search = new CommonFunctions_QsFaultSearch($ladeLeitWartePtr, $this->displayHeader, $user, $requestPtr, $this->common_action, $filter_vehicles);
        $this->qs_fault_search->genSearchForm('search');

        $this->teo_search = new CommonFunctions_TeoSearch($ladeLeitWartePtr, $this->displayHeader, $user, $requestPtr, $this->common_action, $filter_vehicles);
        $this->teo_search->genSearchForm('search');

        $this->setupAjaxAutoCompleteParams();

        if (isset($this->action)) call_user_func(array($this, $this->action));

        $result = array();

        $headings[]["headingone"] = $this->headers;
        $result = array_merge($headings, $result);

        $this->qs_vehicles = new DisplayTable($result, array(
            'id' => 'qs_vehicles_list'
        ));

        $this->displayHeader->enqueueJs("jquery-datepicker-de", "js/jquery.ui.datepicker-de.js");
        $this->displayHeader->enqueueJs("sts-custom-qs", "js/sts-custom-qs.js");

        $this->displayHeader->printContent();

        $this->printContent();
    }

    public function setupQSForms() {
        if (!isset($_SESSION['qs_fault_top'])) {
            $this->qs_fault_top = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')->where('parent_cat', '=', 0)->get('qs_fcat_id=>cat_label');
            $_SESSION['qs_fault_top'] = $this->qs_fault_top;
        } else
            $this->qs_fault_top = $_SESSION['qs_fault_top'];

        $this->qs_fault_top_select = '<select name="qs_fault_cat" data-vehicle_id="{vehicle_id}" data-vin="{vin}" class="qs_fault_cat"><option value="0">---</option>';

        foreach ($this->qs_fault_top as $row_id => $row) {
            $this->qs_fault_top_select .= '<option value="' . $row_id . '">' . $row . '</option>';
        }
        $this->qs_fault_top_select .= '</select>';
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
        $depot_checkbox_str = '';
        foreach ($depots as $depot) {
            $depot_checkbox_str .= '<label for="depot_check_' . $depot . '"><input type="checkbox" name="selected_depots[]" checked=checked id="depot_check_' . $depot . '" class="' . $default_depots . '" value="' . $depot . '">' . $db_depots[$depot] . '</label>';
        }

        if (!empty($depots)) $depots_str = implode(',', $depots);

        $this->depotFilterContent = '<form action="index.php?action=search" method="POST">
        <fieldset class="qs_faults_tab_container">
            <legend class="collapsible"><span class="genericon genericon-expand"></span>Standort Filter</legend>
        <div>';

        if ($default_depots) $produktion_label = 'Produktion/Nacharbeit Standorte ausgewählt :';
        else $produktion_label = 'Ausgewählte Standorte :';
        $this->depotFilterContent .= '<div id="selected_depot_wrap">' . $produktion_label . $depot_checkbox_str . '
                                    </div></div><br>
                                    <div class="collapsible_content" style="display: none">
                                   Andere Standort aussuchen :
                                   <input type="text" name="depots_search" id="depots_search" value="" placeholder="Ort eingeben" style="width: 200px">
                                   <input type="hidden" name="depot_vals" id="depot_vals" value="' . $depots_str . '"><br><br>
                                   <input type="submit" value="Suchen"> <a href="' . $_SERVER['PHP_SELF'] . '">Nur Produktionsorte auswählen</a>
                                    </div>
                                    </fieldset>
                                   </form>
                                   <p><b>Bitte beachten Sie:</b><br>
                                    Für Fahrzeuge ohne <b>TEO Status</b> den kompletten vin eingeben<b><br>
                                    </p>';

        $this->displayHeader->enqueueLocalJs('var depot_list=' . json_encode($json_depots));
    }

    public function setupQSUsers() {
        $this->qs_users = array(
            1 => array('qs_user_id' => 1, 'qs_user' => 'Patrick.Jungnitsch', 'qs_pass' => 'Ga7Gti'),
            6 => array('qs_user_id' => 6, 'qs_user' => 'Sven.Nellesen', 'qs_pass' => 'Ahmai6'),
            84 => array('qs_user_id' => 84, 'qs_user' => 'Philipp.Schnelle', 'qs_pass' => 'ewo8Ra'),
            1094 => array('qs_user_id' => 1094, 'qs_user' => 'lisanne.qs', 'qs_pass' => '7Gai8e')
        );
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
        $this->available_status = array_column($status, 'processed_diagnose_status');
    }

    /**
     * @todo Lothar : Change data source for autocomplete from the qs_fault_list table to MBOM table
     */
    public function setupAjaxAutoCompleteParams() {
        //$this->autoCompleteDataSources=['part_number'=>'ajaxGetMbomPart'];
        $this->autoCompleteDataSources = ['error_desc' => 'ajaxFaultAutoComplete',
            'part_number' => 'ajaxFaultAutoComplete',
            'part_desc' => 'ajaxFaultAutoComplete'];
    }

    public function ajaxFaultAutoComplete() {
        $term = $this->requestPtr->getProperty('term');
        $terms = $this->ladeLeitWartePtr->newQuery('qs_fault_list')->where('field_value', 'ILIKE', '%' . $term . '%')->get('distinct(field_value) as value');
        echo json_encode($terms);
        exit(0);
    }

    public function ajaxGetMbomPart() {
        $vehicle_variant = $this->requestPtr->getProperty('vehicle_variant');
        $term = $this->requestPtr->getProperty('term');
        /*beispiel query
         * wichtig ist das die key in $parts als 'value' heißen.
         * z.B
         * distinct(part_number) as value
         * $parts=[['value'=>'B14XD17_01'],['value'=>'B14XD17_02'],['value'=>'B14XD17_03']]*/
        $parts = $this->ladeLeitWartePtr->newQuery('>>mbom_parts_tabelle<<')->where('vehicle_variant', '=', $vehicle_variant)->get('distinct(part_number) as value');
        echo json_encode($parts);
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

    public function ajaxCommon() {
        echo $this->qs_fault_search->getJsonAjaxContent();
        exit(0);
    }

    public function ajaxDeleteFault() {
        $qs_fcat_id = filter_var($_POST['qs_fcat_id'], FILTER_SANITIZE_NUMBER_INT);
        $fault_sno = filter_var($_POST['fault_sno'], FILTER_SANITIZE_NUMBER_INT);
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $timestamp_insert = date('Y-m-d H:i:sO');
        $timestamp_display = date('d.m.Y H:i');
        $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list')
            ->where('vehicle_id', '=', $vehicle_id)
            ->where('fault_sno', '=', $fault_sno)
            ->where('qs_fcat_id', '=', $qs_fcat_id)
            ->delete();
        $insertCol = ['qs_fcat_id' => $qs_fcat_id,
            'fault_sno' => $fault_sno,
            'vehicle_id' => $vehicle_id,
            'delete_ts' => $timestamp_insert,
            'delete_by' => $this->user->getUserId()];
        $result = $this->ladeLeitWartePtr->newQuery('qs_fault_delete_log')->insert($insertCol);
        echo 'Gelöscht am ' . $timestamp_display;
        exit(0);
    }

    public function genFormElements($child_qs_cat_id, $fields, $cnt, $existing_data, $vehicle_id) {
        $return_text = '';

        if (!empty($existing_data)) {
            foreach ($existing_data as $erow) {
                $disabled = '';
                if (!empty($erow['status']) && $erow['status'] == 'rectified') {
                    $status = $this->ladeLeitWartePtr->newQuery('qs_fault_status')->where('vehicle_id', '=', $vehicle_id)->where('fault_sno', '=', $erow['fault_sno'])->where('qs_fcat_id', '=', $child_qs_cat_id)->getOne('*');
                    $updated_date = '';
                    if (!empty($status['update_ts'])) {
                        $timestamp_st = strtotime($status['update_ts']);
                        $updated_date = date('d.m.Y H:i', $timestamp_st);
                    }
                    $status_ctrl = 'Behoben am ' . $updated_date;
                    $delete_ctrl = '';
                    $disabled = 'disabled';
                } else {
                    if ($this->user->getUserId() == $erow['addedby'])
                        $delete_ctrl = '<a href="#" class="delete_fault" data-vehicle_id="' . $vehicle_id . '" data-qs_fcat_id="' . $child_qs_cat_id . '" data-fault_sno="' . $erow['fault_sno'] . '"><span class="genericon genericon-close"></span>Löschen</a>';
                    if ($this->user->user_can('set_qs_faults_rectified'))
                        $status_ctrl = '<a href="#" class="set_qs_fault_status" data-vehicle_id="' . $vehicle_id . '" data-qs_fcat_id="' . $child_qs_cat_id . '" data-fault_sno="' . $erow['fault_sno'] . '"><span class="genericon genericon-checkmark"></span>Status als behoben setzen</a>';
                    else $status_ctrl = '';
                }
                $return_text .= '<tr>';
                $fault_sno = $erow['fault_sno'];
                $existing_fields = json_decode($erow['data'], true);

                foreach ($fields as $row) {
                    if ($row['field_type'] == 'select') {
                        $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                            ->where('qs_fcat_id', '=', $child_qs_cat_id)
                            ->where('parent_field_key', '=', $row['field_key'])
                            ->where('field_type', '=', 'option')
                            ->orderBy('show_order')
                            ->get('*');

                        $return_text .= '<td><select ' . $disabled . ' name="qs_faults[' . $child_qs_cat_id . '][' . $fault_sno . '][' . $row['field_key'] . ']"  data-qs_cat_id="' . $child_qs_cat_id . '" data-fault_sno="' . $fault_sno . '" class="{has_misc}"><option value="0" >--</option>';
                        $hasMisc = '';
                        $display_val = 'none';
                        foreach ($result as $optiontext) {
                            if (stripos($optiontext['field_label'], 'sonstiges') !== false) $hasMisc = 'has_misc';
                            $selected = '';
                            if ($existing_fields[$row['field_key']] == $optiontext['field_key'])
                                $selected = ' selected="selected" ';
                            if ($existing_fields[$row['field_key']] == $optiontext['field_key'] && $optiontext['field_key'] == 'sonstiges')
                                $display_val = 'block';
                            $return_text .= '<option value="' . $optiontext['field_key'] . '" ' . $selected . '>' . $optiontext['field_label'] . '</option>';
                        }
                        $return_text .= '</select>';
                        if (!empty($hasMisc)) {
                            $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                                ->where('qs_fcat_id', '=', $child_qs_cat_id)
                                ->where('parent_field_key', '=', $row['field_key'])
                                ->where('field_type', '=', 'text')
                                ->getOne('*');
                            $return_text .= '<input ' . $disabled . ' name="qs_faults[' . $child_qs_cat_id . '][' . $fault_sno . '][' . $result['field_key'] . ']" id="has_misc_' . $child_qs_cat_id . '_' . $fault_sno . '" type="' . $row['field_type'] . '" style="display:' . $display_val . '" value="' . $existing_fields[$result['field_key']] . '">';
                        }
                        $return_text .= '</td>';
                        $return_text = str_replace('{has_misc}', $hasMisc, $return_text);
                    } else
                        $return_text .= '<td><input ' . $disabled . ' name="qs_faults[' . $child_qs_cat_id . '][' . $fault_sno . '][' . $row['field_key'] . ']" type="text" value="' . $existing_fields[$row['field_key']] . '"></td>';
                }
                $return_text .= '<td>' . $status_ctrl . '</td><td>' . $delete_ctrl . '</td></tr>';
            }
        }
        //start gen form for new rows
        if (!empty($fields)) {
            $return_text .= '<tr>';
            foreach ($fields as $row) {
                if ($row['field_type'] == 'select') {
                    $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                        ->where('qs_fcat_id', '=', $child_qs_cat_id)
                        ->where('parent_field_key', '=', $row['field_key'])
                        ->where('field_type', '=', 'option')
                        ->orderBy('show_order')
                        ->get('*');

                    $return_text .= '<td><select name="qs_faults[' . $child_qs_cat_id . '][' . $cnt . '][' . $row['field_key'] . ']" data-qs_cat_id="' . $child_qs_cat_id . '" data-fault_sno="' . $cnt . '" class="{has_misc}"><option value="0" >--</option>';
                    $hasMisc = '';
                    foreach ($result as $optiontext) {
                        if (stripos($optiontext['field_label'], 'sonstiges') !== false) $hasMisc = 'has_misc';
                        $return_text .= '<option value="' . $optiontext['field_key'] . '">' . $optiontext['field_label'] . '</option>';
                    }
                    $return_text .= '</select>';
                    if (!empty($hasMisc)) {
                        $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                            ->where('qs_fcat_id', '=', $child_qs_cat_id)
                            ->where('parent_field_key', '=', $row['field_key'])
                            ->where('field_type', '=', 'text')
                            ->getOne('*');
                        $return_text .= '<input name="qs_faults[' . $child_qs_cat_id . '][' . $cnt . '][' . $result['field_key'] . ']" id="has_misc_' . $child_qs_cat_id . '_' . $cnt . '" type="text" style="display:none" value="">';
                    }
                    $return_text .= '</td>';
                    $return_text = str_replace('{has_misc}', $hasMisc, $return_text);
                } else {
                    //check if this field_key is present in the autocompletedatasources,
                    //if yes set class to hasAutoComplete and set the target action to be invoked
                    if (array_key_exists($row['field_key'], $this->autoCompleteDataSources)) {
                        $autoClass = 'hasAutoComp';
                        $ajaxaction = $this->autoCompleteDataSources[$row['field_key']];
                        $vehicle_variant = $this->ladeLeitWartePtr->vehiclesPtr->newQuery('')->where('vehicle_id', '=', $vehicle_id)->getVal('vehicle_variant');
                    } else {
                        $autoClass = $ajaxaction = $vehicle_variant = '';
                    }
                    $return_text .= '<td><input name="qs_faults[' . $child_qs_cat_id . '][' . $cnt . '][' . $row['field_key'] . ']" type="' . $row['field_type'] . '"
                                value=""
                                data-targetaction="' . $ajaxaction . '"
                                class="' . $autoClass . '"
                                data-vehicle_variant="' . $vehicle_variant . '"></td>';
                }

            }
            $return_text .= '<td></td></tr>';
        }

        return $return_text;
    }

    function ajaxUpdateCount() {
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $updated_result = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->join('qs_fault_count', 'vehicles.vehicle_id=qs_fault_count.vehicle_id', 'LEFT JOIN')
            ->where('vehicle_id', '=', $vehicle_id)
            ->getOne('vin,open_fault_cnt,rectified_cnt');

        echo '<a href="#" class="show_all_faults" data-vehicle_id="' . $vehicle_id . '" data-vin="' . $updated_result['vin'] . '">' . $updated_result['open_fault_cnt'] . ' offene / ' . $updated_result['rectified_cnt'] . ' behobene Fehler anzeigen</a>';
        exit(0);
    }

    function ajaxSaveFaultStatus() {
        $qs_fcat_id = filter_var($_POST['qs_fcat_id'], FILTER_SANITIZE_NUMBER_INT);
        $fault_sno = filter_var($_POST['fault_sno'], FILTER_SANITIZE_NUMBER_INT);
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $timestamp_insert = date('Y-m-d H:i:sO');
        $timestamp_display = date('d.m.Y H:i');
        $insertCol = ['qs_fcat_id' => $qs_fcat_id,
            'fault_sno' => $fault_sno,
            'vehicle_id' => $vehicle_id,
            'update_ts' => $timestamp_insert,
            'addedby' => $this->user->getUserId(),
            'status' => 'rectified'];
        $result = $this->ladeLeitWartePtr->newQuery('qs_fault_status')->insert($insertCol);
        if ($result) echo 'Behoben am ' . $timestamp_display;
        exit(0);
    }

    public function ajaxSaveQsFaults() {
        $qs_faults = filter_var_array($_POST['qs_faults'], FILTER_SANITIZE_STRING);
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $cnt = 0;
        foreach ($qs_faults as $qs_fcat_id => $qs_fault) {
            foreach ($qs_fault as $qs_fault_sno => $qs_fault_input) {
                $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list')
                    ->groupBy('fault_sno')
                    ->where('qs_fcat_id', '=', $qs_fcat_id)
                    ->where('vehicle_id', '=', $vehicle_id)
                    ->where('fault_sno', '=', $qs_fault_sno)
                    ->getOne('fault_sno,json_object_agg(field_key,field_value) as data');

                if (!empty($result)) {
                    $existing_data = json_decode($result['data'], true);
                    $diff = array_diff_assoc($qs_fault_input, $existing_data);
                    if (!empty($diff)) {
                        foreach ($diff as $key => $value) {
                            $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list')
                                ->where('qs_fcat_id', '=', $qs_fcat_id)
                                ->where('vehicle_id', '=', $vehicle_id)
                                ->where('fault_sno', '=', $qs_fault_sno)
                                ->where('field_key', '=', $key)
                                ->update(array('field_value', 'update_ts', 'addedby'), array($qs_fault_input[$key], date('Y-m-d H:i:sO'), $this->user->getUserId()));
                        }

                    }
                } else {   //without a defined callback, array_filter simply removes all the empty values
                    if (!empty(array_filter($qs_fault_input))) {
                        $standard_values = array('qs_fcat_id' => $qs_fcat_id, 'vehicle_id' => $vehicle_id, 'fault_sno' => $qs_fault_sno, 'update_ts' => date('Y-m-d H:i:sO'), 'addedby' => $this->user->getUserId());
                        foreach ($qs_fault_input as $key => $each_input) {
                            $insertval = $standard_values + array('field_key' => $key, 'field_value' => $each_input);
                            $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list')
                                ->insert($insertval);

                        }
                    }

                }
            }
        }
        exit(0);
    }

    public function ajaxGetAllFaults() {
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $result = $this->ladeLeitWartePtr->newQuery('qs_fault_list')
            ->groupBy('qs_fault_categories.qs_fcat_id,qs_fault_categories.cat_label,parent_cat,qs_fault_list.fault_sno,status')
            ->join('qs_fault_categories', 'qs_fault_categories.qs_fcat_id=qs_fault_list.qs_fcat_id', 'INNER JOIN')
            ->join('qs_fault_cat_inputs', 'qs_fault_cat_inputs.qs_fcat_id=qs_fault_list.qs_fcat_id AND qs_fault_cat_inputs.field_key=qs_fault_list.field_key', 'FULL OUTER JOIN')
            ->join('qs_fault_status', 'qs_fault_status.qs_fcat_id=qs_fault_list.qs_fcat_id AND qs_fault_status.fault_sno=qs_fault_list.fault_sno AND qs_fault_status.vehicle_id=qs_fault_list.vehicle_id', 'FULL OUTER JOIN')
            ->where('vehicle_id', '=', $vehicle_id)
            ->orderBy('qs_fault_categories.qs_fcat_id,qs_fault_list.fault_sno')
            ->get('qs_fault_list.fault_sno,qs_fault_categories.qs_fcat_id,qs_fault_categories.cat_label,parent_cat,
        json_object_agg(qs_fault_list.field_key,qs_fault_list.field_value) as dataval,
        json_object_agg(qs_fault_list.field_key,qs_fault_cat_inputs.field_type) as datatype,status');

        $parent_cat = [];

        $headings[]["headingone"] = ['Kategorie', 'Sts Teilenummer', 'Bauteilbeschreibung', 'Fehlerbeschreibung', 'Lösungsvorschlag', 'Fehler Status'];
        $processed_rows = [];

        foreach ($result as $row) {
            if (!empty($row['status']) && $row['status'] == 'rectified') {
                $status = $this->ladeLeitWartePtr->newQuery('qs_fault_status')->where('vehicle_id', '=', $vehicle_id)->where('fault_sno', '=', $row['fault_sno'])->where('qs_fcat_id', '=', $row['qs_fcat_id'])->getOne('*');
                $updated_date = '';
                if (!empty($status['update_ts'])) {
                    $timestamp_st = strtotime($status['update_ts']);
                    $updated_date = date('d.m.Y H:i', $timestamp_st);
                }
                $status_ctrl = 'Behoben am ' . $updated_date;
            } else {
                $status_ctrl = '';
            }
            $cat_label = $row['cat_label'];
            if (!empty($row['parent_cat'])) {
                $parent_cat_label = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')->where('qs_fcat_id', '=', $row['parent_cat'])->getVal('cat_label');
                $cat_label = $parent_cat_label . ' > ' . $cat_label;
            }
            $fault_detail = json_decode($row['dataval'], true);
            $fault_fieldtype = json_decode($row['datatype'], true);
            if (in_array('select', $fault_fieldtype)) {
                $select_field_key = array_search('select', $fault_fieldtype);
                $input_details = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')->where('field_key', '=', $fault_detail[$select_field_key])->getVal('field_label');
                if ($fault_detail[$select_field_key] == 'sonstiges') {
                    $sonstiges_key = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                        ->where('qs_fcat_id', '=', $row['qs_fcat_id'])
                        ->where('parent_field_key', '=', $select_field_key)
                        ->where('field_type', '=', 'text')
                        ->getOne('*');
                    $input_details = $input_details . '-' . $fault_detail[$sonstiges_key['field_key']]; //@todo review this §select_field_key _text
                }
                $processed_rows[] = array($cat_label, $input_details, '', $fault_detail['error_desc'], $fault_detail['solution'], $status_ctrl);
            } else
                $processed_rows[] = array($cat_label, $fault_detail['part_number'], $fault_detail['part_desc'], $fault_detail['error_desc'], $fault_detail['solution'], $status_ctrl);
        }
        $result = array_merge($headings, $processed_rows);
        $output_table = new DisplayTable($result);
        echo $output_table->getContent();
        exit(0);

    }

    public function ajaxGetQSFaultForm() {
        $qs_fcat_id = filter_var($_POST['qs_fcat_id'], FILTER_SANITIZE_NUMBER_INT);
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $qs_fcat_label = filter_var($_POST['qs_fcat_label'], FILTER_SANITIZE_STRING);
        $maxfields = 0;
        $child_categories = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')->where('parent_cat', '=', $qs_fcat_id)->get('qs_fcat_id=>cat_label');

        $qs_fault_categories = array($qs_fcat_id => $qs_fcat_label);

        if (!empty($child_categories)) $qs_fault_categories += $child_categories;

        $form_elements_html = '<form action="index.php?action=search" id="qs_faults_list" method="POST"><table class="qs_faults_list_wrap">';

        foreach ($qs_fault_categories as $child_qs_cat_id => $child_qs_cat_label) {
            $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                ->where('qs_fcat_id', '=', $child_qs_cat_id)
                ->where('parent_field_key', 'IS', 'NULL')
                ->orderBy('show_order')
                ->get('*');

            if ($child_qs_cat_id == $qs_fcat_id) $wrap_tag = '<h2>' . $child_qs_cat_label . '</h2>';
            else $wrap_tag = '<strong>' . $child_qs_cat_label . '</strong>';
            $form_elements_html .= '<tr><td colspan={colSpanVal}>' . $wrap_tag . '</td></tr>';

            if (!empty($result)) {
                $form_elements_html .= '<tr>';


                foreach ($result as $row) {
                    $form_elements_html .= '<td>' . $row['field_label'] . '</td>';
                }
                $maxfields = sizeof($result);
                $form_elements_html .= "<td></td><td></td></tr>";
            }


            $existing_data = $this->ladeLeitWartePtr->newQuery('qs_fault_list')
                ->groupBy('qs_fault_list.fault_sno,status,qs_fault_list.addedby')
                ->where('qs_fcat_id', '=', $child_qs_cat_id)
                ->where('vehicle_id', '=', $vehicle_id)
                ->orderBy('qs_fault_list.fault_sno')
                //->where('status','!=','rectified') do not add this condition, this removes the ability to count the fault_sno correctly
                ->join('qs_fault_status', 'qs_fault_status.qs_fcat_id=qs_fault_list.qs_fcat_id AND qs_fault_status.fault_sno=qs_fault_list.fault_sno AND qs_fault_status.vehicle_id=qs_fault_list.vehicle_id', 'FULL OUTER JOIN')
                ->get('qs_fault_list.fault_sno,qs_fault_list.addedby,qs_fault_status.status,json_object_agg(field_key,field_value) as data');
            $fault_snos = array_column($existing_data, 'fault_sno');
            if ($existing_data) $cnt = max($fault_snos) + 1;
            else $cnt = 1;

            $form_elements_html .= $this->genFormElements($child_qs_cat_id, $result, $cnt, $existing_data, $vehicle_id);
        }

        $form_elements_html .= '
                                <tr><td colspan="{colSpanVal}">
                                <br>
                                <input type="hidden" name="vehicle_id" value="' . $vehicle_id . '">
                                <input type="hidden" name="action" value="ajaxSaveQsFaults">
                                <input type="submit" name="submitsave" data-vehicle_id="' . $vehicle_id . '" class="save_qs_faults" value="Speichern">
                                </td></tr></table></form>';
        $maxfields += 2; //one for the Löschen button and one for the Status als behoben button
        $form_elements_html = str_replace('{colSpanVal}', $maxfields, $form_elements_html);
        echo $form_elements_html;

        exit(0);
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
										<input type="hidden" name="action" value="printpdf">
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

    function ajaxFetchLockInfo() {
        $vehicle_id = $this->requestPtr->getProperty('vehicle_id');
        $info = $this->ladeLeitWartePtr->newQuery('qm_lock_history')->where('vehicle_id', '=', $vehicle_id)->get('*');
        $processedrows = [];
        foreach ($info as $row) {
            $row['userid'] = $this->ladeLeitWartePtr->newQuery('users')->where('id', '=', $row['userid'])->getVal('username');
            $row['change_ts'] = date('Y-m-d H:i', strtotime($row['update_ts']));
            if ($row['old_status'] == 'f' && $row['new_status'] == 't') $row['status'] = 'Gesperrt';
            else if ($row['old_status'] == 't' && $row['new_status'] == 'f') $row['status'] = 'Entsperrt';
            $processedrows[] = [$row['change_ts'], $row['status'], $row['userid'], $row['qmcomment']];
        }
        $headings = [['headingone' => ['Datum/Uhrzeit', 'Änderung', 'Benutzer', 'Kommentare']]];
        $htmltable = new DisplayTable(array_merge($headings, $processedrows), ['widths' => ['100px', '160px', '100px', '180px']]);
        echo $htmltable->getContent();
        exit(0);
    }

    function ajaxSaveManualSW() {
        $diagid = filter_var($_POST['diagid'], FILTER_SANITIZE_NUMBER_INT);
        if (empty($diagid)) $diagid = NULL;
        $swver = filter_var($_POST['swver'], FILTER_SANITIZE_STRING);
        $oldsw = filter_var($_POST['oldsw'], FILTER_SANITIZE_STRING);
        $vehicleid = filter_var($_POST['vehicleid'], FILTER_SANITIZE_NUMBER_INT);
        $ecu = filter_var($_POST['ecu'], FILTER_SANITIZE_STRING);
        $vehicle = $this->getDataSingleVehicle($vehicleid);
        $status = $vehicle['processed_diagnose_status'];
        $userid = $this->user->getUserId();
        $msg = $result = '';
        $result = $this->ladeLeitWartePtr->vehiclesPtr->saveManualSW($swver, $vehicle, $ecu, $userid, $msg, $diagid, $oldsw);
        if ($result) {
            $vehicle = $this->getDataSingleVehicle($vehicleid);
            $status = $vehicle['processed_diagnose_status'];
        }
        echo json_encode(['msg' => $msg, 'status' => $status]);
        exit(0);
    }

    function ajaxFetchSWEntry() {
        $vehicle['vin'] = $_POST['vin'];
        $vehicle['vehicle_id'] = $_POST['vehicle_id'];
        $return_str = $this->ladeLeitWartePtr->vehiclesPtr->fetchSwForm($vehicle, $this->user);
        echo $return_str;
        exit(0);
    }

    function ajaxGetFormBodySN() {
        $vehicle['vin'] = $_POST['vin'];
        $return_str = $this->ladeLeitWartePtr->vehiclesPtr->getFormBodySN($vehicle, $this->user);
        echo $return_str;
        exit(0);
    }

    function ajaxSaveManualBodySN() {

        $vin = $_REQUEST['vin'];
        $dbDate = to_iso8601_date($_REQUEST['date']);
        $serial = $_REQUEST['bodysn'];
        $prev = $_REQUEST['prev'];
        $result = ['error' => '', 'action' => '', 'timestamp' => $_REQUEST['date'], 'serial_number' => $serial];

        if (empty ($prev)) {
            $result['action'] = 'insert';
            $insert = ['vin' => $vin, 'timestamp' => $dbDate, 'part_number' => 'D17X06A00200_01', 'serial_number' => $serial];
            if (!$this->diagnosePtr->newQuery('serial_numbers')->insert($insert))
                $result['error'] = "Kann Datensatz nicht einfügen";
        } else {
            $result['action'] = 'update';
            if (!$this->diagnosePtr->newQuery('serial_numbers')
                ->where('vin', '=', $vin)
                ->where('part_number', '=', 'D17X06A00200_01')
                ->update(['serial_number', 'timestamp'], [$serial, $dbDate]))
                $result['error'] = "Kann Datensatz nicht aktualisieren";

        }
        echo json_encode($result);
        exit(0);
    }

    function ajaxFetchErrorDetails() {
        $vehicle['vin'] = $_POST['vin'];
        $vehicle['vehicle_id'] = $_POST['vehicle_id'];
        $vehicle['diagnostic_session_id'] = $_POST['diagnostic_session_id'];
        $vehicle['vehicle_variant'] = $_POST['vehicle_variant'];
        $vehicle['tabledata'] = '';


        $this->addBodySerialNumber($vehicle);
        $this->ladeLeitWartePtr->vehiclesPtr->fetchTeoError($vehicle, $this->user);

        foreach ($vehicle['tables'] as $dtable) {
            if (!empty($dtable)) {
                if (isset($dtable['footer']) && !empty($dtable['footer'])) $footer = $dtable['footer'];
                else $footer = '';
                if (isset($dtable['tableid']) && !empty($dtable['tableid'])) $tableid = $dtable['tableid'];
                else $tableid = '';
                if (isset($dtable['tableclass']) && !empty($dtable['tableclass'])) $tableclass = $dtable['tableclass'];
                else $tableclass = '';
                $htmltable = new DisplayTable(array_merge($dtable['headings'], $dtable['content']), array('widths' => $dtable['colWidths'], 'class' => $tableclass));
                $vehicle['tabledata'] .= $dtable['header'] . $htmltable->getContent() . $footer;
            }
        }
        error_log($vehicle['tabledata'], 3, "/var/tmp/mylog");
        echo <<<HEREDOC
<p><span class="LabelX W100"><b>Sn. Body:</b></span><span class="LabelX W200">{$vehicle['body_serial']}</span><span class="LabelX W150"><b>Produktionsbeginn:</b></span><span class="LabelX W200">{$vehicle['body_date']}</span></p>
{$vehicle['tabledata']}
HEREDOC;
        exit(0);
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

    public function printpdf() {
        $vehicle_id = filter_var($_REQUEST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $vehicle = $this->getDataSingleVehicle($vehicle_id);
        $this->addBodySerialNumber($vehicle);

        $this->ladeLeitWartePtr->vehiclesPtr->diagnoseDetails($vehicle);
        $this->ladeLeitWartePtr->vehiclesPtr->fetchTeoError($vehicle, $this->user, true);
        $eol_protokoll = new QSEOLProtokollPDF($vehicle);
    }

    public function ajaxRows() {
        // $_SESSION['timeperf'].=microtime(true).' start ajaxRows<br>';
        $page = $this->requestPtr->getProperty('page');
        $size = $this->requestPtr->getProperty('size');
        $fcol = $this->requestPtr->getProperty('filter');
        $scol = $this->requestPtr->getProperty('column'); // 1 desc 0 asc
        $filtered_vehicles = $this->requestPtr->getProperty('filtered_vehicles'); // 1 desc 0 asc
        $vchecked = $this->requestPtr->getProperty('vchecked'); // 1 desc 0 asc

        $depots = $this->requestPtr->getProperty('depot_vals');
        $depots = trim(filter_var($depots, FILTER_SANITIZE_STRING));
        if (!empty($depots)) {

            $depots = explode(',', $depots);
            if (!array_diff($depots, $this->productionDepots))
                $productionDepotsOnly = true;
            else
                $productionDepotsOnly = false;
        }

        $result['headers'] = $this->header_cols;
        if (!empty($filtered_vehicles) && $filtered_vehicles != 'error')
            $filtered_vehicles = explode(',', $filtered_vehicles);
        elseif (!empty($filtered_vehicles) && $filtered_vehicles == 'error') {
            echo json_encode($result); // just output empty rows to satisfy tablesorter and exit from here
            exit(0);
        } else
            $filtered_vehicles = null;

        $rows = $this->ladeLeitWartePtr->vehiclesPtr->getEOLVehiclesNew($depots, 'saveQS', $page, $size, $fcol, $scol, $filtered_vehicles, null, $productionDepotsOnly);

        if (!empty($vchecked))
            $vchecked = explode(',', $vchecked);
        else
            $vchecked = null;

        $this->processVehicles($rows, $vchecked);

        $result['total_rows'] = $this->totalrows;
        $result['fcol'] = json_encode($fcol);
        $result['page'] = $page;
        $result['size'] = $size;
        $result['rows'] = $rows;
        // $_SESSION['timeperf'].=microtime(true).' end ajaxRows<br>';
        echo json_encode($result);
        exit(0);
    }

    /**
     * *
     * If qs_user and qs_pass matches against the details in $this->qs_users returns true else false
     *
     * @param string $qs_user
     * @param string $qs_pass
     * @return boolean
     */
    public function authenticateQs($qs_user, $qs_pass) {
        if ($this->qs_users[$qs_user]['qs_pass'] == $qs_pass)
            return true;

        return false;
    }

    /***
     * saveQS
     * authenticates qs user against the $this->qs_users details and sets a msg string
     */
    public function saveQS() {
        $edited_vehicles = $this->requestPtr->getProperty('to_set_vehicles');
        if (!empty($edited_vehicles)) $edited_vehicles = explode(',', $edited_vehicles);
        foreach ($edited_vehicles as $vehicle_id) {
            {
                // need to check since setQSFertig also updates the production quantity in the production_plan table
                // and we dont want it to be saved over again and production_quantity increased again!

                if ($this->ladeLeitWartePtr->vehiclesPtr->getQSFertig($vehicle_id) != 't') {
                    $msg = $this->ladeLeitWartePtr->vehiclesPtr->setQSFertig($vehicle_id, 'TRUE', false, $this->user->getUserId()); // @todo WHY pass always FALSE? Should check to see if this is a pool vehicle or not!
                    $this->msgs[] = $msg . ' als QS Fertig gesetzt.<br>';
                }
            }
        }
    }

    /**
     * *
     * Function to get vehicle vin to allow searching with autocomplete
     */
    function ajaxVehicleVinSearch() {
        $term = $this->requestPtr->getProperty('term');
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->multipleAndWhere('vin', 'ILIKE', '%' . $term . '%')
            ->get("vin as label,vin as value");
        echo json_encode($vehicles);
        exit(0);
    }

    /**
     * function fetches vins from the latest_teo_status tabe based on the TEO date or VIN
     * @return array of vins
     */
    public function getVehicleVins() {
        if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
            $start_date = filter_var($_POST['start_date'], FILTER_SANITIZE_STRING);

            $result = $this->ladeLeitWartePtr->newQuery('latest_teo_status')
                ->where('diagnose_status_time', '>=', $start_date);
            if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
                $end_date = filter_var($_POST['end_date'], FILTER_SANITIZE_STRING);
                $result = $result->where('diagnose_status_time', '<=', $end_date);
            }

            $vins = $result->orderBy('diagnose_status_time', 'ASC')->get('teo_vin');
        } else if (isset($_POST['start_vin'])) {
            $start_vin = filter_var($_POST['start_vin'], FILTER_SANITIZE_STRING);
            $result = $this->ladeLeitWartePtr->newQuery('latest_teo_status')
                ->where('teo_vin', '>=', $start_vin);
            if (isset($_POST['end_vin']) && !empty($_POST['end_vin'])) {
                $end_vin = filter_var($_POST['end_vin'], FILTER_SANITIZE_STRING);
                $result = $result->where('teo_vin', '<=', $end_vin);
            }

            $vins = $result->orderBy('teo_vin', 'ASC')->get('teo_vin');
        }
        $vins = array_column($vins, 'teo_vin');
        return $vins;
    }

    public function writeDataToFile($export_data, $filename, $file_format) {
        $helper = new SpreadsheetHelper();
        if (file_exists("/tmp/" . $filename . '.' . $file_format))
//             this is not the first call to the function, so append the processed data to the existing file
            $helper->appendExport($export_data, $filename, $file_format);
        else
            $helper->generateExport($export_data, $filename, $file_format);


    }

    /**
     * ajax call to this function exports data into an export file
     * functions works in batches of size decided by $this->cnt_vehicles_in_batch
     * VINs are selected from search paramters and stored in the session
     * Session variable also contains the offset which is incremented with each call of the function
     *
     */
    public function exportQsTeo() {
        if (isset($_POST['export_token'])) {
            $export_token_id = filter_var($_POST['export_token'], FILTER_SANITIZE_STRING);
            $file_url = '';

            //if this is not the first call for this session, then session should already exist
            if (isset($_SESSION['export_token_' . $export_token_id])) {
                $session_data = $_SESSION['export_token_' . $export_token_id];
                //the file url returned when the export is complete
                $file_url = '<a href="downloadxport.php?filename=teo_export_' . $export_token_id . '&format=' . $session_data['file_format'] . '" class="reset_export_session" target="_blank">Datei herunterladen</a>';
                //use the offset to pass only a section of the vins to the getEOLVehiclesByVin function
                $sliced_vins = array_slice($session_data['filter_vins'], $session_data['offset'] * $this->cnt_vehicles_in_batch, $this->cnt_vehicles_in_batch);

                $header_set = false;
                if (isset($session_data['header_set']) && $session_data['header_set'] == 'true') $header_set = true;

                if (!empty($sliced_vins)) {
                    $export_data = $this->ladeLeitWartePtr->vehiclesPtr->getEOLVehiclesByVin($sliced_vins, $session_data['select_cols'], $session_data['filters'], $this->productionDepots, $this->available_status, $header_set, $this->header_labels_for_export);
                }

                if (!$header_set)
                    $_SESSION['export_token_' . $export_token_id]['header_set'] = 'false';

                if (!empty($export_data)) {
                    $this->writeDataToFile($export_data, 'teo_export_' . $export_token_id, $session_data['file_format']);
                    //increment offset for next function call
                    $session_data['offset']++;
                    //progress need to be accurate. Value used only for updating the progress bar
                    $progress = ($session_data['offset']) * $this->cnt_vehicles_in_batch / sizeof($session_data['filter_vins']);

                    $_SESSION['export_token_' . $export_token_id]['offset'] = $session_data['offset'];
                    //as long as progress is less than one, we need to pass the key 'progress' with value so jQuery calls this function again
                    if ($progress < 1) {
                        echo json_encode(['progress' => $progress * 100]);
                        exit(0);
                    }
                }
            } //first call for this export session, SESSION variable has to be set here
            else {
                $session_data['offset'] = 1;

                $select_cols = filter_var_array($_POST['exportcols'], FILTER_SANITIZE_STRING);
                $select_cols = array_fill_keys($select_cols, 1);
                $session_data['select_cols'] = $select_cols;

                $file_format = filter_var($_POST['file_format'], FILTER_SANITIZE_STRING);
                $session_data['file_format'] = $file_format;

                $filters = filter_var_array($_POST['export_filter'], FILTER_SANITIZE_STRING);
                $session_data['filters'] = $filters;

                //fetch the vins based on search parameters which are passed as POST variables
                $vins = $this->getVehicleVins();
                $session_data['filter_vins'] = $vins;

                $_SESSION['export_token_' . $export_token_id] = $session_data;

                //calculate progress
                if (!empty($vins)) {
                    //if count of VINs are less than what is fixed per batch, then this function is called only once, set progress to 1
                    if (sizeof($vins) > $this->cnt_vehicles_in_batch)
                        $progress = $this->cnt_vehicles_in_batch / sizeof($vins);
                    else $progress = 1;
                } else $progress = 0;

                $header_set = true;
                $export_data = $this->ladeLeitWartePtr->vehiclesPtr->getEOLVehiclesByVin(array_slice($vins, 0, $this->cnt_vehicles_in_batch), $select_cols, $filters, $this->productionDepots, $this->available_status, $header_set, $this->header_labels_for_export);

                if ($header_set) $_SESSION['export_token_' . $export_token_id]['header_set'] = 'true';

                if (!empty($export_data)) {
                    $this->writeDataToFile($export_data, 'teo_export_' . $export_token_id, $file_format);
                }

                $file_url = '<a href="downloadxport.php?filename=teo_export_' . $export_token_id . '&format=' . $file_format . '" class="reset_export_session" target="_blank">Datei herunterladen</a>';

                if ($progress == 0) {
                    echo json_encode(['error' => 'Keine Fahrzeuge gefunden!']);
                    exit(0);
                } else if ($progress < 1) {
                    echo json_encode(['progress' => $progress * 100]);
                    exit(0);
                }
            }
        }
        //default  behaviour for function, if the key 'progress' is not found then it means processing batches is done!
        echo json_encode(['done' => 100, 'file_url' => $file_url]);
        exit(0);
    }

    /**
     * Batch processing is based on session, this function is called when the exported file is downloaded and user can now initiate new export
     */
    function reset_export_session() {
        if (isset($_POST['export_token'])) {
            $export_token_id = filter_var($_POST['export_token'], FILTER_SANITIZE_STRING);
            unset($_SESSION['export_token_' . $export_token_id]);
        }
        echo $this->user->getUserId() . '_' . time();
        exit(0);
    }


    function printContent() //@todo Remove the inherited printContent functions here .. maybe except for the chrginfra role?
    {
//    var_dump(debug_backtrace());
        include("pages/search.php");
    }
}