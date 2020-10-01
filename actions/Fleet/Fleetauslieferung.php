<?php
$col = 0;
define('TD_DIVISION', $col++);
define('TD_DEPOT', $col++);
define('TD_OZ', $col++); // define('TD_STATION',        $col++ );
define('TD_FZ_TYPE', $col++);
define('TD_BATTERY', $col++);
define('TD_NUMSEATS', $col++);
define('TD_DT_ORDER', $col++);
define('TD_DT_DELIVERY', $col++);
define('TD_FZ_VIN', $col++);
define('TD_FZ_AKZ', $col++);
define('TD_FZ_IKZ', $col++);
define('TD_FZ_VARIANT', $col++);
define('TD_FROM_POOL', $col++);
define('TD_DT_SHIPPING', $col++);

require_once "{$_SERVER['STS_ROOT']}/includes/sts-datetime.php";

class ACtion_Fleetauslieferung extends AClass_OrderList {
    const TABLE_HEADER = [
        TD_DIVISION => "<b>Niederlassung</b>",
        TD_DEPOT => "<b>ZSP</b>",
        TD_OZ => "<b>OZ</b>",   //         TD_STATION      => "<b>Lade-<br>station</b>",
        TD_FZ_TYPE => "<b>Fahrzeug-<br>typ</b>",
        TD_BATTERY => "<b>Battery</b>",
        TD_NUMSEATS => "<b>Anzahl<br>Sitze</b>",
        TD_DT_ORDER => "<b>Bestelldatum</b>",
        TD_DT_DELIVERY => "<b>Auslieferdatum</b><br>(geplant)",
        TD_FZ_VIN => "<b>Fahrzeug (VIN)</b>",
        TD_FZ_AKZ => "<b>Kennzeichen</b>",
        TD_FZ_IKZ => "<b>IKZ</b>",
        TD_FZ_VARIANT => "<b>Modell</b>",
        TD_FROM_POOL => "<b>Fahrzeug-<br>pool</b>",
        TD_DT_SHIPPING => "<b>Versand-<br>datum</b>",
    ];


    const LOCAL_STYLE = <<<HEREDOC
        h2, h3                  {display: inline-block; margin-right: 8px; }
        table.stcytbl tbody td  {padding-top: 1px; padding-bottom: 1px; height: 38px; ; }

        .sameDivision           {color:   #66c; }
        .sameDepot              {color:   #00f; }
        .done                   {color:   #888; }
        #page-header            {margin-bottom: 10px;}
        .select_vehicle.vin     {width: 190px; }
        .select_vehicle.akz     {width: 120px; }
        .select_vehicle.ikz     {width: 100px; }
        .cell_variant           {width: 150px; }

        div.unten               {margin-top: 20px;}
        .seitenteiler.teil1     {width: 300px; /* background-color: #fcc; */}
        .seitenteiler.teil2     {width: 200px; /* background-color: #cfc; */}
        .seitenteiler.teil3     {width: 200px; /* background-color: #ccf; */}
        .teil3 button           {width: 400px; height: 35px;}
        .csvTool_SendDiv        {display: inline-block; }
        .csvTool_SelectSheet    {width: 250px;}

HEREDOC;


    protected $table = null;
    protected $dbOrderData = null;
    protected $csvReader = null;
    protected $pool_ids;

    protected $S_nur_angemeldete;
    protected $S_assigned_vehicles;
    protected $S_current_pool;
    protected $S_proceed_positions;


    //#########################################################################

    function __construct(PageController $pagecontroller) {
        parent::__construct($pagecontroller);

        $this->csvReader = new AClass_CsvTool_Sales ('csvImport', true);
        $this->csvReader->SetOutputBuffering(true);

        $this->table = new AClass_StickyHeaderTable();
        $this->table->setHeight(385, 8);

        $this->table->setHeader(self::TABLE_HEADER);

        $this->S_assigned_vehicles = &InitSessionVar($this->S_data['assignedVehicles'], []);
        $this->S_nur_angemeldete = &InitSessionVar($this->S_data['nur_angemeldete'], 1);
        $this->S_current_pool = &InitSessionVar($this->S_data['current_pool'], 0);
        $this->S_proceed_positions = &InitSessionVar($this->S_data['proceed_positions'], []);
    }

    //#########################################################################
    function Init() {
        parent::Init();

        $pool_ids = array_keys($this->auslieferungsPools);
        if (!$this->S_current_pool)
            $this->S_current_pool = $pool_ids[0];
    }

    //#########################################################################
    function fetchActualOrders($qry) {
        if (count($this->S_proceed_positions))
            $qry = $qry->where('position_id', 'in', array_keys($this->S_proceed_positions), 'OR');
        return parent::fetchActualOrders($qry);
    }

    //#########################################################################
    function Execute() {
        parent::Execute();

        if ($this->csvReader->Active()) {
            $this->csvReader->Execute();
            if ($this->csvReader->Finished()) {

            }
        }


        if ($_REQUEST['auslieferpatz'])
            $this->S_current_pool = $_REQUEST['auslieferpatz'];

        if (isset ($_REQUEST['nur_angemeldete']))
            $this->S_nur_angemeldete = $_REQUEST['nur_angemeldete'];

        $this->dbOrderData = $this->QueryActualOrders();

        if (isset ($_REQUEST['anySubmit']))
            $this->HandleFormData($this->dbOrderData);

        if (isset ($_REQUEST['command']) && ($_REQUEST['command'] == 'zuweisen'))
            $this->FahrzeugZuweisungAusfuehren($this->dbOrderData);

        $this->BuildTable($this->dbOrderData);
    }

    //#########################################################################
    function QueryVehicle($vid) {
        $qry = $this->vehiclesPtr->newQuery();
        $qry = $qry->where('vehicle_id', '=', $vid);
        return $qry->getVals('vehicle_id, vin, code, ikz');
    }

    //#########################################################################
    function ScanVin($input) {
        if (preg_match("/[(]?({$GLOBALS['CvsDataRegex']['vin']})[)]?/i", strtoupper($input), $match))
            return $match[1];
        return "";
    }

    //#########################################################################
    function ScanAkz($input) {
        if (preg_match("/{$GLOBALS['CvsDataRegex']['akz']}/i", strtoupper($input), $match))
            return "BN-{$match[1]} {$match[2]} {$match[3]}";
        return "";
    }

    //#########################################################################
    function ScanIkz($input) {
        if (preg_match("/{$GLOBALS['CvsDataRegex']['ikz']}/", $input))
            return $input;
        return "";
    }

    //#########################################################################
    function HandleFormData(&$dbData) {
        //$update_pos_cols = ['delivery_date', 'delivered_vehicle_id'];
        $from_id = $this->S_current_pool;
        $from_place = $this->auslieferungsPools[$from_id]['place'];

        foreach ($dbData as $pos_id => $position) {
            if (isset($_REQUEST['vin'][$pos_id]['index'])) {
                $this->S_assigned_vehicles[$pos_id] = [];

                $vehicle_id = $_REQUEST['vin'][$pos_id]['index'];
                if ($vehicle_id) {
                    $vSet = &$this->S_assigned_vehicles[$pos_id];

                    $vSet['vehicle_id'] = $vehicle_id;
                    $vSet['from_id'] = $from_id;
                    $vSet['from_place'] = $from_place;
                    $vSet['set_code'] = empty($_REQUEST['akz'][$pos_id]['index']);
                    $vSet['set_ikz'] = empty($_REQUEST['ikz'][$pos_id]['index']);
                    $vSet['vin'] = $this->ScanVin($_REQUEST['vin'][$pos_id]['text']);
                    $vSet['code'] = $this->ScanAkz($_REQUEST['akz'][$pos_id]['text']);
                    $vSet['ikz'] = $this->ScanIkz($_REQUEST['ikz'][$pos_id]['text']);
                    $vSet['vehicle_variant'] = $_REQUEST['variant'][$pos_id];
                    $vSet['dtShipping'] = to_iso8601_date($_REQUEST['shipping'][$pos_id]);
                } else {
                    unset ($this->S_assigned_vehicles[$pos_id]);
                }
            }
        }
    }

    //#########################################################################
    function FahrzeugZuweisungAusfuehren(&$dbData) {
        foreach ($dbData as $pos_id => $position) {
            if (isset ($this->S_assigned_vehicles[$pos_id])) {
                $vSet = &$this->S_assigned_vehicles[$pos_id];
                if (!empty ($vSet['code']) && !empty ($vSet['ikz'])) {
                    $code = $vSet['set_code'] ? $vSet['code'] : null;
                    $ikz = $vSet['set_ikz'] ? $vSet['ikz'] : null;
                    $dtShippingEnds = new DateTime($vSet['dtShipping']);
                    $dtShippingEnds->modify('+1 Week');

                    $error = $this->vehiclesPtr->DepotWechsel($vSet['vehicle_id'], $position['depot_id'], $position['station_id'], $dtShippingEnds, $vSet['code'], $code, $ikz);
                    if ($error)
                        return $this->SetError(STS_ERROR_DB_UPDATE, $error);

                    $updateCols = ['delivery_date', 'delivery_from', 'delivered_vehicle_id'];
                    $updateVals = [$vSet['dtShipping'], $vSet['from_id'], $vSet['vehicle_id']];
                    $qry = $this->leitWartePtr->newQuery('post_vehicle_order_positions');
                    $result = $qry->where('position_id', '=', $pos_id)->update($updateCols, $updateVals);

                    if (result) {

                        $dbData[$pos_id]['delivery_date'] = $vSet['dtShipping'];
                        $dbData[$pos_id]['delivery_from'] = $vSet['from_id'];
                        $dbData[$pos_id]['delivered_vehicle_id'] = $vSet['vehicle_id'];

                        $this->S_proceed_positions[$pos_id] = $dbData[$pos_id];
                    }
                }
            }
        }
    }

    //#########################################################################
    function getUsedVehicles(&$assignment) {
        return array_column($assignment, 'vehicle_id');
    }

    //#########################################################################
    function Ajaxecute($command) {
        switch ($command) {
            case 'assign':
                $pos = $_REQUEST['pos'];
                $vid = $_REQUEST['vehicle'];
                $this->S_assigned_vehicles[$pos] = $this->QueryVehicle($vid);
                $this->S_assigned_vehicles[$pos] = $this->S_current_pool;
                echo "ok";
                break;
        }
        exit;
    }

    //#########################################################################
    function SetupHeaderFiles($displayheader) {
        parent::SetupHeaderFiles($displayheader);

        $today = date("d.m.Y");
        $displayheader->enqueueLocalJs("var today = '$today';");
        $displayheader->enqueueJs("jquery-datepicker", "js/jquery.ui.datepicker-de.js");
        $displayheader->enqueueJs("jquery-comboedit", "js/sts-custom-comboedit.js");
        $displayheader->enqueueJs("sts-auslieferung", "js/sts-custom-auslieferung.js");

        $displayheader->enqueueStylesheet("css-jquery-ui-struct", "js/newjs/jquery-ui.structure.css");
        $displayheader->enqueueStylesheet("css-jquery-ui", "js/newjs/jquery-ui.css");
        $displayheader->enqueueStylesheet("css-jquery-ui-theme", "js/newjs/jquery-ui.theme.css");
        $displayheader->enqueueStylesheet("css-theme.default", "css/theme.default.css");

        $this->table->SetupHeaderFiles($displayheader);
        $displayheader->enqueueLocalStyle(self::LOCAL_STYLE);
    }

    //#########################################################################
    function CheckComplete(&$vin, $akz, $ikz) {
        foreach ($vin as $vid => $val)
            if (empty ($akz[$vid]) || empty ($ikz[$vid]))
                $vin[$vid] = "($val)";
    }

    //#########################################################################
    function BuildTable(&$dbData) {
        $used_vehicle_ids = $this->getUsedVehicles($this->S_assigned_vehicles);


        foreach ($dbData as $pos_id => $order) {
            $vehicle_id = $vehicle_vin = $vehicle_akz = $vehicle_ikz = $vehicle_variant = $from_pool = $shipping_date = '';

            $vehicle_match = [
                'type' => $order['variant_type'],
                'seats' => $order['variant_num_seats'],
                'battery' => $order['battery'],
            ];

            $available_vehicles = $this->QueryVehiclesInPool($this->S_current_pool, $vehicle_match, $this->S_nur_angemeldete);


            if (isset ($this->S_assigned_vehicles[$pos_id])) {
                $assigned_vehicle = &$this->S_assigned_vehicles[$pos_id];
                $vehicle_id = $assigned_vehicle['vehicle_id'];
                $vehicle_vin = $assigned_vehicle['vin'];
                $vehicle_akz = $assigned_vehicle['code'];
                $vehicle_ikz = $assigned_vehicle['ikz'];
                $vehicle_variant = $assigned_vehicle['vehicle_variant'];
                $from_pool = $assigned_vehicle['from_place'];
                $shipping_date = $assigned_vehicle['dtShipping'];

                if (!isset($available_vehicles[$vehicle_id])) {
                    $available_vehicles[$vehicle_id] = [
                        'vehicle_id' => $vehicle_id,
                        'vin' => $vehicle_vin,
                        'code' => $vehicle_akz,
                        'ikz' => $vehicle_ikz,
                        'windchill_variant_name' => $vehicle_variant,
                    ];
                }
            }

            $rowProperies = ['data-div' => $order['division_id'], 'data-depot' => $order['depot_id'], "data-assigned" => $vehicle_id];
            if (!empty($order['delivered_vehicle_id']))
                $rowProperies['class'] = 'done';

            $this->table->newRow($pos_id, $rowProperies);
            $this->table->setCell(TD_DIVISION, $order['division']);
            $this->table->setCell(TD_DEPOT, $order['depot']);
            $this->table->setCell(TD_OZ, $order['oz']);  //             $this->table->setCell (TD_STATION    , $order['station']);
            $this->table->setCell(TD_FZ_TYPE, $order['variant_type']);
            $this->table->setCell(TD_BATTERY, $order['variant_battery']);
            $this->table->setCell(TD_NUMSEATS, $order['variant_num_seats']);
            $this->table->setCell(TD_DT_ORDER, to_locale_date($order['order_datetime']));
            $this->table->setCell(TD_DT_DELIVERY, $order['desired_date']);


            if (empty ($order['delivered_vehicle_id'])) {
                $list_vin = [0 => '-leer-'] + reduce_assoc($available_vehicles, 'vin');
                $list_akz = [0 => '-leer-'] + reduce_assoc($available_vehicles, 'code');
                $list_ikz = [0 => '-leer-'] + reduce_assoc($available_vehicles, 'ikz');
                $opts_data = [0 => 'data-variant=""'] + format_array_values($available_vehicles, 'data-variant="$windchill_variant_name"');

                if (!$this->S_nur_angemeldete) {
                    $this->CheckComplete($list_vin, $list_akz, $list_ikz);
                    $list_akz = array_remove_empty($list_akz);
                    $list_ikz = array_remove_empty($list_ikz);
                }


                $other_used_vehicle = array_diff($used_vehicle_ids, [$vehicle_id]);

                $vehicle_vins = $this->GetHtml_SelectOptions($list_vin, -1, -1, $other_used_vehicle, $opts_data);
                $vehicle_akzs = $this->GetHtml_SelectOptions($list_akz, -1, -1, $other_used_vehicle);
                $vehicle_ikzs = $this->GetHtml_SelectOptions($list_ikz, -1, -1, $other_used_vehicle);
                $this->table->setCell(TD_FZ_VIN, "<select class=\"select_vehicle vin\" name=\"vin[$pos_id]\" id=\"select-vin-$pos_id\" data-pos=\"$pos_id\" data-index=\"$vehicle_id\" data-text=\"$vehicle_vin\">$vehicle_vins</select>");
                $this->table->setCell(TD_FZ_AKZ, "<select class=\"select_vehicle akz\" name=\"akz[$pos_id]\" id=\"select-akz-$pos_id\" data-pos=\"$pos_id\" data-index=\"$vehicle_id\" data-text=\"$vehicle_akz\">$vehicle_akzs</select>");
                $this->table->setCell(TD_FZ_IKZ, "<select class=\"select_vehicle ikz\" name=\"ikz[$pos_id]\" id=\"select-ikz-$pos_id\" data-pos=\"$pos_id\" data-index=\"$vehicle_id\" data-text=\"$vehicle_ikz\">$vehicle_ikzs</select>");
                $this->table->setCell(TD_FZ_VARIANT, "<input readonly size=\"11\" type=\"text\" name=\"variant[$pos_id]\" id=\"id-variant-$pos_id\" value=\"$vehicle_variant\">");

                $disabled = empty($assigned_vehicle['dtShipping']) ? " disabled" : "";
                $this->table->setCell(TD_FROM_POOL, "<span id=\"id-from-pool-$pos_id\">$from_pool</span>");
                $this->table->setCell(TD_DT_SHIPPING, "<input type=\"text\" class=\"datum\" $disabled name=\"shipping[$pos_id]\" value=\"{$shipping_date}\" id=\"id-shipping-$pos_id\" size=\"10\">");
            } else {
                $this->table->setCell(TD_FZ_VIN, $vehicle_vin);
                $this->table->setCell(TD_FZ_AKZ, $vehicle_akz);
                $this->table->setCell(TD_FZ_IKZ, $vehicle_ikz);
                $this->table->setCell(TD_FZ_VARIANT, $vehicle_variant);
                $this->table->setCell(TD_FROM_POOL, $from_pool);
                $this->table->setCell(TD_DT_SHIPPING, $shipping_date);


            }
        }
    }

    //#########################################################################

    function WriteHtmlContent($options = "") {
        AClass_Base::WriteHtmlContent($options);
        echo $this->GetHtml_MultipartFormHeader();

        if ($this->csvReader->Active()) {
            $this->csvReader->WriteContent();
            echo "</form>";
            return;
        }

        echo '<input type="hidden" name="anySubmit" value="1">';

        echo '<div id="page-header"><h2>Aktueller Auslieferungsplatz: </h2>';

        if (count($this->auslieferungsPools) > 0)
            echo "<select name=\"auslieferpatz\" id=\"id-auslieferpatz\">" . $this->GetHtml_SelectOptions(reduce_assoc($this->auslieferungsPools, 'name'), $this->S_current_pool) . "</select></div>\n";
        else
            echo "<h3>{$this->auslieferungsPools[$this->S_current_pool]['name']}</h3></div>\n";

        $this->table->WriteHtml_Content();

        $upload = $this->csvReader->GetHtml_CsvUploadFileAndBtnSend(600);

        $checked = ($this->S_nur_angemeldete) ? ' checked' : '';
        echo <<<HEREDOC

        <div class="unten">
          <div class="seitenteiler teil1">
            <input type="hidden" id="hidden_nur_angemeldete" name="nur_angemeldete" value="{$this->S_nur_angemeldete}">
            <input type="checkbox" id="cb_nur_angemeldete" $checked> Nur angemeldete Fahrzeuge auflisten
          </div>
          <div class="seitenteiler teil2">

          </div>

          <div class="seitenteiler teil3">
            <button type="submit" name="command" value="zuweisen">Standortzuweisung ausführen</button>
          </div>
        </div>
        <div class="unten">
          <div class="teil4">
          <h2>Fahrzeugauslieferung über Excel-Datei</h2><br>

          $upload
          </div>
        </div>
        </form>

HEREDOC;
    }
}

?>