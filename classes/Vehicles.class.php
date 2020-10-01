<?php
/**
 * vehicles.class.php
 * Klasse für alle vehicles
 * @author Pradeep Mohan
 */

/**
 * Class to handle vehicles
 */
require_once $_SERVER['STS_ROOT'] . '/includes/sts-datetime.php';

class Vehicles extends LadeLeitWarte
{

    protected $dataSrcPtr;

    protected $diagnosePtr;

    protected $pvsVariants = [];

    protected $sopVariants = [];

    const DTCS_EXCEPTION_METHOD_NORMAL = 1;

    const DTCS_EXCEPTION_METHOD_MAJORANA = 3;

    const SW_VER_ECU_PARAM_SET_ID = 2;

    protected $enable_manual_flashing_ecus;

    protected $ignore_dtcs_sw_error;

    protected $ignore_log_sw_error;

    protected $sw_version_regex;

    public $aResults;


    function __construct(DataSrc $dataSrcPtr, $tableName, $diagnosePtr, $pvsVariants, $sopVariants)
    {

        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
        $this->diagnosePtr = $diagnosePtr;
        $this->pvsVariants = $pvsVariants;
        $this->sopVariants = $sopVariants;

        $this->enable_manual_flashing_ecus = [
            'PCU'
        ];
        $this->ignore_dtcs_sw_error = [
            'PCU' => [
                65535
            ]
        ];
        $this->ignore_log_sw_error = [
            'PCU' => [
                "testEcusSwValuePcu"
            ]
        ];
        $this->sw_version_regex = [
            'PCU' => "#^[0-9_]+[A-Z0-9]{4}_+$#"
        ];
    }


    function getSopVariants()
    {
        return $this->sopVariants;
    }

    function getEOLVehiclesNew($depot_id = 0, $action, $page, $size, $fcol, $scol, $vehicle_ids_include = null, $vehicle_ids_exclude = null, $productionDepotsOnly = false)
    {

        $searchProcessedStatus = false;
        $searchAllDepots = false;
        $result = $this->newQuery();

        $result = $result->where('vin', 'IS', 'NOT NULL')
            ->multipleAndWhere('vin', 'LIKE', 'WS5B%', 'OR', 'vin', 'LIKE', 'WS5D%', 'OR', 'vin', 'LIKE', 'WF0J%')
            ->join('vehicle_variants', 'vehicles.vehicle_variant=vehicle_variants.vehicle_variant_id', 'LEFT JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('penta_numbers', 'penta_numbers.penta_number_id=vehicles.penta_number_id', 'LEFT JOIN')
            ->join('qs_fault_count', 'qs_fault_count.vehicle_id=vehicles.vehicle_id', 'LEFT JOIN')
            ->join('measurements', 'measurements.vehicle_id=vehicles.vehicle_id', 'LEFT JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
            ->join('sub_vehicle_configurations', 'vehicles.sub_vehicle_configuration_id=sub_vehicle_configurations.sub_vehicle_configuration_id', 'INNER JOIN');

        if (! $productionDepotsOnly) {
            $result->where('vehicles.finished_status', '=', 'FALSE');
        }
        // used for sorting and filtering
        $headers = array(
            'vin',
            'ikz',
            'code',
            'penta_kennwort',
            'c2cbox',
            'windchill_variant_name',
            'penta_number',
            'dname',
            'processed_diagnose_status',
            'sub_vehicle_configuration_name',
            'short_production_description',
            'delivery_status',
            'special_qs_approval',
            'finished_status'
        );

        if (! empty($vehicle_ids_include)) {
            $result->where('vehicle_id', 'IN', $vehicle_ids_include);
        }
        if (! empty($vehicle_ids_exclude)) {
            $result->where('vehicle_id', 'NOT IN', $vehicle_ids_exclude);
        }
        if (! empty($fcol)) {
            foreach ($fcol as $key => $val) {
                if ($headers[$key] == 'processed_diagnose_status') {
                    $searchProcessedStatus = $val;
                    $result->multipleAndWhere('processed_teo_status.processed_diagnose_status', 'ILIKE', '%' . $val, 'OR', 'processed_teo_status.processed_diagnose_status', 'IS', 'NULL'); // search for empty processed_diagnose_status to force parsing the status
                } else if ($headers[$key] == 'windchill_variant_name')
                    $result->where('vehicle_variants.windchill_variant_name', 'ILIKE', '%' . $val . '%');
                else if ($headers[$key] == 'sub_vehicle_configuration_name')
                    $result->where('sub_vehicle_configurations.sub_vehicle_configuration_name', 'ILIKE', '%' . $val . '%');
                else if ($headers[$key] == 'short_production_description')
                    $result->where('sub_vehicle_configurations.short_production_description', 'ILIKE', '%' . $val . '%');
                else if ($headers[$key] == 'dname')
                    $result->where('depots.name', 'ILIKE', '%' . $val . '%');
                else if ($headers[$key] == 'finished_status') {
                    if ($productionDepotsOnly) {
                        $result->where('vehicles.finished_status', '=', $val);
                    }
                } else if ($headers[$key] == 'delivery_status') {
                    if ($productionDepotsOnly) {
                        $result->where('vehicles_sales.delivery_status', '=', $val);
                    }
                }else if (in_array($headers[$key], array(
                    'vin',
                    'ikz',
                    'code'
                ))) {
                    $searchAllDepots = true;
                    $result->where($headers[$key], 'ILIKE', '%' . $val . '%');
                } else
                    $result->where($headers[$key], 'ILIKE', '%' . $val . '%');
            }
        }

        if (! empty($scol)) {
            foreach ($scol as $key => $val) {
                if ($val == 1)
                    $sortorder = 'DESC';
                else
                    $sortorder = 'ASC';

                if ($headers[$key] == 'processed_diagnose_status')
                    $result->orderBy($headers[$key], $sortorder . ' NULLS LAST');
                else
                    $result->orderBy($headers[$key], $sortorder);
            }
        } else
            $result->orderBy('latest_teo_status.diagnose_status_time', 'DESC')
                ->orderBy('vehicles.finished_status', 'ASC')
                ->orderBy('substring(processed_diagnose_status from 1 for 4)', 'DESC')
                ->orderBy('c2cbox', 'DESC NULLS LAST'); //

        // $searchAllDepots is set to true when searching for one single vehicle..
        if (! $searchAllDepots) {
            // if not searching for one single vehicle, then limit search to the depot_ids selected
            if (is_array($depot_id))
                $result = $result->where('depot_id', 'IN', $depot_id);
            else
                $result = $result->where('depot_id', '=', $depot_id);
            $result = $result->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'INNER JOIN');
        } else
            $result = $result->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'LEFT JOIN');
        // if single vehicle then also vehicles without TEO entry

        if (empty($vehicle_ids_include))
            $result = $result->offset($page * $size)->limit($size);

        $eol_vehicles = $result->join('processed_teo_status', 'processed_teo_status.diagnostic_session_id=latest_teo_status.diagnostic_session_id', 'FULL OUTER JOIN')
        ->get("vehicles.vehicle_id,vehicles.vin,vehicles.ikz,code,penta_kennwort,c2cbox,vehicles.vehicle_variant,windchill_variant_name,
            depots.name as dname,depots.depot_id,finished_status,qmlocked,special_qs_approval,vehicles.penta_number_id,penta_number,vehicles_sales.production_date,vehicles_sales.delivery_status,
            latest_teo_status.diagnose_status_time,latest_teo_status.diagnose_status,latest_teo_status.diagnostic_session_id,processed_diagnose_status,sub_vehicle_configurations.short_production_description,sub_vehicle_configurations.sub_vehicle_configuration_name,
            measurements.online,date_trunc('seconds',measurements.timestamp AT TIME ZONE 'Europe/Berlin') as timestamp, open_fault_cnt,rectified_cnt,processed_teo_status.targetsw_changed");
            
        if (empty($eol_vehicles))
            return array();
        // remove this.. $diagnose_dtcs_codes=$this->diagnosePtr->newQuery('dtcs')->get('*');
        foreach ($eol_vehicles as $key => &$vehicle) {
            $vehicle['status_extra_data'] = '';

            if ($vehicle['qmlocked'] == 't')
                $vehicle['status_extra_data'] = 'Fahrzeug von QM gesperrt!<br><br>';
            $vehicle['ecu_status_count'] = $vehicle['log_status_count'] = 0;
            if (empty($vehicle['processed_diagnose_status'])) {
                $this->diagnoseDetails($vehicle);
                if ($searchProcessedStatus !== false && $vehicle['processed_diagnose_status'] != $searchProcessedStatus) {
                    unset($eol_vehicles[$key]); // after processing, if the processed_diagnose_status does not match the actual status searched for
                }
            }
        }
        return $eol_vehicles;

    }


    public function getFilteredVehicles($k)
    {

        $filter_vehicles = [];
        $multipleSelect = "";
        // foreach ($indexes as $key => $value) {
        if (isset($_GET['qs_faults_search_input'][$k]) || isset($_GET['child_cat'][$k])) {
            // regex
            $LIKE_REGEX_START = isset($_GET['regex'][$k]) ? " ~ '" : " LIKE '%";
            $LIKE_REGEX_STOP = isset($_GET['regex'][$k]) ? "' " : "%' ";
            // ---regex
            $qs_faults_input = isset($_GET['qs_faults_search_input'][$k]) ? filter_var_array($_GET['qs_faults_search_input'][$k], FILTER_SANITIZE_STRING) : null;
            $cat_search = filter_var_array($_GET['general_search_cat_qs_errors'], FILTER_SANITIZE_STRING);
            $child_cat = isset($_GET['child_cat'][$k]) ? filter_var_array($_GET['child_cat'][$k], FILTER_SANITIZE_STRING) : null;
            $cat_search = $cat_search[$k];
            $child_cat = $child_cat != null ? $child_cat[array_keys($child_cat)[$k]] : null;
            $search_fields_empty = true;

            if (! empty($qs_faults_input) && ! (strlen(implode($qs_faults_input[array_keys($qs_faults_input)[0]])) == 0)) {
                $multipleSelect = "SELECT DISTINCT vehicle_id FROM qs_fault_list";
                $first = true;
                foreach ($qs_faults_input as $qs_cat_id => $qs_input) {
                    // without a defined callback, array_filter simply removes all the empty values
                    if (is_array($qs_input) && ! (strlen(implode($qs_input)) == 0)) {
                        foreach ($qs_input as $field_key => $field_val) {
                            if ($field_val != "") {
                                $search_fields_empty = false;
                                // verify with category too
                                if (strpos($field_key, "_select") !== false) {
                                    if ($first) {
                                        $first = false;
                                        $multipleSelect .= " WHERE qs_fcat_id = " . $qs_cat_id . " AND field_key  = '" . $field_key . "' AND  field_value = '" . $field_val . "'";
                                    } else {
                                        $multipleSelect .= " INTERSECT SELECT DISTINCT vehicle_id FROM qs_fault_list WHERE qs_fcat_id = " . $qs_cat_id . " AND field_key = '" . $field_key . "' AND field_value = '" . $field_val . "'";
                                    }
                                } else {
                                    if ($first) {
                                        $first = false;
                                        $multipleSelect .= " WHERE qs_fcat_id = " . $qs_cat_id . " AND field_key = '" . $field_key . "' AND  field_value $LIKE_REGEX_START" . $field_val . "$LIKE_REGEX_STOP";
                                    } else {
                                        $multipleSelect .= " INTERSECT SELECT DISTINCT vehicle_id FROM qs_fault_list WHERE qs_fcat_id = " . $qs_cat_id . " AND field_key = '" . $field_key . "' AND field_value $LIKE_REGEX_START" . $field_val . "$LIKE_REGEX_STOP";
                                    }
                                }
                            }
                        }
                    } else {
                        if ($qs_input != "") {
                            $search_fields_empty = false;
                            if ($first) {
                                $first = false;
                                $multipleSelect .= " WHERE field_key = '" . $qs_cat_id . "'' AND field_value = '" . $qs_input . "''";
                            } else {
                                $multipleSelect .= " INTERSECT SELECT DISTINCT vehicle_id FROM qs_fault_list WHERE field_key = '" . $qs_cat_id . "' AND field_value = '" . $qs_input . "'";
                            }
                        }
                    }
                }

                if ($search_fields_empty == true && $child_cat != "") {
                    $search_fields_empty = false;
                    $multipleSelect = "";
                    $result = $this->newQuery('qs_fault_list')->where('qs_fcat_id', '=', $child_cat);
                }
            }

            if (! empty($cat_search) && $cat_search != 0 && $search_fields_empty == true) {
                $search_fields_empty = false;
                $multipleSelect = "";
                $categoriesId = $this->newQuery("qs_fault_categories")->where('parent_cat', '=', $cat_search);
                $categoriesId = $categoriesId->get('qs_fcat_id');
                $result = $this->newQuery('qs_fault_list');
                if (! empty($categoriesId)) {
                    foreach ($categoriesId as $value1) {
                        $result = $result->multipleOrWhere('qs_fcat_id', '=', $value1['qs_fcat_id']);
                    }
                } else {
                    $result = $result->where('qs_fcat_id', '=', $cat_search);
                }
            }
        } else {
            $result = $this->newQuery('qs_fault_list');
        }
        if ($multipleSelect != "") {

            $result = $this->getSpecialSql($multipleSelect);
        } else {
            $result = $result->get('distinct vehicle_id');
        }

        if (! empty($result)) {
            foreach ($result as $value1) {
                array_push($filter_vehicles, $value1['vehicle_id']);
            }
        } else {
            $filter_vehicles[] = 0;
        }

        return $filter_vehicles;

    }


    public function searchDtc($type, $k, $searchNot)
    {

        $ecu = $_GET['general_search_cat_dct'][$k];
        $dtc_number = $_GET['search_value'][$k];
        // regex
        $LIKE_REGEX = isset($_GET['regex'][$k]) ? "~ " : " LIKE ";
        $LIKE_REGEX_CHAR = isset($_GET['regex'][$k]) ? " " : "%";
        if (isset($_GET['regex'][$k])) {
            $searchNot = "!";
        }
        // ---regex

        $result = $this->diagnosePtr->newQuery('latest_teo_status')->join('material_dtcs_revocation_processed', 'using(diagnostic_session_id)', 'JOIN');

        if ($type == 'ecu_dtc_pairs') {

            $result->multipleOrWhere('material_dtcs_revocation_processed.ecu', '=', $ecu, 'AND', 'material_dtcs_revocation_processed.dtc_number', $searchNot . '=', $dtc_number);
        } elseif ($type == 'ecu_log_pairs') {

            $result->multipleOrWhere('material_dtcs_revocation_processed.ecu', '=', $ecu, 'AND', 'material_dtcs_revocation_processed.text', $searchNot . '' . $LIKE_REGEX, $LIKE_REGEX_CHAR . '' . $dtc_number . '' . $LIKE_REGEX_CHAR);
        }

        $result = $result->join('vin_wc_variant_penta', 'using(vin)', 'JOIN')->get('distinct vehicle_id');
        $cars = array();

        if (($result)) {
            foreach ($result as $value) {
                $cars[] = $value['vehicle_id'];
            }
        } else {
            $cars[] = 0; // cant find anything, so look for vin nr 0 = false;
        }

        return $cars;

    }


    function getEOLVehiclesNewWithSearch($depot_id = 0, $action, $page, $size, $fcol, $scol, $vehicle_ids_include = null, $vehicle_ids_exclude = null, $productionDepotsOnly = false, $skipTable = false)
    {

        $searchProcessedStatus = false;
        $searchAllDepots = false;

        if ($this->newQuery('user_settings')
            ->where("sts_userid", "=", $_SESSION["sts_userid"])
            ->get("settings") == null) {
            $settings = array(
                'depots' => array(
                    3348 => 1,
                    3368 => 1,
                    0 => 1,
                    3367 => 1,
                    3170 => 1
                )
            );
            $this->newQuery('user_settings')->insert_multiple_new([
                'sts_userid',
                'settings'
            ], [
                [
                    $_SESSION["sts_userid"],
                    serialize($settings)
                ]
            ]);
        } else {
            $settings = $this->newQuery('user_settings')
                ->where("sts_userid", "=", $_SESSION["sts_userid"])
                ->get("settings");
            $new_settings = array(
                'depots' => array(
                    3348 => 0,
                    3368 => 0,
                    0 => 0,
                    3367 => 0,
                    3170 => 0
                )
            );
            foreach ($_GET['selected_depots'] as $key => $value) {
                $new_settings['depots'][$value] = 1;
            }
            $settings['depots'] = $new_settings['depots'];
            $this->newQuery('user_settings')
                ->where('sts_userid', '=', $_SESSION['sts_userid'])
                ->update(array(
                'settings'
            ), array(
                serialize($settings)
            ));
        }

        $result = $this->newQuery(); // ->where('c2cbox','IS','NOT NULL')

        $aSearchValue = $_GET['search_value'];
        $aSearchValueBool = $_GET['search_value_bool'];
        $aSearchNotBool = $_GET['search_not_bool'];
        $aWhatTosearchIn = $_GET['general_search_cat'];
        $aOperators = $_GET['operator'];
        $aSelectedDepots = $depot_id = $_GET['selected_depots'];
        $aAvaibleParams = CommonFunctions_GeneralSearch::$generalSearchParams;

        /**
         *
         * @var $result NewQueryPgsql
         */
        $result = $result->where('vin', 'IS', 'NOT NULL');

        $result->join('vehicle_variants', 'vehicles.vehicle_variant=vehicle_variants.vehicle_variant_id', 'LEFT JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('penta_numbers', 'penta_numbers.penta_number_id=vehicles.penta_number_id', 'LEFT JOIN')
            ->join('qs_fault_count', 'qs_fault_count.vehicle_id=vehicles.vehicle_id', 'LEFT JOIN')
            ->join('measurements', 'measurements.vehicle_id=vehicles.vehicle_id', 'LEFT JOIN');

        if (! $productionDepotsOnly) {}

        // used for sorting and filtering
        $headers = array(
            'vin',
            'ikz',
            'code',
            'penta_kennwort',
            'c2cbox',
            'windchill_variant_name',
            'penta_number',
            'dname',
            'processed_diagnose_status',
            'special_qs_approval',
            'finished_status'
        );

        if (! empty($vehicle_ids_include)) {
            $result->where('vehicle_id', 'IN', $vehicle_ids_include);
        }
        if (! empty($vehicle_ids_exclude)) {
            $result->where('vehicle_id', 'NOT IN', $vehicle_ids_exclude);
        }
        if (! empty($fcol)) {
            foreach ($fcol as $key => $val) {
                if ($headers[$key] == 'processed_diagnose_status') {
                    $searchProcessedStatus = $val;
                    $result->multipleAndWhere('processed_teo_status.processed_diagnose_status', 'ILIKE', '%' . $val, 'OR', 'processed_teo_status.processed_diagnose_status', 'IS', 'NULL'); // search for empty processed_diagnose_status to force parsing the status
                } else if ($headers[$key] == 'windchill_variant_name')
                    $result->where('vehicle_variants.windchill_variant_name', 'ILIKE', '%' . $val . '%');
                else if ($headers[$key] == 'dname')
                    $result->where('depots.name', 'ILIKE', '%' . $val . '%');
                else if ($headers[$key] == 'finished_status') {
                    if ($productionDepotsOnly) {
                        // $result->where('vehicles.finished_status', '=', $val);
                    }
                } else if (in_array($headers[$key], array(
                    'vin',
                    'ikz',
                    'code'
                )) && strlen($val) > 16) {
                    $searchAllDepots = true;
                    $result->where($headers[$key], 'ILIKE', '%' . $val . '%');
                } else
                    $result->where($headers[$key], 'ILIKE', '%' . $val . '%');
            }
        }

        if (! empty($scol)) {

            foreach ($scol as $key => $val) {
                if ($val == 1)
                    $sortorder = 'DESC';
                else
                    $sortorder = 'ASC';

                if ($headers[$key] == 'processed_diagnose_status')
                    $result->orderBy($headers[$key], $sortorder . ' NULLS LAST');
                elseif ($headers[$key] == 'dname')
                    $result->orderBy('depots.name', $sortorder);
                else
                    $result->orderBy($headers[$key], $sortorder);
            }
        } else
            $result->orderBy('latest_teo_status.diagnose_status_time', 'DESC')
                ->orderBy('vehicles.finished_status', 'ASC')
                ->orderBy('substring(processed_diagnose_status from 1 for 4)', 'DESC')
                ->orderBy('c2cbox', 'DESC NULLS LAST'); //

        // $searchAllDepots is set to true when searching for one single vehicle..
        if (! $searchAllDepots) {
            // if not searching for one single vehicle, then limit search to the depot_ids selected
            if (is_array($depot_id))
                $result = $result->where('depot_id', 'IN', $depot_id);
            // INNER JOIN to filter out the vehicles that do not have a TEO entry
            $result = $result->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'INNER JOIN');
        } else
            $result = $result->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'LEFT JOIN');

        if (! $productionDepotsOnly) {}
        // used for sorting and filtering

        if (0) {} else {
            $iCnt = count($aWhatTosearchIn);
            $filtered_vehicles = [];
            $errors = false;
            $vehicles = [];
            $first = true;

            if ($iCnt > 0) {
                foreach ($aWhatTosearchIn as $k => $v) {
                    $first == true ? ($result = $result->bracketWhere("(", "AND") and $first = false) : "";
                    $curOpera = $k == 0 ? "" : $_GET['operator'][$k - 1];
                    if (! $v) {
                        $result->where("processed_teo_status.processed_diagnose_status", "ILIKE ", "%PASSED%", "");
                        $result->orderStr = '';
                        $result->orderBy("latest_teo_status.diagnose_status_time", "DESC");
                        continue;
                    }
                    if ($v == 'mechanical_errors') {
                        $vehicles = $this->getFilteredVehicles($k);
                        $errors = true;
                        $result->where('vehicle_id', 'IN', $vehicles, $curOpera);
                    } elseif ($v == 'diagnostic') {
                        // we need to search by diagnostic code
                        $searchNot = $aSearchNotBool[$k];
                        if (($_GET['general_search_cat_diag'][$k]) == 'ecu_dtc_pairs') {
                            $searchNot = $searchNot != "-" ? "! " : "";
                            $searchDtc = $this->searchDtc('ecu_dtc_pairs', $k, $searchNot);

                            $result->where('vehicle_id', 'IN', $searchDtc, $curOpera);
                        } elseif ($_GET['general_search_cat_diag'][$k] == 'ecu_log_pairs') {
                            $searchNot = $searchNot != "-" ? "NOT " : "";
                            $searchDtc = $this->searchDtc('ecu_log_pairs', $k, $searchNot);

                            $result->where('vehicle_id', 'IN', $searchDtc, $curOpera);
                        }
                        $errors = true;
                    } elseif ($v == 'vehicle_variants.variant') {
                        if ($curOpera == 'AND')
                            $method = 'multipleAndWhere';
                        else if ($curOpera == 'OR') {
                            $method = 'multipleOrWhere';
                        } else {
                            $method = 'multipleWhere';
                        }

                        $searchNot = $aSearchNotBool[$k];

                        if (isset($_GET['regex'][$k])) {
                            $searchNot = $searchNot != "-" ? "!" : "";
                            $searchVal = $aSearchValue[$k];
                            $whereOp = $searchNot . "~ ";
                        } else {
                            $searchNot = $searchNot != "-" ? "NOT " : "";
                            $searchVal = '%' . $aSearchValue[$k] . '%';
                            $whereOp = $searchNot . "ILIKE ";
                        }

                        $result->$method($aWhatTosearchIn[$k], $whereOp, $searchVal, 'OR', 'vehicle_variants.windchill_variant_name', $whereOp, $searchVal);
                    } else {
                        $searchVal = $aSearchValue[$k];
                        $searchBool = $aSearchValueBool[$k];
                        $searchNot = $aSearchNotBool[$k];
                        if ($searchBool != "-") {
                            $searchNot = $searchNot != "-" ? "!" : "";
                            $result->where($aWhatTosearchIn[$k], $searchNot . '=', $searchBool, $curOpera);
                        } else if ($searchVal != "") {

                            if (isset($_GET['regex'][$k])) {
                                $searchNot = $searchNot != "-" ? "!" : "";

                                $whereOp = $searchNot . "~ ";
                            } else {
                                $searchNot = $searchNot != "-" ? "NOT " : "";
                                $searchVal = '%' . $searchVal . '%';
                                $whereOp = $searchNot . "ILIKE ";
                            }

                            $result->where($aWhatTosearchIn[$k], $whereOp, $searchVal, $curOpera);
                        } else {
                            $result->where("1.1", "=", "1.1", $curOpera);
                        }
                    }
                }
                $first == false ? $result = $result->bracketWhere(")") : "";
            }
        }

        $result = $result->join('processed_teo_status', 'processed_teo_status.diagnostic_session_id=latest_teo_status.diagnostic_session_id', 'FULL OUTER JOIN');

        $cnt = $result->getOne('vehicles.vehicle_id');

        $totalcnt = $result->getNumRows();

        if (empty($vehicle_ids_include)) {
            if ($skipTable == false) {
                $result = $result->offset($page * $size)->limit($size);
            } else {
                $result = $result->limit(11);
            }
        }

        $eol_vehicles = $result->get("vehicles.vehicle_id,vin,ikz,penta_kennwort,c2cbox,vehicle_variant,windchill_variant_name,
            depots.name as dname,depots.depot_id,finished_status,qmlocked,special_qs_approval,vehicle_variant,vehicles.penta_number_id,penta_number,
            latest_teo_status.diagnose_status_time,latest_teo_status.diagnose_status,latest_teo_status.diagnostic_session_id,processed_diagnose_status,
            measurements.online,date_trunc('seconds',measurements.timestamp AT TIME ZONE 'Europe/Berlin') as timestamp, open_fault_cnt,rectified_cnt,processed_teo_status.targetsw_changed,
            vehicle_variants.variant");

        if (empty($eol_vehicles))
            return array();
        foreach ($eol_vehicles as $key => &$vehicle) {
            $vehicle['status_extra_data'] = '';

            if ($vehicle['qmlocked'] == 't')
                $vehicle['status_extra_data'] = 'Fahrzeug von QM gesperrt!<br><br>';
            $vehicle['ecu_status_count'] = $vehicle['log_status_count'] = 0;
            if (empty($vehicle['processed_diagnose_status'])) {
                $this->diagnoseDetails($vehicle);
                if ($searchProcessedStatus !== false && $vehicle['processed_diagnose_status'] != $searchProcessedStatus) {
                    unset($eol_vehicles[$key]); // after processing, if the processed_diagnose_status does not match the actual status searched for
                }
            }
        }
        return array(
            'records' => $eol_vehicles,
            'my_total_count' => $totalcnt
        );

    }


    /**
     * *
     * resetProcessedStatus
     *
     * @param array|integer $vehicle_variants
     * @param string $swecu
     * @return
     */
    function reprocessStatusNewSw($vehicle_variants, $swecu, $newSwVersion, $user)
    {

        // limit the vehicle statuses to be processed to those in the production depots only
        $production_depots = $this->newQuery('depots')
            ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
            ->where('divisions.production_location', '=', 't')
            ->get('depot_id');

        $productionDepots = array_column($production_depots, 'depot_id'); // (0,3348,3367);

        $newSwVersion = preg_replace("#[^0-9a-zA-Z]#", "", $newSwVersion);

        if (! empty($swecu)) {
            $diagnosePtr = $this->getDiagnoseObject();
            $result = $diagnosePtr->newQuery('latest_teo_status');
            if ($this->ignore_dtcs_sw_error[$swecu]) {
                $result = $result->join('material_dtcs_revocation_processed', 'using(diagnostic_session_id)', 'LEFT JOIN')->multipleOrWhere('material_dtcs_revocation_processed.vehicle_variant', 'IN', $vehicle_variants, 'AND', 'material_dtcs_revocation_processed.ecu', '=', $swecu, 'AND', 'material_dtcs_revocation_processed.dtc_number', '=', $this->ignore_dtcs_sw_error[$swecu]);
            }
            if ($this->ignore_log_sw_error[$swecu]) {
                $result = $result->join('material_log_revocation_processed', 'using(diagnostic_session_id)', 'LEFT JOIN')->multipleOrWhere('material_log_revocation_processed.vehicle_variant', 'IN', $vehicle_variants, 'AND', 'material_log_revocation_processed.name', '=', $this->ignore_log_sw_error[$swecu][0]);
            }

            $session_ids = $result->join('ecu_data', 'using(diagnostic_session_id)', 'LEFT JOIN')
                ->join('part_numbers', 'ecu_data.part_number_id=part_numbers.part_number_id AND ecu_data.ecu=part_numbers.ecu ', 'LEFT JOIN')
                ->where("replace(part_numbers.program_version,'_','')", '=', $newSwVersion)
                ->get('latest_teo_status.diagnostic_session_id');
        }

        $result = $this->newQuery('latest_teo_status')->join('vehicles', 'vehicles.vin=latest_teo_status.teo_vin');

        if (! empty($session_ids)) {
            $session_ids_process = array_column($session_ids, 'diagnostic_session_id');
            $result = $result->where('latest_teo_status.diagnostic_session_id', 'IN', $session_ids_process);
        }
        $diagnostic_sessions = $result->where('vehicles.depot_id', 'IN', $productionDepots)
            ->where('vehicles.vehicle_variant', 'IN', $vehicle_variants)
            ->join('depots', 'vehicles.depot_id=depots.depot_id')
            ->join('processed_teo_status', 'processed_teo_status.diagnostic_session_id=latest_teo_status.diagnostic_session_id', 'FULL OUTER JOIN')
            ->get('vehicles.vehicle_id,vin,ikz,code,penta_kennwort,c2cbox,vehicle_variant,
                            depots.name as dname,depots.depot_id,finished_status,qmlocked,special_qs_approval,vehicle_variant,vehicles.penta_number_id
                            latest_teo_status.diagnose_status_time,latest_teo_status.diagnose_status,latest_teo_status.diagnostic_session_id,processed_diagnose_status');
        if (! empty($diagnostic_sessions)) {
            $diagnostic_session_ids = array_column($diagnostic_sessions, 'diagnostic_session_id');
            $update_result = $this->newQuery('processed_teo_status')
                ->where('diagnostic_session_id', 'IN', $diagnostic_session_ids)
                ->update([
                'targetsw_changed',
                'processed_diagnose_status'
            ], [
                't',
                NULL
            ]);
            $insertVals = [];
            $timestamp_insert = date('Y-m-d H:i:sO');
            foreach ($diagnostic_session_ids as $did) {
                $insertVals[] = [
                    $did,
                    $swecu,
                    $timestamp_insert,
                    $user->getUserId()
                ];
            }
        }
        $this->diagnosePtr->newQuery('targetsw_changed')->insertMultiple([
            'diagnostic_session_id',
            'ecu',
            'insert_time',
            'addedby'
        ], $insertVals);

    }


    // @todo: function not needed
    function getC2CBoxOnlineStatus($vehicleID = 0)
    {

        $result = $this->newQuery('measurements')
            ->where('vehicle_id', '=', $vehicleID)
            ->limit(1);
        $info = $result->get('online,timestamp');
        if (empty($info))
            return array();
        return $info;

    }


    /**
     * function to fetch the target sw version (Sollstand) for this vehicle
     *
     * @param integer $vehicle_id
     * @param integer $ecu_id
     * @param integer $paramter_set_id
     * @return string
     */
    function fetchTargetSw($vehicle_id, $ecu_id, $paramter_set_id)
    {

        $query = '
                select variant_id, penta_id, rev_id, ecu_parameter_sets.type_id,
                value_types, vehicle_variant_data.*
                from vehicles
                join variant_ecu_revision_mapping on variant_id=vehicles.vehicle_variant
                and (penta_id=vehicles.penta_number_id or (penta_id=0))
                join vehicle_variant_data on vehicle_variant_id=variant_id and
                overlayed_penta_id=penta_id
                join ecu_parameters using (ecu_parameter_id, ecu_id)
                join ecu_parameter_sets using (ecu_parameter_set_id)
                join parameter_value_types on parameter_value_types_id = type_id
                where vehicle_id=$1  and ecu_id=$2 and ecu_parameter_set_id=$3
                order by penta_id desc
                limit 1;';
        $sw_version_result = $this->newQuery()->specialSqlPrepare($query, array(
            $vehicle_id,
            $ecu_id,
            $paramter_set_id
        ));
        $sw_version = '';
        if (is_array($sw_version_result) && ! empty($sw_version_result)) {
            $sw_version = $sw_version_result[0]['value_string'];
        }
        return $sw_version;

    }


    /**
     * Fetch the version of SW that was manually flashed onto the ECU.
     * Uses
     * ecu is stored as text since the ecu_id for an ecu, in the diagnose and LeitwartenDB database are not always the same
     *
     * @param integer $vehicle_id
     * @param string $ecu
     * @param integer $diagnostic_session_id
     * @return array
     */
    function fetchManualSw($vehicle_id, $ecu, $diagnostic_session_id = null)
    {

        $manualsw = $this->diagnosePtr->newQuery('manual_software_version')
            ->where('vehicle_id', '=', $vehicle_id)
            ->where('ecu', '=', $ecu)
            ->getOne("*");
        return $manualsw;

    }


    /**
     * This is the function used to fetch data for the list of valid SW versions for an ECU.
     * This data populates the drop down menu allowing user to select the version of SW that was manually flashed
     *
     * @param integer $ecu_id
     * @return array
     */
    function availableSWVer($ecu_id)
    {

        $query = "select distinct on (tag, ecu_parameter_set_id, ecu_revision_id)
	    ecu_revision_id,tag_value
	    from ecu_tag_configuration
	    join ecu_revisions using (ecu_revision_id)
	    where ecu_id=$1
	    and tag='version'
	    and ecu_parameter_set_id=2
	    order by ecu_revision_id, ecu_parameter_set_id, tag, timestamp desc";
        $result = $this->newQuery('ecu_tag_configuration')->specialSqlPrepare($query, array(
            $ecu_id
        ));
        $availableSw;
        foreach ($result as $row)
            $availableSw[$row['ecu_revision_id']] = $row['tag_value'];

        return $availableSw;

    }


    function saveManualSW($swver, $vehicle, $ecu, $userid, &$msg, $diagid = NULL, $oldsw = '')
    {

        $vehicleid = $vehicle['vehicle_id'];
        $ecu_id = $this->newQuery('ecus')
            ->where('name', '=', strtolower($ecu))
            ->getVal('ecu_id');
        $sw_version = $this->fetchTargetSw($vehicleid, $ecu_id, self::SW_VER_ECU_PARAM_SET_ID);
        $sw_version_available = $this->availableSWVer($ecu_id);
        $result = null;
        if ($sw_version == $sw_version_available[$swver]) {
            $insertArray = [
                'diagnostic_session_id' => $diagid,
                'new_value' => $sw_version_available[$swver],
                'vehicle_id' => $vehicleid,
                'ecu' => $ecu,
                'insert_time' => date('Y-m-d H:i:sO'),
                'old_value' => $oldsw,
                'addedby' => $userid
            ];
            $result = $this->insertNewSw($insertArray);
            if ($result && ! empty($diagid)) {
                $old_status = $vehicle['processed_diagnose_status'];
                $this->diagnoseDetails($vehicle);
                $new_status = $vehicle['processed_diagnose_status'];
                if ($old_status != $new_status)
                    $msg = 'TEO Status wird geändert';
                else
                    $msg = 'Keine TEO Status Änderung';
            } else if ($result) {
                $msg = 'Eingabe gespeichert!';
            } else
                $msg = 'Fehler beim Speichern!';
        } else
            $msg = 'Eingegebene SW Stand stimmt nicht mit Soll SW Stand überein';
        return $result;

    }


    /**
     * *
     *
     * @param array $insertArray
     * @return integer|false
     */
    function insertNewSw($insertArray)
    {

        return $this->diagnosePtr->newQuery('manual_software_version')->insert($insertArray);

    }


    function fetchSwForm($vehicle, $user)
    {

        $return_str = '';
        foreach ($this->enable_manual_flashing_ecus as $ecu_name) {
            $return_str .= '<h2>' . $ecu_name . '</h2>';
            $manual_sw_version = $this->fetchManualSw($vehicle['vehicle_id'], $ecu_name);
            if (! empty($manual_sw_version)) {
                $userid = $manual_sw_version['addedby'];
                $username = $this->newQuery('users')
                    ->where('id', '=', $userid)
                    ->getVal('username');
                $user_info_string = '<span id="userinfo_' . $vehicle['vehicle_id'] . '" style="display: none">von ' . $username . '</span>';
                $return_str .= $manual_sw_version['new_value'] . ' (manuell geflasht) ' . '<a href="#" style="display: inline-block" class="show_info" ' . 'data-target_id="userinfo_' . $vehicle['vehicle_id'] . '" ' . '><span class="genericon genericon-info"></span></a>' . $user_info_string;
            } else if (! empty($user) && $user->user_can('manualsw')) {
                $ecu_id = $this->newQuery('ecus')
                    ->where('name', '=', strtolower($ecu_name))
                    ->getVal('ecu_id');
                $availableSWVer = $this->availableSWVer($ecu_id);
                $select_str = '<select id="available_ecu_sw" name="ecusw"><option value=""></option>';
                foreach ($availableSWVer as $key => $row)
                    $select_str .= '<option value="' . $key . '">' . $row . '</option>';
                $select_str .= '</select>';
                $return_str .= $select_str . '<button data-diagid="" data-oldsw="" data-ecu="' . $ecu_name . '"  data-vehicleid="' . $vehicle['vehicle_id'] . '" class="save_new_program_version">Speichern</button>';
            }
        }
        return $return_str;

    }


    function fetchTeoError(&$vehicle, $user = false, $printpdf = false)
    {

        $this->diagnosePtr->newQuery('material_dtcs_revocation_processed')->query('REFRESH MATERIALIZED VIEW material_dtcs_revocation_processed');
        $this->diagnosePtr->newQuery('material_log_revocation_processed')->query('REFRESH MATERIALIZED VIEW material_log_revocation_processed');

        $ecu_manual_sw = [];
        $version_numbers = $this->diagnosePtr->newQuery('ecu_data')
            ->multipleAndWhere('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'], 'OR', 'diagnostic_session_id', 'IS', 'NULL')
            ->join('ecus', 'ecus.name=ecu_data.ecu', 'INNER JOIN')
            ->join('general', 'general.diagnostic_session_id=ecu_data.diagnostic_session_id', 'INNER JOIN')
            ->orderBy('ecus.name')
            ->join('part_numbers', 'ecu_data.part_number_id=part_numbers.part_number_id AND ecu_data.ecu=part_numbers.ecu ', 'LEFT JOIN')
            ->get('ecus.name,program_version,hardware_version_number');

        foreach ($version_numbers as &$version_number) {
            if (in_array($version_number['name'], $this->enable_manual_flashing_ecus)) {
                // $pcu_data_key=array_search('PCU',array_column($version_numbers,'name'));
                $ecu_name = $version_number['name'];
                $manual_sw_version = $this->fetchManualSw($vehicle['vehicle_id'], $ecu_name);
                if (! empty($manual_sw_version))
                    $ecu_manual_sw[] = $ecu_name;
                if ($printpdf) {
                    if (! empty($manual_sw_version))
                        $version_number['program_version'] = $manual_sw_version['new_value'] . ' (manuell geflasht)';
                } else {
                    if (! empty($manual_sw_version)) {
                        $userid = $manual_sw_version['addedby'];
                        $username = $this->newQuery('users')
                            ->where('id', '=', $userid)
                            ->getVal('username');
                        $user_info_string = '<span id="userinfo_' . $vehicle['vehicle_id'] . '" style="display: none">von ' . $username . '</span>';
                        $version_number['program_version'] = $manual_sw_version['new_value'] . ' (manuell geflasht) ' . '<a href="#" class="show_info" ' . 'data-target_id="userinfo_' . $vehicle['vehicle_id'] . '" ' . '><span class="genericon genericon-info"></span></a>' . $user_info_string;
                    } else if (! empty($user) && $user->user_can('manualsw')) {
                        $old_sw_ver = $version_number['program_version'];
                        $ecu_id = $this->newQuery('ecus')
                            ->where('name', '=', strtolower($ecu_name))
                            ->getVal('ecu_id');
                        $availableSWVer = $this->availableSWVer($ecu_id);
                        $select_str = '<select id="available_ecu_sw" style="display:none" name="ecusw"><option value=""></option>';
                        foreach ($availableSWVer as $key => $row)
                            $select_str .= '<option value="' . $key . '">' . $row . '</option>';
                        $select_str .= '</select>';
                        $version_number['program_version'] = '<span>' . $version_number['program_version'] . '</span>' . $select_str . '<a href="#" class="edit_program_version" style="display: inline-block"
                                                           data-oldsw="' . htmlentities($old_sw_ver) . '"
                                                           data-diagid="' . $vehicle['diagnostic_session_id'] . '"
                                                           data-vehicleid="' . $vehicle['vehicle_id'] . '"
                                                           data-ecu="' . $ecu_name . '"
                                                           ><span class="genericon genericon-edit"></span></a>';
                    }
                    // else don't change the program version string
                }
            }
        }

        if (! empty($version_numbers)) {
            $table = array();
            $table['tableclass'] = 'teo_data_list';
            $table['header'] = '';
            $table['colWidths'] = array(
                '120px',
                '120px',
                '120px'
            );
            $table['headings'] = array(
                array(
                    'headingone' => array(
                        'ECU',
                        'SW Stand',
                        'HW Stand'
                    )
                )
            );
            $table['content'] = $version_numbers;
            $vehicle['tables']['version_numbers'] = $table;
        }

        $ignition_numbers = $this->diagnosePtr->newQuery('general')
        ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
        ->get("general.ignitionkeynumber");

        if (! empty($ecu_manual_sw)) {
            foreach ($ecu_manual_sw as $ecu_single) {
                $log_names = $this->ignore_log_sw_error[$ecu_single];
                foreach ($log_names as $log_name) {
                    foreach ($newquerylog as &$row) {
                        if ($row['ignitionkeynumber'] == $log_name)
                            $row['revresult'] .= 'manuell geflasht - Fehler wird ignoriert';
                    }
                }
            }
        }

        if (! empty($ignition_numbers)) {
            $table = array();
            $table['header'] = '';
            $table['tableclass'] = 'teo_data_list';
            $table['colWidths'] = array(
                '360px'
            );
            $table['headings'] = array(
                array(
                    'headingone' => array(
                        'Ignition Keynumber'
                    )
                )
            );
            $table['content'] = $ignition_numbers;
            $vehicle['tables']['ignition_numb'] = $table;
        }


        $newquery = $this->diagnosePtr->newQuery('material_dtcs_revocation_processed')
            ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
            ->orderBy('ecu', 'ASC')
            ->get("dtc_number,text,ecu,concat_ws(',',rev,rev_variant,rev_vin) as revresult");

        if (! empty($ecu_manual_sw)) {
            foreach ($ecu_manual_sw as $ecu_single) {
                $dtc_codes = $this->ignore_dtcs_sw_error[$ecu_single];
                foreach ($dtc_codes as $dtc_code) {
                    foreach ($newquery as &$row) {
                        if ($row['dtc_number'] == $dtc_code && $row['ecu'] == $ecu_single)
                            $row['revresult'] .= 'manuell geflasht - Fehler wird ignoriert';
                    }
                }
            }
        }

        if (! empty($newquery)) {
            $table = array();
            $table['tableclass'] = 'teo_data_list';
            $table['header'] = '<h2>ECU Data</h2>';
            $table['colWidths'] = array(
                '120px',
                '180px',
                '80px',
                '120px'
            );
            $table['headings'] = array(
                array(
                    'headingone' => array(
                        'DTCS Code',
                        'Beschreibung',
                        'ECU',
                        'Abweicherlaubnis'
                    )
                )
            );
            $table['content'] = $newquery;
            $vehicle['tables']['ecu_data_new'] = $table;
        }

        $newquerylog = $this->diagnosePtr->newQuery('material_log_revocation_processed')
            ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
            ->get("name,comment,passed,concat_ws(',',rev,rev_variant,rev_vin)  as revresult");

        if (! empty($ecu_manual_sw)) {
            foreach ($ecu_manual_sw as $ecu_single) {
                $log_names = $this->ignore_log_sw_error[$ecu_single];
                foreach ($log_names as $log_name) {
                    foreach ($newquerylog as &$row) {
                        if ($row['name'] == $log_name)
                            $row['revresult'] .= 'manuell geflasht - Fehler wird ignoriert';
                    }
                }
            }
        }

        if (! empty($newquerylog)) {
            $table = array();
            $table['header'] = '<h2>Log Data</h2>';
            $table['tableclass'] = 'teo_data_list';
            $table['colWidths'] = array(
                '160px',
                '120px',
                '60px',
                '180px'
            );
            $table['headings'] = array(
                array(
                    'headingone' => array(
                        'Name',
                        'Kommentar',
                        'Passed?',
                        'Abweicherlaubnis'
                    )
                )
            );
            $table['content'] = $newquerylog;
            $vehicle['tables']['log_data_new'] = $table;
        }

        $qm_status = $this->newQuery('vehicles')
            ->where('vehicle_id', '=', $vehicle['vehicle_id'])
            ->getVal('qmlocked');
        if ($qm_status == 't') {
            $qm_history_lock = $this->newQuery('qm_lock_history')
                ->where('vehicle_id', '=', $vehicle['vehicle_id'])
                ->get('*');
            $table = array();
            $table['header'] = '<h2>QM Status : Gesperrt</h2>';
            if ($qm_history_lock) {
                $processedrows = [];
                foreach ($qm_history_lock as $row) {
                    $row['userid'] = $this->newQuery('users')
                        ->where('id', '=', $row['userid'])
                        ->getVal('username');
                    $row['change_ts'] = date('Y-m-d H:i', strtotime($row['update_ts']));
                    if ($row['old_status'] == 'f' && $row['new_status'] == 't')
                        $row['status'] = 'Gesperrt';
                    else if ($row['old_status'] == 't' && $row['new_status'] == 'f')
                        $row['status'] = 'Entsperrt';
                    $processedrows[] = [
                        $row['change_ts'],
                        $row['status'],
                        $row['userid'],
                        $row['qmcomment']
                    ];
                }

                $table['tableclass'] = '';
                $table['colWidths'] = array(
                    '120px',
                    '120px',
                    '100px',
                    '180px'
                );
                $table['headings'] = array(
                    array(
                        'headingone' => array(
                            'Datum/Uhrzeit',
                            'Änderung',
                            'Benutzer',
                            'Kommentare'
                        )
                    )
                );
                $table['content'] = $processedrows;
            } else {
                $table['header'] .= " - ohne Kommentar";
            }
            $vehicle['tables']['qm_history'] = $table;
        }

    }


    function currentSwInvalid($ecu, $vehicle)
    {

        $currentSw = $this->diagnosePtr->newQuery('ecu_data')
            ->multipleAndWhere('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'], 'OR', 'diagnostic_session_id', 'IS', 'NULL')
            ->where('ecu_data.ecu', '=', $ecu)
            ->join('part_numbers', 'ecu_data.part_number_id=part_numbers.part_number_id AND ecu_data.ecu=part_numbers.ecu ', 'LEFT JOIN')
            ->getVal('program_version');

        if (preg_match($this->sw_version_regex[$ecu], $currentSw) == 0)
            return true;
        else
            return false;

    }


    function diagnoseDetails(&$vehicle)
    {

        $teo_status_map_list = $this->diagnosePtr->newQuery('teo_status_map')
            ->orderBy('priority')
            ->get('*');
        $applicable_status = $vehicle['diagnose_status'];
        $did = $vehicle['diagnostic_session_id'];
        $vehicle['processed_diagnose_status'] = $vehicle['diagnose_status'];
        $i = 0;
        while ($i < sizeof($teo_status_map_list)) {
            $teo_status_map = $teo_status_map_list[$i];
            $i ++;
            $newstatus = $teo_status_map['processed_status'];
            if ($applicable_status != $teo_status_map['applicable_status']) {
                continue;
            } else {
                // Process Normal Exception Method
                if ($teo_status_map['exception_method'] == self::DTCS_EXCEPTION_METHOD_NORMAL) {

                    $normal_count = $this->diagnosePtr->newQuery('material_dtcs_revocation_processed')
                        ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
                        ->getVal('count(*)');
                    if ($normal_count == 0) {
                        error_log(date('Y-m-d H:i:sO') . ": " . $_SESSION['sts_username'] . " -  Entering normal_count 0 sessionid=" . $vehicle['diagnostic_session_id'] . "\n", 3, '/var/www/processing_diagnose_status');
                        $this->diagnosePtr->newQuery('material_dtcs_revocation_processed')->query('REFRESH MATERIALIZED VIEW material_dtcs_revocation_processed');
                        $this->diagnosePtr->newQuery('material_log_revocation_processed')->query('REFRESH MATERIALIZED VIEW material_log_revocation_processed');

                        $normal_count = $this->diagnosePtr->newQuery('material_dtcs_revocation_processed')
                            ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
                            ->getVal('count(*)');
                        $normal_count_log = $this->diagnosePtr->newQuery('material_log_revocation_processed')
                            ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
                            ->getVal('count(*)');

                        if ($normal_count == 0 && $normal_count_log == 0) {
                            error_log(date('Y-m-d H:i:sO') . ": " . $_SESSION['sts_username'] . " -  Both normal_counts are = 0 sessionid=" . $vehicle['diagnostic_session_id'] . "\n", 3, "/var/www/processing_diagnose_status");
                            continue; // do not check for changes
                        }
                    }

                    $newquery = $this->diagnosePtr->newQuery('material_dtcs_revocation_processed')
                        ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
                        ->multipleAndwhere('processed_teo_status_map_id', '=', $teo_status_map['map_id'], 'OR', 'processed_teo_status_map_id', 'IS', 'NULL')
                        ->where('rev', 'IS', 'NULL')
                        ->where('rev_variant', 'IS', 'NULL')
                        ->where('rev_vin', 'IS', 'NULL');
                    $manual_sw = [];

                    foreach ($this->enable_manual_flashing_ecus as $ecu) {
                        $software_this_ecu = null;
                        $software_this_ecu = $this->fetchManualSw($vehicle['vehicle_id'], $ecu);

                        if (! empty($software_this_ecu) && $this->currentSwInvalid($ecu, $vehicle)) {
                            $manual_sw[$ecu] = $software_this_ecu;
                            $dtc_codes = $this->ignore_dtcs_sw_error[$ecu];
                            foreach ($dtc_codes as $dtc_code) {
                                $newquery = $newquery->multipleAndWhere('dtc_number', '!=', $dtc_code, 'AND', 'ecu', '=', $ecu);
                            }
                        }
                    }

                    $targetsw_changed = null;
                    if ($vehicle['targetsw_changed']) {
                        $targetsw_changed = $this->diagnosePtr->newQuery('targetsw_changed')
                            ->where('diagnostic_session_id', '=', $did)
                            ->get('ecu');

                        if (! empty($targetsw_changed)) {
                            foreach ($targetsw_changed as $erow) {
                                $ecu = $erow['ecu'];
                                $dtc_codes = $this->ignore_dtcs_sw_error[$ecu];
                                foreach ($dtc_codes as $dtc_code) {
                                    $newquery = $newquery->multipleAndWhere('dtc_number', '!=', $dtc_code, 'AND', 'ecu', '=', $ecu);
                                }
                            }
                        }
                    }

                    $newquery = $newquery->getVal('count(*)');

                    $newlogquery = $this->diagnosePtr->newQuery('material_log_revocation_processed')
                        ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
                        ->multipleAndwhere('processed_teo_status_map_id', '=', $teo_status_map['map_id'], 'OR', 'processed_teo_status_map_id', 'IS', 'NULL')
                        ->where('rev', 'IS', 'NULL')
                        ->where('rev_variant', 'IS', 'NULL')
                        ->where('rev_vin', 'IS', 'NULL');

                    foreach ($manual_sw as $ecu => $sw_version) {
                        $log_names = $this->ignore_log_sw_error[$ecu];
                        foreach ($log_names as $log_name) {
                            $newlogquery = $newlogquery->where('name', '!=', $log_name);
                        }
                    }

                    if (! empty($targetsw_changed)) {
                        foreach ($targetsw_changed as $erow) {
                            $ecu = $erow['ecu'];
                            $log_names = $this->ignore_log_sw_error[$ecu];
                            foreach ($log_names as $log_name) {
                                $newlogquery = $newlogquery->where('name', '!=', $log_name);
                            }
                        }
                    }

                    $newlogquery = $newlogquery->getVal('count(*)');

                    if ($newquery == 0 && $newlogquery == 0) {
                        // exception status. Not in teo status map. But used for vehicles set to PASSED after flashing correct SW version manually
                        if (! empty($manual_sw))
                            $newstatus = 'PASSED**';
                        if (! empty($targetsw_changed))
                            $newstatus = 'PASSED#';
                        error_log(date('Y-m-d H:i:sO') . ": " . $_SESSION['sts_username'] . " -  Calculated processed_diagnose_status from $applicable_status to $newstatus; session_id=" . $vehicle['diagnostic_session_id'] . "\n", 3, '/var/www/processing_diagnose_status');
                        $vehicle['processed_diagnose_status'] = $applicable_status = $newstatus;
                        // reset $i since the status has changed
                        // dont reset.. use priority instead $i=0;
                    }
                }
            }
        }
        $existing_row = $this->newQuery('processed_teo_status')
            ->where('teo_vin', '=', $vehicle['vin'])
            ->where('diagnostic_session_id', '=', $vehicle['diagnostic_session_id'])
            ->getOne('*');

        $insertArray = array(
            'diagnostic_session_id' => $vehicle['diagnostic_session_id'],
            'teo_vin' => $vehicle['vin'],
            'diagnose_status_time' => $vehicle['diagnose_status_time'],
            'diagnose_status' => $vehicle['diagnose_status'],
            'processed_diagnose_status' => $vehicle['processed_diagnose_status'],
            'vehicle_variant_id' => $vehicle['vehicle_variant'],
            'penta_number_id' => $vehicle['penta_number_id']
        );

        if (empty($existing_row)) // happens for new vehicles and for vehicles with a newer TEO run and thus new diagnostic_session_id
        {
            $this->newQuery('processed_teo_status')->insert($insertArray);
            error_log(date('Y-m-d H:i:sO') . ": " . $_SESSION['sts_username'] . " - Inserting into processed_diagnose_status " . implode(',', $insertArray) . "\n", 3, '/var/www/processing_diagnose_status');
        } else if (! empty($existing_row)) {
            $updateArray = array_diff_assoc($insertArray, $existing_row);
            if (! empty($updateArray)) {
                error_log(date('Y-m-d H:i:sO') . ": Updating processed_diagnose_status from " . $existing_row['processed_diagnose_status'] . " to " . $vehicle['processed_diagnose_status'] . "; session_id=" . $vehicle['diagnostic_session_id'] . "\n", 3, '/var/www/processing_diagnose_status');
                $diagnostic_session_id = $insertArray['diagnostic_session_id'];
                $this->newQuery('processed_teo_status')
                    ->where('diagnostic_session_id', '=', $insertArray['diagnostic_session_id'])
                    ->update(array_keys($updateArray), array_values($updateArray));
            }
        } else
            error_log(date('Y-m-d H:i:sO') . ": " . $_SESSION['sts_username'] . " - Making no change to the processed_diagnose_status; session_id=" . $vehicle['diagnostic_session_id'] . "\n", 3, '/var/www/processing_diagnose_status');

    }


    /**
     * used in QSController
     * returns vehicles that have a c2cbox id, where depot_id=0 and finished_status is NULL
     */
    function getEOLVehicles($depot_id = 0)
    {

        $eol_vehicles = $this->newQuery()
            ->where('c2cbox', 'IS', 'NOT NULL')
            ->where('depot_id', '=', $depot_id)
            ->where('vin', 'IS', 'NOT NULL')
            ->multipleAndWhere('vin', 'LIKE', 'WS5B%', 'OR', 'vin', 'LIKE', 'WS5D%')
            ->where('vehicles.finished_status', '=', 'FALSE')
            ->get('vehicle_id,vin,ikz,code,c2cbox,finished_status,qmlocked,special_qs_approval');

        if ($this->diagnosePtr) {
            foreach ($eol_vehicles as &$vehicle) {
                $diagnose = $this->diagnosePtr->newQuery('general')
                    ->where('vin', '=', $vehicle['vin'])
                    ->multipleAndWhere('system_mode', '=', 'EOLT', 'OR', 'system_mode', 'IS', 'NULL')
                    ->orderBy('date', 'DESC')
                    ->getOne('date,status,diagnostic_session_id');

                $vehicle['diagnose_status'] = $diagnose['status'];
                $vehicle['diagnose_status_time'] = $diagnose['date'];
                $vehicle['status_extra_data'] = '';

                if ($vehicle['qmlocked'] == 't')
                    $vehicle['status_extra_data'] = 'Fahrzeug von QM gesperrt!<br><br>';
                $vehicle['ecu_status_count'] = $vehicle['log_status_count'] = 0;

                if ($diagnose['status'] == 'DEFECTIVE') {

                    $ecu_data = $this->diagnosePtr->newQuery('ecu_data')
                        ->where('diagnostic_session_id', '=', $diagnose['diagnostic_session_id'])
                        ->get('ecu,array_to_json(dtcs) as dtcs');

                    $ecu_data_table_rows = array();
                    if (! empty($ecu_data))
                        foreach ($ecu_data as $row) {
                            $dtcs = json_decode($row['dtcs'], true);
                            if (! empty($dtcs))
                                foreach ($dtcs as $dtcs_code) {
                                    // get the correct text for the ecu and diagnostic code here

                                    $diagnose_dtcs_code_text = $this->diagnosePtr->newQuery('dtcs')
                                        ->where('ecu', '=', $row['ecu'])
                                        ->where('dtc_number', '=', $dtcs_code)
                                        ->getVal('text');
                                    if ($diagnose_dtcs_code_text) {
                                        $revocation_codes = $this->diagnosePtr->newQuery('dtcs_revocation')
                                            ->where('dtc', '=', $dtcs_code)
                                            ->where('ecu', '=', $row['ecu'])
                                            ->getVal('array_to_json(id) as codes');

                                        if (! empty($revocation_codes)) {
                                            $codes = json_decode($revocation_codes, true);
                                            $codes = implode('<br>', $codes);
                                        } else {
                                            $codes = '';
                                            $vehicle['ecu_status_count'] ++; // count only if there are no revocation codes for this error, which means no exception
                                        }
                                        $ecu_data_table_rows[] = array(
                                            $dtcs_code,
                                            $diagnose_dtcs_code_text,
                                            $row['ecu'],
                                            $codes
                                        );
                                    } else // no dtcs description
                                        $ecu_data_table_rows[] = array(
                                            $dtcs_code,
                                            '',
                                            $diagnose_dtcs_codes[$key]['ecu'],
                                            ''
                                        );
                                }
                        }

                    if (! empty($ecu_data_table_rows)) {
                        $vehicle['status_extra_data'] .= '<h2>ECU Data</h2>';
                        $table_header = array(
                            array(
                                'headingone' => array(
                                    'DTCS Code',
                                    'Beschreibung',
                                    'ECU',
                                    'Abweicherlaubnis'
                                )
                            )
                        );
                        $ecu_data_table = new DisplayTable(array_merge($table_header, $ecu_data_table_rows));
                        $vehicle['status_extra_data'] .= $ecu_data_table->getContent();
                    }

                    $log_data = $this->diagnosePtr->newQuery('log_data')
                        ->where('diagnostic_session_id', '=', $diagnose['diagnostic_session_id'])
                        ->where('passed', '=', 'f')
                        ->get('name,comment,passed');

                    if (! empty($log_data)) {
                        foreach ($log_data as &$single_log_data) {
                            $revocation_codes = $this->diagnosePtr->newQuery('log_revocation')
                                ->where('name', '=', $single_log_data['name'])
                                ->getVal('array_to_json(id) as codes');

                            if (! empty($revocation_codes)) {
                                $codes = json_decode($revocation_codes, true);
                                $codes = implode('<br>', $codes);
                                $single_log_data['codes'] = $codes;
                            } else {
                                $codes = '';
                                $vehicle['log_status_count'] ++; // count only if there are no revocation codes for this error, which means this vehicle cannot be passed
                                $single_log_data['codes'] = '';
                            }
                        }
                    }

                    if (! empty($log_data)) {
                        $vehicle['status_extra_data'] .= '<h2>Log Data</h2>';
                        $table_header = array(
                            array(
                                'headingone' => array(
                                    'Name',
                                    'Kommentar',
                                    'Passed?',
                                    'Abweicherlaubnis'
                                )
                            )
                        );
                        $log_data_table = new DisplayTable(array_merge($table_header, $log_data));
                        $vehicle['status_extra_data'] .= $log_data_table->getContent();
                    }

                    if ($vehicle['diagnose_status'] == 'DEFECTIVE' && $vehicle['ecu_status_count'] == 0 && $vehicle['log_status_count'] == 0) // && !empty($vehicle['status_extra_data'])) allow passing vehicles even if they have no ecu_data and log_data
                    {
                        $vehicle['diagnose_status'] = 'PASSED*';
                    }
                }
            }
        }

        // cannot sort after withoutc2c since we want to keep the vehicles with c2c and withouit c2c separate
        usort($eol_vehicles, function ($a, $b) {
            if ($a['diagnose_status'] == $b['diagnose_status'])
                return 0; // if same, do nothing
            else if ($a['diagnose_status'] == 'PASSED')
                return - 1; // if first element is PASSED, then it gets higher priority, so -1
            else if ($a['diagnose_status'] == 'PASSED*' && $b['diagnose_status'] != 'PASSED')
                return - 1; // if first element is PASSED* and if second element is not PASSED, then first element gets higher priority, so -1
            else if ($a['diagnose_status'] == 'PASSED*' && $b['diagnose_status'] == 'PASSED')
                return 1; // if first element is PASSED* and if second element is not PASSED, then secondelement gets higher priority, so 1
            else if (empty($a['diagnose_status']))
                return 1; // if first element is empty, then it gets lower priority, so 1
            else if (empty($b['diagnose_status']))
                return - 1; // if second element is empty, then it gets higher priority than defective, so -1
            else if ($a['diagnose_status'] == 'DEFECTIVE')
                return 1;
        });

        $result = $eol_vehicles;
        return $result;

    }


    function getStsPoolVehicles()
    {

        $depot_id = $this->newQuery('depots')
            ->where('name', 'LIKE', 'Sts_Pool')
            ->getVal('depot_id');
        return $this->getEOLVehicles($depot_id);

    }


    /**
     * previously getCountStsPoolFinishedVehicles...
     * but changed to vehiclesforvariant.. since we can draw from Sts_Pool for both variants
     *
     * @param
     *            integer internal vehicle variant value $variant_value
     */
    function getCountStsPoolVehiclesForVariant($variant_value)
    {

        $depot_id = $this->newQuery('depots')
            ->where('name', 'LIKE', 'Sts_Pool')
            ->getVal('depot_id');
        return $this->newQuery()
            ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
            ->where('depot_id', '=', $depot_id)
            ->where('vin', 'IS', 'NOT NULL')
            ->where('vehicles_sales.vehicle_variant', '=', $variant_value)
            ->getVal('count(vehicles.vehicle_id)');

    }


    function getDefaultVehicleVariant($vehicle_variants)
    {

    }


    /**
     * function called from checkPossibleCombos
     *
     * @param integer $restriction_id
     * @param
     *            array unknown $sopVariants
     * @param array $pvsVariants
     * @return number[]
     */
    function newGetAssignedVehiclesByVariantCntForRestriction($restriction_id, $sopVariants, $pvsVariants)
    {

        $restrictions = array(
            $restriction_id
        );
        $result = $this->newQuery('restrictions')
            ->where('parent_restriction_id', '=', $restriction_id)
            ->get('restriction_id');
        if (! empty($result)) {
            $newsubres = array_column($result, 'restriction_id');
            // merge the restriction_id with its children
            $restrictions = array_merge($restrictions, $newsubres);
            $restrictions = array_unique($restrictions, SORT_NUMERIC);
        }

        $restrictionsvehicles = $this->newQuery('restrictions')
            ->join('stations', 'stations.restriction_id=restrictions.restriction_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
            ->where('restrictions.restriction_id', 'IN', $restrictions)
            ->where('vehicles_sales.vehicle_variant', 'IN', $sopVariants)
            ->groupBy('restrictions.restriction_id,restrictions.power')
            ->get('restrictions.restriction_id,restrictions.power,json_agg(vehicles.vehicle_id) as vids');

        $pvscnt = $this->newQuery('restrictions')
            ->join('stations', 'stations.restriction_id=restrictions.restriction_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
            ->where('restrictions.restriction_id', 'IN', $restrictions)
            ->where('vehicles_sales.vehicle_variant', 'NOT IN', $sopVariants)
            ->getVal('count(vehicles.vehicle_id) as pvscnt');

        $restriction_power = $this->newQuery('restrictions')
            ->where('restriction_id', '=', $restriction_id)
            ->getVal('power');

        if (empty($pvscnt))
            $pvscnt = 0;

        $resVehicles = array();
        $cnteven = $cntodd = $controllable_sop = 0;

        if (! empty($restrictionsvehicles)) {

            foreach ($restrictionsvehicles as $restriction) {

                $resVehicles = array_merge($resVehicles, json_decode($restriction['vids'], true));
            }
            foreach ($resVehicles as $vehicle_id) {
                $vehicle = $this->newQuery('vehicles')
                    ->where('vehicle_id', '=', $vehicle_id)
                    ->getOne('fallback_power_odd,fallback_power_even', 'charger_controllable');

                if (isset($vehicle['charger_controllable']) && $vehicle['charger_controllable'])
                    $controllable_sop ++;
                else if ($vehicle['fallback_power_even'])
                    $cnteven ++;
                else if ($vehicle['fallback_power_odd'])
                    $cntodd ++;
            }
        }
        $nSOP = $cnteven + $cntodd;
        $nPVS = $pvscnt + $controllable_sop;

        return array(
            'pvs' => $nPVS,
            'sop' => $nSOP
        );

    }


    function getCountFinishedEOLVehicleVariant($filter = '')
    {

        $sts_pool_id = $this->newQuery('depots')
            ->where('name', 'LIKE', 'Sts_Pool')
            ->getVal('depots.depot_id');
        if ($filter == 'production')
            $depot_ids = array(
                0
            );
        else if ($filter == 'pool') {
            if ($sts_pool_id)
                $depot_ids = array(
                    $sts_pool_id
                );
        } else {
            if ($sts_pool_id)
                $depot_ids = array(
                    0,
                    $sts_pool_id
                );
            else
                $depot_ids = array(
                    0
                );
        }

        $result = $this->newQuery()
            ->where('vehicles.finished_status', '=', 'TRUE')
            ->where('vehicles.depot_id', 'IN', $depot_ids)
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
            ->groupBy('vehicles_sales.vehicle_variant')
            ->get('vehicles_sales.vehicle_variant,count(vehicles.vin) as vcnt');

        return $result;

    }


    function getFinishedEOLVehicleVariants($vehicle_variant = null, $filter = '', $clientfilter = 'post', $color = null, $aufbau = null, $charger_control_info = false, $exclude_vehicles = false)
    {

        $sts_pool_id = $this->newQuery('depots')
            ->where('name', 'LIKE', 'Sts_Pool')
            ->getVal('depots.depot_id');

        if ($filter == 'production')
            $depot_ids = array(
                0
            );
        else if ($filter == 'pool') {
            if ($sts_pool_id)
                $depot_ids = array(
                    $sts_pool_id
                );
        } else if (is_numeric($filter)) {
            $depot_id = $this->newQuery('depots')
                ->where('division_id', '=', $filter)
                ->getVal('depot_id');
            $depot_ids = array(
                $depot_id
            );
        } else {
            if ($sts_pool_id)
                $depot_ids = array(
                    0,
                    $sts_pool_id
                );
            else
                $depot_ids = array(
                    0
                );
        }

        $result = $this->newQuery();
        if (empty($vehicle_variant)) {
            $result->where('vehicles_sales.vehicle_variant', 'IN', array_merge($this->pvsVariants, $this->sopVariants));
        } else if (is_array($vehicle_variant))
            $result->where('vehicles_sales.vehicle_variant', 'IN', $vehicle_variant);
        else
            $result->where('vehicles_sales.vehicle_variant', '=', $vehicle_variant);

        if ($clientfilter == 'thirdparty') {
            $result->multipleAndWhere('is_valid_ikz(vehicles.ikz)', '=', 'f', 'OR', 'vehicles.ikz', 'IS', 'NULL');
        } else if ($clientfilter == 'post') {
            $result->where('is_valid_ikz(vehicles.ikz)', '=', 't');
        }

        if ($aufbau) {
            $result->where('substring(vehicles.vin from 8 for 1)', '=', $aufbau);
        }

        if ($exclude_vehicles) {
            $exclude_vehicles = explode(',', $exclude_vehicles);
            $result->where('vehicles.vehicle_id', 'NOT IN ', $exclude_vehicles);
        }

        $result = $result->where('vehicles.finished_status', '=', 'TRUE')
            ->where('vehicles.depot_id', 'IN', $depot_ids)
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
            ->orderBy('vehicles.vehicle_id');

        if ($charger_control_info) {
            return $result->get('vehicles.vin,vehicles.code,vehicles.vehicle_id,vehicles.charger_controllable');
        } else
            return $result->get('vehicles.vin,vehicles.code,vehicles.vehicle_id');

    }


    function getFinishedEOLVehicleVariantsWithChargerInfo($vehicle_variant, $filter, $clientfilter = 'post')
    {

        // $clientfilter default used to be null, fixed it to be post so only post vehicles are selected if the
        // parameter is empty
        return $this->getFinishedEOLVehicleVariants($vehicle_variant, $filter, $clientfilter, null, null, true);

    }


    function getFinishedVehiclesChargerInfoExclude($vehicle_variant, $filter, $clientfilter = 'post', $exclude_vehicles = false)
    {

        return $this->getFinishedEOLVehicleVariants($vehicle_variant, $filter, $clientfilter, null, null, true, $exclude_vehicles);

    }


    function getFinishedEOLVehicleVariant($vehicle_variant, $vintype = null, $filter = '')
    {

        $result = $this->getFinishedEOLVehicleVariants($vehicle_variant, $filter);
        if (! isset($vintype)) {
            $vins = array_column($result, 'vin');
            $even_vins = $odd_vins = 0;
            foreach ($vins as $this_vin) {
                $lastTwo = (int) substr($this_vin, - 2);
                if ($lastTwo % 2 == 0)
                    $even_vins ++;
                else
                    $odd_vins ++;
            }

            if ($odd_vins > $even_vins)
                $vintype = 'odd';
            else
                $vintype = 'even';
        }

        if (! empty($result)) {
            foreach ($result as $vehicle) {
                $lastTwo = (int) substr($vehicle['vin'], - 2);
                if ($lastTwo % 2 == 0 && $vintype == 'even')
                    return $vehicle;
                else if ($lastTwo % 2 != 0 && $vintype == 'odd')
                    return $vehicle;
            }
        }

    }


    /**
     * *
     * insertNewDistrict
     * used here
     *
     * @param integer $vehicle_id
     * @param integer $depotid
     */
    function insertNewDistrict($vehicle_id, $depotid)
    {

        $defaultDistrictName = 'Voreingestellter Bezirk';
        $defaultSOC = 100;
        $defaultTime = '08:00:00';
        $days = array(
            'mon',
            'tue',
            'wed',
            'thu',
            'fri',
            'sat',
            'sun'
        );
        $insertData = array(
            'depot_id' => $depotid,
            'name' => $defaultDistrictName
        );

        foreach ($days as $day) {
            $insertData['vehicle_' . $day] = $vehicle_id;
            $insertData['departure_' . $day] = $defaultTime;
            $insertData['required_soc_' . $day] = $defaultSOC;
        }

        return $this->newQuery('districts')->insert($insertData);

    }


    function assignVehicleToStation($vehicle_id, $station_id, $zsp, $delivery_week, $cost_center)
    {

        $production_depots = $this->newQuery('depots')
            ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
            ->where('divisions.production_location', '=', 't')
            ->get('depot_id');
        $production_depot_ids = array_column($production_depots, 'depot_id');
        $current_depot_id = $this->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('depot_id');
        $current_production_location = $this->newQuery('vehicles_sales')
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('production_location');

        if (in_array($current_depot_id, $production_depot_ids)) {
            $production_location = $current_depot_id;
        } else if (! empty($current_production_location) || $current_production_location == 0) {
            $production_location = $current_production_location;
        } else {
            $production_location = NULL;
        }

        $updateResult = $this->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->update(array(
            'station_id',
            'depot_id'
        ), array(
            $station_id,
            $zsp
        ));
        /* get district */
        $days = array(
            'mon',
            'tue',
            'wed',
            'thu',
            'fri',
            'sat',
            'sun'
        );

        $districts = $this->newQuery('districts');

        foreach ($days as $key => $day) {
            if ($key == 0) // first element we want it to be just WHERE
                $districts->where('vehicle_' . $day, '=', $vehicle_id);
            else // other elements we want it to be OR WHERE
                $districts->orWhere('vehicle_' . $day, '=', $vehicle_id);
        }

        $district = $districts->getOne('district_id');

        if (! empty($district))
            $this->newQuery('districts')
                ->where('district_id', '=', $district['district_id'])
                ->update(array(
                'depot_id'
            ), array(
                $zsp
            ));
        else
            $this->insertNewDistrict($vehicle_id, $zsp);

        if ($updateResult)
            return $this->newQuery('vehicles_sales')
                ->where('vehicle_id', '=', $vehicle_id)
                ->update(array(
                'delivery_week',
                'kostenstelle',
                'production_location'
            ), array(
                $delivery_week,
                $cost_center,
                $production_location
            ));

        else
            return false;

    }


    /**
     * used in Aftersales controller as well as QSController.
     * gets the finish status of a vehicle
     *
     * @param integer $vehicle_id
     */
    function getQSFertig($vehicle_id)
    {

        return $this->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('finished_status');

    }


    function getQmVehicleLock($vehicle_id)
    {

        return $this->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('qmlocked');

    }


    function setQmVehicleLock($vehicle_id, $status)
    {

        $updateresult = $this->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->update(array(
            'qmlocked'
        ), array(
            $status
        ));
        return $updateresult;

    }


    /**
     * used in Aftersales controller as well as QSController
     *
     * @param integer $vehicle_id
     * @param boolean $status
     * @param boolean $poolVehicle
     * @param string $qs_user
     */
    function setQSFertig($vehicle_id, $status, $poolVehicle = false, $qs_user = null)
    {

        $vehiclevin = $this->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('vin');
        $vehicle_variant = $this->newQuery('vehicles_sales')
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('vehicle_variant');

        if (in_array($vehicle_variant, $this->sopVariants)) {
            $lastTwo = (int) substr($vehiclevin, - 2);
            if ($lastTwo % 2 == 0) {
                $fallback_power_even = 3000;
                $fallback_power_odd = 0;
            } else if ($lastTwo % 2 == 1) {
                $fallback_power_even = 0;
                $fallback_power_odd = 3000;
            }
            $updateresult = $this->newQuery()
                ->where('vehicle_id', '=', $vehicle_id)
                ->update(array(
                'finished_status',
                'fallback_power_even',
                'fallback_power_odd'
            ), array(
                $status,
                $fallback_power_even,
                $fallback_power_odd
            ));
        } else
            $updateresult = $this->newQuery()
                ->where('vehicle_id', '=', $vehicle_id)
                ->update(array(
                'finished_status'
            ), array(
                $status
            ));

        if ($poolVehicle == true)
            $qs_user_update = $this->newQuery('vehicles_sales')
                ->where('vehicle_id', '=', $vehicle_id)
                ->update(array(
                'production_date',
                'qs_user'
            ), array(
                date('Y-m-d H:i:sO'),
                '-1'
            ));
        else {
            if (isset($qs_user))
                $qs_user_update = $this->newQuery('vehicles_sales')
                    ->where('vehicle_id', '=', $vehicle_id)
                    ->update(array(
                    'production_date',
                    'qs_user'
                ), array(
                    date('Y-m-d H:i:sO'),
                    $qs_user
                ));
        }
        // do not update the production plan if this is a pool vehicle which has already been produced
        if ($updateresult && $poolVehicle === false) {
            $vehicle = $this->newQuery()
                ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
                ->where('vehicles.vehicle_id', '=', $vehicle_id)
                ->getOne('vehicles_sales.vehicle_variant');

            $productionPlan = $this->newQuery('production_plan')
                ->where('variant_value', '=', $vehicle['vehicle_variant'])
                ->where('production_week', '=', 'kw' . date('W'))
                ->getOne('production_plan_id,vehicles_produced');

            if (! empty($productionPlan)) {
                if (empty($productionPlan['vehicles_produced']))
                    $productionPlan['vehicles_produced'] = 0;

                $productionPlan['vehicles_produced'] ++;

                $this->newQuery('production_plan')
                    ->where('production_plan_id', '=', $productionPlan['production_plan_id'])
                    ->update(array(
                    'vehicles_produced'
                ), array(
                    $productionPlan['vehicles_produced']
                ));
            }

            if ($updateresult)
                return $vehiclevin;
        }

    }


    function ajaxGetVehiclesToDeliver($page, $size, $fcol, $scol, $vehicle_ids_include = null, $vehicle_ids_exclude = null)
    {

        $result = $this->newQuery()
            ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->join('stations', 'vehicles.station_id=stations.station_id', 'INNER JOIN');

        // used for sorting and filtering
        $headers = explode(',', 'vin,code,zsp,delivery_week,delivery_date,production_location,select_vehicle,delivery_status,vehicle_reset,vehicle_exchange');

        if (! empty($fcol)) {
            foreach ($fcol as $key => $val) {
                if ($headers[$key] == 'zsp') {
                    $result->multipleAndWhere('depots.name', 'ILIKE', '%' . $val . '%', 'OR', 'depots.dp_depot_id::text', 'ILIKE', '%' . $val . '%');
                } else
                    $result->where($headers[$key], 'ILIKE', '%' . $val . '%');
            }
        }

        if (! empty($vehicle_ids_include)) {
            $result->where('vehicle_id', 'IN', $vehicle_ids_include);
        } else if (! empty($vehicle_ids_exclude)) {
            $result->where('vehicle_id', 'NOT IN', $vehicle_ids_exclude);
        }

        if (! empty($scol)) {

            foreach ($scol as $key => $val) {
                if ($val == 1)
                    $sortorder = 'DESC';
                else
                    $sortorder = 'ASC';
                if ($headers[$key] == 'zsp') {
                    $result->orderBy('depots.name', $sortorder);
                } else {
                    $result->orderBy($headers[$key], $sortorder);
                }
            }
        } else {
            $result->orderBy('vehicles_sales.production_date', 'DESC NULLS LAST')
                ->orderBy('concat(delivery_date,\' \',delivery_week)', 'DESC NULLS LAST')
                ->orderBy('vehicles_sales.vehicle_id', 'DESC')
                ->orderBy('depots.depot_id', 'DESC');
        }
        $result = $result->where('vehicles_sales.delivery_week', 'IS', 'NOT NULL')
            ->where('depots.name', 'NOT LIKE', '%Dritt%')
            ->where('vehicles_sales.delivery_week', '!=', '');

        if (empty($vehicle_ids_include) && ! empty($size)) {
            $result->offset($page * $size)->limit($size);
        }

        return $result->get('depots.dp_depot_id,depots.name as depot_name,
				vehicles_sales.vehicle_variant,vehicles_sales.delivery_week,vehicles_sales.delivery_date,vehicles_sales.vehicle_id,vehicles_sales.delivery_status,
				vehicles.code,vehicles.vin,vehicles_sales.vehicle_variant,vehicles_sales.production_date,vehicles_sales.qs_user,vehicles_sales.production_location');

    }


    function getVehiclesToDeliver($yearmonth = '', $delivered_filter = '', $delivered_from = false)
    {

        if ($yearmonth)
            $month = date('m', strtotime($yearmonth));
        else
            $month = date('m');
        $weeks = $this->getWeeksFromYearMonth(date('Y-' . $month . '-01'));

        $result = $this->newQuery()
            ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN');
        if (! empty($delivered_filter) && $delivered_filter == 'delivered') {
            $result->where('vehicles_sales.delivery_status', '=', 't');
        } else {
            $result->multipleAndWhere('vehicles_sales.delivery_status', '=', 'f', 'OR', 'vehicles_sales.delivery_status', 'IS', 'NULL');
        }

        if ($delivered_from !== false) {
            $result->where('depot_id', '=', $delivered_from);
        }
        return $result->where('vehicles_sales.delivery_week', 'IN', $weeks)
            ->orderBy('vehicles_sales.delivery_status,vehicles_sales.delivery_date,depots.name')
            ->get('depots.dp_depot_id,depots.name as depot_name,
				vehicles_sales.vehicle_variant,vehicles_sales.delivery_week,vehicles_sales.delivery_date,vehicles_sales.vehicle_id,vehicles_sales.delivery_status,
				vehicles.code,vehicles.vin,vehicles_sales.vehicle_variant,vehicles_sales.production_date,vehicles_sales.qs_user');

    }


    function getAllVehicleIdsinRange($db_col, $startval, $endval)
    {

        if (! empty($endval)) {
            $vehicles = $this->newQuery()
                ->where($db_col, ">=", $startval)
                ->where($db_col, "<=", $endval)
                ->orderBy($db_col, 'ASC')
                ->get('vehicle_id');
        } else if ($db_col == 'vorhaben') {
            $vehicles = $this->newQuery('vehicles_sales')
                ->where("vorhaben", "=", $startval)
                ->get('vehicle_id');
        }

        return $vehicles;

    }


    function getDepotsForTransferProto($db_col, $startval)
    {

        if ($db_col == 'vehicles.vehicle_id' && is_array($startval)) {
            $start_vehicle_id = $startval;
            return $this->newQuery()
                ->where('vehicles.vehicle_id', 'IN', $start_vehicle_id)
                ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
                ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
                ->join('zspl', 'zspl.zspl_id=depots.zspl_id', 'INNER JOIN')
                ->groupBy('depots.depot_id,depots.name,depots.dp_depot_id,divisions.name,divisions.division_id,zspl.zspl_id,zspl.name')
                ->orderBy('divisions.division_id,depots.depot_id')
                ->get('depots.depot_id,depots.name,depots.dp_depot_id,depots.street,depots.housenr,depots.place,depots.postcode,
					divisions.name as divname,divisions.division_id,zspl.zspl_id,zspl.name as zname,depots.penta_folge_id');
        }

    }

    function getDetailsForTransferProto($db_col, $startval, $depot_id, $workshop_delivery)
    {

        if ($db_col == 'vehicles.vehicle_id' && is_array($startval)) // add else condition to expand this function for when the user selects
                                                                      // vehicles by the code or vin numbers
        {
            $start_vehicle_id = $startval;
            $result = $this->newQuery()
                ->where('vehicles.vehicle_id', 'IN', $start_vehicle_id)
                ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
                ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN');
            $extra_attributes = '';
            if ($workshop_delivery === false) {
                $result = $result->join('stations', 'stations.station_id=vehicles.station_id', 'INNER JOIN');
                $extra_attributes = ',stations.name as sname';
            }
            $result = $result->orderBy('vehicles_sales.qs_user,vehicles_sales.production_date', 'ASC')
                ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
                ->where("depots.depot_id", "=", $depot_id)
                ->get('vehicles.vehicle_id,vehicles.ikz,vehicles.vin,
					vehicles_sales.vorhaben,vehicles_sales.production_location,vehicles_sales.vehicle_variant,vehicles_sales.delivery_date,vehicles.code,divisions.cost_center,divisions.name,depots.dp_depot_id,
					vehicles_sales.production_date,vehicles_sales.qs_user' . $extra_attributes);
            return $result;
        }

    }


    function getVinFromId($id)
    {

        return $this->newQuery()
            ->where('vehicle_id', '=', $id)
            ->getVal('vin');

    }


    function getCodeFromId($id)
    {

        $whereParams = array();
        $whereParams[] = array(
            'colname' => 'vehicle_id',
            'whereop' => '=',
            'colval' => $id
        );

        $result = $this->dataSrcPtr->selectAll('vehicles', array(
            'code'
        ), $whereParams);
        return $result[0]['code'];

    }


    function getSinglePhaseVariants()
    {

    }


    /**
     * returns vehicles assigned to a depot
     * returns vehicles if they are assigned to a depot, also if they are not assigned to a station
     *
     * @param integer $zsp
     */
    function getVehiclesStationsForDepots($zsp, $finished_status = '') // @todo 2016-09-02 wanted to edit something here
    {

        $result = $this->newQuery();
        if (is_array($zsp))
            $result->where('vehicles.depot_id', 'IN', $zsp);
        else
            $result->where('vehicles.depot_id', '=', $zsp);

        if ($finished_status === true) {
            $result->where('vehicles.finished_status', '=', 't')->orderBy('vehicles.vin');
        } else
            $result->orderBy('stations.name');

        return $result->join('stations', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->get('depots.name as dname,vehicles.vin,vehicles.vehicle_id,vehicles.depot_id,vehicles.code,vehicles.late_charging,vehicles.late_charging_time,
								stations.station_id,stations.name as sname,stations.restriction_id,stations.restriction_id2,stations.restriction_id3');

    }


    /**
     * returns vehicles assigned to a depot
     * returns vehicles if they are assigned to a depot, also if they are not assigned to a station
     *
     * @param integer $zsp
     */
    function getVehiclesVariantsStationsForDepots($zsp, $finished_status = '') // @todo 2016-09-02 wanted to edit something here
    {

        $qry = $this->newQuery();
        $qry = $qry->join('stations', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('vehicles_sales', 'using vehicle_id');

        if (is_array($zsp))
            $qry->where('vehicles.depot_id', 'IN', $zsp);
        else
            $qry->where('vehicles.depot_id', '=', $zsp);

        if ($finished_status === true) {
            $qry->where('vehicles.finished_status', '=', 't')->orderBy('vehicles.vin');
        } else
            $qry->orderBy('stations.name');

        return $qry->get('depots.name as dname,
            vehicles.vin,
            vehicles.vehicle_id,
            vehicles.depot_id,
            vehicles.code,
            vehicles.late_charging,
            vehicles.late_charging_time,
            vehicles.replacement_vehicles,
            vehicles.three_phase_charger,
            stations.station_id,
            stations.name as sname,
            stations.restriction_id,
            stations.restriction_id2,
            stations.restriction_id3,
            vehicles_sales.vehicle_variant');

    }


    /**
     * returns all vehicles in a depot
     * used by sales in the depotassign/ZSP Zuordnung function
     *
     * @param array $selectCols
     * @param mixed $whereStmtParams
     */
    function getVehiclesDepots($selectCols = null, $whereStmtParams = null)
    {

        return $this->getWhereJoin($selectCols, $whereStmtParams, array(
            array(
                'vehicles.vehicle_id',
                'DESC'
            )
        ), array(
            array(
                'FULL OUTER JOIN',
                'vehicles_sales',
                array(
                    array(
                        'vehicles.vehicle_id',
                        'vehicles_sales.vehicle_id'
                    )
                )
            ),
            array(
                'INNER JOIN',
                'depots',
                array(
                    array(
                        'vehicles.depot_id',
                        'depots.depot_id'
                    )
                )
            )
        ));

    }


    /**
     * *
     *
     * @param string $colName
     * @param string $colVal
     * @param boolean $assignedVehiclesOnly
     * @return integer
     */
    function getVehiclesCnt($colName, $colVal, $assignedVehiclesOnly = false)
    {

        $pvsQuery = $this->newQuery()
            ->where('depots.depot_id', '!=', 0)
            ->where('depots.' . $colName . '_id', '=', $colVal)
            ->multipleAndWhere('vehicles_sales.vehicle_variant', 'NOT IN', $this->sopVariants, 'OR', 'vehicles_sales.vehicle_variant', 'IS', 'NULL')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN');

        if ($assignedVehiclesOnly === true) {
            $pvsQuery->where('station_id', ' IS ', 'NOT NULL');
            $pvsQuery->where('station_id', '!=', 0);
        }

        $pvsCnt = (int) $pvsQuery->getVal('count(vehicles.vehicle_id) as vcount');

        $sopQuery = $this->newQuery()
            ->where('depots.depot_id', '!=', 0)
            ->where('depots.' . $colName . '_id', '=', $colVal)
            ->where('vehicles_sales.vehicle_variant', 'IN', $this->sopVariants)
            ->multipleAndWhere('vehicles_sales.delivery_date', '<=', date('Y-m-j'), 'OR', 'vehicles_sales.delivery_date', 'IS', 'NULL')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN');

        if ($assignedVehiclesOnly === true) {
            $sopQuery->where('station_id', ' IS ', 'NOT NULL');
            $sopQuery->where('station_id', '!=', 0);
        }

        $sopCnt = (int) $sopQuery->getVal('count(vehicles.vehicle_id) as vcount');

        return $pvsCnt + $sopCnt;

    }


    /**
     * *
     *
     * @param string $colName
     * @param string $colVal
     * @return integer
     */
    function getAssignedVehiclesCnt($colName, $colVal, $replacement_vehicles = false)
    {

        $qry = $this->newQuery()
            ->join('depots', 'using(depot_id)')
            ->join('vehicles_sales', 'using (vehicle_id)')
            ->where('depots.depot_id', '!=', 0)
            ->where("depots.{$colName}_id", '=', $colVal);

        if ($replacement_vehicles)
            $qry = $qry->multipleAndWhere('station_id', 'IS', 'NULL', 'OR', 'station_id', '=', 0);
        else
            $qry = $qry->multipleAndWhere('station_id', 'IS', 'NOT NULL', 'AND', 'station_id', '!=', 0);

        return $qry->getVal('count(*)');

    }


    /**
     * called from CronController for automatic report generation of all vehicles
     */
    function getVehiclesForReport()
    {

        $pvsQuery = $this->newQuery()
            ->where('depots.depot_id', '!=', 0)
            ->multipleAndWhere('depots.dp_depot_id', 'IS', 'NOT NULL', 'OR', 'depots.name', 'ILIKE', 'Fleet_Pool%')
            ->multipleAndWhere('vehicles_sales.vehicle_variant', 'NOT IN', $this->sopVariants, 'OR', 'vehicles_sales.vehicle_variant', 'IS', 'NULL')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
            ->orderBy('vin', 'DESC')
            ->get('code,vehicles.ikz,vin,depots.name,depots.dp_depot_id');

        $sopQuery = $this->newQuery()
            ->where('depots.depot_id', '!=', 0)
            ->multipleAndWhere('depots.dp_depot_id', 'IS', 'NOT NULL', 'OR', 'depots.name', 'ILIKE', 'Fleet_Pool%')
            ->where('vehicles_sales.vehicle_variant', 'IN', $this->sopVariants)
            ->where('vehicles_sales.delivery_date', '<=', date('Y-m-j'))
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
            ->orderBy('vin', 'DESC')
            ->get('code,vehicles.ikz,vin,depots.name,depots.dp_depot_id');

        return array_merge($sopQuery, $pvsQuery);

    }


    /**
     * called form SalesController when displaying the vehicles delivered for a division in the showDivisionsDeliveryPlan function
     *
     * @param string $kweek
     * @param integer $division_id
     * @return array of vehicle_ids
     */
    function vehiclesDeliveredThisWeekForDiv($kweek, $division_id, $delivery_year, $variant_value, $return_vehicle_details = false)
    {

        $deliveryToDivisions = $this->newQuery('delivery_to_divisions')
            ->where('delivery_week', '=', $kweek)
            ->where('division_id', '=', $division_id)
            ->where('delivery_year', '=', $delivery_year);
        if ($variant_value !== null)
            $deliveryToDivisions = $deliveryToDivisions->where('variant_value', '=', $variant_value);

        $deliveryToDivisions = $deliveryToDivisions->getVal('vehicles_delivered');

        if (! empty($deliveryToDivisions))
            $vehicle_ids = unserialize($deliveryToDivisions);
        else
            $vehicle_ids = array();

        // if there are no vehicles delivered this week, then return the empty array
        // or if return_vehicle_details is set to false, then just return the array of vehicle_ids
        if (empty($vehicle_ids) || $return_vehicle_details === false)
            return $vehicle_ids;
        // if vehicle_ids is not empty and if $return_vehicle_details is set to true, then return the vehicledetails
        else {

            return $this->newQuery('')
                ->where('vehicle_id', 'IN', $vehicle_ids)
                ->join('stations', 'stations.station_id=vehicles.station_id', 'INNER JOIN')
                ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
                ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
                ->orderBy('depots.name')
                ->get("depots.name as dname,depots.dp_depot_id,vehicles.code,stations.name as sname,(vehicles_sales.delivery_date AT TIME ZONE 'CEST')::date");
        }

    }


    function getOffline($vid)
    {

        // return $this->dataSrcPtr->specialSqlPrepare('
        //     SELECT online
        //     FROM measurements_online_status
        //     WHERE vehicle_id=$1 ORDER BY timestamp DESC LIMIT 1', array(
        //     $vid
        // ));

        return $this->dataSrcPtr->specialSqlPrepare('
        select
            BOOL(value::int) as online
        from
            timeseries_latest_data
        where
            vehicle_id = $1
            and
            signal_id = 1', array(
            $vid
        ));
        
    }


    function getC2CBoxIdFromId($id)
    {

        $whereParams = array();
        $whereParams[] = array(
            'colname' => 'vehicle_id',
            'whereop' => '=',
            'colval' => $id
        );

        $result = $this->dataSrcPtr->selectAll('vehicles', '', $whereParams);

        return $result[0]['c2cbox'];

    }


    function getSpecialSql($specialSql)
    {

        $result = $this->dataSrcPtr->specialSql($specialSql);
        return $result;

    }


    function addTemp($insertVals)
    {

        $id = $this->dataSrcPtr->insert('vehicles_temp', $insertVals);
        return $id;

    }


    function addMultipleVehicles($vehicles)
    {

        $variant_set = $this->newQuery('vehicle_variants')
            ->where('vehicle_variant_id', '=', $vehicles[0]['vehicle_variant_wc'])
            ->getOne('charger_controllable, battery');
        // var_dump($variant_set);
        if ($charger_controllable == null)
            $charger_controllable = 'f';
        else
        $charger_controllable = $variant_set['charger_controllable'];

        $usable_battery_capacity = 15700;
        switch ($variant_set['battery']) {
            case 'V5':
            case 'V6/1':
                $usable_battery_capacity = 20480;
                break;
            case 'SDA':
                $usable_battery_capacity = 30000;
                break;
            case 'V6/2':
                $usable_battery_capacity = 40000;
                break;
        }

        $insertCols = array(
            'vin',
            'usable_battery_capacity',
            'depot_id',
            'fallback_power_even',
            'fallback_power_odd',
            'charger_controllable',
            'color_id',
            'vehicle_variant',
            'penta_kennwort',
            'penta_number_id',
            'sub_vehicle_configuration_id',
            'penta_variant_id',
            'penta_keyword'
        );
        // vehicle_variant in this table refers to windchill variant.. but it is still called vehicle_variant in the table!
        $currentvehicle = array();

        // store vehicle_options and vins here
        $vehicle_options = array();

        // store vehicle_options and vehicle_ids here
        $insert_vehicle_options = array();

        foreach ($vehicles as $vehicle) {

            $currentvehicle[0] = $vehicle['vin'];
            $currentvehicle[1] = $usable_battery_capacity;
            $currentvehicle[2] = $vehicle['depot_id'];
            // 2017-09-06 vehicle_variant related to the vehicle_variant table in vehicles_sales
            if (toBool($charger_controllable)) {
                $currentvehicle[3] = $currentvehicle[4] = 1500;
            } else {
                $lastTwo = (int) substr($vehicle['vin'], - 2);
                if ($lastTwo % 2 == 0) {
                    $fallback_power_even = 3000;
                    $fallback_power_odd = 0;
                } else if ($lastTwo % 2 == 1) {
                    $fallback_power_even = 0;
                    $fallback_power_odd = 3000;
                }

                $currentvehicle[3] = $fallback_power_even;
                $currentvehicle[4] = $fallback_power_odd;
            }
            $currentvehicle[5] = $charger_controllable;

            $currentvehicle[6] = $vehicle['color_id'];
            $currentvehicle[7] = $vehicle['vehicle_variant_id'];
            $currentvehicle[8] = $vehicle['penta_kennwort'];
            $currentvehicle[9] = $vehicle['penta_number_id']; // $vehicle['penta_variant_id']; 
            $currentvehicle[10] = $vehicle['sub_vehicle_configuration_id'];
            $currentvehicle[11] = $vehicle['penta_variant_id'];
            $currentvehicle[12] = $vehicle['penta_kennwort'];

            $insertVals[] = $currentvehicle;
        }

        $numrows = $this->dataSrcPtr->insertMultiple('vehicles', $insertCols, $insertVals);

        if (! is_numeric($numrows))
            return $numrows;

        $vehiclevins = array_column($vehicles, 'vin');
        $vehicle_id_vins = $this->newQuery()
            ->where('vin', 'IN', $vehiclevins)
            ->get('vin=>vehicle_id');

        $insertVals = array();
        foreach ($vehicles as $vehicle) {
            $vehicle_id = $vehicle_id_vins[$vehicle['vin']];
            if (! isset($vehicle['tsnumber']))
                $vehicle['tsnumber'] = '';
            if (! isset($vehicle['vorhaben']))
                $vehicle['vorhaben'] = '';
            $insertVals[] = array(
                $vehicle_id,
                $vehicle['code'],
                $vehicle['tsnumber'],
                $vehicle['post_id'],
                $vehicle['vorhaben'],
                date('Y-m-d H:i:sO'),
                $vehicle['production_location']
            );
        }

        $insertCols = array(
            'vehicle_id',
            'akz',
            'tsnumber',
            'vehicle_variant',
            'vorhaben',
            'added_timestamp',
            'production_location'
        );
        $numrows = $this->dataSrcPtr->insertMultiple('vehicles_sales', $insertCols, $insertVals);

        return $numrows;

    }


    /**
     * Used in CommonFunctions_VehiclesStationsOverview.class.php to ge the table of vehicles and stations for the Fahrzeug Übersicht function
     *
     * @param mixed $depot
     */
    function getVehiclesAndStations($depot)
    {

        return $this->newQuery()
            ->where('vehicles.depot_id', '=', $depot)
            ->orWhere('stations.depot_id', '=', $depot)
            ->join('stations', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
            ->get('vehicles.vehicle_id,vehicles.depot_id,depots.dp_depot_id,vehicles.station_id,stations.name as sname, vehicles.vin,vehicles.name,vehicles.code,vehicles_sales.delivery_date');

    }


    function getDetailsForPenta($vehicle_ids)
    {

        $vehicles = $this->newQuery()
            ->where('vehicles.vehicle_id', 'IN', $vehicle_ids)
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'INNER JOIN')
            ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
            ->get('vehicles.vin,depots.street,depots.housenr,depots.postcode,depots.place,vehicles_sales.delivery_date,divisions.division_id');
        $processedVehicles = array();
        $fps_emails = array();
        foreach ($vehicles as $vehicle) {
            $contactperson = '';
            if (! isset($fps_emails[$vehicle['division_id']])) {
                $fps_list = $this->newQuery('users')
                    ->where('division_id', '=', $vehicle['division_id'])
                    ->where('role', '=', 'fuhrparksteuer')
                    ->where('email', 'IS', 'NOT NULL')
                    ->get('email,fname,lname');

                $fps_emails[$vehicle['division_id']] = '';

                foreach ($fps_list as $fps) {
                    if (! isset($fps['fname']))
                        $fps['fname'] = '';
                    if (! isset($fps['lname']))
                        $fps['lname'] = '';

                    $fps_emails[$vehicle['division_id']] .= $fps['fname'] . '  ' . $fps['lname'] . ' ' . $fps['email'] . ' ';
                    $contactperson = $fps_emails[$vehicle['division_id']];
                }
            } else
                $contactperson = $fps_emails[$vehicle['division_id']];

            $processedVehicles[] = array(
                $vehicle['vin'],
                $vehicle['place'],
                $vehicle['street'] . ' ' . $vehicle['housenr'] . ' ' . $vehicle['place'] . '' . $vehicle['postcode'],
                date('d.m.Y', strtotime($vehicle['delivery_date'])),
                $contactperson
            );
        }
        return $processedVehicles;

    }


    // ===============================================================================================================================================================================
    function pentaCSVExport($vehicle_ids, $store_into_file = true)
    {

        $fcontent = array();

        $vehicleHeadings = array(
            'VIN',
            'Lieferort',
            'Lieferadresse',
            'Auslieferungsdatum',
            'Ansprechpartner'
        );
        $fcontent[] = implode(',', $vehicleHeadings);

        $vehicles = $this->getDetailsForPenta($vehicle_ids);

        if (! empty($vehicles)) {
            foreach ($vehicles as $vehicle)
                $fcontent[] = implode(",", $vehicle);

            if ($store_into_file) {
                $fname = 'penta_' . date('Y-m-j_H_i_s');
                $floc = '/tmp/' . $fname . '.csv';
                $fhandle = fopen($floc, "w");
                fwrite($fhandle, implode("\r\n", $fcontent) . "\r\n");
                fclose($fhandle);
                return '<a href="/downloadcsv.php?fname=' . $fname . '">Penta CSV Datei herunterladen</a>';
            }
            return implode("\n", $fcontent) . "\n";
        }
        return '';

    }


    // ===============================================================================================================================================================================
    function AutoJoin($ptrJoin, $usedCols)
    {

        if (strpos($usedCols, 'vehicles_sales.') !== false) {
            $ptrJoin = $ptrJoin->join('vehicles_sales', 'vehicles_sales.vehicle_id = vehicles.vehicle_id');
        }

        if (strpos($usedCols, 'vehicle_variants.') !== false) {
            $ptrJoin = $ptrJoin->join('vehicle_variants', 'vehicles.vehicle_variant = vehicle_variants.vehicle_variant_id');
        }

        if (strpos($usedCols, 'sub_vehicle_configurations.') !== false) {
            $ptrJoin = $ptrJoin->join('sub_vehicle_configurations', 'vehicles.sub_vehicle_configuration_id = sub_vehicle_configurations.sub_vehicle_configuration_id');
        }
        
        if (strpos($usedCols, 'depots.') !== false) {
            $ptrJoin = $ptrJoin->join('depots', 'vehicles.depot_id = depots.depot_id');
        }

        if (strpos($usedCols, 'colors.') !== false) {
            $ptrJoin = $ptrJoin->join('colors', 'vehicles.color_id = colors.color_id');
        }
        if (strpos($usedCols, 'park_lines.') !== false) {
            $ptrJoin = $ptrJoin->join('park_lines', 'using(park_id)', 'LEFT JOIN');
        }
        if (strpos($usedCols, 'penta_numbers.') !== false) {
            $ptrJoin = $ptrJoin->join('penta_numbers', 'using(penta_number_id)');
        }

        return $ptrJoin;

    }


    // ===============================================================================================================================================================================
    function AsColumn($selectCols)
    {

        if (! is_array($selectCols))
            return $selectCols;

        $ret = "";
        foreach ($selectCols as $cc => $dbcol)
            $ret .= "$dbcol as $cc,";
        return substr($ret, 0, - 1);

    }


    // ==============================================================================================

    /**
     * returns the results of a INNER JOIN on the vehicles table
     *
     * {@inheritdoc}
     * @see LadeLeitWarte::getAll()
     */
    function getSimpleQuery($whereUser, $selectCols, $limit = 0, $offset = 0, $order = '')
    {

        $ptrQuery = $this->newQuery();
        $ptrWhere = $ptrQuery;
        $usedCol = "";

        foreach ($selectCols as $cc => $dbCol) {
            if (strstr($dbCol, "(") !== false) {
                unset($selectCols[$cc]);
            }
        }

        $usedCols = implode(',', array_values($selectCols));

        if ($limit > 0) {
            $ptrWhere = $ptrWhere->limit_direct($limit);
        }

        if ($offset > 0) {
            $ptrWhere = $ptrWhere->offset_direct($offset);
        }

        if ($order != '') {
            if (strtolower(substr($order, - 5, 5)) == ' desc') {
                $order = substr($order, 0, - 5);
                $ptrWhere = $ptrWhere->orderBy($order, 'desc');
            } else {
                $ptrWhere = $ptrWhere->orderBy($order);
            }
        }

        foreach ($whereUser as $what => $value) {
            $op = '=';

            switch ($what) {
                case 'vehicle_id':
                    $what = 'vehicles.vehicle_id';
                case 'vehicles.vehicle_id':
                    $op = (is_array($value) ? 'in' : '=');
                    break;

                case 'vin':
                    $what = 'vehicles.vin';
                case 'vehicles.vin':
                    if (is_array($value)) {
                        $op = 'IN';
                        break;
                    } else {
                        $op = 'ilike';
                        $value = "%$value%";
                        break;
                    }

                case 'windchill':
                case 'windchill_variant_name':
                    $what = 'vehicle_variants.windchill_variant_name';
                case 'vehicle_variants.windchill_variant_name':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'akz':
                case 'code':
                    $what = 'vehicles.code';
                case 'vehicles.code':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'ikz':
                    $what = 'vehicles.ikz';
                case 'vehicles.ikz':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'color_id':
                    $what = 'vehicles.color_id';
                case 'vehicles.color_id':
                    $op = '=';
                    break;

                case 'StsOnly':
                case 'depot_id':
                    $what = 'vehicles.depot_id';
                case 'vehicles.depot_id':
                    $op = (is_array($value) ? 'in' : '=');
                    break;

                case 'depots.name':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'delivered':
                    $what = 'vehicles_sales.delivery_status';
                case 'vehicles_sales.delivery_status':
                    $op = '=';
                    $value = "TRUE";
                    break;

                case 'is_dp':
                    $what = 'vehicle_variants.is_dp';
                    break;

                case 'color':
                    $what = 'colors.name';
                case 'colors.name':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'penta_number':
                    $what = 'penta_numbers.penta_number';
                case 'penta_numbers.penta_number':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'sub_vehicle_configuration_name':
                    $what = 'sub_vehicle_configurations.sub_vehicle_configuration_name';
                case 'sub_vehicle_configurations.sub_vehicle_configuration_name':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'c2cbox':
                    $what = 'vehicles.c2cbox';
                case 'vehicles.c2cbox':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;
            }

            if (($op === 'ilike') and ($value === '%*%'))
                $value = '%_%';

            $ptrWhere = $ptrWhere->where($what, $op, $value);
            $usedCols .= "," . $what;
        }

        return $this->AutoJoin($ptrWhere, $usedCols);

    }


    // ===============================================================================================================================================================================
    function getSimpleSearch($whereUser, $selectCols, $limit = 0, $offset = 0, $order = '')
    {

        $query = $this->getSimpleQuery($whereUser, $selectCols, $limit, $offset, $order);
        return $query->get_no_parse($this->AsColumn($selectCols), 'vehicle_id');

    }


    // ===============================================================================================================================================================================
    function getSimpleCount($where, $selectCols)
    {

        $query = $this->getSimpleQuery($where, $selectCols);
        $result = $query->get('count(*)');
        return $result[0]['count'];

    }


    // ===============================================================================================================================================================================
    function getVehicleVariant($vehicle_id)
    {

        return $this->newQuery()
            ->where('vehicle_id', '=', $vehicle_id)
            ->getVal('vehicle_variant');

    }


    // ===============================================================================================================================================================================

    /**
     * sendODXCreate
     * opens socket 3423 of web2 and sends the VINs to be parsed by the ODXCreator
     * command format <vin>WS5D16CAAHA801085</vin>
     *
     * @param array $vins
     *            array of vins, for example : Array ( [0] => WS5D16CAAHA801085 [1] => WS5D16CAAHA801086 )
     * @return boolean
     */
    function sendODXCreate($vins)
    {

        return true;

    }


    // ===============================================================================================
    function getPartlist(&$vehicle_info, $only_special_feature, $filter_features)
    {

        $vehicle_info['parts'] = [];
        $result_parts = &$vehicle_info['parts'];

        $batteryGroup = $this->newQuery('parts')
            ->where('name', '=', 'SDA')
            ->getVal('group_id');
        $battery_variant = null;
        $battery_parts = null;

        $features = [];

        if (! empty($vehicle_info['battery'])) {
            $battery = $this->newQuery('parts')
                ->where('show_in_production_protocol', '=', 't')
                ->where('visible_sales', '=', 't')
                ->where('group_id', '=', $batteryGroup)
                ->where('name', '=', $vehicle_info['battery'])
                ->getOne('*');
        }

        $parts_variant = $this->newQuery('variant_parts_mapping')
            ->join('parts', 'parts.part_id=variant_parts_mapping.part_id', 'INNER JOIN')
            ->where('variant_id', '=', $vehicle_info['vehicle_variant_id'])
            ->where('parts.show_in_production_protocol', '=', 't')
            ->where('parts.visible_sales', '=', 't');
        if ($only_special_feature)
            $parts_variant = $parts_variant->where('parts.is_special_feature', '=', 't');

        $parts_variant = $parts_variant->get('parts.*', 'part_id');

        $parts_penta = $this->newQuery('penta_number_parts_mapping')
            ->join('parts', 'parts.part_id=penta_number_parts_mapping.part_id', 'INNER JOIN')
            ->where('penta_number_id', '=', $vehicle_info['penta_number_id'])
            ->where('parts.show_in_production_protocol', '=', 't')
            ->where('parts.visible_sales', '=', 't');
        if ($only_special_feature)
            $parts_penta = $parts_penta->where('parts.is_special_feature', '=', 't');

        $parts_penta = $parts_penta->get('parts.*', 'part_id');

        $parts_vehicle = $this->newQuery('options_at_vehicles')
            ->join('parts', 'parts.part_id=options_at_vehicles.part_id', 'INNER JOIN')
            ->where('vehicle_id', '=', $vehicle_info['vehicle_id'])
            ->where('parts.show_in_production_protocol', '=', 't')
            ->where('parts.visible_sales', '=', 't');
        if ($only_special_feature)
            $parts_vehicle = $parts_vehicle->where('parts.is_special_feature', '=', 't');

        $parts_vehicle = $parts_vehicle->get('parts.*', 'part_id');

        if (! empty($parts_variant))
            foreach ($parts_variant as $part_id => $part) {
                if ($part['group_id'] == $batteryGroup)
                    $battery_parts = $part;
                else if ($filter_features && toBool($part['is_feature']))
                    $features[] = $part;
                else
                    $result_parts[] = $part;
            }

        if (! empty($parts_penta))
            foreach ($parts_penta as $part_id => $part) {
                if ($part['group_id'] == $batteryGroup)
                    $battery_variant = $battery_parts = $part;
                else if ($filter_features && toBool($part['is_feature']))
                    $features[] = $part;
                else
                    $result_parts[] = $part;
            }

        if (! empty($parts_vehicle))
            foreach ($parts_vehicle as $part_id => $part) {
                if ($part['group_id'] == $batteryGroup)
                    $battery_parts = $part;
                else if ($filter_features && toBool($part['is_feature']))
                    $features[] = $part;
                else
                    $result_parts[] = $part;
            }

        if (! isset($battery_variant))
            $battery_variant = $battery_parts;

        if (isset($battery_variant))
            $vehicle_info['battery'] = $battery_variant['begleitscheinname'];

        if ($filter_features)
            $vehicle_info['features'] = $features;

    }


    function DepotWechsel($vehicle_id, $new_depot_id, $new_station_id, $dispatch_date = null, $code = null, $ikz = null)
    {

        if (! isset($dispatch_date))
            $dispatch_date = time() + 8 * ONE_DAY;

        if (! is_string($dispatch_date))
            $dispatch_date = date('Y-m-d', $dispatch_date);

        $update_cols = [
            'depot_id',
            'station_id',
            'depot_dispatch_date'
        ]; // 'dp_notifier_active_from_date'];
        $update_vals = [
            $new_depot_id,
            $new_station_id,
            $dispatch_date
        ];

        if (isset($code) && ! empty($code)) {
            $update_cols[] = 'code';
            $update_vals[] = $code;
        }

        if (isset($ikz) && ! empty($ikz)) {
            $update_cols[] = 'ikz';
            $update_vals[] = $ikz;
        }

        $qry = $this->newQuery()->where('vehicle_id', '=' . $vehicle_id);
        $res = $qry->update($update_cols, $update_vals);
        if (! $res)
            return $qry->GetLastError();

        $qry = $this->newQuery('districts');
        $qry = $qry->where('vehicle_mon', '=', $vehicle_id);
        $res = $qry->update([
            'depot_id'
        ], [
            $new_depot_id
        ]);
        return 0;

    }


    /**
     * function to replace boolean t and f with user friendly x and blank space
     * used when exporting TEO data to a file
     *
     * @param
     *            array element $item
     * @param
     *            array key $key
     */
    function replace_tf_label(&$item, $key)
    {

        if ($item == 't')
            $item = 'true';
        else if ($item == 'f')
            $item = 'false';
        else if ($item == '')
            $item = '';
    }


    /**
     * *
     *
     * @param array $vins
     * @param array $select_cols
     * @param array $filters
     *            includes $filters['depots'] or $filters['status']
     * @param array $production_depots
     * @param array $available_status
     * @param boolean $include_header
     * @param array $headers
     * @return array|array[]
     */
    function getEOLVehiclesByVin($vins, $select_cols, $filters, $production_depots, $available_status, &$include_header = false, $headers = null)
    {

        $eol_vehicles = $this->newQuery()
            ->where('vin', 'IN', $vins)
            ->join('vehicle_variants', 'vehicles.vehicle_variant=vehicle_variants.vehicle_variant_id', 'LEFT JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('penta_numbers', 'penta_numbers.penta_number_id=vehicles.penta_number_id', 'LEFT JOIN')
            ->join('latest_teo_status', 'vehicles.vin=latest_teo_status.teo_vin', 'LEFT JOIN')
            ->join('processed_teo_status', 'processed_teo_status.diagnostic_session_id=latest_teo_status.diagnostic_session_id', 'FULL OUTER JOIN')
            ->join('vehicles_sales', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'INNER JOIN')
            ->join('sub_vehicle_configurations', 'vehicles.sub_vehicle_configuration_id=sub_vehicle_configurations.sub_vehicle_configuration_id', 'INNER JOIN');

        if (! empty($filters)) {
            if (isset($filters['depots']) && $filters['depots'] == 'production')
                $eol_vehicles = $eol_vehicles->where('depots.depot_id', 'IN', $production_depots);

            if (! empty($filters['status']) && sizeof($filters['status']) != sizeof($available_status)) {
                // perform intersect to obtain the labels for the selected columns
                $to_check_status = array_intersect_key($available_status, $filters['status']);
                // search for empty processed_diagnose_status to force parsing the status
                $eol_vehicles = $eol_vehicles->multipleAndWhere('processed_teo_status.processed_diagnose_status', 'IN', $to_check_status, 'OR', 'processed_teo_status.processed_diagnose_status', 'IS', 'NULL');
            }
        }

        $eol_vehicles = $eol_vehicles->get("vehicles.vehicle_id,vehicles.vin,vehicles.ikz,code,penta_kennwort,c2cbox,vehicles.vehicle_variant,windchill_variant_name,
        depots.name as dname,depots.depot_id,finished_status,qmlocked,special_qs_approval,vehicles.penta_number_id,penta_number,vehicles_sales.production_date,vehicles_sales.delivery_status,sub_vehicle_configurations.short_production_description,sub_vehicle_configurations.sub_vehicle_configuration_name,
        date_trunc('seconds',latest_teo_status.diagnose_status_time AT TIME ZONE 'Europe/Berlin') as diagnose_status_time,latest_teo_status.diagnose_status,latest_teo_status.diagnostic_session_id,processed_diagnose_status,
        processed_teo_status.targetsw_changed");

        /**
         * uncomment to add QS Fehler and Measurements details to the export
         * measurements.online,date_trunc('seconds',measurements.timestamp AT TIME ZONE 'Europe/Berlin') as timestamp, open_fault_cnt,rectified_cnt,
         */

        if (empty($eol_vehicles))
            return array();

            

        $processed_vehicles = null;

        foreach ($eol_vehicles as $key => &$vehicle) {
            /*
             * if vehicle has empty processed_diagnose_status, then it has to be processed with diagnoseDetails
             * and then if it doesn't match the chosen TEO Status, has to be skipped
             */
            $skip_this_vehicle = false;

            if (empty($vehicle['processed_diagnose_status'])) {
                $this->diagnoseDetails($vehicle);
                if (! empty($filters['status']) && sizeof($filters['status']) != sizeof($available_status) && ! in_array($vehicle['processed_diagnose_status'], $to_check_status)) {
                    // after processing, if the processed_diagnose_status does not match the actual status searched for
                    $skip_this_vehicle = false;
                }
            }
            
            // Set the headers for each column
            if ($include_header) {
                $header_and_values = array_intersect_key($vehicle, $select_cols);
                $export_headers = [];
                foreach ($header_and_values as $key => $scol) {
                    $export_headers[] = $headers[$key][0];
                }

                $processed_vehicles[] = $export_headers;
                $include_header = false;
            }

            if ($skip_this_vehicle)
                continue;

            $filtered_vehicle = array_intersect_key($vehicle, $select_cols);

            array_walk($filtered_vehicle, array(
                $this,
                'replace_tf_label'
            ));

            $processed_vehicles[] = $filtered_vehicle;
        }
        return $processed_vehicles;

    }


    function getProductionDepots()
    {

        // limit the vehicle statuses to be processed to those in the production depots only
        $production_depots = $this->newQuery('depots')
            ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
            ->where('divisions.production_location', '=', 't')
            ->get('depot_id');

        $production_depots = array_column($production_depots, 'depot_id');

        return $production_depots;

    }


    /**
     *
     * @param array $vehicle_variants
     * @param array $dtcs
     * @param array $lognames
     * @param boolean $production_depots
     */
    function resetProcessedStatus($vehicle_variants, $dtc_ecus, $lognames, $teo_status_map_id, $production_depots_only = true)
    {

        $production_depots = $this->getProductionDepots();
        $result = $this->diagnosePtr->newQuery('latest_teo_status');

        // 1. search for the latest teo sessions for these vehicles with the corresponding dtc or lognames
        if (! empty($dtc_ecus)) {
            $result = $result->join('material_dtcs_revocation_processed', 'using(diagnostic_session_id)', 'LEFT JOIN');

            foreach ($dtc_ecus as $ecu => $dtcs) {
                foreach ($dtcs as $dtc) {
                    if (! empty($vehicle_variants))
                        $result = $result->multipleOrWhere('material_dtcs_revocation_processed.vehicle_variant', 'IN', $vehicle_variants, 'AND', 'material_dtcs_revocation_processed.ecu', '=', $ecu, 'AND', 'material_dtcs_revocation_processed.dtc_number', '=', $dtc);
                    else
                        $result = $result->multipleOrWhere('material_dtcs_revocation_processed.ecu', '=', $ecu, 'AND', 'material_dtcs_revocation_processed.dtc_number', '=', $dtc);
                }
            }
        }
        if (! empty($lognames)) {
            $result = $result->join('material_log_revocation_processed', 'using(diagnostic_session_id)', 'LEFT JOIN');

            foreach ($lognames as $logname) {
                if (! empty($vehicle_variants))
                    $result = $result->multipleOrWhere('material_log_revocation_processed.vehicle_variant', 'IN', $vehicle_variants, 'AND', 'material_log_revocation_processed.name', '=', $logname);
                else
                    $result = $result->multipleOrWhere('material_log_revocation_processed.name', '=', $logname);
            }
        }

        $session_ids = $result->get('latest_teo_status.diagnostic_session_id');

        if (! empty($session_ids))
            $session_ids = array_column($session_ids, 'diagnostic_session_id');
        else
            return [];

        // 2. get the diagnostic_session_id and teo_vin only for those vehicles in production depots
        $vins_dids = $this->newQuery('processed_teo_status')
            ->join('vehicles', 'vehicles.vin=processed_teo_status.teo_vin')
            ->where('diagnostic_session_id', 'IN', $session_ids);

        if ($production_depots_only)
            $vins_dids = $vins_dids->where('vehicles.depot_id', 'IN', $production_depots);

        $vins_dids = $vins_dids->get('teo_vin,diagnostic_session_id');
        $to_process_ids = array_column($vins_dids, 'diagnostic_session_id');

        // 3. reset and return VINs whose TEO Status have been updated
        $applicable_status = $this->diagnosePtr->newQuery('teo_status_map')
            ->where('map_id', '=', $teo_status_map_id)
            ->getVal('applicable_status');
        $update_query = $this->newQuery('processed_teo_status')
            ->where('diagnostic_session_id', 'IN', $to_process_ids)
            ->where('processed_diagnose_status', '=', $applicable_status)
            ->update([
            'processed_diagnose_status'
        ], [
            NULL
        ]);
        if ($update_query)
            return array_column($vins_dids, 'teo_vin');

    }


    /**
     * *
     *
     * @param
     *            array of VINs
     * @param boolean $production_depots_only
     * @return array|array
     */
    function resetProcessedStatusByVin($vins, $production_depots_only = true)
    {

        $production_depots = $this->getProductionDepots();

        // 1. search for the latest teo sessions for these vins, no search by dtc or logname required since the VIN is already specified and we assume
        // these vehicles have the corresponding dtc or logname

        $diagnostic_session_ids = $this->diagnosePtr->newQuery('latest_teo_status')
            ->where('teo_vin', 'IN', $vins)
            ->get('diagnostic_session_id');
        if (empty($diagnostic_session_ids))
            return [];
        else
            $diagnostic_session_ids = array_column($diagnostic_session_ids, 'diagnostic_session_id');

        // 2. get the diagnostic_session_id and teo_vin only for those vehicles in production depots
        $vins_dids = $this->newQuery('processed_teo_status')
            ->join('vehicles', 'vehicles.vin=processed_teo_status.teo_vin')
            ->where('diagnostic_session_id', 'IN', $diagnostic_session_ids);

        if ($production_depots_only)
            $vins_dids = $vins_dids->where('vehicles.depot_id', 'IN', $production_depots);

        $vins_dids = $vins_dids->get('teo_vin,diagnostic_session_id');
        $to_process_ids = array_column($vins_dids, 'diagnostic_session_id');

        // 3. reset and return VINs whose TEO Status have been updated
        $update_query = $this->newQuery('processed_teo_status')
            ->where('diagnostic_session_id', 'IN', $to_process_ids)
            ->update([
            'processed_diagnose_status'
        ], [
            NULL
        ]);
        if ($update_query)
            return array_column($vins_dids, 'teo_vin');

    }

}
