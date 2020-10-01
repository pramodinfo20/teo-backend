<?php
/**
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle
 *
 */
class DbHistory extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $requestPtr;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;
        $this->requestPtr = new Request();
    }

    function populateTable($page = 0, $size = 25, $fcol = null, $scol = null) {
        $result = $this->newQuery();
        $headers = array('queryid', 'tablename', 'updatecols', 'oldvals', 'newvals', 'update_timestamp', 'username', 'where_stmt', 'affected_ids');

        if (!empty($fcol)) {
            foreach ($fcol as $key => $val)
                $result->where($headers[$key], 'ILIKE', '%' . $val . '%');
        }

        if (!empty($scol)) {

            foreach ($scol as $key => $val) {
                if ($val == 1) $sortorder = 'DESC';
                else $sortorder = 'ASC';
                $result->orderBy($headers[$key], $sortorder);
            }


        } else
            $result->orderBy('queryid', 'DESC');

        return $result->offset($page * $size)->limit($size)->get('*');

    }

}
