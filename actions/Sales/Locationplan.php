<?php
require_once $_SERVER['STS_ROOT']."/includes/sts-array-tools.php";

class ACtion_Locationplan extends AClass_TableBase
{
    private $geoCoord;
    private $sizeImage;

    function __construct ()
    {
        parent::__construct ();

        $leitWartePtr           = $this->controller->GetObject ('ladeLeitWarte');
        $this->gpsMapsPtr       = new GpsMaps ($leitWartePtr);

        $this->vehiclesPtr  = $this->controller->GetObject ("vehicles");
        $this->prodLocs     = $this->vehiclesPtr->newQuery ('depots')
                                    ->join ('divisions', 'using(division_id)')
                                    ->where ('divisions.production_location', '=', 't')
                                    ->get ('depot_id=>depots.name');

        $this->SeitenKoordinaten = [0 => null,
            1 => [_LFT_ =>  610, _TOP_ =>  16],
            2 => [_LFT_ => 1230, _TOP_ => 216],
            3 => [_LFT_ => 1230, _TOP_ =>  16],
            4 => [_LFT_ => 1400, _TOP_ =>  16]];

        if ($this->print)
        {
            $this->SeitenKoordinaten[1][_LFT_] = 30;
        }

        $searchDefaults = [
            'append_results'=>false,
            'suchtext'=>'',
            'suchspalte'=>'vin'];

        $this->S_Show_map   = &InitSessionVar ($this->S_actionVar['show_map'], 'streetscooter');
        $this->S_map_data   = &InitSessionVar ($this->S_actionVar['map_data'], ['display_group'=>'none']);
        $this->S_search     = &InitSessionVar ($this->S_actionVar['search'],    $searchDefaults);


        if ($this->S_map_data['display_group'] != $this->S_Show_map)
        {
            $this->S_map_data = $this->gpsMapsPtr->newQuery ()
                                                    ->where ('display_group', '=', $this->S_Show_map)
                                                    ->where ('parent_map', 'IS', 'NULL')
                                                    ->get('*', 'display_index');
        }

        $tmpUsedDepots          = array();
        $this->primaryMapId     = 0;
        $this->locations        = array();
        $this->activeLocation   = null;

        foreach ($this->S_map_data as $di=>$set)
        {
            $loc = new AClass_Location ($set, $this->SeitenKoordinaten[$di]);
            if ($loc->Init())
            {
                $this->locations[$di] = $loc;
            }
            else
            {
                $this->error = $loc->error;
                unset ($loc);
            }
            $dep = $set['depot_id'];
            $tmpUsedDepots[$dep] = $di;

            if ($di==1)
                $this->primaryMapId =$set['map_id'];
        }

//        $this->subPositions = $this->QuerySubPositions ($this->primaryMapId);

        $this->usedDepots = array_keys ($tmpUsedDepots);

        $this->selectCols          = 'vehicle_id, vin, code, penta_kennwort, park_lines.ident, park_position, ikz, lat, lon, vehicles.color_id, penta_number';
        $this->S_selectedVehicles  = & InitSessionVar ($this->S_data['actual_list'], array());
//        $this->S_currSubPos        = & InitSessionVar ($this->S_data['currSubPos'],  false);
//        $this->S_showSubPos        = & InitSessionVar ($this->S_data['currSubPos'],  0);
        $this->S_colors            = & InitSessionVar ($this->S_data['colors'],      array());
        $this->S_viewpoint         = & InitSessionVar ($this->S_data['viewpoint'],   null);
        $this->S_filter            = & InitSessionVar ($this->S_data['filter'],      array(
                'radius'=>50,
                'limit'=>50,
                'variants'=>['B14', 'B16', 'D16', 'E16'],
                'invert'=>false,
                'select_only'=>false));

        if (count ($this->S_colors) == 0)
        {
            $query = $this->vehiclesPtr->newQuery ('colors');
            $result = $query->get ('color_id, name, rgb');
            $this->S_colors = array_combine (array_column ($result, 'color_id'), array_values($result));
        }
    }

    // ==============================================================================================
        /*
    function QuerySubPositions ($primaryMapId)
    {
        $subPositions = [];
        $result = $this->gpsMapsPtr->newQuery ()
                        ->where ('display_group', '=', $this->S_Show_map)
                        ->where ('parent_map', 'IS', 'NOT NULL')
                        ->get('*', 'map_id');

        foreach ($result as $this_map=>$set)
        {
            if ($set['parent_map'] == $primaryMapId)
            {
                $subPositions[$this_map] = $set;
                $subPositions[$this_map]['list'] = [];

                foreach ($result as $map_id=>$set)
                {
                    if ($set['parent_map']==$this_map)
                    {
                        $subPositions[$this_map]['list'][] = $set;
                    }
                }
            }
        }
        return $subPositions;
    }
    */

    function DefaultDataRegex()
    {
        include $_SERVER['STS_ROOT']."/includes/sts-defines.php";
        return $GLOBALS['CvsDataRegex'];
    }

    // ==============================================================================================

    function SelectByRadius ($location, $geopoint, $filter)
    {
        foreach ($this->S_selectedVehicles as $vid=>&$set)
        {
            $distance_ok = ($filter['radius'] == "");
            $filter_ok   = true;

            if (($set['lat']) && ($set['lon']) && ($filter['radius'] != ""))
            {
                $distance = $this->Distance ($set['coord'], $geopoint);
                $distance_ok = ($distance <= $max_distance);

                if ($filter['invert'])
                    $distance_ok = !$distance_ok;
            }

            if (count($filter['variants'])>0)
            {
                $typ = substr ($set['vin'], 3, 3);
                $filter_ok = in_array ($typ, $filter['variants']);
            }

            $set['selected'] = $distance_ok && $filter_ok;
        }
    }

    // ==============================================================================================

    function removeSelected ()
    {
        foreach ($_REQUEST['sel'] as $vid=>$checked)
        {
            unset ($this->S_selectedVehicles[$vid]);
        }

        foreach ($this->S_selectedVehicles as $vid=>&$vehicle)
        {
            $L = $vehicle['locIndex'];

            if ($L)
                $this->locations[$L]->AddVehicle ($vehicle);
        }
    }

    // ==============================================================================================

    function QueryByRadius ($geopoint, $filter)
    {
        $query = $this->vehiclesPtr->newQuery ();

        if (!$this->activeLocation)
            return ;

        $selectCols     = $this->selectCols;
        $and_variants   = "";
        $local          = &$this->activeLocation->geoRectangle;
        $and_geo        = sprintf ("and (lat>=%f) and (lon>=%f) and (lat<=%f) and (lon<=%f)",
                            $local['from'][_lat_], $local['from'][_lon_], $local['to'][_lat_], $local['to'][_lon_]);



        if (count($filter['variants'])>0)
        {
            $imploded = "'" .  implode  ("','", $filter['variants']) . "'";
            $and_variants = "and substring(vin from 4 for 3) in ($imploded) ";
        }

        if ($filter['radius'] != "")
        {
            $op     = $filter['invert'] ? ">" : "<=";
            $radius = $filter['radius'];
            $where_entfernung = " where entfernung $op $radius ";
        }

        $limit  = ($filter['limit'] != "") ? "limit ".$filter['limit'] : "";

        $lat    = $geopoint[_lat_];
        $lon    = $geopoint[_lon_];

        $prods  = implode (',', array_keys($this->prodLocs));

        $sql    = "
                select *
                from (
                    select $selectCols, (distance(lat, lon, $lat, $lon) * 1000.0) as entfernung
                    from vehicles
                    inner join penta_numbers using (penta_number_id)
                    left join park_lines using (park_id)
                    where depot_id in ($prods) $and_geo $and_variants) as subquery
                $where_entfernung
                order by entfernung
                $limit";

        $result = $query->specialSql ($sql);
        $this->ProcessResults ($result);
    }

    // ==============================================================================================
    /*
    function AddSubPosition ($name, $gps_coord, $radius)
    {
        $addToSub = 0;

        if (empty($name))
            $name = sprintf ("%.6f,%.6f-%dm", $gps_coord[_lat_], $gps_coord[_lon_], $radius);

        foreach ($this->subPositions as $map_id=>$set)
        {
            if (strcasecmp($set['name'], $name)==0)
            {
                $addToSub = $map_id;
                break;
            }
        }

        $dbPrimary = $this->locations[1]->dbInfo;

        $set = [];

        $set ['depot_id']       = $dbPrimary['depot_id'];
        $set ['name']           = $name;
        $set ['address']        = $dbPrimary['address'];
        $set ['display_group']  = $dbPrimary['display_group'];
        $set ['radius']         = $radius;
        $set ['lon']            = $gps_coord [_lon_];
        $set ['lat']            = $gps_coord [_lat_];

        if ($addToSub)
        {
            $set ['parent_map'] = $addToSub;
        }
        else
        {
            $set ['parent_map']     = $this->primaryMapId;
        }

        $this->gpsMapsPtr->newQuery ()->insert ($set);
        $this->gpsMapsPtr->query("SELECT currval('gps_maps_map_id_seq'); ");
        while ( $row = $this->gpsMapsPtr->fetchArray () )
        {
            $new_id = $row['currval'];
            break;
        }


        if ($addToSub)
        {
            $this->subPositions[$addToSub]['list'][] = $set;
        }
        else
        {
            $set ['list'] = [];
            $this->subPositions[$new_id] = $set;
        }

        $this->S_showSubPos = 1;
    }
    */

    // ==============================================================================================

    function LoadList ()
    {
        $import_akz     = [];
        $import_vin     = [];
        $import_ikz     = [];
        $import_penta   = [];
        $import_id      = [];
        $orWhere        = [];


        if (isset ($_REQUEST['liste']))
        {
            $text  = str_replace ([',', ';', "\r", "'", '"'], [lf, lf, "", "", ""], $_REQUEST['liste']);
            $liste = explode (lf, $text);

            $CvsDataRegex = $this->DefaultDataRegex ();

            foreach ($liste as $item)
            {
                if (preg_match ("/^".$CvsDataRegex['penta_kennwort'].'$/', $item))
                {
                    $import_penta [] = $item;
                }
                else
                if (preg_match ("/^".$CvsDataRegex['vin'].'$/', $item))
                {
                    $import_vin [] = $item;
                }
                else
                if (preg_match ("/^".$CvsDataRegex['akz'].'$/', $item))
                {
                    $import_akz [] = $item;
                }
                else
                if (preg_match ("/^".$CvsDataRegex['ikz'].'$/', $item))
                {
                    $import_ikz [] = $item;
                }

            }
        }
        else if (isset ($_REQUEST['vlist']))
        {
            $input = &$_REQUEST['vlist'];

            $liste = explode (',', str_replace (';', ",", $_REQUEST['vlist']));
            foreach ($liste as $item)
            {
                if (preg_match ("/^[0-9]+$/", $item))
                    $import_id [] = $item;
            }
        }

        if ((count ($import_penta)
           + count ($import_vin)
           + count ($import_akz)
           + count ($import_ikz)
           + count ($import_id)) == 0)
            return;

        $query      = $this->vehiclesPtr->newQuery ();

        $where = "
            select $this->selectCols
            from vehicles
            inner join penta_numbers using (penta_number_id)
            left join park_lines using (park_id)
            where (";

        if (count ($import_penta))
            $orWhere  []= "penta_kennwort in ('" . implode("','", $import_penta) . "')";

        if (count ($import_vin))
            $orWhere  []= "vin in ('" . implode("','", $import_vin) . "')";

        if (count ($import_akz))
            $orWhere  []= "code in ('" . implode("','", $import_akz) . "')";

        if (count ($import_ikz))
            $orWhere  []= "ikz in ('" . implode("','", $import_ikz) . "')";

        if (count ($import_id))
            $orWhere  []= "vehicle_id in (" . implode(",", $import_id) . ")";

        $where .= implode (' or ', $orWhere) . ")";
        $result = $query->specialSql ($where);

        $this->ProcessResults ($result);
    }

    // ==============================================================================================

    function SucheVehicle() {
        $this->S_search['suchspalte'] = $_REQUEST['suchspalte'];
        $this->S_search['suchtext'] = $_REQUEST['suchtext'];
        
        $likestr = str_replace(['?', '*'], ['_', '%'], $this->S_search['suchtext']);
        if (substr($likestr, 0, 1) == '!')
            $likestr = substr($likestr, 1);
            else
                $likestr = '%' . $likestr;
                
                if (substr($likestr, -1) == '!')
                    $likestr = substr($likestr, 0, -1);
                    else
                        $likestr = $likestr . '%';
                        
                        $query = $this->vehiclesPtr
                        ->newQuery()
                        ->join('penta_numbers', 'using(penta_number_id)')
                        ->join('park_lines', 'using(park_id)', 'left join')
                        // ->where('vin', 'ilike', $likestr)
                        ->multipleAndWhere('vin', 'ilike', $likestr, 'OR', 'code', 'ilike', $likestr, 'OR', 'ikz', 'ilike', $likestr, 'OR', 'penta_kennwort', 'ilike', $likestr)
                        ->where('depot_id', 'in', $this->usedDepots);
                        
                        $result = $query->get($this->selectCols);
                        
                        if ($result)
                            $this->ProcessResults($result);
                            
    }

    // ==============================================================================================

    function ProcessResults ($result, $append=true)
    {
        $this->S_search['append_results'] = isset ($_REQUEST['append_results']);
        $append = $append && $this->S_search['append_results'];

        if (!$append)
        {
            $this->S_selectedVehicles = [];
            foreach ($this->locations as $l=>$loc)
                $loc->Reset();
        }


        foreach ($result as &$set)
        {
            $vid    = $set['vehicle_id'];

            $this->S_selectedVehicles [$vid] = $set;
            $set = &$this->S_selectedVehicles [$vid];

            $cid    = $set['color_id'];
            $color  = &$this->S_colors[$cid];

            $set['selected']= true;
            $set['typ']     = $set['vin'][3].' '.$set['vin'][4].$set['vin'][5];
            $set['color']   = $color['name'];
            $set['rgb']     = $color['rgb'];

            $set['set_plot'] = false;
            $set['locIndex'] = 0;
            $set['ort'] = "unbekannter Ort";

            if (($set['lat']) && ($set['lon']))
            {
                $set['coord'] = [_lon_=>$set['lon'], _lat_=>$set['lat']];
            }
            else
            {
                $set['ort'] = "keine GPS Daten";
            }
        }
    }

    // ==============================================================================================

    function UpdateFilterset ()
    {
        if (isset ($_REQUEST['cb_variant']) && is_array ($_REQUEST['cb_variant']) && (count($_REQUEST['cb_variant'])>0))
        {
            $this->S_filter['variants'] = array_keys ($_REQUEST['cb_variant']);
        }

        if (isset($_REQUEST['limit']) && ($_REQUEST['limit']>0))
        {
            $this->S_filter['limit'] = $_REQUEST['limit'];
        }

        if (isset($_REQUEST['radius']) && ($_REQUEST['radius']>0))
        {
            $this->S_filter['radius'] = $_REQUEST['radius'];
        }

        $this->S_filter['invert'] = isset($_REQUEST['invert']);
        $this->S_filter['select_only'] = isset($_REQUEST['select_only']);
    }

    // ==============================================================================================

    function Execute()
    {
        $command = safe_val ($_REQUEST, 'hidden_command', '');

        $this->UpdateFilterset();

//        if (isset($_REQUEST['showSubPos']))
//            $this->S_showSubPos = $_REQUEST['showSubPos'];


        if (($_REQUEST['vpX'] != '') && ($_REQUEST['vpY'] != '') && ($_REQUEST['vpZ'] != ''))
        {
            $this->S_viewpoint[_X_] = $_REQUEST['vpX'];
            $this->S_viewpoint[_Y_] = $_REQUEST['vpY'];
            $this->S_viewpoint[_Z_] = $_REQUEST['vpZ'];
        }


        if (($command == '') && isset ($_REQUEST['command']))
            $command = strtolower(reset (array_keys($_REQUEST['command'])));

        switch ($command)
        {
            case "":
                break;

            case 'remove_selected':
                $this->removeSelected ();
                break;

            case 'remove_all':
                $this->S_selectedVehicles = array();
                break;

            case 'loadlist':
                   $this->LoadList ();
                break;

            case 'select':
                if ($this->S_viewpoint && $this->S_viewpoint[_Z_])
                {
                    $i = $this->S_viewpoint[_Z_];
                    $this->activeLocation = $this->locations[$i];
                    $this->gps_point = $this->activeLocation->SetViewpoint ($this->S_viewpoint);
                }

                if ($_REQUEST['select_only'])
                    $this->SelectByRadius ($this->gps_point, $this->S_filter);
                else
                    $this->QueryByRadius ($this->gps_point, $this->S_filter);
                break;

            case 'suche':
                $this->SucheVehicle();
                break;
/*
            case 'addsubposition':
                if ($this->S_viewpoint && ($this->S_viewpoint[_Z_]==1))
                {
                     $this->activeLocation = $this->locations[1];
                     $this->gps_point = $this->activeLocation->SetViewpoint ($this->S_viewpoint);
                     $this->AddSubPosition ($_REQUEST['subPosition']['name'], $this->gps_point, $_REQUEST['radius']);
                }
                break;
*/
        }


        foreach ($this->locations as $i => $loc)
        {
            foreach ($this->S_selectedVehicles as $vid=>&$vehicle)
            {
                if ($vehicle['coord'])
                    $loc->CheckLocation ($vehicle);
            }
        }
    }

    // ==============================================================================================


    function SetupHeaderFiles ($displayheader)
    {
        parent::SetupHeaderFiles ($displayheader);

        $displayheader->enqueueJs ('formtools', 'js/formtools.js');
        $displayheader->enqueueJs ('locationplan', 'js/locationplan.js');
        if ($this->print)
        {
            $displayheader->removeStylesheet ("/.*/i");
            $displayheader->enqueueStylesheet ('skeleton', 'css/skeleton.css');
            $displayheader->enqueueStylesheet ('locationplan', 'css/locationplan.print.css');
        }
        else
        {
            $displayheader->enqueueStylesheet ('locationplan', 'css/locationplan.css');
        }


        foreach ($this->locations as $loc)
        {
            $style = $loc->GetHtml_LocalStyle ();
            $displayheader->enqueueLocalStyle ($style);
        }
    }

    // ==============================================================================================

    function GetHtml_CurrentSpots ()
    {
        foreach ($this->S_selectedVehicles as $vid=>$set)
        {
            $color = '000000'; //$set['rgb']
            $vis = ($set['selected'] && $set['set_plot'])  ? 'visible' : 'hidden';

            printf ('<div class="spot" id="id_spot_%d" style="left:%dpx; top:%dpx;border-color:#%s;visibility:%s"></div>',
                    $vid, $set['img_pos'][_X_] -4, $set['img_pos'][_Y_] -4, $color, $vis);

            printf ('<div class="kennzeichen" id="id_kenz_%d" style="left:%dpx;top:%dpx;visibility:%s">%s</div>',
                    $vid, $set['img_pos'][_X_] +12, $set['img_pos'][_Y_] -4, $vis, $set['code']);
        }
    }

    // ==============================================================================================


    function LinkToExternMap ($lat, $lon, $use_google=false)
    {
        if ($use_google)
            return sprintf ('<a href="https://www.google.de/maps/dir//%f,%f/@%f,%f,16z" title="Link auf google/maps" target="new">',$lat, $lon, $lat, $lon);
        return sprintf ('<a href="https://www.openstreetmap.org/?mlat=%f&mlon=%f&zoom=17" title="Link zu open streetmap" target="new">', $lat, $lon);
    }

    // ==============================================================================================

    function FahrzeugTabelle ($minRows=9)
    {
        foreach ($this->S_selectedVehicles as $vid => $set)
        {
            $vid        = $set['vehicle_id'];
            $selected   = $set['selected'] ? " checked":"";
            $typ        = $set['typ'];

            if ($set['ort'])
            {
                $link       = $this->LinkToExternMap ($set['lat'], $set['lon']) .  $set['ort'] . "</a>";
                $class      = "";
            }
            else
            {

                $link       = 'keine GPS Daten';
                $class      = ' class="greyed"';
            }

            echo <<<HEREDOC

  <tr id="id_tr_$vid" data-vid="$vid">
    <td><input type="checkbox" id="id_sel{$vid}" name="sel[{$vid}]"{$selected} onClick="ShowHideSpot({$vid}, this.checked)"></td>
    <td>{$set['vin']}</td>
    <td>{$set['penta_kennwort']}</td>
    <td>{$set['penta_number']}</td>
    <td id="id_loc{$vid}"{$class}>{$link}</td>
    <td>{$set['ident']} {$set['park_position']}</td>
  </tr>
HEREDOC;

        }

        for ($r = count($this->S_selectedVehicles); $r<$minRows; $r++)
        {
            echo "\n  <tr>\n    <td>&nbsp;</td>\n    <td>&nbsp;</td>\n    <td>&nbsp;</td>\n    <td>&nbsp;</td>\n    <td>&nbsp;</td>\n    <td>&nbsp;</td>\n    <td>&nbsp;</td>\n  </tr>\n";
        }
    }

    // ==============================================================================================

    function FahrzeugTabelle_PrintVersion()
    {
        echo <<<HEREDOC
<table class="printtable" id="printtable">
  <thead>
    <tr>
      <td>AKZ</td>
      <td>Ort</td>
      <td>Typ</td>
      <td>VIN</td>
      <td>IKZ</td>
      <td>Penta Nr.</td>
    </tr>
  </thead>
  <tbody id="fztbody">
HEREDOC;

        foreach ($this->S_selectedVehicles as $vid => $set)
        {
            $vid        = $set['vehicle_id'];
            $typ        = $set['typ'];

            if ($set['depot_id'])
                continue;

            if ($set['ort'])
            {
                $link       = $set['ort'];
                $class      = "";
            }
            else
            {

                $link       = 'keine GPS Daten';
                $class      = ' class="greyed"';
            }

            echo <<<HEREDOC

    <tr id="id_tr_$vid" data-vid="$vid">
      <td>{$set['code']}</td>
      <td id="id_loc{$vid}"{$class}>{$link}</td>
      <td>{$typ}</td>
      <td>{$set['vin']}</td>
      <td>{$set['ikz']}</td>
      <td {$set['penta_kennwort']}</td>
    </tr>
HEREDOC;

        }

        echo "  </tbody>\n</table>\n";
    }

    // ==============================================================================================

    function CreateLocationList ()
    {
        $locations = [];
        foreach ($this->S_selectedVehicles as $vid=>$set)
        {
            if (! in_array($set['ort'], $locations))
                $locations [] = $set['ort'];
        }
        sort ($locations);
        return $locations;
    }

    // ==============================================================================================

    function GetLocationOptionsList ()
    {
        $locations = $this->CreateLocationList ();
        if (count ($locations))
            return '<option>'.implode ('</option><option>', $locations)."</option>\n";
        return "";
    }
    // ==============================================================================================


    function WriteHtmlContent ($options="")
    {
        $vp_disabled    = (empty ($this->S_viewpoint))   ? " disabled" : "";
        $so_disabled    = (empty ($this->S_viewpoint) || empty ($this->S_selectedVehicles)) ? " disabled" : "";
        $invert         = $this->S_filter['invert']      ? " checked" : "";
        $select_only    = $this->S_filter['select_only'] ? " checked" : "";
        $limit          = $this->S_filter['limit'];
        $radius         = $this->S_filter['radius'];
        $variants       = $this->S_filter['variants'];

        $cbFilter_B14   = in_array ('B14', $variants) ? " checked" : "";
        $cbFilter_B16   = in_array ('B16', $variants) ? " checked" : "";
        $cbFilter_D16   = in_array ('D16', $variants) ? " checked" : "";
        $cbFilter_E16   = in_array ('E16', $variants) ? " checked" : "";


        parent::WriteHtmlContent ($options);
        include $_SERVER['STS_ROOT']."/actions/Sales/Locationplan/Locationplan.map.php";
    }

    // ==============================================================================================
}

?>

