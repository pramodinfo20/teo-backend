<?php

/**
 * Klasse für PostgreSQL database queries
 */
class QueryPgsql extends QueryMain {

    /**
     * Konstruktor
     *
     * @param DatabasePgsql $databaseobj
     */
    function __construct($databaseobj) {
        $this->dbconn = $databaseobj->getDBConnection();
        $this->databaseStructPtr = new DatabaseStructureCommon1 ();
    }

    /**
     * selectQueryBuilder Builds a SQL SELECT query with or without additional WHERE parameters
     * @param string $selectTable Name of the database table to query
     * @param array $selectCols If this parameter is passed, only these columns are returned from the table
     * @param array $whereStmtParams
     *      Array of parameters for the WHERE statement.
     *      Uses format array('colname'=>'Contains the name of the column',
     *                'colval'=>'Contains the value of the column',
     *                'whereop'=>'Contains the WHERE operand to be used z.B.  =,>=,>,<,<= u.s.w ')
     * @param array $orderStmtParams
     *      Array of parameters for the ORDER BY clause
     *      Uses format array('colname'=>'Contains the name of the column',
     *                'ordertype'=>'Contains the order type ASC DESC etc')
     * @param array $joinStmtParams
     *      Array of parameters for the JOIN statements
     *      Uses format array('jointype'=>'Contains the type of join z.B. LEFT JOIN, INNER JOIN, RIGHT JOIN etc',
     *                'jointable'=>'Contains the name of the table to be joined',
     *                'onleft'=>'<typejoin> <jointable> ON <onleft>=<onright>',
     *                'onright'=>'<typejoin> <jointable> ON <onleft>=<onright>')
     * @param boolean $showHeading If true returns the Headings declared in DatabaseStructure for the columns in $selectCols or all columns.
     * @return array Array of rows of the result
     */
    function selectQueryBuilder($selectTable, $selectCols, $whereStmtParams, $orderStmtParams, $joinStmtParams, $showHeading = false, $subsql_ret = false, &$cnt = 1, $returnType = 'assoc') {
        $rows = array();
        $wherestr = $orderstr = $joinstr = $groupstr = "";
        $whereVals = array();
        $query = "";
        $tableName = $this->databaseStructPtr->getTableName($selectTable);
        $allCols = $this->databaseStructPtr->getTableCols($selectTable);

        if (is_array($whereStmtParams)) {
            foreach ($whereStmtParams as $whereStmtKey => $whereStmt) {
                $whereStmt['colname'] = trim($whereStmt['colname']);
                $conditionop = '';
                if ($whereStmtKey > 0) //if there is only one WHERE statement, there is no need for conditional operators!
                {
                    if (strpos($whereStmt['colname'], 'OR ') !== FALSE && strpos($whereStmt['colname'], 'OR ') == 0) //true only if OR occurs at the start of the whereStmt
                    {
                        $splitWhere = explode(" ", $whereStmt['colname']);
                        $conditionop = ' OR ';
                        $whereStmt['colname'] = trim($splitWhere[1]);
                    } else {
                        $conditionop = ' AND ';
                    }

                }

                if (strpos($whereStmt['colname'], ".")) {
                    $wherecolname = explode(".", $whereStmt['colname']);
                    $processedCol = $this->databaseStructPtr->getColName($wherecolname[0], $wherecolname[1]);
                } else
                    $processedCol = $this->databaseStructPtr->getColName($tableName, $whereStmt['colname']);


//				// @todo checkpages if(!empty($whereStmt['colval'])) //why should this matter?
//				might have the answer.. this could be used for statements like colname=vehicle_id whereop=IS NOT NULL...
//so if the third param is empty, then the else case is invoked.. problem is now we used raw params which we processed to the required format.. so this method might not work
                {
                    if (is_array($whereStmt['colval']) && !empty($whereStmt['colval'])) // for statements like vehicle_id IN (1,5,6)
                    {
                        if (array_key_exists('subsql', $whereStmt['colval'])) {
                            $subsql = $whereStmt['colval']['subsql'];

                            $subsql_query = $this->selectQueryBuilder($subsql['tableName'], $subsql['selectCols'], $subsql['whereParams'], null, null, null, true, $cnt);
                            //echo $subsql;
                            //$subWhereParams[]=array('colname'=>'districts.depot_id','whereop'=>'=','colval'=>$currentDistrict['depot_id']);
// 							$subSql=array('subsql'=>array('tableName'=>'districts','whereParams'=>$subWhereParams));

                            $whereVals = array_merge($whereVals, $subsql_query[0]);
                            $whereclauses[] = $conditionop . $processedCol . " " . $whereStmt['whereop'] . " (" . $subsql_query[1] . ") ";
                        } else {
                            $whereprepholder = '(';
                            foreach ($whereStmt['colval'] as $val) {
                                $whereprepholders[] = "$$cnt";
                                $whereVals[] = $val;
                                $cnt++;

                            }

                            $whereprepholder .= implode(",", $whereprepholders) . ')';
                            $whereclauses[] = $conditionop . $processedCol . " " . $whereStmt['whereop'] . " " . $whereprepholder;
                        }
                    } else {
                        if (trim($whereStmt['whereop']) == 'LIKE')
                            $whereclauses[] = $conditionop . "lower($processedCol) " . $whereStmt['whereop'] . " LOWER($$cnt)";
                        else if (trim($whereStmt['whereop']) == 'IS')
                            $whereclauses[] = $conditionop . $processedCol . ' ' . $whereStmt['whereop'] . ' ' . $whereStmt['colval'];
                        else
                            $whereclauses[] = $conditionop . $processedCol . $whereStmt['whereop'] . "$$cnt";

                        if (trim($whereStmt['whereop']) != 'IS') {
                            $whereVals[] = $whereStmt['colval'];
                            $cnt++;

                        }
                    }


                }
                // @todo checkpages What is the use case scenario for this condition? check pages/*.php for this condition
// 				else
// 				{
// 					$whereclauses[]=$processedCol." ".$whereStmt['whereop']." ";
// 				}

            }
            $wherestr = ' WHERE ' . implode(' ', $whereclauses);


        }
        if (is_array($joinStmtParams)) {
            foreach ($joinStmtParams as $joinStmt) {
                //continue here

                $typeJoin = $joinStmt['jointype'];
                $joinTable = $joinStmt['jointable'];

                $joinStmtColumns = array();

                foreach ($joinStmt["joinColumns"] as $joinColumns) {

                    $leftTableCol = explode(".", $joinColumns[0]);
                    $rightTableCol = explode(".", $joinColumns[1]);
                    $processedTableLeft = $this->databaseStructPtr->getTableName($leftTableCol[0]);
                    $processedColLeft = $this->databaseStructPtr->getColName($leftTableCol[0], $leftTableCol[1]);
                    $processedTableRight = $this->databaseStructPtr->getTableName($rightTableCol[0]);
                    $processedColRight = $this->databaseStructPtr->getColName($rightTableCol[0], $rightTableCol[1]);
                    $joinStmtColumns[] = $processedColLeft . ' = ' . $processedColRight;
                }
                $joinClauses[] = $typeJoin . ' ' . $joinTable . ' ON ' . implode(" AND ", $joinStmtColumns);
                //echo "<br>inner ".$typeJoin.' '.$joinTable.' ON '.$processedTableLeft.".".$processedColLeft.' = '.$processedTableRight.".".$processedColRight;

            }

            $joinstr = ' ' . implode(' ', $joinClauses);
        }

        if (is_array($orderStmtParams)) {

            foreach ($orderStmtParams as $orderStmt) {
                $processedCol = $this->databaseStructPtr->getColName($tableName, $orderStmt['colname']);
                $orderType = $orderStmt['ordertype'];
                $orderclauses[] = $processedCol . " " . $orderType;

            }

            $orderstr = ' ORDER BY ' . implode(' , ', $orderclauses);

        }
        $groupStmtParams = null;
        //@todo 20160801 implementation pending
        if (isset($groupStmtParams) && is_array($groupStmtParams)) {

            foreach ($groupStmtParams as $groupStmt) {
                $processedCol = $this->databaseStructPtr->getColName($tableName, $groupStmt['colname']);
                $groupclauses[] = $processedCol . " ";

            }

            $groupstr = ' GROUP BY ' . implode(' , ', $groupclauses);

        }

        if (!is_array($selectCols)) {
            if (is_array($joinStmtParams)) {
                $allJoinCols = $allCols;
                foreach ($joinStmtParams as $joinStmt) {
                    $joinTable = $joinStmt['jointable'];
                    $joinColsNew = $this->databaseStructPtr->getTableCols($joinTable);

// 					if(isset($joinStmt['joinAlias']))
// 					{
// 						foreach($joinStmt['joinAlias'] as $jalias)
// 						{
// 							$params=explode('.'$jalias);
// 							//@todo continue here if you want to pass ALIAS as the last argument of a join statement..
//									array('INNER JOIN','depots',array(array('vehicles.depot_id','depots.depot_id')),array('depots.name AS dname'))
// 						}
// 						print_r($this->databaseStructPtr->getTableCols ( $joinTable));
// 						print_r($joinStmt['joinAlias']);

// 					}
                    $allJoinCols = array_merge($allJoinCols, $joinColsNew);
                }
                $query = "SELECT " . implode(',', $allJoinCols) . " FROM " . $tableName . $joinstr . $wherestr . $orderstr;

            } else
                $query = "SELECT " . implode(',', $allCols) . " FROM " . $tableName . $joinstr . $wherestr . $orderstr;

        } else {
            $selectColsProcessed = array();
            foreach ($selectCols as $eachCol)
                $selectColsProcessed[] = $this->databaseStructPtr->getColName($tableName, trim($eachCol));
            $query = "SELECT " . implode(',', $selectColsProcessed) . " FROM " . $tableName . $joinstr . $wherestr . $orderstr; //@todo 20160801 add groupstr here
        }

        if ($subsql_ret) {
            return array($whereVals, $query);
        }

        //else
// 			echo $query."<br>"; print_r($whereVals);
        $this->prepare($query);
        $this->execute($whereVals);

        if (!$this->result) {
            $lastError = pg_last_error($this->dbconn);
            throw new Exception("Datenbank Abfrage fehler");
            return false;
        }

        $rows = array();

        if ($showHeading === true) {

            $rows [] = $this->databaseStructPtr->getTableHeadings($tableName, $selectCols);
        }

        while ($row = $this->fetchArray()) {
            if (!is_array($row)) continue;
            $rows [] = $row;
        }

        return $rows;


    }


    /**
     * insert
     *
     * @param array $insertTable
     * @param array $insertVals associative array, array keys are name of SQL table fields, array values are the value of the fields
     * @return integer ID of the edited row
     */

    function insert($insertTable, $insertVals) {
        $sequencename = $this->databaseStructPtr->getSequenceName($insertTable);

        pg_insert($this->dbconn, $insertTable, $insertVals);
        if (!empty($sequencename)) {
            $result = $this->query("SELECT currval('$sequencename'); ");
            while ($row = $this->fetchArray())
                return $row['currval'];
        } else
            return true;

    }

    /**
     * insertMultiple
     *
     * @param array $insertTable
     * @param array $insertCols column names
     * @param array $insertVals values
     * @return integer ID of the edited row
     */

    function insertMultiple($insertTable, $insertCols, $insertVals) {
        $whereVals = array();
        $whereprepholder_complete = array();
        $cnt = 1;
        foreach ($insertVals as $insertVal) {
            $whereprepholders = array();
            $whereprepholder = '(';
            foreach ($insertVal as $val) {
                $whereprepholders[] = "$$cnt";
                $whereVals[] = $val;
                $cnt++;

            }

            $whereprepholder .= implode(",", $whereprepholders) . ')';
            $whereprepholder_complete[] = $whereprepholder;
            unset($whereprepholders);
        }

        $query = "INSERT INTO " . $insertTable . " (" . implode(',', $insertCols) . ")" . " VALUES " . implode(",\r\n", $whereprepholder_complete);
        unset($whereprepholder_complete);
        $this->prepare($query);
        $this->execute($whereVals);

        if ($this->result === false)
            return pg_last_error($this->dbconn);
        else
            return pg_affected_rows($this->result);


    }

    /**
     * delete
     *
     * @param array $deleteTable
     * @param array $whereParams associative array, array keys are name of SQL table fields, array values are the value of the fields
     * @return boolean returns TRUE successful
     */

    function delete($deleteTable, $whereParams) {
        $editedWhereParams = array();
        foreach ($whereParams as $whereParam) {
            $key = $whereParam['colname'];
            $val = $whereParam['colval'];
            $editedWhereParams[$key] = $val;
        }
        return pg_delete($this->dbconn, $deleteTable, $editedWhereParams);


    }

    /**
     * update Temp
     *
     * @param array $updateTable
     * @param array $updateCols
     * @param array $updateVals
     * @param array $whereStmtParams
     * @return integer ID of the edited row
     */

    function update($updateTable, $updateCols, $updateVals, $whereStmtParams) {
        $rows = array();
        $wherestr = "";
        $orderstr = "";
        $joinstr = "";
        $whereVals = array();
        $query = "";
        $tableName = $this->databaseStructPtr->getTableName($updateTable);
        $allCols = $this->databaseStructPtr->getTableCols($updateTable, false);
// 		UPDATE districts SET name='Showfahrt', required_soc_mon='98', required_soc_tue='98', required_soc_wed='98', required_soc_thu='98', required_soc_fri='98', required_soc_sat='98', required_soc_sun='98', departure_mon = 08:00:00, departure_tue = 08:00:00, departure_wed = 08:00:00, departure_thu = 08:00:00, departure_fri = 08:00:00, departure_sat = 08:00:00, departure_sun = 07:00:00, vehicle_mon='3', vehicle_tue='3', vehicle_wed='3', vehicle_thu='3', vehicle_fri='3', vehicle_sat='3', vehicle_sun='3' WHERE district_id='4' Database Query Failed!

        $updateValsPro = array();
        $cnt = 1;
        foreach ($updateCols as $updateCol) {
            $updateColsPro[] = $allCols[$updateCol] . "=$$cnt";
            $cnt++;
        }

        if (is_array($whereStmtParams)) {
            foreach ($whereStmtParams as $whereStmt) {

                if (strpos($whereStmt['colname'], ".")) {
                    $wherecolname = explode(".", $whereStmt['colname']);
                    $processedCol = $this->databaseStructPtr->getColName($wherecolname[0], $wherecolname[1], false);
                } else
                    $processedCol = $this->databaseStructPtr->getColName($tableName, $whereStmt['colname'], false);

                if (!empty($whereStmt['colval'])) {
                    if (is_array($whereStmt['colval']) && !empty($whereStmt['colval'])) {
                        if (array_key_exists('subsql', $whereStmt['colval'])) {
                            $subsql = $whereStmt['colval']['subsql'];

                            $subsql_query = $this->selectQueryBuilder($subsql['tableName'], $subsql['selectCols'], $subsql['whereParams'], null, null, null, true, $cnt);
                            $whereVals = array_merge($whereVals, $subsql_query[0]);
                            $whereclauses[] = $processedCol . " " . $whereStmt['whereop'] . " (" . $subsql_query[1] . ") ";
                        } else {
                            $whereprepholder = '(';
                            foreach ($whereStmt['colval'] as $val) {
                                $whereprepholders[] = "$$cnt";
                                $whereVals[] = $val;
                                $cnt++;

                            }

                            $whereprepholder .= implode(",", $whereprepholders) . ')';
                            $whereclauses[] = $processedCol . " " . $whereStmt['whereop'] . " " . $whereprepholder;
                        }
                    } else {
                        if ($whereStmt['whereop'] == 'LIKE')
                            $whereclauses[] = "lower($processedCol)" . $whereStmt['whereop'] . "LOWER($$cnt)";
                        else
                            $whereclauses[] = $processedCol . $whereStmt['whereop'] . "$$cnt";
                        $whereVals[] = $whereStmt['colval'];
                        $cnt++;
                    }


                } else {
                    $whereclauses[] = $processedCol . " " . $whereStmt['whereop'] . " ";
                }

            }

            $wherestr = ' WHERE ' . implode(' AND ', $whereclauses);

        }

        $query = "UPDATE " . $tableName . " SET " . implode(",", $updateColsPro) . $wherestr;
// 		echo '<br>'.$query.'<br>'; print_r($updateVals);
        $this->prepare($query);
        $vals = array_merge($updateVals, $whereVals);
        $returnval = $this->execute($vals);
        if (pg_last_error($this->dbconn))
            return false;
        else
            return 1;

    }

    /**
     * specialSql Temp
     *
     * @param string $query
     * @return array Result from the sepcial query
     */
    function specialSql($query) {
        $rows = [];
        $this->result = $this->query($query);
        while ($row = $this->fetchArray()) {
            $rows [] = $row;
        }
        return $rows;

    }

    /**
     * specialSqlPrepare Temp
     *
     * @param string $query
     * @return array Result from the sepcial query
     */
    function specialSqlPrepare($prepQuery, $vals) {
        $rows = [];
        $this->prepare($prepQuery);
        $this->execute($vals);

        while ($row = $this->fetchArray()) {
            $rows [] = $row;
        }
        return $rows;

    }


    /**
     * prepare Prepares the query using pg_prepare function
     *
     * @param array $querypre If this parameter is passed, only these columns are returned from the table
     * @param string $queryname Name of the query to be prepared. Has to be unique for each query. Easier if left blank.
     * @return array Result of the pg_prepare()
     */
    function prepare($querypre, $queryname = '') {
        // Prepare a query for execution
        $this->result = pg_prepare($this->dbconn, $queryname, $querypre);
    }

    /**
     * execute Executes the query using pg_execute function
     *
     * @param array $queryvals All the values to be substituted in the prepared statement returned by pg_prepare
     * @param string $queryname Name of the query to be prepared. Has to be unique for each query. Easier if left blank.
     * @return array Result resource as returned by the pg_execute() function
     */

    function execute($queryvals, $queryname = '') {
        // Execute the prepared query.

        $this->result = pg_execute($this->dbconn, $queryname, $queryvals);
    }

    /**
     * Query function
     * This is a Query Function
     *
     * @param string $query
     * @return resource
     */
    public function query($query) {
        try {
            $this->result = @pg_query($this->dbconn, $query);

            if (!$this->result)
                throw new Exception ("Database Query Failed!");
        } catch (Exception $e) {
            die ($e->getMessage());
        }
        return $this->result;
    }

    /**
     * Fetch single row from result
     * fetch one row from the $result as an object
     *
     * @return object
     */
    public function fetchObject() {
        $row = @pg_fetch_object($this->result);
        return $row;
    }

    /**
     * Fetch single row from result
     * fetch one row from the $result as an array
     *
     * @return array
     */
    public function fetchArray() {
        $row = @pg_fetch_assoc($this->result);
        return $row;
    }

    /**
     * Fetch single row from result
     * fetch one row from the $result as an array
     *
     * @return array
     */
    public function fetchNumArray() {
        $row = @pg_fetch_array($this->result);
        return $row;
    }

    /**
     * Count number of rows in the $this->result resource
     *
     * @return integer
     */
    public function getNumRows() {
        return @pg_num_rows($this->result);
    }
}

?>
