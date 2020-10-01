<?php
/**
 * stations.class.php
 * Klasse für alle Ladepunkte
 * @author Pradeep Mohan
 */

/**
 * Class to handle Ladepunkte/Ladesaüle
 */
class Stations extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $errormsgs;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

    function getLastStationForDepot($depot, $selectCols = null) {
        $whereParams = array();
        $whereParams[] = array('depot_id', '=', $depot);
        $orderParams = array();
        $orderParams[] = array('name', 'DESC');

        $result = $this->getWhere($selectCols, $whereParams, $orderParams);

        if (!empty($result)) return $result[0];
        else return null;
    }

    /**
     * returns all the stations for this single depot
     * Used in ChrginfraController.class.php (2 matches)
     * @param integer $depot
     * @return array
     * @todo 20160809 could replace with the next function getStationsForDepots
     */
    function getStationsForDepot($depot) {
        $result = $this->newQuery()->where("depot_id", "=", $depot)->orderBy('name', 'ASC')->get('*');
        return $result;
    }

    function getStationsForDepots($depots) {
        if (is_array($depots))
            $whereStmt = array('depot_id', 'IN', $depots);
        else
            $whereStmt = array('depot_id', '=', $depots);

        $result = $this->getWhere(null, array($whereStmt),
            array(array('name', 'ASC'))
        );
        return $result;
    }

    function getStationsAndVehiclesForDepots($depots) {
        $result = $this->newQuery();


        if (is_array($depots)) $result->where('depot_id', 'IN', $depots);
        else  $result->where('depot_id', '=', $depots);

        $result = $result->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->orderBy('stations.name')
            ->get('stations.station_id,stations.depot_id,stations.name,restriction_id,restriction_id2,restriction_id3,
								station_power,deactivate,vehicles.vehicle_id');

        return $result;
    }


    //@TODO 20160830 check if this query is correct. do we need to also add stations.station_id IS NOT NULL?
    function getFreeStationsForDepot($depot) {
        $result = $this->getWhereJoin(array('stations.station_id', 'stations.deactivate', 'stations.name', 'stations.vehicle_variant_value_allowed',
            'stations.restriction_id', 'stations.restriction_id2', 'stations.restriction_id3',

        ), array(array("depot_id", "=", $depot), array("vehicles.station_id", " IS", "NULL")),
            array(array('stations.name', 'ASC')),
            array(array('FULL OUTER JOIN', 'vehicles', array(array('vehicles.station_id', 'stations.station_id'))))
        );

        return $result;

    }

    function getFromName($name, $depot, $selectCols = '', $showHeading = false) {
        $whereParams = array();
        $whereParams[] = array('colname' => 'depot_id', 'whereop' => '=', 'colval' => $depot);
        $whereParams[] = array('colname' => 'name', 'whereop' => '=', 'colval' => $name);

        $result = $this->dataSrcPtr->selectAll('stations', $selectCols, $whereParams);
        if (!empty($result)) return $result[0];
        else return NULL;
    }

    function getErrorMsgs() {
        return $this->errormsgs;
    }

    /**
     * counts the stations for this div where vehicle variant allowed is not NULL and station is not already assigned to a vehicle
     * @param integer $division_id
     */
    function getCntVehicleVariantsDivAssigned($division_id) {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->where('divisions.division_id', '=', $division_id)
            ->where('vehicles.station_id', 'IS NULL')
            ->groupBy('stations.vehicle_variant_value_allowed')
            ->get('vehicle_variant_value_allowed,count(stations.vehicle_variant_value_allowed) as scnt');
    }

    function getFreeStationsAlreadyVariantAssigned($variant_value, $specified_stations = null) {
        $result = $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('stations.vehicle_variant_value_allowed', '=', $variant_value)
            ->orderBy('stations.vehicle_variant_update_ts');
        if (!empty($specified_stations))
            $result->where('stations.station_id', 'IN', $specified_stations);

        return $result->get('divisions.division_id,divisions.cost_center,depots.depot_id,stations.station_id,stations.name as sname,depots.name as dname,stations.vehicle_variant_value_allowed,stations.restriction_id');
    }

    /**
     * used in SalesController
     * @param mixed $depot
     * @param mixed $variant_value
     */
    function getFreeStationsAlreadyVariantAssignedForDepot($depot, $variant_value, $specified_stations = null) {
        $result = $this->newQuery()
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('stations.vehicle_variant_value_allowed', '=', $variant_value)
            ->where('stations.depot_id', '=', $depot)
            ->orderBy('stations.vehicle_variant_update_ts');

        if (!empty($specified_stations))
            $result->where('stations.station_id', 'IN', $specified_stations);

        return $result->get('divisions.division_id,divisions.cost_center,depots.depot_id,stations.station_id,stations.name as sname,depots.name as dname,stations.vehicle_variant_value_allowed,stations.restriction_id');
    }

    function newGetFreeStationsVariantDiv($division, $variant_value, $stations) {
        $result = $this->newQuery()
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('stations.vehicle_variant_value_allowed', '=', $variant_value);
        if (!empty($stations))
            $result->where('stations.station_id', 'NOT IN', $stations);

        return $result->where('divisions.division_id', '=', $division)
            ->orderBy('stations.vehicle_variant_update_ts')
            ->get('divisions.division_id,divisions.cost_center,depots.depot_id,depots.dp_depot_id,stations.station_id,stations.name as sname,depots.name as dname,stations.vehicle_variant_value_allowed,stations.restriction_id');
    }

    /**
     * used by function Auszulieferende Fahrzeuge in FuhrparksteuerController to reorder priority
     * @param integer $division
     * @return array
     */
    function getAllFreeStationsWithVariantAssignedForDiv($division) {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('divisions.division_id', '=', $division)
            ->join('transporter_dates', 'stations.station_id=transporter_dates.station_id', 'FULL OUTER JOIN')
            ->orderBy('stations.vehicle_variant_update_ts')
            ->get('depots.depot_id,vehicle_variant_update_ts,depots.dp_depot_id,depots.name as depname,stations.name,stations.station_id,stations.vehicle_variant_value_allowed,transporter_date');

    }

    //@todo 2016-08-01 following two functions are kind of redundant check which can be replaced
    function getCntFreeStationsAlreadyVariantAssigned() {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->groupBy('divisions.division_id,stations.vehicle_variant_value_allowed')
            ->get('divisions.division_id,stations.vehicle_variant_value_allowed,count(stations.vehicle_variant_value_allowed) AS scnt ');
    }

    function getAllDivisionsFreeStationsWithVariantAssigned() {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->groupBy('depots.name,divisions.name,divisions.division_id,stations.vehicle_variant_value_allowed')
            ->orderBy('divisions.division_id,depots.name,stations.vehicle_variant_value_allowed')
            ->get('divisions.name as divname,depots.name as depname,stations.vehicle_variant_value_allowed,count(stations.vehicle_variant_value_allowed) AS scnt ');

    }


    function getCntFreeStationsWithVariantAssignedForDiv($division) {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('divisions.division_id', '=', $division)
            ->groupBy('divisions.name,depots.name,depots.depot_id,divisions.division_id,stations.vehicle_variant_value_allowed')
            ->orderBy('divisions.name,depots.name,depots.depot_id,stations.vehicle_variant_value_allowed')
            ->get('divisions.name as divname,depots.depot_id,depots.name as depname,stations.vehicle_variant_value_allowed,count(stations.vehicle_variant_value_allowed) AS scnt ');

    }

    function getStationsCountWithVariantAssignedForDepot($zsp) {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('depots.depot_id', '=', $zsp)
            ->groupBy('depots.name,depots.dp_depot_id,stations.vehicle_variant_value_allowed')
            ->get('depots.name as depname,depots.dp_depot_id,stations.vehicle_variant_value_allowed,count(stations.vehicle_variant_value_allowed) AS scnt');
    }

    function getStationsCountWithVariantAssignedForDepotVariant($zsp, $variant) {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('stations.vehicle_variant_value_allowed', '=', $variant)
            ->where('depots.depot_id', '=', $zsp)
            ->groupBy('depots.name,depots.dp_depot_id,stations.vehicle_variant_value_allowed')
            ->getOne('depots.name as depname,depots.dp_depot_id,stations.vehicle_variant_value_allowed,count(stations.vehicle_variant_value_allowed) AS scnt');
    }

    function getStationsCountWithVariantAssignedForDivision($div) {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NOT NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'divisions.division_id=depots.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('divisions.division_id', '=', $div)
            ->groupBy('divisions.division_id,stations.vehicle_variant_value_allowed')
            ->get('divisions.division_id,stations.vehicle_variant_value_allowed,count(stations.vehicle_variant_value_allowed) AS scnt');

    }

    function getVariantforStation($station_id) {
        return $this->newQuery()->where('station_id', '=', $station_id)->getVal('vehicle_variant_value_allowed');

    }

    /**
     * gets count of free stations that should be assigned a vehicle variant by the Fuhrparksteuer
     *
     */
    function getFreeStationsToBeAssigned() {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->groupBy('divisions.name,divisions.division_id')
            ->get(' divisions.name,divisions.division_id,count(stations.station_id) as scnt');
    }

    /**
     * gets count of free stations that should be assigned a vehicle variant by the Fuhrparksteuer
     *
     */
    function getFreeStationsToBeAssignedCountForDiv($division_id) {
        return $this->newQuery()->where('stations.vehicle_variant_value_allowed', 'IS', 'NULL')
            ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
            ->join('depots', 'depots.depot_id=stations.depot_id', 'INNER JOIN')
            ->join('divisions', 'depots.division_id=divisions.division_id', 'INNER JOIN')
            ->where('vehicles.station_id', 'IS', 'NULL')
            ->where('divisions.division_id', '=', $division_id)
            ->groupBy('divisions.name,divisions.division_id')
            ->getOne(' divisions.name,divisions.division_id,count(stations.station_id) as scnt');
    }

    function delete($station, $additionalParams = null) {
        //check for vehicles
        $whereParams = array();
        $whereParams = array(array('colname' => 'station_id', 'whereop' => '=', 'colval' => $station['station_id']));
        $result = $this->dataSrcPtr->selectAll('vehicles', null, $whereParams, null, false);

        if (is_array($result) && !empty($result)) {
            $this->errormsgs[] = "Ladepunkt kann nicht gelöscht werden, da noch ein Fahrzeug an Ihm angeschlossen ist.";
            return false;
        }


        $result = $this->dataSrcPtr->delete('stations', $whereParams);
        return true;
    }


    function getRestrictionsForStation($station_id) {
        return $this->newQuery()->where('station_id', '=', $station_id)->getOne('restriction_id', 'restriction_id2', 'restriction_id3');
    }


    function getStationsCnt($colName, $colVal, $whereStmtAddn = null) {
        $qry = $this->newQuery()->join('depots', 'using(depot_id)');
        $qry = $qry->where("depots.{$colName}_id", '=', $colVal);

        if ($whereStmtAddn) {
            $qry = $qry->where($whereStmtAddn[0], $whereStmtAddn[1], $whereStmtAddn[2]);
            $qry = $qry->where('stations.name', ' NOT ILIKE ', 'Schuko%');
        }

        return $qry->getVal('count(*)');
    }
}

