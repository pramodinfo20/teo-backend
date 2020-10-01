<?php

class DbhistoryController extends PageController {

    protected $queries;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {


        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        //rows per page
        $this->numrows = 50;
        //total rows
        $this->totalrows = 500;

        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->enqueueStylesheet('tablesorter-default', "css/theme.default.css");
        $this->displayHeader->enqueueJs("jquery-tablesorter", "js/jquery.tablesorter.min.js");
        $this->displayHeader->enqueueJs("jquery-tablesorter-pager", "js/jquery.tablesorter.pager.js");
        $this->displayHeader->enqueueJs("jquery-tablesorter-widgets", "js/jquery.tablesorter.widgets.js");
        $this->displayHeader->enqueueJs("sts-custom-dbhistory", "js/sts-custom-dbhistory.js");

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . 'test');

        $action = $this->requestPtr->getProperty('action');


        if (isset($action))
            call_user_func(array($this, $action));

        $this->displayHeader->printContent();

// 		$result=$this->ladeLeitWartePtr->dbHistoryPtr->populateTable($this->numrows);
// 		$result=$this->processResultSet($result);
        $result = array();
        $headings[]["headingone"] = array('ID', 'Tabelle', 'Update Columns', 'Alte Werte', 'Neue Werte', 'Timestamp', 'Benutzer Name', 'WHERE', 'Affected Ids');
        $result = array_merge($headings, $result);
        $this->dbqueries = new DisplayTable($result, array('id' => 'db_history_list'));
        $this->printContent();
    }

    function processResultSet($result) {
        foreach ($result as &$oneupdate) {
            if (!empty($oneupdate["userid"])) {
                $user = $this->ladeLeitWartePtr->allUsersPtr->getFromId($oneupdate["userid"]);
                $oneupdate["username"] = $user["username"];
            }


            $oneupdate["updatecols"] = implode('<br>', unserialize($oneupdate["updatecols"]));
            $oneupdate["oldvals"] = unserialize($oneupdate["oldvals"]);
            $str = '';

            if (!empty($oneupdate["oldvals"]))
                foreach ($oneupdate["oldvals"] as $val) {
                    if (!isset($val)) $str .= '--<br>';
                    else $str .= $val . '<br>';
                }

            $oneupdate["oldvals"] = $str;

            $oneupdate["newvals"] = unserialize($oneupdate["newvals"]);

            $str = '';

            if (!empty($oneupdate["newvals"]))
                foreach ($oneupdate["newvals"] as $val) {
                    if (!isset($val)) $str .= '--<br>';
                    else $str .= $val . '--<br>';
                }

            $oneupdate["newvals"] = $str;
            unset($oneupdate['userid']);
        }
        return $result;

    }

    function ajaxRows() {
        $page = $this->requestPtr->getProperty('page');
        $size = $this->requestPtr->getProperty('size');
        $fcol = $this->requestPtr->getProperty('filter');
        $scol = $this->requestPtr->getProperty('column'); //1 desc 0 asc

        $result['headers'] = array('queryid', 'tablename', 'updatecols', 'oldvals', 'newvals', 'update_timestamp', 'username', 'where_stmt', 'affected_ids');

        $rows = $this->ladeLeitWartePtr->dbHistoryPtr->populateTable($page, $size, $fcol, $scol);
        $rows = $this->processResultSet($rows);
        $result['total_rows'] = $this->totalrows;
        $result['fcol'] = json_encode($fcol);
        $result['page'] = $page;
        $result['size'] = $size;
        $result['rows'] = $rows;

        echo json_encode($result);
        exit(0);
        //$headings[]["headingone"]=array('ID','Tabelle','Update Columns','Alte Werte','Neue Werte','Benutzer Name','Timestamp','WHERE','Affected Ids');


    }

}