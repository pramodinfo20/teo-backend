<?php

class ACtion_Fahrzeugbestellungen extends AClass_FormBase {
    protected $user = null;
    protected $stations_overview = null;
    protected $post_variants = null;
    protected $list_num_vehicles = [];
    protected $num_orders_all = 0;
    protected $orders_in_queue = [];
    protected $S_selected_position;
    protected $S_newOrders;
    protected $S_current_row;

    const LOCAL_CSS = "
            h3                                  {margin-bottom: 0; }
            #id_main                            {height: 96%,   width: 98%; margin-top: 20px; margin-left: 40px; display: flex; flex-flow: row nowrap; justify-content: flex-start; align-items: stretch; }
            #id_links                           {width: 900px; }
            #id_rechts                          {width: 100%; margin-left: 10px; position: relative; padding-bottom: 20px;}

            #id_orders                          {border: 2px solid #888; padding: 1px; width: 900px;}
            #id_orders_head                     {width: 900px;}
            #id_orders_body                     {width: 900px; max-height: 500px; overflow-y: scroll; overflow-x: hidden;}

            .orders                             {font-size: 10px; table-layout: fixed; }
            .orders tr                          { }
            .orders td                          {overflow:hidden; float: left; height: 24px; }
            .orders tr td:nth-child(1)          {width: 24px;  padding: 1px 0;  text-align: center;}
            .orders tr td:nth-child(1) input    {margin: 0;}
            .orders tr td:nth-child(2)          {width: 100px; padding: 1px 4px; }
            .orders tr td:nth-child(3)          {width: 100px; padding: 1px 4px; }
            .orders tr td:nth-child(4)          {width: 100px; padding: 1px 4px; }
            .orders tr td:nth-child(5)          {width: 100px; padding: 1px 4px; }
            .orders tr td:nth-child(6)          {width: 50px;  padding: 1px 4px; }
            .orders tr td:nth-child(7)          {width: 100px; padding: 1px 4px; }
            .orders tr td:nth-child(8)          {width: 40px;  padding: 1px 4px; }
            .orders tr td:nth-child(9)          {width: 80px;  padding: 1px 4px; }
            .orders tr td:nth-child(10)         {width: 100px; padding: 0; margin: 0; }
            .orders tr td:nth-child(11)         {width: 10px;  padding: 1px 0; border-left: none; }
            .orders input                       {margin: 0;}
            #id_orders_head tr                  {background-color: #ccc; }
            #id_orders_head td                  {border: 1px solid #444;}

            #id_orders_body tr                  {background-color: #fff; cursor: pointer; }
            #id_orders_body tr.selected         {background-color: #ccf; }
            #id_orders_body td                  {border: 1px dotted #ccc;}

            .buttons                            {text-align: center; }
            .stsbuttons                         {background: linear-gradient(to bottom,#ffe680 0,#ffeeaa 90%,#ffe680 100%); border-radius: 5px; }
            .buttons div                        {height: 30px; width: 120px; padding: 4px 8px 0 8px; vertical-align: bottom;}
            .buttons div span                   {font-size: 13px; padding: 5px 10px;}
            .buttons div img                    {opacity: 0.5; margin-bottom: -6px;}
            .buttons .divdate                   {min-width: 40px; width: auto; display: inline-block; font-size: 10px; line-height: 12px; overflow: visible;}
            .notice                             {text-align: left; }
            .notice  textarea                   {width: 400px; height: 60px;}

            ";

    const LOCAL_JS = <<<HEREDOC

//     function onOrderClick(row)   {
//         var scrollpos = document.getElementById ('id_orders_body').scrollTop;
//         document.location.href = '?' + 'action=fahrzeugbestellungen&selected_position=' + row.dataset.position + '&scrl=' + scrollpos;
//     }


    $(document).ready(function() {

        $('#order-table').tableDnD({
            onDrop: function(table, row) { alert('row='+row);},
            dragHandle: ".dragHandle"
        });

        /*
        $("#order-table tr").hover(function() {
            $(this.cells[1]).addClass('showDragHandle');
        }, function() {
            $(this.cells[1]).removeClass('showDragHandle');
        });
        */

        $(".lieferdatum").datepicker({
            minDate: 0,
            onSelect: function(dateText, inst) {
                alert (dateText);
            }
        });
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

    function WriteHtml_TableFunctions() {
        echo <<<HEREDOC
        <div class="stsbutton" name="move[top]"><img src="/images/symbols/move_to_top.png"><span>nach oben</span></div>
        <div class="stsbutton" name="move[up]"><img src="/images/symbols/move_up2.png"><span>aufwärts</span></div>
        <div class="stsbutton" name="move[down]"><img src="/images/symbols/move_down2.png"><span>abwärts</span></div>
        <div class="stsbutton" name="move[bottom]"><img src="/images/symbols/move_to_bottom.png"><span>nach unten</span></div>
        <div class="divdate">&nbsp;</div><div class="divdate">Lieferdatum<br>setzen</div><div class="divdate"><input name="Lieferdatum" class="lieferdatum" type="text" placeholder="tt.mm.jjjj" size="10"></div>
HEREDOC;
    }

    //#########################################################################

    function __construct(PageController $pagecontroller) {
        parent::__construct($pagecontroller);

        $this->user = $this->controller->GetObject('user');
        if (empty ($this->user))
            return $this->SetError(STS_ERROR_PHP_ASSERTION, "GetObject('user')");

        $this->leitWartePtr = $this->controller->GetObject('ladeLeitWarte');
        $this->vehicleVariantsPtr = $this->controller->GetObject("vehicleVariants");
        $this->vehiclesPtr = $this->controller->GetObject("vehicles");

        $this->S_selected_position = &InitSessionVar($this->S_data['selected_position'], 0);
        $this->S_current_row = &InitSessionVar($this->S_data['current_row'], 0);
    }

    //#########################################################################
    function Init() {
        parent::Init();
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
            <span class="LabelX W080"># Sitze</span>{$order['variant_num_seats']}
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
    //#########################################################################
    function Execute() {
        parent::Execute();

        if (isset($_REQUEST['selected_position']))
            $this->S_selected_position = $_REQUEST['selected_position'];


        $qry = $this->leitWartePtr->newQuery('post_vehicle_order_positions');
        $qry = $qry->join('post_vehicle_order', 'using (order_id)');
        $qry = $qry->join('divisions', 'using (division_id)');
        $qry = $qry->join('depots', 'using (depot_id)');
        $qry = $qry->join('stations', 'using (station_id)');
        $qry = $qry->join('users', 'order_by=users.id');
        $qry = $qry->where('delivered_vehicle_id', 'is null');
        $qry = $qry->orderBy('desired_date');
        $qry = $qry->orderBy('order_datetime');
        $qry = $qry->orderBy('position_id', 'desc');
        $this->actual_orders = safe_array($qry->get_no_parse("
                    position_id,
                    divisions.name      as division,
                    depots.name         as depot,
                    stations.name       as station,
                    depots.dp_depot_id  as oz,
                    variant_type,
                    variant_num_seats,
                    variant_battery,
                    order_datetime::date,
                    desired_date,
                    users.username      as uname,
                    request_notice      as notice",
            'position_id'));

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
        $displayheader->enqueueStylesheet('tablednd', "css/tablednd.css");
        $displayheader->enqueueJs("jquery-datepicker", "js/jquery.ui.datepicker-de.js");
        $displayheader->enqueueJs("tablednd", "js/jquery.tablednd.js");

        $displayheader->enqueueLocalStyle(self::LOCAL_CSS);
        $displayheader->enqueueLocalJS("var href_self = '{$_SERVER['PHP_SELF']}';");
        $displayheader->enqueueLocalJS(self::LOCAL_JS);

        $scrollpos = safe_val($_REQUEST, 'scrl', 0);
        $this->displayfooter->enqueueFinallyCalls("
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
    function WriteHtmlMenu() {

    }

    //#########################################################################
    function WriteHtml_Auftragsliste() {
        ?>
        <h3>Aktuelle Bestellungen/Anforderungen</h3>
        <div id="id_orders">
            <div id="id_orders_head">
                <table class="transparent orders">
                    <tr>
                        <td>&nbsp;</td>
                        <td>Niederlassung</td>
                        <td>ZSP</td>
                        <td>OZ</td>
                        <td>Station</td>
                        <td>Fz. Typ</td>
                        <td>Batterie</td>
                        <td>Sitze</td>
                        <td>Bestelldatum</td>
                        <td>Auslieferung am</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
            <div id="id_orders_body">
                <table class="transparent orders" id="order-table">
                    <?php

                    $n = 0;
                    foreach ($this->actual_orders as $position_id => $order) {
                        $n++;
                        // $xClass = ($position_id == $this->S_selected_position) ? 'class="selected"' : '';

                        echo <<<HEREDOC
          <tr data-position="{$position_id}" data-n="$n" id="select_$position_id">
            <td class="dragHandle"><input type="checkbox" name="select[$position_id]"  value="1" /></td>
            <td class="dragHandle">{$order['division']}</td>
            <td class="dragHandle">{$order['depot']}</td>
            <td class="dragHandle">{$order['oz']}</td>
            <td class="dragHandle">{$order['station']}</td>
            <td>{$order['variant_type']}</td>
            <td>{$order['variant_battery']}</td>
            <td>{$order['variant_num_seats']}</td>
            <td>{$order['order_datetime']}</td>
            <td class="setpos"><input type="text" size="10" name="desired_date[$position_id]" class="lieferdatum inTable" value="{$order['desired_date']}">{$order['desired_date']}</td>
          </tr>
HEREDOC;
                    }

                    for (; $n < 20; $n++) {
                        echo <<<HEREDOC
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
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
                <?php $this->WriteHtml_Auftragsliste(); ?><br>
                <div class="buttons">
                    <?php $this->WriteHtml_TableFunctions(); ?>
                </div>
            </div>
            <div class="seitenteiler" id="id_rechts">

            </div>
        </div>
        <!-- END Fahrzeugbestellung -->
        <?php
        echo "</form>\n";
    }
}

?>