<?php
define('INPUT_STATE_FLASH', 7);
define('INPUT_STATE_EVALUATION', 8);

class ACtion_FlashInterface extends AClass_TableBase
{


    function __construct()
    {

        parent::__construct();
        if ($this->controller) {
            $this->leitwartePtr = $this->controller->GetObject('ladeleitwarte');
            $this->diagnosePtr = $this->controller->GetObject('diagnose');
        }

        $this->btnLabels += [
            'back' => 'Zurück zur Auswahl'
        ];

        $this->btnEnabled += [
            'back' => true
        ];

    }


    function Init()
    {

        parent::Init();

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
        $joker = [
            'sts' => '-- Streetscooter --',
            'all' => '-- alle --',
            'edit' => '-- anderer Ort --'
        ];
        $this->colConfig['depot'] = [
            'enable' => COL_VISIBLE,
            'header' => 'ZSP',
            'db' => [
                'table' => 'vehicles',
                'column' => 'depot_id'
            ],
            'search' => 'SelectWithEditOption',
            'search_init' => 'all',
            '.lookup' => $joker + $this->prodLocationWithStsPool
            // '.call' =>
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

    }


    function ExecuteCommand($command)
    {

        switch ($command) {
            case 'flash':
                $this->flashbcm();
                $this->SetState(INPUT_STATE_EVALUATION);
                break;
            case 'flashvorgang':
                $this->SetState(INPUT_STATE_FLASH);
                if (! empty($_GET['vin'])) {
                    $_SESSION['vin'] = $_GET['vin'];
                }
                break;
            case 'back':
                $this->SetState(INPUT_STATE_SELECT);
                break;
            default:
                parent::ExecuteCommand($command);
        }

    }


    function InitState()
    {

        parent::InitState();
        $this->colConfig['selected']['enable'] = COL_INVISIBLE;

    }


    function On_State_Select()
    {

        parent::On_State_Select();
        if (empty($this->GetWhereFromRequest()['vin']) && empty($this->GetWhereFromRequest()['akz']) && empty($this->GetWhereFromRequest()['ikz'])) {
            unset($this->S_currentVehicles);
        }

    }


    function WriteHtmlContent()
    {

        switch ($this->InputState) {
            case INPUT_STATE_FLASH:
                AClass_Base::WriteHtmlContent();
                if (ord($_SESSION['vin'][6]) > 69) {
                    echo 'Wollen Sie das Fahrzeug ' . $_SESSION['vin'] . ' flashen?<a href="' . $_SERVER['PHP_SELF'] . '?action=flashinterface&command=flash" style="border: 1px solid black; border-radius: 4px; margin: 3px; padding:4px; background: linear-gradient(to bottom,#ffe680 0,#FFCC00 90%,#ffe680 100%); color:black;">Bestätigen</a>';
                } else {
                    echo 'Es können nur Fahrzeuge der Serie F oder neuer geflashed werden';
                }
                echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?action=flashinterface&initPage&command=back">Zurück zur Auswahl</a>';
                break;
            case INPUT_STATE_EVALUATION:
                AClass_Base::WriteHtmlContent();
                echo $this->msg;
                echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?action=flashinterface&initPage&command=back">Zurück zur Auswahl</a>';
                break;
            default:
                parent::WriteHtmlContent();
        }

        include $_SERVER['STS_ROOT'] . "/actions/Aftersales/Flashinterface/Flashinterface.table.php";

    }


    function GetHtmlElement_VinAsLink($RowData, $content, $column, $id, $attr)
    {

        return <<<Html
            <a href="{$_SERVER['PHP_SELF']}?action=flashinterface&vin={$content}&command=flashvorgang">{$content}</a>
Html;

    }


    function flashbcm()
    {

        $this->diagnosePtr->newQuery('sota_tasks')->insert([
            'vin' => $_SESSION['vin'],
            'task_type_id' => 1,
            'workshop_id' => $_SESSION['role']['workshop_id'],
            'timestamp_created' => date('Y-m-d G:i:s O')
        ]);
        $this->msg = "Flashvorgang wurde in Auftrag gegeben";

    }

}