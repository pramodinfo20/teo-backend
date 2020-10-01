<?php
/**
 * zspl.class.php
 * Klasse für alle divisions
 * @author Pradeep Mohan
 */

/**
 * Class to handle ZSPL
 */
class Zspl extends LadeLeitWarte {

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

    /***
     * getAllInUnitWithFilters
     * Used primarily in the FPS Controller.
     * @param array $unitParams array('zspl_id'=>$this->zspl) single WHERE statement to get depots from either the division or the ZSPL
     * @param array $filterParams array('vehicles','stations') one or more tablenames for JOIN statements to remove the rows where depot_id is NULL in either vehicles or stations
     */
    function getAllInUnitWithFilters($unitParams, $filterParams) {
        $result = $this->newQuery();

        if (isset($unitParams)) {
            foreach ($unitParams as $key => $val)
                $result->where($key, '=', $val);
        }

        $result->where('zspl.dp_zspl_id', 'IS', 'NOT NULL')
            ->join('depots', 'zspl.zspl_id=depots.zspl_id', 'INNER JOIN');

        if (isset($filterParams)) {
            foreach ($filterParams as $val)
                $result->join($val, $val . '.depot_id=depots.depot_id', 'INNER JOIN');
        }

        return $result->orderBy('dp_zspl_id', 'ASC')
            ->get('zspl.zspl_id,zspl.name,zspl.dp_zspl_id');
    }


    /** getAllValidZspl
     * used in ZentraleController.class.php returns all the valid ZSPLs
     * @return array
     */
    function getAllValidZspl() {

        return $this->newQuery()
            ->where('dp_zspl_id', '>', 0)
            ->orderBy('dp_zspl_id')
            ->get('*');
    }


    /*** getAllInDivision
     * used in ZentraleController.class.php returns all the valid ZSPLs
     * invalid ZSPLs (without dp_zspl_id) include the unbekannt and fleet ZSPLs
     * @param integer $div
     */
    function getAllInDivision($div) {

        return $this->newQuery()
            ->where('division_id', '=', $div)
            ->orderBy('dp_zspl_id')
            ->get('*');
    }

    /*** getAllValidInDivision
     * used in FuhrparksteuerController.class.php
     * @param integer $div
     */
    function getAllValidInDivision($div) {

        return $this->newQuery()
            ->where('division_id', '=', $div)
            ->where('dp_zspl_id', '>', 0)
            ->orderBy('dp_zspl_id')
            ->get('*');
    }

    /*** getZsplsWithAssignedVehicles
     * used in CommonFunctions_ShowOZSelect_AssignedVehicles.class.php
     * @param integer|null $div
     * @return array
     */
    function getZsplsWithAssignedVehicles($div = null) {

        $result = $this->ladeLeitWartePtr->zsplPtr->newQuery();
        if ($div)
            $result->where('zspl.division_id', '=', $div);

        return $result->where('depots.dp_depot_id', 'IS', 'NOT NULL')
            ->where('depots.dp_depot_id', '>', 0)
            ->join('depots', 'zspl.zspl_id=depots.zspl_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('stations', 'stations.station_id=vehicles.station_id', 'INNER JOIN')
            ->orderBy('zspl.dp_zspl_id')
            ->get('DISTINCT(zspl.zspl_id),zspl.name,zspl.dp_zspl_id');
    }


    /*** getZsplsWithFreeStations
     * Used in CommonFunctions_ShowOZSelect_FreeStations.class.php
     * @param integer $div
     * @return array
     */
    function getZsplsWithFreeStations($div) {

        return $this->newQuery()
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('zspl.division_id', '=', $div)
            ->join('depots', 'zspl.zspl_id=depots.zspl_id', 'INNER JOIN')
            ->join('stations', 'stations.depot_id=depots.depot_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->orderBy('zspl.dp_zspl_id')
            ->get('DISTINCT(zspl.zspl_id),zspl.name,zspl.dp_zspl_id');
    }

    /**
     * returns only those ZSPLs whose depots are assigned to the user role.
     * if $div is given, also filters ZSPLs by division
     * User Role is either chrginfra_ebg or chrginfra_aix
     *
     * @param integer $stationprovider 1 is ebg and 2 is aix
     * @return array
     */
    function getZsplsForChrgInfra($stationprovider, $div) {
        $result = $this->newQuery()
            ->where('depots.stationprovider', '=', $stationprovider);
        if ($div)
            $result->where('depots.division_id', '=', $div);

        return $result->join('depots', 'zspl.zspl_id=depots.zspl_id', 'INNER JOIN')
            ->get('DISTINCT(zspl.zspl_id),zspl.name,zspl.dp_zspl_id');


    }

}