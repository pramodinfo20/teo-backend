<?php
/**
 * CSVTemplates.class.php
 * Class to handle CSV Templates
 * @author Pradeep Mohan
 */

/**
 *
 *
 */
class CSVTemplates extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

}
