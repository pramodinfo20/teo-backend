<?php

class ACtion_Fahrzeugbestellung extends AClass_FormBase {
    protected $user = null;
    protected $stations_overview = null;
    protected $post_variants = null;
    protected $list_num_vehicles = [];
    protected $num_orders_all = 0;
    protected $orders_in_queue = [];
    protected $S_selected_depot;
    protected $S_newOrders;
    protected $S_current_row;

    const LOCAL_CSS = "
            h3          {margin-bottom: 0; }
            #id_main    {height: 96%,   width: 98%; margin-top: 20px; display: flex; flex-flow: row nowrap; justify-content: flex-start; align-items: stretch; }
            #id_links   {width: 500px; }
            #id_rechts  {width: 100%; margin-left: 10px; position: relative; padding-bottom: 20px;}

            #id_depots          {border: 2px solid #888; padding: 1px; width: 500px;}
            #id_depots_head     {width: 500px;}
            #id_depots_body     {width: 500px; max-height: 400px; overflow-y: scroll; overflow-x: hidden;}


            .depots                             {font-size: 10px; table-layout: fixed; }
            .depots td                          {overflow:hidden;}
            .depots tr td:nth-child(1)          {width: 160px; padding: 1px 4px; }
            .depots tr td:nth-child(2)          {width: 100px; padding: 1px 4px; }
            .depots tr td:nth-child(3)          {width:  92px; padding: 1px 4px; }
            .depots tr td:nth-child(4),
            .depots tr td:nth-child(5)          {width: 46px;  padding: 1px 4px; }
            .depots tr td:nth-child(6)          {width: 9px;   padding: 0; }

            #id_depots_body tr td:nth-child(3)  {text-align: center;}
            /*
            #id_depots_body tr td:nth-child(4)  {text-align: right;  }
            #id_depots_body tr td:nth-child(5)
            */
            #id_depots_head tr                  {background-color: #ccc; }
            #id_depots_head td                  {border: 1px solid #444;}

            #id_depots_body tr                  {background-color: #fff; cursor: pointer; }
            #id_depots_body tr.selected         {background-color: #ccf; }
            #id_depots_body td                  {border: 1px dotted #ccc;}

            .id_assign_ks                       {position: relative; }
            .id_assign_ks span                  {position: absolute; left: 0px; top: 0px; visibility: hidden;}
            .id_assign_ks span select           {height: 12px; width: 50px; font-size: 10px; padding: 0 4px;}

            .tree_graph                         {margin-top: -40px; }
            .num_spacer                         {width: 18px; text-align: right; display: inline-block;}
            .numInQueue                         {color: #448; font-style: italic; margin-left:4px;}
            .buttons, .notice                   {width:100%; margin-top: 10px; font-size: 11px;}
            .buttons                            {text-align: center; }
            .notice                             {text-align: left; }
            .notice  textarea                   {width: 500px; height: 60px;}
            .clBattery                          {width: 150px;}
            .no_zsp                             {position: relative; left: 100px; top: 50%; margin-top: -10px; }
            .vehicle_order                      {padding: 0;margin: 0;}

            .clOrderForm                        {border: 4px solid #fd0; padding: 4px; background-color: #fe8; border-radius: 10px;} /* {background-color: #fe8; padding: 4px; } */
            .clOrderForm  button                {font-size: 12px;}
            .clOrderForm  .seitenteiler         {text-align: left; }
            .orderType                          {width: 100px; }
            .clButton                           {margin-top: 8px; }
            .tree_graph li span .LabelX         {border: none; margin: 0px; padding: 0px;}
            .tree_graph li span.vehicle_order   {border-radius: 10px;}
            .clOrder                            {width: 130px; height: 125px; padding: 8px 20px;text-align: left; border: 4px solid #fa0; border-radius: 10px;}
            .clInQueue                          {border: 4px solid #ccf; background-color: #eef;}
            .clNew                              {border: 4px solid #f80; background-color: #fc8;}

            ";

    const LOCAL_JS = <<<HEREDOC
    function reloadForDepot(depot)
    {
        var scrollpos = document.getElementById ('id_depots_body').scrollTop;
        document.location.href = '?' + _urlparams + '&selected_depot=' + depot + '&scrl=' + scrollpos;
    }

    function onOrderChanged()
    {
        var i, numOrder = 0;
        var list = document.getElementsByClassName('orderVariant');
        for (i=0; i<list.length; i++) {
            if (list[i].selectedIndex > 0)
                numOrder++;
        }

        /* document.getElementById ('id_order').disabled = (numOrder == 0); */
    }

    function onTypeChange(selbox, station)
    {
        var sel1 = document.getElementById ('id_battery_'+station);
        var sel2 = document.getElementById ('id_seats_'+station);
        var bttn = document.getElementById ('id_order_'+station);
        var iTyp = selbox.selectedIndex;
        var type = selbox.options[iTyp].value;

        var options = Ajaxecute('getTypeInfo', 'type='+type);


        if (sel1) {
            sel1.innerHTML = options;
            sel1.disabled = (iTyp == 0);
        }

        if (sel2) {
            if (type=='E17')
                sel2.selectedIndex=1;

            sel2.disabled = ((iTyp == 0) || (type=='E17'));
        }

        if (bttn) {
            bttn.disabled = (iTyp == 0);
        }
    }

    function DoOrder (station)
    {
        var sel0 = document.getElementById ('id_type_' + station);
        var sel1 = document.getElementById ('id_battery_' + station);
        var sel2 = document.getElementById ('id_seats_' + station);

        if (sel0 && sel1 && sel2)
        {
            var type    = sel0.options[sel0.selectedIndex].value;
            var battery = sel1.options[sel1.selectedIndex].value;
            var seats   = sel2.options[sel2.selectedIndex].value;
            document.location.href = href_self + '?' + _urlparams + '&command=addOrder&station='+station + '&type='+type + '&battery='+battery + '&seats='+seats;
        }
    }

    function undoOrder (station)
    {
        document.location.href = href_self + '?' + _urlparams + '&command=undoOrder&station='+station;
    }


    $(document).ready(function()
    {
        var current_ks_select=null;

        $('#id_depots_body td').click(function(){
            var sender      = $(this);
            var trObject    = sender.closest('tr');
            var depot       = trObject.data('depot');

            sender.has('select').each(function(){depot = 0;});
            if (depot)
                reloadForDepot (depot);
        }),

        $('.id_assign_ks a').click(function(){
            if (current_ks_select)
            {
                current_ks_select.hide();
                current_ks_select=null;
            }

            var spanObject  = $(this).closest('span');
            if (spanObject)
            {
                spanObject.children('span').show();
                current_ks_select = spanObject;
            }

        })
    });


HEREDOC;

    const TEMPLATE_VEHICLE_ORDER = <<<HEREDOC
    <div class="clOrderForm">
      <div class="seitenteiler">
        Fahrzeugtyp<br>
        <select name="order_type[%station_id%]" OnChange="onTypeChange(this, %station_id%)" id="id_type_%station_id%" class="orderType">
          <option value="0">----</option>
          <option value="B14">B14</option>
          <option value="B16">B16</option>
          <option value="D16">D16</option>
          <option value="E17">E17</option>
        </select>
      </div>

      <div class="seitenteiler">
        Sitze<br>
        <select name="order_seats[%station_id%]" id="id_seats_%station_id%" disabled>
          <option>1</option>
          <option>2</option>
        </select>
      </div><br>

      <div class="seitenteiler">
        Batterie<br>
        <select name="order_battery[%station_id%]" class="clBattery" id="id_battery_%station_id%" disabled>
          <option value="">--nicht spezifiziert--</option>
        </select>
      </div><br>

      <div class="clButton">
        <button onClick="DoOrder(%station_id%)" id="id_order_%station_id%" disabled>Fahrzeug hinzufügen</button>
      </div>

    </div>
HEREDOC;

    function __construct(PageController $pagecontroller) {
        parent::__construct($pagecontroller);

        $this->user = $this->controller->GetObject('user');
        if (empty ($this->user))
            return $this->SetError(STS_ERROR_PHP_ASSERTION, "GetObject('user')");

        $this->leitWartePtr = $this->controller->GetObject('ladeLeitWarte');
        $this->vehicleVariantsPtr = $this->controller->GetObject("vehicleVariants");
        $this->vehiclesPtr = $this->controller->GetObject("vehicles");
        $this->division_id = $this->user->getAssignedDiv();
        $this->depots = [];
        $this->restrictions = $this->controller->GetObject("restrictions");

        $this->S_selected_depot = &InitSessionVar($this->S_data['selected_depot'], 0);
        $this->S_newOrders = &InitSessionVar($this->S_data['newOrders'], []);
        $this->S_current_row = &InitSessionVar($this->S_data['current_row'], 0);
    }

    //#########################################################################
    function Init() {
        parent::Init();

        $this->depots = safe_array($this->leitWartePtr
            ->newQuery('depots')
            ->where('division_id', '=', $this->division_id)
            ->where('dp_depot_id', 'is not null')
            ->orderBy('name')
            ->get('*', 'depot_id'));

        if (count($this->depots) == 0)
            return $this->SetError(STS_ERROR_PHP_ASSERTION, 'keine gültige Niederlassung');
    }

    //#########################################################################
    function GetHtml_ExistingOrder($station, $order, $inQueue, $numOrders = 0) {
        $kind = $inQueue ? 'Laufende' : 'Neue';
        $cssClass = $inQueue ? 'clInQueue' : 'clNew';
        $delivery = (isset ($order['delivery'])) ? '<br><span class="LabelX W080">Lieferdatum:</span>' . $order['delivery'] : '';
        $bttn_undo = $inQueue ? '' : "<br><br><button OnClick=\"undoOrder($station)\">Rückgängig</button>";

        return <<<HEREDOC
          <div class="clOrder {$cssClass}" id="id_order_$station" data-num="$numOrders">
            <span class="LabelX W160"><b>$kind Bestellung</b></span><br>
            <span class="LabelX W080">Typ:</span>{$order['variant_type']}<br>
            <span class="LabelX W080">Battery:</span>{$order['variant_battery']}<br>
            <span class="LabelX W080">Anzahl Sitze</span>{$order['variant_num_seats']}
            {$delivery}{$bttn_undo}
          </div>
HEREDOC;

    }

    //#########################################################################
    function QueryVehicleStatus() {
        $qry = $this->vehiclesPtr->newQuery();
        $qry = $qry->where('depot_id', 'in', array_keys($this->depots));
        $qry = $qry->groupBy('depot_id');
        $this->list_num_vehicles = $qry->get_no_parse('depot_id=>count(*) as cnt');

        $qry = $this->leitWartePtr->newQuery('post_vehicle_order');
        $qry = $qry->join('post_vehicle_order_positions', 'using(order_id)');
        $qry = $qry->where('division_id', '=', $this->division_id);
        $qry = $qry->where('post_vehicle_order_positions.delivered_vehicle_id', 'is null');
        $qry = $qry->groupBy('depot_id');

        $this->list_num_orders = $qry->get_no_parse('depot_id=>count(*) as cnt');

    }

    //#########################################################################
    function SendOrder() {
        if (count($this->S_newOrders) == 0)
            return;
        $num_ordered = 0;

        $insert = [
            'order_by' => $_SESSION['sts_userid'],
            'division_id' => $this->division_id,
        ];

        if ($_REQUEST['notice'])
            $insert['request_notice'] = $_REQUEST['notice'];

        $qry = $this->leitWartePtr->newQuery('post_vehicle_order');
        if (!$qry->insert($insert))
            $this->SetError(STS_ERROR_DB_INSERT, $qry->GetLastError());

        $qry = $this->leitWartePtr->newQuery('post_vehicle_order');
        $qry = $qry->where('division_id', '=', $this->division_id);
        $qry = $qry->orderBy('order_datetime', 'desc')->limit(1);
        $order_id = $qry->getVal('order_id');

        foreach ($this->S_newOrders as $depot_id => $orders) {
            foreach ($orders as $station_id => $order) {
                $order['order_id'] = $order_id;
                $order['depot_id'] = $depot_id;
                $order['station_id'] = $station_id;

                $qry = $this->leitWartePtr->newQuery('post_vehicle_order_positions');
                if (!$qry->insert($order))
                    $this->SetError(STS_ERROR_DB_INSERT, $qry->GetLastError());
                else
                    $num_ordered++;
            }
        }
        $this->SetMessage(STS_MESSAGE_SUCCEED, "$num_ordered Fahrzeuge erfolgreich angefordert/bestellt");
        $this->S_newOrders = [];
    }

    //#########################################################################
    function Execute() {
        parent::Execute();

        $currentOrders = [];

        if (isset ($_REQUEST['selected_depot'])) {
            $this->S_selected_depot = $_REQUEST['selected_depot'];
        }
        $depot = $this->S_selected_depot;

        $command = safe_val($_REQUEST, 'command', '');
        switch ($command) {
            case 'addOrder':
                $station = $_REQUEST['station'];
                $this->S_newOrders[$depot][$station] = [
                    'variant_type' => $_REQUEST['type'],
                    'variant_num_seats' => $_REQUEST['seats'],
                    'variant_battery' => $_REQUEST['battery']
                ];

                break;

            case 'undoOrder':
                $station = $_REQUEST['station'];
                unset ($this->S_newOrders[$this->S_selected_depot][$station]);
                break;

            case 'send':
                $this->SendOrder();
        }


        $this->QueryVehicleStatus();

        if ($depot) {
            $qry = $this->leitWartePtr->newQuery('post_vehicle_order_positions');
            $qry = $qry->where('depot_id', '=', $this->S_selected_depot);
            $qry = $qry->where('delivered_vehicle_id', 'is null');
            $qry = $qry->orderBy('station_id');
            $qry = $qry->orderBy('position_id', 'desc');
            $this->orders_in_queue = safe_array($qry->get_no_parse("distinct on (station_id) *", 'station_id'));
        }

        foreach ($this->orders_in_queue as $station_id => $order)
            $currentOrders[$station_id] = $this->GetHtml_ExistingOrder($station_id, $order, true);

        foreach ($this->S_newOrders[$depot] as $station_id => $order)
            $currentOrders[$station_id] = $this->GetHtml_ExistingOrder($station_id, $order, false);


        $this->restrictions->SetOrderTemplate(self::TEMPLATE_VEHICLE_ORDER);
        $this->restrictions->SetCurrentOrders($currentOrders);

    }

    //#########################################################################
    function Ajaxecute($command) {
        switch ($command) {
            case 'getTypeInfo':
                $type = $_REQUEST['type'];
                $qry = $this->leitWartePtr->newQuery('vehicle_variants');
                $qry = $qry->join('parts', 'battery=parts.name');
                $qry = $qry->where('type', '=', $type);
                $result = $qry->get_no_parse('distinct on (battery) battery, range, begleitscheinname');
                foreach ($result as $row)
                    echo "<option value=\"{$row['battery']}\">{$row['begleitscheinname']}</option>";
                exit;
        }
    }

    //#########################################################################
    function SetupHeaderFiles($displayheader) {

        // parent::SetupHeaderFiles ($displayheader);

        $displayheader->enqueueLocalStyle(self::LOCAL_CSS);
        $displayheader->enqueueLocalJS("var href_self = '{$_SERVER['PHP_SELF']}';");
        $displayheader->enqueueLocalJS(self::LOCAL_JS);

        $scrollpos = safe_val($_REQUEST, 'scrl', 0);
        $this->displayfooter->enqueueFinallyCalls("
                document.getElementById ('id_depots_body').scrollTop = $scrollpos;
                document.getElementById ('id_order').disabled = true;
        ");
    }

    //#########################################################################
    function WriteHtml_DepotInfo() {
        if ($this->S_selected_depot == 0) {
            echo '<h2 class="no_zsp">Kein ZSP ausgewält</h2>';
            return;
        }

        echo $this->restrictions->generateTreeStructureForDepot($this->S_selected_depot, false);

    }

    //#########################################################################
    function WriteHtml_AuswahlDepot() {


        ?>
        <h3>Auswahl ZSP</h3>
        <div id="id_depots">
            <div id="id_depots_head">
                <table class="transparent depots">
                    <tr>
                        <td>ZSP</td>
                        <td>OZ</td>
                        <td>Kostenstelle</td>
                        <!--
                        <td><span class="ttip"># Fz.<span class="ttiptext">Anzahl vorhandener Fahrzeuge</span></span></td>
                        <td><span class="ttip"># Best.<span class="ttiptext">Anzahl bestellter Fahrzeuge</span></span></td>
                         -->
                        <td># Fz.</td>
                        <td># Best.</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
            <div id="id_depots_body">
                <table class="transparent depots">
                    <?php

                    foreach ($this->depots as $depot_id => $depot) {
                        $new_orders = safe_val($this->S_newOrders, $depot_id, []);
                        $num_vehicles = safe_val($this->list_num_vehicles, $depot_id, '');
                        $num_orders = safe_val($this->list_num_orders, $depot_id, '');
                        $num_new = count($new_orders);
                        $td_num = "";

                        switch ($depot['subcompany_id']) {
                            case '1':
                                $kostenstelle = 'Verbund';
                                break;
                            case '2':
                                $kostenstelle = 'DHL';
                                break;
                            case '3':
                                $kostenstelle = 'Delivery';
                                break;
                            default:
                                $kostenstelle = <<<HEREDOC
<span class="id_assign_ks">
  <a>n.d.</a>
  <span>
    <select name="set_ks[$depot_id]" >
      <option value="2">DHL</option>
      <option value="3">Delivery</option>
    </select>
    <button data-depot="$depot_id">zuordnen</button>
  </span>
</span>
HEREDOC;
                                break;
                        }

                        if ($num_new)
                            $this->num_orders_all += $num_new;
                        else
                            $num_new = '&nbsp;';

                        $td_num = "<span class=\"num_spacer\"><b>$num_new</b></span>";

                        if ($num_orders)
                            $td_num .= "<span class=\"num_spacer numInQueue\">(+$num_orders)</span>";

                        $xClass = ($depot_id == $this->S_selected_depot) ? 'class="selected"' : '';
                        $xCell = ($depot_id == $this->S_selected_depot) ? 'id="id_cell" data-running="' . $num_orders . '"' : '';

                        echo <<<HEREDOC
          <tr data-depot="{$depot_id}" $xClass>
            <td>{$depot['name']}</td>
            <td>{$depot['dp_depot_id']}</td>
            <td>{$kostenstelle}</td>
            <td>{$num_vehicles}</td>
            <td $xCellId>{$td_num}</td>
          </tr>
HEREDOC;
                    }

                    ?>
                </table>
            </div>
        </div>
        <?php

    }

    //#########################################################################
    function WriteHtmlContent($options = "") {
        parent::WriteHtmlContent($options);
        echo $this->GetHtml_FormHeader();

        ?>
        <!-- BEGIN Fahrzeugbestellung -->
        <div id="id_main">
            <div class="seitenteiler" id="id_links">
                <?php echo $this->WriteHtml_AuswahlDepot(); ?>

                <div class="notice">
                    Bestellnotiz:<br>
                    <textarea name="notice"></textarea>
                </div>
                <div class="buttons">
                    <div><?php echo $this->num_orders_all; ?> Fahrzeuge vorgemerkt</div>
                    <button type="submit" id="id_order" name="command"
                            value="send" <?php if ($this->num_orders_all == 0) echo 'disabled'; ?>>Bestellung abschicken
                    </button>
                </div>
            </div>
            <div class="seitenteiler" id="id_rechts">
                <?php echo $this->WriteHtml_DepotInfo(); ?>
            </div>
        </div>
        <!-- END Fahrzeugbestellung -->
        <?php
        echo "</form>\n";
    }
}

?>