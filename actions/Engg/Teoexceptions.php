<?php
/**
 * Teoexceptions.class.php
 *
 * @author Pradeep Mohan
 */
//@todo: Show the processed_vins on adding new exceptions
//@todo: PDF upload assumes all revocation with same name have same PDF (during upload)
class ACtion_Teoexceptions extends AClass_Base {
    const ID_TAB_NEW_EXCEPTION = 1;
    const ID_TAB_DTCS_EXCEPTION = 2;
    const ID_TAB_LOG_EXCEPTION = 3;

    function __construct() {
        parent::__construct();
        $this->processed_vins_template =
            '
        <span class="">
        <strong>Abweicherlaubnis hinzugefügt! TEO Status wird wieder berechnet für die folgende Fahrzeuge</strong><br><br>
        {vins_list} 
        </span>
        ';
        $this->S_Tab = 2;
        if ($this->controller) {
            $this->ladeLeitWartePtr = $this->controller->GetObject("ladeLeitWarte");
            $this->userPtr = $this->controller->GetObject("user");
        }
    }

    function SetupHeaderFiles($displayheader) {
        parent::SetupHeaderFiles($displayheader);

        $displayheader->enqueueJs("sts-custom-common-qs", "js/sts-custom-common-qs.js");
        $displayheader->enqueueJs("sts-custom-engg", "js/sts-custom-engg.js");
        $displayheader->enqueueStylesheet("teoexceptions", "css/teoexceptions.css");

    }

    /**
     * save the Abweicherlaubnis based on log names
     */
    function save_log_exception() {
        $exception_name = filter_var($_POST['exception_name'], FILTER_SANITIZE_STRING);
        $teo_status_map = filter_var($_POST['teo_status'], FILTER_SANITIZE_NUMBER_INT);
        $log_names = filter_var($_POST['log_names'], FILTER_SANITIZE_STRING);
        if (!empty($log_names)) $log_names = explode(',', $log_names);
        $applicable_vehicles = filter_var($_POST['applicable_vehicles'], FILTER_SANITIZE_STRING);
        if (!empty($_POST['wcvariant_select'])) {
            $vehicle_variants = filter_var_array($_POST['wcvariant_select'], FILTER_SANITIZE_NUMBER_INT);
        }


        if (isset($_POST['start_vin'])) {
            $start_vin = filter_var($_POST['start_vin'], FILTER_SANITIZE_STRING);
            $result = $this->ladeLeitWartePtr->newQuery('latest_teo_status')->where('teo_vin', '>=', $start_vin);

            if (isset($_POST['end_vin']) && !empty($_POST['end_vin'])) {
                $end_vin = filter_var($_POST['end_vin'], FILTER_SANITIZE_STRING);
                $result = $result->where('teo_vin', '<=', $end_vin);
            } //IMPORTANT: So that only one VIN is fetched!
            else
                $result->limit(1);
            $vins = $result->orderBy('teo_vin', 'ASC')->get('teo_vin');
        }
        $vins = array_column($vins, 'teo_vin');

        $filename = NULL;
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] != UPLOAD_ERR_NO_FILE) {
            $file = new CommonFunctions_SaveFileUpload('pdf_file', '/var/www/teo_exception', $exception_name . '_' . time(), 'pdf');
            try {
                $filename = $file->saveFile();
            } catch (RuntimeException $e) {
                $msg = $e->getMessage();
            }

        }

        if ($applicable_vehicles == 'alle') {
            $insertCols = ['name', 'id_label', 'teo_status_map_id', 'pdf_file'];
            $insertVals = null;
            if (!empty($log_names))
                foreach ($log_names as $log_name) {
                    if (!empty($log_name)) $insertVals[] = [$log_name, $exception_name, $teo_status_map, $filename];
                }

            $processed_vins = $this->ladeLeitWartePtr->vehiclesPtr->resetProcessedStatus(null, null, $log_names, $teo_status_map);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_revocation')->insert_multiple_new($insertCols, $insertVals);
        } else if ($applicable_vehicles == 'byvin') {
            $insertCols = ['name', 'id_label', 'teo_status_map_id', 'vin', 'pdf_file'];
            $insertVals = null;
            foreach ($vins as $vin) {
                if (!empty($log_names))
                    foreach ($log_names as $log_name) {
                        if (!empty($log_name)) $insertVals[] = [$log_name, $exception_name, $teo_status_map, $vin, $filename];
                    }
            }
            $processed_vins = $this->ladeLeitWartePtr->vehiclesPtr->resetProcessedStatusByVin($vins);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_revocation_by_vin')->insert_multiple_new($insertCols, $insertVals);
        } else {
            $insertCols = ['name', 'id_label', 'teo_status_map_id', 'vehicle_variant', 'pdf_file'];
            $insertVals = null;
            foreach ($vehicle_variants as $vehicle_variant) {
                if (!empty($log_names))
                    foreach ($log_names as $log_name) {
                        if (!empty($log_name)) $insertVals[] = [$log_name, $exception_name, $teo_status_map, $vehicle_variant, $filename];
                    }
            }

            $processed_vins = $this->ladeLeitWartePtr->vehiclesPtr->resetProcessedStatus($vehicle_variants, null, $log_names, $teo_status_map);
            $result_insert = $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_revocation_by_variant')->insert_multiple_new($insertCols, $insertVals);
        }
        $vins_str = $this->processLongList(json_encode($processed_vins), 'processed', 'vins');
        $this->msg = str_replace('{vins_list}', $vins_str, $this->processed_vins_template);
    }

    /**
     * save the Abweicherlaubnis based on dtc codes
     */
    function save_dtcs_exception() {
        $exception_name = filter_var($_POST['exception_name'], FILTER_SANITIZE_STRING);
        foreach ($this->allEcus as $ecu) {
            $ecu = strtoupper($ecu);
            if (isset($_POST[$ecu . '_dtcs'])) {
                $dtcs = filter_var($_POST[$ecu . '_dtcs'], FILTER_SANITIZE_STRING);
                $allEcuDtcs[$ecu] = explode(',', $dtcs);
            }
        }
        $teo_status_map = filter_var($_POST['teo_status'], FILTER_SANITIZE_NUMBER_INT);
        $applicable_vehicles = filter_var($_POST['applicable_vehicles'], FILTER_SANITIZE_STRING);

        if (!empty($_POST['wcvariant_select'])) {
            $vehicle_variants = filter_var_array($_POST['wcvariant_select'], FILTER_SANITIZE_NUMBER_INT);
        }


        if (isset($_POST['start_vin'])) {
            $start_vin = filter_var($_POST['start_vin'], FILTER_SANITIZE_STRING);
            $result = $this->ladeLeitWartePtr->newQuery('latest_teo_status')->where('teo_vin', '>=', $start_vin);

            if (isset($_POST['end_vin']) && !empty($_POST['end_vin'])) {
                $end_vin = filter_var($_POST['end_vin'], FILTER_SANITIZE_STRING);
                $result = $result->where('teo_vin', '<=', $end_vin);
            } //IMPORTANT: So that only one VIN is fetched!
            else
                $result->limit(1);
            $vins = $result->orderBy('teo_vin', 'ASC')->get('teo_vin');
        }
        $vins = array_column($vins, 'teo_vin');

        $filename = NULL;
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] != UPLOAD_ERR_NO_FILE) {
            $file = new CommonFunctions_SaveFileUpload('pdf_file', '/var/www/teo_exception', $exception_name . '_' . time(), 'pdf');
            try {
                $filename = $file->saveFile();
            } catch (RuntimeException $e) {
                $msg = $e->getMessage();
            }

        }

        if ($applicable_vehicles == 'alle') {
            $insertCols = ['dtc', 'id_label', 'ecu', 'teo_status_map_id'];
            $insertVals = null;
            foreach ($allEcuDtcs as $ecu => $dtcs) {
                if (is_array($dtcs)) {
                    foreach ($dtcs as $dtc)
                        $insertVals[] = [$dtc, $exception_name, $ecu, $teo_status_map];
                }

            }
            $processed_vins = $this->ladeLeitWartePtr->vehiclesPtr->resetProcessedStatus(null, $allEcuDtcs, null, $teo_status_map);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs_revocation')->insert_multiple_new($insertCols, $insertVals);

        } else if ($applicable_vehicles == 'byvin') {

            $insertCols = ['dtc', 'id_label', 'ecu', 'teo_status_map_id', 'vin'];
            $insertVals = null;
            foreach ($vins as $vin) {
                foreach ($allEcuDtcs as $ecu => $dtcs) {
                    if (is_array($dtcs)) {
                        foreach ($dtcs as $dtc)
                            $insertVals[] = [$dtc, $exception_name, $ecu, $teo_status_map, $vin];
                    }
                }
            }
            $processed_vins = $this->ladeLeitWartePtr->vehiclesPtr->resetProcessedStatusByVin($vins);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs_revocation_by_vin')->insert_multiple_new($insertCols, $insertVals);

        } else {
            $insertCols = ['dtc', 'id_label', 'ecu', 'teo_status_map_id', 'vehicle_variant'];
            $insertVals = null;
            foreach ($vehicle_variants as $vehicle_variant) {
                foreach ($allEcuDtcs as $ecu => $dtcs) {
                    if (is_array($dtcs)) {
                        foreach ($dtcs as $dtc)
                            $insertVals[] = [$dtc, $exception_name, $ecu, $teo_status_map, $vehicle_variant];
                    }

                }
            }

            $processed_vins = $this->ladeLeitWartePtr->vehiclesPtr->resetProcessedStatus($vehicle_variants, $allEcuDtcs, null, $teo_status_map);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs_revocation_by_variant')->insert_multiple_new($insertCols, $insertVals);

        }
        $vins_str = $this->processLongList(json_encode($processed_vins), 'processed', 'vins');
        $this->msg = str_replace('{vins_list}', $vins_str, $this->processed_vins_template);
    }

    function ExecuteTAB_New() {

        if (!$this->userPtr->user_can('add_teo_exceptions')) return;
        $this->allEcus = $this->ladeLeitWartePtr->newQuery('ecus')
            ->where('ecu_id', '>', 0)
            ->orderBy('name')
            ->get('name=>name');

        $this->dtcs_codes = [];

        foreach ($this->allEcus as $ecu) {
            $ecu = strtoupper($ecu);
            $this->dtcs_codes[$ecu] = $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs')->where('ecu', '=', $ecu)->get("concat(dtc_number::text,' | ',text) as label,dtc_number::text as value");
        }
        $teo_status_map = $this->ladeLeitWartePtr->diagnosePtr->newQuery('teo_status_map')->get("map_id,concat_ws('->',applicable_status,processed_status) as status");
        $this->teo_status_map = array_combine(array_column($teo_status_map, 'map_id'), array_column($teo_status_map, 'status'));
        $this->availableVariantTypes = $this->ladeLeitWartePtr->newQuery('vehicle_variants')->orderBy('name')->get('distinct(substring(windchill_variant_name from 1 for 3)) as name');
        if (isset($_POST['submit_dtcs_excp'])) {
            $this->save_dtcs_exception();
        } else if (isset($_POST['submit_log_excp'])) {
            $this->save_log_exception();
        }
    }

    function processLongList($list_json, $label, $key) {
        $list_array = json_decode($list_json);
        if (sizeof($list_array) > 5) {
            $list_array_init = array_slice($list_array, 0, 5);
            $wrap_string = "wrap_{$label}_{$key}";
            $list_string = implode('<br>', $list_array_init) . '
            <a class="parent_hidden_text " data-target="' . $wrap_string . '"><span class="genericon genericon-plus"></span>mehr..</a>';
            $list_array_rest = array_slice($list_array, 5);
            $list_string .= '<br><span style="display: none" class="' . $wrap_string . '">' . implode('<br>', $list_array_rest) . '</span>';
        } else
            $list_string = implode('<br>', $list_array);
        return $list_string;
    }

    function upload_teo_pdf() {
        $exception_name = filter_var($_POST['exception_name'], FILTER_SANITIZE_STRING);
        $exception_type = filter_var($_POST['exception_type'], FILTER_SANITIZE_STRING);
        $filename = NULL;
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] != UPLOAD_ERR_NO_FILE) {
            $file = new CommonFunctions_SaveFileUpload('pdf_file', '/var/www/teo_exception', $exception_name . '_' . time(), 'pdf');
            try {
                $filename = $file->saveFile();
                $msg = 'Datei erfolgreich hochgeladen!';
            } catch (RuntimeException $e) {
                $msg = '<span class="error_msg">' . $e->getMessage() . '</span>';
            }

        }

        if ($exception_type == 'dtcs') {
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs_revocation')->where('id_label', '=', $exception_name)->update(['pdf_file'], [$filename]);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs_revocation_by_variant')->where('id_label', '=', $exception_name)->update(['pdf_file'], [$filename]);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs_revocation_by_vin')->where('id_label', '=', $exception_name)->update(['pdf_file'], [$filename]);
        } else if ($exception_type == 'log') {
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_revocation')->where('id_label', '=', $exception_name)->update(['pdf_file'], [$filename]);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_revocation_by_variant')->where('id_label', '=', $exception_name)->update(['pdf_file'], [$filename]);
            $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_revocation_by_vin')->where('id_label', '=', $exception_name)->update(['pdf_file'], [$filename]);
        }
        return $msg;
    }

    function ExecuteTAB_DTCS() {
        if (isset($_POST['upload_pdf_file'])) $this->msg = $this->upload_teo_pdf();

        $heading = [['headingone' => ['Abweicherlaubnis', 'ECU', 'DTC', 'Fahrzeuge', 'TEO Status Wandlung', 'PDF']]];

        $result = null;

        $dtcs_overview = $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs_revocation_overview')->get('*');

        foreach ($dtcs_overview as $key => $row) {
            $dtcs_string = $this->processLongList($row['dtcs'], 'dtcs_variant', $key);
            $wc_string = $this->processLongList($row['vehicle_filter'], 'vehicle_filter', $key);

            if (!empty($row['pdf_file'])) {
                $row['pdf_file'] = '<a href="downloadfile.php?filename=' . $row['pdf_file'] . '&fromloc=teoexceptions&format=pdf" target="_blank"><span class="genericon genericon-download"></span>' . $row['pdf_file'] . '</a>';
            } else {
                $form_data = '<div class="pdf_upload_form" style="display:none"><form enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                            <p><label><strong>DTC Abweicherlaubnis als PDF hochladen</strong><br><br><input type="file" name="pdf_file"></label></p>
                            <input type="hidden" name="exception_name" value="' . $row['id_label'] . '">
                            <input type="hidden" name="exception_type" value="dtcs">
                            <input type="hidden" name="action" value="teoexceptions">
                            <input type="hidden" name="tab" value="' . self::ID_TAB_DTCS_EXCEPTION . '">
                            <p><input type="submit" name="upload_pdf_file" value="Hinzufügen"></p></form></div>';
                $row['pdf_file'] = '<a href="#" data-exception_name="' . $row['id_label'] . '" class="add_teo_pdf"><span class="genericon genericon-edit"></span>PDF Datei für ' . $row['id_label'] . ' hochladen</a>' . $form_data;
            }
            $result[] = [$row['id_label'], $row['ecu'], $dtcs_string, $wc_string, $row['teo_status_map'], $row['pdf_file']];
        }
        $result = array_merge($heading, $result);
        $id_label_table = new DisplayTable($result, ['class' => 'exception_table', 'csswidths' => [120, 90, 520]]);
        $this->dtcs_rev = $id_label_table->getContent();

    }

    function ExecuteTAB_Log() {
        if (isset($_POST['upload_pdf_file'])) $this->msg = $this->upload_teo_pdf();

        $heading = [['headingone' => ['Abweicherlaubnis', 'Name', 'Fahrzeuge', 'TEO Status Wandlung', 'PDF']]];

        $result = null;

        $log_overview = $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_revocation_overview')->get('*');

        foreach ($log_overview as $key => $row) {
            $log_string = $this->processLongList($row['logname'], 'logname', $key);
            $wc_string = $this->processLongList($row['vehicle_filter'], 'vehicle_filter', $key);
            if (!empty($row['pdf_file'])) {
                $row['pdf_file'] = '<a href="downloadfile.php?filename=' . $row['pdf_file'] . '&fromloc=teoexceptions&format=pdf" target="_blank"><span class="genericon genericon-download"></span>' . $row['pdf_file'] . '</a>';
            } else {
                $form_data = '<div class="pdf_upload_form" style="display:none"><form enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                            <p><label><strong>Log Abweicherlaubnis als PDF hochladen</strong><br><br><input type="file" name="pdf_file"></label></p>
                            <input type="hidden" name="exception_name" value="' . $row['id_label'] . '">
                            <input type="hidden" name="exception_type" value="log">
                            <input type="hidden" name="action" value="teoexceptions">
                            <input type="hidden" name="tab" value="' . self::ID_TAB_LOG_EXCEPTION . '">
                            <p><input type="submit" name="upload_pdf_file" value="Hinzufügen"></p></form></div>';
                $row['pdf_file'] = '<a href="#" data-exception_name="' . $row['id_label'] . '" class="add_teo_pdf"><span class="genericon genericon-edit"></span>PDF Datei für ' . $row['id_label'] . ' hochladen</a>' . $form_data;
            }
            $result[] = [$row['id_label'], $log_string, $wc_string, $row['teo_status_map'], $row['pdf_file']];
        }
        $result = array_merge($heading, $result);
        $id_label_table = new DisplayTable($result, ['class' => 'exception_table', 'csswidths' => [120, 220]]);
        $this->log_rev = $id_label_table->getContent();
    }

    function Execute() {
        parent::Execute();
        if (isset($_REQUEST['tab'])) {
            $this->S_Tab = $_REQUEST['tab'];
        }

        switch ($this->S_Tab) {
            case self::ID_TAB_DTCS_EXCEPTION:
                $this->ExecuteTAB_DTCS();
                break;
            case self::ID_TAB_NEW_EXCEPTION:
                $this->ExecuteTAB_New();
                break;
            case self::ID_TAB_LOG_EXCEPTION:
                $this->ExecuteTAB_Log();
                break;
        }
    }

    function WriteHtml_TabControl() {
        $tabsel = ["", "", "", "", "", "", "", "", ""];
        $tabsel[$this->S_Tab] = " tab_selected";
        $user_can_add_exception = false;
        if ($this->userPtr->user_can('add_teo_exceptions')) $user_can_add_exception = true;
        $tabdef = [

            self::ID_TAB_DTCS_EXCEPTION => [
                'DTC Abweicherlaubnisse',
                'DTC Abweicherlaubnisse',
                true
            ],
            self::ID_TAB_LOG_EXCEPTION => [
                'Log Abweicherlaubnisse',
                'Log Data Abweicherlaubnisse',
                true
            ],
            self::ID_TAB_NEW_EXCEPTION => [
                'Neue Abweicherlaubnis',
                'Neue Abweicherlaubnis',
                $user_can_add_exception,
            ],
        ];
        echo '<div class="tabser"><div class="spc05"></div>';
        $margin = "\n        ";
        foreach ($tabdef as $i => $def) {
            $url = $_SERVER['PHP_SELF'] . '?action=' . $this->action . "&tab=$i";

            $tabcontent = "";
            if ($def[2]) {
                if ($i == $this->S_Tab) $tabcontent = $def[0] . '<span class="ttiptext">' . $def[1] . '</span>';
                else                        $tabcontent = "<a href=\"$url\">" . $def[0] . '</a><span class="ttiptext">' . $def[1] . '</span>';
            } else {
                $tabcontent = '<del>' . $def[0] . '</del><span class="ttiptext">' . $def[1] . '</span>';
            }

            printf('%s<div id="tab0" class="ttip tab%s">%s</div>', $margin, $tabsel[$i], $tabcontent);
        }
        echo "$margin</div>";
    }

    function WriteHtml_Tab_DTCS() {
        ?>
        <div class="row ">
            <div class="columns twelve">
                <?php echo $this->dtcs_rev; ?>
            </div>
        </div>
        <?php
    }

    function WriteHtml_Tab_Log() {
        ?>
        <div class="row ">
            <div class="columns twelve">
                <?php echo $this->log_rev; ?>
            </div>
        </div>
        <?php
    }

    function WriteHtmlContent($options = "") {

        parent::WriteHtmlContent($options);
        ?>
        <h1>Verwaltung Abweicherlaubnisse</h1>
        <br>
<?php
        $this->WriteHtml_TabControl();
        ?>
        <div class="tabcontent">
        <?php
        if (isset($this->msg)) echo $this->msg;
        switch ($this->S_Tab) {
            case self::ID_TAB_NEW_EXCEPTION:
                if (!$this->userPtr->user_can('add_teo_exceptions')) break;
                include $_SERVER['STS_ROOT'] . "/actions/html/Form_Engg_New_Teo.php";
                break;
            case self::ID_TAB_DTCS_EXCEPTION:
                $this->WriteHtml_Tab_DTCS();
                break;

            case self::ID_TAB_LOG_EXCEPTION:
                $this->WriteHtml_Tab_Log();
                break;
        }
        ?></div><?php
    }
}