<?php

require_once $_SERVER['STS_ROOT'] . '/includes/sts-datetime.php';


class ACtion_Auslieferung extends AClass_TableBase {
    const NOTIFICATION_EMAIL = [
        // 'sabrina.henkies@autokontor-bayern.de' => 'Sabrina Henkies',
        // 'd.jung2@dpdhl.com' => 'Jung,D.,FPM 16,KO'
        // 'Michael.Eisentraut@deutschepost.de' => 'Eisentraut, M., Z10E1 eMobility, BN'
        'ismail.sbika@streetscooter.eu' => 'Ismail Sbika'
    ];

    const NOTIFICATION_CC = [
        // 'Team.Auslieferung@streetscooter.eu' => 'Team Auslieferung'
        'ismail.sbika@streetscooter.eu' => 'Ismail Sbika'
    ];

    const LOCAL_JS = <<<HEREDOC
    $(document).ready(function() {

        $(".abholddatum").datepicker({
            minDate: 1,
            dateFormat: "dd.mm.yy",
            onSelect: function(dateText, inst) {
            }
        });

        $(".lieferdatum").datepicker({
            minDate: 1,
            dateFormat: "dd.mm.yy",
            onSelect: function(dateText, inst) {
                //alert (dateText);
            }
        });



        $('#vehicles_list_table' ).tableDnD({
            onDrop: function(table, row) {
                var rowid=row.id;
                var next = $('#'+rowid).nextAll();
                if (next.length > 0)
                    return false;

                var new_group = $('#'+rowid).prev('.fzGroup').clone();
                var n = new_group.data('next');
                new_group.attr('id', 'id_group'+n);
                new_group.attr('data-next', n+1);
                new_group.data('next', n+1);
                new_group.insertAfter($('#'+rowid));
            }
        	,dragHandle: ".dragHandle"
        });

        $('.trGroupLink').click(function(){
            var fzListe = '';
            var mode    = $(this).data('mode');
            var all     = $(this).data('all');

            $(this).parents('tr').prevAll().each(function(){
                var vid = $(this).data('vid');
                if (!vid) {
                    if (all) vid=0;
                    else return false;
                }

                fzListe += vid + ',';
            });

            var url = '/debug.php?action=abholschein&mode='+mode+'&all='+all+'&vlist='+fzListe;

            window.open (url, '_blank');
        });

    });
HEREDOC;

    const LOCAL_STYLE = <<<HEREDOC
    .buttontable td                     {width: 400px;}
    .buttontable td span                {width: 200px; padding: 0 8px;}
    .buttontable td span:nth-child(1)   {font-size: 12px; font-weight: bold;}

    /*
    th.ui-datepicker-week-end,
    td.ui-datepicker-week-end {
        display: none;
    */
}
HEREDOC;

    const POST_DEUTSCHLAND = 1;
    const POST_AUSLAND = 2;

    protected $ziel_division = 0;
    protected $loadJQuery = true;
    protected $numRows = 0;
    protected $numGroups = 0;
    protected $vehicleGroups = [];

    function __construct($pageController) {
        parent::__construct($pageController);

        $this->btnLabels += array(
            'deliver1' => "ausgewählte Fz. ausliefern &gt;&gt;",
            'deliver2' => "Fahrzeuge ausliefern",
        );

        $this->btnEnabled += array(
            'deliver1' => 'anySelected',
            'deliver2' => '1'
        );

        $this->S_dp_order = &InitSessionVar($this->S_data['dp_order'], 0);
        if (isset ($_REQUEST['dp_order']))
            $this->S_dp_order = $_REQUEST['dp_order'];

        $this->b_show_dritt = isset ($_REQUEST['show_dritt']);
    }

    function GetHtml_LinkAbholschein($all) {
        $link = <<<HEREDOC
<a class="trGroupLink" data-mode="view" data-all="$all">[drucken]</a> <a class="trGroupLink" data-mode="download" data-all="$all">[download]</a>
HEREDOC;

    }

    // ==============================================================================================
    /* derived */
    function DefineColConfig() {
        parent::DefineColConfig();

        $this->colConfig ['fz_begleitschein']['enable'] = COL_NOT_USED;
        $this->colConfig ['options_text']['enable'] = COL_INVISIBLE;
        $this->colConfig ['color_id']['enable'] = COL_INVISIBLE;
        $this->colConfig ['windchill']['enable'] = COL_INVISIBLE;
        $this->colConfig ['depot']['.lookup'] = ['sts' => '-- Streetscooter --'] + $this->prodLocationWithStsPool;
    }

    // ==============================================================================================
    function InitConstants() {
        parent::InitConstants();

        switch ($this->S_dp_order) {
            case self::POST_DEUTSCHLAND:
                $division_name = 'post ausliefer%';
                break;
            case self::POST_AUSLAND:
                $division_name = 'post ausland%';
                break;
            default:
                $division_name = 'drittkunden';
                break;
        }

        $this->ziel_division = $this->vehiclesPtr
            ->newQuery('divisions')
            ->where('name', 'ilike', $division_name)
            ->getVal('division_id');

        $this->delivery_depots = $this->vehiclesPtr
            ->newQuery('depots')
            ->where('division_id', '=', $this->ziel_division)
            ->orderBy('name', 'asc')
            ->get('depot_id=>name');
    }

    // ==============================================================================================
    function On_State_Save_Print() {
        parent::On_State_Save_Print();

        $this->colConfig ['vin']['attr td'] = ['class' => "dragHandle"];
        $this->colConfig ['move']['enable'] = COL_VISIBLE;
    }

    // ==============================================================================================
    function OnWhere(&$where) {
        parent::OnWhere($where);

        if ($this->InputState != INPUT_STATE_SAVE_PRINT)
            $where['finished_status'] = 't';

        if (isset ($where['vehicles.vehicle_id']))
            return;

        if ($this->S_dp_order)
            $where['is_dp'] = $this->b_show_dritt ? 'f' : 't';
        else
            $where['is_dp'] = 'f';
    }

    // ==============================================================================================
    function DP_SendNotificationEmail($lieferdatum_ui) {
        // $content_text = $this->GenerateEMailText ($this->S_selectedVehicles, $lieferdatum_ui);
        $content_html = $this->GenerateEMailHtml($this->S_selectedVehicles, $lieferdatum_ui);

        $mailer = new MailerSmimeSwift (self::NOTIFICATION_EMAIL, '', 'StreetScooter Ankündigung ', $content_html, null, true, self::NOTIFICATION_CC);

    }

    // ==============================================================================================
    function GenerateEMailText(&$vehicleList, $lieferdatum_ui) {
        $content = <<<HEREDOC
Sehr geehrte Damen und Herren,

wie bereits angekündigt erhalten Sie in Kürze folgende Fahrzeuge:

FIN                 |Artikel                            |Fahrzeugtyp                             |Batterie       |Datum (vsl.)
--------------------+-----------------------------------+----------------------------------------+---------------+---------------

HEREDOC;


        foreach ($vehicleList as &$vehicle) {
            $penta_id = $vehicle['penta_id'];
            $penta_num = $this->colConfig ['penta_id']['.lookup'][$penta_id];
            $external = $this->vehicleVariantsPtr->GetExternalName($vehicle['variant_id']);
            $parts = $this->vehicleVariantsPtr->GetPartlistFromWindchillName($vehicle['windchill'], true);
            $content .= sprintf("%20s|%35s|%40s|%15s|%15s\n",
                $vehicle['vin'],
                $penta_num,
                $external['external_name'],
                $parts['battery'],
                $lieferdatum_ui);

        }

        $content .= <<<HEREDOC

Die Fahrzeuge werden voraussichtlich am Morgen des oben genannten Datums ausgeliefert.

Mit freundlichen Grüßen,
Ihr StreetScooter Cloud-System Team

HEREDOC;

        return $content;

    }

    // ==============================================================================================
    function GenerateEMailHtml(&$vehicleList, $lieferdatum_ui) {
        $content = <<<HEREDOC
<html>
<meta charset="UTF-8">
<head>
<style>
    body {font-family: arial, helvetica, sans-serif}
    table {margin: 10px 20px; min-width: 400px; width: 80%;}
    table thead tr {background-color: #eeeeee;}
    table, table td, table th  {border: 1px solid #444; border-collapse: collapse;}
</style>
</head>
<body>
<p>
    Sehr geehrte Damen und Herren,<br>
    wie bereits angekündigt erhalten Sie in Kürze folgende Fahrzeuge:
</p>

<table>
<thead>
<tr>
    <th>FIN</th><th>Artikel</th><th>Fahrzeugtyp</th><th>Batterie</th><th>Datum (vsl.)</th>
</tr>
</thead>
<tbody>
HEREDOC;


        foreach ($vehicleList as &$vehicle) {
            $penta_id = $vehicle['penta_id'];
            $penta_num = $this->colConfig ['penta_id']['.lookup'][$penta_id];
            $external = $this->vehicleVariantsPtr->GetExternalName($vehicle['variant_id']);
            $parts = $this->vehicleVariantsPtr->GetPartlistFromWindchillName($vehicle['windchill'], true);
            $content .= sprintf("<tr>\n    <td>%20s</td><td>%35s</td><td>%40s</td><td>%15s</td><td>%15s</td>\n</tr>\n",
                $vehicle['vin'],
                $penta_num,
                $external['external_name'],
                $parts['battery'],
                $lieferdatum_ui);

        }

        $content .= <<<HEREDOC
</tbody>
</table>
<p>Die Fahrzeuge werden voraussichtlich am Morgen des oben genannten Datums ausgeliefert.</p>
<p>
Mit freundlichen Grüßen,<br>
Ihr StreetScooter Cloud-System Team
</p>
</body>
</html>
HEREDOC;

        return $content;

    }

    // ==============================================================================================
    function Deliver() {
        $num_vehicles = count($this->S_selectedVehicles);
        if ($num_vehicles) {
            $abholddatum_ui = $_REQUEST['abholdatum'];
            $abholddatum_db = to_iso8601_date($abholddatum_ui);
            if (empty ($abholddatum_db)) {
                $abholddatum_ui = date('d.m.Y', time() + ONE_DAY);
                $abholddatum_db = date('Y-m-d', time() + ONE_DAY);
            }

            $lieferdatum_ui = $_REQUEST['lieferdatum'];
            $lieferdatum_db = to_iso8601_date($lieferdatum_ui);
            if (empty ($lieferdatum_db)) {
                $lieferdatum_ui = date('d.m.Y', time() + (8 * ONE_DAY));
                $lieferdatum_db = date('Y-m-d', time() + (8 * ONE_DAY));
            }

            $deliver_to = safe_val($_REQUEST, 'deliver_to', 0);
            $this->S_changedIds = array_keys($this->S_selectedVehicles);
            $vehicles_csv = implode(',', $this->S_changedIds);
            $add_dispatch = ($this->S_dp_order == self::POST_DEUTSCHLAND) ? (", depot_dispatch_date='$lieferdatum_db'") : ("");

            $sql = "update vehicles set depot_id=$deliver_to, station_id=null, finished_status='f'$add_dispatch where vehicle_id in ($vehicles_csv)";
            $qry = $this->vehiclesPtr->newQuery();
            if (!$qry->query($sql)) {
                $this->SetError(STS_ERROR_DB_UPDATE, $qry->GetLastError());
                return false;
            }

            $sql = "update vehicles_sales set delivery_date='$lieferdatum_db', shipping_date='$abholddatum_db', delivery_status='t' where vehicle_id in ($vehicles_csv)";
            $qry = $this->vehiclesPtr->newQuery();
            if (!$qry->query($sql)) {
                $this->SetError(STS_ERROR_DB_UPDATE, $qry->GetLastError());
                return false;
            }

            $qry = $this->vehiclesPtr->newQuery('districts');
            $qry = $qry->where('vehicle_mon', 'in', $this->S_changedIds);
            $res = $qry->update(['depot_id'], [$deliver_to]);

            $this->link_penta_csv = $this->vehiclesPtr->pentaCSVExport(array_keys($this->S_selectedVehicles));

            if ($this->S_dp_order == self::POST_DEUTSCHLAND)
                $this->DP_SendNotificationEmail($lieferdatum_ui);

            $this->SetMessage(STS_MESSAGE_SUCCEED, "$num_vehicles Fahrzeuge ausgeliefert nach:<br><b>{$this->delivery_depots[$deliver_to]}</b>.<br><br><span class=\"LabelX W120\">Abholung am: </span><b>$abholddatum_ui</b><br><span class=\"LabelX W120\" >Anlieferung am:</span><b>$lieferdatum_ui</b>");
            return true;
        }
    }

    // ==============================================================================================
    function ExecuteCommand($command) {
        switch ($command) {
            case 'deliver1':
                $this->InputState = INPUT_STATE_VALIDATE;
                return true;

            case 'deliver2':
                if ($this->InputState >= INPUT_STATE_VALIDATE) {
                    if ($this->Deliver()) {
                        $this->InputState = INPUT_STATE_SAVE_PRINT;
                    }
                }
                return true;
        }
        parent::ExecuteCommand($command);
    }

    // ==============================================================================================
    function SetupHeaderFiles($displayheader) {
        parent::SetupHeaderFiles($displayheader);
        $displayheader->enqueueJs("jquery-datepicker", "js/jquery.ui.datepicker-de.js");
        $displayheader->enqueueJs("jquery-tablednd", "js/jquery.tablednd.js");
        $displayheader->enqueueLocalJS(self::LOCAL_JS);

        $displayheader->enqueueLocalStyle(self:: LOCAL_STYLE);
    }

    // ==============================================================================================
    function GetHtml_VehiclesTableRow(array $displayCols, array $vehicleData) {
        $row = parent::GetHtml_VehiclesTableRow($displayCols, $vehicleData);
        if ($this->InputState == INPUT_STATE_SAVE_PRINT) {
            $nCols = count($displayCols);

            $this->numRows++;
            //         $this->vehicleGroups[] = $vehicleData['variant_id'];

            if ((($this->rowCount % 4) == 0) || ($this->rowCount == $this->tableInfo['numRows'])) {
                $this->numGroups++;
                $id = 'id_group' . $this->numGroups;
                $next = $this->numGroups + 1;
                $link = $this->GetHtml_LinkAbholschein(0);

                $row .= "\n<tr id=\"$id\" class=\"fzGroup\" data-next=\"$next\"><td colspan=\"$nCols\"><b>Abholschein</b> $link</td></tr>\n";
            }
        }
        return $row;
    }

    // ==============================================================================================
    function WriteHtmlContent($options = "") {
        parent::WriteHtmlContent($options);

        include $_SERVER['STS_ROOT'] . "/actions/Sales/Auslieferung/Auslieferung.table.php";
    }
    // ==============================================================================================


}

?>
