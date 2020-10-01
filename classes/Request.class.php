<?php
/**
 * request.class.php
 * Request class to process, hold all request variables from $_POST and $_GET
 * @author Pradeep Mohan
 */

/**
 * Request class to process, hold all request variables from $_POST and $_GET
 */
class Request {
    private $properties;
    private $getVars;
    private $feedback = array();

    /**
     * Konstruktor
     */

    function __construct() {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->properties = $_REQUEST;
            $this->getVars = $_GET;

            return;
        }
        //used only when calling program from
        foreach ($_SERVER['argv'] as $arg) {
            if (strpos($arg, '=')) {
                list($key, $val) = explode("=", $arg);
                $this->setProperty($key, $val);
            }
        }

    }

    function get_safe_val($unsafe_val) {
        $safe_val = null;
        if (is_array($unsafe_val)) {
            $safe_val = filter_var_array($unsafe_val, FILTER_SANITIZE_STRING);
        } else {
            $safe_val = filter_var($unsafe_val, FILTER_SANITIZE_STRING);
        }
        return $safe_val;
    }

    /**
     * Gets the required REQUEST variable value
     *
     * @param string $key
     */
    function getProperty($key, $default = null) {
        if (isset($this->properties[$key])) {
            return $this->get_safe_val($this->properties[$key]);
        }
        return $default;
    }

    /**
     * Sets the value of a REQUEST variable
     * @param string $key name of the REQUEST variable to be set
     * @param string $val the value of REQUEST variable
     */

    function setProperty($key, $val) {
        $this->properties[$key] = $val;
    }

    function unsetProperty($key) {
        unset ($this->properties[$key]);
    }

    function clear($holdAction = true) {
        if ($holdAction) {
            $action = $this->properties['action'];
            $this->properties = array('action' => $action);
        } else {
            $this->properties = array();
        }
    }
}
