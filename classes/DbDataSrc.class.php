<?php

/**
 * datasrc.class.php
 * @author Pradeep Mohan
 */
interface DataSrc {
    /**
     *
     * @param string $selectTable
     */
    public function selectAll($selectTable);//@todo different argument from the declaration yet no problem how?

    /**
     *
     * @param string $insertTable
     * @param array $insertValues
     * @return integer
     */
    public function insert($insertTable, $insertValues);

    /**
     *
     * @param string $insertTable
     * @param array $insertCols
     * @param array $insertValues
     * @return integer
     */
    public function insertMultiple($insertTable, $insertCols, $insertValues);

    /**
     *
     * @param string $updateTable
     * @param array $updateColumns
     * @param array $updateValues
     * @param array $whereParams
     * @return integer
     */
    public function update($updateTable, $updateColumns, $updateValues, $whereParams);

    /**
     *
     * @param string $deleteTable
     * @param array $whereParams
     * @return boolean
     */
    public function delete($deleteTable, $whereParams);
}

/**
 * Klasse für Datenbank als die Datenquelle
 * @author Pradeep Mohan
 * Klasse für Datenbank als die Datenquelle z.B. PostgreSQL oder MySQL Datenbank
 *
 */
class DbDataSrc implements DataSrc {
    /**
     * The DatabaseMain pointer
     *
     * @var DatabaseMain $databasePtr
     */

    public $databasePtr;
// 	private $databaseStructPtr; variation from  klassendiagram

    /**
     * Konstruktor
     *
     * @param DatabaseMain $databasePtr Pointer to the database being used
     */

    public function __construct($databasePtr) {
        $this->databasePtr = $databasePtr;
    }


    public function newQuery($tableName) {
        return $this->databasePtr->newQuery($tableName);
    }

    /**
     * selectAll Passes parameters to the selectQueryBuilder function, gets the result rows and returns it
     *
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
     * @return array Array of rows from the selectQueryBuilder function
     */
    public function selectAll($selectTable, $selectCols = '', $whereStmtParams = null, $orderStmtParams = null, $joinStmtParams = null, $showHeading = false) {

        return $this->databasePtr->queryPtr->selectQueryBuilder($selectTable, $selectCols, $whereStmtParams, $orderStmtParams, $joinStmtParams, $showHeading);
    }

    /**
     * specialSql
     * @param string $specialSql
     * @return
     */
    public function specialSql($specialSql) {
        return $this->databasePtr->queryPtr->specialSql($specialSql);
    }

    /**
     * specialSqlPrepare
     * @param string $specialSql
     * @return
     */
    public function specialSqlPrepare($prepQuery, $vals) {
        return $this->databasePtr->queryPtr->specialSqlPrepare($prepQuery, $vals);
    }

    /**
     *
     * @param integer $timeDuration
     * @param string $timeUnit
     * @param string $returnName
     * @return string timestamp with timezone
     */
    public function getTimeStamp($timeDuration = 0, $timeUnit = 'hours', $returnName = 'timenw') {
        $timeUnitDef = array("hours" => "HOURS", "minutes" => "MINUTES", "seconds" => "SECONDS");
        $sqlQuery = "SELECT now() + INTERVAL '" . $timeDuration . " " . $timeUnitDef[$timeUnit] . "' AS " . $returnName;
        $result = $this->databasePtr->queryPtr->specialSql($sqlQuery);
        return $result[0];

    }

    /**
     * (non-PHPdoc)
     * @see DataSrc::insert()
     */
    public function insert($insertTable, $insertValues) {
        return $this->databasePtr->queryPtr->insert($insertTable, $insertValues);
    }

    /**
     * (non-PHPdoc)
     * @see DataSrc::insertMultiple()
     */

    public function insertMultiple($insertTable, $insertCols, $insertValues) {
        return $this->databasePtr->queryPtr->insertMultiple($insertTable, $insertCols, $insertValues);
    }

    /**
     * (non-PHPdoc)
     * @see DataSrc::update()
     */
    public function update($updateTable, $updateColumns, $updateValues, $whereParams) {
        return $this->databasePtr->queryPtr->update($updateTable, $updateColumns, $updateValues, $whereParams);
    }

    /**
     * (non-PHPdoc)
     * @see DataSrc::delete()
     */
    public function delete($deleteTable, $whereParams) {
        return $this->databasePtr->queryPtr->delete($deleteTable, $whereParams);
    }
}

?>