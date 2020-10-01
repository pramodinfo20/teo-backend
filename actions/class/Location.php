<?php
// Die defines sind jeweils paarweise gleich:
//   _X_ = _lon_ = 0
//   _Y_ = _lat_ = 1


class AClass_Location {
    public      $name;
    public      $imageRef;
    public      $geoRectangle   = ['from'=>[6,50],'to'=>[7,51]];
    public      $geoSize        = [];
    public      $imageSize      = [600, 600];
    public      $ViewPoint      = null; //  ['pixel' =>[0,0], 'geo'=>[0,0]];
    public      $myVehicles     = [];
    public      $pagePos        = [];
    protected   $geoOrigin      = [];
    protected   $dGeo           = [];
    protected   $factorGeo2Pixel= [];

    protected   $linkerIndex    = [];
    protected   $rechterIndex   = [];
    protected   $locationplan = null;
    protected   $sublocations = [];


    function __construct (&$set, $SeitenKoordinaten)
    {
        $this->index    = $set['display_index'];
        $this->name     = $set['name'];
        $this->pagePos  = $SeitenKoordinaten;
        $this->imageRef = '/images/maps/'.$set['image_file'];
        $this->dbInfo   = $set;

        $this->SetViewpoint (null);
    }

    // ==============================================================================================

    function Init() {
        $filepath = $_SERVER['DOCUMENT_ROOT'] . $this->imageRef;
        $this->imageSize = getimagesize ($filepath);
        if ($this->imageSize === false) {
            $this->error = "Not an image file: $this->imageRef";
            return false;
        }

        extract ($this->dbInfo);

        $this->geoRectangle = array();

        $this->geoRectangle['from'] = [_lon_=>$from_lon, _lat_=>$from_lat];
        $this->geoRectangle['to']   = [_lon_=>$to_lon,   _lat_=>$to_lat  ];
        $this->geoOrigin            = &$this->geoRectangle['from'];
        $this->dGeo[_X_]            = $this->geoRectangle['to'][_X_] - $this->geoRectangle['from'][_X_];
        $this->dGeo[_Y_]            = $this->geoRectangle['to'][_Y_] - $this->geoRectangle['from'][_Y_];
        $this->factorGeo2Pixel[_X_] = $this->imageSize[_X_] / $this->dGeo[_X_];
        $this->factorGeo2Pixel[_Y_] = $this->imageSize[_Y_] / $this->dGeo[_Y_];

        $this->CalcLocationMetric ();
        return true;
    }

    function CalcLocationMetric() {
        $x1 = $y1 = $this->geoRectangle['from'];
        $x2 = $y2 = $this->geoRectangle['to'];

        $x2[_lat_] = $x1[_lat_];
        $y2[_lon_] = $y1[_lon_];

        $this->locationWidth[_X_] = $this->Distance ($x1, $x2) * 1000;
        $this->locationWidth[_Y_] = $this->Distance ($y1, $y2) * 1000;
        $this->pixelPerMeter[_X_] = $this->imageSize[_X_] / $this->locationWidth[_X_];
        $this->pixelPerMeter[_Y_] = $this->imageSize[_Y_] / $this->locationWidth[_Y_];
    }

    // ==============================================================================================

    function geo2pixel($lon, $lat, $imgOrigin = 'top') {
        $pixelCoord = [
            _X_ => round(($lon - $this->geoOrigin[_X_]) * $this->factorGeo2Pixel[_X_]),
            _Y_ => round(($lat - $this->geoOrigin[_Y_]) * $this->factorGeo2Pixel[_Y_])
        ];
        if (($pixelCoord[_X_] < 0) || ($pixelCoord[_X_] >= $this->imageSize[_X_]) ||
            ($pixelCoord[_Y_] < 0) || ($pixelCoord[_Y_] >= $this->imageSize[_Y_])) {
            $pixelCoord['outside'] = true;
        }

        if ($imgOrigin == 'top')
            $pixelCoord[_TOP_] = $this->imageSize[_Y_] -$pixelCoord[_Y_];

        return $pixelCoord;
    }

    // ==============================================================================================


    function pixel2geo($pixelXY, $imgOrigin = 'top') {
        $px   = ($imgOrigin == 'top') ? ($this->imageSize[_Y_] - $pixelXY[_TOP_]) : $pixelXY[_Y_];

        $lon  = $pixelXY[_X_] / $this->factorGeo2Pixel[_X_];
        $lat  = $px / $this->factorGeo2Pixel[_Y_];
        return [$lon + $this->geoOrigin[_lon_], $lat + $this->geoOrigin[_lat_]];
    }

    // ==============================================================================================


    function Distance($pos1, $pos2 = null) {
        if (!$pos2)
            $pos2 = &$this->Viewpoint ['geo'];

        $lat = ($pos1[_lat_] + $pos2[_lat_]) * 0.008725; // /2. * 0.01745
        $dx  = 111.3 * cos($lat)  * ($pos1[_lon_] - $pos2[_lon_]);
        $dy  = 111.3 * ($pos1[_lat_] - $pos2[_lat_]);
        return sqrt(($dx * $dx) + ($dy * $dy));
    }

    // ==============================================================================================

    function IsHere(&$vehicle) {
        if (in_array($this->index, $vehicle['locIndex']))
            return true;

        return(($vehicle['lon'] >= $this->geoRectangle['from'][_lon_])
            && ($vehicle['lon'] <  $this->geoRectangle['to']  [_lon_])
            && ($vehicle['lat'] >= $this->geoRectangle['from'][_lat_])
            && ($vehicle['lat'] <  $this->geoRectangle['to']  [_lat_]));
    }

    // ==============================================================================================

    function SetViewpoint($pixelCoord) {
        if ($pixelCoord) {
            $x = ($pixelCoord[_X_]+2).'px';
            $y = ($pixelCoord[_Y_]-26).'px';
            $this->style_faehnchen = "left:$x;top:$y;visibility:visible;";
            $this->ViewPoint = ['pixel'=>$pixelCoord, 'geo'=>$this->pixel2geo ($pixelCoord)];
            return $this->ViewPoint['geo'];
        }


        $this->style_faehnchen = "left:0px;top:0px;";
        $this->ViewPoint = null;
        return null;
    }

    // ==============================================================================================

    function Reset() {
        $this->myVehicles     = [];
        $this->linkerIndex    = [];
        $this->rechterIndex   = [];

        foreach ($this->sublocations as $subloc)
            $subloc->Reset();

    }
    // ==============================================================================================

    function CheckLocation(&$vehicle) {
        if ($this->IsHere($vehicle)) {
            $this->AddVehicle ($vehicle);
            return true;
        }
        return false;
    }
    // ==============================================================================================

    function AddVehicle(&$vehicle) {
        $vid = $vehicle['vehicle_id'];

        if (!in_array($this->index, $vehicle['locIndex']))
            $vehicle['locIndex'][] = $this->map_id;

        $vehicle['img_pos'] = $this->geo2pixel ($vehicle['lon'], $vehicle['lat']);

        $vehicle['ort']   = $this->name;
        $vehicle['set_plot'] = true;

        if ($this->ViewPoint && empty($vehicle['entfernung'])) {
            $vehicle['entfernung'] = $this->Distance($vehicle['coord']);
        }

        $this->myVehicles[$vid] = &$vehicle;
        
        foreach ($this->sublocations as $subloc)
            $subloc->CheckLocation($vehicles);

        if ($this->ViewPoint && empty($vehicle['entfernung']))
        {
            $vehicle['entfernung'] = $this->Distance ($vehicle['coord']);
        }
    }

    // ==============================================================================================

    function RemoveVehicle($vehicle_id) {
        unset ($this->myVehicles[$vehicle_id]);

        if (($i = array_search ($vehicle_id, $this->linkerIndex)) !== false)
            unset ($this->linkerIndex[$i]);
        else if (($i = array_search ($vehicle_id, $this->rechterIndex)) !== false)
            unset ($this->rechterIndex[$i]);
        
        foreach ($this->sublocations as $subloc)
            $subloc->RemoveVehicle($vehicle_id);
    }

    // ==============================================================================================
    function GetParkingLocations(&$liste) {
        $di = $this->map_id;

        if (count($this->myVehicles)) {
            if (!isset($liste[$di]))
                $liste[$di] = $this->name;

            foreach ($this->sublocations as $subloc)
                $subloc->GetParkingLocations($liste);
        }
    }

    // ==============================================================================================
    function SortiereRechtsLinks() {
        $lons = array_column ($this->myVehicles, 'lon');
        $lats = array_column ($this->myVehicles, 'lat');
        $vids = array_keys   ($this->myVehicles);

        if (! is_array($lons) || count($lons)==0)
            return;

        $this->PosStatistics = [];
        $this->PosStatistics['lon_min']  = min ($lons);
        $this->PosStatistics['lon_max']  = max ($lons);
        $this->PosStatistics['lon_avg']  = array_sum ($lons) / count($lons);
        $this->PosStatistics['lat_avg']  = array_sum ($lats) / count($lats);
        $this->PosStatistics['pix_min']  = $this->geo2Pixel ($this->PosStatistics['lon_min'], $this->PosStatistics['lat_avg']);
        $this->PosStatistics['pix_max']  = $this->geo2Pixel ($this->PosStatistics['lon_max'], $this->PosStatistics['lat_avg']);
        $this->PosStatistics['pix_avg']  = $this->geo2Pixel ($this->PosStatistics['lon_avg'], $this->PosStatistics['lat_avg']);

        $sort = array_combine  ($lons, $vids);
        if (count ($sort)==0)
            return;

        ksort ($sort, SORT_NUMERIC);
        $n2 = count ($sort) / 2;
        $vids = array_values ($sort);


        while (count($vids)) {
            $vid = array_pop ($vids);
            if ($vid) {
                $lat = $this->myVehicles[$vid]['lat'];
                $rechts[$lat] = $vid;
            }


            $vid = array_shift ($vids);
            if ($vid) {
                $lat = $this->myVehicles[$vid]['lat'];
                $links [$lat] =  $vid;
            }
        }


        $this->linkerIndex = [];
        $this->rechterIndex = [];
        if (is_array($links) && count($links)) {
            krsort ($links);
            $this->linkerIndex  = array_values ($links);
        }


        if (is_array($rechts) && count($rechts)) {
            krsort ($rechts);
            $this->rechterIndex = array_values ($rechts);
        }


    }

    // ==============================================================================================

    function WriteHtml_VehicleSpots($liste, $side)
    {
        if (count($liste) == 0)
            return;

        $abstand    = 200;
        $AKZwidth   = 70;
        $orgLeft    = $this->pagePos[_LFT_];
        $orgTop     = $this->pagePos[_TOP_];

        $kzTop      = $orgTop + $this->PosStatistics['pix_min'][_TOP_] - 10 * count ($liste) +2;

        switch ($side)
        {
            case 'L':
                $lnLeft = $orgLeft + $this->PosStatistics['pix_min'][_LFT_] - $abstand;
                $kzLeft = $lnLeft - $AKZwidth;
                break;

            case 'R':
                $kzLeft = $orgLeft + $this->PosStatistics['pix_max'][_LFT_] +12 + $abstand;
                $lnLeft = $kzLeft;
                break;
        }


        foreach ($liste as $vid)
        {
            $set = &$this->myVehicles[$vid];
            $color_id = $set['color_id'];
            $spot_color = "rgba($red, $blue, $green,0.3)"; //'#'.$set['rgb']; //'000000';
            $akz_color  = '#ffffff'; //'#'.$set['rgb'];
            $vis = ($set['selected'] && $set['set_plot'])  ? 'visible' : 'hidden';

            $vehicleLeft = $orgLeft + $set['img_pos'][_LFT_];
            $vehicleTop  = $orgTop  + $set['img_pos'][_TOP_];

            printf ('<div class="vehicle frb%d" id="id_spot_%d" style="position:absolute;left:%dpx;top:%dpx;visibility:%s;"></div>',
                    $color_id, $vid, $vehicleLeft-3, $vehicleTop-3, $vis);


            printf ('<div class="kennzeichen" id="id_kenz_%d" data-loc="%d" style="left:%dpx;top:%dpx;visibility:%s;" onClick="SelectByKzClick(this)">%s</div>',
                   $vid, $this->index, $kzLeft, $kzTop, $vis, 'WS...'.substr($set['vin'], -6));

            $lnTop  = $kzTop + 8;
            $dx     = max (2, abs ($lnLeft - $vehicleLeft) -2);
            $dy     = max (2, abs ($lnTop  - $vehicleTop) );

            if ($lnTop < $vehicleTop)
            {
                if ($side=='L')
                {
                    printf ('<canvas class="canvas" id="id_canvas_%d" data-dir="OL" width="%d" height="%d" style="left:%dpx;top:%dpx;visibility:%s;"></canvas>',
                            $vid, $dx, $dy, $lnLeft, $lnTop, $vis);
                }
                else
                {
                    printf ('<canvas class="canvas" id="id_canvas_%d" data-dir="OR" width="%d" height="%d" style="left:%dpx;top:%dpx;visibility:%s;"></canvas>',
                            $vid, $dx, $dy, $vehicleLeft+2, $lnTop, $vis);
                }
            }
            else
            {
                if ($side=='L')
                {
                    printf ('<canvas class="canvas" id="id_canvas_%d" data-dir="UL" width="%d" height="%d" style="left:%dpx;top:%dpx;visibility:%s;"></canvas>',
                            $vid, $dx, $dy, $lnLeft, $vehicleTop+2, $vis);
                }
                else
                {
                    printf ('<canvas class="canvas" id="id_canvas_%d" data-dir="UR" width="%d" height="%d" style="left:%dpx;top:%dpx;visibility:%s;"></canvas>',
                            $vid, $dx, $dy, $vehicleLeft+2, $vehicleTop+2, $vis);
                }
            }
            $kzTop += 30;
            echo lf;
        }

    }

    // ==============================================================================================

    function WriteMap ()
    {
        $this->SortiereRechtsLinks ();

        $qname      = $this->name;
        $selColor   = ($this->ViewPoint) ? 'border-color:#dd2200;' : '';

        $posPrint_t = $this->imageSize[_Y_] - 20;
        $urlPrint   = sprintf ("%s?action=%s&print=preview", $_SERVER['PHP_SELF'], $_REQUEST['action']);

        echo <<<HEREDOC
  <div class="lageplan" id="plan{$this->index}" style="">
    <img id="karte{$this->index}" data-index="{$this->index}" style="width:{$this->imageSize[_X_]}px;height:{$this->imageSize[_Y_]}px;$selColor" src="{$this->imageRef}">
    <canvas id="id_canvas{$this->index}" data-index="{$this->index}" data-ppmx="{$this->pixelPerMeter[_X_]}" data-ppmy="{$this->pixelPerMeter[_Y_]}" width="{$this->imageSize[_X_]}" height="{$this->imageSize[_Y_]}"></canvas>
    <div>{$this->name}</div>
    <div id="faehnchen{$this->index}" class="faehnchen" style="{$this->style_faehnchen}"><img src="/images/Faehnchen.png"></div>

HEREDOC;


        echo "
  </div>\n";

    }

    // ==============================================================================================

    function DrawVehicles ()
    {
        $this->WriteHtml_VehicleSpots ($this->linkerIndex,  'L');
        $this->WriteHtml_VehicleSpots ($this->rechterIndex, 'R');
    }

    // ==============================================================================================

    function GetHtml_LocalStyle ()
    {
        $divsize    = $this->imageSize[_Y_] + 40;
        return sprintf ("#plan%d {left:%dpx; top:%dpx; width:%dpx; height:%dpx; }",
                $this->index,
                $this->pagePos[_X_], $this->pagePos[_Y_],
                $this->imageSize[_X_], $divsize );
    }
}
