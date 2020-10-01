<?php

/**
 * database.class.php
 * @brief Klasse für die Database
 * @author Pradeep Mohan
 * @details Klasse für die Database
 */
class DatabaseMain {
    /**
     * Hold the PostgreSQL link resource
     *
     * @var resource $dbconn
     */
    protected $dbconn;
    /**
     * Hostname
     *
     * @var string $host
     */
    protected $host;
    /**
     * Database port
     *
     * @var string $port
     */
    protected $port;
    /**
     * Username
     *
     * @var string $user
     */
    protected $user;
    /**
     * Password
     *
     * @var string $passwd
     */
    protected $passwd;
    /**
     * Databasename to connect
     *
     * @var string $databaseName
     */
    protected $databaseName;
    /**
     * Object of the QueryMain class
     *
     * @var string $queryPtr
     */
    public $queryPtr;

    /**
     * Konstruktor
     *
     * @param string $host
     *          database host name
     * @param string $databaseName
     *          database name
     * @param string $user
     *          username
     * @param string $passwd
     *          password
     */
    public function __construct($host, $port, $databaseName, $user, $passwd, $databaseStructure) {
        $this->host = $host;
        $this->port = $port;
        $this->databaseName = $databaseName;
        $this->user = $user;
        $this->passwd = $passwd;
        $this->databaseStructure = $databaseStructure;
        $this->connect();
        return $this;
    }

    /**
     * getDBConnection Returns the DBHandle/DB Connection Resource
     *
     * @return dbConn PostgresqlResource
     */
    public function getDBConnection() {
//    r(debug_backtrace());
        return $this->dbconn;
    }

    /**
     * getDBStructure Returns the DatabaseStructure pointer
     *
     * @return
     */
    public function getDBStructure() {
        return $this->databaseStructure;
    }
}

?>