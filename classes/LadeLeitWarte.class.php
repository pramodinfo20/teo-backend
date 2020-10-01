<?php
/**
 * ladeleitwarte.class.php
 * Klasse für LadeLeitWarte
 *
 * @author Pradeep Mohan
 */

/**
 * LadeLeitWarte Class, the main App/Process class
 */
class LadeLeitWarte extends QueryMain {
    protected $tableName;
    /**
     * @var DataSrc
     */
    protected $dataSrcPtr;
    public $depotsPtr; //has to be public since its accessed from pagecontroller.php and other page templates

    /**
     * @var Vehicles
     */
    public $vehiclesPtr;
    public $vehicleVariantsPtr;
    public $vehiclesSalesPtr;
    public $vehiclesPostPtr;
    public $vehicleConfigPtr;
    public $vehicleAttributesPtr;
    public $deliveryPlanPtr;
    public $productionPlanPtr;
    public $deliveryToDivisionsPtr;
    public $dailyStatsPtr;
    public $divisionsPtr;
    public $districtsPtr;
    public $restrictionsPtr;
    public $stationsPtr;
    public $zsplPtr;
    public $allUsersPtr;
    public $userTokensPtr;
    public $templatesPtr;
    public $dbHistoryPtr;

    protected $queryPtr;

    function __construct(DataSrc $dataSrcPtr, $tableName = null, $diagnosePtr) {
        $this->depotsPtr = new Depots ($dataSrcPtr, "depots");
        $this->divisionsPtr = new Divisions ($dataSrcPtr, "divisions");
        $this->districtsPtr = new Districts ($dataSrcPtr, "districts");
        $this->departuresPtr = new Departures ($dataSrcPtr, "departures");

        $this->vehiclesSalesPtr = new VehiclesSales ($dataSrcPtr, "vehicles_sales");

        $this->deliveryPlanPtr = new DeliveryPlan($dataSrcPtr);
        $this->deliveryToDivisionsPtr = new DeliveryToDivisions($dataSrcPtr, "delivery_to_divisions");
        $this->deliveryPlanVariantValuesPtr = new DeliveryPlanVariantValues($dataSrcPtr, "delivery_plan_variant_values");

        $this->productionPlanPtr = new ProductionPlan($dataSrcPtr, "production_plan");
        $this->vehiclesPostPtr = new VehiclesPost ($dataSrcPtr, "vehicles_post");
        $this->vehicleConfigPtr = new VehicleConfig($dataSrcPtr, "vehicle_configuration");
        $this->vehicleAttributesPtr = new VehicleAttributes($dataSrcPtr, "vehicle_attributes");
        $this->vehicleVariantsPtr = new VehicleVariants($dataSrcPtr, "vehicle_variants");
        $this->dailyStatsPtr = new DailyStats($dataSrcPtr, "daily_stats");
        $this->stationsPtr = new Stations($dataSrcPtr, "stations");
        $this->restrictionsPtr = new Restrictions($dataSrcPtr, "restrictions");
        $this->zsplPtr = new Zspl($dataSrcPtr, "zspl");
        $this->allUsersPtr = new AllUsers($dataSrcPtr, "users");
        $this->userTokensPtr = new UserTokens($dataSrcPtr, "usertokens");
        $this->csvTemplatesPtr = new CSVTemplates($dataSrcPtr, "csv_templates");
        $this->dbHistoryPtr = new DbHistory($dataSrcPtr, "db_history");
        $this->thirdpartyOrdersPtr = new ThirdpartyOrders($dataSrcPtr, "thirdparty_orders");
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;

        $this->diagnosePtr = $diagnosePtr;

        if (isset($_SESSION["sts_username"]) && !empty($_SESSION["sts_username"])) {
            /*
            $db_vehicle_variants=$this->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante');

            $vehicle_variants=array_combine ( array_column($db_vehicle_variants,'value_id'),array_column($db_vehicle_variants,'value'));

            $sopVariants=$pvsVariants=array();

            foreach($vehicle_variants as $vehicle_variant_value=>$vehicle_variant)
            {
              if(strpos($vehicle_variant,'B14')) $pvsVariants[]=$vehicle_variant_value;
              else $sopVariants[]=$vehicle_variant_value;
            }
            $this->vehiclesPtr = new Vehicles ( $dataSrcPtr,"vehicles",$diagnosePtr,$pvsVariants,$sopVariants);
                */
            $this->vehiclesPtr = new Vehicles ($dataSrcPtr, "vehicles", $diagnosePtr, [], []);
        }
    }

    function getDiagnoseObject() {
        return $this->diagnosePtr;
    }


    function getDbObj() {
        return $this->dataSrcPtr;
    }

    /**
     *
     * @param string $yearmonth
     * @param boolean $remove_overlapping_weeks
     * @param string $prefix_label
     * @param string $prefix_key
     * @return string[]
     *
     * if prefixkey is not passed, then the default prefix_label is 'kw'.. do not change this..
     */
    function getWeeksFromYearMonth($yearmonths, $remove_overlapping_weeks = false, $prefix_label = 'kw', $prefix_key = null, $skip_past_weeks = false) {
        $weeks = array();
        if (!is_array($yearmonths))
            $yearmonths = array($yearmonths);
        foreach ($yearmonths as $yearmonth) {
            $startweek = date('W', strtotime($yearmonth));

            if ($startweek == 52) $startweek = 1;

            if ($skip_past_weeks && $startweek < date('W')) $startweek = date('W');

            if ($remove_overlapping_weeks === true) {
                /**
                 * here we have to check if the this month and the previous month have overlapping calendar weeks..
                 * if it's true, then skip this overlapping week and use the next week as start week
                 */
                //get the previous month and the last week of previous month
                $justmonth = date('n', strtotime($yearmonth));
                //if we are checking for the month of january, then there's no need to check for
                $prevmonth = --$justmonth;
                $prevmonth_lastweek = date('W', strtotime('last day of ' . date('Y-m-01', strtotime($yearmonth . ' -1 month'))));

                if ($prevmonth_lastweek == $startweek)
                    $startweek++;
            }

            $endweek = date('W', strtotime('last day of ' . date('F Y', strtotime($yearmonth))));

            if ($endweek >= $startweek) {
                for ($i = $startweek; $i <= $endweek; $i++) {
                    if (isset($prefix_key)) $weeks[$prefix_key . $i] = $prefix_label . $i;
                    else  $weeks[] = $prefix_label . $i;
                }
            }

        }

        return $weeks;
    }

    function getWeeksFromYearMonthStartingNow($yearmonths, $remove_overlapping_weeks = false, $prefix_label = 'kw', $prefix_key = null) {
        return $this->getWeeksFromYearMonth($yearmonths, $remove_overlapping_weeks, $prefix_label, $prefix_key, true);

    }

    function getAll($selectCols = '', $showHeading = false) {

        return $this->dataSrcPtr->selectAll($this->tableName, $selectCols, null, null, null, $showHeading);

    }

    function getFromId($id, $selectCols = '', $showHeading = false) {
        $whereParams = array();
        $whereParams[] = array('colname' => 'id', 'whereop' => '=', 'colval' => $id);

        $result = $this->dataSrcPtr->selectAll($this->tableName, $selectCols, $whereParams, null, $showHeading);
        return $result[0];
    }

    function getNameFromId($id) {
        return $this->
        newQuery($this->tableName)->
        where('id', '=', $id)
            ->getVal('name');
    }

    /**
     * @param string $tableName
     * @return $this
     */
    function newQuery($tableName = '') {
        if ($tableName == '')
            $this->queryPtr = $this->dataSrcPtr->newQuery($this->tableName);
        else
            $this->queryPtr = $this->dataSrcPtr->newQuery($tableName);
        return $this;
    }

    function changeTableToInsert($tableName = "") {
        if ($tableName != '')
            $this->tableName = $tableName;
        return $this;
    }

    public function __call($method_name, $args) {
        return call_user_func_array(array($this->queryPtr, $method_name), $args);
    }

    function getWhere($selectCols = '', $whereParamsRaw, $orderParamsRaw = null) {
        $whereParams = array();
        foreach ($whereParamsRaw as $whereParamRaw)
            $whereParams[] = array('colname' => $whereParamRaw[0], 'whereop' => $whereParamRaw[1], 'colval' => $whereParamRaw[2]);
        if ($orderParamsRaw) {
            $orderParams = array();
            foreach ($orderParamsRaw as $orderParamRaw)
                $orderParams[] = array('colname' => $orderParamRaw[0], 'ordertype' => $orderParamRaw[1]);
        } else $orderParams = NULL;

        $result = $this->dataSrcPtr->selectAll($this->tableName, $selectCols, $whereParams, $orderParams);
        return $result;
    }

    function getWhereJoin($selectCols = '', $whereParamsRaw = null, $orderParamsRaw = null, $joinParamsRaw) {

        if ($whereParamsRaw) {
            $whereParams = array();
            foreach ($whereParamsRaw as $whereParamRaw)
                $whereParams[] = array('colname' => $whereParamRaw[0], 'whereop' => $whereParamRaw[1], 'colval' => $whereParamRaw[2]);

        } else $whereParams = NULL;

        if ($orderParamsRaw) {
            $orderParams = array();
            foreach ($orderParamsRaw as $orderParamRaw)
                $orderParams[] = array('colname' => $orderParamRaw[0], 'ordertype' => $orderParamRaw[1]);
        } else $orderParams = NULL;

        //note that joinColumns is an array of array of join columns, since a JOIN can be performed based on multiple columns
        //for ex.. JOIN on vehicles.id=vehiclessales.id AND vehicles.col2=vehiclessales.col2
        if ($joinParamsRaw) {
            $joinParams = array();
            foreach ($joinParamsRaw as $joinParamRaw) {
                if (isset($joinParamRaw[3]))
                    $joinParams[] = array('jointype' => $joinParamRaw[0], 'jointable' => $joinParamRaw[1], 'joinColumns' => $joinParamRaw[2], 'joinAlias' => $joinParamRaw[3]);
                else
                    $joinParams[] = array('jointype' => $joinParamRaw[0], 'jointable' => $joinParamRaw[1], 'joinColumns' => $joinParamRaw[2]);
            }

        } else $joinParams = NULL;

        $result = $this->dataSrcPtr->selectAll($this->tableName, $selectCols, $whereParams, $orderParams, $joinParams);
        return $result;
    }

    function add($insertVals, $addtionalParams = null) {
        $id = $this->dataSrcPtr->insert($this->tableName, $insertVals);
        return $id;
    }

    function addMultiple($insertCols, $insertVals, $addtionalParams = null) {
        $numrows = $this->dataSrcPtr->insertMultiple($this->tableName, $insertCols, $insertVals);
        return $numrows;
    }

    function save($updateCols, $updateVals, $whereParamRaw) {
        $whereParam = array();
        $whereParams[] = array('colname' => $whereParamRaw[0], 'whereop' => $whereParamRaw[1], 'colval' => $whereParamRaw[2]);

        $status = $this->dataSrcPtr->update($this->tableName, $updateCols, $updateVals, $whereParams);
        return $status;

    }
    //@todo does not use the normal whereStmt format.. still uses the old format.. used in
    // 1. MitarbeiterController
    //2. Restrictions Class
    //3. stations Class
    function delete($whereParams, $additionalParams = null) {
        $result = $this->dataSrcPtr->delete($this->tableName, $whereParams);
        return $result;
    }


    function specialSqlPrepare($query, $values) {
        return $this->dataSrcPtr->specialSqlPrepare($query, $values);

    }

    /**
     * queryColumnInfo
     * @param string which type of info you need ('data_type', 'column_comment', ...)
     * @param string $tablename name of table to get column info
     * @return array return associative array 'column=>$typeOfInfo' of table
     */
    function queryColumnInfo($typeOfInfo = 'data_type', $tablename = "") {
        if (empty($tablename)) {
            if (empty ($this->columnInfo[$typeOfInfo]) && !empty ($this->tableName))
                $this->columnInfo[$typeOfInfo] = $this->queryColumnInfo($typeOfInfo, $this->tableName);
            return $this->columnInfo[$typeOfInfo];
        }

        if ($typeOfInfo == 'column_comment') {
            $num_cols = 0;
            $table_oid = 0;

            $sql = "select column_name from information_schema.columns where table_name='$tablename'";
            if ($this->newQuery()->query($sql)) {
                $col_names = $this->fetchCol('column_name');
                $num_cols = count($col_names);

                $sql = "SELECT '$tablename'::regclass::oid;";
                if ($this->newQuery()->query($sql)) {
                    $r = $this->fetchRow();
                    $table_oid = $r[0];
                }
            }
            if ($table_oid && $num_cols) {
                $colList = [];
                for ($i = 1; $i <= $num_cols; $i++)
                    $colList[] = "col_description ($table_oid, $i)";
                $sql = "SELECT " . implode(',', $colList);
                if ($this->newQuery()->query($sql)) {
                    $comments = $this->fetchRow();
                    return array_combine($col_names, $comments);
                }
            }
        } else {
            $sql = "select column_name,$typeOfInfo from information_schema.columns where table_name='$tablename'";
            if ($this->newQuery()->query($sql))
                return $this->fetchAssoc("column_name=>$typeOfInfo");
        }
        return false;
    }
}

?>