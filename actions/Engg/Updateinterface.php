<?php

class ACtion_UpdateInterface extends AClass_TableBase {
    private $selec2;

    function __construct() {

        parent::__construct();
        if ($this->controller) {
            $this->diagnosePtr = $this->controller->GetObject('diagnose');
            $this->leitwartePtr = $this->controller->GetObject('ladeleitwarte');
            $this->user = $this->controller->GetObject('user');
        }
        $this->btnLabels += [
            'showselected' => 'Ausgewählte Fahrzeuge anzeigen',
            'save' => 'Task ausführen',
            'save_list' => 'Auswahlliste speichern'
        ];

        $this->btnEnabled += [
            'showselected' => true,
            'save' => true,
            'save_list' => true
        ];


    }

    function GetHtmlElement_Checkbox($dataRow, $content, $idCol, $id, $attr) {
        if (($idCol == 'selected') && empty($dataRow['c2c']))
            return "";

        return parent::GetHtmlElement_Checkbox($dataRow, $content,
            $idCol, $id, $attr);
    }

    function Init() {
        parent::Init();
        $this->selectList = $this->leitwartePtr->newQuery('userstorage')->where('user_id', '=', $this->user->getUserId())->where('context', '=', 'Selected Vehicles Update Interface')->get('storage_name, storage_id');
    }

    function Execute() {
        parent::Execute();
        if ($this->InputState == INPUT_STATE_EDIT_1) {
            $this->selec1 = $this->diagnosePtr->newQuery('ecus')->get('name, ecu_id');
            $showselects = true;

            if (isset($_POST['ecu_id'])) {
                if ($_SESSION['ecu_id'] != $_POST['ecu_id']) {
                    $showselects = false;
                    unset($_SESSION['task_type_id']);
                }
                $_SESSION['ecu_id'] = $_POST['ecu_id'];
                if (!$this->selec2 = $this->diagnosePtr->newQuery('sota_task_types')->where('ecu_id', '=', $_SESSION['ecu_id'])->get('task_type_id, task_name')) {
                    $this->selec2[0]['task_type_id'] = false;
                    $this->selec2[0]['task_name'] = 'Für diese ECU gibt es keine Tasks';
                }
            }


            if ($showselects && isset($_POST['task_type_id'])) {
                $_SESSION['task_type_id'] = $_POST['task_type_id'];
                $selec3 = $this->diagnosePtr->newQuery('sota_task_options')->where('task_type_id', '=', $_POST['task_type_id'])->get('task_option_id, option_name');
                foreach ($selec3 as $s) {
                    $this->selects[] = ['name' => $s['option_name'],
                        'id' => $s['task_option_id'],
                        'data' => $this->diagnosePtr->newQuery('sota_task_option_values')->where('task_option_id', '=', $s['task_option_id'])->get('option_value')];
                }


            }


        }

    }

    function ExecuteCommand($command) {
        switch ($command) {
            case 'showselected':
                $this->SetState(INPUT_STATE_EDIT_1);

                break;
            case'back':
                $this->SetState(INPUT_STATE_SELECT);
                unset($_SESSION['task_type_id']);
                unset($_SESSION['task_options']);
                unset($_SESSION['ecu_id']);

                break;
            case 'save':
                if (!$this->TaskSpeichern()) {
                    $this->msg = 'Bitte fülle alle Optionen';

                } else {
                    $this->msg = 'Task wird bearbeitet';
                }
                break;
            case 'save_list':
                if ($this->SaveSelected()) {
                    $this->listmsg = 'Auswahliste wurde gespeichert';
                } else {
                    $this->listmsg = 'Bitte gebe der Auswahlliste einen Namen';
                }
            case 'loadlist':

                if (!empty($_POST['selectList'])) {
                    if ($_POST['selectList'] == '-')
                        unset($this->S_selectedVehicles);
                    else
                        $this->setList($_POST['selectList']);

                }

            default:
                parent::ExecuteCommand($command);
                break;
        }

    }

    function setList($storage_id) {
        $vehiclelist = explode(',', $this->leitwartePtr->newQuery('userstorage')->where('storage_id', '=', $storage_id)->getOne('content')['content']);
        $cols = $this->getSelectCols();
        $colscsv = implode(',', $cols);
        //$this->S_selectedVehicles = $this->vehiclesPtr->newQuery()->where('vehicle_id','in', $vehiclelist)->orderBy('vin')->get($colscsv,'vehicle_id');
        $this->S_selectedVehicles = $this->vehiclesPtr->getSimpleSearch(['vehicle_id' => $vehiclelist], $cols, 0, 0, 'vin');

    }

    function TaskSpeichern() {
        $count = 0;
        $i = 0;
        $options = $this->diagnosePtr->newQuery('sota_task_options')->where('task_type_id', '=', $_SESSION['task_type_id'])->get('task_option_id');
        foreach ($options as $o) {
            if (!isset($_POST[$o['task_option_id']])) {
                return false;
            }
        }
        foreach ($this->S_selectedVehicles as $v) {
            $insertarray = [
                'vin' => $v['vin'],
                'c2cbox' => $v['c2c'],
                'comment' => $_POST['comment'],
                'timestamp_created' => 'now()',
                'task_type_id' => $_POST['task_type_id']
            ];
            $result = $this->diagnosePtr->newQuery('sota_tasks')->insert($insertarray, array('task_id', 'vin'));
            foreach ($options as $o) {

                $insertoptions = [
                    'task_id' => $result['task_id'],
                    'task_option_id' => $o['task_option_id'],
                    'option_value' => $_POST[$o['task_option_id']]
                ];
                $this->diagnosePtr->newQuery('sota_tasks_selected_options')->insert($insertoptions);

            }


        }
        return true;

    }

    function SaveSelected() {
        if (!empty($_POST['listname'])) {
            $insertarray = [
                'storage_name' => $_POST['listname'],
                'user_id' => $this->user->getUserId(),
                'content' => implode(',', array_keys($this->S_selectedVehicles)),
                'context' => 'Selected Vehicles Update Interface'
            ];
            return $this->leitwartePtr->newQuery('userstorage')->insert($insertarray);
        } else {
            return false;
        }


    }

    function DefineColConfig() {

        parent::DefineColConfig();
        $this->colConfig ['c2c'] =
            [
                'header' => 'Card2Cloud',
                'db' => ['table' => 'vehicles', 'column' => 'c2cbox', 'search' => 'ilike'],
                'size' => 170,
                'numchar' => 11,


                'max numchar' => 11,
            ];
        $this->colConfig ['penta_id'] =
            [
                'enable' => COL_NOT_USED,
            ];
        $this->colConfig ['penta_kennwort'] =
            [
                'enable' => COL_NOT_USED,
            ];
        $this->colConfig ['parkplatz'] =
            [
                'enable' => COL_NOT_USED,
            ];

        $joker = [
            'all' => '-- alle --',
            'sts' => '-- Streetscooter --',
            'edit' => '-- anderer Ort --'];

        $this->colConfig['depot']['search_init'] = 'all';
        $this->colConfig['depot']['.lookup'] = $joker + $this->prodLocationWithStsPool;

    }


    function WriteHtmlContent() {
        parent::WriteHtmlContent();
        include $_SERVER['STS_ROOT'] . "/actions/Engg/Updateinterface/Updateinterface.table.php";
    }
}

?>