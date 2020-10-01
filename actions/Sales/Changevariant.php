<?php
/**
 * CAction_Sales_Changevariant.php
 * @author Lothar Jürgens
 */

require_once $_SERVER['STS_ROOT'] . "/includes/sts-defines.php";

define('STS_ERROR_NO_PENTA_VARIANT', MSG_CLASS_ERROR + 1);
define('STS_ERROR_NO_IDENT', MSG_CLASS_ERROR + 2);
define('STS_ERROR_WRONG_COLUMN', MSG_CLASS_ERROR + 3);
define('STS_ERROR_VARIANT_NOT_EXISTS', MSG_CLASS_ERROR + 4);

class ACtion_Changevariant extends AClass_TableBase {
    function __construct($pageController) {
        parent::__construct($pageController);

        $this->btnLabels += array(
            'delete1' => "ausgewählte Fzg. löschen &gt;&gt;",
            'delete2' => "Fahrzeuge unwiederruflich löschen!",
            'exports' => 'Listen / Export',
            'change' => "Variante ändern",
            'color' => "Farbe ändern",
            'add_option' => "hinzuf.",
            'rm_option' => "entf.",
            'table' => "Suche über Tabelle",
            'edit1' => "ausgewählte Fzg. bearbeiten &gt;&gt;",
            'save' => 'Speichern',
            'location' => 'Standort zuweisen!'
        );

        $this->btnEnabled += array(
            'delete1' => 'anySelected',
            'edit1' => 'anySelected',
            'exports' => 'anySelected',
        );


        $this->S_identCol = &InitSessionVar($this->S_data['identCol'], false);
        $this->S_updateCols = &InitSessionVar($this->S_data['updateCols'], false);
        $this->S_changedIds = &InitSessionVar($this->S_data['changedIds'], false);
    }
    // ==============================================================================================
    /* derived */
    function DefineColConfig() {
        parent::DefineColConfig();

//        $this->colConfig ['new_penta'] = ['header' => 'Penta (neu)'       ];


        $this->colConfig ['color_id']['enable'] = COL_VISIBLE;
        $this->colConfig ['ikz']['enable'] = COL_VISIBLE;
        $this->colConfig ['akz']['enable'] = COL_VISIBLE;
        $this->colConfig ['windchill']['enable']= COL_VISIBLE;
        $this->colConfig ['parkplatz']['enable']= COL_NOT_USED;
        $this->colConfig ['park_position']['enable']= COL_NOT_USED;

        $this->colConfig ['windchill']['enable'] = COL_VISIBLE;
        $this->colConfig ['parkplatz']['enable'] = COL_NOT_USED;
        $this->colConfig ['park_position']['enable'] = COL_NOT_USED;

        $this->colConfig ['options_variant'] =
            [
                'enable' => COL_INVISIBLE,
                'search' => '',
                'db' => ['readonly' => 1, 'subquery' => "array (select part_id from variant_parts_mapping where variant_parts_mapping.variant_id=vehicles.vehicle_variant)"],
                'header' => 'Ausführung',
            ];

        $this->colConfig ['options_penta'] =
            [
                'enable' => COL_INVISIBLE,
                'search' => '',
                //'db'            => ['readonly'=>1, 'subquery' => "array (select part_id from options_at_vehicles where options_at_vehicles.vehicle_id=vehicles.vehicle_id)"],
                'db' => ['readonly' => 1, 'subquery' => "array (select part_id from penta_number_parts_mapping where penta_number_parts_mapping.penta_number_id=vehicles.penta_number_id)"],
                'header' => 'Sonderausführung',
            ];

        $this->colConfig ['options_text'] =
            [
                'enable' => COL_VISIBLE,
                'search' => '',
                'header' => 'Ausführung'
            ];
    }

    // ==============================================================================================
    function UpdateColumnInfo(&$selectCols, $neededCols, $identCol = null) {

        $dbIdent = "";


        $this->error = STS_NO_ERROR;

        foreach ($neededCols as $col) {
            if (isset($this->colConfig[$col])) {
                $dbCol = $this->GetColumnDbName($col);
                if ($col == $identCol) {
                    if (!$dbCol) {
                        $this->error = STS_ERROR_WRONG_COLUMN;
                        return $this->error;
                    }
                } else {
                    $this->colConfig[$col]['html'] = 'ChangingTo';
                }

                $this->colConfig[$col]['enable'] = COL_VISIBLE;
                if (!isset($selectCols[$col]) && $dbCol)
                    $selectCols[$col] = $dbCol;
            }
            // else $dbCols[$col] = "";
        }

        // $neededCols = $dbCols;
        // if ($identCol)
        //    $identCol = $dbIdent;

        return $this->error;
    }

    // ==============================================================================================
    function InitConstants() {
        parent::InitConstants();
        $this->primaryLocations = $this->prodLocationWithStsPool;


    }

    // ==============================================================================================
    function InitState() {
        parent::InitState();

        switch ($this->InputState) {
            case INPUT_STATE_SELECT:
                $this->colConfig['selected']['enable'] = COL_VISIBLE;
                break;

            case INPUT_STATE_EDIT_1:
                $this->ShowAllScrollable = true;
                if ($this->S_changedColumns['penta_id']) // if ($this->S_data['set_variant'])
                {
                    $this->colConfig['penta_id']['html'] = 'ChangingTo';
                    $this->colConfig['penta_id']['attr'] = ['class' => 'Twrap'];
                }
                if ($this->S_changedColumns['windchill']) // if ($this->S_data['set_variant'])
                {
                    $this->colConfig['windchill']['html'] = 'ChangingTo';
                    $this->colConfig['windchill']['attr'] = ['class' => 'Tvehicles'];
                }
                if ($this->S_changedColumns['color_id']) // if ($this->S_data['set_color'])
                {
                    $this->colConfig['color_id']['html'] = 'ChangingTo';
                    $this->colConfig['color_id']['attr'] = ['class' => 'Tvehicles'];
                }
                if ($this->S_changedColumns['options']) //if ($this->S_data['option_added'])
                {
                    $this->colConfig['options_text']['html'] = 'ChangingTo';
                    $this->colConfig['options_text']['attr'] = ['class' => 'Tvehicles'];
                }
                if ($this->S_changedColumns['depot']) // if ($this->S_data['location_changed'])
                {
                    $this->colConfig['depot']['html'] = 'ChangingTo';
                    $this->colConfig['depot']['attr'] = ['class' => 'Tvehicles'];
                }
                break;

            case INPUT_STATE_EDIT_2:
                $this->ShowAllScrollable = true;
                break;


            case INPUT_STATE_SAVE_PRINT:
                if (!$this->S_changedIds)
                    $this->S_changedIds = array_keys($this->S_selectedVehicles);

                $changedVins = array();
                foreach ($this->S_changedIds as $vid)
                    $changedVins [] = $this->S_selectedVehicles[$vid]['vin'];

                $this->createLieferschein($this->S_changedIds);
                unset ($this->S_selectedVehicles);
                unset ($this->S_currentVehicles);
                $this->colConfig['options_text']['enable'] = COL_INVISIBLE;
                break;
        }
    }

    // ==============================================================================================

    function CreatePentaNumber($penta_number, $variant_id, $color_id, $part_list, $penta_config_id) {
        $insert = ['penta_number' => $penta_number,
            'vehicle_variant_id' => $variant_id,
            'color_id' => $color_id,
            'penta_config_id' => $penta_config_id
        ];

        if (!$this->vehiclesPtr->newQuery('penta_numbers')->insert($insert))
            return false;

        $penta_id = $this->vehiclesPtr->newQuery('penta_numbers')
            ->where('penta_number', '=', $penta_number)
            ->where('vehicle_variant_id', '=', $variant_id)
            ->where('color_id', '=', $color_id)
            ->getVal('penta_number_id');

        if ($penta_id && (!$penta_config_id) && (count($part_list) > 0)) {
            $insert = ['penta_number_id' => $penta_id, 'count' => 1];

            foreach ($part_list as $part_id) {
                $insert['part_id'] = $part_id;
                $this->vehiclesPtr->newQuery('penta_number_parts_mapping')->insert($insert);
            }
        }
        return $penta_id;
    }


    function CheckAndCreatePentaNumbers() {
        $this->penta_mapping = [];

        if (isset ($_REQUEST['pentaform'])) {
            foreach ($_REQUEST['pentaform'] as $vehice_id => $pset) {
                $vset = &$this->S_selectedVehicles[$vehice_id];
                $news = &$this->S_selectedVehicles[$vehice_id]['new'];

                $variant_id = $news['penta_path'][0];
                $part_string = $news['penta_path'][1];
                $color_id = $news['penta_path'][2];

                if (isset($pset['create_regular'])) {
                    $penta_number = $news['penta_prefix'] . $pset['suffix'];
                } else
                    if (isset($pset['create_prototype'])) {
                        $penta_number = $news['penta_prefix'] . $vset['vin'];
                    }

                if (isset ($penta_number)) {
                    $penta_id = $this->CreatePentaNumber($penta_number, $variant_id, $color_id, explode(',', $part_string), 0);
                    if ($penta_id) {
                        $this->penta_mapping[$variant_id][$part_string][$color_id] = $penta_id;
                        $news['penta_id'] = $penta_id;
                    }
                }

                unset ($_REQUEST['pentaform']);
            }
        }

        foreach ($this->S_selectedVehicles as $vehicle_id => $set) {
            if (isset($set['new'])) {
                $results = [];
                $news = $set['new'];

                if (isset ($news['penta_path']) && !isset ($news['penta_id'])) {
                    $variant_id = $news['penta_path'][0];
                    $part_string = $news['penta_path'][1];
                    $color_id = $news['penta_path'][2];

                    if (isset ($this->penta_mapping[$variant_id][$part_string][$color_id])) {
                        $news['penta_id'] = $this->penta_mapping[$variant_id][$part_string][$color_id];
                        continue;
                    }


                    if (!strncmp($news['penta_number'], 'kein', 4)) {
                        $this->showPentaFormFor = $vehicle_id;
                        return true;
                    }

                    if (isset ($news['penta_number']) && !empty($news['penta_config_id'])) {
                        $penta_id = $this->CreatePentaNumber($news['penta_number'], $variant_id, $color_id, [], $news['penta_config_id']);
                        if ($penta_id) {
                            $this->penta_mapping[$variant_id][$part_string][$color_id] = $penta_id;
                            $news['penta_id'] = $penta_id;
                        }
                    }
                }
            }
        }
        $this->penta_mapping = [];
        return false;
    }

    function ShowPentaForm() {
        if (isset ($this->showPentaFormFor)) {
            $vehicle_id = $this->showPentaFormFor;
            $news = &$this->S_selectedVehicles[$vehicle_id]['new'];
            $name = "pentaform[{$vehicle_id}]";
            echo $this->vehiclesSalesPtr->getPentaForm($news['penta_prefix'], $news['penta_suffix'], $name);
        }
    }

    function CheckPenta(&$set) {
        $news = &$set['new'];

        if (!isset($this->color_keys))
            $this->color_keys = $this->vehiclesPtr->newQuery('colors')->get('color_id=>color_key');

        $variant_id = isset ($news['variant_id']) ? $news['variant_id'] : $set['variant_id'];
        $variant_name = isset ($news['variant_id']) ? $news['windchill'] : $set['windchill'];
        $color_id = isset ($news['color_id']) ? $news['color_id'] : $set['color_id'];
        $color_key = $this->color_keys[$color_id];
        $part_id_list = isset ($news['options_penta']) ? $news['options_penta'] : $set['options_penta'];
        $part_string = implode(',', $part_id_list);


        if (!isset ($this->penta_mapping))
            $this->penta_mapping = [];

        if (isset ($this->penta_mapping[$variant_id][$part_string][$color_id])) {
            $penta_result = $this->penta_mapping[$variant_id][$part_string][$color_id];
        } else {

            $penta_result = $this->vehiclesSalesPtr->getVariantPentaNumber(
                $variant_id, $variant_name,
                $color_id, $color_key,
                $part_id_list);
        }

        $this->penta_mapping[$variant_id][$part_string][$color_id] = $penta_result;
        $news['penta_path'] = [$variant_id, $part_string, $color_id];

        if ($penta_result['existing_penta_id']) {
            $news['penta_number'] = $penta_result['penta_number'];
            $news['penta_id'] = $penta_result['existing_penta_id'];
        } else
            if ($penta_result['edit_penta_number']) {
                $news['penta_id'] = 0;
                $news['penta_prefix'] = $penta_result['penta_number'];
                $news['penta_suffix'] = $penta_result['suffix'];
            } else {
                $news['.lookup']['penta_id'] = $penta_result['penta_number'];
                $news['penta_config_id'] = $penta_result['penta_config_id'];
                $news['penta_id'] = null;
            }
        $this->S_changedColumns['penta_id'] = true;
    }

    // ==============================================================================================
    function MakeOptionStrings(&$set) {
        $set ['options_text'] = "";
        $options_variant = $set ['options_variant'];
        $options_penta = $set ['options_penta'];

        foreach ($options_penta as $part_id) {
            $group_id = $this->part2group[$part_id];
            $exclList = $this->excl_parts[$group_id];
            $options_variant = array_diff($options_variant, $exclList);
        }

        if (isset ($set ['options_variant'])) {
            $set ['options_variant_text'] = $this->MakeOptionString($options_variant);
        }

        if (isset ($set ['options_penta'])) {
            $set ['options_penta_text'] = $this->MakeOptionString($options_penta);
        }

        if ($set ['options_variant_text'] != "") {
            $set ['options_text'] = $set ['options_variant_text'];
            if ($set ['options_penta_text'] != "")
                $set ['options_text'] .= "<br>";
        }
        if ($set ['options_penta_text'] != "")
            $set ['options_text'] .= "+ <i>" . $set ['options_penta_text'] . "</i>";

    }

    // ==============================================================================================
    function DoSave() {
        $this->error = 0;
        $this->errorData = [];
        $setStation0 = [];

        foreach ($this->S_selectedVehicles as $vehicle_id => $set) {
            if ($set['new']) {
                $results = [];
                $news = $set['new'];

                $updateAbleCols = $this->getUpdateCols(array_keys($news));

                if (isset ($updateAbleCols['vehicle_variants']['windchill'])) {
                    unset ($updateAbleCols['vehicle_variants']['windchill']);
                    $updateAbleCols['vehicles']['variant_id'] = 'vehicle_variant';
                    $variant_id = $news['variant_id'];
                }

                $update = [];
                foreach ($updateAbleCols as $table => $db_assoc) {
                    foreach ($db_assoc as $col => $db_column) {
                        if (isset($news[$col])) {
                            $update[$table]['cols'][] = $db_column;
                            $update[$table]['vals'][] = $news[$col];
                        }
                    }
                }

                $updt = &$update['vehicles'];
                if (count($updt['vals'])) {
/*                    $vehicle_values = explode( ',', $news[$col] );
                    $updt['vals'] = $vehicle_values;
                    if (count($updt['vals']) > 1)
                        $update[$table]['cols'] = array('color_id','penta_number_id','vehicle_variant','sub_vehicle_configuration_id','penta_variant_id');*/
                   
                    $results['vehicles'] = $this->vehiclesPtr
                        ->newQuery()
                        ->where('vehicle_id', '=', $vehicle_id)
                        ->update($updt['cols'], $updt['vals']);

                    if (isset ($updateAbleCols['vehicles']['depot'])) {
                        $setStation0[] = $vehicle_id;
                        $this->vehiclesPtr->newQuery('districts')
                            ->where('vehicle_mon', '=', $vehicle_id)
                            ->update(['depot_id'], [$news['depot']]);
                    }

                }
                $vehicle_values = explode( ',', $news[$col] );
                $updt['vals'] = $vehicle_values;                
                if (count($updt['vals']) > 1) {

                    $update[$table]['cols'] = array('color_id','penta_number_id','vehicle_variant','sub_vehicle_configuration_id','penta_variant_id');
                   
                    $results['vehicles'] = $this->vehiclesPtr
                        ->newQuery()
                        ->where('vehicle_id', '=', $vehicle_id)
                        ->update($updt['cols'], $updt['vals']);

                    if (isset ($updateAbleCols['vehicles']['depot'])) {
                        $setStation0[] = $vehicle_id;
                        $this->vehiclesPtr->newQuery('districts')
                            ->where('vehicle_mon', '=', $vehicle_id)
                            ->update(['depot_id'], [$news['depot']]);
                    }

                }

                $updt = &$update['vehicles_sales'];
                if (count($updt['vals'])) {
                    $results['vehicles_sales'] = $this->vehiclesSalesPtr
                        ->newQuery()
                        ->where('vehicle_id', '=', $$vehicle_id)
                        ->update($updt['cols'], $updt['vals']);
                }

                if (isset($news['.lookup'])) {
                    foreach ($news['.lookup'] as $id => $caption)
                        $this->S_selectedVehicles[$vehicle_id]['.lookup'][$id] = $caption;
                }
            }

            $count_errors = 0;
            foreach ($results as $table => $r) {
                if (!$r)
                    $count_errors++;
            }

            if ($count_errors) {
                $this->errorData[$vehicle_id] = $result;
            } else {
                unset ($set['new']);
            }
        }

        if (count($setStation0)) {
            $setStationCsv = implode(",", $setStation0);
            $sql = "UPDATE vehicles SET station_id=null WHERE vehicle_id IN ($setStationCsv)";
            $this->vehiclesPtr->newQuery()->query($sql);
        }

        $this->error = (count($this->errorData)) ? STS_ERROR_DB_UPDATE : 0;
        return $this->error;
    }

    // ==============================================================================================
    function DeleteVehicles() {
        // $strWhere = "where vehicle_id in (". implode (',', array_keys ($this->S_selectedVehicles)) . ')';
        $lWhere = array_keys($this->S_selectedVehicles);
        $numDel = count($lWhere);

        if (!$this->vehiclesSalesPtr->newQuery()->where('vehicle_id', 'IN', $lWhere)->delete())
            return $this->SetError(STS_ERROR_DB_DELETE, $this->vehiclesSalesPtr->GetLastError());

        if (!$this->vehiclesPtr->newQuery()->where('vehicle_id', 'IN', $lWhere)->delete())
            return $this->SetError(STS_ERROR_DB_DELETE, $this->vehiclesSalesPtr->GetLastError());

        $this->SetState(INPUT_STATE_DELETED);

        $this->SetMessage(STS_MESSAGE_SUCCEED, "$numDel Datensätze erfolgreich gelöscht");
    }

    // ==============================================================================================
    function downloadEolStatus() {
        $n = 0;
        $limit = isset ($_REQUEST['limit']) ? $_REQUEST['limit'] : 100;
        $offset = isset ($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
        $von = $offset + 1;
        $bis = $offset + $limit;

        echo <<<HEREDOC
<html>
  <head>
    <meta charset="UTF-8">
    <title>Get Eol Status</title>
  </head>
  <body>
  <h3>Datensätze $von - $bis</h3>
  <table class="noborder transparent">
HEREDOC;


        $toInsert = ['not set' => [], 'ERROR' => [], 'PASSED' => [], 'PASSED*' => [], 'PASSED#' => [], 'DEFECTIVE' => []];
        $toUpdate = ['not set' => [], 'ERROR' => [], 'PASSED' => [], 'PASSED*' => [], 'PASSED#' => [], 'DEFECTIVE' => []];


        $qry = $this->vehiclesPtr->newQuery()
            ->join('vehicle_variants', 'vehicles.vehicle_variant=vehicle_variants.vehicle_variant_id')
            ->join('vehicle_eol_status', 'using (vehicle_id)', 'left outer join')
            ->where('vin', 'like', 'WS%')
            ->orderBy('vin')
            ->limit($limit)
            ->offset($offset);

        $all_vehicles = $qry->get('vehicle_id,vehicles.vin,ikz,code,penta_kennwort,c2cbox,vehicle_variant,windchill_variant_name,finished_status,qmlocked,special_qs_approval,vehicle_variant,color_id,vehicle_eol_status.status as s');


        foreach ($all_vehicles as &$vehicle) {
            try {
                $this->vehiclesPtr->diagnoseDetails($vehicle);
            } catch (Exception $e) {
                $vehicle['diagnose_status'] = 'ERROR';

            }
            $vid = $vehicle['vehicle_id'];
            $vin = $vehicle['vin'];
            $status = $vehicle['diagnose_status'];
            if ($status == '')
                $status = 'not set';

            if (isset($vehicle['s']))
                $toUpdate[$status][] = $vehicle['vehicle_id'];
            else
                $toInsert[$status][$vid] = $vehicle['vin'];

            if ($n == 0)
                echo "<tr>";

            echo "<td>$vin</td><td>=</td><td style=\"width: 100px\">$status</td><td style=\"width: 100px\">&nbsp;</td>";
            $n++;

            if ($n == 3) {
                echo "</tr>\n";
                $n = 0;
            }
        }

        if ($n > 0)
            echo "</tr>\n";

        $strList = "";
        foreach ($toInsert as $status => &$list) {
            foreach ($list as $vehicle_id => $vin) {
                if (!empty($strList))
                    $strList .= ", ";

                $strList .= "\n($vehicle_id, '$vin', '$status')";
            }
        }

        if (!empty ($strList)) {
            $qry = $this->vehiclesPtr->newQuery();
            $res = $this->vehiclesPtr->query("insert into vehicle_eol_status (vehicle_id, vin, status) values $strList;");
        }

        foreach ($toUpdate as $status => &$list) {
            if (count($list)) {
                $qry = $this->vehiclesPtr->newQuery();
                $strList = implode(',', $list);
                $res = $this->vehiclesPtr->query("update vehicle_eol_status set status = '$status' where vehicle_id in ($strList);");
            }
        }

        echo <<<HEREDOC
    </table>
    <a href="{$_SERVER['PHP_SELF']}?action={$this->action}&command=download-status&offset=$bis">weiter</a>
  </body>
</html>
HEREDOC;
        exit;
    }

    // ==============================================================================================
    function createLieferschein($vehicle_ids) {
        if (count($vehicle_ids)) {
            $vehicles = $this->vehiclesSalesPtr->newQuery('vehicles')
                ->where("vehicle_id", "IN", $vehicle_ids)
                ->join('colors', 'vehicles.color_id=colors.color_id', 'FULL OUTER JOIN')
                ->orderBy('vehicle_id', 'ASC')
                ->get('vehicle_id,vin,code,ikz,colors.name as colorname');

            $llw = $this->controller->GetObject('ladeLeitWarte');

            /*
            $sales_protokoll      = new SalesProtokoll($vehicles,$llw);
            $this->protocol_fname = $sales_protokoll->getPdfName();
      
            $this->pentacsv_fname = $this->savexportcsv($vehicle_ids);
            */
        }

    }

    // ==============================================================================================
    function savexportcsv($vehicle_ids) {
        $vehicleHeadings = array('tsnumber' => 'TS Nummer',
            'penta_kennwort' => 'Penta Kennwort',
            'penta_number' => 'Penta Variante',
            'vin' => 'VIN',
            'code' => 'Kennzeichen',
            'ikz' => 'IKZ',
            'delivery_date' => 'Auslieferungsdatum',
            'delivery_week' => 'Auslieferungswoche',
            'coc' => 'CoC Nr.',
            'vorhaben' => 'Vorhaben',
            'dname' => 'Zugeordnet ZSP'
        );
        $csv_cols = array_keys($vehicleHeadings);
        $db_cols = array_keys($vehicleHeadings);
        foreach ($db_cols as &$selectCol) {
            if ($selectCol == 'dname') $selectCol = 'depots.name as dname';
            if ($selectCol == 'vin') $selectCol = 'vehicles.vin';
            if ($selectCol == 'code') $selectCol = 'vehicles.code';
            if ($selectCol == 'ikz') $selectCol = 'vehicles.ikz';
        }

        $vehicles = $this->vehiclesSalesPtr->getVehicleOverview($db_cols, $vehicle_ids, 'ASC');

        $fname = "/tmp/vin_numbers.csv";
        $fhandle = fopen($fname, "w");
        $fcontent = array();
        $fcontent[] = implode(',', $vehicleHeadings);

        if (!empty ($vehicles)) {
            foreach ($vehicles as &$vehicle) {
                $processedVehicle = array();
                foreach ($csv_cols as $column_name) {
                    $processedVehicle[$column_name] = $vehicle[$column_name];

                }

                $fcontent[] = implode(",", $processedVehicle);
            }

            fwrite($fhandle, implode("\r\n", $fcontent) . "\r\n");
            fclose($fhandle);
            return '<a href="/downloadcsv.php?fname=vin_numbers">Penta CSV Datei herunterladen</a>';
        }
    }

    // ==============================================================================================
    function ChangeSelectedToVariant($to_variant_id) {

        // $this->S_data['set_variant'] = $to_variant_id;

        /*
        $query = $this->vehicleVariantsPtr->newQuery();
        $query->where ('vehicle_variant_id', "=", $to_variant);
        $result = $query->get_no_parse ('default_color, array (select name from variant_parts_mapping '.
                                        'inner join parts on variant_parts_mapping.part_id = parts.part_id '.
                                        "where variant_parts_mapping.variant_id=$to_variant) as parts");
        */

        $variant_changed = false;
        $color_changed = false;
        $options_changed = false;

        $new_variant = $this->vehicleVariants[$to_variant_id];
        $new_variant_parts = $new_variant['parts'];
        $default_color_id = $new_variant['default_color'];
        $default_color_str = $this->allColors[$default_color_id];

        foreach ($this->S_selectedVehicles as $vid => &$set) {
            $old_variant_id = $set['variant_id'];
            if ($old_variant_id != $to_variant_id)
                $variant_changed = true;

            $old_variant = $this->vehicleVariants[$old_variant_id];
            $old_variant_parts = $old_variant['parts'];

            if ($to_variant_id) {
                $set['new']['variant_id'] = $to_variant_id;
                $set['new']['windchill'] = $new_variant['windchill_variant_name'];
            } else {
                unset ($set['new']['variant_id']);
                unset ($set['new']['windchill']);
            }

            if ($set['color'] != $default_color_str) {
                $set['new']['color_id'] = $default_color_id;
                $set['new']['color'] = $default_color_str;
                $color_changed = true;
            }

            if ($old_variant_parts != $new_variant_parts) {
                $new_parts = explode(",", substr($new_variant_parts, 1, -1));
                if (isset ($set['options_penta']))
                    $set['new']['options_penta'] = $set['options_penta'];

                $set['new']['options_variant'] = $new_parts;
                $this->MakeOptionStrings($set ['new']);
                $options_changed = true;
            }

            if ($variant_changed || $color_changed || $options_changed)
                $this->CheckPenta($set);
        }

        if ($variant_changed)
            $this->S_changedColumns['windchill'] = true;

        if ($color_changed)
            $this->S_changedColumns['color_id'] = true;

        if ($options_changed)
            $this->S_changedColumns['options'] = true;

    }

    // ==============================================================================================
    function ChangeColor($to_color) {
        $color_changed = false;
        $to_color_str = $this->allColors[$to_color];

        foreach ($this->S_selectedVehicles as $vid => &$set) {
            $set['new']['color_id'] = $to_color;
            $set['new']['color'] = $to_color_str;

            if ($set['color_id'] != $to_color) {
                $color_changed = true;
                $this->CheckPenta($set);
            }
        }
        if ($color_changed)
            $this->S_changedColumns['color_id'] = true;
    }

    // ==============================================================================================
    function ChangeLocation($to_location) {
        $location_changed = false;
        $prodLoc = array_keys($this->prodLocation);

        if (isset ($this->primaryLocations[$to_location])) {
            $to_location_str = $this->primaryLocations[$to_location];
        } else {
            $to_location_str = $_REQUEST['othername'];
            $this->primaryLocations[$to_location] = $to_location_str;
            $this->colConfig['depot']['.lookup'][$to_location] = $to_location_str;
        }

        foreach ($this->S_selectedVehicles as $vid => &$set) {
            if (in_array($set['depot'], $prodLoc) && !in_array($to_location, $prodLoc))
                continue;

            if ($set['depot'] != $to_location) {
                $set['new']['depot'] = $to_location;
                $set['new']['.lookup']['depot'] = $to_location_str;
                $location_changed = true;
            }
        }

        if ($location_changed)
            $this->S_changedColumns['depot'] = true;

    }

    // ==============================================================================================
    function AddSpecialEquip($partid) {
        $equip_changed = false;

        if (!$partid)
            return;

        foreach ($this->S_selectedVehicles as $vid => &$set) {
            if (!in_array($partid, $set ['options'])) {
                if (!isset ($set ['new']['options'])) {
                    $set ['new']['options_variant'] = $set ['options_variant'];
                    $set ['new']['options_penta'] = $set ['options_penta'];
                }

                $set ['new']['options_penta'][] = $partid;
                $set ['new']['options'] = array_merge($set ['new']['options_variant'], $set ['new']['options_penta']);
                $this->MakeOptionStrings($set ['new']);
                $equip_changed = true;

                $this->CheckPenta($set);
            }
        }

        if ($equip_changed)
            $this->S_changedColumns['options'] = true;

    }

    // ==============================================================================================
    function RemoveSpecialEquip($partid) {
        $equip_changed = false;

        if (!$partid)
            return;

        foreach ($this->S_selectedVehicles as $vid => &$set) {
            if (!isset ($set ['new']['options_penta'])) {
                $set ['new']['options_variant'] = $set ['options_variant'];
                $set ['new']['options_penta'] = $set ['options_penta'];
            }


            if (in_array($partid, $set ['new']['options_penta'])) {
                $i = array_search($partid, $set ['new']['options_penta']);
                array_splice($set ['new']['options_penta'], $i, 1);

                $set ['new']['options'] = array_merge($set ['new']['options_variant'], $set ['new']['options_penta']);
                $this->MakeOptionStrings($set ['new']);
                $equip_changed = true;

                $this->CheckPenta($set);
            }

            /*
            if (in_array($partid, $set ['new']['options_variant']))
            {
                $set ['new']['options_penta'][] = -$partid;
                $set ['new']['options'] = array_merge ($set ['new']['options_variant'], $set ['new']['options_penta']);
      
                $this->MakeOptionStrings ($set ['new']);
                $equip_changed = true;
      
                $this->CheckPenta ($set);
            }
            */
        }

        if ($equip_changed)
            $this->S_changedColumns['options'] = true;

    }

    // ==============================================================================================
    function GetSelectedFromCsv() {
        $this->errorData = [];

        $this->S_identCol = $this->csvTool->GetIdentCol();
        $this->S_updateCols = $this->csvTool->GetDataCols();
        $allIdents = $this->csvTool->GetIdentValueList();
        // $numRows    = $this->csvTool->GetNumRows  ();

        if (!$this->S_identCol)
            return STS_ERROR_NO_IDENT;

        $ident = $this->S_identCol;
        $identDbCol = $this->GetColumnDbName($ident);
        $csvCols = $this->S_updateCols;
        $this->selectCols = $this->getSelectCols();

        if (($err = $this->UpdateColumnInfo($this->selectCols, $csvCols, $ident)) != STS_NO_ERROR)
            return $err;

        $usedCols = implode(',', array_values($this->selectCols));


        foreach ($allIdents as $row => $csvid) {
            $query = $this->vehiclesPtr->newQuery();
            $query = $this->vehiclesPtr->AutoJoin($query, $usedCols);
            $query = $query->where($identDbCol, '=', $csvid);
            $res = $query->get_no_parse($this->vehiclesPtr->AsColumn($this->selectCols));
            if (count($res)) {
                $vid = $res[0]['vehicle_id'];
                $this->S_currentVehicles[$vid] = &$res[0];
                $set = &$this->S_currentVehicles[$vid];
                $set['selected'] = true;

                $set['new'] = [];
                $news = &$set['new'];

                $update = $this->csvTool->GetDataLine($row);
                foreach ($this->S_updateCols as $pos => $col) {
                    $news[$col] = $update [$pos];
                }
            }
        }


        $this->UpdateCurrentVehicles();
        $this->S_selectedVehicles = &$this->S_currentVehicles;


        // $this->getVehicleData ($this->S_where, $limit, $offset, 'vehicles.vehicle_id desc', $this->selectCols);


        // $query = $query->Where

        return STS_NO_ERROR;
    }

    // ==============================================================================================
    function ExecuteCommand($command) {
        switch ($command) {
            case 'delete1':
                $this->SetState(INPUT_STATE_DELETE_1);
                break;

            case 'delete2':
                $this->DeleteVehicles();
                break;

            case 'edit1':
                $this->SetState(INPUT_STATE_EDIT_1);
                break;

            case 'color':
                $this->ChangeColor($_POST['color_to']);
                break;

            case 'location':
                $this->ChangeLocation($_POST['to_location']);
                break;

            case 'add_option':
                $this->AddSpecialEquip($_POST['equip_to_add']);
                break;

            case 'rm_option':
                $this->RemoveSpecialEquip($_POST['equip_to_add']);
                break;

            case 'change':
                $this->ChangeSelectedToVariant($_POST['convert_to']);
                // $this->SetState (INPUT_STATE_EDIT_1);
                break;

            case 'save':
                if ($this->CheckAndCreatePentaNumbers())
                    return;

                if ($this->DoSave() == STS_NO_ERROR)
                    $this->SetState(INPUT_STATE_SAVE_PRINT);
                break;

            case 'exports':
                $this->SetState(INPUT_STATE_SAVE_PRINT);
                break;

            case 'cancel':
                break;

            case 'add':
                $this->CreateSortedLists($fzliste['typ']['soll']);
                // $this->InputState = INPUT_STATE_VIN_LIST;
                break;

            case 'table':
                $this->execMode = TABLE_VEHICLES_TABLESELECT;
                $this->InputState = INPUT_STATE_SELECT;
                break;


            case 'download-status':
                $this->downloadEolStatus();
                break;

            default:
                return parent::ExecuteCommand($command);
        }
        return true;
    }

    // ==============================================================================================
    function UpdateCurrentVehicles() {
        parent::UpdateCurrentVehicles();

//         $this->CurrentIDs = [];

        foreach ($this->S_currentVehicles as &$vehicle) {
            if (isset ($vehicle ['options_variant'])) {
                $vehicle ['options_variant'] = $this->MakeOptionArray($vehicle ['options_variant']);
            }

            if (isset ($vehicle ['options_penta'])) {
                $vehicle ['options_penta'] = $this->MakeOptionArray($vehicle ['options_penta']);
            }

            $vehicle ['options'] = array_merge($vehicle ['options_variant'], $vehicle ['options_penta']);
            $this->MakeOptionStrings($vehicle);

            if (isset ($vehicle['display_depot'])) {
                $vehicle['.lookup']['depot'] = $vehicle['display_depot'];
                unset ($vehicle['display_depot']);
            }
            if (isset ($vehicle ['depot'])) {
                $depot_id = $vehicle ['depot'];
                if (isset($this->primaryLocations[$depot_id]))
                    $vehicle ['prodloc'] = $this->primaryLocations[$depot_id];
            }
        }
    }

    // ==============================================================================================
    function PreExecute() {
        try {
            parent::PreExecute();


        } catch (Exception $E) {
            $this->SetError(STS_ERROR_PHP_EXCEPTION, $E);
        }

    }

    // ==============================================================================================
    function SetupHeaderFiles($displayheader) {
        parent::SetupHeaderFiles($displayheader);
        $this->displayheader->enqueueLocalStyle("
            .buttontable button {min-width: 200px; }
            .buttontable input {min-width: 200px; }
        ");
    }

    // ==============================================================================================
    function GetHtmlSelectOptions_SelectVariants($content) {
        $retString = "";

        foreach ($this->vehicleConfigurations as $id => $set) {
            // array('color_id','penta_number_id','vehicle_variant','sub_vehicle_configuration_id','penta_variant_id');
            // $shortVehicleConfiguration = $set['short_production_description'];
            // if ($shortVehicleConfiguration)
            //     $option = $set['short_production_description'];
            // else
            $option = $set['sub_vehicle_configuration_name'];

            $id = $set['old_vehicle_variant_id'];
            $sub_id = $set['sub_vehicle_configuration_id'];
            $configurationColor_id = $set['configuration_color_id'];
            $pentaVariant_id = $set['penta_variant_id'];
            $pentaNumber_id = $set['old_penta_number_id'];
            $selected = (strcasecmp($content, $option) == 0) ? " selected" : "";
            $retString .= "<option value=\"$configurationColor_id,$pentaNumber_id,$id,$sub_id,$pentaVariant_id\"$selected>$option</option>";
        }
        return $retString;
    }

    // ==============================================================================================
    function GetHtmlElement_SelectVariant($dataRow, $content, $idCol, $id, $attr) {
        $original_id = $dataRow['vehicle_variant'];

        // Ueberpruefe, ob Fahrzeug temporaer geaendert wurde
        if (isset($this->S_changed_variants[$id])) {
            // orginalwert ueberschreiben
            $variant_id = $this->S_changed_variants[$id];
            $content = $this->vehicleVariants[$variant_id]['windchill_variant_name'];

            $htmlExtra .= ' style="border-color:blue;font-weight:bold;"';
        }
        return "<input type=\"hidden\" id=\"init_$id\" value=\"$original_id\"><select$htmlExtra OnChange=\"javascript: DataChange(this, $id)\" $attr>" . $this->GetHTMLSelectOptions_SelectVariants($content) . "</select>";
    }

    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    function GetHtmlElement_ShowVariant($dataRow, $content, $idCol, $id, $attr) {
        // Ueberpruefe, ob Fahrzeug temporaer geaendert wurde
        if (isset($this->S_selectedVehicles[$id])) {
            // orginalwert ueberschreiben
            $variant_id = $this->S_selectedVehicles[$id][$idCol];
            $dataRow['new'][$idCol] = $this->vehicleVariants[$variant_id]['windchill_variant_name'];
        }
        return $this->GetHtmlElement_ChangingTo($dataRow, $content, $idCol, $id, $attr);
    }

    // ==============================================================================================
    function GetHtmlElement_ShowColors($dataRow, $content, $idCol, $id, $attr) {
        return $this->GetHtmlElement_Default($dataRow, $this->allColors[$content], $idCol, $id, $attr);
    }

    // ==============================================================================================
    function GetHtmlSelectOptions_Color($content = false) {
        $strResult = "";
        foreach ($this->allColors as $idColor => $color) {
            $strResult .= "  <option value=\"$idColor\"" . (($idColor == $content) ? " selected" : "") . ">$color</option>";
        }
        return $strResult;
    }

    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    function GetHtmlElement_SelectColors($dataRow, $content, $idCol, $id, $attr) {
        $strResult = "<select name=\"Color[$id]\"$attr>";
        $strResult .= $this->GetHtmlSelectOptions_Color($content);
        $strResult .= "</select>";
        return $strResult;
    }

    // ==============================================================================================
    function GetHtmlElement_SpecialEquip($selected = []) {
        $strResult = "";
        foreach ($this->allParts as $idPart => $part) {
            if (!isset ($selected[$idPart]))
                $strResult .= "  <option value=\"$idPart\">$part</option>";
        }
        return $strResult;
    }

    // ==============================================================================================
    function GetHtmlElement_ShowOptions($dataRow, $content, $idCol, $id, $attr) {
        $options = "";
        foreach ($dataRow ['options'] as $pn => $mode) {
            if ($mode) {
                if (strlen($options) > 0)
                    $options .= '<br>';

                $options .= $this->allParts [$pn];
            }
        }
        return "$options";
    }

    // ==============================================================================================
    function GetHtmlElement_CheckOptions($dataRow, $content, $idCol, $id, $attr) {
        $strResult = '<span style="white-space:nowrap;">';
        $n = 0;
        foreach ($this->allParts as $idPart => $name) {
            if ($n > 0) $strResult .= "<br>";
            $strResult .= "<span class=\"optionsSpc\">";

            switch ($dataRow ['options'][$idPart]) {
                case 0:
                default:
                    $strResult .= "<input type = \"checkbox\" name=\"option[$id][$idPart]\" />";
                    break;

                case 1:
                    $strResult .= "<input type = \"checkbox\" name=\"option[$id][$idPart]\" checked />";
                    break;

                case 2:
                    $strResult .= "<b>[X]</b>&nbsp;";
                    break;
            }
            $strResult .= "</span>$name";
            $n++;
        }
        $strResult .= "<span>\n";
        return $strResult;
    }

    // ==============================================================================================
    function GetHtmlSelectOptions_ProdLoc($content = false) {
        $strResult = "";
        foreach ($this->primaryLocations as $depot_id => $name) {
            $strResult .= "  <option value=\"$depot_id\"" . (($depot_id == $content) ? " selected" : "") . ">$name</option>";
        }
        return $strResult;
    }

    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    function GetHtmlElement_SelectProdLoc($dataRow, $content, $idCol, $id, $attr) {
        $strResult = "<select name=\"ProdLoc[$id]\"$attr>";
        $strResult .= $this->GetHtmlSelectOptions_ProdLoc($content);
        $strResult .= "</select>";
        return $strResult;
    }

    // ==============================================================================================
    function GetHtmlElement_InputPenta($dataRow, $content, $idCol, $id, $attr) {
        return "<input type=\"text\" name=\"penta[$id]\" required$attr>";
    }

    // ==============================================================================================

    function WriteHtmlContent($options = "") {
        global $_SERVER, $_POST;

        parent::WriteHtmlContent($options);

        if (isset ($this->showPentaFormFor))
            return $this->ShowPentaForm();

        switch ($this->execMode) {
            case TABLE_VEHICLES_UNDEFINED:
                include $_SERVER['STS_ROOT'] . "/actions/Sales/Changevariant/Changevariant.csv_input.php";
                break;

            case TABLE_VEHICLES_CVS_UPDATE:
                if ($this->InputState < INPUT_STATE_EDIT_1) {
                    include $_SERVER['STS_ROOT'] . "/actions/Sales/Changevariant/Changevariant.csv_step1+2.php";
                    break;
                }
            /* no break; */

            case TABLE_VEHICLES_TABLESELECT:
                include $_SERVER['STS_ROOT'] . "/actions/Sales/Changevariant/Changevariant.table.php";
                break;
        }
    }
    // ==============================================================================================
}

?>

