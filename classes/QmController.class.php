<?php

class QmController extends QsController {
    protected $screwdata;
    protected $mesurements;
    protected $sticktable;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {


        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->screwdata = new Schraubdaten($this->diagnosePtr);
        $this->mesurements = new Schluesselmessung($this->diagnosePtr);
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        $this->content = '';
        $this->vehicles = null;
        $this->displayHeader = $this->container->getDisplayHeader();

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . 'test');


        $this->action = $this->requestPtr->getProperty('action');

        //rows per page
        $this->numrows = 50;
        //total rows
        $this->totalrows = 500;

        $this->production_depots_only = false;
        $this->setupDepots();
        $this->setupHeaders();
        $this->setupQSUsers();

        $this->common_action = $this->requestPtr->getProperty('common_action');

        $filter_vehicles = $this->getFilteredVehicles();

        $this->setupQSForms();

        $this->qs_fault_search = new CommonFunctions_QsFaultSearch($ladeLeitWartePtr, $this->displayHeader, $user, $requestPtr, $this->common_action, $filter_vehicles);
        $this->qs_fault_search->genSearchForm();

        if (isset($this->action))
            call_user_func(array($this, $this->action));
        $result = array();

        $headings[]["headingone"] = $this->headers;
        $result = array_merge($headings, $result);
        $this->qs_vehicles = new DisplayTable($result, array('id' => 'qs_vehicles_list'));

        if (($this->action == '') or ($this->action == 'home')) {
            $this->qs_vehicles = new DisplayTable($result, array(
                'id' => 'qs_vehicles_list'
            ));

            $this->displayHeader->enqueueJs("sts-custom-qs", "js/sts-custom-qs.js");

            $this->todays_vehicles = array();
        }

        $this->displayHeader->printContent();

        $this->printContent();

    }

    public function setupQSUsers() {
        $this->qm_users = array(
            90 => array('qm_user_id' => 90, 'qm_user' => 'Dirk.Rompf', 'qm_pass' => 'Chi7pe'),
            92 => array('qm_user_id' => 92, 'qm_user' => 'Timo.Schulze', 'qm_pass' => 'jh3Kai'),
            753 => array('qm_user_id' => 753, 'qm_user' => 'Ralf.Frohn', 'qm_pass' => 'geeSho'),
            961 => array('qm_user_id' => 961, 'qm_user' => 'nils.becker', 'qm_pass' => 'Aicai2'),
            1138 => array('qm_user_id' => 1138, 'qm_user' => 'Juergen.Ulrich', 'qm_pass' => 'ia9ieK')
        );
    }

    public function setupHeaders() {

        $this->headers = array('VIN', 'IKZ', 'AKZ', 'Penta Kennwort', 'C2CBox ID', 'Variant',
            array('Penta Artikel', array('data-filter' => 'false', 'data-sorter' => 'false')),
            'Produktionsort',
            array('TEO Status', array('data-filter' => 'false', 'data-sorter' => 'false')),
            array('Sondergenehmigung', array('data-filter' => 'false', 'data-sorter' => 'false')),
            array('QM gesperrt', array('data-filter' => 'false', 'data-sorter' => 'false')),
            array('QS Fehler', array('data-filter' => 'false', 'data-sorter' => 'false'))
        );
    }

    public function processVehicles(&$vehicles, $set_param = null) {
        foreach ($vehicles as &$vehicle) {
            $vehicle['diagnose_status'] = $vehicle['processed_diagnose_status'];
            if (empty($vehicle['code'])) $vehicle['code'] = ' ';
            if (empty($vehicle['ikz'])) $vehicle['ikz'] = ' ';
            if (empty($vehicle['code'])) $vehicle['code'] = ' ';
            if (empty($vehicle['penta_kennwort'])) $vehicle['penta_kennwort'] = ' ';

            if (!empty($vehicle['diagnose_status_time'])) $vehicle['diagnose_status_time'] = date('Y-m-d H:i', strtotime($vehicle['diagnose_status_time']));
            else  $vehicle['diagnose_status_time'] = '';

            if (empty($vehicle['tables'])) $vehicle['status_extra_data'] = 'Keine Daten Verfügbar!';


            if ($vehicle['processed_diagnose_status'] != 'PASSED') {
                // 			$status_content='<a href="#" class="open_status_control" data-targetid="status_data_'.$vehicle['vehicle_id'].'">'.$vehicle['diagnose_status'].'</a><br>'.$vehicle['diagnose_status_time'];
                $status_content = '<a href="#" class="fetcherror"
            data-vehicle_id="' . $vehicle['vehicle_id'] . '"
            data-vin="' . $vehicle['vin'] . '"
            data-vehicle_variant="' . $vehicle['vehicle_variant'] . '"
            data-diagnostic_session_id="' . $vehicle['diagnostic_session_id'] . '"
            >' . $vehicle['processed_diagnose_status'] . '</a><br>' . $vehicle['diagnose_status_time'];


            } else {
                $status_content = $vehicle['processed_diagnose_status'] . '<br>' . $vehicle['diagnose_status_time'];

            }

            if (!empty($vehicle['c2cbox'])) {
                if ($vehicle['online'] == 't')
                    $vehicle['c2cbox'] = $vehicle['c2cbox'] . '<br> Online seit ' . $vehicle['timestamp'];
                else
                    $vehicle['c2cbox'] = $vehicle['c2cbox'] . '<br> Offline seit ' . $vehicle['timestamp'];
            }

            $vehicle['processed_diagnose_status'] = $status_content;

            if ($vehicle['special_qs_approval'] == 't') $vehicle['special_qs_approval'] = 'ja';
            else $vehicle['special_qs_approval'] = 'nein';
            $params_str = '';
            if ($vehicle['qmlocked'] == 't') {
                $org_val = 't';
            } else {
                $org_val = 'f';
            }

            if ($vehicle['qmlocked'] == 't' && $set_param != 'unlock') $params_str .= 'checked="checked"';
            if ($set_param == 'lock') $params_str .= 'checked="checked"';

            $class_name = '';

            if (!empty($set_param) && $set_param == 'lock') $class_name = 'to_lock';
            if (!empty($set_param) && $set_param == 'unlock') $class_name = 'to_unlock';

            $vehicle['vin'] = '<span class="' . $class_name . '">' . $vehicle['vin'] . '</span>';
            if (in_array($vehicle['depot_id'], $this->productionDepots)) {
                $vehicle['qmlocked'] = '<input type="checkbox" class="setQMLock" data-orgval="' . $org_val . '" data-vehicleid="' . $vehicle['vehicle_id'] . '" name="qmlocked_' . $vehicle['vehicle_id'] . '" ' . $params_str . '>';
            } else {
                if ($org_val == 't') $vehicle['qmlocked'] = 'Gesperrt';
                elseif ($org_val == 'f') $vehicle['qmlocked'] = 'Nicht gesperrt';
            }
            $qm_lock_history = $this->ladeLeitWartePtr->newQuery('qm_lock_history')->where('vehicle_id', '=', $vehicle['vehicle_id'])->getVal('count(*)');
            if ($qm_lock_history) $vehicle['qmlocked'] .= '<a href="#" class="show_qmlock_info" data-vehicle_id="' . $vehicle['vehicle_id'] . '" data-vin="' . strip_tags($vehicle['vin']) . '" >Kommentare<span class="genericon genericon-info"></span></a>';
            if (date('Y-m-j') == date('Y-m-j', strtotime($vehicle['diagnose_status_time']))) {
                $this->todays_vehicles[] = $vehicle['vehicle_id'];
            }

            if ($vehicle['rectified_cnt'] || $vehicle['open_fault_cnt']) {
                $fault_cnt_ctrl = '<span id="show_all_faults_wrap_' . $vehicle['vehicle_id'] . '"><a href="#" class="show_all_faults" data-vehicle_id="{vehicle_id}" data-vin="{vin}">' . $vehicle['open_fault_cnt'] . ' offene / ' . $vehicle['rectified_cnt'] . ' behobene Fehler anzeigen</a></span>';
            } else
                $fault_cnt_ctrl = '<span id="show_all_faults_wrap_' . $vehicle['vehicle_id'] . '">Keine Fehler eingetragen</span>';

            $vehicle['qs_fault'] = str_replace(array("{vehicle_id}", "{vin}"), array($vehicle['vehicle_id'], strip_tags($vehicle['vin'])),
                $fault_cnt_ctrl);

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

    function ajaxFetchErrorDetails() {
        $vehicle['vin'] = $_POST['vin'];
        $vehicle['vehicle_id'] = $_POST['vehicle_id'];;
        $vehicle['diagnostic_session_id'] = $_POST['diagnostic_session_id'];
        $vehicle['vehicle_variant'] = $_POST['vehicle_variant'];
        $vehicle['tabledata'] .= '';
        $this->ladeLeitWartePtr->vehiclesPtr->fetchTeoError($vehicle);
        foreach ($vehicle['tables'] as $dtable) {
            if (!empty($dtable)) {
                $htmltable = new DisplayTable(array_merge($dtable['headings'], $dtable['content']), array('widths' => $dtable['colWidths']));
                $vehicle['tabledata'] .= $dtable['header'] . $htmltable->getContent();
            }
        }
        echo $vehicle['tabledata'];
        exit(0);
    }

    public function ajaxRows() {
        $page = $this->requestPtr->getProperty('page');
        $size = $this->requestPtr->getProperty('size');
        $fcol = $this->requestPtr->getProperty('filter');
        $scol = $this->requestPtr->getProperty('column'); //1 desc 0 asc
        $to_lock_vehicles = $this->requestPtr->getProperty('to_lock_vehicles');
        if (!empty($to_lock_vehicles)) $to_lock_vehicles = explode(',', $to_lock_vehicles);
        $to_unlock_vehicles = $this->requestPtr->getProperty('to_unlock_vehicles');
        if (!empty($to_unlock_vehicles)) $to_unlock_vehicles = explode(',', $to_unlock_vehicles);
        $filtered_vehicles = $this->requestPtr->getProperty('filtered_vehicles'); // 1 desc 0 asc

        $depots = $this->requestPtr->getProperty('depot_vals');
        $depots = trim(filter_var($depots, FILTER_SANITIZE_STRING));
        if (!empty($depots)) {

            $depots = explode(',', $depots);
            if (!array_diff($depots, $this->productionDepots))
                $productionDepotsOnly = true;
            else
                $productionDepotsOnly = false;
        }

        $result['headers'] = explode(',', 'vin,ikz,code,penta_kennwort,c2cbox,windchill_variant_name,penta_number,dname,processed_diagnose_status,special_qs_approval,qmlocked,qs_fault');
        //step one : process all vehicles that have to be locked, and add these vehicles to exclude_vehicles for processing later
        $exclude_vehicles = array();
        $lockrows = array();
        if (!empty($to_lock_vehicles)) {
            $exclude_vehicles = array_merge($exclude_vehicles, $to_lock_vehicles);
            $lockrows = $this->ladeLeitWartePtr->vehiclesPtr->getEOLVehiclesNew($depots, 'saveQS', 0, sizeof($to_lock_vehicles), null, null, $to_lock_vehicles);
            $this->processVehicles($lockrows, 'lock');
        }
        //step two : process all vehicles that have to be unlocked, and add these vehicles to exclude_vehicles for processing later
        $unlockrows = array();
        if (!empty($to_unlock_vehicles)) {
            $exclude_vehicles = array_merge($exclude_vehicles, $to_unlock_vehicles);
            $unlockrows = $this->ladeLeitWartePtr->vehiclesPtr->getEOLVehiclesNew($depots, 'saveQS', 0, sizeof($to_unlock_vehicles), null, null, $to_unlock_vehicles);
            $this->processVehicles($unlockrows, 'unlock');
        }

        if (!empty($filtered_vehicles) && $filtered_vehicles != 'error')
            $filtered_vehicles = explode(',', $filtered_vehicles);
        elseif (!empty($filtered_vehicles) && $filtered_vehicles == 'error') {
            echo json_encode($result); // just output empty rows to satisfy tablesorter and exit from here
            exit(0);
        } else
            $filtered_vehicles = null;

        //step three: process all vehicles that are not in the exclude vehicles list
        $rows = $this->ladeLeitWartePtr->vehiclesPtr->getEOLVehiclesNew($depots, 'saveQS', $page, $size, $fcol, $scol, $filtered_vehicles, $exclude_vehicles);
        $this->processVehicles($rows);

        $rows = array_merge($lockrows, $unlockrows, $rows);

        $result['total_rows'] = $this->totalrows;
        $result['fcol'] = json_encode($fcol);
        $result['page'] = $page;
        $result['size'] = $this->numrows;
        $result['rows'] = $rows;

        echo json_encode($result);
        exit(0);
    }

    /***
     * If qm_user and qm_pass matches against the details in $this->qm_users returns true else false
     * @param string $qm_user
     * @param string $qm_pass
     * @return boolean
     */
    public function authenticateQm($qm_user, $qm_pass) {
        return true;
    }

    public function lockAllToday() {
        $qm_user = $this->requestPtr->getProperty('qs_qm_user');
        $qm_pass = $this->requestPtr->getProperty('qs_qm_pass');
        $todays_vehicles = $this->requestPtr->getProperty('todays_vehicles');
        $vehicle_ids = explode(',', $todays_vehicles);
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', 'IN', $vehicle_ids)->get('vehicle_id,vin');
        if ($this->authenticateQm($qm_user, $qm_pass) === true) {
            foreach ($vehicles as $vehicle) {

                if ($this->ladeLeitWartePtr->vehiclesPtr->getQmVehicleLock($vehicle['vehicle_id']) != 't') {
                    if (!$this->ladeLeitWartePtr->vehiclesPtr->setQmVehicleLock($vehicle['vehicle_id'], 'TRUE'))
                        $this->msgs[] = 'Fehler beim Speichern - ' . $vehicle['vin'];
                    else
                        $this->msgs[] = $vehicle['vin'] . ' gesperrt.';
                }

            }
        } else {
            $this->msgs[] = '<span class="error_msg">Benutzername oder Passwort falsch</span>';
        }


    }

    public function unlockAllToday() {
        $qm_user = $this->requestPtr->getProperty('qs_qm_user');
        $qm_pass = $this->requestPtr->getProperty('qs_qm_pass');

        $todays_vehicles = $this->requestPtr->getProperty('todays_vehicles');

        $vehicle_ids = explode(',', $todays_vehicles);

        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', 'IN', $vehicle_ids)->get('vehicle_id,vin');

        if ($this->authenticateQm($qm_user, $qm_pass) === true) {
            foreach ($vehicles as $vehicle) {

                if ($this->ladeLeitWartePtr->vehiclesPtr->getQmVehicleLock($vehicle['vehicle_id']) != 'f') {
                    if (!$this->ladeLeitWartePtr->vehiclesPtr->setQmVehicleLock($vehicle['vehicle_id'], 'FALSE'))
                        $this->msgs[] = 'Fehler beim Speichern - ' . $vehicle['vin'];
                    else
                        $this->msgs[] = $vehicle['vin'] . ' entsperrt.';
                }

            }
        } else {
            $this->msgs[] = '<span class="error_msg">Benutzername oder Passwort falsch</span>';
        }


    }

    public function saveQMLockComment($comment, $vehicle_id, $old_status, $new_status) {
        $insertArray = [
            'vehicle_id' => $vehicle_id,
            'update_ts' => date('Y-m-d H:i:sO'),
            'userid' => $this->user->getUserId(),
            'old_status' => $old_status,
            'new_status' => $new_status,
            'qmcomment' => $comment
        ];
        $this->ladeLeitWartePtr->newQuery('qm_lock_history')->insert($insertArray);

    }

    /***
     * saveQS
     * authenticates qm user against the $this->qm_users details and sets a msg string
     */
    public function saveQM() {
        $qm_user = $this->requestPtr->getProperty('qs_qm_user');
        $qm_pass = $this->requestPtr->getProperty('qs_qm_pass');
        $to_lock_vehicles = $this->requestPtr->getProperty('to_lock_vehicles');
        if (!empty($to_lock_vehicles)) $to_lock_vehicles = explode(',', $to_lock_vehicles);
        $to_unlock_vehicles = $this->requestPtr->getProperty('to_unlock_vehicles');
        if (!empty($to_unlock_vehicles)) $to_unlock_vehicles = explode(',', $to_unlock_vehicles);


        $update_errors = false;
        if ($this->authenticateQm($qm_user, $qm_pass) === true) {
            foreach ($to_lock_vehicles as $vehicle_id) {
                $vin = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', '=', $vehicle_id)->getVal('vin');
                if ($this->ladeLeitWartePtr->vehiclesPtr->getQmVehicleLock($vehicle_id) != 't') {
                    if (!$this->ladeLeitWartePtr->vehiclesPtr->setQmVehicleLock($vehicle_id, 'TRUE'))
                        $this->msgs[] = 'Fehler beim Speichern - ' . $vin;
                    else {
                        $comment = $this->requestPtr->getProperty('qmlock_comment_' . $vehicle_id);
                        $this->saveQMLockComment($comment, $vehicle_id, 'f', 't');
                        $this->msgs[] = $vin . ' gesperrt!';
                    }
                }
            }
            foreach ($to_unlock_vehicles as $vehicle_id) {
                $vin = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', '=', $vehicle_id)->getVal('vin');
                if ($this->ladeLeitWartePtr->vehiclesPtr->getQmVehicleLock($vehicle_id) != 'f') {
                    if (!$this->ladeLeitWartePtr->vehiclesPtr->setQmVehicleLock($vehicle_id, 'FALSE'))
                        $this->msgs[] = 'Fehler beim Speichern - ' . $vin;
                    else {
                        $this->saveQMLockComment($comment, $vehicle_id, 't', 'f');
                        $this->msgs[] = $vin . ' entsperrt!';

                    }
                }
            }

        } else {
            $this->msgs[] = '<span class="error_msg">Benutzername oder Passwort falsch</span>';
        }
    }

    function schraubdaten_admin() {
        $this->sticktable = new AClass_StickyHeaderTable();
        $this->sticktable->setHeight(450);
        $this->sticktable->SetupHeaderFiles($this->displayHeader);
        if (!empty($_FILES)) {
            if (!$this->screwdata->readCsv($_FILES['datencsv']['tmp_name'])) {
                $this->screwdata->deletenewdata();
                $this->msgs[] = "Die Datei hat das falsche Format oder die Spalten unterscheiden sich von der Vorgabe.";
            }
        } else {
            if (!empty($_POST['discard'])) {
                $this->screwdata->deletenewdata();
                $this->msgs[] = "Daten wurden verworfen.";
            }
            if (!empty($_POST['save'])) {
                $this->screwdata->savetodatabase();
                $this->msgs[] = "Die Daten wurden erfolgreich in die Datenbank geladen.";
            }
        }


    }

    function schraubdaten_messung() {
        $this->sticktable = new AClass_StickyHeaderTable();
        $this->sticktable->setHeight(450);
        $this->sticktable->SetupHeaderFiles($this->displayHeader);


        if (!empty($_FILES)) {
            $this->mesurements->readCsv($_FILES['messungcsv']['tmp_name']);
            $this->mesurements->reduceNew();
        }


        if (!empty($_POST['max'])) {
            $this->mesurements->setmax($_POST['max']);
        } else if ($this->mesurements->getnewdata()) {
            $this->mesurements->setmax(count($this->mesurements->getnewdata()));
        } else {
            $this->mesurements->setmax(100);
        }


        if (!empty($_POST['screw_id'])) {
            $this->mesurements->setscrew_id($_POST['screw_id']);
        } else if ($this->mesurements->getnewdata()) {
            $this->mesurements->setscrew_id("Alle");
        } else {
            $this->mesurements->setscrew_id('BCS-0001');
        }

        if ($this->mesurements->getnewdata()) {
            $this->mesurements->setdropdown(true);
            if (!empty($_POST['discard'])) {
                $this->mesurements->deletenewdata();
                $this->msgs[] = "Daten wurden verworfen.";
            }

            if (!empty($_POST['save'])) {

                if ($this->mesurements->savetodatabase()) {
                    $this->msgs[] = "Die Daten wurden erfolgreich in die Datenbank geladen.";
                } else {
                    $this->msgs[] = "Es wurden keine entsprechenden Schraubdaten zu den Messdaten gefunden die Sie einlesen wollten. Bitte lesen Sie zuerst die entsprechenden Schraubdaten ein.";
                }

            }
        } else {
            if ($this->mesurements->setolddata()) {
                $this->mesurements->setdropdown();
                $this->mesurements->print_graph();
            } else {
                $this->msgs[] = "Zu dieser Schraube existieren keine Messdaten";
            }
        }


    }

    function schraubdaten_uebersicht() {
        $this->sticktable = new AClass_StickyHeaderTable();
        $this->sticktable->setHeight(450);
        $this->sticktable->SetupHeaderFiles($this->displayHeader);
        $this->mesurements->setoverview();
    }


}