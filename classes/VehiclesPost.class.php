<?php
/**
 * VehiclesPost.Class.php
 * Klasse für alle vehicles
 * @author Pradeep Mohan
 */

/**
 * Class to handle vehicles added by the post
 *
 */
class VehiclesPost extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $requestPtr;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
        $this->requestPtr = new Request();
    }

    function getUnprocessedBatches() {
        return $this->newQuery()->join('vehicles', 'vehicles.code=vehicles_post.startakz', 'FULL OUTER JOIN')
            ->where('vehicles.vehicle_id', ' IS ', 'NULL')
            ->get('vehicles_post.tempid,vehicles_post.startakz,vehicles_post.startikz,vehicles_post.cntvehicles,vehicles_post.added_timestamp,vehicles_post.addedby_userid');
    }

    function savebatch($postvars, $userid) {
        extract($postvars);
// 		$startikz=$this->requestPtr->getProperty('startikz');
// 		$startikz=$this->requestPtr->getProperty('startikz');
// 		$startakz=$this->requestPtr->getProperty('startakz');
// 		$tsnumber=$this->requestPtr->getProperty('tsnumber');
// 		$cntvehicles=$this->requestPtr->getProperty('cntvehicles');
// 		$vorhaben=$this->requestPtr->getProperty('');
// 		$vehicle_variant=$this->requestPtr->getProperty('vehicle_variant');

        $currentimestamp = $this->dataSrcPtr->getTimeStamp(0);
        $currentimestamp = $currentimestamp['timenw'];
        $insertVals = array('startikz' => $startikz,
            'startakz' => $startakz,
            'cntvehicles' => $cntvehicles,
            'tsnumber' => $tsnumber,
            'vorhaben' => $vorhaben,
            'vehicle_variant' => $vehicle_variant,
            'added_timestamp' => $currentimestamp, // 0 gets the postgrestimestamp for current time
            'addedby_userid' => $userid);

        return $this->newQuery()->insert($insertVals);

    }
}
