<?php /**
 * @brief Klasse für Datenbank Struktur 1
 * @author Pradeep Mohan
 * @details Klasse für Datenbank Struktur 1
 */

class DatabaseStructureCommon1 implements DatabaseStructure {
    /**
     * Define column names for the table 'depots' here
     * @var array $depotsCols
     */
    private $depotsCols;
    /**
     * Define column names for the table 'divisions' here
     * @var array $divisionsCols
     */
    private $divisionsCols;
    private $db_historyCols;
    /**
     * Define column names for the table 'districts' here
     * @var array $districtCols
     */
    private $districtCols;
    private $departuresCols;
    /**
     * Define column names for the table 'vehicles' here
     * @var array $vehiclesCols
     */
    private $vehiclesCols;
    private $vehicles_salesCols;
    private $vehicles_postCols;
    /**
     * Define column names for the table 'restrictions' here
     * @var array $restrictionsCols
     */
    private $restrictionsCols;
    /**
     * Define column names for the table 'stations' here
     * @var array $stationsCols
     */
    private $stationsCols;
    /**
     * Define column names for the table 'zspl' here
     * @var array $zsplCols
     */
    private $zsplCols;
    /**
     * Define column names for the table 'useres' here
     * @var array $usersCols
     */
    private $usersCols;
    /**
     * Define column names for the table 'useres' here
     * @var array $usertokensCols
     */
    private $usertokensCols;
    /**
     * Define the Headings for the table 'districts' here
     * @var array $districtsHeadings
     */
    private $districtsHeadings;
    /**
     * Define the Headings for the table 'divisions' here
     * @var array $divisionsHeadings
     */
    private $divisionsHeadings;
    /**
     * Define the Headings for the table 'vehicles' here
     * @var array $vehiclesHeadings
     */
    private $vehiclesHeadings;

    private $production_planCols;
    private $delivery_to_divisionsCols;
    /**
     * Define the Headings for the table 'restrictions' here
     * @var array $restrictionsHeadings
     */
    private $restrictionsHeadings;


    private $vehicle_attributesCols;
    private $delivery_planCols;
    private $delivery_plan_variant_valuesCols;

    private $vehicle_configurationCols;
    private $vehicle_attribute_valuesCols;
    private $daily_statsCols;

    private $sequenceNames;

    /**
     * Konstruktor
     * @details Initializes all the Column names, Table names and Column Headings (for use with $showHeading)
     *
     */
    function __construct() {


        $this->sequenceNames = array(
            "restrictions" => "restrictions_restriction_id_seq",
            "stations" => "stations_station_id_seq",
            "users" => "users_id_seq",
            "depots" => "depots_depot_id_seq",
            "districts" => "districts_district_id_seq",
            "divisions" => "divisions_division_id_seq",
            "users" => "users_id_seq",
            "usertokens" => "usertokens_id_seq",
            "users" => "users_id_seq",
            "vehicles" => "vehicles_vehicle_id_seq",
            "vehicle_attributes" => "vehicle_attributes_attribute_id_seq",
            "zspl" => "zspl_zspl_id_seq",
            "csv_templates" => "csv_templates_template_id_seq",
            "vehicles_post" => "vehicles_post_tempid_seq",
            "db_history" => "db_history_queryid_seq",
            "production_plan" => "production_plan_production_plan_id_seq",
            "delivery_to_divisions" => "delivery_to_divisions_delivery_id_seq",
            "delivery_plan" => "delivery_plan_delivery_id_seq",
            "transporter_dates" => "transporter_dates_transporter_date_id_seq",
            "workshop_delivery_id" => "workshop_delivery_workshop_delivery_id_seq"
        );

        $this->delivery_to_divisionsCols = array(
            "delivery_id" => "delivery_id",
            "division_id" => "division_id",
            "variant_value" => "variant_value",
            "delivery_year" => "delivery_year",
            "delivery_week" => "delivery_week",
            "vehicles_delivered" => "vehicles_delivered", // set DEFUALT to 0
            "vehicles_delivered_quantity" => "vehicles_delivered_quantity",
            "added_timestamp" => "added_timestamp",
            "delivery_quantity" => "delivery_quantity",
            "priority" => "priority" //new


        );
        $this->production_planCols = array(
            "production_plan_id" => "production_plan_id",
            "variant_value" => "variant_value",
            "production_week" => "production_week",
            "added_timestamp" => "added_timestamp",
            "update_timestamp" => "update_timestamp",
            "production_quantity" => "production_quantity",
            "vehicles_produced" => "vehicles_produced",
// 				does not exists anymore "vehicles_produced_quantity"=>"vehicles_produced_quantity", //edit change to integer
            "production_to_fleet_qty" => "production_to_fleet_qty"
        );
        $this->depotsCols = array(
            "id" => "depot_id",
            "depot_id" => "depot_id",
            "division_id" => "division_id",
            "name" => "name",
            "address" => "address",
            "lat" => "lat",
            "lon" => "lon",
            "restriction_id" => "depot_restriction_id",
            "depot_restriction_id" => "depot_restriction_id",
            "power_supplier" => "power_supplier",
            "zspl_id" => "zspl_id",
            "dp_depot_id" => "dp_depot_id",
            "emails" => "emails",
            "wenumber" => "wenumber",
            "stationprovider" => "stationprovider",
            "active" => "active",
            "real_depot_restriction_id" => "real_depot_restriction_id", //added for the InterServ role
            "vehicle_variant_values_allowed" => "vehicle_variant_values_allowed",
            "workshop_id" => "workshop_id"
        );

        $this->divisionsCols = array(
            "id" => "division_id",
            "division_id" => "division_id",
            "name" => "name",
            "dp_division_id" => "dp_division_id",
            "charging_control_enabled" => "charging_control_enabled",
            "cost_center" => "cost_center",
            "active" => "active"
        );


        $this->db_historyCols = array(
            "id" => "queryid",
            "queryid" => "queryid",
            "tablename" => "tablename",
            "updatecols" => "updatecols",
            "oldvals" => "oldvals",
            "newvals" => "newvals",
            "userid" => "userid",
            "username" => "username",
            "update_timestamp" => "update_timestamp"
        );


        $this->delivery_planCols = array(
            "delivery_id" => "delivery_id",
            "division_id" => "division_id",
            "yearmonth" => "yearmonth",
            "variant" => "variant",
            "quantity" => "quantity",
            "processed_status" => "processed_status", //new edit set DEFAULT to FALSE
            "requirement_met" => "requirement_met"//new
        );

        $this->delivery_plan_variant_valuesCols = array(
            "delivery_plan_variant_values_id" => "delivery_plan_variant_values_id",
            "external_variant_value" => "external_variant_value",
            "internal_variant_value" => "internal_variant_value",
            "variant_external_name" => "variant_external_name"
        );

        $this->divisionsHeadings = array(
            "headingone" => array(
                "division_id" => array
                (
                    "Niederlassung ID",
                    array("colspan" => 1)
                ),
                "name" => array
                (
                    "Niederlassung",
                    array("colspan" => 1)
                ),
                "dp_division_id" => array
                (
                    "OZ",
                    array("colspan" => 1)
                )

            ));

        $this->vehiclesCols = array(
            "id" => "vehicle_id",
            "vehicle_id" => "vehicle_id",
            "depot_id" => "depot_id",
            "station_id" => "station_id",
            "usable_battery_capacity" => "usable_battery_capacity",
            "c2cbox" => "c2cbox",
            "vin" => "vin",
            "name" => "name",
            "code" => "code",
            "lat" => "lat",
            "lon" => "lon",
            "precondition_duration" => "precondition_duration",
            "emergency_charge_time" => "emergency_charge_time",
            "ikz" => "ikz",
            "charger_controllable" => "charger_controllable",
            "fallback_power_odd" => "fallback_power_odd",
            "fallback_power_even" => "fallback_power_even",
            "refitted_c2c" => "refitted_c2c",
            "three_phase_charger" => "three_phase_charger",
            "charger_power" => "charger_power",
            "late_charging" => "late_charging",
            "late_charging_time" => "late_charging_time"
        );

        $this->vehicle_attributesCols = array(
            "attribute_id" => "attribute_id",
            "name" => "name",
            "description" => "description",
            "editable" => "editable"
        );

        $this->vehicle_attribute_valuesCols = array(
            "attribute_id" => "attribute_id",
            "value" => "value",
            "text" => "text",
            "description" => "description",
            "default" => "default",
            "value_id" => "value_id",
            "partnumber" => "partnumber"
        );


        $this->vehicle_configurationCols = array(
            "vehicle_id" => "vehicle_id",
            "timestamp" => "timestamp",
            "value_id" => "value_id",
            "attribute_id" => "attribute_id",
            "description" => "description",
            "user" => "user"
        );


        $this->districtsCols = array(
            "id" => "district_id",
            "district_id" => "district_id",
            "depot_id" => "depot_id",
            "name" => "name",
            "vehicle_mon" => "vehicle_mon",
            "required_soc_mon" => "required_soc_mon",
            "departure_mon" => "departure_mon",
            "vehicle_tue" => "vehicle_tue",
            "required_soc_tue" => "required_soc_tue",
            "departure_tue" => "departure_tue",
            "vehicle_wed" => "vehicle_wed",
            "required_soc_wed" => "required_soc_wed",
            "departure_wed" => "departure_wed",
            "vehicle_thu" => "vehicle_thu",
            "required_soc_thu" => "required_soc_thu",
            "departure_thu" => "departure_thu",
            "vehicle_fri" => "vehicle_fri",
            "required_soc_fri" => "required_soc_fri",
            "departure_fri" => "departure_fri",
            "vehicle_sat" => "vehicle_sat",
            "required_soc_sat" => "required_soc_sat",
            "departure_sat" => "departure_sat",
            "vehicle_sun" => "vehicle_sun",
            "required_soc_sun" => "required_soc_sun",
            "departure_sun" => "departure_sun"
        );

        $this->departuresCols = array(
            "district_id" => "district_id",
            "vehicle_id" => "vehicle_id",
            "day" => "day",
            "time" => "time",
            "soc" => "soc");

        $this->restrictionsCols = array(
            "id" => "restriction_id",
            "restriction_id" => "restriction_id",
            "parent_restriction_id" => "parent_restriction_id",
            "name" => "name",
            "power" => "power",
            "wiring_type" => "wiring_type",
            "trenner" => "trenner");

        $this->stationsCols = array(
            "id" => "station_id",
            "station_id" => "station_id",
            "restriction_id" => "restriction_id",
            "restriction_id2" => "restriction_id2",
            "restriction_id3" => "restriction_id3",
            "depot_id" => "depot_id",
            "name" => "name",
            "station_power" => "station_power",
            "vehicle_variant_value_allowed" => "vehicle_variant_value_allowed",
            "vehicle_variant_update_ts" => "vehicle_variant_update_ts",
            "deactivate" => "deactivate"

        );

        $this->zsplCols = array(
            "zspl_id" => "zspl_id",
            "id" => "zspl_id",
            "name" => "name",
            "emails" => "emails",
            "division_id" => "division_id",
            "dp_zspl_id" => "dp_zspl_id",
            "active" => "active",
        );

        $this->usersCols = array(
            "dep_id" => "id",
            "id" => "id",
            "username" => "username",
            "division_id" => "division_id",
            "zspl_id" => "zspl_id",
            "email" => "email",
            "passwd" => "passwd",
            "privileges" => "privileges",
            "role" => "role",
            "fname" => "fname",
            "notifications" => "notifications",
            "lname" => "lname",
            "addedby" => "addedby");

        $this->usertokensCols = array(
            "token_id" => "id",
            "id" => "id",
            "selector" => "selector",
            "token" => "token",
            "userid" => "userid",
            "expires" => "expires");


        $this->daily_statsCols = array("vehicle_id" => "vehicle_id",
            "date" => "date",
            "status_code" => "status_code",
            "timestamp_start" => "timestamp_start",
            "timestamp_end" => "timestamp_end",
            "km_start" => "km_start",
            "km_end" => "km_end",
            "ignition_count" => "ignition_count",
            "ignition_time" => "ignition_time",
            "stops" => "stops",
            "speed_max" => "speed_max",
            "speed_avg" => "speed_avg",
            "driving_time" => "driving_time",
            "gps_distance" => "gps_distance",
            "ascent" => "ascent",
            "descent" => "descent",
            "drivemode_d_time" => "drivemode_d_time",
            "drivemode_n_time" => "drivemode_n_time",
            "drivemode_r_time" => "drivemode_r_time",
            "drivemode_e_time" => "drivemode_e_time",
            "t_ambient_start" => "t_ambient_start",
            "t_ambient_end" => "t_ambient_end",
            "t_ambient_min" => "t_ambient_min",
            "t_ambient_max" => "t_ambient_max",
            "t_ambient_avg" => "t_ambient_avg",
            "energy_soc" => "energy_soc",
            "energy_per100km_soc" => "energy_per100km_soc",
            "recuperated_energy" => "recuperated_energy",
            "temp_min" => "temp_min",
            "temp_max" => "temp_max",
            "u_min" => "u_min",
            "u_max" => "u_max",
            "i_min" => "i_min",
            "i_max" => "i_max",
            "recuperations" => "recuperations",
            "accelerations" => "accelerations",
            "hand_brake_count" => "hand_brake_count",
            "door_open_count" => "door_open_count",
            "belt_use_count" => "belt_use_count",
            "door_lock_count" => "door_lock_count",
            "front_window_heating_count" => "front_window_heating_count",
            "front_window_heating_time" => "front_window_heating_time",
            "left_indicator_time" => "left_indicator_time",
            "right_indicator_time" => "right_indicator_time",
            "hazzard_time" => "hazzard_time",
            "low_beam_time" => "low_beam_time",
            "low_beam_count" => "low_beam_count",
            "high_beam_time" => "high_beam_time",
            "high_beam_count" => "high_beam_count",
            "parking_light_time" => "parking_light_time",
            "parking_light_count" => "parking_light_count",
            "rear_fog_light_time" => "rear_fog_light_time",
            "rear_fog_light_count" => "rear_fog_light_count",
            "lat_start" => "lat_start",
            "lon_start" => "lon_start",
            "energy_integrated" => "energy_integrated",
            "energy_per100km_integrated" => "energy_per100km_integrated",
            "hand_brake_time" => "hand_brake_time",
            "lat_end" => "lat_end",
            "lon_end" => "lon_end",
            "soc_start_percent" => "soc_start_percent",
            "soc_end_percent" => "soc_end_percent",
            "soc_start_kwh" => "soc_start_kwh",
            "soc_end_kwh" => "soc_end_kwh",
            "heating_energy" => "heating_energy",
            "climate_energy" => "climate_energy",
            "left_indicator_count" => "left_indicator_count",
            "right_indicator_count" => "right_indicator_count",
            "hazzard_count" => "hazzard_count"
        );

        $this->vehicles_postCols = array(
            'tempid' => 'tempid',
            'startikz' => 'startikz',
            'startakz' => 'startakz',
            'cntvehicles' => 'cntvehicles',
            'vorhaben' => 'vorhaben',
            'vehicle_variant' => 'vehicle_variant',
            'tsnumber' => 'tsnumber',
            'added_timestamp' => 'added_timestamp',
            'addedby_userid' => 'addedby_userid');

        $this->vehicles_salesCols = array(
            'vehicle_id' => 'vehicle_id',
            'tsnumber' => 'tsnumber',
            'delivery_date' => 'delivery_date',
            'production_date' => 'production_date',
            'delivery_week' => 'delivery_week',
            'coc' => 'coc',
            'vorhaben' => 'vorhaben',
            'aib' => 'aib',
            'ikz' => 'ikz',
            'kostenstelle' => 'kostenstelle',
            'bill_number' => 'bill_number',
            'added_timestamp' => 'added_timestamp',
            'vehicle_variant' => 'vehicle_variant',
            'qs_user' => 'qs_user',
            'comments' => 'comments',
            'delivery_status' => 'delivery_status'
        );


        $this->csv_templatesCols = array(
            'id' => 'template_id',
            'template_id' => 'template_id',
            'userid' => 'userid',
            'userrole' => 'userrole',
            'csvfields' => 'csvfields',
            'template_name' => 'template_name'
        );


        //@todo is the variable districtsHeadings  used anywhere at all
        $this->districtsHeadings = array(
            "headingone" => array(
                "district_id" => array
                (
                    "Bezirk ID",
                    array("colspan" => 1)
                ),
                "depot_id" => array
                (
                    "Depot ID",
                    array("colspan" => 1)
                ),
                "name" => array
                (
                    "Bezirk Name",
                    array("colspan" => 1)
                ),
                "mo" => array
                (
                    "Montag",
                    array("colspan" => 3)
                ),
                "di" => array
                (
                    "Dienstag",
                    array("colspan" => 3)
                ),

                "mi" => array
                (
                    "Mittwoch",
                    array("colspan" => 3)
                ),

                "do" => array
                (
                    "Donnerstag",
                    array("colspan" => 3)
                ),

                "fr" => array
                (
                    "Freitag",
                    array("colspan" => 3)
                ),
                "sa" => array
                (
                    "Samstag",
                    array("colspan" => 3)
                ),
                "so" => array
                (
                    "Sonntag",
                    array("colspan" => 3)
                )
            ),
            "headingtwo" => array(
                "",
                "",
                "",
                "Fahrzeug",
                "SOC",
                "Start",
                "Fahrzeug",
                "SOC",
                "Start",
                "Fahrzeug",
                "SOC",
                "Start",
                "Fahrzeug",
                "SOC",
                "Start",
                "Fahrzeug",
                "SOC",
                "Start",
                "Fahrzeug",
                "SOC",
                "Start",
                "Fahrzeug",
                "SOC",
                "Start"
            )
        );
    }

    /**
     * getTableName Returns the Table Name declared in the DatabaseStructure being used
     * @param string $tableName The name of the table to be queried from
     * @return string $tableName
     */
    function getTableName($tableName) {
        switch ($tableName) {
            //use case here if any table names have been changed
            default:
                return $tableName;
                break;
        }
    }

    /**
     * getTableHeadings Returns all the column names for a table as declared in the DatabaseStructure being used
     *
     * @param string $tableName The name of the table to be queried from
     * @param string $selectCols If is an array the returns headings only for these Columns
     * @return array Array of the Headings
     */

    function getTableHeadings($tableName, $selectCols = '') {
        switch ($tableName) {
            case 'depots' :
                return $this->depotsHeadings;
                break;
            case 'divisions' :
                if (!is_array($selectCols))
                    return $this->divisionsHeadings;
                else {
                    $headings = array();
                    foreach ($selectCols as $columnName)
                        $headings["headingone"][] = $this->divisionsHeadings["headingone"][$columnName];
                    return $headings;
                }
                break;
            case 'districts' :
                return $this->districtsHeadings;
                break;
        }
    }

    /**
     * getTableCols Returns all the column names for a table as declared in the DatabaseStructure being used
     *
     * @param string $tableName The name of the table to be queried from
     * @return array Array of the column names based on Column Names defined in the Konstruktor of the DatabaseStructure
     */

    function getTableCols($tableName, $withTableNamePrefix = true) {
        $varname = $tableName . "Cols";
        $processedCols = array();
        //@todo maybe we insert code here so that not every table needs to have its columns defined
        foreach ($this->$varname as $colkey => $colname) {
            if ($withTableNamePrefix)
                $processedCols[$colkey] = $tableName . "." . $colname;
            else
                $processedCols[$colkey] = $colname;
        }

        return $processedCols;
    }

    /**
     * Returns the value of the colname as set in the previous declarations. If colname is '*' returns it just like that.
     * @param string $tableName
     * @param string $colName
     * @param string $withTableNamePrefix only for the special case of * If vehicles.* is passed we get with tablename prefix, when * is passed, we get without prefix
     * @return string
     * @todo should we also consider the case where the tablename should not be returned?
     */
    function getDBStructColName($tableName, $colName, $withTableNamePrefix = false) {
        $prefix = '';
        if ($withTableNamePrefix)
            $prefix = $tableName . '.';
        if (trim($colName) == '*') {
            return $prefix . '*';
        } else if (isset($this->{$tableName . "Cols"}[trim($colName)]))
            return $prefix . $this->{$tableName . "Cols"}[trim($colName)];
        else
            return $prefix . $colName;

    }

    /**
     * getColName Returns the column name as declared in the DatabaseStructure being used
     *
     * @param string $tableName The name of the table to be queried from
     * @param string $colName The name of the column to be checked agains the DatabaseStructure being used
     * @return string Name of the column after verification with DatabaseStructure
     */

    function getColName($tableName, $colName, $withTableNamePrefix = true) {
        $encloseCnt = false;
        if (preg_match("/(DISTINCT)/", $colName)) // @todo this does not get the processed colname
        {
            return $colName;

        }

        if (preg_match_all("/count\((.*)\)(.*)/", $colName, $matches)) // @todo this does not get the processed colname
        {

            $colName = $matches[1][0] . $matches[2][0];
            $encloseCnt = true;
        }

        $alias = "";
        if (strpos($colName, " as ")) {

            $split_terms = explode(" as ", $colName);
            $colName = $split_terms[0];
            $alias = " as " . $split_terms[1];
        }

        if (strpos($colName, ".")) {
            $tableName = explode(".", $colName)[0];
            $colName = explode(".", $colName)[1];
        }

        if ($withTableNamePrefix)
            $colName = $tableName . "." . $this->{$tableName . "Cols"}[trim($colName)];
        else
            $colName = $this->{$tableName . "Cols"}[trim($colName)];

        if ($encloseCnt === true)
            $colName = 'count(' . $colName . ')';

        return $colName . $alias;

    }

    function getSequenceName($insertTable) {
        if (isset($this->sequenceNames[$insertTable]))
            return $this->sequenceNames[$insertTable];
        else return '';
    }
}