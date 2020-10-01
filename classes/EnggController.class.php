<?php

class EnggController extends PageController {
    protected $showconfig_content;
    protected $msgs;
    protected $vehicleAttributes;
    protected $vehicleId;
    protected $vin;
    protected $depotName;
    protected $code;
    protected $showconfigTimestamp;
    protected $dailyStats;
    protected $deliveredVehicles;
    protected $producedVehicles;
    protected $translate;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->vehicle_search_content = "";
        $this->config_content = "";
        $this->vehicleId = null;
        $this->deliveredVehicles = null;
        $this->producedVehicles = array();
        $this->displayHeader = $this->container->getDisplayHeader();

// 		$this->displayHeader->enqueueStylesheet('tablesorter-default', "css/theme.default.css");
// 		$this->displayHeader->enqueueJs("jquery-tablesorter", "js/jquery.tablesorter.min.js");
// 		$this->displayHeader->enqueueJs("jquery-tablesorter-pager", "js/jquery.tablesorter.pager.js");
// 		$this->displayHeader->enqueueJs("jquery-tablesorter-widgets", "js/jquery.tablesorter.widgets.js");

        $this->displayHeader->enqueueJs("jquery-datepicker", "js/jquery.ui.datepicker-de.js");
        $this->displayHeader->enqueueJs("jquery-timepicker", "js/jquery-ui-timepicker-addon.js");
        $this->displayHeader->enqueueJs("sts-custom-aftersales", "js/sts-custom-aftersales.js");

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());

        $this->action = $this->requestPtr->getProperty('action');
        $common_action = $this->requestPtr->getProperty('common_action');

        if (isset($this->action) && method_exists($this, $this->action))
            call_user_func(array($this, $this->action));
        else if (isset($common_action) && method_exists($this, $common_action))
            call_user_func(array($this, $common_action));
        /*
        else
        {
          $this->vehicleId=$this->requestPtr->getProperty("vehicle_search");

          if(isset($this->vehicleId))
          {
            $this->vin=$this->ladeLeitWartePtr->vehiclesPtr->getVinFromId($this->vehicleId);
            $this->code=$this->ladeLeitWartePtr->vehiclesPtr->getCodeFromId($this->vehicleId);
          }
          $this->selectVehicle();
        }
            */

        $this->displayHeader->printContent();

        $this->printContent();
    }


    function ajaxGetDtcCodes() {
        $this->allEcus = $this->ladeLeitWartePtr->newQuery('ecus')->where('ecu_id', '>', 0)->orderBy('name')->get('name=>name');
        $this->dtcs_codes = [];
        foreach ($this->allEcus as $ecu) {
            $ecu = strtoupper($ecu);
            $this->dtcs_codes[$ecu] = $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs')->where('ecu', '=', $ecu)->get("concat(dtc_number::text,' | ',text) as label,dtc_number::text as value");
        }
        $ecu = $this->requestPtr->getProperty('ecu');
        echo json_encode($this->dtcs_codes[$ecu]);
        exit(0);
    }

    function ajaxGetLogNames() {
        //passed=f since we want to search only for those test where log data is not passed
        $log_data_names = $this->ladeLeitWartePtr->diagnosePtr->newQuery('log_data')->where('passed', '=', 'f')->get('distinct name as value');
        echo json_encode($log_data_names);
        exit(0);
    }

    function ajaxGetVariant() {
        $vv_filter = $this->requestPtr->getProperty('vv_filter');

        $result = $this->ladeLeitWartePtr->newQuery('vehicle_variants');
        if (preg_match('#^([A-Za-z]{1}[0-9]{2})#', $vv_filter))
            $result = $result->where('windchill_variant_name', 'ilike', $vv_filter . '%');
        $result = $result->orderBy('windchill_variant_name')->get('vehicle_variant_id as value,windchill_variant_name as text');
        echo json_encode($result);
        exit(0);
    }


    /**
     * *
     * Function to get vehicle vin to allow searching with autocomplete
     */
    function ajaxVehicleVinSearch() {
        $term = $this->requestPtr->getProperty('term');
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->multipleAndWhere('vin', 'ILIKE', '%' . $term . '%')
            ->get("vin as label,vin as value");
        echo json_encode($vehicles);
        exit(0);
    }

    function teoexceptions() {
        $this->allEcus = $this->ladeLeitWartePtr->newQuery('ecus')
            ->where('ecu_id', '>', 0)
            ->orderBy('name')
            ->get('name=>name');
        $this->dtcs_codes = [];
//         $this->ecu_options='<option value="">--ECU wählen--</option>';
        foreach ($this->allEcus as $ecu) {
            $ecu = strtoupper($ecu);
//             $this->ecu_options.='<option value="'.$ecu.'">'.$ecu.'</option>';
            $this->dtcs_codes[$ecu] = $this->ladeLeitWartePtr->diagnosePtr->newQuery('dtcs')->where('ecu', '=', $ecu)->get("concat(dtc_number::text,' | ',text) as label,dtc_number::text as value");
        }

    }


    function verlaufsdaten() {
        if ($this->user->user_can('grafana_view')) {
            $grafana_helper = new GrafanaApi($this->ladeLeitWartePtr, $this->user);
            $grafana_helper->setupCurl();
            $grafana_helper->authUserWithKey();
        }

        //set as admin
//         setcookie('sts_grafana','=',time()-3600);
//         setcookie('sts_grafana','eyJrIjoiM1pFUVB2VVNOdWtoR0ZvVzJ6UUlzbWxZRmE1NzBpaVQiLCJuIjoiYWRtaW4iLCJpZCI6MX0=',time()+3600);

    }

    function produzierte() {
        $db = $GLOBALS['config']->get_db('diagnose');
        $domain = null;
        if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        }

        if ($domain == "62.75.137.43")
            $dbconn = pg_connect("host=localhost port=5432 dbname=LeitwartenDB user=leitwarte password=ahy5YocoPhuaW7qu")
            or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());
        else if ($domain == "streetscooter-cloud-system.eu" || $domain == "web4.strs.adns.de")
            $dbconn = pg_connect("host=10.12.54.173 port=5432 dbname=diagnose user=diagnose password=cUEFNetu1AKFC3yohMNK")
            or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());
        else
            $dbconn = pg_connect("host=".$db['host']." port=".$db['port']."  
            dbname=".$db['db']." user=".$db['user'] ."
            password=".$db['password'] )
            or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());

        $query = "select * from (
			select  distinct on(vin) vin, date from general where " .
            // substring(vin,1,6) IN ('WS5B16','WS5D16','WS5E17') and
            "vin not like '%MIRCO%' and vin not like
			'%SOP%' and vin not like '%GRUESS%'
			and date between '2016-01-01' and now()+'1 day'
			order by vin, date asc
			) as a order by date";

        $result = pg_query($query) or die('Abfrage fehlgeschlagen: ' . pg_last_error());

        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $this->producedVehicles[] = $line;
        }
    }

    function ausgelieferte() {
        $this->deliveredVehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            // ->where('vehicles_sales.delivery_date','>=','2016-09-17')
            ->where('vehicles_sales.delivery_status', '=', 't')
            ->where('vehicles.depot_id', 'NOT IN', array(0, 3170))
            ->multipleAndWhere('vehicles.vin', 'LIKE', 'WS5B16%', 'OR',
                'vehicles.vin', 'LIKE', 'WS5D16%', 'OR',
                'vehicles.vin', 'LIKE', 'WS5E17%')
            ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
            ->get('vehicles.vehicle_id,vehicles.vin, vehicles.code,vehicles_sales.delivery_date');
        foreach ($this->deliveredVehicles as &$vehicle) {
            $vehicle['delivery_date'] = date('Y-m-d', strtotime($vehicle['delivery_date']));
        }

    }

    function getOffline() {

        $vid = $this->requestPtr->getProperty("vid");
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getOffline($vid);
        if ($vehicles)
            if ($vehicles[0]['online'] == 't') echo '1';
            else echo '0';
        else
            echo '';
        exit(0);

    }

    function getDailyStats() {
        $this->dailyStats = $this->ladeLeitWartePtr->dailyStatsPtr->getStatsForVehicleId($this->vehicleId);
    }

    function abfrage() {
        $ajax_query = $this->requestPtr->getProperty("ajax");

        $this->showconfigTimestamp = strtotime($this->requestPtr->getProperty("showconfig_timestamp"));

        $this->vehicleAttributes = $this->ladeLeitWartePtr->vehicleConfigPtr->getVehicleConfiguration($this->vehicleId, $this->showconfigTimestamp);
        $this->vin = $this->vehicleAttributes[0]['vin'];
        $this->depotName = $this->vehicleAttributes[0]['dname'];
        $this->code = $this->vehicleAttributes[0]['code'];

        $this->changeConfig();
        $this->getDailyStats(); //@todo is this optimum place to call the other functions?


// 		$attributes=$this->ladeLeitWartePtr->vehicleAttributesPtr->getWhere(null,array(array("attribut_id"=>1)));
// 		[vehicle_id] => 2 [timestamp] => 1459839660 [value_id] => 1 [attribute_id] => 1 [description] => [user] => Weihl [dname] => Nicht zugewiesen [aname] => Motortyp [value] => 140V [vin] => WST66666601234767 [code] =>


// 		$this->config_content.=$attributes[0]["vin"].$attributes[0]["code"];
// 		$processedAttributes=array();
// 		foreach($attributes as $attribute)
// 		{

// 			$processedAttributes[]=array($attribute["attribute_id"],$attribute["aname"],$attribute["value"]);

// 		}

// 		$displayTable=new DisplayTable($processedAttributes);
// 		$this->config_content.=$displayTable->getContent();

// 		if($ajax_query==true)
// 		{
// 			echo $this->config_content;
// 			exit(0);

// 		}
    }

    function changeConfig() {

        $attributes = $this->ladeLeitWartePtr->vehicleAttributesPtr->getAll();
        $this->qform_editConfig = new QuickformHelper($this->displayHeader, "vehicle_attrib_edit");
        $processedAttributes = array_combine(array_column($attributes, 'attribute_id'), array_column($attributes, 'name'));
        $this->qform_editConfig->getVehicleAttribEdit($this->vehicleId, $this->showconfigTimestamp,
            $this->user->getUserFullName(), $processedAttributes); //@todo or rather pass user id?

    }


    function selectVehicle() {
        try {
            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getAll(array("vin", "code", "vehicle_id"));
        } catch (Exception $e) {
            $this->msgs[] = $e->getMessage();
        }

        $processed_vehicles = array();
        foreach ($vehicles as $vehicle)
            $processed_vehicles[$vehicle["vehicle_id"]] = $vehicle["vin"] . "(" . $vehicle["code"] . ")";

        $qform = new QuickformHelper ($this->displayHeader, "vehicle_search_form");
        $qform->add_vehicle_search($processed_vehicles);

        $this->vehicle_search_content = $qform->getContent();

    }

    /***
     * Function to getch concatenated vin and vehicle code to allow searching using autocomplete
     */
    function ajaxVehicleSearch() {
        $term = $this->requestPtr->getProperty('term');
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->multipleAndWhere('vin', 'ILIKE', '%' . $term . '%', 'OR', 'code', 'ILIKE', '%' . $term . '%', 'OR', 'depots.name', 'ILIKE', '%' . $term . '%')
            ->join('depots', 'depots.depot_id=vehicles.depot_id', 'LEFT JOIN')
            ->get("concat(vin,' (',code,')',' ',depots.name) as label,vehicle_id as value");
        echo json_encode($vehicles);
        exit(0);
    }


    /***
     * Function to set vehicle special approval
     */
    function specialapp() {
        if (!$this->user->user_can('specialapproval')) {
            $this->specialapp_content = 'Keine Berechtigungen';
            return;
        }

        $approval_comment = "";
        if (isset($_POST['selected_vehicle'])) $selected_vehicle = filter_var($_POST['selected_vehicle'], FILTER_SANITIZE_NUMBER_INT);
        if (isset($_POST['approval_comment'])) $approval_comment = filter_var($_POST['approval_comment'], FILTER_SANITIZE_STRING);

        $qform = new QuickformHelper ($this->displayHeader, "vehicle_search_form");
        $group = $qform->addElement('group');
        $group->addElement('static')->setContent('VIN/AKZ eingeben: ');
        $group->addElement('text', 'vehicle_search_new', ['id' => 'vehicle_search_new', 'data-targetinput' => 'selected_vehicle'])->setLabel('VIN/AKZ eingeben: ');
        $group->addElement('static')->setContent('<a href="#" style="margin-left: 8px;" id="reset_vehicle_search_new"><span class="genericon genericon-close"></span>Leeren</a>');
        $qform->addElement('hidden', 'selected_vehicle', ['id' => 'selected_vehicle']);
        if (isset($selected_vehicle)) {
            $set = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', '=', $selected_vehicle)->getOne('special_qs_approval', 'special_qs_approval_set_user', 'special_qs_approval_set_time', 'special_qs_approval_comment');
            $special_approval_val = $set['special_qs_approval'];

            if (isset($_POST['save_special_app'])) {
                if (isset($_POST['special_approval']) && ($special_approval_val != 't')) {
                    $udpate_cols = ['special_qs_approval', 'special_qs_approval_set_user', 'special_qs_approval_set_time', 'special_qs_approval_comment'];
                    $udpate_vals = ['t', $_SESSION['sts_userid'], 'now()', $approval_comment];

                    $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', '=', $selected_vehicle)->update($udpate_cols, $udpate_vals);
                    $qform->addElement('static')->setContent('Änderungen gespeichert');
                    $special_approval_val = 't';
                } else if (!isset($_POST['special_approval']) && $special_approval_val == 't') {
                    $udpate_cols = ['special_qs_approval'];
                    $udpate_vals = ['f'];

                    $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', '=', $selected_vehicle)->update($udpate_cols, $udpate_vals);
                    $qform->addElement('static')->setContent('Änderungen gespeichert');
                    $special_approval_val = 'f';
                }
            }

            $group = $qform->addElement('group');
            $group->addElement('static', null, ['for' => 'special_approval'])->setTagName('label')->setContent('Sondergenehmigung?');
            $checkbox = $group->addElement('checkbox', 'special_approval', ['id' => 'special_approval', 'style' => 'vertical-align:middle']);
            if ($special_approval_val == 't') $checkbox->setAttribute('checked');
            else $checkbox->removeAttribute('checked');

            if ($special_approval_val != 't') {
                $qform->addElement('textarea', 'approval_comment', ['id' => 'approval_comment', 'cols' => '40', 'rows' => '5'])->setLabel('Kommentar');
            }


            $qform->addElement('submit', 'save_special_app', ['value' => 'Speichern']);
        } else
            $qform->addElement('submit', null, ['value' => 'Suchen']);

        $qform->addElement('hidden', 'action')->setValue('specialapp');
        $this->specialapp_content = $qform->getContent();
    }

    function vehiclebooking() {
        $this->displayHeader->enqueueJs("sts-custom-fleet", "js/sts-custom-fleet.js");

        if (isset ($_POST['sendbooking']))
            ob_start();
    }

    function documentvehicles() {
        $this->displayHeader->enqueueJs("sts-custom-fleet", "js/sts-custom-fleet.js");
        $this->displayHeader->enqueueStylesheet("css-chosen", "css/chosen.min.css");
        // $this->displayHeader->enqueueJs("jquery-jquery", "//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js");
        $this->displayHeader->enqueueJs("jquery-chosen", "js/chosen.jquery.min.js");
    }
}