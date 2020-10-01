<?php
/**
 * GpsMaps.class.php
 * Klasse für alle database
 * @author Pradeep Mohan
 */

/**
 * Class to handle ecu_parameters
 */
class GpsMaps extends LadeLeitWarte {

    function __construct(LadeLeitWarte $leitWartePtr, $tableName = 'gps_maps') {
        $this->dataSrcPtr = $leitWartePtr->dataSrcPtr;
        $this->tableName = $tableName;
    }

}