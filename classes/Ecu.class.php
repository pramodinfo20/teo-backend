<?php
/**
 * ecu.class.php
 * Klasse für alle database
 * @author Pradeep Mohan
 */

/**
 * Class to handle ecu_parameters
 */
class Ecu extends LadeLeitWarte {

    function __construct(LadeLeitWarte $leitWartePtr, $tableName) {
        $this->dataSrcPtr = $leitWartePtr->dataSrcPtr;
        $this->tableName = $tableName;
    }

}