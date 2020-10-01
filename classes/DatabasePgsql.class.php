<?php

/**
 * @brief Klasse für PostgreSQL Datenbank
 * @author Pradeep Mohan
 * @details Klasse für PostgreSQL Datenbank
 */
class DatabasePgsql extends DatabaseMain {
    /**
     * Implements the pg_connect function to assign the PostgreSQL resource to $this->dbconn
     * Initialises the $this->queryPtr object
     */

    function newQuery($tableName) {
        $queryPtr = new NewQueryPgsql ($this, $tableName);
        return $queryPtr;
    }


    function connect() {
        $db = $this->databaseName;
        $f = false;
        $fConn = null;


        if (isset($GLOBALS['dbCon'][$db])) {
            $this->dbconn = &$GLOBALS['dbCon'][$db];
            $this->queryPtr = new QueryPgsql ($this);
            return;
        }

        $conn_log = isset ($GLOBALS['debug']['sql']['connection_log']);

        if ($conn_log) {
            $tody = date("Y-M-d");
            $filename = "/var/www/stslogs/$db-connect-$tody.csv";
            $fExists = file_exists($filename);
            $fConn = fopen($filename, "at+");

            if (!$fExists)
                fprintf($fConn, "timestamp,session,username,ip,status\n");
        }

        try {
            if (isset ($GLOBALS['VERBOSE']) && isset ($GLOBALS['debug']['sql'])) {
                $debug = &$GLOBALS['debug']['sql'];
                $f = fopen($debug['logfile'], "at+");
            }

            $GLOBALS['dbCon'][$db] = @pg_connect("host=$this->host port=$this->port dbname=$db user=$this->user password=$this->passwd");

            if ($fConn) {
                $state = $GLOBALS['dbCon'][$db] ? "connected" : "error";
                $user = (isset ($_SESSION['sts_username']) ? $_SESSION['sts_username'] : 'unknown user');
                fprintf($fConn, "%s,%s,%s,%s,%s\n", date("d.M.Y - G:i:s:u"), session_id(), $user, $_SERVER['REMOTE_ADDR'], $state);
                if ($state == 'error')
                    foreach ($_SERVER as $key => $value)
                        fprintf($fConn, "--> %20s: %s\n", $key, $value);
                fclose($fConn);

                if (!$fExists)
                    chmod($filename, 0660);
            }


            if (!$GLOBALS['dbCon'][$db]) {
                throw new Exception ("Could not connect to PostgreSQL $db Server 1");
            } else {
                $this->dbconn = &$GLOBALS['dbCon'][$db];
                $this->queryPtr = new QueryPgsql ($this);
                if ($f) {
                    fprintf($f, "Database open successfully: host={$this->host} port={$this->port} dbname={$this->databaseName} user={$this->user} session timestamp=%s", date("d.M.Y - G:i:s:"));
                    fclose($f);
                }
            }
        } catch (Exception $e) {
            if ($f) {
                fprintf($f, "Error opening Database with: host={$this->host} port={$this->port} dbname={$this->databaseName} user={$this->user} on timestamp:%s\n", date("d.M.Y - G:i:s:"));
                fprintf($f, "Message: %s\n", $e->getMessage());
                fclose($f);
            }
            die ($e->getMessage());
        }
    }
}

?>