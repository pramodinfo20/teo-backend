<?php
/**
 * vehicles.class.php
 * Klasse für alle vehicles
 * @author Pradeep Mohan
 */

/**
 * Class to handle vehicles
 *
 */
class VehiclesSales extends LadeLeitWarte {
    protected $dataSrcPtr;


    const FOUND_PENTA_NO_PARTS = 0;
    const FOUND_PENTA_LESS_PARTS = 1;
    const FOUND_PENTA_OLNY_PARTS = 2;
    const FOUND_PENTA_PARTS_AND_COLOR = 3;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

    /**
     * Ajax function used for the sort and filter functions in Fahrzeug Übersicht
     * Original ajax url specified in sts-custom-sales.js : index.php?action=ajaxRows&page={page}&size={size}&{filterList:filter}&{sortList:column}
     * Processed ajax url (for example): index.php?action=ajaxRows&page=0&size=25&filter[2]=D16&column[0]=1 where 1 is DESC and 0 is ASC
     */


    function populateTable($headers, $page = 0, $size = 25, $fcol = null, $scol = null) {
        $result = $this->newQuery()->join('vehicles', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN');

        if (!empty($fcol)) {
            foreach ($fcol as $key => $val) {
// 				if($headers[$key]=='vehicles.vehicle_id')
                $result->where($headers[$key] . '::text', 'ILIKE', '%' . $val . '%');
// 				else
// 					$result->where($headers[$key],'ILIKE','%'.$val.'%');

            }
        }

        if (!empty($scol)) {

            foreach ($scol as $key => $val) {
                if ($val == 1) $sortorder = 'DESC';
                else $sortorder = 'ASC';
                $result->orderBy($headers[$key], $sortorder);
            }


        } else
            $result->orderBy('vehicles.vehicle_id', 'DESC');

        return $result->offset($page * $size)->limit($size)->get(implode(',', $headers));

    }

    function incrementPentaNumber(&$penta_nummer) {
        $s = strlen($penta_nummer);
        $p = $s - 1;
        if ($p < 0)
            return false;

        $n = 0;
        while (($p >= 0) && (ord($penta_nummer[$p]) >= ord('0')) && (ord($penta_nummer[$p]) <= ord('9'))) {
            $n++;
            $p--;
        }
        if ($n == 0) {
            $penta_nummer .= '_01';
            return true;
        }

        $p++;

        $f = '%0' . $n . 'd';
        $n = intval(substr($penta_nummer, $p));
        $uffix = sprintf($f, $n + 1);
        $penta_nummer = substr($penta_nummer, 0, $p) . $uffix;
        return true;
    }
    // =======================================================================================
    /*
    function createVariantPentaNumber (&$penta_nummer, $variant_id, $windchill_name, $color_id, $this_parts, $createTemp=false)
      {
        $pentaResult = 0;

        if ($createTemp && empty($penta_nummer))
        {
            $most_similar_set = [];
            $pentaResult = $this->getVariant**PentaNumber ($most_similar_set, $variant_id, $color_id, $this_parts);
        }

        if (substr($penta_nummer, 0,1)=='(')
        {
           $penta_nummer = substr($penta_nummer, 1,-1);
           if (!$this->incrementPentaNumber ($penta_nummer))
               return false;
        }

        $numberExists = true;
          while ($numberExists)
          {
              $query = $this->newQuery('penta_numbers')->where ('penta_number', '=', $penta_nummer);
              $numberExists = ($query->getVal('penta_number_id')>1);
              if ($numberExists)
              {
                  if (!$createTemp || !$this->incrementPentaNumber ($penta_nummer))
                      return false;
              }
          }


        $insert = array(
                   'penta_number'      =>$penta_nummer,
                   'vehicle_variant_id'=>$variant_id,
                   'color_id'          =>$color_id );
          //@todo: 2017-09-12 CODE DUPLICATION! Also exists in SalesProtokoll.class.php, bitte optimieren
       $query = $this->newQuery('penta_numbers');
       $penta_id = $query->insert ($insert);

       if ($penta_id===true)
       {
           $penta_id = $query->where('penta_number', '=', $penta_nummer)->getVal('penta_number_id');
           if ($penta_id < 1)
              return false;
       }

       if (count ($this_parts))
       {
           $insert = array('penta_number_id'=>$penta_id);
           $query  = $this->newQuery('penta_number_parts_mapping');
           foreach ($this_parts as $part_id)
           {
               $insert['part_id'] = $part_id;
               $query->insert ($insert);
           }
       }
       return true;
    }
    // =======================================================================================
    protected function EqualParts ($this_parts, $parts_db)
    {
        $count_diff = 0;
        while (count($this_parts) && count($parts_db))
        {
            if ($this_parts[0] == $parts_db[0])
            {
                array_shift ($this_parts);
                array_shift ($parts_db);
            }
            else
                if ($parts_db[0] > $this_parts[0])
                {
                    $count_diff++;
                    array_shift ($this_parts);
                }
            else
                return 0x7fffffff;
        }
        if (count($parts_db))
            return 0x7fffffff;

            return $count_diff + count ($this_parts);
    }

    */

    function getPentaForm($penta_number, $penta_suffix, $varset = "pentaform") {

        unset ($_REQUEST[$varset]['suffix']);
        unset ($_REQUEST[$varset]['create_regular']);
        unset ($_REQUEST[$varset]['create_prototype']);

        $forwarded = forwardPostVarsAsHiddenInput();

        return <<<HEREDOC
       <form action="{$_SERVER['PHP_SELF']}" method="post">$forwarded
         <div class="PentaForm">
           <h2>Penta-Nummer</h2>
	       Für diese Fahrzeugkonfiguration ist keine Produktnummer im Penta-System registriert!<br>
	       Sie können hier nun eine neue Penta-Nummer vergeben, oder für Prototypen, mit eine fahzeugspezifische Nummer fortfahren.<br>
           <br>
           <p>Neue Penta-Nummer:<br>
             <b>{$penta_number}</b> <input type="text" name="{$varset}[suffix]" style="width:140px; margin-left: 4px;" value="{$penta_suffix}" >
             <input type="submit" name="{$varset}[create_regular]" value="Pentanummer anlegen">
           </p>
           <input type="submit" name="{$varset}[create_prototype]" value="Pentanummer für Prototyp erstellen">
         </div>

         </form>
HEREDOC;
    }

    // New Vehicle Configuration
    function getVariantPentaNumber($windchill_variant_id, $windchill_variant_name, $color_id, $color_key, $vehicle_parts) {
        $variant_name = $windchill_variant_name;
        $variant_name_len = strlen($variant_name);
        $vehicle_penta_name = sprintf("%s_%s", $variant_name, $color_key);
        $vehicle_penta_suffix = "";


        if (empty ($vehicle_parts)) {
            $has_special_parts = false;
            $part_string = "{}";
        } else {
            sort($vehicle_parts);
            $has_special_parts = count($vehicle_parts) > 0;
            $part_string = '{' . implode(',', $vehicle_parts) . '}';
            $vehicle_penta_suffix = ($has_special_parts) ? "_01" : "";
        }

        $searchResult = [
            'edit_penta_number' => $has_special_parts,
            'existing_penta_id' => false,
            'penta_number' => $vehicle_penta_name,
            'penta_config_id' => 0,
            'suffix' => $vehicle_penta_suffix,
            'color_id' => $color_id];


        $sql = "
SELECT penta_variant_id,penta_variant_name,configuration_color_id, sub_vehicle_configuration_id
FROM vehicle_configurations
WHERE vehicle_configuration_id={$windchill_variant_id};";


        /*$penta_data = null;
        $result = $this->newQuery()->query($sql);
        if ($result)
            $penta_data = $this->fetchAssoc('penta_number_id');

        if (empty ($penta_data))
            return $searchResult;


        $top_penta_suffix = 0;
        $matching_parts_color = 0;
        $matching_parts = 0;
        $matching_suffix = "";


        $reg = "^{$variant_name}_[A-Z]{2}(|_[0-9]{2})\$";

        foreach ($penta_data as $penta_id => $set) {
            if (preg_match("/$reg/i", $set['penta_number'], $found)) {
                if ($set['part_string'] == $part_string) {
                    if ($set['color_id'] == $color_id) {
                        $matching_parts_color = $penta_id;
                        break;
                    }

                    if (!$matching_parts) {
                        $matching_parts = $penta_id;
                        $matching_suffix = safe_val($found, 1, "");
                    }
                }

                if (!empty ($found[1])) {
                    $n = intval(substr($found[1], 1));
                    if ($n > $top_penta_suffix)
                        $top_penta_suffix = $n;
                }
            }
        }*/

/*        if ($matching_parts_color) {
            $searchResult['edit_penta_number'] = false;
            $searchResult['existing_penta_id'] = $matching_parts_color;
            $searchResult['penta_number'] = $penta_data[$matching_parts_color]['penta_number'];
            $serachResult['penta_config_id'] = $penta_data[$matching_parts_color]['penta_config_id'];
            $searchResult['suffix'] = "";
            return $searchResult;
        }*/

        if ($matching_parts) {
            $searchResult['edit_penta_number'] = false;
            $searchResult['penta_number'] = $vehicle_penta_name . $matching_suffix;
            $searchResult['penta_config_id'] = $penta_data[$matching_parts]['penta_config_id'];
            return $searchResult;
        }

/*        if ($has_special_parts)
            $searchResult['suffix'] = sprintf("_%02d", $top_penta_suffix + 1);
        else
            $searchResult['suffix'] = "";*/
        return $searchResult;
    }

/*
    function getVariantPentaNumber($windchill_variant_id, $windchill_variant_name, $color_id, $color_key, $vehicle_parts) {
        $variant_name = $windchill_variant_name;
        $variant_name_len = strlen($variant_name);
        $vehicle_penta_name = sprintf("%s_%s", $variant_name, $color_key);
        $vehicle_penta_suffix = "";


        if (empty ($vehicle_parts)) {
            $has_special_parts = false;
            $part_string = "{}";
        } else {
            sort($vehicle_parts);
            $has_special_parts = count($vehicle_parts) > 0;
            $part_string = '{' . implode(',', $vehicle_parts) . '}';
            $vehicle_penta_suffix = ($has_special_parts) ? "_01" : "";
        }

        $searchResult = [
            'edit_penta_number' => $has_special_parts,
            'existing_penta_id' => false,
            'penta_number' => $vehicle_penta_name,
            'penta_config_id' => 0,
            'suffix' => $vehicle_penta_suffix,
            'color_id' => $color_id];


        $sql = "
SELECT penta_numbers.penta_number_id,penta_number,color_id, penta_config_id,
array (
		SELECT part_id
		FROM penta_number_parts_mapping
        WHERE penta_number_parts_mapping.penta_number_id = penta_numbers.penta_number_id
        ORDER BY part_id
) as part_string
FROM penta_numbers
WHERE vehicle_variant_id={$windchill_variant_id};";


        $penta_data = null;
        $result = $this->newQuery()->query($sql);
        if ($result)
            $penta_data = $this->fetchAssoc('penta_number_id');

        if (empty ($penta_data))
            return $searchResult;


        $top_penta_suffix = 0;
        $matching_parts_color = 0;
        $matching_parts = 0;
        $matching_suffix = "";


        $reg = "^{$variant_name}_[A-Z]{2}(|_[0-9]{2})\$";

        foreach ($penta_data as $penta_id => $set) {
            if (preg_match("/$reg/i", $set['penta_number'], $found)) {
                if ($set['part_string'] == $part_string) {
                    if ($set['color_id'] == $color_id) {
                        $matching_parts_color = $penta_id;
                        break;
                    }

                    if (!$matching_parts) {
                        $matching_parts = $penta_id;
                        $matching_suffix = safe_val($found, 1, "");
                    }
                }

                if (!empty ($found[1])) {
                    $n = intval(substr($found[1], 1));
                    if ($n > $top_penta_suffix)
                        $top_penta_suffix = $n;
                }
            }
        }

        if ($matching_parts_color) {
            $searchResult['edit_penta_number'] = false;
            $searchResult['existing_penta_id'] = $matching_parts_color;
            $searchResult['penta_number'] = $penta_data[$matching_parts_color]['penta_number'];
            $serachResult['penta_config_id'] = $penta_data[$matching_parts_color]['penta_config_id'];
            $searchResult['suffix'] = "";
            return $searchResult;
        }

        if ($matching_parts) {
            $searchResult['edit_penta_number'] = false;
            $searchResult['penta_number'] = $vehicle_penta_name . $matching_suffix;
            $searchResult['penta_config_id'] = $penta_data[$matching_parts]['penta_config_id'];
            return $searchResult;
        }

        if ($has_special_parts)
            $searchResult['suffix'] = sprintf("_%02d", $top_penta_suffix + 1);
        else
            $searchResult['suffix'] = "";
        return $searchResult;
    }
*/

    /*
  function getVehiclePentaNumber (&$most_similar_set, $vehicle_id, $color_id, $variant_id)
    {
        //@todo: 2017-09-12 CODE DUPLICATION! Also exists in SalesProtokoll.class.php, bitte optimieren
        $parts_for_penta = $this->newQuery('options_at_vehicles')
                                ->where ('vehicle_id',          '=', $vehicle_id)
                                ->get   ('part_id');

        if (is_array ($parts_for_penta))
            $parts = array_column ($parts_for_penta, 'part_id');
        else
            $parts = array();

        $pentaResult = $this->getVariant**PentaNumber ($most_similar_set, $variant_id, $color_id, $parts);
        return $pentaResult;
    }
    */

    /**
     * returns the results of a INNER JOIN on the vehicles table
     * {@inheritDoc}
     * @see LadeLeitWarte::getAll()
     */
    function getVehicleOverview($selectCols = '', $vehicleIds = null, $order = 'DESC') {
        if (empty($vehicleIds))
            return;

        $selectCols[] = 'vehicles.vehicle_id';
        $selectCols[] = 'vehicles.color_id';
        $selectCols[] = 'vehicles.vehicle_variant';

        $query = $this->newQuery()
            ->join('vehicles', 'vehicles.vehicle_id=vehicles_sales.vehicle_id', 'FULL OUTER JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN');

        if (array_search('penta_number', $selectCols) !== FALSE)
            $query = $query->join('penta_numbers', 'vehicles.penta_number_id=penta_numbers.penta_number_id');


        $query = $query->where('vehicles.vehicle_id', 'IN', $vehicleIds);
        $vehicles = $query->get(implode(',', $selectCols));

        return $vehicles;
    }

    // ===============================================================================================================================================================================

    function processSelectedCols($selectCols) {
        if (is_array($selectCols)) {
            $selectCols = implode(',', $selectCols);
        }

        if (strtolower($selectCols) == 'default') {
            $selectCols = 'vehicles_sales.vehicle_id,' .
                'vehicles.vin,' .
                'vehicles_sales.vehicle_variant' .
                'vehicles_sales.akz,' .
                'vehicles_sales.ikz,' .
                'vehicles_sales.delivery_status,' .
                'vehicles.finished_status,';
        }
        return $selectCols;
    }

    // ===============================================================================================================================================================================

    /**
     * returns the results of a INNER JOIN on the vehicles table
     * {@inheritDoc}
     * @see LadeLeitWarte::getAll()
     */
    function getQueryByUserWhere($whereUser, $selectCols, $limit = 0, $offset = 0, $order = '') {
        $ptrQuery = $this->newQuery();
        $ptrWhere = $ptrQuery;
        $usedCols = "";


        $usedCols = $selectCols;

        if ($limit > 0) {
            $ptrWhere = $ptrWhere->limit_direct($limit);
        }

        if ($offset > 0) {
            $ptrWhere = $ptrWhere->offset_direct($offset);
        }

        if ($order != '') {
            if (strtolower(substr($order, -5, 5)) == ' desc') {
                $order = substr($order, 0, -5);
                $ptrWhere = $ptrWhere->orderBy($order, 'desc');
            } else {
                $ptrWhere = $ptrWhere->orderBy($order);
            }
        }

        foreach ($whereUser as $what => $value) {
            // $isRange = strpos ($value, '-');

            switch ($what) {
                case 'vehicle_id':
                    $what = 'vehicles.vehicle_id';
                case 'vehicles.vehicle_id':
                    $op = '=';
                    break;

                case 'vin':
                    $what = 'vehicles.vin';
                case 'vehicles.vin':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'akz':
                    $what = 'vehicles_sales.akz';
                case 'vehicles_sales.akz':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'ikz':
                    $what = 'vehicles_sales.ikz';
                case 'vehicles_sales.ikz':
                    $op = 'ilike';
                    $value = "%$value%";
                    break;

                case 'StsOnly':
                    $what = 'vehicles.depot_id';
                case 'vehicles.depot_id':
                    $op = '=';
                    $value = "0";
                    break;

                case 'delivered':
                    $what = 'vehicles_sales.delivery_status';
                case 'vehicles_sales.delivery_status':
                    $op = '=';
                    $value = "TRUE";
                    break;

                case 'finished':
                    $what = 'vehicles.finished';
                case 'vehicles.finished':
                    $op = '=';
                    $value = "TRUE";
                    break;

                case 'depot_id':
                    $what = 'vehicles.depot_id';
                case 'vehicles.depot_id':
                    $op = '=';
                    break;

                default:
                    $op = 'ilike';
                    $value = "%$value%";
                    break;
            }
            $ptrWhere = $ptrWhere->where($what, $op, $value);
            $usedCols .= "," . $what;
        }


        $ptrJoin = $ptrWhere->join('vehicles', 'vehicles_sales.vehicle_id = vehicles.vehicle_id', 'INNER JOIN');
        if (strpos($usedCols, 'depots.') !== false) {
            $ptrJoin = $ptrJoin->join('depots', 'vehicles.depot_id = depots.depot_id', 'INNER JOIN');
        }

        if (strpos($usedCols, 'vehicle_variants.') !== false) {
            $ptrJoin = $ptrJoin->join('vehicle_variants', 'vehicles.vehicle_variant = vehicle_variants.vehicle_variant_id', 'INNER JOIN');
        }
        return $ptrJoin;
    }

    // ===============================================================================================================================================================================
    function getVehicleByUserWhere($whereUser, $selectCols = 'default', $limit = 0, $offset = 0, $order = '') {
        $selectCols = $this->processSelectedCols($selectCols);
        $query = $this->getQueryByUserWhere($whereUser, $selectCols, $limit, $offset, $order);
        $queryResult = $query->get($selectCols);

        return $queryResult;
    }

    // ===============================================================================================================================================================================
    function getVehicleCount($where, $selectCols) {
        $selectCols = $this->processSelectedCols($selectCols);
        $query = $this->getQueryByUserWhere($where, $selectCols);
        $result = $query->get('count(*)');
        return $result[0]['count'];
    }

    // ===============================================================================================================================================================================

    function getVehicleVariant($vehicle_id) {
        return $this->newQuery()->where('vehicle_id', '=', $vehicle_id)->getVal('vehicle_variant');
    }

    // ===============================================================================================================================================================================

    function getDateOfProduction($vehicle_id) {
        return $this->newQuery()->where('vehicle_id', '=', $vehicle_id)->getVal('production_date');
    }

    // ===============================================================================================================================================================================

    function getExportCSVOptions($selectCols = null) {

        return $this->getWhereJoin($selectCols, null, array(array('vehicles.vehicle_id', 'DESC')),
            array(
                array('INNER JOIN', 'vehicles', array(array('vehicles.vehicle_id', 'vehicles_sales.vehicle_id'))),
                array('INNER JOIN', 'depots', array(array('vehicles.depot_id', 'depots.depot_id')))
            ));

    }

}
