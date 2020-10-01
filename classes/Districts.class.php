<?php
/**
 * depots.class.php
 * Klasse für alle database
 * @author Pradeep Mohan
 */

/**
 * Class to handle depots
 */
class Districts extends LadeLeitWarte {
    protected $dataSrcPtr;

    function __construct(DataSrc $dataSrcPtr) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = 'districts';
    }

    /***
     * getForVehicle
     * CommonFunctions_VehicleManagement.class.php, FuhrparksteuerController.class.php, SalesController.class.php
     * @param integer $vehicle_id
     * @return array
     */
    function getForVehicle($vehicle_id, $depot_id = null) {
        $days = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');

        $result = $this->newQuery();

        if ($depot_id)
            $result->where('districts.depot_id', '=', $depot_id);

        $result->multipleAndWhere('districts.vehicle_' . $days[0], '=', $vehicle_id,
            'OR', 'districts.vehicle_' . $days[1], '=', $vehicle_id,
            'OR', 'districts.vehicle_' . $days[2], '=', $vehicle_id,
            'OR', 'districts.vehicle_' . $days[3], '=', $vehicle_id,
            'OR', 'districts.vehicle_' . $days[4], '=', $vehicle_id,
            'OR', 'districts.vehicle_' . $days[5], '=', $vehicle_id,
            'OR', 'districts.vehicle_' . $days[6], '=', $vehicle_id);

        return $result->get('*');
    }

    /***
     * insertNewDistrict
     * used in CommonFunctions_VehicleManagement.class.php SalesController.class.php
     * @param integer $vehicle_id
     * @param integer $depotid
     */
    function insertNewDistrict($vehicle_id, $depotid) {
        $defaultDistrictName = 'Voreingestellter Bezirk';
        $defaultSOC = 100;
        $defaultTime = '08:00:00';
        $days = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat');
        $insertData = array(
            'depot_id' => $depotid,
            'name' => $defaultDistrictName
        );
        foreach ($days as $day) {
            $insertData['vehicle_' . $day] = $vehicle_id;

            $insertData['departure_' . $day] = $defaultTime;
            $insertData['required_soc_' . $day] = $defaultSOC;
        }
        return $this->newQuery()->insert($insertData);
    }
}