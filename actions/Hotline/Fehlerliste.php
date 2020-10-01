<?php
define('INPUT_STATE_ERROR_LIST', 7);
define('ERROR_STATE', 8);

class ACtion_Fehlerliste extends AClass_TableBase
{

    private $header = array(
        'timestamp' => 'timestamp',
        'division_id' => 'Division ID',
        'severity_code' => 'Severity Code',
        'description' => 'Beschreibung'
    );


    function __construct()
    {

        parent::__construct();
        if ($this->controller) {
            $this->leitwartePtr = $this->controller->GetObject('ladeleitwarte');
        }

        $this->btnLabels += [
            'back' => 'Zurück zur Auswahl'
        ];

        $this->btnEnabled += [
            'back' => true
        ];

    }


    function DefineColConfig()
    {

        parent::DefineColConfig();

        $this->colConfig['vin'] += [
            'html' => 'VinAsLink'
        ];
        $this->colConfig['parkplatz'] = [
            'enable' => COL_NOT_USED
        ];
        $this->colConfig['akz'] = [
            'header' => 'Kennzeichen',
            'db' => [
                'table' => 'vehicles',
                'column' => 'code',
                'search' => 'ilike'
            ],
            'size' => 170,
            'numchar' => 11,
            'max numchar' => 11
        ];
        $this->colConfig['ikz'] = [
            'header' => 'IKZ',
            'db' => [
                'table' => 'vehicles',
                'column' => 'ikz',
                'search' => 'ilike'
            ],
            'max numchar' => 8,
            'size' => 170
        ];
        $this->colConfig['penta_id'] = [
            'enable' => COL_NOT_USED
        ];
        $this->colConfig['park_position'] = [
            'enable' => COL_NOT_USED
        ];
        $this->colConfig['depot'] = [
            'header' => 'ZSP'
        ];
        $this->colConfig['penta_kennwort'] = [
            'enable' => COL_NOT_USED
        ];
        $this->colConfig['herstellung'] = [
            'enable' => COL_NOT_USED
        ];

    }


    function Execute()
    {

        parent::Execute();
        switch ($this->InputState) {
            case INPUT_STATE_ERROR_LIST:
                $this->errorlist = $this->leitwartePtr->newQuery('errors')
                    ->where('vehicle_id', '=', $_GET['vin'])
                    ->orderBy('timestamp', 'desc')
                    ->get('timestamp, division_id,severity_code, description');

                $this->tablehead = $this->getHead_Html();
                $this->tablebody = $this->getBody_Html();

                break;
        }

    }


    function ExecuteCommand($command)
    {

        switch ($command) {
            case 'errorList':
                $qry = $this->leitwartePtr->newQuery('vehicles')
                    ->join('depots', 'vehicles.depot_id=depots.depot_id')
                    ->join('divisions', 'depots.division_id=divisions.division_id')
                    ->where('divisions.division_id', '<', 50)
                    ->where('divisions.division_id', '>', 0)
                    ->where('vehicles.vehicle_id', '=', $_GET['vin'])
                    ->get('*');
                if (! empty($qry)) {
                    $this->SetState(INPUT_STATE_ERROR_LIST);
                } else {
                    $this->SetState(ERROR_STATE);
                }
                break;
            case 'back':
                $this->SetState(INPUT_STATE_SELECT);
                break;
            default:
                parent::ExecuteCommand($command);
        }

    }


    function Init()
    {

        parent::Init();

    }


    function InitState()
    {

        parent::InitState();
        $this->colConfig['selected']['enable'] = COL_INVISIBLE;

    }


    function WriteHtmlContent()
    {

        switch ($this->InputState) {
            case INPUT_STATE_ERROR_LIST:
                AClass_Base::WriteHtmlContent();
                break;
            default:
                parent::WriteHtmlContent();
        }

        include $_SERVER['STS_ROOT'] . "/actions/Hotline/Fehlerliste/Fehlerliste.table.php";

    }


    function GetHtmlElement_VinAsLink($RowData, $content, $column, $id, $attr)
    {

        if ($this->leitwartePtr->newQuery('errors')
            ->where('vehicle_id', '=', $id)
            ->get('*')) {
            return <<<Html
            <a href="{$_SERVER['PHP_SELF']}?action=Fehlerliste&{$column}={$id}&command=errorList">{$content}</a>
Html;
        } else {
            return <<<Html
            {$content}
Html;
        }

    }


    function getHead_Html()
    {

        $result = "";
        foreach ($this->errorlist[0] as $k => $v) {
            $result .= '<th>' . $this->header[$k] . '</th>';
        }
        return $result;

    }


    function getBody_Html()
    {

        $result = "";
        foreach ($this->errorlist as $row) {
            $result .= '<tr>';
            foreach ($row as $k => $v) {
                $result .= '<td>' . $v . '</td>';
            }
            $result .= '</tr>';
        }
        return $result;

    }

}