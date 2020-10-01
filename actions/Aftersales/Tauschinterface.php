<?php
define('INPUT_STATE_EVALUATION', 9);
define('INPUT_STATE_CHANGEPART', 8);
define('INPUT_STATE_SELECTMODE', 7);

class ACtion_TauschInterface extends AClass_TableBase {


    function __construct() {
        parent::__construct();
        if ($this->controller) {
            $this->leitwartePtr = $this->controller->GetObject('ladeleitwarte');
        }
        $this->btnLabels += ['save' => "Teil-Auswahl bestätigen",
            'commit' => "Tausch durchführen"
        ];


        $this->btnEnabled += ['back' => true,
            'save' => true,
            'commit' => true
        ];

    }

    function DefineColConfig() {
        parent::DefineColConfig();

        $this->colConfig ['vin'] += [
            'html' => 'VinAsLink'
        ];
        $this->colConfig ['parkplatz'] =
            [
                'enable' => COL_NOT_USED,
            ];

    }

    function Execute() {
        parent::Execute();
        switch ($this->InputState) {
            case INPUT_STATE_SELECTMODE:
                if (isset($_GET['vin'])) {
                    $_SESSION['vid'] = $_GET['vin'];
                }
                break;
            case INPUT_STATE_CHANGEPART:


                if (isset($_POST['inputsearch'])) {
                    $input = '%' . $_POST['inputsearch'] . '%';
                    $qry = '(SELECT distinct coalesce(exchange_part_number, ebom_variant_components.part_number) as part_number
                                FROM vehicles
                                JOIN ebom_variant_components ON vehicle_variant_id = vehicles.vehicle_variant
                                LEFT JOIN component_exchanges ON component_exchanges.ebom_part_number = ebom_variant_components.part_number 
                                AND component_exchanges.parent_id = ebom_variant_components.parent_id AND component_exchanges.vehicle_id = vehicles.vehicle_id
                                LEFT JOIN components as exchange_components ON exchange_part_number = exchange_components.part_number
                                WHERE vehicles.vehicle_id = ' . $_SESSION['vid'] . ' AND ebom_variant_components.verbaut_bis is null) as partlist';
                    $parts = $this->leitwartePtr->newQuery($qry)
                        ->multipleAndWhere('components.part_number', 'like', $input, 'OR', 'components.part_name', 'like', $input)
                        ->join('components', 'components.part_number = partlist.part_number')
                        ->OrderBy('part_number')
                        ->get('partlist.part_number, part_name');

                    $this->inputoptions = $this->HtmlOption($parts, $_POST['inputselect']);
                } else {
                    $qry = '(SELECT distinct coalesce(exchange_part_number, ebom_variant_components.part_number) as part_number
                                FROM vehicles
                                JOIN ebom_variant_components ON vehicle_variant_id = vehicles.vehicle_variant
                                LEFT JOIN component_exchanges ON component_exchanges.ebom_part_number = ebom_variant_components.part_number
                                AND component_exchanges.parent_id = ebom_variant_components.parent_id AND component_exchanges.vehicle_id = vehicles.vehicle_id
                                LEFT JOIN components as exchange_components ON exchange_part_number = exchange_components.part_number
                                WHERE vehicles.vehicle_id = ' . $_SESSION['vid'] . ' AND ebom_variant_components.verbaut_bis is null) as partlist';
                    $parts = $this->leitwartePtr->newQuery($qry)->join('components', 'components.part_number = partlist.part_number')
                        ->OrderBy('part_number')
                        ->get('partlist.part_number, part_name');
                    $this->inputoptions = $this->HtmlOption($parts, $_POST['inputselect']);
                }
                if (isset($_POST['exchangesearch'])) {
                    if (strlen($_POST['exchangesearch']) > 10) {
                        $input = '%' . $_POST['exchangesearch'] . '%';
                        $parts = $this->leitwartePtr->newQuery('components')
                            ->multipleAndWhere('components.part_number', 'like', $input, 'OR', 'components.part_name', 'like', $input)->get('part_number, part_name');
                        $this->exchangeoptions = $this->HtmlOption($parts, $_POST['exchangeselect']);
                    } else {
                        $this->exchangeoptions = '<option disabled>Bitte suche nach mehr als 10 Zeichen</option>';
                    }
                } else {
                    $this->exchangeoptions = '<option disabled>Bitte suche ein Teil</option>';

                }
                $reasons = $this->leitwartePtr->newQuery('exchange_reasons')->get('*');
                $this->reasonoptions = $this->ReasonHtmlOption($reasons, $_POST['reasonselect']);
                break;
            case INPUT_STATE_SAVE:
                break;
            case INPUT_STATE_EVALUATION:
                $this->processExchange();
                break;

        }

    }

    function HtmlOption($parts, $selected) {
        $options = '';
        foreach ($parts as $p) {
            $options .= '<option value="' . $p['part_number'] . '"';
            if ($selected == $p['part_number']) {
                $options .= 'selected';
            }
            $options .= '>' . $p['part_name'] . '(' . $p['part_number'] . ')</option>';
        }
        return $options;
    }

    function ParentHtmlOption($parts) {
        $options = '';
        foreach ($parts as $p) {
            $options .= '<option value="' . $p['parent_id'] . '"';
            $options .= '>' . $p['parent_id'] . '(' . $p['structure_level'] . ')</option>';
        }
        return $options;
    }

    function ReasonHtmlOption($reasons, $selected) {
        $options = '';
        foreach ($reasons as $r) {
            $options .= '<option value="' . $r['reason_id'] . '"';
            if ($selected == $r['reason_id']) {
                $options .= 'selected';
            }
            $options .= '>' . $r['reason'] . '</option>';
        }
        return $options;
    }

    function ExchangeParent($parts) {
        $options = '';
        foreach ($parts as $p) {
            $options .= '<option value="' . $p['parent_id'] . '"';
            $options .= '>' . $p['parent_id'] . '</option>';
        }
        return $options;
    }

    function ExecuteCommand($command) {
        switch ($command) {
            case 'changePart':
                $this->SetState(INPUT_STATE_CHANGEPART);
                break;
            case 'selectMode':
                $this->SetState(INPUT_STATE_SELECTMODE);
                break;
            case 'back':
                if ($this->InputState < 8) {
                    $this->SetState(INPUT_STATE_SELECT);
                } else {
                    $this->SetState($this->InputState - 1);
                }

                break;
            case 'save':
                if (isset($_POST['inputselect']) && isset($_POST['exchangeselect'])) {
                    $this->GetParents();
                } else {
                    $this->msg = "Bitte wählen sie beide Teile aus";
                }
                break;
            case 'commit':
                $this->getParents();
                if ($_POST['inputserial']) {
                    $this->processSerials($_POST['inputserial'], $_POST['inputselect']);
                }
                if ($_POST['exchangeserial']) {
                    $this->processSerials($_POST['exchangeserial'], $_POST['exchangeselect']);
                }

                if (isset($_POST['parentselect']) && isset($_POST['reasonselect'])) {
                    $this->SetState(INPUT_STATE_EVALUATION);
                } else if (isset($_POST['reasonselect'])) {
                    $this->msg = "Bitte wähle eine Stelle aus an der dieses Teil ausgetauscht werden soll";
                } else if (isset($_POST['parentselect'])) {
                    $this->msg = "Bitte wähle einen Austauschgrund";
                }
                break;
            default:
                parent::ExecuteCommand($command);
        }
    }

    function Init() {
        parent::Init();
    }

    function InitState() {
        parent::InitState();
        $this->colConfig ['selected']['enable'] = COL_INVISIBLE;


    }

    function WriteHtmlContent() {

        switch ($this->InputState) {
            case INPUT_STATE_CHANGEPART:
                AClass_Base::WriteHtmlContent();
                break;
            case INPUT_STATE_SELECTMODE:
                AClass_Base::WriteHtmlContent();
                break;
            case INPUT_STATE_EVALUATION:
                AClass_Base::WriteHtmlContent();
                break;
            default:
                parent::WriteHtmlContent();

        }
        include $_SERVER['STS_ROOT'] . "/actions/Aftersales/Tauschinterface/Tauschinterface.table.php";
    }

    function GetHtmlElement_VinAsLink($RowData, $content, $column, $id, $attr) {
        return <<<Html
        <a href="{$_SERVER['PHP_SELF']}?action=tauschinterface&{$column}={$id}&command=selectMode">{$content}</a>
Html;

    }

    function getParents() {
        $parts = $this->leitwartePtr->newQuery('vehicles')
            ->where('vehicles.vehicle_id', '=', $_SESSION['vid'])
            ->where('ebom_variant_components.part_number', '=', $_POST['inputselect'])
            ->where('ebom_variant_components.verbaut_bis', 'is null')
            ->where('component_exchanges.ebom_part_number', 'is null')
            ->join('ebom_variant_components', 'vehicle_variant_id=vehicles.vehicle_variant')
            ->join('component_exchanges', 'ebom_variant_components.part_number = component_exchanges.ebom_part_number AND ebom_variant_components.parent_id = component_exchanges.parent_id AND vehicles.vehicle_id = component_exchanges.vehicle_id', 'left join')
            ->get('ebom_variant_components.parent_id, ebom_variant_components.structure_level');
        $exchanged = $this->leitwartePtr->newQuery('vehicles')
            ->where('vehicles.vehicle_id', '=', $_SESSION['vid'])
            ->where('component_exchanges.exchange_part_number', '=', $_POST['inputselect'])
            ->join('component_exchanges', 'component_exchanges.vehicle_id = vehicles.vehicle_id')
            ->get('component_exchanges.parent_id');
        $this->parentoptions = $this->ParentHtmlOption($parts);
        $this->parentoptions .= $this->ExchangeParent($exchanged);
    }

    function processExchange() {
        if ($oldchange = $this->leitwartePtr->newQuery('component_exchanges')->where('vehicle_id', '=', $_SESSION['vid'])->where('exchange_part_number', '=', $_POST['inputselect'])->where('parent_id', '=', $_POST['parentselect'])->getOne('*')) {
            $this->leitwartePtr->newQuery('component_exchanges_history')->insert($oldchange);
            $this->leitwartePtr->newQuery('component_exchanges')
                ->where('vehicle_id', '=', $_SESSION['vid'])
                ->where('exchange_part_number', '=', $_POST['inputselect'])
                ->where('parent_id', '=', $_POST['parentselect'])
                ->update(['ebom_part_number', 'exchange_part_number', 'parent_id', 'exchange_date', 'user_id', 'reason_id', 'comment'], [$oldchange['ebom_part_number'], $_POST['exchangeselect'], $_POST['parentselect'], date('Y-m-d G:i:s O'), $_SESSION['sts_userid'], $_POST['reasonselect'], $_POST['commentary']]);
        } else {
            $this->leitwartePtr->newQuery('component_exchanges')
                ->insert(['vehicle_id' => $_SESSION['vid'], 'ebom_part_number' => $_POST['inputselect'], 'exchange_part_number' => $_POST['exchangeselect'], 'parent_id' => $_POST['parentselect'], 'exchange_date' => date('Y-m-d G:i:s O'), 'user_id' => $_SESSION['sts_userid'], 'reason_id' => $_POST['reasonselect'], 'comment' => $_POST['commentary']]);
        }
    }

    function processSerials($serial, $part_number) {
        $prevserial = $this->diagnosePtr->newQuery('serial_numbers')
            ->where('serial_number', '=', $serial)
            ->orderBy('timestamp', 'desc')->getOne('*');
        if ($prevserial['vin'] != $_SESSION['vin'] || $prevserial['part_number'] != $part_number) {
            $this->diagnosePtr->newQuery('serial_numbers')
                ->insert(['vin' => $_SESSION['vin'], 'timestamp' => date('Y-m-d G:i:s O'), 'part_number' => $part_number, 'serial_number' => $serial]);
        }
    }

}