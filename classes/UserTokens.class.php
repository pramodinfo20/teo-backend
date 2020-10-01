<?php
/**
 * UserTokens.Class.php
 * Klasse für alle UserTokens
 * @author Pradeep Mohan
 */

/**
 * Class to handle user tokens
 *
 */
class UserTokens extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $requestPtr;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
    }

    function getUserToken($userid) {

        return $this->newQuery()
            ->where('userid', '=', $userid)
            ->get('*');
    }

    function getUserTokenFromSelector($selector) {

        return $this->newQuery()
            ->where('selector', '=', $selector)
            ->getOne('*');
    }

}
