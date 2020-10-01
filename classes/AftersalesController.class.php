<?php

/*change*/

class AftersalesController extends ControllerBase {
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
    protected $diagnosePtr;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);
        $this->vehicle_search_content = "";
        $this->config_content = "";
        $this->vehicleId = null;
        $this->deliveredVehicles = null;
        $this->producedVehicles = [];

        $this->mySessionVars = &InitSessionVar($_SESSION['aftersales']['werkstaettenlogin'], []);
        $this->newLogins = &InitSessionVar($mySessionVars['newLogins'], []);
        $this->changedPwds = &InitSessionVar($mySessionVars['changedPwds'], []);

        $this->diagnosePtr = $ladeLeitWartePtr->getDiagnoseObject();
    }

    //=============================================================================================================
    function Init($action) {
        parent::Init($action);

        $this->displayHeader->enqueueStylesheet('tablesorter-default', "css/theme.default.css");


        $this->displayHeader->enqueueLocalStyle('
            .wksArea                    {position: relative; top: -50px; margin-bottom: 30px; }
            .wksFrame                   {display: inline-block; background-color: #ddf; border: 2px solid #888; width: 1067px;}
            .wksTableArea               {width:1067px; max-height:250px; overflow-Y: scroll; position: relative; top: -1px;}
            .wksTable                   {table-layout: fixed; width: auto; border-color: #888;}
            .wksTable thead tr          {background-color: #ddd;}
            .wksTable thead td          {border-bottom: none; font-weight: bold;}
            .wksTable td                {padding: 2px 5px; overflow: hidden;}
            .wksTable tr                {height: 33px; }
            .wksTable td:nth-child(1)   {width:  50px; }
            .wksTable td:nth-child(2)   {width: 200px; }
            .wksTable td:nth-child(3)   {width: 250px; }
            .wksTable td:nth-child(4)   {width: 189px; }
            .wksTable td:nth-child(5)   {width: 150px; }
            .wksTable td:nth-child(6)   {width: 150px; }
            .wksTable td:nth-child(7)   {width:  10px; padding: 2px 0; }
            .wksHead                    {border-bottom: 2px solid #888; }

            .wksLogin                   {width: 140px; }
            .wksPasswd                  {background-color: #ffd; width: 140px; }
        ');

        $this->displayHeader->enqueueLocalJs("
            var href_wks_login = '{$_SERVER['PHP_SELF']}?action=werkstaettenlogin';
        ");

        $this->displayHeader->enqueueJs("jquery-tablesorter", "js/jquery.tablesorter.min.js");
        $this->displayHeader->enqueueJs("jquery-tablesorter-pager", "js/jquery.tablesorter.pager.js");
        $this->displayHeader->enqueueJs("jquery-tablesorter-widgets", "js/jquery.tablesorter.widgets.js");
        $this->displayHeader->enqueueJs("jquery-datepicker", "js/jquery.ui.datepicker-de.js");
        $this->displayHeader->enqueueJs("jquery-timepicker", "js/jquery-ui-timepicker-addon.js");
        $this->displayHeader->enqueueJs("sts-custom-aftersales", "js/sts-custom-aftersales.js");
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());


        $this->vehicleId = $this->requestPtr->getProperty("vehicle_search");
        if (isset($this->vehicleId)) {
            $this->vin = $this->ladeLeitWartePtr->vehiclesPtr->getVinFromId($this->vehicleId);
            $this->code = $this->ladeLeitWartePtr->vehiclesPtr->getCodeFromId($this->vehicleId);
        }
        $this->selectVehicle();
        //call before so that saveQS knows what to save!
        $this->poolVehicles = $this->ladeLeitWartePtr->vehiclesPtr->getStsPoolVehicles();
    }

    //=============================================================================================================
    function Execute() {
        if (isset($this->action) && method_exists($this, $this->action))
            call_user_func(array($this, $this->action));
        else {
            $this->showListObjects();
        }

        //call after saveQS so that the saved vehicle is removed from the list
        $this->poolVehicles = $this->ladeLeitWartePtr->vehiclesPtr->getStsPoolVehicles();
    }

    /**
     * Shows list of either Niederlassungen, ZSPLn or ZSPn or the vehicle station assignment table.
     */
    function showListObjects() {
        $div = $this->requestPtr->getProperty('division');
        $zspl = $this->requestPtr->getProperty('zspl');
        $depot = $this->requestPtr->getProperty('zsp');
        $this->overview = new CommonFunctions_VehiclesStationsOverview($this->ladeLeitWartePtr, $this->displayHeader, $this->user, $this->requestPtr);
        if ($depot) {
            $this->overview->buildVehiclesStationsTable($depot);
            $this->overview->buildSummaryForDepot($depot);
        } else if ($zspl) {
            $this->overview->buildOverviewForZspl($zspl);
        } else if ($div) {
            $this->overview->buildOverviewForDivision($div);
        } else {
            $this->showingDivisions = true;
            $this->overview->buildOverview();
        }

    }

    //=============================================================================================================
    function PrintPage() {
        $this->displayHeader->printContent();
        $this->printContent();
    }

   //  //=============================================================================================================
   //  function produzierte() {
   //      $domain = null;
   //      if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
   //          $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
   //      }

   //      if ($domain == "62.75.137.43")
   //          $dbconn = pg_connect("host=localhost port=5432 dbname=Staging_Leitwarten user=webinterface password=kv2bktj7Xn2IpNv5R82p")
   //          or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());
   //      else if ($domain == "streetscooter-cloud-system.eu" || $domain == "web4.strs.adns.de" || $domain == "staging.streetscooter-cloud-system.eu")
   //          $dbconn = pg_connect("host=10.12.54.173 port=5432 dbname=Staging_Diagnose user=diagnose password=cUEFNetu1AKFC3yohMNK")
   //          or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());
   //      else
   //          $dbconn = pg_connect("host=localhost port=5432 dbname=Diagnose_v1 user=diagnose password=diagnose")
   //          or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());

   //      $query = "select * from (
			// select  distinct on(substring (vin,15)) vin, date from general where
			// substring(vin,0,7)='WS5B16' and vin not like '%MIRCO%' and vin not like
			// '%SOP%' and vin not like '%GRUESS%'
			// and date between '2016-09-01' and now()+'1 day'
			// order by substring (vin,15), date desc
			// ) as a order by date";

   //      $result = pg_query($query) or die('Abfrage fehlgeschlagen: ' . pg_last_error());

   //      while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
   //          $this->producedVehicles[] = $line;
   //      }
   //  }

    //=============================================================================================================
    function produzierte() {

        $query = "select * from (
            select  distinct on(substring (vin,15)) vin, date from general where
            substring(vin,0,7)='WS5B16' and vin not like '%MIRCO%' and vin not like
            '%SOP%' and vin not like '%GRUESS%'
            and date between '2016-09-01' and now()+'1 day'
            order by substring (vin,15), date desc
            ) as a order by date";

        $qry = $this->diagnosePtr->newQuery('general');
        $result = $qry->query($query); 
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $this->producedVehicles[] = $line;
        }
    }

    //=============================================================================================================
    function ausgelieferte() {
        $this->deliveredVehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
            ->where('vehicles_sales.delivery_date', '>=', '2016-09-17')
            ->where('vehicles_sales.delivery_status', '=', 't')
            ->where('vehicles.depot_id', 'NOT IN', array(0, 3170))
            ->where('vehicles.vin', 'LIKE', 'WS5B16%')
            ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
            ->get('vehicles.vehicle_id,vehicles.vin, vehicles.code,vehicles_sales.delivery_date');
        foreach ($this->deliveredVehicles as &$vehicle) {
            $vehicle['delivery_date'] = date('Y-m-d', strtotime($vehicle['delivery_date']));
        }
    }

    //=============================================================================================================
    function werkstaettenlogin() {
        $wks_id = 0;
        $create_login = false;
        $create_passwd = '';
        $change_passwd = false;

        if (isset ($_REQUEST['create_account'])) {
            $wks_id = $_REQUEST['create_account'];

            if (!isset ($this->newLogins[$workshop_id])) {
                $create_login = $_REQUEST['login'];
                $create_passwd = $this->user->generatePassword();
                $this->newLogins[$wks_id] = ['L' => $create_login, 'P' => $create_passwd];
            }
        }

        if (isset ($_REQUEST['reset_passwd'])) {
            $wks_id = $_REQUEST['reset_passwd'];
            $change_passwd = $this->user->generatePassword();
            $this->changedPwds[$wks_id] = $change_passwd;
        }


        if ($create_login) {
            $qry = $this->ladeLeitWartePtr->newQuery('workshops')->where('workshop_id', '=', $wks_id);
            $wks = $qry->getOne('*');

            $insert_vals = [
                'username' => $create_login,
                'email' => $wks['email_person1'],
                'passwd' => $create_passwd,
                'privileges' => 'N;',
                'fname' => 'Werkstatt',
                'lname' => $wks['name'],
                'addedby' => $this->user->getUserId(),
                'role' => 'workshop',
                'workshop_id' => $wks_id,
            ];

            $this->ladeLeitWartePtr->allUsersPtr->add($insert_vals);
        }

        if ($change_passwd) {
            $updateCols = ['passwd'];
            $updateVals = $change_passwd;
            $whereParamsRaw = ['worshop_id', '=', $wks_id];

            $this->ladeLeitWartePtr->allUsersPtr->save($updateCols, $updateVals, $whereParamsRaw);
        }

        $subselect = ("select distinct dp_zspl_id from depots join zspl using (zspl_id) where depots.workshop_id=workshops.workshop_id");

        $qry = $this->ladeLeitWartePtr->newQuery('workshops')->where('workshop_id', '>', '0');
        $this->all_workshops = $qry->get_no_parse("*, array($subselect) as zspls", 'workshop_id');

        $qry = $this->ladeLeitWartePtr->newQuery('users')->where('workshop_id', '>', '0');
        $this->all_logins = $qry->get('username,workshop_id,id', 'workshop_id');

    }

    //=============================================================================================================
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

    //=============================================================================================================
    function getDailyStats() {
        $this->dailyStats = $this->ladeLeitWartePtr->dailyStatsPtr->getStatsForVehicleId($this->vehicleId);
    }

    //=============================================================================================================
    function saveQS() {
        foreach ($this->poolVehicles as $vehicle) {
            $vehicle_id = $vehicle['vehicle_id'];
            if (isset($_POST['finishedstatus_' . $vehicle['vehicle_id']])) {
                //need to check since setQSFertig also updates the production quantity in the production_plan table
                //and we dont want it o be done unless actually a vehicle has been produced

                if ($this->ladeLeitWartePtr->vehiclesPtr->getQSFertig($vehicle_id) != 't') {
                    //pass third argument true so that we know this is a pool vehicle an thus vehicles produced quantity in the production plan will not be updated
                    $this->ladeLeitWartePtr->vehiclesPtr->setQSFertig($vehicle_id, 'TRUE', true);
                    $this->msgs[] = 'Änderungen gespeichert!';
                }
            }

        }

    }

    //=============================================================================================================
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

    //=============================================================================================================
    function changeConfig() {

        $attributes = $this->ladeLeitWartePtr->vehicleAttributesPtr->getAll();
        $this->qform_editConfig = new QuickformHelper($this->displayHeader, "vehicle_attrib_edit");
        $processedAttributes = array_combine(array_column($attributes, 'attribute_id'), array_column($attributes, 'name'));
        $this->qform_editConfig->getVehicleAttribEdit($this->vehicleId, $this->showconfigTimestamp,
            $this->user->getUserFullName(), $processedAttributes); //@todo or rather pass user id?

    }

    //=============================================================================================================
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

    /**
     * *
     * Function to get concatenated vin, depot name, vehicle code to allow searching using autocomplete
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

    function verlaufsdaten() {
        if ($this->user->user_can('grafana_view')) {
            $grafana_helper = new GrafanaApi($this->ladeLeitWartePtr, $this->user);
            $grafana_helper->setupCurl();
            $grafana_helper->authUserWithKey();
        }
    }
}