<?php
/**
 * actions/AClass_TableBase.php
 *
 * Basisklasse mit den fuer den Sales wichtigen Grundfunktionen
 * Action-Patch aus dem Verzeichnis {http-root}/actions
 *
 * @author Lothar Jürgens
 */
define('TABLE_VEHICLES_UNDEFINED', '-');
define('TABLE_VEHICLES_CVS_UPDATE', 'c');
define('TABLE_VEHICLES_RANGESELECT', 'r');
define('TABLE_VEHICLES_TABLESELECT', 't');

define('INPUT_STATE_SELECT', 0);
define('INPUT_STATE_EDIT_1', 1);
define('INPUT_STATE_EDIT_2', 2);
define('INPUT_STATE_VALIDATE', 2);
define('INPUT_STATE_SAVE_PRINT', 4);
define('INPUT_STATE_DELETE_1', 5);
define('INPUT_STATE_DELETED', 6);

class AClass_TableBase extends AClass_FormBase
{

    // ==============================================================================================
    public $S_numRowsPerPage;

    public $S_currentPage;

    public $availablePageSizes;

    protected $colConfigDefault;

    protected $colConfig;

    protected $rowCount = 0;


    /**
     * Konstruktor
     *
     * @param PageController $pageController
     */
    function __construct()
    {

        parent::__construct();

        if ($this->controller) {
            $this->vehiclesPtr = $this->controller->GetObject("vehicles");
            $this->vehicleVariantsPtr = $this->controller->GetObject("vehicleVariants");
            $this->vehiclesSalesPtr = $this->controller->GetObject("vehiclesSales");
        }

        $this->S_where = & InitSessionVar($this->S_actionVar['where'], []);
        $this->S_pager = & InitSessionVar($this->S_actionVar['pager'], []);
        $this->S_currentPage = & InitSessionVar($this->S_pager['currentPage'], 1);
        $this->S_numRowsPerPage = & InitSessionVar($this->S_pager['numRowsPerPage'], 10);

        $this->S_currentVehicles = & InitSessionVar($this->S_data['currentVehicles'], []);
        $this->S_selectedVehicles = & InitSessionVar($this->S_data['selectedVehicles'], []);
        $this->S_changedColumns = & InitSessionVar($this->S_data['changedColumns'], []);

        $this->execMode = & InitSessionVar($this->S_data['execMode'], TABLE_VEHICLES_UNDEFINED);
        $this->InputState = & InitSessionVar($this->S_data['InputState'], INPUT_STATE_SELECT);

        $this->availablePageSizes = [
            5,
            10,
            20,
            50,
            100,
            200
        ];

        $this->colConfig = [];

        $this->ShowAllScrollable = false;
        $this->tableInfo = [];
        $this->onGet = [];
        $this->errorData = [];
        $this->error = 0;

        $this->btnLabels = [
            'undo' => "Auswahl zurücksetzen",
            'back' => "&lt;&lt; Zurück zur Auswahl",
            'rstfltr' => "Filter zurücksetzen"
        ];

        $this->btnEnabled = [
            'undo' => 'anySelected',
            'rstfltr' => 'anyFilter'
        ];

        if (isset($_REQUEST['execmode'])) {
            switch ($_REQUEST['execmode']) {
                case 'table':
                    $this->execMode = TABLE_VEHICLES_TABLESELECT;
                    break;
            }
        }

        if (($this->execMode <= TABLE_VEHICLES_CVS_UPDATE) && ($this->InputState < INPUT_STATE_EDIT_1))
            $this->csvTool = new AClass_CsvTool_Sales('csv_update', true);
        else
            $this->csvTool = null;

    }


    // ==============================================================================================
    function DefineColConfig()
    {

        $this->colConfigDefault = [
            'search' => 'InputText',
            'attr search' => [
                'onChange' => "SubmitForm('go_first')",
                'size' => 20
            ],

            'enable' => COL_VISIBLE
        ];

        $this->colConfig['selected'] = [
            'enable' => COL_INVISIBLE,
            'header' => '&nbsp;',
            'html' => 'Checkbox',
            'attr th' => [
                'style' => 'width:32px;padding:4px;'
            ], //
            'attr' => [
                'onClick' => "EnableButtons (true)"
            ],
            'search' => 'Checkbox',
            'attr search' => [
                'onClick' => "SubmitForm('go_first')"
            ],
            'size' => 35
        ];

        $this->colConfig['selected']['attr'] = [
            'onChange' => "EnableElements (['id_btn_add','ad_btn_change'], true);OnSelectRow(this);"
        ];

        $this->colConfig['move'] = [
            'enable' => COL_INVISIBLE,
            'header' => '&nbsp;',
            'html' => 'UpDown',
            'attr td' => [
                'class' => 'dragHandle'
            ],
            'attr th' => [
                'style' => 'width:32px;padding:4px;'
            ], //
            'search' => '',
            'size' => 35
        ];

        $this->colConfig['vehicle_id'] = [
            'enable' => COL_INVISIBLE,
            'header' => 'ID',
            'db' => [
                'table' => 'vehicles',
                'column' => 'vehicle_id',
                'search' => 'in'
            ],
            'attr td' => [
                'style' => 'width:20px;'
            ],
            'attr th' => [
                'style' => 'width:20px;'
            ],
            'attr search' => [
                'size' => 5
            ],
            'size' => 10
        ];

        $this->colConfig['vin'] = [
            'header' => 'VIN',
            'db' => [
                'table' => 'vehicles',
                'column' => 'vin',
                'search' => 'ilike'
            ],
            'attr search' => [
                'placeholder' => 'Muster (? *) oder VIN-Liste '
            ],
            'size' => 170,
            'max numchar' => 17
        ];
        $this->colConfig['akz'] = [
            'enable' => COL_NOT_USED,
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
            'enable' => COL_NOT_USED,
            'header' => 'IKZ',
            'db' => [
                'table' => 'vehicles',
                'column' => 'ikz',
                'search' => 'ilike'
            ],
            'max numchar' => 8,
            'size' => 170
        ];
        $this->colConfig['penta_kennwort'] = [
            'header' => 'Penta Kennwort',
            'db' => [
                'table' => 'vehicles',
                'column' => 'penta_kennwort',
                'search' => 'ilike'
            ],
            'attr search' => [
                'size' => 16
            ],
            'numchar' => 16,
            'size' => 140
        ];

        $this->colConfig['penta_id'] = [
            'header' => 'Penta Artikel',
            'db' => [
                'table' => 'penta_numbers',
                'column' => 'penta_number',
                'search' => 'ilike'
            ],
            'size' => 170,
            'numchar' => 20,
            'enable' => COL_INVISIBLE
            // '.lookup' => [
            //     0 => 'kein Penta Artikel'
            // ] + $this->vehiclesPtr->newQuery('penta_numbers')->get('penta_number_id=>penta_number')
        ];

        $this->colConfig['vehicle_configuration_id'] = [
            'header' => 'Subkonfiguration',
            'db' => [
                'table' => 'sub_vehicle_configurations',
                'column' => 'sub_vehicle_configuration_name',
                'search' => 'ilike'
            ],
            'size' => 170,
            'numchar' => 20,
            'enable' => COL_VISIBLE
        ];

        $this->colConfig['tsnumber'] = [
            'enable' => COL_NOT_USED,
            'header' => 'TS Nummer',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'tsnumber',
                'search' => 'ilike'
            ],
            'size' => 170,
            'numchar' => 20
        ];
        $this->colConfig['datum_produktion'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Datum<br>Fertigung',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'production_date'
            ],
            'html' => 'ShowAsDate',
            'numchar' => 12
        ];
        $this->colConfig['datum_auslieferung'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Datum<br>Auslieferung',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'delivery_date'
            ],
            'html' => 'ShowAsDate',
            'numchar' => 12
        ];
        $this->colConfig['woche_auslieferung'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Woche<br>Ausl.',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'delivery_week'
            ],
            'numchar' => 5
        ];
        $this->colConfig['coc'] = [
            'enable' => COL_NOT_USED,
            'header' => 'CoC Nr.',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'coc',
                'search' => 'ilike'
            ],
            'numchar' => 10
        ];
        $this->colConfig['vorhaben'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Vorhaben Nr.',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'vorhaben',
                'search' => 'ilike'
            ],
            'numchar' => 20
        ];
        $this->colConfig['datum_zuordnung'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Zugeordnet Seit',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'added_timestamp',
                'search' => 'ilike'
            ],
            'html' => 'ShowAsDate',
            'numchar' => 12
        ];
        $this->colConfig['comments'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Kommentare',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'comments',
                'search' => 'ilike'
            ],
            'numchar' => 12
        ];

        $this->colConfig['variant_id'] = [
            'db' => [
                'table' => 'vehicle_variants',
                'column' => 'vehicle_variant_id',
                'search' => '='
            ],
            'enable' => COL_INVISIBLE
        ];

        $this->colConfig['windchill'] = [
            'header' => 'Konfiguration',
            'db' => [
                'table' => 'vehicle_variants',
                'column' => 'windchill_variant_name'
            ],
            'size' => 170,
            'numchar' => 20,
            'enable' => COL_NOT_USED
        ];

        /*
         * $this->colConfig ['penta_number'] =
         * [
         * 'header' => 'Penta Artikel',
         * 'search' => '',
         * 'db' => ['table'=>'vehicle_variants', 'column'=>'penta_format_string'],
         * 'enable' => COL_VISIBLE,
         * ];
         *
         * $this->colConfig ['esp_func'] =
         * [
         * 'db' => ['table'=>'vehicle_variants', 'column'=>'esp_func'],
         * 'enable' => COL_INVISIBLE,
         * ];
         */

        $this->colConfig['is_dp'] = [
            'header' => 'Postfahrzeug',
            'db' => [
                'table' => 'vehicle_variants',
                'column' => 'is_dp'
            ],
            'size' => 170,
            'numchar' => 20,
            'enable' => COL_INVISIBLE
        ];

        $this->colConfig['color_id'] = [
            'enable' => COL_INVISIBLE,
            'db' => [
                'table' => 'vehicles',
                'column' => 'color_id'
            ],
            'header' => 'Farbe',
            'size' => '100',
            'search' => 'select',
            '.lookup' => [
                0 => '-- alle -- '
            ] + $this->allColors
        ];

        $joker = [
            'sts' => '-- Streetscooter --',
            'all' => '-- alle --',
            'edit' => '-- anderer Ort --'
        ];

        $this->colConfig['depot'] = [
            'enable' => COL_VISIBLE,
            'header' => 'Standort',
            'db' => [
                'table' => 'vehicles',
                'column' => 'depot_id'
            ],
            'search' => 'SelectWithEditOption',
            'search_init' => 'sts',
            '.lookup' => $joker + $this->prodLocationWithStsPool
            // '.call' =>
        ];

        $this->colConfig['herstellung'] = [
            'enable' => COL_VISIBLE,
            'header' => 'Werk',
            'db' => [
                'table' => 'vehicles_sales',
                'column' => 'production_location'
            ],
            'search' => 'SelectWithEditOption',
            '.lookup' => [
                'all' => '-- alle --'
            ] + $this->prodLocation
        ];

        $this->colConfig['parkplatz'] = [
            'enable' => COL_VISIBLE,
            'header' => 'Parkplatz',
            'search' => 'Select',
            'db' => [
                'table' => 'park_lines',
                'column' => 'map_id'
            ],
            '.lookup' => [
                'all' => ' -- alle --'
            ] + $this->parkplaetze,
            'attr td' => [
                'style' => 'min-width: 60px;'
            ]
        ];

        $this->colConfig['park_reihe'] = [
            'enable' => COL_INVISIBLE,
            // 'header' => 'Reihe',
            'db' => [
                'table' => 'park_lines',
                'column' => 'ident'
            ] // ['table'=>'vehicles', 'column'=>'park_id', 'show'=>'park_lines.ident'],
        ];

        $this->colConfig['park_nummer'] = [
            'enable' => COL_INVISIBLE,
            'db' => [
                'table' => 'vehicles',
                'column' => 'park_position'
            ]
        ];

        $this->colConfig['park_position'] = [
            'enable' => COL_VISIBLE,
            'header' => 'Position',
            'onGet' => 'onGet_ParkPosition',
            'attr td' => [
                'style' => 'text-align:center;'
            ]
        ];

        $this->colConfig['fz_begleitschein'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Fzg. Begleitschein',
            'html' => 'FzBegleitschein',
            'search' => ''
        ];

        $this->colConfig['uebergabeprotokoll'] = [
            'enable' => COL_NOT_USED,
            'header' => 'Übergabeprotokoll',
            'html' => 'Uebergabeprotokoll',
            'search' => ''
        ];

    }


    // ==============================================================================================
    function ProcessColConfig()
    {

        foreach ($this->colConfig as $col => &$set) {
            if (isset($set['onGet'])) {
                if (method_exists($this, $set['onGet']))
                    $this->onGet[$col] = $set['onGet'];
            }
        }

    }


    // ==============================================================================================
    function Init()
    {

        parent::Init();
        $this->InitConstants();
        $this->DefineColConfig();
        $this->ProcessColConfig();

    }


    // ==============================================================================================
    function InitConstants()
    {

        $this->prodLocationWithStsPool = $this->vehiclesPtr->newQuery('divisions')
            ->join('depots', 'using(division_id)', 'inner join')
                                // ->where('production_location', '=', 't')
                                ->orderBy('production_location','DESC')
                                ->orderBy('depots.name','ASC')
                                ->get('depot_id=>depots.name');

        $this->prodLocation = $this->prodLocationWithStsPool;
        $this->sts_pool = array_search('Sts_Pool', $this->prodLocation);
        if ($this->sts_pool)
            unset($this->prodLocation[$this->sts_pool]);

        $this->allColorSets = $this->vehiclesPtr->newQuery('colors')
            ->orderBy('color_id')
            ->get('*', 'color_id');
        $this->allColors = reduce_assoc($this->allColorSets, 'name');
        $this->allPartsData = $this->vehiclesPtr->newQuery('parts')
            ->where('visible_sales', '=', 't')
            ->orderBy('part_id')
            ->get('*', 'part_id');
        $this->allParts = reduce_assoc($this->allPartsData, 'name');
        $this->partGroups = $this->vehiclesPtr->newQuery('part_groups')->get('*', 'group_id');
        $this->part2group = reduce_assoc($this->allPartsData, 'group_id');
        $this->excl_parts = [];
        foreach ($this->part2group as $part_id => $group) {
            if (! isset($this->excl_parts[$group]))
                $this->excl_parts[$group] = [];

            if (toBool($this->partGroups[$group]['allow_multi']))
                continue;

            $this->excl_parts[$group][] = $part_id;
        }

        $this->vehicleVariants = $this->vehicleVariantsPtr->GetVariantData(false, 'vehicle_variant_id,array (select part_id from variant_parts_mapping where variant_parts_mapping.variant_id = vehicle_variants.vehicle_variant_id order by part_id) as parts,default_color,windchill_variant_name');
        $this->allPentaVariants = $this->vehiclesSalesPtr->GetVariantPentaNumberList(false);

        $this->parkplaetze = safe_array($this->vehiclesPtr->newQuery('gps_maps')
            ->where('is_parking_location', '=', 't')
            ->orderBy('name')
            ->get('map_id=>name'));
        
        // $this->vehicleConfigurations = $this->ladeLeitWartePtr->newQuery('vehicle_configurations')
        //     ->get('vehicle_configuration_id=>vehicle_configuration_key');
        $this->vehicleConfigurations = $this->vehiclesPtr->newQuery('sub_vehicle_configurations')
            ->join('penta_variants', 'using(sub_vehicle_configuration_id)', 'left join')
            ->join('vehicle_configurations', 'using(vehicle_configuration_id)', 'left join')
            ->where('sub_vehicle_configuration_name', 'not like', 'VariableKonfiguration%')
            ->orderBy('sub_vehicle_configuration_name','DESC')
            ->get('old_vehicle_variant_id,vehicle_configuration_id,sub_vehicle_configuration_name,short_production_description,sub_vehicle_configuration_id,configuration_color_id,penta_variant_id,old_penta_number_id');
    }


    // ==============================================================================================
    function SetState($state)
    {

        $this->InputState = $state;

    }


    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    function InitState()
    {

        switch ($this->InputState) {
            case INPUT_STATE_SELECT:
                $this->colConfig['selected']['enable'] = COL_VISIBLE;
                break;

            case INPUT_STATE_SAVE_PRINT:
                $this->colConfig['fz_begleitschein']['enable'] = COL_VISIBLE;
                $this->colConfig['uebergabeprotokoll']['enable'] = COL_VISIBLE;
                $this->colConfig['abholschein']['enable'] = COL_VISIBLE;
                break;
        }

    }


    // ==============================================================================================
    function merge_defaults(&$defaults, $update)
    {

        foreach ($update as $key => $value) {
            if (($key == 'style') && (substr($value, 0, 1) == '+')) {
                $value = safe_val($defaults, 'style', "") . substr($value, 1);
            }

            if ($value == '') {
                unset($defaults[$key]);
            } else {
                $defaults[$key] = $value;
            }
        }

    }


    // ==============================================================================================
    function GetParklines()
    {

        $qry = $this->vehiclesPtr->newQuery('park_lines');
        if (isset($this->dbWhere['park_lines.map_id']))
            $qry = $qry->where('map_id', '=', $this->dbWhere['park_lines.map_id']);

        return $qry->orderBy('ident')->get('park_lines=>ident');

    }


    // ==============================================================================================
    function MakeHtmlAttributes($coldef, $name = "attr")
    {

        $resultStr = "";

        if (isset($this->colConfigDefault[$name]))
            $attributes = $this->colConfigDefault[$name];
        else
            $attributes = [];

        if (isset($coldef[$name]))
            $this->merge_defaults($attributes, $coldef[$name]);

        if (count($attributes)) {
            foreach ($attributes as $type => $value) {
                $resultStr .= " $type=\"$value\"";
            }
        }
        return $resultStr;

    }


    // ==============================================================================================
    function getVisibleCols()
    {

        $result = [];
        $default = safe_val($this->colConfigDefault, 'enable', COL_VISIBLE);

        foreach ($this->colConfig as $col => $def) {
            if (safe_val($def, 'enable', $default) == COL_VISIBLE) {
                $result[] = $col;
            }
        }
        return $result;

    }


    // ==============================================================================================
    function GetColumnDbName($col)
    {

        if (isset($this->colConfig[$col]['db'])) {
            return $this->colConfig[$col]['db']['table'] . '.' . $this->colConfig[$col]['db']['column'];
        }
        return false;

    }


    // ==============================================================================================
    function OnWhere(&$where)
    {

        switch ($where['vehicles.depot_id']) {
            case 'sts':
                $where['vehicles.depot_id'] = array_keys($this->prodLocationWithStsPool);
                break;

            case 'edit':
                $where['depots.name'] = $_REQUEST['search_text']['depot'];

            case 'all':
                unset($where['vehicles.depot_id']);
                // $this->selectCols['display_depot'] = 'depots.name';
                // unset ($this->colConfig['depot']['.lookup']);
                break;
        }

        if ($where['vehicles_sales.production_location'] == 'all')
            unset($where['vehicles_sales.production_location']);

        if (! $where['vehicles.color_id'])
            unset($where['vehicles.color_id']);

        if ($where['park_lines.map_id'] == 'all')
            unset($where['park_lines.map_id']);

    }


    // ==============================================================================================
    function On_State_Select()
    {

        SafeUpdateFromRequest($this->S_numRowsPerPage, 'page_size');

        $this->S_where = $this->GetWhereFromRequest();
        $this->dbWhere = $this->UpdateDbKeyNames($this->S_where);
        $this->selectCols = $this->getSelectCols();
        $limit = max(1, $this->S_numRowsPerPage);

        $this->OnWhere($this->dbWhere);

        $this->numResult = $this->vehiclesPtr->getSimpleCount($this->dbWhere, $this->selectCols);
        $this->numPages = ceil($this->numResult / $limit);

        $this->ExecutePager();

        $offset = (max(1, $this->S_currentPage) - 1) * $limit;

        $this->S_currentVehicles = $this->getVehicleData($this->dbWhere, $limit, $offset, 'vehicles.vehicle_id desc', $this->selectCols);

        $this->UpdateCurrentVehicles();

    }


    // ==============================================================================================
    function On_State_Save_Print()
    {

        $this->dbWhere = [
            'vehicle_id' => $this->S_changedIds
        ];
        $this->selectCols = $this->getSelectCols();
        $this->selectCols['display_depot'] = 'depots.name';
        $this->S_currentVehicles = $this->getVehicleData($this->dbWhere, 0, 0, 'vehicles.vehicle_id desc', $this->selectCols);
        $this->UpdateCurrentVehicles();
        $this->S_selectedVehicles = &$this->S_currentVehicles;

    }


    // ==============================================================================================
    function UpdateDbKeyNames($where)
    {

        $result = [];
        foreach ($where as $cc => &$value) {
            $dbkey = $this->GetColumnDbName($cc);
            $result[$dbkey] = &$value;
        }
        return $result;

    }


    // ==============================================================================================
    /*
     * getUpdateCols returns a set of column names grouped by table name
     */
    function getUpdateCols($columns = null)
    {

        $result = [];
        $default = safe_val($this->colConfigDefault, 'enable', COL_VISIBLE);

        if (! $columns)
            $columns = array_keys($this->colConfig);

        foreach ($columns as $col) {
            if (isset($this->colConfig[$col])) {
                $def = &$this->colConfig[$col];
                if (isset($def['db']) && (safe_val($def, 'enable', $default) > COL_NOT_USED)) {
                    $db = &$def['db'];
                    if (! $db['readonly'] && isset($db['column']) && isset($db['table'])) {
                        $table = $db['table'];
                        $dbcol = $db['column'];

                        $result[$table][$col] = $dbcol;
                    }
                }
            }
        }
        return $result;

    }


    // ==============================================================================================
    function getSelectCols()
    {

        $result = [];
        $default = safe_val($this->colConfigDefault, 'enable', COL_VISIBLE);

        foreach ($this->colConfig as $col => $def) {
            if (isset($def['db']) && (safe_val($def, 'enable', $default) > COL_NOT_USED)) {
                $db = &$def['db'];
                // $as_alias = ($addAlias) ? " as $col" : "";
                $dbCol = "";

                if (isset($db['column']) && isset($db['table'])) {
                    $result[$col] = sprintf("%s.%s", $db['table'], $db['column']);
                } else if (isset($db['subquery']))
                    $result[$col] = $db['subquery'];
            }
        }
        return $result;

    }


    // ==============================================================================================
    function getVehicleData($whereUser, $limit = 0, $offset = 0, $order = '', $selectCols = false)
    {

        if ($selectCols === false)
            $selectCols = $this->getSelectCols();

        return $this->vehiclesPtr->getSimpleSearch($whereUser, $selectCols, $limit, $offset, $order);

    }


    // ==============================================================================================
    function UpdateCurrentVehicles()
    {

        if (count($this->onGet)) {
            foreach ($this->S_currentVehicles as &$vehicle) {
                $id = $vehicle['vehicle_id'];
                $vehicle['selected'] = isset($this->S_selectedVehicles[$id]) ? "On" : "";

                foreach ($this->onGet as $col => $method)
                    $this->$method($vehicle, $col);
            }
        }

    }


    // ==============================================================================================
    function onGet_ParkPosition(&$data, $col)
    {

        $data['park_position'] = "{$data['park_reihe']} {$data['park_nummer']}";

    }


    // ==============================================================================================
    function GetWhereFromRequest()
    {

        global $_REQUEST;

        $where = array();
        $delims = [
            ';',
            ',',
            ' '
        ];

        if (isset($_REQUEST['search']['selected']) && count($this->S_selectedVehicles)) {
            return [
                'vehicle_id' => array_keys($this->S_selectedVehicles)
            ];
        }

        if (isset($_REQUEST['search'])) {
            foreach ($_REQUEST['search'] as $col => $value) {
                if ($col == 'selected')
                    continue;

                if ($col == 'vin') {
                    $delim = '';
                    $such = str_replace([
                        '"',
                        "'",
                        ' '
                    ], [
                        '',
                        '',
                        ''
                    ], $value);
                    if (strlen($such)) {
                        foreach ($delims as $d) {
                            if (strpos($such, $d) > 0) {
                                $delim = $d;
                                break;
                            }
                        }

                        if (empty($delim))
                            $where[$col] = $such;
                        else
                            $where[$col] = str_getcsv($such, $delim);
                    }
                } else {
                    $value = preg_replace('/^["](.*)["]$/', '\1', $value);
                    if ($value != "")
                        $where[$col] = str_replace([
                            "'",
                            ";"
                        ], [
                            '',
                            ''
                        ], $value);
                }
            }
        } else if ($this->inputState == INPUT_STATE_SELECT) {
            foreach ($this->colConfig as $col => &$coldef)
                if (($coldef['enable'] > COL_NOT_USED) && isset($coldef['search_init']))
                    $where[$col] = $coldef['search_init'];
        }

        return $where;

    }


    // ==============================================================================================
    function MakeOptionArray($dbresult)
    {

        return str_getcsv(substr($dbresult, 1, - 1));

    }


    // ==============================================================================================
    function MakeOptionString($ids)
    {

        $ret = "";
        foreach ($ids as $part_id) {
            $ret .= ", " . $this->allParts[$part_id];
        }
        return substr($ret, 2);

    }


    // ==============================================================================================
    function GetDefaultNewQuery($bAddVariants = true, $bAddColor = true, $bAddSales = false, $addMaps = false)
    {

        $query = $this->vehiclesPtr->newQuery();
        if ($bAddVariants)
            $query = $query->join('vehicle_variants', 'vehicle_variants.vehicle_variant_id = vehicles.vehicle_variant', 'inner join');
        if ($bAddColor)
            $query = $query->join('colors', 'colors.color_id = vehicles.color_id', 'inner join');
        if ($bAddSales)
            $query = $query->join('vehicles_sales', 'vehicles_sales.vehicle_id = vehicles.vehicle_id', 'inner join');
        if ($addMaps)
            $query = $query->join('park_lines', 'using(park_id)', 'left join');
        return $query;

    }


    // ==============================================================================================
    function GetHtml_VehiclesTableHead(array $displayCols)
    {

        $retString = "  <tr>\n"; // <tr class=\"tableheader\">\n";

        foreach ($displayCols as $col) {
            $def = &$this->colConfig[$col];

            if (isset($def['header']))
                $head = $def['header'];
            else
                $head = $col;

            $attr_th = $this->MakeHtmlAttributes($def, 'attr th');

            $retString .= "    <th $attr_th>$head</th>\n";
        }

        if ($this->ShowAllScrollable) {
            $retString .= '<th style="width:8px;padding:0px;">&nbsp;</th>';
        }
        $retString .= "  </tr>\n";
        return $retString;

    }


    // ==============================================================================================
    function GetHtmlSearch_InputText($col, $value, $attr = "")
    {

        if (is_array($value))
            $value = implode(',', $value);

        return "<input type=\"text\" name=\"search[$col]\" value=\"$value\" $attr>";

    }


    // ==============================================================================================
    function GetHtmlSearch_InputNumber($col, $value, $attr = "")
    {

        return "<input type=\"number\" name=\"search[$col]\" value=\"$value\" $attr>";

    }


    // ==============================================================================================
    function GetHtmlSearch_Checkbox($col, $value, $attr = "")
    {

        $checked = $value ? " checked" : "";
        return "<input type=\"checkbox\" name=\"search[$col]\"$checked $attr>";

    }


    // ==============================================================================================
    function GetLookupData($col)
    {

        if (isset($this->S_currentVehicles['.lookup'][$col]))
            return $this->S_currentVehicles['.lookup'][$col];

        if (isset($this->colConfig[$col]['.call'])) {
            $method = $this->colConfig[$col]['.call'];
            if (method_exists($this, $method)) {
                $this->S_currentVehicles['.lookup'][$col] = call_user_method($method, $this);
                return $this->S_currentVehicles['.lookup'][$col];
            }
            return null;
        }
        if (isset($this->colConfig[$col]['.lookup']))
            return $this->colConfig[$col]['.lookup'];

        return null;

    }


    // ==============================================================================================
    function GetHtmlSearch_SelectWithEditOption($col, $value, $attr = "")
    {

        $options = "";
        if ($data = $this->GetLookupData($col)) {
            $edit_visible = ($value == 'edit' ? 'visible' : 'hidden');
            $txtval = $_REQUEST['search_text'][$col];

            foreach ($data as $id => $text) {
                $sel = ("$value" == "$id") ? " selected" : "";
                $options .= "<option value=\"$id\"$sel>$text</option>";
            }

            return <<<HERE
<div class="positioneer" style="width:175px;">
  <select name="search[$col]" id="id_search_$col" style="width:175px;" onChange="OnComboBox(this)">
    {$options}
  </select>
  <input type="text" name="search_text[$col]" id="id_search_{$col}_text" value="$txtval" style="position:absolute;top:5px;left:4px;width:128px;visibility:$edit_visible;" onChange="SubmitAndGoto(1);">
</div>
HERE;
        }
        return "";

    }


    // ==============================================================================================
    function GetHtmlSearch_Select($col, $value, $attr = "")
    {

        $selected = $value ? " checked" : "";

        if ($data = $this->GetLookupData($col)) {
            $result = "<select name=\"search[$col]\" onChange=\"SubmitAndGoto(1);\">";
            $sel = ($value == "") ? " selected" : "";

            foreach ($data as $id => $text) {
                $sel = ("$value" == "$id") ? " selected" : "";
                $result .= "<option value=\"$id\"$sel>$text</option>";
            }
            $result .= "</select>";
            return $result;
        }
        return "";

    }


    // ==============================================================================================
    function GetHtml_VehiclesSearchRow(array $displayCols)
    {

        $anySearch = false;
        $retString = "  <tr>\n";
        $def_search = safe_val($this->colConfigDefault, 'search', '');
        $def_attr_td = safe_val($this->colConfigDefault, 'attr search td', '');
        $def_attr_search = safe_val($this->colConfigDefault, 'attr search', '');

        foreach ($displayCols as $col) {
            $coldef = &$this->colConfig[$col];
            $func = safe_val($coldef, 'search', $def_search);

            if (empty($coldef['attr search']['id']))
                $coldef['attr search']['id'] = "id_select_$col";

            $attr_td = $this->MakeHtmlAttributes($coldef, 'attr search td');
            $attr_search = $this->MakeHtmlAttributes($coldef, 'attr search');

            if (($func != "") && (isset($coldef['db']) || ($col == 'selected'))) {
                $anySearch = true;
                $mapped = 'GetHtmlSearch_' . $func;
                if ($col == 'selected')
                    $value = safe_val($_REQUEST['search'], $col, '');
                else
                    $value = safe_val($this->S_where, $col, '');

                if (method_exists($this, $mapped)) {
                    $retString .= "    <td $attr_td>" . $this->$mapped($col, $value, $attr_search) . "</td>\n";
                } else {
                    $retString .= "    <td $attr_td></td>\n";
                }
            } else {
                $retString .= "<td>&nbsp;</td>\n";
            }
        }

        if ($anySearch)
            return $retString . "  </tr>\n";

        return "";

    }


    // ==============================================================================================

    /**
     * GetHTML_VehiclesTableRow Formats a SQL result into a HTML segment
     *
     * @param array $dataRow
     *            associative array {sql-columnName} => {vehicle data}
     * @param array $colsEditable
     *            (optional) list of sql column names, which will be editable
     * @return string the HTML formated table row
     */
    function GetHtml_VehiclesTableRow(array $displayCols, array $vehicleData)
    {

        $id = $vehicleData['vehicle_id'];

        // $def_attr_tr = safe_val ($this->colConfigDefault, 'attr tr', '');
        // $def_attr_td = safe_val ($this->colConfigDefault, 'attr td', '');
        $def_html = safe_val($this->colConfigDefault, 'html', 'Default');
        $this->rowCount ++;
        // $retString = " <tr $def_attr_tr>\n";
        $retString = "  <tr id=\"row-{$this->rowCount}\" data-vid=\"{$vehicleData['vehicle_id']}\">\n";

        foreach ($displayCols as $col) {
            $def = &$this->colConfig[$col];

            $content = $vehicleData[$col];
            $lookup = $this->GetLookupData($col);
            if ($lookup)
                $content = $lookup[$content];

            $attr_td = $this->MakeHtmlAttributes($def, 'attr td'); // safe_val ($def, 'attr td', "");

            $retString .= "    <td $attr_td>";
            $mapped = strtolower(safe_val($def, 'html', $def_html));

            if ($mapped != "") {
                $attr = $this->MakeHtmlAttributes($def, 'attr');
                // $attr = safe_val ($def, 'attr', []);
                $mapped = 'GetHtmlElement_' . $mapped;

                if (method_exists($this, $mapped)) {
                    $retString .= $this->$mapped($vehicleData, $content, $col, $id, $attr);
                } else {
                    $this->LogErrorFirstOnly(SELF::ERROR_MAPPED_METHOD_NOT_FOUND, $mapped);
                }
            } else {
                $retString .= $this->GetHtmlElement_default($vehicleData, $content, $col, $id, $attr);
            }

            $retString .= "</td>\n";
        }

        $retString .= "  </tr>\n";
        return $retString;

    }


    // ==============================================================================================
    function GetHtmlElement_FzBegleitschein($vehicleData, $content, $col, $id, $attr)
    {

        return <<<HEREDOC
<a href="{$_SERVER['PHP_SELF']}?action=fz_begleitschein&mode=view&vlist=$id" target="_blank">[Drucken]</a>,
<a href="{$_SERVER['PHP_SELF']}?action=fz_begleitschein&mode=download&vlist=$id">[Speichern]</a>
HEREDOC;

    }


    // ==============================================================================================
    function GetHtmlElement_Uebergabeprotokoll($vehicleData, $content, $col, $id, $attr)
    {

        return <<<HEREDOC
<a href="{$_SERVER['PHP_SELF']}?action=uebergabeprotokoll&mode=view&vlist=$id" target="_blank">[Drucken]</a>,
<a href="{$_SERVER['PHP_SELF']}?action=uebergabeprotokoll&mode=download&vlist=$id">[Speichern]</a>
HEREDOC;

    }


    // ==============================================================================================
    function GetHtml_Download_Uebergabeprotokoll()
    {

        $vlist = implode(',', $this->S_changedIds);

        return <<<HEREDOC
      <span>Übergabeprotokoll</span>
      <span>
        <a href="{$_SERVER['PHP_SELF']}?action=uebergabeprotokoll&mode=view&vlist=$vlist" target="_blank">[Drucken]</a>&nbsp;
        <a href="{$_SERVER['PHP_SELF']}?action=uebergabeprotokoll&mode=download&vlist=$vlist">[Speichern]</a>
      </span>
HEREDOC;

    }


    // ==============================================================================================
    function GetHtmlElement_UpDown($vehicleData, $content, $col, $id, $attr)
    {

        return "<img src=\"/images/symbols/updown2.gif\">";

    }


    // ==============================================================================================
    function GetHtml_Download_Abholschein()
    {

        $vlist = implode(',', $this->S_changedIds);

        return <<<HEREDOC
      <span>Abholschein</span>
      <span>
        <a href="{$_SERVER['PHP_SELF']}?action=abholschein&mode=view&vlist=$vlist" target="_blank">[Drucken]</a>&nbsp;
        <a href="{$_SERVER['PHP_SELF']}?action=abholschein&mode=download&vlist=$vlist">[Speichern]</a>
      </span>
HEREDOC;

    }


    // ==============================================================================================
    function GetHtmlElement_Abholschein($vehicleData, $content, $col, $id, $attr)
    {

        return <<<HEREDOC
<a href="{$_SERVER['PHP_SELF']}?action=abholschein&mode=view&vlist=$id" target="_blank">[Drucken]</a>,
<a href="{$_SERVER['PHP_SELF']}?action=abholschein&mode=download&vlist=$id">[Speichern]</a>
HEREDOC;

    }


    // ==============================================================================================
    function PreExecute()
    {

    }


    // ==============================================================================================
    function Execute()
    {

        try {
            parent::Execute();

            if ($this->csvTool) {
                $result = $this->csvTool->Execute();
                switch ($result) {
                    case 0:
                        $this->execMode = TABLE_VEHICLES_UNDEFINED;
                        break;

                    case 1:
                        $this->execMode = TABLE_VEHICLES_CVS_UPDATE;
                        return;

                    case 2:
                        if ($this->GetSelectedFromCsv())
                            return;

                        $this->SetState(INPUT_STATE_EDIT_1);
                        break;
                }
            }

            $this->PreExecute();
            $this->ApplySelected($this->vehiclesSalesPtr);
            $this->ExecuteCommand($_REQUEST['command']);
            $this->InitState();

            switch ($this->InputState) {
                case INPUT_STATE_SELECT:
                    $this->On_State_Select();
                    break;

                case INPUT_STATE_SAVE_PRINT:
                    $this->On_State_Save_Print();
                    break;
            }
        } catch (Exception $E) {
            $this->SetError(STS_ERROR_PHP_EXCEPTION, $E);
        }

    }


    // ==============================================================================================
    function ExecuteCommand($command)
    {

        switch ($command) {
            case 'rstfltr':
                $this->S_where = "";
                $_REQUEST['search'] = [];
                return true;

            case 'back':
            case 'undo':
                $this->UndoTempChanges();
                $this->SetState(INPUT_STATE_SELECT);
                break;
        }
        return false;

    }


    // ==============================================================================================
    function ExecuteCsvUpdate()
    {

        $this->execMode = TABLE_VEHICLES_CVS_UPDATE;

    }


    // ==============================================================================================
    function UndoTempChanges()
    {

        foreach ($this->S_selectedVehicles as $vid => &$set) {
            unset($set['new']);
        }
        $this->S_selectedVehicles = [];
        $this->S_changedColumns = [];

    }


    // ==============================================================================================
    function ApplySelected()
    {

        global $_REQUEST;

        if ($this->execMode == TABLE_VEHICLES_CVS_UPDATE) {
            $this->S_selectedVehicles = &$this->S_currentVehicles;
            return;
        }
        if ($this->InputState >= INPUT_STATE_EDIT_1) // || isset($_POST['command']['edit1']))
            return;

        foreach ($this->S_selectedVehicles as $vid => $set) {
            if (empty($_REQUEST['cb_selected'][$vid]) && isset($this->S_currentVehicles["$vid"]) && isset($this->S_selectedVehicles[$vid])) {
                unset($this->S_selectedVehicles[$vid]);
            }
        }

        if (is_array($_REQUEST['cb_selected'])) {
            foreach ($_REQUEST['cb_selected'] as $vid => $on) {
                if (empty($this->S_selectedVehicles[$vid])) {
                    $this->S_selectedVehicles[$vid] = & $this->S_currentVehicles[$vid];
                    $this->S_selectedVehicles[$vid]['checked'] = true;
                }
            }
        }

    }


    // ==============================================================================================
    function ExecutePager()
    {

        global $_REQUEST;

        if (isset($_REQUEST['command'])) {
            $command = strtolower($_REQUEST['command']);
            $currentPage = $_REQUEST['currentPage'];

            switch ($command) {
                case 'go_first':
                    $this->S_currentPage = 1;
                    break;
                case 'go_prev':
                    $this->S_currentPage = max(1, $currentPage - 1);
                    break;
                case 'go_next':
                    $this->S_currentPage = min($this->numPages, $currentPage + 1);
                    break;
                case 'go_last':
                    $this->S_currentPage = $this->numPages;
                    break;

                case 'goto':
                    SafeUpdateFromRequest($this->S_currentPage, 'goto_page');
                    break;
            }
        }

    }


    // ==============================================================================================
    function SetupHeaderFiles($displayheader)
    {

        parent::SetupHeaderFiles($displayheader);

        $displayheader->enqueueJs('formtools', 'js/formtools.js');
        $displayheader->enqueueJs('jquery-2', 'js/jquery-2.2.0.min.js');
        $displayheader->enqueueJs('chosen', 'js/chosen.jquery.min.js');

        $onSelected = [];
        $strSelected = "";

        foreach ($this->btnEnabled as $btn => $enabled) {
            if ($enabled == 'anySelected')
                $onSelected[] = "id_btn_$btn";
        }

        if (count($onSelected))
            $strSelected = "'" . implode("','", $onSelected) . "'";

        $this->displayfooter->enqueueFinallyCalls("on_selected_btn=[$strSelected];");

    }


    // ==============================================================================================
    function GetHtmlButtonAttributes($command)
    {

        $label = $this->btnLabels[$command];
        $enabled = true;

        if (isset($this->btnEnabled[$command])) {
            $eval = $this->btnEnabled[$command];
            if ($eval === 'anySelected')
                $enabled = (count($this->S_selectedVehicles) > 0);
            else if ($eval === 'anyFilter')
                $enabled = (count($this->S_where) > 0);
            else if (($eval === false) || ($eval === 0))
                $enabled = false;
            else if (($eval === true) || ($eval === 1))
                $enabled = true;
            else
                $enabled = eval("return $eval;");
        }
        $disabled = ($enabled ? "" : " disabled");

        return "name=\"bt_$command\" id=\"id_btn_$command\" value=\"$label \" OnClick=\"javascript:SubmitForm('$command')\"$disabled";

    }


    // ==============================================================================================
    function WriteHtmlContent($options = "")
    {

        $this->prevsel = $this->numsel = count($this->S_selectedVehicles);
        foreach ($this->S_currentVehicles as $vid => $set) {
            if ($set['selected'])
                $this->prevsel --;
        }

        $this->S_CurrentIDs = array();
        $this->displayCols = $this->getVisibleCols();

        parent::WriteHtmlContent($options);

    }


    // ==============================================================================================
    function InitTableInfo($class = 'sales', $width = '100%')
    {

        $this->tableInfo['class'] = $class;
        $this->tableInfo['width'] = $width;
        $this->tableInfo['style'] = "width:$width;";
        $this->tableInfo['open'] = [];
        $this->tableInfo['close'] = [
            "</table>",
            "</div>"
        ];

        if ($this->InputState < INPUT_STATE_EDIT_1) {
            $this->tableInfo['data'] = & $this->S_currentVehicles;
        } else {
            $this->tableInfo['data'] = & $this->S_selectedVehicles;
        }

        $this->tableInfo['numRows'] = count($this->tableInfo['data']);

        if ($this->ShowAllScrollable) {
            $this->tableInfo['style'] .= "table-layout:fixed;";
            $this->tableInfo['open'][] = "<div style=\"width:$width;\">";
            $this->tableInfo['close'][] = "</div>";
        } else {
            $this->tableInfo['open'][] = '<div style="overflow-x: scroll;width:$width;">';
        }

        $this->tableInfo['tag'] = sprintf('<table class="%s" id="vehicles_list_table" style="%s">' . lf, $class, $this->tableInfo['style']);
        $this->tableInfo['open'][] = &$this->tableInfo['tag'];

    }


    // ==============================================================================================
    function WriteHtmlTable($class = 'sales', $width = '100%')
    {

        $this->InitTableInfo($class, $width);

        echo "<!-- :::::::::::::::::::::::::::::::: Die Tabelle ::::::::::::::::::::::::::::::::::::: -->\n";

        echo implode("\n", $this->tableInfo['open']);

        echo "\n<thead>\n";
        echo $this->GetHtml_VehiclesTableHead($this->displayCols);

        if ($this->InputState == INPUT_STATE_SELECT) {
            echo $this->GetHtml_VehiclesSearchRow($this->displayCols);
        }
        echo "</thead>\n";

        if ($this->ShowAllScrollable) {
            echo '</table>' . lf;
            echo sprintf('<div style="overflow-y: scroll;width:%s;max-height:500px;">' . lf, $this->tableInfo['width']);
            echo $this->tableInfo['tag'];
            $this->tableInfo['close'][] = "</div>";
        }

        echo "<tbody>\n";

        foreach ($this->tableInfo['data'] as $dataRow) {
            echo $this->GetHtml_VehiclesTableRow($this->displayCols, $dataRow);
        }

        echo implode("\n", $this->tableInfo['close']);

        if ($this->InputState == INPUT_STATE_EDIT_2) {
            echo '</table><p>&nbsp;</p><table><tr><td style="width:200px;text-align:center;">';
            echo '  <input class="sales" type="button" value="Zurück" OnClick="' . "javascript:SubmitForm('back')" . '">&nbsp;&nbsp;';
            echo '  <input class="sales" type="button" value="Speichern" OnClick="' . "javascript:SubmitForm('save')" . '">';
            echo "\n</td></tr>";
        }

    }

}
?>
