<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 1/9/19
 * Time: 1:39 PM
 */

class CommonFunctions_GeneralSearch extends CommonFunctions {
    protected $ladeLeitWartePtr;

    protected $user;

//  protected $displayHeader;

    protected $requestPtr;

    public static $generalSearchParams = array(
        'vin' => 'VIN', //1
        'ikz' => 'Code / IKZ', //1
        'penta_kennwort' => 'penta kennwort', //1
        'c2cbox' => 'TCU ID / C2CBox ID', //1  /c2cbox
        'vehicle_variants.variant' => 'ConfigurationController (Subconfiguration) / Variant', //1 /vehicles.vehicle_variant
        'penta_numbers.penta_number' => 'Penta article ', //0 /
//      'location' => '_Location (Place of production)', //0
        'processed_teo_status.processed_diagnose_status' => 'TEO result state', //0 /processed_teo_status.processed_diagnose_status
//      'permission' => '_Special permission', //0 //todo: not available right now
        'special_qs_approval' => 'Sonder- genehmigung ', //1 /special_qs_approval
        'finished_status' => 'QS state',
        'qmlocked' => 'QM lock / QM gesperrt ', //1 /qmlocked
        'mechanical_errors' => 'Mechanische Fehler',  //sub-category $generalSearchSubcategoryQSErrors
        'diagnostic' => 'Diagnose Daten' //sub-category $generalSearchSubcategoryDiagnosticData
    );

    public $generalSearchSubcategoryQSErrors = array(
        'missing_part' => 'Fehlteil',
        'adjustment_needed' => 'Anpassung notwendig',
        'defect_part' => 'Defektes Teil (Reparieren / Austauschen)',
        'varnish_repair' => 'Verkratzter Lack / Smart Repair',
        'tightness' => 'Festigkeit',
        'assembly_problems' => 'Montageprobleme'
    );

    public $generalSearchSubcategoryDiagnosticData = array(
        'ecu_dtc_pairs' => 'ECU / DTC Paarung',
        'ecu_log_pairs' => 'ECU / Log Daten Paarung'
    );

    public $general_search_top_select;

    public $dtcs_codes, $allEcus, $ecu_options;

    /**
     * @var
     */
    public $diagnosePtr;


    function __construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $common_action, $filter_vehicles) {
        parent::__construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $common_action, $filter_vehicles);

        $this->translate = parent::getTranslationsForDomain();

        $this->diagnosePtr = $ladeLeitWartePtr->getDiagnoseObject();

        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->user = $user;
        $this->displayHeader = $displayHeader;
        $this->requestPtr = $requestPtr;
        $this->content = '';
        $this->ajaxContent = '';
        $this->filter_vehicles = $filter_vehicles;

        $this->prepareDtcForm();


    }


    public function prepareDtcForm() {

        $this->allEcus = $this->ladeLeitWartePtr->newQuery('ecus')
            ->where('ecu_id', '>', 0)
            ->orderBy('name')
            ->get('ecu_id=>name');
        $this->dtcs_codes = [];
        $this->ecu_options = '<option value="">--ECU wählen--</option>';
        foreach ($this->allEcus as $ecu) {
            $ecu = strtoupper($ecu);
            $this->ecu_options .= '<option value="' . $ecu . '">' . $ecu . '</option>';
            $this->dtcs_codes[$ecu] = $this->diagnosePtr->newQuery('dtcs')->where('ecu', '=', $ecu)->get("concat(dtc_number::text,' | ',text) as label,dtc_number::text as value");
        }


//    r($this->dtcs_codes);
    }


    function getSubCat() {
        $qs_fcat_id = 0;
        $child_categories = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')
            ->where('parent_cat', '=', $qs_fcat_id)
            ->get('qs_fcat_id=>cat_label');

//    if (!empty($child_categories)) {
//      $this->qs_child_cat_select = ' <div class="row genByAjax"><div class="columns six">Sub-Kategorie wählen<br>';
//      $this->qs_child_cat_select .= '<select name="child_cat[' . $qs_fcat_id . ']" class="qs_fault_cat_search child_cat" ><option value=0>--</option>';
//      foreach ($child_categories as $child_qs_fcat_id => $child_qs_fcat_label) {
//        $this->qs_child_cat_select .= '<option value="' . $child_qs_fcat_id . '" >' . $child_qs_fcat_label . '</option>';
//      }
//      $this->qs_child_cat_select .= '</select></div></div>';
//    }
        return $child_categories;
    }


    function genSearchForm($action = null) {

//    $this->general_search = $this->ladeLeitWartePtr->newQuery('search_categories')->where('parent_cat', '=', 0)->get('qs_fcat_id=>cat_label');
        $this->general_search_top_select = '<select name="general_search_cat[]" id="general_search_cat" class="general_search_cat"><option value="0">---</option>';
//    $current_filter = '';

//    $this->qs_fault_top = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')->where('parent_cat', '=', 0)->get('qs_fcat_id=>cat_label');
//    $this->qs_fault_top_select = '<select name="qs_fault_cat_search" class="qs_fault_cat_search"><option value="0">---</option>';
        $current_filter = '';
        foreach (self::$generalSearchParams as $k => $value) {
            $this->general_search_top_select .= '<option value="' . $k . '">' . $value . '</option>';
        }
        $this->general_search_top_select .= '</select>';


        $this->subcategory_qserrors_select = '<select id="" name="general_search_cat_qs_errors[]" class="general_search_hidden general_search_cat_qs_errors"><option value="0">---</option>';
        foreach ($this->getSubCat() as $k => $value) {
            $this->subcategory_qserrors_select .= '<option value="' . $k . '">' . $value . '</option>';
        }
        $this->subcategory_qserrors_select .= '</select>';

        $this->subcategory_diagnostic_select = '<select id="" name="general_search_cat_diag[]" class="general_search_hidden general_search_cat_diag"><option value="0">---</option>';
        foreach ($this->generalSearchSubcategoryDiagnosticData as $k => $value) {
            $this->subcategory_diagnostic_select .= '<option value="' . $k . '">' . $value . '</option>';
        }
        $this->subcategory_diagnostic_select .= '</select>';

        $this->subcategory_dtc_select = '<select id="" name="general_search_cat_dct[]" class="general_search_hidden general_search_cat_dtc">';
        $this->subcategory_dtc_select .= $this->ecu_options;
        $this->subcategory_dtc_select .= '</select>';


        $notfound_msg = '';
        $indexaction = '?action=ajaxRowsSearch';
        $filter_vehicles_str = '';
        $this->content = '<form action="index.php' . $indexaction . '" method="POST" id="general_search">';
        if (!(isset($GLOBALS['search_hide_fieldset']) && $GLOBALS['search_hide_fieldset'] == true)) {
            $this->content .= '<fieldset class="general_search_tab_container">';

        } else {
            $this->content .= '<fieldset class="general_search_tab_container reset_fieldset">';
        }
        $formLegendTitle = $this->translate['generalSearch']['formLegendTitle'];
        $formTitle = $this->translate['generalSearch']['formTitle'];
        $andBool = $this->translate['generalSearch']['andBool'];
        $orBool = $this->translate['generalSearch']['orBool'];
        $checkboxNote = $this->translate['generalSearch']['checkboxNote'];
        $notBool = $this->translate['generalSearch']['notBool'];
        $yesOption = $this->translate['generalSearch']['notBool'];
        $noOption = $this->translate['generalSearch']['notBool'];
        $deleteAllFilter = $this->translate['generalSearch']['deleteAllFilter'];
        $this->content .= '<legend class="collapsible"><span class="genericon genericon-expand"></span>' . $formLegendTitle . '</legend>
                          <div style="margin-left: 10px;">' . $current_filter . $notfound_msg . '</div>
                          <div class="collapsible_content" style="display: none" >
                          <div class="row" id="mainCat">
                          <div class="columns eleven"  >
                          ' . $formTitle . '<a title="Neues Suchkriterium hinzufügen" id="new_row_butt" href="#">[+]</a><br>
    
                            <div id="main_form_row" class="main_rows_to_copy">
                            <select class="general_search_hidden select_operator" name="operator[]">
                              <option value="AND">' . $andBool . '</option>
                              <option value="OR">' . $orBool . '</option>
                            </select><br />
                            <input type="checkbox" class="regex"  name="regex[]" value="1">' . $checkboxNote . '<br />
                            ' . $this->general_search_top_select . '
                            ' . $this->subcategory_qserrors_select . ' 
                            ' . $this->subcategory_diagnostic_select . '
                            ' . $this->subcategory_dtc_select . '
                            <select name="search_not_bool[]" class="search_not_bool options_hidden">
                              <option value="-">=</option>
                              <option value="0">' . $notBool . '</option>
                            </select>
                            <input name="search_value[]" class="search_value options_hidden" value="">
                            <select name="search_value_bool[]" class="search_value_bool options_hidden">
                              <option value="-">-------</option>
                              <option value="1">' . $yesOption . '</option>
                              <option value="0">' . $noOption . '</option>
                            </select>
                            <div class="more_parameters" index="0"></div>
                            <a href="#" title="DSuchkriterium entfernen" class="general_search_hidden click_delete_row">[-]</a>
                             <br />
                            </div>
                          <div id="new_row_inputs">
                                
                          </div>
                          
                          </div>
                          </div>
                          <div class="row">
                          <div class="columns eight nopad" id="ajaxFieldsWrap" class="genByAjax">
                          </div>
                          </div>
                          <div class="row">
                          <div class="columns eight nopad" class="genByAjax">
                          <input type="hidden" name="filtered_vehicles" id="filtered_vehicles" value="' . $filter_vehicles_str . '">
                          <input type="submit" id="main_search_button" name="qs_fault_search" value="Suche">
                          <a id="delete_all_fields" href="#">' . $deleteAllFilter . '</a>
                          </div>
                          </div>
                          </div>';
        //if (!(isset($GLOBALS['search_hide_fieldset']) && $GLOBALS['search_hide_fieldset'] == true)) {
        $this->content .= '</fieldset>';
        /*} else {
          $this->content .= '</div';
        }*/

//
//    echo 'cycki'   .$this->content ;

        return $this->content;


    }


    function genSearchFormDtc() {
        $dtcs_tags = $log_name_tags = $hide_content = $dtcs_hidden = $log_name_hidden = $andSelect = $orSelect = $log_hidden = $log_tags = '';
        if (isset($_POST['teo_fehler_search'])) {
            $result = null;
            $allEcuDtcs = [];
            foreach ($this->allEcus as $ecu) {
                $ecu = strtoupper($ecu);
                if (isset($_POST[$ecu . '_dtcs'])) {
                    $dtcs = filter_var($_POST[$ecu . '_dtcs'], FILTER_SANITIZE_STRING);
                    $allEcuDtcs[$ecu] = explode(',', $dtcs);
                }
            }

            if (!empty($allEcuDtcs)) {
                foreach ($allEcuDtcs as $ecu => $dtcs) {
                    $dtcs_hidden .= '<input type="hidden" value="' . implode(',', $dtcs) . '" name="' . $ecu . '_dtcs" id="' . $ecu . '_dtcs" >';
                    foreach ($dtcs as $dtc) {
                        $dtcs_tags .= '<a href="#" class="selected_dtc_code_tag" data-ecu="' . $ecu . '" data-dtc="' . $dtc . '" >' . $ecu . ' - ' . $dtc . '<span class="genericon genericon-close"></span></a>';
                    }
                }
            }

            if (isset($_POST['log_names'])) {
                $log_names = filter_var($_POST['log_names'], FILTER_SANITIZE_STRING);
                $log_hidden .= '<input type="hidden" value="' . $log_names . '" name="log_names" id="log_names" >';
                $log_names_array = explode(',', $log_names);
                foreach ($log_names_array as $log_name) {
                    $log_tags .= '<a href="#" class="selected_log_name_tag" data-log_name="' . $log_name . '" >' . $log_name . '<span class="genericon genericon-close"></span></a>';
                }
            }
            $hide_content = '';
        } else {
            $hide_content = ' style="display: none;" ';
        }
        if (isset($_POST['combine_op'])) {
            if ($_POST['combine_op'] == 'or') $orSelect = ' selected="selected" ';
            else $andSelect = ' selected="selected" ';
        }

        $teoFilter = $this->translate['generalSearch']['teoFilter'];
        $dtcCode = $this->translate['generalSearch']['dtcCode'];
        $dtcCodeNote = $this->translate['generalSearch']['dtcCodeNote'];
        $andBool = $this->translate['generalSearch']['andBool'];
        $orBool = $this->translate['generalSearch']['orBool'];
        $dtcError = $this->translate['generalSearch']['dtcError'];
        $logData = $this->translate['generalSearch']['logData'];
        $deleteFilter = $this->translate['generalSearch']['deleteFilter'];
        $this->content = '<form action="index.php" method="POST" id="teo_search">
                        <fieldset class="qs_faults_tab_container">
                        <legend class="collapsible"><span class="genericon genericon-expand"></span>' . $teoFilter . '</legend>
                        <div class="collapsible_content" ' . $hide_content . ' >
                        <fieldset class="row">
                        <fieldset class="columns four">
                        <strong>' . $dtcCode . '</strong>
                        <label for="teo_ecu_select">
                        <select name="ecu" id="teo_ecu_select">' . $this->ecu_options . '
                        </select>
                        </label>
                        <label for="teo_dtcs_code">
                            <input type="text" id="teo_dtcs_code" value="" placeholder="DTCS Code oder * eingeben" name="teo_dtcs_code"><br>
                            <span style="color: #999">' . $dtcCodeNote . '</span>
                        </label>
                        </fieldset>
                        <fieldset class="columns three">
                        <label for="combine_op">
                        <select name="combine_op" id="combine_op">
                            <option value="or" ' . $orSelect . '>' . $andBool . '</option>
                            <option value="and" ' . $andSelect . '>' . $orBool . '</option>
                        </select><br>
                        <span style="color: #999">' . $dtcError . '</span></label>
                        </fieldset>
                        <fieldset class="columns four">
                        <strong>' . $logData . '</strong>
                        <label for="teo_log_error">
                            <input type="text" value="" id="log_error_code" name="log_error_code">
                        </label>
                        </fieldset>
                        </fieldset>

                        <fieldset class="row">
                        <fieldset class="columns four"  id="selected_dtcs_codes" >' . $dtcs_hidden . $dtcs_tags . '
                        </fieldset>
                        <fieldset class="columns three"></fieldset>
                        <fieldset class="columns four"  id="selected_log_error" >' . $log_hidden . $log_tags . '
                        </fieldset>
                        </fieldset>

                        <fieldset class="row">
                        <fieldset class="columns offset-by-four four nopad">
                        <input type="submit" name="teo_fehler_search" value="' . $this->translate['generalSearch']['btnSearch'] . '">
                        <a href="' . $_SERVER['PHP_SELF'] . '">' . $deleteFilter . '</a>
                        </fieldset>
                        </fieldset>
                        </fieldset>
                        </form>';
    }


}
