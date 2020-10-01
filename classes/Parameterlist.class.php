<?php
/**
 * Parameterlist.class.php
 * @author Lothar Jürgens
 */

define('BT_NONE', 0);
define('BT_DISABLED', 1);
define('BT_ENABLED', 2);
define('BT_TOGGLED', 3);


function sortVariant($a, $b) {
    $res = strncasecmp($b, $a, 5);
    if ($res)
        return $res;

    return strcasecmp(substr($a, 5), substr($b, 5));
}

/*
function sortCapter($val1, $val2)
{
  $v1 = $v2 = 0;
  $s1 = $s2 = [];
  if (preg_match('/^([0-9.]*[0-9])[.]?([^0-9.].*)$/', $val1, $match1)) {
    $v1 = $match1[1];
    $r1 = $match1[2];
    $s1 = explode('.', $v1);
  }

  if (preg_match('/^([0-9.]*[0-9])[.]?([^0-9.].*)$/', $val2, $match2)) {
    $v2 = $match2[1];
    $r2 = $match2[2];
    $s2 = explode('.', $v2);
  }

  while (count($s1) && count($s2)) {
    $v1 = array_shift($s1);
    $v2 = array_shift($s2);
    if ($v1 > $v2)
      return 1;
    if ($v1 < $v2)
      return -1;
  }
  if (count($s1))
    return 1;
  if (count($s2))
    return -1;

  return strcmp($r1, $r2);
}
*/

/*include("ValidateFormParameterManagement.class.php");*/

class Parameterlist extends AClass_FormBase {
    const FORCE_ALL_TAGS = 1;
    const WITH_DELETE_PENTA = false;

    const ID_Fahrzeuginfo = 0;
    const ID_Fahrzeugeigenschaften = 1;
    const ID_GlobaleParameter = 2;
    const ID_AlleParameter = 3;
    const ID_GeraeteParameter = 4;

    const PRIV_ECU_DATA = 'ecu_data';
    const PRIV_ECU_PROFILE = 'ecu_data'; // 'ecu_revision'
    const PRIV_ECU_PROPERTIES = 'ecu_properties';
    const PRIV_VARIANT_EDIT = 'variant_edit';
    const PRIV_VARIANT_COC = 'variant_coc';
    const PRIV_VARIANT_CREATE = 'variant_create';
    const PRIV_SET_VARIABLE = 'variable';
    const PRIV_VARIABLE_ADMIN = 'variable_admin';

    const ID_TAB_Validate = 0;
    const ID_TAB_Varianten = 1;
    const ID_TAB_ECU_Versionen = 2;
    /*  const ID_TAB_SW_ODX_Uebersicht = 3;
  const ID_TAB_CAN_Matrix = 4;
  const ID_TAB_Pentavarianten = 5;
  const ID_TAB_Freigabe = 6;
  const ID_TAB_Verantwortliche = 7;
  const ID_TAB_Parts = 8;
  const ID_TAB_PartsParameter = 9;
  const ID_TAB_EcuOdxVersion = 10;
  const ID_TAB_VariantenKonfig = 11;*/

    /* const EDIT_VariantData = 1;*/
    const EDIT_EcuVersion = 2;
    const EDIT_NewEcuVersion = 3;
    /*const EDIT_CopyEcuVersion = 4;
  const EDIT_Variant = 5;
  const EDIT_NewVariant = 6;
  const EDIT_CopyVariant = 7;
  const EDIT_Privileges = 8;
  const EDIT_CopyVariantEcuData = 9;
  const EDIT_NewGlobalVariable = 10;
  const EDIT_CopyGlobalVariable = 11;
  const EDIT_DistributeCocValues = 12;*/
    const EDIT_DistributeSWParameters = 13;

    const PARAM_RW = 0;
    const PARAM_NOT_USED = 1;
    const PARAM_ONLY_READ = 2;
    const PARAM_CONST = 3;
    const PARAM_TAGS_MISSING = 4;
    const PARAM_NO_VALUE = 5;
    const PARAM_TYPE_ERROR = 6;
    const PARAM_SET_BY_MACRO = 7;
    const PARAM_SET_BY_PART = 8;
    const PARAM_SET_DYNAMIC = 9;
    const PARAM_IS_DEFAULT = 10;

    const output_php = 0;
    const output_db = 1;
    const output_odx02 = 2;
    const output_gui = 3;

    const odx01_int = 1;
    const odx01_float = 2;
    const odx01_string = 3;
    const odx01_bool = 4;
    const odx01_undefined = 5;

    const ECU_PARAMETER_OK = 0;
    const ECU_VARIANT_DATA_CORRUPT = 1;
    const ECU_SOFTWARE_CONFIG_CORRUPT = 2;

    const VARIANT_TESTVEHICLE = 'VariableKonfiguration';
    const VIN_STS_TESTVEHICLE = 'TestC2CBox0123456';
    const ODX_ACCESS_TIMOUT_S = 30;
    const FIXED_ACTIONS_ODX01 = [1 => 'rc', 2 => 'rc', 4 => 'r'];
    const CAPTION_BREAK = false; //'num_axles';

    const ODX02_VALUETYPES = [
        'unused' => '(keine Vorgabe)',
        'deflt' => 'Default',
        'const' => 'Konstante',
        'macro' => 'globaler Parameter',
        'dynmc' => 'Dynamisch',
        'part' => 'Bauteil (noch keine Funktion)',
//         'list'  => 'Auswahlliste',
//         'radio' => 'Radiobox',
//         'ra nge' => 'Zahlenbereich'
    ];
    const ODX02_DYNAMIC_KEYS = ['-leer-', 'VIN', 'MINUTE', 'HOUR', 'DAY', 'MONTH', 'YEAR', 'SECOND']; //, '%TIMESTAMP%', '%UNIXTIME%'];

    /*  const VEHICLE_VARIANT_NO_COPY = ['vehicle_variant_id', 'windchill_variant_name', 'coc_released_by', 'coc_released_date'];*/

    const ODX_DUMMY_CONTENT = '
<ecuComposition>
    <a12 />
    <b14 />
    <b16>
        <vin value="TestC2CBox0123456">
            <hw version="100745">
                <sw version="R20160624">
                    <parameters>
                        <parameter id="WARNING_LIGHT_ESP">true</parameter>
                        <parameter id="WARNING_LIGHT_PARKING_BRAKE">true</parameter>
                        <parameter id="WARNING_LIGHT_COOLEANT_TEMPERATURE">true</parameter>
                        <parameter id="WARNING_LIGHT_EHPS">true</parameter>
                        <parameter id="MAXIMAL_POWER_KW">50</parameter>
                        <parameter id="BOOST_TRANSITION_KW">36</parameter>
                        <parameter id="RECURPERATION_KW">-17</parameter>
                        <parameter id="AVERAGE_ENERGY_CONSUMPTION">false</parameter>
                        <parameter id="STARTING_ANIMATION">false</parameter>
                    </parameters>
                </sw>
            </hw>
        </vin>
    </b16>
</ecuComposition>

<ecuComposition version="2017-10">
  <!--neueste Daten immer ueber aeltere Daten - Modelle alphabethisch sortiert-->
  <d16>
    <vin value="TestC2CBox0123456">
      <hw action="rc" byteCount="18" offset="0" reponseId="0x1BC018F1" requestId="0x1BC78803" factor="1" type="ascii" udsId="0x0133" version="0x413132583832353330305f303420462e3133">
        <sw action="rc" byteCount="21" offset="0" factor="1" type="ascii" udsId="0x0134" version="0x413132583832353330305f30345f303220582e59">
          <parameters>
            <parameter action="rwc" byteCount="1" id="WARNING_LIGHT_ESP" offset="0" factor="1" type="unsigned" udsId="0x0201" unit="0">0x01</parameter>
          </parameters>
        </sw>
      </hw>
    </vin>
  </d16>
</ecuComposition>
';


    // ===============================================================================================
    // ===============================================================================================
    function __construct($locked) {
        parent::__construct();
        $this->V_data = [];
        $this->configError = 0;
        $this->odxAccessError = 0;
        $this->odxAccessMutex = null;
        $this->errorStyle = ' style="background-color:#faa"';

        $this->leitWartePtr = $this->controller->GetObject('ladeLeitWarte');
        $this->vehiclesPtr = $this->controller->GetObject("vehicles");
        $this->vehicleVariantsPtr = $this->controller->GetObject("vehicleVariants");
        $this->vehiclesSalesPtr = $this->controller->GetObject("vehiclesSales");
        $this->userPtr = $this->controller->GetObject("user");

        $this->ecuPtr = new Ecu ($this->leitWartePtr, 'ecus');
        $this->variantDataPtr = new VariantData ($this->leitWartePtr, 'vehicle_variant_data');
        $this->forceReload = true;
        $this->allprotocols = [0 => 'keine Kommunikation', 1 => 'UDS (only)', 2 => 'XCP (only)', 3 => 'UDS + XCP'];
        $this->allEcus = $this->ecuPtr->newQuery()
            ->where('ecu_id', '>', 0)
            ->orderBy('name')
            ->get('*', 'ecu_id');
        $this->allEcuNames = reduce_assoc($this->allEcus, 'name');
        $firstEcu = array_keys($this->allEcuNames)[0];
        $this->iGeneralPar = array_search('general', $this->allEcuNames);
        $this->suchFehler = "";
        $this->suchMeldung = "";
        $this->masterID = 0;
        $this->loadFromVariant = false;
        $this->m_VariantChanged = false;
        $this->m_VariantType = null;
        $this->m_Permission = null;
        $this->m_EcuChanged = false;
        $this->m_EditMode = safe_val($_POST, 'edit', 0);
        $this->m_table = null;
        $this->m_maxSerialNumber = 0;

        $tmpListe = $this->leitWartePtr->newQuery('colors')->get_no_parse("distinct color_key");
        $this->all_color_keys = array_column($tmpListe, 'color_key');

        if ($this->iGeneralPar)
            unset ($this->allEcuNames[$this->iGeneralPar]);
        if (isset ($this->allEcuNames[0]))
            unset ($this->allEcuNames[0]);

        $this->DB_allTypes = $this->ecuPtr->newQuery('parameter_value_types')->orderBy('parameter_value_types_id')->get("parameter_value_types_id=>value_types");
        $this->DB_allUnits = $this->ecuPtr->newQuery('units')->orderBy('unit_id')->get("unit_id=>name");
        $this->DB_VariantSet = null;
        $this->DB_Parameters = null;
        $this->DB_allVariants = null;
        $this->DB_allVariantTypes = [];
        $this->DB_indexAllVariant = null;
        $this->DB_VariantEcuData = null;
        $this->DB_allRevisions = null;
        $this->DB_RevisionMap = null;
        $this->DB_Variables = null;


        $this->listVMs = ['sop2017', 'sop2018'];

        $this->allParts = $this->ecuPtr->newQuery('parts')->orderBy('group_id')->get("*", "part_id");
        $this->S_CombiID = &InitSessionVar($this->S_data['variantID'], false);
        $this->S_ParamType = &InitSessionVar($this->S_data['paramType'], 0);
        $this->S_variantEcu = &InitSessionVar($this->S_data['variantEcu'], $firstEcu);
        $this->S_SearchText = &InitSessionVar($this->S_data['searchText'], "");
        $this->S_SearchItem = &InitSessionVar($this->S_data['searchItem'], false);
        $this->S_SearchList = &InitSessionVar($this->S_data['searchList'], false);
        $this->S_SearchFor = &InitSessionVar($this->S_data['searchFor'], '');
        $this->S_SearchVehicle = &InitSessionVar($this->S_data['searchVehicle'], 0);
        $this->S_variantRev = &InitSessionVar($this->S_data['variantRev'], 0);
        $this->S_Revisions = &InitSessionVar($this->V_data['Revisions'], 0);
        $this->S_ecuVersion = &InitSessionVar($this->S_data['ecuVersion'], 0);
        $this->S_currentEcu = &InitSessionVar($this->S_data['currentEcu'], $firstEcu);
        $this->S_dbParamDef = &InitSessionVar($this->S_data['dbParamDef'], 0);
        $this->S_OdxVerEcu = &InitSessionVar($this->S_data['odxVerEcu'], 1);
        $this->m_OdxVerShowTab = &InitSessionVar($this->S_data['odxVerShowTab'], []);
//         $this->m_OdxVerShow     = & InitSessionVar ($this->S_data['odxVerShow'],        1       );
        $this->S_OdxSaveBoth = true;
        $this->S_currentVariable = &InitSessionVar($this->S_data['currentVariable'], []);

        $this->S_Overview = &InitSessionVar($this->S_data['overview'], null);
        $this->S_Tab = &InitSessionVar($this->S_data['selectedTab'], null);
        $this->S_EditSet = &InitSessionVar($this->S_data['editSet'], null);
        $this->S_infoState = &InitSessionVar($this->S_data['infoState'], 1);
        /*      $this->S_OdxDownload    = & InitSessionVar ($this->S_data['odxDownload'],   null    ); */

        $this->numNew = 0;
        $this->tmpOrder = 0;
        $this->column_id = 0;
        $this->m_WindchillId = null;
        $this->m_PentaConfigId = null;
        $this->EcuParamState = self::ECU_PARAMETER_OK;
        $this->m_EcuBigEndian = false;
        $this->odx_content = false;
        $this->odx_file = "";
        $this->m_difference = [];
        $this->m_ecu_to_utf8 = false;
        $this->m_utf8_to_ecu = false;
        $this->m_SearchDest = null;
        $this->m_SearchDestText = '';
        $this->m_privilegs_id = 1;
        $this->m_OdxVerShow = 0;

        $nextYear = date("y") + 1;

        $this->versionLimits = ['<c1' => 'A', '>c1' => 'E', '<c23' => 12, '>c23' => $nextYear, '!=c4' => 'X',
            'regex' => [
                '/^[B-Z][0-9]{2}X[0-9A-F]{8}_[0-9]{2}\s*[A-Z]?$/',
                '/^[A-D]1[0-9]X[0-9]{6}_[0-9]{2}\s*[A-Z]?[.]?[0-9]*$/',
                '/^A12X825300_[0-4][0-9]_[0-9]{2}\s*[A-Z]?$/'
            ],
            'new-regex' => '/^[ABCDEF]{1}[1-3]{1}[0-9]{1}[A-Z]{1}([0-9]*)([_]{1}[0-9]{1,8})*$/'
        ];
        $this->locked = $locked;

    } // D17X518004_01 A

    // ===============================================================================================
    function Init() {
        parent::Init();

        $this->QueryVariant_AllPentaVariants();
        $this->DB_Variables = $this->QueryVariables();
        if ($first = reset($this->DB_Variables))
            $this->m_variablesRootId = $first['parent_id'];

        if ($this->S_CombiID)
            $this->m_VariantType = substr($this->DB_allVariants[$this->S_CombiID]['v_name'], 0, 3);

        $hiV = chr(ord(substr(reset($this->DB_allVariantTypes), 0, 1)) + 1);
        $this->versionLimits['>c1'] = $hiV;

        if (empty ($this->m_OdxVerShowTab)) {
            foreach ($this->allEcus as $ecu_id => $set) {
                $this->m_OdxVerShowTab[$ecu_id] = toBool($set['supports_odx02']) ? 2 : 1;
            }
        }
    }

    // ===============================================================================================
    function Ajaxecute($command) {
        parent::Ajaxecute($command);

        if (preg_match('/^priv[-]([a-z0-9_-]+)$/', $command, $match)) {
            echo $this->PrivilegsAjaxecute($match[1]);
            exit;
        }

        switch ($command) {
            case 'showInfoBlock':
                $this->S_infoState = $_REQUEST['visibility'];
                break;

            case 'setodx2':
                foreach ($_REQUEST['odx2'] as $ecu_id => $checked) {
                    $res = $this->ecuPtr->newQuery()
                        ->where('ecu_id', '=', $ecu_id)
                        ->update(['supports_odx02'], [$checked]);
                }
                break;

            case 'setbigend':
                foreach ($_REQUEST['bigend'] as $ecu_id => $checked) {
                    $res = $this->ecuPtr->newQuery()
                        ->where('ecu_id', '=', $ecu_id)
                        ->update(['big_endian'], [$checked]);
                }
                break;


        }
    }

    // ==============================================================================================
    function IncludeTableInfo() {
        if (!isset ($this->cocTableInfo)) {
            $this->cocTableInfo = [];
            $this->cocHidden = [];
            $this->configInfo = [];

            $hidden = &$this->cocHidden;
            $config = &$this->configInfo;
            $coc = &$this->cocTableInfo;
            $db = &$this->leitWartePtr;

            include $_SERVER['STS_ROOT'] . "/actions/Engg/Parameterlist/Parameterlist.coc.php";
        }
    }
    /*
  // ==============================================================================================
  function Separated_Variant_ID($vname)
  {
    $vn1 = substr($vname, 0, 3);
    $vn2 = substr($vname, 3, 2);
    $vn3 = substr($vname, 5, 4);
    $vn4 = substr($vname, 9, 1);
    $vn5 = substr($vname, 10, 1);
    return "$vn1 $vn2 $vn3 $vn4 $vn5";
  }
*/
    // ===============================================================================================
    function CheckRevisionName($version_string) {
        if (strlen($version_string) < 5)
            return false;

        if (substr($version_string, 0, 2) == '**')
            return true;

        $validation_flag = true;

        $c1 = $version_string[0];
        $c23 = substr($version_string, 1, 2);
        $c4 = $version_string[3];
        $VL = &$this->versionLimits;

        if (($c1 < $VL['<c1']) || ($c1 > $VL['>c1']) || ($c23 < $VL['<c23']) || ($c23 > $VL['>c23']) || ($c4 != $VL['!=c4']))
            $validation_flag = true;

        if ($validation_flag) {
            foreach ($VL['regex'] as $reg)
                if (preg_match($reg, $version_string))
                    return true;
        }

        if (preg_match($VL['new-regex'], $version_string))
            return true;

        return false;
    }

    function CheckSuffixName($version_string) {
        if (strlen($version_string) < 2)
            return false;
        else
            return true;
    }

    function DisplayedSuffixName($revision_set, $checkReleased) {
        if ($revision_set['subversion_suffix'] != '') {
            if ($this->CheckSuffixName($revision_set['subversion_suffix'])) {
                return $revision_set['subversion_suffix'];
            }

            $rev = $revision_set['ecu_revision_id'];
            return "{unbekannte Version ID $rev}";
        } else {
            return '';
        }
    }

    // ===============================================================================================
    function DisplayedRevisionName($revision_set, $checkReleased) {
        if ($this->CheckRevisionName($revision_set['sts_version'])) {
            if (!$checkReleased || toBool($revision_set['released']))
                return $revision_set['sts_version'];

            return "({$revision_set['sts_version']})";
        }

        $rev = $revision_set['ecu_revision_id'];
        return "{unbekannte Version ID $rev}";
        /*
        $vs = $revision_set['sw'];
        if (($vs=='') || ($vs=="''"))
            $vs = "{leerer String} ID=" . $revision_set['ecu_revision_id'];

        return $vs;
        */
    }

    // ===============================================================================================
    function GetListOfDisplayedRevisionsNames($revisions, $only_profile_ok = true) {
        $corrected = [];
        foreach ($revisions as $id => $revision_set) {
            if (!$only_profile_ok || toBool($revision_set['sw_profile_ok'])) {
                $corrected[$id] = $this->DisplayedRevisionName($revision_set, true);
            }
        }
//
        return $corrected;
    }

    // ==============================================================================================
    function CntVariantsUsingRevision($rev_id) {
        $result = $this->vehiclesPtr->newQuery('variant_ecu_revision_mapping')
            ->where('rev_id', '=', $rev_id)
            ->where('variant_id', '>', 1)
            ->getVal('count(*) as cnt');
        return $result;
    }


    // ==============================================================================================
    function CntVehiclesbyPenta() {
        $result = $this->vehiclesPtr->newQuery()
            ->where('penta_number_id', 'IS', 'NOT NULL')
            ->where('VIN', '!=', self::VIN_STS_TESTVEHICLE)
            ->groupBy('penta_number_id')
            ->get('penta_number_id, count(*) as anzahl', 'penta_number_id');

        return reduce_assoc($result, 'anzahl');
    }

    /*
  // ===============================================================================================
  function CntVehiclesbyPenta_alt($cnt_label_name = 'vcnt')
  {
    return $this->ecuPtr->newQuery('vehicles')->where('penta_number_id', 'IS', 'NOT NULL')->groupBy('penta_number_id')->get('penta_number_id,count(*) as ' . $cnt_label_name);
  }
*/
    /*
  // ===============================================================================================
  function GetVariantPartlist($variant_id, $penta_id = 0)
  {
    $this->advicingParts = [];
    $this->engeneeringParts = [];
    $count_penta_parts = 0;
    $this->allPartGroups = $this->leitWartePtr->newQuery('part_groups')->get("*", 'group_id');

    $qry = $this->leitWartePtr->newQuery('variant_parts_mapping')
        ->where('variant_id', '=', $variant_id);
    $variant_parts = $qry->get_no_parse("*, 'f' as is_penta_part", 'part_id');

    $qry = $this->leitWartePtr->newQuery('penta_number_parts_mapping')
        ->where('penta_number_id', '=', $penta_id);
    $penta_parts = $qry->get_no_parse("*, 'f' as is_penta_part", 'part_id');

    $usedParts = safe_merge($variant_parts, $penta_parts);

    foreach ($this->allParts as $part_id => $set) {
      if (toBool($set['visible_sales']))
        $list = &$this->advicingParts;
      else
        $list = &$this->engeneeringParts;

      $group_id = safe_val($set, 'group_id', 0);

      if (!isset($list[$group_id]))
        $list[$group_id] = [];

      if (isset ($usedParts[$part_id])) {
        $set['count'] = $usedParts[$part_id]['count'];
        $set['is_penta_part'] = toBool($usedParts[$part_id]['is_penta_part']);
        if ($set['is_penta_part'])
          $count_penta_parts++;

        if (!isset ($this->allPartGroups[$group_id]['parts']))
          $this->allPartGroups[$group_id]['parts'] = [];
        $this->allPartGroups[$group_id]['parts'][] = $part_id;
      } else {
        $set['count'] = 0;
        $set['is_penta_part'] = false;
      }

      $list[$group_id][$part_id] = $set;
    }
  }
*/

    // ===============================================================================================
    function ParsePentaNumber($penta_number) {
        $regex = '/' . REGEX_VALID_CONFIG . '([^_]*)(_(?i:' . implode('|', $this->all_color_keys) . '))((?:|_.*))$/';


        $result = ["", "", ""];
        if (strncasecmp($penta_number, 'B14_v12_', 8) == 0) {
            $result[0] = 'B14';
            $result[1] = '_v12';
            $result[2] = substr($penta_number, 8, 2);
            $result[3] = substr($penta_number, 10);
        } else {
            if (preg_match($regex, $penta_number, $match)) {
                $result[0] = $match[1];
                $result[1] = $match[2];
                $result[2] = $match[3];
                $result[3] = (isset ($match[4]) ? $match[4] : "");
            }
        }
        return $result;
    }


    // ===============================================================================================
    function QueryVariant_AllPentaVariants() {
        $this->DB_allVariants = [];
        $this->DB_allVariantTypes = [];
        $this->DB_indexAllVariant = [];
        $regex = '/' . REGEX_VALID_CONFIG . '/';

        $query = <<<SQLDOC
SELECT
    vehicle_variant_id      AS v_id,
    windchill_variant_name  AS v_name,
    penta_number_id         AS p_id,
    penta_number            AS p_name,

    default_color, battery, vin_method,
    penta_config_id,  color_id,

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

        $qry = $this->ecuPtr->newQuery();
        if (!$qry->query($query))
            return $this->SetError($qry->GetLastError());

        $all_variants = $qry->fetchAll();
        $cnt_by_penta = $this->CntVehiclesbyPenta();

        foreach ($all_variants as $variantset) {
            if (preg_match($regex, $variantset['v_name'], $match)) {
                $vt = $match[1];
                if (!isset ($this->DB_allVariantTypes [$vt])) {
                    $this->DB_allVariantTypes[$vt] = $vt;
                    $this->DB_indexAllVariant[$vt] = [];
                }
            }
        }
        krsort($this->DB_allVariantTypes);

        $tmptree = [];
        foreach ($all_variants as $n => &$variantset) {
            $penta_id = $variantset['p_id'];
            $penta_number = $variantset['p_name'];
            $windchill_id = $variantset['v_id'];
            $windchill_name = $variantset['v_name'];
            $fz_serie = intval(substr($windchill_name, 3, 2));
            $default_color = $variantset['default_color'];
            $vt = substr($windchill_name, 0, 3);

            if (empty ($penta_number)) {
                $basename = $variantset['v_name'];
                $uncolored = $basename . '_##';
                $penta_id = 0;
                $penta_cfg_id = 0;
                $color_id = $default_color;
                $count_prod = 0;
            } else {
                $penta_cfg_id = $variantset['penta_config_id'];
                $color_id = $variantset['color_id'];
                $count_prod = safe_val($cnt_by_penta, $penta_id, 0);

                $scanResult = $this->ParsePentaNumber($penta_number);
                if (empty ($scanResult[0]) || empty ($scanResult[1])) {
                    $combi_id = "$windchill_id:$penta_cfg_id";
                    $this->DB_allVariants[$combi_id] = $variantset;
                    continue;
                }
                $basename = $scanResult[0] . $scanResult[1];
                $uncolored = $basename . '_##';

                if (!empty ($scanResult[3])) {
                    $basename .= '_##' . $scanResult[3];
                    $uncolored .= $scanResult[3];
                }
            }

            if ($fz_serie > $this->m_maxSerialNumber)
                $this->m_maxSerialNumber = $fz_serie;

            if (empty ($color_id))
                $color_id = 1;

            $variantset['i_name'] = $basename;
            $variantset['u_name'] = $uncolored;

            if (isset ($tmptree[$vt][$windchill_name][$uncolored]))
                $node = $tmptree[$vt][$windchill_name][$uncolored];
            else
                $node = [
                    'index' => $n,
                    'windchill_id' => $windchill_id,
                    'penta_config_id' => $penta_cfg_id,
                    'color_variants' => [],
                    'color_counts' => [],
                    'count_prod' => 0
                ];


            $node['color_variants'][$color_id] = $penta_id;
            $node['color_counts']  [$color_id] = $count_prod;
            $node['count_prod'] += $count_prod;

            $tmptree[$vt][$windchill_name][$uncolored] = $node;
        }

        foreach ($tmptree as $vt => &$set)
            uksort($set, "sortVariant");


        krsort($this->DB_indexAllVariant);
        foreach ($this->DB_allVariantTypes as $vt) {
            foreach ($tmptree[$vt] as $windchill_name => &$subvarianten) {
                uksort($subvarianten, "sortVariant");

                $prim_tmpset = array_shift($subvarianten);
                $prim_n = $prim_tmpset['index'];
                $windchill_id = $prim_tmpset['windchill_id'];
                $config_id = safe_val($prim_tmpset, 'penta_config_id', 0);
                $prim_id = "$windchill_id:$config_id";
                $count = $prim_tmpset['count_prod'];
                $indexName = $windchill_name;

                $this->DB_allVariants[$prim_id] = $all_variants[$prim_n];


                if (count($subvarianten)) {
                    $this->DB_allVariants[$prim_id]['subvarianten'] = [];
                    foreach ($subvarianten as $tmpset) {
                        $sub_n = $tmpset['index'];
                        $sub_id = "$windchill_id:{$tmpset['penta_config_id']}";
                        $count += $tmpset['count_prod'];

                        $this->DB_allVariants[$sub_id] = $all_variants[$sub_n];
                        $this->DB_allVariants[$sub_id]['master'] = $prim_id;
                        $this->DB_allVariants[$sub_id]['count_prod'] = $tmpset['count_prod'];
                        $this->DB_allVariants[$sub_id]['color_variants'] = $tmpset['color_variants'];

                        $this->DB_allVariants[$prim_id]['subvarianten'][] = $sub_id;
                    }

//                     $indexName = "[+] " . $indexName;
                    $this->DB_allVariants[$prim_id]['count_all'] = $count;
                }

                $this->DB_allVariants[$prim_id]['i_name'] = $indexName;
                $this->DB_allVariants[$prim_id]['count_prod'] = $prim_tmpset['count_prod'];
                $this->DB_allVariants[$prim_id]['color_variants'] = $prim_tmpset['color_variants'];

                make_node($this->DB_indexAllVariant, $vt, $prim_id, $indexName);
            }
        }
    }

    // ===============================================================================================
    function QueryMakroParameter($macro_id) {
        $qry = $this->ecuPtr->newQuery('ecu_parameter_sets')
            ->join('ecu_parameters', 'using (ecu_parameter_set_id)')
            ->where('ecu_parameter_set_id', '=', $macro_id);
        $result = $qry->get('*');
        if ($result)
            return $result[0];
        return false;
    }

    // ===============================================================================================
    function QueryVariables() {
        $qry = $this->leitWartePtr
            ->newQuery('ecu_parameter_sets')
            ->join('ecu_parameters', 'using (ecu_parameter_set_id)')
            ->where('ecu_parameters.ecu_id', '=', '-1')
            ->orderBy('odx_name');

        return $qry->get('*', 'ecu_parameter_set_id');
    }
    /*
  // ===============================================================================================
  function GetVariantBattery()
  {
    $variant_battery = $this->DB_VariantSet['battery'];

    if (!empty ($variant_battery)) {
      foreach ($this->allParts as $part_id => $pset) {
        if ($pset['name'] == $variant_battery)
          return $pset['begleitscheinname'];
      }
    }

    $partkey = $this->DB_VariantSet['v_parts'] . $this->DB_VariantSet['p_parts'];
    $partkey = str_replace(['{', '}'], [',', ','], $partkey);

    foreach ($this->allParts as $part_id => $pset) {
      if (($pset['group_id'] == STS_PARTGROUP_BATTERY) && (strpos($partkey, ",$part_id,") !== false))
        return $pset['begleitscheinname'];
    }

    return (substr($vname, 0, 3) == 'D16') ? "(SDA) ?" : "(V5) ?";
  }
*/
    // ===============================================================================================
    function QueryEcuTagsFromParameter($param_set_id, $revision) {
        $sql = $this->ecuPtr->newQuery('ecu_tag_configuration')
            ->where('ecu_parameter_set_id', '=', $param_set_id)
            ->where('ecu_revision_id', '=', $revision)
            ->orderBy('tag')
            ->orderBy('timestamp');

        return $sql->get_no_parse('DISTINCT ON (tag) *', 'tag');
    }
    /*
  // ===============================================================================================
  function QueryRevisionsFromPentaVariant($windchill_id, $penta_id)
  {
    $ql = $this->ecuPtr->newQuery('variant_ecu_revision_mapping');
    $ql = $ql->join('ecu_revisions', 'rev_id=ecu_revision_id', 'left join')
        ->where('variant_id', '=', $windchill_id)
        ->where('penta_id', 'IN', [$penta_id, 0])
        ->orderBy('ecu_id')
        ->orderBy('penta_id', 'desc');
    $rev = $ql->get_no_parse('DISTINCT ON (ecu_id) variant_ecu_revision_mapping.*,hw,sw,sts_version,request_id,response_id,href_windchill,use_uds,use_xcp,sw_profile_ok,released,ecu_revisions.timestamp_last_change as revision_last_change,info_text,version_info', 'ecu_id');
    return $rev;
  }
*/
    /*
  // ===============================================================================================
  function QueryEcuRevisionFromPentaVariant($ecu_id, $windchill_id = 0, $penta_id = 0)
  {
    if (!$this->DB_allRevisions)
      $this->DB_allRevisions = $this->QueryRevisionsFromPentaVariant($windchill_id, $penta_id);

    if (!$windchill_id || ($windchill_id == $this->m_WindchillId))
      return $this->DB_allRevisions[$ecu_id];

    $ql = $this->ecuPtr->newQuery('variant_ecu_revision_mapping');
    $ql = $ql->where('variant_id', '=', $windchill_id)
        ->where('penta_id', 'IN', [$penta_id, 0])
        ->where('ecu_id', '=', $ecu_id)
        ->orderBy('penta_id', 'desc');
    $rev = $ql->get('*');
    if ($rev)
      return $rev[0];

    return false;
  }
*/
    // ===============================================================================================
    function LoadCharacterMapping($ecu_id) {
        $ecuName = strtolower($this->allEcuNames[$ecu_id]);
        $filename = $_SERVER['STS_ROOT'] . "/actions/Engg/Parameterlist/Parameterlist.chrmap.$ecuName.php";

        if (file_exists($filename)) {
            include($filename);

            if ($this->m_ecu_to_utf8) {
                $this->m_utf8_to_ecu = [];
                foreach ($this->m_ecu_to_utf8 as $ascii => $utf8) {
                    $this->m_utf8_to_ecu[$utf8] = $ascii;
                }

                krsort($this->m_utf8_to_ecu);
            }
        }
    }

    // ===============================================================================================
    function SetCurrentEcu($ecu_id) {
        $this->S_variantEcu = $ecu_id;

        if ($ecu_id) {
            $this->S_Revisions = $this->QueryRevisions($ecu_id);
            $this->DB_RevisionMap = $this->QueryEcuRevisionFromPentaVariant($ecu_id, $this->m_WindchillId, $this->m_PentaConfigId);
            $this->m_EcuBigEndian = toBool($this->allEcus[$ecu_id]['big_endian']);

            $this->LoadCharacterMapping($ecu_id);

            $this->m_OdxVerShow = &$this->m_OdxVerShowTab[$ecu_id];
        } else {
            $this->S_Revisions = null;
            $this->DB_RevisionMap = null;
            $this->m_EcuBigEndian = false;
        }

        if ($this->DB_RevisionMap) {
            if (isset ($this->DB_RevisionMap['rev_id'])) {
                $revision_id = $this->DB_RevisionMap['rev_id'];
                $this->S_variantRev = safe_val($this->S_Revisions, $revision_id);
                $this->S_ecuVersion = $this->S_variantRev;
            } else {
                $this->S_variantRev = null;
                $this->S_ecuVersion = null;
            }

            $this->UpdateRevisionCopyState($this->DB_RevisionMap);
            $this->GetEnggPrivileges(self::PRIV_ECU_DATA, $ecu_id);

            return $revision_id;
        } else {
            $this->S_variantRev = null;
            $this->S_ecuVersion = null;
        }
        return false;
    }
    /*
  // ===============================================================================================
  function GetPartsGrouped()
  {
    if (isset ($this->groupedParts))
      return $this->groupedParts;

    $partGroups = $this->ecuPtr->newQuery('part_groups')->get("*", "group_id");
    $this->groupedParts = [];

    foreach ($this->allParts as $part_id => $part) {
      mkBool($part['is_default']);
      mkBool($part['visible_engg']);
      mkBool($part['visible_sales']);

      if (!$part['visible_engg'])
        continue;

      $group_id = $part['group_id'];
      if ($group_id) {
        $id = "G:$group_id";
        $group = &$partGroups[$group_id];
        mkBool($group['allow_none']);
        mkBool($group['allow_multi']);

        if (!isset ($this->groupedParts[$id])) {
          $this->groupedParts[$id] = $group;
          $this->groupedParts[$id]['options'] = [];
        }
        $this->groupedParts[$id]['options'][$part_id] = $part;
      } else {
        $id = "P:$part_id";
        $this->groupedParts[$id] = [
            'group_name' => $part['name'],
            'allow_none' => true,
            'allow_multi' => false,
            'options' => [$part_id => $part]
        ];
      }
    }
    return $this->groupedParts;
  }
*/
    /*
  // ===============================================================================================
  function GetVariantPartsMapping()
  {
    if (isset ($this->variantPartsMapping))
      return $this->variantPartsMapping;

    $this->variantPartsMapping = [];
    //tables = ['variant_parts_mapping', 'construction_parts_mapping'];
    $tables = ['construction_parts_mapping'];

    foreach ($tables as $mapping) {
      $qr = $this->vehicleVariantsPtr
          ->newQuery($mapping)
          ->get('*');

      if (empty($qr))
        continue;


      foreach ($qr as $set) {
        extract($set);
        if (!isset($this->variantPartsMapping[$variant_id]))
          $this->variantPartsMapping[$variant_id] = [];

        $this->variantPartsMapping[$variant_id][$part_id] = $count;
      }
    }

    if (count($this->variantPartsMapping))
      return $this->variantPartsMapping;
    unset ($this->variantPartsMapping);
    return false;
  }
*/
    // ===============================================================================================
    function GetListUsedParameters($mapping_set) {
        $result = [];
        $parameters = $this->QueryParameters($mapping_set['ecu_id'], $mapping_set['rev_id']);

        foreach ($parameters as $pid => &$set) {
            if ($this->m_OdxVerShow == 1) {
                if (!toBool($set['use_in_odx01']))
                    continue;
            } else {
                $deleted = safe_tag_value($set['tag'], 'deleted');
                if (strpos($deleted, 'dx.sts.02'))
                    continue;
            }
            $result[$pid] = $set['param_id'];
        }
        return $result;
    }

    // ===============================================================================================
    function UpdateRevisionCopyState(&$current_rev, $set_error = true) {
        if ($copy_from_variant_id = $current_rev['copy_from_variant_id']) {
            $copy_from_penta_id = $current_rev['copy_from_penta_id'];
            $ecu_id = $current_rev['ecu_id'];
            $copy_timestamp = $current_rev['timestamp_copy'];


            $master_rev = $this->QueryEcuRevisionFromPentaVariant($ecu_id, $copy_from_variant_id, $copy_from_penta_id);
            $master_variant_id = $master_rev['variant_id'];
            $master_penta_id = $master_rev['penta_id'];
            if ($master_penta_id) {
                $master_combi_id = "$master_variant_id:$master_penta_id";
                $master_info = $this->DB_allVariants[$master_combi_id];
                $master_windchill = $master_info['u_name'];
            } else {
                $master_windchill = $this->vehicleVariantsPtr->newQuery()->where('vehicle_variant_id', '=', $master_variant_id)->getVal('windchill_variant_name');
            }

            $current_rev['master'] = $master_windchill;
            if ($current_rev['rev_id'] != $master_rev['rev_id']) {
                if ($set_error)
                    $this->SetMessage(MESSAGE_MASTER_REV_CHANGED, [$current_rev['id'], $master_windchill]);
                $current_rev['diff'] = 'error';
                return;
            }


            $current_rev['diff'] = $this->GetDifferenceToMaster($current_rev, $master_rev);
            $current_rev['changed'] = [];

            if ($master_rev['timestamp_last_change'] > $copy_timestamp)
                $current_rev['changed'][] = 'master';

            if ($current_rev['timestamp_last_change'] > $copy_timestamp)
                $current_rev['changed'][] = 'this';
        }
    }

    // ===============================================================================================
    function GetDifferenceToMaster($current_rev, $master_rev) {
        $parmeters = $this->GetListUsedParameters($current_rev);
        $value_ids = implode(',', array_values($parmeters));
        if ($value_ids == "")
            return [];

        $sql = "
            select * from (
              select
                ecu_parameter_id,
                vehicle_variant_id  as c_id,
                value_int           as c_int,
                value_double        as c_double,
                value_string        as c_string,
                value_bool          as c_bool,
                tag_disabled        as c_disabled,
                value               as c_value
              from vehicle_variant_data
              where vehicle_variant_id={$current_rev['variant_id']}
              and overlayed_penta_id={$current_rev['penta_id']}
              and ecu_parameter_id in ($value_ids)
              ) as current
            full join (
              select
                ecu_parameter_id,
                vehicle_variant_id  as m_id,
                value_int           as m_int,
                value_double        as m_double,
                value_string        as m_string,
                value_bool          as m_bool,
                tag_disabled        as m_disabled,
                value               as m_value
              from  vehicle_variant_data
              where vehicle_variant_id={$master_rev['variant_id']}
              and overlayed_penta_id={$master_rev['penta_id']}
              and ecu_parameter_id in ($value_ids)
              ) as master
         	using (ecu_parameter_id)
            where  c_id is null or m_id is null
                or c_int       != m_int
                or c_double    != m_double
                or c_string    != m_string
                or c_bool      != m_bool
                or c_disabled  != m_disabled
                or c_value     != m_value";

        $qry = $this->variantDataPtr->newQuery();
        if ($qry->query($sql))
            return $qry->fetchAssoc("ecu_parameter_id");
        return false;
    }

    // ===============================================================================================
    function GetMasterValue($value_id, $paramSet, $value, $odx2) {
        if ($this->DB_RevisionMap['diff'] === 'error')
            return null;

        if (isset ($this->DB_RevisionMap['diff'][$value_id])) {
            $diff = &$this->DB_RevisionMap['diff'][$value_id];

            if (toBool($diff['enable']) != toBool($diff['enable']))
                return 'Parameter ' . toBool($diff['enable']) ? 'aktiviert' : 'deaktiviert';

            $master_set = [
                'value_int' => $diff['m_int'],
                'value_double' => $diff['m_double'],
                'value_string' => $diff['m_string'],
                'value_bool' => $diff['m_bool']
            ];
            $master_value = $this->GetValue_odx01($master_set, $paramSet, $master_type);

            if ($odx2 && ($value == $master_value)) {
                if ($diff['c_value'] != $diff['m_value'])
                    return "hex Werte unterschiedlich";
            }

            return $master_value;
        }
        return "";
    }

    // ===============================================================================================
    function GetMasterValueIcon($value_id, $paramSet, $value, $odx2) {
        if (isset ($this->DB_RevisionMap['diff'][$value_id])) {
            $master_val = $this->GetMasterValue($value_id, $paramSet, $value, $odx2);
            if (isset ($master_val))
                return '<span class="ttip ne">&ne;<span class="rtiptext">Orginal: ' . $master_val . '</span></span>';
        }
        return "";
    }
    /*
  // ===============================================================================================
  function QueryVariantPerSearch($suchtext)
  {
    $sucheFZ = false;
    $sucheVAR = false;
    $where = null;
    $sucheNach = &$this->S_SearchFor;
    $sucheNach = '';
    $suchtext = strtoupper($suchtext);
    $ret_tree = false;

    if (substr($suchtext, 0, 1) == 'W') {
      $sucheFZ = true;
      $sucheNach = 'VIN';
      $where = ['vin', 'like', "$suchtext%"];
    } else
      if (substr($suchtext, 0, 2) == 'BN') {
        $sucheFZ = true;
        $sucheNach = 'Kennzeichen';
        if (preg_match('/^BN[- ](P[A-Z*?]?)[- ]?([0-9][0-9*?]{0,3}E?)$/', $suchtext, $treffer)) {
          $t1 = $treffer[1];
          $t2 = $treffer[2];

          $where = ['code', 'like', "BN_{$t1}_{$t2}%"];
        }
      } else
        if (substr($suchtext, 0, 1) == 'P') {
          $sucheFZ = true;
          $sucheNach = 'Kennzeichen';
          if (preg_match('/^(P[A-Z*?]?)[- ]?([0-9][0-9*?]{0,3}E?)$/', $suchtext, $treffer)) {
            $t1 = $treffer[1];
            $t2 = $treffer[2];

            $where = ['code', 'like', "BN_{$t1}_{$t2}%"];
          }
        } else {
          $type = substr($suchtext, 0, 3);

          if (in_array($type, $this->DB_allVariantTypes)) {
            $sucheNach = 'Konfiguration';

            $sucheVAR = true;
            $where = ['windchill_variant_name', '~*', "$suchtext*"];
//                 $where  = ['penta_number', '~*', "$suchtext*"];
          }
        }


    if (empty ($sucheNach) && preg_match('/^[*?]?([0-9][0-9*?]*)$/', $suchtext, $match)) {
      $sucheFZ = true;
      $sucheNach = 'VIN';
      $where = ['vin', 'like', "%{$match[1]}"];
    }

    if (empty ($where)) {
      $this->suchFehler = "keine gültige Eingabe!";
      false;
    }


    if ($where[1] == 'like')
      $where[2] = str_replace(['?', '*'], ['_', '%'], $where[2]);

    if ($where[1] == '~*')
      $where[2] = str_replace(['?', '*'], ['.', '.*'], $where[2]);

    if ($sucheFZ) {
      $sql = $this->vehiclesPtr->newQuery()
          ->join('vehicle_variants', 'vehicle_variants.vehicle_variant_id=vehicles.vehicle_variant')
          ->join('penta_numbers', 'using (penta_number_id)')
          ->where($where[0], $where[1], $where[2])
          ->orderBy('windchill_variant_name')
          ->orderBy($where[0]);

      $result = $sql->get('vehicle_id,vehicle_variant,code,vin,windchill_variant_name,vehicles.penta_number_id,penta_config_id');

      if (count($result) > 1) {
        foreach ($result as &$set) {
          $config_name = $set['windchill_variant_name'];
          $vehicle_id = $set['vehicle_id'];

          if (!isset($ret_tree[$config_name]))
            $ret_tree[$config_name] = [];
          $ret_tree[$config_name][$vehicle_id] = $set;
        }
      } else if (count($result) == 1) {
        if (isset ($GLOBALS['VERBOSE'])) {
          echo "\n<!-- HOLLA\n";
          echo "\n-->\n";
        }

        $combi_id = "{$result[0]['vehicle_variant']}:{$result[0]['penta_config_id']}";
        $ret_tree[$combi_id] = $result[0]['windchill_variant_name'];
      }
    } else if ($sucheVAR) {
      $sql = "select vehicle_variant_id,windchill_variant_name,penta_number_id,penta_config_id " .
          "from vehicle_variants " .
          "left join penta_numbers using (vehicle_variant_id) " .
          "where windchill_variant_name ~* '{$where[2]}' " .
          "order by windchill_variant_name";

      $query = $this->vehicleVariantsPtr->newQuery();
      if ($query->query($sql)) {
        $result = $query->fetchAll();
      }

      foreach ($result as &$set) {
        $variant_id = $set['vehicle_variant_id'];
        $penta_id = safe_val($set, 'penta_config_id', 0);
        $combi_id = "$variant_id:$penta_id";
        $config_name = $set['windchill_variant_name'];

        $ret_tree[$combi_id] = $config_name;
      }
    }

    // keine Daten sind halt keine Daten, also abruch!
    if (!$result || (count($result) == 0)) {
      $this->suchFehler = "keine Daten gefunden!";
      return false;
    }


    // Suchergebnis-Liste nur wenn mehrere Varianten zur auswahl stehen
    if (isset ($_GET['term'])) {
      if ($sucheFZ) {
        if ($sucheNach == 'VIN') $cleaned_result = array_combine(array_column($result, "vehicle_id"), array_column($result, "vin"));
        else $cleaned_result = array_combine(array_column($result, "vehicle_id"), array_column($result, "code"));
      } else if ($sucheVAR)
        $cleaned_result = array_combine(array_column($result, "penta_number_id"), array_column($result, "penta_number"));
      echo json_encode($cleaned_result);
      exit;
    }

    $this->suchMeldung = (count($ret_tree) > 1)
        ? "Suche nach $sucheNach ergab mehrere Treffer"
        : "Suche nach $sucheNach erfolgereich!";

    return $ret_tree;
  }
*/
    /*
  // ===============================================================================================
  function QueryVariantProperties($combi_id)
  {
    if (!isset($this->DB_allVariants[$combi_id]))
      return false;

    $this->DB_VariantSet = $this->DB_allVariants[$combi_id];
    $this->m_WindchillId = $this->DB_VariantSet ['v_id'];

    if (isset ($this->DB_VariantSet['subvarianten'])) {
      $this->masterID = $combi_id;
    } else
      if (isset ($this->DB_VariantSet['master'])) {
        $this->masterID = $this->DB_VariantSet['master'];
      }


    if ($this->masterID) {
      $liste = $this->DB_allVariants[$this->masterID]['subvarianten'];
      foreach ($liste as $pid) {
        $sub = &$this->DB_allVariants[$pid];
        $sub['penta_part_string'] = $this->MakePartString($sub['p_parts']);
      }
    }

    $this->DB_VariantSet['windchill_part_string'] = $this->MakePartString($this->DB_VariantSet['v_parts']);
    $this->DB_VariantSet['penta_part_string'] = $this->MakePartString($this->DB_VariantSet['p_parts']);
  }
*/
    /*
  // ===============================================================================================
  function MakePartString($idListe)
  {
    $result = "";
    $idListe = substr($idListe, 1, -1);
    if (empty($idListe))
      return "";

    $ar = explode(',', $idListe);
    foreach ($ar as $i => $pid) {
      $ar[$i] = $this->allParts[$pid]['name'];
    }
    return implode(', ', $ar);
  }
*/
    // ===============================================================================================
    function QueryRevisions($ecu_id) {
        $query = $this->ecuPtr->newQuery('ecu_revisions')->where('ecu_id', '=', $ecu_id)->orderBy('ecu_revision_id', 'desc');
        $result = $query->get('*', 'ecu_revision_id');
        return $result;
    }

    // ===============================================================================================
    function QueryParameters($ecu, $revision, $pset_id = 0) {
        $this->S_Overview = null;
        $query = $this->ecuPtr->newQuery('ecu_parameter_sets')
            ->join('ecu_parameters',
                'ecu_parameter_sets.ecu_parameter_set_id = ecu_parameters.ecu_parameter_set_id', 'left outer join')
            ->join('ecu_tag_configuration',
                'ecu_tag_configuration.ecu_parameter_set_id  =  ecu_parameter_sets.ecu_parameter_set_id', 'left outer join');

        $query = $query->where('ecu_parameters.ecu_id', '=', $ecu)
            ->where('ecu_tag_configuration.ecu_revision_id', '=', $revision);

        if ($pset_id)
            $query = $query->where('ecu_parameter_sets.ecu_parameter_set_id', '=', $pset_id);

        $query = $query->orderBy('ecu_parameters.order');
        $query = $query->orderBy('ecu_parameter_sets.ecu_parameter_set_id');
        return $this->ArrangeParameterSet($query->get('*'));
    }

    // ===============================================================================================
    function ArrangeParameterSet($allTags) {
        $resultSet = [];
        foreach ($allTags as $n => $set) {
            $ps_id = $set['ecu_parameter_set_id'];
            $tag = $set['tag'];
            $pid = $set['ecu_parameter_id'];
            if (!isset($resultSet[$ps_id]))
                $resultSet[$ps_id] = [
                    'param_id' => $pid,
                    'pset_id' => $ps_id,
                    'type_id' => $set['type_id'],
                    'comment' => $set['comment'],
                    'ecu' => $set['ecu_id'],
                    'odx_name' => $set['odx_name'],
//                'name'          => $set['odx_name'],
                    'order' => $set['order'],
                    'unit_id' => $set['unit_id'],
                    'use_in_odx01' => $set['use_in_old_format'],
                ];

            $newtags = [
                'tag_value' => $set['tag_value'],
                'timestamp' => $set['timestamp'],
            ];

            if (isset($resultSet[$ps_id]['tags'][$tag])
                && ($resultSet[$ps_id]['tags'][$tag]['timestamp'] > $newtags['timestamp']))
                continue;

            $resultSet[$ps_id]['tags'][$tag] = $newtags;
            if ($tag == 'id')
                $resultSet[$ps_id]['name'] = $set['tag_value'];
        }

        return $resultSet;
    }

    // ===============================================================================================
    function QueryParamNames() {
        $sql = $this->ecuPtr->newQuery('ecu_tag_configuration')->where('tag', '=', 'id')->orderBy('tag_value');
        return $sql->get('DISTINCT tag_value');
    }

    // ==============================================================================================
    function HandleAccessCollision($ecuDevice) {
        if ($this->odxAccessMutex['now'] > $this->odxAccessMutex['$this->odxAccessMutex']) {
            $ecuDevice = ($ecuDevice) ? $ecuDevice : 'NULL';
            $timeout_s = self::ODX_ACCESS_TIMOUT_S;
            $user_id = $_SESSION['sts_userid'];
            $sql = "update sts_privilegs set " .
                "user_id=$user_id, " .
                "ecu_id=$ecuDevice, " .
                "timeout_access=current_timestamp + interval '$timeout_s second' " .
                "where sts_privileg_id={$this->odxAccessMutex['sts_privileg_id']}";

            $res = $this->ecuPtr->newQuery('sts_privilegs')->query($sql);
            return null;
        }

    }

    // ==============================================================================================
    function CreateDummyValues($ecu, $revision, $variant_id) {
        $postvals = ['used' => [], 'value' => []];
        $loret_ipsum = "Beware the Jabberwock, my son! The jaws that bite, the claws that catch! Beware the Jubjub bird, and shun The frumious Bandersnatch!";

        $activeValues = &$postvals['active'];
        $boolValues = &$postvals['bool'];
        $editValues = &$postvals['value'];
        $usedValues = &$postvals['used'];

        if (empty($this->DB_Parameters))
            $this->DB_Parameters = $this->QueryParameters($ecu, $revision);

        foreach ($this->DB_Parameters as $pset_id => $et) {
            $used = false;

            if ($pset_id <= 2)
                continue;

            $param_id = $et['param_id'];

            switch ($this->m_OdxVerShow) {
                case 1:
                    $used = toBool($et['use_in_odx01']);
                    if (!$used)
                        break;


                    switch ($et['type_id']) {
                        case self::odx01_int:
                            $editValues[$param_id] = rand(2, 42);
                            break;

                        case self::odx01_float:
                            $editValues[$param_id] = 39.99;
                            break;

                        case self::odx01_string:
                            $editValues[$param_id] = $loret_ipsum;
                            break;

                        case self::odx01_bool:
                            $editValues[$param_id] = rand(0, 1);
                            if ($editValues[$param_id])
                                $boolValues[$param_id] = '1';
                            break;
                    }
                    break;

                case 2:
//                     $used = toBool ($et['use_in_odx2']);
//                     if (!$used)
//                         break;

                    $deleted = safe_tag_value($et['tags'], 'deleted', null);
                    if ($deleted && (stripos($deleted, 'odx.sts.02') !== false))
                        continue;

                    $type = safe_tag_value($et['tags'], 'type', '');
                    $size = safe_tag_value($et['tags'], 'byteCount', 0);
                    if (empty ($type) || ($size == 0))
                        continue;

                    switch ($type) {
                        case 'ascii':
                            $editValues[$param_id] = substr($loret_ipsum, 0, $size);
                            break;

                        case 'blob':
                            $editValues[$param_id] = '0x';
                            for ($i = 0; $i < $size; $i++)
                                $editValues[$param_id] .= sprintf("%02x", rand(0, 255));
                            break;

                        case 'signed':
                            $editValues[$param_id] = -rand(2, 255);
                            break;

                        case 'unsigned':
                            $editValues[$param_id] = rand(2, 255);
                            break;

                    }
                    break;
            }
            $activeValues[$param_id] = 1;
            $usedValues[$param_id] = 1;
        }

        $this->Save_Variant_EcuValues($postvals, $variant_id, 0);
    }

    // ==============================================================================================
    function GetOdxAccess($ecuDevice) {
        $sql = "select sts_privilegs.*, users.fname, users.lname, current_timestamp + interval '0 second' as now "
            . "from sts_privilegs join users on user_id=id "
            . "where context='mutex_odx_creator'";

        $query = $this->ecuPtr->newQuery('sts_privilegs');
        $res = $query->query($sql);

        if ($res)
            $this->odxAccessMutex = $query->fetchArray();

        if ($this->odxAccessMutex) {
            if (($this->odxAccessMutex['user_id'] != $_SESSION['sts_userid'])
                && ($this->odxAccessMutex['now'] <= $this->odxAccessMutex['timeout_access'])) {
//                     return $this->SetError (ERROR_ODX_CREATOR_LOCKED);
                $this->odxAccessError = ERROR_ODX_CREATOR_GET_LOCK;
                return $this->odxAccessError;
            }

            $ecuDevice = ($ecuDevice) ? $ecuDevice : 'NULL';
            $timeout_s = self::ODX_ACCESS_TIMOUT_S;
            $user_id = $_SESSION['sts_userid'];
            $sql = "update sts_privilegs set " .
                "user_id=$user_id, " .
                "ecu_id=$ecuDevice, " .
                "timeout_access=localtimestamp+ interval '$timeout_s second' " .
                "where sts_privileg_id={$this->odxAccessMutex['sts_privileg_id']}";

            $res = $this->ecuPtr->newQuery('sts_privilegs')->query($sql);
            return null;


            return $this->HandleAccessCollision($ecuDevice);
            return null;
        } else {
            $with_ecu_id = ($ecuDevice) ? 'ecu_id,' : "";
            $ecu_id_val = ($ecuDevice) ? "$ecuDevice," : "";
            $timout_s = self::ODX_ACCESS_TIMOUT_S;
            $sql = "insert into sts_privilegs (user_id, context, {$with_ecu_id} is_owner, allow_write, timeout_access) "
                . "values ({$_SESSION['sts_userid']}, 'mutex_odx_creator', $ecu_id_val 't', 't', current_timestamp + interval '{$timout_s} second')";


            $query = $this->ecuPtr->newQuery('sts_privilegs');
            $query->query($sql);

//             if ($ret)
//                 return null;
        }
        //return $this->odxAccessError;
        return null;
    }


    // ==============================================================================================
    function SaveTmpPentaVariant() {
        if ($this->GetOdxAccess($this->S_currentEcu))
            return;

        $set = $this->vehicleVariantsPtr->newQuery()
            ->join('penta_numbers', 'using (vehicle_variant_id)')
            ->where('windchill_variant_name', '=', self::VARIANT_TESTVEHICLE)
            ->get('vehicle_variant_id, penta_number_id');

        if (!$set)
            return;

        $this->test_variant_id = $set[0]['vehicle_variant_id'];
        $this->test_penta_id = $set[0]['penta_number_id'];

        $rev_id = $this->S_ecuVersion['ecu_revision_id'];

        $updateCols = ['vehicle_variant', 'penta_number_id'];
        $updateVals = [$this->test_variant_id, $this->test_penta_id];

        $query = $this->vehiclesPtr->newQuery()
            ->where('vin', '=', self::VIN_STS_TESTVEHICLE)
            ->update($updateCols, $updateVals);

        $query = $this->vehiclesPtr->newQuery('variant_ecu_revision_mapping')
            ->where('variant_id', '=', $this->test_variant_id)
            ->where('ecu_id', '=', $this->S_currentEcu);

        $res = $query->get('*');
        if ($res) {
            if (($res[0]['rev_id'] != $rev_id) || ($res[0]['odx_download_mode'] != 2)) {
                $updateCols = ['rev_id', 'odx_download_mode', 'timestamp_last_change'];
                $updateVals = [$this->S_ecuVersion['ecu_revision_id'], 2, 'now()'];
                $query = $this->vehiclesPtr->newQuery('variant_ecu_revision_mapping')
                    ->where('variant_id', '=', $this->test_variant_id)
                    ->where('ecu_id', '=', $this->S_currentEcu)
                    ->update($updateCols, $updateVals);
            }
        } else {
            $insert = ['variant_id' => $this->test_variant_id,
                'penta_id' => 0,
                'ecu_id' => $this->S_currentEcu,
                'rev_id' => $rev_id,
                'odx_download_mode' => 2];

            $query = $this->vehiclesPtr
                ->newQuery('variant_ecu_revision_mapping')
                ->insert($insert);
        }
        $this->leitWartePtr->query('DELETE FROM vehicle_variant_data WHERE vehicle_variant_id =1');
        $combi_id = "{$this->test_variant_id}:0";
        $this->DB_VariantSet = $this->DB_allVariants[$combi_id];
        $this->CreateDummyValues($this->S_currentEcu, $rev_id, $this->test_variant_id);
        $this->DownloadOdx($this->test_variant_id, 0, $this->S_currentEcu);
    }
    // ===============================================================================================

    /**
     * DownloadOdx Erzeugung einer ODX-Datei mit der ausgeählten Konfiguration
     * @param int $variant_id Windchill Variante
     * @param int $penta_id Penta Variante (nur bei Ausnahmekonfigurationen)
     * @param int $ecu_id ECU Device
     * @param string $getWhat (zip/odx/view/dummy)
     */
    function DownloadOdx($variant_id, $penta_id, $ecu_id) //
    {
//    $GLOBALS['IS_DEBUGGING'] = true;
        if (!$this->DB_VariantSet || !$ecu_id)
            return;

        if ($this->GetOdxAccess($ecu_id))
            return;

        $getWhat = $GLOBALS['IS_DEBUGGING'] ? 'dummy' : 'view';
        $ecu = $this->allEcuNames[$ecu_id];
        $test_vin = self::VIN_STS_TESTVEHICLE;
        $update_cols = ['vehicle_variant', 'penta_number_id'];
        $update_vals = [$variant_id, $penta_id];
        $odx2 = ($this->m_OdxVerShow == 2) ? "_new" : "";
        $extension = ($getWhat == 'zip') ? 'zip' : 'odx';
        $this->odx_file = "$ecu$odx2.$extension";

        if ($getWhat == 'dummy') {
            $this->odx_content = self::ODX_DUMMY_CONTENT;
            return;
        }

        $result = $this->vehiclesPtr->newQuery()->where('vin', '=', $test_vin)->update($update_cols, $update_vals);

        if ($result) {
            $w = stream_get_wrappers();
            if (in_array('https', $w) && extension_loaded('openssl')) {
                /*$authinfo = "Authorization: Basic " . base64_encode("diagnose:Yd5kvBXAflS8f8S5Pxss");
        $context = stream_context_create(['http' => ['header' => $authinfo],
            'ssl' => ['verify_peer' => false,
                'verify_peer_name' => false]]);

        //$url = "https://217.31.88.170/diagnose_update/data/TestC2CBox0123456/$ecu.zip";
        //$url = "https://217.31.88.170/diagnose_update/data/TestC2CBox0123456/$ecu.zip";
        $url = "https://217.31.88.170/diagnose_update/getOdx_dbase.php?vin=$test_vin&ecu=$ecu&variant=$odx2&extension=$extension";
        $this->odx_content = file_get_contents($url, false, $context);*/

                $context = stream_context_create();
                $url = "http://odxcreator.local/diagnose_update/getOdx_dbase.php?vin=$test_vin&ecu=$ecu&variant=$odx2&extension=$extension";
                $this->odx_content = file_get_contents($url);
                $hdrLength = 'Content-Length';
                $hdrType = 'Content-Type';

                foreach ($http_response_header as $line) {
                    if (strcasecmp(substr($line, 0, strlen($hdrLength)), $hdrLength) == 0)
                        $this->odx_content_length = $line;
                    if (strcasecmp(substr($line, 0, strlen($hdrType)), $hdrType) == 0)
                        $this->odx_content_type = $line;
                }

                if ($getWhat == 'view')
                    return;


                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename="' . $this->odx_file . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header($this->odx_content_length);

                foreach ($http_response_header as $line) {
                    if (strcasecmp(substr($line, 0, strlen($hdrLength)), $hdrLength) == 0)
                        header($line);
                    if (strcasecmp(substr($line, 0, strlen($hdrType)), $hdrType) == 0)
                        header($line);
                }
                echo $this->odx_content;
                exit;
            }
        }
        return;
    }
    /*
  // ===============================================================================================
  function CopyVariantParameterData($srcCombiId, $dstListe, $listParamIds = true, $noMessage = false)
  {
    try {
      $copyAllValues = ($listParamIds === true);
      $copyValues = is_array($listParamIds) || $copyAllValues;
      $onlyVersion = ($copyValues === false) || ($copyValues === []);

      if (!$this->S_variantRev)
        return $this->SetError(STS_ERROR_PHP_ASSERTION, "S_variantRev=NULL");

      // 1. Init working data
      sscanf($srcCombiId, "%d:%d", $srcWindchill, $srcPentaId);

      $srcRev = $this->S_variantRev['ecu_revision_id'];
      $srcEcu = $this->S_variantRev['ecu_id'];

      if (count($dstListe) == 0)
        return;

      $mapUpdate = [];
      $mapAll = [];
      foreach ($dstListe as $combi_id) {
        if ($combi_id != $srcCombiId) {
          sscanf($combi_id, "%d:%d", $v_id, $p_id);
          $mapAll[$p_id] = $v_id;
        }
      }

      $mapInsert = $mapAll;


      // 2. get version information of source configuration
      $this->DB_RevisionMap = $this->QueryEcuRevisionFromPentaVariant($srcEcu, $srcWindchill, $srcPentaId);
      $src_vehicle_variant_id = $this->DB_RevisionMap['variant_id'];
      $src_overlayed_penta_id = $this->DB_RevisionMap['penta_id'];
      $src_odx_download_mode = $this->DB_RevisionMap['odx_download_mode'];
      $src_parameters_check_ok = $this->DB_RevisionMap['parameters_check_ok'];
      $src_parameters_released = $this->DB_RevisionMap['parameters_released'];
      $ecu_variant_keys = [];

      foreach ($mapInsert as $penta_id => $variant_id) {
        $sql = "
                    select * from variant_ecu_revision_mapping
                    where variant_id  = {$variant_id}
                        and ecu_id    = {$srcEcu}
                        and (penta_id = {$penta_id} or penta_id=0)
                    order by penta_id desc
                    limit 1";

        $query = $this->ecuPtr->newQuery();
        $result = $query->query($sql);
        if ($result && ($set = $this->ecuPtr->fetchArray())) {
          $ecu_variant_keys[$penta_id] = ['variant_id' => $set['variant_id'], 'penta_id' => $set['penta_id']];

          unset($mapInsert[$penta_id]);
          $mapUpdate[$penta_id] = $variant_id;
        }
      }


      // Insert Revision to WIndchill/Penta Variant
      $revisionMapData = [
          'variant_id' => 0,
          'penta_id' => 0,
          'ecu_id' => $srcEcu,
          'rev_id' => $srcRev,
          'odx_download_mode' => $src_odx_download_mode,
          'parameters_check_ok' => $src_parameters_check_ok,
          'parameters_released' => $src_parameters_released,
          'timestamp_last_change' => 'now()',
          'timestamp_copy' => 'now()',
      ];

      $revisionMapData['copy_from_variant_id'] = $copyValues ? $srcWindchill : null;
      $revisionMapData['copy_from_penta_id'] = $copyValues ? $srcPentaId : null;

      foreach ($mapInsert as $penta_id => $variant_id) {
        $revisionMapData['variant_id'] = $variant_id;
        $ecu_variant_keys[$penta_id] = [
            'variant_id' => $variant_id,
            'penta_id' => 0
        ];

        $result = $this->ecuPtr->newQuery('variant_ecu_revision_mapping')->insert($revisionMapData);
        if (!$result)
          return $this->SetError(STS_ERROR_DB_INSERT, $insertCol);
      }

      if (count($mapUpdate)) {
        unset ($revisionMapData['variant_id']);
        unset ($revisionMapData['penta_id']);

        foreach ($mapUpdate as $penta_id => $variant_id) {
          $result = $this->ecuPtr->newQuery('variant_ecu_revision_mapping')
              ->where('variant_id', '=', $ecu_variant_keys[$penta_id]['variant_id'])
              ->where('penta_id', '=', $ecu_variant_keys[$penta_id]['penta_id'])
              ->where('ecu_id', '=', $srcEcu)
              ->update_assoc($revisionMapData);
        }
      }


      if (true) //$copyValues)
      {
        $list_combi_ids = [];

        if ($copyAllValues) {
          // 3. create list of all used ecu_parameters
          $qry = $this->variantDataPtr->newQuery('ecu_parameters')
              ->where('ecu_parameters.ecu_id', '=', $srcEcu);

          if ($onlyVersion)
            $qry = $qry->where('ecu_parameters.ecu_parameter_set_id', 'in', [1, 2]);

          $parameters = $qry->getVals('ecu_parameter_id');
        } else {
          $parameters = $listParamIds;
        }

        if ($parameters) {
          $str_parameters = implode(',', $parameters);

          foreach ($mapAll as $penta_id => $variant_id) {
            if (!isset ($ecu_variant_keys[$penta_id]))
              return $this->SetError(STS_ERROR_PHP_ASSERTION, "ecu_variant_keys[$penta_id]==null");

            $combi = "{$ecu_variant_keys[$penta_id]['variant_id']}:{$ecu_variant_keys[$penta_id]['penta_id']}";
            if (!isset ($list_combi_ids[$combi]))
              $list_combi_ids[$combi] = [
                  'variant_id' => $ecu_variant_keys[$penta_id]['variant_id'],
                  'penta_id' => $ecu_variant_keys[$penta_id]['penta_id']
              ];
          }


          $this->ClearWBackup_ParameterValues('copy', array_keys($list_combi_ids), $parameters);

          foreach ($list_combi_ids as $ids) {
            $sql = "insert into vehicle_variant_data\n"
                . "      (ecu_parameter_id, value_int, value_double, value_string, value_bool, tag_disabled, value, vehicle_variant_id, overlayed_penta_id, set_by, sw_preset_type)\n"
                . "select ecu_parameter_id, value_int, value_double, value_string, value_bool, tag_disabled, value, {$ids['variant_id']}, {$ids['penta_id']}, {$_SESSION['sts_userid']}, sw_preset_type\n"
                . "from vehicle_variant_data\n"
                . "    where vehicle_variant_id = {$src_vehicle_variant_id}\n"
                . "    and overlayed_penta_id = {$src_overlayed_penta_id}\n"
                . "    and ecu_parameter_id in ({$str_parameters})";


            $result = $this->variantDataPtr->newQuery()->query($sql);
          }
        }
      }

      if ($noMessage)
        return STS_MESSAGE_SUCCEED;

      $numCopied = count($mapAll);
      return $this->SetMessage(STS_MESSAGE_SUCCEED, "$numCopied Konfigurationen erfolgreich kopiert.");
    } catch (Exception $e) {
      return $this->SetError(STS_ERROR_PHP_EXCEPTION, $e->getMessage());
    }
  }
*/
    /*
  // ===============================================================================================
  function CopyGlobalParameters($src_variant_id, $dst_variant_id)
  {
    $allVarIds = array_column($this->DB_Variables, 'ecu_parameter_id');
    $allVarStr = implode(_cma_, $allVarIds);

    $res = $this->variantDataPtr->newQuery()
        ->where('vehicle_variant_id', '=', $dst_variant_id)
        ->where('ecu_parameter_id', 'IN', $allVarIds)
        ->delete();

    $insert_cols = "ecu_parameter_id, value_int, value_double, value_string, value_bool, tag_disabled, value, overlayed_penta_id";

    $sql = "
            INSERT INTO vehicle_variant_data (vehicle_variant_id, $insert_cols)
            SELECT $dst_variant_id AS vid, $insert_cols
            FROM vehicle_variant_data
            WHERE ecu_parameter_id IN ($allVarStr)
            AND vehicle_variant_id = $src_variant_id";

    $res = $this->variantDataPtr->newQuery()->query($sql);
  }
*/
    /*
  // ===============================================================================================
  function Set_Variant_EcuRevision($rev_id)
  {
    if ($this->S_variantEcu && $this->m_WindchillId) {
      $ql = $this->ecuPtr->newQuery('variant_ecu_revision_mapping')
          ->where('variant_id', '=', $this->m_WindchillId)
          ->where('penta_id', 'in', [$this->m_PentaConfigId, 0])
          ->where('ecu_id', '=', $this->S_variantEcu)
          ->orderBy('penta_id', 'desc');

      $existing = $ql->get('rev_id,penta_id');
      if ($existing) {
        if (!$rev_id) $rev_id = null;
        $found_penta = $existing[0]['penta_id'];
        $update_cols = ['rev_id'];
        $update_vals = [$rev_id];
        $ql = $this->ecuPtr->newQuery('variant_ecu_revision_mapping')
            ->where('variant_id', '=', $this->m_WindchillId)
            ->where('penta_id', '=', $found_penta)
            ->where('ecu_id', '=', $this->S_variantEcu);

        $result = $ql->update($update_cols, $update_vals);
      } else {
        if (!$rev_id)
          return;

        $insert = ['variant_id' => $this->m_WindchillId,
            'penta_id' => 0,
            'ecu_id' => $this->S_variantEcu,
            'rev_id' => $rev_id
        ];
        $ql = $this->ecuPtr->newQuery('variant_ecu_revision_mapping');
        $result = $ql->insert($insert);
      }
    }

    $this->S_variantRev = safe_val($this->S_Revisions, $rev_id, null);
    if ($this->S_variantRev) {
      $this->S_ecuVersion = $this->S_variantRev;
      if (!$this->S_ecuVersion['released']) {
        $this->SetWarning(WARNING_ECU_SW_NOT_RELEASED);
//                 $this->S_ecuVersion['sw_profile_ok'];
      }
      $this->DB_Parameters = $this->QueryParameters($this->S_variantEcu, $rev_id);
    }

    $this->Save_Initial_EcuValues($this->m_WindchillId, $found_penta);
  }
*/
    // ===============================================================================================
    function TabChange($leaveTab, $enterTab) {
        switch ($leaveTab) {
            /*      case self::ID_TAB_Varianten:
        break;*/
//             case self::ID_TAB_Fahrzeugsuche:                break;
            case self::ID_TAB_ECU_Versionen:
                break;
//--------------2019---------------
            // case self::ID_TAB_Validate:
            //break;
//---------------------------------
            /*      case self::ID_TAB_CAN_Matrix:
        break;
      case self::ID_TAB_Pentavarianten:
        break;
      case self::ID_TAB_Freigabe:
        break;
      case self::ID_TAB_Verantwortliche:
        break;
      case self::ID_TAB_Parts:
        break;
      case self::ID_TAB_PartsParameter:
        break;*/
        }

        switch ($enterTab) {
            /*      case self::ID_TAB_Varianten:
        break;
//             case self::ID_TAB_Fahrzeugsuche:                break;*/
            case self::ID_TAB_ECU_Versionen:
                $this->loadFromVariant = (($leaveTab == self::ID_TAB_Varianten) && ($this->S_ParamType == self::ID_GeraeteParameter));
                break;
//------------------------2019----------
            // case self::ID_TAB_Validate:
            //  $this->loadFromVariant = (($leaveTab == self::ID_TAB_Varianten) && ($this->S_ParamType == self::ID_GeraeteParameter));
            // break;
//------------------------------------
            /*      case self::ID_TAB_CAN_Matrix:
        break;
      case self::ID_TAB_Pentavarianten:
        break;
      case self::ID_TAB_Freigabe:
        break;
      case self::ID_TAB_Verantwortliche:
        break;
      case self::ID_TAB_Parts:
        break;
      case self::ID_TAB_PartsParameter:
        break;*/
        }
        $this->m_EditMode = 0;
        return true;
    }

    // ===============================================================================================
    function Execute() {
        parent::Execute();

        if (isset($_REQUEST['tab'])) {
            if (($this->S_Tab != $_REQUEST['tab']) && $this->TabChange($this->S_Tab, $_REQUEST['tab'])) {
                $this->S_Tab = $_REQUEST['tab'];
            }
        }

        if (isset($_REQUEST['command'])) {
            switch ($_REQUEST['command']) {
                case 'start_privileges':
                    $this->m_EditMode = self::EDIT_Privileges;
                    break;

                case 'save_privileges':
                    $this->SavePrivileges($this->S_Tab, $this->S_ParamType);
                case 'cancel_privileges':
                    $this->m_EditMode = 0;
            }
        }


        switch ($this->S_Tab) {
            /*      case self::ID_TAB_Varianten:
        $this->ExecuteTAB_Varianten();
        break;*/
//             case self::ID_TAB_Fahrzeugsuche:                break;
            case self::ID_TAB_ECU_Versionen:
                $this->ExecuteTAB_ECUVersionen();
                break;
//------------------------2019--------------------
            //case self::ID_TAB_Validate:
            //  $this->validateForm();
            // break;
//------------------------------------------------
            /*        break;
      case self::ID_TAB_SW_ODX_Uebersicht:
        $this->ExecuteTAB_SW_ODX_Uebersicht();
        break;
        break;
      case self::ID_TAB_CAN_Matrix:
        break;
      case self::ID_TAB_Pentavarianten:
        break;
      case self::ID_TAB_Freigabe:
        break;
      case self::ID_TAB_Verantwortliche:
        break;
      case self::ID_TAB_Parts:
        break;
      case self::ID_TAB_PartsParameter:
        break;*/
        }

        if (isset ($_REQUEST['odxVersion'][$this->S_currentEcu]))
            $this->m_OdxVerShow = $_REQUEST['odxVersion'][$this->S_currentEcu];
    }
    /*
  // ===============================================================================================
  function ExecuteTAB_VariantInfo($command)
  {
    switch ($command) {
      case 'save_variant':
        $this->SaveVariant();
        $this->m_EditMode = 0;
        break;

      case 'cancel_variant':
        $this->m_EditMode = 0;
        break;

      case 'new_variant':
        $this->m_EditMode = self::EDIT_NewVariant;
        break;

      case 'copy_variant':
        $this->m_EditMode = self::EDIT_CopyVariant;
        // $this->CopyVariant ();
        break;

      case 'delete_variant':
        $this->DeleteCurrentVariant();
        break;
    }
  }
*/
    /*
  // ===============================================================================================
  function ExecuteTAB_VariantCocCommand($command)
  {
    switch ($command) {
      case 'edit':
        $this->m_EditMode = self::EDIT_Variant;
        break;

      case 'save':
        $this->Save_Variant_CocData();
        $this->m_EditMode = 0;
        break;

      case 'cancel':
        $this->m_EditMode = 0;
        break;

      case 'startcopycoc':
        $this->m_EditMode = self::EDIT_DistributeCocValues;
        $this->IndexWindchill = [];
        $this->WindchillEcuRev = [];
        break;

      case 'copyparams':
        $this->CopyVariantCocData();
        $this->m_EditMode = 0;
        break;

      case 'downloadPdf':
        $this->CocTestdruck();
        break;

      case 'getcoc':
        $this->GetCoc();
        break;

    }
  }
*/
    /*
  // ===============================================================================================
  function ExecuteTAB_VariantVariablesCommand($command)
  {
    switch ($command) {
      case 'new_variable':
        $this->m_EditMode = self::EDIT_NewGlobalVariable;
        break;

      case 'save_variable':
        $this->ExecuteVariable();
        break;

      case 'startcopyparams':
        $this->m_EditMode = self::EDIT_CopyGlobalVariable;
        break;

      case 'copyparams':
//                 $this->ExecuteVariable ();
        $this->CopyGlobalVariable($this->S_CombiID, $_REQUEST['copyTo']);
        break;

    }
  }
*/
    /*
  // ===============================================================================================
  function ExecuteTAB_VariantEcuDataCommand($command)
  {
    switch ($command) {
      case 'edit':
        $this->m_EditMode = self::EDIT_VariantData;
        break;

      case 'save':
        $windchill_id = $this->DB_VariantSet['v_id'];
        $overlayed_penta_id = $this->DB_RevisionMap['penta_id'];
        $this->Save_Variant_EcuValues($_POST, $windchill_id, $overlayed_penta_id);
        $this->m_EditMode = 0;
        break;

      case 'cancel':
        $this->m_EditMode = 0;
        break;

      case 'savePartMapping':
        $this->SaveVariantPartMapping();
        break;

      case 'set_revision';
        $rev_id = safe_val($_REQUEST, 'sw_rev', 0);
        $this->Set_Variant_EcuRevision($rev_id);
        break;

      case 'startcopyparams':

        $this->m_EditMode = self::EDIT_CopyVariantEcuData;
        $this->IndexWindchill = [];
        $this->WindchillEcuRev = [];
        break;

      case 'copyparams':
        if (isset ($_REQUEST['only_sw'])) {
          $copyValues = false;
        } else {
          if (substr($_REQUEST['copy_param_list'], -1) == ',')
            $paramlist = substr($_REQUEST['copy_param_list'], 0, -1);
          else
            $paramlist = $_REQUEST['copy_param_list'];

          $copyValues = explode(',', preg_replace('[^0-9,]', '', $paramlist));
        }

        $this->CopyVariantParameterData($this->S_CombiID, $_REQUEST['copyTo'], $copyValues);
        $this->m_EditMode = 0;
        break;

      case 'odxdownload':
        if ($this->DB_VariantSet) {
          $v_id = $this->DB_VariantSet['v_id'];
          $p_id = $this->DB_VariantSet['p_id'];
          $ecu_id = $this->S_variantEcu;
          $this->DownloadOdx($v_id, $p_id, $ecu_id);
        }
        break;
      case 'consolidate':
        if ($this->DB_RevisionMap) {
          $map_id = intVal($this->DB_RevisionMap['id']);
          if ($map_id) {
            $sql = "update variant_ecu_revision_mapping\n"
                . "set copy_from_variant_id=NULL, copy_from_penta_id=NULL\n"
                . "where id=$map_id";

            if ($this->ecuPtr->newQuery()->query($sql)) {
              $this->DB_RevisionMap['copy_from_variant_id'] = null;
              $this->DB_RevisionMap['copy_from_penta_id'] = null;
            }
          }
        }
        break;
    }
  }
*/

    // ===============================================================================================
    function ExecuteTAB_ECUVersionen() {
        $ecu = $rev = '';
        if (isset ($_REQUEST['ecuVersion']['selectedEcu'])) {
            $ecu = $this->S_currentEcu = intval($_REQUEST['ecuVersion']['selectedEcu']);
        } else if ($this->loadFromVariant) {
            $ecu = $this->S_variantEcu;
        }


        if ($ecu)
            $this->S_currentEcu = $ecu;


        if ($this->S_currentEcu) {
            $this->LoadCharacterMapping($this->S_currentEcu);

            if (isset ($_REQUEST['odxVersion'][$this->S_currentEcu])) {
                $this->m_OdxVerShowTab[$this->S_currentEcu] = intval($_REQUEST['odxVersion'][$this->S_currentEcu]);
                //$this->m_EditMode = 0;
            }

            $this->m_OdxVerShow = &$this->m_OdxVerShowTab[$this->S_currentEcu];
        }

        if (isset($_REQUEST['command'])) {
            switch ($_REQUEST['command']) {
                case 'add':
                    $this->Adopt_ECUVersionEditSet(false);
                    $this->AddRows_ECUVersion();
                    return;

                case 'cancel':
                    $this->m_EditMode = 0;
                    $this->S_EditSet = null;
                    $this->CreateEditSet_ECUVersionen();
                    break;

                /*case 'copy':
          if ($this->S_ecuVersion) {
            $this->m_EditMode = self::EDIT_CopyEcuVersion;
          }
          break;*/

                case 'downloadOdx':
                    if ($this->S_currentEcu && $this->S_ecuVersion) {
                        $this->SaveTmpPentaVariant();
                    }
                    break;


                case 'edit':
                    if ($this->S_ecuVersion) {
                        $this->m_EditMode = self::EDIT_EcuVersion;
                    }
                    break;

                /* case 'new':
           $this->m_EditMode = self::EDIT_NewEcuVersion;
           $this->CreateEditSet_ECUVersionen();
           break;*/

                case 'refresh':
                    $this->Adopt_ECUVersionEditSet(false);
                    return;

                case 'save':
                    if ($this->S_ecuVersion) {

                        $this->Adopt_ECUVersionEditSet();
                        /*       if ($this->m_EditMode == self::EDIT_DistributeSWParameters) {
              $this->AddCopiedRows_ECUVersion();
            }*/
                        $this->AddCopiedRows_ECUVersion();
                        $result = $this->Check_ECUVersionEditSet();
                        if (!$result) {
                            $this->configError = ERROR_INVALID_ODX2_CONFIG;
//                         $this->SetError(ERROR_INVALID_ODX2_CONFIG, "", ERRORLEVEL_MESSAGE);
                        }

                        if (true) {
                            $this->SaveNewRevision();
                            $this->m_EditMode = 0;
                            $this->S_EditSet = null;
                            break;
                        }
                        return;
                    }
                    break;

                case 'start_copy_params':
                    $this->m_EditMode = self::EDIT_DistributeSWParameters;
                    break;

                case 'copy_params':
                    $this->DistributeParameters();
                    $this->m_EditMode = 0;
                    break;
                /*
              case 'delete_rev':
                $this->DeleteCurrentRevision();
                break;*/

            }
        }

        if ($this->S_currentEcu) {
            $ecu = $this->S_currentEcu;

            $this->S_Revisions = $this->QueryRevisions($ecu);
            $this->m_EcuBigEndian = toBool($this->allEcus[$ecu]['big_endian']);

            if (isset ($_REQUEST['ecuVersion']['ecu'][$ecu])) {
                $rev = $_REQUEST['ecuVersion']['ecu'][$ecu];
            } else if ($this->loadFromVariant) {
                $rev = $this->S_variantRev['ecu_revision_id'];
            }

            if ($rev) {
                $this->S_ecuVersion = $this->S_Revisions[$rev];
                $this->CreateEditSet_ECUVersionen();
            }
        } else {
            $this->S_Revisions = null;
            $this->S_ecuVersion = null;
            $this->S_dbParamDef = null;
        }

        if ($this->S_currentEcu) {
            $this->GetEnggPrivileges(self::PRIV_ECU_PROFILE, $this->S_currentEcu);
        }
    }
    /*
  // ===============================================================================================
  function ExecuteTAB_SW_ODX_Uebersicht()
  {
    $this->m_table = new AClass_StickyHeaderTable();
  }
*/
    /*
  // ===============================================================================================
  function Execute_Variant_Selectors()
  {
    $prev_id = $this->S_CombiID;

    if (isset ($_REQUEST['filter']['suchen']) || isset($_REQUEST['filter']['suche_ziel']) || isset ($_GET['term'])) {

      $suchText = $_REQUEST['filter']['suchtext'];
      // if (isset($_GET['term'])) {$suchText   = filter_var($_GET['term'],FILTER_SANITIZE_STRING);}
      $ergebnis = $this->QueryVariantPerSearch($suchText);

      if (isset ($_REQUEST['filter']['suchen'])) {
        if ($ergebnis) {
          if (count($ergebnis) == 1) {
            // Suchergebnis ist eindeutig: Variante übernehmen
            reset($ergebnis);
            $combi_id = key($ergebnis);
            $this->S_CombiID = $combi_id;
            $variant_set = &$this->DB_allVariants[$combi_id];

            $this->m_VariantType = substr($variant_set['v_name'], 0, 3);
          } else {
            $this->m_VariantType = 'suchliste';
          }
        }
        $this->S_SearchText = $suchText;
        $this->S_SearchList = $ergebnis;
      } else {
        $this->m_SearchDestText = $suchText;
        $this->m_SearchDest = $ergebnis;
        $this->m_EditMode = self::EDIT_CopyVariantEcuData;
      }
    } else {
      if (isset ($_REQUEST['filter']['variantType']))
        $this->m_VariantType = $_REQUEST['filter']['variantType'];

      $vt = $this->m_VariantType;

      if (isset ($_REQUEST['filter'][$vt]))
        $this->S_CombiID = $_REQUEST['filter'][$vt];

      if ($vt == 'suchliste')
        $this->S_SearchItem = $this->S_CombiID;

      if ($this->S_CombiID[0] == '#') {
        $this->S_SearchVehicle = substr($this->S_CombiID, 1);
        $set = $this->vehiclesPtr->newQuery()->join('penta_numbers', 'using(penta_number_id)')->where('vehicle_id', '=', $this->S_SearchVehicle)->getOne('vehicle_variant,penta_config_id');
        $this->S_CombiID = "{$set['vehicle_variant']}:{$set['penta_config_id']}";
      }
    }

    $this->m_VariantChanged = ($this->S_CombiID != $prev_id);


    if (isset ($_REQUEST['filter']['ecu'])) {
      $ecu = $_REQUEST['filter']['ecu'];
      $ecu_changed = ($this->S_variantEcu != $ecu);
      $this->S_currentEcu = $this->S_variantEcu = $ecu;
      $this->S_OdxVerEcu = toBool($this->allEcus[$ecu]['supports_odx02']) ? 2 : 1;
      if ($ecu_changed)
        $this->m_OdxVerShow = $this->S_OdxVerEcu;
    }

    if (isset ($_REQUEST['filter']['paramType'])) {
      $this->S_ParamType = $_REQUEST['filter']['paramType'];
    }

    if (isset ($_REQUEST['filter']['makro']))
      $this->S_currentVariable = $_REQUEST['filter']['makro'];
  }
*/
    /*
  // ===============================================================================================
  function ExecuteTAB_Varianten()
  {
    if (isset ($_REQUEST['command']) && ($_REQUEST['command'] == 'new_clear_variant')) {
      $this->S_CombiID = '';
      $this->S_ParamType = self::ID_Fahrzeuginfo;
      $this->m_EditMode = self::EDIT_NewVariant;
      return;
    }


    if (isset($_REQUEST['filter']) || isset($_GET['term']))
      $this->Execute_Variant_Selectors();

    if ($this->S_CombiID) {
      $this->QueryVariantProperties($this->S_CombiID);
    } else {
      $this->S_variantEcu = 0;
    }


    $this->GetEnggPrivileges(self::PRIV_VARIANT_EDIT);

    if ($this->DB_VariantSet) {
      $this->m_WindchillId = safe_val($this->DB_VariantSet, 'v_id', 0);
      $this->m_PentaConfigId = safe_val($this->DB_VariantSet, 'penta_config_id', 0);

      $this->DB_allRevisions = $this->QueryRevisionsFromPentaVariant($this->m_WindchillId, $this->m_PentaConfigId);

    }

    $overlayed_penta_id = $this->m_PentaConfigId; //$this->DB_VariantSet['p_id'];
    switch ($this->S_ParamType) {
      case self::ID_GeraeteParameter:
        $this->SetCurrentEcu($this->S_variantEcu);

        if (!($this->S_variantEcu && $this->S_variantRev)) break;

        $revision = $this->S_variantRev['ecu_revision_id'];
        $this->DB_Parameters = $this->QueryParameters($this->S_variantEcu, $revision);
        $overlayed_penta_id = $this->DB_RevisionMap['penta_id'];

      case self::ID_GlobaleParameter:
        $windchill_variant_id = $this->DB_VariantSet['v_id'];
        $this->DB_VariantEcuData = $this->QueryVariantEcuValues($windchill_variant_id, $overlayed_penta_id);
        $this->UpdateByOverwritingParts($this->DB_VariantEcuData, $this->S_variantEcu, $this->DB_VariantSet['v_parts'], $this->DB_VariantSet['p_parts']);
    }

    if ($this->S_ParamType != self::ID_GeraeteParameter)
      $this->S_currentEcu = 0;

    if (isset ($_REQUEST['command'])) {
      switch ($this->S_ParamType) {
        case self::ID_Fahrzeuginfo:
          $this->ExecuteTAB_VariantInfo($_REQUEST['command']);
          break;

        case self::ID_Fahrzeugeigenschaften:
          $this->ExecuteTAB_VariantCocCommand($_REQUEST['command']);
          break;

        case self::ID_GeraeteParameter:
          $this->ExecuteTAB_VariantEcuDataCommand($_REQUEST['command']);
          break;

        case self::ID_GlobaleParameter:
          $this->ExecuteTAB_VariantVariablesCommand($_REQUEST['command']);
          $this->DB_VariantEcuData = $this->QueryVariantEcuValues($windchill_variant_id, $overlayed_penta_id);
//                 $this->SetCurrentEcu ($this->S_variantEcu);
//                 $this->DB_Parameters        = $this->QueryParameters ($this->S_variantEcu, $revision);
//                 $_REQUEST = [];
//                 $this->ExecuteTAB_Varianten ();

          break;
      }
    }
  }
*/
    // ===============================================================================================
    function SetupHeaderFiles($displayheader) {
        //parent::SetupHeaderFiles($displayheader);

        $displayheader->enqueueStylesheet("parameterlist", "css/parameterlist.css");
//        $displayheader->enqueueStylesheet ("css-jquery-ui", "js/newjs/jquery-ui.css" );

        $subVariantOf = safe_val($this->DB_VariantSet, 'master', 0);

        $displayheader->enqueueJs("sts-jquery", "js/jquery-2.2.0.min.js");
        $displayheader->enqueueJs("sts-jquery-ui", "js/newjs/jquery-ui.min.js");
        $displayheader->enqueueJs("jquery-datepicker", "js/jquery.ui.datepicker-de.js");
        $displayheader->enqueueStylesheet("css-jquery-ui-struct", "js/newjs/jquery-ui.structure.css");
        $displayheader->enqueueStylesheet("css-jquery-ui-theme", "js/newjs/jquery-ui.theme.css");

        $displayheader->enqueueJs("parameterlist", "js/parameterlist.js");

        $scrollpos['main'] = intval(safe_val($_REQUEST, 'scrollpos', 0));
        $scrollpos['1'] = intval(safe_val($_REQUEST, 'scrollpos1', 0));
        $scrollpos['2'] = intval(safe_val($_REQUEST, 'scrollpos2', 0));
        $scrollpos['3'] = intval(safe_val($_REQUEST, 'scrollpos3', 0));
        $scrollpos['4'] = intval(safe_val($_REQUEST, 'scrollpos4', 0));

        $displayheader->enqueueLocalJs(sprintf("var scrollpos_main=%d, scrollpos1=%d, scrollpos2=%d, scrollpos3=%d, scrollpos4=%d;", $scrollpos['main'], $scrollpos[1], $scrollpos[2], $scrollpos[3], $scrollpos[4]));
        $displayheader->enqueueLocalJs(sprintf("var editMode=%d, modified=false, action='%s';", intval($this->m_EditMode), $this->action));
        $displayheader->enqueueLocalJs(sprintf("var variantType=initType='%s', variantId='%s', showSubVariant=%d, paramType=%d, variantEcu=%d;",
            $this->m_VariantType, $this->S_CombiID, $subVariantOf, $this->S_ParamType, $this->S_variantEcu));

        $displayheader->enqueueLocalJs(sprintf("var selectedEcu=%d;", $this->S_currentEcu));
//         $displayheader->enqueueLocalJs ('$(document).ready(function() { $(".datumEingabe").datepicker({}); });');
        //if ($this->m_table)
        //$this->m_table->SetupHeaderFiles($displayheader);
    }

    // ===============================================================================================
    function GetEnggPrivileges($context, $iData = 0) {
        $fixed_user = [82 => 'Fabian Schmitt', 555 => 'Sebastian Müller'];
        $struct = ['current' => 0, 'owner' => [], 'authorized' => $fixed_user];

        if (!isset($this->m_Permission[self::PRIV_ECU_PROFILE]))
            $this->m_Permission[self::PRIV_ECU_PROFILE] = $struct;
        if (!isset($this->m_Permission[self::PRIV_ECU_DATA]))
            $this->m_Permission[self::PRIV_ECU_DATA] = $struct;
        if (!isset($this->m_Permission[self::PRIV_VARIANT_EDIT]))
            $this->m_Permission[self::PRIV_VARIANT_EDIT] = $struct;
        if (!isset($this->m_Permission[self::PRIV_VARIANT_COC]))
            $this->m_Permission[self::PRIV_VARIANT_COC] = $struct;
        if (!isset($this->m_Permission[self::PRIV_VARIANT_CREATE]))
            $this->m_Permission[self::PRIV_VARIANT_CREATE] = $struct;
        if (!isset($this->m_Permission[self::PRIV_SET_VARIABLE]))
            $this->m_Permission[self::PRIV_SET_VARIABLE] = $struct;

        if (!isset ($this->m_Permission[$context])) $this->m_Permission[$context] = $struct;

        $result = &$this->m_Permission;

        $qry = $this->variantDataPtr->newQuery('sts_privilegs')
            ->join('users', 'user_id=id', 'inner join')
            ->where('context', '=', $context);

        switch ($context) {
            case self::PRIV_SET_VARIABLE:
                if ($iData == 0)
                    return [];

                $qry = $qry->where('ecu_parameter_set_id', '=', $iData);
                break;

            case self::PRIV_ECU_PROFILE:
            case self::PRIV_ECU_DATA:
                $qry = $qry->where('ecu_id', '=', $iData);
                break;
        }


        $qry = $qry->orderBy('username');
        $perms = $qry->get('user_id,username,fname,lname,context,is_owner,allow_write');

        if ($perms) {
            foreach ($perms as $et) {
                $uid = $et['user_id'];
                $uname = $et['fname'] . ' ' . $et['lname'];
                $context = $et['context'];

                $result[$context]['authorized'][$uid] = $uname;
                if (toBool($et['is_owner']))
                    $result[$context]['owner'][$uid] = $uname;

                if ($uid == $_SESSION['sts_userid']) {
                    if (toBool($et['is_owner']) || ($uid == $addStsEngg)) //(strtolower(substr($et['username'],0,3))=='sts'))
                    {
                        $result[$context]['current'] = 'owner';
                    } else
                        if (toBool($et['allow_write'])) {
                            $result[$context]['current'] = 'write';
                        }
                }
            }
        }

        if ($this->userPtr->IsAdmin())
            $result[$context]['current'] = 'owner';

        return $result;
    }

    // ===============================================================================================
    function getRequestUid(&$xu, &$uid) {
        if (!preg_match('/^([BCDEW])[:]([0-9]+)$/', $_REQUEST['xuid'], $match))
            return STS_ERROR_WRONG_USER;

        $xu = $match[1];
        $uid = $match[2];

        return 0;
    }

    // ===============================================================================================
    function getRequestContext(&$context, &$iData, &$param_set_id) {
        $param_set_id = null;
        $iData = 0;
        $privId = $_REQUEST['privId'];


        if (!preg_match('/^([a-z].*)[-]([0-9]+)$/', $privId, $match)) {
            $context = $privId;
            return STS_ERROR_INPUT_FILTER_MATCH;
        }

        $context = $match[1];
        switch ($context) {
            case self::PRIV_SET_VARIABLE:
                $param_set_id = intval($match[2]);
                $iData = -1;
                break;

            case self::PRIV_ECU_PROFILE:
            case self::PRIV_ECU_DATA:
                $iData = intval($match[2]);
                break;
        }

        return STS_NO_ERROR;
    }
    /*
  // ===============================================================================================
  function PrivilegsAjaxecute($command)
  {
    if ($res = $this->getRequestContext($context, $iData, $param_set_id))
      return $res;

    if ($res = $this->getRequestUid($xu, $uid))
      return $res;

    $delete = false;
    $qry = $this->ecuPtr->newQuery('sts_privilegs');
    $insert_set = ['user_id' => $uid, 'is_owner' => 'f', 'allow_write' => 't'];

    switch ($command) {
      case 'add_owner':
        $insert_set['is_owner'] = 't';

      case 'add':
        $insert_set['context'] = $context;
        $insert_set['ecu_id'] = $iData;
        $insert_set['ecu_parameter_set_id'] = $param_set_id;
        $insert_set['set_by_user'] = $_SESSION['sts_userid'];

        if (!$qry->insert($insert_set))
          return STS_ERROR_DB_INSERT;
        break;


      case 'rem_owner':
      case 'rem':
        $delete = true;

      case 'to_owner':
      case 'to_writer':
        $qry = $qry->where('context', '=', $context)
            ->where('ecu_id', '=', $iData)
            ->where('user_id', '=', $uid);

        if (isset($param_set_id))
          $qry = $qry->where('ecu_parameter_set_id', '=', $param_set_id);
        else
          $qry = $qry->where('ecu_parameter_set_id', 'is null', null);

        // Delete entry
        if ($delete) {

          if ($xu == 'C')
            return STS_ERROR_WRONG_USER;

          if (!$qry->delete())
            return STS_ERROR_DB_DELETE;
        } // Modify Entry (set/remove Owner-Flag)
        else {
          $is_owner = ($command == 'to_owner') ? 't' : 'f';
          if (!$qry->update(['is_owner'], [$is_owner]))
            return STS_ERROR_DB_UPDATE;
        }
    }
    return 0;
  }
*/
    /*
  // ===============================================================================================
  function SavePrivileges($tab, $paramType)
  {
    $user_added = explode(',', $_REQUEST['addUser']);
    $user_removed = explode(',', $_REQUEST['dropUser']);

    if ($res = $this->getRequestContext($context, $iData, $param_set_id))
      return $res;

    $this->GetEnggPrivileges($context, $iData);

    foreach ($user_removed as $uid) {
      $qry = $this->ecuPtr->newQuery('sts_privilegs')
          ->where('user_id', '=', $uid)
          ->where('context', '=', $context);

      $qry = $qry->where('ecu_id', '=', $iData);

      if (isset ($param_set_id))
        $qry = $qry->where('ecu_parameter_set_id', '=', $param_set_id);
      else
        $qry = $qry->where('ecu_parameter_set_id', 'is null', '');

      $qry->delete();
    }

    $insert_set['context'] = $context;
    $insert_set['ecu_id'] = $iData;
    $insert_set['allow_write'] = 't';
    $insert_set['set_by_user'] = $_SESSION['sts_userid'];

    if (isset ($param_set_id))
      $insert_set['ecu_parameter_set_id'] = $param_set_id;

    foreach ($user_added as $uid) {
      $insert_set['user_id'] = $uid;
      $this->ecuPtr->newQuery('sts_privilegs')
          ->insert($insert_set);
    }

    unset ($this->m_Permission[$context]);
    $this->GetEnggPrivileges($context, $iData);
  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_OwnerAndPrivileged($context, $iData)
  {
    $this->GetEnggPrivileges($context, $iData);

    $permission = &$this->m_Permission[$context];
    $my_userid = $_SESSION['sts_userid'];
    $is_admin = $permission['current'] === 'owner';
    $authorized = &$permission['authorized'];
    $owners = &$permission['owner'];
    $id = "$context-$iData";
    $editMode = $is_admin && ($this->m_EditMode == self::EDIT_Privileges) && ($_REQUEST['id'] == $id);
    $select_enabled = ' disabled';
    $select_engineers = '';

    $admin_buttons = '';
    $opt_writers = "";
    $opt_engineers = "";

    foreach ($authorized as $uid => $name) {
      if (strtolower(substr($name, 0, 3)) == 'sts')
        continue;

      if (preg_match('/fabian[^a-z]*schmitt/i', $name) || preg_match('/sebastian[^a-z]*müller/i', $name)) {
        $xuid = "C:$uid";
      } else
        if (isset ($permission['owner'][$uid])) {
          $xuid = "B:$uid";
        } else {
          $xuid = "W:$uid";
        }

      $opt = "<option value=\"$xuid\">$name</option>";
      $opt_writers .= $opt;
    }


    if ($is_admin) {
      if ($editMode) {
        if (!isset($this->EnggUsers))
          $this->EnggUsers = $this->ecuPtr->newQuery('users')
              ->where('role', 'like', '%engg%')
              ->orderBy('fname')
              ->orderBy('lname')
              ->get('id,username,fname,lname', 'id');

        foreach ($this->EnggUsers as $uid => $set) {
          $name = "{$set['fname']} {$set['lname']}";
          if (empty(trim($name))) {
            $name = $set['username'];
          }

          $opt_engineers .= "<option value=\"E:$uid\">$name</option>";
        }

        $select_engineers = <<<HEREDOC

                <select id="id-engineers-$id" class="privileges-select" data-type="engineers" size="4">
                    $opt_engineers
                </select>

HEREDOC;
        $select_enabled = '';

        $admin_buttons = <<<HEREDOC

                <a href="#" class="privCommand inactiveLink" data-cmd="add" id="id-add-$id">[&lt;&lt; hinzuf.]</a>
                    &nbsp;&nbsp;
                <a href="#" class="privCommand inactiveLink" data-cmd="rem" id="id-rem-$id">[entf. &gt;&gt;]</a>
                <br>
                <a href="#" class="privCommand" data-cmd="save"> <div id="id-save-$id"  class="stsbutton disabled buttonsize_small">Ok</div></a>
                <a href="#" class="privCommand" data-cmd="abort"><div id="id-abort-$id" class="stsbutton buttonsize_small">Abbruch</div></a>

HEREDOC;
      } else {
        $url = $this->GetHtml_Url();
        if ($this->m_EditMode)
          $admin_buttons = sprintf('<div class="stsbutton disabled buttonsize_small ">Ändern</div>');
        else
          $admin_buttons = sprintf('<a href="%s&command=start_privileges&id=%s"><div class="stsbutton buttonsize_small">Ändern</div></a>', $url, $id);
      }
    }

    echo <<<HEREDOC

      <div class="infoFrame stdborder single_privilegs priv_root" data-id="$id">
        <div class="single_priv_div">
          <select id="id-writer-$id" class="privileges-select" data-type="writer" size="4"{$select_enabled}>{$opt_writers}</select>
        </div>
        <div class="single_priv_div">
          <b>Schreibberechtige:</b>
            {$admin_buttons}
        </div>
        <div class="single_priv_div">
          {$select_engineers}
        </div>
      </div>

HEREDOC;
  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_OwnerAndPrivileged_New($context, $iData)
  {
    $this->GetEnggPrivileges($context, $iData);

    $permission = &$this->m_Permission[$context];
    $my_userid = $_SESSION['sts_userid'];
    $is_admin = $permission['current'] === 'owner';
    $authorized = &$permission['authorized'];
    $owners = &$permission['owner'];
    $id = "$context-$iData";
    $editMode = $is_admin && ($this->m_EditMode == self::EDIT_Privileges) && ($_REQUEST['id'] == $id);
    $select_enabled = ' disabled';

    $admin_buttons = '';
    $opt_owners = "";
    $opt_writers = "";
    $opt_engineers = "";


    foreach ($authorized as $uid => $name) {
      if (preg_match('/fabian[^a-z]*schmitt/i', $name) || preg_match('/sebastian[^a-z]*müller/i', $name)) {
        $xuid = "C:$uid";
      } else if (isset ($permission['owner'][$uid])) {
        $xuid = "D:$uid";
      } else {
        $xuid = "W:$uid";
      }

      $opt = "<option value=\"$xuid\">$name</option>";

      if (isset ($owners[$uid]))
        $opt_owners .= $opt;
      else
        $opt_writers .= $opt;
    }


    if ($is_admin) {
      if (!isset($this->EnggUsers))
        $this->EnggUsers = $this->ecuPtr->newQuery('users')
            ->where('role', 'like', '%engg%')
            ->orderBy('fname')
            ->orderBy('lname')
            ->get('id,username,fname,lname', 'id');

      foreach ($this->EnggUsers as $uid => $set) {
        $name = "{$set['fname']} {$set['lname']}";
        if (empty(trim($name))) {
          $name = $set['username'];
        }

        $opt_engineers .= "<option value=\"E:$uid\">$name</option>";
      }

      $select_enabled = '';
    }

    echo <<<HEREDOC

      <div class="infoFrame stdborder priv_root enhanced_privilegs" data-id="$id">
        <div class="enhanced_priv_members ">
          <p><b>Verantwortliche</b><br>
          <select id="id-owner-$id" class="privileges-select" data-type="owner" size="4"{$select_enabled}>{$opt_owners}</select>
          </p>
          <p><b>Schreibberechtigte</b><br>
          <select id="id-writer-$id" class="privileges-select" data-type="writer" size="9"{$select_enabled}>{$opt_writers}</select>
          </p>
        </div>

        <div class="enhanced_priv_edit">
            <div class="priv_buttons_owner">
              <a href="#" class="privCommand inactiveLink" data-cmd="add_owner" id="id-add_owner-$id">[&lt;&lt; hinzuf.]</a><br>

              <a href="#" class="privCommand inactiveLink" data-cmd="rem_owner" id="id-rem_owner-$id">[entf. &gt;&gt;]</a>
            </div>

            <div class="priv_buttons_switch">
              <a href="#" class="privCommand inactiveLink" data-cmd="to_owner" id="id-to_owner-$id">[&uarr; setzten]</a><br>
              <a href="#" class="privCommand inactiveLink" data-cmd="to_writer" id="id-to_writer-$id">[&darr; entf.]</a>
            </div>

            <div class="priv_buttons_writer">
              <a href="#" class="privCommand inactiveLink" data-cmd="add" id="id-add-$id">[&lt;&lt; hinzuf.]</a><br>
              <a href="#" class="privCommand inactiveLink" data-cmd="rem" id="id-rem-$id">[entf. &gt;&gt;]</a>
            </div>

            <div class="enhanced_priv_engineers">
              <p><b>Mitarbeiter</b>
                <select id="id-engineers-$id" class="privileges-select" data-type="engineers" size="15">
                  $opt_engineers
                </select>
              </p>
            </div>
        </div>
      </div>

HEREDOC;
  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_VariantCloneButtons($allow_edit)
  {
    $show = '';
    $hide = ' style="display:none;"';
    $cmdNew = ($this->m_EditMode == self::EDIT_NewVariant);
    $cmdCpy = ($this->m_EditMode == self::EDIT_CopyVariant);

    $btnSize = "W140";
    $class_new_vehicle = ($cmdNew ? 'selected' : '');
    $class_cpy_vehicle = ($cmdCpy ? 'selected' : '');

//         $style_save_disabled    =
    $style_cancel_disabled = (($this->m_EditMode) ? $hide : $show);
//         $style_save_enabled     =
    $style_cancel_enabled = (($this->m_EditMode) ? $show : $hide);

    $variant_id = $this->DB_VariantSet['v_id'];
    $used = ($this->DB_VariantSet['count_prod'] > 0) || ($this->DB_VariantSet['count_all'] > 0);
    $has_sub = isset ($this->DB_VariantSet['subvarianten']) ? 1 : 0;

    $allow_del = (!($cmdNew || $cmdCpy) && !$used);

    if ($allow_edit) {
      $style_new_vehicle_disabled = $cmdCpy ? $show : $hide;
      $style_cpy_vehicle_disabled = $cmdNew ? $show : $hide;
      $style_new_vehicle_enabled = $cmdCpy ? $hide : $show;
      $style_cpy_vehicle_enabled = $cmdNew ? $hide : $show;
      $style_del_vehicle_disabled = $allow_del ? $hide : $show;
      $style_del_vehicle_enabled = $allow_del ? $show : $hide;
    } else {
      $style_new_vehicle_disabled = '';
      $style_cpy_vehicle_disabled = '';
      $style_del_vehicle_disabled = '';

      $style_new_vehicle_enabled = ' style="display:none;"';
      $style_cpy_vehicle_enabled = ' style="display:none;"';
      $style_del_vehicle_enabled = ' style="display:none;"';
    }

    // $url&command=delete_variant

    $url = $this->GetHtml_Url();
    echo <<<HERE______________________________________________________DOC
        <div class="MiniButtons">
          <ul class="submenu_ul">
            <li><span id="id_save_variant_disabled" class="sts_submenu $btnSize disabled"$show>Speichern</span></li>
		    <li><a href="javascript:SaveParameters('save_variant')" id="id_save_variant" class="sts_submenu $btnSize"$hide>Speichern</a></li>
            <li><span id="id_cancel_variant" class="sts_submenu $btnSize disabled"$style_cancel_disabled>Abbruch</span></li>
		    <li><a href="$url&command=cancel_variant" id="id_cancel_variant" class="sts_submenu $btnSize"$style_cancel_enabled">Abbruch</a></li>
            <li><span id="id_delete_variant_disabled" class="sts_submenu $btnSize disabled"$style_del_vehicle_disabled>Konfiguration löschen</span></li>
		    <li><a href="javascript:SafeDeleteVariant($has_sub)" id="id_delete_variant" class="sts_submenu $btnSize $class_del_vehicle"$style_del_vehicle_enabled>Konfiguration löschen</a></li>
            <li><span id="id_copy_variant_disabled" class="sts_submenu $btnSize disabled"$style_cpy_vehicle_disabled>Konfiguration kopieren</span></li>
		    <li><a href="$url&command=copy_variant" id="id_copy_variant" class="sts_submenu $btnSize $class_cpy_vehicle"$style_cpy_vehicle_enabled>Konfiguration kopieren</a></li>
          </ul>
        </div>
HERE______________________________________________________DOC;

//    <li><span id="id_new_variant_disabled" class="sts_submenu $btnSize disabled"$style_new_vehicle_disabled>neue Konfiguration</span></li>
//		    <li><a href="$url&command=new_variant" id="id_new_variant" class="sts_submenu $btnSize $class_new_vehicle"$style_new_vehicle_enabled>neue Konfiguration</a></li>
//

  }
*/

    // ===============================================================================================
    function GetHtml_DeleteFunction($name, $col_id, $deleted = false) {
        $image_trash = '"/images/symbols/icon-trash.png"';
        $image_deleted = '"/images/symbols/icon-del.png"';
        $image_blank = '"/images/symbols/icon-18x18.png"';

        if (empty($name))
            return $deleted ? "<img src=$image_deleted>" : "<img src=$image_blank>";

        $display1 = $deleted ? 'none' : 'inline';
        $display2 = $deleted ? 'inline' : 'none';
        $deleted = ($deleted) ? 1 : 0;

        return <<<HEREDOC
                <input type="hidden" name="{$name}[$col_id][deleted]" id="id_del{$col_id}" value="{$deleted}">
                <a href="javascript:delParamDef('{$col_id}', 1)" id="id_a0_del{$col_id}" style="display:$display1"><img src=$image_trash></a>
                <a href="javascript:delParamDef('{$col_id}', 0)" id="id_a1_del{$col_id}" style="display:$display2"><img src=$image_deleted></a>
HEREDOC;
    }
    /*
  // ===============================================================================================
  function WriteHtml_PentaInfo()
  {
    $deletefunc = (self::WITH_DELETE_PENTA) ? "<th></th>" : "";
    $dbg_info = '';

    $penta_parts = $this->DB_VariantSet['penta_part_string'];
    if (!empty($penta_parts))
      $penta_parts = "<div class=\"abstand\"></div><u><b>Sonderoptionen</b> (Penta Sondervariante)</u><br><div>$penta_parts</div>";


    if (safe_val($GLOBALS, 'VERBOSE', false)) {
      if ($this->DB_RevisionMap) {
        $overlayed = ($this->DB_RevisionMap['penta_id']) ? "JA" : "NEIN";
        $dbg_info = '<div class="dbgInfo infoBlock">' .
            "<span>Windchill=<b>{$this->DB_RevisionMap['variant_id']}</b></span>" .
            "<span>Penta Config ID=<b>{$this->m_PentaConfigId}</b></span>" .
            "<span>Sonderkofiguration: $overlayed</span></div><hr>";
      } else {
        $dbg_info = '<div class="dbgInfo infoBlock">' .
            "<span>Windchill=<b>{$this->DB_VariantSet['v_id']}</b></span>" .
            "<span>Penta=<b>{$this->DB_VariantSet['p_id']}</b></span>" .
            "<span>Penta Config ID=<b>{$this->m_PentaConfigId}</b></span></div><hr>";
      }
    }


    echo <<<HEREDOC

        <h3>Penta Artikel</h3><h2> {$this->DB_VariantSet['u_name']}</h2><hr>$dbg_info
        <table class="countVehicles white stdborder">
          <thead>
            <tr>
              $deletefunc
              <th>Variante</th>
              <th>Farbe</th>
              <th><span class="ttip">Fz. ges.<span class="ttiptext">Anzahl der Fahrzeuge in MoPrA</span></span></th>
              <th><span class="ttip">Fz. prod.<span class="ttiptext">Anzahl der produzierten Fahrzeuge (TEO PASSED)</span></span></th>
            </tr>
          </thead>
          <tbody>
HEREDOC;


    $sum_count_all = $sum_count_finished = 0;
    $colorVariants = implode(',', $this->DB_VariantSet['color_variants']);
    $sql = "
            select penta_number_id, penta_number, colors.name as color,
              (select count (*) from vehicles where vehicles.penta_number_id=penta_numbers.penta_number_id) as count_all,
              (select count (*) from vehicles where vehicles.penta_number_id=penta_numbers.penta_number_id and (finished_status='t' or depot_id>0)) as count_finished
            from penta_numbers
            join colors using (color_id)
            where penta_number_id in ($colorVariants);";

    $qry = $this->leitWartePtr->newQuery();
    if ($qry->query($sql))
      $penta_details = $qry->fetchAssoc('penta_number_id');

    foreach ($penta_details as $penta_id => &$set) {
      $sum_count_all += $set['count_all'];
      $sum_count_finished += $set['count_finished'];
    }


    echo "
            <tr>$deletefunc<td style=\"font-weight: bold;\" colspan=\"2\">Fahrzeuge gesamt</td><td>$sum_count_all</td><td>$sum_count_finished</td></tr>";

    foreach ($penta_details as $penta_id => &$set) {
      $deletefunc = (self::WITH_DELETE_PENTA) ? "<td>" . $this->GetHtml_DeleteFunction('penta', $penta_id) . "</td>" : "";

      echo "
                <tr>$deletefunc<td>{$set['penta_number']}</td><td>{$set['color']}</td><td>{$set['count_all']}</td><td>{$set['count_finished']}</td></tr>";
    }

    echo <<<HEREDOC
              <tbody>
            </table>

           {$penta_parts}
HEREDOC;
  }*/
    /*
  // ===============================================================================================
  function WriteHtml_VariantInfoHeader($editMode)
  {
    $vname = $this->DB_VariantSet['v_name'];
    $params = $this->vehicleVariantsPtr->Decode_WC_Variant_Name($vname);
    $wcparts = $this->DB_VariantSet['windchill_part_string'];

    if (empty($wcparts)) $wcparts = '<span class="InactiveLink">{keine}</span>';

    if ($params) {
      $vname = "{$params['type']} {$params['series']} {$params['layout-key']} {$params['feature-key']} {$params['battery-key']}";
      if (empty ($params['battery'])) {
        $params['battery-key'] = '?';
        $params['battery'] = $this->GetVariantBattery();
      }
    } else if ($editMode == self::EDIT_NewVariant) {
      $params['series'] = '01';
      $params['layout-key'] = 'PURE';
      $params['layout'] = 'Pure';
      $params['feature-key'] = 'A';
      $params['feature'] = 'Beifahrersitz';
      $params['battery-key'] = 'A';
      $params['battery'] = 'V4/V5';
    } else {
      $params['series'] = '?';
      $params['layout-key'] = 'BOX';
      $params['layout'] = 'Koffer';
      $params['feature-key'] = 'A';
      $params['feature'] = 'Beifahrersitz';
      $params['battery-key'] = 'X';
      $params['battery'] = 'V4/V5';
    }


    if ($editMode) {
      $vname = <<<HEREDOC
<div class="vname">:
  <input id="id_config_type" name="variant[type]" type="text" value="{$params['type']}" size="3" max-size="3">
  <div id="id_series_key" >{$params['series']}</div>
  <div id="id_layout_key" >{$params['layout-key']}</div>
  <div id="id_feature_key">{$params['feature-key']}</div>
  <div id="id_battery_key">{$params['battery-key']}</div>
  <div id="id_variant_exists">Variant exist</div>
</div>

HEREDOC;

      $listSeries = [];
      for ($s = 1; $s <= ($this->m_maxSerialNumber + 1); $s++) {
        $os = sprintf('%02d', $s);
        $listSeries[$os] = $os;
      }

      $listLayouts = VehicleVariants::LAYOUT_NAMES;
      foreach ($listLayouts as $key => &$name) $name = "[$key] - $name";
      $listFeatures = VehicleVariants::FEATURE_NAMES;
      foreach ($listFeatures as $key => &$name) $name = "[$key] - $name";
      $listBatteries = VehicleVariants::BATTERY_NAMES;
      foreach ($listBatteries as $key => &$name) $name = "[$key] - $name";

      $series = '<select id="id_series"  name="variant[series]"  OnChange="UpdateVariantConfigString (this)">' . $this->GetHtml_SelectOptions($listSeries, $params['series']) . '</select>';
      $layout = '<select id="id_layout"  name="variant[layout]"  OnChange="UpdateVariantConfigString (this)">' . $this->GetHtml_SelectOptions($listLayouts, $params['layout-key']) . '</select>';
      $feature = '<select id="id_feature" name="variant[feature]" OnChange="UpdateVariantConfigString (this)">' . $this->GetHtml_SelectOptions($listFeatures, $params['feature-key']) . '</select>';
      $battery = '<select id="id_battery" name="variant[battery]" OnChange="UpdateVariantConfigString (this)">' . $this->GetHtml_SelectOptions($listBatteries, $params['battery-key']) . '</select>';
      $vinmethod = '<select id="id_vmethod" name="variant[vmethod]">' . $this->GetHtml_SelectOptions($this->listVMs, $this->DB_VariantSet['vin_method']) . '</select>';
    } else {
      $vname = "<h2>: $vname</h2>";
      $series = "{$params['series']     }";
      $layout = "{$params['layout-key'] } - {$params['layout'] }";
      $feature = "{$params['feature-key']} - {$params['feature']}";
      $battery = "{$params['battery-key']} - {$params['battery']}";
      $vinmethod = "{$this->DB_VariantSet['vin_method']}";
    }


    echo <<<HEREDOC
        <div class="infoFrame stdborder flexibleHeight">
          <div class="infoHeader">
            <h3>Konfiguration</h3>$vname<hr>
            <span class="Label100">Serie</span><span class="LabelX">:       $series</span></br>
            <span class="Label100">Ausführung</span><span class="LabelX">:  $layout</span></br>
            <span class="Label100">Merkmal</span><span class="LabelX">:     $feature</span></br>
            <span class="Label100">Batterie</span><span class="LabelX">:    $battery</span></br>
            <span class="Label100">VIN Methode</span><span class="LabelX">: $vinmethod</span>
            <hr>
            <u><b>Optionen</b> (Windchill)</u>:<br>
            <div>{$wcparts}</div>
          </div>
        </div>
        <div class="infoFrame stdborder flexibleHeight">
          <div>
HEREDOC;


    switch ($editMode) {
      case self::EDIT_NewVariant:
        break;

      case self::EDIT_CopyVariant:
        echo <<<HEREDOC
            <div class="copyOptions">
              <span class="LabelX W120"><h3>Kopieroptionen:</h3></span>
              <span class="LabelX W100"><input type="checkbox" checked value="1" name="copy[coc]" > COC-Werte</span>
              <span class="LabelX W130"><input type="checkbox" checked value="1" name="copy[vars]" > globale Parameter</span>
              <hr>
HEREDOC;
        foreach ($this->DB_allRevisions as $ecu_id => $version) //                 foreach ($this->allEcuNames as $ecu_id=>$ecu)
        {
          $xClassData = $xClassSw = "";

          $rev_id = $version['rev_id'];

          if (!toBool($version['sw_profile_ok']))
            $xClassSw = " redText";
          else
            if (!toBool($version['released']))
              $xClassSw = " yellowText";

          if (!toBool($version['parameters_check_ok']))
            $xClassData = " redText";
          else
            if (!toBool($version['parameters_released']))
              $xClassData = " yellowText";


          $Uecu = strtoupper($this->allEcuNames[$ecu_id]);

          echo <<<HEREDOC
              <div>
                <span class="LabelX W120">$Uecu:</span>
                <span class="LabelX W100{$xClassSw}"><input type="checkbox" checked value="1" OnClick="UncheckSibling(this)" name="copy[ecu_sw][$ecu_id]">SW Version</span>
                <span class="LabelX W130{$xClassData}"><input type="checkbox" checked value="1" name="copy[ecu_data][$ecu_id]">Werte</span>
              </div>
HEREDOC;
        }
        echo "\n            </div>\n";
        break;

      default:
        $this->WriteHtml_PentaInfo();
        break;
    }

    echo <<<HEREDOC
          </div>
        </div>
HEREDOC;

  }*/
    /*
  // ===============================================================================================
  function WriteHtml_VariantInfoHeaderEdit()
  {

    $vname = $this->Separated_Variant_ID($this->DB_VariantSet['v_name']);

    $battery = $this->DB_VariantSet['battery'];
    if (empty($battery)) {
      $partkey = $this->DB_VariantSet['v_parts'] . $this->DB_VariantSet['p_parts'];
      $partkey = str_replace(['{', '}'], [',', ','], $partkey);
      $batteries = [11, 12, 14];
      foreach ($batteries as $part_id) {
        $search = ",$part_id,";
        if (strpos($partkey, $search) !== false) {
          $battery = $this->allParts[$part_id]['name'];
          break;
        }
      }
    }

    if (empty ($battery)) {
      $battery = (substr($vname, 0, 3) == 'D16') ? "(SDA) ?" : "(V5) ?";
    }

    $configuration_info = "";

    $serie = substr($this->DB_VariantSet['v_name'], 3, 2);

    if (strlen(trim($this->DB_VariantSet['v_name'])) == 11 && (int)$serie >= 3) {
      $vehicle_params = $this->vehicleVariantsPtr->Decode_WC_Variant_Name($this->DB_VariantSet['v_name']);
      $windchill_parts = $this->DB_VariantSet['windchill_part_string'];
      if (empty($windchill_parts)) $windchill_parts = '<span class="InactiveLink">{keine}</span>';

      $configuration_info = <<<HEREDOC
              <span class="Label100">Serie</span><span class="LabelX">:       {$serie}</span></br>
              <span class="Label100">Ausführung</span><span class="LabelX">:  {$vehicle_params['layout-key']} - {$vehicle_params['layout']}</span></br>
              <span class="Label100">Merkmal</span><span class="LabelX">:     {$vehicle_params['feature-key']} - {$vehicle_params['feature']}</span></br>
              <span class="Label100">Batterie</span><span class="LabelX">:    {$vehicle_params['battery-key']} - {$vehicle_params['battery']}</span></br>
              <span class="Label100">VIN Methode</span><span class="LabelX">: {$this->DB_VariantSet['vin_method']}</span>
              <hr>
              <u><b>Optionen</b> (Windchill)</u>:<br>
              <div>{$windchill_parts}</div>

HEREDOC;
    }


    $penta_parts = $this->DB_VariantSet['penta_part_string'];
    if (!empty($penta_parts))
      $penta_parts = "<div class=\"abstand\"></div><u><b>Sonderoptionen</b> (Penta Sondervariante)</u><br><div>$penta_parts</div>";


    if ($GLOBALS['VERBOSE']) {
      if ($this->DB_RevisionMap) {
        $overlayed = ($this->DB_RevisionMap['penta_id']) ? "JA" : "NEIN";
        $dbg_info = '<div class="dbgInfo infoBlock">' .
            "<span>Windchill=<b>{$this->DB_RevisionMap['variant_id']}</b></span>" .
            "<span>Penta Config ID=<b>{$this->m_PentaConfigId}</b></span>" .
            "<span>Sonderkofiguration: $overlayed</span></div><hr>";
      } else {
        $dbg_info = '<div class="dbgInfo infoBlock">' .
            "<span>Windchill=<b>{$this->DB_VariantSet['v_id']}</b></span>" .
            "<span>Penta=<b>{$this->DB_VariantSet['p_id']}</b></span>" .
            "<span>Penta Config ID=<b>{$this->m_PentaConfigId}</b></span></div><hr>";
      }
    }


    $deletefunc = (self::WITH_DELETE_PENTA) ? "<th></th>" : "";

    echo <<<HEREDOC
        <div class="infoFrame stdborder flexibleHeight">
          <div>
            <h3>Konfiguration</h3><h2>$vname</h2><hr>
             $configuration_info
          </div>
        </div>
        <div class="infoFrame stdborder flexibleHeight">
          <div>
            <h3>Penta Variante</h3><h2> {$this->DB_VariantSet['u_name']}</h2><hr>$dbg_info
            <table class="countVehicles white stdborder">
              <thead>
                <tr>$deletefunc<th>Variante</th><th>Farbe</th><th>Fz. ges.</th><th>Fz. prod.</th></tr>
              </thead>
              <tbody>
HEREDOC;


    $colorVariants = implode(',', $this->DB_VariantSet['color_variants']);
    $sql = "
            select penta_number_id, penta_number, colors.name as color,
              (select count (*) from vehicles where vehicles.penta_number_id=penta_numbers.penta_number_id) as count_all,
              (select count (*) from vehicles where vehicles.penta_number_id=penta_numbers.penta_number_id and (finished_status='t' or depot_id>0)) as count_finished
            from penta_numbers
            join colors using (color_id)
            where penta_number_id in ($colorVariants);";

    $qry = $this->leitWartePtr->newQuery();
    if ($qry->query($sql))
      $penta_details = $qry->fetchAssoc('penta_number_id');

    foreach ($penta_details as $penta_id => &$set) {

      $deletefunc = (self::WITH_DELETE_PENTA) ? "<td>" . $this->GetHtml_DeleteFunction('penta', $penta_id) . "</td>" : "";

      echo "
                <tr>$deletefunc<td>{$set['penta_number']}</td><td>{$set['color']}</td><td>{$set['count_all']}</td><td>{$set['count_finished']}</td></tr>";
    }

    echo <<<HEREDOC

              <tbody>
            </table>

           {$penta_parts}
          </div>
        </div>
HEREDOC;

  }*/
    /*
  // ===============================================================================================
  function WriteHtml_VariantInfoBlock($headline, $privilege, $ecu_id = 0)
  {
    $editMode = (($this->m_EditMode == self::EDIT_NewVariant) || ($this->m_EditMode == self::EDIT_CopyVariant)) ? $this->m_EditMode : 0;
    $infoState = $editMode ? 1 : $this->S_infoState;
    $ico_minimize = '/images/symbols/minimize.png';
    $ico_maximize = '/images/symbols/maximize.png';
    $display = $infoState ? 'flex' : 'none';
    $display0 = $infoState ? 'block' : 'none';
    $display1 = $infoState ? 'none' : 'block';

    echo <<<HEREDOC
      <div class="infoBlock">
        <div id="id_infoHeadline">
          <span><b>$headline</b></span>
          <span>
            <a id="id_infoState0" style="display:$display0" href="javascript:HandleInfoBlock(0);"><img src="$ico_minimize"></a>
            <a id="id_infoState1" style="display:$display1" href="javascript:HandleInfoBlock(1);"><img src="$ico_maximize"></a>
          </span>
        </div>
        <div id="id_infoBlock" style="display:$display">
HEREDOC;

    $this->WriteHtml_VariantInfoHeader($editMode);
    $this->GetEnggPrivileges($privilege, $ecu_id);

    if (!$editMode) {

      $permissions = &$this->m_Permission[$privilege];
      $owners = implode(',', $permissions['owner']);


      echo <<<HEREDOC
              <div class="infoFrame stdborder">
                <div>
                  <span class="Label250">Parameter Verantworlicher</span><span class="Label150">: $owners</span><br>
                  <span class="Label250">Parameter Verantworlicher gesetzt durch</span><span class="Label150">: Jens Frangenheim</span><br>
                  <span class="Label250">Berechtigt Parameter Verantworlichen zu setzten</span><span class="Label150">: NEIN</span>
                </div>
              </div>
HEREDOC;

      $this->WriteHtml_OwnerAndPrivileged($privilege, $ecu_id);
    }

    echo "
        </div>
      </div>\n";
  }*/
    /*
  // ===============================================================================================
  function WriteHtml_VariantMain()
  {
    $numOptions = [0 => '--', 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9];

    echo '  <div class="seitenteiler flexTop scrollboxY" id="idPartList">' . nl . $this->GetHtml_FormHeader("idr_", 4, "cocForm");


    $this->WriteHtml_VariantInfoBlock('Fahrzeuginformation', self::PRIV_VARIANT_EDIT);
    $allow_edit = toBool($this->m_Permission[self::PRIV_VARIANT_EDIT]['current']);
    $onChange = (($this->m_EditMode == self::EDIT_NewVariant)
        || ($this->m_EditMode == self::EDIT_CopyVariant)) ? '' : 'onVariantPartChanged(true)';

    // $this->WriteHtml_OwnerAndPrivileged (self::PRIV_VARIANT_EDIT, 0);


    $this->IncludeTableInfo();
    $table_info = $this->vehicleVariantsPtr->queryColumnInfo();
    $table_comment = $this->vehicleVariantsPtr->queryColumnInfo('column_comment');
    $variant_id = $this->DB_VariantSet['v_id'];
    $variant_data = $this->vehicleVariantsPtr->newQuery()->where('vehicle_variant_id', '=', $variant_id)->getOne('*');


    // zusätliche variant-Information
    if (true) {
      echo <<<HEREDOC
      <div class="infoBlock">
        <div class="infoFrame stdborder flexibleHeight">
          <h3>Interne Fahrzeuginformation</h3>
          <table class="variantInfo eculist">
            <thead>
              <tr><th>Eigenschaft</th><th>Wert</th></tr>
            </thead>
            <tbody>
HEREDOC;
      foreach ($this->configInfo as $col => $set) {
        if ($set === false)
          continue;

        $caption = safe_val($set, 'info', $col);
        if ($allow_edit)
          $readonly = $set['readonly'] ? ' readonly' : '';
        else
          $readonly = ' readonly';

        if ($readonly)
          $content = $this->WriteHtml_CocValue($variant_data[$col], $table_info[$col], $col, $set);
        else
          $content = $this->WriteHtml_CocEditControl($variant_data[$col], $table_info[$col], $col, $set);

        echo <<<HEREDOC
              <tr>
                <td>$caption</td><td>$content</td>
              </tr>
HEREDOC;
      }

      echo <<<HEREDOC
            </tbody>
          </table>
          <br>
            <br>
HEREDOC;
    }

    $this->GetVariantPartlist($this->DB_VariantSet['v_id'], $this->DB_VariantSet['p_id']);

    if (count($this->advicingParts)) {
      echo <<<HEREDOC
          <h3>Bauteile Fahrzeugbegleitschein</h3>
          <table class="variantInfo partlist">
            <thead>
              <tr><th>Bauteil</th><th>Verwendung</th></tr>
            </thead>
            <tbody>
HEREDOC;

      foreach ($this->advicingParts as $group_id => &$list) {
        $group = $this->allPartGroups[$group_id];

        if (toBool($group['allow_multi'])) {
          foreach ($list as $part_id => &$set) {
            if ($allow_edit) {
              $checked = $set['count'] ? " checked" : "";

              echo <<<HEREDOC
                          <tr>
                            <td>{$set['name']}</td>
                            <td><input type="checkbox" name="part[$part_id]"$checked onClick="$onChange"></td>
                          </tr>
HEREDOC;
            } else {
              $checked = $set['count'] ? "X" : "";
              echo <<<HEREDOC
                          <tr>
                            <td>{$set['name']}</td>
                            <td>$checked</td>
                          </tr>
HEREDOC;
            }
          }
        } else {
          if ($allow_edit) {
            echo <<<HEREDOC
                  <tr>
                    <td>
                      {$group['group_name']}
                    </td>
                    <td>
                      <select class="selPart" name="group[$group_id]" onChange="$onChange">
HEREDOC;
            if ($group['allow_none'])
              echo '<option value="0">-- nicht benutzt --</option>';

            foreach ($list as $part_id => &$set) {
              $selOpt = ($set['count'] > 0) ? " selected" : "";

              echo "<option value=\"$part_id\"$selOpt>{$set['name']}</option>";
            }
            echo "</select>\n";
            echo <<<HEREDOC
                  </td>
                </tr>
HEREDOC;
          } else {
            $part_id = $group['parts'][0];
            $part_name = $part_id ? $this->allParts[$part_id]['name'] : "-- nicht benutzt --";

            echo <<<HEREDOC
                  <tr>
                    <td>
                      {$group['group_name']}
                    </td>
                    <td>
                      {$part_name}
                    </td>
                  </tr>
HEREDOC;
          }
        }
      }
    }


    echo <<<HEREDOC
              </tbody>
          </table>
          <br>
          <span class="inactiveLink ttip">Link Baureiheninfo<span class="ttiptext">Die Baureiheninfo ist zur Zeit noch in Entwicklung<br>und wird in Kürze bereit gestellt.</span></span>
        </div>
HEREDOC;


    if (false) //count ($this->engeneeringParts))
    {
      echo <<<HEREDOC
        <div class="infoFrame stdborder flexibleHeight">
          <h3>ECU Bauteile</h3>
          <table class="variantInfo eculist">
            <thead>
              <tr><th>Bauteil</th><th>Verwendung</th></tr>
            </thead>
            <tbody>
HEREDOC;
      foreach ($this->engeneeringParts as $group_id => &$list) {
        $group = $this->allPartGroups[$group_id];

        if (toBool($group['allow_multi'])) {
          if ($allow_edit) {
            foreach ($list as $part_id => &$set) {
              $checked = $set['count'] ? " checked" : "";
              echo "              <tr><td>{$set['name']}</td><td><input type=\"checkbox\" name=\"part[$part_id]\"$checked onClick=\"$onChange\"></td></tr>\n";
            }
          } else {
            foreach ($list as $part_id => &$set) {
              $checkd = $set['count'] ? "X" : "";
              echo "              <tr><td>{$set['name']}</td><td>$checked</td></tr>\n";
            }
          }
        } else {
          if ($allow_edit) {
            echo "              <tr><td>{$group['group_name']}</td><td><select name=\"group[$group_id]\" onChange=\"$onChange\">";
            if ($group['allow_none'])
              echo '<option value="0">-- nicht benutzt --</option>';

            foreach ($liWriteHtml_SelectVariantst as $part_id => &$set) {
              $selOpt = ($set['count'] > 0) ? " selected" : "";

              echo "<option value=\"$part_id\"$selOpt>{$set['name']}</option>";
            }

            echo "</select>\n";
          } else {
            $part_id = $group['parts'][0];
            $part_name = $part_id ? $this->allParts[$part_id]['name'] : "-- nicht benutzt --";

            echo <<<HEREDOC
                  <tr>
                    <td>
                      {$group['group_name']}
                    </td>
                    <td>
                      {$part_name}
HEREDOC;

          }
          echo <<<HEREDOC
                  </td>
                </tr>
HEREDOC;
        }
      }
      echo <<<HEREDOC
            </tbody>
          </table>
        </div>
HEREDOC;
    }

    echo <<<HEREDOC
        <div class="infoFrame flexibleHeight">
          <h3>Verwendete ECUs</h3>
          <table class="variantInfo partlist">
            <thead>
              <tr><th>ECU</th><th>SW-Version</th><th>Verwendung</th></tr>
            </thead>
            <tbody>
HEREDOC;

    $this->GetEnggPrivileges(self::PRIV_VARIANT_EDIT, 0);
    $allow_by_variant_config = toBool($this->m_Permission[self::PRIV_VARIANT_EDIT]['current']);

    foreach ($this->allEcuNames as $ecu_id => $ecu_name) {
      if ($allow_by_variant_config) {
        $allow_edit = true;
      } else {
        $this->GetEnggPrivileges(self::PRIV_ECU_DATA, $ecu_id);
        $allow_edit = toBool($this->m_Permission[self::PRIV_ECU_DATA]['current']);
      }

      $disabled = $allow_edit ? '' : ' disabled';

      if (!isset ($this->DB_allRevisions[$ecu_id])) {
        $verwendet = sprintf('<input type="checkbox" name="ecu_used[%d]" onClick="onVariantPartChanged(true)"%s><span class=\"inactiveDescr\">kein Eintrag</span>', $ecu_id, $disabled);
        $version = "";
      } else
        if (!isset ($this->DB_allRevisions[$ecu_id]['rev_id'])) {
          $used = toBool($this->DB_allRevisions[$ecu_id]['ecu_used']);
          $verwendet = sprintf('<input type="checkbox" name="ecu_used[%d]" onClick="onVariantPartChanged(true)" %s%s>', $ecu_id, $used ? " checked" : "", $disabled);
          $version = '<span class="inactiveDescr">keine SW Version ausgewählt</span>';
        } else
          if (toBool($this->DB_allRevisions[$ecu_id]['use_uds'])) {
            $used = toBool($this->DB_allRevisions[$ecu_id]['ecu_used']);
            $verwendet = sprintf('<input type="checkbox" name="ecu_used[%d]" onClick="onVariantPartChanged(true)" %s%s>', $ecu_id, $used ? " checked" : "", $disabled);
            $version = $this->DB_allRevisions[$ecu_id]['sts_version'];
          } else {
            $verwendet = "<span class=\"inactiveDescr\">kein UDS</span>";
            $version = "";
          }

      echo <<<HEREDOC
              <tr>
                <td>$ecu_name</td>
                <td>$version</td>
                <td>$verwendet</td>
              </tr>
HEREDOC;
    }
    echo <<<HEREDOC
        </tbody>
          </table>
        </div>
HEREDOC;

    if (false)
      echo <<<HEREDOC
        <div class="infoFrame stdborder">
          <h3>Baureiheninfo</h3>
          <table class="partlist">
            <thead>
              <tr><th>Windchill</th><th>Penta</th><th>Anzahl</th><th>Bauteil</th></tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
HEREDOC;

//    echo <<<HEREDOC
//     </div>
//  </div>
//  <div class="variantPartsButtons stdborder">
//    <div class="MiniButtons">
//HEREDOC;
//
//
//    $this->WriteHtml_VariantCloneButtons($allow_edit);
//
//    echo <<<HEREDOC
//    </div>
//  </div>
//HEREDOC;


  }*/
    /*
  // ===============================================================================================
  function SaveVariant()
  {
    $result = 0;

    switch ($this->m_EditMode) {
      case self::EDIT_NewVariant:
        $result = $this->SaveNewVariant();
        break;
      case self::EDIT_CopyVariant:
        $result = $this->SaveCopyVariant();
        break;
      case 0:
        break;
      default:
        return;
    }

    $this->SaveVariantParts();
    $this->SaveVariantUsedEcus();

    if (is_array($result)) {
      $_REQUEST = [];
      $_POST = [];
      $_GET = [];
      $this->ExecuteTAB_Varianten();
    }
    return $result;
  }*/
    /*
  // ===============================================================================================
  function SaveNewVariant()
  {
    extract($_REQUEST['variant']);
    $new_winchill_name = "{$type}{$series}{$layout}{$feature}{$battery}";

    $is_dp = 'f';
    $default_color = 1;
    $fzVariante = 'Koffer';
    $anzahlSitze = 2;
    $zielstaat = 'DE';

    switch ($layout) {
      case 'BPOS':
      case 'EPOS':
        $is_dp = 't';
        break;

      case 'EBOX':
      case 'BBOX':
        $default_color = 5;
        break;

      case 'PICK':
        $fzVariante = 'Pritsche';
        $default_color = 4;
        break;

      case 'PURE':
        $fzVariante = 'Pure';
        $default_color = 4;
        break;
    }

    switch ($feature) {
      case 'A':
      case 'B':
      case 'D':
        break;

      case 'C':
        $anzahlSitze = 1;
        break;

      case 'E':
        $zielstaat = 'GB';
        break;
    }

    $batteries = [
        'A' => 'V6/1',
        'B' => 'SDA',
        'C' => 'V6/2',
    ];


    // $strType    = (($type=='D16') ? 'D16A' : $type);
    $iBatch = intval($series);
    $strBatch = chr(64 + ($iBatch >= 9 ? $iBatch + 1 : $iBatch));
    $vin_method = $this->listVMs[$vmethod];
    $strBattery = $batteries[$battery];


    if ($_REQUEST['copy']['coc']) {
      unset ($_REQUEST['copy']['coc']);

      $id_src = $this->DB_VariantSet['v_id'];
      $qry = $this->vehicleVariantsPtr->newQuery();
      $qry = $qry->where('vehicle_variant_id', '=', $id_src);
      $cols = $qry->getOne();

      unset ($cols['vehicle_variant_id']);
      unset ($cols['windchill_variant_name']);
      unset ($cols['type']);
      unset ($cols['number_of_seats']);
      unset ($cols['fahrzeugvariante']);
      unset ($cols['is_dp']);
      unset ($cols['zielstaat']);
      unset ($cols['default_color']);
      unset ($cols['vin_batch']);
      unset ($cols['vin_method']);
      unset ($cols['battery']);
      unset ($cols['coc_released_by']);
      unset ($cols['coc_released_date']);

      $copy_cols = implode(_cma_, array_keys($cols));
      $insert_cols = 'windchill_variant_name,type,number_of_seats,'
          . 'fahrzeugvariante,is_dp,zielstaat,vin_batch,'
          . 'default_color,vin_method,battery,'
          . $copy_cols;

      $sql = "
                insert into vehicle_variants ($insert_cols)
                select
                    '$new_winchill_name' as new_name,
                    '$type' as new_type,
                     $anzahlSitze as new_seats,
                    '$fzVariante' as new_fahrzeugvariante,
                    '$is_dp' as new_is_dp,
                    '$zielstaat' as new_zielstaat,
                    '$strBatch' as new_batch,
                    '$default_color' as new_default_color,
                    '$vin_method' as new_vin_method,
                    '$strBattery' as new_battery,
                    $copy_cols from vehicle_variants
                where vehicle_variant_id = $id_src;";

      $this->vehicleVariantsPtr->newQuery()->query($sql);
    } else {
      $insert = [
          'windchill_variant_name' => $new_winchill_name,
          'type' => $type,
          'number_of_seats' => $anzahlSitze,
          'fahrzeugvariante' => $fzVariante,
          'is_dp' => $is_dp,
          'zielstaat' => $zielstaat,
          'vin_batch' => $strBatch,
          'default_color' => $default_color,
          'vin_method' => $vin_method,
          'battery' => $strBattery
      ];

      $qry = $this->vehicleVariantsPtr->newQuery();
      if (!$qry->insert($insert))
        return $this->SetError(STS_ERROR_DB_INSERT, $qry->GetLastError());
    }

    $variant_id = $this->vehicleVariantsPtr->newQuery()->where('windchill_variant_name', '=', $new_winchill_name)->getVal('vehicle_variant_id');
    if ($variant_id) {
      $color_key = $this->vehicleVariantsPtr->newQuery('colors')->where('color_id', '=', $default_color)->getVal('color_key');
      $penta_number = "{$new_winchill_name}_{$color_key}";
      $insert_penta = ['penta_number' => $penta_number, 'vehicle_variant_id' => $variant_id, 'color_id' => $default_color];
      $qry = $this->vehicleVariantsPtr->newQuery('penta_numbers');

      if (!$qry->insert($insert_penta))
        return $this->SetError(STS_ERROR_DB_INSERT, $qry->GetLastError());

      $penta_id = $this->vehicleVariantsPtr->newQuery('penta_numbers')->where('penta_number', '=', $penta_number)->getVal('penta_number_id');

      if (empty ($penta_id))
        return $this->SetError(STS_ERROR_PHP_ASSERTION, "penta_number_id=$penta_id");

      $this->vehicleVariantsPtr->newQuery()->query("update penta_numbers set penta_config_id=penta_number_id where penta_number_id=$penta_id");

      $combi_id = "$variant_id:$penta_id";
      $this->S_CombiID = $combi_id;
      $this->m_VariantType = $type;

      if (!isset ($this->DB_allVariantTypes[$type]))
        $this->DB_allVariantTypes[$type] = $type;

      $new_index = [];
      $inserted = false;
      foreach ($this->DB_indexAllVariant[$type] as $id => $name) {
        if (!$inserted && sortVariant($new_winchill_name, $name) < 0) {
          $inserted = true;
          $new_index[$combi_id] = $new_winchill_name;
        }
        $new_index[$id] = $name;
      }
      $this->DB_indexAllVariant[$type] = $new_index;

      $this->DB_allVariants[$combi_id] = [
          'v_id' => $variant_id,
          'v_name' => $new_winchill_name,
          'p_id' => $penta_id,
          'p_name' => $penta_number,
          'default_color' => $default_color,
          'battery' => $strBattery,
          'vin_method' => $vin_method,
          'penta_config_id' => $penta_id,
          'color_id' => $default_color,
          'v_parts' => '{}',
          'p_parts' => '{}',
          'i_name' => $new_winchill_name,
          'u_name' => $new_winchill_name . '_##',
          'count_all' => 0,
          'count_prod' => 0,
          'color_variants' => [$default_color => $penta_id],
      ];

      $this->DB_VariantSet = &$this->DB_allVariants[$combi_id];

      return [$variant_id, $penta_id];
    }

    return 0;
  }*/
    /*
  // ===============================================================================================
  function SaveCopyVariant()
  {
    $src_combi_id = $this->S_CombiID;

    $result = $this->SaveNewVariant();
    if (is_array($result)) {
      $variant_id = $result[0];
      $penta_id = $result[1];
      $new_combi_id = "$variant_id:$penta_id";

      if (isset ($_REQUEST['copy']['vars']))
        $this->CopyGlobalParameters($this->DB_VariantSet['v_id'], $variant_id);

      $ecuList = array_keys($_REQUEST['copy']['ecu_sw']);
      foreach ($ecuList as $ecu_id) {
        if ($this->SetCurrentEcu($ecu_id)) {
          $with_data = isset($_REQUEST['copy']['ecu_data'][$ecu_id]);
          $this->CopyVariantParameterData($src_combi_id, [$new_combi_id], $with_data, true);
        }
      }

      $this->SetCurrentEcu(0);
    }

    return $result;
  }*/
    /*
  // ===============================================================================================
  function DeleteCurrentVariant()
  {

    $variant_id = $this->DB_VariantSet['v_id'];
    $penta_id = $this->DB_VariantSet['p_id'];
    $config_id = $this->DB_VariantSet['penta_config_id'];
    $is_sub = isset ($this->DB_VariantSet['master']);
    $all_penta_ids = array_values($this->DB_VariantSet['color_variants']);

    $qry_data = $this->ecuPtr->newQuery('vehicle_variant_data')->where('vehicle_variant_id', '=', $variant_id);
    $qry_revmap = $this->ecuPtr->newQuery('variant_ecu_revision_mapping')->where('variant_id', '=', $variant_id);
    $qry_vparts = null;
    $qry_variants = null;
    $del_index_ids = [$this->S_CombiID];

    if ($is_sub) {
      $qry_penta = $this->ecuPtr->newQuery('penta_numbers')->where('penta_config_id', '=', $config_id);

      $qry_data = $qry_data->where('overlayed_penta_id', '=', $config_id);
      $qry_revmap = $qry_revmap->where('penta_id', '=', $config_id);
    } else {
      foreach ($this->DB_VariantSet['subvarianten'] as $combi_id) {
        $db_sub = &$this->DB_allVariants[$combi_id];
        $all_penta_ids += array_values($db_sub['color_variants']);
        $del_index_ids[] = $combi_id;
      }

      $qry_penta = $this->ecuPtr->newQuery('penta_numbers')->where('vehicle_variant_id', '=', $variant_id);
      $qry_vparts = $this->ecuPtr->newQuery('variant_parts_mapping')->where('variant_id', '=', $variant_id);
      $qry_variants = $this->ecuPtr->newQuery('vehicle_variants')->where('vehicle_variant_id', '=', $variant_id);
    }

    $qry_pparts = $this->ecuPtr->newQuery('penta_number_parts_mapping')->where('penta_number_id', 'in', $all_penta_ids);


    $qry_data->delete();
    $qry_revmap->delete();
    $qry_pparts->delete();
    $qry_penta->delete();

    if ($qry_vparts)
      $qry_vparts->delete();
    if ($qry_variants)
      $qry_variants->delete();

    if ($is_sub) {
      $master_id = $this->DB_VariantSet['master'];
      $master_set = &$this->DB_allVariants[$master_id];
      $master_set['subvarianten'] = array_diff($master_set['subvarianten'], [$this->S_CombiID]);
    }

    foreach ($del_index_ids as $combi_id) {
      unset ($this->DB_allVariants[$combi_id]);

      foreach ($this->DB_allVariantTypes as $vt)
        unset ($this->DB_indexAllVariant[$vt][$combi_id]);
    }
    $this->DB_VariantSet = null;
    $this->S_CombiID = null;

  }*/
    /*
  // ===============================================================================================
  function SaveVariantParts()
  {
    $variant_id = $this->DB_VariantSet['v_id'];
    $penta_id = $this->DB_VariantSet['p_id'];

    $this->GetVariantPartlist($variant_id, $penta_id);

    $toDelete = [];
    $toInsert = [];

    foreach ($this->advicingParts as $group_id => &$list) {
      $group = $this->allPartGroups[$group_id];

      if (toBool($group['allow_multi'])) {
        foreach ($list as $part_id => &$set) {
          if (($set['count'] > 0) && !isset ($_POST['part'][$part_id]))
            $toDelete[] = $part_id;

          if (!($set['count']) && isset ($_POST['part'][$part_id]))
            $toInsert[] = $part_id;
        }
      } else {
        if ($group['parts'][0] != $_POST['group'][$group_id]) {

          $toDelete = safe_merge($toDelete, $group['parts']);
          $toInsert[] = $_POST['group'][$group_id];
        }
      }
    }

    foreach ($this->engeneeringParts as $group_id => &$list) {
      $group = $this->allPartGroups[$group_id];

      if (toBool($group['allow_multi'])) {
        foreach ($list as $part_id => &$set) {
          if (($set['count'] > 0) && !isset ($_POST['part'][$part_id]))
            $toDelete[] = $part_id;

          if (!($set['count']) && isset ($_POST['part'][$part_id]))
            $toInsert[] = $part_id;
        }
      } else {
        if ($group['parts'][0] != $_POST['group'][$group_id]) {
          $toDelete = array_merge($toDelete, $group['parts']);
          $toInsert[] = $_POST['group'][$group_id];
        }
      }
    }

    $qry = $this->leitWartePtr->newQuery('variant_parts_mapping')
        ->where('variant_id', '=', $variant_id)
        ->where('part_id', 'in', $toDelete);

    $result = $qry->delete();

    if (count($toInsert)) {
      foreach ($toInsert as $part_id) {
        $insert_cols = ['variant_id' => $variant_id, 'part_id' => $part_id];
        $this->leitWartePtr->newQuery('variant_parts_mapping')->insert($insert_cols);
      }
    }

  }*/
    /*
  // ===============================================================================================
  function SaveVariantUsedEcus()
  {
    foreach ($this->allEcuNames as $ecu_id => $ecu_name) {
      $used = isset($_REQUEST['ecu_used'][$ecu_id]) ? 't' : 'f';

      if (isset ($this->DB_allRevisions[$ecu_id])) {
        $rSet = &$this->DB_allRevisions[$ecu_id];

        $variant_id = $rSet['variant_id'];
        $penta_id = $rSet['penta_id'];

        if ($used != $rSet['ecu_used']) {
          $qry = $this->ecuPtr->newQuery('variant_ecu_revision_mapping');
          $qry = $qry->where('variant_id', '=', $variant_id);
          $qry = $qry->where('penta_id', '=', $penta_id);
          $qry = $qry->where('ecu_id', '=', $ecu_id);
          $res = $qry->update(['ecu_used'], [$used]);
          $rSet['ecu_used'] = $used;
        }
      } else if (toBool($used)) {
        sscanf($this->S_CombiID, '%d:%d', $variant_id, $config_id);

        $insert = [
            'variant_id' => $variant_id,
            'penta_id' => 0,
            'ecu_id' => $ecu_id,
            'odx_download_mode' => 0
        ];


        $this->ecuPtr->newQuery('variant_ecu_revision_mapping')->insert($insert);
      }
    }
  }*/

    // ===============================================================================================
    function LimitedSpace($value, $type, $border = 0, $numChar = 24, $attribute = "", $optionalClass = "") {
        $symLarger = "";
        switch ($type) {
            case 'ascii':
            case 'string':
            case '(string)':
            case 'blob':
                if (strlen($value) > $numChar) {
                    $symLarger = '&raquo;';
                    $border = max(1, $border);
                }

                return sprintf(
                    '<input type="text" value="%s" class="limitedLeft %s %s" readonly="1" size="%s" %s>'
//                    .'<span class="W020">%s</span>'
                    , $value, ($border ? " border$border" : ""), $optionalClass, $numChar, $attribute
//                    , $symLarger
                );

            default:
                return sprintf(
                    '<input type="text" value="%s" class="limitedCenter %s%s" readonly="1" size="10" %s>',
                    $value, (($border) ? " border$border" : ""), $optionalClass, $attribute);
        }
        return $value;
    }
    /*
  // ===============================================================================================
  function WriteHtml_CocValue($value, $datatype, $col, $colInfo)
  {
    if ($datatype == 'boolean') {
      return toBool($value) ? 'JA' : 'NEIN';
    }

    if (isset ($colInfo['map'])) {
      return $colInfo['map'][$value];
    }

    if ($datatype == 'date') {
      return to_locale_date($value);
    }

    return nl2br(htmlspecialchars($value));
  }*/
    /*
  // ===============================================================================================
  function WriteHtml_CocEditControl($value, $datatype, $col, $descr)
  {
    $content = "";
    $control_type = "";

    if ($descr['control_type'] && preg_match('/^([a-z]*)[ ](.*)$/', $descr['control_type'] . " ", $match)) {
      $control_type = $match[1];
      $control_param = trim($match[2]);
    }


    if (isset ($descr['value_select']))
      $descr['sel'] = explode(',', $descr['value_select']);

    if (isset ($descr['sel']) || isset ($descr['map'])) {
      if (isset ($descr['sel'])) $array = &$descr['sel']; else
        if (isset ($descr['map'])) $array = &$descr['map']; else $array = [];

      $allow_null = empty ($value) || (isset ($descr['null']) ? toBool($descr['null']) : false);

      if (count($array)) {
        $content = "<select name=coc[$col]>";
        if ($allow_null)
          $content .= '<option value=""> --- </option>';


        foreach ($array as $i => $val) {
          if (isset ($descr['map']))
            $selected = ($i == $value) ? " selected" : "";
          else
            $selected = ($val == $value) ? " selected" : "";

          $content .= "<option value=\"$i\"$selected>$val</option>";
        }
        $content .= "</select>";
        return $content;
      }
    }

    switch ($control_type) {
      case 'textarea':
        return "<textarea name=\"coc[$col]\">$value</textarea>";

    }

    switch ($datatype) {
      case 'boolean':
        $checked[0] = toBool($value) ? '' : ' checked';
        $checked[1] = toBool($value) ? ' checked' : '';
        $content = "<input type=\"radio\" value=\"f\" name=\"coc[$col]\"{$checked[0]}><span class=\"LabelX W060\">NEIN</span><input type=\"radio\" value=\"t\" name=\"coc[$col]\"{$checked[1]}> JA";
        break;

      case 'integer':
      case 'bigint':
        $attr_min = isset ($descr['min_value']) ? " min=\"{$descr['min_value']}\"" : "";
        $attr_max = isset ($descr['max_value']) ? " min=\"{$descr['max_value']}\"" : "";
        $content = "<input type=\"number\" name=\"coc[$col]\" value=\"$value\"{$attr_min}{$attr_max}>";
        break;

      case 'date':
        $value = to_locale_date($value);
        $content = "<input type=\"text\" size=\"10\" name=\"coc[$col]\" class=\"datumEingabe\" value=\"$value\">";
        break;

      default:
        $content = "<input type=\"text\" size=\"20\" name=\"coc[$col]\" value=\"$value\">";
        break;
    }

    return $content;
  }*/
    /*
  // ===============================================================================================
  function GetCoc()
  {
    $this->cocPrinter = new AClass_Coc();
    $this->cocPrinter->Execute();
  }*/
    /*
  // ===============================================================================================
  function CocTestdruck()
  {
    $this->cocPrinter = new AClass_Coc();
    $this->cocPrinter->CreateDummyDocument($this->DB_VariantSet['v_id']);
  }*/
    /*
  // ===============================================================================================
  function WriteHtml_Variant_CocData()
  {
    if (!$this->DB_VariantSet)
      return;


    $table_info = $this->vehicleVariantsPtr->queryColumnInfo();
    $qry = $this->vehicleVariantsPtr->newQuery('coc_description')
        ->join('units', 'using unit_id', 'left join')
        ->orderBy('field_set')
        ->orderBy('id1')
        ->orderBy('id2')
        ->orderBy('id3')
        ->orderBy('id4')
        ->orderBy('description');

    $coc_description = $qry->get('*,COALESCE(field_ident1, 0) as id1, COALESCE(field_ident2, 0) as id2, COALESCE(field_ident3, 0) as id3, COALESCE(field_ident4, 0) as id4');


    $this->IncludeTableInfo();

    $formheader = $this->GetHtml_FormHeader("idr_", 4, "cocForm");

    echo <<<HEREDOC
  <div class="seitenteiler flexTop scrollboxY" id="idCoc">
$formheader
      <div class="infoBlock">
HEREDOC;

    $this->WriteHtml_VariantInfoBlock('Fahrzeugeigenschaften (COC-Werte)', self::PRIV_VARIANT_COC);

    $variant_id = $this->DB_VariantSet['v_id'];
    $variant_data = $this->vehicleVariantsPtr
        ->newQuery()
        ->where('vehicle_variant_id', '=', $variant_id)
        ->getOne('*');
    #
    $readonly = $this->m_EditMode ? '' : ' readonly';
    $released = false;
    $approval_date = to_locale_date($variant_data['approval_date']);

    if (isset ($variant_data['coc_released_by'])) {
      $released = true;
      $date = to_locale_date($variant_data['coc_released_date']);
      $user = $this->vehicleVariantsPtr->newQuery('users')->where('id', '=', $variant_data['coc_released_by'])->getOne('fname,lname');
      $username = $user['fname'] . ' ' . $user['lname'];

      $status = <<<HEREDOC
        <span class="LabelX W200 largerFont">Status:</span>
            <input type="text" size="30" readonly value="ABGESCHLOSSEN"><br>
        <span class="LabelX W200 largerFont">Durch:</span>
            <input type="text" size="30" readonly value="$username"><br>
        <span class="LabelX W200 largerFont">Datum:</span>
            <input type="text" size="30" readonly value="{$date}"><br>

HEREDOC;

    } else
      if ($this->m_EditMode) {
        $status = <<<HEREDOC
        <span class="LabelX W200 largerFont">Status:</span>
            <select name="coc[coc_released]">
                <option value="0">WIRD NOCH BEARBEITET</option>
                <option value="1">ABGESCHLOSSEN</option>
            </select>

HEREDOC;

      } else {
        $status = <<<HEREDOC
        <span class="LabelX W200 largerFont">Status:</span>
            <input type="text" size="30" readonly value="WIRD NOCH BEARBEITET">
HEREDOC;
      }

    echo <<<HEREDOC
      </div>

      <div style="margin: 30px 0 10px 0;">
        <span class="LabelX W200 largerFont">COC Zulassungscode:</span>
            <input type="text" size="30" name="coc[approval_code]" value="{$variant_data['approval_code']}"$readonly><br>
        <span class="LabelX W200 largerFont">Datum d. COC Zulassung:</span>
            <input type="text" size="30" name="coc[approval_date]" value="{$approval_date}"$readonly class="datepicker"><br>
        $status
      </div>
      <table class="variantCocValues">
        <thead>
          <tr>
            <th>Feld</th><th>Bereich</th><th>Wert</th><th>Einheit</th><th>Beschreibung</th>
          </tr>
        </thead>
	    <tbody>
HEREDOC;

    foreach ($coc_description as $descr) {
      $col = $descr['coc_column'];
      $caption = safe_val($descr, 'description', '{{' . $col . '}}');
      $area = $descr['field_set'];

      $ident = safe_val($descr, 'field_prefix', '');
      for ($i = 1; $i <= 4; $i++)
        if (isset ($descr["field_ident$i"]))
          $ident .= $descr["field_ident$i"] . '.';

      $datatype = $table_info[$col];
      if (isset ($this->cocHidden[$col]) || isset ($this->configInfo[$col]))
        continue;


      $colInfo = isset($this->cocTableInfo[$col]) ? $this->cocTableInfo[$col] : [];
      $unit = safe_val($descr, 'name', '');

      echo "
          <tr>
            <td>$ident</td><td>$area</td>
            <td>";

      $value = stripslashes($variant_data[$col]);

      if (!$released && ($this->m_EditMode == self::EDIT_Variant))
        $content = $this->WriteHtml_CocEditControl($value, $datatype, $col, $descr);
      else
        $content = $this->WriteHtml_CocValue($value, $datatype, $col, $descr);

      echo "$content</td><td>$unit</td><td><div class=\"ttip\">$caption<span class=\"ttiptext\">$col</span></div></td>
          </tr>\n";
    }

    echo <<<HEREDOC
        </tbody>
      </table>
    </form>
  </div>
HEREDOC;

    if (isset ($this->cocPrinter)) {
      echo "  <div>\n";
      echo $this->cocPrinter->GetHtml_ErrorVins();
      echo $this->cocPrinter->GetHtml_Downloads();
      echo "  </div\n>";
    }
//
//    echo <<<HEREDOC
//  <div class="variantPartsButtons stdborder">
//    <div style="width:200px;" id="ausgabe"></div>
//    <div class="MiniButtons">
//
//HEREDOC;
//
//    $this->GetEnggPrivileges(self::PRIV_VARIANT_COC, 0);
//    $allow_edit = toBool($this->m_Permission[self::PRIV_VARIANT_COC]['current']);
//    if ($released)
//      $allow_edit = false;
//
//    $url = $this->GetHtml_Url();
//    $xtraButton = <<<HEREDOC
//        <li><a href="$url&command=downloadPdf" class="sts_submenu W080"><img src="/images/symbols/download_active.png"> PDF</a></li>
//HEREDOC;
//
//    $this->WriteHtml_CommandButtons($allow_edit, ($this->m_EditMode == self::EDIT_Variant), BT_NONE, BT_NONE, BT_NONE, BT_NONE, BT_NONE, $xtraButton);
//
//    echo <<<HEREDOC
//    </div>
//  </div>
//HEREDOC;

  }*/
    /*
  // ===============================================================================================
  function CreateNewVariant()
  {

  }*/
    /*
  // ===============================================================================================
  function CopyVariant($newConfigString)
  {
    $src = $this->DB_VariantSet['v_id'];
    $qry = $this->vehicleVariantsPtr->newQuery();
    $res = $qry->where('vehicle_variant_id', '=', $src)->get('*');
    if (!$res)
      return $this->SetError(STS_ERROR_DB_SELECT, $qry->GetLastError());

    $copy_from = $res[0];
    $src_windchill_name = $copy_from['windchill_variant_name'];
    $tmp_windchill_name = " COPY_$src_windchill_name";

    unset ($copy_from['vehicle_variant_id']);
    unset ($copy_from['windchill_variant_name']);

    $insert_cols = implode(',', array_keys($copy_from));

    $sql = "insert into vehicle_variants (windchill_variant_name,$insert_cols) select '$tmp_windchill_name',$insert_cols from vehicle_variants where vehicle_variant_id = $src";

    $qry = $this->vehicleVariantsPtr->newQuery();
    if (!$qry->query($sql))
      return $this->SetError(STS_ERROR_DB_INSERT, $qry->GetLastError());

    $qry = $this->vehicleVariantsPtr->newQuery();
    $res = $qry->where('windchill_variant_name', '=', $tmp_windchill_name)->getOne('vehicle_variant_id');
    if (!$res)
      return $this->SetError(STS_ERROR_DB_SELECT, $qry->GetLastError());

    $new_variant_id = $res['vehicle_variant_id'];
  }*/
    /*
  // ===============================================================================================
  function Save_Variant_CocData()
  {
    if (!$this->DB_VariantSet)
      return;

    $this->GetEnggPrivileges(self::PRIV_VARIANT_COC, 0);
    $allow_edit = toBool($this->m_Permission[self::PRIV_VARIANT_COC]['current']);
    if (!$allow_edit)
      return $this->SetError(STS_ERROR_NO_PRIVILEGS, "COC Werte zu setzen");

    if ($_REQUEST['coc']['coc_released'] && ($allow_edit != 'owner'))
      return $this->SetError(STS_ERROR_NO_PRIVILEGS, "COC Werte frei zu geben");


    $table_info = $this->vehicleVariantsPtr->queryColumnInfo();
    $qry = $this->vehicleVariantsPtr->newQuery('coc_description')
        ->join('units', 'using unit_id', 'left join')
        ->orderBy('field_set')
        ->orderBy('id1')
        ->orderBy('id2')
        ->orderBy('id3')
        ->orderBy('id4')
        ->orderBy('description');

    $coc_description = $qry->get('*,COALESCE(field_ident1, 0) as id1, COALESCE(field_ident2, 0) as id2, COALESCE(field_ident3, 0) as id3, COALESCE(field_ident4, 0) as id4');


    $this->IncludeTableInfo();


    $variant_id = $this->DB_VariantSet['v_id'];
    $update = [];
    $post = &$_POST['coc'];

    $previous_data = $this->vehicleVariantsPtr->newQuery()->where('vehicle_variant_id', '=', $variant_id)->getOne('*');

    get_safe_update($update, $previous_data, $post, 'approval_code');

    $value = to_iso8601_date($_POST['coc']['approval_date']);
    if (substr(safe_val($previous_data, 'approval_date', ''), 0, 10) != $value)
      $update['approval_date'] = $value;


    if ($_POST['coc']['coc_released']) {
      $update['coc_released_by'] = $_SESSION['sts_userid'];
      $update['coc_released_date'] = date('Y-m-d H:i:s');
    }


    if (!isset ($previous_data['coc_released_by'])) {

      foreach ($coc_description as $descr) {
        $col = $descr['coc_column'];

        if ($col == 'vehicle_variant_id')
          continue;
        if ($col == 'year')
          continue;

        $colInfo = isset ($this->cocTableInfo[$col]) ? $this->cocTableInfo[$col] : [];
        $value = addslashes($_POST['coc'][$col]);
        $datatype = $table_info[$col];

        if (isset ($colInfo['sel'])) {
          $value = $colInfo['sel'][$value];
        }

        if ($datatype == 'boolean')
          $value = toBool($value) ? 't' : 'f';

        if (($datatype == 'integer') && ($value == ""))
          $value = null;

        if (safe_val($previous_data, $col, '') != $value)
          $update[$col] = $value;
      }
    }


    if (count($update)) {
      $query = $this->vehicleVariantsPtr->newQuery()
          ->where('vehicle_variant_id', '=', $variant_id);
      $result = $query->update($update);
      if (!$result)
        $this->SetError($query->GetLastError());
    }
  }*/
    /*
  // ===============================================================================================
  function CopyVariantCocData()
  {
    try {
      if (!$this->DB_VariantSet)
        return $this->SetError(STS_ERROR_PHP_ASSERTION, "S_variantRev=NULL");

      $dstListe = $_REQUEST['copyTo'];
      if (count($dstListe) == 0)
        return;


      $id_src = $this->DB_VariantSet['v_id'];
      $qry = $this->vehicleVariantsPtr->newQuery();
      $qry = $qry->where('vehicle_variant_id', '=', $id_src);
      $cols = $qry->getOne();

      foreach (self::VEHICLE_VARIANT_NO_COPY as $col)
        unset ($cols[$col]);

      $update_cols = array_keys($cols);
      $update_strs = [];
      foreach ($update_cols as $key) {
        $update_strs[] = "{$key}=src.{$key}";
      }
      $update_str = implode(",\n  ", $update_strs);


      $list_to_update = "";
      foreach ($dstListe as $combi_id) {
        sscanf($combi_id, "%d:%d", $v_id, $p_id);
        if ($v_id != $id_src) {
          if (!empty ($list_to_update))
            $list_to_update .= ',';

          $list_to_update .= $v_id;
        }
      }


      $sql = <<<SQLDOC
UPDATE vehicle_variants AS dst
SET $update_str
FROM (
  SELECT *
  FROM vehicle_variants
  WHERE vehicle_variant_id=$id_src
  ) AS src
WHERE dst.vehicle_variant_id in ($list_to_update);
SQLDOC;

      $qry = $this->vehicleVariantsPtr->newQuery();
      $res = $qry->query($sql);
    } catch (Exception $e) {
      $this->SetError(STS_ERROR_PHP_EXCEPTION, $e->getMessage());
    }
  }*/

    // ===============================================================================================
    //////////////////////////////////////////////////////////////////////////////////////////////////
    // ===============================================================================================
    function QueryVariantEcuValues($variant, $penta_id) {
        if (!isset($penta_id))
            $penta_id = 0;

        $query = $this->variantDataPtr->newQuery()
            ->where('vehicle_variant_id', '=', $variant)
            ->where('overlayed_penta_id', '=', $penta_id)
            ->orderBy('ecu_parameter_id');

        return $query->get('*', 'ecu_parameter_id');
    }

    /*
  // ===============================================================================================
  function UpdateByOverwritingParts(&$ecuValues, $ecu_id, $windchill_parts, $penta_parts)
  {
    $parts = array_unique(array_merge(pg_explode($windchill_parts), pg_explode($penta_parts)));
    if (count($parts)) {
      $qry = $this->variantDataPtr->newQuery('parts')
          ->join('ecu_parameters_overwrite', 'using (parameter_overwrite_id)')
          ->where('ecu_parameters_overwrite.ecu_id', '=', $ecu_id)
          ->where('parts.part_id', 'in', $parts);

      $params = $qry->get('*', 'ecu_parameter_id');
      if (!$params)
        return;

      foreach ($params as $param_id => $set) {
        if (isset ($ecuValues[$param_id])) {
          if (!isset ($set['tag_disabled']))
            $set['tag_disabled'] = $ecuValues[$param_id]['tag_disabled'];

          $ecuValues[$param_id] = $set;
        }
      }
    }
  }
*/
    // ===============================================================================================
    function GetParameterOrderList() {
        $result = $tmp = $tail = [];
        foreach ($this->DB_Parameters as $psid => &$set) {
            $deleted = safe_tag_value($set['tags'], 'deleted', "");
            if (($psid <= 2) || ($deleted == 'all') || (strpos($deleted, 'odx.sts.02') !== false))
                continue;

            $order = safe_tag_value($set['tags'], 'order', $set['order']);
            if ($order < 0) {
                $tail[] = $psid;
            } else {
                make_node($tmp, $order);
                $tmp[$order][] = $psid;
            }
        }
        ksort($tmp);
        foreach ($tmp as $list)
            foreach ($list as $psid)
                $result[] = $psid;
        foreach ($tail as $psid)
            $result[] = $psid;

        return $result;
    }

    // ===============================================================================================
    function Convert2Hex(&$value, $paramset, $odx_01_type = self::odx01_undefined) {
        $signed = false;

        if (strtolower(substr($value, 0, 2)) == '0x')
            return true;

        $bigEndian = toBool(safe_tag_value($paramset, 'bigEndian', false));
        $strType = strtolower(safe_tag_value($paramset, 'type'));
        $byteCount = intval(safe_tag_value($paramset, 'byteCount', 0));

        if (empty($strType)) {
            switch ($odx_01_type) {
                case self::odx01_int:
                case self::odx01_float:
                case self::odx01_string:
                case self::odx01_bool:
                case self::odx01_undefined:
                    return false;
            }
        }

        if (!$byteCount)
            return false;

        switch ($strType) {
            case 'bin':
            case 'blob':
                if ($byteCount)
                    $tmp = substr($value, 0, $byteCount);
                $hex = bin2hex($tmp);
                if (strlen($hex))
                    $value = '0x' . $hex;
                else
                    $value = '';
                return true;

            case 'ascii':
                $value = '0x' . $this->Utf8ToAsciiHex($value, $byteCount);
                return true;

            //----------------------------------------------

            case 'signed':
                $signed = true;
            case 'unsigned':
                if (!$byteCount)
                    return false;

                $factor = floatval(safe_tag_value($paramset, 'factor', 1.0));
                $offset = intval(safe_tag_value($paramset, 'offset', 0));

                $charCount = 2 * $byteCount;
                $adjusted = ($value * $factor) + $offset;

                if (!$signed && ($adjusted < 0))
                    return false;

                $hex = sprintf("%0$charCount" . 'x', $adjusted);
                if (strlen($hex) > $charCount) {
                    $value = substr($hex, -$charCount);
                } else {
                    if ($signed && ($hex[0] > '7'))
                        $value = str_pad($hex, $charCount, 'f', STR_PAD_LEFT);
                    else
                        $value = str_pad($hex, $charCount, '0', STR_PAD_LEFT);
                }

                if ($bigEndian)
                    $value = $this->SwapHexByteOrder($value);

                $value = "0x" . $value;
                return true;

        }
        return false;

    }

    // ===============================================================================================
    function SwapHexByteOrder($hex) {
        $result = "";
        $len = strlen($hex) / 2;
        for ($i = $len - 1; $i >= 0; $i--)
            $result .= substr($hex, $i * 2, 2);
        return $result;
    }

    // ===============================================================================================
    function AsciiHexToUtf8($hex) {
        $ascii = hex2bin($hex);

        if ($this->m_ecu_to_utf8) {
            $ascii = str_replace(array_keys($this->m_ecu_to_utf8), array_values($this->m_ecu_to_utf8), $ascii);
        }

        $end = strpos($ascii, "\0");
        if ($end !== false)
            $ascii = substr($ascii, 0, $end);
        return $ascii;
    }

    // ===============================================================================================
    function Utf8ToAsciiHex($utf8, $max_size = null) {
        if ($this->m_utf8_to_ecu) {
            $ecu_ascii = str_replace(array_keys($this->m_utf8_to_ecu), array_values($this->m_utf8_to_ecu), $utf8);
            $hex = bin2hex($ecu_ascii);
        } else {
            $hex = bin2hex($utf8);
        }

        if (isset ($max_size)) {
            $size = 2 * $max_size;
            $len = strlen($hex);
            if ($len > $size)
                return substr($hex, 0, $size);
            else if ($len < $size)
                return str_pad($hex, $size, '0', STR_PAD_RIGHT);
        }

        return $hex;
    }

    // ===============================================================================================
    function HexToInt($hex, $nChar, $signed, $bigEndian = false) {
        if (empty($nChar) && $signed)
            return 'error';

        if ($bigEndian)
            $hex = $this->SwapHexByteOrder($hex);

        $ovlen = strlen($hex) - $nChar;
        if ($ovlen > 0) {
            $overflow = substr($hex, 0, $ovlen);
            if ($overflow != str_repeat('0', $ovlen)) {
                if (!$signed || ($overflow != str_repeat('f', $ovlen)))
                    return 'error';
            }
            $ovlen = 0;
            $value = substr($hex, $ovlen);
        }

        $dezimal = base_convert($hex, 16, 10);

        if (($ovlen == 0) && $signed && ($hex[0] > '7'))
            $dezimal -= base_convert(str_repeat('f', $nChar), 16, 10) + 1;

        return $dezimal;
    }

    // ===============================================================================================
    function GetValue_odx01($valueSet, $paramset, &$type, $dest = self::output_odx02) {
        $type_id = $paramset['type_id'];
        $strType = $this->DB_allTypes[$type_id];
        $type = substr($strType, 6);

        if (isset ($valueSet[$strType])) {
            $value = $valueSet[$strType];


            if ($type == 'bool') {
                $value = toBool($value);
                switch ($dest) {
                    case self::output_php:
                        break;
                    case self::output_db:
                        $value = ($value ? 't' : 'f');
                        break;
                    case self::output_odx02:
                        $value = ($value ? '1' : '0');
                        break;
                    case self::output_gui:
                        $value = ($value ? '1 = An' : '0 = Aus');
                        break;
                }
            }
            return $value;
        }
        return null;
    }

    // ===============================================================================================
    function GetValue_odx02($hexvalue, $paramset) {
        $scanResult = [];
        $signed = false;
        $type = safe_tag_value($paramset, 'type', '');
        $byteCount = safe_tag_value($paramset, 'byteCount', 0);
        $factor = safe_tag_value($paramset, 'factor', 1);
        $offset = safe_tag_value($paramset, 'offset', 0);
        $bigEndian = safe_tag_value($paramset, 'bigEndian', false);
        $factor = ($factor && ($factor != 1)) ? (1 / $factor) : 1;

        $scanResult['type'] = $type;
        $scanResult['error'] = 0;
        $scanResult['value'] = '';

        if (strtolower(substr($hexvalue, 0, 2)) == '0x')
            $hex = substr($hexvalue, 2);
        else {
            if (($hexvalue == '') and ($ype == 'ascii'))
                return $scanResult;

            if (preg_match('/^[0-9a-f]+$/', $hexvalue)) {
                $hex = $hexvalue;
            } else {
                $scanResult['error'] = ERROR_NO_HEXADZIMAL_STRING;
                $scanResult['value'] = $hexvalue;
                return $scanResult;
            }
        }
        if ($byteCount == 0) {
            $scanResult['error'] = ERROR_MISSING_TAG_SIZE;
            return $scanResult;
        }

        switch ($type) {
            case 'signed':
                $signed = true;
            case 'unsigned':
                $value = $this->HexToInt($hex, $byteCount * 2, $signed, $bigEndian);
                if ($value == 'error')
                    $scanResult['error'] = ERROR_CANNOT_CONVERT_TO_HEX;
                else
                    $scanResult['value'] = ($value - $offset) * $factor;

                break;

            case 'ascii':
                $scanResult['value'] = $this->AsciiHexToUtf8(substr($hex, 0, 2 * $byteCount));
                if ($pos = strpos($scanResult['value'], "\0"))
                    $scanResult['value'] = substr($scanResult['value'], 0, $pos);
                break;

            case '':
                $scanResult['error'] = ERROR_MISSING_TAG_TYPE;
                break;

            case 'bin':
                $scanResult['type'] = 'blob';
            case 'blob':
                $scanResult['value'] = substr($hex, 0, 2 * $byteCount);
                break;

            default:
                $scanResult['error'] = ERROR_UNKNOWN_TYPE;
                break;

        }

        return $scanResult;
    }

    // ===============================================================================================
    function FillupHex($hexValue, $type, $numBytes) {
        $hexValue = substr($hexValue, 2);
        $numChar = 2 * $numBytes;
        switch ($type) {
            case self::odx01_int:
            case self::odx01_float:
            case self::odx01_bool:
            case 'signed':
            case 'unsigned':
                if (strlen($hexValue) < $numChar)
                    return '0x' . str_pad($hexValue, $numChar, '0', STR_PAD_LEFT);
                if (strlen($hexValue) == $numChar)
                    return '0x' . $hexValue;
                break;

            case self::odx01_string:
            case 'ascii':
            case 'blob':
                if (strlen($hexValue) < $numChar)
                    return '0x' . str_pad($hexValue, $numChar, '0', STR_PAD_RIGHT);
                if (strlen($hexValue) == $numChar)
                    return '0x' . $hexValue;
                break;
        }
        return false;
    }

    // ===============================================================================================
    function SetDataError($paramSet, $errorNo = ERROR_EMPTY_VALUE) {
        $param_name = $paramSet['odx_name'];
        if ($this->m_OdxVerShow > 1)
            $param_name = safe_tag_value($paramSet, 'id', $param_name);

        $pset_id = $paramSet['pset_id'];
        if (!isset ($this->variantDataErrors[$errorNo])) {
            $this->variantDataErrors[$errorNo] = [0 => $this->DB_VariantSet['i_name']];
        }
        $this->variantDataErrors[$errorNo][$pset_id] = $param_name;
    }

    // ===============================================================================================
    function MakeInput($value, $id, $type, $max_size = 0) {
        $input_type = 'text';
        $value_attr = " value=\"$value\"";
        $hexPrefix = "";
        $extra = "";

        switch ($type) {
//             case 'hidden':                return '<input name="used['.$id.']" type="hidden" value="1">';
            case 'hidden':
                return '';

            case 'signed':
            case 'unsigned':
            case 'int':
                $input_type = 'text';
                break;

            case 'double':
                $extra = ' step="0.1"';
                $input_type = 'number';
                break;

            case 'blob':
                $max_size = $max_size * 2;
                $hexPrefix = "0x ";
            case 'ascii':
            case 'string':
                $extra = ' size="24"';
                if ($max_size)
                    $extra .= " maxsize=\"$max_size\"";
                break;

            case 'bool':
                $selected = toBool($value) ? ["", " checked"] : [" checked", ""];

                return <<<HEREDOC
        <div id="id_input-$id" class="inputBool">
            <input name="value[$id]" type="radio" value="0"{$selected[0]}><span class="LabelX W060">0 = Aus</span>
            <input name="value[$id]" type="radio" value="1"{$selected[1]}>1 = An
        </div>
HEREDOC;

        }
        return <<<HEREDOC
        $hexPrefix<input id="id_input-$id" name="value[$id]" type="$input_type"{$value_attr}{$extra}>
HEREDOC;

    }

    // ===============================================================================================
    function ClearWBackup_ParameterValues($action, $list_combi_ids, $list_param_ids = null, $bDelete = true) {
        $action = substr($action, 0, 6);
        if (count($list_combi_ids)) {
            $user_id = $_SESSION['sts_userid'];
            $csv_variants = implode("','", $list_combi_ids);

            $where = " where (vehicle_variant_id::text || ':' || overlayed_penta_id::text) in ('$csv_variants')";

            if (isset ($list_param_ids) && count($list_param_ids)) {
                $csv_param_ids = implode(',', $list_param_ids);
                $where .= " and ecu_parameter_id in ($csv_param_ids)";
            }

            $ql = "insert into vehicle_variant_data_history "
                . "(backup_time, action, vehicle_variant_id, ecu_parameter_id, value_int, value_double, value_string, value_bool, tag_disabled, value, overlayed_penta_id, set_by, sw_preset_type, changed_by) "
                . "select now(), '$action', vehicle_variant_id, ecu_parameter_id, value_int, value_double, value_string, value_bool, tag_disabled, value, overlayed_penta_id, set_by, sw_preset_type, $user_id "
                . "from vehicle_variant_data" . $where;

            $db = $this->variantDataPtr->newQuery();
            if (!$db->query($ql))
                return $this->SetError(STS_ERROR_DB_DELETE, $db->GetLastError());

            if ($bDelete) {
                $ql = "delete from vehicle_variant_data " . $where;
                $db = $this->variantDataPtr->newQuery();
                if (!$db->query($ql))
                    return $this->SetError(STS_ERROR_DB_DELETE, $db->GetLastError());
            }
        }
        return 0;
    }

    // ===============================================================================================
    function Save_Variant_ParameterValue(&$postvals, $param_id, $pset_id, $et, $windchill_id, $overlayed_penta_id, $no_macro = false) {
        $updateSet = [
            'value_int' => null,
            'value_double' => null,
            'value_string' => null,
            'value_bool' => null,
            'value' => null,
            'tag_disabled' => null,
            'set_by' => null,
            'sw_preset_type' => null,
        ];

        $paramSet = &$et['tags'];
        $valueSet = &$this->DB_VariantEcuData[$param_id];
        $value = null;
        $has_value = false;
        $editValues = &$postvals['value'];

        $error = 0;
        $type = '';
        $typ_odx01 = 5;
        $col_odx01 = "";
        $val_odx01 = "";
        $save_odx02 = true;

        $disabled = null;
        $sw_preset_type = null;

        if ($this->S_OdxSaveBoth && toBool($et['use_in_odx01'])) {
            $typ_odx01 = intval($et['type_id']);
        }

        if (isset ($postvals['active']))
            $disabled = isset ($postvals['active'][$param_id]) ? 'f' : 't';


        $id = safe_tag_value($paramSet, 'id', $et['odx_name']);
        $action = safe_tag_value($paramSet, 'action', '');

        if (tag_exists($paramSet, 'type')) {
            $type = safe_tag_value($paramSet, 'type', '');
        } else
            if ($typ_odx01 != self::odx01_undefined) {
                $this->SetWarning(ERROR_MISSING_TAG_TYPE, "$param_id:$id");
            } else {
                $error = $this->SetError(ERROR_CANNOT_SAVE_ODX02_id, "$param_id:$id");
            }

        $byteCount = safe_tag_value($paramSet, 'byteCount', 0);
        if (!$byteCount) {
            $this->SetWarning(ERROR_MISSING_TAG_SIZE, "$param_id:$id");
        }


        if ($action != 'r') {
            $has_value = true;
            $revConst = safe_tag_value($paramSet, 'value', '');
            if ($pset_id <= 2) {
                $valuetype = 'version';
                $hexValue = safe_tag_value($paramSet, 'version', '');
                $value = $revConst;
            } else {
                $valuetype = safe_tag_value($paramSet, 'valuetype', '');
                switch ($valuetype) {
                    case 'const':
                        $value = $revConst;
                        $sw_preset_type = 'C';
                        break;

                    case 'macro':

                        if ($no_macro)
                            $value = $editValues[$param_id];
                        else {
                            $value = $this->GetVariableValue($revConst);
                            $sw_preset_type = 'M';
                        }
                        break;

                    case 'list':
                        break;

                    case 'dynmc':
                        $value = null;
                        $hex_value = null;
                        $has_value = false;
                        break;

                    case 'deflt':
                        $default = $revConst;
                        if (toBool($postvals['isDefault'][$param_id])) {
                            $sw_preset_type = 'D';
                            $value = $default;
                            break;
                        }

                    default:
                        $value = $editValues[$param_id];
                        break;
                }
            }
        }

        if ($has_value) {
            if (strtolower(substr($value, 0, 2)) == '0x') {
                if (empty($type)) {
                    $hex_value = $value;
                    switch ($typ_odx01) {
                        case self::odx01_int:
                        case self::odx01_bool:
                        case self::odx01_float:
                            $value = base_convert(substr($hex_value, 2), 16, 10);
                            break;

                        case self::odx01_string:
                            $value = $this->AsciiHexToUtf8(substr($hex_value, 2));
                            break;
                    }
                } else {

                    $hex_value = $this->FillupHex($value, $type, $byteCount);
                    $resConvert = $this->GetValue_odx02($hex_value, $paramSet);
                    if ($resConvert['error']) {
                        return $this->SetError($resConvert['error'], $value);
                    } else {
                        $value = $resConvert['value'];
                    }
                }
            } else {

                switch ($type) {
                    case 'signed':
                    case 'unsigned':
                        if ($value == '')
                            //return STS_NO_ERROR; //2019------

                            break;

                    case 'blob':
                        $i = (substr($value, 0, 2) == '0x') ? 2 : 0;
                        $value = str_pad(substr($value, $i), 2 * $byteCount, '0', STR_PAD_RIGHT);;
                        $hex_value = "0x$value";
                        break;

                    case 'string':
                    case 'ascii':
                        // $value      = $editValues[$param_id];
                        break;
                }
            }

            switch ($typ_odx01) {
                case 0:
                    break;

                case self::odx01_int:
                    $val_odx01 = intval($value);
                    $updateSet['value_int'] = $val_odx01;
                    $col_odx01 = 'value_int';
                    break;

                case self::odx01_float:
                    if ($value == '')
                        return STS_NO_ERROR;

                    $val_odx01 = floatval($value);
                    $updateSet['value_double'] = $val_odx01;
                    //$vals[1] = $val_odx01;
                    $col_odx01 = 'value_double';
                    break;

                case self::odx01_string:
                    $val_odx01 = $value;
                    $updateSet['value_string'] = $val_odx01;
                    $col_odx01 = 'value_string';
                    break;

                case self::odx01_bool:
                    $val_odx01 = toBool($value) ? 't' : 'f';
                    $updateSet['value_bool'] = $val_odx01;
                    $col_odx01 = 'value_bool';
                    break;
            }

            if (isset($hex_value)) {
                $hex_value = "$hex_value";
            } else {
                if ($save_odx02) {
                    $hex_value = $value;
                    if ($this->Convert2Hex($hex_value, $paramSet))
                        $hex_value = "$hex_value";
                    else {
                        $this->SetError(ERROR_CANNOT_CONVERT_TO_HEX, "$param_id:$id"); //"Fehler beim konvertieren des Parameters ($param_id) in hexadizimal.";
                        $hex_value = null;
                    }
                } else $hex_value = null;
            }
        } else {
            $hex_value = null;
        }


        $exists = $this->variantDataPtr->newQuery()
            ->where('vehicle_variant_id', '=', $windchill_id)
            ->where('overlayed_penta_id', '=', $overlayed_penta_id)
            ->where('ecu_parameter_id', '=', $param_id)
            ->getVal('count(*) as cnt');

        if ($exists) {
            $updateSet['value_bool'] = safe_db_value_booln($updateSet['value_bool']);
            $updateSet['value_int'] = safe_db_value_int($updateSet['value_int']);
            $updateSet['value_double'] = safe_db_value_float($updateSet['value_double']);
            $updateSet['value_string'] = safe_db_value_string($updateSet['value_string']);
            $updateSet['value'] = safe_db_value_string($hex_value);
            $updateSet['tag_disabled'] = safe_db_value_bool($disabled);
            $updateSet['set_by'] = $_SESSION['sts_userid'];
            $updateSet['sw_preset_type'] = safe_db_value_string($sw_preset_type);


            $sql = "update vehicle_variant_data set\n" .
                "value_bool     = {$updateSet['value_bool']},\n" .
                "value_int      = {$updateSet['value_int']},\n" .
                "value_double   = {$updateSet['value_double']},\n" .
                "value_string   = {$updateSet['value_string']},\n" .
                "value          = {$updateSet['value']},\n" .
                "tag_disabled   = {$updateSet['tag_disabled']},\n" .
                "set_by         = {$updateSet['set_by']},\n" .
                "sw_preset_type = {$updateSet['sw_preset_type']}\n" .
                "where  vehicle_variant_id  = $windchill_id " .
                "and    overlayed_penta_id  = $overlayed_penta_id " .
                "and    ecu_parameter_id    = $param_id";

            $result = $this->variantDataPtr->newQuery()->query($sql);

            if (!$result)
                return $this->SetError(STS_ERROR_DB_UPDATE, $db->GetLastError());
        } else {
            $insert = ['vehicle_variant_id' => $windchill_id,
                'overlayed_penta_id' => $overlayed_penta_id,
                'ecu_parameter_id' => $param_id,
                'set_by' => $_SESSION['sts_userid'],
            ];

            if (isset ($disabled))
                $insert['tag_disabled'] = $disabled;

            if ($save_odx02)
                $insert['value'] = $hex_value;

            if ($col_odx01 != '')
                $insert[$col_odx01] = $val_odx01;

            if (isset ($sw_preset_type))
                $insert['sw_preset_type'] = $sw_preset_type;

            $db = $this->variantDataPtr->newQuery();
            $result = $db->insert($insert);
            if (!$result)
                return $this->SetError(STS_ERROR_DB_INSERT, $db->GetLastError());
        }
        return STS_NO_ERROR;
    }

    // ===============================================================================================
    function Save_Variant_EcuValues(&$postvals, $windchill_id, $overlayed_penta_id) {
        $odxMethod = $_POST['selected']['odxMethod'];
        $this->errorFields = [];

        if ($this->DB_RevisionMap['odx_download_mode'] != $odxMethod) {
            $this->ecuPtr->newQuery('variant_ecu_revision_mapping')
                ->where('variant_id', '=', $this->DB_RevisionMap['variant_id'])
                ->where('penta_id', '=', $this->DB_RevisionMap['penta_id'])
                ->where('ecu_id', '=', $this->DB_RevisionMap['ecu_id'])
                ->update(['odx_download_mode', 'timestamp_last_change'], [$odxMethod, 'now()']);
        }

        $this->DB_VariantEcuData = $this->QueryVariantEcuValues($windchill_id, $overlayed_penta_id);

        if (!$this->DB_Parameters)
            $this->DB_Parameters = $this->QueryParameters($this->S_variantEcu, $this->S_variantRev['ecu_revision_id']);

        $list_param_ids = array_column($this->DB_Parameters, 'param_id');
        $this->ClearWBackup_ParameterValues('edit', ["$windchill_id:$overlayed_penta_id"], $list_param_ids, false);

        foreach ($this->DB_Parameters as $paramset_id => $param_set) {
            $value_id = $param_set['param_id'];
            $do_save_version = isset($postvals['value'][$value_id]);

            if ($paramset_id <= 2) {
//                 $postvals['used'][$param_id] = true;
                $postvals['active'][$value_id] = true;
                $do_save_version = true;
            } else {
                $vt = safe_tag_value($param_set['tags'], 'valuetype', '');
                $aktion = safe_tag_value($param_set['tags'], 'action', '');

                switch ($vt) {
                    case 'deflt':
                    case 'const':
                    case 'macro':
                    case 'dynmc':
                        $do_save_version = true;
                        break;
                    default:
                        if ($aktion == 'r')
                            $do_save_version = true;
                        break;
                }
            }

            if ($do_save_version) {
                $this->Save_Variant_ParameterValue($postvals, $value_id, $paramset_id, $param_set, $windchill_id, $overlayed_penta_id);
            }
        }

//         $diff $this->GetDifferenceToMaster
        $this->DB_VariantEcuData = $this->QueryVariantEcuValues($windchill_id, $overlayed_penta_id);
    }

    // ===============================================================================================
    function Save_Initial_EcuValues($windchill_id, $overlayed_penta_id) {
        $this->errorFields = [];
        $postvals['value'] = [];
        $postvals['active'] = [];

        if (!$this->DB_Parameters)
            $this->DB_Parameters = $this->QueryParameters($this->S_variantEcu, $this->S_variantRev['ecu_revision_id']);

        foreach ($this->DB_Parameters as $paramset_id => $param_set) {
            $value_id = $param_set['param_id'];
            $vt = safe_tag_value($param_set['tags'], 'valuetype', '');

            switch ($vt) {
                case 'deflt':
                case 'const':
                case 'macro':
                    $do_save_version = true;
                    break;
                default:
                    $do_save_version = ($paramset_id <= 2);
                    break;
            }


            if ($do_save_version) {
                $postvals['active'][$value_id] = true;
                $this->Save_Variant_ParameterValue($postvals, $value_id, $paramset_id, $param_set, $windchill_id, $overlayed_penta_id);
            }
        }
    }

    // ===============================================================================================
    function WriteHtmlRow_ParameterSet_Odx_01($psid, $set) {
        if (!toBool($set['use_in_odx01']))
            return;

        $param_id = $set['param_id'];
        $ecu_id = $set['ecu'];
        $ecu = $this->allEcuNames[$ecu_id];
        $odx_name = $set['odx_name'];
        $readonly = false;
        $uds_id = '';
        $value = "";
        $valueSet = null;
        $paramSet = &$set;
        $odx_02_set = false;
        $editMode = ($this->m_EditMode == self::EDIT_VariantData);
        $error = 0;
        $bgError = "";
        $diff = "";

        if ($this->DB_VariantEcuData[$param_id])
            $valueSet = &$this->DB_VariantEcuData[$param_id];

//             $valueSet = []; //$this->variantDataErrors[ERROR_EMPTY_VALUE][$psid] = $odx_name;

        $has_new_struct = is_array($set['tags']) && (count($set['tags']) > 0);

        if ($has_new_struct) {
            $deleted = safe_tag_value($set['tags'], 'deleted', "");
            $odx02name = safe_tag_value($set['tags'], 'id', "");
            $uds_id = safe_tag_value($set['tags'], 'udsId', "");
        }

        if (($deleted == 'all') || (strpos($deleted, 'odx.sts.01') !== false))
            return;

        if (isset ($set['tags']['id'])) {
            $odx_02_set = isset ($set['tags']['udsId']) && isset ($set['tags']['action']) && isset ($set['tags']['type']);
            $odx02name = safe_tag_value($set['tags'], 'id', "");

            if ($odx_name != $odx02name) {
                $box = '<div class="yellowNote">JA (&ne;)</div>';
                $eq = 'ungleicher';
                $bez = "<br>Bezeichnung unter odx.sts.02 ist: $odx02name";
            } else {
                $box = '<div class="greenNote">JA (=)</div>';
                $eq = 'gleicher';
                $bez = "";
            }
            $hinweis = sprintf('<div class="ttip">%s<span class="ttiptext">Dieser Parameter wird auch in odx.sts.02 mit %s Bezeichnung verwendet. %s</span></div>', $box, $eq, $bez);
        } else {
            $odx02name = "";
            $hinweis = '<div class="ttip"><div class="greyNote">NEIN</div><span class="ttiptext">Dieser Parameter wird nicht in odx.sts.02 verwendet!</span></div>';
        }


        switch ($psid) {
            case 1:
                if (empty($odx_name)) $param_name = 'HW Version';
                $readonly = true;
                break;
            case 2:
                if (empty($odx_name)) $param_name = 'SW Version';
                $readonly = true;
                break;
        }

        $mode = ($editMode) ? self::output_odx02 : self::output_gui;
        $comment = empty ($set['comment']) ? "Beschreibung fehlt!" : $set['comment'];
        $value = $this->GetValue_odx01($valueSet, $paramSet, $type, $mode);

        $revValue = safe_tag_value($set['tags'], 'value', null);

        if ($valueSet['parameter_overwrite_id'])
            $revValueType = 'part';
        else
            $revValueType = safe_tag_value($set['tags'], 'valuetype', null);

        switch ($revValueType) {
            case 'const':
                $td_used = 'KONST.';
                $readonly = true;
                break;

            case 'macro':
                $readonly = true;
                $macroname = $this->DB_Variables[$revValue]['odx_name'];
                $revValue = $this->GetVariableValue($revValue);
                $td_used = "<div class=\"ttip\">GLOBAL<span class=\"ttiptext\">{$macroname}</span></div>";
                break;

            case 'part':
                $td_used = 'PART';
                $readonly = true;
                break;
        }


        switch ($psid) {
            case 1:
            case 2:
                $action = (($this->S_variantEcu == 6) ? 'r' : 'rc');
                break;
            case 4:
                $action = 'r';
                $readonly = true;
                break;

            default:
                $action = 'rwc';
                break;
        }


        if ($readonly) {
            switch ($type) {
                case 'bool':
                    $value = toBool(floatval($revValue)) ? '1 = An' : '0 = Aus';
                    $value = $this->LimitedSpace($value, $type, 0, 24, ' style="Color:#888;"');
                    break;
                case 'int':
                    $value = $this->LimitedSpace(intval($revValue), $type, 0, 24, ' style="Color:#888;"');
                    break;
                case 'double':
                    $value = $this->LimitedSpace(floatval($revValue), $type, 0, 24, ' style="Color:#888;"');
                    break;
                case 'string':
                    $value = $this->LimitedSpace($revValue, $type, 0, 24, ' style="Color:#888;"');
                    break;
            }
        } else {
            if (!isset($value) && !toBool($valueSet['tag_disabled']))
                $this->SetDataError($paramSet);
        }

        switch ($psid) {
            case 1:
                $td_order = "";
                $td_used = "HW";
                $value = safe_tag_value($set['tags'], 'value', $value);
                $td_unit = "";
                break;
            case 2:
                $td_order = "";
                $td_used = "SW";
                $value = safe_tag_value($set['tags'], 'value', $value);
                $td_unit = "";
                break;

            default:
                $td_order = "<div class=\"ttip\"><div class=\"colinfo\">{$set['order']}</div><span class=\"ttiptext\">{$psid}:{$param_id}</span></div>";

                if ($editMode) {
                    if (!$readonly)
                        $value = $this->MakeInput($value, $param_id, $type);
                    $checked = toBool($valueSet['tag_disabled']) ? "" : " checked";
                    $td_used = sprintf('<input type="checkbox" name="active[%d]"%s>', $param_id, $checked);
                } else {
                    if (!$readonly) {
                        $value = $this->LimitedSpace($value, $type);
                        $td_used = toBool($valueSet['tag_disabled']) ? "" : "JA";
                    }
                }

                if ($psid == 4)
                    $value = "";

                $unit_id = isset ($set['unit_id']) ? $set['unit_id'] : 0;
                $td_unit = $this->DB_allUnits[$unit_id];
        }

        $diff = $this->GetMasterValueIcon($param_id, $paramSet, $value, false);
        if ($error)
            $bgError = ' class="errorfield"';

        echo <<<HEREDOC
    <tr>
      <td>{$td_order}</td>
      <td>{$td_used}</td>
      <td>{$ecu}</td>
      <td><div class="ttip">{$odx_name}<span class="ttiptext">{$comment}</span></div></td>
      <td>{$hinweis}</td>
      <td>{$action}</td>
      <td>{$type}</td>
      <td{$bgError}>{$value}{$diff}</td>
      <td>{$td_unit}</td>
    </tr>
HEREDOC;

    }
    /*
  // ===============================================================================================
  function WriteHtml_Variant_EcuTable_Odx_01()
  {
    echo <<<HEREDOC
    <div class="tableFrame">
      <table class="variantEcuValues valueHighlighted versionHighlighted" id="variantEcuTable01">
        <thead>
          <tr>
            <td><div class="ttip ttip_headln">Ord.       <span class="ttiptext">Reihenfolge in der odx-Datei. Für odx.sts.01 notwendige Eigenschaft.</span></div></td>
            <td><div class="ttip ttip_headln">Aktiv      <span class="ttiptext">Nur als 'Aktiv' markierte Parameter erscheinen in der odx-Datei.</span></div></td>
            <td><div class="ttip ttip_headln">Gerät      <span class="ttiptext">ECU-Gerät</span></div></td>
            <td><div class="ttip ttip_headln">Bezeichnung<span class="ttiptext">Kennung/Bezeichner des Parameters</span></div></td>
            <td><div class="ttip ttip_headln">odx.sts.02 <span class="ttiptext MMLeft200">Zeigt an, ob dieser Parameter auch in der Version 02 verwendet wird und<br> falls ja unter gleicher oder anderer Kennung!</span></div></td>
            <td><div class="ttip ttip_headln">Aktion     <span class="ttiptext MMLeft100">Verwendung des Parameters (r=read, w=write, c=check)</span></div></td>
            <td><div class="ttip ttip_headln">Typ        <span class="ttiptext MMLeft100">Datentyp (int, double, string oder bool)</span></div></td>
            <td><div class="ttip ttip_headln">Wert       <span class="ttiptext MMLeft100">Dem Fahrzeugtype zugegewiesener Parameterwert</span></div></td>
            <td><div class="ttip ttip_headln">Einheit    <span class="rtiptext">Einheit zu 'Wert'</span></div></td>
          </tr>
        </thead>
        <tbody>
HEREDOC;


    $version = &$this->DB_Parameters[1];
    $this->WriteHtmlRow_ParameterSet_Odx_01(1, $version);

    $version = &$this->DB_Parameters[2];
    $this->WriteHtmlRow_ParameterSet_Odx_01(2, $version);

    echo "          <tr><td colspan=\"9\"></tr>\n";

    foreach ($this->DB_Parameters as $psid => $set) {
      if ($psid > 2)
        $this->WriteHtmlRow_ParameterSet_Odx_01($psid, $set);
    }


    echo <<<HEREDOC

        </tbody>
      </table>
    </div>
HEREDOC;

  }*/
    /*
  // ===============================================================================================
  function Update_Variant_ParameterSet_Odx_02($psid, $set, $variant_id, $config_id)
  {
    $combi_id = "$variant_id:$config_id";
    $param_id = $set['param_id'];
    $ecu_id = $set['ecu'];
    $set_by = $_SESSION['sts_userid'];

    $ecu = $this->allEcuNames[$ecu_id];
    $value = null;
    $tag_disabled = 'f';
    $no_record = false;
    $no_value = false;
    $sw_preset_type = null;
    $paramSet = &$set['tags'];
    $valueSet = ['value' => null];

    if (isset ($this->DB_VariantEcuData[$param_id])) {
      $valueSet = $this->DB_VariantEcuData[$param_id];
      $tag_disabled = $valueSet['tag_disabled'];
      $set_by = $valueSet['set_by'];
    }

    $use_odx_01 = toBool($set['use_in_odx01']);
    $use_odx_02 = true;
    $odx_01_type = 5;
    $value = null;

    if ($use_odx_01) {
      $odx_01_type = $set['type_id'];
      $odx_01_typestr = '';
      $odx_01_value = $this->GetValue_odx01($valueSet, $set, $odx_01_typestr);
      $odx_01_name = $set['odx_name'];
      $value = $odx_01_value;
    }

    $fixvalue = null;
    $default = null;

    $id = safe_tag_value($paramSet, 'id', null);
    $param_name = isset($id) ? $id : $odx_01_name;
    $uds_id = safe_tag_value($paramSet, 'udsId', 0);
    $type = safe_tag_value($paramSet, 'type', "");
    $byteCount = safe_tag_value($paramSet, 'byteCount', 1);
    $factor = safe_tag_value($paramSet, 'factor', 1);
    $offset = safe_tag_value($paramSet, 'offset', 0);
    $action = safe_tag_value($paramSet, 'action', '(?)');

    $startBit = safe_tag_value($paramSet, 'startBit', 0);
    $stopBit = safe_tag_value($paramSet, 'stopBit', 0);
    $rev_value = safe_tag_value($paramSet, 'value', "");
    $rev_valuetype = safe_tag_value($paramSet, 'valuetype', "");
    $deleted = safe_tag_value($paramSet, 'deleted', "");
    if (($deleted == 'all') || (strpos($deleted, 'odx.sts.02') !== false))
      $use_odx_02 = false;

    if (($deleted == 'all') || (strpos($deleted, 'odx.sts.01') !== false))  //  || (!isset($id))
      $use_odx_01 = false;

    $no_record = (($action === "") || !($use_odx_02 || $use_odx_01));
    $no_value = ($action === 'r');


    if ($psid <= 2) {
      $fixvalue = safe_tag_value($paramSet, 'version', "");
      $sw_preset_type = 'V';
    } else {
      switch ($rev_valuetype) {
        case 'const':
          $fixvalue = $rev_value;
          $sw_preset_type = 'C';
          break;

        case 'deflt':
          $default = ($type == 'ascii') ? $rev_value : str_replace(',', '.', $rev_value);
          $sw_preset_type = $valueSet['sw_preset_type'];
          break;

        case 'macro':
          $sw_preset_type = 'M';
          $macrovalue = $this->GetVariableValue($rev_value);
          if ($macrovalue === false)
            $fixvalue = 0;
          else
            if ($macrovalue === true)
              $fixvalue = 1;
            else
              $fixvalue = $macrovalue;
          break;

        case 'dynmc':
          $no_value = true;
          $sw_preset_type = null;
          break;

        case 'version':
          $sw_preset_type = 'V';
          break;

        default:
          $sw_preset_type = null;
          break;
      }
    }

    if (!$no_value) {
      if (isset ($fixvalue))
        $any_value = $fixvalue;
      else
        if (isset ($odx_01_value))
          $any_value = $odx_01_value;
        else
          if (isset ($valueSet['value']))
            $any_value = $valueSet['value'];
          else {
            $sw_preset_type = 'D';
            $any_value = $default;
          }

      if (strtolower(substr($any_value, 0, 2)) == '0x') {
        $hexvalue = $any_value;
        $resConvert = $this->GetValue_odx02($any_value, $paramSet);
        $error = $resConvert['error'];
        $type = $resConvert['type'];
        $value = $resConvert['value'];
      } else

        if ($any_value === '') {
          if (($type != 'ascii') && ($type != '(ascii'))
            $error = ERROR_NO_HEXADZIMAL_STRING;
        } else {
          $value = $any_value;
          if (!$this->Convert2Hex($any_value, $paramSet)) {
            $error = ERROR_CANNOT_CONVERT_TO_HEX;
          } else {
            $hexvalue = $any_value;
          }
        }

      if (($rev_valuetype == 'deflt') && ($sw_preset_type == 'D')) {
        $default_hex = $default;
        $this->Convert2Hex($default_hex, $paramSet);

        if ($default_hex != $hexvalue)
          $sw_preset_type = null;
      }
    }

    if ($error)
      return $error;


    $checkSet = [];
    $checkSet['vehicle_variant_id'] = $variant_id;
    $checkSet['ecu_parameter_id'] = $param_id;
    $checkSet['value_int'] = null;
    $checkSet['value_double'] = null;
    $checkSet['value_string'] = null;
    $checkSet['value_bool'] = null;
    $checkSet['tag_disabled'] = $tag_disabled;
    $checkSet['value'] = null;
    $checkSet['overlayed_penta_id'] = $config_id;
    $checkSet['set_by'] = $set_by;
    $checkSet['sw_preset_type'] = $sw_preset_type;

    switch ($odx_01_type) {
      case 'int':
      case self::odx01_int:
        $checkSet['value_int'] = $value;
        break;
      case 'double':
      case self::odx01_float:
        $checkSet['value_double'] = $value;
        break;
      case 'string':
      case self::odx01_string:
        $checkSet['value_string'] = $value;
        break;
      case 'bool':
      case self::odx01_bool:
        $checkSet['value_bool'] = toBool($value) ? 't' : 'f';
        break;
    }

    $checkSet['value'] = $hexvalue;

    if ($checkSet != $valueSet) {
      if (isset ($this->DB_VariantEcuData[$param_id])) {
        $this->ClearWBackup_ParameterValues('ensure', [$combi_id], [$param_id], true);
      }

      if (!$no_record) {
        $qry = $this->variantDataPtr->newQuery()->insert($checkSet);
        $this->DB_VariantEcuData[$param_id] = $checkSet;
      } else {
        unset ($this->DB_VariantEcuData[$param_id]);
      }
    }
    return 0;
  }
*/
    // ===============================================================================================
    function Update_ParameterSet_Odx_02($psid, $set) {
        if ($this->DB_RevisionMap)
            return $this->Update_Variant_ParameterSet_Odx_02($psid, $set, $this->DB_RevisionMap['variant_id'], $this->DB_RevisionMap['penta_id']);
    }

    // ===============================================================================================
    function WriteHtmlRow_ParameterSet_Odx_02($psid, $set) {
        $param_id = $set['param_id'];
        $ecu_id = $set['ecu'];
        $ecu = $this->allEcuNames[$ecu_id];
        $readonly = self::PARAM_RW;
        $action = '?';
        $uds_id = '-';
        $value = "";
        $html_value = "";
        $has_default = false;
        $editMode = ($this->m_EditMode == self::EDIT_VariantData);
        $byteCount = "";
        $editClass = '';
        $dataSource = '';
        $paramSet = &$set['tags'];
        $valueSet = null;
        $td_unit = "";
        $errorFont = ['uds_id' => '', 'type' => '', 'size' => '', 'value' => '', 'hex' => '', 'byteCount' => ''];
        $tdCheckbox = "";

        $error = $this->Update_ParameterSet_Odx_02($psid, $set);

        if (isset ($this->DB_VariantEcuData[$param_id]))
            $valueSet = $this->DB_VariantEcuData[$param_id];

        $hexValue = $valueSet['value'];

        if (toBool($set['use_in_odx01'])) {
            $odx_01_type = $set['type_id'];
            $odx_01_value = $this->GetValue_odx01($valueSet, $set, $odx_01_type);
            $odx_01_name = $set['odx_name'];

            switch ($odx_01_type) {
                case 'int':
                    $odx_01_alias = 'signed';
                    $odx_01_size = 2;
                    break;
                case 'double':
                    $odx_01_alias = 'signed';
                    $odx_01_size = 4;
                    break;
                case 'string':
                    $odx_01_alias = 'ascii';
                    $odx_01_size = 0;
                    break;
                case 'bool':
                    $odx_01_alias = 'unsigned';
                    $odx_01_size = 1;
                    break;
                default:
                    $odx_01_alias = 'blob';
                    $odx_01_size = 0;
                    break;
            }
            $odx1set = [];
            $odx1set['type'] = ['tag_value' => $odx_01_alias];
            $odx1set['bigEndian'] = $paramSet['bigEndian'];
            if ($odx_01_size)
                $odx1set['byteCount'] = ['tag_value' => $odx_01_size];
        } else {
            $odx_01_type = 0;
            $odx_01_value = '';
            $odx_01_name = '';
            $odx_01_alias = '';
            $odx_01_size = '';

        }

        $param_name = safe_tag_value($paramSet, 'id', "$odx_01_name");
        $uds_id = safe_tag_value($paramSet, 'udsId', "(?)");
        $type = safe_tag_value($paramSet, 'type', "");
        $byteCount = safe_tag_value($paramSet, 'byteCount', "");
        $factor = safe_tag_value($paramSet, 'factor', 1);
        $offset = safe_tag_value($paramSet, 'offset', 0);
        $action = safe_tag_value($paramSet, 'action', '(?)');

        $startBit = safe_tag_value($paramSet, 'startBit', 0);
        $stopBit = safe_tag_value($paramSet, 'stopBit', 0);
        $rev_value = safe_tag_value($paramSet, 'value', "");
        $rev_value_usage = safe_tag_value($paramSet, 'valuetype', "");
        $deleted = safe_tag_value($paramSet, 'deleted', "");
        if (($deleted == 'all') || (strpos($deleted, 'odx.sts.02') !== false) || ($action == "")) //(empty($action))); // && ($this->S_OdxVerEcu!=1)))
            return 0;

        $correction = "";
        if ($factor && ($factor != 1)) {
            $correction .= "*$factor";
        }
        if ($offset && ($offset != 0)) {
            $correction .= "+$offset";
        }


        if (toBool($set['use_in_odx01'])) {
            if ($odx_01_name != $param_name) {
                $box = '<div class="yellowNote">JA (&ne;)</div>';
                $eq = 'ungleich';
                $bez = "<br>Bezeichnung unter odx.sts.01 ist: $odx_01_name";
            } else {
                $box = '<div class="greenNote">JA (=)</div>';
                $eq = 'gleich';
                $bez = "";
            }
            $hinweis = sprintf('<div class="ttip">%s<span class="ttiptext">Dieser Parameter wird auch in odx.sts.01 mit %s Bezeichnung verwendet. %s</span></div>', $box, $eq, $bez);
        } else {
            $odx_01_name = "";
            $hinweis = '<div class="ttip"><div class="greyNote">NEIN</div><span class="ttiptext">Dieser Parameter wird nicht in odx.sts.01 verwendet!</span></div>';
        }


        $comment = empty ($set['comment']) ? "Beschreibung fehlt!" : $set['comment'];


        if ($psid <= 2) {
            $td_order = "";
            $rev_value_usage = 'version';
            $hexValue = safe_tag_value($paramSet, 'version', "");
            $valueSet['value'] = $hexValue;
        } else {
            switch ($action) {
                case 'r':
                    $readonly = self::PARAM_ONLY_READ;
                    $value = 'wird nur gelesen';
                    $rev_value = 'keine Vorgabe';
                    break;

                case '':
                    $readonly = self::PARAM_NOT_USED;
                    $value =
                    $rev_value = 'wird nicht benutzt';
                    break;
            }

            $order = safe_tag_value($paramSet, 'order', $set['order']);
            $td_order = "<div class=\"ttip\"><div class=\"colinfo\">{$order}</div><span class=\"ttiptext\">{$psid}:{$param_id}</span></div>";

            if (isset ($valueSet['parameter_overwrite_id'])) {
                $readonly = self::PARAM_SET_BY_PART;
                $value = $this->GetValue_odx01($valueSet, $set, $odx_01_type);
            }

        }

        if (!$readonly) {
            switch ($rev_value_usage) {
                case 'const':
                    $valueSet['value'] = $rev_value;
                    $readonly = self::PARAM_CONST;
                    break;

                case 'deflt':
                    $default = $rev_value;
                    $has_default = true;

                    if (isset ($valueSet['sw_preset_type']) && ($valueSet['sw_preset_type'] == 'D')) {
                        $readonly = self::PARAM_IS_DEFAULT;
                    } else
                        if ($editMode) {

                            $dataSource = <<<HEREDOC
<input type="hidden" name="isDefault[$param_id]" id="id_default-$param_id" value="0">
<a id="id_undefault-$param_id" href="javascript:undefault($param_id)" style="display:none;">
  <span class="ttip">DEFAULT<span class="ttiptext">Klick um Wert zu verselbstständigen</span></span>
</a>

<a id="id_reset-$param_id" href="javascript:resetValue($param_id)">
  <span class="ttip">RESET<span class="ttiptext">Auf Standardwert zurücksetzen (default=$rev_value)</span></span>
<a>
HEREDOC;
                        } else {
                            $dataSource = '<span class="ttip"><span class="not_default">&ne; DEFAULT</span><span class="ttiptext">default=' . $rev_value . '</span></span>';
                        }
                    break;

                case 'macro':
                    $macroname = $this->DB_Variables[$rev_value]['odx_name'];
                    $macrovalue = $this->GetVariableValue($rev_value);
                    if ($macrovalue === false)
                        $valueSet['value'] = 0;
                    else
                        if ($macrovalue === true)
                            $valueSet['value'] = 1;
                        else
                            $valueSet['value'] = $macrovalue;

                    $readonly = self::PARAM_SET_BY_MACRO;
                    break;

                case 'dynmc':
                    $readonly = self::PARAM_SET_DYNAMIC;
                    break;


                default:
                case 'version':
                    break;

            }


            if (isset ($valueSet['value'])) {
                $hexValue = $valueSet['value'];
            } else
                if (isset ($default)) {
                    $hexValue = $default;
                }

            if (!isset($hexValue))
                $hexValue = isset ($odx_01_value) ? $odx_01_value : $default;

            if (strtolower(substr($hexValue, 0, 2)) == '0x') {
                $resConvert = $this->GetValue_odx02($hexValue, $paramSet);
                $error = $resConvert['error'];
                $type = $resConvert['type'];
                $value = $resConvert['value'];
            } else {
                $value = $hexValue;
                if ($value === '') {
                    $type = safe_tag_value($paramSet, 'type', '');
                    if (empty($type) && $odx_01_alias)
                        $type = "($odx_01_alias)";

                    if (($type != 'ascii') && ($type != '(ascii'))
                        $error = ERROR_NO_HEXADZIMAL_STRING;
                } else {
                    if (!$this->Convert2Hex($hexValue, $paramSet)) {
                        if ($this->Convert2Hex($hexValue, $odx1set)) {
                            $type = "($odx_01_type)";
                            $byteCount = "($odx_01_size)";
                        } else {
                            $error = ERROR_CANNOT_CONVERT_TO_HEX;
                        }
                    }
                }
            }


            switch ($error) {

                case ERROR_UNKNOWN_TYPE:
                    $type = "{$type}";
                    $errorFont['type'] = ' class="errorfont"';
                    $readonly = self::PARAM_TAGS_MISSING;
                    break;

                case ERROR_MISSING_TAG_TYPE:
                    $type = '{fehlt}';
                    $errorFont['type'] = ' class="errorfont"';
                    $readonly = self::PARAM_TAGS_MISSING;
                    break;

                case ERROR_MISSING_TAG_SIZE:
                    $byteCount = '{fehlt}';
                    $errorFont['type'] = ' class="errorfont"';
                    $readonly = self::PARAM_TAGS_MISSING;
                    break;

                case ERROR_NO_HEXADZIMAL_STRING:
                    $hexValue = "{kein hexadezimal}";
                    $errorFont['hex'] = ' class="errorfont"';
                    break;

                case ERROR_CANNOT_CONVERT_TO_HEX:
                    $hexValue = "{ungültig}";
                    $errorFont['hex'] = ' class="errorfont"';
                    break;

                case 0:
                    if (strlen($hexValue) > 18) {
                        //$hexValue = '<input type="text" value="'.$hexValue.'" class="limitedText" readonly size="16">';
                        $hex = substr($hexValue, 0, 18) . '...';
                        $hexValue = "<div class=\"ttip\">$hex<span class=\"rtiptext\">$hexValue</span></div>";
                    }
                    break;

                default:
                    break;
            }
        }

        if ($psid <= 2)
            $readonly = self::PARAM_CONST;

        switch ($readonly) {
            case self::PARAM_SET_BY_MACRO:
            case self::PARAM_SET_BY_PART:
            case self::PARAM_CONST:
                $html_value = $this->LimitedSpace($value, $type, 0, 22, ' style="Color:#888;"');
                break;

            case self::PARAM_IS_DEFAULT:
                $html_value = $this->LimitedSpace($value, $type, 0, 22, ' style="Color:#888;"');

                if ($editMode) {
                    $input = $this->MakeInput($value, $param_id, $type, $byteCount);
                    $html_value = <<<HEREDOC
<div id="id_is_default-$param_id">$html_value</div>
<div id="id_is_not_default-$param_id" style="display:none">$input</div>
HEREDOC;
                }
                break;

            case self::PARAM_RW:
                if ($editMode) {
                    // $cssHidden =  ($readonly==self::PARAM_IS_DEFAULT) ? ' style="display:none;"'  : '';

                    $html_value = $this->MakeInput($value, $param_id, $type, $byteCount);

                    if ($has_default) {
                        $html_default = $this->LimitedSpace($default, $type, 0, 22, ' style="Color:#888;"');
                        $html_value = <<<HEREDOC
<div id="id_is_default-$param_id" style="display:none">$html_default</div>
<div id="id_is_not_default-$param_id" >$html_value</div>
HEREDOC;
                    }

                } else {
                    $html_value = $this->LimitedSpace($value, $type);
                }
                break;

            case self::PARAM_SET_DYNAMIC:
                $hexValue = "<span class=\"inactiveLink\">{extern}</span>";
                break;

            case self::PARAM_NOT_USED:
            case self::PARAM_ONLY_READ:
//                if ($editMode)
                //$value .= $this->MakeInput ($value, $param_id, 'hidden');
//                else
                $html_value = "<span class=\"inactiveLink\">{$value}</span>";
                $hexValue = $html_value;
                break;

            case self::PARAM_TAGS_MISSING:
            case self::PARAM_TYPE_ERROR:
                if ($editMode) {
                    $editClass = $this->errorStyle;
                    $html_value = '{Eingabe nicht möglich}';
                }
                break;
        }


        $tr_disabled = "";
        switch ($psid) {
            case 1:
                $td_used = "HW";
                break;
            case 2:
                $td_used = "SW";
                break;
            default:
                $unit_id_01 = empty ($set['unit_id']) ? 0 : $set['unit_id'];
                $unit_01 = $this->DB_allUnits[$unit_id_01];
                $unit = safe_tag_value($paramSet, 'unit', $unit_01);
                $td_unit = $unit ? $unit : "";

                if ($editMode) {
                    $checked = toBool($valueSet['tag_disabled']) ? "" : " checked";
                    $td_used = sprintf('<input type="checkbox" name="active[%d]"%s>', $param_id, $checked);
                } else {
                    $td_used = toBool($valueSet['tag_disabled']) ? "NEIN" : "JA";
                    $tr_disabled = toBool($valueSet['tag_disabled']) ? ' class="disabled"' : '';
                }
        }

        switch ($readonly) {
            case self::PARAM_IS_DEFAULT:
                $dataSource = '<span class="is_default">= DEFAULT</span>';
                if ($editMode) {
                    $dataSource = <<<HEREDOC
<input type="hidden" name="isDefault[$param_id]" id="id_default-$param_id" value="1">
<a id="id_undefault-$param_id" href="javascript:undefault($param_id)">
    <span class="ttip">DEFAULT<span class="ttiptext">Klick um Wert zu verselbständigen</span></span>
</a>

<a id="id_reset-$param_id" href="javascript:resetValue($param_id)" style="display:none;">
    <span class="ttip">RESET<span class="ttiptext">Auf Standardwert zurücksetzen (default=$default)</span></span>
</a>

HEREDOC;
                }

                break;

            case self::PARAM_SET_DYNAMIC:
                $dataSource = 'DYNAMIC';
                break;

            case self::PARAM_SET_BY_PART:
                $dataSource = "PART";
                break;

            case self::PARAM_SET_BY_MACRO:
                $editClass = ' class="readonly"';
                $dataSource = "<div class=\"ttip\">GLOBAL<span class=\"ttiptext\">{$macroname}</span></div>";
                break;
        }

        $diff = $this->GetMasterValueIcon($param_id, $paramSet, $value, true);

        /*    if ($this->m_EditMode == self::EDIT_CopyVariantEcuData) {
      if ($psid <= 4)
        $td_order = '&nbsp;';
      else
        $td_order = <<<HEREDOC
      <input type="checkbox" class="param_selector" data-param="$param_id" checked>
HEREDOC;
    }*/

        echo <<<HEREDOC
    <tr$tr_disabled>
      <td>$td_order</td>
      <td>$td_used</td>
      <td>{$dataSource}</td>
      <td><div class="ttip">{$param_name}<span class="ttiptext">{$comment}</span></div></td>
      <td>{$hinweis}</td>
      <td{$errorFont['uds_id']}>{$uds_id}</td>
      <td>{$action}</td>
      <td{$errorFont['type']}>{$type}</td>
      <td{$errorFont['byteCount']}>{$byteCount}</td>
      <td$editClass>{$html_value}{$diff}</td>
      <td>{$td_unit}</td>
      <td>{$correction}</td>
      <td{$errorFont['hex']}>{$hexValue}</td>
      <td>{$startBit}</td>
      <td>{$stopBit}</td>
    </tr>
HEREDOC;
        return $error;
    }
    /*
  // ===============================================================================================
  function WriteHtml_Variant_EcuTable_Odx_02(&$param_states)
  {
    if ($this->m_EditMode == self::EDIT_CopyVariantEcuData)
      $td1 = '<td><div class="ttip ttip_headln"><input type="checkbox" id="cb_select_all" checked>Alle<span class="ttiptext">Alle aus- abwählen</span></div></td>';
    else
      $td1 = '<td><div class="ttip ttip_headln">Ord.       <span class="ttiptext">Reihenfolge in der odx-Datei. Bestimmt nur die reihenfolge der Abarbeitung</span></div></td>';

    echo <<<HEREDOC
    <div class="tableFrame">
      <table class="variantEcuValues valueHighlighted versionHighlighted" id="variantEcuTable02">
        <thead>
          <tr>
            $td1
            <td><div class="ttip ttip_headln">Aktiv      <span class="ttiptext">Nur als 'Aktiv' markierte Parameter erscheinen in der odx-Datei.</span></div></td>
            <td><div class="ttip ttip_headln">Quelle     <span class="ttiptext">Wert wird bestimmt von</span></div></td>
            <td><div class="ttip ttip_headln">Bezeichnung<span class="ttiptext">Kennung/Bezeichner des Parameters</span></div></td>
            <td><div class="ttip ttip_headln">odx.sts.01 <span class="ttiptext">Zeigt an, ob dieser Parameter auch in der Version 01 verwendet wird,<br> und falls ja, unter gleicher oder anderer Kennung!</span></div></td>
            <td><div class="ttip ttip_headln">Uds-ID     <span class="ttiptext">UDS-ID</span></div></td>
            <td><div class="ttip ttip_headln">Aktion     <span class="ttiptext MMLeft100">Verwendung des Parameters (r=read, w=write, c=check)</span></div></td>
            <td><div class="ttip ttip_headln">Typ        <span class="ttiptext MMLeft100">Datentyp auf der Schnittstelle (ascii, signed, unsigned oder blob)</span></div></td>
            <td><div class="ttip ttip_headln">n-Bytes    <span class="ttiptext MMLeft100">Anzahl der Bytes</span></div></td>
            <td><div class="ttip ttip_headln">Wert       <span class="ttiptext MMLeft100">Dem Fahrzeugtype zugegewiesener Parameterwert (human value)</span></div></td>
            <td><div class="ttip ttip_headln">Einheit    <span class="ttiptext MMLeft200">Einheit zu 'Wert'</span></div></td>
            <td><div class="ttip ttip_headln">Umrechn.   <span class="ttiptext MMLeft200">Anzeige Umrechnungsfaktor/-offset zum Umrechnen auf Schnittstelle</span></div></td>
            <td><div class="ttip ttip_headln">Hex        <span class="ttiptext MMLeft200">Resultierender Hexadezimalwert auf der Schnittstelle</span></div></td>
            <td><div class="ttip ttip_headln">Start      <span class="rtiptext">Start Bits auf dem CAN-BUS</span></div></td>
            <td><div class="ttip ttip_headln">Stopp      <span class="rtiptext">Stopp Bits auf dem CAN-BUS</span></div></td>
          </tr>
        </thead>
        <tbody>
HEREDOC;
    $version = &$this->DB_Parameters[1];
    $param_states[] = $this->WriteHtmlRow_ParameterSet_Odx_02(1, $version);

    $version = &$this->DB_Parameters[2];
    $param_states[] = $this->WriteHtmlRow_ParameterSet_Odx_02(2, $version);

    echo '
          <tr><td colspan="16"></tr>
';

    $orderlist = $this->GetParameterOrderList();
    foreach ($orderlist as $psid) {
      $param_states[] = $this->WriteHtmlRow_ParameterSet_Odx_02($psid, $this->DB_Parameters[$psid]);
    }

    echo <<<HEREDOC

        </tbody>
      </table>
    </div>
HEREDOC;

  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_Variant_EcuTable($ecuId)
  {
    $permissions = &$this->m_Permission[self::PRIV_ECU_DATA];
    $ecuName = ucfirst($this->allEcuNames[$ecuId]);
    $vname = $this->Separated_Variant_ID($this->DB_VariantSet['v_name']);
    $pname = $this->DB_VariantSet['p_name'];
    $is_admin = $allow_edit = false;
    $odxSupport = toBool($this->allEcus[$ecuId]["supports_odx02"]) ? "JA" : "NEIN";
    $odxModes = ['gesperrt', 'Odx-File', 'Datenbank'];
    $requestId = "";
    $responseId = "";
    $copy_from = "";
    $copy_master = "";
    $displ_copy_eq = "none";
    $displ_copy_ne = "none";
    $param_states = [];


    $dirty = false;

    if ($this->DB_RevisionMap) {
      $iOdxMethod = safe_val($this->DB_RevisionMap, 'odx_download_mode', 0);
      if ($this->m_EditMode == self::EDIT_VariantData) {
        $odxMethod = '<select name="selected[odxMethod]">' .
            $this->GetHtml_selectOptions($odxModes, $iOdxMethod) .
            "</select>\n";
      } else {
        $odxMethod = "<span id=\"id_odx_download_mode\">{$odxModes[$iOdxMethod]}</span>";
      }

      $copy_master = safe_val($this->DB_RevisionMap, 'master', "");
      if ($copy_master) {
        $dirty = count($this->DB_RevisionMap['diff']) > 0;
        $displ_copy_eq = $dirty ? 'none' : 'inline';
        $displ_copy_ne = $dirty ? 'inline' : 'none';
        $ref = "map_id={$this->DB_RevisionMap['id']}";
      }

    }

    if ($this->S_variantRev) {
      $requestId = '0x' . $this->S_variantRev['request_id'];
      $responseId = '0x' . $this->S_variantRev['response_id'];

      if (toBool($this->S_variantRev['use_xcp'])) {
        if (toBool($this->S_variantRev['use_uds']))
          $this->SetWarning(WARNING_ECU_USES_XCP, [$this->S_variantEcu, $this->S_variantRev['sts_version']]);
        else
          $this->SetWarning(WARNING_ECU_USES_XCP_ONLY, [$this->S_variantEcu, $this->S_variantRev['sts_version']]);
      }
    }


    if ($ecuId) {
      $allow_edit = toBool($permissions['current']);
      $is_admin = $allow_edit;
    }

    $debug_info = "";
    if ($GLOBALS['IS_DEBUGGING']) {
      $debug_info = "
        <div>DB Windchill/Penta: {$this->DB_RevisionMap['variant_id']} / "
          . ($this->DB_RevisionMap['penta_id'] ? "" : '0</span><span class="inactiveLink"> / ')
          . "{$this->DB_VariantSet['penta_config_id']}</div>";
    }

    $form = $this->GetHtml_FormHeader("idr_", 4, "mainform");

    echo <<<HEREDOC
<div class="scrollboxY stdborder" style="height: 570px;">
$form
    <div class="infoBlock">
HEREDOC;

    $this->WriteHtml_VariantInfoBlock("Ecu Werte: $ecuName ", self::PRIV_ECU_DATA, $this->S_variantEcu);
    $helptext = "Hier klicken, um Bezug zum Ursprung zu lösen";

    echo <<<HEREDOC
      <div class="obere infoHeadline stdborder">
        <div><h3>Konfiguration: </h3> <h2>$vname</h2></div>
        <div><h3>Penta-Artikel: </h3> <h2>$pname</h2></div>
        <div><h3>Gerät/ECU: </h3> <h2>$ecuName</h2></div>
      </div>
      <div class="untere infoHeadline stdborder">
        <div>Request-ID: <b>$requestId</b></div>
        <div>Response-ID: <b>$responseId</b></div>
        <div>Supports odx.sts.02: <b>$odxSupport</b></div>$debug_info
        <div>ODX Erzeugung: $odxMethod</div>
        <div id="id_uncomplete" class="status_uncomplete">UNVOLLSTÄNDIG</div>
        <div class="status_copy_eq" style="display:$displ_copy_eq" OnClick="SubmitCommand ('consolidate')"><span class="ttip">= $copy_master<span class="ttiptext MMLeft050">$helptext</span></span></div>
        <div class="status_copy_ne" style="display:$displ_copy_ne" OnClick="SubmitCommand ('consolidate')"><span class="ttip">&ne; $copy_master<span class="ttiptext MMLeft050">$helptext</span></span></div>
      </div>
    </div>
HEREDOC;


    if ($this->S_variantRev) {
      $this->variantDataErrors = [];

      $odxChecked = ["", "", ""];
      $odxChecked[$this->m_OdxVerShow] = " checked";
      $odx_01_ask_params = "";

      if (toBool($this->DB_RevisionMap['use_uds'])) {
        switch ($this->m_OdxVerShow) {
          case 1:
            $this->WriteHtml_Variant_EcuTable_Odx_01();
            $odx_01_ask_params = "<br>Zu angenommenen Ecu-Eigenschaften bitte bei den  Diagnose-Leuten (TEO/SIA) nachfragen.";
            break;

          case 2:
            $this->WriteHtml_Variant_EcuTable_Odx_02($param_states);
            break;
        }
      } else {
        echo '<h2 style="margin: 20px; 50%">Diese ECU-Software unterstützt keine UDS-Kommunikation</h2>';
      }


      $check_ok = (count($this->variantDataErrors) == 0);
      $released = false;

      foreach ($param_states as $state) {
        if ($state) {
          $check_ok = false;
          break;
        }
      }

      if ($check_ok) {
        $released = toBool($this->DB_RevisionMap['parameters_released']);
      } else {
        foreach ($this->variantDataErrors as $errNo => $list)
          $this->SetError($errNo, array_values($list));

        if ($this->displayfooter)
          $this->displayfooter->enqueueFinallyCalls("
{
    var elem ;
    if (elem = document.getElementById ('id_uncomplete'))
        elem.style.visibility = 'visible';
    if (elem = document.getElementById ('id_copy_params_link'))
        elem.style.display = 'none';
    if (elem = document.getElementById ('id_copy_params_dummy'))
        elem.style.display = 'inline-block';
}
");
      }

      $update_cols = ['parameters_check_ok', 'parameters_released'];
      $update_vals[0] = $check_ok ? 't' : 'f';
      $update_vals[1] = $released ? 't' : 'f';

      if ($this->DB_RevisionMap['parameters_check_ok'] . $this->DB_RevisionMap['parameters_released'] != $update_vals[0] . $update_vals[1]) {
        $qry = $this->ecuPtr->newQuery('variant_ecu_revision_mapping');
        $qry = $qry->where('variant_id', '=', $this->DB_RevisionMap['variant_id']);
        $qry = $qry->where('penta_id', '=', $this->DB_RevisionMap['penta_id']);
        $qry = $qry->where('ecu_id', '=', $this->DB_RevisionMap['ecu_id']);
        $qry->update($update_cols, $update_vals);
      }


      $urlparams = $this->GetHtml_UrlParams();

      echo <<<HEREDOC
  </form>
</div>
<div class="odxVersion">
  Anzeige optimiert für:
  <input type="radio" id="id_odx01" name="odxVersion[{$this->S_variantEcu}]" value="1"{$odxChecked[1]} onClick="window.location.href='{$_SERVER['PHP_SELF']}?$urlparams&reload=1&odxVersion[{$this->S_variantEcu}]=1'">
  <label for="id_odx01"> odx.sts.01</label>
  <input type="radio" id="id_odx02" name="odxVersion[{$this->S_variantEcu}]" value="2"{$odxChecked[2]} onClick="window.location.href='{$_SERVER['PHP_SELF']}?$urlparams&reload=1&odxVersion[{$this->S_variantEcu}]=2'">
  <label for="id_odx02"> odx.sts.02</label>
</div>
<div> Die Reihenfolge der Parameter gibt die Reihenfolge der Verarbeitung in TEO und SIA an. Die in TEO/SIA angezeigten Meldungstexte sowie Fehlercodes lassen sich hier nicht einsehen.
    $odx_01_ask_params

</div>
HEREDOC;


    }
/*
//    echo <<<HEREDOC
//
//<div class="variantPartsButtons stdborder">
//  <div class="MiniButtons">
//HEREDOC;
//    if ($this->S_variantRev) {
//      $allow_edit &= ($this->m_EditMode == 0);
//
//
//      if ($this->odxAccessError == ERROR_ODX_CREATOR_LOCKED) {
//        $mutex_owner_name = "{$this->odxAccessMutex['fname']} {$this->odxAccessMutex['lname']}";
//
//        echo $this->GetHtml_ErrorBox("ODX Creator gesperrt durch den Anwender $mutex_owner_name");
//      } else
//        if ($this->odxAccessError == ERROR_ODX_CREATOR_GET_LOCK) {
//          echo $this->GetHtml_ErrorBox("Fehler beim Zugriff auf die freigabe 'ODX Creator'");
//        }
//
//      if (($this->m_OdxVerShow == 2) && self::FORCE_ALL_TAGS && ($this->configError == ERROR_INVALID_ODX2_CONFIG)) {
//        $allow_edit = false;
//        echo $this->GetHtml_ErrorBox("Parameter Konfiguration fehlerhaft oder unvollständig");
//      }
//
//
//      $this->WriteHtml_CommandButtons($allow_edit, ($this->m_EditMode == self::EDIT_VariantData));
//    }
//    echo <<<HEREDOC
//  </div>
//</div>
//HEREDOC;

  }
  */
    // ===============================================================================================
    //////////////////////////////////////////////////////////////////////////////////////////////////
    // ===============================================================================================
    function GetEcuParametersUsingValuetype($idVar, $valuetype) {
        if (empty ($this->DB_allRevisions) || !$idVar)
            return [];

        $allRevIds = array_remove_empty(array_column($this->DB_allRevisions, 'rev_id'));
        $allRevIdSQL = implode(_cma_, $allRevIds);

        $sql = <<<SQLDOC
SELECT _usage.*, _name.*, ecu_revisions.ecu_id, ecus.name,
    (SELECT odx_name FROM ecu_parameter_sets WHERE ecu_parameter_set_id = _usage.ecu_parameter_set_id),
    (SELECT ecu_parameter_id FROM ecu_parameters WHERE ecu_parameter_set_id = _usage.ecu_parameter_set_id AND ecu_id = ecu_revisions.ecu_id)
FROM
(
    SELECT ecu_revision_id, ecu_parameter_set_id FROM
    (
        SELECT distinct on (tag, ecu_parameter_set_id, ecu_revision_id)
            ecu_revision_id, ecu_parameter_set_id, tag, tag_value
        FROM ecu_tag_configuration
        WHERE tag='valuetype'
            AND tag_value='$valuetype'
            AND ecu_revision_id in ($allRevIdSQL)
        ORDER BY ecu_revision_id, ecu_parameter_set_id, tag, timestamp desc
    ) AS _tag

    JOIN
    (
        SELECT distinct on (tag, ecu_parameter_set_id, ecu_revision_id)
            ecu_revision_id, ecu_parameter_set_id, tag, tag_value
        FROM ecu_tag_configuration
        WHERE tag='value'
            AND tag_value='$idVar'
            AND ecu_revision_id in ($allRevIdSQL)
        ORDER BY ecu_revision_id, ecu_parameter_set_id, tag, timestamp desc
    ) AS _macro USING (ecu_revision_id, ecu_parameter_set_id)
) AS _usage

JOIN
(
    SELECT distinct on (tag, ecu_parameter_set_id, ecu_revision_id)
        tag_value AS id, ecu_parameter_set_id
    FROM ecu_tag_configuration
    WHERE tag='id'
    AND ecu_revision_id in ($allRevIdSQL)
    ORDER BY ecu_revision_id, ecu_parameter_set_id, tag, timestamp desc
) AS _name USING (ecu_parameter_set_id)
JOIN ecu_revisions USING(ecu_revision_id)
JOIN ecus using (ecu_id)
ORDER BY ecu_id
SQLDOC;

        $qry = $this->ecuPtr->newQuery();
        if ($qry->query($sql))
            return $qry->fetchAssoc('ecu_parameter_id');
        return [];
    }

    // ===============================================================================================
    function GetVariableValue($variable) {
        $qry = $this->variantDataPtr->newQuery()
            ->join('ecu_parameters', 'using (ecu_parameter_id)')
            ->where('vehicle_variant_id', '=', $this->DB_VariantSet ['v_id'])
            ->where('overlayed_penta_id', '=', $this->DB_VariantSet ['penta_config_id'])
            ->where('ecu_parameters.ecu_parameter_set_id', '=', $variable);

        $data = $qry->getOne('*');
        $params = $this->DB_Variables[$variable];
        $type = null;
        $value = $this->GetValue_odx01($data, $params, $type, self::output_php); //$dest=self::output_odx02)

        return $value;

    }

    // ===============================================================================================
    function CreateNewVariable($varname) {
        if (empty ($varname))
            return $this->SetError(ERROR_EMPTY_VALUE, "Name");

        $qry = $this->ecuPtr->newQuery('ecu_parameter_sets')
            ->where('odx_name', '=', $varname)
            ->where('parent_id', '=', $this->m_variablesRootId);
        if ($qry->getNumRows())
            return $this->SetError(ERROR_VARIABLE_ALLREADY_EXISTS, $varname);

        $insert = ['odx_name' => $varname,
            'type_id' => $_POST['type_id'],
            'unit_id' => $_POST['unit_id'],
            'parent_id' => $this->m_variablesRootId,
            'odx_tag_name' => 'variable'
        ];

        $qry = $this->ecuPtr->newQuery('ecu_parameter_sets')->insert($insert);

        $qry = $this->ecuPtr->newQuery('ecu_parameter_sets')
            ->where('odx_name', '=', $varname)
            ->where('parent_id', '=', $this->m_variablesRootId);

        $newVariable = $qry->getVal('ecu_parameter_set_id');
        if (!$newVariable)
            return $this->SetError(ERROR_CANNOT_SAVE_PARAMDEF);

        $this->S_currentVariable = [$newVariable];

        $insert = ['ecu_parameter_set_id' => $newVariable,
            'ecu_id' => -1,
            'order' => -1
        ];
        $qry = $this->ecuPtr->newQuery('ecu_parameters')->insert($insert);

        unset ($insert['order']);
        $insert['user_id'] = $_SESSION['sts_userid'];
        $insert['context'] = self::PRIV_SET_VARIABLE;
        $insert['is_owner'] = 't';
        $insert['allow_write'] = 't';
        $qry = $this->ecuPtr->newQuery('sts_privilegs')->insert($insert);

        $this->DB_Variables = $this->QueryVariables();
    }

    // ===============================================================================================
    function SaveVariable($variant_id, $config_id, $macro_id, $value) {
        $set_param = &$this->DB_Variables[$macro_id];
        $id_type = $set_param['type_id'];

        if (($id_type < 1) || ($id_type > 4))
            $id_type = self::odx01_string;

        switch ($id_type) {
            case self::odx01_bool:
                $db_value = toBool($value) ? 't' : 'f';
                break;

            default:
                $db_value = $value;
                break;
        }

        $param_id = $this->variantDataPtr->newQuery('ecu_parameters')
            ->where('ecu_parameter_set_id', '=', $macro_id)
            ->getVal('ecu_parameter_id');

        $qry = $this->variantDataPtr->newQuery()
            ->where('vehicle_variant_id', '=', $variant_id)
            ->where('overlayed_penta_id', '=', $config_id)// for macros pentaNr is must be given!
            ->where('ecu_parameter_id', '=', $param_id);

        $exists = $qry->get('*');
        if ($exists) {
            $update_cols = ['value_int', 'value_double', 'value_string', 'value_bool'];
            $update_vals = [NULL, NULL, NULL, NULL];

            $update_vals[$id_type - 1] = $db_value;

            $db = $this->variantDataPtr->newQuery();
            $db = $db->where('vehicle_variant_id', '=', $variant_id)
                ->where('overlayed_penta_id', '=', $config_id)
                ->where('ecu_parameter_id', '=', $param_id);
            $res = $db->update($update_cols, $update_vals);

            if (!$res)
                return $this->SetError(STS_ERROR_DB_UPDATE, $db->GetLastError());
        } else {
            $colname = $this->DB_allTypes[$id_type];
            $insert = ['vehicle_variant_id' => $variant_id,
                'overlayed_penta_id' => $config_id,
                'ecu_parameter_id' => $param_id,
                $colname => $db_value];

            $res = $this->variantDataPtr->newQuery()->insert($insert);
            if (!$res)
                return $this->SetError(STS_ERROR_DB_INSERT, $db->GetLastError());
        }
        $this->PushVariableValue($macro_id, $value, $id_type);
    }
    /*
  // ===============================================================================================
  function ExecuteVariable()
  {
    if ($this->DB_VariantSet) {
      if ($this->m_EditMode == self::EDIT_NewGlobalVariable)
        $this->CreateNewVariable(strtoupper(trim($_POST['makroname'])));

      if (count($this->S_currentVariable) == 1) {
        $macro_id = $this->S_currentVariable[0];
        $set_param = &$this->DB_Variables[$macro_id];
        $id_type = $set_param['type_id'];
        $id_variant = safe_val($this->DB_VariantSet, 'v_id', 0);
        $id_config = safe_val($this->DB_VariantSet, 'penta_config_id', 0);

        $value = $_POST['value'][$id_type];

        $this->SaveVariable($id_variant, $id_config, $macro_id, $value);
      }
    }
    $this->m_EditMode = 0;
  }
*/
    /*
  // ===============================================================================================
  function CopyGlobalVariable($srcCombiId, $dstListe)
  {
    foreach ($this->S_currentVariable as $macro_id) {
      $param_set = &$this->DB_Variables[$macro_id];
      $param_id = $param_set['ecu_parameter_id'];
      $dataSet = &$this->DB_VariantEcuData[$param_id];
      $value = $this->GetValue_odx01($dataSet, $param_set);

      $this->ClearWBackup_ParameterValues('cp_mac', $dstListe, [$param_id]);
      foreach ($dstListe as $dst_combi) {
        sscanf($dst_combi, "%d:%d", $dst_variant_id, $dst_config_id);
        $this->SaveVariable($dst_variant_id, $dst_config_id, $macro_id, $value);
      }
    }
  }
*/
    // ===============================================================================================
    function PushVariableValue($variable, $value, $id_type) {
        $usage = $this->GetEcuParametersUsingValuetype($variable, 'macro');

        foreach ($usage as $set) {
            $ecu_id = $set['ecu_id'];
            $rev_id = $set['ecu_revision_id'];
            $param_id = $set['ecu_parameter_set_id'];
            $value_id = $set['ecu_parameter_id'];
            $windchill_id = $this->DB_allRevisions[$ecu_id]['variant_id'];
            $overlayed_penta_id = $this->DB_allRevisions[$ecu_id]['penta_id'];
            $postvals = ['value' => [$value_id => $value]];
            $params_all = $this->QueryParameters($ecu_id, $rev_id, $param_id);

            //           $this->DB_VariantEcuData = $this->QueryVariantEcuValues ($windchill_id, $overlayed_penta_id);
            $this->ClearWBackup_ParameterValues('macro', ["$windchill_id:$overlayed_penta_id"], [$value_id]);
            $this->Save_Variant_ParameterValue($postvals, $value_id, $param_id, $params_all[$param_id], $windchill_id, $overlayed_penta_id, true);
        }
    }
    /*
  // ===============================================================================================
  function WriteHtml_GlobalParameter()
  {
    $showTable = true;

    echo '<div class="scrollboxY stdborder" style="height: 570px;">' . lf;
    echo $this->GetHtml_FormHeader("idr_", 2, "mainform") . lf;

    if ($this->m_EditMode == self::EDIT_NewGlobalVariable) {
      $types = [];
      foreach ($this->DB_allTypes as $typid => $type)
        $types[$typid] = substr($type, 6);
      array_pop($types);

      $headline = "<b>neuer globales Parameter</b>";
      $unit_str = '<select name="unit_id">' . $this->GetHtml_SelectOptions($this->DB_allUnits) . '</select>';
      $td_name = '<input id="id_varname" type="text" name="makroname" OnInput="OnVarChanged(this.value, \'\')">';
      $type = '<select name="type_id" onChange="OnVarTypeChanged(this.selectedIndex)">' . $this->GetHtml_SelectOptions($types) . '</select>';
      $type_id = 1;
      $allow_edit = true;
      $caption = "anlegen";

      $this->WriteHtml_VariantInfoBlock("Globaler Parameter: $headline", self::PRIV_SET_VARIABLE, 0);
    } else {
      if (count($this->S_currentVariable) == 1) {
        $param_id = $this->S_currentVariable[0];
        $param_set = &$this->DB_Variables[$param_id];
        $pid = $param_set['ecu_parameter_id'];
        $dataSet = &$this->DB_VariantEcuData[$pid];
        $makroName = $param_set['odx_name'];
        $headline = "<b>$makroName</b>";
        $caption = "übernehmen";

      } else {
        $param_id = 0;
        $makroName = "";
        $headline = "<i>{nicht ausgewählt}</i>";
        $showTable = false;
      }


      $this->WriteHtml_VariantInfoBlock("Globaler Parameter: $headline", self::PRIV_SET_VARIABLE, $param_id);


      if (isset ($param_set)) {
        $usage = "";
        $used_by_ecus = $this->GetEcuParametersUsingValuetype($param_id, 'macro');
        if (count($used_by_ecus)) {
          foreach ($used_by_ecus as $par_id => $set) {
            $ecu_id = $set['ecu_id'];
            $param_id = $set['ecu_parameter_set_id'];
            $param_name_01 = $set['odx_name'];
            $param_name_02 = safe_val($set, 'id', $param_name_01);
            $usage .= "<div><span class=\"LabelX W080\">{$this->allEcuNames[$ecu_id]} :</span><span>{$param_name_02}</span></div>";
          }
        }


        $td_name = "<b>$makroName</b>";
        $type = 'string';

        $allow_edit = $this->m_Permission[self::PRIV_SET_VARIABLE]['current'];

        $value = $this->GetValue_odx01($dataSet, $param_set, $type);
        $unit_id = $param_set['unit_id'];
        $unit_str = ($unit_id) ? $this->DB_allUnits[$unit_id] : "";
        $type_id = $param_set['type_id'];
        $type = (isset($type_id) ? $this->DB_allTypes[$type_id] : 'value_string');
        $type = substr($type, 6);
      }
    }


    if ($allow_edit) {
      $vis = ["", "none", "none", "none", "none"];
      $vis[$type_id] = "inline-block";

      $checked = ["", ""];
      $bVal = toBool($value) ? 1 : 0;
      $checked[$bVal] = " checked";

      if ($this->m_EditMode == 0) {
        $onValueChange = "OnInput=\"OnVarChanged(this.value, $value)\"";
        $onValueClick = "OnClick=\"OnVarChanged(this.value, $bVal)\"";
      }

      $td_value = <<<HEREDOC
<input style="display:{$vis[1]}" id="id_variable1" type="number" value="$value" name="value[1]" $onValueChange>
<input style="display:{$vis[2]}" id="id_variable2" type="number" value="$value" name="value[2]" $onValueChange step="0.01" >
<input style="display:{$vis[3]}" id="id_variable3" type="text"   value="$value" name="value[3]" $onValueChange>
<div style="display:{$vis[4]}" id="id_variable4">
    <span class="W040"><input type="radio" value="0" name="value[4]" $checked[0] $onValueClick> Aus</span>
    <span class="W040"><input type="radio" value="1" name="value[4]" $checked[1] $onValueClick> Ein</span>
</div>
HEREDOC;

      $btn_apply = <<<HEREDOC
            <div id="id_save_variable_inactive" class="stsbutton disabled buttonsize_small ">$caption</div>
            <div id="id_save_variable_active" class="stsbutton buttonsize_small" OnClick="SaveParameters('save_variable')">$caption</div>
HEREDOC;

      if ($this->m_EditMode == self::EDIT_NewGlobalVariable) {
        $unit_str = sprintf('<select name="unit">%s</select>',
            $this->GetHtml_SelectOptions($this->DB_allUnits, $unit_id));
      }
    } else {
      $td_value = isset ($dataSet) ? $value : "<i>{nicht gesetzt}</i>";
      $btn_apply = "&nbsp;";
    }

    if ($showTable)
      echo <<<HEREDOC
    <table class="makroValues">
      <thead>
        <tr><td>Parametername</td><td>Wert</td><td>Einheit</td><td>Typ</td><td></td><td>Verwendet in ECU/Parameter</td></tr>
      </thead>
      <tbody>
        <tr>
          <td>$td_name</td>
          <td>$td_value</td>
          <td>$unit_str</td>
          <td>$type</td>
          <td>$btn_apply</td>
          <td>$usage</td>
        </tr>
      </tbody>
    </table>
HEREDOC;


    echo <<<HEREDOC
  </form>
</div>
HEREDOC;

  }
*/

    // ===============================================================================================
    function PushConstantValue($rev_id, $psid, $hexvalue, $value, $type_id, $push_default = false) {
        $update_values = ['null', 'null', 'null', 'null', 'null'];
        $update_hex = isset ($hexvalue) ? "'$hexvalue'" : 'null';
        $ecu_id = $this->S_currentEcu;
        $insert_col = null;

        if (isset ($value)) {
            switch ($type_id) {
                case self::odx01_int:
                    $value = intval($value);
                    $update_values[$type_id] = $value;
                    $insert_col = 'value_int';
                    break;

                case self::odx01_float:
                    $value = floatval($value);
                    $update_values[$type_id] = $value;
                    $insert_col = 'value_double';
                    break;

                case self::odx01_string:
                    $value = $this->variantDataPtr->EscapeString($value);
                    $update_values[$type_id] = "E'$value'";
                    $insert_col = 'value_string';
                    break;

                case self::odx01_bool:
                    $value = (toBool($value) ? 't' : 'f');
                    $update_values[$type_id] = "'$value'";
                    $insert_col = 'value_bool';
                    break;
            }
        }

        $qry = $this->variantDataPtr->newQuery('ecu_parameters')
            ->where('ecu_id', '=', $ecu_id)
            ->where('ecu_parameter_set_id', '=', $psid);
        $value_id = $qry->getVal('ecu_parameter_id');

        $qry = $this->variantDataPtr->newQuery('variant_ecu_revision_mapping')->where("rev_id", "=", $rev_id);
        $result = $qry->get_no_parse("(variant_id::text || ':' || penta_id::text) as combi_id");
        $list_combi_ids = array_column($result, "combi_id");

        $kind = $push_default ? 'deflt' : 'const';
        $this->ClearWBackup_ParameterValues($kind, $list_combi_ids, [$value_id], false);

        $combi_csv = implode("','", $list_combi_ids);

        $sw_preset_type = $push_default ? 'D' : 'C';

        $insert_ids = $update_ids = [];

        $sql = "select * from ("
            . "  select (vehicle_variant_id::text || ':' || overlayed_penta_id::text) as combi_id, sw_preset_type from vehicle_variant_data where ecu_parameter_id=$value_id) as subsel "
            . "where combi_id in ('$combi_csv')";

        $qry = $this->variantDataPtr->newQuery();
        $res = $qry->query($sql, false);
        if ($res) {
            $list = $qry->fetchAll();
            $update_ids = array_column($list, 'combi_id');
            $insert_ids = array_diff($list_combi_ids, $update_ids);

            if ($push_default) {
                $update_ids = [];
                foreach ($list as $set) {
                    if ($set['sw_preset_type'] == 'D')
                        $update_ids[] = $set['combi_id'];
                }
            }
        }

        foreach ($insert_ids as $combi_id) {
            sscanf($combi_id, "%d:%d", $variant_id, $penta_id);
            $sql = "insert into vehicle_variant_data"
                . " (vehicle_variant_id, overlayed_penta_id, ecu_parameter_id, set_by, sw_preset_type,"
                . "  value_int, value_double, value_string, value_bool, value)"
                . " values ($variant_id, $penta_id, $value_id, {$_SESSION['sts_userid']}, '$sw_preset_type',"
                . " {$update_values[self::odx01_int]}, {$update_values[self::odx01_float]},"
                . " {$update_values[self::odx01_string]}, {$update_values[self::odx01_bool]}, $update_hex)";

            $res = $this->variantDataPtr->newQuery()->query($sql, false);
        }

        if (count($update_ids)) {
            $combi_csv = implode("','", $update_ids);

            $sql = "update vehicle_variant_data set "
                . "value_int       = {$update_values[self::odx01_int]}, "
                . "value_double    = {$update_values[self::odx01_float]}, "
                . "value_string    = {$update_values[self::odx01_string]}, "
                . "value_bool      = {$update_values[self::odx01_bool]}, "
                . "value           = {$update_hex}, "
                . "set_by          = {$_SESSION['sts_userid']}, "
                . "sw_preset_type  = '{$sw_preset_type}' "
                . "where ecu_parameter_id=$value_id "
                . "and (vehicle_variant_id::text || ':' || overlayed_penta_id::text) in ('$combi_csv')";

            $res = $this->variantDataPtr->newQuery()->query($sql, false);
        }
    }

    /*
  // ===============================================================================================
  function GetHtml_PartsFromVariant($variant)
  {
    $result = "";
    foreach ($this->allParts as $partId => $set) {
      $name = $set['name'];
      $result .= "<span><input type=\"checkbox\" name=\"filter[part][$partId]\"> $name</span><br>\n";
    }
    return $result;
  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_TabControl()
  {
    $tabsel = ["", "", "", "", "", "", "", "", "", "", ""];
    $tabsel[$this->S_Tab] = " tab_selected";

    $tabdef = [
        self::ID_TAB_Varianten => ['Fahrzeugwerte', 'Fahrzeugparameter und Werte setzten', true],
        self::ID_TAB_ECU_Versionen => ['ECU Software Versionen', 'ECU-Parameterset/Software Versionen definieren und bestimmen', true],
        self::ID_TAB_SW_ODX_Uebersicht => ['SW/ODX Übersicht', 'Übericht SW-Versionen und ODX-Erzeugung', true],
        self::ID_TAB_CAN_Matrix => ['CAN Matrix', '', false],
        self::ID_TAB_Pentavarianten => ['Penta-/Subvarianten', '', false],
        self::ID_TAB_Freigabe => ['Param. Freigabe', '', false],
        self::ID_TAB_Verantwortliche => ['Verantwortlichkeiten', 'Bauteilverantwortliche und Schreibberechtigte bestimmen', true], //$GLOBALS['IS_DEBUGGING']],
        self::ID_TAB_Parts => ['Baureiheninfo', 'Zusatzoptionen anlegen/löschen', false],
        self::ID_TAB_PartsParameter => ['Bauteil Parameter Verknüpung', 'Parameter patching der Zusatzoptionen', false],
        self::ID_TAB_EcuOdxVersion => ['Odx-Typ für ECU', 'Aktivieren der Odx.02 Unterstützung zu jeder ECU', true],
        self::ID_TAB_VariantenKonfig => ['D17 Varianten Konfigurator', '', false]
    ];


    $url = $this->GetHtml_Url();
    echo <<<HEREDOC

      <script>
        function OnTabSelect (tab) {
            if (! CheckModifiedAndConfirm()) return;
            document.location='{$url}&tab=' + tab;
        }
      </script>
      <div class="tabser">
        <div class="spc05"></div>
HEREDOC;

    $margin = "\n        ";
    foreach ($tabdef as $i => $def) {
      $url = $_SERVER['PHP_SELF'] . '?action=' . $this->action . "&tab=$i";

      $tabcontent = "";
      if ($def[2]) {
        if ($i == $this->S_Tab)
          $tabcontent = $def[0] . '<span class="ttiptext">' . $def[1] . '</span>';
        else
          $tabcontent = "<a href=\"javascript:TabSelect ('$url')\">" . $def[0] . '</a><span class="ttiptext">' . $def[1] . '</span>';
      } else {
        $tabcontent = '<del>' . $def[0] . '</del><span class="ttiptext">' . $def[1] . '</span>';
      }

      printf('%s<div id="tab%d" class="ttip tab%s">%s</div>', $margin, $i + 1, $tabsel[$i], $tabcontent);
    }

    echo "$margin</div>";

  }
*/
    /*
  // ===============================================================================================
  function GetVariantNameForSelect($variant_name, $has_subvar, $opened, $subName = "")
  {
    if (empty ($subName)) {

      if ($has_subvar) {
        $node_chars = $opened ? "[--] " : "[+] ";
      } else {
        $node_chars = "&nbsp;&nbsp;&nbsp;&nbsp; ";
      }
    } else {
      $node_chars = "";
      $variant_name = "&nbsp;&gt;&nbsp;&nbsp;&nbsp;##_$subName: ";
    }

    $len = strlen($variant_name);
    while ($len < 11) {
      $variant_name .= '&nbsp;&nbsp;&nbsp;';
      $len++;
    }
    return "{$node_chars}{$variant_name} &nbsp;&nbsp;";
  }
*/
    // ===============================================================================================
    function GetUsedEcus() {
        $result = [];

        foreach ($this->allEcuNames as $ecu_id => $name) {
            if (isset ($this->DB_allRevisions[$ecu_id]) && ($this->DB_allRevisions[$ecu_id]['ecu_used'] == 't'))
                $result[$ecu_id] = $name;
        }
        return $result;
    }
    /*
  // ===============================================================================================
  function WriteHtml_SelectVariant()
  {
    include __DIR__ . '/Parameterlist/Parameterlist.AuswahlLinks.php';
  }
*/
    // ===============================================================================================
    function GetPartsMappingActions(&$dbActions, &$mapping, $vid, $part_id) {
        $checked = isset ($_POST['parts'][$vid][$part_id]) ? 1 : 0;
        $db_exist = isset ($mapping[$vid][$part_id]);
        $db_val = $db_exist ? $mapping[$vid][$part_id] : null;

        if (!$db_exist) {
            $dbActions['insert'][$vid][$part_id] = $checked;
        } else {
            if ($checked != $db_val) {
                $dbActions['update'][$vid][$part_id] = $checked;
            }
        }
    }
    /*
  //================================================================================================
  function SaveVariantPartMapping()
  {
    $groupedParts = &$this->GetPartsGrouped();
    $mapping = &$this->GetVariantPartsMapping();
    $dbActions = ['insert' => [], 'update' => []];

    foreach ($this->DB_allVariants as $vid => $variant) {
      foreach ($groupedParts as $pid => $group) {
        $type = substr($pid, 0, 1);
        $part_id = substr($pid, 2);

        if ($type == 'P') {
          $part = &$group['options'][$part_id];
          $this->GetPartsMappingActions($dbActions, $mapping, $vid, $part_id);
        } else {
          if ($group['allow_multi']) {
            foreach ($group['options'] as $part_id => $part) {
              $this->GetPartsMappingActions($dbActions, $mapping, $vid, $part_id);
            }
          } else {
            $set_to = $_POST['parts'][$vid][$pid];
            if ($set_to == 'error')
              continue;


            foreach ($group['options'] as $part_id => $part) {
              $val = ($part_id == $set_to) ? 1 : 0;

              if (isset ($mapping[$vid][$part_id])) {
                if ($mapping[$vid][$part_id] != $val)
                  $dbActions['update'][$vid][$part_id] = $val;
              } else {
                $dbActions['insert'][$vid][$part_id] = $val;
              }
            }
          }
        }
      }
    }

//         $dump = new CDUMP_FILE();
//         $dump->Dump_Value ($dbActions);

    if (count($dbActions['insert'])) {
      $ql_insert = "insert into construction_parts_mapping (variant_id, part_id, count) values \n";
      foreach ($dbActions['insert'] as $variant_id => &$list)
        foreach ($list as $part_id => $count)
          $ql_insert .= "($variant_id, $part_id, $count),\n";

      $l = strlen($ql_insert);
      $ql_insert[$l - 2] = ';';
//             $dump->write ($ql_insert);

      $this->variantDataPtr->newQuery()->query($ql_insert);
    }


    if ($dbActions['update']) {
      $ql_update = "";
      foreach ($dbActions['update'] as $variant_id => &$list)
        foreach ($list as $part_id => $count)
          $ql_update .= "update construction_parts_mapping set count=$count where variant_id=$variant_id and part_id=$part_id;\n";

//             $dump->write ($ql_update);
      $this->variantDataPtr->newQuery()->query($ql_update);
    }

    unset ($this->variantPartsMapping);
  }
*/
    /*
  // ===============================================================================================
  function GetSelectedPartId(&$options, &$mapping)
  {
    $default = 0;
    foreach ($options as $part_id => &$part) {
      if (isset ($mapping[$part_id])) {
        if ($mapping[$part_id] > 0)
          return $part_id;
      } else {
        if ($part['is_default'])
          $default = $part_id;
      }
    }
    return $default;
  }
*/
    // ===============================================================================================
    function DisableUnusedValues($rev, $param_set_id) {
        $qry = $this->ecuPtr->newQuery('variant_ecu_revision_mapping');
        $qry = $qry->where('rev_id', '=', $rev);
        $res = $qry->get('variant_id, penta_id, ecu_id');


        if (count($res)) {
            $ecu_id = $res[0]['ecu_id'];

            $qry = $this->ecuPtr->newQuery('ecu_parameters')
                ->where('ecu_id', '=', $ecu_id)
                ->where('ecu_parameter_set_id', '=', $param_set_id);
            $param_id = $qry->getVal('ecu_parameter_id');

            if ($param_id) {
                $combi_ids = [];
                foreach ($res as $set)
                    $combi_ids[] = "{$set['variant_id']}:{$set['penta_id']}";
                $combi_csv = implode("','", $combi_ids);

                $this->ClearWBackup_ParameterValues('disable', $combi_ids, [$param_id], false);

                $sql = "
                    update vehicle_variant_data
                    set tag_disabled='t'
                    where ecu_parameter_id=$param_id
                    and (vehicle_variant_id::text || ':' || overlayed_penta_id::text) in ('$combi_csv')";

                $qry = $this->ecuPtr->newQuery('vehicle_variant_data');
                $res = $qry->query($sql);
            }
        }
    }
    /*
  // ===============================================================================================
  function WriteHtml_VariantPartsMappingHeader($groupedParts, $has_multi, $withScrollDummy = false)
  {
    $span = ($has_multi) ? ' rowspan="2"' : "";

    echo <<<HEREDOC
    <thead>
      <tr class="tabellenkopf">
        <th$span class="thVariant">Fzg.-konfiguration</th>
HEREDOC;

    foreach ($groupedParts as $id => $group) {
      if ($group['allow_multi'])
        echo '        <th colspan="' . count($group['options']) . '">';
      else
        echo '        <th rowspan="2" class="thPart">';

      echo $group['group_name'] . "</th>\n";

//             $span = ($group['allow_multi']) ?  ' colspan="' . count ($group['options']) . '"'   :   ' rowspan="2"';
//             echo "        <th$span class=\"thPart\">" . $group['group_name'] . "</th>\n";
    }

    if ($has_multi) {
      if ($withScrollDummy)
        echo "        <td$span class=\"scrollspace\">&nbsp;</td>\n";
      echo "      </tr>\n      <tr>\n";

      foreach ($groupedParts as $id => $group) {
        if ($group['allow_multi']) {
          foreach ($group['options'] as $pid => $part)
            echo "        <td class=\"thMulti\">" . $part['name'] . "</td>\n";
        }
      }

      echo "      </tr>\n    </thead>\n";
    }
  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_VariantPartsMappingTable(&$groupedParts, &$mapping)
  {
    foreach ($this->DB_allVariants as $vid => $variant) {
      echo "      <tr>\n";
      echo "        <td>" . $variant['variant_name'] . "</td>\n";

      foreach ($groupedParts as $pid => $group) {
        $type = substr($pid, 0, 1);
        $part_id = substr($pid, 2);
        $name = "parts[$vid][$part_id]";

        if ($type == 'P') {
          $part = &$group['options'][$part_id];
          $hasPart = isset($mapping[$vid][$part_id]) ? $mapping[$vid][$part_id] : ($part['is_default']);
          $checked = $hasPart ? " checked" : "";
          echo "        <td><input type=\"checkbox\" name=\"$name\"$checked OnClick=\"OnPartsChanged()\">\n        </td>\n";
        } else {
          if ($group['allow_multi']) {
            echo "        ";
            foreach ($group['options'] as $part_id => $part) {
              $name = "parts[$vid][$part_id]";
              $hasPart = isset($mapping[$vid][$part_id]) ? $mapping[$vid][$part_id] : ($part['is_default']);
              $checked = $hasPart ? " checked" : "";

              echo "<td><input type=\"checkbox\" name=\"$name\"$checked OnClick=\"OnPartsChanged()\"></td>";
            }
            echo "\n";
          } else {
            $selPart = $this->GetSelectedPartId($group['options'], $mapping[$vid]);
            $name = "parts[$vid][$pid]";

            echo "        <td><select name=\"$name\" OnChange=\"OnPartsChanged()\">";
            if ($group['allow_none']) {
              $selected = ($selPart == 0) ? " selected" : "";
              echo "<option value=\"error\"$selected>!NICHT GESETZT!</option>";
            } else
              if ($selPart == 0) {
                echo "<option value=\"0\"$selected>---</option>";
              }


            foreach ($group['options'] as $part_id => $part) {
              $selected = ($part_id == $selPart) ? " selected" : "";
              echo "<option value=\"$part_id\"$selected>" . $part['name'] . "</option>";
            }
            echo "</select>\n        </td>\n";
          }
        }
      }
      echo "      </tr>\n";
    }
  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_VariantPartsMapping()
  {
    echo <<<HEREDOC
<div class="mainframe" id="id_mainframe">
  <div class="seitenteiler" style="width:100%;justify-content: center; align-items: center;">
    <img src="images/symbols/under-construction.jpeg"><br>
  </div>
</div>
HEREDOC;
    return;

    $groupedParts = $this->GetPartsGrouped();
    $mapping = $this->GetVariantPartsMapping();

    $has_multi = false;
    $num_cols = 1;

    foreach ($groupedParts as $id => $group) {
      if ($group['allow_multi']) {
        $has_multi = true;
        $num_cols += count($group['options']);
      } else {
        $num_cols++;
      }
    }

    $formheader = $this->GetHtml_FormHeader("idr_", 0, "mainform");

    echo <<<HEREDOC
$formheader
<div class="mainframe" id="id_mainframe">
  <div class="seitenteiler"
    <div class="scrollHead stdborder" id="id_scrollHeader">
      <div id="id_headroom">
        <table class="partstable" id="id_tableHeader">
HEREDOC;

    $this->WriteHtml_VariantPartsMappingHeader($groupedParts, $has_multi, true);

    echo "
          <tr class=\"dummy\" id=\"id_headercols\">";
    for ($i = 0; $i < $num_cols; $i++)
      echo "<td></td>";
    echo "</tr>\n";

    echo <<<HEREDOC

        </table>
      </div>   <!-- id_headroom -->
    </div>   <!-- scrollboxX -->

    <div class="scrollboxXY stdborder" id="id_scrollbox">
      <div id="id_cutter">
        <table class="partstable" id="id_tableBody">
HEREDOC;

    $this->WriteHtml_VariantPartsMappingHeader($groupedParts, $has_multi);
    $this->WriteHtml_VariantPartsMappingTable($groupedParts, $mapping);

    echo <<<HEREDOC
        </table>
      </div>   <!-- id_cutter -->
    </div>   <!-- scrollboxXY -->
  </div>  <!-- seitenteiler -->
</div>   <!-- mainframe -->


<div class="variantPartsButtons stdborder">
<div style="width:200px;" id="ausgabe"></div>
HEREDOC;

    $this->WriteHtml_SaveVariantParts();

    echo "</div>\n</form>\n";

    echo '<div class="infoPanel">Under construction</div>';

  }
*/
    /*
  // ===============================================================================================
  function WriteHtml_SwOdxUebersicht()
  {
    include __DIR__ . '/Parameterlist/Parameterlist.SwUebersicht.php';
  }
*/
    // ===============================================================================================
    function WriteHtml_SetOdxVersions() {
        $this->GetEnggPrivileges(self::PRIV_ECU_PROPERTIES, 0);
        $allow_change = ($this->m_Permission[self::PRIV_ECU_PROPERTIES]['current']);

        echo $this->GetHtml_FormHeader('idr_');

        echo <<<HEREDOC
<div class="mainframe" id="id_mainframe" style="display:block">
  <div class="ecu_properties">
HEREDOC;

        $this->WriteHtml_OwnerAndPrivileged(self::PRIV_ECU_PROPERTIES, 0);

        echo <<<HEREDOC
  </div>
  <div class="ecu_properties">
    <table>
      <tbody>
HEREDOC;

        echo "
        <tr><th>ECU</th>";
        foreach ($this->allEcus as $ecu_id => $set)
            echo "<td>{$set['name']}</td>";
        echo "</tr>
        <tr><th>ODX.02</th>";
        foreach ($this->allEcus as $ecu_id => $set) {
            if ($allow_change) {
                $checked = toBool($set['supports_odx02']) ? " checked" : "";
                echo "<td><input type=\"checkbox\" name=\"odx2[$ecu_id]\" value=\"1\"$checked OnClick=\"Ajaxecute ('setodx2', 'odx2[$ecu_id]=' + this.checked);\"></td>";
            } else {
                $checked = toBool($set['supports_odx02']) ? "X" : " ";
                echo "<td><span class=\"blackNote\">$checked</span></td>";
            }
        }
        echo "</tr>
        <tr><th>Big-Endian</th>";
        foreach ($this->allEcus as $ecu_id => $set) {
            if ($allow_change) {
                $checked = toBool($set['big_endian']) ? " checked" : "";
                echo "<td><input type=\"checkbox\" name=\"bigend[$ecu_id]\" value=\"1\"$checked OnClick=\"Ajaxecute ('setbigend', 'bigend[$ecu_id]=' + this.checked);\"></td>";
            } else {
                $checked = toBool($set['big_endian']) ? "X" : " ";
                echo "<td><span class=\"blackNote\">$checked</span></td>";
            }
        }
        echo <<<HEREDOC
        </tr>
      </tbody>
    </table>
  </div>
</div>
</form>
HEREDOC;

    }
    /*
  // ===============================================================================================
  function WriteHtml_TAB_Verantwortliche()
  {
    include __DIR__ . "/Parameterlist/Parameterlist.Verantwortliche.php";
  }
*/
    // ===============================================================================================
    function WriteHtml_CommandButtons($bAllowEdit = false, $editMode = false, $addNew = BT_NONE, $addClone = BT_NONE, $addDownload = BT_NONE, $addCopy = BT_NONE, $addDel = BT_NONE, $xtraButton = '') {
        $bt_copy_pararm = "";

        if ($editMode || ($addCopy == BT_TOGGLED)) {
            $addNew = ($addNew) ? BT_DISABLED : BT_NONE;
            $addClone = ($addClone) ? BT_DISABLED : BT_NONE;
            $addDownload = ($addDownload) ? BT_DISABLED : BT_NONE;
            $addDel = ($addDel) ? BT_DISABLED : BT_NONE;
        }

        if ($editMode || ($addNew == BT_TOGGLED) || ($addClone == BT_TOGGLED)) {
            $addCopy = ($addClone) ? BT_DISABLED : BT_NONE;
            $addDel = ($addDel) ? BT_DISABLED : BT_NONE;
        }

        if ($addNew == BT_TOGGLED)
            $addClone = BT_DISABLED;
        if ($addClone == BT_TOGGLED)
            $addNew = BT_DISABLED;

        $url = $this->GetHtml_Url();

        if ($bAllowEdit && !$this->locked)
            $bt_edit = sprintf('<li><a href="%s&command=edit&odxVersion[%s]=%s" class="sts_submenu W080">Bearbeiten</a></li>', $url,
                isset($_REQUEST['ecuVersion']['selectedValue']) ? $_REQUEST['ecuVersion']['selectedValue'] : $_REQUEST['ecuVersion']['selectedEcu'], isset($_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])]) && $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] != "" ? $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] : "2");
        else
            $bt_edit = '<li><span class="sts_submenu W080 disabled">Bearbeiten</span></li>';

        $otherMode = ($addNew == BT_TOGGLED) || ($addClone == BT_TOGGLED);
        $bearbeiten_selected = ($otherMode) ? " disabled" : " selected";

        if ($addCopy)
            $bt_copy_pararm = "<li><span class=\"sts_submenu W080\">Par. kopieren</span></li>";

        echo '
              <ul class="submenu_ul">';
        /*
    if ($addNew) {
      switch ($addNew) {
        case BT_DISABLED:
          printf('<li><span class="sts_submenu W080 disabled">Neu</span></li>');
          break;
        case BT_ENABLED:
          printf('<li><a href="%s&command=new" class="sts_submenu W080">Neu</a></li>', $url);
          break;
        case BT_TOGGLED:
          printf('<li><span class="sts_submenu W080 selected">Neu</span></li>');
          break;
      }
    }

    if ($addClone) {
      switch ($addClone) {
        case BT_DISABLED:
          printf('<li><span class="sts_submenu W080 disabled">Neue Kopie</span></li>');
          break;
        case BT_ENABLED:
          printf('<li><a href="%s&command=copy" class="sts_submenu W080">Neue Kopie</a></li>', $url);
          break;
        case BT_TOGGLED:
          printf('<li><span class="sts_submenu W080 selected">Neue Kopie</span></li>');
          break;
      }
    }

    if ($addDel) {
      switch ($addDel) {
        case BT_DISABLED:
          printf('<li><span class="sts_submenu W080 disabled">Löschen</span></li>');
          break;
        case BT_ENABLED:
          printf('<li><a href="javascript:DeleteRev();" class="sts_submenu W080">Löschen</a></li>');
          break;
      }
    }

    if ($addCopy == BT_TOGGLED) {
      echo <<<HERE______________________________________________________DOC

                <li><span class="sts_submenu W080 disabled">Bearbeiten</span></li>
		        <li><span class="sts_submenu W080 disabled">Speichern</span></li>
                <li><a href="$url&command=cancel" class="sts_submenu W080">Abbruch</a></li>
                <li><a href="javascript:SaveParametersX('copy_params', 'idr');" class="sts_submenu W080">ausführen</a></li>
HERE______________________________________________________DOC;
    } else
      if ($editMode || $otherMode) {
        echo <<<HERE______________________________________________________DOC

                <li><span class="sts_submenu W080$bearbeiten_selected">Bearbeiten</span></li>
		        <li><a href="javascript:SaveParameters('save')" class="sts_submenu W080">Speichern</a></li>
                <li><a href="$url&command=cancel" class="sts_submenu W080">Abbruch</a></li>
             {$bt_copy_pararm}

HERE______________________________________________________DOC;
      }      else {
        if ($addCopy == BT_ENABLED)
          $bt_copy_pararm = "<li><a href=\"$url&command=start_copy_params\" class=\"sts_submenu W080\">Par. kopieren</a></li>";

        echo <<<HERE______________________________________________________DOC

                {$bt_edit}
		        <li><span class="sts_submenu W080 disabled">Speichern</span></li>
                <li><span class="sts_submenu W080 disabled">Abbruch</span></li>
                {$bt_copy_pararm}
HERE______________________________________________________DOC;
      }


    echo <<<HERE______________________________________________________DOC

                <li class="dropdown">
                  <div class="sts_submenu W080 disabled">Export</div>
			      <div class="dropdown-content">
					<!-- <a href="?action=parameterlist&command=export_odt">OpenDocument (.odt)</a> -->
					<!-- <a href="?action=parameterlist&command=export_xlsx">OfficeXML (.xlsx)</a> -->
					<a href="#">OpenDocument (.odt)</a>
					<a href="#">OfficeXML (.xlsx)</a>
			      </div>
			    </li>
HERE______________________________________________________DOC;

*/


//NEW MENU ---------------- 2019
        if ($editMode || $this->m_EditMode == self::EDIT_DistributeSWParameters) {
            if ($this->m_OdxVerShow == 2) {
                if ($this->m_EditMode == self::EDIT_DistributeSWParameters) {
                    echo <<<HERE______________________________________________________DOC

            <li><span class="sts_submenu W080$bearbeiten_selected">Bearbeiten</span></li>
            <li><a href="javascript:SaveParameters('save')" class="sts_submenu W080">Speichern</a></li>
HERE______________________________________________________DOC;

                    $msg = sprintf('<li><a href="%s&command=cancel&odxVersion[%s]=%s" class="sts_submenu W080">Abbruch</a></li>', $url, isset($_REQUEST['ecuVersion']['selectedValue']) ? $_REQUEST['ecuVersion']['selectedValue'] : $_REQUEST['ecuVersion']['selectedEcu'], isset($_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])]) && $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] != "" ? $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] : "2");

                    echo $msg;
                    echo '<li><span class="sts_submenu W080 disabled">Par. kopieren</span></li>';
                    echo '<li><a href="javascript:copySelectedParameters()" class="sts_submenu W080">Copy</a></li>';
                    echo '<li><a href="javascript:copySelectedParametersIntoOtherSW()" class="sts_submenu W130" title="Changes will not be saved">Copy into other SW</a></li>';

                } else {
                    echo <<<HERE______________________________________________________DOC

            <li><span class="sts_submenu W080$bearbeiten_selected">Bearbeiten</span></li>
HERE______________________________________________________DOC;
                    echo '<li><a href="javascript:SaveParameters(\'save\')" class="sts_submenu W080">Speichern</a></li>';
                    $msg = sprintf('<li><a href="%s&command=cancel&odxVersion[%s]=%s" class="sts_submenu W080">Abbruch</a></li>', $url, isset($_REQUEST['ecuVersion']['selectedValue']) ? $_REQUEST['ecuVersion']['selectedValue'] : $_REQUEST['ecuVersion']['selectedEcu'], isset($_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])]) && $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] != "" ? $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] : "2");

                    echo $msg;
                    echo "<li><a href=\"$url&command=start_copy_params\" class=\"sts_submenu W080\">Par. kopieren</a></li>";
                }
            } else {
                echo <<<HERE______________________________________________________DOC

            <li><span class="sts_submenu W080$bearbeiten_selected">Bearbeiten</span></li>
            <li><a href="javascript:SaveParameters('save')" class="sts_submenu W080">Speichern</a></li>
HERE______________________________________________________DOC;

                $msg = sprintf('<li><a href="%s&command=cancel&odxVersion[%s]=%s" class="sts_submenu W080">Abbruch</a></li>', $url, isset($_REQUEST['ecuVersion']['selectedValue']) ? $_REQUEST['ecuVersion']['selectedValue'] : $_REQUEST['ecuVersion']['selectedEcu'], isset($_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])]) && $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] != "" ? $_REQUEST['odxVersion'][key($_REQUEST['odxVersion'])] : "2");

                echo $msg;
            }
        } else {
            echo <<<HERE______________________________________________________DOC

                {$bt_edit}
		        <li><span class="sts_submenu W080 disabled">Speichern</span></li>
            <li><span class="sts_submenu W080 disabled">Abbruch</span></li>
            
HERE______________________________________________________DOC;
            if ($this->m_OdxVerShow == 2) {
                echo '<li ><span class="sts_submenu W080 disabled" > Par . kopieren</span ></li >';
            }
        }
//----------------------
        switch ($addDownload) {
            case BT_NONE:
                break;

            case BT_DISABLED:

                echo <<<HERE______________________________________________________DOC

                <li class="dropdown">
                  <div class="sts_submenu W080 disabled"><img src="/images/symbols/download_inactive.png"> ODX</div>
			    </li>
HERE______________________________________________________DOC;
                break;

            case BT_ENABLED:
                echo <<<HERE______________________________________________________DOC

                <li><a href="$url&command=downloadOdx&selected[odxMethod]=2" class="sts_submenu W080"><img src="/images/symbols/download_active.png"> ODX</a></li>
HERE______________________________________________________DOC;


        }

        echo "\n$xtraButton";
        echo "\n              </ul>\n";

    }
    /*
  // ===============================================================================================
  function WriteHtml_SaveVariantParts()
  {
    echo <<<HERE______________________________________________________DOC
        <div class="MiniButtons">
            <ul class="submenu_ul">
                <li><span id="id_save_parts_disabled" class="sts_submenu W160 disabled">Speichern</span></li>
		        <li><a href="javascript:document.formPartsMapping.submit()" id="id_save_parts" class="sts_submenu W160" style="display:none;">Speichern</a></li>
            </ul>
        </div>
HERE______________________________________________________DOC;
  }
*/
    // ===============================================================================================
    function AddRows_ECUVersion() {
        $num = $_POST['addnum'];
        $numnew = $this->S_EditSet['count_new'];
        $new_set = &$_REQUEST['ecu_new'];

        for ($i = 0; $i < $num; $i++) {
            $numnew++;
            $editSet = $new_set;

            $editSet['added'] = 1;
            $editSet['order'] = -1;
            $editSet['deleted'] = false;
            $editSet['id'] = '';
            $editSet['idx_id'] = '';
            $editSet['udsId'] = $new_set['udsId'];
            $editSet['type'] = $new_set['type'];
            $editSet['size'] = $new_set['size'];
            $editSet['factor'] = $new_set['factor'];
            $editSet['offset'] = $new_set['offset'];
            $editSet['unit'] = $new_set['unit'];
            $editSet['startBit'] = $new_set['start'];
            $editSet['stopBit'] = $new_set['stop'];
//2019--------------------------
            $editSet['protocol'] = $new_set['protocol'];
//------------------------------
            $editSet['valuetype'] = $new_set['vtype'];
            $editSet['action'] = is_array($new_set['action']) ? implode('', array_keys($new_set['action'])) : "";
            $editSet['use_in_odx01'] = false;
            switch ($new_set['vtype']) {
                case 'unused':
                    $editSet['value'] = '';
                    break;
                case 'macro':
                    $editSet['value'] = $new_set['macro'];
                    break;
                default:
                    $editSet['value'] = $new_set['value'];
                    break;
            }

            $this->S_EditSet['edit']["99add$numnew"] = $editSet;
        }

        $this->S_EditSet['count_new'] = $numnew;
    }


    //2019------------------------------
    function AddCopiedRows_ECUVersion() {
//    $num = $_POST['addnum'];
        $numnew = $this->S_EditSet['count_new'];
        $num = 0;

        foreach ($_REQUEST['ecu'] as $key => $value)
            if (substr($key, 0, 5) == '99add')
                ++$num;


        for ($i = 0; $i < $num; $i++) {
            ++$numnew;
            $new_set = $_REQUEST['ecu']['99add' . ($i + 1)];
            $editSet = $_REQUEST['ecu']['99add' . ($i + 1)];
            $editSet['added'] = 1;
            $editSet['order'] = $new_set['order'];
            $editSet['deleted'] = false;
            $editSet['id'] = $new_set['id'];
            $editSet['idx_id'] = '';
            $editSet['udsId'] = $new_set['udsId'];
            $editSet['type'] = $new_set['type'];
            $editSet['size'] = $new_set['size'];
            $editSet['factor'] = $new_set['factor'];
            $editSet['offset'] = $new_set['offset'];
            $editSet['unit'] = $new_set['unit'];
            $editSet['startBit'] = $new_set['start'];
            $editSet['stopBit'] = $new_set['stop'];
//2019--------------------------
            $editSet['protocol'] = $new_set['protocol'];
//------------------------------
            $editSet['valuetype'] = $new_set['valuetype'];
            $editSet['action'] = is_array($new_set['action']) ? implode('', array_keys($new_set['action'])) : "";

            switch ($new_set['valuetype']) {
                case 'part':
                case 'unused':
                    $editSet['value'] = '';
                    break;
                case 'macro':
                    $editSet['value'] = $new_set['macroname'];
                    break;
                case 'const':
                case 'deflt':
                    $editSet['value'] = $new_set['value'];
                    break;
                case 'dyncs' :
                    $editSet['value'] = $new_set['dyn_token'];
                    break;
            }

            $this->S_EditSet['edit']["99add" . $numnew] = $editSet;
        }
        $this->S_EditSet['count_new'] = $numnew;
    }

//--------------------

    // ===============================================================================================
    function Check_ECUVersionEditSet() {
        if (!$this->CheckRevisionName($this->S_EditSet['revision']['sts_version']))
            $this->S_EditSet['err']['revision']['sts_version'] = true;
        if (!preg_match('/^[0-9a-f]{1,8}$/', $this->S_EditSet['revision']['request_id']))
            $this->S_EditSet['err']['revision']['request_id'] = true;
        if (!preg_match('/^[0-9a-f]{1,8}$/', $this->S_EditSet['revision']['response_id']))
            $this->S_EditSet['err']['revision']['response_id'] = true;


        $errCount = count($this->S_EditSet['err']['revision']);

        if ($this->m_OdxVerShow == 1)
            return $errCount == 0;

        foreach ($this->S_EditSet['edit'] as $param_id => &$editSet) {
//             if (($param_id==4) & !isset ($editSet['used']))          continue;

            if (isset($editSet['deleted']) && stripos($editSet['deleted'], 'dx.sts.02'))
                continue;

            if ($param_id == 4 && isset($editSet['deleted']) && $editSet['deleted'])
                continue;

            $errors = [];

            if (empty ($editSet['id'])) $errors[] = 'id';
            //if (!isset ($editSet['udsId']) || ($editSet['udsId'] == '')) $errors[] = 'udsId';
            if (empty ($editSet['size']) || ($editSet['size'] == 0)) $errors[] = 'size';

            if (count($errors)) {
                $errCount += count($errors);
                $this->S_EditSet['err'][$param_id] = $errors;
            }
        }

        $this->S_EditSet['err']['count'] = $errCount;
        return $errCount == 0;
    }

    // ===============================================================================================
    function InsertOrUpdateSpecialTag($rev_id, $param_id, $tagname, $tagvalue, $is_odx_tag = false) {
        $insert = [
            'ecu_revision_id' => $rev_id,
            'ecu_parameter_set_id' => $param_id,
            'tag' => $tagname,
            'tag_value' => $tagvalue,
            'is_odx_tag' => $is_odx_tag ? 't' : 'f'
        ];

        $this->ecuPtr->newQuery('ecu_tag_configuration')->insert($insert);

    }

    // ===============================================================================================
    function SaveParamDefinitionSet_odx01($old_rev_id, $new_rev_id, $param_id, &$editSet, &$orginalSet) {
        $rev = $old_rev_id + $new_rev_id;

        if ($editSet['unit_01'] != $orginalSet['unit_01']) {
            $unit_id = $editSet['unit_01'];
            $this->ecuPtr->newQuery('ecu_parameter_sets')
                ->where('ecu_parameter_set_id', '=', $param_id)
                ->update(['unit_id'], [$unit_id]);
        }

        $has_value = ($param_id <= 2);
        $has_orginal = isset($orginalSet);
        $no_orginal = !$has_orginal;

        if ($param_id > 2) {
            if (($editSet['unitstr_02'] != '') || ($has_orginal && ($editSet['unitstr_02'] != $orginalSet['unitStr_02'])))
                $insert_vals[] = ['unit', $editSet['unitstr_02'], $param_id, $rev, 't'];

            $has_value = ($editSet['valuetype'] != 'unused');
            if ($no_orginal || ($has_orginal && ($editSet['valuetype'] != $orginalSet['valuetype'])))
                $insert_vals[] = ['valuetype', $editSet['valuetype'], $param_id, $rev, 'f'];

            if ($has_value && ($no_orginal || ($has_orginal && ($editSet['value'] != $orginalSet['value']))))
                $insert_vals[] = ['value', $editSet['value'], $param_id, $rev, 'f'];
        } else {
            if ($new_rev_id || ($editSet['value'] != $orginalSet['value']) || empty($orginalSet['version'])) {
                $paramset = &$this->DB_Parameters[$param_id]['tags'];
                $hex_value = $editSet['value'];
                $this->Convert2Hex($hex_value, $paramset);

                $insert_vals[] = ['version', $hex_value, $param_id, $rev, 't'];
                $insert_vals[] = ['value', $editSet['value'], $param_id, $rev, 'f'];
            }
        }


        if (count($insert_vals)) {
            $insert_cols = ['tag', 'tag_value', 'ecu_parameter_set_id', 'ecu_revision_id', 'is_odx_tag'];

            $result = $this->ecuPtr->newQuery('ecu_tag_configuration')->insert_multiple_new($insert_cols, $insert_vals);
            if (!$result) {
                $this->SetError(ERROR_CANNOT_SAVE_PARAMDEF, $this->ecuPtr->GeLastError());
                return false;
            }
        }

        switch ($editSet['valuetype']) {
            case  'version':
            case  'const':
                $hex_value = $editSet['value'];
                if (!$this->Convert2Hex($hex_value, $paramset))
                    $hex_value = null;
                $this->PushConstantValue($rev, $param_id, $hex_value, $editSet['value'], $editSet['type_id']);
                break;
        }


        return true;

    }

    // ===============================================================================================
    function SaveParamDefinitionSet($old_rev_id, $new_rev_id, $param_id, &$editSet, &$orginalSet) {
        $rev = $old_rev_id + $new_rev_id;

        $insert_cols = ['tag', 'tag_value', 'ecu_parameter_set_id', 'ecu_revision_id', 'is_odx_tag'];
        $insert_vals = [];
        $list_new_parameters = [];
        $value_changed = false;
        $hex_value = '';

        if (substr($param_id, 0, 5) == '99add') {
            $sql = $this->ecuPtr->newQuery('ecu_tag_configuration')
                ->join('ecu_parameter_sets', 'ecu_parameter_sets.ecu_parameter_set_id=ecu_tag_configuration.ecu_parameter_set_id')
                ->where('tag', '=', 'id')
                ->where('tag_value', '=', $editSet['id']);
            $result = $sql->get('*');
            if ($result && count($result)) {
                $param_id = $result[0]['ecu_parameter_set_id'];
            } else {
                if (isset($_REQUEST['ecu'][$param_id]['previous'])) {
                    $set = $this->ecuPtr->newQuery('ecu_parameter_sets')
                        ->where('ecu_parameter_set_id', '=', $_REQUEST['ecu'][$param_id]['previous'])
                        ->get('type_id, parent_id, odx_tag_name, use_in_old_format');
                    $param_insert = ['odx_name' => $editSet['id'], 'type_id' => $set[0]['type_id'], 'parent_id' => $set[0]['parent_id'], 'odx_tag_name' => $set[0]['odx_tag_name'], 'use_in_old_format' => $set[0]['use_in_old_format']];
                } else {
                    $param_insert = ['odx_name' => $editSet['id'], 'type_id' => 5, 'parent_id' => 3, 'odx_tag_name' => 'parameter', 'use_in_old_format' => 'f'];
                }
                $result = $this->ecuPtr->newQuery('ecu_parameter_sets')->insert($param_insert);
                $sql = $this->ecuPtr->newQuery('ecu_parameter_sets')->where('odx_name', '=', $editSet['id']);
                $result = $sql->get('*');
                if ($result && count($result)) {
                    $param_id = $result[0]['ecu_parameter_set_id'];
                } else {
                    return false;
                }

                $param_insert = ['ecu_parameter_set_id' => $param_id, 'ecu_id' => $this->S_currentEcu, 'order' => -1];
                $result = $this->ecuPtr->newQuery('ecu_parameters')->insert($param_insert);

                if ($result)
                    $list_new_parameters[] = $editSet['id'];
            }
        } else
            if (empty ($this->S_dbParamDef[$param_id])) {
                $param_insert = ['ecu_parameter_set_id' => $param_id, 'ecu_id' => $this->S_currentEcu, 'order' => -1];
                $result = $this->ecuPtr->newQuery('ecu_parameters')->insert($param_insert);
            }


        if (count($list_new_parameters)) {
            $mail_to = "Simon.Krisch@streetscooter.eu";
            $ecu_name = $this->allEcuNames[$this->S_currentEcu];
            $parlist = implode("  \r\n", $list_new_parameters);
            $mailtext = "Folgende Parameter sind für das ECU-Gerät $ecu_name hinzugefügt worden:\r\n  $parlist";

            $mailer = new MailerSmimeSwift ($mail_to, "Simon Krisch", "Neue Parameter für $ecu_name" . $d, $mailtext, null, true);
        }

        $has_orginal = isset($orginalSet);
        $no_orginal = !$has_orginal;

        $paramset = [
            'valuetype' => ['tag_value' => $editSet['valuetype']],
            'type' => ['tag_value' => $editSet['type']],
            'byteCount' => ['tag_value' => $editSet['size']],
            'factor' => ['tag_value' => $editSet['factor']],
            'offset' => ['tag_value' => $editSet['offset']],
            'bigEndian' => ['tag_value' => $editSet['bigEndian']],
        ];
//----------------2019
        if ($new_rev_id || $no_orginal || ($editSet['protocol'] != $orginalSet['protocol']))
            $insert_vals[] = ['protocol', $editSet['protocol'], $param_id, $rev, 't'];
//------------------------

        if ($new_rev_id || $no_orginal || ($editSet['id'] != $orginalSet['id']))
            $insert_vals[] = ['id', $editSet['id'], $param_id, $rev, 't']; //($param_id>2) ? 't':'f'];

        if ($new_rev_id || $no_orginal || ($editSet['order'] != $orginalSet['order']))
            $insert_vals[] = ['order', $editSet['order'], $param_id, $rev, 'f'];

        if ($new_rev_id || $no_orginal || ($editSet['udsId'] != $orginalSet['udsId']))
            $insert_vals[] = ['udsId', normhex($editSet['udsId'], 2), $param_id, $rev, 't'];

        if ($new_rev_id || $no_orginal || ($editSet['type'] != $orginalSet['type']))
            $insert_vals[] = ['type', $editSet['type'], $param_id, $rev, 't'];

        if ((($editSet['type'] == 'signed') || ($editSet['type'] == 'unsigned'))
            && (isset ($orginalSet['bigEndian'])
                ? ($editSet['bigEndian'] != $orginalSet['bigEndian'])
                : $editSet['bigEndian']
            )
        ) $insert_vals[] = ['bigEndian', $editSet['bigEndian'] ? 't' : 'f', $param_id, $rev, 't'];

        if ($new_rev_id || $no_orginal || ($editSet['size'] != $orginalSet['size']))
            $insert_vals[] = ['byteCount', $editSet['size'], $param_id, $rev, 't'];

        if ($new_rev_id || $no_orginal || ($editSet['action'] != $orginalSet['action']))
            $insert_vals[] = ['action', $editSet['action'], $param_id, $rev, 't'];

        if (($editSet['factor'] != '') && (($editSet['factor'] != 1) || ($has_orginal && ($editSet['factor'] != $orginalSet['factor']))))
            $insert_vals[] = ['factor', $editSet['factor'], $param_id, $rev, 't'];

        if (($editSet['offset'] != '') && (($editSet['offset'] != 0) || ($has_orginal && ($editSet['offset'] != $orginalSet['offset']))))
            $insert_vals[] = ['offset', $editSet['offset'], $param_id, $rev, 't'];


        if ($param_id > 2) {
            if (($editSet['unitstr_02'] != '') || ($has_orginal && ($editSet['unitstr_02'] != $orginalSet['unitStr_02'])))
                $insert_vals[] = ['unit', $editSet['unitstr_02'], $param_id, $rev, 't'];

            $has_value = ($editSet['valuetype'] != 'unused');
//             if ($has_value || ($has_orginal && ($editSet['valuetype'] != $orginalSet['valuetype'])))
            if ($new_rev_id || $no_orginal || ($has_orginal && ($editSet['valuetype'] != $orginalSet['valuetype'])))
                $insert_vals[] = ['valuetype', $editSet['valuetype'], $param_id, $rev, 'f'];

//             if ($has_value && ($no_orginal || ($editSet['value'] != $orginalSet['value'])))
            if ($new_rev_id || $has_value && ($no_orginal || ($has_orginal && ($editSet['value'] != $orginalSet['value'])))) {
                $insert_vals[] = ['value', $editSet['value'], $param_id, $rev, 'f'];
                $value_changed = true;
            }
        } else {
            if ($new_rev_id || ($editSet['value'] != $orginalSet['value']) || empty($orginalSet['version'])) {
                $value_changed = true;
                $hex_value = $editSet['value'];
                $this->Convert2Hex($hex_value, $paramset);

                $insert_vals[] = ['version', $hex_value, $param_id, $rev, 't'];
                $insert_vals[] = ['value', $editSet['value'], $param_id, $rev, 'f'];
            }
        }

        if (count($insert_vals)) {
            $result = $this->ecuPtr->newQuery('ecu_tag_configuration')->insert_multiple_new($insert_cols, $insert_vals);
            if (!$result) {
                $this->SetError(ERROR_CANNOT_SAVE_PARAMDEF, $this->ecuPtr->GeLastError());
                return false;
            }
        }

        $push_default = false;
        switch ($editSet['valuetype']) {
            case  'deflt':
                $push_default = true;
            case  'version':
            case  'const':
                $hex_value = $editSet['value'];
                $this->Convert2Hex($hex_value, $paramset);
                $this->PushConstantValue($rev, $param_id, $hex_value, $editSet['value'], $editSet['type_id'], $push_default);
                break;

            case 'dynmc':
                $this->PushConstantValue($rev, $param_id, $editSet['value'], $editSet['value'], $editSet['type_id'], false);
                break;
        }

        if (empty ($editSet['action']))
            $this->DisableUnusedValues($rev, $param_id);

        return true;
    }

    // ===============================================================================================
    function SaveNewRevision() {
        $new_rev = $old_rev = 0;
        $parametersAdded = [];
        $rev = &$this->S_EditSet['revision'];
        $use_uds = ($rev['protocol'] & 1) ? 't' : 'f';
        $use_xcp = ($rev['protocol'] & 2) ? 't' : 'f';
        $rev_ok = ($this->S_EditSet['err']['count'] == 0);
        $released = ($rev['released'] && $rev_ok) ? 't' : 'f';
        $rev_ok = ($rev_ok) ? 't' : 'f';

        if (($this->m_EditMode == self::EDIT_NewEcuVersion)) { //|| ($this->m_EditMode == self::EDIT_CopyEcuVersion)) { --- 2019
            $sw = $rev['sts_version'];
            $hw = $rev['sts_version'];
            if (strlen($hw) > 3)
                $hw = substr($hw, 0, 3);

            if (isset ($this->S_EditSet['edit'][1]['value']))
                $hw = $this->S_EditSet['edit'][1]['value'];

            if (isset ($this->S_EditSet['edit'][2]['value']))
                $sw = $this->S_EditSet['edit'][2]['value'];

            $ecu_id = $this->S_currentEcu;
            $stsVer = $rev['sts_version'];
            if (empty ($stsVer))
                $stsVer = strtoupper($this->allEcuNames[$ecu_id]) . date("-YMd h:m:s");

            $insert = [
                'ecu_id' => $ecu_id,
                'sts_version' => $stsVer,
                'sw' => $sw,
                'hw' => $hw,
                'request_id' => $rev['request_id'],
                'response_id' => $rev['response_id'],
                'href_windchill' => $rev['href_windchill'],
                'use_uds' => $use_uds,
                'use_xcp' => $use_xcp,
                'sw_profile_ok' => $rev_ok,
                'released' => $released,
                'info_text' => $rev['info_text'],
                'timestamp_last_change' => 'now()',
                'subversion_suffix' => $rev['subversion_suffix']
            ];

            $qry = $this->ecuPtr->newQuery('ecu_revisions');
            $res = $qry->insert($insert);

            if (!$res) {
                $error = $qry->GetLastError();
                $this->S_EditSet['err']['db']['insert'] = @pg_last_error();
                return false;
            }

            $sql = $this->ecuPtr->newQuery('ecu_revisions');
            $sql = $sql->where('ecu_id', '=', $ecu_id)
                ->where('sts_version', '=', $stsVer)
                ->where('request_id', '=', $rev['request_id'])
                ->where('response_id', '=', $rev['response_id'])
                ->where('subversion_suffix', '=', $rev['subversion_suffix'])
                ->orderBy('ecu_revision_id', 'desc');
            $res = $sql->get('*');
            if (!$res) {
                $this->S_EditSet['err']['db']['insert'] = 'cannot refetch';
                return false;
            }

            $this->S_ecuVersion = $res[0];
            $new_rev = $this->S_ecuVersion['ecu_revision_id'];
            $this->S_Revisions[$rev] = $this->S_ecuVersion;
        } else {
            $old_rev = $this->S_ecuVersion['ecu_revision_id'];
            $update_cols = [];
            $update_vals = [];

            $update_cols[] = 'subversion_suffix';
            $update_vals[] = $rev['subversion_suffix'];

            $update_cols[] = 'sts_version';
            $update_vals[] = $rev['sts_version'];

            $update_cols[] = 'href_windchill';
            $update_vals[] = $rev['href_windchill'];

            $update_cols[] = 'request_id';
            $update_vals[] = $rev['request_id'];

            $update_cols[] = 'response_id';
            $update_vals[] = $rev['response_id'];

            $update_cols[] = 'use_uds';
            $update_vals[] = $use_uds;

            $update_cols[] = 'use_xcp';
            $update_vals[] = $use_xcp;

            $update_cols[] = 'sw_profile_ok';
            $update_vals[] = $rev_ok;

            $update_cols[] = 'released';
            $update_vals[] = $released;

            $update_cols[] = 'info_text';
            $update_vals[] = $rev['info_text'];


            if (count($update_cols)) {
                $update_cols[] = 'timestamp_last_change';
                $update_vals[] = 'now()';
                $result = $this->ecuPtr->newQuery('ecu_revisions')
                    ->where('ecu_revision_id', '=', $old_rev)
                    ->update($update_cols, $update_vals);
            }
        }


        switch ($this->m_OdxVerShow) {
            case 1:
                foreach ($this->S_EditSet['edit'] as $param_id => &$editSet) {
                    $orginal = null;
                    if ($editSet['use_in_odx01']) {
                        if (isset ($this->S_EditSet['existing'][$param_id]))
                            $orginal = &$this->S_EditSet['existing'][$param_id];

                        $this->SaveParamDefinitionSet_odx01($old_rev, $new_rev, $param_id, $editSet, $orginal);
                    }
                }
                break;

            case 2:
                foreach ($this->S_EditSet['edit'] as $param_id => &$editSet) {
                    $orginal = null;
                    if (isset ($this->S_EditSet['existing'][$param_id]))
                        $orginal = &$this->S_EditSet['existing'][$param_id];

                    if ($editSet['deleted'] != $orginal['deleted']) {
                        if ($editSet['deleted'] && !isset($this->S_dbParamDef[$param_id]))
                            continue;
                        $this->InsertOrUpdateSpecialTag($old_rev + $new_rev, $param_id, 'deleted', $editSet['deleted']);
                        continue;
                    }

                    if (empty ($editSet['deleted'])) {
                        if (substr($param_id, 0, 5) == '99add') {
                            $parametersAdded[] = $editSet;
                        }

                        $this->SaveParamDefinitionSet($old_rev, $new_rev, $param_id, $editSet, $orginal);
                    }
                }
                break;
        }

        $nP = count($parametersAdded);
        if ($nP) {
            $nPX = (($nP == 1) ? 'neuer' : 'neue');
            $ecu = $this->S_currentEcu;
            $rev = $old_rev | $new_rev;

            $ecuName = $this->allEcuNames[$ecu];

            $empfaenger = "lothar.juergens@streetcooter.eu";
            $mailSubject = "$nP $nPX MOPRA Parameter hinzugefügt für $ecuName";
            $link = $_SERVER['PHP_SELF'] . "?action={$this->action}&tab=2&selectedEcu=$ecu&ecu[$ecu]=$rev";
            $mailText = <<<HEREDOC
<html>
  <head>
    <title>Neue ECU Parameter</title>
  </head>
  <body>
    <p>Folgende $nP Parameter sind dem Geträt $ecuName in der Revision {$rev['sts_version']} hinzugefügt worden:</p>
    <table style="border:1 solid #888;">
      <tr style="background-color: #ddd;"><th>Parameter</th>Uds-ID</th><th>Typ</th><th>n-Bytes</th><th>Aktion</th></tr>
HEREDOC;

            foreach ($parametersAdded as $set) {
                $mailText .= "<tr><td>{$set['id']}</td><td>{$set['udsId']}</td><td>{$set['type']}</td><td>{$set['size']}</td><td>{$set['action']}</td></tr>";
            }

            $mailText .= <<<HEREDOC
    </table>
    <p>Link zu Parameterkonfiguration <a href="https://$link">$ecuName Revision {$rev['sts_version']}</a></p>
  </body>
</html>
HEREDOC;

            // $mailer = new Mailer ($empfaenger, "", $mailSubject, $mailText);

            // $result = $this->SendHtmlMail ($empfaenger, $mailSubject, $mailText, "lothar.juergens@streetscooter.eu");

        }

    }
    /*
  // ===============================================================================================
  function DeleteCurrentRevision()
  {
    if (!$this->S_ecuVersion)
      return;

    $rev_id = $this->S_ecuVersion['ecu_revision_id'];
    if ($rev_id <= 0)
      return;

    $this->GetEnggPrivileges(self::PRIV_ECU_PROFILE, $this->S_currentEcu);
    $num_used = $this->CntVariantsUsingRevision($rev_id);

    if ($this->m_Permission[self::PRIV_ECU_PROFILE]['current'] != 'owner')
      return $this->SetError(STS_ERROR_NO_PRIVILEGS, 'zu löschen');

    if ($num_used > 0)
      return $this->SetError(ERROR_CANNOT_DELETE_USED_REV);

    $qry = $this->ecuPtr->newQuery('ecu_revisions')->where('ecu_revision_id', '=', $rev_id);
    if (!$qry->update(['ecu_id'], [0]))
      return $this->SetError(STS_ERROR_DB_DELETE, $qry->GetLastError());

    $this->S_ecuVersion = null;
    $this->DB_RevisionMap = null;
    $this->S_EditSet = null;
  }*/

    // ===============================================================================================
    function Adopt_ECUVersionEditSet_Odx01($autoCorrect) {

        if ($autoCorrect)
            $this->S_EditSet['revision']['sts_version'] = trim(strtoupper($_POST['ecuVersion']['sts_version']));
        else
            $this->S_EditSet['revision']['sts_version'] = trim($_POST['ecuVersion']['sts_version']);

        foreach ($this->S_EditSet['edit'] as $param_id => &$editSet) {
            $editSet['unit_01'] = $unit = safe_val($_POST['ecu'][$param_id], 'unit', 0);
            $editSet['unitstr_01'] = ($unit) ? $this->DB_allUnits[$unit] : "";
            $editSet['unitstr_02'] = $editSet['unitstr_01'];

            if ($param_id > 2)
                $editSet['valuetype'] = $_POST['ecu'][$param_id]['valuetype'];
            else
                $editSet['valuetype'] = 'version';

            switch ($editSet['valuetype']) {
                case 'unused':
                    //2019--------
                    $editSet['value'] = '';
                    //-------------
                    break;

                case 'version':
                case 'deflt':
                case 'const':
                    $editSet['value'] = trim($_POST['ecu'][$param_id]['value']);
                    break;

                case 'macro':
                    $editSet['value'] = trim($_POST['ecu'][$param_id]['macroname']);
                    break;
            }

        }
    }

    // ===============================================================================================
    function Adopt_ECUVersionEditSet($autoCorrect = true) {
        $this->S_EditSet['err'] = ['revision' => [], 'existing' => [], 'edit' => [], 'sort' => [], 'count_new' => 0];

        $this->S_EditSet['revision']['href_windchill'] = filter_var(trim($_POST['ecuVersion']['href_windchill']), FILTER_SANITIZE_URL);
        $this->S_EditSet['revision']['info_text'] = addslashes(trim($_POST['ecuVersion']['info']));
        $this->S_EditSet['revision']['protocol'] = $_POST['ecuVersion']['protocol'];
        $this->S_EditSet['revision']['released'] = $_POST['ecuVersion']['released'];

        if ($this->m_OdxVerShow == 1)
            return $this->Adopt_ECUVersionEditSet_Odx01($autoCorrect);

        if ($autoCorrect) {
            $this->S_EditSet['revision']['sts_version'] = trim(strtoupper($_POST['ecuVersion']['sts_version']));
            $this->S_EditSet['revision']['request_id'] = str_pad(trim(strtolower($_POST['ecuVersion']['request'])), 8, '0', STR_PAD_LEFT);
            $this->S_EditSet['revision']['response_id'] = str_pad(trim(strtolower($_POST['ecuVersion']['response'])), 8, '0', STR_PAD_LEFT);
            $this->S_EditSet['revision']['subversion_suffix'] = trim(strtoupper($_POST['ecuVersion']['subversion_suffix']));
        } else {
            $this->S_EditSet['revision']['sts_version'] = trim($_POST['ecuVersion']['sts_version']);
            $this->S_EditSet['revision']['request_id'] = trim($_POST['ecuVersion']['request']);
            $this->S_EditSet['revision']['response_id'] = trim($_POST['ecuVersion']['response']);
            $this->S_EditSet['revision']['subversion_suffix'] = trim($_POST['ecuVersion']['subversion_suffix']);

        }

        foreach ($this->S_EditSet['edit'] as $param_id => &$editSet) {
            if ($param_id == 4) {
                $editSet['use_in_odx02'] = isset ($_POST['ecu'][4]['use']);
                $_POST['ecu'][$param_id]['deleted'] = !$editSet['use_in_odx02'];
                $editSet['order'] = 0;
            } else {
                $editSet['order'] = $_POST['ecu'][$param_id]['order'];
            }

            if (isset($_POST['ecu'][$param_id]) || isset($editSet['added'])) {

                $deleted = safe_val($_POST['ecu'][$param_id], 'deleted', 0);
                $editSet['deleted'] = $deleted ? 'odx.sts.02' : "";

                $editSet['id'] = trim($_POST['ecu'][$param_id]['id']);
                $editSet['udsId'] = trim($_POST['ecu'][$param_id]['udsId']);
                $editSet['type'] = $_POST['ecu'][$param_id]['type'];
                $editSet['size'] = $_POST['ecu'][$param_id]['size'];
                $editSet['factor'] = $_POST['ecu'][$param_id]['factor'];
                $editSet['offset'] = $_POST['ecu'][$param_id]['offset'];
                $editSet['unit_02'] = $_POST['ecu'][$param_id]['unit'];
                $editSet['protocol'] = $_POST['ecu'][$param_id]['protocol'];

                if ($param_id > 2)
                    $editSet['valuetype'] = $_POST['ecu'][$param_id]['valuetype'];
                else
                    $editSet['valuetype'] = 'version';

                switch ($editSet['valuetype']) {
                    case 'unused':
                        //2019--------------------------------
                        $editSet['value'] = '';
                        //-----------------------------------
                        break;

                    case 'version':
                    case 'deflt':
                    case 'const':
                        $editSet['value'] = trim($_POST['ecu'][$param_id]['value']);

                        if ($editSet['type'] == 'string') {
                            $editSet['value'] = bin2hex($editSet['value']);
                        } elseif ($editSet['type'] == 'int') {
                            $editSet['value'] = dechex($editSet['value']);
                        }
                        break;

                    case 'macro':
                        $editSet['value'] = trim($_POST['ecu'][$param_id]['macroname']);
                        break;

                    case 'dynmc':
                        $editSet['value'] = trim($_POST['ecu'][$param_id]['dyn_token']);
                        break;

                }

                $actions = $_POST['ecu'][$param_id]['action'];
                $editSet['action'] = is_array($actions) ? implode('', array_keys($actions)) : "";

                $unit = isset ($editSet['unit_02']) ? $editSet['unit_02'] : 0;
                $editSet['unitstr_02'] = ($unit) ? $this->DB_allUnits[$unit] : "";

                if ($autoCorrect) {
                    if ($editSet['id'] == "")
                        $editSet['id'] = $editSet['odx_name'];
                    $editSet['id'] = strtoupper($editSet['id']);
                }
            }
        }
    }

    // ===============================================================================================
    function CreateEditSet_ECUVersionen() {
        $this->S_EditSet = $set = ['revision' => [], 'existing' => [], 'edit' => [], 'sort' => [], 'count_new' => 0];
        $this->S_EditSet['err'] = $set;

        /*if ($this->m_EditMode == self::EDIT_NewEcuVersion)
      return;*/

        $this->S_dbParamDef = $this->QueryParameters($this->S_currentEcu, $this->S_ecuVersion['ecu_revision_id']);

        $this->S_EditSet ['revision']['sts_version'] = strtoupper($this->DisplayedRevisionName($this->S_ecuVersion, false)); //$this->S_ecuVersion['sts_version'];
        $this->S_EditSet ['revision']['href_windchill'] = $this->S_ecuVersion['href_windchill'];
        $this->S_EditSet ['revision']['request_id'] = strtolower($this->S_ecuVersion['request_id']);
        $this->S_EditSet ['revision']['response_id'] = strtolower($this->S_ecuVersion['response_id']);
        $this->S_EditSet ['revision']['profile_ok'] = toBool($this->S_ecuVersion['sw_profile_ok']);
        $this->S_EditSet ['revision']['released'] = toBool($this->S_ecuVersion['released']);
        $this->S_EditSet ['revision']['info_text'] = $this->S_ecuVersion['info_text'];
        $this->S_EditSet ['revision']['subversion_suffix'] = strtoupper($this->DisplayedSuffixName($this->S_ecuVersion, false));

        $use_uds = toBool($this->S_ecuVersion['use_uds']);
        $use_xcp = toBool($this->S_ecuVersion['use_xcp']);

        $this->S_EditSet ['revision']['protocol'] = ($use_uds ? 1 : 0) + ($use_xcp ? 2 : 0);
        if (empty ($this->S_EditSet ['revision']['protocol']))
            return;
        foreach ($this->S_dbParamDef as $param_id => &$set) {
            $this->S_EditSet['edit'][$param_id] = [];
            $this->S_EditSet['existing'][$param_id] = [];

            $editSet = &$this->S_EditSet['edit'][$param_id];
            $existSet = &$this->S_EditSet['existing'][$param_id];

            $old_type = $set['type_id'];
            $editSet['param_id'] = $param_id;
            $editSet['odx_name'] = $set['odx_name'];
            $editSet['unit_01'] = safe_val($set, 'unit_id', 0);
            $editSet['order'] = $set['order'];
//----------2019
            //$editSet['protocol'] = $set['protocol'];
//-----------
            $unit = isset ($editSet['unit_01']) ? $editSet['unit_01'] : 0;
            $editSet['unitstr_01'] = ($unit) ? $this->DB_allUnits[$unit] : "";
            $editSet['use_in_odx01'] = toBool($set['use_in_odx01']);
            $editSet['factor'] = 1;
            $editSet['value'] = "";
            $editSet['valuetype'] = "deflt";
            $editSet['action'] = 'rw';
            $editSet['id'] = "";
            $editSet['deleted'] = false;
            $editSet['type_id'] = $old_type;

            switch ($old_type) {
                case 1:
                case 'int':
                    $editSet['old_type'] = 'int';
                    //$editSet['type']    = 'unsigned';
                    //$editSet['size']    = 2;
                    break;

                case 2:
                case 'double':
                    $editSet['old_type'] = 'double';
                    //$editSet['type']    = 'unsigned';
                    //$editSet['factor']  = 10;
                    //$editSet['size']    = 4;
                    break;

                case 3:
                case 'string':
                    $editSet['old_type'] = 'string';
                    //$editSet['type']    = 'ascii';
                    //$editSet['size']    = 16;
                    break;

                case 4:
                case 'bool':
                    $editSet['old_type'] = 'bool';
                    //$editSet['type']    = 'unsigned';
                    //$editSet['size']    = 1;
                    break;
            }

            $existSet['unit_01'] = $editSet['unit_01'];

            if (isset ($set['tags'])) {
                $tags = &$set['tags'];
                $editSet['id'] = safe_tag_value($tags, 'id', "");
                $editSet['deleted'] = safe_tag_value($tags, 'deleted', "");
                $editSet['udsId'] = safe_tag_value($tags, 'udsId', "");
                $editSet['startBit'] = safe_tag_value($tags, 'startBit', 0);
                $editSet['stopBit'] = safe_tag_value($tags, 'stopBit', 0);
                $editSet['type'] = safe_tag_value($tags, 'type', ''); //$editSet['type']);
                $editSet['size'] = safe_tag_value($tags, 'byteCount', ''); //$editSet['size']);
                $editSet['action'] = safe_tag_value($tags, 'action', 'rw');
                $editSet['unitstr_02'] = safe_tag_value($tags, 'unit', $editSet['unitstr_01']);
                $editSet['value'] = safe_tag_value($tags, 'value', '');
                $editSet['valuetype'] = safe_tag_value($tags, 'valuetype', 'unused');
                $editSet['order'] = safe_tag_value($tags, 'order', $editSet['order']);
                $editSet['bigEndian'] = safe_tag_value($tags, 'bigEndian', $this->m_EcuBigEndian);
                //2019--------------------------
                $editSet['protocol'] = safe_tag_value($tags, 'protocol', NULL);
                $editSet['unitstr_01'] = safe_tag_value($tags, 'unit', '');
                //------------------------------
                $editSet['unit_02'] = empty ($editSet['unitstr_02']) ? 0 :
                    array_search($editSet['unitstr_02'], $this->DB_allUnits);

                $existSet['id'] = safe_tag_value($tags, 'id', null);
                $existSet['deleted'] = safe_tag_value($tags, 'deleted', null);
                $existSet['udsId'] = safe_tag_value($tags, 'udsId', null);
                $existSet['startBit'] = safe_tag_value($tags, 'startBit', null);
                $existSet['stopBit'] = safe_tag_value($tags, 'stopBit', null);
                $existSet['type'] = safe_tag_value($tags, 'type', null);
                $existSet['offset'] = safe_tag_value($tags, 'offset', null);
                $existSet['action'] = safe_tag_value($tags, 'action', null);
                $existSet['unitstr_02'] = safe_tag_value($tags, 'unit', null);
                $existSet['value'] = safe_tag_value($tags, 'value', null);
                $existSet['valuetype'] = safe_tag_value($tags, 'valuetype', null);
                $existSet['order'] = safe_tag_value($tags, 'order', null);
                $existSet['bigEndian'] = safe_tag_value($tags, 'bigEndian', null);

                striphexprefix($editSet['udsId']);
                striphexprefix($existSet['udsId']);

                if ($param_id <= 2) {
                    $editSet ['version'] = safe_tag_value($tags, 'version', '');
                    $existSet['version'] = safe_tag_value($tags, 'version', null);

                    if (empty($editSet ['version'])) {
                        if (!empty($editSet['value'])) {
                            $ver = $editSet['value'];
                            $this->Convert2Hex($ver, $editSet);
                            /*
                            switch ($existSet['valuetype'])
                            {
                                case 'ascii':
                                    $ver = '0x' . bin2hex ($editSet['value']);
                                    break;
                            }
                            */
                            $existSet['value'] = $editSet['value'];
                            $editSet ['version'] = $ver;
                            $existSet['version'] = $ver;
                        }

                    } else {
                        $ver = $editSet ['version'];
                        if (substr($ver, 0, 2) == '0x') {
                            switch ($editSet['type']) {
                                case 'signed':
                                    $ver = $this->HexToInt($ver, $editSet['size'], true);
                                    break;
                                case 'unsgned':
                                    $ver = $this->HexToInt($ver, $editSet['size'], false);
                                    break;
                                case 'ascii':
                                    $ver = $this->AsciiHexToUtf8(substr($ver, 2));
                                    break;
                                case 'blob':
                                    break;
                            }
                        }
                        $editSet['value'] = $ver;
                        $existSet['value'] = false;
                    }
                } else if ($param_id == 4) {
                    $editSet['id'] = 'SERIAL_NUMBER';
                    if (tag_exists($tags, 'id')) {
                        $deleted = safe_tag_value($tags, 'deleted', "");
                        if (stripos($deleted, 'odx.sts.02') === false)
                            $editSet['use_in_odx02'] = true;
                    }
                } else {
                    $editSet['factor'] = safe_tag_value($tags, 'factor', $editSet['factor']);
                    $editSet['offset'] = safe_tag_value($tags, 'offset', 0);
                    $existSet['size'] = safe_tag_value($tags, 'byteCount', false);
                    $existSet['factor'] = safe_tag_value($tags, 'factor', null);
                }
            } else {
                $existSet['id'] = false;
                $existSet['deleted'] = false;
                $existSet['udsId'] = false;
                $existSet['type'] = false;
                $existSet['size'] = false;
                $existSet['factor'] = false;
                $existSet['offset'] = false;
                $existSet['action'] = false;
                $existSet['unitstr_02'] = false;
                $existSet['value'] = false;
                $existSet['valuetype'] = false;
                $existSet['version'] = false;
                $existSet['order'] = false;
            }
        }

        if (!isset ($this->S_EditSet['edit'][4])) {
            $this->S_EditSet['edit'][4] = [
                'id' => 'SERIAL_NUMBER',
                'use_in_odx01' => false,
                'use_in_odx02' => false,
                'deleted' => 'odx.sts.01,odx.sts.02',
                'action' => 'r',
                'startBit' => 0,
                'stopBit' => 0,
            ];
        }

        if (!$this->Check_ECUVersionEditSet()) {
//             $this->SetError(ERROR_INVALID_ODX2_CONFIG, "", ERRORLEVEL_MESSAGE);
            $this->configError = ERROR_INVALID_ODX2_CONFIG;
        }
    }

    // ===============================================================================================
    function DistributeParameters() {
        $scrRev = $this->S_variantRev;
        $dstRev = $_REQUEST['ecuVersion']['copyTo'];
        $params = array_keys($_REQUEST['ecuVersion']['selparam']);
        $params_csv = implode(',', $params);
        $select = <<<HEREDOC
select
    distinct on (tag, ecu_parameter_set_id, ecu_revision_id)
    now(), tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag
from ecu_tag_configuration
join ecu_revisions using (ecu_revision_id)
where   ecu_id=6
and ecu_revision_id = $scrRev
and ecu_parameter_set_id in ($params_csv)
order by ecu_revision_id, ecu_parameter_set_id, tag, timestamp
;
HEREDOC;

        foreach ($dstRev as $rev_id) {
            $sql = <<<HEREDOC
insert into ecu_tag_configuration ("timestamp", tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag) $select
HEREDOC;

            $qry = $this->ecuPtr->newQuery();
            $res = $qry->query($sql);
        }

    }

    // ===============================================================================================
    function SortEditSet_ECUVersionen($odxVer) {
        $tmp = [];
        $tail = [];
        $num = 0;
        foreach ($this->S_EditSet['edit'] as $param_id => &$set) {
            if ($param_id > 4) {
                $deleted = (isset($set['deleted'])) ? $set['deleted'] : "";
                if ($deleted == 'all' || (strpos($deleted, "odx.sts.0$odxVer") !== false))
                    continue;

                $ord = $set['order'];
                if ($ord < 0) {
                    $tail[] = $param_id;
                    continue;
                }

                if (!isset ($tmp[$ord]))
                    $tmp[$ord] = [];
                $tmp[$ord][] = $param_id;
            }
        }

        ksort($tmp);

        $this->S_EditSet['sort'] = [];
        foreach ($tmp as $list)
            foreach ($list as $param_id) {
                $this->S_EditSet['sort'][] = $param_id;
                $num++;
            }

        foreach ($tail as $param_id) {
            $this->S_EditSet['sort'][] = $param_id;
            $num++;
        }

        $this->S_EditSet['num'] = $num;
        $this->S_EditSet['last_id'] = $param_id;
    }

    // ===============================================================================================
    function WriteHtml_TableEcuParametersEditSet_odx01($col_id, &$editSet, $editMode) {
        $hinweis = '';
        $td_unit = "";
        $size_of_Wert = 24;
        if ($editSet['order'] < 0)
            return;

        if (!empty($editSet['id'])) {
            if (!isset($editSet['deleted']) || stripos($editSet['deleted'], 'odx.sts.02') === false) {
                if ($editSet['id'] != $editSet['odx_name']) {
                    $box = '<div class="yellowNote">JA (&ne;)</div>';
                    $eq = 'ungleich';
                    $bez = "<br>Bezeichnung unter odx.sts.02 ist: {$editSet['id']}";
                } else {
                    $box = '<div class="greenNote">JA (=)</div>';
                    $eq = 'gleich';
                    $bez = "";
                }
                $hinweis = sprintf('<div class="ttip">%s<span class="ttiptext">Dieser Parameter wird auch in odx.sts.02 mit %s Bezeichnung verwendet. %s</span></div>', $box, $eq, $bez);
            }
        }

        if (empty($hinweis))
            $hinweis = '<div class="ttip"><div class="greyNote">NEIN</div><span class="ttiptext">Dieser Parameter wird nicht in odx.sts.02 verwendet!</span></div>';

        if (($col_id > 2) && (($editSet['old_type'] == 'int') || ($editSet['old_type'] == 'double'))) {
            $td_unit = $editSet['unitstr_01'];
//      if ($editMode) {
//        $td_unit = "<select name=\"ecu[$col_id][unit]\">" . $this->GetHtml_SelectOptions($this->DB_allUnits, $editSet['unit_01']) . '</select>';
//      }
        }

        if ($editMode) {
            $protocol = "
                    <select class='protocol' name='ecu[" . $col_id . "][protocol]'>
                        <option value='1'>UDS</option>
                        <option value='2'>XCP</option>
                    </select>";
        } else {
            $protocol = $editSet['protocol'] != null ? $editSet['protocol'] == 1 ? "UDS" : "XCP" : "NULL";
        }


        /*
        $unit       = $editSet['unit_01'];
        $td_unit    = safe_val ($this->DB_allUnits, $unit, "");
        */
        $pset_id = $editSet['param_id'];
        $value = $editSet['value'];
        $realValue = ($editSet['type'] == 'string') ? hex2bin($value) : hexdec($value);
        $allValuetypes = self::ODX02_VALUETYPES;
        $valuetype = $editSet['valuetype'];

        switch ($pset_id) {
            case 1:
            case 2:
                $action = ($this->S_currentEcu == 6) ? 'r' : 'rc';
                $str_valuetype = "Versions-ID";

                if ($editMode)
                    $realValue = <<<HEREDOC

            <input  class="value" name="ecu[$col_id][value]" id="id_value_edit_$col_id" type="text" size="{$size_of_Wert}" value="$realValue">
HEREDOC;

                break;

            case 4:
                $action = 'r';
                $str_valuetype = '<span class="inactiveLink">(keine Vorgabe)</span>';
                break;

            default:
                $action = 'rwc';

                if ($editMode) {
                    $allValuetypeOptions = $this->GetHtml_SelectOptions($allValuetypes, $valuetype);

                    $display_edit = ($valuetype == 'deflt' || $valuetype == 'const') ? "inline" : "none";
                    $display_select = ($valuetype == 'macro') ? "inline" : "none";
                    $optionsAllMacros = $this->GetHtml_SelectOptions(reduce_assoc($this->DB_Variables, 'odx_name'), $editSet['value']);
                    $options = str_replace('<option value="part">Bauteil (noch keine Funktion)</option>', '', $allValuetypeOptions);
                    $options = str_replace('<option value="dynmc">Dynamisch</option>', '', $options);

                    $str_valuetype =

                        '<select name="ecu[' . $col_id . '][valuetype]" OnChange="OnValuetypeChanged(this, \'' . $col_id . '\')">' . $options . '</select>';


                    $realValue = <<<HEREDOC

            <input  class="value" name="ecu[$col_id][value]" id="id_value_edit_$col_id" type="text" size="{$size_of_Wert}" value="$realValue" style="display:$display_edit;">
            <select class="macroname" name="ecu[$col_id][macroname]" id="id_macro_select_$col_id" style="display:$display_select;">$optionsAllMacros</select>


HEREDOC;
                } else {
                    if ($valuetype == 'macro')
                        $value = $this->DB_Variables[$value]['odx_name'];

                    if ($valuetype && ($valuetype != 'unused'))
                        $str_valuetype = $allValuetypes[$valuetype];
                    else
                        $str_valuetype = '<span class="inactiveLink">(keine Vorgabe)</span>';
                }
                break;

        }

        echo
        '<tr>
          <td>';
        if ($col_id > 3) {
            $this->column_id++;
            echo '<div class="ttip">' . $this->column_id . '<span class="ttiptext">ID: ' . $pset_id . ':' . $col_id . '</span></div>';
        } else {
            echo '<div class="ttip"><span class="ttiptext">ID: ' . $pset_id . ':' . $col_id . '</span></div>';
        }
        echo <<<HEREDOC
        </td>
          <td>{$editSet['odx_name']}</td>
          <td>{$hinweis}</td>
          <td class="protocol">{$protocol}</td>
          <td>{$action}</td>
          <td>{$editSet['old_type']}</td>
          <td>{$td_unit}</td>
          <td>{$realValue}</td>
          <td>{$str_valuetype}</td>
        </tr>
HEREDOC;

    }

    // ===============================================================================================
    function WriteHtml_TableEcuParametersEditSet_odx02($col_id, &$editSet, $editMode) {
        if (isset($editSet['deleted']) && stripos($editSet['deleted'], 'dx.sts.02') && ($col_id != 4))
            return;

        $allValuetypes = self::ODX02_VALUETYPES;
        $allTokens = [];
        $valuetype = $editSet['valuetype'];

        $size_of_Bezeichnung = 32;
        $size_of_Wert = 24;
        $image_trash = '"/images/symbols/icon-trash.png"';
        $image_deleted = '"/images/symbols/icon-del.png"';

        $checked_r = strstr($editSet['action'], 'r') ? " checked" : "";
        $checked_w = strstr($editSet['action'], 'w') ? " checked" : "";
        $checked_c = strstr($editSet['action'], 'c') ? " checked" : "";
        $disable_linking = $checked_r != "" && $checked_w == "" && $checked_c == "" ? "disabled" : "";
        $sel_deflt = "";
        $sel_const = "";
        $sel_list = "";
        $hexinfo = "";

        $errClass = ['id' => "", 'udsId' => "", 'action' => "", 'type' => "", 'size' => ""];

        foreach (self::ODX02_DYNAMIC_KEYS as $tok)
            $allTokens["%$tok%"] = $tok;

        if ($editSet['use_in_odx01'] && ($editSet['order'] > 0)) {
            if ($editSet['id'] != $editSet['odx_name']) {
                $box = '<div class="yellowNote">JA (&ne;)</div>';
                $eq = 'ungleich';
                $bez = "<br>Bezeichnung unter odx.sts.01 ist: {$editSet['odx_name']}";
            } else {
                $box = '<div class="greenNote">JA (=)</div>';
                $eq = 'gleich';
                $bez = "";
            }
            $hinweis = sprintf('<div class="ttip">%s<span class="ttiptext">Dieser Parameter wird auch in odx.sts.01 mit %s Bezeichnung verwendet. %s</span></div>', $box, $eq, $bez);
        } else {
            $hinweis = '<div class="ttip"><div class="greyNote">NEIN</div><span class="ttiptext">Dieser Parameter wird nicht in odx.sts.01 verwendet!</span></div>';
        }


        if ($col_id > 4) {
            $this->column_id++;
            if ($this->m_EditMode == self::EDIT_DistributeSWParameters) {
                $colinfo = "<input type=\"checkbox\" name=\"ecuVersion[selparam][$col_id]\" class=\"param_selector\" checked>";
            } else {
                $colinfo = sprintf('<div class="ttip">%d<span class="ttiptext">ID=%d</span></div>', $this->column_id, $col_id);
            }

            if ($valuetype && ($valuetype != 'unused'))
                $str_valuetype = $allValuetypes[$valuetype];
            else
                $str_valuetype = '<span class="inactiveLink">(keine Vorgabe)</span>';
        } else {
            $colinfo = "";
            switch ($col_id) {
                case 1:
                    $str_valuetype = 'HW Identifikation';
                    $valuetype = 'version';
                    break;
                case 2:
                    $str_valuetype = 'SW Identifikation';
                    $valuetype = 'version';
                    break;
                case 4:
                    $valuetype = 'unused';
                    break;

            }
        }


        $td_edit_id = $editSet['id'];
        if ($editMode) {
            $type_selected = ['ascii' => "", 'string' => "", 'signed' => "", 'unsigned' => "", 'blob' => "", 'int' => "", 'double' => "", 'bool' => ""];
            $protocol_selected = ['UDP' => '', 'XCP' => ''];
            $type_selected[$editSet['type']] = " selected";
            $protocol_selected[$editSet['protocol']] = "selected";
            $unit_options = $this->GetHtml_SelectOptions($this->DB_allUnits, $editSet['unit_02'], 18);

            if ($col_id > 4) {
                $deletefunc = $this->GetHtml_DeleteFunction('ecu', $col_id);
                $movefunc = "<input type=\"hidden\" name=\"ecu[$col_id][order]\" id=\"id_order_{$this->column_id}\" value=\"{$this->column_id}\">";

                $default = empty ($editSet['id']) ? $editSet['odx_name'] : $editSet['id'];
                $td_edit_id = sprintf('<input  name="ecu[%s][id]" type="search" list="allparamNames" size="%d" value="%s" placeholder="%s" onClick="if (this.value==%s) {this.value=this.placeholder;this.select();}">',
                    $col_id, $size_of_Bezeichnung, $editSet['id'], $default, "''");


                if ($this->column_id > 1)
                    $movefunc .= '<a class="move_up" href="javascript:move_up(' . $this->column_id . ')"><img src="/images/symbols/move_up.png"></a><br>';


                if ($this->column_id < $this->S_EditSet['num'])
                    $movefunc .= '<a class="move_down" href="javascript:move_down(' . $this->column_id . ')"><img src="/images/symbols/move_down.png"></a>';

                $this->tmpOrder++;
                $allValuetypeOptions = $this->GetHtml_SelectOptions($allValuetypes, $valuetype);

                $str_valuetype = "
                <select name=\"ecu[$col_id][valuetype]\" OnChange=\"OnValuetypeChanged(this, '$col_id')\" $disable_linking>{$allValuetypeOptions}</select>\n";

                if ($this->m_EditMode == self::EDIT_DistributeSWParameters) {
                    $deletefunc = "";
                    $movefunc = "<input type=\"hidden\" name=\"ecu[$col_id][order]\" id=\"id_order_{$this->column_id}\" value=\"{$this->column_id}\">";
                }
            } else {
                $deletefunc = "";
                $movefunc = "";

                $selected = ["", ""];
                $i = stripos($editSet['id'], 'VERSION_SUPPLIER') ? 1 : 0;
                $selected[$i] = " selected";

                switch ($col_id) {
                    case 1:
                        $td_edit_id = '<select class="ecuSelectVersion" name="ecu[1][id]"><option' . $selected[0] . '>HW_VERSION_STS</option><option' . $selected[1] . '>HW_VERSION_SUPPLIER</option></select>';
                        break;

                    case 2:
                        $td_edit_id = '<select class="ecuSelectVersion" name="ecu[2][id]"><option' . $selected[0] . '>SW_VERSION_STS</option><option' . $selected[1] . '>SW_VERSION_SUPPLIER</option></select>';
                        break;

                    case 4:
                        $checked = $editSet['use_in_odx02'] ? " checked" : "";
                        $td_edit_id = '<input type="checkbox" name="ecu[4][use]"' . $checked . '> SERIAL_NUMBER';
                        break;
                }
            }

            $input_type = 'number';

            echo <<<HEREDOC
            <tr id="idcol{$this->column_id}">
              <td><div class="colinfo">$colinfo {$movefunc}</div></td>
              <td>{$deletefunc}</td>
              <td>{$td_edit_id}</td>
              <td>{$hinweis}</td>
              <td class="protocol">
                    <select name="ecu[$col_id][protocol]">
                        <option value="1" {$protocol_selected[1]}>UDS</option>
                        <option value="2" {$protocol_selected[2]}>XCP</option>
                    </select>
              </td>
              <td class="uds">0x <input  name="ecu[$col_id][udsId]" type="text" size="8" value="{$editSet['udsId']}"></td>
              <td>
HEREDOC;
            if ($col_id != 4)
                echo <<<HEREDOC
                <input class='rights' type="checkbox" name="ecu[$col_id][action][r]"$checked_r>
                <input class='rights' type="checkbox" name="ecu[$col_id][action][w]"$checked_w>
                <input class='rights' type="checkbox" name="ecu[$col_id][action][c]"$checked_c>
HEREDOC;
            else
                echo <<<HEREDOC
                <input class='rights' type="checkbox" name="ecu[$col_id][action][r]"$checked_r readonly>
HEREDOC;

            echo <<<HEREDOC
              </td>
              <td>
                <select name="ecu[$col_id][type]">
                  <option value="ascii"{$type_selected['ascii']}>ascii</option>
                  <option value="string"{$type_selected['string']}>string</option>
                  <option value="signed"{$type_selected['signed']}>signed</option>
                  <option value="unsigned"{$type_selected['unsigned']}>unsigned</option>
                  <option value="blob"{$type_selected['blob']}>blob</option>
                  <option value="int"{$type_selected['int']}>int</option>
                  <option value="double"{$type_selected['double']}>double</option>
                  <option value="bool"{$type_selected['bool']}>bool</option>
                </select>
              </td>
              <td><input  name="ecu[$col_id][size]" type="$input_type" class="number" min="1" max="1000" value="{$editSet['size']}"></td>
HEREDOC;

            if ($col_id > 4)
                echo <<<HEREDOC
              <td><input  name="ecu[$col_id][factor]" type="$input_type" class="number" value="{$editSet['factor']}" step="0.001"></td>
              <td><input  name="ecu[$col_id][offset]" type="$input_type" class="number" value="{$editSet['offset']}" step="0.001"></td>
              <td><select name="ecu[$col_id][unit]" class="unit">{$unit_options}</select></td>
HEREDOC;

            else
                echo <<<HEREDOC
              <td></td>
              <td></td>
              <td></td>
HEREDOC;


            //if ($editSet['value'])
            //$this->Variables
            $display_edit = (($valuetype == 'deflt') || ($valuetype == 'const') || ($valuetype == 'version')) ? "inline" : "none";
            $display_select = ($valuetype == 'macro') ? "inline" : "none";
            $display_tokens = ($valuetype == 'dynmc') ? "inline" : "none";
            $optionsAllMacros = $this->GetHtml_SelectOptions(reduce_assoc($this->DB_Variables, 'odx_name'), $editSet['value']);
            $optionsAllTokens = $this->GetHtml_SelectOptions($allTokens, $editSet['value']);
            $realValue = ($editSet['type'] == 'string') ? hex2bin($editSet['value']) : hexdec($editSet['value']);

            if ($col_id != 4)
                echo <<<HEREDOC
              <td>
                <input class="value" name="ecu[$col_id][value]" id="id_value_edit_$col_id" type="text" size="{$size_of_Wert}" value="{$realValue}" style="display:$display_edit" $disable_linking>
                <select class="macroname" name="ecu[$col_id][macroname]" id="id_macro_select_$col_id" style="display:$display_select" $disable_linking>$optionsAllMacros</select>
                <select class="dyn_token" name="ecu[$col_id][dyn_token]" id="id_token_select_$col_id" style="display:$display_tokens" $disable_linking>$optionsAllTokens</select>
              </td>
              <td>{$str_valuetype}</td>
HEREDOC;
            else
                echo <<<HEREDOC
              <td></td>
              <td></td>
HEREDOC;

            echo <<<HEREDOC
              <td><input  name="ecu[$col_id][startBit]" type="text" size="2" maxsize="1" value="{$editSet['startBit']}"></td>
              <td><input  name="ecu[$col_id][stopBit]" type="text" size="2" maxsize="1" value="{$editSet['stopBit']}"></td>
            </tr>
HEREDOC;
        } else // (not $editMode)
        {

            $td_show_id = $td_edit_id;
            $td_show_udsId = "0x" . $editSet['udsId'];
            $td_show_protocol = $editSet['protocol'] != null ? $editSet['protocol'] == 1 ? "UDS" : "XCP" : "NULL";
            $td_show_action = $editSet['action'];
            $td_show_type = $editSet['type'];
            $td_show_size = $editSet['size'];
            $td_show_factor = $editSet['factor'];

            if ($col_id == 4) {
                $str_valuetype = '<span class="inactiveLink">(nicht benutzt)</span>';
                if (!$editSet['use_in_odx02']) {
                    $td_show_id = "<span class=\"inactiveLink\">SERIAL_NUMBER (wird nicht verwendet)</span>";
                    $td_show_udsId = "";
                    $td_show_protocol = "";
                    $td_show_action = "";
                    $td_show_type = "";
                    $td_show_size = "";
                    $td_show_factor = "";
                    $hinweis = "";
                }
            }

            if (($col_id != 4) || $editSet['use_in_odx02']) {
                if (empty ($id))
                    $id = '<span class="inherited">' . $editSet['odx_name'] . '</span>';

                if (empty ($editSet['id']))
                    $errClass['id'] = &$this->errorStyle;

                if (empty ($editSet['udsId']))
                    //$errClass['udsId'] = ' class="errorfield"';
                    $errClass['udsId'] = &$this->errorStyle;

                if (empty ($editSet['type']))
                    $errClass['type'] = &$this->errorStyle;

                if (empty ($editSet['size']) || !$editSet['size'])
                    $errClass['size'] = &$this->errorStyle;
            }

            switch ($valuetype) {
                case 'macro':
                    $value = $editSet['value'];
                    $str_value = $this->DB_Variables[$value]['odx_name'];
                    break;

                case 'version':
                case 'deflt':
                case 'const':
                    $str_value = ($editSet['type'] == 'string') ? hex2bin($editSet['value']) : hexdec($editSet['value']);
                    break;

                case 'dynmc':
                    $value = $editSet['value'];
                    if (isset ($allTokens[$value]))
                        $str_value = $value;
                    else {
                        $this->SetError(ERROR_INVALID_TOKEN, $value);
                        $str_value = "{ungültiger token: $value}";
                    }
                    break;

                default:
                    $str_value = '<span class="inactiveDescr indeted">{keine Vorgabe}</span>';
                    break;
            }

            echo <<<HEREDOC
            <tr>
              <td>$colinfo</td>
              <td style="display:none;">&nbsp;</td>
              <td{$errClass['id']}>{$td_show_id}</td>
              <td>{$hinweis}</td>
              <td class="protocol">{$td_show_protocol}</td>
              <td {$errClass['udsId']} class="uds">{$td_show_udsId}</td>
              <td{$errClass['action']}>{$td_show_action}</td>
              <td{$errClass['type']}>{$td_show_type}</td>
              <td{$errClass['size']}>{$td_show_size}</td>
              <td>{$td_show_factor}</td>
              <td>{$editSet['offset']}</td>
              <td>{$editSet['unitstr_02']}</td>
              <td>{$str_value}</td>
              <td>{$str_valuetype}</td>
              <td>{$editSet['startBit']}</td>
              <td>{$editSet['stopBit']}</td>
            </tr>
HEREDOC;

        }
    }

    // ===============================================================================================
    function WriteHtml_TableEcuParametersEditSet($col_id, &$editSet, $editMode) {
        if (!isset ($editSet))
            return;

        $deleted = strtolower(trim($editSet['deleted']));
        if ($deleted == 'all')
            return;

        switch ($this->m_OdxVerShow) {
            case 1:
                if ($editSet['use_in_odx01'] && !strpos($deleted, 'dx.sts.01') && ($editSet['order'] > 0 || $editSet['order'] == ''))
                    $this->WriteHtml_TableEcuParametersEditSet_odx01($col_id, $editSet, $editMode);
                break;

            case 2:
                $this->WriteHtml_TableEcuParametersEditSet_odx02($col_id, $editSet, $editMode);
                break;
        }
    }

    // ===============================================================================================
    function WriteHtml_TableEcuParameters($editMode = false) {
        $image_add = '"/images/symbols/icon-add.png"';
        $display1stRow = $editMode ? '' : ' style="display: none;"';
        $ord_or_sel = 'Ord.';

        if ($this->m_EditMode == self::EDIT_DistributeSWParameters)
            $ord_or_sel = <<<HEREDOC
<div class="ttip ttip_headln"><input type="checkbox" id="cb_select_all" checked>Alle<span class="ttiptext">Alle aus- abwählen</span></div>
HEREDOC;

        switch ($this->m_OdxVerShow) {
            case 1:
                echo <<<HEREDOC
          <table class="ecuParamDefinesForOdx01 versionHighlighted" id="idTableEcuParamDefines">
            <thead>
              <tr>
                <td>Ord.</td>
                <td>Bezeichnung</td>
                <td>odx v.02</td>
                <td class="protocol">Protocol</td>
                <td>Aktion</td>
                <td>Typ</td>
                <td>Einheit</td>
                <td>SW-Wertvorgabe</td>
                <td>Vorgabetyp</td>
              </tr>
            </thead>
            <tbody>
HEREDOC;
                break;


            case 2:
                echo <<<HEREDOC
          <table class="ecuParamDefinesForOdx02 versionHighlighted" id="idTableEcuParamDefines">
            <thead>
              <tr>
                <td>$ord_or_sel</td>
                <td{$display1stRow}>Del.</td>
                <td>Bezeichnung</td>
                <td>odx v.01</td>
                <td class="protocol">Protocol</td>
                <td class="uds">UDS-Id</td>
                <td>R/W/C</td>
                <td>Typ</td>
                <td># Bytes</td>
                <td>Faktor</td>
                <td>Offset</td>
                <td>Einheit</td>
                <td>SW-Wertvorgabe</td>
                <td>Vorgabetyp</td>
                <td>Start</td>
                <td>Stopp</td>
              </tr>
            </thead>
            <tbody>

HEREDOC;
                break;
        }

        $this->WriteHtml_TableEcuParametersEditSet(1, $this->S_EditSet['edit'][1], $editMode);
        $this->WriteHtml_TableEcuParametersEditSet(2, $this->S_EditSet['edit'][2], $editMode);
        echo "              <tr><td colspan=\"16\"></td>\n";

        $showSerialNumber = true;

        if ($this->m_OdxVerShow == 1) {
            $showSerialNumber = ($this->S_EditSet['edit'][4]['order'] > 0) &&
                !strpos($this->S_EditSet['edit'][4]['deleted'], 'dx.sts.01');
        }

        if ($showSerialNumber) {
            $this->WriteHtml_TableEcuParametersEditSet(4, $this->S_EditSet['edit'][4], $editMode);
            echo "              <tr><td colspan=\"16\"></td>\n";
        } else {
            echo '<tr style="display:none"><td></td></tr><tr style="display:none"><td></td></tr>';
        }

        $this->SortEditSet_ECUVersionen($this->m_OdxVerShow);
        //
        foreach ($this->S_EditSet['sort'] as $param_id) {
            $editSet = &$this->S_EditSet['edit'][$param_id];

            $this->WriteHtml_TableEcuParametersEditSet($param_id, $editSet, $editMode);
        }

        echo "          </tbody>\n";

        if ($editMode && ($this->m_OdxVerShow == 2)) {
            $new_uds_id = safe_val($_REQUEST['ecu_new'], 'udsId', '');
            $new_action = safe_val($_REQUEST['ecu_new'], 'action', ['r' => 'on', 'w' => 'on', 'c' => 'on']);
            $new_type = safe_val($_REQUEST['ecu_new'], 'type', 'ascii');
            $new_size = safe_val($_REQUEST['ecu_new'], 'size', 2);
            $new_factor = safe_val($_REQUEST['ecu_new'], 'factor', 1);
            $new_offset = safe_val($_REQUEST['ecu_new'], 'offset', 0);
            $new_unit = safe_val($_REQUEST['ecu_new'], 'unit', 0);
            $new_value = safe_val($_REQUEST['ecu_new'], 'value', '');
            $new_macro = safe_val($_REQUEST['ecu_new'], 'macro', 0);
            $new_vtype = safe_val($_REQUEST['ecu_new'], 'vtype', 'unused');
            $new_start = safe_val($_REQUEST['ecu_new'], 'start', 0);
            $new_stop = safe_val($_REQUEST['ecu_new'], 'stop', 0);

            $checked['r'] = isset ($new_action['r']) ? ' checked' : '';
            $checked['w'] = isset ($new_action['w']) ? ' checked' : '';
            $checked['c'] = isset ($new_action['c']) ? ' checked' : '';

            $display_edit = (($new_vtype == 'deflt') || ($new_vtype == 'const') || ($new_vtype == 'version')) ? "inline" : "none";
            $display_select = ($new_vtype == 'macro') ? "inline" : "none";

            $macro_options = $this->GetHtml_SelectOptions(reduce_assoc($this->DB_Variables, 'odx_name'), $new_macro);
            $unit_options = $this->GetHtml_SelectOptions($this->DB_allUnits, $new_unit, 18);
            $type_options = $this->GetHtml_SelectOptions(self::ODX02_VALUETYPES, $new_vtype);

            if ($this->m_EditMode != self::EDIT_DistributeSWParameters)
                echo <<<HEREDOC
          <tfoot>

            <tr style="background-color:#ccc">
              <td><a href="javascript:AddNewRows()"><img src={$image_add}></a></td>
              <td colspan="3"><input type="number" value="1" class="number" name="addnum"> Zeilen hinzufügen</td>
                <td class="protocol">
                <select name="ecu_new[protocol]">
                    <option value="1">UDS</option>
                    <option value="2">XCP</option>
                </select>
              </td>
              <td class="uds">0x<input  name="ecu_new[udsId]" type="text" size="8" value="">$new_uds_id</td>
              <td>
                <input type="checkbox" name="ecu_new[action][r]" {$checked['r']}>
                <input type="checkbox" name="ecu_new[action][w]" {$checked['w']}>
                <input type="checkbox" name="ecu_new[action][c]" {$checked['c']}>
              </td>
              <td>
                <select name="ecu_new[type]">
                  <option value="ascii"    {$selected['ascii']}>ascii</option>
                  <option value="signed"   {$selected['signed']}>signed</option>
                  <option value="unsigned" {$selected['unsigned']}>unsigned</option>
                  <option value="blob"     {$selected['blob']}>blob</option>
                </select>
              </td>
              <td>
                <input  name="ecu_new[size]" type="number" class="number" min="1" max="1000" value="$new_size">
              </td>
              <td>
                <input  name="ecu_new[factor]" type="number" class="number" value="$new_factor" step="0.001">
              </td>
              <td>
                <input  name="ecu_new[offset]" type="number" class="number" value="$new_offset">
              </td>
              <td>
                <select name="ecu_new[unit]">
                    $unit_options
                </select>
              </td>
              <td>
                <input  name="ecu_new[value]" id="id_value_edit_new" type="text" size="24" value="{$new_value}" style="display:$display_edit">
                <select name="ecu_new[macro]" id="id_macro_select_new" style="display:$display_select">$macro_options</select>

              </td>
              <td>
                <select name="ecu_new[vtype]" OnChange="OnValuetypeChanged(this, 'new')">{$type_options}</select>
              </td>
              <td>
                <input  name="ecu_new[start]" type="text" size="2" maxsize="1" value="$new_start">
              </td>
              <td>
                <input  name="ecu_new[stop]" type="text" size="2" maxsize="1" value="$new_stop">
              </td>
            </tr>
          </tfoot>
HEREDOC;

            echo '</table>
                <datalist id="allparamNames">';

            $this->allParamNames = $this->QueryParamNames();
            if (!empty($this->allParamNames)) {
                foreach ($this->allParamNames as $set)
                    if (!empty ($set['tag_value']))
                        echo "<option value=\"{$set['tag_value']}\" />\n";
            }
            echo "
        </datalist>\n";
        } else echo "
        </table>\n";
    }

    // ===============================================================================================
    function WriteHtml_EcuVersions() {
        $errClass = ['sts_version' => '', 'request_id' => '', 'response_id' => '', 'subversion_suffix' => ''];
        if (!isset ($this->Variables))
            $this->Variables = $this->QueryParameters(0, 0);

        $ecuOptions = $this->GetHtml_SelectOptions($this->allEcuNames, $this->S_currentEcu, 16);
        $this_ecu_versions = [];

        //$formheader = $this->GetHtml_FormHeader("idr_", 2, "mainform");
        $disabled = ($this->m_EditMode == self::EDIT_DistributeSWParameters) ? 'disabled' : '';

        /* echo <<<HEREDOC
 $formheader
   <input type="hidden" name="scrollpos" id="id_scrollpos" value="0">
   <div class="mainframe" id="id_mainframe">
     <div class="seitenteiler flexTop" id="idEcuLinks">
       <table class="ecuVersions sales">
         <tbody>
           <tr class="tr1">
             <th>Gerät/ECU</th>
           </tr>
           <tr class="tr2">
             <td>
               <select class="ParamFilterA" name="ecuVersion[selectedEcu]" id="idSelectedEcu" $disabled size="14">
                 {$ecuOptions}
               </select>
             </td>
           </tr>
           <tr class="tr3">
             <th>Versionen</th>
           </tr>
           <tr class="tr4">
             <td>

 HEREDOC;*/
        /*
        $selectd = $this->S_ecuVersion['ecu_revision_id'];
        if (($this->m_EditMode == self::EDIT_NewEcuVersion) || ($this->m_EditMode == self::EDIT_CopyEcuVersion))
          $selectd = false;

        if ($this->m_EditMode == self::EDIT_DistributeSWParameters) {
          $ecu = $this->S_currentEcu;
          $revisions = $this->QueryRevisions($ecu);
          $corrected = $this->GetListOfDisplayedRevisionsNames($revisions, false);

          unset ($corrected[$selectd]);

          echo <<<HEREDOC
                  <select multiple class="ParamFilterA" name="ecuVersion[copyTo][]" size="14">
    HEREDOC;

          echo $this->GetHtml_SelectOptions($corrected, false, 16);
          echo "              </select>\n";

        } else {

          foreach ($this->allEcuNames as $ecu => $ecuname) {
            $revisions = $this->QueryRevisions($ecu);
            $corrected = $this->GetListOfDisplayedRevisionsNames($revisions, false);
            $visible = "none";

            if ($ecu == $this->S_currentEcu) {
              $visible = "inline";
              $this_ecu_versions = $corrected;
            }


            echo <<<HEREDOC
                      <select class="ParamFilterA" name="ecuVersion[ecu][$ecu]" id="idEcuVersion{$ecu}" OnClick="OnEcuVersionChanged(this)" size="14" style="display:{$visible};">

    HEREDOC;

            echo $this->GetHtml_SelectOptions($corrected, $selectd, 16);
            echo "              </select>\n";
          }
        }
    */
        //$_SERVER['REQUEST_URI'] . '">
        /* echo $formheader; */
        echo '<form method="POST" name="mainform" id="idr_form" action="index.php?action=ecuParMan"> 
        <input type="hidden" name="action" value="ecuParMan">
        <input type="hidden" name="pageid" value="1">
        <input type="hidden" name="command" id="idr_command" value="">';

        if (($this->m_EditMode != 0)) {
            echo "<input type='hidden' name='edit' value='" . $this->m_EditMode . "'>";
        }

        echo '<input type="hidden" name="ecuVersion[selectedValue]" value="' . $_REQUEST['ecuVersion']['selectedEcu'] . '">';
        echo '<input type="hidden" name="ecuVersion[ecu][' . $_REQUEST["ecuVersion"]["selectedEcu"] . ']" value="' . $_REQUEST['ecuVersion']['ecu'][$_REQUEST['ecuVersion']['selectedEcu']] . '">';

        echo isset($_REQUEST["odxVersion"][$_REQUEST['ecuVersion']['selectedEcu']]) ? "<input type='hidden' name='odxVersion[" . $_REQUEST['ecuVersion']['selectedEcu'] . "]' value='" . $_REQUEST["odxVersion"][$_REQUEST['ecuVersion']['selectedEcu']] . "'>" : "";

        echo '<div class="seitenteiler flexTop" id="idEcuRechts">';

        $this->SetAutoForward('ecuVersion');

        if (count($this->S_EditSet) == 0) {
            echo "    </div>\n  </div>\n</form>\n";
            return;
        }

        $ecu = $this->S_currentEcu;
        $sts_version = $this->S_EditSet['revision']['sts_version'];
        $subversion_suffix = $this->S_EditSet['revision']['subversion_suffix'];
        $subversion_suffix_flag = $subversion_suffix != '' ? true : false;
        $td_info_text = htmlspecialchars(stripslashes($this->S_EditSet['revision']['info_text']));
        $td_sts_version = $sts_version;
        $td_subversion_suffix = $subversion_suffix;
        $td_ecu = $this->allEcuNames[$ecu];
        $td_href_windchill = $this->S_EditSet['revision']['href_windchill'];
        $td_request_id = $this->S_EditSet['revision']['request_id'];
        $td_response_id = $this->S_EditSet['revision']['response_id'];
        $editMode = 0;
        $odx_version_text = [1 => 'odx.sts.01', 2 => 'odx.sts.02'];
        $td_ecu_support = toBool($this->allEcus[$ecu]['supports_odx02']) ? 'JA' : 'NEIN';

        $proto = max(0, $this->S_EditSet['revision']['protocol']);
        $td_protokoll = $this->allprotocols[$proto];
        $profile_ok = toBool($this->S_EditSet['revision']['profile_ok']);
        $released = toBool($this->S_EditSet['revision']['released']);

        if (($this->m_EditMode == self::EDIT_EcuVersion) || ($this->m_EditMode == 0)) {
            foreach (['sts_version', 'request_id', 'response_id'] as $tag)
                $errClass[$tag] = $this->S_EditSet['err']['revision'][$tag] ? $this->errorStyle : "";
        }

        if ($subversion_suffix_flag) {
            $readonly_sts_version = 'readonly';
        } else {
            $readonly_sts_version = '';
        }

        switch ($this->m_EditMode) {
            /*      case self::EDIT_NewEcuVersion:
        $sts_version = $td_href_windchill = $td_request_id = $td_response_id = $td_protokoll = $td_info_text = $td_subversion_suffix = "";
        $this->S_EditSet['err']['revision'] = [];

      case self::EDIT_CopyEcuVersion:
        $released = false;*/
            case self::EDIT_DistributeSWParameters:
            case self::EDIT_EcuVersion:
                /*     if ($this->m_EditMode == self::EDIT_CopyEcuVersion) {
          $td_sts_version = sprintf('<input type="search" list="allversions" name="ecuVersion[sts_version]" size="24" value="" placeholder="%s"%s %s>',
              $sts_version, $errClass['sts_version'], $readonly_sts_version);

          if (count($this_ecu_versions))
            $td_sts_version .= '<datalist id="allversions"><option value="' . implode('" /><option value="', $this_ecu_versions) . '" /></datalist>';
        } else { */

                $td_sts_version = sprintf('<input type="text" name="ecuVersion[sts_version]" size="24" value="%s"%s %s placeholder="Bsp.: D16X48AD72B1_01 A">',
                    $sts_version, $errClass['sts_version'], $readonly_sts_version);
                /*}*/

                if ($subversion_suffix_flag)
                    $td_subversion_suffix = sprintf('<input type="text" name="ecuVersion[subversion_suffix]" style="width: 150px" max-length="100" value="%s"%s placeholder="">',
                        $subversion_suffix, $errClass['subversion_suffix']);
                else
                    $td_subversion_suffix = '<input type="hidden" name="ecuVersion[subversion_suffix]" value="" disabled>';

                $td_href_windchill = sprintf('<input placeholder="http://windchillapp.streetscooter.local/Windchill" title="http://windchillapp.streetscooter.local/Windchill" type="text" name="ecuVersion[href_windchill]" id="id_href_windchill" size="100" maxlength="800" value="%s"%s>',
                    $td_href_windchill, $errClass['href_windchill']);

                $td_request_id = sprintf('<input type="text" name="ecuVersion[request]" size="10" maxlength="8" value="%s"%s placeholder="12a45e78">',
                    $td_request_id, $errClass['request_id']);

                $td_response_id = sprintf('<input type="text" name="ecuVersion[response]" size="10" maxlength="8" value="%s"%s placeholder="12a45e78">',
                    $td_response_id, $errClass['response_id']);

                $td_protokoll = sprintf('<select name="ecuVersion[protocol]">%s</select>', $this->GetHtml_SelectOptions($this->allprotocols, $proto));

                $td_info_text = sprintf('<input type="text" name="ecuVersion[info]" style="width: 150px" max-length="100" value="%s">', $td_info_text);
                $editMode = $this->m_EditMode;

                break;
        }

        if (!$profile_ok)
            $td_released = "SW fehlerhaft";

        if ($editMode) {
            $html_odx_version = $odx_version_text[$this->m_OdxVerShow];

            if ($profile_ok) {
                $released_selected = $released ? " selected" : "";
                $td_released = '<select name="ecuVersion[released]"><option value="0">in entwicklung</option><option value="1"' . $released_selected . '>fertig/released</option></select>';
            }
        } else {
            $odxChecked = ["", "", ""];
            $odxChecked[$this->m_OdxVerShow] = " checked";
            if ($profile_ok)
                $td_released = $released ? "fertig/released" : "in entwicklung";

            $td_href_windchill = $this->LimitedSpace($td_href_windchill, 'string', 2, 80, 'id="id_href_windchill"');
            $url = $this->GetHtml_Url();
            $html_odx_version = <<<HEREDOC
            <input type="radio" id="id_odx01" name="odxVersion[{$this->S_currentEcu}]" value="1"{$odxChecked[1]} onClick="window.location.href='$url&odxVersion[{$this->S_currentEcu}]=1'">
            <label for="id_odx01"> odx.sts.01</label>
            <input type="radio" id="id_odx02" name="odxVersion[{$this->S_currentEcu}]" value="2"{$odxChecked[2]} onClick="window.location.href='$url&odxVersion[{$this->S_currentEcu}]=2'">
            <label for="id_odx02"> odx.sts.02</label>
HEREDOC;
        }

        switch ($this->m_OdxVerShow) {
            case 1:
                $odxComment = "Die Reihenfolge kann nicht geändert werden. Hier kann nur SW-Revision, Link zu Windchill und Defaultwerte der Parameter und deren Verwendung editiert werden. ";

                if ($proto > 1)
                    $odxComment .= '<br><span class="errorfont"><b>ACHTUNG! Diese SW-Version verwendet XCP!</b> Es werden zusätzliche, hier nicht sichtbare, Parameter verwendet.</span>';

                echo <<<HEREDOC
      <div>
      <div style="overflow-x: scroll">
        <table class="silver stdborder EcuVersionHeader">
            <tr class="EcuVersionSpacer"><th colspan="13"></th></tr>
            <tr class="EcuVersionHeader1">
              <th>SW-Revision:</th><td {$errClass['sts_version']}>{$td_sts_version}</td>
              <th>&nbsp;</th>
              <th>odx.sts.02:</th><td>{$td_ecu_support}</td>
              <th>Protokolle</th><td protocol="$proto">$td_protokoll</td>
              <th colspan="6">&nbsp;</th>
            </tr>
            <tr class="EcuVersionSpacer"><th colspan="13"></th></tr>
            <tr class="EcuVersionHeader2">
              <th>Versionsinfo:</th><td>{$td_info_text}</td>
              <th>&nbsp;</th>
              <th>Link zu Windchill:</th><td colspan="8">{$td_href_windchill}</td>
              <th>&nbsp;</th>
            </tr>
HEREDOC;
                if ($subversion_suffix_flag) {
                    echo <<<HEREDOC
            <tr class="EcuVersionSpacer"><th colspan="13"></th></tr>
          <tr class="EcuVersionHeader3">
              <th>Suffix:</th><td {$errClass['subversion_suffix']}>{$td_subversion_suffix}</td>
              <th colspan="10"></th>
            </tr>
HEREDOC;
                } else {
                    echo $td_subversion_suffix;
                }
                echo <<<HEREDOC
            <tr class="EcuVersionSpacer"><th colspan="13"> </th></tr>
        </table>
        </div>
        <div class="scrollboxXY stdborder" id="id_scrollbox" style="max-height:410px;margin-top:10px;">

HEREDOC;
                break;

            case 2:
                $odxComment = "Die Reienfolge für Parameter 1 (HW_VERSION_X) und 2 (SW_VERSION_X) und deren Verwendung können nicht geändert weren. Diese werden unter TEO als HW und SW Versionkennung angezeigt. " .
                    "das Löschen wirkt sich nur auf odx.sts.02 aus. Der Parameter SERIAL_NUMBER wird falls vorhanden ausgelesen und in TEO/SIA angezeigt.";

                if ($proto > 1)
                    $odxComment .= '<br><span class="errorfont"><b>ACHTUNG! Diese SW-Version verwendet XCP!</b> Die angezeigten Werte haben zum Teil andere Bedeutungen oder es werden zusätzliche, hier nicht sichtbare, Parameter verwendet.</span>';


                echo <<<HEREDOC
      <div>
        <div style="overflow-x: scroll">
        <table class="silver stdborder EcuVersionHeader">
            <tr class="EcuVersionSpacer"><th colspan="13"></th></tr>
            <tr class="EcuVersionHeader1">
              <th>SW-Revision:</th><td {$errClass['sts_version']}>{$td_sts_version}</td>
              <th>odx.sts.02:</th><td>{$td_ecu_support}</td>
              <th>Protokoll:</th><td protocol="$proto">$td_protokoll</td>
              <th><span class="request">Request-ID:</span></th><td{$errClass['request_id']}><span class="request">0x$td_request_id</span></td>
              <th><span class="response">Response-ID:</span></th><td{$errClass['response_id']}><span class="response">0x$td_response_id</span></td>
              <th>&nbsp;</th>
            </tr>
            <tr class="EcuVersionSpacer"><th colspan="13"></th></tr>
            <tr class="EcuVersionHeader2">

              <th>Versionsinfo:</th><td>{$td_info_text}</td>
              <th>Status:</th><td>$td_released</td>
              <th>Link zu Windchill:</th><td colspan="5">{$td_href_windchill}</td>
              <th>&nbsp;</th>
            </tr>
HEREDOC;
                if ($subversion_suffix_flag) {
                    echo <<<HEREDOC
            <tr class="EcuVersionSpacer"><th colspan="13"></th></tr>
          <tr class="EcuVersionHeader3">
              <th>Suffix:</th><td {$errClass['subversion_suffix']}>{$td_subversion_suffix}</td>
              <th colspan="10"></th>
            </tr>
HEREDOC;
                } else {
                    echo $td_subversion_suffix;
                }
                echo <<<HEREDOC
          <tr class="EcuVersionSpacer"><th colspan="13"> </th></tr>
        </table>
        </div>    
        <div class="scrollboxXY stdborder" id="id_scrollbox" style="max-height:410px;margin-top:10px;">

HEREDOC;
                break;
        }

        if ($this->S_currentEcu && $this->S_ecuVersion) {
            $this->WriteHtml_TableEcuParameters($editMode);


            echo <<<HEREDOC
        </div>
        <div class="odxVersion">
          Anzeige optimiert für: {$html_odx_version}

        </div>
        <div><span class="W030">Die Reihenfolge der Parameter gibt die Reihenfolge der Verarbeitung in TEO und SIA an. <br>
        $odxComment
           </span>
        </div>
      </div> 
      
      <div class="variantPartsButtons stdborder">
        <div class="MiniButtons">
HEREDOC;

            $this->GetEnggPrivileges(self::PRIV_ECU_PROFILE, $this->S_currentEcu);
            $num_used = $this->CntVariantsUsingRevision($this->S_ecuVersion['ecu_revision_id']);

            $allow_edit = !empty ($this->m_Permission[self::PRIV_ECU_PROFILE]['current']);
            $allow_del = ($this->m_Permission[self::PRIV_ECU_PROFILE]['current'] == 'owner') && ($num_used == 0);

            if ($allow_edit) {
                $editMode = ($this->m_EditMode == self::EDIT_EcuVersion);
                /*        $addNew = ($this->m_EditMode == self::EDIT_NewEcuVersion) ? BT_TOGGLED : BT_ENABLED;
        $addClone = ($this->m_EditMode == self::EDIT_CopyEcuVersion) ? BT_TOGGLED : BT_ENABLED;
        $addCopy = ($this->m_EditMode == self::EDIT_DistributeSWParameters) ? BT_TOGGLED : BT_ENABLED;*/
                $addDel = (($this->m_EditMode) || (!$allow_del)) ? BT_DISABLED : BT_ENABLED;
            } else {
                $addNew = BT_DISABLED;
                $addClone = BT_DISABLED;
                $addCopy = BT_DISABLED;
                $addDel = BT_DISABLED;
            }

            if ($this->odxAccessError === ERROR_ODX_CREATOR_LOCKED) {
                $mutex_owner_name = "{$this->odxAccessMutex['fname']} {$this->odxAccessMutex['lname']}";

                echo $this->GetHtml_ErrorBox("ODX Creator gesperrt durch den Anwender $mutex_owner_name");
            } else
                if ($this->odxAccessError === ERROR_ODX_CREATOR_GET_LOCK) {
                    echo $this->GetHtml_ErrorBox("Fehler beim Zugriff auf die freigabe 'ODX Creator'");
                }

            if (($this->m_OdxVerShow == 2) && ($this->configError === ERROR_INVALID_ODX2_CONFIG)) {
                echo $this->GetHtml_ErrorBox("Parameter Konfiguration fehlerhaft oder unvollständig</div><div><br>
                    Nur fehlerfreie (nicht rote) Zeilen werden den Fahrzeugwerten angezeigt und in die odx-Datei geschrieben.");
            }

            if (($this->m_OdxVerShow == 1) && toBool($this->allEcus[$this->S_currentEcu]['supports_odx02'])) {
                $allow_edit = $editMode = $addNew = $addClone = $addCopy = 0;
            }

            // $allow_edit = !$released;
            $this->WriteHtml_CommandButtons($allow_edit, $editMode, $addNew, $addClone, BT_ENABLED, $addCopy, $addDel);

            echo "        </div>\n";

        }

        echo <<<HEREDOC
      </div>
    </div>
    <div class="seitenteiler flexTop" id="idEcuBack">
      <h2>Keine SW-Version ausgewählt</h2>
      <div class="variantPartsButtons stdborder">
        <div class="MiniButtons">
          <ul class="submenu_ul">
            <li><a href="/index.php?action=parameterlist&pageid=1&ecuVersion[selectedEcu]=6&ecuVersion[ecu][6]=29&edit=0&command=new" class="sts_submenu W080">Neu</a></li>
            <li><div class="sts_submenu W080 disabled">Neue Kopie</div></li>
            <li><div class="sts_submenu W080 disabled">Bearbeiten</div></li>
		    <li><div class="sts_submenu W080 disabled">Speichern</div></li>
            <li><div class="sts_submenu W080 disabled">Abbruch</div></li>
            <li><div class="sts_submenu W080 disabled">Export</div></li>
            <li><div class="sts_submenu W080 disabled"><img src="/images/symbols/download_active.png"> ODX</div></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</form>
HEREDOC;

    }
    /*
  // ===============================================================================================
  function WriteHtml_TAB_Varianten()
  {
    echo "<div class=\"mainframe\" id=\"id_mainframe\">\n";
    $this->WriteHtml_SelectVariant();
    $this->SetAutoForward("filter");

    echo "\n<div class=\"seitenteiler flexTop\" id=\"idRechte\" style=\"justify-content:space-between;min-width:0;\">\n";

    if ($this->DB_VariantSet || ($this->m_EditMode == self::EDIT_NewVariant)) {
      switch ($this->S_ParamType) {
        case self::ID_Fahrzeuginfo:
          // ------------------------------------------------------------------------------------------------
          // Vehicle_Variant Fahrzeuginfo
          //
          $this->WriteHtml_VariantMain();
          break;

        case self::ID_Fahrzeugeigenschaften:
          // ------------------------------------------------------------------------------------------------
          // Vehicle_Variant Fahrzeugeigenschaften
          //
          $this->WriteHtml_Variant_CocData();
          break;

        case self::ID_GlobaleParameter:
          // ------------------------------------------------------------------------------------------------
          // Globale Parameter
          //
          $this->WriteHtml_GlobalParameter();
          break;

        case self::ID_AlleParameter:
          echo <<<HEREDOC
    <div class="mainframe" id="id_mainframe">
      <div class="seitenteiler" style="width:100%;justify-content: center; align-items: center;">
        <img src="images/symbols/under-construction.jpeg"><br>
      </div>
    </div>
HEREDOC;

          break;

        case self::ID_GeraeteParameter:
          // ------------------------------------------------------------------------------------------------
          // Geräteparameter
          //
          if ($this->S_variantEcu) {
            if ($this->DB_Parameters) {
              $this->WriteHtml_Variant_EcuTable($this->S_variantEcu);
            } else
              if ($this->DB_Parameters == 0) {
                $this->SetWarning(WARNING_NO_VARIANT_ECU_REV);
              }
          }


          break;
      }
    } else {
      $this->SetMessage(MESSAGE_DONT_USE_BROWSER_BACK);
    }
    echo "  </div>\n</div>\n";
  }
*/
    // ===============================================================================================
    function WriteHtmlContent($options = "") {
        $H = "h2";

        $this->SetAutoPostVar('edit', $this->m_EditMode);
        $this->SetAutoForward("selected");

        parent::WriteHtmlContent($options);

        // $this->WriteHtml_TabControl();


        // ------------------------------------------------------------------------------------------------
        // Filter
        //
        //echo "<$H>Filter</$H>\n";

        switch ($this->S_Tab) {
            /*case self::ID_TAB_Varianten:
        $this->WriteHtml_TAB_Varianten();
        break;

      case self::ID_TAB_Parts:
        $this->WriteHtml_VariantPartsMapping();
        break;*/

            case self::ID_TAB_ECU_Versionen:
                $this->WriteHtml_EcuVersions();
                break;

            /*    case self::ID_TAB_SW_ODX_Uebersicht:
        $this->WriteHtml_SwOdxUebersicht();
        break;

      case self::ID_TAB_EcuOdxVersion:
        $this->WriteHtml_SetOdxVersions();
        break;

      case self::ID_TAB_Verantwortliche:
        $this->WriteHtml_TAB_Verantwortliche();
        break;*/
        }


        if ($this->odx_content) {
            $odx_decoded = htmlspecialchars($this->odx_content);

            echo <<<HEREDOC
    <div class="odxbox" id="id_odxbox">
      <div>ODX Datenbetrachter - {$this->odx_file}</div>
        <textarea readonly wrap="off">
$odx_decoded
        </textarea>
      <div class="msgButtons">
        <input type="Button"><input type="Button" value="schließen" onClick="document.getElementById('id_odxbox').style.visibility='hidden';"><input type="Button">
      </div>
    </div>
HEREDOC;
        }
        // echo '<a href="javascript:UpdateTableColumnWidth ()">Tablewidth</a>';
    }
    // ===============================================================================================

}

?>

