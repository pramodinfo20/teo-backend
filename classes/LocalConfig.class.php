<?php

class LocalConfig {
    protected $db = [];
    protected $debug = [];
    protected $config = [];
    protected $config_file = "";

    function __construct($application) {
        $dirname = dirname($_SERVER['DOCUMENT_ROOT']);


        $this->config_file = "$dirname/config/$application.config.php";
        $this->config_file = "/var/www/config/webinterface.config.php";

        if (file_exists($this->config_file)) {
            $this->IncludeConfig($this->config_file, $this->db, $this->debug, $this->config);
            $GLOBALS['config'] = $this;
        }
    }


    function IncludeConfig($filename, &$db, &$debug, &$config) {
        include($filename);
    }


    function get_db($dbname) {
        return $this->db[$dbname];
    }

    function get_debug() {
        return $this->debug;
    }

    function get_property($tag, $default = null) {
        if (isset ($this->config[$tag]))
            return $this->config[$tag];
        return $default;
    }
}