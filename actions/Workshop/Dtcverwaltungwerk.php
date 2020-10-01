<?php
define('STATE_SESSION_SELECTION', 0);
define('STATE_DTCS', 1);

class ACtion_Dtcverwaltungwerk extends AClass_Base {

    private $head = array(
        'diagnostic_session_id' => 'Session',
        'ecu' => 'ECU',
        'dtc_number' => 'DTC-Nummer',
        'age' => 'Alter',
        'count' => 'Menge',
        'text' => 'Text',
        'system_mode' => 'System-Modus',
        'date' => 'Datum',
        'diagnosis_version' => 'System Version'
    );
    private $system_map = array(
        'DIAG' => 'SIA Werkstatt',
        'ASLS' => 'SIA Aftersales',
        'EOLT' => 'TEO',
        'REST' => '',
        'unknown' => ''
    );

    function __construct() {
        parent::__construct();
        if ($this->controller) {
            $this->diagnosePtr = $this->controller->getObject('diagnose');
        }
    }

    function Init() {
        parent::Init();
        if (!empty($_GET['vin'])) {
            $_SESSION['vin'] = $_GET['vin'];
        }
    }

    function Execute() {
        parent::Execute();
        switch ($_GET['command']) {
            case 'sessionlist':
                $this->getSessionList();
                $this->state = STATE_SESSION_SELECTION;
                break;
            case 'dtclist':
                $this->getDtcTable();
                $this->state = STATE_DTCS;
                break;
            case 'generateExport':
                $this->getDtcTable();
                $this->generateExport($_GET['param']);
                break;

        }
    }

    function generateExport($format) {
        $helper = new SpreadsheetHelper();
        $spreadsheet = $helper->CreateSpreadsheet();

        $spreadsheet->getProperties()->setCreator('Sts')
            ->setLastModifiedBy('Sts')
            ->setTitle('DTC Data')
            ->setSubject('DTC Data')
            ->setDescription('DTC Data');

        foreach ($this->dtcdata[0] as $k => $v) {
            $arr[] = $this->head[$k];
        }
        $arraydata[] = $arr;
        foreach ($this->dtcdata as $d) {
            unset($arr);
            foreach ($d as $k => $v) {
                if ($k == 'system_mode') {
                    $arr[] = $this->system_map[$v];
                } else {
                    $arr[] = $v;
                }
            }
            $arraydata[] = $arr;
        }


        $spreadsheet->getActiveSheet()
            ->fromArray(
                $arraydata,  // The data to set
                NULL,        // Array values with this value will not be set
                'A1'         // Top left coordinate of the worksheet range where
            //    we want to set these values (default is A1)
            );

        foreach (range('A', $spreadsheet->getActiveSheet()->getHighestDataColumn()) as $col) {
            $spreadsheet->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->setTitle('DTCS ' . $_GET['session']);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = $helper->CreateWriter($spreadsheet, $format);

        // $writer->save('php://output');
        ob_clean();

        $file = tempnam(sys_get_temp_dir(), '');
        $writer->save($file);
        if ($format == 'Xlsx' || $format == 'Ods') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $this->vin . '(' . $this->session_id . ').' . $format . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . filesize($file));
        } else if ($format == 'Csv') {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $this->vin . '(' . $this->session_id . ')' . $format . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
        }
        readfile($file);
        exit;
    }

    function WriteHtmlContent() {
        parent::WriteHtmlContent();
        switch ($this->state) {
            case STATE_SESSION_SELECTION:
                if ($this->sessionList) {
                    echo '<h4>Sessions für ' . $_GET['vin'] . ':</h4>';
                    echo '<table><thead><tr>';
                    foreach ($this->sessionList[0] as $k => $v) {
                        echo '<td>' . $this->head[$k] . '</td>';
                    }
                    echo '<td></td>';
                    echo '</tr></thead><tbody>';
                    foreach ($this->sessionList as $session) {
                        $dtc = false;
                        $ecus = $this->diagnosePtr->newQuery('ecu_data')->where('diagnostic_session_id', '=', $session['diagnostic_session_id'])->get('dtcs');
                        foreach ($ecus as $e) {
                            if ($e['dtcs'] != '{}') {
                                $dtc = true;
                            }
                        }
                        preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $session['date'], $date);
                        echo '<tr>';

                        if ($dtc) {
                            echo '<td><a href="?action=' . $this->action . '&vin=' . $this->vin . '&session=' . $session['diagnostic_session_id'] . '&command=dtclist">' . $session['diagnostic_session_id'] . '</a></td><td>' . $date[0] . '</td><td>' . $this->system_map[$session['system_mode']] . '</td><td></td>';
                        } else {
                            echo '<td>' . $session['diagnostic_session_id'] . '</td><td>' . $date[0] . '</td><td>' . $this->system_map[$session['system_mode']] . '</td><td>(keine DTCs für diese Sitzung vorhanden)</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo 'Für diese VIN liegen keine Sessions vor';
                }
                break;
            case STATE_DTCS:
                if ($this->dtcdata) {

                    echo '<table>';
                    $count = 0;
                    foreach ($this->dtcdata as $data) {
                        echo '<tr>';
                        if ($count == 0) {
                            foreach ($data as $k => $d) {
                                echo '<th>';
                                echo $this->head[$k];
                                echo '</th>';
                            }
                            echo '</tr><tr>';
                        }
                        foreach ($data as $k => $v) {
                            if ($k == 'system_mode') {
                                echo '<td>' . $this->system_map[$v] . '</td>';
                            } else
                                echo '<td>' . $v . '</td>';
                        }
                        echo '</tr>';
                        $count++;
                    }
                    echo '</table>';
                    echo <<<HEREDOC
                    <a href="?action={$this->action}&session={$this->session_id}&vin={$this->vin}&command=generateExport&param=Xlsx" >Download Office XML(XLSX) Export</a><br>
                    <a href="?action={$this->action}&session={$this->session_id}&vin={$this->vin}&command=generateExport&param=Csv" >Download CSV-Export</a><br>
                    <a href="?action={$this->action}&session={$this->session_id}&vin={$this->vin}&command=generateExport&param=Ods" >Download Open Document(ODS) Export</a><br>
                    <button type="submit" disabled>Werkstätten anzeigen(coming soon)</button>
                    
HEREDOC;
                }
                break;
        }
    }

    function getSessionList() {
        $this->sessionList = $this->diagnosePtr->newQuery('general')
            ->where('vin', '=', $_SESSION['vin'])
            ->orderBy('date')
            ->get('distinct diagnostic_session_id, date, system_mode');
    }

    function getDtcTable() {
        if (isset($_GET['session'])) {
            $this->session_id = intval($_GET['session']);
            $query = <<<SQLDOC
                    SELECT
                    	ecu_dtcs.diagnostic_session_id,
                    	ecu_dtcs.system_mode,
                        ecu_dtcs.diagnosis_version,
                        ecu_dtcs.date,
                        d.ecu,
                    	dtc_number,
                    	age,
                    	count,
                    	text
                    FROM (SELECT
                    		diagnostic_session_id,
                    		system_mode,
                            diagnosis_version,
                            date,
                            ecu,
                    		UNNEST(dtcs) as dtc_number
                    	FROM
                    		ecu_data e
                    	INNER JOIN
                    		general USING (diagnostic_session_id)
                    	WHERE
                    		diagnostic_session_id={$this->session_id}
                    	) ecu_dtcs
                    INNER JOIN
                    	dtcs d USING (ecu, dtc_number)
                    LEFT JOIN
                    	dtc_info di ON d.ecu=di.ecu AND di.diagnostic_session_id=ecu_dtcs.diagnostic_session_id AND d.dtc_number=di.dtc
                    	
SQLDOC;
            $qry = $this->diagnosePtr->newQuery();
            $qry->query($query);
            $this->dtcdata = $qry->fetchAll();
        }
    }

}

?>