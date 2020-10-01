<?php
/**
 * Departures.class.php
 * Class to handle second departure times of vehicles
 * @author Pradeep Mohan
 */

/**
 * Class to handle second departure times of vehicles
 */
class Departures extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

    function getForVehicle($vehicle_id) {
        $days = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');

        $results = $this->newQuery()->where('vehicle_id', '=', $vehicle_id)->get('*');

        $thisvehicle = array();
        if (!empty($results)) {
            foreach ($results as $result) {

                $thisvehicle['second_departure_' . $result['day']] = $result['time'];
                $thisvehicle['second_required_soc_' . $result['day']] = $result['soc'];

            }
            $thisvehicle['vehicle_id'] = $vehicle_id;
        }
        return $thisvehicle;
    }

    /** replaced thse functions with direct queries
     * function saveMultipleWhere($updateCols,$updateVals,$whereStmts)
     * {
     * $whereParam=array();
     * foreach($whereStmts as $whereStmt)
     * $whereParams[]=array('colname'=>$whereStmt[0],'whereop'=>$whereStmt[1],'colval'=>$whereStmt[2]);
     *
     * $status=$this->dataSrcPtr->update('departures',$updateCols,$updateVals,$whereParams);
     * return $status;
     * }
     *
     * function deleteMultipleWhere($whereStmts)
     * {
     * $whereParam=array();
     * foreach($whereStmts as $whereStmt)
     * $whereParams[]=array('colname'=>$whereStmt[0],'whereop'=>$whereStmt[1],'colval'=>$whereStmt[2]);
     *
     * $status=$this->dataSrcPtr->delete('departures',$whereParams);
     * return $status;
     * }
     **/
    function add($insertVals, $addtionalParams = null) {
        extract($insertVals);

        $trimmed = trim($time);

        if (preg_match("/^[01]?[0-9]$/", $trimmed))
            $trimmed = "$trimmed:00";

        if (preg_match("/^[01]?[0-9]:[0-5][0-9](:[0-5][0-9])?$/", $trimmed, $match)) {
            $sql = "insert into departures (district_id, vehicle_id, day, \"time\", soc) "
                . "values ($district_id, $vehicle_id, '$day', '{$match[0]}', $soc);";

            if ($this->newQuery()->query($sql))
                return true;
        }
        return false;
// 	        $id=$this->dataSrcPtr->insert ($this->tableName,$insertVals);
// 		return $id;
    }


}