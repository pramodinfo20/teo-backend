<?php
/**
 * CommonFunctions_VehiclesStationsOverview.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle Ubersicht function
 *
 */
class CommonFunctions_VehiclesStationsOverview extends CommonFunctions {
    protected $overview;
    protected $summary;

    protected $sumTotalES = 0;
    protected $sumTotalAS = 0;
    protected $sumTotalFS = 0;
    protected $sumTotalAV = 0;
    protected $sumTotalEV = 0;
    protected $sumTotalV = 0;
    protected $sumTotalS = 0;

    protected $stationstr;
    protected $ratioVehicleStation;
    protected $ratioAssignedVehicles;
    protected $overviewHeading;

    protected $showSupportEmails;

    function __construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $getaction = '') {
        parent::__construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $getaction);

        $this->showSupportEmails = false;

    }

    function buildOverview($unit = '', $unitVal = '', $getaction = '') //@todo why pass getaction twice? isnt it enough in construct?
    {
        $currentDiv_id = 0;
        $heading2 = "";

        switch ($unit) {
            case 'zspl':
                $currentZspl = $this->ladeLeitWartePtr->zsplPtr->getFromId($unitVal);
                $currentDiv_id = $currentZspl['division_id'];
                $heading2 = 'ZSP Name';
                $this->overviewHeading = "Übersicht: ZSPL {$currentZspl['name']} (OZ: {$currentZspl['dp_zspl_id']})";
                $this->objectLabel = "depot";
                $this->listObjects = $this->ladeLeitWartePtr->depotsPtr->getAllInZspl($unitVal);
                $showGps = safe_val($GLOBALS, 'VERBOSE', false);
                break;

            case 'division':
                $currentDiv = $this->ladeLeitWartePtr->divisionsPtr->getFromId($unitVal);
                $currentDiv_id = $currentDiv['division_id'];
                $heading2 = "ZSPL Name";
                $this->overviewHeading = 'Übersicht: Niederlassung ' . $currentDiv['name'] . ' (OZ: ' . $currentDiv['dp_division_id'] . ') ';
                $this->objectLabel = "zspl";
                $this->listObjects = $this->ladeLeitWartePtr->zsplPtr->getAllInDivision($unitVal);

                break;

            default:
                $currentDiv_id = 0;
                $this->overviewHeading = "Übersicht: Niederlassungen";
                $heading2 = 'Niederlassung Name';

                $this->objectLabel = "division";
                $this->listObjects = $this->ladeLeitWartePtr->divisionsPtr->getAllValidDivisions();
                break;
        }

        if ($currentDiv_id) {
            $type_aus = DEPOT_TYPE_POST_AUSSTEHENDE;
            $type_pool = DEPOT_TYPE_POST_FLEET_POOL;

            $additional = $this->ladeLeitWartePtr->depotsPtr->newQuery()
                ->where('division_id', '=', $currentDiv_id)
                ->where('depot_type_id', 'in', [$type_aus, $type_pool])
                ->get_no_parse('*, (select count(*) as num_vehicles from vehicles where vehicles.depot_id=depots.depot_id)', 'depot_type_id');

            $num_aus = $additional[$type_aus]['num_vehicles'];
            $num_pool = $additional[$type_pool]['num_vehicles'];
            $num_free = $num_aus + $num_pool;

            $this->HeadingInfo = <<<HEREDOC
<table class="transparent noborder" style="width: 500px; margin: 10px 5px;">
<tr>
    <th>Anzahl ausstehende Zuweisungen:</th>
    <td style="text-align: left;width: 80px;">{$num_aus}</td>
    <td><a href="{$_SERVER['PHP_SELF']}?action=$getaction&zsp={$additional[$type_aus]['depot_id']}">Fahrzeuge verschieben...</a></td>
</tr><tr>
    <th>Anzahl Fahrzeuge im Fleet Pool</th>
    <td style="text-align: left;width: 80px;">{$num_pool}</td>
    <td><a href="{$_SERVER['PHP_SELF']}?action=$getaction&zsp={$additional[$type_pool]['depot_id']}">Fahrzeuge verschieben...</a></td>
</tr>
</table>
HEREDOC;
        }

        if (!isset($this->listObjects))
            return;

        if ($showGps)
            $heading7 = "
    <th>GPS Koordinaten</th>";

        $this->overview = <<<HEREDOC
<table class="zentralelist">
  <tr class="heading">
    <th>OZ</th>
    <th>$heading2</th>
    <th>Anzahl Fahrzeuge <span class="greyText normalText">(zugewiesene)</div></th>
    <th>Anzahl Ersatzfahrzeuge</th>
    <th>Anzahl freie Ladepunkte</th>
    <th>Anzahl Ladepunkte <span class="greyText normalText">(eingetragene)</div></th>
    <th>EBG / AixACCT / andere</th>$heading7
  </tr>
HEREDOC;


        $obj = $this->objectLabel;
        $vdb = &$this->ladeLeitWartePtr->vehiclesPtr;
        $sdb = &$this->ladeLeitWartePtr->stationsPtr;

        foreach ($this->listObjects as $listObject) {
            $object_id = $listObject["{$this->objectLabel}_id"];
            $dp_id = "dp_{$this->objectLabel}_id";
            $oz = $listObject[$dp_id];
            if (empty ($oz))
                continue;

            $vehicleCnt = $num_free;
            $stationCnt = intval($sdb->getStationsCnt($obj, $object_id));
            $ebgStationCnt = intval($sdb->getStationsCnt($obj, $object_id, ['depots.stationprovider', '=', 1]));
            $aixStationCnt = intval($sdb->getStationsCnt($obj, $object_id, ['depots.stationprovider', '=', 2]));
            $otherStationCnt = $stationCnt - ($ebgStationCnt + $aixStationCnt);

            $assignedVehiclesCnt = intval($vdb->getAssignedVehiclesCnt($obj, $object_id, false));
            $replacementVehiclesCnt = intval($vdb->getAssignedVehiclesCnt($obj, $object_id, true));
            $freeStationsCnt = $stationCnt - $assignedVehiclesCnt;

            if ((!$getaction) && preg_match('/^([a-z_][a-z0-9_]*)[^a-z0-9_].*/i', $_REQUEST['action'], $match))
                $getaction = $match[1];
            $actionstr = '&action=' . $getaction;

            if ($this->objectLabel == 'depot') {
                if (($assignedVehiclesCnt + $replacementVehiclesCnt) > 0)
                    $listObjectLink = "<a href=\"?zsp={$listObject['depot_id']}$actionstr\">{$listObject['name']}</a>";
                else
                    $listObjectLink = $listObject['name'];
            } else if ($this->objectLabel == "zspl") {
                $listObjectLink = "<a href=\"?zspl={$listObject["zspl_id"]}$actionstr\">{$listObject["name"]}</a>";
            } else if ($this->objectLabel == "division") {
                $listObjectLink = "<a href=\"?division={$listObject["division_id"]}$actionstr\">{$listObject["name"]}</a>";
            }


            $assignedVehiclesCnt = zero2dash($assignedVehiclesCnt);
            $replacementVehiclesCnt = zero2dash($replacementVehiclesCnt);
            $freeStationsCnt = zero2dash($freeStationsCnt);
            $stationCnt = zero2dash($stationCnt);
            $ebgStationCnt = zero2dash($ebgStationCnt);
            $aixStationCnt = zero2dash($aixStationCnt);
            $otherStationCnt = zero2dash($otherStationCnt);


            $stationstr = "$ebgStationCnt / $aixStationCnt / $otherStationCnt";

            $this->overview .= <<<HEREDOC

  <tr>
    <td>$oz</td>
    <td>$listObjectLink</td>
    <td>$assignedVehiclesCnt</td>
    <td>$replacementVehiclesCnt</td>
    <td>$freeStationsCnt</td>
    <td>$stationCnt</td>
    <td>$stationstr</td>
HEREDOC;

            if ($showGps)
                $this->overview .= <<<HEREDOC
    <td><a href="https://www.openstreetmap.org/?mlat={$listObject["lat"]}&mlon={$listObject["lon"]}&zoom=17" title="Link zu open streetmap" target="new">{$listObject["lat"]}, {$listObject["lon"]}</a>
HEREDOC;

            $this->overview .= "
  </tr>";


            $this->sumTotalES += $ebgStationCnt;
            $this->sumTotalAS += $aixStationCnt;
            $this->sumTotalFS += $freeStationsCnt;
            $this->sumTotalS += $stationCnt;
            $this->sumTotalAV += $assignedVehiclesCnt;
            $this->sumTotalEV += $replacementVehiclesCnt;
        }

        $sumTotalAV = zero2dash($this->sumTotalAV);
        $sumTotalEV = zero2dash($this->sumTotalEV);
        $sumTotalFS = zero2dash($this->sumTotalFS);
        $sumTotalS = zero2dash($this->sumTotalS);
        $sumTotalES = zero2dash($this->sumTotalES);
        $sumTotalAS = zero2dash($this->sumTotalAS);

        $otherStationCnt = zero2dash($this->sumTotalS - ($this->sumTotalES + $this->sumTotalAS));

        $stationstr = "{$sumTotalES} / {$sumTotalAS} / $otherStationCnt";

        $this->overview .= <<<HEREDOC

  <tr class="summe">
    <th colspan="2">Summe</th>
    <td>{$sumTotalAV}</td>
    <td>{$sumTotalEV}</td>
    <td>{$sumTotalFS}</td>
    <td>{$sumTotalS}</td>
    <td>{$stationstr}</td>
HEREDOC;


        if ($showGps)
            $this->overview .= "
    <td>&nbsp;</td>";

        $this->overview .= "
  </tr>
</table>
";
    }

    function buildOverviewForDivision($division_id, $getaction = '') {
        $this->buildOverview('division', $division_id, $getaction);
    }

    function buildOverviewForZspl($zspl_id, $getaction = '') {
        $this->buildOverview('zspl', $zspl_id, $getaction);
    }

    function buildSummary() {
        /*
        if($this->sumTotalS==0)
            $this->stationstr='0';
        else
            $this->stationstr=$this->sumTotalS.' ('.$this->sumTotalES.' / '.$this->sumTotalAS.' / '.($this->sumTotalS-($this->sumTotalES+$this->sumTotalAS)).') ';

        if($this->sumTotalS!=0)
            $this->ratioVehicleStation=ceil($this->sumTotalV/$this->sumTotalS*100).' % ';
        else
            $this->ratioVehicleStation=' ';

        if($this->sumTotalAV!=0)
            $this->ratioAssignedVehicles=$this->sumTotalAV.' ('.ceil($this->sumTotalAV/$this->sumTotalV*100).' %)';
        else $this->ratioAssignedVehicles='';
        */
    }

    function buildSummaryForDepot($zsp) {
        $this->showSupportEmails = true;

        $currentDepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($zsp);
        $this->overviewHeading = 'Übersicht: ZSP ' . $currentDepot['name'] . ' (OZ: ' . $currentDepot['dp_depot_id'] . ') ';

        $this->sumTotalV = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesCnt('depot', $zsp);

        $this->sumTotalS = $this->ladeLeitWartePtr->stationsPtr->getStationsCnt('depot', $zsp);

        $this->sumTotalES = $this->ladeLeitWartePtr->stationsPtr->getStationsCnt('depot', $zsp, array('depots.stationprovider', '=', 1));


        $this->sumTotalAS = $this->ladeLeitWartePtr->stationsPtr->getStationsCnt('depot', $zsp, array('depots.stationprovider', '=', 2));


        $this->sumTotalAV = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesCnt('depot', $zsp, true);

        $this->buildSummary();
    }

    function buildVehiclesStationsTable($zsp) {
        $this->vehiclesAndStations = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesAndStations($zsp);
    }

    function printContent() {
        include("pages/common/vehicles_stations_overview.php");
    }
}
