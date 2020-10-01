<?php

/**
 * databaseStructure.class.php
 * @brief Klasse für Datenbank Struktur
 * @author Pradeep Mohan
 * @details Interface to handle different database structures
 */
interface DatabaseStructure {

    public function getTableName($tableName);

    public function getTableHeadings($tableName, $selectCols = '');

    public function getTableCols($tableName);

    public function getColName($tableName, $colName);
}

?>