<?php

/**
 * VehicleVariants.class.php
 * Klasse für Fahrzeug Varianten Infos
 * @author Pradeep Mohan
 */

/**
 * Class to handle vehicle variant infos, especially for COC Papier generation
 */
class VehicleVariants extends LadeLeitWarte
{

    const LAYOUT_NAMES = [
        'EPOS' => '„Post“ mit Eigenbaukoffer',
        'BPOS' => '„Post“ mit Zuliefererkoffer',
        'EBOX' => '„Box“ mit Eigenbaukoffer',
        'BBOX' => '„Box“ mit Zuliefererkoffer',
        'PICK' => '„PickUp“',
        'PURE' => '„Pure“',
        'PVS' => '„PVS“ Produkt Vorserie',
        'YAPT' => '„YAPT“ YAMATO ProtoType'
    ];

    const FEATURE_NAMES = [
        'A' => 'Doppelsitz',
        'B' => 'Notsitz',
        'C' => 'Letterbox',
        'D' => 'Doppelsitzer mit Rundumleuchte',
        'E' => 'Doppelsitzer für Linksverkehr',
        'F' => 'SonderPost: Doppelsitz mit Radio',
        'G' => 'SonderPost: Letterbox mit Radio',
        'H' => 'SonderPost: Notsitz mit Keyless Entry',
        'I' => 'Doppelsitzer mit Rechtslenker',
        'Z' => 'Dummy'
    ];

    // insert new Parameters (SDA/1 [38 kWh] and SDA/2 [76 kWh]) into database in parts table
    const BATTERY_NAMES = [
        'A' => 'V6 8M (20 kWh)',
        'B' => 'SDA (30 kWh)',
        'C' => 'V6 16M (40 kWh)',
        'D' => 'SDA (38 kWh)',
        'E' => 'SDA (76 kWh)',
        // 'F' => 'V6+ 16M (43,4 kWh)',
        'Z' => 'Dummy'
    ];

    const STEERING_MAP = [
        "A" => "right hand traffic",
        "B" => "left hand traffic (right hand drive)",
        "C" => "left hand traffic (left hand drive)"
    ];

    const REAR_WINDOW_MAP = [
        "X" => "rear window",
        "Y" => "no rear window"
    ];

    const AIR_CONDITIONING_MAP = [
        "X" => "air conditioning",
        "Y" => "no air conditioning"
    ];

    const PASSENGER_AIRBAG_MAP = [
        "X" => "codriver airbag",
        "Y" => "no codriver airbag"
    ];

    const KEYLESS_ENTRY_GO = [
        "A" => "keyless entry",
        "B" => "keyless go"
    ];

    const SPECIAL_APPLICATION_AREA_MAP = [
        "P" => "Post (Sackkarrenhalter, Airbag-Logo, Front-Logo, Beklebung)",
        "U" => "urban / construction (rotating beacon)",
        "Y" => "No"
    ];

    const RADIO_MAP = [
        "A" => "radio with mirrowlink",
        "B" => "only camera"
    ];

    const SOUND_GENERATOR_MAP = [
        "X" => "sound generator",
        "Y" => "no sound generator"
    ];

    const COUNTRY_CODE_MAP = [
        "A" => "Australia",
        "D" => "Germany",
        "J" => "Japan",
        "N" => "Netherlands",
        "U" => "USA"
    ];

    const COLOR_MAP = [
        "Y" => "yellow",
        "O" => "orange",
        "W" => "white"
    ];

    const WHEELING_MAP = [
        "A" => "summer wheels",
        "B" => "winter wheels"
    ];

    const LENGTY_BODY_MAP = [
        "A" => "(L1 - W1) (4700 mm – 3050 mm)",
        "B" => "(L1 - W2) (4700 mm – 3200 mm)",
        "C" => "(L2 - W3) (5300 mm – 3500 mm)",
        "E" => "(L3 - W3) (5800 mm – 3500 mm)",
        "F" => "(L3 - W4) (5800 mm –3900 mm)",
        "Y" => "None"
    ];

    const FRONT_AXLE_MAP = [
        "A" => "front axle 1 (1350 kg)"
    ];

    const REAR_AXLE_MAP = [
        "A" => "HA1",
        "B" => "HA2 (1800 kg)",
        "Y" => "no rear axle"
    ];

    const ZGG_MAP = [
        "A" => "2800 kg"
    ];

    const TYPE_OF_FUEL_MAP = [
        "E" => "Electric"
    ];

    const TRACTION_BATTERY_MAP = [
        "A" => "V6+ (20 kWh)",
        "C" => "V6+ (40 kWh)"
    ];

    const CHARGING_SYSTEM_MAP = [
        "A" => "3.5 kW",
        "B" => "11 kW"
    ];

    const V_MAX_MAP = [
        "A" => "85 km/h",
        "B" => "120 km/h"
    ];

    const SEAT_MAP = [
        "1" => "1 seat + letterbox",
        "2" => "codriver seat",
        "N" => "folding codriver seat"
    ];

    const TRAILER_HITCH_MAP = [
        "X" => "trailer hitch",
        "Y" => "no trailer hitch"
    ];

    const SUPER_STRUCTURE_MAP = [
        "B" => "BOX B",
        "C" => "BOX E chilled",
        "E" => "BOX E",
        "F" => "BOX F frozen",
        "P" => "pickup",
        "T" => "tipper",
        "Y" => "without bodywork"
    ];

    const ENERGY_SUPPLY_SUPERSTRUCTURE_MAP = [
        "A" => "60 Ah",
        "B" => "80 Ah",
        "C" => "100 Ah",
        "D" => "PV + 100 Ah",
        "E" => "PV + 120 Ah",
        "F" => "FC + 100 Ah",
        "G" => "FC + 120 Ah",
        "Y" => "without"
    ];

    const DEV_STATUS_MAP = [
        "B" => "Prototype (\"B-Muster\")",
        "C" => "PVS / PreSeries (\"C-Muster\")",
        "D" => "Small Series (\"D-Muster\")",
        "S" => "Series"
    ];

    const BODY_MAP = [
        "L" => "SingleCab - Low",
        "S" => "SingleCab - Standard"
    ];

    const NUMBER_DRIVE_STEERED_AXLE_MAP = [
        "1" => "1 / FWD / 1",
        "2" => "2 / FWD / 1",
        "3" => "3 / FWD / 1",
        "4" => "2 / AWD / 1"
    ];

    const ENGINE_TYPE_MAP = [
        "A" => "AV3 (39 kW)",
        "B" => "AV3 (47 kW)",
        "C" => "AV3 (75 kW)"
    ];

    const STAGE_OF_COMPLETION_MAP = [
        "X" => "complete",
        "Y" => "incomplete"
    ];


    public function getMapOfAllConfigurationParameters()
    {

        return array(
            "steering" => self::STEERING_MAP,
            "rearWindow" => self::REAR_WINDOW_MAP,
            "airConditioning" => self::AIR_CONDITIONING_MAP,
            "passengerAirbag" => self::PASSENGER_AIRBAG_MAP,
            "keylessEntryGo" => self::KEYLESS_ENTRY_GO,
            "specialApplicationArea" => self::SPECIAL_APPLICATION_AREA_MAP,
            "radio" => self::RADIO_MAP,
            "soundGenerator" => self::SOUND_GENERATOR_MAP,
            "countryCode" => self::COUNTRY_CODE_MAP,
            "color" => self::COLOR_MAP,
            "wheeling" => self::WHEELING_MAP,
            "bodyLength" => self::LENGTY_BODY_MAP,
            "frontAxle" => self::FRONT_AXLE_MAP,
            "rearAxle" => self::REAR_AXLE_MAP,
            "zgg" => self::ZGG_MAP,
            "typeOfFuel" => self::TYPE_OF_FUEL_MAP,
            "tractionBattery" => self::TRACTION_BATTERY_MAP,
            "chargingSystem" => self::CHARGING_SYSTEM_MAP,
            "vMax" => self::V_MAX_MAP,
            "seat" => self::SEAT_MAP,
            "trailerHitch" => self::TRAILER_HITCH_MAP,
            "superStructure" => self::SUPER_STRUCTURE_MAP,
            "energySupplySuperstructure" => self::ENERGY_SUPPLY_SUPERSTRUCTURE_MAP,
            "devStatus" => self::DEV_STATUS_MAP,
            "body" => self::BODY_MAP,
            "numberDriveSteeredAxle" => self::NUMBER_DRIVE_STEERED_AXLE_MAP,
            "engineType" => self::ENGINE_TYPE_MAP,
            "stageOfCompletion" => self::STAGE_OF_COMPLETION_MAP
        );

    }

    protected $dataSrcPtr;

    protected $tableName;


    function __construct(DataSrc $dataSrcPtr, $tableName = null)
    {

        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = "vehicle_variants";

    }


    function getCOCDetails($numseats, $compartment_kind)
    {

        return $this->newQuery()
            ->where('number_of_seats', '=', $numseats)
            ->where('compartment_kind', '=', $compartment_kind)
            ->getOne('*');

    }


    function getAttributeValuesFor($attribute_name, $extraoptions = array('showObsoleteVariants' => false))
    {

        $attribute = $this->getFromName($attribute_name);

        if ($attribute) {
            $result = $this->newQuery()
                ->where('vehicle_attributes.attribute_id', '=', $attribute['attribute_id'])
                ->join('vehicle_attribute_values', 'vehicle_attributes.attribute_id=vehicle_attribute_values.attribute_id', 'INNER JOIN');

            if ($extraoptions['showObsoleteVariants'] === false)
                $result->where('vehicle_attribute_values.value', 'NOT IN', $this->obsoleteVariants);

            return $result->get('vehicle_attributes.name,vehicle_attribute_values.value,vehicle_attributes.attribute_id,vehicle_attribute_values.value_id');
        }

    }


    function GetVariantList($onlyPost)
    {

        $query = $this->newQuery()->where('windchill_variant_name', 'IS', "NOT NULL");

        if ($onlyPost) {
            $query = $query->where('is_dp', '=', 't');
        }
        $query = $query-> // ->orderBy ('prio', 'desc')
        orderBy('windchill_variant_name', 'desc');

        $list = $query->get('vehicle_variant_id,windchill_variant_name');

        $ids = array_column($list, 'vehicle_variant_id');
        $values = array_column($list, 'windchill_variant_name');
        $result = array();
        foreach ($ids as $i => $id)
            $result[$id] = $values[$i];

        return $result;

    }


    function GetVariantData($onlyPost, $columns = null)
    {

        if (is_array($columns))
            $columns = implode(',', $columns);
        else if (empty($columns))
            $columns = 'vehicle_variant_id,windchill_variant_name';

        if (strstr($columns, 'vehicle_variant_id') === false)
            $columns = "vehicle_variant_id,$columns";

        $cp = strrpos($columns, ',');
        $lastcol = substr($columns, $cp + 1);

        $query = $this->newQuery()
            ->where('windchill_variant_name', 'IS', "NOT NULL")
            ->where('windchill_variant_name', 'not ilike ', "variable%");

        if ($onlyPost)
            $query = $query->where('is_dp', '=', 't');

        $query = $query-> // ->orderBy ('prio', 'desc')
        orderBy('windchill_variant_name', 'desc');

        $list = $query->get_no_parse($columns);

        $ids = array_column($list, 'vehicle_variant_id');
        $values = array_values($list);

        return array_combine($ids, $values);

    }


    function GetVariantAmbiguousList($with_uniques = false)
    {

        $query = $this->newQuery()
            ->join('vehicle_external_mapping', 'vehicle_variant_id = variant_id', 'inner join')
            ->join('vehicle_external_names', 'vehicle_external_mapping.external_id=vehicle_external_names.external_id', 'inner join')
            ->where('windchill_variant_name', 'IS', "NOT NULL")
            ->where('is_dp', '=', 't');

        $query = $query->groupBy('vehicle_variant_id');

        $list = $query->get('vehicle_variant_id,count(vehicle_variant_id)');

        $result = array();
        foreach ($list as $row) {
            $variant = $row['vehicle_variant_id'];
            $count = $row['count'];
            if ($with_uniques || ($count > 1))
                $result[$variant] = $count;
        }

        return $result;

    }


    function getVariantTypeList()
    {

        $query = $this->newQuery()
            ->orderBy('type')
            ->get('DISTINCT type');
        return $query;

    }


    function getWindchillList()
    {

        $query = $this->newQuery()
            ->orderBy('windchill_variant_name')
            ->get('DISTINCT windchill_variant_name');
        return $query;

    }


    function sendUpdateODXCreator($variants)
    {

        /*
         * global $IS_DEBUGGING;
         *
         * $host = "10.12.54.170";
         *
         * if (!is_array($variants))
         * $variants = array ($variants);
         *
         *
         * if ( count ($variants))
         * {
         * if ($IS_DEBUGGING)
         * $fp = fopen ("/tmp/sendUpdateODXCreator.tmp", "at+");
         * else
         * $fp = fsockopen ($host, 3423, $errno, $errstr, 30);
         *
         * if (!$fp)
         * return "$errstr ($errno)";
         *
         * foreach ($variants as $var)
         * {
         * $fc = strtoupper (substr ($var,0,1));
         * if ($fc=='W')
         * {
         * fwrite ($fp, "<wcv>$var</wcv>");
         * }
         * else if (($fc>='0') && ($fc<='9'))
         * {
         * fwrite ($fp, "<vvid>$var</vvid>");
         * }
         * }
         * fclose($fp);
         * }
         * else
         * {
         * return false;
         * }
         */
        return true;

    }


    // ===============================================================================================
    function SeparateConfigurationName($vname)
    {

        $result = [];

        if (substr($vname, 0, 3) == 'E18') {
            $result['type'] = substr($vname, 0, 3);
            $result['series'] = substr($vname, 3, 2);
            $result['layout-key'] = substr($vname, 5, 4);
            $result['feature-key'] = substr($vname, 9, 1);
            $result['battery-key'] = substr($vname, 10, 1);
            return $result;
        }

        if (substr($vname, 0, 3) == 'E17') {
            $result['type'] = substr($vname, 0, 3);
            $result['series'] = substr($vname, 3, 2);
            $result['layout-key'] = substr($vname, 5, 4);
            $result['feature-key'] = substr($vname, 9, 1);
            $result['battery-key'] = 'B';
            return $result;
        }

        if (preg_match('/!D17([0-9][0-9])PVS([A-Z])([A-Z])/', $vname, $match)) {
            $result['type'] = 'D17';
            $result['series'] = $match[1];
            $result['layout-key'] = 'PVS';
            $result['feature-key'] = $match[2];
            $result['battery-key'] = $match[3];
            return $result;
        }

        if (preg_match('/^[BD]16(0[12])([A-Z]{3,4})([A-Z])/', $vname, $match)) {
            $result['type'] = substr($vname, 0, 3);
            $result['series'] = substr($vname, 3, 2);
            $result['layout-key'] = $match[2];
            $result['feature-key'] = $match[3];
            $result['battery-key'] = ' ';
            return $result;
        }

        if (preg_match('/^[BD][12][0-9]([01][0-9])([A-Z]{3,4})([A-Z])([A-G])/', $vname, $match)) {
            $result['type'] = substr($vname, 0, 3);
            $result['series'] = substr($vname, 3, 2);

            $result['layout-key'] = $match[2];
            $result['feature-key'] = $match[3];
            $result['battery-key'] = $match[4];
            return $result;
        }
        return false;

    }


    // ===============================================================================================
    function Decode_WC_Variant_Name($wcname)
    {

        $keys = $this->SeparateConfigurationName($wcname);
        if (! $keys)
            return $keys;

        $layout_key = $keys['layout-key'];
        $feature_key = $keys['feature-key'];
        $battery_key = $keys['battery-key'];
        $series_key = $keys['series'];
        $type_key = $keys['type'];
        $variant_type = $type_key . $series_key;

        $keys['layout'] = self::LAYOUT_NAMES[$layout_key];
        $keys['feature'] = self::FEATURE_NAMES[$feature_key];
        $keys['battery'] = self::BATTERY_NAMES[$battery_key];

        return $keys;

    }


    // ===============================================================================================
    function GetPartlistFromWindchillName($wcname, $getAssoc = false)
    {

        $has_camera = false;
        $has_esp = false;
        $has_letterbox = false;
        $has_notsitz = false;
        $has_leuchte = false;
        $result = [];

        if ($wcname[0] == 'E')
            return $result;

        $decoded = $this->Decode_WC_Variant_Name($wcname);

        switch ($decoded['type']) {
            case 'B14':
                return $result;

            case 'B16':
                $has_esp = ($decoded['series'] >= '03');

            case 'D16':
                $has_camera = (($decoded['layout-key'] == 'BPOS') || ($decoded['layout'] == 'EPOS'));
                $has_leuchte = ($decoded['feature-key'] == 'D');
                $has_letterbox = ($decoded['feature-key'] == 'C');
                $has_notsitz = ($decoded['feature-key'] == 'B');
                break;

            case 'D17':
                $has_esp = true;
                break;
        }

        $second_seat = 'Beifahrersitz';
        if ($has_letterbox)
            $second_seat = 'Letterbox';
        else if ($has_notsitz)
            $second_seat = 'Notsitz';

        $radio_cam = $has_camera ? 'Radio' : 'Rückfahrkameramonitor';

        if ($getAssoc) {
            $result = [
                'esp' => ($has_esp ? 'ESP' : ''),
                'signal' => ($has_leuchte ? 'Rundumleuchte' : ''),
                '2nd_seat' => $second_seat,
                'radiocam' => $radio_cam,
                'battery' => $decoded['battery']
            ];
        } else {
            if ($has_esp)
                $result[] = 'ESP';

            if ($has_leuchte)
                $result[] = 'Rundumleuchte';

            $result[] = $second_seat;

            $result[] = $radio_cam;

            $result[] = $decoded['battery'];
        }
        return $result;

    }


    function GetExternalName($variant_id, $penta_part_list = null)
    {

        $vehicle_variant_set = $this->newQuery()
            ->where('vehicle_variant_id', '=', $variant_id)
            ->getOne('is_dp, number_of_seats, type');

        if ($vehicle_variant_set && toBool($vehicle_variant_set['is_dp'])) {
            $num_seats = $vehicle_variant_set['number_of_seats'];
            if ($num_seats == 0)
                $num_seats = 1;

            $windchill_parts = $this->newQuery('variant_parts_mapping')
                ->where('variant_id', '=', $variant_id)
                ->getVals('part_id');

            if (empty($windchill_parts))
                $windchill_parts = [];

            if (isset($penta_part_list))
                $all_parts = array_merge($windchill_parts, $penta_part_list);
            else
                $all_parts = $windchill_parts;

            if (count($all_parts)) {
                $properties = $this->newQuery('parts')
                    ->where('part_id', 'in', $all_parts)
                    ->getVals('part_properties');
                $prop_text = ';;' . implode(';', $properties) . ';';
                if (strpos($prop_text, ';seats=2;'))
                    $num_seats = 2;
                if (strpos($prop_text, ';seats=1;'))
                    $num_seats = 1;
            }

            $set = $this->newQuery('vehicle_external_names')
                ->where('num_seats', '=', $num_seats)
                ->where('variant_type', '=', substr($vehicle_variant_set['type'], 0, 3))
                ->limit(1)
                ->getOne('external_id,external_name');

            if ($set)
                return $set;
        }
        return [
            'external_id' => 0,
            'external_name' => ''
        ];

    }


    public function getAllVehicleVariants()
    {

        $query = <<<SQLDOC
SELECT
    vehicle_variant_id      AS v_id,
    windchill_variant_name  AS v_name,
    penta_number_id         AS p_id,
    penta_number            AS p_name,

    default_color, 
    battery, 
    vin_method,
    penta_config_id,  
    color_id,

    array (
        SELECT part_id
        FROM variant_parts_mapping
        WHERE variant_parts_mapping.variant_id = vehicle_variant_id
    ) AS v_parts,
    array (
        SELECT part_id
        FROM penta_number_parts_mapping
        WHERE penta_number_parts_mapping.penta_number_id = penta_numbers.penta_number_id
    ) AS p_parts
FROM
    vehicle_variants
LEFT OUTER JOIN penta_numbers USING (vehicle_variant_id)
ORDER BY windchill_variant_name ASC
SQLDOC;

        $qry = $this->newQuery('vehicle_variants')->query($query);
        $allVariants = pg_fetch_all($qry);

        return $allVariants;

    }


    public function getVehicleVariantsByCombiId($combiId)
    {

        $vehicleVariantId = explode(':', $combiId)[0];
        $pentaCfgId = explode(':', $combiId)[1];

        $query = <<<SQLDOC
SELECT
    vehicle_variant_id      AS v_id,
    windchill_variant_name  AS v_name,
    penta_number_id         AS p_id,
    penta_number            AS p_name,

    default_color, 
    battery, 
    vin_method,
    penta_config_id,  
    color_id,
    variant,
    version,
    options,
    type,
    dev_status,

    array (
        SELECT part_id
        FROM variant_parts_mapping
        WHERE variant_parts_mapping.variant_id = vehicle_variant_id
    ) AS v_parts,
    array (
        SELECT part_id
        FROM penta_number_parts_mapping
        WHERE penta_number_parts_mapping.penta_number_id = penta_numbers.penta_number_id
    ) AS p_parts
FROM
    vehicle_variants
LEFT OUTER JOIN penta_numbers USING (vehicle_variant_id)
WHERE vehicle_variant_id = $vehicleVariantId AND penta_config_id = $pentaCfgId
ORDER BY windchill_variant_name ASC
SQLDOC;

        $qry = $this->newQuery('vehicle_variants')->query($query);
        $variant = pg_fetch_all($qry);

        return $variant;

    }


    public function getVehicleVariantsByVnames($vNames)
    {

        $query = <<<SQLDOC
SELECT
    vehicle_variant_id      AS v_id,
    windchill_variant_name  AS v_name,
    penta_number_id         AS p_id,
    penta_number            AS p_name,

    default_color, 
    battery, 
    vin_method,
    penta_config_id,  
    color_id,
    variant,
    version,
    options,
    type,

    array (
        SELECT part_id
        FROM variant_parts_mapping
        WHERE variant_parts_mapping.variant_id = vehicle_variant_id
    ) AS v_parts,
    array (
        SELECT part_id
        FROM penta_number_parts_mapping
        WHERE penta_number_parts_mapping.penta_number_id = penta_numbers.penta_number_id
    ) AS p_parts
FROM
    vehicle_variants
LEFT OUTER JOIN penta_numbers USING (vehicle_variant_id)
WHERE windchill_variant_name IN('$vNames')
ORDER BY windchill_variant_name ASC
SQLDOC;

        $qry = $this->newQuery('vehicle_variants')->query($query);
        $variant = pg_fetch_all($qry);

        return $variant;

    }


    public function getEcuListWithSWForConfigurationPreview($configurationID)
    {

        $query = <<<SQL
SELECT "EcuID", name, "StsPartNumber" 
FROM "Ecus"
LEFT JOIN "EcuSwVersions" ESV on "Ecus"."EcuID" = ESV."ecuID"
LEFT JOIN "ParameterEcuSwVersionMapping" PESVM on ESV."SwVersionID" = PESVM."ecuSwVersionID"
LEFT JOIN "EcuSwParameterValues" ESPV on PESVM."ecuSwParameterID" = ESPV."parameterID"
WHERE "subVehilceConfigurationID" = $configurationID
SQL;

        $qry = $this->newQuery('vehicle_variants')->query($query);
        $result = pg_fetch_all($qry);

        return $result;

    }


    public function getAllEcuListWithAppliedInformationInConfiguration($configurationID)
    {

        $query = <<<SQL
SELECT "EcuID", "name", "StsPartNumber", "subVehilceConfigurationID" 
FROM "Ecus"
LEFT JOIN "EcuSwVersions" ESV on "Ecus"."EcuID" = ESV."ecuID"
LEFT JOIN "ParameterEcuSwVersionMapping" PESVM on ESV."SwVersionID" = PESVM."ecuSwVersionID"
LEFT JOIN "EcuSwParameterValues" ESPV on PESVM."ecuSwParameterID" = ESPV."parameterID"
SQL;

        $qry = $this->newQuery('Ecus')->query($query);
        $result = pg_fetch_all($qry);

        foreach ($result as &$item) {
            $item['included'] = ($item['subVehilceConfigurationID'] == $configurationID) ? true : false;
        }

        return $result;

    }


    public function saveConfigurationToDB()
    {

        $vehicleConfigurationID = 4;
        $vehicleConfigurationKey = "test dupadupa";

        $query = <<<SQL
UPDATE "VehicleConfigurations"
SET "vehicleConfigurationKey" = 'test123'
WHERE "vehicleConfigurationID" = 4
SQL;

        // $this->newQuery('VehicleConfigurations')->query($query);

        // $qry = $this->vehicleVariantsPtr->newQuery();
        // $res = $qry->query($query);

        $query = $this->vehiclesPtr->newQuery('VehicleConfigurations')
            ->where('vehicleConfigurationID', '=', 4)
            ->update('vehicleConfigurationKey', 'dddddddddddddddddd');

        $i = 1;

    }


    public function getOptionsParameterCompounds($vehConfId)
    {

        $optionsSet = $this->newQuery()
            ->where('vehicle_variant_id', '=', $vehConfId)
            ->getVal('options');

        $result['steering'] = $this->combineKeyWithDescription($optionsSet, self::STEERING_MAP, 0);
        $result['rearWindow'] = $this->combineKeyWithDescription($optionsSet, self::REAR_WINDOW_MAP, 1);
        $result['airConditioning'] = $this->combineKeyWithDescription($optionsSet, self::AIR_CONDITIONING_MAP, 2);
        $result['passengerAirbag'] = $this->combineKeyWithDescription($optionsSet, self::PASSENGER_AIRBAG_MAP, 3);
        $result['keylessEntryGo'] = $this->combineKeyWithDescription($optionsSet, self::KEYLESS_ENTRY_GO, 4);
        $result['specialApplicationArea'] = $this->combineKeyWithDescription($optionsSet, self::SPECIAL_APPLICATION_AREA_MAP, 5);
        $result['radio'] = $this->combineKeyWithDescription($optionsSet, self::RADIO_MAP, 6);
        $result['soundGenerator'] = $this->combineKeyWithDescription($optionsSet, self::SOUND_GENERATOR_MAP, 7);
        $result['countryCode'] = $this->combineKeyWithDescription($optionsSet, self::COUNTRY_CODE_MAP, 8);
        $result['color'] = $this->combineKeyWithDescription($optionsSet, self::COLOR_MAP, 9);
        $result['wheelings'] = $this->combineKeyWithDescription($optionsSet, self::WHEELING_MAP, 10);

        return $result;

    }


    public function getVersionParameterCompounds($vehConfId)
    {

        $versionSet = $this->newQuery()
            ->where('vehicle_variant_id', '=', $vehConfId)
            ->getVal('version');

        $result['lengthBody'] = $this->combineKeyWithDescription($versionSet, self::LENGTY_BODY_MAP, 0);
        $result['frontAxle'] = $this->combineKeyWithDescription($versionSet, self::FRONT_AXLE_MAP, 1);
        $result['rearAxle'] = $this->combineKeyWithDescription($versionSet, self::REAR_AXLE_MAP, 2);
        $result['zgg'] = $this->combineKeyWithDescription($versionSet, self::ZGG_MAP, 3);
        $result['typeOfFuel'] = $this->combineKeyWithDescription($versionSet, self::TYPE_OF_FUEL_MAP, 4);
        $result['tractionBattery'] = $this->combineKeyWithDescription($versionSet, self::TRACTION_BATTERY_MAP, 5);
        $result['chargingSystem'] = $this->combineKeyWithDescription($versionSet, self::CHARGING_SYSTEM_MAP, 6);
        $result['vMax'] = $this->combineKeyWithDescription($versionSet, self::V_MAX_MAP, 7);
        $result['seats'] = $this->combineKeyWithDescription($versionSet, self::SEAT_MAP, 8);
        $result['trailerHitch'] = $this->combineKeyWithDescription($versionSet, self::TRAILER_HITCH_MAP, 9);
        $result['superstructures'] = $this->combineKeyWithDescription($versionSet, self::SUPER_STRUCTURE_MAP, 10);
        $result['energySupplySuperstructure'] = $this->combineKeyWithDescription($versionSet, self::ENERGY_SUPPLY_SUPERSTRUCTURE_MAP, 11);

        return $result;

    }


    public function getDevStatusCompound($devStatusLetter)
    {

        $devStatusMap = array(
            "B" => "Prototype (\"B-Muster\")",
            "C" => "PVS / PreSeries (\"C-Muster\")",
            "D" => "Small Series (\"D-Muster\")",
            "S" => "Series"
        );

        return array(
            $devStatusLetter => $devStatusMap[$devStatusLetter]
        );

    }


    function combineKeyWithDescription($parameterSet, $valuesMap, $letterPosition)
    {

        $parameterLetter = substr($parameterSet, $letterPosition, 1);
        $result = array(
            $parameterLetter => $valuesMap[$parameterLetter]
        );
        return $result;

    }


    public function getVariantParameterCompounds($vehConfId)
    {

        $variantSet = $this->newQuery()
            ->where('vehicle_variant_id', '=', $vehConfId)
            ->getVal('variant');

        $result['body'] = $this->combineKeyWithDescription($variantSet, self::BODY_MAP, 0);
        $result['numberDriveSteeredAxle'] = $this->combineKeyWithDescription($variantSet, self::NUMBER_DRIVE_STEERED_AXLE_MAP, 1);
        $result['engineType'] = $this->combineKeyWithDescription($variantSet, self::ENGINE_TYPE_MAP, 2);
        $result['stageOfCompletion'] = $this->combineKeyWithDescription($variantSet, self::STAGE_OF_COMPLETION_MAP, 3);

        return $result;

    }

}


