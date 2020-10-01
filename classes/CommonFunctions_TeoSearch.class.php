<?php

/**
 * CommonFunctions_TeoSearch.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 */
class CommonFunctions_TeoSearch extends CommonFunctions {

    protected $ladeLeitWartePtr;

    protected $user;

    protected $displayHeader;

    protected $requestPtr;

    function __construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $common_action, $filter_vehicles) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->user = $user;
        $this->displayHeader = $displayHeader;
        $this->requestPtr = $requestPtr;
        $this->content = '';
        $this->ajaxContent = '';
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->filter_vehicles = $filter_vehicles;
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
        $this->displayHeader->enqueueJs("sts-custom-common-qs", "js/sts-custom-common-qs.js");
        if (isset($common_action) && method_exists($this, $common_action))
            call_user_func(array($this, $common_action));
    }

    function ajaxGetDtcCodes() {
        $ecu = $this->requestPtr->getProperty('ecu');
        echo json_encode($this->dtcs_codes[$ecu]);
        exit(0);
    }

    function ajaxGetLogNames() {
        //passed=f since we want to search only for those test where log data is not passed
        $log_data_names = $this->diagnosePtr->newQuery('log_data')->where('passed', '=', 'f')->get('distinct name as value');
        echo json_encode($log_data_names);
        exit(0);
    }

    function genSearchForm() {
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

        $this->content = '<form action="index.php" method="POST" id="teo_search">
                        <fieldset class="qs_faults_tab_container">
                        <legend class="collapsible"><span class="genericon genericon-expand"></span>TEO Filter</legend>
                        <div class="collapsible_content" ' . $hide_content . ' >
                        <fieldset class="row">
                        <fieldset class="columns four">
                        <strong>DTC Code</strong>
                        <label for="teo_ecu_select">
                        <select name="ecu" id="teo_ecu_select">' . $this->ecu_options . '
                        </select>
                        </label>
                        <label for="teo_dtcs_code">
                            <input type="text" id="teo_dtcs_code" value="" placeholder="DTCS Code oder * eingeben" name="teo_dtcs_code"><br>
                            <span style="color: #999">* zeigt alle Fahrzeuge mit einem Fehler dieses Steuergeräts</span>
                        </label>
                        </fieldset>
                        <fieldset class="columns three">
                        <label for="combine_op">
                        <select name="combine_op" id="combine_op">
                            <option value="or" ' . $orSelect . '>oder</option>
                            <option value="and" ' . $andSelect . '>und</option>
                        </select><br>
                        <span style="color: #999">DTC Fehler, Log Fehler Zusammenhang</span></label>
                        </fieldset>
                        <fieldset class="columns four">
                        <strong>Log Data</strong>
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
                        <input type="submit" name="teo_fehler_search" value="Suchen">
                        <a href="' . $_SERVER['PHP_SELF'] . '"> Alle Filter löschen</a>
                        </fieldset>
                        </fieldset>
                        </fieldset>
                        </form>';
    }

    function getHtml() {
        return $this->content;
    }


}
