<?php
/**
 * divisions.class.php
 * Klasse für alle divisions
 * @author Pradeep Mohan
 */

/**
 * Class to handle divisions/Niederlassungen
 */
class Divisions extends LadeLeitWarte {

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;

    }

    /***
     * getAllValidDivisions
     * used in ZentraleController.class.php
     * @return array
     */
    function getAllValidDivisions() {
        return $this->newQuery()->where('dp_division_id', '>', 0)
            ->where('active', '=', 't')->orderBy('dp_division_id')->get('*');
    }

    /***
     * getDPDivision
     * used in ZentraleController.class.php
     * @param integer $dp_div_id
     * @return array
     */
    function getDPDivision($dp_div_id) {
        return $this->newQuery()
            ->where('dp_division_id', '=', $dp_div_id)
            ->orderBy('dp_division_id', 'ASC')
            ->getOne('*');
    }

    /**
     * returns only those divisions whose depots are assigned to the user role.
     * User Role is either chrginfra_ebg or chrginfra_aix
     *
     * @param integer $stationprovider 1 is ebg and 2 is aix
     * @return array
     */
    function getDivsForChrgInfra($stationprovider) {
        return $this->newQuery()
            ->where('depots.stationprovider', '=', $stationprovider)
            ->join('depots', 'divisions.division_id=depots.division_id ', 'INNER JOIN')
            ->get('DISTINCT(divisions.division_id),divisions.name,divisions.dp_division_id');

    }
}