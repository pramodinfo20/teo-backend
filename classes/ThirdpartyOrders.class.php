<?php
/**
 * vehicles.class.php
 * Klasse für alle vehicles
 * @author Pradeep Mohan
 */

/**
 * Class to handle vehicles
 *
 */
class ThirdPartyOrders extends LadeLeitWarte {
    protected $dataSrcPtr;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

    function orderExists($order_num) {
        return $this->newQuery()->where('order_num', '=', $order_num)->getOne('*');

    }


}
