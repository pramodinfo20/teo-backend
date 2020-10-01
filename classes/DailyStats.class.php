<?php
/**
 * DailyStats.class.php
 * Klasse für daily stats
 * @author Pradeep Mohan
 */

/**
 * Class to handle daily stats
 *
 */
class DailyStats extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $errormsgs;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

    /***
     * returns daily stats for all vehicles in the vehicles table
     * @return array
     */
    function getStatsForAllVehicles() {
        return $this->newQuery('vehicles')->join('daily_stats', 'vehicles.vehicle_id=daily_stats.vehicle_id', 'INNER JOIN')
            ->groupBy('vehicles.vehicle_id')->get('vehicles.vehicle_id,vehicles.vin,vehicles.code');

    }

    /***
     * returns the daily stats for this vehicle id
     * use in AftersalesController and EnggController
     * @param integer $vehicle_id
     * @returns array
     */
    function getStatsForVehicleId($vehicle_id) {
        return $this->newQuery('')->where('vehicle_id', '=', $vehicle_id)
            ->get('*');
    }
}
