<?php

/**
 * Klasse für PostgreSQL database queries
 */
class NewQueryPgsql extends QueryMain {

    /**
     * Konstruktor
     *
     * @param DatabasePgsql $databaseobj
     */
    function __construct($databaseobj, $tablename = null) {
        $this->dbconn = $databaseobj->getDBConnection();
        $this->databaseStructPtr = $databaseobj->getDBStructure();
        $this->tableName = $tablename;
        $this->whereStmt = '';
        $this->joinStmt = '';
        $this->orderByStmt = '';
        $this->groupByStmt = '';
        $this->limitStr = '';
        $this->offsetStr = '';
        $this->havingStr = '';
        $this->orderStr = '';
        $this->prepareVals = array();
        $this->placeHolderCnt = 1;
        $this->displayquery = 0;
    }

    /**
     * getVal
     * @param string $field colname to get
     * @return array get the first column from the first result row and returns the value
     */
    function getVal($field) {
        $result = $this->get($field);
        //cannot return $result[0][$field]
        //what if the $field is depots.name as dname? so use foreach to return the first (and only
        //element of the result row
        if (!empty($result))
            foreach ($result[0] as $val) return $val;
        else return false;
    }

    /**
     * getVals
     * @param string $field colname to get
     * @return array Returns all result rows from the selected column
     */
    function getVals($field) {
        $result = $this->get($field);
        if (!empty($result))
            return array_column($result, $field);
        return false;
    }

    /**
     * getOne
     * @param string $fields one column name or comma separated string of multiple column names
     * @return array returns the first row from the result
     */
    function getOne($fields = '*') {
        $result = $this->get($fields);
        if (!empty($result))
            return $result[0];
        else return null;
    }

    function displayquery() {
        $this->displayquery = 1;
        return $this;
    }


    /**
     * get
     * $queryPtr->where('vehicle_id','>',500)->get('vehicle_id,vin,code')
     * @param string $fields one column name or comma separated string of multiple column names
     * @param string $indexCol one column name to (re-)index the result associative array. This columns has to be an unique key within the table.
     * @return array result of SQL Query as an associative array
     * @throws Exception
     */
    function get($fields, $indexCol = false) {
        return $this->get_no_parse($this->getProcessedCol($fields), $indexCol);
    }


    /**
     * get_no_parse
     * $queryPtr->where('vehicle_id','>',500)->get('vehicle_id,vin,code')
     * @param string $fields one column name or comma separated string of multiple column names
     * @return array: result of SQL Query as an associative array
     * @throws Exception
     */
    function get_no_parse($expression, $indexCol = false) {
        static $dbgNoPrepare = false;

        if (strpos($expression, '=>')) {
            $indexCol = $expression;
            $expression = str_replace('=>', ',', $expression);
        }

        $rest = $this->tableName .
            $this->joinStmt .
            $this->whereStmt .
            $this->groupByStmt .
            $this->orderStr .
            $this->offsetStr .
            $this->limitStr .
            $this->havingStr;

        $query = "SELECT " . $expression . " FROM " . $rest;

        if ($dbgNoPrepare) {
            $res = true;
            $str = preg_replace('/\$[0-9]+/', "'%s'", $query);
            $query = vsprintf($str, $this->prepareVals);
            $this->query($query);
        } else {
            $res = ($this->prepare($query) && $this->execute($this->prepareVals));
        }


//    echo($query.'<br>');;
//    var_dump(debug_backtrace());;

        if (!$res) {
            error_log('Database query failed :' . $query);
            //do not throwexception, just log the error. throw new Exception("Datenbank Abfrage fehler.");
            return false;
        }

        if ($indexCol)
            return $this->fetchAssoc($indexCol);

        $rows = array();

        while ($row = $this->fetchArray()) {
            if (!is_array($row)) continue;

            $rows [] = $row;
        }

        if (!empty($rows))
            return $rows;
        else return null;
    }


    /**
     * getMaxNumericSuffix ^
     * @param string $colName
     * @return int highest numeric suffix from column data
     */
    function getMaxNumericSuffix($colName) {
        return $this->get_no_parse("max(cast (regexp_replace($colName, '.*[a-zA-Z ]([0-9]+)$', E'\\\\1') as int))");
    }


    /**
     * getProcessedCol
     * @param string $colName
     * @return string gets the processed colname from the Database Structure pointer
     */
    function getProcessedCol($colName) {
        // ~ is the delimiter
        if (preg_match_all('~[^,]*\([^)]++\)[^,]*|[^,]++~i', $colName, $matches) && (count($matches[0]) > 1)) {
            //http://stackoverflow.com/questions/16476744/exploding-a-string-using-a-regular-expression
            $fields = $matches[0];
            foreach ($fields as &$field) {
                //divisions.name,divisions.dp_division_id,json_object_agg(delivery_plan.yearmonth,delivery_plan.quantity
                $field = $this->getProcessedCol($field);
            }
            return implode(',', $fields);
        } // if $alias, then process $table.$colname again, and return with AS $alias
        else if (preg_match("/(.*) as (.*)/i", $colName, $matches)) {
            return $this->getProcessedCol($matches[1]) . ' AS ' . $matches[2];
        } else if (preg_match("/(DISTINCT ON) *\((.*)\) ([a-zA-Z0-9\._]*)/i", $colName, $matches)) {
            return $matches[1] . '(' . $this->getProcessedCol($matches[2]) . ') ' . $this->getProcessedCol($matches[3]);
        } else if (preg_match("/(to_char) *\((.*),(.*)\)/i", $colName, $matches)) {
            return $matches[1] . '(' . $this->getProcessedCol($matches[2]) . ',' . $matches[3] . ')';
        } else if (preg_match("/(concat_ws) *\((.*)\)/i", $colName, $matches)) {
            $sep_and_columns = explode(',', $matches[2]);
            if (is_array($sep_and_columns) && sizeof($sep_and_columns) > 1) {
                $separator = array_shift($sep_and_columns);
                $sep_and_columns = implode(',', $sep_and_columns);
                return $matches[1] . '(' . $separator . ',' . $this->getProcessedCol($sep_and_columns) . ')';
            }
        } //moved this up by one level to accommodate functions like sum(col1-col2)
        else if (preg_match("/(COUNT|DISTINCT|DISTINCT ON|json_object_agg|sum|array_to_json|json_agg|distance|is_valid_ikz) *\((.*)\)/i", $colName, $matches)) {
            return $matches[1] . '(' . $this->getProcessedCol($matches[2]) . ')';
        } //matches arithmetic operations on columns for ex delivery_plan.quantity-delivery-plan.requirement_met
        else if (preg_match("/([a-zA-Z0-9\._]*)(-|\+)([a-zA-Z0-9\._]*)/i", $colName, $matches)) {
            return $this->getProcessedCol($matches[1]) . ' ' . trim($matches[2]) . ' ' . $this->getProcessedCol($matches[3]);
        } else if (strpos($colName, ".") !== false) {
            $colNameArray = explode(".", $colName);
            return $this->databaseStructPtr->getDBStructColName($colNameArray[0], $colNameArray[1], true);
        } else {
            return $this->databaseStructPtr->getDBStructColName($this->tableName, $colName);
        }


    }

    /**
     * groupBy
     * $queryPtr->groupBy('depot_id')
     * @param string $colName
     * @return NewQueryPgsql
     */
    function groupBy($colName) {
        $this->groupByStmt .= ' GROUP BY ' . $this->getProcessedCol($colName) . ' ';
        return $this;

    }

    /**
     * orWhere
     * $queryPtr->where('vehicle_id','>',500)->orWhere('vehicle_id','<',200)
     * @param string $colName
     * @param string $whereOp
     * @param string $colVal
     * @param string $andOrOp
     * @return NewQueryPgsql
     */
    function orWhere($colName, $whereOp, $colVal = null, $andOrOp = 'AND') {
        $this->where($colName, $whereOp, $colVal, 'OR');
        return $this;
    }

    /**
     * bracketWhere
     * $bracket value "(" or ")"
     * $andOrOp value "AND", "OR" or ""
     * usage: any where arguments: bracketWhere("(") , Where($colanme, $wehreOp, $colVal, ""), bracketWhere(")")
     * usage: where arguments exist: bracketWhere("(", $andOrOp) , Where($colanme, $wehreOp, $colVal, ""), bracketWhere(")")
     */
    function bracketWhere($bracket, $andOrop = "") {
        if (empty($this->whereStmt)) {
            $this->whereStmt .= "\n" . "WHERE " . $bracket . " ";
        } else {
            $this->whereStmt .= " " . $andOrop . " " . $bracket . " ";
        }
        return $this;
    }

    /**
     * where
     * $queryPtr->where('vehicle_id','>',500)
     * @param string $colName
     * @param string $whereOp
     * @param string $colVal
     * @param string $andOrOp
     * @param boolean $returnStmt if true then $this->where() returns the whereStmt instead of appending to $this->whereStmt
     * @return NewQueryPgsql
     */
    function where($colName, $whereOp, $colVal = null, $andOrOp = 'AND', $returnStmt = false) {
        $function = '';

        if (preg_match('/^[ ]*([a-z_]+)[ ]*[(][ ]*([a-z_][a-z0-9_]*)[ ]*[)][ ]*$/i', $colName, $match)) {
            $function = $match[1];
            $colName = $match[2];
        }

        if (strpos($colName, ".") !== false)
            $processedCol = $this->getProcessedCol($colName);
        else
            $processedCol = $this->tableName . '.' . $this->getProcessedCol($colName);

        if ($function)
            $processedCol = "$function($processedCol)";

        if (in_array(trim($whereOp), array('IS'))) {
            $wherePrepPlaceholders = $colVal;
        } else if (isset($colVal)) {
            if (!is_array($colVal)) {
                $wherePrepPlaceholders = "$$this->placeHolderCnt";
                $this->prepareVals[] = $colVal;
                $this->placeHolderCnt++;
            } else {
                $wherePrepPlaceholders = array();
                foreach ($colVal as $val) {
                    $wherePrepPlaceholders[] = "$$this->placeHolderCnt";
                    $this->prepareVals[] = $val;
                    $this->placeHolderCnt++;
                }
                $wherePrepPlaceholders = '(' . implode(",", $wherePrepPlaceholders) . ')';
            }

        } else
            $wherePrepPlaceholders = '';


        //if function was called by the multipleWhereBuilder, then set up the prepareVals and placeHolderCnt but do not add to the $this->whereStmt
        if ($returnStmt) {
            return $processedCol . " " . $whereOp . " " . $wherePrepPlaceholders . ' ';
        } else {
            if (empty($this->whereStmt)) // if this is the first where clause being processed
                $this->whereStmt .= "\n" . 'WHERE ' . $processedCol . " " . $whereOp . " " . $wherePrepPlaceholders . ' ';
            else
                $this->whereStmt .= ' ' . $andOrOp . ' ' . $processedCol . " " . $whereOp . " " . $wherePrepPlaceholders . ' ';

            return $this;
        }
    }

    function multipleWhere() {
        $numargs = func_num_args();
        $arg_list = func_get_args();
        $this->multipleWhereBuilder('', $arg_list, $numargs);
        return $this;
    }

    /**
     * Queries like AND (vehicles_sales.vehicle_variant NOT IN (2) OR vehicles_sales.vehicle_variant IS NULL)
     * Example function call ->multipleAndWhere('vehicles_sales.vehicle_variant','NOT IN',array(2),
     * 'OR','vehicles_sales.vehicle_variant','IS','NULL')
     * @return NewQueryPgsql
     */
    function multipleAndWhere() {
        $numargs = func_num_args();
        $arg_list = func_get_args();
        $this->multipleWhereBuilder('AND', $arg_list, $numargs);
        return $this;
    }

    /**
     * Queries like OR (vehicles_sales.vehicle_variant NOT IN (2) OR vehicles_sales.vehicle_variant IS NULL)
     * Example function call ->multipleOrWhere('vehicles_sales.vehicle_variant','NOT IN',array(2),
     * 'OR','vehicles_sales.vehicle_variant','IS','NULL')
     * @return NewQueryPgsql
     */
    function multipleOrWhere() {
        $numargs = func_num_args();
        $arg_list = func_get_args();
        $this->multipleWhereBuilder('OR', $arg_list, $numargs);
        return $this;

    }

    /***
     * called by either multipleAndWhere or multipleOrWhere
     * @param string $mainWhereOp OR|AND
     * @param mixed $arg_list array of arguments passed
     * @param mixed $numargs sizeof the arguments array
     */
    function multipleWhereBuilder($mainWhereOp, $arg_list, $numargs) {
        $full_where_stmt = '';

        //start processing the where clause
        $whereindex = 0;
        do {
            /* for whereindex=0, this will never be true
              function call ->multipleOrWhere('vehicles_sales.vehicle_variant','NOT IN',array(2),
                              'OR','vehicles_sales.vehicle_variant','IS','NULL')
               but for whereindex>2, in the example above, $arg_list[3], will be OR
            */
            if (trim($arg_list[$whereindex]) == 'OR' || trim($arg_list[$whereindex]) == 'AND') {
                $whereOp = $arg_list[$whereindex];
                $whereindex++;
            } else $whereOp = '';
            //$whereindex++ get the value of array with index $whereindex and then move to the next index and so on
            //for example if whereindex=4 then $this->where($arg_list[4],$arg_list[5],$arg_list[6],'AND',true)
            //definitely pass true as last argument since $returnStmt has to be true so that $this->where() returns the whereStmt instead of appending to $this->whereStmt
            $full_where_stmt .= $whereOp . ' ' . $this->where($arg_list[$whereindex++], $arg_list[$whereindex++], $arg_list[$whereindex++], 'AND', true);

        } while ($whereindex < $numargs);

        if (empty($this->whereStmt))
            $this->whereStmt .= ' WHERE (' . $full_where_stmt . ') ';
        else
            $this->whereStmt .= ' ' . $mainWhereOp . ' (' . $full_where_stmt . ') ';
    }

    /**
     * join
     * $queryPtr->join('depots','depots.depot_id=vehicles.depot_id','FULL OUTER JOIN')
     * @param string $joinTable
     * @param string $joinCols
     * @param string $joinType
     * @return NewQueryPgsql
     */
    function join($joinTable, $joinCols, $joinType = "inner join") {
        if ((strncasecmp($joinCols, 'using ', 6) == 0) || (strncasecmp($joinCols, 'using(', 6) == 0)) {
            $joinCols = trim(substr($joinCols, 5));
            if ($joinCols[0] != '(')
                $joinCols = "($joinCols)";

            $this->joinStmt .= " $joinType $joinTable USING $joinCols ";
            return $this;
        }
        if (strpos($joinCols, 'AND') !== false) {
            $joinColsArray = array();
            $joinColsMultiple = explode('AND', $joinCols);
            foreach ($joinColsMultiple as $joinCols) {
                $parseJoinCols = explode('=', $joinCols);
                $lColName = $this->getProcessedCol($parseJoinCols[0]);
                $rColName = $this->getProcessedCol($parseJoinCols[1]);
                $joinColsArray[] = $lColName . '=' . $rColName;
            }
            $joinColsStr = implode(' AND ', $joinColsArray);
        } else {
            $parseJoinCols = explode('=', $joinCols);
            $lColName = $this->getProcessedCol($parseJoinCols[0]);
            $rColName = $this->getProcessedCol($parseJoinCols[1]);
            $joinColsStr = $lColName . '=' . $rColName;
        }
        $this->joinStmt .= ' ' . $joinType . ' ' . $joinTable . ' ON ' . $joinColsStr . ' ';
        return $this;
    }

    function innerJoin($joinTable, $joinCols) {
        return $this->join($joinTable, $joinCols, 'INNER JOIN');
    }

    function outerJoin($joinTable, $joinCols) {
        return $this->join($joinTable, $joinCols, 'FULL OUTER JOIN');
    }

    function leftJoin($joinTable, $joinCols) {
        return $this->join($joinTable, $joinCols, 'LEFT JOIN');
    }


    function orderBy($colName, $orderType = 'ASC') {
        if ($this->orderStr == '')
            $this->orderStr = "\n" . 'ORDER BY ' . $this->getProcessedCol($colName) . ' ' . $orderType . ' ';
        else
            $this->orderStr .= ', ' . $this->getProcessedCol($colName) . ' ' . $orderType . ' ';
        return $this;
    }

    /**
     * limit
     * $queryPtr->limit(10)
     * @param integer $numrows
     * @return NewQueryPgsql
     */
    function limit($numrows) {
        $this->limitStr = " LIMIT $$this->placeHolderCnt";
        $this->prepareVals[] = $numrows;
        $this->placeHolderCnt++;
        return $this;
    }

    /**
     * limit_direct
     * $queryPtr->limit_direct(10)
     * @param integer $numrows
     * @return NewQueryPgsql
     */
    function limit_direct($numrows) {
        $this->limitStr = " LIMIT $numrows";
        return $this;
    }

    /**
     * offset
     * $queryPtr->offset(10)
     * @param integer $numrows
     * @return NewQueryPgsql
     */
    function offset($numrows) {
        $this->offsetStr = " OFFSET $$this->placeHolderCnt";
        $this->prepareVals[] = $numrows;
        $this->placeHolderCnt++;
        return $this;
    }

    /**
     * offset_direct
     * $queryPtr->offset_direct(10)
     * @param integer $numrows
     * @return NewQueryPgsql
     */
    function offset_direct($numrows) {
        $this->offsetStr = " OFFSET $numrows";
        return $this;
    }


    function having($fstr, $fop, $fval) {
        $this->havingStr = " HAVING $fstr $fop $$this->placeHolderCnt";
        $this->prepareVals[] = $fval;
        $this->placeHolderCnt++;
        return $this;
    }

    /**
     * update
     *
     * @param array $updateCols
     * @param array $updateVals
     * @return boolean|number
     */
    function update($updateCols, $updateVals = null) {
        if (!isset ($updateVals)) {
            $updateVals = array_values($updateCols);
            $updateCols = array_keys($updateCols);
        }

        $history_vals = $this->prepareVals;
        $affected_ids = implode(',', $history_vals);
        $fields = $this->getProcessedCol(implode(',', $updateCols));
        $historyquery = "SELECT " . $fields . " FROM " . $this->tableName . $this->joinStmt . $this->whereStmt . $this->groupByStmt . $this->orderStr . $this->offsetStr . $this->limitStr;
        $db_history = pg_prepare($this->dbconn, '', $historyquery);
        $db_history = pg_execute($this->dbconn, '', $history_vals);


        $oldVals = array();

        $oldVals = @pg_fetch_row($db_history);
        if ($oldVals != $updateVals) {

            $thisquery = array('updatecols' => serialize($updateCols),
                'newvals' => serialize($updateVals),
                'oldvals' => serialize($oldVals),
                'username' => filter_var($_SESSION['sts_username'], FILTER_SANITIZE_STRING),
                'update_timestamp' => date('Y-m-d H:i:sO'),
                'affected_ids' => $affected_ids,
                'where_stmt' => $this->whereStmt,
                'tablename' => $this->tableName);
            pg_insert($this->dbconn, 'db_history', $thisquery);
        }

        // 		echo $query; print_r($this->prepareVals);
        // 		if(pg_last_error($this->dbconn))
        // 			return pg_last_error($this->dbconn);

        //

        foreach ($updateCols as $updateCol) {
            $updateCol = $this->getProcessedCol($updateCol);
            $updateColsPro[] = $updateCol . "=$$this->placeHolderCnt";
            $this->placeHolderCnt++;
        }

        $query = "UPDATE " . $this->tableName . " SET " . implode(",", $updateColsPro) . $this->whereStmt;

        if ($this->prepare($query)) {
            $this->prepareVals = array_merge($this->prepareVals, $updateVals);
            $returnval = $this->execute($this->prepareVals);
        }

        $errorMsg = pg_last_error($this->dbconn);
        if ($errorMsg) {
            error_log($errorMsg);
            return false;
        } else
            return 1;
    }

    /**
     * update_assoc
     *
     * @param array $assocData
     * @return boolean|number
     */
    function update_assoc(&$assocData) {
        return $this->update(array_keys($assocData), array_values($assocData));
    }

    /**
     *
     * @param array $insertVals
     * @return mixed|boolean
     */
    function insert_multiple_new($insertCols, $insertVals) {
        $sequencename = $this->databaseStructPtr->getSequenceName($this->tableName);

        $insert_placeholders = array();
        foreach ($insertVals as $insert_this_row) {
            $row_place_holders = array();
            foreach ($insert_this_row as $insert_this_val) {
                $row_place_holders[] = "$$this->placeHolderCnt";
                $this->prepareVals[] = $insert_this_val;
                $this->placeHolderCnt++;
            }
            $insert_placeholders[] = '(' . implode(',', $row_place_holders) . ')';
        }
        $insert_placeholders = implode(",", $insert_placeholders);

        $query = "INSERT INTO " . $this->tableName . " (" . implode(',', $insertCols) . ") VALUES " . $insert_placeholders;

        $this->prepare($query);
        $result = $this->execute($this->prepareVals);
        if (!$result)
            return false;

        if (!empty($sequencename)) {
            $result = $this->query("SELECT currval('$sequencename'); ");
            while ($row = $this->fetchArray())
                return $row['currval'];
        } else
            return true;

    }


    /**
     *
     * @param array $insertVals
     * @return mixed|boolean
     */
    function insert($insertVals, $returningCols = NULL) {
        if ($returningCols != NULL) {
            $countvals = count($insertVals);
            $qry = 'INSERT INTO ' . $this->tableName . ' (';
            $cols = implode(',', array_keys($insertVals));
            $qry .= $cols . ')';
            $values = array();
            for ($i = 1; $i <= $countvals; $i++) {
                $values[$i] = "\$$i";
            }
            $vals = implode(',', $values);
            if (is_array($returningCols))
                $returns = implode(',', $returningCols);
            else
                $returns = $returningCols;

            $qry .= ' Values(' . $vals . ') RETURNING ' . $returns;
            $prepes = pg_prepare($this->dbconn, '', $qry);
            if (!$prepes)
                return false;

            $exres = pg_execute($this->dbconn, '', array_values($insertVals));
            if (!$exres)
                return false;

            $row = pg_fetch_assoc($exres);
            return $row;
        } else {
            $sequencename = $this->databaseStructPtr->getSequenceName($this->tableName);

            $this->result = pg_insert($this->dbconn, $this->tableName, $insertVals);
            $this->logToFile("INSERT", $insertVals, '');
            if (!$this->result)
                return false;

            if (!empty($sequencename)) {
                $result = $this->query("SELECT currval('$sequencename'); ");
                while ($row = $this->fetchArray())
                    return $row['currval'];
            } else
                return true;
        }
    }

    function delete() {
        $query = 'DELETE FROM ' . $this->tableName . $this->whereStmt;
        $this->prepare($query);
        $this->execute($this->prepareVals);
// 		echo $query; print_r($this->prepareVals);
        if ($this->result === FALSE)
            return pg_last_error($this->dbconn);
        else
            return 1;

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

    function insertold_delete($insertTable, $insertVals) {
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
     * insertMultiple do not remove this function!
     *
     * @param array $insertTable
     * @param array $insertCols column names
     * @param array $insertVals values
     * @return integer ID of the edited row
     */

    function insertMultiple($insertCols, $insertVals) {
        $insertTable = $this->tableName;
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

    function deleteold_remove($deleteTable, $whereParams) {
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

    function updateold_delete($updateTable, $updateCols, $updateVals, $whereStmtParams) {
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
        // $rows = '';
        $rows[] = '';

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
        $rows = '';
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
        $result = pg_prepare($this->dbconn, $queryname, $querypre);
        //r($querypre);echo "\n";


        $this->logToFile("PREPARE", $querypre, $queryname);
        return $result;
    }

    /**
     * execute Executes the query using pg_execute function
     *
     * @param array $queryvals All the values to be substituted in the prepared statement returned by pg_prepare
     * @param string $queryname Name of the query to be prepared. Has to be unique for each query. Easier if left blank.
     * @return array Result resource as returned by the pg_execute() function
     */

    function execute($queryvals, $queryname = '') {

        //r($queryvals);
        // Execute the prepared query.
        $this->result = pg_execute($this->dbconn, $queryname, $queryvals);

        //r($queryvals);echo "\n";
        $this->logToFile('EXECUTE', $queryvals, $queryname);
        return $this->result;
    }

    /**
     * Query function
     * This is a Query Function
     *
     * @param string $query
     * @return resource
     */
    public function query($query, $cutSemicolon = true) {

        try {
            if ($cutSemicolon && ($pos = strpos($query, ';')))
                $query = substr($query, 0, $pos);

            $this->result = pg_query($this->dbconn, $query);
            $this->logToFile('QUERY', $query);

            if (!$this->result)
                throw new Exception ("Database Query Failed!");
        } catch (Exception $e) {
            die ($e->getMessage());
        }
        return $this->result;
    }


    /**
     * Fetch single row from result
     * fetch one row from the $result as an array
     *
     * @return array
     */
    public function fetchRow() {
        $row = pg_fetch_row($this->result);
        return $row;
    }


    public function fetchCol($colId) {
        $result = [];
        while (true) {
            $row = pg_fetch_assoc($this->result);

            if (empty ($row))
                break;
            else
                $result[] = $row[$colId];
        }

        return $result;
    }


    /**
     * Fetch single row from result
     * fetch one row from the $result as an object
     *
     * @return object
     */
    public function fetchObject() {
        $row = pg_fetch_object($this->result);
        return $row;
    }

    /**
     * Fetch single row from result
     * fetch one row from the $result as an array
     *
     * @return array
     */
    public function fetchArray() {
        $row = pg_fetch_assoc($this->result);
        return $row;
    }

    /**
     * Fetch single row from result
     * fetch one row from the $result as an array
     *
     * @return array
     */
    public function fetchNumArray() {
        $row = pg_fetch_array($this->result);
        return $row;
    }

    /**
     * Fetch all rows from result
     * @param bool $bFetchAssoc : if true (default) fetchAll return an assciative array instead of an numbered array
     *
     * @return array of arrays
     */
    public function fetchAll($bFetchAssoc = true) {
        $result = [];
        while (true) {
            if ($bFetchAssoc)
                $row = pg_fetch_assoc($this->result);
            else
                $row = pg_fetch_array($this->result);

            if (empty ($row))
                break;
            else
                $result[] = $row;
        }

        return $result;
    }


    public function fetchComment() {
        $result = [];
        $field = 0;
        while (true) {
            $oid = pg_field_type_oid($this->result, $field);
            if (!$oid)
                break;
            $result[] = $oid;
            $field++;
        }
        return $result;
    }

    /**
     * Count number of rows in the $this->result resource
     *
     * @return integer
     */
    public function getNumRows() {
        return pg_num_rows($this->result);
    }


    public function fetchAssoc($indexCol) {
        $dataCol = false;
        $pos = strpos($indexCol, '=>');

        if ($pos !== false) {
            $dataCol = trim(substr($indexCol, $pos + 2));
            $indexCol = trim(substr($indexCol, 0, $pos));
        }

        $pos = strpos($indexCol, 'as ');
        if ($pos !== false)
            $indexCol = trim(substr($indexCol, $pos + 3));

        $pos = strpos($dataCol, 'as ');
        if ($pos !== false)
            $dataCol = trim(substr($dataCol, $pos + 3));

        do {
            $row = $this->fetchArray();
            if (!$row)
                return null;
        } while (!is_array($row));

        if (!isset($row[$indexCol])) {
            if ($pos = strpos($indexCol, '.'))
                $indexCol = substr($indexCol, $pos + 1);

            if (!isset($row[$indexCol]))
                throw new Exception(sprintf('Datenbank Fehler (get): keine gültige Index-Spalte: %s', $indexCol));
        }

        if ($dataCol && !array_key_exists($dataCol, $row)) {
            if ($pos = strpos($dataCol, '.'))
                $dataCol = substr($dataCol, $pos + 1);

            if (!array_key_exists($dataCol, $row))
                throw new Exception(sprintf('Datenbank Fehler (get): keine gültige Daten-Spalte: %s', $dataCol));
        }

        $rows = [];
        while ($row) {
            if (is_array($row)) {
                $ixVal = $row[$indexCol];
                if (isset($rows[$ixVal]))
                    throw new Exception(sprintf('Datenbank Fehler (get): Mehrfacher  Schlüsselwert für Spalte:"%s"=%s.', $indexCol, $ixVal));

                if ($dataCol)
                    $rows[$ixVal] = $row[$dataCol];
                else {
                    $rows[$ixVal] =  &$row;
                    unset ($row);
                }
            }
            $row = $this->fetchArray();
        }


        return $rows;
    }

    // -----------------------------------------------------------------------------------------
    public function GetLastError() {
        return pg_last_error();
    }

    // -----------------------------------------------------------------------------------------
    function EscapeString($string) {
        return pg_escape_string($this->dbconn, $string);
    }

    // -----------------------------------------------------------------------------------------
    function logToFile($func, $query, $queryname = "") {
        $debug = &$GLOBALS['debug']['sql'];
        $errString = "";
        $queryvals = "";

        if ($debug['level'] > SQL_LOG_OFF) {
            switch ($func) {
                case 'PREPARE':
                    if ($queryname == "") $queryname = '@';
                    $this->preparedQuery[$queryname] = $query;
                    $errString = @pg_last_error();
                    if ($errString == "")
                        return;
                    break;

                case 'EXECUTE';
                    if ($queryname == "") $queryname = '@';
                    $queryvals = $query;
                    $query = $this->preparedQuery[$queryname];
                    unset ($this->preparedQuery[$queryname]);
                    if ($this->result)
                        $errString = pg_result_error($this->result);
                    else
                        $errString = pg_last_error();
                    break;

                case 'QUERY':
                    $errString = pg_result_error($this->result);
                    break;

                case 'INSERT':
                    $errString = pg_last_error();
                    foreach ($query as $col => $value)
                        $errString .= "\n$col=>'$value'";
                    break;

            }
        }


        $doLog = ($debug['level'] == SQL_LOG_ALL) || (($errString != "") && ($debug['level'] == SQL_LOG_ERROR));
        if ($doLog || $this->displayquery) {
            if (!$queryvals)
                $queryvals = $this->prepareVals;

            $num = count($queryvals);
            $logstr = $query;

            for ($n = $num; $n > 0; $n--) {
                $key = '$' . $n;
                $logstr = str_replace($key, "'" . $queryvals[$n - 1] . "'", $logstr);
            }
            // $str=preg_replace('/\$[0-9]+/',"'%s'",$query);
            // $logstr = vsprintf ($str, $queryvals);
        }

        if ($doLog && $debug['logfile']) {
            if (!($f = fopen($debug['logfile'], "at+"))) return;
            //only for INSERT
            if (is_array($logstr)) $logstr = 'INSERT statement';
            fprintf($f, "\n======================================== %s'.-10s: %s ==================================================\n\n%s\n", $func, date("d.M.Y - G:i:s:"), $logstr);
            if ($errString)
                fprintf($f, "----------------------------------------------------------------------------\n%s\n\n", $errString);
            fclose($f);
        }

        if ($this->displayquery) {
            if ($errString) echo $logstr . "<br><br>$errString<br><br>\n";
            else    echo $logstr;
        }
    }
}

?>